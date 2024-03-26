import request from '@/libs/request';
import Vue from 'vue';

let fApi;
let unique = 1;
import formCreate from '@form-create/element-ui';

const uniqueId = () => ++unique;
export default function modalForm(formRequestPromise, config = {}) {
  const h = this.$createElement;
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
                console.log(e, 'rep');
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
        data = Vue.observable(data);
        data.rules.forEach((e) => {
          e.title += '：';
        });
        this.$msgbox({
          title: data.title,
          showCancelButton: true,
          customClass: config.class || 'modal-form',
          mask: false,
          closeOnClickModal: false,
          message: h('div', { class: 'common-form-create', key: uniqueId() }, [
            h('formCreate', {
              props: {
                rule: data.rules,
                option: data.config,
              },
              on: {
                mounted: ($f) => {
                  fApi = $f;
                },
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
                      this.$message.success(res.msg || '提交成功');
                      resolve(res);
                    })
                    .catch((err) => {
                      this.$message.error(err.msg || '提交失败');
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
        this.$message.error(e.msg || '--');
      });
  });
}
