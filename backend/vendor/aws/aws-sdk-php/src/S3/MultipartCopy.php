<?php

namespace Aws\S3;

use Aws\Arn\ArnParser;
use Aws\CommandPool;
use Aws\Exception\AwsException;
use Aws\Multipart\AbstractUploadManager;
use Aws\Multipart\UploadState;
use Aws\ResultInterface;
use Aws\S3\Exception\MultipartCopyAnnotationException;
use Aws\S3\Exception\S3Exception;
use GuzzleHttp\Promise as P;
use GuzzleHttp\Promise\Coroutine;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7;

class MultipartCopy extends AbstractUploadManager
{
    use MultipartUploadingTrait {
        getInitiateParams as private traitGetInitiateParams;
    }

    private const VALID_METADATA_DIRECTIVES = [
        'COPY' => true,
        'REPLACE' => true,
    ];

    private const TAGS_DIRECTIVE_UNSPECIFIED = 'UNSPECIFIED';
    private const TAGS_DIRECTIVE_COPY        = 'COPY';
    private const TAGS_DIRECTIVE_REPLACE     = 'REPLACE';

    private const VALID_TAGS_DIRECTIVES = [
        self::TAGS_DIRECTIVE_UNSPECIFIED => true,
        self::TAGS_DIRECTIVE_COPY        => true,
        self::TAGS_DIRECTIVE_REPLACE     => true,
    ];

    private const ANNOTATIONS_DIRECTIVE_UNSPECIFIED = 'UNSPECIFIED';
    private const ANNOTATIONS_DIRECTIVE_COPY        = 'COPY';
    private const ANNOTATIONS_DIRECTIVE_EXCLUDE     = 'EXCLUDE';

    private const VALID_ANNOTATIONS_DIRECTIVES = [
        self::ANNOTATIONS_DIRECTIVE_UNSPECIFIED => true,
        self::ANNOTATIONS_DIRECTIVE_COPY        => true,
        self::ANNOTATIONS_DIRECTIVE_EXCLUDE     => true,
    ];

    /** Phase 3 PutObjectAnnotation retry policy (full-jitter exponential, capped). */
    private const ANNOTATION_MAX_ATTEMPTS  = 3;
    private const ANNOTATION_BASE_DELAY_MS = 100;
    private const ANNOTATION_MAX_DELAY_MS  = 5000;

    /** Metadata fields forwarded from source to destination on metadata_directive=COPY. */
    private static array $copyMetadataFields = [
        'CacheControl',
        'ContentDisposition',
        'ContentEncoding',
        'ContentLanguage',
        'ContentType',
        'Expires',
        'Metadata',
    ];

    /** @var string|array */
    private $source;
    /** @var string|null User-provided versionId (from source string or config). */
    private $sourceVersionId;
    /** @var string|null HeadObject-derived versionId (used only for Phase 1 reads). */
    private ?string $headDerivedVersionId = null;
    /** @var ResultInterface|null */
    private $sourceMetadata;
    /** @var string|null */
    private ?string $sourceETag = null;
    /** @var array|null Source TagSet (populated when tags_directive resolves to COPY). */
    private ?array $sourceTags = null;
    /** @var array<string,string> Annotation name => payload, populated in Phase 1. */
    private array $annotationBodies = [];

    /**
     * Creates a multipart upload for copying an S3 object.
     *
     * The valid configuration options are as follows:
     *
     * - acl: (string) ACL to set on the object being upload. Objects are
     *   private by default.
     * - before_complete: (callable) Callback to invoke before the
     *   `CompleteMultipartUpload` operation. The callback should have a
     *   function signature like `function (Aws\Command $command) {...}`.
     * - before_initiate: (callable) Callback to invoke before the
     *   `CreateMultipartUpload` operation. The callback should have a function
     *   signature like `function (Aws\Command $command) {...}`.
     * - before_upload: (callable) Callback to invoke before `UploadPartCopy`
     *   operations. The callback should have a function signature like
     *   `function (Aws\Command $command) {...}`.
     * - bucket: (string, required) Name of the bucket to which the object is
     *   being uploaded.
     * - concurrency: (int, default=int(5)) Maximum number of concurrent
     *   `UploadPart` operations allowed during the multipart upload.
     * - key: (string, required) Key to use for the object being uploaded.
     * - params: (array) An array of key/value parameters that will be applied
     *   to each of the sub-commands run by the uploader as a base.
     *   Auto-calculated options will override these parameters. If you need
     *   more granularity over parameters to each sub-command, use the before_*
     *   options detailed above to update the commands directly.
     * - part_size: (int, default=int(5242880)) Part size, in bytes, to use when
     *   doing a multipart upload. This must between 5 MB and 5 GB, inclusive.
     * - state: (Aws\Multipart\UploadState) An object that represents the state
     *   of the multipart upload and that is used to resume a previous upload.
     *   When this option is provided, the `bucket`, `key`, and `part_size`
     *   options are ignored.
     * - metadata_directive: (string, default='COPY') 'COPY' or 'REPLACE'.
     *   Caller-supplied `params['Metadata']` does NOT change the directive,
     *   set this option explicitly to opt into REPLACE. When 'COPY', source
     *   metadata fields (Metadata, CacheControl, ContentDisposition,
     *   ContentEncoding, ContentLanguage, ContentType, Expires) are forwarded
     *   and any matching caller-supplied fields are dropped. When 'REPLACE',
     *   no source metadata is read and caller-supplied params are used as-is.
     * - tags_directive: (string, default='UNSPECIFIED') 'UNSPECIFIED', 'COPY',
     *   or 'REPLACE'. UNSPECIFIED means no tag work and any caller-supplied
     *   `params['Tagging']` is dropped. COPY reads source tags via GetObjectTagging
     *   and writes them to the destination via PutObjectTagging after
     *   CompleteMultipartUpload. REPLACE skips the read and writes
     *   caller-supplied `params['Tagging']` to the destination.
     * - annotations_directive: (string, default='UNSPECIFIED') 'UNSPECIFIED',
     *   'COPY', or 'EXCLUDE'. UNSPECIFIED and EXCLUDE both skip annotation
     *   work. COPY reads source annotations via ListObjectAnnotations and
     *   per-name GetObjectAnnotation, then writes them to the destination
     *   via per-name PutObjectAnnotation.
     * - source_metadata: (Aws\ResultInterface) The result of a HeadObject call
     *   on the copy source. If not provided, the SDK makes a HeadObject request
     *   to obtain the source object's size and metadata. Providing this avoids
     *   the extra request.
     * - display_progress: (boolean) Set true to track status in 1/8th increments
     *   for upload.
     *
     * @param S3ClientInterface $client
     * @param string|array      $source
     * @param array             $config
     */
    public function __construct(
        S3ClientInterface $client,
        $source,
        array $config = []
    ) {
        if (is_array($source)) {
            $this->source = $source;
        } else {
            $this->source = $this->getInputSource($source);
        }

        $config = array_change_key_case($config);

        // Resume: replay the original directives unless caller overrides.
        if (isset($config['state']) && $config['state'] instanceof UploadState) {
            $stateConfig = $config['state']->getConfig();
            if (!empty($stateConfig)) {
                $config += array_change_key_case($stateConfig);
            }
        }

        parent::__construct(
            $client,
            $config + ['source_metadata' => null]
        );

        if ($this->displayProgress) {
            $this->getState()->setProgressThresholds(
                $this->sourceMetadata["ContentLength"]
            );
        }
    }

    /**
     * Alias of {@see self::upload()}.
     *
     * @return ResultInterface
     */
    public function copy()
    {
        return $this->upload();
    }

    /**
     * Drives the multipart copy workflow:
     *
     *   Phase 1: Source reads.
     *     HeadObject (pins VersionId), then (no-op unless tags/annotations were opted in)
     *     GetObjectTagging and ListObjectAnnotations + per-name GetObjectAnnotation in parallel.
     *
     *   Phase 2: Multipart upload. CreateMultipartUpload, UploadPartCopy parts
     *     in parallel, CompleteMultipartUpload.
     *
     *   Phase 3 (optional): Destination writes (skipped unless tags/annotations were
     *     opted in). PutObjectTagging (atomic, fail-fast), then per-name
     *     PutObjectAnnotation in parallel with retries (partial failure is
     *     surfaced via {@see MultipartCopyAnnotationException}).
     *
     * @return PromiseInterface
     */
    public function promise(): PromiseInterface
    {
        if ($this->promise) {
            return $this->promise;
        }

        return $this->promise = Coroutine::of(function () {
            try {
                // Phase 1: HeadObject pins VersionId for the conditional reads below.
                yield $this->fetchSourceMetadata();

                $tagsDir  = $this->resolveTagsDirective();
                $annotDir = $this->resolveAnnotationsDirective();

                // Tags + annotations are independent once VersionId is pinned.
                $concurrent = [];
                if ($tagsDir === self::TAGS_DIRECTIVE_COPY) {
                    $concurrent[] = $this->fetchSourceTags();
                }
                if ($annotDir === self::ANNOTATIONS_DIRECTIVE_COPY) {
                    $concurrent[] = $this->fetchSourceAnnotations();
                }
                if ($concurrent) {
                    yield P\Utils::all($concurrent);
                }

                if ($this->state->isCompleted()) {
                    throw new \LogicException(
                        'This multipart upload has already been completed or aborted.'
                    );
                }

                // Phase 2: initiate.
                if (!$this->state->isInitiated()) {
                    if (is_callable($this->config['prepare_data_source'])) {
                        $this->config['prepare_data_source']();
                    }
                    $init = yield $this->execCommand('initiate', $this->getInitiateParams());
                    $this->state->setUploadId(
                        $this->info['id']['upload_id'],
                        $init[$this->info['id']['upload_id']]
                    );
                    $this->state->setStatus(UploadState::INITIATED);
                }

                // Phase 2: parts.
                $resultHandler = $this->getResultHandler($errors);
                $pool = new CommandPool(
                    $this->client,
                    $this->getUploadCommands($resultHandler),
                    [
                        'concurrency' => $this->config['concurrency'],
                        'before'      => $this->config['before_upload'],
                    ]
                );

                yield $pool->promise();

                if ($errors) {
                    throw new $this->config['exception_class']($this->state, $errors);
                }

                // Phase 2: complete.
                $complete = yield $this->execCommand('complete', $this->getCompleteParams());
                $this->state->setStatus(UploadState::COMPLETED);

                // Phase 3: destination writes (if applicable).
                $destETag      = $complete['ETag']      ?? null;
                $destVersionId = $complete['VersionId'] ?? null;

                yield from $this->writeDestinationTags($tagsDir, $destVersionId);

                if ($annotDir === self::ANNOTATIONS_DIRECTIVE_COPY) {
                    yield from $this->writeDestinationAnnotations($destETag, $destVersionId);
                }

                yield $complete;
            } catch (AwsException $e) {
                throw new $this->config['exception_class']($this->state, $e);
            }
        });
    }

    /**
     * @return array
     */
    protected function loadUploadWorkflowInfo()
    {
        return [
            'command' => [
                'initiate' => 'CreateMultipartUpload',
                'upload' => 'UploadPartCopy',
                'complete' => 'CompleteMultipartUpload',
            ],
            'id' => [
                'bucket' => 'Bucket',
                'key' => 'Key',
                'upload_id' => 'UploadId',
            ],
            'part_num' => 'PartNumber',
        ];
    }

    /**
     * Yields UploadPartCopy commands for parts not yet uploaded.
     *
     * @param callable $resultHandler
     * @return \Generator
     */
    protected function getUploadCommands(callable $resultHandler)
    {
        $parts = ceil($this->getSourceSize() / $this->determinePartSize());

        for ($partNumber = 1; $partNumber <= $parts; $partNumber++) {
            if (!$this->state->hasPartBeenUploaded($partNumber)) {
                $command = $this->client->getCommand(
                    $this->info['command']['upload'],
                    $this->createPart($partNumber, $parts) + $this->getState()->getId()
                );
                $command->getHandlerList()->appendSign($resultHandler, 'mup');
                yield $command;
            }
        }
    }

    /**
     * Builds the parameter array for a single UploadPartCopy.
     *
     * @param int $partNumber
     * @param int $partsCount
     * @return array
     */
    private function createPart($partNumber, $partsCount)
    {
        $data = [];

        $config = $this->getConfig();
        $params = $config['params'] ?? [];
        foreach ($params as $k => $v) {
            $data[$k] = $v;
        }
        // Source may be a string or, when the key contains '?', an array.
        if (is_array($this->source)) {
            $key = str_replace('%2F', '/', rawurlencode($this->source['source_key']));
            $bucket = $this->source['source_bucket'];
        } else {
            [$bucket, $key] = explode('/', ltrim($this->source, '/'), 2);
            $key = implode(
                '/',
                array_map(
                    'urlencode',
                    explode('/', rawurldecode($key))
                )
            );
        }

        $uri = ArnParser::isArn($bucket) ? '' : '/';
        $uri .= $bucket . '/' . $key;
        $data['CopySource'] = $uri;
        $data['PartNumber'] = $partNumber;
        if (!empty($this->sourceVersionId)) {
            $data['CopySource'] .= "?versionId=" . $this->sourceVersionId;
        }
        if ($this->sourceETag !== null) {
            $data['CopySourceIfMatch'] = $this->sourceETag;
        }

        $defaultPartSize = $this->determinePartSize();
        $startByte = $defaultPartSize * ($partNumber - 1);
        $data['ContentLength'] = $partNumber < $partsCount
            ? $defaultPartSize
            : $this->getSourceSize() - ($defaultPartSize * ($partsCount - 1));
        $endByte = $startByte + $data['ContentLength'] - 1;
        $data['CopySourceRange'] = "bytes=$startByte-$endByte";

        return $data;
    }

    /**
     * @param ResultInterface $result
     * @return string
     */
    protected function extractETag(ResultInterface $result)
    {
        return $result->search('CopyPartResult.ETag');
    }

    /**
     * Builds CreateMultipartUpload params: applies metadata_directive, strips
     * Tagging when Phase 3 will write tags separately.
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function getInitiateParams()
    {
        $params = $this->traitGetInitiateParams();

        $directive = strtoupper($this->resolveMetadataDirective());

        if (!isset(self::VALID_METADATA_DIRECTIVES[$directive])) {
            throw new \InvalidArgumentException(
                "Invalid metadata_directive value '$directive'."
                . " Must be 'COPY' or 'REPLACE'."
            );
        }

        // CreateMultipartUpload has no MetadataDirective member. The directive is local-only.
        // Under COPY, forwarded fields exactly mirror source. Caller's params for fields
        // source is empty on are dropped, matching how tags work.
        if ($directive === 'COPY') {
            $sourceMetadata = $this->getSourceMetadata();
            foreach (self::$copyMetadataFields as $field) {
                if (!empty($sourceMetadata[$field])) {
                    $params[$field] = $sourceMetadata[$field];
                } else {
                    unset($params[$field]);
                }
            }
        }

        // When tags_directive is UNSPECIFIED, no tag work
        // happens at all and any caller-supplied
        // params['Tagging'] is dropped here too — callers who need their
        // tags applied must opt in via tags_directive='REPLACE'.
        unset($params['Tagging']);

        return $params;
    }

    /**
     * @return string|null
     */
    protected function getSourceMimeType()
    {
        return $this->getSourceMetadata()['ContentType'];
    }

    /**
     * @return int
     */
    protected function getSourceSize()
    {
        return $this->getSourceMetadata()['ContentLength'];
    }

    /**
     * Sync wrapper for callers in the constructor / trait that need a value now.
     *
     * @return ResultInterface
     */
    private function getSourceMetadata()
    {
        return $this->fetchSourceMetadata()->wait();
    }

    /**
     * Resolves the source HeadObject result and caches it.
     *
     * @return PromiseInterface
     */
    private function fetchSourceMetadata(): PromiseInterface
    {
        if (!$this->sourceMetadata instanceof ResultInterface
            && $this->config['source_metadata'] instanceof ResultInterface
        ) {
            $this->sourceMetadata = $this->config['source_metadata'];
            $this->captureSourceIdentifiers($this->sourceMetadata);
        }

        if ($this->sourceMetadata instanceof ResultInterface) {
            return P\Create::promiseFor($this->sourceMetadata);
        }

        return $this->client->headObjectAsync($this->buildHeadParams())
            ->then(function (ResultInterface $r): ResultInterface {
                $this->sourceMetadata = $r;
                $this->captureSourceIdentifiers($r);
                return $r;
            });
    }

    /**
     * Captures VersionId and ETag from a source HeadObject result.
     *
     * HeadObject-derived VersionId is used only for Phase 1 source reads
     * (tags/annotations). Only user-provided versionId is attached to
     * UploadPartCopy requests.
     *
     * @param ResultInterface $r
     * @return void
     */
    private function captureSourceIdentifiers(ResultInterface $r): void
    {
        if (!empty($r['VersionId'])) {
            $this->headDerivedVersionId = $r['VersionId'];
        }

        if (!empty($r['ETag'])) {
            $this->sourceETag = $r['ETag'];
        }
    }

    /**
     * Builds HeadObject params for the source, parsing `?versionId=` if present.
     *
     * @return array
     */
    private function buildHeadParams(): array
    {
        if (is_array($this->source)) {
            $headParams = [
                'Key'    => $this->source['source_key'],
                'Bucket' => $this->source['source_bucket'],
            ];
            if (isset($this->source['source_version_id'])) {
                $this->sourceVersionId = $this->source['source_version_id'];
                $headParams['VersionId'] = $this->sourceVersionId;
            }

            return $headParams;
        }

        [$bucket, $key] = explode('/', ltrim($this->source, '/'), 2);
        $headParams = [
            'Bucket' => $bucket,
            'Key'    => $key,
        ];
        if (str_contains($key, '?')) {
            [$key, $query] = explode('?', $key, 2);
            $headParams['Key'] = $key;
            $query = Psr7\Query::parse($query, false);
            if (isset($query['versionId'])) {
                $this->sourceVersionId = $query['versionId'];
                $headParams['VersionId'] = $this->sourceVersionId;
            }
        }

        return $headParams;
    }

    /**
     * Builds Bucket/Key (and VersionId if pinned) for source-side reads.
     *
     * Uses the user-provided sourceVersionId if available, otherwise falls
     * back to the HeadObject-derived versionId for Phase 1 consistency.
     *
     * @return array
     */
    private function buildSourceObjectParams(): array
    {
        if (is_array($this->source)) {
            $p = [
                'Bucket' => $this->source['source_bucket'],
                'Key'    => $this->source['source_key'],
            ];
        } else {
            [$bucket, $key] = explode('/', ltrim($this->source, '/'), 2);
            if (str_contains($key, '?')) {
                [$key] = explode('?', $key, 2);
            }

            $p = [
                'Bucket' => $bucket,
                'Key'    => rawurldecode($key),
            ];
        }

        $versionId = $this->sourceVersionId ?? $this->headDerivedVersionId;
        if (!empty($versionId)) {
            $p['VersionId'] = $versionId;
        }

        return $p;
    }

    /**
     * GetObjectTagging on the source. Caches `TagSet` on the instance.
     * Requires fetchSourceMetadata() to have resolved (uses pinned VersionId).
     *
     * @return PromiseInterface
     */
    private function fetchSourceTags(): PromiseInterface
    {
        return $this->client
            ->getObjectTaggingAsync($this->buildSourceObjectParams())
            ->then(function (ResultInterface $r): ResultInterface {
                $this->sourceTags = $r['TagSet'] ?? [];
                return $r;
            });
    }

    /**
     * Drains source annotation names, then fans out per-name
     * GetObjectAnnotation calls bounded by `concurrency`. Caches payloads
     * on the instance. Requires fetchSourceMetadata() to have resolved.
     *
     * @return PromiseInterface
     */
    private function fetchSourceAnnotations(): PromiseInterface
    {
        return Coroutine::of(function () {
            $listParams = $this->buildSourceObjectParams();

            $names = [];
            yield $this->client
                ->getPaginator('ListObjectAnnotations', $listParams)
                ->each(function (ResultInterface $page) use (&$names) {
                    foreach ($page['Annotations'] ?? [] as $entry) {
                        if (!empty($entry['AnnotationName'])) {
                            $names[] = $entry['AnnotationName'];
                        }
                    }
                });

            if (empty($names)) {
                return;
            }

            $getParams = $this->buildSourceObjectParams();
            $commands = array_map(function ($name) use ($getParams) {
                return $this->client->getCommand(
                    'GetObjectAnnotation',
                    $getParams + ['AnnotationName' => $name]
                );
            }, $names);

            $pool = new CommandPool($this->client, $commands, [
                'concurrency' => $this->config['concurrency'],
                'fulfilled' => function (
                    ResultInterface $result,
                    $iterKey
                ) use ($names) {
                    $name    = $names[$iterKey];
                    $payload = $result['AnnotationPayload'] ?? null;
                    $body    = $payload === null ? '' : (string) $payload;
                    // PutObjectAnnotation requires a payload >= 1 byte.
                    if ($body !== '') {
                        $this->annotationBodies[$name] = $body;
                    }
                },
                'rejected' => function (
                    $reason,
                    $iterKey,
                    PromiseInterface $aggregatePromise
                ) {
                    // Abort the pool on mid-loop precondition failures.
                    $aggregatePromise->reject($reason);
                },
            ]);

            yield $pool->promise();
        });
    }

    /**
     * Destination [Bucket, Key] for Phase 3 writes. Falls back to UploadState id
     * for resumed copies.
     *
     * @return array{0: string, 1: string}
     */
    private function resolveDestinationBucketAndKey(): array
    {
        $bucket = $this->config['bucket'] ?? null;
        $key    = $this->config['key']    ?? null;
        if ($bucket === null || $bucket === '' || $key === null || $key === '') {
            $id = $this->state->getId();
            $bucket = $id['Bucket'] ?? $bucket;
            $key    = $id['Key']    ?? $key;
        }

        return [$bucket, $key];
    }

    /**
     * Phase 3 step 1: PutObjectTagging on the destination, when the resolved
     * tags_directive is COPY (use $this->sourceTags) or REPLACE (use caller-
     * supplied params['Tagging']).
     *
     * @param string      $tagsDirective
     * @param string|null $destVersionId
     * @return \Generator
     */
    private function writeDestinationTags(
        string $tagsDirective,
        ?string $destVersionId
    ): \Generator
    {
        [$destBucket, $destKey] = $this->resolveDestinationBucketAndKey();

        if ($tagsDirective === self::TAGS_DIRECTIVE_REPLACE) {
            $callerTagging = $this->config['params']['Tagging'] ?? null;
            if ($callerTagging === null || $callerTagging === '') {
                return;
            }

            $tagging = is_array($callerTagging)
                ? $callerTagging
                : ['TagSet' => $this->parseTaggingQueryString((string) $callerTagging)];

            $params = [
                'Bucket'  => $destBucket,
                'Key'     => $destKey,
                'Tagging' => $tagging,
            ];
            if ($destVersionId !== null) {
                $params['VersionId'] = $destVersionId;
            }

            yield $this->client->putObjectTaggingAsync($params);
            return;
        }

        if ($tagsDirective !== self::TAGS_DIRECTIVE_COPY || empty($this->sourceTags)) {
            return;
        }

        $params = [
            'Bucket'  => $destBucket,
            'Key'     => $destKey,
            'Tagging' => ['TagSet' => $this->sourceTags],
        ];
        if ($destVersionId !== null) {
            $params['VersionId'] = $destVersionId;
        }

        yield $this->client->putObjectTaggingAsync($params);
    }

    /**
     * Phase 3 step 2: per-name PutObjectAnnotation on the destination.
     * Promise\Each::ofLimitAll over a generator: each step is invoked only
     * when a concurrency slot opens, bounding in-flight requests.
     *
     * @param string|null $destETag
     * @param string|null $destVersionId
     * @return \Generator
     * @throws MultipartCopyAnnotationException
     */
    private function writeDestinationAnnotations(
        ?string $destETag,
        ?string $destVersionId
    ): \Generator
    {
        if (empty($this->annotationBodies)) {
            return;
        }

        $succeeded = [];
        /** @var array<string,S3Exception> $failed */
        $failed = [];

        [$destBucket, $destKey] = $this->resolveDestinationBucketAndKey();

        $putAnnotationsCalls = function () use (
            $destBucket,
            $destKey,
            $destETag,
            $destVersionId,
            &$succeeded,
            &$failed
        ) {
            foreach ($this->annotationBodies as $name => $body) {
                $params = [
                    'Bucket'            => $destBucket,
                    'Key'               => $destKey,
                    'AnnotationName'    => $name,
                    'AnnotationPayload' => $body,
                ];
                if ($destVersionId !== null) {
                    $params['VersionId'] = $destVersionId;
                }
                if ($destETag !== null) {
                    $params['ObjectIfMatch'] = $destETag;
                }

                yield $this->putAnnotationWithRetries($params)->then(
                    function () use ($name, &$succeeded) {
                        $succeeded[] = $name;
                    },
                    function ($reason) use ($name, &$failed) {
                        $failed[$name] = $reason;
                    }
                );
            }
        };

        yield P\Each::ofLimitAll($putAnnotationsCalls(), $this->config['concurrency']);

        if ($failed) {
            throw new MultipartCopyAnnotationException(
                $this->state,
                $failed,
                $succeeded
            );
        }
    }

    /**
     * Single-annotation PutObjectAnnotation with exponential
     * backoff + jitter. Delay is set on the command via `@http.delay`.
     *
     * @param array $baseParams
     * @return PromiseInterface
     */
    private function putAnnotationWithRetries(array $baseParams): PromiseInterface
    {
        return Coroutine::of(function () use ($baseParams) {
            $delayMs = 0;
            for ($attempt = 1; $attempt <= self::ANNOTATION_MAX_ATTEMPTS; $attempt++) {
                $params = $baseParams;
                if ($delayMs > 0) {
                    $params['@http'] = ['delay' => $delayMs];
                }

                try {
                    $result = yield $this->client->putObjectAnnotationAsync($params);

                    yield P\Create::promiseFor($result);

                    return;
                } catch (S3Exception $e) {
                    $code = $e->getStatusCode();
                    $retryable = ($code !== null && $code >= 500);
                    if (!$retryable || $attempt === self::ANNOTATION_MAX_ATTEMPTS) {
                        throw $e;
                    }

                    // Full-jitter exponential backoff.
                    $base = self::ANNOTATION_BASE_DELAY_MS << ($attempt - 1);
                    $delayMs = random_int(0, min(self::ANNOTATION_MAX_DELAY_MS, $base));
                }
            }
        });
    }

    /**
     * @return string
     */
    private function resolveMetadataDirective(): string
    {
        $explicit = $this->config['metadata_directive'] ?? null;
        if ($explicit !== null) {
            return strtoupper((string) $explicit);
        }

        return 'COPY';
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    private function resolveTagsDirective(): string
    {
        $explicit = $this->config['tags_directive'] ?? null;
        if ($explicit === null) {
            return self::TAGS_DIRECTIVE_UNSPECIFIED;
        }

        $value = strtoupper((string) $explicit);
        if (!isset(self::VALID_TAGS_DIRECTIVES[$value])) {
            throw new \InvalidArgumentException(
                "Invalid tags_directive value '$value'. Must be one of: "
                . implode(', ', array_keys(self::VALID_TAGS_DIRECTIVES)) . '.'
            );
        }

        return $value;
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    private function resolveAnnotationsDirective(): string
    {
        $explicit = $this->config['annotations_directive'] ?? null;
        if ($explicit === null) {
            return self::ANNOTATIONS_DIRECTIVE_UNSPECIFIED;
        }

        $value = strtoupper((string) $explicit);
        if (!isset(self::VALID_ANNOTATIONS_DIRECTIVES[$value])) {
            throw new \InvalidArgumentException(
                "Invalid annotations_directive value '$value'. Must be one of: "
                . implode(', ', array_keys(self::VALID_ANNOTATIONS_DIRECTIVES)) . '.'
            );
        }

        return $value;
    }

    /**
     * URL-decoded source location, prefixed with '/' when not an ARN.
     *
     * @param string $inputSource
     * @return string
     */
    private function getInputSource($inputSource)
    {
        $sourceBuilder = ArnParser::isArn($inputSource) ? '' : '/';
        $sourceBuilder .= ltrim(rawurldecode($inputSource), '/');

        return $sourceBuilder;
    }
}
