<template>
  <div class="c_product" v-if="configData">
    <div class="title">{{ configData.title }}</div>
    <div class="list-box">
      <draggable class="dragArea list-group" :list="configData.list" group="peoples" handle=".move-icon">
        <div class="item" v-for="(item, index) in configData.list" :key="index">
          <div class="move-icon">
            <span class="iconfont-diy iconxingzhuangjiehe"></span>
          </div>
          <div>
            <div class="con-item">
              <span>{{ item.text.title }}</span>
              <div>
                <el-input
                  v-model="item.text.val"
                  :placeholder="item.text.pla"
                  :maxlength="item.text.max"
                  show-word-limit
                />
              </div>
            </div>
            <div class="con-item">
              <span>{{ item.dataType.title }}</span>
              <div>
                <el-radio-group v-model="item.dataType.tabVal">
                  <el-radio :label="key" v-for="(radio, key) in item.dataType.tabList" :key="key">
                    <span>{{ radio.name }}</span>
                  </el-radio>
                </el-radio-group>
              </div>
            </div>
            <div class="con-item">
              <span>{{ item.dataType.tabList[item.dataType.tabVal].name }}</span>
              <div>
                <el-input v-if="item.dataType.tabVal == 0" v-model="item.microPage.name" placeholder="选择页面">
                  <i class="el-icon-link" slot="suffix" @click="getLink(index)" />
                </el-input>
                <el-cascader
                  v-else-if="item.dataType.tabVal == 1"
                  @change="sliderChange"
                  @focus="
                    (e) => {
                      activeIndex = index;
                    }
                  "
                  placeholder="请选择分类"
                  size="mini"
                  v-model="item.classPage.id"
                  :options="treeSelect"
                  :props="props"
                  filterable
                  clearable
                >
                </el-cascader>
              </div>
            </div>
          </div>
          <div class="delete" @click.stop="bindDelete(index)">
            <i class="el-icon-circle-close" style="font-size: 20px" />
          </div>
        </div>
      </draggable>
    </div>
    <div v-if="configData.list">
      <div class="add-btn" @click="addHotTxt" v-if="configData.list.length < configData.max">
        <el-button class="btn" type="primary" ghost> <span class="iconfont iconjiahao"></span>添加 </el-button>
      </div>
    </div>
    <!-- <linkaddress
      ref="linkaddres"
      :linkType="1"
      :fromType="'diyPage'"
      :isCateTree="!['homeComb', 'tabNav'].includes(defaults.name)"
      @linkUrl="linkUrl"
    ></linkaddress> -->
    <linkaddress
      ref="linkaddres"
      :linkType="1"
      :fromType="'diyPage'"
      :isCateTree="!['homeComb', 'tabNav'].includes(defaults.name)"
      @linkUrl="linkUrl"
    ></linkaddress>
  </div>
</template>

<script>
import vuedraggable from 'vuedraggable';
import linkaddress from '@/components/linkaddress';
import { cascaderListApi } from '@/api/product';
export default {
  name: 'c_tab_list',
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
      treeSelect: [],
      props: { multiple: false, checkStrictly: false, emitPath: false },
    };
  },
  mounted() {
    this.goodsCategory();
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
    goodsCategory() {
      cascaderListApi(0)
        .then((res) => {
          this.treeSelect = res.data;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    //商品分类
    sliderChange(e) {
      console.log(e);
      // this.configData.list[this.configData.tabCur].selectConfig.activeValue = e;
      // this.$emit('getConfig', { name: 'cascader', values: e });
      this.configData.list[this.activeIndex].classPage.id = e;
      let name = this.treeSelect.find((item) => item.id == e).label;
      this.configData.list[this.activeIndex].classPage.name = name;
    },
    linkUrl(e) {
      console.log(e, this.configData.list[this.activeIndex].dataType.tabVal, '111');
      if (this.configData.list[this.activeIndex].dataType.tabVal) {
        let obj = e.split('?')[1];
        let obj2 = obj.split('&');
        this.configData.list[this.activeIndex].classPage.name = obj2[1].split('=')[1];
        this.configData.list[this.activeIndex].classPage.id = obj2[0].split('=')[1];
      } else {
        let obj = e.split('?')[1];
        let obj2 = obj.split('&');
        this.configData.list[this.activeIndex].microPage.name = obj2[1].split('=')[1];
        this.configData.list[this.activeIndex].microPage.id = obj2[0].split('=')[1];
      }
    },
    getLink(index) {
      this.activeIndex = index;
      let obj = {};
      if (this.configData.list[this.activeIndex].dataType.tabVal) {
        obj = {
          id: 8,
          pid: 2,
          type: 'product_category',
        };
      } else {
        obj = {
          id: 9,
          pid: 1,
          type: 'special',
        };
      }
      this.$refs.linkaddres.handleCheckChange(obj);
      this.$refs.linkaddres.modals = true;
    },
    addHotTxt() {
      if (this.configData.list.length == 0) {
        let storage = window.localStorage;
        this.itemObj = JSON.parse(storage.getItem('itemObj'));
        this.itemObj.dataType.tabVal = 0;
        this.itemObj.microPage.name = '';
        this.itemObj.classPage.name = '';
        this.configData.list.push(this.itemObj);
      } else {
        let obj = JSON.parse(JSON.stringify(this.configData.list[this.configData.list.length - 1]));
        obj.dataType.tabVal = 0;
        obj.microPage.name = '';
        obj.classPage.name = '';
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
    },
  },
};
</script>

<style scoped lang="scss">
::v-deep .ivu-input {
  font-size: 12px !important;
}

::v-deep .ivu-input-wrapper {
  width: 240px;
}

::v-deep .ivu-input-word-count {
  color: #bbbbbb;
}

::v-deep .ivu-input-icon {
  color: #bbbbbb;
}
::v-deep .el-radio{
  margin-bottom: 1px !important;
}
.c_product {
  padding: 0 15px 20px 15px;

  .list-box {
    .item {
      display: flex;
      align-items: center;
      position: relative;
      margin-top: 23px;
      padding: 18px 20px 18px 0;
      background-color: #f9f9f9;
      border-radius: 3px;

      .delete {
        position: absolute;
        right: -10px;
        top: -10px;
        color: #ccc;
        cursor: pointer;
      }
    }

    .move-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 50px;
      cursor: move;
    }

    .con-item {
      display: flex;
      align-items: center;
      margin-bottom: 15px;

      &:last-child {
        margin-bottom: 0;
      }

      span {
        width: 75px;
        font-size: 12px;
        color: #999999;
      }
    }
  }

  .add-btn {
    margin-top: 21px;

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
  font-size: 12px;
  color: #999;
}

.iconfont-diy {
  color: #dddddd;
  font-size: 16px;
}
</style>
