<template>
  <div class="label-wrapper">
    <div class="list-box">
      <template v-if="isUser">
        <div class="label-box" v-for="(item, index) in labelList" :key="index">
          <div class="title" v-if="item.children">{{ item.label_name }}</div>
          <div class="list" v-if="item.children && item.children.length">
            <div
              class="label-item"
              :class="{ on: label.disabled }"
              v-for="(label, j) in item.children"
              :key="j"
              v-db-click
              @click="selectLabel(label)"
            >
              {{ label.label_name }}
            </div>
          </div>
        </div>
      </template>
      <div v-if="!isUser">暂无标签</div>
    </div>
    <div class="footer">
      <el-button class="btns" ghost v-db-click @click="cancel">取消</el-button>
      <el-button type="primary" class="btns" v-db-click @click="subBtn">确定</el-button>
    </div>
  </div>
</template>

<script>
import { productUserLabel } from '@/api/product';
export default {
  name: 'userLabel',
  props: {
    // dataLabel: {
    // 	type: Array,
    // 	default: () => []
    // }
  },
  data() {
    return {
      labelList: [],
      dataLabel: [],
      isUser: false,
    };
  },
  mounted() {
    this.setLabel();
  },
  methods: {
    inArray: function (search, array) {
      for (let i in array) {
        if (array[i].id == search) {
          return true;
        }
      }
      return false;
    },
    // 用户标签
    setLabel() {
      // this.dataLabel = data;
      productUserLabel()
        .then((res) => {
          res.data.map((el) => {
            if (el.children) {
              this.isUser = true;
              el.children.map((label) => {
                if (this.inArray(label.id, this.dataLabel)) {
                  label.disabled = true;
                } else {
                  label.disabled = false;
                }
              });
            }
          });
          this.labelList = res.data;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    selectLabel(label) {
      if (label.disabled) {
        let index = this.dataLabel.indexOf(this.dataLabel.filter((d) => d.id == label.id)[0]);
        this.dataLabel.splice(index, 1);
        label.disabled = false;
      } else {
        this.dataLabel.push({ label_name: label.label_name, id: label.id });
        label.disabled = true;
      }
    },
    // 确定
    subBtn() {
      this.$emit('activeData', JSON.parse(JSON.stringify(this.dataLabel)));
    },
    cancel() {
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
    .label-item {
      margin: 10px 8px 10px 0;
      padding: 3px 8px;
      background: #eeeeee;
      color: #333333;
      border-radius: 2px;
      cursor: pointer;
      font-size: 12px;
      &.on {
        color: #fff;
        background: var(--prev-color-primary);
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
.btn {
  width: 60px;
  height: 24px;
}
.title {
  font-size: 13px;
}
.list-box {
  overflow-y: auto;
  overflow-x: hidden;
  max-height: 240px;
}
.label-box {
  margin-bottom: 10px;
}
</style>
