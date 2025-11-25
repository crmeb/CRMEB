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
    import rightBtn from '@/components/rightBtn/index.vue';
    import { mapMutations } from 'vuex'
    export default {
        name: 'c_wechat_live',
        componentsName: 'wechat_live',
        cname: '小程序直播',
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
                // 组件参数配置
                option: {
                    submitBtn: false
                },
                configObj: {}, // 配置对象
                rCom: [
                    {
                        components: toolCom.c_set_up,
                        configNme: 'setUp'
                    }
                ] // 当前页面组件
            }
        },
        watch: {
            num (nVal) {
                // debugger;
                let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[nVal]))
                this.configObj = value;
            },
            configObj: {
                handler (nVal, oVal) {
                    this.$store.commit('mobildConfig/UPDATEARR', { num: this.num, val: nVal });
                },
                deep: true
            },
            'configObj.setUp.tabVal': {
                handler (nVal, oVal) {
                    var arr = [this.rCom[0]]
                    if (nVal == 0) {
                        let tempArr = [
							{
								components: toolCom.c_title,
								configNme: 'titleLeft'
							},
							{
							    components: toolCom.c_radio,
							    configNme: 'styleConfig'
							},
							{
								components: toolCom.c_title,
								configNme: 'titleContent'
							},
                            {
                                components: toolCom.c_slider,
                                configNme: 'numberConfig'
                            },
							{
							    components: toolCom.c_checkbox,
							    configNme: 'checkboxInfo'
							}
                        ]
                        this.rCom = arr.concat(tempArr)
                    } else {
                        let tempArr = [
                            {
                            	components: toolCom.c_title,
                            	configNme: 'titleRight'
                            },
							{
							    components: toolCom.c_slider,
							    configNme: 'liveConfig'
							},
                            {
                                components: toolCom.c_fillet,
                                configNme: 'filletImg'
                            },
							{
								components: toolCom.c_title,
								configNme: 'titleCurrency'
							},
							{
							    components: toolCom.c_bg_color,
							    configNme: 'moduleColor'
							},
							{
							    components: toolCom.c_bg_color,
							    configNme: 'bottomBgColor'
							},
							{
							    components: toolCom.c_slider,
							    configNme: 'topConfig'
							},
							{
							    components: toolCom.c_slider,
							    configNme: 'bottomConfig'
							},
							{
							    components: toolCom.c_slider,
							    configNme: 'prConfig'
							},
							{
							    components: toolCom.c_slider,
							    configNme: 'mbConfig'
							},
							{
							    components: toolCom.c_fillet,
							    configNme: 'fillet'
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
                let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[this.num]))
                this.configObj = value;
            })
        },
        methods: {
            getConfig (data) {

            }
        }
    }
</script>

<style scoped>

</style>
