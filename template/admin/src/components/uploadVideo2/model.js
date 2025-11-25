import videoSelectModal from './videoModal.vue';

const videoModal = {};

videoModal.install = function (Vue) {
  const ModalConstructor = Vue.extend(videoSelectModal);
  const instance = new ModalConstructor();
  instance.$mount(document.createElement('div'));
  document.body.appendChild(instance.$el);
  Vue.prototype.$videoModal = function (callback, isMore, modelName, boolean) {
    instance.visible = true;
    instance.callback = callback;
    instance.more = isMore;
    instance.modelName = modelName;
    instance.booleanVal = boolean;
  };
};

export default videoModal;
