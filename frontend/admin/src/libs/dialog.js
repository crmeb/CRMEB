// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
// 原实现基于 vue-ydui 的 dialog；Vue3 下用 Element Plus 重写并保持原 API 形态
// （项目中实际只用到了 $dialog.error，其余方法保持等价实现）
import { ElMessage, ElMessageBox, ElLoading, ElNotification } from 'element-plus';

const dialog = {
  confirm: ElMessageBox.confirm,
  alert: ElMessageBox.alert,
  prompt: ElMessageBox.prompt,
  notify: (options) => ElNotification(options),
  loading: {
    open(options) {
      dialog.__loadingInstance = ElLoading.service(
        Object.assign({ lock: true, text: '加载中...', background: 'rgba(0, 0, 0, 0.7)' }, options),
      );
    },
    close() {
      dialog.__loadingInstance && dialog.__loadingInstance.close();
      dialog.__loadingInstance = null;
    },
  },
};

const icons = { error: '操作失败', success: '操作成功' };
Object.keys(icons).forEach((key) => {
  dialog[key] = (mes, obj = {}) => {
    ElMessage({
      message: mes || icons[key],
      type: key,
      duration: 1000,
      ...obj,
    });
    // 与原 toast 行为保持一致：约 1s 后 resolve，便于调用方 await 后刷新列表
    return new Promise(function (resolve) {
      setTimeout(() => {
        resolve();
      }, (obj.timeout || 1) * 1000);
    });
  };
});

dialog.message = (mes = '操作失败', obj = {}) => {
  ElMessage({
    message: mes,
    duration: 1000,
    ...obj,
  });
  return new Promise(function (resolve) {
    setTimeout(() => {
      resolve();
    }, (obj.timeout || 1) * 1000);
  });
};

dialog.validateError = (...args) => {
  validatorDefaultCatch(...args);
};

export function validatorDefaultCatch(err, type = 'message') {
  return dialog[type](err.errors[0].message);
}

export default dialog;
