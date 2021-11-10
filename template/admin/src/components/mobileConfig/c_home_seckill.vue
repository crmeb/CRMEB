<template>
    <div class="mobile-config">
        <div  v-for="(item,key) in rCom" :key="key">
            <component :is="item.components.name" :configObj="configObj" ref="childData" :configNme="item.configNme" :key="key" @getConfig="getConfig" :index="activeIndex" :num="item.num"></component>
        </div>
        <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
    </div>
</template>

<script>
    import toolCom from '@/components/mobileConfigRight/index.js'
    import { mapState, mapMutations, mapActions } from 'vuex'
    import rightBtn from '@/components/rightBtn/index.vue';
    export default {
        name: 'c_home_seckill',
        componentsName: 'home_seckill',
        cname: '秒杀',
        props: {
            activeIndex: {
                type: null
            },
            num: {
                type: null
            },
            index: {
                type: null
            }
        },
        components: {
            ...toolCom,
            rightBtn
        },
        data () {
            return {
                configObj: {

                },
                rCom: [
                    {
                        components: toolCom.c_set_up,
                        configNme: 'setUp'
                    }
                ]
            }
        },
        watch: {
            num (nVal) {
                // debugger;
                let value = JSON.parse(JSON.stringify(this.$store.state.admin.mobildConfig.defaultArray[nVal]))
                this.configObj = value;
            },
            configObj: {
                handler (nVal, oVal) {
                    this.$store.commit('admin/mobildConfig/UPDATEARR', { num: this.num, val: nVal });
                },
                deep: true
            },
            'configObj.setUp.tabVal': {
                handler (nVal, oVal) {
                    var arr = [this.rCom[0]]
                    if (nVal == 0) {
                        let tempArr = [
                            {
                                components: toolCom.c_input_number,
                                configNme: 'numberConfig'
                            },
                            {
                                components: toolCom.c_is_show,
                                configNme: 'priceShow'
                            },
                            {
                                components: toolCom.c_is_show,
                                configNme: 'discountShow'
                            },
                            {
                                components: toolCom.c_is_show,
                                configNme: 'titleShow'
                            },
                            {
                                components: toolCom.c_is_show,
                                configNme: 'seckillShow'
                            }
                        ]
                        this.rCom = arr.concat(tempArr)
                    } else {
                        let tempArr = [
                            {
                                components: toolCom.c_upload_img,
                                configNme: 'imgConfig'
                            },
                            {
                                components: toolCom.c_bg_color,
                                configNme: 'countDownColor'
                            },
                            {
                                components: toolCom.c_bg_color,
                                configNme: 'themeColor'
                            },
                            {
                                components: toolCom.c_bg_color,
                                configNme: 'bgColor'
                            },
                            {
                                components: toolCom.c_txt_tab,
                                configNme: 'conStyle'
                            },
                            {
                                components: toolCom.c_slider,
                                configNme: 'prConfig'
                            },
                            {
                                components: toolCom.c_slider,
                                configNme: 'lrConfig'
                            },
                            {
                                components: toolCom.c_slider,
                                configNme: 'mbConfig'
                            }
                        ]
                        this.rCom = arr.concat(tempArr)
                    }
                },
                deep: true
            }
        },
        mounted () {
            this.$nextTick(() => {
                let value = JSON.parse(JSON.stringify(this.$store.state.admin.mobildConfig.defaultArray[this.num]))
                this.configObj = value;
            })
        },
        methods: {
            // 获取组件参数
            getConfig (data) {}

        }
    }
</script>

<style scoped>

</style>
