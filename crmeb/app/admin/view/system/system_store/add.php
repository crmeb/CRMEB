<!DOCTYPE html>
<html lang="zh-CN">
<head>
    {include file="public/head"}

    <link href="/system/frame/css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
    <link href="/system/frame/css/style.min.css?v=3.0.0" rel="stylesheet">
    <title>{$title|default=''}</title>
    <style></style>
</head>
<body>
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>门店设置</h5>
                </div>
                <div id="store-attr" class="mp-form" v-cloak="">
                    <i-Form :label-width="80" style="width: 100%">
                        <template>
                            <Alert type="warning">除门店简介外其他选项都是必填项</Alert>
                            <Form-Item>
                                <Row>
                                    <i-Col span="13">
                                        <span>门店名称：</span>
                                        <i-Input placeholder="门店名称" v-model="form.name" style="width: 80%" type="text"></i-Input>
                                    </i-Col>
                                </Row>
                            </Form-Item>
                            <Form-Item>
                                <Row>
                                    <i-Col span="13">
                                        <span>门店简介：</span>
                                        <i-Input placeholder="门店简介" v-model="form.introduction" style="width: 80%" type="text"></i-Input>
                                    </i-Col>
                                </Row>
                            </Form-Item>
                            <Form-Item>
                                <Row>
                                    <i-Col span="13">
                                        <span>门店手机号：</span>
                                        <i-Input placeholder="门店手机号" v-model="form.phone" style="width: 80%" type="text"></i-Input>
                                    </i-Col>
                                </Row>
                            </Form-Item>
                            <Form-Item>
                                <Row>
                                    <i-Col span="13">
                                        <span>门店地址：</span>
                                        <Cascader :data="addresData" :value.sync="form.address" @on-change="handleChange" style="width: 80%;display: inline-block;"></Cascader>
                                    </i-Col>
                                </Row>
                            </Form-Item>
                            <Form-Item>
                                <Row>
                                    <i-Col span="13">
                                        <span>详细地址：</span>
                                        <i-Input placeholder="详细地址" v-model="form.detailed_address" style="width: 80%" type="text"></i-Input>
                                    </i-Col>
                                </Row>
                            </Form-Item>
<!--                            <Form-Item>-->
<!--                                <Row>-->
<!--                                    <i-Col span="13">-->
<!--                                        <span>核销时效：</span>-->
<!--                                        <Date-picker type="daterange" @on-change="changeValidTime" placeholder="选择日期" :value="form.valid_time"></Date-picker>-->
<!--                                    </i-Col>-->
<!--                                </Row>-->
<!--                            </Form-Item>-->
                            <Form-Item>
                                <Row>
                                    <i-Col span="13">
                                        <span>营业时间：</span>
                                        <Time-picker type="timerange" @on-change="changeDayTime" placement="bottom-end" :value="form.day_time" placeholder="选择时间"></Time-picker>
                                    </i-Col>
                                </Row>
                            </Form-Item>
                            <Form-Item>
                                <Row>
                                    <i-Col span="13">
                                        <span>门店logo：</span>
                                        <div class="demo-upload-list" v-if="form.image">
                                            <template>
                                                <img :src="form.image">
                                                <div class="demo-upload-list-cover">
                                                    <Icon type="ios-eye-outline" @click="visible = true "></Icon>
                                                    <Icon type="ios-trash-outline" @click="form.image=''"></Icon>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="ivu-upload" style="display: inline-block; width: 58px;" @click="openWindows('选择图片','{:Url('widget.images/index',['fodder'=>'image'])}',{w:900,h:550})" v-if="!form.image">
                                            <div class="ivu-upload ivu-upload-drag">
                                                <div style="width: 58px; height: 58px; line-height: 58px;">
                                                    <i class="ivu-icon ivu-icon-camera" style="font-size: 20px;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <Modal title="查看图片" :visible.sync="visible">
                                            <img :src="form.image" v-if="visible" style="width: 100%">
                                        </Modal>
                                    </i-Col>
                                </Row>
                            </Form-Item>
                            <Form-Item>
                                <Row>
                                    <i-Col span="13">
                                        <span style="float: left">经纬度：</span>
                                        <Tooltip content="请点击查找位置进行选择位置">
                                            <i-Input placeholder="经纬度" v-model="form.latlng" :readonly="true" style="width: 80%" >
                                                <span slot="append" @click="openWindows('查找位置','{:Url('select_address')}',{w:400,h:700})" style="cursor:pointer">查找位置</span>
                                            </i-Input>
                                        </Tooltip>
                                    </i-Col>
                                </Row>
                            </Form-Item>
                        </template>
                        <Form-Item>
                            <Row>
                                <i-Col span="8" offset="6">
                                    <i-Button type="primary" @click="submit">提交</i-Button>
                                </i-Col>
                            </Row>
                        </Form-Item>
                    </i-Form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__PLUG_PATH}city.js"></script>
<script>
    var storeData={:json_encode($store)};
    mpFrame.start(function(Vue) {
        $.each(city,function (key,item) {
            city[key].value = item.label;
            if(item.children && item.children.length){
                $.each(item.children,function (i,v) {
                    city[key].children[i].value=v.label;
                    if(v.children && v.children.length){
                        $.each(v.children,function (k,val) {
                            city[key].children[i].children[k].value=val.label;
                        });
                    }
                });
            }
        });
        new Vue({
            data:function() {
                return {
                    id:storeData.id || 0,
                    addresData:city,
                    form:{
                        name:storeData.name || '',
                        introduction:storeData.introduction || '',
                        phone:storeData.phone || '',
                        address:storeData.address || [],
                        image:storeData.image || '',
                        detailed_address:storeData.detailed_address || '',
                        latlng:storeData.latlng || '',
                        valid_time:storeData.valid_time || [],
                        day_time:storeData.day_time || [],
                    },
                    visible:false,
                }
            },
            methods:{
                changeDayTime:function(date){
                    this.$set(this.form,'day_time',date);
                },
                changeValidTime:function(date){
                    this.$set(this.form,'valid_time',date);
                },
                createFrame:function(title,src,opt){
                    opt === undefined && (opt = {});
                    var h = parent.document.body.clientHeight - 100;
                    return layer.open({
                        type: 2,
                        title:title,
                        area: [(opt.w || 700)+'px', (opt.h || h)+'px'],
                        fixed: false, //不固定
                        maxmin: true,
                        moveOut:false,//true  可以拖出窗外  false 只能在窗内拖
                        anim:5,//出场动画 isOutAnim bool 关闭动画
                        offset:'auto',//['100px','100px'],//'auto',//初始位置  ['100px','100px'] t[ 上 左]
                        shade:0,//遮罩
                        resize:true,//是否允许拉伸
                        content: src,//内容
                        move:'.layui-layer-title'
                    });
                },
                handleChange:function(value,selectedData){
                    var that = this;
                    that.form.address = [];
                    $.each(selectedData,function (key,item) {
                        that.form.address.push(item.label);
                    });
                    that.$set(that.form,'address',that.form.address);
                },
                openWindows:function(title,url,opt){
                    return this.createFrame(title,url,opt);
                },
                changeIMG:function(name,url){
                    this.form[name]=url;
                },
                isPhone:function(test){
                    var reg = /^1[3456789]\d{9}$/;
                    return reg.test(test);
                },
                submit:function () {
                    var that = this;
                    if(!that.form.name) return  $eb.message('error','请填写门店行名称');
                    if(!that.form.phone) return  $eb.message('error','请输入手机号码');
                    if(!that.isPhone(that.form.phone)) return  $eb.message('error','请输入正确的手机号码');
                    if(!that.form.address) return  $eb.message('error','请选择门店地址');
                    if(!that.form.detailed_address) return  $eb.message('error','请填写门店详细地址');
                    if(!that.form.image) return  $eb.message('error','请选择门店logo');
                    if(!that.form.valid_time) return  $eb.message('error','请选择核销时效');
                    if(!that.form.day_time) return  $eb.message('error','请选择门店营业时间');
                    if(!that.form.latlng) return  $eb.message('error','请选择门店经纬度！');
                    var index = layer.load(1, {
                        shade: [0.5,'#fff']
                    });
                    $eb.axios.post('{:Url("save")}'+(that.id ? '?id='+that.id : ''),that.form).then(function (res) {
                        layer.close(index);
                        layer.msg(res.data.msg);
                        if(res.data.data.id) that.id=res.data.data.id;
                    }).catch(function (err) {
                        console.log(err);
                        layer.close(index);
                    })
                },
                selectAdderss:function (data) {
                    //lat 纬度 lng 经度
                    this.form.latlng=data.latlng.lat+','+data.latlng.lng;
                }
            },
            mounted:function () {
                window.changeIMG=this.changeIMG;
                window.selectAdderss=this.selectAdderss;
            }
        }).$mount(document.getElementById('store-attr'))
    })
</script>