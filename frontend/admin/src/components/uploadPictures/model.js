import { createVNode, render } from 'vue';
import imgSelectModal from './imgModal.vue';

const imgModal = {};

// 命令式单例弹窗（createVNode + render，挂全量 appContext 保证弹窗内可用全局组件/指令/store）
imgModal.install = function (app) {
  const container = document.createElement('div');
  document.body.appendChild(container);
  const vnode = createVNode(imgSelectModal);
  vnode.appContext = app._context;
  render(vnode, container);
  const instance = vnode.component.exposed;
  app.config.globalProperties.$imgModal = function (callback, isMore, modelName, boolean) {
    instance.open({ callback, isMore, modelName, boolean });
  };
};

export default imgModal;
