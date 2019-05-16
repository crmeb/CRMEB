<?php
/**
 * Created by CRMEB.
 * User: 136327134@qq.com
 * Date: 2019/4/9 17:43
 */

namespace app\core\implement;


/*
 * 模板消息接口类
 *
 * */

interface TemplateInterface
{
    public static function sendTemplate($openId,$tempCode,$dataKey,$formId=null,$link=null,$defaultColor=null);

    public static function getConstants($key=null);
}