<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <title>CRMEB管理系统</title>
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="{__FRAME_PATH}css/bootstrap.min.css" rel="stylesheet">
    <link href="{__FRAME_PATH}css/font-awesome.min.css" rel="stylesheet">
    <link href="{__FRAME_PATH}css/animate.min.css" rel="stylesheet">
    <link href="{__FRAME_PATH}css/style.min.css" rel="stylesheet">
</head>
<body class="fixed-sidebar full-height-layout gray-bg" style="overflow:hidden">
<div id="wrapper">
    <!--左侧导航开始-->
    <nav class="navbar-default navbar-static-side" role="navigation">
        <div class="nav-close"><i class="fa fa-times-circle"></i>
        </div>
        <div class="sidebar-collapse">
            <ul class="nav" id="side-menu">
                <li class="nav-header">
                    <div class="dropdown profile-element admin_open">
                        <span>
                            <img alt="image" class="imgbox" src="{$site_logo}" onerror="javascript:this.src='http://shop.crmeb.net/public/system/images/admin_logo.png';"/>
                        </span>
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="clear" style="margin-top: 20px;">
                               <span class="block m-t-xs"><strong class="font-bold">{$_admin['real_name']}</strong></span>
                                <span class="text-muted text-xs block">{$role_name.role_name ? $role_name.role_name : '管理员'}<b class="caret"></b></span>
                            </span>
                        </a>
                        <ul class="dropdown-menu animated fadeInRight m-t-xs">
                            <li><a class="J_menuItem admin_close" href="{:Url('setting.systemAdmin/adminInfo')}">个人资料</a>
                            </li>
                            <li><a class="admin_close" target="_blank" href="http://www.crmeb.com/">联系我们</a>
                            </li>
                            <li class="divider"></li>
                            <li><a href="{:Url('Login/logout')}">安全退出</a>
                            </li>
                        </ul>
                    </div>
                    <div class="logo-element">CB
                    </div>
                </li>
                <!--  菜单  -->
                {volist name="menuList" id="menu"}
                <?php if(isset($menu['child']) && count($menu['child']) > 0){ ?>
                    <li>
                        <a href="#"><i class="fa fa-{$menu.icon}"></i> <span class="nav-label">{$menu.menu_name}</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            {volist name="menu.child" id="child"}
                            <li>
                                <?php if(isset($child['child']) && count($child['child']) > 0){ ?>
                                    <a href="#"><i class="fa fa-{$child.icon}"></i>{$child.menu_name}<span class="fa arrow"></span></a>
                                    <ul class="nav nav-third-level">
                                        {volist name="child.child" id="song"}
                                        <li><a class="J_menuItem" href="{$song.url}"><i class="fa fa-{$song.icon}"></i> {$song.menu_name}</a></li>
                                        {/volist}
                                    </ul>
                                <?php }else{ ?>
                                    <a class="J_menuItem" href="{$child.url}"><i class="fa fa-{$child.icon}"></i>{$child.menu_name}</a>
                                <?php } ?>
                            </li>
                            {/volist}
                        </ul>
                    </li>
                <?php } ?>
                {/volist}
            </ul>
        </div>
    </nav>
    <!--左侧导航结束-->
    <!--右侧部分开始-->
    <div id="page-wrapper" class="gray-bg dashbard-1">
        <div class="row content-tabs" @touchmove.prevent>
            <button class="roll-nav roll-left navbar-minimalize minimalize-styl-2 btn" style="padding: 0;margin: 0;"><i class="fa fa-bars"></i></button>
            <button class="roll-nav J_tabLeft" style="right: 241px;"><i class="fa fa-backward"></i></button>
            <nav class="page-tabs J_menuTabs">
                <div class="page-tabs-content">
                    <a href="javascript:;" class="active J_menuTab" data-id="{:Url('Index/main')}">首页</a>
                </div>
            </nav>
            <button class="roll-nav roll-right J_tabRight"><i class="fa fa-forward"></i>
            </button>
            <div class="btn-group roll-nav roll-right">
                <button class="dropdown J_tabClose" data-toggle="dropdown">关闭操作<span class="caret"></span>
                </button>
                <ul role="menu" class="dropdown-menu dropdown-menu-right">
                    <li class="J_tabShowActive"><a>定位当前选项卡</a>
                    </li>
                    <li class="divider"></li>
                    <li class="J_tabCloseAll"><a>关闭全部选项卡</a>
                    </li>
                    <li class="J_tabCloseOther"><a>关闭其他选项卡</a>
                    </li>
                </ul>
            </div>
            <a href="javascript:void(0);" class="roll-nav roll-right J_tabReply"><i class="fa fa-reply"></i> 返回</a>
            <a href="javascript:void(0);" class="roll-nav roll-right J_tabRefresh"><i class="fa fa-refresh"></i> 刷新</a>
        </div>
        <!--内容展示模块-->
        <div class="row J_mainContent" id="content-main">
            <iframe class="J_iframe" name="iframe_crmeb_main" width="100%" height="100%" src="{:Url('Index/main')}" frameborder="0" data-id="{:Url('Index/main')}" seamless></iframe>
        </div>
        <!--底部版权-->
        <div class="footer"  @touchmove.prevent>
            <div class="pull-right">&copy; 2014-2018 <a href="http://www.crmeb.com/" target="_blank">西安众邦科技</a>
            </div>
        </div>
    </div>
    <!--右侧部分结束-->
</div>
<div id="vm"></div>
<script src="{__FRAME_PATH}js/jquery.min.js"></script>
<script src="{__FRAME_PATH}js/bootstrap.min.js"></script>
<script src="{__FRAME_PATH}js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="{__FRAME_PATH}js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="{__FRAME_PATH}js/plugins/layer/layer.min.js"></script>
<script src="{__FRAME_PATH}js/hplus.min.js"></script>
<script src="{__FRAME_PATH}js/contabs.min.js"></script>
<script src="{__FRAME_PATH}js/plugins/pace/pace.min.js"></script>
{include file="public/style"}
<script src="{__ADMIN_PATH}js/index.js"></script>
</body>
</html>
