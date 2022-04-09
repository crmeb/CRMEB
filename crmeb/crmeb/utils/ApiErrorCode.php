<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\utils;

/**
 * 错误码统一存放类
 * Class ApiErrorCode
 * @package crmeb\services
 */
class ApiErrorCode
{

    const SUCCESS = [200, 'SUCCESS'];
    const ERROR = [400, 'ERROR'];

    const ERR_LOGIN_INVALID = [410000, 'Landing overdue'];
    const ERR_AUTH = [400011, 'You do not have permission to access for the time being'];
    const ERR_RULE = [400012, 'Interface is not authorized, you cannot access'];
    const ERR_ADMINID_VOID = [400013, 'Failed to get administrator ID'];
    //保存token失败
    const ERR_SAVE_TOKEN = [400, 'Failed to save token'];
    //登陆状态不正确
    const ERR_LOGIN_STATUS = [410002, 'The login status is incorrect. Please login again.'];
    //请登陆
    const ERR_LOGIN = [410000, 'Please login'];

    //公众号访问错误请求码
    const ERROR_WECHAT_MESSAGE = [
        45008 => 'graphic message limit exceeded',
        45007 => 'voice playback time exceeds the limit',
        45006 => 'image link field exceeds limit',
        45005 => 'link field exceeds limit',
        45004 => 'description field exceeds limit',
        45003 => 'header field exceeds limit',
        45002 => 'message content exceeds limit',
        45001 => 'multimedia file size exceeds limit',
        44004 => 'text message content is empty',
        44003 => 'text message content is empty',
        44002 => 'multimedia file is empty The packet of 44002 post is empty',
        44001 => 'the multimedia file is empty',
        43019 => 'the recipient needs to be removed from the blacklist',
        43005 => 'need a friend relationship',
        43004 => 'needs attention of receiver',
        43003 => 'requires HTTPS request',
        43002 => 'post request required',
        43001 => 'get request required',
        42007 => 'user changes wechat password, accesstoken and refreshtoken are invalid and need to be re authorized',
        42003 => 'oauth_ Code timeout',
        42002 => 'refresh_ Token timeout',
        42001 => 'access_ Token timeout, please check access_ For the validity of token, please refer to basic support - access_ In token, for access_ Detailed description of token mechanism',
        41009 => 'missing openid',
        41008 => 'missing OAuth code',
        41007 => 'missing submenu data',
        41006 => 'lack of media_ ID parameter',
        41005 => 'missing multimedia file data',
        41004 => 'missing secret parameter',
        41003 => 'missing refresh_ Token parameter',
        41002 => 'missing appid parameter',
        41001 => 'missing access_ Token parameter',
        40163 => 'oauth_ Code used',
        40155 => 'do not add any other official account\'s home page links.',
        40137 => 'image format not supported',
        40132 => 'illegal micro signal',
        40125 => 'invalid appsecret',
        40121 => 'illegal media_ ID type',
        40120 => 'sub button type error',
        40119 => 'button type error',
        40118 => 'media_ Illegal ID size',
        40117 => 'illegal group name',
        40060 => 'when deleting a single text, the specified article_ Illegal idx',
        40051 => 'illegal group name',
        40050 => 'illegal group ID',
        40048 => 'invalid URL',
        40039 => 'illegal URL length',
        40038 => 'illegal request format',
        40035 => 'illegal parameter',
        40033 => "illegal request character, can't contain character in the format of 'uxxxx'",
        40032 => 'illegal openid list length',
        40031 => 'Illegal openid list',
        40030 => 'Illegal refresh_ token',
        40029 => 'invalid OAuth_ code',
        40028 => 'Illegal custom menu user',
        40027 => 'Illegal submenu button URL length',
        40026 => 'Illegal submenu button key length',
        40025 => 'Illegal submenu button name length',
        40024 => 'Illegal submenu button type',
        40023 => 'Illegal number of submenu buttons',
        40022 => 'Illegal submenu series',
        40021 => 'Illegal menu version number',
        40020 => 'Illegal button URL length',
        40019 => 'Illegal key length of button',
        40018 => 'Illegal button name length',
        40017 => 'Illegal button type',
        40016 => 'Number of illegal buttons',
        40015 => 'Illegal menu type',
        40014 => 'Illegal access_ Token, please compare access carefully_ The validity of token (if expired), or whether to call the interface for the appropriate official account.',
        40013 => 'Illegal appid, please check the correctness of appid, avoid abnormal characters and pay attention to case',
        40012 => 'Illegal thumbnail file size',
        40011 => 'Illegal video file size',
        40010 => 'Illegal voice file size',
        40009 => 'Illegal image file size',
        40008 => 'Illegal message type',
        40007 => 'Illegal media file ID',
        40006 => 'Illegal file size',
        40005 => 'Illegal file type',
        40004 => 'Illegal media file type',
        40002 => 'Illegal voucher type',
        40003 => 'Illegal OpenID, developers confirm whether OpenID has been concerned about the official account number, or whether it is OpenID of other official account.',
        40001 => 'Get access_ Appsecret error in token, or access_ Token is not valid. Developers are asked to compare the correctness of AppSecret with the official account, or to see if the interface is being invoked for the appropriate public number.'
    ];


}
