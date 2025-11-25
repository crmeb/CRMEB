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
  const typeArry = ['.jpg', '.png', '.jpeg', '.JPG', '.PNG', '.JPEG', '.gif', '.GIF', '.webp', '.WEBP'];
  const type = file.name.substring(file.name.lastIndexOf('.'));
  const isImage = typeArry.indexOf(type) > -1;
  if (!isImage) {
    Message.error('上传图片格式不对');
  }
  return isImage;
}

export function isVoiceUpload(file) {
  const typeArry = ['.mp3', '.MP3'];
  const type = file.name.substring(file.name.lastIndexOf('.'));
  const isImage = typeArry.indexOf(type) > -1;
  if (!isImage) {
    Message.error('上传音频格式不对');
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
export function isXlsUpload(file) {
  const typeArry = ['.xls', '.xlsx'];
  const type = file.name.substring(file.name.lastIndexOf('.'));
  const isFile = typeArry.indexOf(type) > -1;
  if (!isFile) {
    Message.error('上传文件格式不对');
  }
  return isFile;
}

export function arraysEqual(arr1, arr2) {
  // 如果两个数组的长度不同，直接返回 false
  if (arr1.length !== arr2.length) {
    return false;
  }

  // 将两个数组分别排序
  const sortedArr1 = arr1.slice().sort();
  const sortedArr2 = arr2.slice().sort();

  // 比较排序后的数组
  for (let i = 0; i < sortedArr1.length; i++) {
    if (sortedArr1[i] !== sortedArr2[i]) {
      return false;
    }
  }

  return true;
}
