<template>
    <div class="slider-box">
        <div class="c_row-item">
            <Col class="label" span="4" v-if="datas[name].title">
                {{datas[name].title}}
            </Col>
            <Col span="19" class="slider-box">
                <Select v-model="datas[name].activeValue" @on-change="sliderChange">
                    <Option v-for="(item,index) in datas[name].list" :value="item.activeValue" :key="index">{{ item.title }}</Option>
                </Select>
            </Col>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'c_select',
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
        mounted () {
            this.bus.$on('upData',data=>{
                this.datas[this.name].list = data
                this.bus.$off()
            })
        },
        watch: {
            configData: {
                handler (nVal, oVal) {
                    this.datas = nVal
                },
                deep: true
            }
        },
        methods: {
            sliderChange (e) {
                this.$emit('getConfig', { name: 'select', values: e })
            }
        }
    }
</script>

<style scoped>

</style>
