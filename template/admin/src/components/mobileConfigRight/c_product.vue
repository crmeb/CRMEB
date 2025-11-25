<template>
  <div class="c_product" v-if="configData">
    <div class="title" v-if="configData.title">{{ configData.title }}</div>
    <div class="list-box">
      <draggable class="dragArea list-group" :list="configData.list" group="peoples" handle=".move-icon">
        <div class="item" v-for="(item, index) in configData.list" :key="index" @click="activeBtn(index)">
          <!-- v-model="configData.tabCur" -->
          <div class="move-icon">
            <span class="iconfont-diy iconxingzhuangjiehe"></span>
          </div>
          <div class="content">
            <div class="con-item" v-for="(list, key) in item.chiild" :key="key">
              <span>{{ list.title }}</span>
              <div style="width: 100%">
                <el-input v-model="list.val" :placeholder="list.pla">
                  <i v-if="list.title == '链接'" class="el-icon-link" slot="suffix" @click="getLink(index, key, item)" />
                </el-input>
              </div>
            </div>
            <div class="con-item" v-if="configData.type">
              <span>状态</span>
              <el-switch v-model="item.show" />
            </div>
            <div class="con-item" v-if="item.link">
              <span>{{ item.link.title }}</span>
              <el-select v-model="item.link.activeVal" style="" @change="(e) => sliderChange(index, e)">
                <el-option
                  v-for="(item, j) in item.link.optiops"
                  :value="item.value"
                  :key="j"
                  :label="item.label"
                ></el-option>
              </el-select>
            </div>
          </div>
          <div class="delete" @click.stop="bindDelete(index)">
            <i class="el-icon-circle-close"  style="font-size: 20px" />
          </div>
        </div>
      </draggable>
    </div>
    <div v-if="configData.list">
      <div class="add-btn" @click="addHotTxt" v-if="configData.list.length < configData.max">
        <el-button class="btn" type="primary" ghost> <span class="iconfont iconjiahao"></span>添加 </el-button>
      </div>
    </div>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import vuedraggable from 'vuedraggable';
import linkaddress from '@/components/linkaddress';

export default {
  name: 'c_product',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
    index: {
      type: null,
    },
  },
  components: {
    linkaddress,
    draggable: vuedraggable,
  },
  data() {
    return {
      defaults: {},
      configData: {},
      itemObj: {},
      activeIndex: 0,
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
    });
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
      },
      deep: true,
    },
  },
  methods: {
    linkUrl(e) {
      this.configData.list[this.activeIndex].chiild[1].val = e;
    },
    getLink(index, key, item) {
      if (!key || item.link) {
        return;
      }
      this.activeIndex = index;
      this.$refs.linkaddres.modals = true;
    },
    addHotTxt() {
      if (this.configData.list.length == 0) {
        let storage = window.localStorage;
        this.itemObj = JSON.parse(storage.getItem('itemObj'));
        if (this.itemObj.link) {
          this.itemObj.link.activeVal = 0;
        }
        this.itemObj.chiild[0].val = '';
        this.itemObj.chiild[1].val = '';
        this.configData.list.push(this.itemObj);
      } else {
        let obj = JSON.parse(JSON.stringify(this.configData.list[this.configData.list.length - 1]));
        if (obj.chiild[0].empty) {
          obj.chiild[0].val = '';
          obj.chiild[1].val = '';
        }
        this.configData.list.push(obj);
      }
    },
    // 删除数组
    bindDelete(index) {
      if (this.configData.list.length == 1) {
        let itemObj = this.configData.list[0];
        this.itemObj = itemObj;
        let storage = window.localStorage;
        storage.setItem('itemObj', JSON.stringify(itemObj));
      }
      this.configData.list.splice(index, 1);
      this.configData.tabCur = 0;
      this.$emit('getConfig', { name: 'delete', indexs: 0 });
    },
    sliderChange(index) {
      this.configData.tabCur = index;
      this.$emit('getConfig', { name: 'product', indexs: index });
    },
    activeBtn(index) {
      this.configData.tabCur = index;
      this.$emit('getConfig', { name: 'product', indexs: index });
    },
  },
};
</script>

<style scoped lang="scss">
::v-deep .ivu-input {
  font-size: 12px !important;
}

::v-deep .ivu-input-word-count {
  color: #bbbbbb;
}

::v-deep .ivu-input-icon {
  color: #bbbbbb;
}

.c_product {
  margin-bottom: 20px;
  padding: 0 15px 20px 15px;

  .list-box {
    .item {
      position: relative;
      display: flex;
      padding: 18px 20px 18px 0;
      background-color: #f9f9f9;
      border-radius: 3px;

      .delete {
        position: absolute;
        right: -13px;
        top: -14px;
        color: #ccc;
        cursor: pointer;
      }
    }

    .move-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      cursor: move;
    }

    .content {
      flex: 1;

      .con-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;

        &:last-child {
          margin-bottom: 0;
        }

        span {
          width: 45px;
          font-size: 12px;
          color: #999;
        }
      }
    }
  }

  .add-btn {
    margin-top: 20px;

    .btn {
      width: 100%;
      height: 36px;
      border-color: #eeeeee;
      color: #666666;

      .iconfont {
        font-size: 11px;
        margin-right: 5px;
      }
    }
  }
}

.title {
  padding-top: 20px;
  font-size: 12px;
  color: #999;
}

.iconfont-diy {
  color: #dddddd;
  font-size: 16px;
}
</style>
