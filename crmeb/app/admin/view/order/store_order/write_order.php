{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <!--搜索条件-->
        <div class="layui-col-md12" v-cloak="">
            <div class="layui-card">
                <div class="layui-card-body">
                    <div class="layui-form-item" style="padding-top: 20px;">
                        <input style="height: 50px;line-height: 1.5;display: inline;width: 80%;" v-model="verify_code" type="text" name="title" lay-verify="title" autocomplete="off" placeholder="请输入核销码" class="layui-input">
                        <button style="height: 50px;border-radius: 15px;" type="button" class="layui-btn layui-btn-normal" @click="verify">验证</button>
                    </div>
                    <fieldset class="layui-elem-field" v-if="orderInfo.uid">
                        <legend>订单信息</legend>
                        <div class="layui-field-box">
                            <div class="layui-form">
                                <table class="layui-table">
                                    <tbody>
                                        <tr>
                                            <td>订 单 号</td>
                                            <td>{{orderInfo.order_id}}</td>
                                        </tr>
                                        <tr>
                                            <td>购买金额</td>
                                            <td>{{orderInfo.pay_price}}</td>
                                        </tr>
                                        <tr>
                                            <td>购买用户</td>
                                            <td>{{orderInfo.nickname}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </fieldset>
                    <div class="layui-form-item" style="padding-top: 50px;text-align: center;padding-bottom: 30px;">
                        <button type="button" class="layui-btn layui-btn-normal" @click="confirm">立即核销</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name='script'}
<script>
    require(['vue'],function(Vue) {
        new Vue({
            el: "#app",
            data: {
                verify_code:'',
                orderInfo:{},
                is_confirm:false,
            },
            methods:{
                verify:function(){
                    var that = this;
                    var reg = /[0-9]{12}/;
                    if(!this.verify_code) return layList.msg('请填写核销码!');
                    if(!reg.test(this.verify_code)) return layList.msg('请填写正确的核销码！');
                    layList.baseGet(layList.U({a:'write_order',q:{verify_code:this.verify_code,is_confirm:0}}),function (res) {
                        that.orderInfo = res.data;
                        that.is_confirm = true;
                    },function (res) {
                        layList.msg(res.msg);
                    });
                },
                confirm:function () {
                    var that = this;
                    if(that.is_confirm === false) return layList.msg('请先验证订单！');
                    layList.baseGet(layList.U({a:'write_order',q:{verify_code:that.verify_code,is_confirm:1}}),function (res) {
                        layList.msg(res.msg,function () {
                            parent.$(".J_iframe:visible")[0].contentWindow.formReload();
                            parent.layer.close(parent.layer.getFrameIndex(window.name));
                        });
                    },function (res) {
                        layList.msg(res.msg);
                    });
                }
            },
            mounted:function () {

            }
        })
    })
</script>
{/block}