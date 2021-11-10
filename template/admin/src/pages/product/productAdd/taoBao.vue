<template>
    <div class="Box">
        <Card>
            <div>生成的商品默认是没有上架的，请手动上架商品！
                <a href="http://help.crmeb.net/crmeb-v4/1863579" v-if="copyConfig.copy_type == 2"
                   target="_blank">如何配置密钥</a>
                <span v-else>您当前剩余{{copyConfig.copy_num}}条采集次数，<a href="#" @click="mealPay('copy')">增加采集次数</a></span>
            </div>
            <div>商品采集设置：设置 > 系统设置 > 第三方接口设置 > 采集商品配置</div>
        </Card>
        <Form class="formValidate mt20" ref="formValidate" :label-width="120" label-position="right"
              @submit.native.prevent>
            <Row :gutter="24" type="flex">
                <Col span="18">
                    <FormItem label="链接地址：">
                        <Input search enter-button="确定" v-model="soure_link" placeholder="请输入链接地址" class="numPut"
                               @on-search="add"/>
                    </FormItem>
                </Col>
            </Row>
        </Form>
        <Spin size="large" fix v-if="spinShow"></Spin>
    </div>
</template>

<script>
    import {crawlFromApi, copyConfigApi} from '@/api/product'

    export default {
        name: 'taoBao',
        data() {
            return {
                soure_link: '',
                spinShow: false,
                grid: {
                    xl: 8,
                    lg: 8,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                grid2: {
                    xl: 12,
                    lg: 12,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                copyConfig: {
                    'copy_type': 2,
                    'copy_num': 0,
                },
                artFrom: {
                    type: 'taobao',
                    url: ''
                },
            }
        },
        computed: {},
        created() {
        },
        mounted() {
            this.getCopyConfig()
        },
        methods: {
            mealPay(val) {
                this.$router.push({path: '/admin/setting/sms/sms_pay/index', query: {type: val}});
            },
            getCopyConfig() {
                copyConfigApi().then(res => {
                    this.copyConfig.copy_type = res.data.copy_type
                    this.copyConfig.copy_num = res.data.copy_num
                })
            },
            // 生成表单
            add() {
                if (this.soure_link) {
                    var reg = /(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?/
                    if (!reg.test(this.soure_link)) {
                        return this.$Message.warning('请输入以http开头的地址！')
                    }
                    this.spinShow = true
                    this.artFrom.url = this.soure_link
                    crawlFromApi(this.artFrom).then(res => {
                        let info = res.data.productInfo;
                        this.$emit('on-close', info);
                        this.spinShow = false;
                    }).catch(res => {
                        this.spinShow = false
                        this.$Message.error(res.msg)

                    })
                } else {
                    this.$Message.warning('请输入链接地址！')
                }
            },
        }
    }
</script>

<style scoped lang="stylus">
    /deep/ .ivu-form-item-content {
        line-height unset !important;
    }

    .Box .ivu-radio-wrapper {
        margin-right: 25px;
    }

    .Box .numPut {
        width: 100% !important;
    }
</style>
