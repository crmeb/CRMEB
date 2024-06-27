
# 启动docker-compose
run:
	@echo ">> run"
	@docker-compose -f ./docker-compose.yml up -d
	@echo "[后台地址]\n http://localhost:8011/"
	@echo "[MySql信息]\n host:192.168.10.11:3306  user:root password:123456 database:crmeb"
	@echo "[Redis信息]\n host:192.168.10.10:6379 password:123456"

# 启动php容器，进入启动PHP命令
phprun:
	@echo ">> 进入项目容器后请执行以下三个命令"
	@echo "启动定时任务  php think timer start --d"
	@echo "启动长连接  php think workerman start --d"
	@echo "启动队列  php think queue:listen --queue"
	@echo ">> 进入项目容器"
	@docker exec -it crmeb_php /bin/bash && cd /var/www

# 构建
build:
	@echo ">> php build"
	@docker-compose -f ./docker-compose.yml build

# 停止
down:
	@echo ">> php stop"
	@docker-compose -f ./docker-compose.yml down

# 查看日志
logs:
	@echo ">> cat logs"
	@docker-compose -f ./docker-compose.yml logs -f

# 查看状态
status:
	@echo ">> cat logs"
	@docker-compose -f ./docker-compose.yml logs ps
