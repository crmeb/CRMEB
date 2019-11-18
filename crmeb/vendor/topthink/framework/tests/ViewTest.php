<?php

namespace think\tests;

use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use think\App;
use think\Config;
use think\Container;
use think\contract\TemplateHandlerInterface;
use think\View;
use Mockery as m;

class ViewTest extends TestCase
{
    /** @var App|MockInterface */
    protected $app;

    /** @var View|MockInterface */
    protected $view;

    /** @var Config|MockInterface */
    protected $config;

    protected function tearDown(): void
    {
        m::close();
    }

    protected function setUp()
    {
        $this->app = m::mock(App::class)->makePartial();
        Container::setInstance($this->app);

        $this->app->shouldReceive('make')->with(App::class)->andReturn($this->app);
        $this->config = m::mock(Config::class)->makePartial();
        $this->app->shouldReceive('get')->with('config')->andReturn($this->config);

        $this->view = new View($this->app);
    }

    public function testAssignData()
    {
        $this->view->assign('foo', 'bar');
        $this->view->assign(['baz' => 'boom']);
        $this->view->qux = "corge";

        $this->assertEquals('bar', $this->view->foo);
        $this->assertEquals('boom', $this->view->baz);
        $this->assertEquals('corge', $this->view->qux);
        $this->assertTrue(isset($this->view->qux));
    }

    public function testRender()
    {
        $this->config->shouldReceive("get")->with("view.type", 'php')->andReturn(TestTemplate::class);

        $this->view->filter(function ($content) {
            return $content;
        });

        $this->assertEquals("fetch", $this->view->fetch('foo'));
        $this->assertEquals("display", $this->view->display('foo'));
    }

}

class TestTemplate implements TemplateHandlerInterface
{

    /**
     * 检测是否存在模板文件
     * @access public
     * @param string $template 模板文件或者模板规则
     * @return bool
     */
    public function exists(string $template): bool
    {
        return true;
    }

    /**
     * 渲染模板文件
     * @access public
     * @param string $template 模板文件
     * @param array  $data     模板变量
     * @return void
     */
    public function fetch(string $template, array $data = []): void
    {
        echo "fetch";
    }

    /**
     * 渲染模板内容
     * @access public
     * @param string $content 模板内容
     * @param array  $data    模板变量
     * @return void
     */
    public function display(string $content, array $data = []): void
    {
        echo "display";
    }

    /**
     * 配置模板引擎
     * @access private
     * @param array $config 参数
     * @return void
     */
    public function config(array $config): void
    {
        // TODO: Implement config() method.
    }

    /**
     * 获取模板引擎配置
     * @access public
     * @param string $name 参数名
     * @return void
     */
    public function getConfig(string $name)
    {
        // TODO: Implement getConfig() method.
    }
}
