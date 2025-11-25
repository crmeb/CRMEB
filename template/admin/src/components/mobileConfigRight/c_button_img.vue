<template>
  <div>
    <div class="button-style acea-row row-middle">
      <div class="title-tips" v-if="configData">
        <span>{{ configData.title }}</span>
      </div>
      <div class="style-box acea-row row-middle" v-for="(item, index) in list" :key="index">
        <div class="pictrue acea-row row-center-wrapper" :class="current == index ? 'on' : ''" @click="tap(index)">
          <img
            :src="item.url"
            :style="{
              width: item.width + 'px',
              height: item.height + 'px',
            }"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_button_img',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  data() {
    return {
      defaults: {},
      configData: {},
      current: 0,
      list: [],
    };
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
        this.getBnt(nVal);
      },
      deep: true,
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
      this.getBnt(this.defaults);
    });
  },
  methods: {
    tap(index) {
      this.current = index;
      this.configData.tabVal = index;
    },
    getBnt(nVal) {
      let obj = [
        {
          url: require('@/assets/images/cart2.png'),
          width: 24,
          height: 24,
        },
        {
          url: require('@/assets/images/cart3.png'),
          width: 24,
          height: 24,
        },
      ];
      if (nVal.bntStyleConfig.typeFrom == 'bnt') {
        this.list = obj;
      } else {
        if (nVal.styleConfig.tabVal == 0 || nVal.styleConfig.tabVal == 4) {
          this.list = [
            {
              url: require('@/assets/images/cart1.png'),
              width: 42,
              height: 24,
            },
            {
              url: require('@/assets/images/cart2.png'),
              width: 24,
              height: 24,
            },
            {
              url: require('@/assets/images/cart3.png'),
              width: 24,
              height: 24,
            },
          ];
        } else {
          this.current = this.current == 2 ? 1 : this.current;
          this.list = obj;
        }
        nVal.bntStyleConfig.tabVal = this.current;
      }
    },
  },
};
</script>

<style scoped lang="scss">
.button-style {
  padding: 0 15px;
  margin-bottom: 20px;
  .title-tips {
    color: #999999;
    font-size: 12px;
    width: 75px;
    margin-right: 16px;
  }
  .style-box {
    .pictrue {
      width: 54px;
      height: 36px;
      border-radius: 3px;
      border: 1px solid #fff;
      margin-right: 10px;
      &.on {
        border: 1px solid var(--prev-color-primary);
      }
      img {
        display: block;
      }
    }
  }
}
</style>
