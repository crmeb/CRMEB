{extend name="public/container"}
{block name="content"}
<div class="ibox-content order-info">

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <img src="{$spread.avatar}" width="50" height="50" style="border-radius: 60px;" />
                    <span style="font-size: 16px">{$spread.nickname}</span>
                </div>
                <div class="panel-body">
                    <div class="row show-grid">
                        <div class="col-xs-6" style="color: #ff0005">余额：¥ {$spread.now_money}</div>
                        <div class="col-xs-6">UID：{$spread.uid}</div>
                        <div class="col-xs-6" style="color: green">佣金：¥ {$spread.brokerage_price}</div>
                        <div class="col-xs-6">真实姓名：{$spread.real_name}</div>
                        <div class="col-xs-6">身份证:：{$spread.card_id}</div>
                        <div class="col-xs-6">手机号码：{$spread.phone}</div>
                        <div class="col-xs-6">生日：{$spread.birthday}</div>
                        <div class="col-xs-6">积分：{$spread.integral}</div>
                        <div class="col-xs-6">用户备注：{$spread.mark}</div>
                        <div class="col-xs-6">最后登录时间：{$spread.last_time|date="Y/m/d H:i"}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>
{/block}
{block name="script"}

{/block}
