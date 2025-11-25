<template>
  <div class="mobile-config">
    <div v-for="(item, key) in rCom" :key="key">
      <component
        :is="item.components.name"
        :configObj="configObj"
        ref="childData"
        :configNme="item.configNme"
        :key="key"
        :index="activeIndex"
        :num="item.num"
      ></component>
    </div>
    <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
  </div>
</template>

<script>
import toolCom from '@/components/mobileConfigRight/index.js';
import rightBtn from '@/components/rightBtn/index.vue';
import { mapState, mapMutations, mapActions } from 'vuex';
export default {
  name: 'c_banner',
  componentsName: 'home_banner',
  components: {
    ...toolCom,
    rightBtn,
  },
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
  data() {
    return {
      configObj: {},
      rCom: [
        {
          components: toolCom.c_set_up,
          configNme: 'setUp',
        },
      ],
      oneStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleRight',
        },
        {
          components: toolCom.c_radio,
          configNme: 'docConfig',
        },
        {
          components: toolCom.c_radio,
          configNme: 'docPosition',
        },
        {
          components: toolCom.c_radio,
          configNme: 'toneConfig',
        },
      ],
      twoStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'dotColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'dotBgColor',
        },
      ],
      threeStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleImg',
        },
        {
          components: toolCom.c_fillet,
          configNme: 'filletImg',
        },
      ],
      fourStyle: [
        {
          components: toolCom.c_slider,
          configNme: 'imgConfig',
        },
      ],
      oneCurrencyStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleCurrency',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'bgColor',
        },
        {
          components: toolCom.c_slider,
          configNme: 'topConfig',
        },
      ],
      twoCurrencyStyle: [
        {
          components: toolCom.c_slider,
          configNme: 'bottomConfig',
        },
      ],
      threeCurrencyStyle: [
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
      setUp: 0,
      type: 0,
      type2: 0,
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
          let tempArr = [
            {
              components: toolCom.c_title,
              configNme: 'titleLeft',
            },
            {
              components: toolCom.c_radio,
              configNme: 'styleConfig',
            },
            {
              components: toolCom.c_title,
              configNme: 'titleContent',
            },
            {
              components: toolCom.c_swipers_list,
              configNme: 'swiperConfig',
            },
          ];
          this.rCom = arr.concat(tempArr);
        } else {
          if (this.type2 == 2) {
            if (this.type) {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.twoStyle,
                ...this.threeStyle,
                ...this.fourStyle,
                ...this.oneCurrencyStyle,
                ...this.twoCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.threeStyle,
                ...this.fourStyle,
                ...this.oneCurrencyStyle,
                ...this.twoCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            }
          } else if (this.type2 == 1) {
            if (this.type) {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.twoStyle,
                ...this.threeStyle,
                ...this.oneCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.threeStyle,
                ...this.oneCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            }
          } else {
            if (this.type) {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.twoStyle,
                ...this.threeStyle,
                ...this.oneCurrencyStyle,
                ...this.twoCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.threeStyle,
                ...this.oneCurrencyStyle,
                ...this.twoCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            }
          }
        }
      },
      deep: true,
    },
    'configObj.styleConfig.tabVal': {
      handler(nVal, oVal) {
        this.type2 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp) {
          if (nVal == 2) {
            if (this.type) {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.twoStyle,
                ...this.threeStyle,
                ...this.fourStyle,
                ...this.oneCurrencyStyle,
                ...this.twoCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.threeStyle,
                ...this.fourStyle,
                ...this.oneCurrencyStyle,
                ...this.twoCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            }
          } else if (nVal == 1) {
            if (this.type) {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.twoStyle,
                ...this.threeStyle,
                ...this.oneCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.threeStyle,
                ...this.oneCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            }
          } else {
            if (this.type) {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.twoStyle,
                ...this.threeStyle,
                ...this.oneCurrencyStyle,
                ...this.twoCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.threeStyle,
                ...this.oneCurrencyStyle,
                ...this.twoCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            }
          }
        }
      },
      deep: true,
    },
    'configObj.toneConfig.tabVal': {
      handler(nVal, oVal) {
        this.type = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp) {
          if (this.type2 == 2) {
            if (nVal) {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.twoStyle,
                ...this.threeStyle,
                ...this.fourStyle,
                ...this.oneCurrencyStyle,
                ...this.twoCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.threeStyle,
                ...this.fourStyle,
                ...this.oneCurrencyStyle,
                ...this.twoCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            }
          } else if (this.type2 == 1) {
            if (nVal) {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.twoStyle,
                ...this.threeStyle,
                ...this.oneCurrencyStyle,
                ...this.twoCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.threeStyle,
                ...this.oneCurrencyStyle,
                ...this.twoCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            }
          } else {
            if (nVal) {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.twoStyle,
                ...this.threeStyle,
                ...this.oneCurrencyStyle,
                ...this.twoCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.threeStyle,
                ...this.oneCurrencyStyle,
                ...this.twoCurrencyStyle,
                ...this.threeCurrencyStyle,
              ];
            }
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
    handleSubmit(name) {
      let obj = {};
      obj.activeIndex = this.activeIndex;
      obj.data = this.configObj;
      this.add(obj);
    },
    ...mapMutations({
      add: 'mobildConfig/UPDATEARR',
    }),
  },
};
</script>

<style scoped lang="scss">
.title-tips {
  padding-bottom: 10px;
  font-size: 14px;
  color: #333;
  span {
    margin-right: 14px;
    color: #999;
  }
}
</style>
