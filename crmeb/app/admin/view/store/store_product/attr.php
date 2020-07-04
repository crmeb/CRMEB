<!DOCTYPE html>
<html lang="zh-CN">
<head>
    {include file="public/head"}
    <title>{$title|default=''}</title>
    <style>
        .check{color: #f00}
        .demo-upload{
            display: block;
            height: 33px;
            text-align: center;
            border: 1px solid transparent;
            border-radius: 4px;
            overflow: hidden;
            background: #fff;
            position: relative;
            box-shadow: 0 1px 1px rgba(0,0,0,.2);
            margin-right: 4px;
        }
        .demo-upload img{
            width: 100%;
            height: 100%;
            display: block;
        }
        .demo-upload-cover{
            display: none;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,.6);
        }
        .demo-upload:hover .demo-upload-cover{
            display: block;
        }
        .demo-upload-cover i{
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            margin: 0 2px;
        }
    </style>
    <script>
        window.test=1;
    </script>
</head>
<body>
<div id="store-attr" class="mp-form" v-cloak="">
    <i-Form :label-width="80" style="width: 100%" v-show="hidden == false">
        <Form-Item>
            <Row>
                <i-Col span="5">
                    <i-Button type="dashed" long @click="hiddenBool" icon="plus-round">添加新规则</i-Button>
                </i-Col>
            </Row>
        </Form-Item>
    </i-Form>
    <i-Form :label-width="80" style="width: 100%" v-show="hidden == true">
        <Form-Item
                :label="'规则名称:'">
            <Row>
                <i-Col style="position: relative;margin-right: 6px"  span="5"
                       v-for="(item, index) in items"
                       :key="index">
                    <i-Input type="text" v-model="item.value" placeholder="设置名称"></i-Input>
                    <i-Button style="position: absolute;top:0;right:0;margin-top:1px;border: none;font-size: 8px;line-height: 1.8" type="ghost" @click="handleRemove(index)" v-show="item.attrHidden == true"><Icon type="close-round" /></i-Button>
                    <i-Button style="position: absolute;top:0;right:0;margin-top:1px;border: none;font-size: 8px;line-height: 1.8" type="ghost" @click="attrHiddenBool(item)" v-show="item.attrHidden == false"><Icon type="checkmark-round"></Icon></i-Button>
                </i-Col>
                <i-Col span="5">
                    <i-Button type="dashed" long @click="handleAdd" icon="plus-round">添加新规则</i-Button>
                </i-Col>
            </Row>
        </Form-Item>
        <Form-Item v-show="item.attrHidden == true"
                v-for="(item, index) in items"
                :key="index"
                :label="''+item.value+':'" >
            <Row>
                <i-Col span="3"
                       v-for="(attr,k) in item.detail"
                       :key="attr"
                       :name="attr">
                    <Tag type="border" closable color="blue" @on-close="attrRemove(item,k)">{{ attr }}</Tag>
                </i-Col>
                <i-Col span="5">
                    <i-Input type="text" v-model="item.detailValue" placeholder="设置属性"></i-Input>
                </i-Col>
                <i-Col span="5">
                    <i-Button type="primary" style="margin-left: 6px" @click="attrAdd(item)">添加</i-Button>
                </i-Col>
            </Row>
        </Form-Item>
        <Form-Item v-show="hidden == true" style="width: 100%;">
            <Row style="margin: 0 88px 0 20px">
                <i-Col span="24">
                    <i-Button type="primary" long @click="addGoods(true)">生成</i-Button>
                </i-Col>
            </Row>
        </Form-Item>

        <template v-if="items[0].value!='' && items[0].detail.length>0 && attrs.length">
            <template v-for="(attr,index) in attrs">
                <Form-Item>
                    <Row>
                        <template v-for="(item,index) in attr.detail">
                            <i-Col span="3" style="margin-right: 2px">
                                {{index}}:{{item}}
                            </i-Col>
                        </template>
                        <i-Col span="4">
                            <span :class="attr.check ? 'check':''">金额:</span>&nbsp;&nbsp;<i-Input placeholder="金额" v-model="attr.price" style="width: 60%"
                                     :number="true"></i-Input>
                        </i-Col>
                        <i-Col span="4">
                            <span :class="attr.check ? 'check':''">库存:</span>&nbsp;&nbsp;<i-Input placeholder="库存" v-model="attr.sales" style="width: 60%"
                                                                                                  :number="true"></i-Input>
                        </i-Col>
                        <i-Col span="6">
                            <span :class="attr.check ? 'check':''">成本价:</span>&nbsp;&nbsp;<i-Input placeholder="成本价" v-model="attr.cost" style="width: 60%"
                                                                                                   :number="true"></i-Input>
                        </i-Col>
                        <i-Col span="6">
                            <span :class="attr.check ? 'check':''">产品条码:</span>&nbsp;&nbsp;<i-Input placeholder="产品条码" v-model="attr.bar_code" style="width: 60%"
                                                                                                   :number="true"></i-Input>
                        </i-Col>
                        <i-Col span="2" offset="1" style="margin-right: 2px">
                            <div class="demo-upload">
                                <img :src="attr.pic">
                                <div class="demo-upload-cover">
                                    <Icon type="ios-eye-outline" @click.native="openPic(attr.pic)" ></Icon>
                              <!--      <Upload
                                            :show-upload-list="false"
                                            :on-success="uploadSuccess(attr)"
                                            :on-error="uploadError"
                                            :format="['jpg','jpeg','png']"
                                            :max-size="2048"
                                            accept="image/*"
                                            :on-format-error="uploadFormatError"
                                            action="{:Url('upload')}"
                                            style="display: inline-block"
                                            :goods="attr"
                                    >
                                    </Upload>-->
                                    <Icon type="ios-cloud-upload-outline"  @click.native="getAttrPic(index)"></Icon>


                                </div>
                            </div>
                        </i-Col>
                        <i-Col span="2" style="margin-right: 3px">
                            <i-Button type="ghost" @click="removeGoods(index)">删除</i-Button>
                        </i-Col>
                    </Row>
                </Form-Item>
            </template>
            <Form-Item>
                <Row>
<!--                    <i-Col span="10">-->
<!--                        <i-Button type="dashed" long @click="addGoods" icon="plus-round">添加新商品</i-Button>-->
<!--                    </i-Col>-->
                    <i-Col span="2" offset="2">
                        <i-Button type="primary" @click="submit">提交</i-Button>
                    </i-Col>
                    <i-Col span="2" offset="1">
                        <i-Button type="error" @click="clear">清空所有属性</i-Button>
                    </i-Col>
                </Row>
            </Form-Item>
        </template>
    </i-Form>
    <Spin fix v-show="submiting == true">保存中...</Spin>
</div>
<script>
    var _vm ;
    mpFrame.start(function(Vue){
        new Vue({
            data () {
                return {
                    hidden:false,
                    attrHidden:false,
                    submiting :false,
                    items: <?php echo $result && isset($result['attr']) && !empty($result['attr']) ? json_encode($result['attr']) : 'false'; ?> || [
                        {
                            value: '',
                            detailValue:'',
                            attrHidden:false,
                            detail:[]
                        }
                    ],
                    attrs:<?php echo $result && isset($result['value']) && !empty($result['value']) ? json_encode($result['value']) : '[]'; ?>
                }
            },
            watch:{
                items:{
                    handler:function(){
//                        this.attrs = [];
                    },
                    deep:true
                }
            },
            methods: {
                getAttrPic(index){
                    this.createFrame('选择图片','/admin/widget.images/index/fodder/'+index+'.html',{h:500});
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
                setAttrPic(index,pic){
                    this.$set(this.attrs[index],'pic',pic);
                },
                attrHiddenBool(item){
                    if(item.value == ''){
                        $eb.message('error','请填写规则名称');
                    }else{
                        item.attrHidden = true;
                    }
                },
                hiddenBool(){
                    this.hidden = true;
                },
                handleAdd () {
                    if(!this.checkAttr())return ;
                    this.items.push({
                        value: '',
                        detailValue:'',
                        attrHidden:false,
                        detail:[]
                    });
                },
                checkAttr(){
                    var bool = true;
                    this.items.map(function(item){
                        if(!bool) return;
                        if(!item.value){
                            $eb.message('error','请填写规则名称');
                            bool = false;
                        }else if(!item.detail.length){
                            $eb.message('error','请设置规则属性');
                            bool = false;
                        }
                    });
                    return bool;
                },
                attrAdd (item) {
                    if(!item.detailValue) return false;
                    item.detail.push(item.detailValue);
                    item.detailValue = '';
                },
                handleRemove (index) {
                    if(this.items.length > 1)
                        this.items.splice(index,1);
                    else
                        $eb.message('error','请设置至少一个规则');
                },
                attrRemove(item,k){
                    if(1==item.detail.length){
                        $eb.message('error','请设置至少一个属性');
                        return false;
                    }
                    item.detail.splice(k,1);
                },
                removeGoods(index){
                    this.attrs.splice(index,1);
                },
                checkGoods(){
                    var bool = true;
                    this.attrs.map(function(attr){
                        if(!bool) return ;
                        if(!Object.keys(attr.detail).length){
                            $eb.message('error','请选择至少一个属性');
                            bool = false;
                        }else if(attr.price != parseFloat(attr.price) || attr.price < 0){
                            $eb.message('error','请输入正确的商品价格');
                            bool = false;
                        }else if(attr.sales != parseInt(attr.sales) || attr.sales < 0){
                            $eb.message('error','请输入正确的商品库存');
                            bool = false;
                        }
                    });
                    return bool;
                },
                addGoods(type){
                    if(this.attrs.length){
                        if(!this.checkGoods())return ;
                    }
                    var that = this;
                    $eb.axios.post("{:Url('is_format_attr',array('id'=>$id))}",{items:this.items,attrs:this.attrs}).then(function(res){
                        if(res.data.code == 200){
                            that.attrs = res.data.data
                        }else{
                            $eb.message('error',res.data.msg);
                        }
                    }).catch(function(err){
                        if(res.data.code == 200){
                            that.attrs = res.data.data
                        }else{
                            $eb.message('error',res.data.msg);
                        }
                    })
                },
                openPic(src){
                    $eb.openImage(src);
                },
                uploadSuccess(data){
                    return function(response, file, fileList){
                        if(response.code == 200){
                            data.pic = response.data.url;
                        }else{
                            $eb.message('error',response.data.msg || '图片上传失败!');
                        }
                    }
                },
                uploadError(error, file, fileList){
                    $eb.message('error',error);
                },
                uploadFormatError(file, fileList){
                    $eb.message('error','图片格式错误');
                },
                submit(){
                    var that = this;
                    that.submiting = true;
                    if(!this.checkAttr() || !this.checkGoods()) return ;
                    for(let attr in that.attrs){
                        that.attrs[attr].check = false;
                    }
                    $eb.axios.post("{:Url('set_attr',array('id'=>$id))}",{items:this.items,attrs:this.attrs}).then(function(res){
                        that.submiting = false;
                        if(res.status == 200 && res.data.code == 200){
                            $eb.message('success',res.data.msg || '编辑成功!');
                            $eb.closeModalFrame(window.name);
                        }else{
                            $eb.message('error',res.data.msg || '请求失败!');
                        }
                    }).catch(function(err){
                        $eb.message('error',err);
                    })
                },
                clear(){
                    var that = this;
                    requirejs(['sweetalert'], function (swel) {
                        swel({
                            title: "您确定要清空产品属性吗",
                            text: "删除后将无法恢复，请谨慎操作！",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "是的，我要清空！",
                            cancelButtonText: "让我再考虑一下…",
                            closeOnConfirm: false,
                            closeOnCancel: false
                        }).then(function () {
                            $eb.axios.post("{:Url('clear_attr',array('id'=>$id))}", {
                                items: that.items,
                                attrs: that.attrs
                            }).then(function (res) {
                                if (res.status == 200 && res.data.code == 200) {
                                    $eb.message('success', res.data.msg || '清空成功!');
                                    window.location.reload();
                                } else {
                                    $eb.message('error', res.data.msg || '清空失败!');
                                }
                            }).catch(function (err) {
                                $eb.message('error', err);
                            })
                        }).catch(console.log);
                    });
                }
            },
            mounted (){
                _vm = this;
                var resultAdmin = <?php echo $result && isset($result['attr']) && !empty($result['attr']) ? json_encode($result['attr']) : 'false'; ?>;
                if(resultAdmin) this.hidden = true;

                window.changeIMG = (index,pic)=>{
                    _vm.setAttrPic(index,pic);
                };
            }
        }).$mount(document.getElementById('store-attr'));
    });
</script>
</body>
