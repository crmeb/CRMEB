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
        name: 'c_auxiliary_box',
        componentsName: 'auxiliary_box',
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
							    components: toolCom.c_slider,
							    configNme: 'heightConfig'
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
							    components: toolCom.c_bg_color,
							    configNme: 'bgColor'
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
							    configNme: 'lrEdge'
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
            // 获取组件参数
            getConfig (data) {},
        }
    }
</script>

<style scoped>

</style>
