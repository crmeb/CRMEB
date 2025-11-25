<template>
  <div class="label-wrapper">
    <div v-if="!labelList.length" class="nonefont">暂无标签</div>
    <div v-else class="label-box" v-for="(item, index) in labelList" :key="index">
      <div class="title">{{ item.cate_name }}</div>
      <div class="list">
        <div
          class="label"
          :class="{ on: label.active }"
          v-for="(label, j) in item.list"
          :key="j"
          v-db-click
          @click="selectLabel(label)"
        >
          <div
            class="label-item"
            :style="{
              backgroundColor: label.bg_color,
              color: label.font_color,
              border: label.border_color ? '1px solid ' + label.border_color : 'none',
            }"
            v-if="!label.image"
          >
            {{ label.name }}
          </div>
          <img :src="label.image" class="img-tag" v-else />
          <div class="sanjiao" v-if="label.active">
            <span class="iconfont iconwancheng"></span>
          </div>
        </div>
      </div>
    </div>
    <div class="acea-row row-right mt20">
      <el-button v-db-click @click="cancel">取 消</el-button>
      <el-button type="primary" v-db-click @click="subBtn">确 定</el-button>
    </div>
  </div>
</template>

<script>
import { productLabelUseListApi } from '@/api/product';
export default {
  name: 'userLabel',
  props: {
    uid: {
      type: String | Number,
      default: 0,
    },
    only_get: {
      default: false,
    },
    selectDataLabel: {
      type: Array,
      default: () => {
        [];
      },
    },
    defaultLabelList: {
      type: Array,
      default: () => {
        [];
      },
    },
  },
  data() {
    return {
      labelList: [],
      activeIds: [],
      unLaberids: [],
    };
  },
  watch: {
    defaultLabelList: {
      handler(nVal, oVal) {
        if (nVal != oVal) {
          this.labelList = JSON.parse(JSON.stringify(nVal));
        }
      },
      deep: true,
      immediate: true,
    },
  },
  mounted() {
    // consol.log(this.defaultLabelList);
    // this.labelList = this.defaultLabelList
  },
  methods: {
    selectLabel(label) {
      if (label.active) {
        let index = this.activeIds.indexOf(label.id);
        this.activeIds.splice(index, 1);
        label.active = false;
      } else {
        this.activeIds.push(label.id);
        label.active = true;
      }
    },
    // 确定
    subBtn() {
      let unLaberids = [];
      this.labelList.map((item) => {
        item.list.map((i) => {
          if (i.active == true) {
            unLaberids.push(i.id);
          }
        });
      });
      this.$emit('activeLabel', unLaberids);
    },
    cancel() {
      this.activeIds = [];
      this.unLaberids = [];
      this.$emit('close');
    },
  },
};
</script>

<style lang="scss" scoped>
.label-wrapper {
  .list {
    display: flex;
    flex-wrap: wrap;
    .label {
      position: relative;
      border: 1px solid #ffffff;
      padding: 2px;
      border-radius: 4px;
      margin: 0 8px 10px 0;
      cursor: pointer;
      display: flex;
      align-items: center;
      &.on {
        border: 1px solid #2d8cf0;
      }
    }
    .label-item {
      padding: 3px 8px;
      background: #eeeeee;
      color: #333333;
      border-radius: 2px;
      cursor: pointer;
      font-size: 12px;
    }

    .img-tag {
      height: 22px;
      border-radius: 2px;
    }
    .sanjiao {
      position: absolute;
      right: 0;
      bottom: 0;
      width: 20px;
      height: 20px;
      background: #2d8cf0;
      clip-path: polygon(100% 100%, 100% 0, 0 100%);
      color: #fff;
      text-align: right;
      .iconfont {
        font-size: 10px;
      }
    }
  }

  .footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 40px;

    button {
      margin-left: 10px;
    }
  }
}
.label-box {
  margin-bottom: 10px;
}
.btn {
  width: 60px;
  height: 24px;
}

.title {
  font-size: 13px;
  margin-bottom: 8px;
}

.nonefont {
  text-align: center;
  padding-top: 20px;
}
</style>
