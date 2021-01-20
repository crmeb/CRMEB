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
use crmeb\utils\ApiErrorCode;

return [
    'SUCCESS' => '操作成功',
    'Successful operation' => '操作成功',
    'ERROR' => '操作失败',
    'Landing overdue' => '登陆过期',
    'Modified success' => '修改成功',
    'Modification failed' => '修改失败',
    'You do not have permission to access for the time being' => '您暂无权限访问',
    'Interface is not authorized, you cannot access' => '接口未授权，您无法访问',
    'Failed to get administrator ID' => '管理员id获取失败',
    'Token is invalid, please login' => 'token失效,请登录',
    'Please login' => '请登录',
    'The login status is incorrect. Please login again.' => '登录状态有误,请重新登录',

    //订单错误提示语言
    'Data does not exist!' => '数据不存在',
    'Write off successfully' => '核销成功',
    'Write off failure' => '核销失败',
    'Order written off' => '订单已核销',
    'Write off order does not exist' => '核销订单不存在',
    'Lack of write-off code' => '缺少核销码',
    'Missing order ID' => '缺少订单id',
    'Please enter the total price' => '请输入商品总价',
    'Please enter the actual payment amount' => '请输入实际支付金额',
    'Failed to write off the group order' => '拼团订单暂未成功无法核销',
    'Parent classification error' => '父级分类错误',
    'There are attachments under the category. Please delete them first' => '分类下面有附件,请先删除附件',

    //上传配置错误提示语言
    'Please configure accessKey and secretKey' => '请设置上传配置的accessKey和secretKey',
    'Upload failure' => '上传失败',
    'Upload file does not exist' => '上传文件不存在',
    'COS bucket cannot be null' => '腾讯云bucket不能为空',
    'COS allowPrefix cannot be null' => '腾讯云允许前缀不能为空',
    'durationSeconds must be a int type' => 'durationSeconds必须是int类型',
    'get cam failed' => '获取cam失败',
    'Failed to generate upload directory, please check the permission!' => '生成上传目录失败，请检查权限！',

    //易联云
    'request was aborted' => '请求已中止',
    'Accesstoken has expired' => '访问窗口已过期',

    //云信短信
    'Mobile number cannot be empty' => '手机号码不能为空',
    'Account does not exist' => '帐户不存在',
    'Access token does not exist' => '访问令牌不存在',
    'Missing template number' => '缺少模板号',

    //订阅消息&模板消息
    'Template number does not exist' => '模板号不存在',
    'Template ID does not exist' => '模板ID不存在',
    'Openid does not exist' => 'Openid不存在',

    'Upload filesize error' => '上传文件大小超出系统设置,请从新选择',
    'Upload fileExt error' => '上传文件后缀不允许,请从新选择',
    'Upload fileMime error' => '上传文件类型不允许,请从新选择',

    ApiErrorCode::ERR_SAVE_TOKEN[1] => '保存token失败'
];
