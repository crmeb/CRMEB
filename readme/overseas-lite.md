# Overseas Lite（20人团队后台 / 海外版）

## 目标

- 面向海外（默认英文/UTC）
- 适合 20 人团队后台：模块收敛、权限清晰、避免“全家桶”式膨胀

## 启用方式

安装前/安装后都可以，通过 `.env` 开启：

```
[APP]
OVERSEAS_MODE=1
TIMEZONE=UTC

[LANG]
default_lang=en-us
```

## 初始化（推荐安装完成后执行一次）

```
docker exec -it crmeb_php php think overseas:init
```

会做：

- 设置 `site_name=Overseas Store`
- 清空 `custom_admin_js`
- 禁用秒杀/砍价/拼团显示（`model_checkbox=[]`）

