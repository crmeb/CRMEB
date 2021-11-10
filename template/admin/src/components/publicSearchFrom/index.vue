<template>
    <div>
        <Form ref="orderData" :label-width="80" label-position="right" class="tabform">
            <Row :gutter="24" type="flex" v-for="(item,index) in fromList" :key="index">
                <Col :xl="8" :lg="8" :md="8" :sm="24" :xs="24">
                    <FormItem :label="item.title+'：'">
                        <RadioGroup type="button" v-model="date">
                            <Radio :label="itemn.text" v-for="(itemn,indexn) in item.fromTxt" :key="indexn">{{itemn.text}}{{item.num}}</Radio>
                        </RadioGroup>
                    </FormItem>
                </Col>
                <Col v-if="item.custom">
                    <FormItem class="tab_data">
                        <DatePicker :editable="false" format="yyyy/MM/dd" type="daterange" placement="bottom-end" placeholder="请选择时间" style="width: 200px;"></DatePicker>
                    </FormItem>
                </Col>
            </Row>
            <Row :gutter="24" type="flex" v-if="isExist.existOne">
                <Col span="10" class="mr">
                    <FormItem :label="searchFrom.title+'：'" prop="real_name" label-for="real_name" >
                        <Input search enter-button :placeholder="searchFrom.place" element-id="name" />
                    </FormItem>
                </Col>
                <Col>
                    <Button class="mr">导出</Button>
                    <span class="Refresh">刷新</span><Icon type="ios-refresh" />
                </Col>
            </Row>
            <Row :gutter="24" type="flex" class="withdrawal" v-if="isExist.existTwo">
                <Col span="2.5" class="item">
                    <TreeSelect v-model="withdrawalTxt" :data="treeData.withdrawal" class="perW160" @on-change="changeTree"/>
                </Col>
                <Col span="2.5" class="item">
                    <TreeSelect v-model="paymentTxt" :data="treeData.payment" class="perW160" @on-change="changeTree"/>
                </Col>
                <Col span="6"  class="item">
                    <Input search enter-button placeholder="微信名称、姓名、支付宝账号、银行卡号" element-id="name" />
                </Col>
            </Row>
        </Form>
    </div>
</template>
<style scoped lang="stylus">
    .Refresh
        font-size 12px
        color #1890FF
        cursor pointer
    .ivu-form-item
        margin-bottom 10px
    .tabform >>> .ivu-col
        padding 0!important;
    .tabform >>> .ivu-row-flex
       margin 0!important;
    .withdrawal >>> .item
       margin-right 10px;
    .tab_data >>> .ivu-form-item-content
       margin-left 10px!important
    .ivu-form-label-left >>> .ivu-form-item-label
        text-align: right;
</style>
<script>
    import { mapState } from 'vuex'
    export default {
        name: 'publicSearchFrom',
        props: {
            fromList: {
                type: Array
            },
            searchFrom: {
                type: Object
            },
            treeData: {
                type: Object
            },
            isExist: {
                type: Object
            }
        },
        data () {
            return {
                date: '全部',
                withdrawalTxt: '提现状态',
                paymentTxt: '提现方式'
            }
        },
        computed: {

        },
        mounted () {

        },
        methods: {
            changeTree () {

            }
        }
    }
</script>
