<template>
  <div class="cardkey-stock">
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :inline="true" @submit.prevent="onSearch">
          <el-form-item label="商品名称">
            <el-input
              v-model="search.store_name"
              placeholder="请输入商品名称/关键字/ID"
              clearable
              @keyup.enter="onSearch"
              style="width: 220px"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="onSearch">查询</el-button>
            <el-button @click="onReset">重置</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>

    <el-card :bordered="false" shadow="never" class="ivu-mt mt16" :body-style="{ padding: '20px 20px 20px' }">
      <el-table :data="list" v-loading="loading" stripe empty-text="暂无商品数据" row-key="id">
        <el-table-column label="商品ID" width="80" prop="id" />
        <el-table-column label="商品图" width="90">
          <template #default="{ row }">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="商品名称" min-width="220">
          <template #default="{ row }">
            <span>{{ row.store_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="卡密类型" width="110">
          <template #default="{ row }">
            <span>{{ row.card_type === 'single' ? '单卡密' : '组合卡密' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="卡密库存" width="110">
          <template #default="{ row }">
            <el-tag :type="(row.card_count || 0) > 0 ? 'success' : 'danger'"> {{ row.card_count || 0 }} 张 </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="商品售价" width="100">
          <template #default="{ row }">{{ row.price }}</template>
        </el-table-column>
        <el-table-column label="操作" width="160" fixed="right">
          <template #default="{ row }">
            <a v-db-click @click="openCard(row)">卡密管理</a>
            <a v-db-click @click="openImport(row)">导入卡密</a>
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

    <!-- 卡密列表弹窗 -->
    <el-dialog v-model="cardDialog" title="卡密库存明细" width="720px" :close-on-click-modal="false">
      <el-table :data="cardList" v-loading="cardLoading" stripe empty-text="暂无卡密，请先导入">
        <el-table-column label="ID" width="70" prop="id" />
        <el-table-column label="卡号" min-width="160">
          <template #default="{ row }">{{ row.card_content || '--' }}</template>
        </el-table-column>
        <el-table-column label="卡密" min-width="160">
          <template #default="{ row }">{{ row.card_password || '--' }}</template>
        </el-table-column>
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getCardStatusType(row.status)">{{ getCardStatusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="关联订单" width="180">
          <template #default="{ row }">{{ row.order_no || '--' }}</template>
        </el-table-column>
        <el-table-column label="发放时间" width="160">
          <template #default="{ row }">{{ row.sent_time ? formatDate(row.sent_time) : '--' }}</template>
        </el-table-column>
      </el-table>
    </el-dialog>

    <!-- 导入卡密弹窗 -->
    <el-dialog
      v-model="importDialog"
      :title="'导入卡密 - ' + (currentProduct ? currentProduct.store_name : '')"
      width="640px"
      :close-on-click-modal="false"
    >
      <el-alert
        type="info"
        show-icon
        :closable="false"
        title="每行一张卡密，格式：卡号,卡密（英文逗号分隔）"
        style="margin-bottom: 16px"
      />
      <el-input
        type="textarea"
        v-model="importText"
        :rows="10"
        placeholder="例如：&#10;CARD001,ABC123456&#10;CARD002,DEF789012"
      />
      <template #footer
        ><span class="dialog-footer">
          <el-button @click="importDialog = false">取 消</el-button>
          <el-button type="primary" :loading="importLoading" @click="submitImport">确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { cardkeyProductList, cardkeyProductCardList, cardkeyProductImportCard } from '../cardkeyProduct';

defineOptions({ name: 'cardkeyStockList' });

const loading = ref(false);
const list = ref([]);
const total = ref(0);
const search = ref({ page: 1, limit: 15, store_name: '' });
const cardDialog = ref(false);
const cardLoading = ref(false);
const cardList = ref([]);
const currentProduct = ref(null);
const importDialog = ref(false);
const importLoading = ref(false);
const importText = ref('');

async function loadList() {
  loading.value = true;
  try {
    const params = { page: search.value.page, limit: search.value.limit };
    if (search.value.store_name) params.store_name = search.value.store_name;
    const res = await cardkeyProductList(params);
    const data = res.data || {};
    list.value = data.list || [];
    total.value = data.count || 0;
  } catch (e) {
    ElMessage.error(e.message || '获取商品列表失败');
  }
  loading.value = false;
}
function onSearch() {
  search.value.page = 1;
  loadList();
}
function onReset() {
  search.value = { page: 1, limit: 15, store_name: '' };
  loadList();
}
function openCard(row) {
  currentProduct.value = row;
  cardDialog.value = true;
  cardLoading.value = true;
  cardkeyProductCardList(row.id)
    .then((res) => {
      cardList.value = res.data || [];
    })
    .catch((e) => {
      ElMessage.error(e.message || '获取卡密列表失败');
    })
    .finally(() => {
      cardLoading.value = false;
    });
}
function openImport(row) {
  currentProduct.value = row;
  importText.value = '';
  importDialog.value = true;
}
async function submitImport() {
  const lines = importText.value
    .split('\n')
    .map((line) => line.trim())
    .filter((line) => line !== '');
  if (!lines.length) {
    ElMessage.warning('请先输入卡密内容');
    return;
  }
  const cards = lines.map((line) => {
    // 支持 "卡号,卡密" 或仅 "卡密" 两种格式
    const parts = line.split(/[,，\s]+/).filter((p) => p !== '');
    return { card_content: parts[0] || '', card_password: parts[1] || parts[0] || '' };
  });
  importLoading.value = true;
  try {
    const res = await cardkeyProductImportCard(currentProduct.value.id, cards);
    ElMessage.success(res.msg || `导入成功，共导入${cards.length}条卡密`);
    importDialog.value = false;
    loadList();
  } catch (e) {
    ElMessage.error(e.message || '导入失败');
  }
  importLoading.value = false;
}
function getCardStatusText(status) {
  const map = { unused: '可用', reserved: '待支付预留', sent: '已发放', used: '已使用', recycled: '已回收' };
  return map[status] || status || '-';
}
function getCardStatusType(status) {
  const map = { unused: 'info', reserved: 'warning', sent: 'success', used: 'info', recycled: 'danger' };
  return map[status] || '';
}
function formatDate(ts) {
  if (!ts) return '--';
  const d = new Date(ts * 1000);
  const y = d.getFullYear();
  const M = ('0' + (d.getMonth() + 1)).slice(-2);
  const day = ('0' + d.getDate()).slice(-2);
  const h = ('0' + d.getHours()).slice(-2);
  const m = ('0' + d.getMinutes()).slice(-2);
  return y + '-' + M + '-' + day + ' ' + h + ':' + m;
}

onMounted(() => {
  loadList();
});
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
}
.tabBox_img img {
  width: 100%;
  height: 100%;
}
</style>
