#!/bin/bash

# CRMEB Docker 开发环境管理脚本
# 用法: ./run.sh [install|start|restart|stop|delete|logs|ps]
#       ./run.sh [dev|admin:dev|admin:build|uni:dev|uni:build]

# 确保从项目根目录执行
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_info()  { echo -e "${GREEN}[INFO]${NC}  $1"; }
print_warn()  { echo -e "${YELLOW}[WARN]${NC}  $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# OSC 8 hyperlink + ANSI 青色下划线（对齐 Vite 的 URL 样式）：
# 支持 OSC 8 的终端（VS Code / Trae / iTerm2 等）可点击，不支持的也会渲染为青色文本
hyperlink() { printf '\e]8;;%s\a\e[36;4m%s\e[0m\e]8;;\a' "$1" "${2:-$1}"; }

# 帮助信息
show_help() {
    echo -e "${GREEN}CRMEB Docker 管理脚本${NC}"
    echo ""
    echo "用法: $0 [命令]"
    echo ""
    echo "命令说明:"
    echo "  install   清理所有数据并重新安装启动"
    echo "  start     启动容器"
    echo "  restart   重启容器"
    echo "  stop      停止并删除容器"
    echo "  delete    停止并清理所有数据"
    echo "  logs      查看日志"
    echo "  ps        查看容器状态"
    echo "  dev       同时启动管理后台前端与 uniapp 移动端开发服务器 (H5)"
    echo "  admin:dev    管理后台前端 - 开发测试启动"
    echo "  admin:build  管理后台前端 - 编译打包到程序 public 目录"
    echo "  uni:dev      uniapp 移动端 - 开发测试启动 (H5)"
    echo "  uni:build    uniapp 移动端 - 编译打包到程序 public 对应目录"
    echo "  -h        显示帮助"
}

# 检查 docker-compose 是否可用
check_docker() {
    if ! command -v docker-compose &> /dev/null; then
        print_error "docker-compose 未安装，请先安装 Docker Desktop 或 docker-compose"
        exit 1
    fi
    if [ ! -f "docker-compose.yml" ]; then
        print_error "找不到 docker-compose.yml，请确保从项目根目录运行此脚本"
        exit 1
    fi
}

# 清理可能冲突的网络
cleanup_network() {
    print_info "清理冲突网络..."
    for net in $(docker network ls --format "{{.Name}}" | grep -E "(app_net|crmeb)"); do
        docker network rm "$net" 2>/dev/null && echo "  已删除网络: $net"
    done
    docker network prune -f 2>/dev/null
}

# 清理数据文件
cleanup() {
    print_info "开始清理数据..."

    # 设置目录权限
    chmod -R 777 ./backend/runtime 2>/dev/null
    chmod -R 777 ./backend/public 2>/dev/null
    chmod 777 ./backend/.env ./backend/.version ./backend/.constant 2>/dev/null

    # 删除 install.lock
    if [ -f "./backend/public/install.lock" ]; then
        rm -f ./backend/public/install.lock
        print_info "已删除 install.lock"
    fi

    # 清理 runtime 目录（保留目录结构）
    if [ -d "./backend/runtime" ]; then 
        find ./backend/runtime -mindepth 1 -delete
        print_info "已清理 runtime 目录"
    fi

    # 清理 MySQL 数据目录
    if [ -d "./docker/mysql/data" ]; then
        find ./docker/mysql/data -mindepth 1 -delete
        print_info "已清理 MySQL 数据目录"
    fi

    # 清理 MySQL 日志目录
    if [ -d "./docker/mysql/log" ]; then
        find ./docker/mysql/log -mindepth 1 -delete
        print_info "已清理 MySQL 日志目录"
    fi

    # 清理 Nginx 日志目录
    if [ -d "./docker/nginx/log" ]; then
        find ./docker/nginx/log -mindepth 1 -delete
        print_info "已清理 Nginx 日志目录"
    fi

    print_info "清理完成"
}

# 停止并删除容器
do_stop() {
    check_docker
    print_info "停止并删除容器..."
    docker-compose down
    print_info "已停止"
}

# 启动容器
do_start() {
    check_docker
    cleanup_network
    print_info "启动容器..."
    docker-compose up -d
    echo ""
    docker-compose ps
    echo ""
    print_info "访问地址: $(hyperlink 'http://localhost:8011')"
}

# 安装（清理数据并启动）
do_install() {
    check_docker
    print_warn "此操作将清除所有数据并重新安装，请确认..."
    read -p "确认执行？(y/N): " confirm
    [ "$confirm" != "y" ] && [ "$confirm" != "Y" ] && echo "已取消" && exit 0

    print_info "停止旧容器..."
    docker-compose down 2>/dev/null

    cleanup
    cleanup_network

    print_info "安装并启动环境..."
    docker-compose up -d
    echo ""
    docker-compose ps
    echo ""
    print_info "安装完成！请访问 $(hyperlink 'http://localhost:8011') 进行安装向导"
}

# 重启容器
do_restart() {
    check_docker
    print_info "重启容器..."
    docker-compose restart
    print_info "重启完成"
}

# 删除容器和数据
do_delete() {
    check_docker
    print_warn "此操作将删除所有容器和数据，请确认..."
    read -p "确认执行？(y/N): " confirm
    [ "$confirm" != "y" ] && [ "$confirm" != "Y" ] && echo "已取消" && exit 0

    docker-compose down 2>/dev/null
    cleanup
    print_info "已删除完成"
}

# 查看日志
do_logs() {
    check_docker
    docker-compose logs -f
}

# 查看状态
do_status() {
    check_docker
    docker-compose ps
}

# ========== 前端命令（管理后台 / uniapp 移动端） ==========

# 检查 node/npm 是否可用
check_node() {
    if ! command -v npm &> /dev/null; then
        print_error "未检测到 npm，请先安装 Node.js (>=18)"
        exit 1
    fi
}

# 依赖缺失时自动安装
ensure_deps() {
    local dir="$1"
    if [ ! -d "$dir/node_modules" ]; then
        print_warn "$dir 缺少 node_modules，先执行 npm install..."
        ( cd "$dir" && npm install )
    fi
}

# 管理后台前端：开发测试启动
do_admin_dev() {
    check_node
    ensure_deps "./frontend/admin"
    print_info "启动管理后台前端开发服务器: $(hyperlink 'http://localhost:1619')"
    print_info "/adminapi 代理到 http://localhost:8011，请确保后端服务已启动"
    ( cd ./frontend/admin && npm run dev )
}

# 管理后台前端：编译打包并发布到 backend/public/admin
do_admin_build() {
    check_node
    ensure_deps "./frontend/admin"
    print_info "编译管理后台前端（build:all = 主包 + 扩展 UMD）..."
    ( cd ./frontend/admin && npm run build:all ) || { print_error "编译失败"; exit 1; }
    print_info "发布到 backend/public/admin ..."
    rm -rf ./backend/public/admin
    mkdir -p ./backend/public/admin
    cp -R ./frontend/admin/dist/. ./backend/public/admin/
    print_info "完成！访问地址: $(hyperlink 'http://localhost:8011/admin')"
}

# uniapp 移动端：开发测试启动（H5）
do_uni_dev() {
    check_node
    ensure_deps "./frontend/uni-app"
    print_info "启动 uniapp H5 开发服务器（默认 http://localhost:5173，以终端实际输出为准）"
    ( cd ./frontend/uni-app && npm run dev:h5 )
}

# uniapp 移动端：编译打包（H5）并发布到 backend/public/ 对应目录
do_uni_build() {
    check_node
    ensure_deps "./frontend/uni-app"
    print_info "编译 uniapp H5..."
    ( cd ./frontend/uni-app && npm run build:h5 ) || { print_error "编译失败"; exit 1; }
    print_info "发布到 backend/public ..."
    # index.html / assets / pages / static 均为 H5 构建产物（uni 会把 src/pages/**/static 拷入产物 pages/）
    rm -rf ./backend/public/assets ./backend/public/pages ./backend/public/static
    cp -R ./frontend/uni-app/dist/build/h5/. ./backend/public/
    print_info "完成！访问地址: $(hyperlink 'http://localhost:8011')"
}

# 同时启动管理后台前端与 uniapp H5 开发服务器
do_dev_all() {
    check_node
    ensure_deps "./frontend/admin"
    ensure_deps "./frontend/uni-app"
    print_info "管理后台前端: $(hyperlink 'http://localhost:1619')（/adminapi 代理到 http://localhost:8011，请确保后端服务已启动）"
    print_info "uniapp H5: 默认 http://localhost:5173（以终端实际输出为准）"
    print_warn "日志按 [admin] / [uni] 前缀区分来源；按 Ctrl+C 可同时停止两个服务"
    # 收到 Ctrl+C 时先解除 trap，再向整个进程组发信号，确保 npm/vite 一并退出
    trap 'trap - INT TERM; kill 0' INT TERM
    ( cd ./frontend/admin && FORCE_COLOR=1 npm run dev 2>&1 | sed 's/^/[admin] /' ) &
    ( cd ./frontend/uni-app && FORCE_COLOR=1 npm run dev:h5 2>&1 | sed 's/^/[uni]   /' ) &
    wait
}

# 交互式菜单
show_menu() {
    echo ""
    echo -e "${GREEN}===== CRMEB Docker 管理 =====${NC}"
    echo "  1) 安装并启动"
    echo "  2) 启动容器"
    echo "  3) 重启容器"
    echo "  4) 停止并删除容器"
    echo "  5) 删除容器和数据"
    echo "  6) 查看日志"
    echo "  7) 查看容器状态"
    echo "  8) 显示帮助"
    echo ""
    echo -e "${GREEN}----- 开发调试（同时启动多端） -----${NC}"
    echo "  31) 同时启动 管理后台 + 移动端 开发服务器"
    echo ""
    echo -e "${GREEN}----- 管理后台前端 (frontend/admin) -----${NC}"
    echo "  11) 开发测试启动"
    echo "  12) 编译打包到程序 public 目录"
    echo ""
    echo -e "${GREEN}----- uniapp 移动端 (frontend/uni-app) -----${NC}"
    echo "  21) 开发测试启动 (H5)"
    echo "  22) 编译打包到程序 public 对应目录"
    echo ""
    echo "  0) 退出"
    echo ""
    read -p "请选择操作 (0-8, 11, 12, 21, 22, 31): " choice
    echo ""
    case $choice in
        1) do_install ;;
        2) do_start ;;
        3) do_restart ;;
        4) do_stop ;;
        5) do_delete ;;
        6) do_logs ;;
        7) do_status ;;
        8) show_help; show_menu ;;
        11) do_admin_dev ;;
        12) do_admin_build ;;
        21) do_uni_dev ;;
        22) do_uni_build ;;
        31) do_dev_all ;;
        0) echo "已退出"; exit 0 ;;
        *) print_error "无效选项，请重试"; show_menu ;;
    esac
}

# 主逻辑
if [ $# -eq 0 ]; then
    show_menu
else
    case "$1" in
        install)  do_install ;;
        start|up) do_start ;;
        restart)  do_restart ;;
        stop|down) do_stop ;;
        delete)   do_delete ;;
        logs)     do_logs ;;
        ps|status) do_status ;;
        admin:dev)   do_admin_dev ;;
        admin:build) do_admin_build ;;
        uni:dev)     do_uni_dev ;;
        uni:build)   do_uni_build ;;
        dev|dev:all) do_dev_all ;;
        -h|--help) show_help ;;
        *)
            print_error "未知命令: $1"
            show_help
            exit 1
            ;;
    esac
fi

exit 0
