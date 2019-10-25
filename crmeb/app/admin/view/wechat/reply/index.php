{extend name="public/container"}
{block name="head_top"}
<link rel="stylesheet" type="text/css" href="{__ADMIN_PATH}css/main.css" />
<script src="{__FRAME_PATH}js/bootstrap.min.js"></script>
<script src="{__FRAME_PATH}js/content.min.js"></script>
<script src="{__ADMIN_PATH}frame/js/ajaxfileupload.js"></script>
<script src="{__PLUG_PATH}moment.js"></script>
{/block}
{block name="content"}
<div id="app" class="row">
    <div class="col-sm-12">
        <div class="wechat-reply-wrapper">
            <div class="ibox-title"><p>{{msg}}</p></div>
            <div class="ibox-content clearfix">
                <div class="view-wrapper col-sm-4">
                    <div class="mobile-header">公众号</div>
                    <section class="view-body" style="overflow:scroll;">
                        <div class="time-wrapper"><span class="time">9:36</span></div>
                        <div class="view-item text-box clearfix" :class="{show:type=='text'}">
                            <div class="avatar fl"><img src="{__ADMIN_PATH}images/head.gif" /></div>
                            <div class="box-content fl">
                                {{dataGroup.text.content}}
                            </div>
                        </div>

                        <div class="view-item news-box" :class="{show:type=='news'}" v-if="dataGroup.news.length >0">
                            <div class="vn-content" v-if="dataGroup.news.length ==1">
                                <div class="vn-title">{{dataGroup.news[0].title}}</div>
                                <div class="vn-time">{{dataGroup.news[0].date}}</div>
                                <div class="vn-picture" :style="{backgroundImage: 'url('+dataGroup.news[0].image+')'}"></div>
                                <div class="vn-picture-info">{{dataGroup.news[0].description}}</div>
                                <div class="vn-more">
                                    <a :href="dataGroup.news[0].url">阅读原文</a>
                                </div>
                            </div> 
                            <div class="vn-content" v-else>
                                <div class="con-item-box">
                                    <div class="vn-picture" :style="{backgroundImage: 'url('+dataGroup.news[0].image+')'}"></div>
                                    <div class="first-title">{{dataGroup.news[0].title}}</div>
                                </div>
                                <div class="con-item-list clearfix" v-for="(newinfos,index) in dataGroup.news" v-if="index>0">
                                    <div class="list-tit-info fl">{{newinfos.title}}</div>
                                    <div class="list-pic fr" :style="{backgroundImage: 'url('+newinfos.image+')'}"></div>
                                </div>
                            </div>
                        </div>
                            
                        <div class="view-item text-box clearfix" :class="{show:type=='image'}">
                            <div class="avatar fl"><img src="{__ADMIN_PATH}images/head.gif" /></div>
                            <div class="box-content fl">
                                <img class="picbox" :src="dataGroup.image.src" alt="" />
                            </div>
                        </div>

                       <?php /*   <div class="view-item music-box clearfix" :class="{show:type=='voice'}">
                              <div class="avatar fl"><img src="{__ADMIN_PATH}images/head.gif" /></div>
                              <div class="box-content fl">
                                  <p>{{musicTit}}</p>
                                  <p>{{musicInfo}}</p>
                                  <div class="music-icon"><i class="fa fa-music"></i></div>
                              </div>
                          </div>  */ ?>

                        <?php /*  <div class="view-item video-box" :class="{show:type=='video'}">
                              <div class="vn-content">
                                  <div class="vn-title">{{videoTit}}</div>
                                  <div class="vn-time">11月11日</div>
                                  <div class="video-content">
                                      <video src="" width="100%" controls="" preload=""></video>
                                  </div>
                              </div>
                              <div class="vn-more">{{videoInfo}}</div>
                          </div>  */ ?>
                    </section>
                </div>
                <div class="control-wrapper col-sm-8">
                    <section>
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="control-title">{{msg}}</div>
                            <div class="control-body">
                                <div class="form-group clearfix">
                                    <label class="col-sm-2 control-label tips" for="">规则状态</label>
                                    <div class="group-item col-sm-10">
                                        <div class="radio i-checks" style="display:inline;margin-left: 16px;">
                                            <label class="" style="padding-left: 0;">
                                                    <input style="position: relative;top: 1px;left: 3px;" checked="checked" v-model="status" type="radio" value="1" name="status">
                                                启用</label>
                                            </div>
                                        <div class="radio i-checks" style="display:inline;margin-left: 32px;">
                                            <label class="" style="padding-left: 0;">
                                                    <input style="position: relative;top: 1px;left: 3px;" type="radio" value="0" v-model="status" name="status">
                                                禁用
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group clearfix">
                                    <label class="col-sm-2 tips" for="">消息类型</label>
                                    <div class="col-sm-10 group-item">
                                        <select class="form-control m-b" v-model="type" name="account">
                                            <option value="text">文字消息</option>
                                            <option value="image">图片消息</option>
                                            <option value="news">图文消息</option>
                                            <option value="voice">声音消息</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="control-main">
                                    <!-- 文字 -->
                                    <div class="control-item control-main-txt" :class="{show:type=='text'}">
                                        <div class="form-group clearfix">
                                            <label class="col-sm-2 tips" for="">规则内容</label>
                                            <div class="col-sm-10 group-item">
                                                <textarea v-model="dataGroup.text.content" name="" id="" cols="30" rows="10" placeholder="请输入内容"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 图文 -->
                                    <div class="control-item control-main-news" :class="{show:type=='news'}">
                                        <div class="form-group clearfix">
                                            <label class="col-sm-2 tips" for="">选取图文</label>
                                            <div class="col-sm-10 group-item" @click="selectNews" style="cursor: pointer;"><span>选择图文消息</span></div>
                                        </div>
                                    </div>
                                    <!-- 图片 -->
                                    <div class="control-item control-main-picture" :class="{show:type=='image'}">
                                        <div class="form-group clearfix">
                                            <label class="col-sm-2 tips" for="">图片地址</label>
                                            <div class="col-sm-10 group-item">
                                                <input v-model="dataGroup.image.src" class="form-control" readonly type="text" placeholder="请上传图片"/>
                                                <span id="flag" class="file-btn" @click="uploadImg">上传</span>
                                            </div>
                                        </div>
                                        <div class="tips-info">
                                            <span>文件最大2Mb，支持bmp/png/jpeg/jpg/gif格式</span>
                                            <div class="upload-img"><img src="" alt="" /><span>上传图片</span></div>
                                        </div>
                                    </div>
                                    <!-- 音乐 -->
                                    <div class="control-item control-main-music" :class="{show:type=='voice'}">
                                        <div class="form-group clearfix">
                                            <label class="col-sm-2 tips" for="">上传声音</label>
                                            <div class="col-sm-10 group-item">
                                                <input class="form-control" type="text" readonly placeholder="请上传音乐文件或输入音乐URL地址" v-model="dataGroup.voice.src"/>
                                                <span class="file-btn" @click="uploadMusic">上传</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 视屏 -->
                                    <div class="control-item control-main-video" :class="{show:type=='video'}">
                                        <div class="form-group clearfix">
                                            <label class="col-sm-2 tips" for="">视频标题</label>
                                            <div class="col-sm-10 group-item">
                                                <input class="form-control" type="text" placeholder="视频标题"/>
                                            </div>
                                        </div>
                                        <div class="form-group clearfix">
                                            <label class="col-sm-2 tips" for="">视频上传</label>
                                            <div class="col-sm-10 group-item">
                                                <input class="form-control" type="text" placeholder="请上传音乐文件或输入视频URL地址"/>
                                                <span class="file-btn">上传</span>
                                            </div>
                                        </div>
                                        <div class="tips-info">
                                            <p>文件最大10MB，只支持MP4格式</p>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 tips" for="">视频描述</label>
                                            <div class="col-sm-10 group-item">
                                                <textarea name="" id="" cols="30" rows="10" placeholder="请输入内容"></textarea>
                                            </div>
                                        </div>
                                        <input id="file" type="file" name="file" style="display: none;" />
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: center;"><button type="button" class="btn btn-w-m btn-primary" @click="submit">提交</button></div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script type="text/javascript">
    $eb = parent._mpApi, $upload = $('#file');

    $eb.mpFrame.start(function (Vue) {
        var $http = $eb.axios;
        const vm = new Vue({
            data: {
                status: 1,
                msg: '',
                type: 'text',
                textBox: '',
                pic: '',
                key:'',
                dataGroup:{
                    text:{
                        content:''
                    },
                    image:{
                        src:''
                    },
                    voice:{
                        src:''
                    },
                    news:[]
                },
                uploadColl: function () {
                },
                uploadLink: ''
            },
            methods: {
                transfer:function (str){
                    var s = "";
                    if (str.length === 0) { return "";}
                    s = str.replace(/&amp;/g, "&");
                    s = s.replace(/&lt;/g, "<");
                    s = s.replace(/&gt;/g, ">");
                    s = s.replace(/&nbsp;/g, " ");
                    s = s.replace(/&#39;/g, "\'");
                    s = s.replace(/&quot;/g, "\"");
                    return s;
                },
                submit: function(){
                    if(!this.check()) return false;
                    $eb.axios.post("{:Url('save',array('key'=>$key))}",{key:this.key,status:this.status,data:this.dataGroup[this.type],type:this.type}).then(function(res){
                        if(res.status == 200 && res.data.code == 200){
                            $eb.message('success','设置成功!');
                        }
                        else
                            $eb.message('error',res.data.msg || '设置失败!');
                    }).catch(this.returnError);
                },
                check: function(){
                    var dataGroup = this.dataGroup;
                    switch (this.type){
                        case 'text':
                            if(dataGroup.text.content == '')
                                return this.returnError('请输入文字消息内容');
                            break;
                        case 'image':
                            if(dataGroup.image.src == '')
                                return this.returnError('请上传图片');
                            break;
                        case 'news':
                            if(dataGroup.news.length < 1)
                                return this.returnError('请选择图文消息');
                            break;
                        case 'voice':
                            if(dataGroup.voice.src == '')
                                return this.returnError('请上传声音');
                            break;
                    }
                    return true;
                },
                returnError:function(err){
                    $eb.message('error',err);
                    return false;
                },
                uploadImg: function () {
                    var vm = this;
                    this.uploadLink = "{:Url('upload_img')}";
                    $('#file').attr('accept','image/*');
                    this.uploadColl = function(pic){
                        vm.dataGroup.image.src = vm.transfer(pic);
                    };
                    $('#file').trigger('click');
                },
                uploadMusic:function(){
                    var vm = this;
                    this.uploadLink = "{:Url('upload_file')}";
                    $('#file').attr('accept','audio/*');
                    this.uploadColl = function(pic){
                        vm.dataGroup.voice.src = pic;
                    };
                    $('#file').trigger('click');
                },
                upload: function () {
                    var vm = this;
                    $('#file').on('change', function () {
                        $.ajaxFileUpload({
                            url: vm.uploadLink,
                            type: 'post',
                            secureuri: false, //一般设置为false
                            data:{'file':'file'},
                            fileElementId: 'file', // 上传文件的id、name属性名
                            dataType: 'json', //返回值类型，一般设置为json、application/json
                            success: function (data, status) {
                                if(data.code == 200){
                                    vm.uploadColl && vm.uploadColl(data.data);
                                    $eb.message('success','上传成功!');
                                }else{
                                    $eb.message('error',data.msg);
                                }
                                vm.upload();
                            },
                            error: function (data, status, e) {
                                $eb.message('error','上传失败!');
                                vm.upload();
                            }
                        });
                    });
                },
                selectNews:function(){
                    var vm = this,i;
                    parent._selectNews$eb = function(data){
                        var newsList = [];
                        data.new.map((news)=>{
                            newsList.push({
                                title:news.title,
                                description:news.synopsis,
                                url:news.url,
                                image:news.image_input,
                                date:moment(news.add_time*1000).format('M月D日'),
                                id:news.id
                            });
                        });
                        vm.dataGroup.news = newsList;
                        delete parent._selectNews$eb;
                        $eb.closeModalFrame(i);
                    };
                    i = $eb.createModalFrame('选择图文消息',"{:Url('admin/wechat.wechatNewsCategory/select',['callback'=>'_selectNews$eb'])}",{w:975});
                }
            },
            mounted: function () {
                window.vm = this;
                this.upload();
                this.key = "{$key}"
                this.msg = "{$title}";
                var res= <?php echo json_encode($replay_arr); ?>;
                window.vm.type = res.type;
                window.vm.status = res.status;
                if(res.type == 'image') {
                    window.vm.dataGroup.image.src = res.data.src;
                }else if(res.type == 'text'){
                    window.vm.dataGroup.text.content = res.data.content;
                }else if(res.type == 'voice'){
                    window.vm.dataGroup.voice.src = res.data.src;
                }else if(res.type == 'news'){
                    window.vm.dataGroup.news = res.data;
                }
                if(window.vm.used_key){
                    window.vm.keyword = res.key;
                }
            }
        });
        vm.$mount(document.getElementById('app'));
    });
</script>
{/block}

