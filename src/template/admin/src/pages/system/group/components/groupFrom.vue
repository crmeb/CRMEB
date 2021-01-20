<template>
    <div>
        <Modal v-model="modals" width="850"  scrollable footer-hide closable :title='titleFrom' :mask-closable="false" @on-cancel="handleReset">
            <Form ref="formValidate" :model="formValidate" :label-width="100" :rules="ruleValidate"  @submit.native.prevent>
                <Row type="flex" :gutter="24">
                    <Col span="24">
                        <FormItem label="数据组名称：" prop="name">
                            <Input v-model="formValidate.name" placeholder="请输入数据组名称" style="width: 90%;"></Input>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="数据字段：" prop="config_name">
                            <Input v-model="formValidate.config_name" placeholder="请输入数据字段" style="width: 90%;"></Input>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="数据简介：" prop="info">
                            <Input v-model="formValidate.info" placeholder="请输入数据简介" style="width: 90%;"></Input>
                        </FormItem>
                    </Col>
                  <Col span="24">
                      <FormItem label="数类型：" prop="cate_id">
                            <RadioGroup v-model="formValidate.cate_id">
                                <Radio :label="0">默认</Radio>
                                <Radio :label="1">数据</Radio>
                            </RadioGroup>
                      </FormItem>
                  </Col>
                    <Col span="24" v-for="(item, index) in formValidate.typelist" :key="index">
                        <Col v-bind="grid">
                            <FormItem :label="'字段' + (index+1)+'：'"  :prop="'typelist.' + index + '.name.value'" :rules="{required: true, message: '请输入字段名称：姓名', trigger: 'blur'}">
                                <Input v-model="item.name.value" placeholder="字段名称：姓名"></Input>
                            </FormItem>
                        </Col>
                        <Col v-bind="grid" class="goupBox">
                            <FormItem :prop="'typelist.' + index + '.title.value'" :rules="{required: true, message: '请输入字段配置名', trigger: 'blur'}">
                                <Input v-model="item.title.value" placeholder="字段配置名：name"></Input>
                            </FormItem>
                        </Col>
                        <Col v-bind="grid" prop="type" class="goupBox mr15">
                            <FormItem :prop="'typelist.' + index + '.type.value'" :rules="{required: true, message: '请选择字段类型', trigger: 'change'}">
                                <i-select placeholder="字段类型" v-model="item.type.value">
                                    <i-option value="input">文本框</i-option>
                                    <i-option value="textarea">多行文本框</i-option>
                                    <i-option value="radio">单选框</i-option>
                                    <i-option value="checkbox">多选框</i-option>
                                    <i-option value="select">下拉选择</i-option>
                                    <i-option value="upload">单图</i-option>
                                    <i-option value="uploads">多图</i-option>
                                </i-select>
                            </FormItem>
                        </Col>
                            <Col span="1">
                                <Icon type="ios-close-circle-outline" class="cur" @click="delGroup(index)"/>
                            </Col>
                        <Col span="24" v-if="item.type.value ==='radio' || item.type.value ==='checkbox' || item.type.value ==='select'">
                            <FormItem :prop="'typelist.' + index + '.param.value'" :rules="{required: true, message: '请输入参数方式', trigger: 'blur'}">
                                <Input type="textarea" :rows="4" :placeholder="item.param.placeholder"
                                       v-model="item.param.value" style="width: 90%;"></Input>
                            </FormItem>
                        </Col>
                    </Col>
                    <Col>
                        <FormItem>
                            <Button type="primary" @click="addType">添加字段</Button>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <Button type="primary" long @click="handleSubmit('formValidate')" :disabled="valids">提交</Button>
                    </Col>
                </Row>
            </Form>
        </Modal>
    </div>
</template>

<script>
    import { groupAddApi, groupInfoApi } from '@/api/system'
    export default {
        name: 'menusFrom',
        props: {
            groupId: {
                type: Number,
                default: 0
            },
            titleFrom: {
                type: String,
                default: ''
            },
            addId: {
                type: String,
                default: ''
            }
        },
        data () {
            return {
                iconVal: '',
                grid: {
                    xl: 7,
                    lg: 7,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                modals: false,
                modal12: false,
                ruleValidate: {
                    name: [
                        { required: true, message: '请输入数据组名称', trigger: 'blur' }
                    ],
                    config_name: [
                        { required: true, message: '请输入数据字段', trigger: 'blur' }
                    ],
                    info: [
                        { required: true, message: '请输入数据简介', trigger: 'blur' }
                    ],
                    names: [
                        { required: true, message: '请输入字段名称', trigger: 'blur' }
                    ],
                },
                FromData: [],
                valids: false,
                list2: [],
                formValidate: {
                    name: '',
                    config_name: '',
                    info: '',
                    typelist: [],
                    cate_id:0,
                }
            }
        },
        watch: {
            'addId' (n) {
                if (n === 'addId') {
                    this.formValidate.typelist = []
                }
            }
        },
        methods: {
            // 点击添加字段
            addType () {
                this.formValidate.typelist.push({
                    name: {
                        value: ''
                    },
                    title: {
                        value: ''
                    },
                    type: {
                        value: ''
                    },
                    param: {
                        placeholder: '参数方式例如:\n1=白色\n2=红色\n3=黑色',
                        value: ''
                    }
                })
                console.log(this.formValidate)
            },
            // 删除字段
            delGroup (index) {
                this.formValidate.typelist.splice(index, 1)
            },
            // 详情
            fromData (id) {
                groupInfoApi(id).then(async res => {
                    this.formValidate = res.data.info
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 提交
            handleSubmit (name) {
                let data = {
                    url: this.groupId ? `/setting/group/${this.groupId}` : 'setting/group',
                    method: this.groupId ? 'put' : 'post',
                    datas: this.formValidate
                }
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        if (this.formValidate.typelist.length === 0) return this.$Message.error('请添加字段名称：姓名！')
                        groupAddApi(data).then(async res => {
                            this.$Message.success(res.msg)
                            this.modals = false
                            this.$refs[name].resetFields()
                            this.formValidate.typelist = []
                            this.$emit('getList')
                        }).catch(res => {
                            this.$Message.error(res.msg)
                        })
                    } else {
                        if (!this.formValidate.name) return this.$Message.error('请添加数据组名称！')
                        if (!this.formValidate.config_name) return this.$Message.error('请添加数据字段！')
                        if (!this.formValidate.info) return this.$Message.error('请添加数据简介！')
                    }
                })
            },
            handleReset () {
                this.modals = false
                this.$refs['formValidate'].resetFields()
                this.$emit('clearFrom')
            }
        },
        created () {
        },
        mounted () {
        }
    }
</script>

<style scoped lang="stylus">
    .cur
       cursor pointer
    .goupBox >>> .ivu-form-item-content
       margin-left: 43px!important;

</style>
