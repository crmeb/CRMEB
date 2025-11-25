<template>
  <div class="mobile-config pro">
    <div v-for="(item, key) in rCom" :key="key">
      <component
        :is="item.components.name"
        :configObj="configObj"
        ref="childData"
        :configNme="item.configNme"
        :key="key"
        @getConfig="getConfig"
        :index="activeIndex"
        :num="item.num"
      ></component>
    </div>
    <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
  </div>
</template>

<script>
import toolCom from '@/components/mobileConfigRight/index.js';
import { mapState, mapMutations, mapActions } from 'vuex';
import rightBtn from '@/components/rightBtn/index.vue';
export default {
  name: 'c_home_menu',
  cname: '导航组',
  componentsName: 'home_menu',
  props: {
    activeIndex: {
      type: null,
    },
    num: {
      type: null,
    },
    index: {
      type: null,
    },
  },
  components: {
    ...toolCom,
    rightBtn,
  },
  data() {
    return {
      configObj: {},
      rCom: [
        {
          components: toolCom.c_set_up,
          configNme: 'setUp',
        },
      ],
      rComContent: [
        {
          components: toolCom.c_title,
          configNme: 'titleLeft',
        },
        {
          components: toolCom.c_radio,
          configNme: 'menuStyleConfig',
        },
        {
          components: toolCom.c_radio,
          configNme: 'number',
        },
        {
          components: toolCom.c_radio,
          configNme: 'showConfig',
        },
      ],
      oneContent: [
        {
          components: toolCom.c_radio,
          configNme: 'rowsNum',
        },
      ],
      twoContent: [
        {
          components: toolCom.c_title,
          configNme: 'titleContent',
        },
        {
          components: toolCom.c_menu_list,
          configNme: 'menuConfig',
        },
      ],
      oneStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleRight',
        },
        {
          components: toolCom.c_fillet,
          configNme: 'filletImg',
        },
      ],
      twoStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titlePointer',
        },
        {
          components: toolCom.c_radio,
          configNme: 'toneConfig',
        },
      ],
      threeStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'pointerColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'pointerBgColor',
        },
      ],
      fourStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleCurrency',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'bgColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'bottomBgColor',
        },
		{
		  components: toolCom.c_bg_color,
		  configNme: 'textColor',
		},
        {
          components: toolCom.c_slider,
          configNme: 'topConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'bottomConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'prConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'mbConfig',
        },
        {
          components: toolCom.c_fillet,
          configNme: 'fillet',
        },
      ],
      type: 0, //展示样式索引
      setUp: 0, //0：内容；1：样式
      type2: 0, //导航样式索引
      type3: 0, //色调索引
    };
  },
  watch: {
    num(nVal) {
      let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[nVal]));
      this.configObj = value;
    },
    configObj: {
      handler(nVal, oVal) {
        this.$store.commit('mobildConfig/UPDATEARR', { num: this.num, val: nVal });
      },
      deep: true,
    },
    'configObj.setUp.tabVal': {
      handler(nVal, oVal) {
        this.setUp = nVal;
        var arr = [this.rCom[0]];
        if (nVal == 0) {
          let rCom = arr.concat(this.rComContent);
          if (this.type == 0) {
            this.rCom = rCom.concat(this.twoContent);
          } else {
            let rCom2 = rCom.concat(this.oneContent);
            this.rCom = rCom2.concat(this.twoContent);
          }
        } else {
          if (this.type2 == 2) {
            if (this.type == 0) {
              this.rCom = arr.concat(this.fourStyle);
            } else {
              if (this.type3 == 0) {
                let rCom = arr.concat(this.twoStyle);
                this.rCom = rCom.concat(this.fourStyle);
              } else {
                let rCom = arr.concat(this.twoStyle);
                let rCom2 = rCom.concat(this.threeStyle);
                this.rCom = rCom2.concat(this.fourStyle);
              }
            }
          } else {
            let rCom = arr.concat(this.oneStyle);
            if (this.type == 0) {
              this.rCom = rCom.concat(this.fourStyle);
            } else {
              if (this.type3 == 0) {
                let rCom2 = rCom.concat(this.twoStyle);
                this.rCom = rCom2.concat(this.fourStyle);
              } else {
                let rCom2 = rCom.concat(this.twoStyle);
                let rCom3 = rCom2.concat(this.threeStyle);
                this.rCom = rCom3.concat(this.fourStyle);
              }
            }
          }
        }
      },
      deep: true,
    },
    'configObj.menuStyleConfig.tabVal': {
      handler(nVal, oVal) {
        this.type2 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp == 0) {
          let rCom = arr.concat(this.rComContent);
          if (this.type == 0) {
            this.rCom = rCom.concat(this.twoContent);
          } else {
            let rCom2 = rCom.concat(this.oneContent);
            this.rCom = rCom2.concat(this.twoContent);
          }
        } else {
          if (nVal == 2) {
            if (this.type == 0) {
              this.rCom = arr.concat(this.fourStyle);
            } else {
              if (this.type3 == 0) {
                let rCom = arr.concat(this.twoStyle);
                this.rCom = rCom.concat(this.fourStyle);
              } else {
                let rCom = arr.concat(this.twoStyle);
                let rCom2 = rCom.concat(this.threeStyle);
                this.rCom = rCom2.concat(this.fourStyle);
              }
            }
          } else {
            let rCom = arr.concat(this.oneStyle);
            if (this.type == 0) {
              this.rCom = rCom.concat(this.fourStyle);
            } else {
              if (this.type3 == 0) {
                let rCom2 = rCom.concat(this.twoStyle);
                this.rCom = rCom2.concat(this.fourStyle);
              } else {
                let rCom2 = rCom.concat(this.twoStyle);
                let rCom3 = rCom2.concat(this.threeStyle);
                this.rCom = rCom3.concat(this.fourStyle);
              }
            }
          }
        }
      },
      deep: true,
    },
    'configObj.showConfig.tabVal': {
      handler(nVal, oVal) {
        this.type = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp) {
          if (this.type2 == 2) {
            if (nVal == 0) {
              this.rCom = arr.concat(this.fourStyle);
            } else {
              if (this.type3 == 0) {
                let rCom = arr.concat(this.twoStyle);
                this.rCom = rCom.concat(this.fourStyle);
              } else {
                let rCom = arr.concat(this.twoStyle);
                let rCom2 = rCom.concat(this.threeStyle);
                this.rCom = rCom2.concat(this.fourStyle);
              }
            }
          } else {
            let rCom = arr.concat(this.oneStyle);
            if (nVal == 0) {
              this.rCom = rCom.concat(this.fourStyle);
            } else {
              if (this.type3 == 0) {
                let rCom2 = rCom.concat(this.twoStyle);
                this.rCom = rCom2.concat(this.fourStyle);
              } else {
                let rCom2 = rCom.concat(this.twoStyle);
                let rCom3 = rCom2.concat(this.threeStyle);
                this.rCom = rCom3.concat(this.fourStyle);
              }
            }
          }
        } else {
          let rCom = arr.concat(this.rComContent);
          if (nVal == 0) {
            this.rCom = rCom.concat(this.twoContent);
          } else {
            let rCom2 = rCom.concat(this.oneContent);
            this.rCom = rCom2.concat(this.twoContent);
          }
        }
      },
      deep: true,
    },
    'configObj.toneConfig.tabVal': {
      handler(nVal, oVal) {
        this.type3 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp) {
          if (this.type2 == 2) {
            if (this.type == 0) {
              this.rCom = arr.concat(this.fourStyle);
            } else {
              if (nVal == 0) {
                let rCom = arr.concat(this.twoStyle);
                this.rCom = rCom.concat(this.fourStyle);
              } else {
                let rCom = arr.concat(this.twoStyle);
                let rCom2 = rCom.concat(this.threeStyle);
                this.rCom = rCom2.concat(this.fourStyle);
              }
            }
          } else {
            let rCom = arr.concat(this.oneStyle);
            if (this.type == 0) {
              this.rCom = rCom.concat(this.fourStyle);
            } else {
              if (nVal == 0) {
                let rCom2 = rCom.concat(this.twoStyle);
                this.rCom = rCom2.concat(this.fourStyle);
              } else {
                let rCom2 = rCom.concat(this.twoStyle);
                let rCom3 = rCom2.concat(this.threeStyle);
                this.rCom = rCom3.concat(this.fourStyle);
              }
            }
          }
        } else {
          let rCom = arr.concat(this.rComContent);
          if (this.type == 0) {
            this.rCom = rCom.concat(this.twoContent);
          } else {
            let rCom2 = rCom.concat(this.oneContent);
            this.rCom = rCom2.concat(this.twoContent);
          }
        }
      },
      deep: true,
    },
  },
  mounted() {
    this.$nextTick(() => {
      let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[this.num]));
      this.configObj = value;
    });
  },
  methods: {
    getConfig(data) {},
  },
};
</script>

<style scoped></style>
