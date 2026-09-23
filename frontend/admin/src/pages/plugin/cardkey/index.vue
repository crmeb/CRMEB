<template>
  <div class="article-manager">
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="artFrom" label-width="80px" label-position="right" inline @submit.prevent>
          <div class="acea-row search-form">
            <div class="search-form-box">
              <el-form-item label="商品搜索：" label-for="store_name">
                <el-input
                  clearable
                  placeholder="请输入商品名称/关键字/ID"
                  v-model="artFrom.store_name"
                  class="form_content_width"
                />
              </el-form-item>
              <el-form-item label="商品分类：" label-for="pid">
                <el-cascader
                  v-model="artFrom.cate_id"
                  :options="treeSelect"
                  :props="{ multiple: false, emitPath: false, checkStrictly: true }"
                  clearable
                  class="form_content_width"
                ></el-cascader>
              </el-form-item>
              <template v-if="collapse">
                <el-form-item label="会员专属：">
                  <el-select v-model="artFrom.vip_product" clearable placeholder="全部" class="form_content_width">
                    <el-option label="全部" value="" />
                    <el-option label="不限制" value="0" />
                    <el-option label="仅会员可见可买" value="1" />
                    <el-option label="所有人可见仅会员可买" value="2" />
                  </el-select>
                </el-form-item>
                <el-form-item label="添加时间：">
                  <date-range-picker
                    class="form_range_content_width"
                    clearable
                    v-model="timeVal"
                    :editable="false"
                    @change="onchangeTime"
                    format="YYYY/MM/DD"
                    value-format="YYYY/MM/DD"
                  ></date-range-picker>
                </el-form-item>
                <el-form-item label="库存：" label-for="store_name">
                  <el-input
                    clearable
                    placeholder="最小值"
                    v-model="artFrom.stock_s[0]"
                    class="form_range_content_width"
                  />
                  ~
                  <el-input
                    clearable
                    placeholder="最大值"
                    v-model="artFrom.stock_s[1]"
                    class="form_range_content_width"
                  />
                </el-form-item>
                <el-form-item label="价格：" label-for="store_name">
                  <el-input
                    clearable
                    placeholder="最小值"
                    v-model="artFrom.price_s[0]"
                    class="form_range_content_width"
                  />
                  ~
                  <el-input
                    clearable
                    placeholder="最大值"
                    v-model="artFrom.price_s[1]"
                    class="form_range_content_width"
                  />
                </el-form-item>
                <el-form-item label="销量：" label-for="store_name">
                  <el-input
                    clearable
                    placeholder="最小值"
                    v-model="artFrom.sales_s[0]"
                    class="form_range_content_width"
                  />
                  ~
                  <el-input
                    clearable
                    placeholder="最大值"
                    v-model="artFrom.sales_s[1]"
                    class="form_range_content_width"
                  />
                </el-form-item>
              </template>
            </div>
            <div class="search-form-sub">
              <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
              <el-button class="ResetSearch" v-db-click @click="reset">重置</el-button>
              <a class="ivu-ml-8 font12 ml10" v-db-click @click="collapse = !collapse">
                <template v-if="!collapse"> 展开 <i class="el-icon-arrow-down" /> </template>
                <template v-else> 收起 <i class="el-icon-arrow-up" /> </template>
              </a>
            </div>
          </div>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16" :body-style="{ padding: '20px 20px 20px' }">
      <div class="Button">
        <router-link v-auth="['admin-cardkey-product-save']" :to="$routeProStr + '/product/cardkey/add'"
          ><el-button type="primary" class="mr14">添加卡密商品</el-button></router-link
        >
        <el-dropdown class="bnt mr14" @command="batchSelect">
          <el-button>批量修改<i class="el-icon-arrow-down el-icon--right"></i></el-button>
          <template #dropdown
            ><el-dropdown-menu>
              <el-dropdown-item :command="1">商品分类</el-dropdown-item>
              <el-dropdown-item :command="3">购买送积分</el-dropdown-item>
              <el-dropdown-item :command="4">购买送优惠券</el-dropdown-item>
              <el-dropdown-item :command="5">关联用户标签</el-dropdown-item>
              <el-dropdown-item v-if="artFrom.type === '1'" :command="7">批量下架</el-dropdown-item>
              <el-dropdown-item v-if="artFrom.type === '2'" :command="8">批量上架</el-dropdown-item>
              <el-dropdown-item :command="9">设置商品标签</el-dropdown-item>
              <el-dropdown-item v-if="artFrom.type !== '6'" :command="11">移到回收站</el-dropdown-item>
              <el-dropdown-item v-if="artFrom.type == '6'" :command="12">恢复商品</el-dropdown-item>
            </el-dropdown-menu></template
          >
        </el-dropdown>
      </div>
      <el-table
        ref="table"
        :data="tableList"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        :row-key="getRowKey"
        @selection-change="handleSelectRow"
        empty-text="暂无数据"
      >
        <el-table-column type="selection" width="60" :reserve-selection="true"> </el-table-column>
        <el-table-column label="商品ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品图" min-width="90">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="商品名称" min-width="250">
          <template #default="scope">
            <span>{{ scope.row.store_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="卡密类型" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.card_type === 'combo' ? '组合卡密' : '单卡密' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="卡密数量" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.card_count || 0 }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品售价" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="销量" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.sales }}</span>
          </template>
        </el-table-column>
        <el-table-column label="排序" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.sort }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="100">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_show"
              :value="scope.row.is_show"
              :disabled="scope.row.stop_status ? true : false"
              @change="changeSwitch(scope.row)"
              size="large"
              active-text="上架"
              inactive-text="下架"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" minWidth="100">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <el-dropdown>
              <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
              <template #dropdown
                ><el-dropdown-menu>
                  <el-dropdown-item>
                    <router-link :to="{ path: $routeProStr + '/product/product_reply/' + scope.row.id }"
                      ><a>查看评论</a></router-link
                    >
                  </el-dropdown-item>
                  <el-dropdown-item @click="openModal(scope.row, 'vipPriceSet')">会员价管理</el-dropdown-item>
                  <el-dropdown-item @click="openModal(scope.row, 'brokerageSet')">佣金管理</el-dropdown-item>
                  <el-dropdown-item v-if="artFrom.type === '6'" @click="del(scope.row, '恢复商品', scope.$index)"
                    >恢复商品</el-dropdown-item
                  >
                  <el-dropdown-item v-if="artFrom.type === '6'" @click="fullDel(scope.row, '彻底删除', scope.$index)"
                    >彻底删除</el-dropdown-item
                  >
                  <el-dropdown-item v-else @click="del(scope.row, '移入回收站', scope.$index)"
                    >移到回收站</el-dropdown-item
                  >
                </el-dropdown-menu></template
              >
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="artFrom.page"
          v-model:limit="artFrom.limit"
          @pagination="getDataList"
        />
      </div>
    </el-card>
    <brokerageSet ref="brokerageSetRef" :productId="productId"></brokerageSet>
    <vipPriceSet ref="vipPriceSetRef" :productId="productId"></vipPriceSet>
    <el-dialog
      v-model="batchModal"
      class="batch-box"
      title="批量设置"
      :show-close="true"
      :close-on-click-modal="false"
      width="540px"
    >
      <el-form
        class="batchFormData"
        :rules="ruleBatch"
        :model="batchFormData"
        label-width="90px"
        label-position="right"
        @submit.prevent
      >
        <el-row :gutter="24">
          <el-col :span="24" v-if="batchType == 1">
            <el-form-item label="商品分类：" prop="cate_id">
              <el-cascader
                v-model="batchFormData.cate_id"
                :options="treeSelect"
                :props="{ multiple: true, emitPath: false, checkStrictly: true }"
                clearable
                style="width: 400px"
              ></el-cascader>
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="[3, 4, 5].includes(batchType)">
            <el-form-item label="赠送积分：" prop="give_integral" v-if="batchType == 3">
              <el-input-number
                :controls="false"
                v-model="batchFormData.give_integral"
                :min="0"
                :max="9999999999"
                placeholder="请输入积分"
                style="width: 100%"
              />
            </el-form-item>
            <el-form-item label="赠送优惠券：" v-if="batchType == 4">
              <div v-if="couponName.length" class="mb20">
                <el-tag closable v-for="(item, index) in couponName" :key="index" @close="handleClose(item)">{{
                  item.title
                }}</el-tag>
              </div>
              <el-button type="primary" v-db-click @click="addCoupon">添加优惠券</el-button>
            </el-form-item>
            <el-form-item label="关联标签：" prop="label_id" v-if="batchType == 5">
              <div class="acea-row label_width">
                <div class="labelInput acea-row row-between-wrapper" v-db-click @click="openLabel">
                  <div style="width: auto">
                    <div v-if="dataLabel.length">
                      <el-tag
                        class="m-r-2"
                        closable
                        v-for="(item, index) in dataLabel"
                        @close="closeLabel(item)"
                        :key="index"
                        >{{ item.label_name }}</el-tag
                      >
                    </div>
                    <span class="span" v-else>选择用户关联标签</span>
                  </div>
                  <div class="iconfont iconxiayi"></div>
                </div>
              </div>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="clearBatchData">取 消</el-button>
          <el-button type="primary" v-db-click @click="batchSub">确 定</el-button>
        </span></template
      >
    </el-dialog>
    <el-dialog
      v-model="labelShow"
      title="请选择用户标签"
      width="540px"
      :show-close="true"
      :close-on-click-modal="false"
    >
      <userLabel @activeData="activeData" @close="labelClose"></userLabel>
    </el-dialog>
    <el-dialog v-model="tagShow" title="请选择商品标签" :show-close="true" width="540px" :close-on-click-modal="false">
      <goodsLabel :defaultLabelList="goodsLabelList" @activeLabel="activeGoodsLabel" @close="labelClose"></goodsLabel>
    </el-dialog>
    <coupon-list ref="couponTemplates" @nameId="nameId" :couponids="batchFormData.coupon_ids"></coupon-list>
  </div>
</template>

<script setup>
import { nextTick, onActivated, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { cardkeyProductList } from '@/pages/plugin/cardkey/cardkeyProduct';
import {
  PostgoodsIsShow,
  cascaderListApi,
  productShowApi,
  productUnshowApi,
  batchSetting,
  productLabelUseListApi,
} from '@/api/product';
import userLabel from '@/components/labelList';
import goodsLabel from '@/components/goodsLabel';
import couponList from '@/components/couponList';
import brokerageSet from '../../../pages/product/components/brokerageSet.vue';
import vipPriceSet from '../../../pages/product/components/vipPriceSet.vue';
import { modalSure } from '@/utils/public';
import setting from '@/setting';

defineOptions({ name: 'cardkeyProduct' });

const router = useRouter();
const routePre = setting.routePre;

const table = ref(null);
const brokerageSetRef = ref(null);
const vipPriceSetRef = ref(null);
const couponTemplates = ref(null);

const batchModal = ref(false);
const labelShow = ref(false);
const tagShow = ref(false);
const batchType = ref(1);
const batchFormData = ref({
  cate_id: [],
  give_integral: 0,
  label_id: [],
  coupon_ids: [],
});
const ruleBatch = ref({});
const couponName = ref([]);
const dataLabel = ref([]);
const artFrom = ref({
  page: 1,
  limit: 15,
  store_name: '',
  cate_id: '',
  vip_product: '',
  sales_s: ['', ''],
  stock_s: ['', ''],
  price_s: ['', ''],
  time: '',
  type: '1',
});
const tableList = ref([]);
const total = ref(0);
const loading = ref(false);
const treeSelect = ref([]);
const ids = ref([]);
const multipleSelection = ref([]);
const productId = ref(0);
const goodsLabelList = ref([]);
const timeVal = ref([]);
const collapse = ref(false);

function refreshAll() {
  goodsCategory();
  getLabelList();
  getDataList();
}

onMounted(() => {
  // 首次挂载加载数据（activated 依赖 keep-alive 缓存，未缓存时不会触发）
  refreshAll();
});

onActivated(() => {
  // keep-alive 缓存后再次进入时刷新数据
  refreshAll();
});

function onchangeTime(e) {
  timeVal.value = e;
  artFrom.value.time = timeVal.value ? timeVal.value.join('-') : '';
  artFrom.value.page = 1;
  getDataList();
}
function getLabelList() {
  productLabelUseListApi()
    .then((res) => {
      res.data.map((el) => {
        if (el.list && el.list.length) {
          el.list.map((label) => {
            label.active = false;
          });
        }
      });
      goodsLabelList.value = res.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function openModal(row, type) {
  productId.value = row.id;
  if (type === 'vipPriceSet') {
    vipPriceSetRef.value.visible = true;
  } else {
    brokerageSetRef.value.visible = true;
  }
}
function batchSub() {
  let data = batchFormData.value;
  data.ids = ids.value;
  data.type = batchType.value;
  let activeIds = [];
  dataLabel.value.forEach((item) => {
    activeIds.push(item.id);
  });
  data.label_id = activeIds;
  batchSetting(data)
    .then((res) => {
      ElMessage.success(res.msg);
      getDataList();
      clearBatchData(false);
      ids.value = [];
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
function clearBatchData(status) {
  if (!status) {
    batchFormData.value = {
      cate_id: [],
      give_integral: null,
      label_id: [],
      coupon_ids: [],
      label_list: [],
    };
    dataLabel.value = [];
  }
  batchModal.value = false;
  table.value.clearSelection();
}
function batchSelect(type) {
  if (!ids.value.length) {
    ElMessage.warning('请选择要修改的商品');
  } else if (type === 7) {
    onDismount();
  } else if (type === 8) {
    onShelves();
  } else if (type === 9) {
    batchType.value = type;
    tagShow.value = true;
  } else if (type === 11) {
    batchGoodsSetting('全部移到回收站', 1);
  } else if (type === 12) {
    batchGoodsSetting('恢复选中商品', 2);
  } else {
    batchType.value = type;
    batchModal.value = true;
  }
}
function batchGoodsSetting(tit, type) {
  let url = type == 1 ? 'product/product/batch_delete' : 'product/product/batch_recover';
  let delfromData = {
    title: tit,
    url,
    method: 'post',
    ids: {
      ids: ids.value,
    },
    un: 1,
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      getDataList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function activeData(dataLabelVal) {
  labelShow.value = false;
  dataLabel.value = dataLabelVal;
}
function nameId(id, names) {
  batchFormData.value.coupon_ids = id;
  couponName.value = unique(names);
}
function handleClose(name) {
  let index = couponName.value.indexOf(name);
  couponName.value.splice(index, 1);
  batchFormData.value.coupon_ids.splice(index, 1);
}
function unique(arr) {
  const res = new Map();
  return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
}
function labelClose() {
  labelShow.value = false;
  tagShow.value = false;
}
function activeGoodsLabel(data) {
  tagShow.value = false;
  batchFormData.value.label_list = Array.from(new Set(data));
  batchSub();
}
function openLabel() {
  labelShow.value = true;
}
function closeLabel(label) {
  let index = dataLabel.value.indexOf(dataLabel.value.filter((d) => d.id == label.id)[0]);
  dataLabel.value.splice(index, 1);
}
function addCoupon() {
  couponTemplates.value.isTemplate = true;
  couponTemplates.value.tableList();
}
function onShelves() {
  if (ids.value.length === 0) {
    ElMessage.warning('请选择要上架的商品');
  } else {
    let data = {
      ids: ids.value,
    };
    productShowApi(data)
      .then((res) => {
        ElMessage.success(res.msg);
        getDataList();
      })
      .catch((res) => {
        ElMessage.error(res.msg);
      });
  }
}
function onDismount() {
  if (ids.value.length === 0) {
    ElMessage.warning('请选择要下架的商品');
  } else {
    let data = {
      ids: ids.value,
    };
    productUnshowApi(data)
      .then((res) => {
        ElMessage.success(res.msg);
        artFrom.value.page = 1;
        getDataList();
      })
      .catch((res) => {
        ElMessage.error(res.msg);
      });
  }
}
function getRowKey(row) {
  return row.id;
}
function handleSelectRow(selection) {
  const uniqueArr = [];
  const idArr = [];
  for (let i = 0; i < selection.length; i++) {
    const item = selection[i];
    if (!idArr.includes(item.id)) {
      uniqueArr.push(item);
      idArr.push(item.id);
    }
  }
  ids.value = idArr;
  multipleSelection.value = uniqueArr;
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
function getDataList() {
  loading.value = true;
  artFrom.value.cate_id = artFrom.value.cate_id || '';
  cardkeyProductList(artFrom.value)
    .then((res) => {
      tableList.value = res.data.list || [];
      total.value = res.data.count || 0;
      nextTick(() => {
        showSelectData();
      });
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg || '获取列表失败');
    });
}
function showSelectData() {
  if (multipleSelection.value.length > 0) {
    tableList.value.forEach((row) => {
      multipleSelection.value.forEach((item) => {
        if (row.id === item.id) {
          table.value.toggleRowSelection(item, true);
        }
      });
    });
  }
}
function userSearchs() {
  artFrom.value.page = 1;
  getDataList();
}
function changeSwitch(row) {
  PostgoodsIsShow(row.id, row.is_show)
    .then((res) => {
      ElMessage.success(res.msg);
      getDataList();
    })
    .catch((res) => {
      row.is_show = !row.is_show ? 1 : 0;
      ElMessage.error(res.msg);
    });
}
function edit(row) {
  router.push({ path: routePre + '/product/cardkey/add/' + row.id });
}
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `product/product/${row.id}`,
    method: 'DELETE',
    ids: '',
    un: 1,
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tableList.value.splice(num, 1);
      getDataList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function fullDel(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `product/full_del/${row.id}`,
    method: 'DELETE',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tableList.value.splice(num, 1);
      getDataList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function reset() {
  artFrom.value = {
    page: 1,
    limit: 15,
    store_name: '',
    cate_id: '',
    vip_product: '',
    sales_s: ['', ''],
    stock_s: ['', ''],
    price_s: ['', ''],
    time: '',
    type: '1',
  };
  timeVal.value = [];
  getDataList();
}
</script>

<style scoped>
.mt16 {
  margin-top: 16px;
}
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;
  img {
    width: 100%;
    height: 100%;
  }
}
.labelInput {
  border: 1px solid #dcdee2;
  padding: 0 15px 0 10px;
  border-radius: 5px;
  min-height: 30px;
  cursor: pointer;
  font-size: 12px;
  .span {
    color: #c5c8ce;
  }
  .iconxiayi {
    margin-left: 5px;
    font-size: 12px;
  }
}
.el-dropdown-link {
  cursor: pointer;
  color: var(--prev-color-primary);
  font-size: 12px;
}
.el-icon-arrow-down {
  font-size: 12px;
}
.el-dropdown-menu__item {
  a {
    color: #606266;
  }
}
.label_width {
  width: 400px;
}
.search-form {
  display: flex;
  justify-content: space-between;
  .search-form-box {
    display: flex;
    flex-wrap: wrap;
    flex: 1;
  }
  .search-form-sub {
    display: flex;
    align-items: baseline;
  }
}
.batch-box {
  :deep(.ivu-modal-body) {
    overflow: auto;
    min-height: 350px;
  }
}
</style>
