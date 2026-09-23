<template>
  <div class="center-product-manager">
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="artFrom" label-width="80px" label-position="right" inline @submit.prevent>
          <div class="acea-row search-form">
            <div class="search-form-box">
              <el-form-item label="商品搜索：" label-for="keyword">
                <el-input
                  clearable
                  placeholder="请输入商品名称/关键字"
                  v-model="artFrom.keyword"
                  class="form_content_width"
                />
              </el-form-item>
              <el-form-item label="产品类型：" label-for="product_type">
                <el-select v-model="artFrom.product_type" clearable placeholder="全部" class="form_content_width">
                  <el-option label="全部" value="" />
                  <el-option v-for="item in typeList" :key="item.key" :label="item.name" :value="item.key" />
                </el-select>
              </el-form-item>
              <el-form-item label="上架状态：">
                <el-select v-model="artFrom.is_show" clearable placeholder="全部" class="form_content_width">
                  <el-option label="全部" value="" />
                  <el-option label="上架" value="1" />
                  <el-option label="下架" value="0" />
                </el-select>
              </el-form-item>
              <el-form-item label="数据状态：">
                <el-select v-model="artFrom.is_del" clearable placeholder="全部" class="form_content_width">
                  <el-option label="全部" value="" />
                  <el-option label="正常" value="0" />
                  <el-option label="回收站" value="1" />
                </el-select>
              </el-form-item>
            </div>
            <div class="search-form-sub">
              <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
              <el-button class="ResetSearch" v-db-click @click="reset">重置</el-button>
            </div>
          </div>
        </el-form>
      </div>
    </el-card>

    <el-card :bordered="false" shadow="never" class="ivu-mt mt16" :body-style="{ padding: '0 20px 20px' }">
      <el-table
        ref="table"
        :data="tableList"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        :row-key="getRowKey"
        empty-text="暂无数据"
      >
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
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="产品类型" min-width="100">
          <template #default="scope">
            <el-tag type="primary">{{ getTypeName(scope.row.product_type) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="售价" min-width="100">
          <template #default="scope">
            <span class="price">¥{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="原价" min-width="100">
          <template #default="scope">
            <span class="original-price">¥{{ scope.row.original_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="销量" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.sales || 0 }}</span>
          </template>
        </el-table-column>
        <el-table-column label="库存" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.stock || 0 }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="100">
          <template #default="scope">
            <el-tag :type="scope.row.is_show ? 'success' : 'info'">
              {{ scope.row.is_show ? '上架' : '下架' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" minWidth="100">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
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
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { centerProductList, centerProductTypeList } from '@/api/centerProduct';
import Setting from '@/setting';

defineOptions({ name: 'centerProductList' });

const router = useRouter();

const artFrom = reactive({
  page: 1,
  limit: 15,
  keyword: '',
  product_type: '',
  is_show: '',
  is_del: '',
});
const tableList = ref([]);
const total = ref(0);
const loading = ref(false);
const typeList = ref([]);

async function getTypeList() {
  try {
    const res = await centerProductTypeList();
    typeList.value = res.data || [];
  } catch (e) {
    ElMessage.error('获取产品类型失败');
  }
}
function getTypeName(typeKey) {
  const item = typeList.value.find((t) => t.key === typeKey);
  return item ? item.name : typeKey;
}
function getRowKey(row) {
  return row.id;
}
function getDataList() {
  loading.value = true;
  centerProductList(artFrom)
    .then((res) => {
      tableList.value = res.data.list || [];
      total.value = res.data.count || 0;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg || '获取列表失败');
    });
}
function userSearchs() {
  artFrom.page = 1;
  getDataList();
}
function reset() {
  Object.assign(artFrom, {
    page: 1,
    limit: 15,
    keyword: '',
    product_type: '',
    is_show: '',
    is_del: '',
  });
  getDataList();
}
function edit(row) {
  const editPath = Setting.routePre + '/product/' + row.product_type + '/add/' + row.id;
  router.push({ path: editPath });
}

// created
getTypeList();
getDataList();
</script>

<style scoped>
.search-form {
  display: flex;
  justify-content: space-between;
}
.search-form-box {
  display: flex;
  flex-wrap: wrap;
  flex: 1;
}
.search-form-sub {
  display: flex;
  align-items: baseline;
}
.mt16 {
  margin-top: 16px;
}
.price {
  color: #ff4757;
  font-weight: bold;
}
.original-price {
  color: #999;
  text-decoration: line-through;
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
</style>
