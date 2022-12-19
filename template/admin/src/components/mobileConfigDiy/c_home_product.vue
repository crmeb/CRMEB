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
import toolCom from '@/components/mobileConfigRightDiy/index.js';
import { mapState, mapMutations, mapActions } from 'vuex';
import rightBtn from '@/components/rightBtn/index.vue';
import { getGroomList } from '@/api/diy';
export default {
  name: 'c_home_product',
  componentsName: 'home_product',
  cname: '促销列表',
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
        var arr = [this.rCom[0]];
        if (nVal == 0) {
          let tempArr = [
            {
              components: toolCom.c_product,
              configNme: 'tabConfig',
            },
            {
              components: toolCom.c_input_number,
              configNme: 'numConfig',
            },
            {
              components: toolCom.c_is_show,
              configNme: 'titleShow',
            },
            {
              components: toolCom.c_is_show,
              configNme: 'opriceShow',
            },
            {
              components: toolCom.c_is_show,
              configNme: 'priceShow',
            },
            {
              components: toolCom.c_is_show,
              configNme: 'couponShow',
            },
          ];
          this.rCom = arr.concat(tempArr);
        } else {
          let tempArr = [
            {
              components: toolCom.c_txt_tab,
              configNme: 'titleConfig',
            },
            {
              components: toolCom.c_bg_color,
              configNme: 'themeColor',
            },
            {
              components: toolCom.c_bg_color,
              configNme: 'fontColor',
            },
            {
              components: toolCom.c_bg_color,
              configNme: 'labelColor',
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
  methods: {
    getConfig(data) {
      let tabConfig = this.configObj.tabConfig;
      if (data.name == 'radio') {
        return;
      }
      if (data.name == 'delete' && !tabConfig.list.length) {
        return (this.configObj.productList.list = []);
      }
      let val = {
        page: 1,
        limit: parseInt(this.configObj.numConfig.val),
      };
      let type = parseInt(tabConfig.list[tabConfig.tabCur].link.activeVal);
      getGroomList(type, val)
        .then((res) => {
          this.configObj.productList.list = res.data.list;
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
  },
};
</script>

<style scoped lang="stylus">
.pro
    padding 0 15px
    .tips
        height 50px
        line-height 50px
        color #999
        font-size 12px
        border-bottom 1px solid rgba(0,0,0,0.05);
.btn-box
    padding-bottom 20px
</style>
