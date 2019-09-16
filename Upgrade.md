# 升级指导
**环境要求php7.1+**
**升级前请注意备份站点和数据库!!!**
建议备份目录
```
backup/
public/uploads/
```

1. 修改微信服务授权地址: `/api/wechat/serve`
2. 公众号菜单商城首页地址去掉wap，直接填写网址即可，例如：原来http://www.abc.com/wap 改为 http://www.abc.com
3. 修改nginx或 mysql 项目根目录为`项目目录/public/`
4. 升级 PHP至 `7.1+`
5. 修改公众号微信支付目录为网站域名
例如网站域名为`http://www.crmeb.com`则修改为`www.crmeb.com/`
6. 在数据库中执行数据库更新文件`update.sql`
7. 复制根目录`.example.env`另存为`.env`,重新配置数据库连接信息
~~~
APP_DEBUG = true

[APP]
DEFAULT_TIMEZONE = Asia/Shanghai

[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1 #数据库连接地址
DATABASE = test #数据库名称
USERNAME = username #数据库登录账号
PASSWORD = password #数据库登录密码
HOSTPORT = 3306 #数据库端口
CHARSET = utf8
DEBUG = true

[LANG]
default_lang = zh-cn
~~~

8. 开启服务器 `20002`,`20003`端口, 用于长连接服务.如果是云服务器需要在安全组中开启端口
9.  h5端
    调试
    ```sh
    npm run  serve
    ```
    打包
    ```sh
    npm run   build
    ``` 
    配置 `.env.production` 文件
    ```
    VUE_APP_API_URL= #接口地址,例如 http://www.abc.com/api
    VUE_APP_WS_URL= #长连接服务地址,例如 ws://www.abc.com:20003
    ```
打包好的页面文件，上传至public目录下
10、数据库批量替换图片路径
11、组合数据需要重新配置，例如：个人中心菜单，首页导航链接


