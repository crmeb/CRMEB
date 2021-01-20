<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests\Annotations;

use OpenApi\Annotations as OA;
use OpenApi\Tests\OpenApiTestCase;

class OperationTest extends OpenApiTestCase
{
    public function securityData()
    {
        return [
            'empty' => [
                [],
                '/** @OA\Get(security={ }) */',
                '{"security":[]}',
            ],
            'basic' => [
                [['api_key' => []]],
                '/** @OA\Get(security={ {"api_key":{}} }) */',
                '{"security":[{"api_key":[]}]}',
            ],
            'optional' => [
                [[]],
                '/** @OA\Get(security={ {} }) */',
                '{"security":[{}]}',
            ],
            'optional-oauth2' => [
                [[], ['petstore_auth' => ['write:pets', 'read:pets']]],
                '/** @OA\Get(security={ {}, {"petstore_auth":{"write:pets","read:pets"}} }) */',
                '{"security":[{},{"petstore_auth":["write:pets","read:pets"]}]}',
            ],
        ];
    }

    /**
     * @dataProvider securityData
     */
    public function testSecuritySerialization($security, $dockBlock, $expected)
    {
        // test with Get implementation...
        $operation = new OA\Get([
            'security' => $security,
        ]);
        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $json = $operation->toJson($flags);
        $this->assertEquals($expected, $json);

        $analysis = $this->analysisFromDockBlock($dockBlock);
        $this->assertCount(1, $analysis);
        $json = $analysis[0]->toJson($flags);
        $this->assertEquals($expected, $json);
    }
}
