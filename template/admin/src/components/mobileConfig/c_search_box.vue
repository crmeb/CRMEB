<template>
  <div class="mobile-config">
    <Form ref="formInline">
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
    </Form>
  </div>
</template>

<script>
import toolCom from '@/components/mobileConfigRight/index.js';
import rightBtn from '@/components/rightBtn/index.vue';
import { mapMutations } from 'vuex';
export default {
  name: 'c_search_box',
  componentsName: 'search_box',
  cname: '搜索框',
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
      hotIndex: 1,
      configObj: {}, // 配置对象
      rCom: [
        {
          components: toolCom.c_set_up,
          configNme: 'setUp',
        },
      ], // 当前页面组件
      oneContent: [
        {
          components: toolCom.c_title,
          configNme: 'titleLeft',
        },
        {
          components: toolCom.c_radio,
          configNme: 'styleConfig',
        },
      ],
      oneContentType: [
        {
          components: toolCom.c_radio,
          configNme: 'styleTypeConfig',
        },
      ],
      oneContentFix: [
        {
          components: toolCom.c_radio,
          configNme: 'fixConfig',
        },
      ],
      twoContent: [
        {
          components: toolCom.c_upload_img,
          configNme: 'logoConfig',
        },
      ],
      threeContent: [
        {
          components: toolCom.c_input_item,
          configNme: 'titleConfig',
        },
        {
          components: toolCom.c_input_item,
          configNme: 'linkConfig',
        },
      ],
      rComContent: [
        {
          components: toolCom.c_title,
          configNme: 'titleSearch',
        },
        {
          components: toolCom.c_input_item,
          configNme: 'tipConfig',
        },
        {
          components: toolCom.c_title,
          configNme: 'titleHotWords',
        },
        {
          components: toolCom.c_hot_word,
          configNme: 'hotWords',
        },
        {
          components: toolCom.c_input_number,
          configNme: 'numConfig',
        },
      ],
      oneStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleRight',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'searchBoxColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'tipColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'hotWordsColor',
        },
      ],
      twoStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleTxt',
        },
        {
          components: toolCom.c_radio,
          configNme: 'txtFixConfig',
        },
      ],
      twoStyle1: [
        {
          components: toolCom.c_radio,
          configNme: 'txtStyleConfig',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'txtColor',
        },
        {
          components: toolCom.c_slider,
          configNme: 'txtSize',
        },
      ],
      currencyStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleCurrency',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'moduleColor',
        },
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
      // debugger;
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
          this.getRComContent(arr);
        } else {
          this.getRComStyle(arr);
        }
      },
      deep: true,
    },
    'configObj.styleConfig.tabVal': {
      handler(nVal, oVal) {
        this.type = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp == 0) {
          this.getRComContent(arr);
        } else {
          this.getRComStyle(arr);
        }
      },
      deep: true,
    },
    'configObj.styleTypeConfig.tabVal': {
      handler(nVal, oVal) {
        this.type2 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp == 0) {
          this.getRComContent(arr);
        } else {
          this.getRComStyle(arr);
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
    getRComContent(arr) {
      console.log('11', this.type2);
      if (this.type == 1) {
        // this.rCom = [...arr, ...this.oneContent, ...this.threeContent];
        this.rCom = [...arr, ...this.oneContent, ...this.threeContent, ...this.oneContentFix];
      } else if (this.type == 2) {
      } else {
        if (this.type2 == 0) {
          this.rCom = [...arr, ...this.oneContent, ...this.oneContentType, ...this.threeContent, ...this.rComContent];
        } else if (this.type2 == 1) {
          this.rCom = [...arr, ...this.oneContent, ...this.oneContentType, ...this.twoContent, ...this.rComContent];
        } else {
          this.rCom = [...arr, ...this.oneContent, ...this.oneContentType, ...this.oneContentFix, ...this.rComContent];
        }
      }
    },
    getRComStyle(arr) {
      console.log('222');
      if (this.type == 0) {
        if (this.type2 == 2) {
          this.rCom = [...arr, ...this.oneStyle, ...this.twoStyle, ...this.currencyStyle];
        } else {
          this.rCom = [...arr, ...this.oneStyle, ...this.twoStyle, ...this.twoStyle1, ...this.currencyStyle];
        }
      } else {
        this.rCom = [...arr, ...this.twoStyle, ...this.twoStyle1, ...this.currencyStyle];
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
