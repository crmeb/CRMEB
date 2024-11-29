#!/bin/bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH

php_version='74'
mysql_version='5.7'
redis_version='6.2'
action_type='install'
root_path=$(cat /var/bt_setupPath.conf)
setup_path=$root_path/server

#宝塔是否已安装
if [ -z "$root_path" ]; then
    echo "请先安装宝塔"
    exit 1
fi

#nginx是否已安装
if [ ! -f "${setup_path}/nginx/sbin/nginx" ]; then
    echo "请先安装nginx并配置网站"
    exit 1
fi

#安装php
php_install=1
#php路径变量
php_path="${setup_path}/php/74/bin/php"
# 获取已安装的php版本
for phpVer in 71 72 73 74; do
    if [ -d "${setup_path}/php/${phpVer}/bin" ]; then
        php_version=${phpVer}
        php_install=0
    fi
done
if [ $php_install == 1 ]; then
. ${setup_path}/panel/install/install_soft.sh 1 $action_type php $php_version
fi
case "${php_version}" in
    '71')
        extFile="${setup_path}/php/71/lib/php/extensions/no-debug-non-zts-20160303"
        php_path="${setup_path}/php/71/bin/php"
    ;;
    '72')
        extFile="${setup_path}/php/72/lib/php/extensions/no-debug-non-zts-20170718"
        php_path="${setup_path}/php/72/bin/php"
    ;;
    '73')
        extFile="${setup_path}/php/73/lib/php/extensions/no-debug-non-zts-20180731"
        php_path="${setup_path}/php/73/bin/php"
    ;;
    '74')
        extFile="${setup_path}/php/74/lib/php/extensions/no-debug-non-zts-20190902"
        php_path="${setup_path}/php/74/bin/php"
    ;;
esac

echo "PHP $php_version 安装成功"
echo '---------------------------------'

#安装mysql
if [ ! -d "${setup_path}/mysql" ]; then
. ${setup_path}/panel/install/install_soft.sh 1 $action_type mysql $mysql_version
fi

echo "mysql $mysql_version 安装成功"
echo '---------------------------------'

#安装redis
if [ ! -d "${setup_path}/redis" ]; then
. ${setup_path}/panel/install/install_soft.sh 0 $action_type redis $redis_version
fi

echo "redis $redis_version 安装成功"
echo '---------------------------------'

#安装php-redis 插件
if [ ! -e "${extFile}/redis.so" ]; then
. ${setup_path}/panel/install/install_soft.sh 1 $action_type redis $php_version
fi
echo 'php-redis 插件安装成功'
echo '---------------------------------'

#安装php-fileinfo 插件
if [ ! -e "${extFile}/fileinfo.so" ]; then
. ${setup_path}/panel/install/install_soft.sh 1 $action_type fileinfo $php_version
fi

echo 'php-fileinfo 插件安装成功'
echo '---------------------------------'


#安装php-swoole 插件
# if [ ! -e "${extFile}/swoole.so" ]; then
# . ${setup_path}/panel/install/install_soft.sh 1 $action_type swoole4 $php_version
# fi



# 定义函数，用于检查并修改php.ini中的disable_functions配置
function modify_disable_functions {
    local setup_path="$1"
    local php_version="$2"
    local functions_to_check=("proc_open" "pcntl_signal" "pcntl_signal_dispatch" "pcntl_fork" "pcntl_wait" "pcntl_alarm")
    local ini_file="${setup_path}/php/${php_version}/etc/php.ini"

    # 检查文件是否存在
    if [ ! -f "$ini_file" ]; then
        echo "Error: PHP configuration file not found at $ini_file"
        return 1
    fi

    # 遍历函数列表，检查并修改disable_functions配置
    for func in "${functions_to_check[@]}"; do
        # 检查函数是否已经在disable_functions列表中
        if grep -q "disable_functions.*$func" "$ini_file"; then
            echo "Info: $func is already disabled in $ini_file, removing..."

            # 使用sed命令精确地删除函数及其周围的逗号和空格
            # 注意：这里假设函数周围总是存在逗号和空格，这可能需要根据实际情况调整
            sed -i "s/, \+$func,/,/g" "$ini_file"

            # 检查命令是否成功执行
            if [ $? -eq 0 ]; then
                echo "Info: Successfully removed $func from $ini_file"
            else
                echo "Error: Failed to remove $func from $ini_file"
                return 1
            fi
        else
            echo "Info: $func is not disabled in $ini_file, skipping..."
        fi
    done

    return 0
}

# 调用函数，传入正确的参数
modify_disable_functions "${setup_path}" "$php_version"
#pcntl_signal pcntl_signal_dispatch pcntl_fork pcntl_wait pcntl_alarm 禁用函数删除
#sed -i 's/,proc_open//' ${setup_path}/php/$php_version/etc/php.ini
# 注意：请根据实际的PHP版本和安装路径调整参数

echo '修改mysql sql_mode配置'
echo '---------------------------------'
#修改mysql配置
# MySQL配置文件路径
CONFIG_FILE="/etc/my.cnf"

# 检查配置文件是否存在
if [ ! -f "$CONFIG_FILE" ]; then
    echo "MySQL配置文件 $CONFIG_FILE 不存在，请检查路径"
    exit 1
fi
# 首先检查是否存在 [mysqld] 段落
if ! grep -q "\[mysqld\]" "$CONFIG_FILE"; then
    echo "\[mysqld\]" >> "$CONFIG_FILE"
fi

# 备份原配置文件（可选）
cp "$CONFIG_FILE" "${CONFIG_FILE}.bak"
echo "MySQL配置文件已备份至 ${CONFIG_FILE}.bak"

# 使用grep检查sql_mode是否存在，如果存在，则替换其值
if grep -q "^[[:space:]]*sql_mode[[:space:]]*=" "$CONFIG_FILE"; then
    # 如果存在，修改 sql_mode 的值
    sed -i 's/^ *sql_mode *=.*$/sql_mode=NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION/' "$CONFIG_FILE"
fi

# 使用grep检查sql-mode是否存在
if grep -q "^[[:space:]]*sql-mode[[:space:]]*=" "$CONFIG_FILE"; then
    # 如果存在，修改 sql-mode 的值
    sed -i 's/^ *sql-mode *=.*$/sql-mode=NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION/' "$CONFIG_FILE"
fi
# 使用grep -E支持扩展正则表达式，同时匹配sql_mode和sql-mode，如果行不存在，则在[mysqld]段末尾添加新的sql_mode设置
if ! grep -qE "^[[:space:]]*(sql_mode|sql-mode)[[:space:]]*=" "$CONFIG_FILE"; then
    sed -i '/\[mysqld\]/a\sql_mode=NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' "$CONFIG_FILE"
fi


# 检查并显示修改后的配置文件中关于sql_mode的部分
grep 'sql-mode' "$CONFIG_FILE"
grep 'sql_mode' "$CONFIG_FILE"

# 重启mysql
echo "MySQL配置文件已更新，正在重载MySQL配置..."
/etc/init.d/mysqld reload
echo "如果没生效请重启MySQL服务。"
echo "--------------------------------------"

#修改nginx配置
project_path=$(cd `dirname $0`; pwd)
project_name="${project_path##*/}"
domain="${project_name//_/.}"
if [ -e "${setup_path}/panel/vhost/nginx/${domain}.conf" ]; then
echo -e "
server
{
    listen 80;
    server_name ${domain};
    index index.php index.html index.htm default.php default.htm default.html;
    root /www/wwwroot/${project_name}/public;

    #SSL-START SSL相关配置，请勿删除或修改下一行带注释的404规则
    #error_page 404/404.html;
    #SSL-END

    #ERROR-PAGE-START  错误页配置，可以注释、删除或修改
    #error_page 404 /404.html;
    #error_page 502 /502.html;
    #ERROR-PAGE-END

    #PHP-INFO-START  PHP引用配置，可以注释或修改
    #清理缓存规则

    location ~ /purge(/.*) {
        proxy_cache_purge cache_one \$host\$1\$is_args\$args;
    }
    #引用反向代理规则，注释后配置的反向代理将无效
    location /notice {
        proxy_pass http://127.0.0.1:40001/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header X-real-ip \$remote_addr;
        proxy_set_header X-Forwarded-For \$remote_addr;
    }
    #提示：v4.3.0 以前版本，可不用配置一下代码
    location /msg {
        proxy_pass http://127.0.0.1:40002/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header X-real-ip \$remote_addr;
        proxy_set_header X-Forwarded-For \$remote_addr;
    }

	include enable-php-$php_version.conf;
    #PHP-INFO-END

    #REWRITE-START URL重写规则引用,修改后将导致面板设置的伪静态规则失效
    include ${setup_path}/panel/vhost/rewrite/${domain}.conf;
    #REWRITE-END

    #禁止访问的文件或目录
    location ~ ^/(\.user.ini|\.htaccess|\.git|\.svn|\.project|LICENSE|README.md)
    {
        return 404;
    }

    #一键申请SSL证书验证目录相关设置
    location ~ \.well-known{
        allow all;
    }

    access_log  /www/wwwlogs/${domain}.log;
    error_log  /www/wwwlogs/${domain}.error.log;
}
" > ${setup_path}/panel/vhost/nginx/${domain}.conf
fi
echo 'nginx配置成功'
echo '---------------------------------'

# 伪静态配置
if [ -e "${setup_path}/panel/vhost/rewrite/${domain}.conf" ]; then
echo -e "
location / {
   if (!-e \$request_filename) {
   	rewrite  ^(.*)$  /index.php?s=/\$1  last;
   	break;
   }
}
" > ${setup_path}/panel/vhost/rewrite/${domain}.conf
fi
echo '伪静态配置成功'
echo '---------------------------------'

echo '重载nginx配置'
/etc/init.d/nginx reload
echo '---------------------------------'

#获取域名列表

# config_file=${setup_path}/panel/vhost/nginx/${domain}.conf
# #获取域名列表
# domain_list=$(cat $config_file|grep server_name|head -n 1|sed "s/server_name//"|sed "s/;//"|xargs)
# #获取PHP版本
# php_version=$(cat $config_file|grep 'enable-php'|grep -Eo "[0-9]+"|head -n 1)
# echo $php_version



echo '系统环境安装成功！'
echo '==============================================='
# 设置目录权限
echo '---------------------------------'
echo "设置目录权限"
chmod -R 777 runtime
chmod -R 777 .version
chmod -R 777 .env
chmod -R 777 .constant
chmod -R 777 backup
chmod -R 777 public


echo '正在启动系统定时任务、长连接、队列'
# 启动定时任务
echo '---------------------------------'
echo "启动定时任务:$php_path think timer start --d"
$php_path think timer start --d

# 启动长连接
echo '---------------------------------'
echo "启动长连接:$php_path think workerman start --d"
$php_path think workerman start --d

# 启动队列
echo '---------------------------------'
echo "启动队列:$php_path think queue:listen --queue"
$php_path think queue:listen --queue


# 操作说明，进入程序根目录运行 /bin/bash baota.sh
