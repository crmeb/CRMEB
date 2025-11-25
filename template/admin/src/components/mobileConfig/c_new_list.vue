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
import toolCom from '@/components/mobileConfigRight/index.js';
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
      oneStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleRight',
        },
        {
          components: toolCom.c_fillet,
          configNme: 'filletImg',
        },
        {
          components: toolCom.c_radio,
          configNme: 'nameConfig',
        },
        {
          components: toolCom.c_radio,
          configNme: 'toneConfig',
        },
      ],
      twoStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'likeSuccessColor',
        },
      ],
      threeStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'nameColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'timeColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'browseColor',
        },
        // {
        //   components: toolCom.c_bg_color,
        //   configNme: 'likeColor',
        // },
        {
          components: toolCom.c_bg_color,
          configNme: 'statisticColor',
        },
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
              configNme: 'titleArticle',
            },
            {
              components: toolCom.c_select,
              configNme: 'selectConfig',
            },
            {
              components: toolCom.c_input_number,
              configNme: 'numConfig',
            },
            {
              components: toolCom.c_title,
              configNme: 'titleList',
            },
            {
              components: toolCom.c_checkbox,
              configNme: 'checkboxList',
            },
          ];
          this.rCom = arr.concat(tempArr);
        } else {
          if (this.type) {
            this.rCom = [...arr, ...this.oneStyle, ...this.twoStyle, ...this.threeStyle];
          } else {
            this.rCom = [...arr, ...this.oneStyle, ...this.threeStyle];
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
          if (nVal) {
            this.rCom = [...arr, ...this.oneStyle, ...this.twoStyle, ...this.threeStyle];
          } else {
            this.rCom = [...arr, ...this.oneStyle, ...this.threeStyle];
          }
        }
      },
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
          this.$message.error(err.msg);
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
          this.$message.error(err.msg);
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
