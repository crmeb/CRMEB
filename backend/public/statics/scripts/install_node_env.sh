#!/bin/bash
#
# CRMEB Node.js 环境一键安装脚本
# 支持: CentOS, Ubuntu, Debian, Alpine, macOS
#

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# sudo 命令（如果是 root 则为空）
SUDO_CMD=""

# 日志函数
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 检查 root 权限
check_root() {
    if [ "$EUID" -eq 0 ]; then
        log_info "当前以 root 用户运行"
        SUDO_CMD=""
    else
        log_warn "当前不是 root 用户，将使用 sudo 执行安装命令"
        # 检查 sudo 是否可用
        if command -v sudo &> /dev/null; then
            SUDO_CMD="sudo"
            # 测试 sudo 权限
            if ! sudo -n true 2>/dev/null; then
                log_info "请输入您的密码以继续安装..."
                sudo true
            fi
        else
            log_error "sudo 未安装，请以 root 用户运行此脚本"
            exit 1
        fi
    fi
}

# 检测操作系统
detect_os() {
    if [[ "$OSTYPE" == "darwin"* ]]; then
        OS_TYPE="macos"
        OS_VERSION=$(sw_vers -productVersion)
    elif [ -f /etc/os-release ]; then
        . /etc/os-release
        OS_TYPE=$(echo "$ID" | tr '[:upper:]' '[:lower:]')
        OS_VERSION="$VERSION_ID"
    elif [ -f /etc/redhat-release ]; then
        OS_TYPE="centos"
        OS_VERSION=$(cat /etc/redhat-release | grep -oE '[0-9]+\.[0-9]+' | head -1)
    else
        OS_TYPE="unknown"
        OS_VERSION="unknown"
    fi
    
    log_info "检测到操作系统: $OS_TYPE $OS_VERSION"
}

# 检查是否已安装 Node.js
check_node() {
    if command -v node &> /dev/null; then
        NODE_VERSION=$(node -v 2>/dev/null | sed 's/v//')
        log_info "Node.js 已安装，版本: $NODE_VERSION"
        return 0
    fi
    return 1
}

# 检查是否已安装 npm
check_npm() {
    if command -v npm &> /dev/null; then
        NPM_VERSION=$(npm -v 2>/dev/null)
        log_info "npm 已安装，版本: $NPM_VERSION"
        return 0
    fi
    return 1
}

# 检查是否已安装 miniprogram-ci
check_miniprogram_ci() {
    if command -v miniprogram-ci &> /dev/null; then
        CI_VERSION=$(miniprogram-ci --version 2>/dev/null | head -1)
        log_info "miniprogram-ci 已安装，版本: $CI_VERSION"
        return 0
    fi
    return 1
}

# 在 CentOS 上安装 Node.js
install_node_centos() {
    # 检测 CentOS 版本
    local centos_version="7"
    if [ -f /etc/redhat-release ]; then
        centos_version=$(cat /etc/redhat-release | grep -oE '[0-9]+' | head -1)
    fi
    
    # CentOS 7 只能使用 Node.js 16.x（glibc 2.17 限制）
    if [ "$centos_version" = "7" ]; then
        log_warn "检测到 CentOS 7，由于系统库版本限制，将安装 Node.js 16.x"
        log_warn "建议升级到 CentOS 8/9 以获得更新版本的 Node.js"
        
        if command -v curl &> /dev/null; then
            curl -fsSL https://rpm.nodesource.com/setup_16.x | $SUDO_CMD bash -
            $SUDO_CMD yum install -y nodejs
        else
            $SUDO_CMD yum install -y curl
            curl -fsSL https://rpm.nodesource.com/setup_16.x | $SUDO_CMD bash -
            $SUDO_CMD yum install -y nodejs
        fi
    else
        log_info "正在 CentOS $centos_version 上安装 Node.js 20.x (LTS)..."
        
        if command -v curl &> /dev/null; then
            curl -fsSL https://rpm.nodesource.com/setup_20.x | $SUDO_CMD bash -
            $SUDO_CMD yum install -y nodejs
        else
            $SUDO_CMD yum install -y curl
            curl -fsSL https://rpm.nodesource.com/setup_20.x | $SUDO_CMD bash -
            $SUDO_CMD yum install -y nodejs
        fi
    fi
}

# 在 Ubuntu/Debian 上安装 Node.js
install_node_debian() {
    log_info "正在 $OS_TYPE 上安装 Node.js 20.x (LTS)..."
    
    $SUDO_CMD apt-get update
    
    # 使用 NodeSource 仓库安装 Node.js 20.x
    if command -v curl &> /dev/null; then
        curl -fsSL https://deb.nodesource.com/setup_20.x | $SUDO_CMD bash -
        $SUDO_CMD apt-get install -y nodejs
    else
        $SUDO_CMD apt-get install -y curl
        curl -fsSL https://deb.nodesource.com/setup_20.x | $SUDO_CMD bash -
        $SUDO_CMD apt-get install -y nodejs
    fi
}

# 在 Alpine 上安装 Node.js
install_node_alpine() {
    log_info "正在 Alpine 上安装 Node.js..."
    $SUDO_CMD apk update
    $SUDO_CMD apk add nodejs npm
}

# 在 macOS 上安装 Node.js
install_node_macos() {
    log_info "正在 macOS 上安装 Node.js..."
    
    # 检查是否有 Homebrew
    if command -v brew &> /dev/null; then
        brew install node
    else
        log_error "请先安装 Homebrew，然后重试"
        log_info "安装命令: /bin/bash -c \"\$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)\""
        exit 1
    fi
}

# 安装 Node.js
install_node() {
    if check_node; then
        log_info "Node.js 已安装，跳过安装步骤"
        return 0
    fi
    
    case "$OS_TYPE" in
        centos|rhel|fedora|rocky|almalinux)
            install_node_centos
            ;;
        ubuntu|debian)
            install_node_debian
            ;;
        alpine)
            install_node_alpine
            ;;
        macos)
            install_node_macos
            ;;
        *)
            log_error "不支持的操作系统: $OS_TYPE"
            log_info "请手动安装 Node.js: https://nodejs.org/"
            exit 1
            ;;
    esac
    
    # 验证安装
    if check_node && check_npm; then
        log_info "Node.js 安装成功！"
    else
        log_error "Node.js 安装失败"
        exit 1
    fi
}

# 安装 miniprogram-ci
install_miniprogram_ci() {
    if check_miniprogram_ci; then
        log_info "miniprogram-ci 已安装，跳过安装步骤"
        return 0
    fi
    
    log_info "正在全局安装 miniprogram-ci..."
    
    # 设置 npm 镜像（可选，加速国内下载）
    npm config set registry https://registry.npmmirror.com
    
    # 全局安装 miniprogram-ci
    $SUDO_CMD npm install -g miniprogram-ci
    
    # 验证安装
    if check_miniprogram_ci; then
        log_info "miniprogram-ci 安装成功！"
    else
        log_error "miniprogram-ci 安装失败"
        exit 1
    fi
}

# 显示安装结果
show_result() {
    echo ""
    echo "=============================================="
    echo -e "${GREEN}安装完成！${NC}"
    echo "=============================================="
    echo ""
    
    if check_node; then
        echo -e "Node.js:        ${GREEN}✓${NC} $(node -v)"
    else
        echo -e "Node.js:        ${RED}✗${NC} 未安装"
    fi
    
    if check_npm; then
        echo -e "npm:            ${GREEN}✓${NC} v$(npm -v)"
    else
        echo -e "npm:            ${RED}✗${NC} 未安装"
    fi
    
    if check_miniprogram_ci; then
        echo -e "miniprogram-ci: ${GREEN}✓${NC} $(miniprogram-ci --version 2>/dev/null | head -1)"
    else
        echo -e "miniprogram-ci: ${RED}✗${NC} 未安装"
    fi
    
    echo ""
}

# 主函数
main() {
    echo ""
    echo "=============================================="
    echo "  CRMEB Node.js 环境一键安装脚本"
    echo "=============================================="
    echo ""
    
    # 检查 root 权限
    check_root
    
    # 检测操作系统
    detect_os
    
    # 安装 Node.js
    install_node
    
    # 安装 miniprogram-ci
    install_miniprogram_ci
    
    # 显示结果
    show_result
    
    exit 0
}

# 执行主函数
main "$@"
