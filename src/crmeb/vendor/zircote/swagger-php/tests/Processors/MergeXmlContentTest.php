<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\Response;
use OpenApi\Processors\MergeXmlContent;
use OpenApi\Tests\OpenApiTestCase;
use const OpenApi\UNDEFINED;

class MergeXmlContentTest extends OpenApiTestCase
{
    public function testXmlContent()
    {
        $comment = <<<END
            @OA\Response(response=200,
                @OA\XmlContent(type="array",
                    @OA\Items(ref="#/components/schemas/repository")
                )
            )
END;
        $analysis = new Analysis($this->parseComment($comment));
        $this->assertCount(3, $analysis->annotations);
        $response = $analysis->getAnnotationsOfType(Response::class)[0];
        $this->assertSame(UNDEFINED, $response->content);
        $this->assertCount(1, $response->_unmerged);
        $analysis->process(new MergeXmlContent());
        $this->assertCount(1, $response->content);
        $this->assertCount(0, $response->_unmerged);
        $json = json_decode(json_encode($response), true);
        $this->assertSame('#/components/schemas/repository', $json['content']['application/xml']['schema']['items']['$ref']);
    }

    public function testMultipleMediaTypes()
    {
        $comment = <<<END
            @OA\Response(response=200,
                @OA\MediaType(mediaType="image/png"),
                @OA\XmlContent(type="array",
                    @OA\Items(ref="#/components/schemas/repository")
                )
            )
END;
        $analysis = new Analysis($this->parseComment($comment));
        $response = $analysis->getAnnotationsOfType(Response::class)[0];
        $this->assertCount(1, $response->content);
        $analysis->process(new MergeXmlContent());
        $this->assertCount(2, $response->content);
    }

    public function testParameter()
    {
        $comment = <<<END
            @OA\Parameter(name="filter",in="query", @OA\XmlContent(
                @OA\Property(property="type", type="string"),
                @OA\Property(property="color", type="string")
            ))
END;
        $analysis = new Analysis($this->parseComment($comment));
        $this->assertCount(4, $analysis->annotations);
        $parameter = $analysis->getAnnotationsOfType(Parameter::class)[0];
        $this->assertSame(UNDEFINED, $parameter->content);
        $this->assertCount(1, $parameter->_unmerged);
        $analysis->process(new MergeXmlContent());
        $this->assertCount(1, $parameter->content);
        $this->assertCount(0, $parameter->_unmerged);
        $json = json_decode(json_encode($parameter), true);
        $this->assertSame('query', $json['in']);
        $this->assertSame('application/xml', array_keys($json['content'])[0]);
        $this->assertArrayNotHasKey('mediaType', $json['content']['application/xml']);
    }

    public function testNoParent()
    {
        $this->assertOpenApiLogEntryContains('Unexpected @OA\XmlContent() must be nested');
        $comment = <<<END
            @OA\XmlContent(type="array",
                @OA\Items(ref="#/components/schemas/repository")
            )
END;
        $analysis = new Analysis($this->parseComment($comment));
        $analysis->process(new MergeXmlContent());
    }

    public function testInvalidParent()
    {
        $this->assertOpenApiLogEntryContains('Unexpected @OA\XmlContent() in @OA\Property() in');
        $comment = <<<END
            @OA\Property(
                @OA\XmlContent(type="array",
                    @OA\Items(ref="#/components/schemas/repository")
                )
            )
END;
        $analysis = new Analysis($this->parseComment($comment));
        $analysis->process(new MergeXmlContent());
    }
}
