// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

import {
  Confirm as confirm,
  Alert as alert,
  Toast as toast,
  Notify as notify,
  Loading as loading
} from "vue-ydui/dist/lib.rem/dialog";

const dialog = {
  confirm,
  alert,
  toast,
  notify,
  loading
};

const icons = { error: "操作失败", success: "操作成功" };
Object.keys(icons).reduce((dialog, key) => {
  dialog[key] = (mes, obj = {}) => {
    return new Promise(function(resolve) {
      toast({
        mes: mes || icons[key],
        timeout: 1000,
        icon: key,
        callback: () => {
          resolve();
        },
        ...obj
      });
    });
  };
  return dialog;
}, dialog);

dialog.message = (mes = "操作失败", obj = {}) => {
  return new Promise(function(resolve) {
    toast({
      mes,
      timeout: 1000,
      callback: () => {
        resolve();
      },
      ...obj
    });
  });
};

dialog.validateError = (...args) => {
  validatorDefaultCatch(...args);
};

export function validatorDefaultCatch(err, type = "message") {
  return dialog[type](err.errors[0].message);
}

export default dialog;
