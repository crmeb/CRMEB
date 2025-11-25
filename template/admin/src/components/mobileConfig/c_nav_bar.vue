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
        name: 'c_nav_bar',
        componentsName: 'nav_bar',
        cname: '导航',
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
            // formCreate: formCreate.$form()
            ...toolCom,
            rightBtn
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
				oneStyle: [
					{
						components: toolCom.c_title,
						configNme: 'titleRight'
					},
					{
					    components: toolCom.c_radio,
					    configNme: 'toneConfig'
					}
				],
				twoStyle: [
					{
					    components: toolCom.c_bg_color,
					    configNme: 'decorateColor'
					}
				],
				twoStyle2: [
					{
					    components: toolCom.c_bg_color,
					    configNme: 'decorateColor2'
					}
				],
				threeStyle: [
					{
					    components: toolCom.c_bg_color,
					    configNme: 'textColor'
					}
				],
				fourStyle: [
					{
					    components: toolCom.c_bg_color,
					    configNme: 'textColor2'
					}
				],
				fiveStyle: [
					{
					    components: toolCom.c_bg_color,
					    configNme: 'textColor3'
					}
				],
				currencyStyle: [
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
				setUp: 0,
				type: 0,
				type2: 0
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
					if(nVal == 0){
						let tempArr = [
							{
								components: toolCom.c_title,
								configNme: 'titleLeft'
							},
							{
								components: toolCom.c_button_style,
								configNme: 'styleConfig'
							},
							{
							    components: toolCom.c_radio,
							    configNme: 'stickyConfig'
							},
							{
								components: toolCom.c_title,
								configNme: 'titleTab'
							},
							{
							  components: toolCom.c_tab_list,
							  configNme: 'tabListConfig'
							}
						]
						this.rCom = arr.concat(tempArr);
					}else{
						if(this.type == 0){
							if(this.type2 == 0){
								this.rCom = [...arr,...this.oneStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.threeStyle,...this.currencyStyle];
							}
						}else if(this.type == 1){
							if(this.type2 == 0){
								this.rCom = [...arr,...this.oneStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle2,...this.fourStyle,...this.currencyStyle];
							}
						}else{
							if(this.type2 == 0){
								this.rCom = [...arr,...this.oneStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.fiveStyle,...this.currencyStyle];
							}
						}
					}
				},
				deep: true
			},
			'configObj.styleConfig.tabVal': {
				handler (nVal, oVal) {
					this.type = nVal;
					var arr = [this.rCom[0]];
					if(this.setUp){
						if(nVal == 0){
							if(this.type2 == 0){
								this.rCom = [...arr,...this.oneStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.threeStyle,...this.currencyStyle];
							}
						}else if(nVal == 1){
							if(this.type2 == 0){
								this.rCom = [...arr,...this.oneStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle2,...this.fourStyle,...this.currencyStyle];
							}
						}else{
							if(this.type2 == 0){
								this.rCom = [...arr,...this.oneStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.fiveStyle,...this.currencyStyle];
							}
						}
					}
				},
				deep: true
			},
			'configObj.toneConfig.tabVal': {
				handler (nVal, oVal) {
					this.type2 = nVal;
					var arr = [this.rCom[0]];
					if(this.setUp){
						if(this.type == 0){
							if(nVal == 0){
								this.rCom = [...arr,...this.oneStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.threeStyle,...this.currencyStyle];
							}
						}else if(this.type == 1){
							if(nVal == 0){
								this.rCom = [...arr,...this.oneStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle2,...this.fourStyle,...this.currencyStyle];
							}
						}else{
							if(nVal == 0){
								this.rCom = [...arr,...this.oneStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.fiveStyle,...this.currencyStyle];
							}
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
        }
    }
</script>

<style scoped>

</style>
