<template>
  <div class="article-manager">
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidateRef"
          :model="formValidate"
          inline
          label-width="80px"
          label-position="right"
          @submit.prevent
        >
          <el-form-item label="评论时间：">
            <date-range-picker
              clearable
              v-model="timeVal"
              @change="onchangeTime"
              format="YYYY/MM/DD"
              value-format="YYYY/MM/DD"
            ></date-range-picker>
          </el-form-item>
          <el-form-item label="评价状态：">
            <el-select
              v-model="formValidate.is_reply"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="1" label="已回复"></el-option>
              <el-option value="0" label="未回复"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="审核状态：">
            <el-select
              v-model="formValidate.status"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="0" label="未审核"></el-option>
              <el-option value="1" label="已通过"></el-option>
              <el-option value="2" label="已拒绝"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="商品信息：" label-for="store_name">
            <el-input
              placeholder="请输入商品信息"
              clearable
              v-model="formValidate.store_name"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item label="用户名称：">
            <el-input
              enter-button
              placeholder="请输入"
              clearable
              v-model="formValidate.account"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-row class="action-row">
        <el-col v-bind="grid">
          <el-button v-auth="['product-reply-save_fictitious_reply']" type="primary" v-db-click @click="addRep"
            >添加自评</el-button
          >
          <el-button v-auth="['product-reply-save_fictitious_reply']" v-db-click @click="openBatchModal"
            >批量审核</el-button
          >
        </el-col>
      </el-row>
      <el-table
        ref="table"
        :data="tableList"
        class="mt14"
        v-loading="loading"
        @on-sort-change="sortMethod"
        @selection-change="handleSelectRow"
        empty-text="暂无数据"
      >
        <el-table-column type="selection" width="60"> </el-table-column>
        <el-table-column label="评论ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品信息" min-width="130">
          <template #default="scope">
            <div class="imgPic acea-row row-middle">
              <div class="pictrue" v-viewer><img v-lazy="scope.row.image" /></div>
              <div class="info line2">{{ scope.row.store_name }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="规格" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.suk }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="评分" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.score }}</span>
          </template>
        </el-table-column>
        <el-table-column label="评价内容" min-width="130">
          <template #default="scope">
            <div class="mb5 content_font">{{ scope.row.comment }}</div>
            <div v-viewer class="pictrue mr10" v-for="(item, index) in scope.row.pics || []" :key="index">
              <img v-lazy="item" :src="item" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="回复内容" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.merchant_reply_content }}</span>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" min-width="80">
          <template #default="scope">
            <el-tag effect="dark" v-if="scope.row.status == 1"> 通过 </el-tag>
            <el-tag effect="dark" type="warning" v-if="scope.row.status == 0"> 待审核 </el-tag>
            <el-tag effect="dark" type="danger" v-if="scope.row.status == 2"> 已拒绝 </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="评价时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <template v-if="scope.row.status == 0">
              <a class="item" v-db-click @click="adopt(scope.row, '审核通过', 1)">通过</a>
              <el-divider direction="vertical"></el-divider>
              <a class="item" v-db-click @click="adopt(scope.row, '拒绝', 2)">驳回</a>
              <el-divider direction="vertical"></el-divider>
            </template>
            <a v-if="scope.row.status != 2" v-db-click @click="reply(scope.row)">回复</a>
            <el-divider v-if="scope.row.status != 2" direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除评论', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="formValidate.page"
          v-model:limit="formValidate.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
    <el-dialog v-model="modals" scrollable title="回复内容" width="720px">
      <el-form ref="contentsRef" :model="contents" :rules="ruleInline" label-position="right" @submit.prevent>
        <el-form-item prop="content">
          <el-input v-model="contents.content" type="textarea" :rows="4" placeholder="请输入回复内容" />
        </el-form-item>
      </el-form>
      <template #footer
        ><div>
          <el-button @click="cancels">取消</el-button>
          <el-button type="primary" v-db-click @click="oks">确定</el-button>
        </div></template
      >
    </el-dialog>
    <addReply
      @close="close"
      :visible="replyModal"
      :goods="goodsData"
      :attr="attrData"
      :avatar="avatarData"
      :picture="pictureData"
      @callGoods="callGoods"
      @callAttr="callAttr"
      @callPicture="callPicture"
      @removePicture="removePicture"
    ></addReply>
    <el-dialog v-model="goodsModal" title="选择商品" width="1000px">
      <goodsList v-if="replyModal" @getProductId="getProductId"></goodsList>
    </el-dialog>
    <el-dialog v-model="attrModal" title="选择商品规格" width="1000px">
      <el-table ref="table" :row-key="getRowKey" :data="goodsData.attrs" height="500">
        <el-table-column label="" width="70">
          <template #default="scope">
            <el-radio
              v-model="templateRadio"
              :label="scope.row.unique"
              :value="scope.row.unique"
              @change="getTemplateRow(scope.row)"
              >&nbsp;</el-radio
            >
          </template>
        </el-table-column>
        <el-table-column label="图片" width="120">
          <template #default="scope">
            <div class="product-data">
              <img class="image" :src="scope.row.pic" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="规格" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.suk }}</span>
          </template>
        </el-table-column>
        <el-table-column label="售价" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.ot_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="优惠价" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
    <el-dialog v-model="pictureModal" width="950px" title="上传商品图" :close-on-click-modal="false">
      <uploadPictures
        :isChoice="isChoice"
        @getPic="getPic"
        @getPicD="getPicD"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="pictureModal"
      ></uploadPictures>
    </el-dialog>
    <el-dialog
      v-model="batchModal"
      class="batch-box"
      title="审核批量设置"
      :show-close="true"
      :close-on-click-modal="false"
      width="540px"
    >
      <el-form ref="batchFormDataRef" :model="batchFormData" label-width="90px" label-position="right" @submit.prevent>
        <el-row :gutter="24">
          <el-col :span="24">
            <el-form-item label="批量设置：" prop="status">
              <el-radio-group v-model="batchFormData.status">
                <el-radio :label="1" :value="1">通过</el-radio>
                <el-radio :label="2" :value="2">拒绝</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="batchModal = false">取 消</el-button>
          <el-button type="primary" v-db-click @click="batchSub">确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import { replyListApi, setReplyApi, replyBatchStatus } from '@/api/product';
import addReply from '../components/addReply.vue';
import goodsList from '@/components/goodsList/index';
import uploadPictures from '@/components/uploadPictures';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'product_productEvaluate' });

const route = useRoute();

const formValidateRef = ref(null);
const contentsRef = ref(null);
const batchFormDataRef = ref(null);

const templateRadio = ref(0);
const modals = ref(false);
const replyModal = ref(false);
const pictureModal = ref(false);
const goodsModal = ref(false);
const batchModal = ref(false);
const attrModal = ref(false); // 选择商品规格
const batchFormData = reactive({
  status: 1,
});
const grid = {
  xl: 7,
  lg: 10,
  md: 12,
  sm: 12,
  xs: 24,
};
const gridPic = {
  xl: 6,
  lg: 8,
  md: 12,
  sm: 12,
  xs: 12,
};
const gridBtn = {
  xl: 4,
  lg: 8,
  md: 8,
  sm: 8,
  xs: 8,
};
const formValidate = reactive({
  is_reply: '',
  data: '',
  store_name: '',
  key: '',
  order: '',
  account: '',
  status: '',
  product_id: route.params.id === undefined ? 0 : route.params.id,
  page: 1,
  limit: 15,
});
const value = ref('45');
const tableList = ref([]);
const goodsAddType = ref('');
const goodsData = ref({});
const attrData = ref({});
const avatarData = ref({});
const pictureData = ref([]);
const selectProductAttrList = ref([]);
const isChoice = ref('');
const picTit = ref('');
const tableIndex = ref(0);
const total = ref(0);
const loading = ref(false);
const timeVal = ref([]);
const contents = reactive({
  content: '',
});
const ruleInline = {
  content: [{ required: true, message: '请输入回复内容', trigger: 'blur' }],
};
const rows = ref({});
const ids = ref([]);

// created
if (route.query.is_reply == 0) formValidate.is_reply = route.query.is_reply;
getList();

watch(
  () => route.params.id,
  (to, from) => {
    formValidate.product_id = 0;
    getList();
  },
);
watch(replyModal, (value) => {
  if (!value) {
    goodsData.value = {};
    attrData.value = {};
    avatarData.value = {};
    pictureData.value = [];
    getList();
  }
});

// 通过/驳回
function adopt(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `product/reply/set_status/${row.id}/${num}`,
    method: 'put',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 添加虚拟评论；
function addRep() {
  // modalForm(fictitiousReply(formValidate.product_id)).then(() => getList());
  replyModal.value = true;
}
function getRowKey(row) {
  return row.unique;
}
function getTemplateRow(row) {
  attrData.value = row;
  attrModal.value = false;
}
function oks() {
  modals.value = true;
  contentsRef.value.validate((valid) => {
    if (valid) {
      setReplyApi(contents, rows.value.id)
        .then(async (res) => {
          ElMessage.success(res.msg);
          modals.value = false;
          contentsRef.value.resetFields();
          getList();
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    } else {
      return false;
    }
  });
}
function handleSelectRow(selection) {
  const idsArr = [];
  for (let i = 0; i < selection.length; i++) {
    const item = selection[i];
    if (!idsArr.includes(item.id)) {
      idsArr.push(item.id);
    }
  }
  ids.value = idsArr;
}
function openBatchModal() {
  if (!ids.value.length) return ElMessage.warning('请先选择评论');
  batchModal.value = true;
}
function batchSub() {
  let delfromData = {
    ids: ids.value,
    status: batchFormData.status,
  };
  replyBatchStatus(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      batchModal.value = false;
      ids.value = [];
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function cancels() {
  modals.value = false;
  contentsRef.value.resetFields();
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `product/reply/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tableList.value.splice(num, 1);
      total.value = total.value - 1;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 回复
function reply(row) {
  modals.value = true;
  rows.value = row;
}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e || [];
  formValidate.data = timeVal.value[0] ? (timeVal.value ? timeVal.value.join('-') : '') : '';
  formValidate.page = 1;
  getList();
}
function sortMethod(a) {
  if (a.order === 'normal') {
    formValidate.key = '';
    formValidate.order = '';
  } else {
    formValidate.key = a.key;
    formValidate.order = a.order;
  }
  getList();
}
function close(e) {
  replyModal.value = e;
  attrData.value = {};
  templateRadio.value = '';
}
// 选择时间
function selectChange(tab) {
  formValidate.data = tab;
  timeVal.value = [];
  formValidate.page = 1;
  getList();
}
// 列表
function getList() {
  loading.value = true;
  formValidate.is_reply = formValidate.is_reply || '';
  formValidate.store_name = formValidate.store_name || '';
  replyListApi(formValidate)
    .then(async (res) => {
      let data = res.data;
      tableList.value = data.list;
      total.value = res.data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function userSearchs() {
  formValidate.page = 1;
  getList();
}
function search() {}
function callGoods() {
  goodsModal.value = true;
}
function callAttr() {
  attrModal.value = true;
}
function getProductId(goods) {
  goodsData.value = goods;
  goodsModal.value = false;
  attrData.value.unique = '';
  templateRadio.value = '';
  attrData.value = {};
}
function getPic(pc) {
  avatarData.value = pc;
  pictureModal.value = false;
}
function getPicD(pc) {
  let pictureDataCopy = [...pictureData.value];
  pictureDataCopy = pictureDataCopy.concat(pc);
  pictureDataCopy.sort((a, b) => a.att_id - b.att_id);
  let picture = [];
  for (let i = 0; i < pictureDataCopy.length; i++) {
    if (pictureDataCopy[i + 1] && pictureDataCopy[i].att_id != pictureDataCopy[i + 1].att_id) {
      picture.push(pictureDataCopy[i]);
    }
    if (!pictureDataCopy[i + 1]) {
      picture.push(pictureDataCopy[i]);
    }
  }
  pictureData.value = picture;
  pictureModal.value = false;
}
function callPicture(type) {
  isChoice.value = type;
  pictureModal.value = true;
}
function removePicture(att_id) {
  let index = pictureData.value.findIndex((item) => item.att_id === att_id);
  pictureData.value.splice(index, 1);
}
</script>
<style lang="scss" scoped>
.content_font {
  color: #2b85e4;
}
.search {
  :deep(.ivu-form-item-content) {
    margin-left: 0 !important;
  }
}
.ivu-mt .Button .bnt {
  margin-right: 6px;
}
.ivu-mt .ivu-table-row {
  font-size: 12px;
  color: rgba(0, 0, 0, 0.65);
}
.ivu-mt :deep(.ivu-table-cell) {
  padding: 10px 0 !important;
}
.pictrue {
  width: 36px;
  height: 36px;
  display: inline-block;
  cursor: pointer;
}
.pictrue img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
}
.ivu-mt .imgPic .info {
  flex: 1;
  margin-left: 10px;
}
.ivu-mt .picList .pictrue {
  height: 36px;
  margin: 7px 3px 0 3px;
}
.ivu-mt .picList .pictrue img {
  height: 100%;
  display: block;
}
.product-data {
  display: flex;
  align-items: center;
  .image {
    width: 50px !important;
    height: 50px !important;
    margin-right: 10px;
  }
}
</style>
