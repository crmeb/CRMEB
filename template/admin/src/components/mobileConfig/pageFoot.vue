<template>
  <div class="mobile-config">
    <Form ref="formInline">
      <div v-for="(item, key) in rCom" :key="key">
        <component
          :is="item.components.name"
          ref="childData"
          :configObj="configObj"
          :configNme="item.configNme"
        ></component>
      </div>
    </Form>
  </div>
</template>

<script>
import toolCom from '@/components/mobileConfigRight/index.js';
import rightBtn from '@/components/rightBtn/index.vue';
import { mapMutations } from 'vuex';

export default {
  name: 'pageFoot',
  cname: '底部导航',
  components: {
    ...toolCom,
    rightBtn,
  },
  data() {
    return {
      hotIndex: 1,
      configObj: {}, // 配置对象
      rCom: [
        {
          components: toolCom.c_set_up,
          configNme: 'setUp',
        },
      ], // 当前页面组件
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
          configNme: 'activeTxtColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'txtColor',
        },
      ],
      currencyTitle: [
        {
          components: toolCom.c_title,
          configNme: 'titleCurrency',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'bgColor',
        },
      ],
      currencyTitle2: [
        {
          components: toolCom.c_title,
          configNme: 'titleCurrency',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'bgColor2',
        },
      ],
      currencyStyle: [
        {
          components: toolCom.c_slider,
          configNme: 'topConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'bottomConfig',
        },
      ],
      currencyStyle2: [
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
      type3: 0,
    };
  },
  watch: {
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
              configNme: 'effectConfig',
            },
            {
              components: toolCom.c_radio,
              configNme: 'navConfig',
            },
            {
              components: toolCom.c_radio,
              configNme: 'navStyleConfig',
            },
            {
              components: toolCom.c_title,
              configNme: 'titleNav',
            },
            {
              components: toolCom.c_foot,
              configNme: 'menuList',
            },
          ];
          this.rCom = arr.concat(tempArr);
        } else {
          this.getRComStyle(arr, this.type, this.type2, this.type3);
        }
      },
      deep: true,
    },
    'configObj.navConfig.tabVal': {
      handler(nVal, oVal) {
        this.type = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp) {
          this.getRComStyle(arr, nVal, this.type2, this.type3);
        }
      },
      deep: true,
    },
    'configObj.navStyleConfig.tabVal': {
      handler(nVal, oVal) {
        this.type2 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp) {
          this.getRComStyle(arr, this.type, nVal, this.type3);
        }
      },
      deep: true,
    },
    'configObj.toneConfig.tabVal': {
      handler(nVal, oVal) {
        this.type3 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp) {
          this.getRComStyle(arr, this.type, this.type2, nVal);
        }
      },
      deep: true,
    },
  },
  mounted() {
    this.configObj = this.$store.state.mobildConfig.pageFooter;
  },
  methods: {
    getRComStyle(arr, type, type2, type3) {
      if (type == 0) {
        if (type2 == 2) {
          this.rCom = [...arr, ...this.currencyTitle, ...this.currencyStyle];
        } else {
          if (type3 == 0) {
            this.rCom = [...arr, ...this.oneStyle, ...this.currencyTitle, ...this.currencyStyle];
          } else {
            this.rCom = [...arr, ...this.oneStyle, ...this.twoStyle, ...this.currencyTitle, ...this.currencyStyle];
          }
        }
      } else {
        if (type2 == 2) {
          this.rCom = [...arr, ...this.currencyTitle2, ...this.currencyStyle, ...this.currencyStyle2];
        } else {
          if (type3 == 0) {
            this.rCom = [
              ...arr,
              ...this.oneStyle,
              ...this.currencyTitle2,
              ...this.currencyStyle,
              ...this.currencyStyle2,
            ];
          } else {
            this.rCom = [
              ...arr,
              ...this.oneStyle,
              ...this.twoStyle,
              ...this.currencyTitle2,
              ...this.currencyStyle,
              ...this.currencyStyle2,
            ];
          }
        }
      }
    },
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
