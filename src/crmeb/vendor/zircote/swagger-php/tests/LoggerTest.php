<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests;

use OpenApi\Annotations\Get;
use OpenApi\Annotations\Post;
use OpenApi\Logger;

class LoggerTest extends OpenApiTestCase
{
    public function shortenFixtures()
    {
        return [
            [Get::class, '@OA\Get'],
            [[Get::class, Post::class], ['@OA\Get', '@OA\Post']],
        ];
    }

    /**
     * @dataProvider shortenFixtures
     */
    public function testShorten($classes, $expected)
    {
        $this->assertEquals($expected, Logger::shorten($classes));
    }
}
