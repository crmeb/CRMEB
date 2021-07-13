<template>
    <div>
        <Modal
                v-model="val"
                title="商品属性"
                width="70%"
                @on-cancel="cancel"
        >
            <div class="Modals">
                <Form  class="form" ref="form"  :label-width="70" label-position="right">
                    <Row :gutter="24" type="flex">
                        <Col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
                            <FormItem label="规格：" prop="store_name" label-for="store_name">
                                <Input placeholder="规格" style="width:10%" class="input" :value="item" v-for="(item,index) in specs" :key="index">
                                      <Icon type="md-close" slot="suffix" />
                                </Input>
                                <Input placeholder="请输入" v-model="specsVal" style="width:10%" class="input">
                                   <Icon type="md-add" slot="suffix" @click="confirm"/>
                                </Input>
                                <!--<Button type="primary" icon="md-add" @click="confirm"></Button>-->
                            </FormItem>
                        </Col>
                        <Col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
                            <FormItem :label="item.attr+':'" prop="store_name" label-for="store_name" v-for="(item,index) in attrList" :key="index">
                                <Tag type="border" closable color="primary" v-for="(itemn,index) in item.attrVal" :key="index">{{itemn}}</Tag>
                                <Input placeholder="请输入" v-model="item.inputVal" style="width:10%" class="input">
                                   <Icon type="md-add" slot="suffix" @click="confirmAttr(index)"/>
                                </Input>
                                <!--<Button type="primary" icon="md-add" @click="confirm"></Button>-->
                            </FormItem>
                        </Col>
                    </Row>
                </Form>
            </div>
            <div slot="footer"></div>
        </Modal>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    export default {
        name: 'attribute',
        props: {
            attrTemplate: {
                type: Boolean
            }
        },
        data () {
            return {
                val: false,
                specsVal: '',
                specs: [],
                attrVal: '',
                attrList: []
            }
        },
        watch: {
            attrTemplate: function (n) {
                this.val = n
            }
        },
        computed: {
        },
        methods: {
            cancel () {
                this.$emit('changeTemplate', false)
            },
            confirm () {
                if (this.specsVal === '') {
                    this.$Message.error('请填写规格名称')
                } else {
                    this.specs.push(this.specsVal)
                    this.attrList.push({
                        attr: this.specsVal,
                        inputVal: '',
                        attrVal: []
                    })
                    this.specsVal = ''
                    if (this.specsVal !== '') {
                        this.attrList.forEach(item => {
                            if (item.attrVal.length < 1) {
                                this.$Message.error('请填写规格属性')
                            }
                        })
                    }
                }
            },
            confirmAttr (index) {
                let attrList = this.attrList[index]
                if (attrList.inputVal === '') {
                    this.$Message.error('请填写规格属性')
                } else {
                    attrList.attrVal.push(attrList.inputVal)
                    attrList.inputVal === ''
                }
            }
        },
        mounted () {}
    }
</script>

<style scoped lang="stylus">
    .Modals >>> .input
      margin-right 10px;
</style>
