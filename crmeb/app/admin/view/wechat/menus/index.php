{extend name="public/container"}
{block name="head_top"}
<link rel="stylesheet" type="text/css" href="{__ADMIN_PATH}css/main.css" />
<link href="{__FRAME_PATH}css/plugins/iCheck/custom.css" rel="stylesheet">
{/block}
{block name="content"}
<div id="app" class="row">
    <div class="col-sm-12">
        <div class="wechat-reply-wrapper wechat-menu">
            <div class="ibox-content clearfix">
                <div class="view-wrapper col-sm-4">
                    <div class="mobile-header">公众号</div>
                    <section class="view-body">
                        <div class="time-wrapper"><span class="time">9:36</span></div>
                    </section>
                    <div class="menu-footer">
                        <ul class="flex">
                            <li v-for="(menu, index) in menus" :class="{active:menu === checkedMenu}">
                                  <span @click="activeMenu(menu,index,null)"><i class="icon-sub"></i>{{ menu.name || '一级菜单' }}</span>
                                  <div class="sub-menu">
                                      <ul>
                                          <li v-for="(child, cindex) in menu.sub_button" :class="{active:child === checkedMenu}">
                                              <span @click="activeMenu(child,cindex,index)">{{ child.name || '二级菜单' }}</span>
                                          </li>
                                          <li v-if="menu.sub_button.length < 5" @click="addChild(menu,index)"><i class="icon-add"></i></li>
                                      </ul>
                                  </div>
                              </li>
                            <li v-if="menus.length < 3" @click="addMenu()"><i class="icon-add"></i></li>
                        </ul>
                    </div>
                </div>
                <div class="control-wrapper menu-control col-sm-8" v-show="checkedMenuId !== null">
                    <section>
                        <div class="control-main">
                            <h3 class="popover-title">菜单名称 <a class="fr" href="javascript:void(0);" @click="delMenu">删除</a></h3>
                            <p class="tips-txt">已添加子菜单，仅可设置菜单名称。</p>
                            <div class="menu-content control-body">
                                <form action="">
                                    <div class="form-group clearfix">
                                        <label for="" class="col-sm-2">菜单名称</label>
                                        <div class="col-sm-9 group-item">
                                            <input type="text" placeholder="菜单名称" class="form-control" v-model="checkedMenu.name">
                                            <span>字数不超过13个汉字或40个字母</span>
                                        </div>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label class="col-sm-2 control-label tips" for="">规则状态</label>
                                        <div class="group-item col-sm-9">
                                            <select class="form-control m-b" name="" id="" v-model="checkedMenu.type">
                                                <?php /*  <option value="text">文字消息</option>  */ ?>
                                                <option value="click">关键字</option>
                                                <option value="view">跳转网页</option>
                                               <?php /*   <option value="feat">事件功能</option>  */ ?>
                                                <option value="miniprogram">小程序</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="menu-control-box">
                                        <!-- 文字消息 -->
                                       <?php /*   <div class="text-box item" :class="{show:checkedMenu.type=='text'}">
                                              <p>回复内容</p>
                                              <textarea v-model="checkedMenu.content" cols="60" rows="10" placeholder="请输入回复内容"></textarea>
                                          </div>  */ ?>
                                        <!-- 关键字 -->
                                        <div class="keywords item" :class="{show:checkedMenu.type=='click'}">
                                            <p>关键字</p>
                                            <input type="text" placeholder="请输入关键字" class="form-control" v-model="checkedMenu.key">

                                        </div>
                                        <!-- 跳转地址 -->
                                        <div class="url item" :class="{show:checkedMenu.type=='view'}">
                                            <p>跳转地址</p>
                                            <input type="text" v-model="checkedMenu.url" placeholder="请输入跳转地址" class="form-control">
                                            <p class="text-left"></p>
                                            <div class="well well-lg">
                                                    <span class="help-block m-b-none">首页：{$Request.domain}</span>
                                                    <span class="help-block m-b-none">个人中心：{$Request.domain}/user</span>
                                            </div>

                                        </div>
                                        <!-- 事件功能 -->
                                        <?php /*  <div class="feat-select item" :class="{show:type=='feat'}">
                                              <div class="radio i-checks" style="display:block">
                                                  <label class="" style="padding-left: 0;">
                                                      <div class="iradio_square-green" style="position: relative;">
                                                          <div class="iradio_square-green" style="position: relative;">
                                                          <input checked="checked" type="radio" value="2" name="feat" style="position: absolute; opacity: 0;"></div>
                                                      </div>
                                                      <i></i>扫码推事件
                                                  </label>
                                              </div>
                                              <div class="radio i-checks" style="display:block">
                                                  <label class="" style="padding-left: 0;">
                                                      <div class="iradio_square-green" style="position: relative;">
                                                          <div class="iradio_square-green" style="position: relative;"><input type="radio" value="2" name="feat" style="position: absolute; opacity: 0;"></div>
                                                      </div>
                                                      <i></i>扫码推事件且弹出“消息接收中”提示框
                                                  </label>
                                              </div>
                                              <div class="radio i-checks" style="display:block">
                                                  <label class="" style="padding-left: 0;">
                                                      <div class="iradio_square-green" style="position: relative;">
                                                          <div class="iradio_square-green" style="position: relative;"><input type="radio" value="2" name="feat" style="position: absolute; opacity: 0;"></div>
                                                      </div>
                                                      <i></i>弹出系统拍照发图
                                                  </label>
                                              </div>
                                              <div class="radio i-checks" style="display:block">
                                                  <label class="" style="padding-left: 0;">
                                                      <div class="iradio_square-green" style="position: relative;">
                                                          <div class="iradio_square-green" style="position: relative;"><input type="radio" value="2" name="feat" style="position: absolute; opacity: 0;"></div>
                                                      </div>
                                                      <i></i>弹出拍照或者相册发图
                                                  </label>
                                              </div>
                                              <div class="radio i-checks" style="display:block">
                                                  <label class="" style="padding-left: 0;">
                                                      <div class="iradio_square-green" style="position: relative;">
                                                          <div class="iradio_square-green" style="position: relative;"><input type="radio" value="2" name="feat" style="position: absolute; opacity: 0;"></div>
                                                      </div>
                                                      <i></i>弹出微信相册发图器
                                                  </label>
                                              </div>
                                              <div class="radio i-checks" style="display:block">
                                                  <label class="" style="padding-left: 0;">
                                                      <div class="iradio_square-green" style="position: relative;">
                                                          <div class="iradio_square-green" style="position: relative;"><input type="radio" value="2" name="feat" style="position: absolute; opacity: 0;"></div>
                                                      </div>
                                                      <i></i>弹出地理位置选择器
                                                  </label>
                                              </div>
                                          </div>  */ ?>
                                        <!-- 小程序 -->
                                        <div class="wrchat-app item" :class="{show:checkedMenu.type=='miniprogram'}">
                                            <div class="list">
                                                <p>appid</p>
                                                <input class="form-control" v-model="checkedMenu.appid" type="text" />
                                            </div>
                                            <div class="list">
                                                <p>备用网页url</p>
                                                <input class="form-control" v-model="checkedMenu.url" type="text" />
                                            </div>
                                            <div class="list">
                                                <p>小程序路径</p>
                                                <input class="form-control" v-model="checkedMenu.pagepath" type="text" />
                                            </div>
                                        </div>
                                        <!-- 多客服 -->
                                        <div class="service item">
                                            <p>回复内容</p>
                                            <textarea  cols="60" rows="10"></textarea>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="ibox-content submit">
                <button class="btn btn-w-m btn-primary" @click="submit">保存发布</button>
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script src="{__FRAME_PATH}js/plugins/iCheck/icheck.min.js"></script>
<script src="{__FRAME_PATH}js/bootstrap.min.js"></script>
<script src="{__FRAME_PATH}js/content.min.js"></script>
<script src="{__PLUG_PATH}reg-verify.js"></script>
<script type="text/javascript">
    $eb = parent._mpApi;
    $eb.mpFrame.start(function(Vue){
        var $http = $eb.axios;
        const vm = new Vue({
            data:{
                menus:<?=$menus?>,
                checkedMenu:{
                    type:'click',
                    name:''
                },
                checkedMenuId:null,
                parentMenuId:null
            },
            methods:{
                defaultMenusData:function(){
                    return {
                        type:'click',
                        name:'',
                        sub_button:[]
                    };
                },
                defaultChildData:function(){
                    return {
                        type:'click',
                        name:''
                    };
                },
                addMenu:function(){
                    if(!this.check()) return false;
                    var data = this.defaultMenusData(),id = this.menus.length;
                    this.menus.push(data);
                    this.checkedMenu = data;
                    this.checkedMenuId = id;
                    this.parentMenuId = null;
                },
                addChild:function(menu,index){
                    if(!this.check()) return false;
                    var data = this.defaultChildData(),id = menu.sub_button.length;
                    menu.sub_button.push(data);
                    this.checkedMenu = data;
                    this.checkedMenuId = id;
                    this.parentMenuId = index;
                },
                delMenu:function(){
                    console.log(this.parentMenuId);
                    this.parentMenuId === null ?
                        this.menus.splice(this.checkedMenuId,1) : this.menus[this.parentMenuId].sub_button.splice(this.checkedMenuId,1);
                    this.parentMenuId = null;
                    this.checkedMenu = {};
                    this.checkedMenuId = null;
                },
                activeMenu:function(menu,index,pid){
                    if(!this.check()) return false;
                    pid === null ?
                        (this.checkedMenu = menu) : (this.checkedMenu = this.menus[pid].sub_button[index],this.parentMenuId = pid);
                    this.checkedMenuId=index
                },
                check:function(){
                    if(this.checkedMenuId === null) return true;
                    if(!this.checkedMenu.name){
                        $eb.message('请输入按钮名称!');
                        return false;
                    }
                    if(this.checkedMenu.type == 'click' && !this.checkedMenu.key){
                        $eb.message('请输入关键字!');
                        return false;
                    }
                    if(this.checkedMenu.type == 'view' && !$reg.isHref(this.checkedMenu.url)){
                        $eb.message('请输入正确的跳转地址!');
                        return false;
                    }
                    if(this.checkedMenu.type == 'miniprogram'
                        && (!this.checkedMenu.appid
                        || !this.checkedMenu.pagepath
                        || !this.checkedMenu.url)){
                        $eb.message('请填写完整小程序配置!');
                        return false;
                    }
                    return true;
                },
                submit:function(){
                    if(!this.menus.length){
                        $eb.message('error','请添加菜单!');
                        return false;
                    }
                    $http.post("{:url('wechat.menus/save',array('dis'=>1))}",{button:this.menus}).then(function (res) {
                        if(res.status == 200 && res.data.code == 200)
                            $eb.message('success','发布菜单成功!');
                        else
                            return Promise.reject(res.data.msg || '发布菜单失败!');
                    }).catch(function(err){
                        $eb.message('error',err);
                    })
                }
            },
            mounted:function(){
                window.vm = this;
            }
        });
        vm.$mount(document.getElementById('app'));
    });
    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
    });
</script>
{/block}

