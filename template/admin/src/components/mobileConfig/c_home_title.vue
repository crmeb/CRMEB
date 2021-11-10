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
    import { mapState, mapMutations, mapActions } from 'vuex'
    export default {
        name: 'c_home_bargain',
        componentsName: 'home_bargain',
        components: {
            ...toolCom,
            rightBtn
        },
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
        data () {
            return {
                configObj: {},
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
                                components: toolCom.c_input_item,
                                configNme: 'titleConfig'
                            },
                            {
                                components: toolCom.c_input_item,
                                configNme: 'linkConfig'
                            }
                        ]
                        this.rCom = arr.concat(tempArr)
                    } else {
                        let tempArr = [
                            {
                                components: toolCom.c_bg_color,
                                configNme: 'titleColor'
                            },
                            {
                                components: toolCom.c_bg_color,
                                configNme: 'themeColor'
                            },
                            {
                                components: toolCom.c_txt_tab,
                                configNme: 'textPosition'
                            },
                            {
                                components: toolCom.c_txt_tab,
                                configNme: 'textStyle'
                            },
                            {
                                components: toolCom.c_txt_tab,
                                configNme: 'bgStyle'
                            },
                            {
                                components: toolCom.c_slider,
                                configNme: 'prConfig'
                            },
                            {
                                components: toolCom.c_slider,
                                configNme: 'fontSize'
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
            getConfig (data) {},
        }
    }
</script>

<style scoped lang="stylus">
    .title-tips
        padding-bottom 10px
        font-size 14px
        color #333
        span
            margin-right 14px
            color #999
</style>
