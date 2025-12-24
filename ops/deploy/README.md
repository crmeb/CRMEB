## 发布与回滚（目录软链最小方案）

目标：不引入复杂发布系统，具备“可回滚、可验证”的最小闭环。

### 目录结构（建议）

```
/var/www/CRMEB/
  releases/
    20251224_010203/
    20251223_235959/
  current -> /var/www/CRMEB/releases/20251224_010203
```

Nginx `root` 指向 `.../current/crmeb/public`（见 `ops/nginx/crmeb.conf.example`）。

