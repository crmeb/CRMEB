// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed: CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import { Boot } from '@wangeditor/editor';
import AiMenu from './ai';
import VideoMenu from './editor';
import HtmlMenu from './html';
import ImageMenu from './imageMenu';

const menuConfigs = [
  { key: 'alertHtml', factory: () => new HtmlMenu() },
  { key: 'alertMenuKey', factory: () => new VideoMenu() },
  { key: 'uploadImgMenuKey', factory: () => new ImageMenu() },
  { key: 'aiMenuKey', factory: () => new AiMenu() },
];

menuConfigs.forEach((menuConfig) => {
  try {
    Boot.registerMenu(menuConfig);
  } catch (error) {
    if (!(error instanceof Error) || !error.message.includes(`Duplicated key '${menuConfig.key}'`)) throw error;
  }
});
