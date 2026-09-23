<?php
namespace Qiniu\Tests;

use PHPUnit\Framework\TestCase;

use Qiniu\Etag;

class EtagTest extends TestCase
{
    public function test0M()
    {
        $file = qiniuTempFile(0, false);
        list($r, $error) = Etag::sum($file);
        unlink($file);
        $this->assertEquals('Fto5o-5ea0sNMlW_75VgGJCv2AcJ', $r);
        $this->assertNull($error);
    }

    public function testLess4M()
    {
        $file = qiniuTempFile(3 * 1024 * 1024, false);
        list($r, $error) = Etag::sum($file);
        unlink($file);
        $this->assertEquals('Fs5BpnAjRykYTg6o5E09cjuXrDkG', $r);
        $this->assertNull($error);
    }

    public function test4M()
    {
        $file = qiniuTempFile(4 * 1024 * 1024, false);
        list($r, $error) = Etag::sum($file);
        unlink($file);
        $this->assertEquals('FiuKULnybewpEnrfTmxjsxc-3dWp', $r);
        $this->assertNull($error);
    }

    public function testMore4M()
    {
        $file = qiniuTempFile(5 * 1024 * 1024, false);
        list($r, $error) = Etag::sum($file);
        unlink($file);
        $this->assertEquals('lhvyfIWMYFTq4s4alzlhXoAkqfVL', $r);
        $this->assertNull($error);
    }

    public function test8M()
    {
        $file = qiniuTempFile(8 * 1024 * 1024, false);
        list($r, $error) = Etag::sum($file);
        unlink($file);
        $this->assertEquals('lmRm9ZfGZ86bnMys4wRTWtJj9ClG', $r);
        $this->assertNull($error);
    }
}
