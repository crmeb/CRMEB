# CRMEB uni-app
## 开发规范

### 命名
页面目录： 
1. 主（pages）目录下的文件夹命名格式为下划线命名法，例如：商品详情 goods_details
```
例如：
    user 我的里面的文件
        ├─ user_address 添加地址
        ├─ user_address_list 地址列表
        ├─ user_get_coupon 领取优惠券列表
        ├─ user_coupon 我的优惠券列表
        ├─ order_confirm 订单确认
        ├─ order_details 订单详情
```
2. 组件（components）目录下的文件夹命名格式为骆驼命名法，例如：优惠券列表弹窗组件 couponListWindow
```
例如：
        ├─ addressWindow 地址弹窗
        ├─ countDown 倒计时
        ├─ couponListWindow 优惠券列表
        ├─ couponWindow 优惠券
        ├─ productConSwiper 轮播图
        ├─ productWindow 属性
```
3. 类名函数命名 小驼峰式 例如：addUser
4. 常量 采用全大写下划线命名 例如：VUE_APP_API_URl
5. 变量命名 小驼峰式 例如：userInfo
### 文件管理规范
~~~
 api            接口，最好按照模块建立文件，便于后期维护
 components     一个组建一个文件夹
 config         主要放置项目配置
 libs           主要放置有关授权函数
 mixins         验证码倒计时
 pages          页面模块必须建文件夹区分（尽量避免在主包建立文件夹，防止小程序超包）
 plugin         插件
 static         静态资源包，放置图片样式等（小图标尽量不要更改，样式可以在app.vue中进行全局引用）
 vuex           路由状态管理，一个模块在modules 中建一个文件
 utils          自定义工具js 独立命名，一般不用新建文件夹
 main.js        vue初始化入口文件
 App.vue        应用配置，用来配置app全局样式以及监听应用生命周期
 pages.json     配置页面路由、导航条、选项卡等页面类信息
 ~~~
 ### 业务页面文件模块命名
 ~~~
 ├─ annex 有关扫码付款页
 ├─ auth  h5授权页
 ├─ columnGoods 精品推荐、热门榜单、首发新品、促销单品
 ├─ goods_cate  商品分类
 ├─ goods_details 商品详情
 ├─ goods_list 商品列表
 ├─ goods_search 商品搜索页
 ├─ index 首页
 ├─ live_list 小程序直播列表页
 ├─ news_details 新闻详情页
 ├─ news_list 新闻列表页
 ├─ order_addcart 购物车
 ├─ order_pay_status 支付状态页
 ├─ user 我的
 └─ users 有关个人中心的页面
 ~~~
 ### 开发要求
1. 使用弹性盒子flex布局方便快速；
2. style的样式单位尽可能的使用rpx，方便兼容其他端；
3. 为兼容多版本页面布局必须以 <template></template> 包裹，里面必须有个大标签来包裹所有模块；注：尽量避免使用html的标签；
例如：
![输入图片说明](https://images.gitee.com/uploads/images/2021/0107/143436_c11c149c_1339838.png "carbon (5).png")
4. 有关css样式规范
- <style></style>样式模块为页面内样式，只作用于本页面；
- static/css/base.css用于全局样式；例如：弹性盒子、颜色、内边距等的封装样式； 
- static/css/style.scss全局样式，主要放置某几个页面使用同种样式；
- uni.scss这里是uni-app内置的常用样式变量；
 注：为了css样式的兼容性；本项目使用scss编译插件；
例如：
![输入图片说明](https://images.gitee.com/uploads/images/2021/0107/143043_4390e3ad_1339838.png "carbon (4).png")
5. uni-app布局标签以及外部大框架是比较靠近小程序规范；里面编写的主要语法比较靠近vue；所以有这两个经验的基本上也很容易快速上手；当然想要更容易的了解到uni-app，详见uni-app官网   

## 开发打包项目

### 安装并使用 HBuilder X 打开 uni-app

### 配置说明 `config/app.js` 

```
module.exports = {
    // 小程序配置
    // #ifdef MP
    // 请求域名 格式： https://您的域名
    HTTP_REQUEST_URL: `https://v4.admin.crmeb.net`,
    // 长连接 格式：wss://您的域名:20003
    // 需要在后台设置->基础配置->WSS配置 开启WSS配置，并上传证书，重启workerman后生效
    // 请在PHP项目根目录下启动，长连接命令：sudo -u www php think workerman start --d
    // #endif

    // H5配置
    // #ifdef H5
    //H5接口是浏览器地址，非单独部署不用修改
    HTTP_REQUEST_URL: 'https://v4.admin.crmeb.net',
    // HTTP_REQUEST_URL: window.location.protocol + "//" + window.location.host,
    // 长连接地址，非单独部署不用修改
    // #endif
    // 以下配置在不做二开的前提下,不需要做任何的修改
    HEADER: {
        'content-type': 'application/json',
        //#ifdef H5
        'Form-type': navigator.userAgent.toLowerCase().indexOf('micromessenger') !== -1 ? 'wechat' : 'h5',
        //#endif
        //#ifdef MP
        'Form-type': 'routine',
        //#endif
        //#ifdef APP-VUE
        'Form-type': 'app'
        //#endif
    },
    // 回话密钥名称 请勿修改此配置
    TOKENNAME: 'Authori-zation',
    // 缓存时间 0 永久
    EXPIRE: 0,
    // 分页最多显示条数
    LIMIT: 10
};
```

### 启动项目（浏览器）
```
HBuilder X -> 运行 -> 运行到浏览器 -> 选择浏览器
```
![输入图片说明](https://images.gitee.com/uploads/images/2021/0107/163030_3f98128e_1339838.png "微信图片_2021010007162312.png")
### 启动项目(微信小程序)
```
// 配置appid
/manifest.json文件 -> 微信小程序配置 配置AppID
//运行设置
HBuilder X -> 工具 -> 设置 -> 运行设置 -> 小程序运行设置(微信开发工具路径)
//启动
HBuilder X -> 运行 -> 运行到小程序模拟器 -> 微信开发者工具
```
![输入图片说明](https://images.gitee.com/uploads/images/2021/0107/163400_b8b9ae7c_1339838.png "微信图片_2021010007163225.png")
### 打包H5
```
HBuilder X -> 发行 -> 网站-h5手机版 -> 发行

打包文件存存在 \uni-app\unpackage\dist\build\h5
```
![输入图片说明](https://images.gitee.com/uploads/images/2021/0107/175944_d231f5ba_1339838.png "微信图片_20210100007175558.png")
### 打包微信小程序
```
HBuilder X -> 发行 -> 小程序-微信 -> 发行
打包文件存放在：\uni-app\unpackage\dist\build\mp-weixin
```
![输入图片说明](https://images.gitee.com/uploads/images/2021/0107/175426_2b5d19bd_1339838.png "微信图片_202101000007174625.png")
### 放置目录
~~~
请把打包好的页面文件上传到 /项目/public/ 目录中
~~~

## 页面说明
主要目录结构及说明：
~~~
  ├─api 接口统一存放目录
  │ ├─api.js 公共接口
  │ ├─order.js 订单接口
  │ ├─public.js 授权分享接口
  │ ├─store.js 产品接口
  │ └─user.js 用户接口
  ├─components 组件目录
  │ ├─addressWindow 地址组件
  │ ├─countDown 倒计时组件
  │ ├─couponListWindow 优惠券列表弹框组件（商品详情、提交订单）
  │ ├─couponWindow 优惠券弹出框组件（首页）
  │ ├─goodList 商品列表组件
  │ ├─home 悬浮导航组件
  │ ├─invoicePicker 发票弹窗组件
  │ ├─jyf-parser 富文本框组件
  │ ├─Loading 正在加载组件
  │ ├─login_mobile 登录组件
  │ │ ├─index h5端手机号授权
  │ │ └─routine_phone 小程序手机号授权
  │ ├─orderGoods 订单产品展示组件
  │ ├─payment 支付弹出框组件
  │ ├─PriceChange 后台更改价格组件
  │ ├─productConSwiper Swiper产品轮播图组件
  │ ├─productWindow 产品属性组件
  │ ├─promotionGood 促销产品组件
  │ ├─recommend 热门推荐组件
  │ ├─shareInfo 分享组件
  │ ├─shareRedPackets 产品页分享红包组件
  │ ├─swipers swipers轮播组件
  │ ├─ucharts 图表组件
  │ ├─uni-calendar 日期组件
  │ ├─userEvaluation 产品页评论列表组件
  │ ├─wPicker 省市区组件
  │ ├─Authorize.vue 授权组件
  │ ├─emptyPage.vue 空页面组件
  │ └─tabNav.vue 头部导航组件
  ├─config 配置目录
  │ ├─app.js 接口配置
  │ ├─cache.js 缓存配置
  │ └─socket.js 长链接配置
  ├─libs 
  │ ├─login.js  是否登录
  │ ├─order.js 页面的跳转
  │ ├─routine.js 小程序授权
  │ └─wechat.js 公众号授权
  ├─mixins 验证码目录
  │ └─SendVerifyCode.js 验证码倒计时
  ├─pages 页面目录
  │ ├─annex 
  │ │ ├─offline_pay 扫码付款
  │ │ └─offline_result 付款结果页
  │ ├─auth 授权
  │ ├─columnGoods 
  │ │ └─HotNewGoods 精品推荐、热门榜单、首发新品、促销单品
  │ ├─goods_cate 商品分类
  │ ├─goods_details 商品详情
  │ ├─goods_list 商品列表
  │ ├─goods_search 商品搜索
  │ ├─index 首页
  │ ├─live_list 直播列表
  │ ├─news_details 新闻详情
  │ ├─news_list 新闻列表
  │ ├─order_addcart 购物车
  │ ├─order_pay_status 订单支付状态
  │ ├─user 我的
  │ ├─users 我的页面
  │ │ ├─goods_comment_con 商品评价
  │ │ ├─goods_comment_list 评价列表
  │ │ ├─goods_logistics 物流信息
  │ │ ├─goods_return 申请退货
  │ │ ├─login 登录
  │ │ ├─order_confirm 订单确认
  │ │ ├─order_details 订单详情
  │ │ ├─order_list 订单列表
  │ │ ├─static 放置图片的文件包
  │ │ ├─user_address 添加地址
  │ │ ├─user_address_list 地址列表
  │ │ ├─user_bill 账单明细
  │ │ ├─user_coupon 我的优惠卷
  │ │ ├─user_get_coupon 领取优惠卷
  │ │ ├─user_goods_collection 收藏产品
  │ │ ├─user_info 个人资料
  │ │ ├─user_integral 积分明细
  │ │ ├─user_invoice_form 添加新发票
  │ │ ├─user_invoice_list 发票管理
  │ │ ├─user_invoice_order 发票订单详情
  │ │ ├─user_money 我的账户
  │ │ ├─user_phone 手机号绑定
  │ │ ├─user_pwd_edit 修改密码
  │ │ ├─user_return_list 退款列表
  │ │ └─wechat_login 授权页
  ├─plugin 插件
  ├─static 静态文件
  │ ├─css 样式文件
  │ ├─iconfont 小图标字体包
  │ ├─images  图片包
  ├─store Vuex目录
  ├─utils 工具类目录
  │ ├─cache.js 缓存
  │ ├─emoji.js 表情图标
  │ ├─index.js 用户授权
  │ ├─permission.js 权限
  │ ├─request.js 请求接口
  │ ├─SubscribeMessage.js 订阅消息
  │ ├─util.js 工具函数
  │ └─validate.js 验证码
  ├─App.vue    
  ├─main.js    
~~~

二开文档：https://gitee.com/ZhongBangKeJi/CRMEB-Min/wikis/%E7%A7%BB%E5%8A%A8%E6%AE%B5%E6%89%93%E5%8C%85%E5%8F%91%E5%B8%83?sort_id=3303679