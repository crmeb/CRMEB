<?php declare(strict_types=1);

namespace OpenApi\Tests\Fixtures;

/**
 * @OA\Info(title="Fixture for AugmentOperationTest", version="test")
 */
class UsingPhpDoc
{
    /**
     * Example summary
     *
     * Example description...
     * More description...
     *
     * @OA\Get(path="api/test1", @OA\Response(response="200", description="a response"))
     */
    public function methodWithDescription()
    {
    }

    /**
     * Example summary
     *
     * @OA\Get(path="api/test2", @OA\Response(response="200", description="a response"))
     */
    public function methodWithSummary()
    {
    }
}
