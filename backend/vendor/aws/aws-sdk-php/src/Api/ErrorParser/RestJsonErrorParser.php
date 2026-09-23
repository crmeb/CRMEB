<?php
namespace Aws\Api\ErrorParser;

use Aws\Api\Parser\AbstractParser;
use Aws\Api\Parser\JsonParser;
use Aws\Api\Service;
use Aws\Api\StructureShape;
use Aws\CommandInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Parses JSON-REST errors.
 */
class RestJsonErrorParser extends AbstractErrorParser
{
    use JsonParserTrait;

    private $parser;

    public function __construct(?Service $api = null, ?JsonParser $parser = null)
    {
        parent::__construct($api);
        $this->parser = $parser ?: new JsonParser();
    }

    public function __invoke(
        ResponseInterface $response,
        ?CommandInterface $command = null
    ) {
        $response = AbstractParser::getResponseWithCachingStream($response);
        $data = $this->genericHandler($response);

        // Merge in error data from the JSON body
        if ($json = $data['parsed']) {
            $data = array_replace($json, $data);
        }

        // Correct error type from services like Amazon Glacier
        if (!empty($data['type'])) {
            $data['type'] = strtolower($data['type']);
        }

        // Retrieve error message directly
        $data['message'] = $data['parsed']['message']
            ?? $data['parsed']['Message']
            ?? $data['parsed']['error_description']
            ?? null;

        $this->populateShape($data, $response, $command);

        return $data;
    }
}
