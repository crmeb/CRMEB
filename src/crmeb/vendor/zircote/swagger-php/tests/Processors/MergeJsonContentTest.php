<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\Response;
use OpenApi\Processors\MergeJsonContent;
use OpenApi\Tests\OpenApiTestCase;
use const OpenApi\UNDEFINED;

class MergeJsonContentTest extends OpenApiTestCase
{
    public function testJsonContent()
    {
        $comment = <<<END
            @OA\Response(response=200,
                @OA\JsonContent(type="array",
                    @OA\Items(ref="#/components/schemas/repository")
                )
            )
END;
        $analysis = new Analysis($this->parseComment($comment));
        $this->assertCount(3, $analysis->annotations);
        $response = $analysis->getAnnotationsOfType(Response::class)[0];
        $this->assertSame(UNDEFINED, $response->content);
        $this->assertCount(1, $response->_unmerged);
        $analysis->process(new MergeJsonContent());
        $this->assertCount(1, $response->content);
        $this->assertCount(0, $response->_unmerged);
        $json = json_decode(json_encode($response), true);
        $this->assertSame('#/components/schemas/repository', $json['content']['application/json']['schema']['items']['$ref']);
    }

    public function testMultipleMediaTypes()
    {
        $comment = <<<END
            @OA\Response(response=200,
                @OA\MediaType(mediaType="image/png"),
                @OA\JsonContent(type="array",
                    @OA\Items(ref="#/components/schemas/repository")
                )
            )
END;
        $analysis = new Analysis($this->parseComment($comment));
        $response = $analysis->getAnnotationsOfType(Response::class)[0];
        $this->assertCount(1, $response->content);
        $analysis->process(new MergeJsonContent());
        $this->assertCount(2, $response->content);
    }

    public function testParameter()
    {
        $comment = <<<END
            @OA\Parameter(name="filter",in="query", @OA\JsonContent(
                @OA\Property(property="type", type="string"),
                @OA\Property(property="color", type="string")
            ))
END;
        $analysis = new Analysis($this->parseComment($comment));
        $this->assertCount(4, $analysis->annotations);
        $parameter = $analysis->getAnnotationsOfType(Parameter::class)[0];
        $this->assertSame(UNDEFINED, $parameter->content);
        $this->assertCount(1, $parameter->_unmerged);
        $analysis->process(new MergeJsonContent());
        $this->assertCount(1, $parameter->content);
        $this->assertCount(0, $parameter->_unmerged);
        $json = json_decode(json_encode($parameter), true);
        $this->assertSame('query', $json['in']);
        $this->assertSame('application/json', array_keys($json['content'])[0]);
        $this->assertArrayNotHasKey('mediaType', $json['content']['application/json']);
    }

    public function testNoParent()
    {
        $this->assertOpenApiLogEntryContains('Unexpected @OA\JsonContent() must be nested');
        $comment = <<<END
            @OA\JsonContent(type="array",
                @OA\Items(ref="#/components/schemas/repository")
            )
END;
        $analysis = new Analysis($this->parseComment($comment));
        $analysis->process(new MergeJsonContent());
    }

    public function testInvalidParent()
    {
        $this->assertOpenApiLogEntryContains('Unexpected @OA\JsonContent() in @OA\Property() in');
        $comment = <<<END
            @OA\Property(
                @OA\JsonContent(type="array",
                    @OA\Items(ref="#/components/schemas/repository")
                )
            )
END;
        $analysis = new Analysis($this->parseComment($comment));
        $analysis->process(new MergeJsonContent());
    }
}
