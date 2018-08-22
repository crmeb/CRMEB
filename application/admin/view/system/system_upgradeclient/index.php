{extend name="public/container"}
{block name="content"}
<style>
    .code{
        color: #97824B;
        font-size: 3em;
        border: 2px solid #ccc;
        padding: 10px;
        width: 50%;
        margin: 0 auto;
        font-weight: 700;
        font-family: 'Raleway', sans-serif;
        visibility: visible;
        animation-duration: 1000ms;
        animation-delay: 500ms;
        text-align: center;
        -webkit-box-shadow:0 0 10px #000;
        -moz-box-shadow:0 0 10px #000;
        box-shadow:0 0 10px #000;
        cursor: pointer;
        border-radius: 2%;
    }
    .colore{
        background-color: #CCCCCC;
    }
    .code:hover{
        background-color:rgba(0,0,0,0.1);
    }
</style>
<div class="row">
    <div class="col-sm-12" id="upgrade">
        <div class="wrapper wrapper-content animated fadeInUp">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="m-b-md">
                                <h2>在线升级 <span style="font-size: 5px;color: red">当前版本为：{{version}}</span></h2>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <?php if(isset($auth['code']) && $auth['code']==200 && isset($auth['data'])){if($auth['data']==1){?>
                                <div class="col-sm-12">
                                    <h4 class="code" @click="auto_upgrad()" v-text="content">正在加载中</h4>
                                </div>
                            <?php }else{?>
                            <div class="col-sm-12">
                                <h4 class="code colore">您没有权限升级</h4>
                            </div>
                            <?php }}else{?>
                                <div class="col-sm-12">
                                    <h4 class="code colore"><?php echo isset($auth['msg'])?$auth['msg']:'服务器异常';?></h4>
                                </div>
                            <?php }?>
                    </div>
                    <div class="row m-t-sm">
                        <div class="col-sm-12">
                            <div class="panel blank-panel">
                                <div class="panel-heading">
                                    <div class="panel-options">
                                        <ul class="nav nav-tabs">
                                            <li><a href="javascript:;">更新详情</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tab-1">
                                            <div class="feed-activity-list">
                                                <div class="feed-element">
                                                    <div class="media-body">
                                                       <div class="col-sm-2">版本号</div>
                                                        <div class="col-sm-8">更新内容</div>
                                                        <div class="col-sm-2">更新时间</div>
                                                    </div>
                                                    <div  v-for="item in list" v-cloak="">
                                                        <hr>
                                                        <div class="media-body">
                                                            <div class="col-sm-2">{{item.version}}</div>
                                                            <div class="col-sm-8">{{item.content}}</div>
                                                            <div class="col-sm-2">{{item.add_time}}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="media-body" style="margin-top: 20px">
                                                    <div class="col-sm-12 text-center" style="font-size: 15px;cursor:pointer;color: #FOF8FF">
                                                        <div>
                                                            <span v-show="load==true" @click="getlist()">点击加载</span>
                                                            <div class="sk-spinner sk-spinner-circle" v-show="loading==true">
                                                                <div class="sk-circle1 sk-circle"></div><div class="sk-circle2 sk-circle"></div><div class="sk-circle3 sk-circle"></div><div class="sk-circle4 sk-circle"></div><div class="sk-circle5 sk-circle"></div><div class="sk-circle6 sk-circle"></div><div class="sk-circle7 sk-circle"></div><div class="sk-circle8 sk-circle"></div><div class="sk-circle9 sk-circle"></div><div class="sk-circle10 sk-circle"></div><div class="sk-circle11 sk-circle"></div><div class="sk-circle12 sk-circle"></div>
                                                            </div>
                                                            <span v-show="load==false && loading==false">没有更多了</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    require(['vue','axios','layer'],function(Vue,axios,layer){
        var version_code=<?php echo (int)$version_code;?>,
            version="<?php echo $version;?>";
        new Vue({
            el:"#upgrade",
            data:{
                list:[],
                page:1,
                limit:20,
                version:version,
                version_code:version_code,
                loading:false,
                load:false,
                content:'正在加载中',
                count:0
            },
            watch:{
                count:function (n) {
                    console.log(n);
                    if(n<=0){
                        this.content='已是最新版本';
                    }else{
                        this.content='还有'+this.count+'个版本未升级 点击升级';
                    }
                }
            },
            methods:{
                getList:function () {
                    var that=this;
                    that.loading=true;
                    axios.post("{:Url('get_list')}",{page:this.page,limit:this.limit}).then(function (rem) {
                        var len=rem.data.data.list.length;
                        if(rem.data.code==200){
                            that.list=rem.data.data.list;
                        }
                        that.page=rem.data.data.page;
                        that.loading=false;
                        if(len<that.limit){
                            that.load=false;
                        }else{
                            that.load=true;
                        }
                    });
                },
                auto_upgrad:function () {
                    var that=this;
                    if(this.count<=0) return;
                     that.content='正在升级中请勿关闭浏览器或者页面';
                    axios.post("{:Url('auto_upgrad')}",{id:this.version_code}).then(function (rem) {
                        if(rem.data.code==200){
                            that.version_code=rem.data.data.code;
                            that.version=rem.data.data.version;
                            that.count=0;
                        }else{
                            that.content=rem.data.msg;
                        }
                    })
                },
                get_new_version_conte:function (){
                    var that=this;
                    axios.post("{:Url('get_new_version_conte')}",{id:this.version_code}).then(function (rem) {
                        if(rem.data.code=200){
                            that.count=rem.data.data.count;
                            if(that.count==0) that.content='已是最新版本';
                        }
                    })
                }
            },
            mounted:function () {
                this.getList()
                this.get_new_version_conte()
            }
        })
    })
</script>
{/block}