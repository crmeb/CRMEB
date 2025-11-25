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
  name: 'c_home_comb',
  componentsName: 'home_comb',
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
      oneContent: [
        {
          components: toolCom.c_title,
          configNme: 'titleLeft',
        },
        {
          components: toolCom.c_radio,
          configNme: 'styleConfig',
        },
        {
          components: toolCom.c_radio,
          configNme: 'classConfig',
        },
        {
          components: toolCom.c_radio,
          configNme: 'searchConfig',
        },
        {
          components: toolCom.c_radio,
          configNme: 'searchBox',
        },
        {
          components: toolCom.c_title,
          configNme: 'titleSearch',
        },
      ],
      fixContent: [
        {
          components: toolCom.c_radio,
          configNme: 'searchFix',
        },
      ],
      txtContent: [
        {
          components: toolCom.c_input_item,
          configNme: 'titleConfig',
        },
      ],
      logoContent: [
        {
          components: toolCom.c_upload_img,
          configNme: 'logoConfig',
        },
      ],
      logoUpContent: [
        {
          components: toolCom.c_upload_img,
          configNme: 'logoConfig',
        },
        {
          components: toolCom.c_upload_img,
          configNme: 'logoUpConfig',
        },
      ],
      twoContent: [
        {
          components: toolCom.c_input_item,
          configNme: 'inputConfig',
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
      threeContent: [
        {
          components: toolCom.c_title,
          configNme: 'titleTab',
        },
        {
          components: toolCom.c_tab_list,
          configNme: 'tabListConfig',
        },
      ],
      rComContent: [
        {
          components: toolCom.c_title,
          configNme: 'titleImg',
        },
        {
          components: toolCom.c_menu_list,
          configNme: 'swiperConfig',
        },
      ],
      oneStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleRight',
        },
        {
          components: toolCom.c_slider,
          configNme: 'contentConfig',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'classColor',
        },
      ],
      twoStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titlePointer',
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
      threeStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'dotColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'dotBgColor',
        },
      ],
      fourStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleImg',
        },
        {
          components: toolCom.c_fillet,
          configNme: 'filletImg',
        },
        {
          components: toolCom.c_title,
          configNme: 'titleGradient',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'gradientColor',
        },
      ],
      bgColor: [
        {
          components: toolCom.c_title,
          configNme: 'titleGradient',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'gradientColor',
        },
      ],
      setUp: 0,
      type: 0,
      type2: 0,
      type3: 0,
      type4: 0,
    };
  },
  watch: {
    num(nVal) {
      const value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[nVal]));
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
    'configObj.classConfig.tabVal': {
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
    'configObj.searchBox.tabVal': {
      handler(nVal, oVal) {
        this.type2 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp == 0) {
          this.getRComContent(arr);
        } else {
          this.getRComStyle(arr);
        }
      },
    },
    'configObj.toneConfig.tabVal': {
      handler(nVal, oVal) {
        this.type3 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp == 0) {
          this.getRComContent(arr);
        } else {
          this.getRComStyle(arr);
        }
      },
    },
    'configObj.searchConfig.tabVal': {
      handler(nVal, oVal) {
        this.type4 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp == 0) {
          this.getRComContent(arr);
        } else {
          this.getRComStyle(arr);
        }
      },
    },
  },
  mounted() {
    this.$nextTick(() => {
      const value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[this.num]));
      this.configObj = value;
    });
  },
  methods: {
    getRComContent(arr) {
      let logoConfig = this.logoUpContent;
      if (this.type == 0) {
        if (this.type2 == 0) {
          this.rCom = [
            ...arr,
            ...this.oneContent,
            ...this.txtContent,
            ...this.twoContent,
            ...this.threeContent,
            ...this.rComContent,
          ];
        } else {
          this.rCom = [
            ...arr,
            ...this.oneContent,
            ...logoConfig,
            ...this.twoContent,
            ...this.threeContent,
            ...this.rComContent,
          ];
        }
      } else {
        if (this.type2 == 0) {
          this.rCom = [...arr, ...this.oneContent, ...this.txtContent, ...this.twoContent, ...this.rComContent];
        } else {
          this.rCom = [...arr, ...this.oneContent, ...logoConfig, ...this.twoContent, ...this.rComContent];
        }
      }
    },
    getRComStyle(arr) {
      if (this.type == 0) {
        if (this.type3 == 0) {
          this.rCom = [...arr, ...this.oneStyle, ...this.twoStyle, ...this.fourStyle];
        } else {
          this.rCom = [...arr, ...this.oneStyle, ...this.twoStyle, ...this.threeStyle, ...this.fourStyle];
        }
      } else {
        if (this.type3 == 0) {
          this.rCom = [...arr, ...this.twoStyle, ...this.fourStyle];
        } else {
          this.rCom = [...arr, ...this.twoStyle, ...this.threeStyle, ...this.fourStyle];
        }
      }
    },
    handleSubmit(name) {
      const obj = {};
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
