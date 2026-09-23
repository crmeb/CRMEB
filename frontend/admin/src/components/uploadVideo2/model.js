import { createVNode, render } from 'vue';
import videoSelectModal from './videoModal.vue';

const videoModal = {};

// 命令式单例弹窗（createVNode + render，挂全量 appContext 保证弹窗内可用全局组件/指令/store）
videoModal.install = function (app) {
  const container = document.createElement('div');
  document.body.appendChild(container);
  const vnode = createVNode(videoSelectModal);
  vnode.appContext = app._context;
  render(vnode, container);
  const instance = vnode.component.exposed;
  app.config.globalProperties.$videoModal = function (callback, isMore, modelName, boolean) {
    instance.open({ callback, isMore, modelName, boolean });
  };
};

export default videoModal;
