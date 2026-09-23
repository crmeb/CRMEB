<?php
namespace Aws\Api\Parser;

use Aws\Api\Operation;
use Aws\Api\Parser\Exception\ParserException;
use Aws\Result;
use Aws\CommandInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Base implementation for Smithy RPC V2 protocol parsers.
 *
 * Implementers MUST define the following static property representing
 * the `Smithy-Protocol` header value:
 *    self::HEADER_SMITHY_PROTOCOL => static::$smithyProtocol
 *
 * @internal
 */
abstract class AbstractRpcV2Parser extends AbstractParser
{
    private const HEADER_SMITHY_PROTOCOL = 'Smithy-Protocol';

    /** @var string  */
    protected static string $smithyProtocol;

    public function __invoke(
        CommandInterface $command,
        ResponseInterface $response
    ) {
        $operation = $this->api->getOperation($command->getName());

        return $this->parseResponse($response, $operation);
    }

    /**
     * Parses a response according to Smithy RPC V2 protocol standards.
     *
     * @param ResponseInterface $response the response to parse.
     * @param Operation $operation the operation which holds information for
     *        parsing the response.
     *
     * @return Result
     */
    private function parseResponse(
        ResponseInterface $response,
        Operation $operation
    ): Result
    {
        $smithyProtocolHeader = $response->getHeaderLine(self::HEADER_SMITHY_PROTOCOL);
        if ($smithyProtocolHeader !== static::$smithyProtocol) {
            $statusCode = $response->getStatusCode();
            throw new ParserException(
                "Malformed response: Smithy-Protocol header mismatch (HTTP {$statusCode}). "
                . 'Expected ' . static::$smithyProtocol
            );
        }

        if ($operation['output'] === null) {
            return new Result([]);
        }

        $outputShape = $operation->getOutput();
        foreach ($outputShape->getMembers() as $memberName => $memberProps) {
            if (!empty($memberProps['eventstream'])) {
                return new Result([
                    $memberName => new EventParsingIterator(
                        $response->getBody(),
                        $outputShape->getMember($memberName),
                        $this
                    )
                ]);
            }
        }

        $result = $this->parseMemberFromStream(
            $response->getBody(),
            $outputShape,
            $response
        );

        return new Result(is_null($result) ? [] : $result);
    }
}
