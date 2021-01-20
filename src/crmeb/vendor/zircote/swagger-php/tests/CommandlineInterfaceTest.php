<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests;

class CommandlineInterfaceTest extends OpenApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testStdout()
    {
        $path = __DIR__.'/../Examples/swagger-spec/petstore-simple';
        exec(__DIR__.'/../bin/openapi --format yaml '.escapeshellarg($path).' 2> /dev/null', $output, $retval);
        $this->assertSame(0, $retval);
        $yaml = implode("\n", $output);
        $this->assertSpecEquals(file_get_contents($path.'/petstore-simple.yaml'), $yaml);
    }

    public function testOutputTofile()
    {
        $path = __DIR__.'/../Examples/swagger-spec/petstore-simple';
        $filename = sys_get_temp_dir().'/swagger-php-clitest.yaml';
        exec(__DIR__.'/../bin/openapi --format yaml -o '.escapeshellarg($filename).' '.escapeshellarg($path).' 2> /dev/null', $output, $retval);
        $this->assertSame(0, $retval);
        $this->assertCount(0, $output, 'No output to stdout');
        $yaml = file_get_contents($filename);
        unlink($filename);
        $this->assertSpecEquals(file_get_contents($path.'/petstore-simple.yaml'), $yaml);
    }
}
