import imgSelectModal from './imgModal.vue';

const imgModal = {};

imgModal.install = function (Vue) {
  const ModalConstructor = Vue.extend(imgSelectModal);
  const instance = new ModalConstructor();
  instance.$mount(document.createElement('div'));
  document.body.appendChild(instance.$el);
  Vue.prototype.$imgModal = function (callback, isMore, modelName, boolean) {
    instance.visible = true;
    instance.callback = callback;
    instance.more = isMore;
    instance.modelName = modelName;
    instance.booleanVal = boolean;
  };
};

export default imgModal;
