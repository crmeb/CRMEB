<template>
  <div>
    <el-dialog v-model="isTemplate" title="优惠券列表" append-to-body width="1000px">
      <el-table
        :data="couponList"
        ref="couponTable"
        class="mt20"
        v-loading="loading"
        highlight-current-row
        :row-key="getRowKey"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
        @selection-change="changeCheckbox"
      >
        <el-table-column v-if="!luckDraw" type="selection" width="55" :reserve-selection="true"> </el-table-column>
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
        <el-table-column label="ID" width="70">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="优惠券名称" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="优惠券类型" min-width="100">
          <template #default="scope">
            <span v-if="scope.row.type === 1">品类券</span>
            <span v-else-if="scope.row.type === 2">商品券</span>
            <span v-else-if="scope.row.type === 3">会员券</span>
            <span v-else>通用券</span>
          </template>
        </el-table-column>
        <el-table-column label="面值" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.coupon_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="最低消费额" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.use_min_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="发布数量" min-width="100">
          <template #default="scope">
            <span v-if="scope.row.is_permanent">不限量</span>
            <div v-else>
              <span class="fa">发布：{{ scope.row.total_count }}</span>
              <span class="sheng ml10">剩余：{{ scope.row.remain_count }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="有效期限" min-width="100">
          <template #default="scope">
            <div v-if="scope.row.start_time">
              {{ $filters.formatDate(scope.row.start_time) }} - {{ $filters.formatDate(scope.row.end_time) }}
            </div>
            <span v-else>不限时</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="100">
          <template #default="scope">
            <el-tag size="default" v-show="scope.row.status === 1">正常</el-tag>
            <el-tag size="default" type="danger" v-show="scope.row.status === 0">未开启</el-tag>
            <el-tag size="default" type="info" v-show="scope.row.status === -1">已失效</el-tag>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="tableFrom.page"
          v-model:limit="tableFrom.limit"
          @pagination="tableList"
        />
      </div>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="cancel">取 消</el-button>
          <el-button type="primary" v-db-click @click="ok">确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { nextTick, onMounted, reactive, ref, watch } from 'vue';
import { releasedListApi } from '@/api/marketing';

defineOptions({ name: 'index' });

const emit = defineEmits(['getCouponId', 'nameId']);

const props = defineProps({
  couponids: {
    type: Array,
  },
  updateIds: {
    type: Array,
  },
  updateName: {
    type: Array,
  },
  luckDraw: {
    type: Boolean,
    default: false,
  },
  status: {
    type: Number,
    default: null,
  },
  receiveType: {
    type: [Number, String],
    default: 3,
  },
});

const templateRadio = ref(0);

const currentid = ref(0);
const productRow = ref({});
const isTemplate = ref(false);
const loading = ref(false);
const tableFrom = reactive({
  receive_type: 3,
  page: 1,
  limit: 15,
});
const total = ref(0);
const ids = ref([]);
const texts = ref([]);
const couponList = ref([]);
const selectedIds = ref([]);
const selectedNames = ref([]);
const multipleSelection = ref([]);
const couponTable = ref(null);

watch(
  () => props.updateIds,
  (newVal) => {
    selectedIds.value = newVal;
  },
);
watch(
  () => props.updateName,
  (newVal) => {
    selectedNames.value = newVal;
    multipleSelection.value = newVal;
  },
);
watch(isTemplate, (n) => {
  if (n) {
    tableFrom.page = 1;
    tableList();
  }
});

function getRowKey(row) {
  return row.id;
}
function getTemplateRow(row) {
  currentid.value = row.id;
  productRow.value = row;
}
//对象数组去重；
function unique(arr) {
  const res = new Map();
  return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
}
function changeCheckbox(selection) {
  let uniqueArr = [];
  let cups = [];
  let ids = [];
  let arr = unique(selection);
  for (let i = 0; i < arr.length; i++) {
    const item = arr[i];
    if (!ids.includes(item.id)) {
      let obj = {
        id: item.id,
        title: item.title,
        full_reduction: item.full_reduction, // 满
        use_min_price: item.use_min_price, // 满
        coupon_price: item.coupon_price, // 减
      };
      cups.push(obj);
      ids.push(item.id);
      uniqueArr.push(item);
    }
  }
  selectedIds.value = ids;
  selectedNames.value = cups;
  multipleSelection.value = uniqueArr;
}
function cancel() {
  isTemplate.value = false;
  if (props.luckDraw) {
    currentid.value = 0;
  }
}
function tableList() {
  loading.value = true;
  tableFrom.receive_type = props.receiveType === 'all' ? '' : props.receiveType;
  if (props.status !== null) {
    tableFrom.status = props.status;
  }
  releasedListApi(tableFrom).then((res) => {
    let data = res.data;
    couponList.value = data.list;
    total.value = data.count;
    nextTick(() => {
      //确保dom加载完毕
      selectedIds.value.length && setChecked();
      showSelectData();
    });
    loading.value = false;
  });
}
function setChecked() {
  //将new Set()转化为数组
  let ids = [...selectedIds.value];
  couponList.value.forEach((row) => {
    if (ids.includes(row.id)) {
      couponTable.value.toggleRowSelection(row, true);
    } else {
      couponTable.value.toggleRowSelection(row, false);
    }
  });
}
function ok() {
  if (props.luckDraw) {
    emit('getCouponId', productRow.value);
    currentid.value = 0;
  } else {
    emit('nameId', selectedIds.value, selectedNames.value);
  }
  isTemplate.value = false;
}
function pageChange(index) {
  tableFrom.page = index;
  tableList();
}
function limitChange(limit) {
  tableFrom.limit = limit;
  tableList();
}
function showSelectData() {
  if (multipleSelection.value.length > 0) {
    // 判断是否存在勾选过的数据
    couponList.value.forEach((row) => {
      // 获取数据列表接口请求到的数据
      multipleSelection.value.forEach((item) => {
        // 勾选到的数据
        if (row.id === item.id) {
          // couponTable.value.toggleRowSelection(item, true); // 若有重合，则回显该条数据
        }
      });
    });
  }
}

// 页面通过 $refs.couponTemplates.isTemplate / tableList() 驱动
defineExpose({ isTemplate, tableList });
</script>

<style scoped></style>
