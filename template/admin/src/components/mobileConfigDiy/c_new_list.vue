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
import { mapState, mapMutations, mapActions } from 'vuex';
import { categoryList } from '@/api/diy';
import { cmsListApi } from '@/api/cms';
export default {
  name: 'c_home_bargain',
  componentsName: 'home_bargain',
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
    };
  },
  watch: {
    num(nVal) {
      let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[nVal]));
      this.configObj = value;
      this.categoryList();
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
              components: toolCom.c_select,
              configNme: 'selectConfig',
            },
            {
              components: toolCom.c_input_number,
              configNme: 'numConfig',
            },
          ];
          this.rCom = arr.concat(tempArr);
        } else {
          let tempArr = [
            {
              components: toolCom.c_txt_tab,
              configNme: 'listStyle',
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
              components: toolCom.c_txt_tab,
              configNme: 'conStyle',
            },
            {
              components: toolCom.c_slider,
              configNme: 'prConfig',
            },
            {
              components: toolCom.c_slider,
              configNme: 'itemConfig',
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
      this.categoryList();
    });
  },
  methods: {
    categoryList() {
      categoryList()
        .then((res) => {
          let data = [];
          res.data.map((item) => {
            data.push({ title: item.title, activeValue: item.id.toString() });
          });
          this.configObj.selectConfig.list = data;
          // this.$store.commit('mobildConfig/UPDATEARR', { num: this.num, val: this.pageData })
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    // 获取组件参数
    getConfig(data) {
      if (data.name == 'radio') {
        return;
      }
      let val = {
        pid: parseInt(this.configObj.selectConfig.activeValue),
        page: 1,
        limit: parseInt(this.configObj.numConfig.val),
      };
      cmsListApi(val)
        .then((res) => {
          this.configObj.selectList.list = res.data.list;
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
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

<style scoped lang="stylus">
.title-tips
    padding-bottom 10px
    font-size 14px
    color #333
    span
        margin-right 14px
        color #999
</style>
