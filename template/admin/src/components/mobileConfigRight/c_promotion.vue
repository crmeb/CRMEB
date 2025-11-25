<template>
  <div class="c_product" v-if="configData">
    <div class="title">{{ configData.title }}</div>
    <div class="list-box">
      <draggable class="dragArea list-group" :list="configData.list" group="peoples" handle=".move-icon">
        <div class="item" v-for="(item, index) in configData.list" :key="index" @click="activeBtn(index)">
          <!-- v-model="configData.tabCur" -->
          <div class="acea-row">
            <div class="move-icon">
              <span class="iconfont-diy iconxingzhuangjiehe"></span>
            </div>
            <div class="content">
              <div class="con-item" v-for="(list, key) in item.chiild" :key="key" v-if="key < (tabIndex == 0 ? 2 : 1)">
                <span>{{ list.title }}</span>
                <div style="width: 100%">
                  <el-input v-model="list.val" :placeholder="list.pla" :maxlength="list.max" />
                </div>
              </div>
            </div>
          </div>
          <div class="acea-row row-right" v-if="configData.tabCur == index">
            <div class="conter">
              <div class="c_row-item" v-if="tabIndex == 4">
                <div class="c_label">上传图片</div>
                <div class="color-box">
                  <div class="box" @click="modalPicTap('单选')">
                    <div class="pictrue acea-row row-center-wrapper" v-if="item.image">
                      <img :src="item.image" alt="" />
                      <div class="iconfont icondel_1" @click.stop="bindPicDelete"></div>
                    </div>
                    <div class="upload-box" v-else><i class="el-icon-plus" /></div>
                  </div>
                </div>
              </div>
              <div class="c_row-item">
                <el-col class="c_label"> 选择方式 </el-col>
                <el-col class="color-box">
                  <el-select v-model="item.tabVal" placeholder="请选择" @change="tabChange">
                    <el-option
                      v-for="(itemn, indexn) in typeList"
                      :value="itemn.activeValue"
                      :key="indexn"
                      :label="itemn.title"
                    ></el-option>
                  </el-select>
                </el-col>
              </div>
              <div class="goods-box acea-row" v-if="item.tabVal == 1">
                <div class="title">选择商品</div>
                <div class="list">
                  <draggable class="dragArea list-group" :list="item.goodsList.list" group="peoples">
                    <div
                      class="items"
                      v-for="(goods, gIndex) in item.goodsList.list"
                      :key="gIndex"
                      v-if="item.goodsList.list.length"
                    >
                      <img :src="goods.image" alt="" />
                      <span class="iconfont-diy icondel_1" @click.stop="bindGoodDelete(gIndex)"></span>
                    </div>
                    <div class="add-item items" @click="openGoods(index)">
                      <span class="iconfont-diy iconaddto"></span>
                    </div>
                  </draggable>
                </div>
              </div>
              <div v-else>
                <div class="c_row-item" v-if="item.tabVal == 2">
                  <el-col class="label" :span="4">品牌名称</el-col>
                  <el-col :span="19" class="slider-box">
                    <el-cascader
                      @change="brandChange"
                      placeholder="请选择品牌"
                      size="mini"
                      v-model="item.brandConfig.brandVal"
                      :options="brandData"
                      :props="props"
                      filterable
                      clearable
                    >
                    </el-cascader>
                  </el-col>
                </div>
                <div class="c_row-item" v-else-if="item.tabVal == 3">
                  <el-col class="label" :span="4">商品分类</el-col>
                  <el-col :span="19" class="slider-box">
                    <el-cascader
                      @change="sliderChange"
                      placeholder="请选择分类"
                      size="mini"
                      v-model="item.selectConfig.activeValue"
                      :options="treeSelect"
                      :props="props"
                      filterable
                      clearable
                    >
                    </el-cascader>
                  </el-col>
                </div>
                <div class="c_row-item" v-else>
                  <el-col class="label" :span="4">商品标签</el-col>
                  <el-col :span="19" class="slider-box">
                    <div
                      class="labelInput acea-row row-between-wrapper"
                      @click="openStoreLabel(item.goodsLabel.list, index)"
                    >
                      <div style="width: 90%">
                        <div v-if="item.goodsLabel.list.length">
                          <el-tag
                            v-for="(j, jindex) in item.goodsLabel.list"
                            :key="jindex"
                            @on-close="closeStoreLabel(j)"
                            >{{ j.label_name }}</el-tag
                          >
                        </div>
                        <span class="span" v-else>选择商品标签</span>
                      </div>
                      <div class="iconfont iconxiayi"></div>
                    </div>
                  </el-col>
                </div>
                <div class="c_row-item">
                  <el-col class="label" :span="4">
                    <span>商品数量</span>
                  </el-col>
                  <el-col :span="19" class="slider-box on">
                    <!-- sliderChange -->
                    <el-slider
                      v-model="item.numConfig.val"
                      show-input
                      @change="radioChange()"
                      :max="100"
                      :min="1"
                      :step="1"
                    ></el-slider>
                  </el-col>
                </div>
                <div class="c_row-item">
                  <el-col class="c_label"> 商品排序 </el-col>
                  <el-col class="color-box">
                    <el-radio-group v-model="item.goodsSort" @input="radioChange()">
                      <el-radio :label="0">
                        <span>综合</span>
                      </el-radio>
                      <el-radio :label="1">
                        <span>销量</span>
                      </el-radio>
                      <el-radio :label="2">
                        <span>价格</span>
                      </el-radio>
                    </el-radio-group>
                  </el-col>
                </div>
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
      <div class="add-btn" @click="addHotTxt">
        <el-button style="width: 100%; height: 40px">+ 添加</el-button>
      </div>
    </div>
    <!-- 商品标签 -->
    <el-dialog
      :visible.sync="storeLabelShow"
      scrollable
      title="选择商品标签"
      :closable="true"
      width="540"
      :footer-hide="true"
      :mask-closable="false"
    >
      <storeLabelList
        v-if="storeLabelShow"
        ref="storeLabel"
        @activeData="activeStoreData"
        @close="storeLabelClose"
      ></storeLabelList>
    </el-dialog>
    <el-dialog :visible.sync="modals" title="商品列表" class="paymentFooter" width="900">
      <goods-list
        ref="goodslist"
        :ischeckbox="true"
        :isdiy="true"
        isType
        :selectIds="selectIds"
        @getProductId="getProductId"
        v-if="modals"
      ></goods-list>
    </el-dialog>
    <el-dialog :visible.sync="modalPic" width="960px" :title="configData.header ? configData.header : '上传图片'">
      <uploadPictures
        :isChoice="isChoice"
        @getPic="getPic"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="modalPic"
      ></uploadPictures>
    </el-dialog>
  </div>
</template>

<script>
import vuedraggable from 'vuedraggable';
import storeLabelList from '@/components/storeLabelList';
import goodsList from '@/components/goodsList';
import uploadPictures from '@/components/uploadPictures';
import { cascaderListApi } from '@/api/product';
export default {
  name: 'c_promotion',
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
    draggable: vuedraggable,
    storeLabelList,
    goodsList,
    uploadPictures,
  },
  data() {
    return {
      props: { multiple: true, checkStrictly: true, emitPath: false },
      defaults: {},
      configData: {},
      itemObj: {},
      storeLabelShow: false,
      modals: false,
      modalPic: false,
      isChoice: '单选',
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
      typeList: [
        {
          activeValue: 1,
          title: '指定商品',
        },
        {
          activeValue: 3,
          title: '指定分类',
        },
        {
          activeValue: 4,
          title: '商品标签',
        },
      ],
      brandData: [],
      treeSelect: [],
      tabIndex: 1,
      selectIds: [],
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
      this.goodsCategory();
    });
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
        this.tabIndex = nVal.styleConfig.tabVal;
        // this.selectIds = nVal[this.configNme].goodsList.ids || [];
      },
      deep: true,
    },
  },
  methods: {
    // 点击图文封面
    modalPicTap(title) {
      this.modalPic = true;
    },
    bindPicDelete() {
      this.configData.list[this.configData.tabCur].image = '';
    },
    // 获取图片信息
    getPic(pc) {
      this.$nextTick(() => {
        this.configData.list[this.configData.tabCur].image = pc.att_dir;
        this.modalPic = false;
      });
    },
    getBrandList() {
      brandList()
        .then((res) => {
          this.brandData = res.data;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    goodsCategory() {
      cascaderListApi(1)
        .then((res) => {
          this.treeSelect = res.data;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    openGoods() {
      this.modals = true;
    },
    //对象数组去重；
    unique(arr) {
      const res = new Map();
      return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
    },
    getProductId(data) {
      this.modals = false;
      let list = this.configData.list[this.configData.tabCur].goodsList.list.concat(data);
      this.configData.list[this.configData.tabCur].goodsList.list = this.unique(list);
    },
    cancel() {
      this.modals = false;
    },
    bindGoodDelete(index) {
      this.configData.list[this.configData.tabCur].goodsList.list.splice(index, 1);
    },
    openStoreLabel(row, index) {
      this.storeLabelShow = true;
      this.$nextTick(() => {
        this.$refs.storeLabel.storeLabel(JSON.parse(JSON.stringify(row)));
      });
    },
    closeStoreLabel(label) {
      let list = this.configData.list[this.configData.tabCur].goodsLabel.list;
      let index = list.indexOf(list.filter((d) => d.id == label.id)[0]);
      list.splice(index, 1);
      this.getLabelId(list);
    },
    activeStoreData(storeDataLabel) {
      this.storeLabelShow = false;
      this.configData.list[this.configData.tabCur].goodsLabel.list = storeDataLabel;
      this.getLabelId(storeDataLabel);
    },
    getLabelId(storeDataLabel) {
      let storeActiveIds = [];
      storeDataLabel.forEach((item) => {
        storeActiveIds.push(item.id);
      });
      this.configData.list[this.configData.tabCur].goodsLabel.activeValue = storeActiveIds;
      this.$emit('getConfig', { name: 'goodsLabel' });
    },
    // 标签弹窗关闭
    storeLabelClose() {
      this.storeLabelShow = false;
    },
    addHotTxt() {
      if (this.configData.list.length == 0) {
        let storage = window.localStorage;
        this.itemObj = JSON.parse(storage.getItem('itemObj'));
        if (this.itemObj.link) {
          this.itemObj.link.activeVal = 0;
        }
        this.itemObj.chiild[0].val = '首发新品';
        this.itemObj.chiild[1].val = '最新出炉';
        this.itemObj.tabVal = 0;
        this.itemObj.selectConfig.activeValue = [];
        this.itemObj.goodsLabel.activeValue = [];
        this.itemObj.goodsLabel.list = [];
        this.itemObj.goodsSort = 0;
        this.itemObj.numConfig.val = 6;
        this.itemObj.goodsList.list = [];
        this.itemObj.productList.list = [];
        this.configData.list.push(this.itemObj);
      } else {
        let obj = JSON.parse(JSON.stringify(this.configData.list[this.configData.list.length - 1]));
        if (obj.chiild[0].empty) {
          obj.chiild[0].val = '';
          obj.chiild[1].val = '';
        }
        obj.tabVal = 1;
        obj.selectConfig.activeValue = [];
        obj.goodsLabel.activeValue = [];
        obj.goodsLabel.list = [];
        obj.goodsSort = 0;
        obj.numConfig.val = 6;
        obj.goodsList.list = [];
        obj.productList.list = [];
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
    activeBtn(index) {
      this.configData.tabCur = index;
      // this.$emit('getConfig', { name: 'product', indexs: index })
    },
    radioChange(e) {
      this.$emit('getConfig', { name: 'promotion', values: e });
    },
    // 品牌
    brandChange() {
      this.$emit('getConfig', { name: 'brands' });
    },
    //商品分类
    sliderChange(e) {
      this.configData.list[this.configData.tabCur].selectConfig.activeValue = e;
      this.$emit('getConfig', { name: 'cascader', values: e });
    },
    tabChange(e) {
      this.$emit('getConfig', { name: 'selectType', values: e });
    },
  },
};
</script>

<style scoped lang="scss">
::v-deep .el-cascader {
  width: 100%;
}

::v-deep .el-cascader__search-input {
  margin-left: 8px;
}

::v-deep .ivu-radio-wrapper {
  margin-right: 25px;
}

.box {
  width: 64px;
  height: 64px;
  position: relative;
  border-radius: 3px;

  .pictrue {
    background: url(../../assets/images/transparents.jpg) no-repeat;
    background-size: 100% 100%;
    position: relative;
    width: 100%;
    height: 100%;

    .iconfont {
      position: absolute;
      right: -12px;
      top: -19px;
      font-size: 24px;
      color: #cccccc;
    }
  }

  .upload-box {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    border-radius: 3px;
    border: 1px solid #eeeeee;

    .ivu-icon {
      color: #ccc;
    }
  }

  img {
    width: 100%;
    height: 100%;
    border-radius: 3px;
  }
}

.goods-box {
  .title {
    color: #999999;
    font-size: 12px;
    width: 67px;
    margin-top: 23px;
  }

  .list {
    width: 236px;
  }

  .list-group {
    display: flex;
    flex-wrap: wrap;
  }

  .add-item {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;

    .iconfont-diy {
      font-size: 25px;
      color: #d8d8d8;
    }
  }

  .items {
    position: relative;
    width: 64px;
    height: 64px;
    margin-bottom: 16px;
    margin-right: 12px;
    border: 1px solid #eee;
    border-radius: 3px;

    img {
      width: 100%;
      height: 100%;
    }

    .icondel_1 {
      position: absolute;
      right: -10px;
      top: -16px;
      color: #cccccc;
      font-size: 22px;
      cursor: pointer;
    }
  }
}

.ivu-input-number {
  width: 100%;
}

.labelInput {
  border: 1px solid #dcdee2;
  width: 100%;
  padding: 0 8px;
  border-radius: 5px;
  min-height: 30px;
  background-color: #fff;
  cursor: pointer;

  .span {
    color: #c7c7c7;
    font-size: 12px;
  }

  .iconxiayi {
    font-size: 12px;
  }
}

.conter {
  width: 335px;
  background: #f9f9f9;
  padding: 20px 15px 4px 15px;
  margin-top: 20px;
}

.txt_tab {
  margin-top: 20px;
}

.c_row-item {
  margin-bottom: 20px;

  .slider-box {
    padding-left: 3px;

    &.on {
      padding-left: 10px;
    }
  }

  .label {
    color: #999999;
    font-size: 12px;
  }

  .c_label {
    color: #999999;
    font-size: 12px;
  }

  .color-box {
    // width: 243px;
  }
}

.row-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.iconfont {
  font-size: 18px;
}

::v-deep .ivu-input {
  font-size: 12px !important;
}

.c_product {
  margin-bottom: 20px;
  padding: 0 15px;

  .list-box {
    .item {
      position: relative;
      margin-top: 20px;
      padding: 20px 15px 20px 0;
      border: 1px solid rgba(238, 238, 238, 1);
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
      width: 40px;
      cursor: move;

      .iconxingzhuangjiehe {
        color: #ddd;
      }
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
    margin-top: 18px;
  }
}

.title {
  font-size: 12px;
  color: #bbbbbb;
}

.icondrag2 {
  color: #dddddd;
  font-size: 38px;
}
</style>
