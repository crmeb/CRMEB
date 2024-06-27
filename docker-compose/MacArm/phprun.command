#!/bin/bash

# 进入项目容器运行PHP脚本文件

# 进入容器
docker exec -it crmeb_php /bin/bash
# 进入项目
cd /var/www
# 启动 定时任务
php think timer start --d
# 启动 长连接
php think workerman start --d
# 启动 队列
php think queue:listen --queue

