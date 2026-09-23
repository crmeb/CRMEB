/** AI Agent 扩展 UMD 入口，供 PageContainer 动态加载。 */
import App from './App';
import routes from './routes';

const extKey = '__ext_aiagent';

if (!window[extKey]) {
  window[extKey] = {
    App,
    routes,
  };
}

export default window[extKey];
