文档地址：https://doc.crmeb.com/web/single/crmeb_v4/1115

1、下载编译工具
https://www.dcloud.io/hbuilderx.html
2、修改请求地址
修改文件 /config/app.js
HTTP_REQUEST_URL 修改这个参数

### 目录结构
~~~
└─view uni-app存放根目录
├─api 接口统一存放目录
│ ├─activity.js 活动接口
│ ├─admin.js 后台订单管理接口
│ ├─api.js 公共接口
│ ├─order.js 订单接口
│ ├─public.js 授权分享接口
│ ├─store.js 产品接口
│ └─user.js 用户接口
├─components 组件目录
│ ├─addressWindow 地址组件
│ ├─countDown 倒计时组件
│ ├─couponListWindow 优惠券列表弹框组件
（商品详情、提交订单）
│ ├─couponWindow 优惠券弹出框组件
（首页）
│ ├─goodList 商品展示组件
│ ├─home 悬浮导航组件
│ ├─jyf-parser 富文本框组件
│ ├─Loading 正在加载组件
│ ├─orderGoods 订单产品展示组件
│ ├─payment 支付弹出框组件
│ ├─PriceChange 后台更改价格组件
│ ├─productConSwiper Swiper产品轮播图组件
│ ├─productWindow 产品属性组件
│ ├─promotionGood 促销产品组件
│ ├─recommend 热门推荐组件
│ ├─shareInfo 分享组件
│ ├─shareRedPackets 产品页分享组件
│ ├─swipers swipers轮播组件
│ ├─ucharts 图表组件
│ ├─uni-calendar 日期组件
│ ├─userEvaluation 产品页评论列表组件
│ ├─Authorize.vue 授权组件
│ ├─emptyPage.vue 空页面组件
│ └─tabNav.vue 导航组件
├─config 配置目录
│ ├─app.js 接口配置
│ ├─cache.js 缓存配置
│ └─socket.js 长链接配置
├─libs
│ ├─chat.js
│ ├─login.js  是否登录
│ ├─order.js 活动页面的跳转
│ ├─routine.js 小程序授权
│ └─wechat.js 公众号授权
├─mixins 验证码目录
│ └─SendVerifyCode.js 验证码倒计时
├─pages 页面目录
│ ├─activity 活动
│ │ ├─bargain 砍价记录
│ │ ├─goods_bargain 砍价列表
│ │ ├─goods_bargain_details 砍价详情
│ │ ├─goods_combination 拼团列表
│ │ ├─goods_combination_details 拼团详情
│ │ ├─goods_combination_status 拼团状态
│ │ ├─goods_seckill 秒杀列表
│ │ ├─goods_seckill_details 秒杀详情
│ │ ├─poster-poster 海报页面
│ ├─admin 订单管理
│ │ ├─delivery 订单发货
│ │ ├─order 订单统计
│ │ ├─order_cancellation 订单核销
│ │ ├─orderDetail 订单详情
│ │ ├─orderList 订单列表
│ │ └─statistics 营业额统计
│ ├─auth 授权
│ ├─columnGoods  精品推荐，热门榜单，首发新品，促销单品
│ ├─customer_list 客服列表
│ ├─goods_cate 商品分类
│ ├─goods_details 商品详情
│ ├─goods_list 商品列表
│ ├─goods_search 商品搜索
│ ├─index 首页
│ ├─news_details 新闻详情
│ ├─news_list 新闻列表
│ ├─order_addcart 购物车
│ ├─order_details 订单详情
│ ├─order_pay_status 订单支付状态
│ ├─retrieve_password 找回密码
│ ├─order_addcart 购物车
│ ├─user 个人中心
│ ├─users 我的页面
│ │ ├─commission_rank 佣金排行
│ │ ├─goods_comment_con 商品评价
│ │ ├─goods_comment_list 评价列表
│ │ ├─goods_details_store 商品提货点
│ │ ├─goods_logistics 物流信息
│ │ ├─goods_return 商品退货
│ │ ├─login 登录
│ │ ├─order_confirm 订单确认
│ │ ├─order_list 订单列表
│ │ ├─promoter_rank 推广人排行
│ │ ├─promoter-list 推广人列表
│ │ ├─promoter-order 推广人订单
│ │ ├─retrievePassword 找回密码
│ │ ├─user_address 添加地址
│ │ ├─user_address_list 地址列表
│ │ ├─user_bill 账单明细
│ │ ├─user_cash 提现
│ │ ├─user_coupon 我的优惠卷
│ │ ├─user_get_coupon 领取优惠卷
│ │ ├─user_goods_collection 收藏产品
│ │ ├─user_info 个人资料
│ │ ├─user_integral 积分明细
│ │ ├─user_money 我的账户
│ │ ├─user_payment 充值
│ │ ├─user_phone 手机号绑定
│ │ ├─user_pwd_edit 修改密码
│ │ ├─user_return_list 退款列表
│ │ ├─user_sgin 签到
│ │ ├─user_sgin_list 签到记录
│ │ ├─user_spread_code 分销海报
│ │ ├─user_spread_money 佣金明细
│ │ ├─user_spread_user 我的推广
│ │ └─user_vip 会员页面
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
│ ├─request.js 请求基类
│ ├─SubscribeMessage.js 消息
│ ├─util.js 工具函数
│ └─validate.js 验证码
├─App.vue    
├─main.js

~~~
