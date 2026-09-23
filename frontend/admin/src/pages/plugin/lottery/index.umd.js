import App from './App.vue';
import routes from './routes.js';

(function () {
  window.__ext_lottery = {
    App,
    routes,
    name: 'lottery',
  };
})();
