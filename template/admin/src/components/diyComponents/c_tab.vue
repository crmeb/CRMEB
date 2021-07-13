<template>
    <div>
        <div class="title-tips" v-if="datas[name].tabList">
            <span>选择模板</span>{{datas[name].tabList[datas[name].tabVal].name}}
        </div>
        <div class="radio-box" :class="{on:datas[name].type == 1}">
            <RadioGroup v-model="datas[name].tabVal" type="button" size="large" @on-change="radioChange($event)">
                <Radio :label="index" v-for="(item,index) in datas[name].tabList" :key="index" >
                    <span class="iconfont" :class="item.icon" v-if="item.icon"></span>
                    <span v-else>{{item.name}}</span>
                </Radio>
            </RadioGroup>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'c_tab',
        props: {
            name: {
                type: String
            },
            configData:{
                type:null
            },
            configNum: {
                type: Number | String,
                default: 'default'
            },
            moduleName: {
                type: String
            }
        },
        data () {
            return {
                formData: {
                    type: 0
                },
                defaults: {},
                datas: this.configData[this.configNum]
            }
        },
        watch: {
            configData: {
                handler (nVal, oVal) {
                    this.datas = nVal[this.configNum];
                    this.$store.commit('moren/upDataGoodList', {name: this.moduleName,type: this.datas.tabConfig.tabVal});
                },
                deep: true
            }
        },
        mounted () {
            this.$nextTick(()=>{})
        },
        methods: {
            radioChange (e) {
                this.$emit('getConfig', e);
                this.$store.commit('moren/upDataGoodList', {name: this.moduleName,type: e});
            }
        }
    }
</script>

<style scoped lang="stylus">
    .radio-box
        /deep/.ivu-radio-group-button
            display flex
            width 100%
            .ivu-radio-wrapper
                flex 1
                display flex
                align-items center
                justify-content center
        &.on
            /deep/.ivu-radio-group-button
                .ivu-radio-wrapper
                    flex 1
    .title-tips
        padding-bottom 10px
        font-size 14px
        color #333
        span
            margin-right 14px
            color #999
    .iconfont
        font-size 20px
        line-height 18px
</style>