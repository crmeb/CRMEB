<template>
  <div class="c_product" v-if="configData">
    <div class="title">{{ configData.title }}</div>
    <div class="list-box">
      <draggable
        class="dragArea list-group"
        :list="configData.list"
        group="peoples"
        handle=".move-icon"
        :item-key="promotionItemKey"
      >
        <template #item="{ element: item, index }">
          <div class="item" @click="activeBtn(index)">
            <!-- v-model="configData.tabCur" -->
            <div class="acea-row">
              <div class="move-icon">
                <span class="iconfont iconxingzhuangjiehe"></span>
              </div>
              <div class="content">
                <template v-for="(list, key) in item.chiild" :key="key">
                  <div class="con-item" v-if="key < (tabIndex == 0 ? 2 : 1)">
                    <span>{{ list.title }}</span>
                    <div style="width: 100%">
                      <el-input v-model="list.val" :placeholder="list.pla" :maxlength="list.max" />
                    </div>
                  </div>
                </template>
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
                  <div class="title">选择方式</div>
                  <div class="flex-1">
                    <el-select v-model="item.tabVal" placeholder="请选择" @change="tabChange" style="width: 100%">
                      <el-option
                        v-for="(itemn, indexn) in typeList"
                        :value="itemn.activeValue"
                        :key="indexn"
                        :label="itemn.title"
                      ></el-option>
                    </el-select>
                  </div>
                </div>
                <div class="goods-box acea-row" v-if="item.tabVal == 1">
                  <div class="title">选择商品</div>
                  <div class="list">
                    <draggable
                      class="dragArea list-group"
                      :list="item.goodsList.list"
                      group="peoples"
                      :item-key="(element) => item.goodsList.list.indexOf(element)"
                    >
                      <template #item="{ element: goods, index: gIndex }">
                        <div class="items" v-if="item.goodsList.list.length">
                          <img :src="goods.image" alt="" />
                          <span class="iconfont icondel_1" @click.stop="bindGoodDelete(gIndex)"></span>
                        </div>
                      </template>
                      <template #footer>
                        <div class="add-item items" @click="openGoods(index)">
                          <span class="iconfont iconaddto"></span>
                        </div>
                      </template>
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
                        v-model="item.brandConfig.brandVal"
                        :options="brandData"
                        :props="cascaderProps"
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
                        v-model="item.selectConfig.activeValue"
                        :options="treeSelect"
                        :props="cascaderProps"
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
                      <c_slider_deferred
                        v-model="item.numConfig.val"
                        show-input
                        @change="radioChange"
                        :max="100"
                        :min="1"
                        :step="1"
                      />
                    </el-col>
                  </div>
                  <div class="c_row-item">
                    <el-col class="label" :span="5">
                      <span>商品排序</span>
                    </el-col>
                    <el-col class="color-box" :span="19" style="margin-top: 15px">
                      <el-radio-group v-model="item.goodsSort" @change="radioChange()">
                        <el-radio :label="0" :value="0">
                          <span>综合</span>
                        </el-radio>
                        <el-radio :label="1" :value="1">
                          <span>销量</span>
                        </el-radio>
                        <el-radio :label="2" :value="2">
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
        </template>
      </draggable>
    </div>
    <div v-if="configData.list">
      <div class="add-btn" @click="addHotTxt">
        <el-button style="width: 100%; height: 40px">+ 添加</el-button>
      </div>
    </div>
    <!-- 商品标签 -->
    <el-dialog
      v-model="storeLabelShow"
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
    <el-dialog v-model="modals" title="商品列表" class="paymentFooter" width="900">
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
    <el-dialog v-model="modalPic" width="960px" :title="configData.header ? configData.header : '上传图片'">
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

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { ElMessage } from 'element-plus';
import draggable from 'vuedraggable';
import c_slider_deferred from './c_slider_deferred.vue';
import storeLabelList from '@/components/storeLabelList';
import goodsList from '@/components/goodsList';
import uploadPictures from '@/components/uploadPictures';
import { cascaderListApi } from '@/api/product';

defineOptions({ name: 'c_promotion' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
  index: {
    type: null,
  },
});

const emit = defineEmits(['getConfig']);

// el-cascader 的 props 配置（原 data 里名为 props，与组件 props 重名，故改名）
const cascaderProps = { multiple: true, checkStrictly: true, emitPath: false };
const defaults = ref({});
const configData = ref({});
const itemObj = ref({});
const storeLabelShow = ref(false);
const modals = ref(false);
const modalPic = ref(false);
const isChoice = ref('单选');
const gridBtn = ref({
  xl: 4,
  lg: 8,
  md: 8,
  sm: 8,
  xs: 8,
});
const gridPic = ref({
  xl: 6,
  lg: 8,
  md: 12,
  sm: 12,
  xs: 12,
});
const typeList = ref([
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
]);
const brandData = ref([]);
const treeSelect = ref([]);
const tabIndex = ref(1);
const selectIds = ref([]);

const storeLabel = ref(null);
const goodslist = ref(null);

// vuedraggable4 item-key（数据无唯一 id，用索引）
function promotionItemKey(element) {
  return configData.value.list.indexOf(element);
}

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    configData.value = props.configObj[props.configNme];
    goodsCategory();
  });
});

watch(
  () => props.configObj,
  (nVal, oVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
    tabIndex.value = nVal.styleConfig.tabVal;
    // selectIds.value = configData.value.goodsList.ids || [];
  },
  { deep: true },
);

// 点击图文封面
function modalPicTap(title) {
  modalPic.value = true;
}
function bindPicDelete() {
  configData.value.list[configData.value.tabCur].image = '';
}
// 获取图片信息
function getPic(pc) {
  nextTick(() => {
    configData.value.list[configData.value.tabCur].image = pc.att_dir;
    modalPic.value = false;
  });
}
function getBrandList() {
  // 遗留死代码：原文件即未 import brandList，此方法从未被调用
  brandList()
    .then((res) => {
      brandData.value = res.data;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
function goodsCategory() {
  cascaderListApi(1)
    .then((res) => {
      treeSelect.value = res.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function openGoods() {
  modals.value = true;
}
//对象数组去重；
function unique(arr) {
  const res = new Map();
  return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
}
function getProductId(data) {
  modals.value = false;
  let list = configData.value.list[configData.value.tabCur].goodsList.list.concat(data);
  configData.value.list[configData.value.tabCur].goodsList.list = unique(list);
}
function cancel() {
  modals.value = false;
}
function bindGoodDelete(index) {
  configData.value.list[configData.value.tabCur].goodsList.list.splice(index, 1);
}
function openStoreLabel(row, index) {
  storeLabelShow.value = true;
  nextTick(() => {
    storeLabel.value.storeLabel(JSON.parse(JSON.stringify(row)));
  });
}
function closeStoreLabel(label) {
  let list = configData.value.list[configData.value.tabCur].goodsLabel.list;
  let index = list.indexOf(list.filter((d) => d.id == label.id)[0]);
  list.splice(index, 1);
  getLabelId(list);
}
function activeStoreData(storeDataLabel) {
  storeLabelShow.value = false;
  configData.value.list[configData.value.tabCur].goodsLabel.list = storeDataLabel;
  getLabelId(storeDataLabel);
}
function getLabelId(storeDataLabel) {
  let storeActiveIds = [];
  storeDataLabel.forEach((item) => {
    storeActiveIds.push(item.id);
  });
  configData.value.list[configData.value.tabCur].goodsLabel.activeValue = storeActiveIds;
  emit('getConfig', { name: 'goodsLabel' });
}
// 标签弹窗关闭
function storeLabelClose() {
  storeLabelShow.value = false;
}
function addHotTxt() {
  if (configData.value.list.length == 0) {
    let storage = window.localStorage;
    itemObj.value = JSON.parse(storage.getItem('itemObj'));
    if (itemObj.value.link) {
      itemObj.value.link.activeVal = 0;
    }
    itemObj.value.chiild[0].val = '新品推荐';
    itemObj.value.chiild[1].val = '最新出炉';
    itemObj.value.tabVal = 0;
    itemObj.value.selectConfig.activeValue = [];
    itemObj.value.goodsLabel.activeValue = [];
    itemObj.value.goodsLabel.list = [];
    itemObj.value.goodsSort = 0;
    itemObj.value.numConfig.val = 6;
    itemObj.value.goodsList.list = [];
    itemObj.value.productList.list = [];
    configData.value.list.push(itemObj.value);
  } else {
    let obj = JSON.parse(JSON.stringify(configData.value.list[configData.value.list.length - 1]));
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
    configData.value.list.push(obj);
  }
}
// 删除数组
function bindDelete(index) {
  if (configData.value.list.length == 1) {
    let obj = configData.value.list[0];
    itemObj.value = obj;
    let storage = window.localStorage;
    storage.setItem('itemObj', JSON.stringify(obj));
  }
  configData.value.list.splice(index, 1);
  configData.value.tabCur = 0;
  emit('getConfig', { name: 'delete', indexs: 0 });
}
function activeBtn(index) {
  configData.value.tabCur = index;
  // emit('getConfig', { name: 'product', indexs: index })
}
function radioChange(e) {
  emit('getConfig', { name: 'promotion', values: e });
}
// 品牌
function brandChange() {
  emit('getConfig', { name: 'brands' });
}
//商品分类
function sliderChange(e) {
  configData.value.list[configData.value.tabCur].selectConfig.activeValue = e;
  emit('getConfig', { name: 'cascader', values: e });
}
function tabChange(e) {
  emit('getConfig', { name: 'selectType', values: e });
}
</script>

<style scoped lang="scss">
:deep(.el-cascader) {
  width: 100%;
}

:deep(.el-cascader__search-input) {
  margin-left: 8px;
}

:deep(.ivu-radio-wrapper) {
  margin-right: 25px;
}

.box {
  width: 64px;
  height: 64px;
  position: relative;
  border-radius: 3px;

  .pictrue {
    background: url(@diy/assets/images/transparents.jpg) no-repeat;
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

    .iconfont {
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
  .title {
    color: #999999;
    font-size: 12px;
    width: 67px;
  }
  .flex-1 {
    flex: 1;
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

:deep(.ivu-input) {
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
