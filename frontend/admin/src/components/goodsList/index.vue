<template>
  <div class="goodList">
    <el-form :model="formValidate" label-width="80px" label-position="right" inline class="tabform">
      <el-form-item label="商品分类：" v-if="!liveStatus">
        <el-cascader
          v-model="formValidate.cate_id"
          :options="treeSelect"
          :props="{ checkStrictly: true, emitPath: false, multiple: true }"
          clearable
          class="form_content_width"
        ></el-cascader>
      </el-form-item>
      <el-form-item label="商品类型：" v-if="!type && diy">
        <el-select v-model="goodType" clearable @change="userSearchs" class="form_content_width">
          <el-option v-for="item in goodList" :value="item.activeValue" :key="item.activeValue" :label="item.title">
          </el-option>
        </el-select>
      </el-form-item>
      <el-form-item label="商品搜索：">
        <el-input
          clearable
          placeholder="请输入商品名称/关键字/ID"
          v-model="formValidate.store_name"
          class="form_content_width"
        />
        <el-button type="primary" v-db-click @click="userSearchs" class="ml15">查询</el-button>
      </el-form-item>
    </el-form>
    <el-table
      ref="table"
      empty-text="暂无数据"
      max-height="400"
      :highlight-current-row="many !== 'many'"
      :data="tableList"
      v-loading="loading"
      @select="changeCheckbox"
      @select-all="changeCheckbox"
    >
      <el-table-column v-if="many == 'many'" type="selection" width="55"> </el-table-column>
      <el-table-column v-else width="50">
        <template #default="scope">
          <el-radio
            v-model="templateRadio"
            :label="scope.row.id"
            :value="scope.row.id"
            @change="getTemplateRow(scope.row)"
            >&nbsp;</el-radio
          >
        </template>
      </el-table-column>

      <el-table-column label="商品ID" width="80">
        <template #default="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="图片" width="80">
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
      <el-table-column label="商品分类" min-width="150" v-if="liveStatus">
        <template #default="scope">
          <span>{{ scope.row.cate_name }}</span>
        </template>
      </el-table-column>
    </el-table>
    <div class="acea-row row-right page">
      <pagination
        v-if="total"
        :total="total"
        v-model:page="formValidate.page"
        v-model:limit="formValidate.limit"
        @pagination="pageChange"
      />
      <el-button type="primary" v-db-click @click="ok" v-if="many === 'many' && !diy" class="ml15">提交</el-button>
    </div>
  </div>
</template>

<script setup>
import { nextTick, onMounted, reactive, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import { cascaderListApi, changeListApi } from '@/api/product';
import { liveGoods } from '@/api/live';
import { getProductList } from '@diy/api';

defineOptions({ name: 'index' });

const emit = defineEmits(['getProductId', 'getProductDiy']);

const route = useRoute();

const props = defineProps({
  is_new: {
    type: String,
    default: '',
  },
  type: {
    type: Number,
    default: 0,
  },
  diy: {
    type: Boolean,
    default: false,
  },
  ischeckbox: {
    type: Boolean,
    default: false,
  },
  liveStatus: {
    type: Boolean,
    default: false,
  },
  isLive: {
    type: Boolean,
    default: false,
  },
  isdiy: {
    type: Boolean,
    default: false,
  },
  selectIds: {
    type: Array,
    default: () => {
      return [];
    },
  },
  datas: {
    type: Object,
    default: function () {
      return {};
    },
  },
});

const templateRadio = ref(0);
const modal_loading = ref(false);
const treeSelect = ref([]);
const formValidate = reactive({
  page: 1,
  limit: 15,
  cate_id: [],
  store_name: '',
  is_new: props.is_new,
});
const total = ref(0);
const modals = ref(false);
const loading = ref(false);
const grid = {
  xl: 10,
  lg: 10,
  md: 12,
  sm: 24,
  xs: 24,
};
const tableList = ref([]);
const currentid = ref(0);
const productRow = ref({});
const images = ref([]);
const many = ref('');
const goodType = ref('');
let diyVal = null;
const goodList = [
  {
    activeValue: 0,
    title: '商品列表',
  },
  {
    activeValue: '7',
    title: '优品推荐',
  },
];
const table = ref(null);

watch(
  () => props.ischeckbox,
  (newVal, oldVal) => {
    if (newVal) {
      many.value = 'many';
    }
  },
  { immediate: true },
);

// created
{
  let manyInit = '';
  if (props.ischeckbox) {
    manyInit = 'many';
  } else {
    manyInit = route.query.type;
  }
  many.value = manyInit;
}

onMounted(() => {
  goodsCategory();
  if (props.diy) {
    productList();
  } else {
    getList();
  }
});

function productList() {
  let data = {
    page: formValidate.page,
    limit: formValidate.limit,
    cate_id: Array.isArray(formValidate.cate_id) ? formValidate.cate_id.join(',') : formValidate.cate_id,
    store_name: formValidate.store_name,
    type: props.type ? props.type : goodType.value,
  };
  loading.value = true;
  getProductList(data)
    .then((res) => {
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
function getTemplateRow(row) {
  let imgs = [];
  let imageObject = {
    image: row.image,
    product_id: row.id,
    store_name: row.store_name,
    temp_id: row.temp_id,
  };
  imgs.push(imageObject);
  images.value = imgs;
  diyVal = row;
  emit('getProductId', row);
}
function changeCheckbox(selection) {
  let imgs = [];
  selection.forEach(function (item) {
    let imageObject = {
      image: item.image,
      product_id: item.id,
      store_name: item.store_name,
      temp_id: item.temp_id,
    };
    imgs.push(imageObject);
  });

  images.value = imgs;
  diyVal = selection;
  emit('getProductDiy', selection);
}
// 商品分类；
function goodsCategory() {
  cascaderListApi(1)
    .then((res) => {
      treeSelect.value = res.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function pageChange() {
  if (props.diy) {
    productList();
  } else {
    getList();
  }
}
// 列表
function getList() {
  loading.value = true;
  if (!props.liveStatus) {
    if (props.isLive) {
      formValidate.is_live = 1;
    }
    // cascader 多选模式下 cate_id 为数组，转为逗号分隔字符串
    let params = { ...formValidate };
    if (Array.isArray(params.cate_id)) {
      params.cate_id = params.cate_id.join(',');
    }
    changeListApi(params)
      .then(async (res) => {
        let data = res.data;
        tableList.value = data.list;
        total.value = res.data.count;
        loading.value = false;
        nextTick(() => {
          if (props.selectIds.length) {
            let arr = [];
            props.selectIds.map((item) => {
              data.list.map((i) => {
                if (i.id == item) {
                  table.value.toggleRowSelection(i, true);
                  arr.push(i);
                }
              });
            });
            changeCheckbox(arr);
          }
        });
      })
      .catch((res) => {
        loading.value = false;
        ElMessage.error(res.msg);
      });
  } else {
    liveGoods({
      is_show: '1',
      status: '1',
      live_id: props.datas.id,
      kerword: formValidate.store_name,
      page: formValidate.page,
      limit: formValidate.limit,
    })
      .then(async (res) => {
        let data = res.data;
        data.list.forEach((el) => {
          el.image = el.cover_img;
        });
        if (props.selectIds.length) {
          props.selectIds.map((item) => {
            data.list.map((i) => {
              if (i.id == item) {
                table.value.toggleRowSelection(i);
              }
            });
          });
        }
        nextTick((e) => {
          tableList.value = data.list;
          total.value = res.data.count;
          loading.value = false;
        });
      })
      .catch((res) => {
        loading.value = false;
        ElMessage.error(res.msg);
      });
  }
}
function ok() {
  if (images.value.length > 0) {
    if (route.query.fodder === 'image') {
      let imageValue = form_create_helper.get('image');
      form_create_helper.set('image', imageValue.concat(images.value));
      form_create_helper.close('image');
    } else {
      table.value.clearSelection();
      if (props.isdiy) {
        emit('getProductId', diyVal);
      } else {
        emit('getProductId', images.value);
      }
    }
  } else {
    ElMessage.warning('请先选择商品');
  }
}
// 表格搜索
function userSearchs() {
  currentid.value = 0;
  productRow.value = {};
  formValidate.page = 1;
  if (props.diy) {
    productList();
  } else {
    getList();
  }
}
function clear() {
  productRow.value.id = '';
  currentid.value = '';
}

// 页面通过 $refs.goodslist.getList() / goodsCategory() 驱动；formValidate 供组合营销等页面覆写查询条件
defineExpose({ getList, goodsCategory, formValidate });
</script>

<style lang="scss" scoped>
:deep(.el-checkbox) {
  margin-bottom: 0 !important;
}
.footer {
  margin: 15px 0;
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
.tabform {
  :deep(.ivu-form-item) {
    margin-bottom: 16px !important;
  }
}
.btn {
  margin-top: 20px;
  float: right;
}
.goodList {
  :deep(table) {
    width: 100% !important;
  }
}
</style>
