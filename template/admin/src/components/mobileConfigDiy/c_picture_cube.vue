<template>
  <div class="mobile-config pro">
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
import toolCom from '@/components/mobileConfigRightDiy/index.js';
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
          components: toolCom.c_tab,
          configNme: 'tabConfig',
        },
        {
          components: toolCom.c_pictrue,
          configNme: 'picStyle',
        },
        {
          components: toolCom.c_menu_list,
          configNme: 'menuConfig',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'bgColor',
        },
        {
          components: toolCom.c_txt_tab,
          configNme: 'bgStyle',
        },
        {
          components: toolCom.c_slider,
          configNme: 'prConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'mbConfig',
        },
      ],
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

<style scoped lang="stylus">
/deep/.ivu-radio-group-button.ivu-radio-group-large .ivu-radio-wrapper{
    width 52px!important
    margin-bottom 10px
    margin-right 9px!important
}
.pro
    padding 15px 15px 0
    .tips
        height 50px
        line-height 50px
        color #999
        font-size 12px
        border-bottom 1px solid rgba(0,0,0,0.05);
.btn-box
    padding-bottom 20px
</style>
