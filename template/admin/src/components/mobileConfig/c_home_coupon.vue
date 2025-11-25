<template>
  <div class="mobile-config">
    <div v-for="(item, key) in rCom" :key="key">
      <component
        :is="item.components.name"
        :configObj="configObj"
        ref="childData"
        :configNme="item.configNme"
        :key="key"
        @getConfig="getConfig"
      ></component>
    </div>
    <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
  </div>
</template>

<script>
import toolCom from '@/components/mobileConfigRight/index.js';
import rightBtn from '@/components/rightBtn/index.vue';
import { mapMutations } from 'vuex';
export default {
  name: 'c_home_coupon',
  componentsName: 'home_coupon',
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
          configNme: 'toneConfig',
        },
      ],
      twoStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'couponMoneyColor',
        },
      ],
      bntBgStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'bntBgColor',
        },
      ],
      couponBgStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'couponBgColor',
        },
      ],
      currencyTitleStyle: [
        {
          components: toolCom.c_slider,
          configNme: 'spacingConfig',
        },
        {
          components: toolCom.c_title,
          configNme: 'titleCurrency',
        },
      ],
      moduleColorStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'moduleColor',
        },
      ],
      moduleColorStyle2: [
        {
          components: toolCom.c_bg_color,
          configNme: 'moduleColor2',
        },
      ],
      currencyStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'bottomBgColor',
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
      setUp: 0,
      type: 0,
      type2: 0,
    };
  },
  watch: {
    num(nVal) {
      this.configObj = this.$store.state.mobildConfig.defaultArray[nVal];
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
              components: toolCom.c_button_style,
              configNme: 'styleConfig',
            },
            {
              components: toolCom.c_title,
              configNme: 'titleData',
            },
            {
              components: toolCom.c_slider,
              configNme: 'numberConfig',
            },
          ];
          this.rCom = arr.concat(tempArr);
        } else {
          this.getRComStyle(arr, this.type, this.type2);
        }
      },
      deep: true,
    },
    'configObj.styleConfig.tabVal': {
      handler(nVal, oVal) {
        this.type = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp) {
          this.getRComStyle(arr, nVal, this.type2);
        }
      },
      deep: true,
    },
    'configObj.toneConfig.tabVal': {
      handler(nVal, oVal) {
        this.type2 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp) {
          this.getRComStyle(arr, this.type, nVal);
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
    getRComStyle(arr, type, type2) {
      if (type == 0 || type == 3) {
        if (type2 == 0) {
          this.rCom = [
            ...arr,
            ...this.oneStyle,
            ...this.currencyTitleStyle,
            ...this.moduleColorStyle2,
            ...this.currencyStyle,
          ];
        } else {
          this.rCom = [
            ...arr,
            ...this.oneStyle,
            ...this.twoStyle,
            ...this.bntBgStyle,
            ...this.couponBgStyle,
            ...this.currencyTitleStyle,
            ...this.moduleColorStyle2,
            ...this.currencyStyle,
          ];
        }
      } else if (type == 1) {
        if (type2 == 0) {
          this.rCom = [
            ...arr,
            ...this.oneStyle,
            ...this.currencyTitleStyle,
            ...this.moduleColorStyle,
            ...this.currencyStyle,
          ];
        } else {
          this.rCom = [
            ...arr,
            ...this.oneStyle,
            ...this.twoStyle,
            ...this.bntBgStyle,
            ...this.currencyTitleStyle,
            ...this.moduleColorStyle,
            ...this.currencyStyle,
          ];
        }
      } else if (type == 2) {
        if (type2 == 0) {
          this.rCom = [
            ...arr,
            ...this.oneStyle,
            ...this.currencyTitleStyle,
            ...this.moduleColorStyle2,
            ...this.currencyStyle,
          ];
        } else {
          this.rCom = [
            ...arr,
            ...this.oneStyle,
            ...this.twoStyle,
            ...this.currencyTitleStyle,
            ...this.moduleColorStyle2,
            ...this.currencyStyle,
          ];
        }
      } else {
        if (type2 == 0) {
          this.rCom = [
            ...arr,
            ...this.oneStyle,
            ...this.currencyTitleStyle,
            ...this.moduleColorStyle2,
            ...this.currencyStyle,
          ];
        } else {
          this.rCom = [
            ...arr,
            ...this.oneStyle,
            ...this.twoStyle,
            ...this.bntBgStyle,
            ...this.currencyTitleStyle,
            ...this.moduleColorStyle2,
            ...this.currencyStyle,
          ];
        }
      }
    },
    // 获取组件参数
    getConfig(data) {},
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
