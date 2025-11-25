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
                configObj: {},
                rCom: [
                    {
                        components: toolCom.c_set_up,
                        configNme: 'setUp'
                    }
                ],
				oneContent:[
					{
						components: toolCom.c_title,
						configNme: 'titleLeft'
					},
					{
					    components: toolCom.c_radio,
					    configNme: 'styleConfig'
					}
				],
				oneContentImg:[
					{
					  components: toolCom.c_upload_img,
					  configNme: 'imgBgConfig'
					},
				],
				twoContent:[
					{
					    components: toolCom.c_radio,
					    configNme: 'titleConfig'
					}
				],
				twoContentImg:[
					{
					  components: toolCom.c_upload_img,
					  configNme: 'imgConfig'
					}
				],
				twoContentColorImg:[
					{
					  components: toolCom.c_upload_img,
					  configNme: 'imgColorConfig'
					}
				],
				twoContentText:[
					{
					  components: toolCom.c_input_item,
					  configNme: 'titleTxtConfig'
					}
				],
				threeContent:[
					{
					  components: toolCom.c_input_item,
					  configNme: 'rightBntConfig'
					},
					{
						components: toolCom.c_title,
						configNme: 'titleGoodsList'
					},
					{
					    components: toolCom.c_radio,
					    configNme: 'goodStyleConfig'
					},
					{
						components: toolCom.c_title,
						configNme: 'titleGoods'
					},
					{
					    components: toolCom.c_slider,
					    configNme: 'numberConfig'
					},
					{
					    components: toolCom.c_checkbox,
					    configNme: 'checkboxInfo'
					}
				],
				fourContent:[
					{
					    components: toolCom.c_radio,
					    configNme: 'seckillConfig'
					}
				],
				oneStyle:[
					{
						components: toolCom.c_title,
						configNme: 'titleRight'
					}
				],
				twoStyle:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'headerBgColor'
					}
				],
				threeStyle:[
					{
					    components: toolCom.c_radio,
					    configNme: 'titleText'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'titleColor'
					},
					{
					    components: toolCom.c_slider,
					    configNme: 'titleNumber'
					}
				],
				fourStyle:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'headerBntColor'
					}
				],
				fourStyle2:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'headerBntColor2'
					}
				],
				fourBntStyle:[
					{
					    components: toolCom.c_slider,
					    configNme: 'bntNumber'
					}
				],
				fourColorStyle:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'tipsColor'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'numberBgColor'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'numberColor'
					},
				],
				fourColorStyle2:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'tipsColor2'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'numberBgColor2'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'numberColor2'
					},
				],
				fourGoodsStyle:[
					{
						components: toolCom.c_title,
						configNme: 'titleGoodsStyle'
					},
					{
					    components: toolCom.c_fillet,
					    configNme: 'filletImg'
					},
					{
					    components: toolCom.c_radio,
					    configNme: 'goodsName'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'goodsNameColor'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'goodsPriceColor'
					},
					{
					    components: toolCom.c_radio,
					    configNme: 'toneConfig'
					}
				],
				fiveStyle:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'seckillPriceColor'
					}
				],
				fiveStyle2:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'seckillPriceColor2'
					}
				],
				progressStyle:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'progressColor'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'progressTxtColor'
					}
				],
				bntStyle:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'goodsBntColor'
					}
				],
				sixStyle:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'goodsBntTxtColor'
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
				type:0,
				type2:0,
				type3:0,
				type4:0,
				type5:0
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
						this.getRComContent(arr,this.type,this.type2,this.type3)
                    } else {
						this.getRComStyle(arr,this.type,this.type2,this.type3,this.type5,this.type4)
                    }
                },
                deep: true
            },
			'configObj.styleConfig.tabVal': {
				handler (nVal, oVal) {
					this.type = nVal;
					var arr = [this.rCom[0]];
					if(this.setUp == 0){
						this.getRComContent(arr,nVal,this.type2,this.type3)
					}else{
						this.getRComStyle(arr,nVal,this.type2,this.type3,this.type5,this.type4)
					}
				},
				deep: true
			},
			'configObj.titleConfig.tabVal': {
				handler (nVal, oVal) {
					this.type2 = nVal;
					var arr = [this.rCom[0]];
					if(this.setUp == 0){
						this.getRComContent(arr,this.type,nVal,this.type3)
					}else{
						this.getRComStyle(arr,this.type,nVal,this.type3,this.type5,this.type4)
					}
				},
				deep: true
			},
			'configObj.goodStyleConfig.tabVal':{
				handler (nVal, oVal) {
					this.type3 = nVal;
					var arr = [this.rCom[0]];
					if(this.setUp == 0){
						this.getRComContent(arr,this.type,this.type2,nVal)
					}else{
						this.getRComStyle(arr,this.type,this.type2,nVal,this.type5,this.type4)
					}
				},
				deep: true
			},
			'configObj.seckillConfig.tabVal':{
				handler (nVal, oVal) {
					this.type5 = nVal;
					var arr = [this.rCom[0]];
					if(this.setUp){
					   this.getRComStyle(arr,this.type,this.type2,this.type3,nVal,this.type4)
					}
				},
				deep: true
			},
			'configObj.toneConfig.tabVal':{
				handler (nVal, oVal) {
					this.type4 = nVal;
					var arr = [this.rCom[0]];
					if(this.setUp){
					   this.getRComStyle(arr,this.type,this.type2,this.type3,this.type5,nVal)
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
			getRComContent(arr,type,type2,type3){
				if(type == 0){
					if(type2 == 0){
						if(type3 == 2){
							this.rCom = [...arr,...this.oneContent,...this.twoContent,...this.twoContentColorImg,...this.threeContent]
						}else{
							this.rCom = [...arr,...this.oneContent,...this.twoContent,...this.twoContentColorImg,...this.threeContent,...this.fourContent]
						}
					}else{
						if(type3 == 2){
							this.rCom = [...arr,...this.oneContent,...this.twoContent,...this.twoContentText,...this.threeContent]
						}else{
							this.rCom = [...arr,...this.oneContent,...this.twoContent,...this.twoContentText,...this.threeContent,...this.fourContent]
						}
					}
				}else{
					if(type2 == 0){
						if(type3 == 2){
							this.rCom = [...arr,...this.oneContent,...this.oneContentImg,...this.twoContent,...this.twoContentImg,...this.threeContent]
						}else{
							this.rCom = [...arr,...this.oneContent,...this.oneContentImg,...this.twoContent,...this.twoContentImg,...this.threeContent,...this.fourContent]
						}
					}else{
						if(type3 == 2){
							this.rCom = [...arr,...this.oneContent,...this.oneContentImg,...this.twoContent,...this.twoContentText,...this.threeContent]
						}else{
							this.rCom = [...arr,...this.oneContent,...this.oneContentImg,...this.twoContent,...this.twoContentText,...this.threeContent,...this.fourContent]
						}
					}
				}
			},
			getRComStyle(arr,type,type2,type3,type5,type4){
				let obj = [...arr,...this.oneStyle,...this.twoStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourColorStyle2,...this.fourGoodsStyle,...this.currencyStyle];
				let obj2 = [...arr,...this.oneStyle,...this.twoStyle,...this.threeStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourColorStyle2,...this.fourGoodsStyle,...this.currencyStyle];
				let obj3 = [...arr,...this.oneStyle,...this.fourStyle,...this.fourBntStyle,...this.fourColorStyle,...this.fourGoodsStyle,...this.currencyStyle];
				let obj4 = [...arr,...this.oneStyle,...this.threeStyle,...this.fourStyle,...this.fourBntStyle,...this.fourColorStyle,...this.fourGoodsStyle,...this.currencyStyle];
				if(type == 0){
					if(type2 == 0){
						if(type3 == 0){
							if(type5 == 0){
								if(type4 == 0){
									this.rCom = obj
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourColorStyle2,...this.fourGoodsStyle,...this.fiveStyle,...this.progressStyle,...this.bntStyle,...this.sixStyle,...this.currencyStyle]
								}
							}else{
								if(type4 == 0){
									this.rCom = obj
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourColorStyle2,...this.fourGoodsStyle,...this.fiveStyle,...this.progressStyle,...this.currencyStyle]
								}
							}
						}else if(type3 == 2){
							if(type4 == 0){
								this.rCom = obj
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourColorStyle2,...this.fourGoodsStyle,...this.fiveStyle2,...this.bntStyle,...this.currencyStyle]
							}
						}else{
							if(type5 == 0){
								if(type4 == 0){
									this.rCom = obj
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourColorStyle2,...this.fourGoodsStyle,...this.fiveStyle,...this.bntStyle,...this.sixStyle,...this.currencyStyle]
								}
							}else{
								if(type4 == 0){
									this.rCom = obj
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourColorStyle2,...this.fourGoodsStyle,...this.fiveStyle,...this.currencyStyle]
								}
							}
						}
					}else{
						if(type3 == 0){
							if(type5 == 0){
								if(type4 == 0){
									this.rCom = obj2
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.threeStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourColorStyle2,...this.fourGoodsStyle,...this.fiveStyle,...this.progressStyle,...this.bntStyle,...this.sixStyle,...this.currencyStyle]
								}
							}else{
								if(type4 == 0){
									this.rCom = obj2
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.threeStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourColorStyle2,...this.fourGoodsStyle,...this.fiveStyle,...this.progressStyle,...this.currencyStyle]
								}
							}
						}else if(type3 == 2){
							if(type4 == 0){
								this.rCom = obj2
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.threeStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourColorStyle2,...this.fourGoodsStyle,...this.fiveStyle2,...this.bntStyle,...this.currencyStyle]
							}
						}else{
							if(type5 == 0){
								if(type4 == 0){
									this.rCom = obj2
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.threeStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourColorStyle2,...this.fourGoodsStyle,...this.fiveStyle,...this.bntStyle,...this.sixStyle,...this.currencyStyle]
								}
							}else{
								if(type4 == 0){
									this.rCom = obj2
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.threeStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourColorStyle2,...this.fourGoodsStyle,...this.fiveStyle,...this.currencyStyle]
								}
							}
						}
					}
				}else{
					if(type2 == 0){
						if(type3 == 0){
							if(type5 == 0){
								if(type4 == 0){
									this.rCom = obj3
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.fourStyle,...this.fourBntStyle,...this.fourColorStyle,...this.fourGoodsStyle,...this.fiveStyle,...this.progressStyle,...this.bntStyle,...this.sixStyle,...this.currencyStyle]
								}
							}else{
								if(type4 == 0){
									this.rCom = obj3
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.fourStyle,...this.fourBntStyle,...this.fourColorStyle,...this.fourGoodsStyle,...this.fiveStyle,...this.progressStyle,...this.currencyStyle]
								}
							}
						}else if(type3 == 2){
							if(type4 == 0){
								this.rCom = obj3
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.fourStyle,...this.fourBntStyle,...this.fourColorStyle,...this.fourGoodsStyle,...this.fiveStyle2,...this.bntStyle,...this.currencyStyle]
							}
						}else{
							if(type5 == 0){
								if(type4 == 0){
									this.rCom = obj3
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.fourStyle,...this.fourBntStyle,...this.fourColorStyle,...this.fourGoodsStyle,...this.fiveStyle,...this.bntStyle,...this.sixStyle,...this.currencyStyle]
								}
							}else{
								if(type4 == 0){
									this.rCom = obj3
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.fourStyle,...this.fourBntStyle,...this.fourColorStyle,...this.fourGoodsStyle,...this.fiveStyle,...this.currencyStyle]
								}
							}
						}
					}else{
						if(type3 == 0){
							if(type5 == 0){
								if(type4 == 0){
									this.rCom = obj4
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.threeStyle,...this.fourStyle,...this.fourBntStyle,...this.fourColorStyle,...this.fourGoodsStyle,...this.fiveStyle,...this.progressStyle,...this.bntStyle,...this.sixStyle,...this.currencyStyle]
								}
							}else{
								if(type4 == 0){
									this.rCom = obj4
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.threeStyle,...this.fourStyle,...this.fourBntStyle,...this.fourColorStyle,...this.fourGoodsStyle,...this.fiveStyle,...this.progressStyle,...this.currencyStyle]
								}
							}
						}else if(type3 == 2){
							if(type4 == 0){
								this.rCom = obj4
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.threeStyle,...this.fourStyle,...this.fourBntStyle,...this.fourColorStyle,...this.fourGoodsStyle,...this.fiveStyle2,...this.bntStyle,...this.currencyStyle]
							}
						}else{
							if(type5 == 0){
								if(type4 == 0){
									this.rCom = obj4
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.threeStyle,...this.fourStyle,...this.fourBntStyle,...this.fourColorStyle,...this.fourGoodsStyle,...this.fiveStyle,...this.bntStyle,...this.sixStyle,...this.currencyStyle]
								}
							}else{
								if(type4 == 0){
									this.rCom = obj4
								}else{
									this.rCom = [...arr,...this.oneStyle,...this.threeStyle,...this.fourStyle,...this.fourBntStyle,...this.fourColorStyle,...this.fourGoodsStyle,...this.fiveStyle,...this.currencyStyle]
								}
							}
						}
					}
				}
			},
            // 获取组件参数
            getConfig (data) {}
        }
    }
</script>

<style scoped>

</style>
