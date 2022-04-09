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
Host:192.168.10.1 
Post:3306 
user:root 
pwd:123456 
```
### Redis信息：
```
Host:192.168.10.10
Post:6379
db:0
pwd:123456
```
## 7、常见问题
1、端口被占用进入docker-compose.yml 里面修改端口

2、如果运行docker-compose up -d 启动失败，请查看docker-compose.yml 修改里面镜像地址或其它配置


