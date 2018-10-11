<!DOCTYPE html>
<html lang="zh-CN">
<head>
    {include file="public/frame_head" /}
    <title>{block name="title"}{/block}</title>
    {block name="head_top"}{/block}
    {include file="public/style" /}
    {block name="head"}{/block}
</head>
<body class="gray-bg">
<!--演示地址https://daneden.github.io/animate.css/?-->
<div class="wrapper wrapper-content animated ">
{block name="content"}{/block}
{block name="foot"}{/block}
{block name="script"}{/block}
{include file="public/frame_footer" /}
</div>
</body>
</html>
