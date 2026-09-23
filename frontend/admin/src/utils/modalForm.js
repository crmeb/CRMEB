// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
// 后端 form-create JSON schema 动态表单弹窗（Element Plus + form-create 3.x）
import { createVNode, reactive } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import formCreate from '@form-create/element-ui';
import request from '@/libs/request';
import { normalizeFormCreateRule } from '@/utils/formCreateIcon';

let fApi;
let unique = 1;

const uniqueId = () => ++unique;

export default function modalForm(formRequestPromise, config = {}) {
  return new Promise((resolve, reject) => {
    formRequestPromise
      .then(({ data }) => {
        if (!data.config) data.config = {};
        data.config.submitBtn = false;
        data.config.resetBtn = false;
        if (!data.config.form) data.config.form = {};
        if (!data.config.formData) data.config.formData = {};
        data.config.formData = { ...data.config.formData, ...config.formData };
        data.config.form.labelWidth = '105px';
        data.config.global = {
          upload: {
            props: {
              onSuccess(rep, file) {
                if (rep.status === 200) {
                  file.url = rep.data.src;
                }
              },
            },
          },
          frame: {
            props: {
              onLoad(e) {
                e.fApi = fApi;
              },
            },
          },
          inputNumber: {
            props: {
              controls: false,
            },
          },
        };
        data.rules = data.rules.map(normalizeFormCreateRule);
        data = reactive(data);
        data.rules.forEach((e) => {
          e.title += '：';
        });
        const FormCreateComp = formCreate.$form ? formCreate.$form() : formCreate;
        ElMessageBox({
          title: data.title,
          showCancelButton: true,
          customClass: config.class || 'modal-form',
          // modal:false 会禁用蒙层（历史遗留），后台表单弹窗需要蒙层遮挡与点击遮罩语义
          modal: true,
          closeOnClickModal: false,
          message: createVNode('div', { class: 'common-form-create', key: uniqueId() }, [
            createVNode(FormCreateComp, {
              rule: data.rules,
              option: data.config,
              onMounted: ($f) => {
                fApi = $f;
                if (typeof config.onMounted === 'function') {
                  config.onMounted($f);
                }
              },
              'onUpdate:api': ($f) => {
                fApi = $f;
              },
            }),
          ]),
          beforeClose: (action, instance, done) => {
            const fn = () => {
              setTimeout(() => {
                instance.confirmButtonLoading = false;
              }, 500);
            };

            if (action === 'confirm') {
              instance.confirmButtonLoading = true;
              fApi.submit(
                (formData) => {
                  request[data.method.toLowerCase()](data.action, formData)
                    .then((res) => {
                      done();
                      ElMessage.success(res.msg || '提交成功');
                      resolve(res);
                    })
                    .catch((err) => {
                      ElMessage.error(err.msg || '提交失败');
                      // reject(err);
                    })
                    .finally(() => {
                      fn();
                    });
                },
                () => fn(),
              );
            } else {
              fn();
              done();
            }
          },
        });
      })
      .catch((e) => {
        ElMessage.error(e.msg || '--');
      });
  });
}
