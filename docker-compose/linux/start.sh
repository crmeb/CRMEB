#!/bin/bash

echo ">> run"
docker-compose up -d
echo "[后台地址]\n http://localhost:8011/"
echo "[MySql信息]\n host:192.168.10.11:3306  user:root password:123456 database:crmeb"
echo "[Redis信息]\n host:192.168.10.10:6379 password:123456"
