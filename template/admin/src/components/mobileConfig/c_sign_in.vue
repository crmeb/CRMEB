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
        name: 'c_sign_in',
        componentsName: 'sign_in',
        cname: '签到',
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
                ], // 当前页面组件
				oneStyle:[
					{
						components: toolCom.c_title,
						configNme: 'titleRight'
					},
					{
					    components: toolCom.c_radio,
					    configNme: 'toneConfig'
					}
				],
				twoStyle:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'bntBgColor'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'bntTxtColor'
					}
				],
				twoStyle2:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'labelBgColor'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'labelTxtColor'
					}
				],
				titleStyle:[
					{
						components: toolCom.c_title,
						configNme: 'titleCurrency'
					}
				],
				moduleStyle:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'moduleColor'
					}
				],
				moduleStyle2:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'moduleColor2'
					}
				],
				currencyStyle:[
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
				],
				setUp:0,
				type:0,
				type2:0
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
					this.setUp = nVal;
                    var arr = [this.rCom[0]];
                    if (nVal == 0) {
                        let tempArr = [
							{
								components: toolCom.c_title,
								configNme: 'titleLeft'
							},
							{
								components: toolCom.c_button_style,
								configNme: 'styleConfig'
							}
                        ]
                        this.rCom = arr.concat(tempArr)
                    } else {
						this.getRComStyle(arr,this.type,this.type2);
					}
                },
                deep: true
            },
			'configObj.styleConfig.tabVal': {
				handler (nVal, oVal) {
					this.type2 = nVal;
					var arr = [this.rCom[0]];
					if(this.setUp){
						this.getRComStyle(arr,this.type,nVal);
					}
				},
				deep: true
			},
			'configObj.toneConfig.tabVal': {
				handler (nVal, oVal) {
					this.type = nVal;
					var arr = [this.rCom[0]];
					if(this.setUp){
						this.getRComStyle(arr,nVal,this.type2);
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
			getRComStyle(arr,type,type2){
				if(type2 == 0){
					if(type == 0){
						this.rCom = [...arr,...this.oneStyle,...this.titleStyle,...this.moduleStyle2,...this.currencyStyle]
					}else{
						this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.titleStyle,...this.moduleStyle2,...this.currencyStyle]
					}
				}else{
					if(type == 0){
						this.rCom = [...arr,...this.oneStyle,...this.titleStyle,...this.moduleStyle,...this.currencyStyle]
					}else{
						this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.twoStyle2,...this.titleStyle,...this.moduleStyle,...this.currencyStyle]
					}
				}
			},
            getConfig (data) {

            }
        }
    }
</script>

<style scoped>

</style>
