// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from '@/libs/request';

export function ajCaptcha(params) {
  return request({
    url: 'ajcaptcha',
    method: 'get',
    params: params,
  });
}

export function ajCaptchaCheck(data) {
  return request({
    url: 'ajcheck',
    method: 'post',
    data: data,
  });
}

/**
 * @description 表格--删除
 * @param {Number} param id {Number} 配置id
 */
export function tableDelApi(data) {
  return request({
    url: data.url,
    method: data.method,
    data: data.ids,
    kefu: data.kefu || '',
  });
}

/**
 * 获取消息提醒
 */
export function jnoticeRequest() {
  return request({
    url: 'jnotice',
    method: 'GET',
  });
}

/**
 * 获取logo
 */
export function getLogo() {
  return request({
    url: 'logo',
    method: 'GET',
  });
}
