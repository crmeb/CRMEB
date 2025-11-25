<template>
    <div class="mobile-config">
        <div  v-for="(item,key) in rCom" :key="key">
            <component :is="item.components.name" :configObj="configObj" ref="childData" :configNme="item.configNme" :key="key" :index="activeIndex" :num="item.num"></component>
        </div>
        <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
    </div>
</template>

<script>
    import toolCom from '@/components/mobileConfigRight/index.js'
    import rightBtn from '@/components/rightBtn/index.vue';
    import { mapState, mapMutations, mapActions } from 'vuex'
    import { newcomerList } from '@/api/diy'
    export default {
        name: 'c_new_vip',
        componentsName: 'home_new_vip',
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
					}
				],
				twoStyle01:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'tipsColor'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'integralBgColor'
					}
				],
				twoStyle02:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'bntColor'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'integralTxtColor'
					}
				],
				threeStyle:[
					{
						components: toolCom.c_title,
						configNme: 'titleCoupon'
					},
					{
					    components: toolCom.c_radio,
					    configNme: 'toneCouponConfig'
					}
				],
				fourStyle01:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'couponMoneyColor'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'bntTxtColor'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'couponTypeColor'
					},
					{
					    components: toolCom.c_slider,
					    configNme: 'spacingConfig'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'vipBgColor'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'couponBgColor'
					}
				],
				fourStyle02:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'couponMoneyColor'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'couponBgColor2'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'bntBgColor'
					},
					{
					    components: toolCom.c_slider,
					    configNme: 'spacingConfig2'
					}
				],
				fiveStyle:[
					{
						components: toolCom.c_title,
						configNme: 'titleGoods'
					},
					{
					    components: toolCom.c_radio,
					    configNme: 'toneGoodsConfig'
					}
				],
				sixStyle:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'priceColor'
					}
				],
				currencyTitleStyle:[
					{
						components: toolCom.c_title,
						configNme: 'titleCurrency'
					}
				],
				moduleColorStyle:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'moduleColor'
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
				type2:0,
				type3:0,
				type4:0,
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
                    var arr = [this.rCom[0]];
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
                                components: toolCom.c_checkbox,
                                configNme: 'checkboxInfo'
                            }
                        ];
                        this.rCom = arr.concat(tempArr)
                    } else {
						this.getRComStyle(arr,this.type,this.type2,this.type3,this.type4)
                    }
                },
                deep: true
            },
			'configObj.styleConfig.tabVal': {
				handler (nVal, oVal) {
					this.type = nVal;
					var arr = [this.rCom[0]];
					if(this.setUp){
						this.getRComStyle(arr,nVal,this.type2,this.type3,this.type4)
					}
				},
				deep: true
			},
			'configObj.toneConfig.tabVal': {
				handler (nVal, oVal) {
					this.type2 = nVal;
					var arr = [this.rCom[0]];
					if(this.setUp){
						this.getRComStyle(arr,this.type,nVal,this.type3,this.type4)
					}
				},
				deep: true
			},
			'configObj.toneCouponConfig.tabVal': {
				handler (nVal, oVal) {
					this.type3 = nVal;
					var arr = [this.rCom[0]];
					if(this.setUp){
						this.getRComStyle(arr,this.type,this.type2,nVal,this.type4)
					}
				},
				deep: true
			},
			'configObj.toneGoodsConfig.tabVal': {
				handler (nVal, oVal) {
					this.type4 = nVal;
					var arr = [this.rCom[0]];
					if(this.setUp){
						this.getRComStyle(arr,this.type,this.type2,this.type3,nVal)
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
			getRComStyle(arr,type,type2,type3,type4){
				if(type == 0){
					if(type2 == 0){
						if(type3 == 0){
							if(type4 == 0){
								this.rCom = [...arr,...this.oneStyle,...this.threeStyle,...this.fiveStyle,...this.currencyTitleStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.threeStyle,...this.fiveStyle,...this.sixStyle,...this.currencyTitleStyle,...this.currencyStyle];
							}
						}else{
							if(type4 == 0){
								this.rCom = [...arr,...this.oneStyle,...this.threeStyle,...this.fourStyle01,...this.fiveStyle,...this.currencyTitleStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.threeStyle,...this.fourStyle01,...this.fiveStyle,...this.sixStyle,...this.currencyTitleStyle,...this.currencyStyle];
							}
						}
					}else{
						if(type3 == 0){
							if(type4 == 0){
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle01,...this.threeStyle,...this.fiveStyle,...this.currencyTitleStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle01,...this.threeStyle,...this.fiveStyle,...this.sixStyle,...this.currencyTitleStyle,...this.currencyStyle];
							}
						}else{
							if(type4 == 0){
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle01,...this.threeStyle,...this.fourStyle01,...this.fiveStyle,...this.currencyTitleStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle01,...this.threeStyle,...this.fourStyle01,...this.fiveStyle,...this.sixStyle,...this.currencyTitleStyle,...this.currencyStyle];
							}
						}
					}
				}else{
					if(type2 == 0){
						if(type3 == 0){
							this.rCom = [...arr,...this.oneStyle,...this.threeStyle,...this.currencyTitleStyle,...this.moduleColorStyle,...this.currencyStyle];
						}else{
							this.rCom = [...arr,...this.oneStyle,...this.threeStyle,...this.fourStyle02,...this.currencyTitleStyle,...this.moduleColorStyle,...this.currencyStyle];
						}
					}else{
						if(type3 == 0){
							this.rCom = [...arr,...this.oneStyle,...this.twoStyle02,...this.threeStyle,...this.currencyTitleStyle,...this.moduleColorStyle,...this.currencyStyle];
						}else{
							this.rCom = [...arr,...this.oneStyle,...this.twoStyle02,...this.threeStyle,...this.fourStyle02,...this.currencyTitleStyle,...this.moduleColorStyle,...this.currencyStyle];
						}
					}
				}
			},
            // 获取组件参数
            // getConfig (data) {
            //     newcomerList({
            //         page: 1,
            //         limit: this.configObj.numConfig.val,
            //         priceOrder: this.configObj.itemSort.type == 2 ? 'desc' : '',
            //         salesOrder: this.configObj.itemSort.type == 1 ? 'desc' : ''
            //     }).then(res=>{
            //         this.configObj.newVipList.list = res.data;
            //     }).catch(err=>{
            //        return this.$message.error(err.msg);
            //     })
            // },
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
