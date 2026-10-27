<template>
  <div>
    <pages-header
      ref="pageHeader"
      :title="$route.meta.title"
      :backUrl="$routeProStr + '/marketing/live/goods'"
      description="从商品库选择商品生成直播商品，提交微信审核通过后可在直播间上架。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-form
        ref="formRef"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        class="tabform"
        @submit.prevent
      >
        <el-row :gutter="24">
          <el-col :span="24">
            <el-form-item label="选择商品：">
              <div class="box">
                <div class="box-item" v-for="(item, index) in goods" :key="index">
                  <img :src="item.image" alt="" />
                  <i class="el-icon-error" v-db-click @click="bindDelete(index, item)" style="font-size: 16px"></i>
                </div>
                <div class="upload-box acea-row row-center-wrapper" v-db-click @click="selectGoods">
                  <i class="el-icon-goods" style="font-size: 24px"></i>
                </div>
              </div>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div class="active-btn" v-if="goods.length > 0">
        <el-button type="primary" v-db-click @click="liveGoods">生成直播商品</el-button>
      </div>
      <div class="table-box" v-if="isShowBox">
        <el-table
          :data="tabList"
          ref="table"
          class="mt14"
          v-loading="loading"
          no-userFrom-text="暂无数据"
          no-filtered-userFrom-text="暂无筛选结果"
        >
          <el-table-column label="商品ID" width="80">
            <template #default="scope">
              <span>{{ scope.row.id }}</span>
            </template>
          </el-table-column>
          <el-table-column label="商品信息" min-width="90">
            <template #default="scope">
              <div class="product_box">
                <img :src="scope.row.image" alt="" />
                <span>{{ scope.row.store_name }}</span>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="直播售价" min-width="130">
            <template #default="scope">
              <span>{{ scope.row.price }}</span>
            </template>
          </el-table-column>
          <el-table-column label="库存" min-width="130">
            <template #default="scope">
              <span>{{ scope.row.stock }}</span>
            </template>
          </el-table-column>
          <el-table-column label="操作" fixed="right" width="80">
            <template #default="scope">
              <a v-db-click @click="del(scope.row, scope.$index)">删除</a>
            </template>
          </el-table-column>
        </el-table>
        <div class="sub_btn">
          <el-button
            type="primary"
            style="width: 8%"
            v-db-click
            @click="bindSub"
            :disabled="disabled"
            :loading="loadings"
            >提交</el-button
          >
        </div>
      </div>
    </el-card>
    <el-dialog v-model="modals" title="商品列表" class="paymentFooter" width="1000px">
      <goods-list ref="goodslist" :selectIds="selectIds" @getProductId="getProductId" :ischeckbox="true"></goods-list>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { ElMessage } from 'element-plus';
import { useMediaStore } from '@/store/module/media';
import goodsList from '@/components/goodsList';
import { liveGoodsCreatApi, liveGoodsAddApi } from '../../../api';
import Setting from '@/setting';

defineOptions({ name: 'LiveGoodsAdd' });

const router = useRouter();

const { isMobile } = storeToRefs(useMediaStore());
const labelWidth = computed(() => (isMobile.value ? undefined : '100px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const isShowBox = ref(false);
const loading = ref(false);
const modals = ref(false);
const goods = ref([]);
const tempGoods = ref({});
const formValidate = ref({});
const tabList = ref([]);
const disabled = ref(false);
const loadings = ref(false);
const selectIds = ref([]);

const goodslist = ref(null);
const table = ref(null);

function selectGoods() {
  modals.value = true;
}
/** 生成直播商品 */
function liveGoods() {
  let array = [];
  goods.value.map((el) => {
    array.push(el.product_id);
  });
  liveGoodsCreatApi({ product_id: array })
    .then((res) => {
      tabList.value = res.data;
      isShowBox.value = true;
    })
    .catch((error) => {
      ElMessage.error(error.msg);
    });
}
/** 对象数组去重 */
function unique(arr) {
  const res = new Map();
  return arr.filter((arr) => !res.has(arr.product_id) && res.set(arr.product_id, 1));
}
function getProductId(productList) {
  modals.value = false;
  goods.value = unique(goods.value.concat(productList));
}
function bindDelete(index, item) {
  goods.value.splice(index, 1);
  let i = tabList.value.findIndex((e) => e.id == item.product_id);
  tabList.value.splice(i, 1);
  if (!goods.value.length) {
    isShowBox.value = false;
  }
}
function del(row, index) {
  tabList.value.splice(index, 1);
  let i = goods.value.findIndex((e) => e.product_id == row.id);
  goods.value.splice(i, 1);
  if (!tabList.value.length) {
    isShowBox.value = false;
  }
}
/** 提交 */
function bindSub() {
  disabled.value = true;
  loadings.value = true;
  liveGoodsAddApi({ goods_info: tabList.value })
    .then((res) => {
      ElMessage.success('添加成功');
      disabled.value = false;
      setTimeout(() => {
        router.push({ path: Setting.routePre + '/marketing/live/goods' });
      }, 500);
    })
    .catch((error) => {
      disabled.value = false;
      ElMessage.error(error.msg);
    });
}
</script>

<style lang="scss" scoped>
.upload-box {
  width: 58px;
  height: 58px;
  line-height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.02);
  cursor: pointer;
}
.box {
  display: flex;
  flex-wrap: wrap;
  .box-item {
    position: relative;
    margin-right: 20px;
    .el-icon-error {
      position: absolute;
      right: -10px;
      top: -8px;
      color: #999;
      cursor: pointer;
    }
  }
  .upload-box,
  .box-item {
    width: 60px;
    height: 60px;
    margin-bottom: 10px;
    img {
      width: 100%;
      height: 100%;
      cursor: pointer;
    }
  }
}
.active-btn {
  padding-left: 96px;
}
.table-box {
  margin: 0 107px;
}
.sub_btn {
  margin-top: 10px;
}
.product_box {
  display: flex;
  img {
    width: 36px;
    height: 36px;
    margin-right: 10px;
  }
}
</style>
