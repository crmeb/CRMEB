<template>
    <div class="mobile-config">
        <div  v-for="(item,key) in rCom" :key="key">
            <component :is="item.components.name" :configObj="configObj" ref="childData" @getConfig="getConfig" :key="key" :configNme="item.configNme"></component>
        </div>
        <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
    </div>
</template>

<script>
    import toolCom from '@/components/mobileConfigRight/index.js'
    import rightBtn from '@/components/rightBtn/index.vue';
    import { mapState, mapMutations, mapActions } from 'vuex'
    export default {
        name: 'c_home_store_list',
        componentsName: 'home_store_list',
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
						configNme: 'titleStoreList'
					},
					{
					    components: toolCom.c_radio,
					    configNme: 'storeStyleConfig'
					},
					{
						components: toolCom.c_title,
						configNme: 'titleStore'
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
						components: toolCom.c_title,
						configNme: 'titleGoods'
					},
					{
					    components: toolCom.c_slider,
					    configNme: 'numberGoodsConfig'
					},
					{
					    components: toolCom.c_checkbox,
					    configNme: 'checkboxGoodsInfo'
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
				fourStoreStyle:[
					{
						components: toolCom.c_title,
						configNme: 'titleStoreStyle'
					},
					{
					    components: toolCom.c_fillet,
					    configNme: 'filletStoreImg'
					},
					{
					    components: toolCom.c_slider,
					    configNme: 'storeNumber'
					},
					{
					    components: toolCom.c_radio,
					    configNme: 'storeName'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'storeNameColor'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'deliveryBg'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'deliveryText'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'storeBg'
					},
					{
					    components: toolCom.c_bg_color,
					    configNme: 'storeText'
					}
				],
				goodsStyle:[
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
					    components: toolCom.c_radio,
					    configNme: 'toneConfig'
					}
				],
				goodsPriceStyle:[
					{
					    components: toolCom.c_bg_color,
					    configNme: 'goodsPriceColor'
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
				type4:0
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
            			this.getRComContent(arr,this.type,this.type2,this.type3)
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
            		if(this.setUp == 0){
            			this.getRComContent(arr,nVal,this.type2,this.type3)
            		}else{
            			this.getRComStyle(arr,nVal,this.type2,this.type3,this.type4)
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
            			this.getRComStyle(arr,this.type,nVal,this.type3,this.type4)
            		}
            	},
            	deep: true
            },
            'configObj.storeStyleConfig.tabVal':{
            	handler (nVal, oVal) {
            		this.type3 = nVal;
            		var arr = [this.rCom[0]];
            		if(this.setUp == 0){
            			this.getRComContent(arr,this.type,this.type2,nVal)
            		}else{
            			this.getRComStyle(arr,this.type,this.type2,nVal,this.type4)
            		}
            	},
            	deep: true
            },
            'configObj.toneConfig.tabVal':{
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
			getRComContent(arr,type,type2,type3){
				if(type == 0){
					if(type2 == 0){
						if(type3 == 0){
							this.rCom = [...arr,...this.oneContent,...this.twoContent,...this.twoContentColorImg,...this.threeContent,...this.fourContent]
						}else{
							this.rCom = [...arr,...this.oneContent,...this.twoContent,...this.twoContentColorImg,...this.threeContent]
						}
					}else{
						if(type3 == 0){
							this.rCom = [...arr,...this.oneContent,...this.twoContent,...this.twoContentText,...this.threeContent,...this.fourContent]
						}else{
							this.rCom = [...arr,...this.oneContent,...this.twoContent,...this.twoContentText,...this.threeContent]
						}
					}
				}else{
					if(type2 == 0){
						if(type3 == 0){
							this.rCom = [...arr,...this.oneContent,...this.oneContentImg,...this.twoContent,...this.twoContentImg,...this.threeContent,...this.fourContent]
						}else{
							this.rCom = [...arr,...this.oneContent,...this.oneContentImg,...this.twoContent,...this.twoContentImg,...this.threeContent]
						}
					}else{
						if(type3 == 0){
							this.rCom = [...arr,...this.oneContent,...this.oneContentImg,...this.twoContent,...this.twoContentText,...this.threeContent,...this.fourContent]
						}else{
							this.rCom = [...arr,...this.oneContent,...this.oneContentImg,...this.twoContent,...this.twoContentText,...this.threeContent]
						}
					}
				}
			},
			getRComStyle(arr,type,type2,type3,type4){
				if(type == 0){
					if(type2 == 0){
						if(type3 == 0){
							if(type4 == 0){
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourStoreStyle,...this.goodsStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourStoreStyle,...this.goodsStyle,...this.goodsPriceStyle,...this.currencyStyle];
							}
						}else{
							this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourStoreStyle,...this.currencyStyle];
						}
					}else{
						if(type3 == 0){
							if(type4 == 0){
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.threeStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourStoreStyle,...this.goodsStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.threeStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourStoreStyle,...this.goodsStyle,...this.goodsPriceStyle,...this.currencyStyle];
							}
						}else{
							this.rCom = [...arr,...this.oneStyle,...this.twoStyle,...this.threeStyle,...this.fourStyle2,...this.fourBntStyle,...this.fourStoreStyle,...this.currencyStyle];
						}
					}
				}else{
					if(type2 == 0){
						if(type3 == 0){
							if(type4 == 0){
								this.rCom = [...arr,...this.oneStyle,...this.fourStyle,...this.fourBntStyle,...this.fourStoreStyle,...this.goodsStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.fourStyle,...this.fourBntStyle,...this.fourStoreStyle,...this.goodsStyle,...this.goodsPriceStyle,...this.currencyStyle];
							}
						}else{
							this.rCom = [...arr,...this.oneStyle,...this.fourStyle,...this.fourBntStyle,...this.fourStoreStyle,...this.currencyStyle];
						}
					}else{
						if(type3 == 0){
							if(type4 == 0){
								this.rCom = [...arr,...this.oneStyle,...this.threeStyle,...this.fourStyle,...this.fourBntStyle,...this.fourStoreStyle,...this.goodsStyle,...this.currencyStyle];
							}else{
								this.rCom = [...arr,...this.oneStyle,...this.threeStyle,...this.fourStyle,...this.fourBntStyle,...this.fourStoreStyle,...this.goodsStyle,...this.goodsPriceStyle,...this.currencyStyle];
							}
						}else{
							this.rCom = [...arr,...this.oneStyle,...this.threeStyle,...this.fourStyle,...this.fourBntStyle,...this.fourStoreStyle,...this.currencyStyle];
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
