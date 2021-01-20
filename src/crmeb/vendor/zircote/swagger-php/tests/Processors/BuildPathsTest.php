<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Get;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\PathItem;
use OpenApi\Annotations\Post;
use OpenApi\Processors\BuildPaths;
use OpenApi\Processors\MergeIntoOpenApi;
use OpenApi\Tests\OpenApiTestCase;
use const OpenApi\UNDEFINED;

class BuildPathsTest extends OpenApiTestCase
{
    public function testMergePathsWithSamePath()
    {
        $openapi = new OpenApi([]);
        $openapi->paths = [
            new PathItem(['path' => '/comments']),
            new PathItem(['path' => '/comments']),
        ];
        $analysis = new Analysis([$openapi]);
        $analysis->openapi = $openapi;
        $analysis->process(new BuildPaths());
        $this->assertCount(1, $openapi->paths);
        $this->assertSame('/comments', $openapi->paths[0]->path);
    }

    public function testMergeOperationsWithSamePath()
    {
        $openapi = new OpenApi([]);
        $analysis = new Analysis(
            [
            $openapi,
            new Get(['path' => '/comments']),
            new Post(['path' => '/comments']),
            ]
        );
        $analysis->process(new MergeIntoOpenApi());
        $analysis->process(new BuildPaths());
        $this->assertCount(1, $openapi->paths);
        $path = $openapi->paths[0];
        $this->assertSame('/comments', $path->path);
        $this->assertInstanceOf(PathItem::class, $path);
        $this->assertInstanceOf(Get::class, $path->get);
        $this->assertInstanceOf(Post::class, $path->post);
        $this->assertSame(UNDEFINED, $path->put);
    }
}
