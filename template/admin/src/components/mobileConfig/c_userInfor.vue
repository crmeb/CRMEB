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
    import { mapState, mapMutations, mapActions } from 'vuex';
    export default {
        name: 'c_userInfor',
        componentsName: 'home_userInfor',
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
                ],
				oneStyle:[
					{
						components: toolCom.c_title,
						configNme: 'titleRight'
					},
					{
					    components: toolCom.c_radio,
					    configNme: 'toneConfig'
					},
				],
				twoStyle:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'progressColor'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'progressBgColor'
					}
				],
				currencyStyle:[
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
				],
				setUp:0,
				type:0
            }
        },
        watch: {
            num (nVal) {
                this.configObj = this.$store.state.mobildConfig.defaultArray[nVal]
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
                                components: toolCom.c_checkbox,
                                configNme: 'checkboxInfo'
                            },
							{
								components: toolCom.c_title,
								configNme: 'titleImg'
							},
							{
							  components: toolCom.c_upload_img,
							  configNme: 'logoConfig'
							}
                        ];
                        this.rCom = arr.concat(tempArr);
                    } else {
						if(this.type == 0){
						   this.rCom = [...arr,...this.oneStyle,...this.currencyStyle]
						}else{
						   this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.currencyStyle]
						}
                    }
                },
                deep: true
            },
			'configObj.toneConfig.tabVal': {
				handler (nVal, oVal) {
					this.type = nVal;
					var arr = [this.rCom[0]]
					if(this.setUp){
						if(nVal == 0){
						   this.rCom = [...arr,...this.oneStyle,...this.currencyStyle]
						}else{
						   this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.currencyStyle]
						}
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
            getConfig (data) {

            },
            handleSubmit (name) {
                let obj = {}
                obj.activeIndex = this.activeIndex
                obj.data = this.configObj
                this.add(obj);
            },
            ...mapMutations({
                add: 'mobildConfig/UPDATEARR'
            })
        }
    }
</script>

<style scoped>

</style>
