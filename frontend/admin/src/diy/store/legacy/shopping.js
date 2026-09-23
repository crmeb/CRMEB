// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import __asset_foo1_01_0 from '@/assets/images/foo1-01.png';
import __asset_foo1_02_1 from '@/assets/images/foo1-02.png';
import __asset_foo2_01_2 from '@/assets/images/foo2-01.png';
import __asset_foo2_02_3 from '@/assets/images/foo2-02.png';
import __asset_foo3_01_4 from '@/assets/images/foo3-01.png';
import __asset_foo3_02_5 from '@/assets/images/foo3-02.png';
import __asset_foo4_01_6 from '@/assets/images/foo4-01.png';
import __asset_foo4_02_7 from '@/assets/images/foo4-02.png';
import __asset_foo5_01_8 from '@/assets/images/foo5-01.png';
import __asset_foo5_02_9 from '@/assets/images/foo5-02.png';

/**
 * diy配置
 * */

import toolCom from '@diy/components/legacy/diyComponents/index.js';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useShoppingStore = defineStore('shopping', () => {
  const activeName = ref({});
  const defaultConfig = ref({
    swiperBg: {
      isShow: {
        val: true,
      },
      imgList: {
        title: '最多可添加10张图片，建议宽度750px',
        max: 10,
        list: [
          {
            img: 'http://kaifa.crmeb.net/uploads/attach/2020/03/20200319/a32307fd1043c350932a462839288d38.jpg',
            info: [
              {
                title: '标题',
                value: '',
                maxlength: 10,
                tips: '选填，不超过十个字',
              },
              {
                title: '链接',
                value: '',
                maxlength: 999,
                tips: '请填写链接',
              },
            ],
          },
          {
            img: 'http://kaifa.crmeb.net/uploads/attach/2020/03/20200319/906d46eb6f734eaf1fd820601893af0d.jpg',
            info: [
              {
                title: '标题',
                value: '',
                maxlength: 10,
                tips: '选填，不超过十个字',
              },
              {
                title: '链接',
                value: '',
                maxlength: 999,
                tips: '请填写链接',
              },
            ],
          },
        ],
      },
    },
    monograph: {
      isShow: {
        val: true,
      },
      imgList: {
        title: '建议宽度750px',
        max: '',
        list: [
          {
            img: 'http://kaifa.crmeb.net/uploads/attach/2020/03/20200319/a32307fd1043c350932a462839288d38.jpg',
            info: [
              {
                title: '标题',
                value: '',
                maxlength: 10,
                tips: '选填，不超过十个字',
              },
              {
                title: '链接',
                value: '',
                maxlength: 999,
                tips: '请填写链接',
              },
            ],
          },
          {
            img: 'http://kaifa.crmeb.net/uploads/attach/2020/03/20200319/906d46eb6f734eaf1fd820601893af0d.jpg',
            info: [
              {
                title: '标题',
                value: '',
                maxlength: 10,
                tips: '选填，不超过十个字',
              },
              {
                title: '链接',
                value: '',
                maxlength: 999,
                tips: '请填写链接',
              },
            ],
          },
        ],
      },
    },
    picTxt: {
      isShow: {
        val: true,
      },
      richText: {
        val: '',
      },
    },
    tabBar: {
      tabBarList: {
        title: '图片建议宽度81*81px',
        list: [
          {
            name: '首页',
            imgList: [__asset_foo1_01_0, __asset_foo1_02_1],
            link: '/pages/index/index',
          },
          {
            name: '分类',
            imgList: [__asset_foo2_01_2, __asset_foo2_02_3],
            link: '/pages/goods_cate/goods_cate',
          },
          // {
          //     name:'周边',
          //     imgList:[__asset_foo3_01_4,__asset_foo3_02_5],
          //     pagePath: ''
          // },
          {
            name: '购物车',
            imgList: [__asset_foo4_01_6, __asset_foo4_02_7],
            link: '/pages/order_addcart/order_addcart',
          },
          {
            name: '我的',
            imgList: [__asset_foo5_01_8, __asset_foo5_02_9],
            link: '/pages/user/index',
          },
        ],
      },
    },
  });
  const component = ref({
    swiperBg: {
      list: [
        {
          components: toolCom.c_is_show,
          configNme: 'isShow',
        },
        {
          components: toolCom.c_upload_list,
          configNme: 'imgList',
        },
      ],
    },
    monograph: {
      list: [
        {
          components: toolCom.c_is_show,
          configNme: 'isShow',
        },
        {
          components: toolCom.c_upload_list,
          configNme: 'imgList',
        },
      ],
    },
    picTxt: {
      list: [
        {
          components: toolCom.c_is_show,
          configNme: 'isShow',
        },
        {
          components: toolCom.c_page_ueditor,
          configNme: 'richText',
        },
      ],
    },
    tabBar: {
      list: [
        {
          components: toolCom.c_tab_bar,
          configNme: 'tabBarList',
        },
      ],
    },
  });

  /**
   * @description 设置选中name
   * @param {String} name
   */
  function setConfig(name) {
    activeName.value = name;
  }
  /**
   * @description 更新默认数据
   * @param {Object} data
   */
  function updataConfig(data) {
    let value = defaultConfig.value;
    for (let i in data) {
      for (let j in value) {
        if (i === j) {
          value[j] = data[i];
        }
      }
    }
    defaultConfig.value = value;
  }

  return { activeName, defaultConfig, component, setConfig, updataConfig };
});
