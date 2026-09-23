<?php
namespace Aws\S3;

use Aws\CommandInterface;
use Aws\Multipart\UploadState;
use Aws\ResultInterface;

trait MultipartUploadingTrait
{
    private $uploadedBytes = 0;

    /**
     * Creates an UploadState object for a multipart upload by querying the
     * service for the specified upload's information.
     *
     * @param S3ClientInterface $client   S3Client used for the upload.
     * @param string            $bucket   Bucket for the multipart upload.
     * @param string            $key      Object key for the multipart upload.
     * @param string            $uploadId Upload ID for the multipart upload.
     * @param array             $config   Optional config to retain on the
     *                                    state. Pass the directive keys
     *                                    (`metadata_directive`,
     *                                    `tags_directive`,
     *                                    `annotations_directive`) the
     *                                    original copy was launched with so
     *                                    a resumed `MultipartCopy` replays
     *                                    Phase 3 with the same behavior. The
     *                                    caller can also override directives
     *                                    on the resume by passing them again
     *                                    to the `MultipartCopy` constructor.
     *
     * @return UploadState
     */
    public static function getStateFromService(
        S3ClientInterface $client,
        $bucket,
        $key,
        $uploadId,
        array $config = []
    ) {
        $state = new UploadState([
            'Bucket'   => $bucket,
            'Key'      => $key,
            'UploadId' => $uploadId,
        ], $config);

        foreach ($client->getPaginator('ListParts', $state->getId()) as $result) {
            // Get the part size from the first part in the first result.
            if (!$state->getPartSize()) {
                $state->setPartSize($result->search('Parts[0].Size'));
            }
            // Mark all the parts returned by ListParts as uploaded.
            foreach ($result['Parts'] as $part) {
                $state->markPartAsUploaded($part['PartNumber'], [
                    'PartNumber' => $part['PartNumber'],
                    'ETag'       => $part['ETag']
                ]);
            }
        }

        $state->setStatus(UploadState::INITIATED);

        return $state;
    }

    protected function handleResult(CommandInterface $command, ResultInterface $result)
    {
        $partData = [];
        $partData['PartNumber'] = $command['PartNumber'];
        $partData['ETag'] = $this->extractETag($result);
        $commandName = $command->getName();
        $checksumResult = $commandName === 'UploadPart'
            ? $result
            : $result[$commandName . 'Result'];

        if (isset($command['ChecksumAlgorithm'])) {
            $checksumMemberName = 'Checksum' . strtoupper($command['ChecksumAlgorithm']);
            $partData[$checksumMemberName] = $checksumResult[$checksumMemberName] ?? null;
        }

        $this->getState()->markPartAsUploaded($command['PartNumber'], $partData);

        // Updates counter for uploaded bytes.
        $this->uploadedBytes += $command["ContentLength"];
        // Sends uploaded bytes to progress tracker if getDisplayProgress set
        if ($this->displayProgress) {
            $this->getState()->getDisplayProgress($this->uploadedBytes);
        }
    }

    abstract protected function extractETag(ResultInterface $result);

    protected function getCompleteParams()
    {
        $config = $this->getConfig();
        $params = isset($config['params']) ? $config['params'] : [];

        $params['MultipartUpload'] = [
            'Parts' => $this->getState()->getUploadedParts()
        ];

        return $params;
    }

    protected function determinePartSize()
    {
        // Make sure the part size is set.
        $partSize = $this->getConfig()['part_size'] ?: MultipartUploader::PART_MIN_SIZE;

        // Adjust the part size to be larger for known, x-large uploads.
        if ($sourceSize = $this->getSourceSize()) {
            $partSize = (int) max(
                $partSize,
                ceil($sourceSize / MultipartUploader::PART_MAX_NUM)
            );
        }

        // Ensure that the part size follows the rules: 5 MB <= size <= 5 GB.
        if ($partSize < MultipartUploader::PART_MIN_SIZE || $partSize > MultipartUploader::PART_MAX_SIZE) {
            throw new \InvalidArgumentException('The part size must be no less '
                . 'than 5 MB and no greater than 5 GB.');
        }

        return $partSize;
    }

    protected function getInitiateParams()
    {
        $config = $this->getConfig();
        $params = isset($config['params']) ? $config['params'] : [];

        if (isset($config['acl'])) {
            $params['ACL'] = $config['acl'];
        }

        // Set the ContentType if not already present
        if (empty($params['ContentType']) && $type = $this->getSourceMimeType()) {
            $params['ContentType'] = $type;
        }

        return $params;
    }

    /**
     * @return UploadState
     */
    abstract protected function getState();

    /**
     * @return array
     */
    abstract protected function getConfig();

    /**
     * @return int
     */
    abstract protected function getSourceSize();

    /**
     * @return string|null
     */
    abstract protected function getSourceMimeType();

    /**
     * Parses an S3 Tagging query-string (`k=v&k2=v2`) into a TagSet array
     * (`[['Key' => k, 'Value' => v], ...]`).
     *
     * Shared between MultipartUpload (where callers may pass a Tagging string
     * via params) and MultipartCopy's tags_directive=REPLACE path.
     *
     * @param string $tagging
     * @return array
     */
    protected static function parseTaggingQueryString(string $tagging): array
    {
        $tagSet = [];
        foreach (explode('&', $tagging) as $pair) {
            if ($pair === '') {
                continue;
            }
            $parts = explode('=', $pair, 2);
            $tagSet[] = [
                'Key'   => urldecode($parts[0]),
                'Value' => urldecode($parts[1] ?? ''),
            ];
        }
        return $tagSet;
    }
}
