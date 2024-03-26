// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import { Message } from 'element-ui';
export function importAll(r) {
  let __modules = {};
  r.keys().forEach((key) => {
    let m = r(key).default;
    let n = m.name;
    __modules[n] = m;
  });
  return __modules;
}

export function isPicUpload(file) {
  const typeArry = ['.jpg', '.png', '.JPG', '.PNG', '.gif', '.GIF'];
  const type = file.name.substring(file.name.lastIndexOf('.'));
  const isImage = typeArry.indexOf(type) > -1;
  if (!isImage) {
    Message.error('上传图片格式不对');
  }
  return isImage;
}

export function isVideoUpload(file) {
  const typeArry = ['.mp4', '.MP4'];
  const type = file.name.substring(file.name.lastIndexOf('.'));
  const isImage = typeArry.indexOf(type) > -1;
  if (!isImage) {
    Message.error('上传文件必须为mp4格式视频');
  }
  return isImage;
}

export function isFileUpload(file) {
  const typeArry = ['.doc', '.DOC', '.docx', '.xls', '.xlsx'];
  const type = file.name.substring(file.name.lastIndexOf('.'));
  const isFile = typeArry.indexOf(type) > -1;
  if (!isFile) {
    Message.error('上传文件格式不对');
  }
  return isFile;
}
