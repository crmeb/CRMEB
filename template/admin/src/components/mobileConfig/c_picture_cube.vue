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
export default {
  name: 'c_picture_cube',
  componentsName: 'picture_cube',
  cname: '图片魔方',
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
      ], // 当前页面组件
      setUp: 0,
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
              components: toolCom.c_button_style,
              configNme: 'styleConfig',
            },
            {
              components: toolCom.c_title,
              configNme: 'titleShow',
            },
            {
              components: toolCom.c_pictrue,
              configNme: 'picStyle',
            },
            {
              components: toolCom.c_title,
              configNme: 'titleContent',
            },
            {
              components: toolCom.c_menu_list,
              configNme: 'menuConfig',
            },
          ];
          this.rCom = arr.concat(tempArr);
        } else {
          let tempArr = [
            {
              components: toolCom.c_title,
              configNme: 'titleRight',
            },
            {
              components: toolCom.c_slider,
              configNme: 'imgConfig',
            },
            {
              components: toolCom.c_fillet,
              configNme: 'filletImg',
            },
            {
              components: toolCom.c_title,
              configNme: 'titleCurrency',
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
              components: toolCom.c_slider,
              configNme: 'mbConfig',
            },
          ];
          this.rCom = arr.concat(tempArr);
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
  methods: {},
};
</script>

<style scoped lang="scss">
::v-deep.ivu-radio-group-button.ivu-radio-group-large .ivu-radio-wrapper {
  width: 52px !important;
  margin-bottom: 10px;
  margin-right: 9px !important;
}
.pro {
  padding: 15px 15px 0;
  .tips {
    height: 50px;
    line-height: 50px;
    color: #999;
    font-size: 12px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  }
}
.btn-box {
  padding-bottom: 20px;
}
</style>
