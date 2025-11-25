<template>
  <div>
    <div class="button-style acea-row row-middle">
      <div class="title-tips" v-if="configData">
        <span>{{ configData.title }}</span>
      </div>
      <div class="style-box acea-row row-middle">
        <div class="bnt" @click="styleTap">修改风格</div>
        <div class="name">当前：样式{{ configData.tabVal + 1 }}</div>
      </div>
    </div>
    <el-dialog
      :visible.sync="modals"
      title="风格选择器"
      height="500"
      :width="configData.type == 'signIn' || configData.type == 'ranking' ? '630px' : '910px'"
    >
      <div class="list acea-row row-middle">
        <div
          class="item"
          :class="current == index ? 'on' : ''"
          v-for="(item, index) in list"
          :key="index"
          @click="tap(index)"
        >
          <div class="pictrue acea-row row-center-wrapper">
            <img
              :src="item.url"
              :style="{
                width: item.width + 'px',
                height: item.height + 'px',
              }"
            />
            <span class="iconfont icona-zu80222" v-if="current == index"></span>
          </div>
          <div class="name">风格{{ index + 1 }}</div>
        </div>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button v-db-click @click="cancel">取 消</el-button>
        <el-button type="primary" v-db-click @click="ok">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: 'c_button_style',
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
      modals: false,
      current: 0,
      navBar: [
        {
          url: require('@/assets/images/tab02.png'),
          width: 220,
          height: 24,
        },
        {
          url: require('@/assets/images/tab03.png'),
          width: 220,
          height: 24,
        },
        {
          url: require('@/assets/images/tab01.png'),
          width: 220,
          height: 24,
        },
      ],
      signIn: [
        {
          url: require('@/assets/images/signIn01.png'),
          width: 220,
          height: 64,
        },
        {
          url: require('@/assets/images/signIn02.png'),
          width: 220,
          height: 59,
        },
      ],
      ranking: [
        {
          url: require('@/assets/images/ranking01.png'),
          width: 200,
          height: 172,
        },
        {
          url: require('@/assets/images/ranking02.png'),
          width: 200,
          height: 167,
        },
      ],
      coupon: [
        {
          url: require('@/assets/images/coupon01.png'),
          width: 220,
          height: 69,
        },
        {
          url: require('@/assets/images/coupon02.png'),
          width: 220,
          height: 87,
        },
        {
          url: require('@/assets/images/coupon03.png'),
          width: 220,
          height: 62,
        },
        {
          url: require('@/assets/images/coupon04.png'),
          width: 220,
          height: 69,
        },
        {
          url: require('@/assets/images/coupon05.png'),
          width: 220,
          height: 49,
        },
      ],
      pictureCube: [
        {
          url: require('@/assets/images/cube2.png'),
          width: 130,
          height: 129,
          count: 2,
        },
        {
          url: require('@/assets/images/cube3.png'),
          width: 130,
          height: 129,
          count: 2,
        },
        {
          url: require('@/assets/images/cube4.png'),
          width: 130,
          height: 129,
          count: 3,
        },
        {
          url: require('@/assets/images/cube5.png'),
          width: 130,
          height: 129,
          count: 3,
        },
        {
          url: require('@/assets/images/cube6.png'),
          width: 130,
          height: 129,
          count: 3,
        },
        {
          url: require('@/assets/images/cube7.png'),
          width: 130,
          height: 129,
          count: 3,
        },
        {
          url: require('@/assets/images/cube8.png'),
          width: 130,
          height: 129,
          count: 3,
        },
        {
          url: require('@/assets/images/cube9.png'),
          width: 130,
          height: 129,
          count: 4,
        },
        {
          url: require('@/assets/images/cube10.png'),
          width: 130,
          height: 129,
          count: 5,
        },
        {
          url: require('@/assets/images/cube11.png'),
          width: 130,
          height: 129,
          count: 4,
        },
        {
          url: require('@/assets/images/cube12.png'),
          width: 130,
          height: 129,
          count: 1,
        },
        // {
        //   url: require('@/assets/images/cube1.png'),
        //   width: 130,
        //   height: 130,
        //   count: 16,
        // },
      ],
      list: [],
    };
  },
  watch: {
    configObj: {
      handler(nVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
      },
      deep: true,
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
      this.$nextTick((e) => {
        this.current = this.configData.tabVal;
      });
      switch (this.configData.type) {
        case 'navBar':
          this.list = this.navBar;
          break;
        case 'signIn':
          this.list = this.signIn;
          break;
        case 'ranking':
          this.list = this.ranking;
          break;
        case 'coupon':
          this.list = this.coupon;
          break;
        case 'pictureCube':
          this.list = this.pictureCube;
          break;
      }
    });
  },
  methods: {
    tap(index) {
      this.current = index;
    },
    styleTap() {
      this.modals = true;
    },
    cancel() {
      this.modals = false;
    },
    ok() {
      this.modals = false;
      this.configData.tabVal = this.current;
      this.configData.count = this.list[this.current].count;
      if (this.defaults.picStyle) {
        this.defaults.picStyle.tabVal = 0;
      }
    },
  },
};
</script>

<style scoped lang="scss">
::v-deep.ivu-modal-body {
  max-height: 623px;
  overflow: auto;
}
.list {
  padding-left: 8px;
  max-height: 50vh;
  .item {
    margin-right: 11px;
    text-align: center;
    cursor: pointer;

    &.on {
      .pictrue {
        border-color: var(--prev-color-primary);
      }
    }

    .name {
      color: #3d3d3d;
      font-size: 14px;
      margin: 12px 0;
    }

    &:nth-of-type(3n) {
      margin-right: 0;
    }
    .pictrue {
      width: 273px;
      height: 218px;
      background: #f5f5f5;
      border-radius: 4px;
      border: 1px solid #dddddd;
      position: relative;

      img {
        display: block;
      }

      .iconfont {
        position: absolute;
        right: -1px;
        bottom: -4px;
        color: var(--prev-color-primary);
      }
    }
  }
}
.button-style {
  padding: 0 15px;
  margin-bottom: 20px;

  .title-tips {
    margin-right: 14px;
    color: #999;
    font-size: 12px;
    width: 82px;
  }

  .style-box {
    .bnt {
      width: 94px;
      height: 32px;
      background: var(--prev-color-primary);
      border-radius: 4px;
      text-align: center;
      line-height: 32px;
      color: #fff;
      font-size: 12px;
      cursor: pointer;
    }
    .name {
      color: #999999;
      font-size: 12px;
      margin-left: 12px;
    }
  }
}
</style>
