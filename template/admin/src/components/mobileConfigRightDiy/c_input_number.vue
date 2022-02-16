<template>
    <div class="numbox" v-if="configData">
        <div class="c_row-item">
        <Col class="label" span="4">
            <span>{{configData.title ||'商品数量'}}</span>
        </Col>
        <Col span="19" class="slider-box">
            <!--<Input v-model="configData.val" type="number" placeholder="请输入数量" @on-change="bindChange" style="text-align: right;"/>-->
            <InputNumber v-model="configData.val" placeholder="请输入数量" :step="1" :max="100" :min="1" @on-change="bindChange" style="text-align: right;"></InputNumber>
        </Col>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'c_input_number',
        props: {
            configObj: {
                type: Object
            },
            configNme: {
                type: String
            }
        },
        data () {
            return {
                defaults: {},
                sliderWidth: 0,
                configData: {}
            }
        },
        created () {
            this.defaults = this.configObj
            this.configData = this.configObj[this.configNme]
        },
        watch: {
            configObj: {
                handler (nVal, oVal) {
                    this.defaults = nVal
                    this.configData = nVal[this.configNme]
                },
                immediate: true,
                deep: true
            }
        },
        methods:{
            bindChange(){
                this.$emit('getConfig', { name: 'number', numVal: this.configData.val })
            }
        }
    }
</script>

<style scoped lang="stylus">
    .ivu-input-number
        width 100%
    /deep/.ivu-input
        font-size 13px!important
    .numbox
        display flex
        align-items center
        margin-bottom 20px
        span
            width 80px
            color #999
            /*font-size 12px*/
    .c_row-item
        width 100%
</style>
