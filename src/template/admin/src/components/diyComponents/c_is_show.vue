<template>
    <div class="c_row-item">
        <Col class="label" span="4">
            是否显示
        </Col>
        <Col span="19">
            <i-switch v-model="datas[name].val"/>
        </Col>
    </div>
</template>

<script>
    export default {
        name: 'c_is_show',
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

<style scoped>
    .c_row-item{
        margin-bottom: 10px;
    }
</style>