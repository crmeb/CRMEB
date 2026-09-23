<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: '20px 20px 0px' }">
      <el-form
        :model="tableFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        label-position="right"
        inline
        @submit.prevent
      >
        <el-form-item label="活动时间：">
          <date-range-picker
            v-model="timeVal"
            :editable="false"
            @change="onchangeTime"
            format="YYYY/MM/DD"
            value-format="YYYY/MM/DD"
          ></date-range-picker>
        </el-form-item>
        <el-form-item label="活动状态：">
          <el-select
            class="form_content_width"
            v-model="tableFrom.start"
            clearable
            @change="userSearchs"
            placeholder="全部"
          >
            <el-option label="全部" value="" />
            <el-option label="未开始" :value="0" />
            <el-option label="进行中" :value="1" />
            <el-option label="已结束" :value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="活动类型：">
          <el-select
            class="form_content_width"
            v-model="tableFrom.factor"
            clearable
            @change="userSearchs"
            placeholder="全部"
          >
            <el-option label="全部" value="" />
            <el-option label="积分抽取" :value="1" />
            <el-option label="订单支付" :value="3" />
            <el-option label="订单评价" :value="4" />
          </el-select>
        </el-form-item>
        <el-form-item label="开启状态：">
          <el-select
            class="form_content_width"
            v-model="tableFrom.status"
            clearable
            @change="userSearchs"
            placeholder="全部"
          >
            <el-option label="全部" value="" />
            <el-option label="开启" :value="1" />
            <el-option label="关闭" :value="2" />
          </el-select>
        </el-form-item>

        <el-form-item label="搜索抽奖：">
          <el-input
            class="form_content_width"
            placeholder="请输入活动名称"
            v-model="tableFrom.keyword"
            @change="userSearchs"
            clearable
          />
        </el-form-item>
        <el-button type="primary" v-db-click @click="userSearchs()">查询</el-button>
      </el-form>
    </el-card>
    <el-card class="mt-20" :bordered="false" shadow="never">
      <el-button class="action-row mb12" type="primary" v-db-click @click="openPage(0)">创建抽奖活动</el-button>
      <el-table :data="tableList" v-loading="loading" highlight-current-row no-userFrom-text="暂无数据" class="mt14">
        <el-table-column label="ID" min-width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="活动名称" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="活动类型" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.lottery_type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="抽奖人数" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.records_total_user }}</span>
          </template>
        </el-table-column>
        <el-table-column label="中奖人数" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.records_wins_user }}</span>
          </template>
        </el-table-column>
        <el-table-column label="抽奖次数" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.records_total_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="中奖次数" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.records_wins_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="活动状态" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.status_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="开启状态" min-width="100">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="活动时间" min-width="180">
          <template #default="scope">
            <p>开始：{{ scope.row.start_time }}</p>
            <p>结束：{{ scope.row.end_time }}</p>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="180" fixed="right">
          <template #default="scope">
            <a @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical" />
            <a @click="openPage(1, scope.row)">抽奖记录</a>
            <el-divider direction="vertical" />
            <el-dropdown @command="(command) => changeMenu(scope.row, command, scope.$index)">
              <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
              <template #dropdown
                ><el-dropdown-menu>
                  <!-- <el-dropdown-item command="1">拉黑人员</el-dropdown-item>
                  <el-dropdown-item command="2">拉黑列表</el-dropdown-item> -->
                  <el-dropdown-item command="3">
                    <span class="copy copy-data" :data-clipboard-text="copyLink(scope.row)">复制链接</span>
                  </el-dropdown-item>
                  <el-dropdown-item command="4">删除抽奖</el-dropdown-item>
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
          v-model:page="tableFrom.page"
          v-model:limit="tableFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, getCurrentInstance } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { lotteryList, lotteryStatus } from '../api';
import { modalSure } from '@/utils/public';
import ClipboardJS from 'clipboard';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'lotteryList' });

const { proxy } = getCurrentInstance();
const router = useRouter();
const mediaStore = useMediaStore();

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'left'));

const shipModel = ref(false);
const loading = ref(false);
const blackModel = ref(false);
const locationList = ref([]);
const timeVal = ref([]);
const shipForm = ref({
  id: '',
  deliver_name: '',
  deliver_number: null,
});
const markForm = ref({
  id: '',
  mark: '',
});
const blackList = ref([]);
const loading2 = ref(false);
const promoterShow = ref(false);
const tableList = ref([]);
const grid = {
  xl: 7,
  lg: 10,
  md: 12,
  sm: 24,
  xs: 24,
};
const tableFrom = ref({
  keyword: '',
  time: [],
  page: 1,
  limit: 15,
  factor: '',
  status: '',
  start: '',
});
const total = ref(0);
const time = ref([]);
const modelType = ref(1);
const lottery_id = ref('');
const modelTitle = ref('');
const customerShow = ref(false);
const formInline = ref({
  uid: 0,
  image: '',
});
const total2 = ref(0);

onMounted(() => {
  nextTick(function () {
    const clipboard = new ClipboardJS('.copy-data');
    clipboard.on('success', () => {
      ElMessage.success('复制成功');
    });
  });
  getList();
});

// 操作
function changeMenu(row, name, index) {
  switch (name) {
    case '1':
      customer(row);
      break;
    case '3':
      onCopy(row);
      break;
    case '4':
      del(row, '删除活动', index);
      break;
  }
}
function onCopy(row) {
  proxy
    .$copyText(copyLink(row))
    .then((message) => {
      ElMessage.success('复制成功');
    })
    .catch((err) => {
      ElMessage.error('复制失败');
    });
}
function copyLink(row) {
  return `${window.location.origin}/pages/goods/lottery/grids/index?type=${row.factor}&lottery_id=${row.id}`;
}
function customer(row) {
  promoterShow.value = true;
  formInline.value.lottery_id = row.id;
}
function customerPle() {
  customerShow.value = true;
}
// 选择人员
function selectCustomer(e) {
  customerShow.value = false;
  formInline.value.uid = e.uid;
  formInline.value.image = e.image;
  formInline.value.nickname = e.nickname;
}
function edit(row) {
  router.push({
    path: '/admin/marketing/lottery/create',
    query: { lottery_id: row.id, type: row.type },
  });
}
function openPage(type, row) {
  let url;
  if (type === 1) {
    url = `/admin/marketing/lottery/recording_list?id=${row.id}`;
  } else {
    url = `/admin/marketing/lottery/create`;
  }
  router.push({
    path: url,
  });
}
// 具体日期
function onchangeTime(e) {
  time.value = e;
  if (!e || !e[0]) {
    tableFrom.value.time = [];
  } else {
    tableFrom.value.time = time.value[0] ? time.value.join('-') : '';
  }
  tableFrom.value.page = 1;
  getList();
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `marketing/lottery/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tableList.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 列表
function getList() {
  loading.value = true;
  lotteryList(tableFrom.value)
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
// 修改是否显示
function onchangeIsShow(row) {
  let data = {
    id: row.id,
    status: row.status,
  };
  lotteryStatus(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function userSearchs() {
  tableFrom.value.page = 1;
  getList();
}
</script>

<style scoped lang="scss">
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

.prize {
  display: flex;
  align-items: center;
}

body .el-table ::-webkit-scrollbar {
  // z-index: 11111;
  // width: 6px;
  // height: 6px;
  // background-color: #F5F5F5;
}

.prize img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 5px;
}

.trips {
  color: #ccc;
}
.picBox {
  display: inline-block;
  cursor: pointer;

  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
  }

  .pictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 10px;

    img {
      width: 100%;
      height: 100%;
    }
  }

  .iconfont {
    color: #898989;
  }
}
</style>
