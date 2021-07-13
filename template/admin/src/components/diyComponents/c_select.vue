<template>
    <div class="slider-box">
        <div class="c_row-item">
            <Col class="label" span="4" v-if="datas[name].title">
                {{datas[name].title}}
            </Col>
            <Col span="19" class="slider-box">
                <Select v-model="datas[name].activeValue" clearable @on-change="sliderChange">
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
            },
            configNum: {
                type: Number | String,
                default: 'default'
            }
        },
        data () {
            return {
                defaults: {},
                datas: this.configData[this.configNum]
            }
        },
        mounted () {
            if(this.name==='selectConfig'){
                this.bus.$on('upData',data=>{
                    this.datas[this.name].list = data
                    console.log(data);
                    console.log('22222222222222');
                    this.bus.$off()
                })
            }
        },
        watch: {
            configData: {
                handler (nVal, oVal) {
                    console.log('44444444444',nVal);
                    console.log('88888888888',oVal);
                    this.datas = nVal[this.configNum]
                    console.log(nVal[this.configNum]);
                    console.log('33333333333');
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
    .slider-box{
        margin-top: 10px;
    }
</style>
