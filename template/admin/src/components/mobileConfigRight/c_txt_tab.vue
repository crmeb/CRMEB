<template>
    <div class="txt_tab" v-if="configData">
        <div class="c_row-item">
            <Col class="c_label">
                {{configData.title}}
                <span>{{configData.list[configData.type].val}}</span>
            </Col>
            <Col class="color-box">
                <RadioGroup v-model="configData.type" type="button" @on-change="radioChange($event)">
                    <Radio :label="key" v-for="(radio,key) in configData.list" :key="key">
                        <span class="iconfont-diy" :class="radio.icon" v-if="radio.icon"></span>
                        <span v-else>{{radio.val}}</span>
                    </Radio>
                </RadioGroup>
            </Col>
        </div>
    </div>

</template>

<script>
    export default {
        name: 'c_txt_tab',
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
        methods: {
            radioChange (e) {
                if(this.configData.name !== 'itemSstyle' && this.configData.name !== 'bgStyle' && this.configData.name !== 'conStyle'){
                    this.$emit('getConfig', { name: 'radio', values: e })
                }
            }
        }
    }
</script>

<style scoped lang="stylus">
    .txt_tab
        margin-top 20px
    .c_row-item
        margin-bottom 20px
    .row-item
        display flex
        justify-content space-between
        align-items center
    .iconfont
        font-size 18px
</style>
