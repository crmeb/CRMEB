<template>
  <div>
    <el-card shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          v-if="search.length"
          ref="curlFrom"
          :model="from"
          :label-width="labelWidth"
          :label-position="labelPosition"
          inline
          @submit.prevent
        >
          <el-form-item :label="item.name + ':'" v-for="(item, index) in search" :key="index">
            <el-input
              v-if="item.type === 'input'"
              v-model="from[item.field]"
              :placeholder="'请输入' + item.name"
              class="form_content_width"
            />
            <date-range-picker
              v-else-if="item.type === 'date-picker'"
              :editable="false"
              clearabl
              @change="searchs"
              v-model="from[item.field]"
              format="YYYY/MM/DD"
              value-format="YYYY/MM/DD"
            ></date-range-picker>
            <el-select
              v-else-if="item.type === 'select'"
              v-model="from[item.field]"
              placeholder="请选择"
              clearable
              @change="searchs"
              class="form_content_width"
            >
              <el-option v-for="(val, i) in item.option" :value="val.value" :label="val.label" :key="i"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="searchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card shadow="never" class="ivu-mt" :class="search.length ? 'mt16' : ''">
      <el-row class="action-row">
        <el-col v-bind="grid">
          <el-button type="primary" v-db-click @click="add">添加</el-button>
        </el-col>
      </el-row>
      <el-table
        :data="dataList"
        ref="table"
        class="mt14"
        v-loading="loading"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column :label="item.title" :min-width="item.minWidth" v-for="(item, index) in columns" :key="index">
          <template #default="scope">
            <template v-if="item.key">
              <span>{{ scope.row[item.key] }}</span>
            </template>
            <template v-else-if="item.from_type == 'frameImageOne'">
              <div class="tabBox_img" v-viewer>
                <img v-lazy="scope.row[item.slot]" />
              </div>
            </template>
            <template v-else-if="item.from_type == 'frameImages'">
              <div class="frame-images">
                <div class="tabBox_img" v-viewer v-for="(item, index) in scope.row[item.slot]" :key="index">
                  <img v-lazy="item" />
                </div>
              </div>
            </template>
            <template v-else-if="item.from_type == 'dateTimeRange'">
              <span>{{ scope.row[item.slot][0] }}--{{ scope.row[item.slot][1] }}</span>
            </template>
            <template v-else-if="item.slot === 'action'">
              <a v-db-click @click="show(scope.row)">详情</a>
              <el-divider direction="vertical" />
              <a v-db-click @click="edit(scope.row)">修改</a>
              <el-divider direction="vertical"></el-divider>
              <a v-db-click @click="del(scope.row, '删除', scope.$index)">删除</a>
            </template>
            <template v-else-if="item.from_type === 'switches'">
              <el-switch
                :active-value="1"
                :inactive-value="0"
                v-model="scope.row[item.slot]"
                :value="scope.row[item.slot]"
                size="large"
                @change="onchangeIsShow(scope.row, item.slot)"
              >
              </el-switch>
            </template>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="from.page"
          v-model:limit="from.limit"
          @pagination="getList"
        />
      </div>
    </el-card>

    <el-dialog title="查看详情" v-model="dialogTableVisible" v-if="dialogTableVisible">
      <el-descriptions :title="readFields.name">
        <el-descriptions-item :label="item.comment" v-for="(item, index) in readFields.all" :key="index">
          <div v-if="item.from_type == 'frameImageOne'">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="info[item.field]" />
            </div>
          </div>
          <div v-else-if="item.from_type == 'frameImages'">
            <div class="frame-images">
              <div class="tabBox_img" v-viewer v-for="(item, index) in info[item.field]" :key="index">
                <img v-lazy="item" />
              </div>
            </div>
          </div>
          <div v-else-if="item.from_type == 'dateTimeRange'">
            <span>{{ info[item.field][0] }}--{{ info[item.field][1] }}</span>
          </div>
          <div v-else>{{ info[item.field] }}</div>
        </el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useRoute, onBeforeRouteUpdate } from 'vue-router';
import { ElMessage } from 'element-plus';
import { crudApi, getList as getListApi, getCreateApi, getStatusApi, getEditApi } from '@/api/crud.js';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'crud_index' });

const route = useRoute();
const mediaStore = useMediaStore();

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const loading = ref(false);
const columns = ref([]);
const readFields = ref({
  name: '',
  all: [],
});
const from = reactive({
  page: 1,
  limit: 15,
});
const dataList = ref([]);
const total = ref(0);
const methodApi = ref({});
const curdKey = ref('');
const dialogTableVisible = ref(false);
const info = ref({});
const search = ref([]);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

onBeforeRouteUpdate((to, fromRoute, next) => {
  from.page = 1;
  getCrudApi(to.params.table_name);
  next();
});

function show(row) {
  let url = methodApi.value.read.replace('<id>', row.id);
  getCreateApi(url)
    .then((res) => {
      dialogTableVisible.value = true;
      info.value = res.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function onchangeIsShow(row, field) {
  let url = methodApi.value.status.replace('<id>', row.id);
  getStatusApi(url, { field: field, value: row[field] })
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function searchs() {
  from.page = 1;
  getList();
}
function getCrudApi(tableName) {
  crudApi(tableName).then((res) => {
    methodApi.value = res.data.route;
    curdKey.value = res.data.key;
    readFields.value = res.data.readFields;
    res.data.search.map((item) => {
      from[item.field] = '';
    });
    search.value = res.data.search;
    res.data.columns.push({
      title: '操作',
      slot: 'action',
      fixed: 'right',
      width: 100,
      align: 'center',
    });
    res.data.columns.map((item) => {
      if (item.from_type === 'frameImageOne') {
        item.render = (h, params) => {
          return h(
            'div',
            {
              class: 'tabBox_img',
              directives: [
                {
                  name: 'viewer',
                },
              ],
            },
            [
              h('img', {
                directives: [
                  {
                    name: 'lazy',
                    value: params.row[item.slot],
                  },
                ],
              }),
            ],
          );
        };
      } else if (item.from_type === 'frameImages') {
        item.render = (h, params) => {
          let image = params.row[item.slot] || [];
          let imageH = [];
          image.map((item) => {
            imageH.push(
              h('img', {
                directives: [
                  {
                    name: 'lazy',
                    value: item,
                  },
                ],
              }),
            );
          });
          return h(
            'div',
            {
              class: 'tabBox_img',
              directives: [
                {
                  name: 'viewer',
                },
              ],
            },
            imageH,
          );
        };
      }
    });
    columns.value = res.data.columns;
    getList();
  });
}
// 添加
function add() {
  let url = methodApi.value.create;
  modalForm(getCreateApi(url)).then(() => getList());
}
//列表
function getList() {
  loading.value = true;
  let url = methodApi.value.index;
  getListApi(url, from)
    .then(async (res) => {
      let data = res.data;
      dataList.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 修改
function edit(row) {
  let url = methodApi.value.edit.replace('<id>', row[curdKey.value]);
  modalForm(getEditApi(url)).then(() => getList());
}
// 删除
function del(row, tit, num) {
  let url = methodApi.value.delete.replace('<id>', row[curdKey.value]);
  let delfromData = {
    title: tit,
    num: num,
    url: url,
    method: 'DELETE',
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

// created
getCrudApi(route.params.table_name);
</script>

<style scoped lang="scss">
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;
  margin: 1px;

  img {
    width: 100%;
    height: 100%;
  }
}

.frame-images {
  display: flex;
  flex-wrap: wrap;
}
</style>
