{include file="public/frame_head"}
<style type="text/css" media="screen">
    td img{width: 35px; height: 35px;}
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">用户名</th>
                            <th class="text-center">用户头像</th>
                            <th class="text-center">领取时间</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">{$vo.nickname}</td>
                            <td class="text-center"><img src="{$vo.avatar}" class="head_image" data-image="{$vo.avatar}" width="35" height="35"></td>
                            <td class="text-center">{$vo.add_time}</td>
                        </tr>
                        {/volist}
                        </tbody>
                    </table>
                </div>
                {include file="public/inner_page"}
            </div>
        </div>
    </div>
    <script>
        $('td img').on('click',function (e) {
            var image = $(this).attr("src");
            $eb.openImage(image);
        })
    </script>
</div>
{include file="public/inner_footer"}
