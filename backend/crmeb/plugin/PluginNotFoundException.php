<?php

namespace crmeb\plugin;

/**
 * 插件未找到异常
 *
 * 当通过 key 查询插件但 Registry 中不存在时抛出。
 */
class PluginNotFoundException extends \RuntimeException
{
}
