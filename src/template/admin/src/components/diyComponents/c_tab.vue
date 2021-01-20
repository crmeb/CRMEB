<template>
    <div style="margin-bottom: 20px">
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
            }
        },
        data () {
            return {
                formData: {
                    type: 0
                },
                defaults: {},
                datas: this.configData,
                upDataGoodList : ''
            }
        },
        watch: {
            configData: {
                handler (nVal, oVal) {
                    this.datas = nVal
                },
                deep: true
            }
        },
        mounted () {
            this.$nextTick(()=>{
                // this.datas = this.configData;
                let name = this.$store.state.userInfo.pageName;
                this.upDataGoodList = name+'/upDataGoodList';
                this.$store.commit(this.upDataGoodList, this.datas.tabConfig.tabVal);
            })
        },
        methods: {
            radioChange (e) {
                this.$emit('getConfig', e);
                this.$store.commit(this.upDataGoodList, e);
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