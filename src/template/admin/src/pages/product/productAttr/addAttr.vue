<template>
    <Modal
             scrollable
            v-model="modal"
            @on-cancel="onCancel"
            title="添加商品规格"
            width="950">
        <Form  ref="formDynamic" :model="formDynamic" :rules="rules" class="attrFrom" :label-width="110" label-position="right" @submit.native.prevent>
            <Row :gutter="24">
                <Col span="24">
                    <Col span="8" class="mb15">
                        <FormItem label="规格模板名称：" prop="rule_name">
                            <Input  placeholder="请输入标题名称" :maxlength="20" v-model="formDynamic.rule_name"  />
                        </FormItem>
                    </Col>
                </Col>
                <Col span="23" class="noForm"  v-for="(item, index) in formDynamic.spec" :key="index">
                    <FormItem>
                        <div class="acea-row row-middle"><span class="mr5">{{item.value}}</span><Icon type="ios-close-circle" @click="handleRemove(index)"/></div>
                        <div class="rulesBox">
                            <Tag type="dot" class="mb5" closable color="primary" v-for="(j, indexn) in item.detail" :key="indexn" :name="j"  @on-close="handleRemove2(item.detail,indexn)">{{j}}</Tag>
                            <Input search enter-button="添加" placeholder="请输入属性名称" v-model="item.detail.attrsVal" @on-search="createAttr(item.detail.attrsVal,index)" style="width: 150px"/>
                        </div>
                    </FormItem>
                </Col>
                <Col span="24" v-if="isBtn" class="mt10">
                    <Col span="8" class="mr15">
                        <FormItem label="规格名称：">
                            <Input  placeholder="请输入规格" v-model="attrsName"  />
                        </FormItem>
                    </Col>
                    <Col span="8" class="mr20">
                        <FormItem label="规格值：">
                            <Input v-model="attrsVal" placeholder="请输入规格值"  />
                        </FormItem>
                    </Col>
                    <Col span="2">
                        <Button type="primary"  @click="createAttrName">确定</Button>
                    </Col>
                    <Col span="2">
                        <Button  @click="offAttrName">取消</Button>
                    </Col>
                </Col>
                <Spin size="large" fix v-if="spinShow"></Spin>
            </Row>
            <Button type="primary" icon="md-add" @click="addBtn" v-if="!isBtn" class="ml95 mt10">添加新规格</Button>
        </Form>
        <div slot="footer">
            <Button type="primary" :loading="modal_loading"   @click="handleSubmit('formDynamic')">确定</Button>
        </div>
    </Modal>
</template>

<script>
    import { mapState } from 'vuex'
    import { ruleAddApi, ruleInfoApi } from '@/api/product'
    export default {
        name: 'addAttr',
        data () {
            return {
                spinShow: false,
                modal_loading: false,
                grid: {
                    xl: 3,
                    lg: 3,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                modal: false,
                index: 1,
                rules: {
                    rule_name: [
                        { required: true, message: '请输入规格名称', trigger: 'blur' }
                    ]
                },
                formDynamic: {
                    rule_name: '',
                    spec: []
                },
                attrsName: '',
                attrsVal: '',
                formDynamicNameData: [],
                isBtn: false,
                formDynamicName: [],
                results: [],
                result: [],
                ids: 0
            }
        },
        computed: {
        },
        methods: {
            onCancel () {
                this.clear()
            },
            // 添加按钮
            addBtn () {
                this.isBtn = true
            },
            // 详情
            getIofo (row) {
                this.spinShow = true
                this.ids = row.id
                ruleInfoApi(row.id).then(res => {
                    this.formDynamic = res.data.info
                    this.spinShow = false
                }).catch(res => {
                    this.spinShow = false
                    this.$Message.error(res.msg)
                })
            },
            // 提交
            handleSubmit (name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        if (this.formDynamic.spec.length === 0) {
                            return this.$Message.warning('请至少添加一条商品规格！')
                        }
                        this.modal_loading = true
                        setTimeout(() => {
                            ruleAddApi(this.formDynamic, this.ids).then(res => {
                                this.$Message.success(res.msg)
                                setTimeout(() => {
                                    this.modal = false
                                    this.modal_loading = false
                                }, 500)
                                setTimeout(() => {
                                    this.$emit('getList')
                                    this.clear()
                                }, 600)
                            }).catch(res => {
                                this.modal_loading = false
                                this.$Message.error(res.msg)
                            })
                        }, 1200)
                    } else {
                        return false
                    }
                })
            },
            clear () {
                this.$refs['formDynamic'].resetFields()
                this.formDynamic.spec = []
                this.isBtn = false
                this.attrsName = ''
                this.attrsVal = ''
            },
            // 取消
            offAttrName () {
                this.isBtn = false
            },
            // 删除
            handleRemove (index) {
                this.formDynamic.spec.splice(index, 1)
            },
            // 删除属性
            handleRemove2 (item, index) {
                item.splice(index, 1)
            },
            // 添加规则名称
            createAttrName () {
                if (this.attrsName && this.attrsVal) {
                    let data = {
                        value: this.attrsName,
                        detail: [
                            this.attrsVal
                        ]
                    }
                    this.formDynamic.spec.push(data)
                    var hash = {}
                    this.formDynamic.spec = this.formDynamic.spec.reduce(function (item, next) {
                        /* eslint-disable */
                        hash[next.value] ? '' : hash[next.value] = true && item.push(next);
                        return item
                    }, [])
                     this.attrsName = '';
                     this.attrsVal = '';
                    this.isBtn = false;
                } else {
                    this.$Message.warning('请添加规格名称或规格值');
                }
            },
            // 添加属性
            createAttr (num, idx) {
                if (num) {
                    this.formDynamic.spec[idx].detail.push(num);
                    var hash = {};
                    this.formDynamic.spec[idx].detail = this.formDynamic.spec[idx].detail.reduce(function (item, next) {
                        /* eslint-disable */
                        hash[next] ? '' : hash[next] = true && item.push(next);
                        return item
                    }, [])
                } else {
                    this.$Message.warning('请添加属性');
                }
            }
        }
    }
</script>

<style scoped lang="stylus">
    .rulesBox
        display flex
        flex-wrap: wrap;
    .attrFrom
        >>> .ivu-form-item
         margin-bottom 0px !important
</style>
