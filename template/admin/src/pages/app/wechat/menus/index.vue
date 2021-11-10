<template>
    <div class="article-manager">
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <!-- 公众号设置 -->
            <Row :gutter="24" type="flex">
                <Col span="24" class="ml40">
                    <!-- 预览功能 -->
                    <Col :span="24">
                        <Col :xl="7" :lg="7" :md="22" :sm="22" :xs="22" class="left mb15">
                            <img class="top" src="../../../../assets/images/mobilehead.png" />
                            <img class="bottom" src="@/assets/images/mobilefoot.png" />
                            <div style="background: #F4F5F9; min-height: 438px; position: absolute;
         top: 63px; width: 320px; ">
                            </div>
                            <div class="textbot">
                                <div class="li" v-for="(item,indx) in list" :key="indx" :class="{active:item === formValidate}">
                                    <div>
                                        <div class="add" @click="add(item,indx)">
                                            <Icon type="ios-add" />
                                            <div class="arrow"></div>
                                        </div>
                                        <div class="tianjia">
                                            <div class="addadd" v-for="(j,index) in item.sub_button" :key="index"
                                                 :class="{active:j === formValidate}" @click="gettem(j,index,indx)">{{j.name || '二级菜单'}}</div>
                                        </div>
                                    </div>
                                    <div class="text" @click="gettem(item,indx,null)">{{item.name || '一级菜单'}}</div>
                                </div>
                                <div class="li" v-show="list.length < 3">
                                    <div class="text" @click="addtext"><Icon type="ios-add" /></div>
                                </div>
                            </div>
                        </Col>
                        <Col :xl="11" :lg="12" :md="22" :sm="22" :xs="22" >
                            <Tabs value="name1" v-if="checkedMenuId !== null">
                                <TabPane label="菜单信息" name="name1">
                                    <Col span="24" class="userAlert">
                                        <div class="box-card right">
                                            <Alert show-icon closable class="spwidth"> 已添加子菜单，仅可设置菜单名称</Alert>
                                            <Form  ref="formValidate" :model="formValidate" :rules="ruleValidate" :label-width="100" class="mt20">
                                                <FormItem label="菜单名称" prop="name">
                                                    <Input v-model="formValidate.name" placeholder="请填写菜单名称" class="spwidth"></Input>
                                                </FormItem>
                                                <FormItem label="规则状态" prop="type">
                                                    <Select v-model="formValidate.type" placeholder="请选择规则状态" class="spwidth">
                                                        <Option value="click">关键字</Option>
                                                        <Option value="view">跳转网页</Option>
                                                        <Option value="miniprogram">小程序</Option>
                                                    </Select>
                                                </FormItem>
                                                <div v-if="formValidate.type === 'click'">
                                                    <FormItem label="关键字" prop="key">
                                                        <Input v-model="formValidate.key" placeholder="请填写关键字" class="spwidth"></Input>
                                                    </FormItem>
                                                </div>
                                                <div v-if="formValidate.type === 'miniprogram'">
                                                    <FormItem label="appid" prop="appid">
                                                        <Input v-model="formValidate.appid" placeholder="请填写appid" class="spwidth"></Input>
                                                    </FormItem>
                                                    <FormItem label="小程序路径" prop="pagepath">
                                                        <Input v-model="formValidate.pagepath" placeholder="请填写小程序路径" class="spwidth"></Input>
                                                    </FormItem>
                                                    <FormItem label="备用网页" prop="url">
                                                        <Input v-model="formValidate.url" placeholder="请填写备用网页" class="spwidth"></Input>
                                                    </FormItem>
                                                </div>
                                                <div v-if="formValidate.type === 'view'">
                                                    <FormItem label="跳转地址" prop="url">
                                                        <Input v-model="formValidate.url" placeholder="请填写跳转地址" class="spwidth"></Input>
                                                    </FormItem>
                                                </div>
                                            </Form>
                                        </div>
                                    </Col>
                                </TabPane>
                                <Button size="small" type="error"  slot="extra" @click="deltMenus">删除</Button>
                            </Tabs>
                            <Col :span="24" v-if="isTrue">
                                <Button type="primary" style="display: block;margin: 10px auto;" @click="submenus('formValidate')">保存并发布</Button>
                            </Col>
                        </Col>
                    </Col>
                </Col>
            </Row>
        </Card>

        <Modal v-model="modal2" width="360">
            <p slot="header" style="color:#f60;text-align:center">
                <Icon type="ios-information-circle"></Icon>
                <span>删除</span>
            </p>
            <div style="text-align:center">
                <p>确定删除吗？</p>
            </div>
            <div slot="footer">
                <Button type="error" size="large" long  @click="del">确定</Button>
            </div>
        </Modal>
    </div>
</template>

<script>
    import { wechatMenuApi, MenuApi } from '@/api/app'
    export default {
        name: 'wechatMenus',
        data () {
            return {
                modal2: false,
                formValidate: {
                    name: '',
                    type: 'click',
                    appid: '',
                    url: '',
                    key: '',
                    pagepath: '',
                    id: 0
                },
                ruleValidate: {
                    name: [
                        { required: true, message: '请填写菜单名称', trigger: 'blur' },
                        { min: 1, max: 14, message: '长度在 1 到 14 个字符', trigger: 'blur' }
                    ],
                    key: [
                        { required: true, message: '请填写关键字', trigger: 'blur' }
                    ],
                    appid: [
                        { required: true, message: '请填写appid', trigger: 'blur' }
                    ],
                    pagepath: [
                        { required: true, message: '请填写备用网页', trigger: 'blur' }
                    ],
                    url: [
                        { required: true, message: '请填写跳转地址', trigger: 'blur' }
                    ],
                    type: [
                        { required: true, message: '请选择规则状态', trigger: 'change' }
                    ]
                },
                parentMenuId: null,
                list: [],
                checkedMenuId: null,
                isTrue: false
            }
        },
        mounted () {
            this.getMenus()
            if (this.list.length) {
                this.formValidate = this.list[this.activeClass]
            } else {
                return this.formValidate
            }
        },
        methods: {
            // 添加一级字段函数
            defaultMenusData () {
                return {
                    type: 'click',
                    name: '',
                    sub_button: []
                }
            },
            // 添加二级字段函数
            defaultChildData () {
                return {
                    type: 'click',
                    name: ''
                }
            },
            // 获取 菜单
            getMenus () {
                wechatMenuApi().then(async res => {
                    let data = res.data
                    this.list = data.menus
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 点击保存提交
            submenus (name) {
                if (this.isTrue && !this.checkedMenuId && this.checkedMenuId !== 0) {
                    this.putData()
                } else {
                    this.$refs[name].validate((valid) => {
                        if (valid) {
                            this.putData()
                        } else {
                            if (!this.check()) return false
                        }
                    })
                }
            },
            // 新增data
            putData () {
                let data = {
                    button: this.list
                }
                MenuApi(data).then(async res => {
                    this.$Message.success(res.msg)
                    this.checkedMenuId = null
                    this.formValidate = {}
                    this.isTrue = false
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 点击元素
            gettem (item, index, pid) {
                this.checkedMenuId = index
                this.formValidate = item
                this.parentMenuId = pid
                this.isTrue = true
            },
            // 增加二级
            add (item, index) {
                if (!this.check()) return false
                if (item.sub_button.length < 5) {
                    let data = this.defaultChildData()
                    let id = item.sub_button.length
                    item.sub_button.push(data)
                    this.formValidate = data
                    this.checkedMenuId = id
                    this.parentMenuId = index
                    this.isTrue = true
                }
            },
            // 增加一级
            addtext () {
                if (!this.check()) return false
                let data = this.defaultMenusData()
                let id = this.list.length
                this.list.push(data)
                this.formValidate = data
                this.checkedMenuId = id
                this.parentMenuId = null
                this.isTrue = true
            },
            // 判断函数
            check: function () {
                let reg = /[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+\.?/
                if (this.checkedMenuId === null) return true
                if (!this.isTrue) return true
                if (!this.formValidate.name) {
                    this.$Message.warning('请输入按钮名称!')
                    return false
                }
                if (this.formValidate.type === 'click' && !this.formValidate.key) {
                    this.$Message.warning('请输入关键字!')
                    return false
                }
                if (this.formValidate.type === 'view' && !(reg.test(this.formValidate.url))) {
                    this.$Message.warning('请输入正确的跳转地址!')
                    return false
                }
                if (this.formValidate.type === 'miniprogram' &&
                    (!this.formValidate.appid ||
                    !this.formValidate.pagepath ||
                    !this.formValidate.url)) {
                        this.$Message.warning('请填写完整小程序配置!')
                        return false
                    }
                return true
            },
            // 删除
            deltMenus () {
                if (this.isTrue) {
                    this.modal2 = true
                } else {
                    this.$Message.warning('请选择菜单!')
                }
            },
            // 确认删除
            del () {
                this.parentMenuId === null ? this.list.splice(this.checkedMenuId, 1) : this.list[this.parentMenuId].sub_button.splice(this.checkedMenuId, 1)
                this.parentMenuId = null
                this.formValidate = {
                    name: '',
                    type: 'click',
                    appid: '',
                    url: '',
                    key: '',
                    pagepath: '',
                    id: 0
                }
                this.isTrue = true
                this.modal2 = false
                this.checkedMenuId = null
                this.$refs['formValidate'].resetFields()
            }
        }
    }
</script>
<style scoped lang="less">
    *{
        -moz-user-select: none; /*火狐*/
        -webkit-user-select: none; /*webkit浏览器*/
        -ms-user-select: none; /*IE10*/
        -khtml-user-select: none; /*早期浏览器*/
        user-select: none;
    }
    .left {
        min-width: 390px;
        min-height: 550px;
        position: relative;
        padding-left: 40px;
    }

    .top {
        position: absolute;
        top: 0px;
    }

    .bottom {
        position: absolute;
        bottom: 0px;
    }

    .textbot {
        position: absolute;
        bottom: 0px;
        left: 82px;
        width: 100%;
    }
    .active {
        border: 1px solid #44B549 !important;
        color: #44B549 !important;
    }
    .li {
        float: left;
        width: 93px;
        line-height: 48px;
        border: 1px solid #E7E7EB;
        background: #FAFAFA;
        text-align: center;
        cursor: pointer;
        color: #999;
        position: relative;
    }
    .text{
        height: 50px;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow:ellipsis;
        padding: 0 5px;
    }
    .text:hover {
        color: #000;
    }

    .add {
        position: absolute;
        bottom: 65px;
        width: 100%;
        line-height: 48px;
        border: 1px solid #E7E7EB;
        background: #FAFAFA;
    }
    .arrow {
        position: absolute;
        bottom: -16px;
        left: 36px;
        /* 圆角的位置需要细心调试哦 */
        width: 0;
        height: 0;
        font-size: 0;
        border: solid 8px;
        border-color:#fff #F4F5F9 #F4F5F9 #F4F5F9;
    }
    .tianjia {
        position: absolute;
        bottom: 115px;
        width: 100%;
        line-height: 48px;
        background: #FAFAFA;
    }
    .addadd {
        width: 100%;
        line-height: 48px;
        border: 1px solid #E7E7EB;
        background: #FAFAFA;
        height: 48px;
    }
    .right {
        background: #fff;
        min-height: 400px;
    }
    .spwidth{
        width: 100%;
    }
    .userAlert{
      margin-top: 16px!important;
    }
</style>
