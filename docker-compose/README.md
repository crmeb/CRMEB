# docker-compose 快速运行项目
## 1、安装docker
docker 官网下载
https://www.docker.com/products/docker-desktop
或命令安装
```
curl -sSL https://get.daocloud.io/docker | sh
```
## 2、安装docker-compose
https://www.runoob.com/docker/docker-compose.html
## 3、下载CRMEB程序
建议去下载最新开源代码 https://gitee.com/ZhongBangKeJi/CRMEB
程序放到docker-compose 同级目录下
## 4、启动项目
```
进入docker-compose目录 cd /docker-compose

运行命令：docker-compose up -d
```
建议：先复制一份环境变量文件并修改密码（尤其是准备对外提供服务时）
```
cp .env.example .env
```
说明：为避免误暴露，本仓库 `docker-compose` 默认将 MySQL/Redis/PHP-FPM/Nginx 端口仅绑定到 `127.0.0.1`（本机可访问）。
进入PHP容器启动队列、定时任务、长连接命令
```
进入容器：docker exec -it crmeb_php /bin/bash
进入到项目目录：cd /var/www
定时任务命令：php think timer start --d
长连接命令：php think workerman start --d
队列命令：php think queue:listen --queue
```
## 5、访问CRMEB 系统
http://localhost:8011/
## 6、安装CRMEB
### Mysql数据库信息：
```
Host:192.168.10.11
Post:3306
user:root
pwd:见 docker-compose/.env（MYSQL_ROOT_PASSWORD）
```
（宿主机访问：`127.0.0.1:3336`）
### Redis信息：
```
Host:192.168.10.10
Post:6379
db:0
pwd:默认无密码（仅用于本地开发）；生产环境建议开启 requirepass 且不要对公网暴露
```
（宿主机访问：`127.0.0.1:6379`）

如需开启 Redis 密码：
- 在对应的 `redis/redis.conf` 中取消注释 `requirepass` 并设置强密码
- 同时在 `docker-compose/.env` 设置 `REDIS_PASSWORD` 为同一密码（用于 healthcheck 的 redis-cli 自动鉴权）
## 7、常见问题
1. 端口被占用进入docker-compose.yml 里面修改端口

2. 如果运行docker-compose up -d 启动失败，请查看docker-compose.yml 修改里面镜像地址或其它配置

3. Error response from daemon: Address already in use 报错
  一般情况下是设置的ip被占用，修改下某个容器下的ipv4_address地址

4. MYSQL容器无法启动，没有任何日志
  注意m1芯片下需要使用mysql镜像daocloud.io/library/mysql:5.7.5-m15；其他任何情况下都
   使用mysql:5.7的镜像
