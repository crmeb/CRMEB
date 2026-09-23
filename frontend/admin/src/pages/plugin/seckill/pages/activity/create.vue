<template>
  <div v-loading="spinShow">
    <pages-header
      ref="pageHeader"
      :title="$route.params.id ? '编辑秒杀活动' : '添加秒杀活动'"
      :backUrl="$routeProStr + '/marketing/seckill/activity'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-row class="mt30 acea-row row-middle row-center">
        <el-col :span="20">
          <steps :stepList="stepList" :isActive="current" @stepActive="stepActive"></steps>
        </el-col>
        <el-col :span="23">
          <el-form
            class="form mt30"
            ref="formValidateRef"
            :model="formValidate"
            :label-width="labelWidth"
            :label-position="labelPosition"
            @submit.prevent
          >
            <el-col v-show="current === 0">
              <el-col :span="24">
                <el-col v-bind="grid">
                  <el-form-item label="活动标题：" label-for="title">
                    <el-input
                      clearable
                      placeholder="请输入活动标题"
                      v-model="formValidate.title"
                      class="content_width"
                      maxlength="80"
                      show-word-limit
                    />
                  </el-form-item>
                </el-col>
              </el-col>
              <el-col :span="24">
                <el-form-item label="活动时间：">
                  <div>
                    <el-date-picker
                      clearable
                      :editable="false"
                      type="daterange"
                      format="YYYY-MM-DD"
                      value-format="YYYY-MM-DD"
                      range-separator="-"
                      start-placeholder="开始日期"
                      end-placeholder="结束日期"
                      @change="onchangeTime"
                      class="content_width"
                      v-model="formValidate.section_time"
                    >
                    </el-date-picker>
                    <div class="grey">设置活动开启结束时间，用户可以在有效时间内参与秒杀</div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="开始时间：">
                  <div>
                    <el-select v-model="formValidate.time_ids" multiple class="content_width">
                      <el-option v-for="item in timeList" :value="item.id" :key="item.id" :label="item.time_name">
                      </el-option>
                    </el-select>
                    <div class="grey">
                      选择产品开始时间段，该时间段内用户可参与购买；其它时间段会显示活动未开始或已结束。如活动超过一天，则活动期内，每天都会定时开启
                    </div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="总购买数量限制：">
                  <div>
                    <el-input-number
                      :controls="false"
                      :min="1"
                      placeholder="请输入数量限制"
                      element-id="num"
                      :precision="0"
                      :max="10000"
                      v-model="formValidate.num"
                      class="content_width"
                    />
                    <div class="grey">
                      活动有效期内每个用户可购买该商品总数限制。例如设置为4，表示本次活动有效期内，每个用户最多可购买总数4个
                    </div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="单次购买数量限制：">
                  <div>
                    <el-input-number
                      :controls="false"
                      :min="1"
                      placeholder="请输入单次购买数量限制"
                      element-id="once_num"
                      :precision="0"
                      :max="10000"
                      v-model="formValidate.once_num"
                      class="content_width"
                    />
                    <div class="grey">
                      用户参与秒杀时，一次购买最大数量限制。例如设置为2，表示参与秒杀时，用户一次购买数量最大可选择2个
                    </div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="秒杀是否参与分销：" label-for="is_commission">
                  <div>
                    <el-switch
                      class="defineSwitch"
                      :active-value="1"
                      :inactive-value="0"
                      v-model="formValidate.is_commission"
                      size="large"
                      active-text="开启"
                      inactive-text="关闭"
                    >
                    </el-switch>
                    <div class="grey">商品是否参与商城分销返佣</div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="活动状态：" label-for="status">
                  <el-switch
                    class="defineSwitch"
                    :active-value="1"
                    :inactive-value="0"
                    v-model="formValidate.status"
                    size="large"
                    active-text="开启"
                    inactive-text="关闭"
                  >
                  </el-switch>
                </el-form-item>
              </el-col>
            </el-col>
            <el-row v-show="current === 1">
              <el-col :span="24">
                <div class="acea-row row-between-wrapper">
                  <div>
                    <el-button type="primary" @click="addGoods">添加商品</el-button>
                    <el-button @click="batchSet" class="ml20" :disabled="!isAllChecked && !checkPidList.length">
                      批量设置
                    </el-button>
                    <el-button @click="delAll" class="ml20" :disabled="!isAllChecked && !checkPidList.length">
                      批量删除
                    </el-button>
                  </div>
                  <div class="goodsWord">
                    <el-form-item label="商品搜索：">
                      <el-input
                        class="w_input240"
                        v-model="keyword"
                        placeholder="请输入商品关键词"
                        @input="searchWord"
                      ></el-input>
                    </el-form-item>
                  </div>
                </div>
              </el-col>
              <el-col :span="24">
                <div class="vxeTable">
                  <vxe-table
                    border="inner"
                    ref="xTree"
                    :column-config="{ resizable: true }"
                    :tree-config="{ childrenField: 'attrs', reserve: true }"
                    @checkbox-all="checkboxAll"
                    @checkbox-change="checkboxItem"
                    :data="searchTableData.length || keyword ? searchTableData : tableData"
                    :row-config="{ keyField: 'id' }"
                  >
                    <vxe-column type="checkbox" title="多选" width="100" tree-node></vxe-column>
                    <vxe-column field="info" title="商品信息" min-width="300">
                      <template v-slot="{ row }">
                        <div class="flex imgPic row-middle">
                          <viewer>
                            <div class="pictrue"><img v-lazy="row.parent == 1 ? row.image : row.pic" /></div>
                          </viewer>
                          <div class="info">
                            <el-tooltip max-width="200" placement="bottom" transfer>
                              <span class="line2">{{ row.store_name }}{{ row.suk }}</span>
                              <template #content
                                ><p>{{ row.store_name }}{{ row.suk }}</p></template
                              >
                            </el-tooltip>
                          </div>
                        </div>
                      </template>
                    </vxe-column>
                    <vxe-column field="cost" title="成本价" min-width="80"></vxe-column>
                    <vxe-column field="product_price" title="售价" min-width="80"></vxe-column>
                    <vxe-column field="price" title="秒杀价" min-width="150">
                      <template v-slot="{ row }">
                        <div v-if="row.parent == 1">——</div>
                        <vxe-input
                          v-else
                          v-model="row.price"
                          min="0"
                          placeholder="请输入秒杀价"
                          type="float"
                          digits="2"
                          step="1"
                        ></vxe-input>
                      </template>
                    </vxe-column>
                    <vxe-column field="quota" title="限量" min-width="150">
                      <template v-slot="{ row }">
                        <vxe-input
                          v-model="row.quota"
                          min="0"
                          placeholder="请输入限量"
                          type="integer"
                          @input="onParentQuotaChange(row)"
                        ></vxe-input>
                      </template>
                    </vxe-column>
                    <vxe-column field="stock" title="库存" min-width="90"></vxe-column>
                    <vxe-column field="status" title="是否开启" min-width="100">
                      <template v-slot="{ row }">
                        <el-switch v-model="row.status" :active-value="1" :inactive-value="0" size="large">
                          <template #open><span>上架</span></template>
                          <template #close><span>下架</span></template>
                        </el-switch>
                      </template>
                    </vxe-column>
                    <vxe-column field="date" title="操作" min-width="100" fixed="right" align="center">
                      <template v-slot="{ row }">
                        <a @click="del(row, $event)" v-if="row.parent == 1">删除</a>
                      </template>
                    </vxe-column>
                  </vxe-table>
                </div>
              </el-col>
            </el-row>
            <el-col class="mt20" :span="24">
              <el-form-item>
                <el-button class="submission" v-db-click @click="step" :disabled="current === 0">上一步</el-button>
                <el-button
                  :disabled="submitOpen && current === 1"
                  type="primary"
                  class="submission"
                  v-db-click
                  @click="next('formValidate')"
                >
                  {{ current === 1 ? '提交' : '下一步' }}
                </el-button>
              </el-form-item>
            </el-col>
          </el-form>
        </el-col>
      </el-row>
    </el-card>
    <!-- 选择商品-->
    <el-dialog v-model="modals" title="商品列表" class="paymentFooter" width="1000px">
      <goods-list ref="goodslist" :ischeckbox="true" isdiy :goodsType="1" @getProductId="getProductId"></goods-list>
    </el-dialog>
    <!-- 运费模板-->
    <freight-template ref="template" @addSuccess="productGetTemplate"></freight-template>
    <el-dialog v-model="modalsSet" title="批量设置" @close="batchVisibleChange">
      <el-form :model="formBatch" :label-width="labelWidth" :label-position="labelPosition" @submit.prevent>
        <el-form-item label="秒杀价：" prop="price">
          <el-input
            class="w_input315"
            v-model="formBatch.price"
            min="0"
            placeholder="请输入秒杀价"
            type="float"
            digits="2"
            step="1"
          ></el-input>
        </el-form-item>
        <el-form-item label="限量：" prop="quota">
          <el-input
            class="w_input315"
            v-model="formBatch.quota"
            min="0"
            placeholder="请输入限量"
            type="integer"
          ></el-input>
        </el-form-item>
      </el-form>
      <template #footer
        ><div>
          <el-button @click="modalsSet = false">取消</el-button>
          <el-button type="primary" @click="okBatch">保存</el-button>
        </div></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import goodsList from '@/components/goodsList/index';
import { seckillActivityInfoApi, seckillActivityAddApi, seckillTimeListApi } from '../../api';
import { productGetTemplateApi } from '@/api/product';
import freightTemplate from '@/components/freightTemplate/index';
import steps from '@/components/steps/index';
import setting from '@/setting';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'seckillActivityCreate' });

const route = useRoute();
const router = useRouter();
const routePre = setting.routePre;
const mediaStore = useMediaStore();

const pageHeader = ref(null);
const formValidateRef = ref(null);
const xTree = ref(null);
const goodslist = ref(null);
const template = ref(null);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '135px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const stepList = ref(['填写基础信息', '选择秒杀商品']);
const submitOpen = ref(false);
const spinShow = ref(false);
const current = ref(0);
const modals = ref(false);
const modalsSet = ref(false);
const grid = { xl: 12, lg: 20, md: 24, sm: 24, xs: 24 };
const formValidate = ref({
  title: '',
  section_time: [],
  time_ids: [],
  num: 0,
  once_num: 0,
  status: 1,
  is_commission: 0,
  product_infos: [],
});
const formBatch = ref({ price: '', quota: '' });
const templateList = ref([]);
const timeList = ref([]);
const copy = ref(0);
const isAllChecked = ref(false);
const checkPidList = ref([]);
const searchTableData = ref([]);
const tableData = ref([]);
const keyword = ref('');

onMounted(() => {
  if (route.params.id) {
    current.value = 0;
    getInfo();
  }
  productGetTemplate();
  seckillTimeList();
});

function stepActive(index) {
  current.value = index;
}
function addGoods() {
  modals.value = true;
}
function batchSet() {
  modalsSet.value = true;
}
function del(row) {
  if (searchTableData.value.length) {
    searchTableData.value.forEach((i, index) => {
      if (row.id == i.id) searchTableData.value.splice(index, 1);
    });
    tableData.value.forEach((i, index) => {
      if (row.id == i.id) return tableData.value.splice(index, 1);
    });
  } else {
    tableData.value.forEach((i, index) => {
      if (row.id == i.id) return tableData.value.splice(index, 1);
    });
  }
  let idx = checkPidList.value.indexOf(row.id);
  if (idx !== -1) checkPidList.value.splice(idx, 1);
}
function delAll() {
  if (isAllChecked.value) {
    tableData.value = [];
  } else {
    tableData.value = tableData.value.filter((item) => !checkPidList.value.some((ele) => ele === item.id));
  }
  checkPidList.value = [];
  isAllChecked.value = false;
}
function batchVisibleChange() {
  formBatch.value.price = '';
  formBatch.value.quota = '';
}
function searchWord() {
  let list = [];
  tableData.value.forEach((item) => {
    if (item.store_name.indexOf(keyword.value) != -1) list.push(item);
  });
  searchTableData.value = keyword.value ? list : [];
}
function checkboxAll() {
  isAllChecked.value = xTree.value.isAllCheckboxChecked();
  if (!isAllChecked.value) checkPidList.value = [];
}
function checkboxItem(e) {
  let id = parseInt(e.rowid);
  if (e.row.product_id) {
    let pIndex = checkPidList.value.indexOf(e.row.product_id);
    if (pIndex !== -1 && !e.checked)
      checkPidList.value = checkPidList.value.filter((item) => item !== e.row.product_id);
    if (pIndex === -1 && e.checked) checkPidList.value.push(e.row.product_id);
  } else {
    let pIndex = checkPidList.value.indexOf(id);
    if (pIndex !== -1 && !e.checked) checkPidList.value = checkPidList.value.filter((item) => item !== id);
    if (pIndex === -1 && e.checked) checkPidList.value.push(id);
  }
  isAllChecked.value = xTree.value.isAllCheckboxChecked();
}
// 父级限量输入变化时，按库存比例分配到子规格
function onParentQuotaChange(row) {
  if (row.parent === 1 && row.quota > 0 && row.attrs && row.attrs.length) {
    const totalStock = row.attrs.reduce((sum, attr) => sum + (Number(attr.stock) || 0), 0);
    let assigned = 0;
    row.attrs.forEach((attr, index) => {
      const stock = Number(attr.stock) || 1;
      let quota;
      if (index === row.attrs.length - 1) {
        quota = row.quota - assigned;
      } else {
        quota = Math.floor((row.quota * stock) / totalStock);
        assigned += quota;
      }
      attr.quota = Math.max(quota, 1);
    });
  }
}
function freight() {
  template.value.id = 0;
  template.value.isTemplate = true;
}
function seckillTimeList() {
  seckillTimeListApi()
    .then((res) => {
      timeList.value = res.data.list.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function productGetTemplate() {
  productGetTemplateApi().then((res) => {
    templateList.value = res.data;
  });
}
function getProductId(data) {
  modals.value = false;
  let listChecked = JSON.parse(JSON.stringify(data));
  listChecked.forEach((item) => {
    item.parent = 1;
    item.status = 1;
    item.isAllChecked = true;
    item.attrs.forEach((value) => {
      value.cate_name = item.cate_name;
      value.store_label = item.store_label;
      value.status = 1;
      if (!value.quota) {
        value.quota = Number(value.stock) || 1;
      }
    });
    if (!item.quota) {
      item.quota = item.attrs.reduce((sum, attr) => sum + (Number(attr.quota) || 0), 0);
    }
  });
  tableData.value = unique(tableData.value.concat(listChecked));
}
function unique(arr) {
  const res = new Map();
  return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
}
function okBatch() {
  if (formBatch.value.price == '' && formBatch.value.quota == '') return ElMessage.error('请输入秒杀价或限量');
  const updateParentQuota = (item) => {
    if (item.attrs && item.attrs.length) {
      item.quota = item.attrs.reduce((sum, attr) => sum + (Number(attr.quota) || 0), 0);
    }
  };
  let target = isAllChecked.value
    ? tableData.value
    : tableData.value.filter((item) => checkPidList.value.includes(item.id));
  target.forEach((item) => {
    item.attrs.forEach((j) => {
      if (formBatch.value.price != '') j.price = formBatch.value.price;
      if (formBatch.value.quota != '' && j.status) j.quota = formBatch.value.quota;
    });
    updateParentQuota(item);
  });
  modalsSet.value = false;
}
function onchangeTime(e) {
  formValidate.value.section_time = e;
}
function getInfo() {
  spinShow.value = true;
  seckillActivityInfoApi(route.params.id)
    .then(async (res) => {
      formValidate.value = res.data;
      tableData.value = res.data.product_infos;
      tableData.value.forEach((item) => {
        item.parent = 1;
        item.isAllChecked = true;
        item.attrs.forEach((value) => {
          value.cate_name = item.cate_name;
          value.store_label = item.store_label;
        });
        if (item.attrs && item.attrs.length) {
          item.quota = item.attrs.reduce((sum, attr) => sum + (Number(attr.quota) || 0), 0);
        }
      });
      spinShow.value = false;
    })
    .catch((res) => {
      spinShow.value = false;
      ElMessage.error(res.msg);
    });
}
function next(name) {
  if (current.value === 1) {
    formValidate.value.id = Number(route.params.id) || 0;
    submitOpen.value = true;
    let product_infos = [];
    tableData.value.forEach((item) => {
      product_infos.push({ id: item.id, status: item.status, attrs: item.attrs });
      formValidate.value.product_infos = product_infos;
    });
    seckillActivityAddApi(formValidate.value)
      .then(async (res) => {
        submitOpen.value = false;
        ElMessage.success(res.msg);
        setTimeout(() => {
          router.push({ path: routePre + '/marketing/seckill/activity' });
        }, 500);
      })
      .catch((res) => {
        submitOpen.value = false;
        ElMessage.error(res.msg);
      });
  } else {
    if (current.value === 0 && !formValidate.value.section_time.length) {
      return ElMessage.warning('请选择活动时间');
    }
    if (current.value === 0 && !formValidate.value.time_ids.length) {
      return ElMessage.warning('请选择秒杀时间段');
    }
    current.value += 1;
  }
}
function step() {
  current.value--;
}
function cancel() {
  modals.value = false;
}
</script>

<style lang="scss" scoped>
.content_width {
  width: 460px;
}
.grey {
  color: #999;
  font-size: 12px;
}
.pictrue {
  width: 60px;
  height: 60px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  margin-right: 15px;
  display: inline-block;
  position: relative;
  cursor: pointer;
  img {
    width: 100%;
    height: 100%;
  }
}
.imgPic {
  display: flex;
  align-items: center;
}
</style>
