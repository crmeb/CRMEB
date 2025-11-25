<template>
  <div class="label-wrapper">
    <div v-if="!labelList.length" class="nonefont">暂无标签</div>
    <div v-else class="label-box" v-for="(item, index) in labelList" :key="index">
      <div class="title">{{ item.cate_name }}</div>
      <div class="list">
        <div
          class="label"
          :class="{ on: label.disabled }"
          v-for="(label, j) in item.list"
          :key="j"
          v-db-click
          @click="selectLabel(label, index)"
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
          <div class="sanjiao" v-show="label.disabled">
            <span class="iconfont iconwancheng"></span>
          </div>
        </div>
      </div>
    </div>
    <div class="footer">
      <el-button type="primary" class="btns" ghost @click="cancel">取消</el-button>
      <el-button type="primary" class="btns" @click="subBtn">确定</el-button>
    </div>
  </div>
</template>

<script>
import { productLabelUseListApi } from '@/api/product';
export default {
  name: 'storeLabelList',
  props: {},
  data() {
    return {
      labelList: [],
      dataLabel: [],
      isStore: false,
    };
  },
  mounted() {},
  methods: {
    inArray: function (search, array) {
      for (const i in array) {
        if (array[i].id === search) {
          return true;
        }
      }
      return false;
    },
    storeLabel(data) {
      this.dataLabel = data || [];
      productLabelUseListApi()
        .then((res) => {
          res.data.map((el) => {
            if (el.list && el.list.length) {
              this.isStore = true;
              el.list.map((label) => {
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
    selectLabel(label, index) {
      console.log(this.dataLabel);
      if (label.disabled) {
        const index = this.dataLabel.indexOf(this.dataLabel.filter((d) => d.id === label.id)[0]);
        this.dataLabel.splice(index, 1);
        label.disabled = false;
      } else {
        this.dataLabel.push({ label_name: label.name, id: label.id });
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
