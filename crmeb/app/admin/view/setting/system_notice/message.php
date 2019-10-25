{extend name="public/container"}
{block name="head_top"}
<link href="{__FRAME_PATH}css/plugins/iCheck/custom.css" rel="stylesheet">
{/block}
{block name="content"}
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12 animated fadeInRight">
            <div class="mail-box-header">

                <form method="get" action="index.html" class="pull-right mail-search">
                    <div class="input-group">
                        <input type="text" class="form-control input-sm" name="search" placeholder="搜索邮件标题，正文等">
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-sm btn-primary">
                                搜索
                            </button>
                        </div>
                    </div>
                </form>
                <h2>
                    收件箱 (16)
                </h2>
                <div class="mail-tools tooltip-demo m-t-md">
                    <div class="btn-group pull-right">
                        <button class="btn btn-white btn-sm"><i class="fa fa-arrow-left"></i>
                        </button>
                        <button class="btn btn-white btn-sm"><i class="fa fa-arrow-right"></i>
                        </button>

                    </div>
                    <button class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="left" title="刷新邮件列表"><i class="fa fa-refresh"></i> 刷新</button>
                    <button class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="标为已读"><i class="fa fa-eye"></i>
                    </button>
                    <button class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="标为重要"><i class="fa fa-exclamation"></i>
                    </button>
                    <button class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="标为垃圾邮件"><i class="fa fa-trash-o"></i>
                    </button>

                </div>
            </div>
            <div class="mail-box">

                <table class="table table-hover table-mail">
                    <tbody>
                    <tr class="unread">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks">
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">支付宝</a>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">支付宝提醒</a>
                        </td>
                        <td class=""><i class="fa fa-paperclip"></i>
                        </td>
                        <td class="text-right mail-date">昨天 10:20</td>
                    </tr>
                    <tr class="unread">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks" checked>
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">Amaze UI</a>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">Amaze UI Beta2 发布</a>
                        </td>
                        <td class=""></td>
                        <td class="text-right mail-date">上午10:57</td>
                    </tr>
                    <tr class="read">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks">
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">WordPress</a> <span class="label label-warning pull-right">验证邮件</span>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">wp-user-frontend-pro v2.1.9</a>
                        </td>
                        <td class=""></td>
                        <td class="text-right mail-date">上午9:21</td>
                    </tr>
                    <tr class="read">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks">
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">淘宝网</a>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">史上最全！淘宝双11红包疯抢攻略！</a>
                        </td>
                        <td class=""></td>
                        <td class="text-right mail-date">中午12:24</td>
                    </tr>
                    <tr class="read">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks">
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">淘宝网</a> <span class="label label-danger pull-right">AD</span>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">亲，双11来啦！帮你挑货，还送你4999元红包！仅此一次！</a>
                        </td>
                        <td class=""><i class="fa fa-paperclip"></i>
                        </td>
                        <td class="text-right mail-date">上午6:48</td>
                    </tr>
                    <tr class="read">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks">
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">支付宝</a>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">支付宝提醒</a>
                        </td>
                        <td class=""><i class="fa fa-paperclip"></i>
                        </td>
                        <td class="text-right mail-date">昨天 10:20</td>
                    </tr>
                    <tr class="read">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks">
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">Amaze UI</a>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">Amaze UI Beta2 发布</a>
                        </td>
                        <td class=""></td>
                        <td class="text-right mail-date">上午10:57</td>
                    </tr>
                    <tr class="read">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks">
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">WordPress</a> <span class="label label-warning pull-right">验证邮件</span>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">wp-user-frontend-pro v2.1.9</a>
                        </td>
                        <td class=""></td>
                        <td class="text-right mail-date">上午9:21</td>
                    </tr>
                    <tr class="read">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks">
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">淘宝网</a>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">史上最全！淘宝双11红包疯抢攻略！</a>
                        </td>
                        <td class=""></td>
                        <td class="text-right mail-date">中午12:24</td>
                    </tr>
                    <tr class="read">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks">
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">淘宝网</a> <span class="label label-danger pull-right">AD</span>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">亲，双11来啦！帮你挑货，还送你4999元红包！仅此一次！</a>
                        </td>
                        <td class=""><i class="fa fa-paperclip"></i>
                        </td>
                        <td class="text-right mail-date">上午6:48</td>
                    </tr>
                    <tr class="read">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks">
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">支付宝</a>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">支付宝提醒</a>
                        </td>
                        <td class=""><i class="fa fa-paperclip"></i>
                        </td>
                        <td class="text-right mail-date">昨天 10:20</td>
                    </tr>
                    <tr class="read">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks">
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">Amaze UI</a>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">Amaze UI Beta2 发布</a>
                        </td>
                        <td class=""></td>
                        <td class="text-right mail-date">上午10:57</td>
                    </tr>
                    <tr class="read">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks">
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">WordPress</a> <span class="label label-warning pull-right">验证邮件</span>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">wp-user-frontend-pro v2.1.9</a>
                        </td>
                        <td class=""></td>
                        <td class="text-right mail-date">上午9:21</td>
                    </tr>
                    <tr class="read">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks">
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">淘宝网</a>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">史上最全！淘宝双11红包疯抢攻略！</a>
                        </td>
                        <td class=""></td>
                        <td class="text-right mail-date">中午12:24</td>
                    </tr>
                    <tr class="read">
                        <td class="check-mail">
                            <input type="checkbox" class="i-checks">
                        </td>
                        <td class="mail-ontact"><a href="mail_detail.html">淘宝网</a> <span class="label label-danger pull-right">AD</span>
                        </td>
                        <td class="mail-subject"><a href="mail_detail.html">亲，双11来啦！帮你挑货，还送你4999元红包！仅此一次！</a>
                        </td>
                        <td class=""><i class="fa fa-paperclip"></i>
                        </td>
                        <td class="text-right mail-date">上午6:48</td>
                    </tr>
                    </tbody>
                </table>


            </div>
        </div>
    </div>
</div>
{/block}
