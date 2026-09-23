export default {
  install(app, options) {
    if (options && options.developmentOff && import.meta.env.DEV) return;
    // app.config.errorHandler = (error, vm, mes) => {
    //   let info = {
    //     type: 'script',
    //     code: 0,
    //     mes: error.message,
    //     url: window.location.href,
    //   };
    //   nextTick(() => {
    //     store.dispatch('addErrorLog', info);
    //   });
    // };
  },
};
