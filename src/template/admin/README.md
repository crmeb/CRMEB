# CRMEB Admin
## 开发规范
统一使用ES6 语法
方法注释
/*
* th => 表头
* data => 数据
* fileName => 文件名
* fileType => 文件类型
* sheetName => sheet页名
*/
export default function toExcel ({ th, data, fileName, fileType, sheetName }) 
行注释 //

### 命名

页面目录 文件夹命名格式骆驼式命名法,例如：用户列表 userList 
例如：商品模块
product 商品
    ├─ product 商品管理
        ├─productList 商品管理目录
            ├- index.vue  首页
        ├─ components  组建
            ├─ tableFrom.vue
            ├─ tableList.vue
        ├─ handle 操作功能页面目录
            ├─ delete.vue
    ├─productCategory 商品分类目录
        ├─ index.vue 商品分类首页

页面命名、组建、文件夹 命名格式小驼峰命名法,例如：用户列表 userList

类名函数命名 大驼峰式 例如：addUser
变量命名 小驼峰式 例如：user 或者 userInfo _userinfo user-info
常量 采用全大些下划线命名 例如：VUE_APP_API_URl

### 文件管理规范
pages 页面模块必须建文件夹区分；
api 接口一个模块一个文件；
components 一个组建一个文件夹；
plugins 插件一个插件一个文件夹；
vuex 路由状态管理，一个模块在modules 中建一个文件；
router 一个模块一个模块在modules 中建一个文件；
style 样式尽量采用iView自带组建，common.less 系统通用样式不要轻易动；
style中style.css自定义全局样式；
utils 自定义工具js 独立命名，一般不用新建文件夹；
## 模块命名
~~~
├─ account 有关登录页
├─ app 应用（小程序模板消息、公众号）
├─ cms 文章管理
├─ finance  财务管理
├─ index 主页
├─ marketing 优惠券、积分
├─ notify 短信账户、短信购买、短信模板
├─ order 系统订单管理
├─ product 商品管理
├─ setting 系统设置、运费模板、可视化等
├─ system 开发配置、安全维护等
└─ user 用户管理
~~~
### 页面说明
主要目录结构及说明：
```
├── public                      # 静态资源
│   ├── favicon.ico            # favicon图标
│   └── index.html             # html 模板
├── src                         # 源代码
│   ├── api                    # 所有请求
│   │    └──account.js        # 有关登录的接口
│   │    └──app.js            # 有关应用（小程序、公众号）的接口
│   │    └──cms.js            # 有关内容（文章管理、分类）的接口
│   │    └──common.js         # 表格删除、获取消息提醒的接口
│   │    └──diy.js            # 可视化的接口
│   │    └──finance.js        # 有关财务的接口
│   │    └──index.js          # 有关首页的接口
│   │    └──marketing.js      # 有关营销的接口
│   │    └──order.js          # 有关订单的接口
│   │    └──product.js        # 有关商品的接口
│   │    └──setting.js        # 有关设置的接口
│   │    └──system.js         # 有关维护（开发配置、安全维护）的接口
│   │    └──systemAdmin.js    # 有关管理员的接口（设置--管理权限--管理员列表）
│   │    └──systemMenus.js    # 有关权限规则的接口（设置--管理权限--权限规则）
│   │    └──uploadPictures.js # 有关上传图片附件的接口
│   │    └──user.js           # 有关会员的接口
│   ├── assets                 # 图片、svg 等静态资源
│   ├── components             # 公共组件
│   │    └──cards             # 统计
│   │    └──common-icon       # 框架导航图片组件
│   │    └──copyright         # 页面footer的底部声明
│   │    └──couponList        # 添加优惠券列表
│   │    └──customerInfo      # 选择用户
│   │    └──diyComponents     # 可视化组件
│   │    └──echarts           # 统计图
│   │    └──echartsNew        # 导航栏列表统计图组件
│   │    └──freightTemplate   # 运费模板
│   │    └──from              # 生成表单
│   │    └──goodsList         # 商品列表
│   │    └──iconFrom          # 导航添加图标
│   │    └──icons             # 动态添加图片组件
│   │    ├── main             # 布局
│   │          └──components  # 布局组件
│   │               └──a-back-top     # 返回顶部
│   │               └──error-store    # 错误日志（弃用）
│   │               └──fullscreen     # 控制是否全屏图标
│   │               └──header-bar     # （面包屑、折叠面板、刷新）
│   │               └──header-notice  # 头部提示消息
│   │               └──header-search  # 头部搜索
│   │               └──language       # 多语言
│   │               └──side-menu      # 侧边导航栏
│   │               └──tags-nav       # 右侧关闭按钮
│   │               └──user           # 我的（个人中心、退出登录）
│   │    └──mde               # 多文本框
│   │    └──modelSure         # 确认模态框
│   │    └──newsCategory      # 图文管理页
│   │    └──parent-view       # 暂没用到
│   │    └──publicSearchFrom  # 头部搜索（没用到）
│   │    └──quill             # 编辑器（没用到）
│   │    └──referrerInfo      # 推荐人信息
│   │    └──rightConfig       # 可视化调用组件入口
│   │    └──searchFrom        # 订单页的搜索
│   │    └──sendCoupons       # 发送优惠券
│   │    └──sortList          # 商品分类列表
│   │    └──systemStore       # 添加提货点
│   │    └──ueditorFrom       # 编辑器组件（供参考，没用到，主要用安装组件--vue-ueditor-wrap）
│   │    └──uploadPictures    # 上传图片
│   │    └──uploadVideo       # 上传视频（用于商品编辑器里面）
│   │    └──userLabel         # 用户签名弹窗
│   ├── config                 # 配置
│   ├── directive              # 配置（没用到）
│   ├── filters                # 有关直播调用的方法
│   ├── libs                   # 公共方法
│   ├── locale                 # 暂无用处
│   ├── mock                   # 数据模拟
│   ├── pages                  # 所有页面
│   │    └──account           # 有关登录页
│   │         └──login        # 登录
│   │    └──app               # 应用
│   │         └──routine      # 小程序模板消息
│   │         └──wechat       # 公众号
│   │              └──menus   # 微信菜单
│   │              └──newsCategory   # 图文管理
│   │                   └──save      # 图文添加
│   │              └──reply          # 自动回复
│   │                   └──follow    # 微信关注回复/无效关键词回复
│   │                   └──keyword   # 关键字回复
│   │              └──user           # 用户
│   │                   └──tag       # 用户标签
│   │                   └──user      # 微信用户
│   │                   └──message   # 用户行为记录
│   │    └──cms                      # 内容
│   │         └──addArticle          # 添加文章/编辑文章
│   │         └──article             # 文章管理
│   │         └──articleCategory     # 文章分类
│   │    └──finance                  # 财务
│   │         └──financialRecords    # 财务记录
│   │              └──bill           # 资金记录
│   │              └──recharge       # 充值记录
│   │    └──index                    # 主页
│   │    └──marketing                # 营销
│   │         └──storeCoupon         # 优惠券制作
│   │         └──storeCouponIssue    # 优惠券列表
│   │         └──storeCouponUser     # 会员领取记录
│   │         └──userPoint           # 积分日志
│   │    └──notify                   # 短信设置
│   │         └──smsConfig           # 短信账户
│   │         └──smsPay              # 短信购买
│   │         └──smsTemplateApply    # 短信模板
│   │    └──order                    # 订单管理
│   │    └──product                  # 商品
│   │         └──productAdd          # 添加商品
│   │         └──productAttr         # 商品规格
│   │         └──productClassify     # 商品分类
│   │         └──productList         # 商品管理
│   │         └──productReply        # 商品评论管理
│   │    └──setting                  # 设置
│   │         └──cityDada            # 城市数据
│   │         └──deliveryService     # 配送员列表
│   │         └──devise              # 页面设计
│   │              └──links          # 页面链接
│   │              └──list           # 模板列表
│   │         └──freight             # 物流公司
│   │         └──setSystem           # 系统设置
│   │         └──shippingTemplates   # 运费模板
│   │         └──systemAdmin         # 管理员列表
│   │         └──systemMenus         # 权限规则
│   │         └──systemRole          # 身份管理
│   │         └──user                # 个人中心
│   │         └──verifyOrder         # 核销订单
│   │    └──system                   # 维护
│   │         └──clear               # 刷新缓存
│   │         └──configTab           # 配置
│   │              └──index          # 配置分类
│   │              └──list           # 配置列表
│   │         └──error               # 错误页
│   │              └──403            # 403
│   │              └──404            # 404
│   │              └──500            # 500
│   │         └──group               # 组合数据
│   │         └──maintain              
│   │              └──systemCleardata    # 清除数据
│   │              └──systemDatabackup   # 数据备份
│   │              └──systemFile         # 文件校验
│   │                   └──opendir       # 文件管理
│   │              └──systemLog          # 系统日志
│   │    └──user                         # 会员
│   │         └──group                   # 会员分组
│   │         └──label                   # 会员标签
│   │         └──list                    # 会员管理
│   ├── plugins                           # 插件
│   ├── router                            # 路由配置
│   │    └──modules                      # 页面路由模块
│   │         └──app.js                       # 有关应用（小程序、公众号）
│   │         └──cms.js                       # 有关内容（文章管理、文章分类）
│   │         └──echarts.js                   # 有关统计
│   │         └──finance.js                   # 有关财务  
│   │         └──frameOut.js                  # 有关客服
│   │         └──index.js                     # 有关主页
│   │         └──marketing.js                 # 有关营销
│   │         └──order.js                     # 有关订单
│   │         └──product.js                   # 有关商品
│   │         └──setting.js                   # 有关设置
│   │         └──system.js                    # 有关维护
│   │         └──user.js                      # 有关会员
│   │    └──index.js                          # 路由的导出以及拦截处理
│   │    └──routes.js                         # 路由的汇总
│   ├── store                                  # Vuex 状态管理
│   ├── utils                                  # js工具
│   │    └──auth.js                           # 有关授权的鉴权指令
│   │    └──authLapse.js                      # 授权提示框
│   │    └──city.js                           # 省市区的json文件
│   │    └──modalForm.js                      # 表单模态框
│   │    └──modalSure.js                      # 删除询问模态框
│   │    └──public.js                         # 表格删除询问模态框
│   │    └──videoCloud.js                     # 上传云储存视频（七牛、腾讯、阿里）
│   │    └──validate.js                       # 将时间戳转化成时间；
│   ├── styles            # 样式管理
│   ├── setting.js        # 业务配置文件
│   ├── main.js           # 入口文件 加载组件 初始化等
│   └── App.vue           # 入口页面
├── package.json           # package.json
├── package-lock           # package-lock.json
├── README                 # 说明文档
└── vue.config.js          # Vue配置
```
### 调试
```sh
npm run serve
```
### 打包
```sh
npm run build
``` 
### 配置说明 `src/setting.js` 
```sh
VUE_APP_API_URL#接口地址,例如 http://www.abc.com/api
VUE_APP_WS_URL #长连接服务地址,例如 ws://www.abc.com:20003
```
### 放置目录
请把打包好的页面文件上传到public/admin目录中
请把打包好的页面文件上传到public/admin目录中

### 页面路径文档

|     |     |     |     |     |
| --- | --- | --- | --- | --- |
|  页面        |   页面地址   |  路径 |  带参路径例子  |  参数解释  |
|  主页     |   @/pages/index/index |   /admin/home    |     |      |
| 登录 | @/pages/account/login |/admin/login| |  |
| 微信菜单     | @/pages/app/wechat/menus/index|   /admin/app/wechat/setting/menus/index    |  |   |
| 图文管理| @/pages/app/wechat/newsCategory/index | /admin/app/wechat/news_category/index | |  |
| 图文添加 |@/pages/app/wechat/newsCategory/save | /admin/app/wechat/news_category/save/:id? |/admin/app/wechat/news_category/save/0|id：0添加；非0编辑（图文id）|
|微信关注回复|@/pages/app/wechat/reply/follow| /admin/app/wechat/reply/follow/subscribe|||
| 关键字回复|@/pages/app/wechat/reply/keyword|/admin/app/wechat/reply/keyword|||
|关键字添加|@/pages/app/wechat/reply/follow|/admin/app/wechat/reply/keyword/save/:id?|/admin/app/wechat/reply/keyword/save/0|id：0添加；非0编辑（关键字id）|
| 无效关键词回复 |@/pages/app/wechat/reply/follow|/admin/app/wechat/reply/index/default|  |  |
| 微信用户     | @/pages/app/wechat/user/user  |   /admin/app/wechat/wechat_user/user/index   |     |      |
| 用户标签    | @/pages/app/wechat/user/tag   |   /admin/app/wechat/wechat_user/user/tag   |   |  |
|用户分组  | @/pages/app/wechat/user/tag | /admin/app/wechat/wechat_user/user/group    |     |     |
| 用户行为记录  | @/pages/app/wechat/user/message | /admin/app/wechat/wechat_user/user/message   | |  |
| 微信模板消息     | @/pages/app/routine/routineTemplate/index |   /admin/app/wechat/setting/template/index    |     |      |
|小程序模板消息|@/pages/app/routine/routineTemplate/index|/admin/app/routine/routine_template/index|  |  |
|文章管理|@/pages/cms/article/index|/admin/cms/article/index|  |  |
| 文章分类|@/pages/cms/articleCategory/index|/admin/cms/article_category/index|      |  |
| 文章添加|@/pages/cms/addArticle/index|/admin/cms/article/add_article/:id?|/admin/cms/article/add_article/2|id：没有id为添加；有id为编辑（文章id）|
| 充值记录|@/pages/finance/financialRecords/recharge|/admin/finance/user_recharge/index|||
|资金记录|@/pages/finance/financialRecords/bill |/admin/finance/finance/bill |||
|优惠券制作|@/pages/marketing/storeCoupon/index|/admin/marketing/store_coupon/index|||
|优惠券列表|@/pages/marketing/storeCouponIssue/index|/admin/marketing/store_coupon_issue/index| | |
|会员领取记录|@/pages/marketing/storeCouponUser/index|/admin/marketing/store_coupon_user/index|||
| 积分日志  | @/pages/marketing/userPoint/index  | /admin/marketing/user_point/index | | |
| 短信账户 | @/pages/notify/smsConfig/index | /admin/setting/sms/sms_config/index | | |
| 短信模板 | @/pages/notify/smsTemplateApply/index | /admin/setting/sms/sms_template_apply/index | | |
| 短信购买 | @/pages/notify/smsPay/index | /admin/setting/sms/sms_pay/index | | |
| 公共短信模板 | @/pages/notify/smsTemplateApply/index |  /admin/setting/sms/sms_template_apply/commons | | |
| 订单管理  | @/pages/order/orderList/index | /admin/order/list | | |
| 收银订单  | @/pages/order/offline/index | /admin/order/offline | |  |
| 发票管理  | @/pages/order/invoice/index | /admin/order/invoice/list | |  |
| 商品管理  | @/pages/product/productList | /admin/product/product_list |  |  |
| 商品分类  | @/pages/product/productClassify | /admin/product/product_classify | | |
| 商品添加  | @/pages/product/productAdd |/admin/product/add_product/:id? | /admin/product/add_product/84| id：没有id为添加；有id为编辑（商品id）|
| 商品评论  | @/pages/product/productReply |/admin/product/product_reply/:id?|/admin/product/product_reply/84 | id：没有id为商品评论页；有id为查看评论（商品id） |
| 属性规则  | @/pages/product/productAttr | /admin/product/product_attr | | |
| 城市数据   | @/pages/setting/cityDada/index | /admin/setting/freight/city/list| | |
| 配送员列表 | @/pages/setting/deliveryService/index | /admin/setting/delivery_service/index| | |
| 页面设计   | @/pages/setting/devise/index | /admin/setting/pages/diy| | |
| 页面设计列表 | @/pages/setting/devise/list | /admin/setting/pages/devise| | |
| 页面链接  |    @/pages/setting/devise/links | /admin/setting/pages/links| | |
| 物流公司 | @/pages/setting/freight/index | /admin/setting/freight/express/index | | |
| 积分配置  | @/pages/setting/setSystem/index |/admin/marketing/integral/system_config/3/11 | |  |
| 运费模板 | @/pages/setting/shippingTemplates/index |/admin/setting/freight/shipping_templates/list| | |
| 管理员管理 | @/pages/setting/systemAdmin/index | /admin/setting/system_admin/index | | |
| 权限规则 | @/pages/setting/systemMenus/index | /admin/setting/system_menus/index | | |
| 身份管理  | @/pages/setting/systemRole/index | /admin/setting/system_role/index | | |
| 个人中心  | @/pages/setting/user/index |/admin/system/user| |  |
| 核销订单 | @/pages/setting/verifyOrder/index |/admin/setting/merchant/system_verify_order/index| | |
| 刷新缓存 | @/pages/system/clear/index |/admin/system/maintain/clear/index| | |
| 配置分类 | @/pages/system/configTab/index |/admin/system/config/system_config_tab/index| | |
| 配置列表 | @/pages/system/configTab/list |/admin/system/config/system_config_tab/list/:id?| /admin/system/config/system_config_tab/list/22 | id:配置id |
| 403 | @/pages/system/error/403 | /admin/403 |  |
| 500 | @/pages/system/error/500 | /admin/500 |  |
| 404 | @/pages/system/error/404 | /admin/* |  |
| 系统日志 | @/pages/system/maintain/systemLog/index |/admin/system/maintain/system_log/index| | |
| 文件校验 | @/pages/system/maintain/systemFile/index |/admin/system/maintain/system_file/index| | |
| 清除数据 | @/pages/system/maintain/systemCleardata/index |/admin/system/maintain/system_cleardata/index| | |
| 数据备份 | @/pages/system/maintain/systemDatabackup/index |/admin/system/maintain/system_databackup/index| | |
| 文件管理 | @/pages/system/maintain/systemFile/opendir |/admin/system/maintain/system_file/opendir| | |
| 在线更新 | @/pages/system/systemUpgradeclient/index |/admin/system/system_upgradeclient/index| | |
| 数据配置 | @/pages/system/group/list |/admin/system/system_group_data| | |
| 会员分组 | @/pages/user/group/index |/admin/user/group| |  |
| 会员标签 | @/pages/user/label/index |/admin/user/label| |  |
| 会员管理 | @/pages/user/list/index |/admin/user/list| |  |
| 上传图片| @/components/uploadPictures/widgetImg |/admin/widget.images/index.html| |  |
| 上传图标| @/components/iconFrom/index |/admin/widget.widgets/icon.html| |  |
| 选择商品| @/components/goodsList/index |/admin/store.StoreProduct/index.html| |  |
| 选择用户| @/components/customerInfo/index |/admin/system.User/list.html| |  |
| 上传视频| @/components/uploadVideo/index |/admin/widget.video/index.html| |  |

### 前端添加页面
```sh
一、新路由模块添加：
    1、在src/router/modules里面路由模块js文件；
       例如：
           import BasicLayout from '@/components/main'
           
           const pre = 'order_'
           
           export default {
             path: '/admin/order',
             name: 'order',
             header: 'order',
             redirect: {
               name: `${pre}list`
             },
             component: BasicLayout,
             children: [
               {
                 path: 'list',
                 name: `${pre}list`,
                 meta: {
                   auth: ['admin-order-storeOrder-index'],
                   title: '订单管理'
                 },
                 component: () => import('@/pages/order/orderList/index')
               }
             ]
           }
    2、在src/router/routers.js里面引用以及导出；
       例如：
         import order from './modules/order'
         const frameIn = [
             order
         ]
         // 导出需要显示菜单的
         export const frameInRoutes = frameIn
         
         // 重新组织后导出
         export default [
             ...frameIn
         ]
         
二、添加动态菜单：
    1、设置 > 管理权限 > 权限规则 
    2、侧边导航设置项：      
       path：完整的页面路径
       title：菜单标题
       icon：（选填）菜单图标，该选项仅支持 iView 内置 icon
``` 
### vuex使用
```sh
本项目在src/store/module文件下建立你所要储存数据的js模块；
  例如（user.js）：
      export default {
        namespaced: true,
        state: {
          pageName: ''
        },
        mutations: {
          setPageName(state,id){
            state.pageName = id
          }
        },
        actions: {
        
        }
      }
在src/store/index.js文件引用；
  例如：
      import Vue from 'vue'
      import Vuex from 'vuex'
      import VuexPersistence from 'vuex-persist'
      import user from './module/user'
      Vue.use(Vuex)
      export default new Vuex.Store({
          state: {
          },
          mutations: {
          },
          actions: {
          },
          //这块代码为持久化储存，需要安装插件VuexPersistence
          plugins:[
              new VuexPersistence({
                  reducer: state => ({
                      user: state.user, //这个就是存入localStorage的值
                  }),
                  storage: window.localStorage
              }).plugin
          ],
          modules: {
              user
          }
      })
在其他页面：
    传值：this.$store.commit('userInfo/setPageName', row.template_name);  
    引用：this.$store.state.userInfo.pageName;
    
注：vuex有多种传值以及调用方法；具体的可以参考vuex的官方文档；        
```
### Cookies储存封装函数
```sh
目录：src/libs/util.js；
例如：
   import { getCookies, setCookies，removeCookies } from '@/libs/util'
储存数据：setCookies('token','kfhskd')
获取数据：getCookies('token')
移除数据：removeCookies('token')
```
### 可视化使用
```sh
可视化页面目录：src/pages/setting/devise/index.vue;
页面分3个模块：移动端首页、首页的可视化编辑、项目链接；
              1、移动端首页：使用iframe引用前台页面；
                 例如：
                     <iframe class="iframe-box" :src="iframeUrl" frameborder="0" ref="iframe"></iframe>
              2、可视化编辑：
                     src/components/rightConfig/index.vue页面为主要操作的容器；所有小组件使用动态添加的方法引用到此页面；
                     例如：
                         <template>
                             <div class="right-box" v-if="rCom.length">
                                 <div class="title-bar">模块配置</div>
                                 <div class="mobile-config" v-if="rCom.length">
                         
                                     <div v-for="(item,key) in rCom" :key="key">
                                         //此标签为动态添加组件
                                         <component :is="item.components.name" :name="item.configNme" :configData="configData"></component>
                                     </div>
                                     <div style="text-align: center;" v-if="rCom.length">
                                         <Button type="primary" style="width:100%;margin:0 auto;height: 40px" @click="saveConfig">保存</Button>
                                     </div>
                                 </div>
                             </div>
                         
                         </template>
                     小组件目录：src/components/diyComponents/放置你要编辑产品的具体操作功能；
                           例如（c_is_show.vue）：
                               <template>
                                   <div class="c_row-item">
                                       <Col class="label" span="4">
                                           是否显示
                                       </Col>
                                       <Col span="19">
                                           <i-switch v-model="datas[name].val"/>
                                       </Col>
                                   </div>
                               </template>
                     小组件初始化数据datas在vuex里面储存，查找src/store/module/moren.js

```








      

