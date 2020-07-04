{include file="public/frame_head"}
<style>
    .panel button{display: block;margin:5px;}
    .clear_tit span{font-size: 12px; color: #ED4014;margin: 15px 0;}
    .clear_box{border: 1px solid #DADFE6;border-radius: 3px;display: flex;flex-direction: column;align-items: center;padding: 30px 10px;box-sizing:border-box}
    .clear_box_sp1{font-size: 16px;color: #000000;display: block;}
    .clear_box_sp2{font-size: 14px;color: #808695;display: block;margin: 12px 0;}
    .layui-btn-danger {background-color: #FF5722;}
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <blockquote class="layui-elem-quote layui-quote-nm">
                        清除数据请谨慎，清除就无法恢复哦！
                    </blockquote>
                    <div class="clear_box layui-col-md3 layui-col-lg3 layui-col-sm3" v-for="item in clearData">
                        <span class="clear_box_sp1">{{item.name}}</span>
                        <span class="clear_box_sp2" v-if="item.info">{{item.info}}</span>
                        <button type="primary" :class="item.class ? item.class : 'layui-btn-danger' " class="layui-btn cleardata" @click="unDate(item)">{{item.button ? item.button : '立即清理'}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--<button type="button" class="btn btn-w-m btn-danger btn-primary creatuser" data-url="{:Url('system.SystemCleardata/userdate')}">创建前台用户用户名：crmeb 密码：123456</button>-->
</div>
<script type="text/javascript" src="/static/plug/vue/dist/vue.min.js"></script>
<script>
    new Vue({
        data:{
            clearData:[
                {
                    name:'更换域名',
                    info:'替换所有本地上传的图片域名',
                    url:"{:Url('system.SystemCleardata/undata',['type'=>3])}",
                    act:'replace',
                    button:'立即更换',
                    class:'layui-btn-normal',
                },
                {
                    name:'清除用户生成的临时附件',
                    info:'清除用户生成的临时附件，不会影响产品图',
                    url:"{:Url('system.SystemCleardata/undata',['type'=>1])}"
                },
                {
                    name: '清除回收站产品',
                    info:'清除回收站产品，谨慎操作',
                    url: "{:Url('system.SystemCleardata/undata',['type'=>2])}",
                },
                {
                    name:'清除用户数据',
                    info:'用户相关的所有表都将被清除，谨慎操作',
                    url: "{:Url('system.SystemCleardata/userRelevantData')}",
                },
                {
                    name:'清除商城数据',
                    info:'清除商城数据，谨慎操作',
                    url: "{:Url('system.SystemCleardata/storeData')}",
                },
                {
                    name:'清除产品分类',
                    info:"会清除所有产品分类，谨慎操作",
                    url:"{:Url('system.SystemCleardata/categoryData')}",
                },
                {
                    name:"清除订单数据",
                    info:'清除用户所有订单数据，谨慎操作',
                    url:"{:Url('system.SystemCleardata/orderData')}",
                },
                {
                    name:'清除客服数据',
                    info:'清除添加的客服数据，谨慎操作',
                    url:"{:Url('system.SystemCleardata/kefuData')}",
                },
                {
                    name:'清除微信数据',
                    info:'清除微信菜单保存数据，微信关键字无效回复',
                    url:"{:Url('system.SystemCleardata/wechatData')}",
                },
                {
                    name:'清除微信用户',
                    info:"清除用户表和微信用户表,谨慎操作",
                    url:"{:Url('system.SystemCleardata/wechatuserData')}"
                },
                {
                    name:'清除内容分类',
                    info:'清除添加的文章和文章分类,谨慎操作',
                    url:"{:Url('system.SystemCleardata/articleData')}",
                },
                {
                    name:'清除所有附件',
                    info:'清除所有附件用户生成和后台上传,谨慎操作',
                    url:"{:Url('system.SystemCleardata/uploadData')}",
                },
                {
                    name:'清除系统记录',
                    info:'清除系统记录,谨慎操作',
                    url:"{:Url('system.SystemCleardata/systemdata')}",
                }
            ],
        },
        methods:{
            unDate:function (item) {
                if(item.act !== undefined && item.act)
                    return this[item.act] && this[item.act](item);
                $eb.$swal('delete',function(){
                    $eb.axios.get(item.url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success',res.data.msg);
                        }else
                            return Promise.reject(res.data.msg || '操作失败')
                    }).catch(function(err){
                        $eb.$swal('error',typeof err == 'object' ? err.toString() : err);
                    });
                },{'title':'您确定要'+item.name+'吗？','text':'数据清除无法恢复','confirm':'是的，我要操作'})
            },
            replace:function (item) {
                var re = /^(?=^.{3,255}$)(http(s)?:\/\/)?(www\.)?[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+(:\d+)*(\/\w+\.\w+)*$/;
                $eb.$alert('textarea',{title:'请输入需要替换的域名，格式为：http://域名。替换规则：会使用当前[设置]里面的[网站域名]去替换成当前您输入的域名,替换成功后再去更换[网站域名]'},function (res) {
                    if(!res)
                        return $eb.$swal('error','请输入需要替换的域名');
                    if(!re.test(res))
                        return $eb.$swal('error','请输入正确的域名');
                    $eb.axios.post(item.url,{value:res}).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success',res.data.msg);
                        }else
                            return Promise.reject(res.data.msg || '操作失败')
                    }).catch(function(err){
                        $eb.$swal('error',typeof err == 'object' ? err.toString() : err);
                    });
                });
            }
        },
        mounted:function () {

        }
    }).$mount(document.getElementById('app'));

</script>
{include file="public/inner_footer"}
