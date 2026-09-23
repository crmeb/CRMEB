crmeb/app/api目录是网站前端(非管理后台)的API接口目录。

它与adminapi目录的区别在于:

- adminapi目录下的是管理后台系统的API接口
- api目录下的是网站前端系统(手机端/微信小程序/H5等)的API接口

具体来说:

- api目录下也是采用控制器(Controller)方式组织接口代码
- 每个控制器对应一个功能模块,如OrderController负责订单相关接口等
- 接口用于前端页面的ajax请求,获取数据用于渲染
- 接口也采用RESTful风格设计

例如:

- 用户注册接口在UserController的register方法
- 获取订单列表在OrderController的lists方法
- 支付结果通知在PayController的notify方法

和adminapi目录一样,api目录也通过定义清晰的接口,解耦了前后端,让前端更专注于业务展示。

区别在于目标用户不同:

- adminapi为后台管理员使用
- api目录下的接口为前台用户(手机端、小程序端等)提供数据服务

所以二者都起到了前后端分离的关键作用。