<?php

namespace think;

use PHPUnit\Framework\TestCase;
use tag\Demo;

class TemplateTest extends TestCase
{
    public function getTemplate()
    {
        $config = [
            'view_path'          => __DIR__ . '/../template' . DIRECTORY_SEPARATOR,
            'cache_path'         => __DIR__ . '/../cache' . DIRECTORY_SEPARATOR,
            'tpl_cache'          => false,
            'tpl_replace_string' => ['__STATIC__' => '/static'],
            'taglib_pre_load'    => Demo::class,
        ];
        return new Template($config);
    }

    // 直接渲染
    public function testDisplay()
    {
        $this->expectOutputString('hello-thinkphp');

        $template = $this->getTemplate();
        $content = '{$name}-{$email}';
        $template->display($content, ['name' => 'hello', 'email' => 'thinkphp']);
    }

    // 渲染文件
    public function testFetch()
    {
        $this->expectOutputString('success');

        $template = $this->getTemplate();
        $template->fetch('fetch');
    }

    // 布局
    public function testLayout()
    {
        $this->expectOutputString('startsuccessend');

        $template = $this->getTemplate();
        $template->layout('layout');
        $template->fetch('fetch');
        $template->layout(false);
    }

    // 扩展解析
    public function testExtend()
    {
        $this->expectOutputString('test.name');

        $template = $this->getTemplate();
        $template->extend('$Cms', function (array $vars) {
            return '\'' . implode('.', $vars) . '\'';
        });

        $content = '{$Cms.test.name}';
        $template->display($content);
    }

    // 变量
    public function testParseVar()
    {
        $this->expectOutputString('e10adc3949ba59abbe56e057f20f883e');

        $template = $this->getTemplate();
        $content = '{:md5("123456")}';
        $template->display($content, ['password' => '123456']);
    }

    // 变量使用函数
    public function testParseVarFunction()
    {
        $this->expectOutputString('e10adc3949ba59abbe56e057f20f883e-123456-666456');

        $template = $this->getTemplate();
        $content = '{$password|md5}-{$password|raw}-{$password|str_replace=123,666,###}';
        $template->display($content, ['password' => '123456']);
    }

    // 默认值
    public function testParseDefaultFunction()
    {
        $this->expectOutputString('test');

        $template = $this->getTemplate();
        $content = '{$default|default="test"}';
        $template->display($content);
    }

    // 系统变量
    public function testParseThinkVar()
    {
        $this->expectOutputString($_SERVER['PHP_SELF'] . '-' . PHP_VERSION . '-' . PHP_VERSION);

        $template = $this->getTemplate();
        $content = '{$Request.server.PHP_SELF}-{$Think.const.PHP_VERSION}-{$Think.PHP_VERSION}';
        $template->display($content);
    }

    // 数组
    public function testParseArrayVar()
    {
        $this->expectOutputString('thinkphp<br/>thinkphp');

        $template = $this->getTemplate();
        $content = '{$data.name}<br/>{$data["name"]}';
        $template->display($content, ['data' => ['name' => 'thinkphp']]);
    }

    // 对象
    public function testParseObjectVar()
    {
        $this->expectOutputString('a-b-c-d');

        $object = new class {
            public string $a = 'a';
            public const b = 'b';

            public function c($str) {
                return $str;
            }

            static public function d($str) {
                return $str;
            }
        };

        $template = $this->getTemplate();
        $content = '{$data->a}-{$data::b}-{$data->c("c")}-{$data::d("d")}';
        $template->display($content, ['data' => $object]);
    }

    // 运算符
    public function testParseVarOperator()
    {
        $this->expectOutputString('2-0-2-0.5-1-1-1-4');

        $template = $this->getTemplate();
        $content = '{$a+1}-{$a-1}-{$a*$b}-{$a/$b}-{$a%$b}-{$a++}-{--$b}-{$a+$b+abs(-1)}';
        $template->display($content, ['a' => 1, 'b' => 2]);
    }

    // 三元运算符
    public function testParseTernaryOperator()
    {
        $this->expectOutputString('真-默认值-有值-NO');

        $template = $this->getTemplate();
        $content = '{$true?"真":"假"}-{$null ?? "默认值"}-{$one ?= "有值"}-{$zero ?: "NO"}';
        $template->display($content, ['null' => null, 'zero' => 0, 'true' => true, 'one' => 1]);
    }

    // 单行注释
    public function testParseSimpleNote()
    {
        $this->expectOutputString('123');

        $template = $this->getTemplate();
        $content = '123{// 注释内容 }';
        $template->display($content);
    }

    // 多行注释
    public function testParseMoreNote()
    {
        $this->expectOutputString('123');

        $template = $this->getTemplate();
        $content = "123{/* 这是模板\r\n注释内容*/ }";
        $template->display($content);
    }

    // 引用标签
    public function testParseInclude()
    {
        $this->expectOutputString('include');

        $template = $this->getTemplate();
        $content = '{include file="include"}';
        $template->display($content);
    }

    // 继承标签
    public function testParseExtend()
    {
        $this->expectOutputString("title\r\n主内容main\r\n");

        $template = $this->getTemplate();
        $content = "{extend name='extend' /}\r\n{block name='title'}title{/block}\r\n{block name='main'}{__block__}main{/block}";
        $template->display($content);
    }

    // 输出替换
    public function testParseReplaceString()
    {
        $this->expectOutputString("start/staticend");

        $template = $this->getTemplate();
        $content = "start__STATIC__end";
        $template->display($content);
    }

    // 标签扩展
    public function testParseDemoTag()
    {
        $this->expectOutputString(<<<'HTML'
<h1>闭合标签</h1>
2022-12-31 16:00:00<hr>
<h1>开放标签</h1>
    0=>1<br>
    1=>3<br>
    2=>5<br>
    3=>7<br>
    4=>9<br>
<br>
    0=>2<br>
    1=>4<br>
    2=>6<br>
    3=>8<br>
    4=>10<br>

HTML);

        $template = $this->getTemplate();
        $content = <<<'HTML'
<h1>闭合标签</h1>
{demo:close time='$demo_time'/}
<hr>
<h1>开放标签</h1>
{demo:open name='demo_name'}
    {$key}=>{$demo_name}<br>
{/demo:open}
<br>
{demo:open name='demo_name' type='1'}
    {$key}=>{$demo_name}<br>
{/demo:open}
HTML;

        $template->display($content, ['demo_time' => 1672502400]);
    }
}
