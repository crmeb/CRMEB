<template>
  <div class="virtual-list">
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :inline="true" @submit.prevent="onSearch">
          <el-form-item label="商品名称">
            <el-input
              v-model="search.store_name"
              placeholder="商品名称/ID"
              clearable
              @keyup.enter="onSearch"
              style="width: 220px"
            />
          </el-form-item>
          <el-form-item label="分类">
            <el-cascader
              v-model="search.cate_id"
              :options="categoryTree"
              :props="{ multiple: false, emitPath: false, checkStrictly: true }"
              clearable
              placeholder="全部"
              style="width: 180px"
            />
          </el-form-item>
          <template v-if="collapse">
            <el-form-item label="售价">
              <el-input v-model="search.price_min" placeholder="最低价" style="width: 120px" />
              <span class="ml5 mr5">~</span>
              <el-input v-model="search.price_max" placeholder="最高价" style="width: 120px" />
            </el-form-item>
            <el-form-item label="库存">
              <el-input v-model="search.stock_min" placeholder="最小值" style="width: 120px" />
              <span class="ml5 mr5">~</span>
              <el-input v-model="search.stock_max" placeholder="最大值" style="width: 120px" />
            </el-form-item>
            <el-form-item label="添加时间">
              <date-range-picker
                v-model="timeRange"
                format="YYYY-MM-DD"
                value-format="YYYY-MM-DD"
                range-separator="~"
                @change="onTimeChange"
              />
            </el-form-item>
          </template>
          <el-form-item>
            <el-button type="primary" @click="onSearch">查询</el-button>
            <el-button @click="onReset">重置</el-button>
            <a class="ml10 font12" v-db-click @click="collapse = !collapse">
              <template v-if="!collapse">展开<i class="el-icon-arrow-down" /></template>
              <template v-else>收起<i class="el-icon-arrow-up" /></template>
            </a>
          </el-form-item>
        </el-form>
      </div>
    </el-card>

    <el-card :bordered="false" shadow="never" class="ivu-mt mt16" :body-style="{ padding: '0 20px 20px' }">
      <el-tabs v-model="search.type" @tab-change="onTabClick">
        <el-tab-pane label="全部" name="all" />
        <el-tab-pane label="上架中" name="1" />
        <el-tab-pane label="已下架" name="0" />
        <el-tab-pane label="回收站" name="-1" />
      </el-tabs>

      <div class="mb12">
        <el-button type="primary" @click="goAdd">添加网盘商品</el-button>
        <el-button :disabled="!selectedIds.length" @click="onBatchDelete">批量删除</el-button>
      </div>

      <el-table
        :data="list"
        v-loading="loading"
        stripe
        empty-text="暂无数据"
        @selection-change="onSelectionChange"
        row-key="id"
      >
        <el-table-column type="selection" width="50" :reserve-selection="true" />
        <el-table-column label="ID" width="70" prop="id" />
        <el-table-column label="商品图" width="80">
          <template #default="{ row }">
            <div class="tabBox_img" v-viewer v-if="row.image">
              <img v-lazy="row.image" />
            </div>
            <span v-else>--</span>
          </template>
        </el-table-column>
        <el-table-column label="商品名称" min-width="200" prop="store_name" show-overflow-tooltip />
        <el-table-column label="售价" width="100" prop="price" />
        <el-table-column label="库存" width="80" prop="stock" />
        <el-table-column label="销量" width="80" prop="sales" />
        <el-table-column label="排序" width="70" prop="sort" />
        <el-table-column label="状态" width="110">
          <template #default="{ row }">
            <el-switch
              :model-value="row.is_show"
              :active-value="1"
              :inactive-value="0"
              @change="(val) => onToggleStatus(row, val)"
              active-text="上架"
              inactive-text="下架"
              class="defineSwitch"
            />
          </template>
        </el-table-column>
        <el-table-column label="添加时间" width="160">
          <template #default="{ row }">{{ dateFormat(row.add_time) }}</template>
        </el-table-column>
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <a v-db-click @click="goEdit(row.id)">编辑</a>
            <el-divider direction="vertical" />
            <el-dropdown @command="(cmd) => onMoreAction(cmd, row)">
              <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right" /></span>
              <template #dropdown
                ><el-dropdown-menu>
                  <el-dropdown-item command="delete" v-if="search.type !== '-1'">移到回收站</el-dropdown-item>
                  <el-dropdown-item command="restore" v-if="search.type === '-1'">恢复商品</el-dropdown-item>
                  <el-dropdown-item command="forceDelete" v-if="search.type === '-1'" divided
                    >彻底删除</el-dropdown-item
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
          v-model:page="search.page"
          v-model:limit="search.limit"
          @pagination="loadList"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage, ElMessageBox } from 'element-plus';
import { virtualApi } from '../api';
import { cascaderListApi } from '@/api/product';

defineOptions({ name: 'virtualProductList' });

const router = useRouter();

const loading = ref(false);
const list = ref([]);
const total = ref(0);
const categoryTree = ref([]);
const search = ref({
  page: 1,
  limit: 15,
  store_name: '',
  status: '',
  type: 'all',
  cate_id: '',
  price_min: '',
  price_max: '',
  stock_min: '',
  stock_max: '',
  time: '',
});
const selectedIds = ref([]);
const timeRange = ref([]);
const collapse = ref(false);

// 原 filters.dateFormat
function dateFormat(ts) {
  if (!ts) return '';
  const d = new Date(ts * 1000);
  const y = d.getFullYear();
  const M = ('0' + (d.getMonth() + 1)).slice(-2);
  const day = ('0' + d.getDate()).slice(-2);
  const h = ('0' + d.getHours()).slice(-2);
  const m = ('0' + d.getMinutes()).slice(-2);
  return y + '-' + M + '-' + day + ' ' + h + ':' + m;
}

async function loadCategory() {
  try {
    const res = await cascaderListApi(1);
    categoryTree.value = res.data || [];
  } catch (e) {
    categoryTree.value = [];
  }
}
function buildParams() {
  const params = new URLSearchParams();
  params.set('page', search.value.page);
  params.set('limit', search.value.limit);
  if (search.value.store_name) params.set('store_name', search.value.store_name);
  if (search.value.cate_id) params.set('cate_id', search.value.cate_id);
  if (search.value.price_min) params.set('price_min', search.value.price_min);
  if (search.value.price_max) params.set('price_max', search.value.price_max);
  if (search.value.stock_min) params.set('stock_min', search.value.stock_min);
  if (search.value.stock_max) params.set('stock_max', search.value.stock_max);
  if (search.value.time) params.set('time', search.value.time);
  // type映射: all->空, 1->上架, 0->下架, -1->回收站
  if (search.value.type === 'all') {
    // 不传status
  } else if (search.value.type === '-1') {
    params.set('is_del', '1');
  } else {
    params.set('status', search.value.type);
  }
  return params;
}
async function loadList() {
  loading.value = true;
  try {
    const res = await virtualApi('/product/list?' + buildParams().toString());
    list.value = res.data.list || [];
    total.value = res.data.total || 0;
  } catch (e) {
    ElMessage.error(e.message);
  }
  loading.value = false;
}
function onSearch() {
  search.value.page = 1;
  loadList();
}
function onReset() {
  search.value = {
    page: 1,
    limit: 15,
    store_name: '',
    status: '',
    type: 'all',
    cate_id: '',
    price_min: '',
    price_max: '',
    stock_min: '',
    stock_max: '',
    time: '',
  };
  timeRange.value = [];
  selectedIds.value = [];
  loadList();
}
function onTabClick() {
  search.value.page = 1;
  selectedIds.value = [];
  loadList();
}
function onTimeChange(val) {
  search.value.time = val ? val.join(',') : '';
}
function onSelectionChange(val) {
  selectedIds.value = val.map((r) => r.id);
}
function goAdd() {
  router.push({ path: '/admin/product/virtual/add' });
}
function goEdit(id) {
  router.push({ path: '/admin/product/virtual/add/' + id });
}
async function onToggleStatus(row, val) {
  try {
    await virtualApi('/product/status/' + row.id + '/' + val, { method: 'PUT' });
    ElMessage.success(val ? '上架成功' : '下架成功');
    row.is_show = val;
  } catch (e) {
    ElMessage.error(e.message);
  }
}
function onDelete(row) {
  ElMessageBox.confirm('确定将商品移入回收站吗？', '提示', { type: 'warning' })
    .then(async () => {
      await virtualApi('/product/' + row.id, { method: 'DELETE' });
      ElMessage.success('已移入回收站');
      loadList();
    })
    .catch(() => {});
}
function onBatchDelete() {
  if (!selectedIds.value.length) return;
  ElMessageBox.confirm('确定将选中的 ' + selectedIds.value.length + ' 个商品移入回收站吗？', '提示', {
    type: 'warning',
  })
    .then(async () => {
      for (const id of selectedIds.value) {
        await virtualApi('/product/' + id, { method: 'DELETE' });
      }
      ElMessage.success('批量删除成功');
      selectedIds.value = [];
      loadList();
    })
    .catch(() => {});
}
function onMoreAction(cmd, row) {
  if (cmd === 'delete') onDelete(row);
  else if (cmd === 'restore') onRestore(row);
  else if (cmd === 'forceDelete') onForceDelete(row);
}
async function onRestore(row) {
  try {
    await virtualApi('/product/restore/' + row.id, { method: 'PUT' });
    ElMessage.success('恢复成功');
    loadList();
  } catch (e) {
    ElMessage.error(e.message);
  }
}
async function onForceDelete(row) {
  ElMessageBox.confirm('确定彻底删除该商品吗？删除后不可恢复！', '警告', {
    type: 'warning',
    confirmButtonText: '确定删除',
  })
    .then(async () => {
      await virtualApi('/product/force/' + row.id, { method: 'DELETE' });
      ElMessage.success('已彻底删除');
      loadList();
    })
    .catch(() => {});
}

// created
loadCategory();
loadList();
</script>

<style scoped>
.mt16 {
  margin-top: 16px;
}
.mb12 {
  margin-bottom: 12px;
}
.ml5 {
  margin-left: 5px;
}
.mr5 {
  margin-right: 5px;
}
.ml10 {
  margin-left: 10px;
}
.font12 {
  font-size: 12px;
  color: #409eff;
  cursor: pointer;
}
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}
.el-dropdown-link {
  cursor: pointer;
  color: var(--prev-color-primary);
  font-size: 12px;
}
</style>
