#!/bin/bash

# CRMEB Docker 开发环境管理脚本
# 用法: ./run.sh [install|start|restart|stop|delete|logs|ps]

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

# OSC 8 hyperlink：在 VS Code / Trae / iTerm2 / macOS Terminal 等终端里渲染为可点击链接
hyperlink() { printf '\e]8;;%s\a%s\e]8;;\a' "$1" "${2:-$1}"; }

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
    echo "  0) 退出"
    echo ""
    read -p "请选择操作 (0-8): " choice
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
        -h|--help) show_help ;;
        *)
            print_error "未知命令: $1"
            show_help
            exit 1
            ;;
    esac
fi

exit 0
