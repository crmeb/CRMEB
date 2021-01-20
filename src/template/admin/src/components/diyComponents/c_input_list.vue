<template>
    <div class="numbox" v-if="datas[name]">
        <div class="c_row-item" v-for="(item,index) in datas[name].list" :key="index">
            <Col class="label">
                <span>{{item.title}}</span>
            </Col>
            <Col span="19" class="slider-box">
                <Input v-model="item.val" :placeholder="item.pla" :maxlength="item.max" style="text-align: right;"/>
            </Col>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'c_input_list',
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
                defaults: {},
                datas: this.configData
            }
        },
        mounted () {},
        watch: {
            configData: {
                handler (nVal, oVal) {
                    this.datas = nVal
                },
                immediate: true,
                deep: true
            }
        },
        methods: {}
    }
</script>

<style scoped lang="stylus">
    .numbox
        margin 20px 0 10px 0
        span
            width 80px
            color #999
    .c_row-item
        width 100%
        &~.c_row-item
             margin-top 20px
</style>