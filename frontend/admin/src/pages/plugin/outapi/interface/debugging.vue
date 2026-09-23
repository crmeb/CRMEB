<template>
  <div class="content" v-if="interfaceData">
    <div class="head">
      <el-input v-model="interfaceData.url">
        <template #prepend>
          <el-select v-model="interfaceData.method" style="width: 120px">
            <el-option
              v-for="(item, index) in requestTypeList"
              :key="index"
              :value="item.value"
              :label="item.label"
            ></el-option>
          </el-select>
        </template>
      </el-input>
      <el-button class="ml20" type="primary" v-db-click @click="requestData">请求</el-button>
      <el-button class="ml10 copy-btn" type="success" v-db-click @click="insertCopy()">复制</el-button>
    </div>
    <div class="params">
      <el-tabs class="mt10" v-model="paramsType" @tab-change="changeTab">
        <el-tab-pane label="Params" name="Params"> </el-tab-pane>
        <el-tab-pane label="Body" name="Body"> </el-tab-pane>
        <el-tab-pane label="Header" name="Header"> </el-tab-pane>
      </el-tabs>
      <div v-show="paramsType === 'Params'">
        <vxe-table
          class="mt10"
          show-overflow
          keep-source
          ref="xTable"
          :print-config="{}"
          :export-config="{}"
          :tree-config="{ transform: true, rowField: 'id', parentField: 'parentId' }"
          :data="interfaceData.request_params"
          :row-config="{ keyField: 'id' }"
          :column-config="{ resizable: true }"
        >
          <vxe-column field="attribute" width="150" title="属性" tree-node :edit-render="{}">
            <template #default="{ row }">
              <vxe-input v-model="row.attribute" type="text"></vxe-input>
            </template>
          </vxe-column>
          <vxe-column field="value" title="参数值" :edit-render="{}">
            <template #default="{ row }">
              <vxe-input v-model="row.value" type="text"></vxe-input>
            </template>
          </vxe-column>
          <vxe-column field="type" title="类型" width="120" :edit-render="{}">
            <template #default="{ row }">
              <vxe-select v-model="row.type" transfer>
                <vxe-option
                  v-for="item in typeList"
                  :key="item.value"
                  :value="item.value"
                  :label="item.label"
                ></vxe-option>
              </vxe-select>
            </template>
          </vxe-column>
          <vxe-column field="must" title="必填" width="50" :edit-render="{}">
            <template #default="{ row }">
              <span>{{ row.must == '1' ? '是' : '否' }}</span>
            </template>
          </vxe-column>
          <vxe-column field="trip" width="150" title="说明" :edit-render="{}">
            <template #default="{ row }">
              <vxe-input v-model="row.trip" type="text"></vxe-input>
            </template>
          </vxe-column>
          <vxe-column title="操作" width="120">
            <template #default="{ row }">
              <vxe-button
                type="text"
                v-if="row.type === 'array'"
                status="primary"
                v-db-click
                @click="insertRow(row, 'xTable')"
                >插入</vxe-button
              >
              <vxe-button type="text" status="primary" v-db-click @click="removeRow(row, 'xTable')">删除</vxe-button>
            </template>
          </vxe-column>
        </vxe-table>
        <el-button class="mt10" type="primary" v-db-click @click="insertEvent('xTable')">添加参数</el-button>
      </div>
      <div v-show="paramsType === 'Body'">
        <vxe-table
          class="mt10"
          show-overflow
          keep-source
          ref="yTable"
          :print-config="{}"
          :export-config="{}"
          :tree-config="{ transform: true, rowField: 'id', parentField: 'parentId' }"
          :data="interfaceData.request_body"
          :row-config="{ keyField: 'id' }"
          :column-config="{ resizable: true }"
        >
          <vxe-column field="attribute" width="150" title="属性" tree-node :edit-render="{}">
            <template #default="{ row }">
              <vxe-input v-model="row.attribute" type="text"></vxe-input>
            </template>
          </vxe-column>
          <vxe-column field="value" title="参数值" :edit-render="{}">
            <template #default="{ row }">
              <vxe-input v-model="row.value" type="text"></vxe-input>
            </template>
          </vxe-column>
          <vxe-column field="type" title="类型" width="120" :edit-render="{}">
            <template #default="{ row }">
              <vxe-select v-model="row.type" transfer>
                <vxe-option
                  v-for="item in typeList"
                  :key="item.value"
                  :value="item.value"
                  :label="item.label"
                ></vxe-option>
              </vxe-select>
            </template>
          </vxe-column>
          <vxe-column field="must" title="必填" width="50" :edit-render="{}">
            <template #default="{ row }">
              <span>{{ row.must == '1' ? '是' : '否' }}</span>
            </template>
          </vxe-column>
          <vxe-column field="trip" title="说明" width="150" :edit-render="{}">
            <template #default="{ row }">
              <vxe-input v-model="row.trip" type="text"></vxe-input>
            </template>
          </vxe-column>
          <vxe-column title="操作" width="120">
            <template #default="{ row }">
              <vxe-button
                type="text"
                v-if="row.type === 'array'"
                status="primary"
                v-db-click
                @click="insertRow(row, 'yTable')"
                >插入</vxe-button
              >
              <vxe-button type="text" status="primary" v-db-click @click="removeRow(row, 'yTable')">删除</vxe-button>
            </template>
          </vxe-column>
        </vxe-table>
        <el-button class="mt10" type="primary" v-db-click @click="insertEvent('yTable')">添加参数</el-button>
      </div>
      <div v-show="paramsType === 'Header'">
        <vxe-table
          class="mt10"
          show-overflow
          keep-source
          ref="zTable"
          :print-config="{}"
          :export-config="{}"
          :tree-config="{ transform: true, rowField: 'id', parentField: 'parentId' }"
          :data="interfaceData.headerData"
          :row-config="{ keyField: 'id' }"
          :column-config="{ resizable: true }"
        >
          <vxe-column field="attribute" width="300" title="属性" tree-node :edit-render="{}">
            <template #default="{ row }">
              <vxe-input v-model="row.attribute" type="text"></vxe-input>
            </template>
          </vxe-column>
          <vxe-column field="value" title="参数值" :edit-render="{}">
            <template #default="{ row }">
              <vxe-input v-model="row.value" type="text"></vxe-input>
            </template>
          </vxe-column>
          <vxe-column field="type" title="类型" width="200" :edit-render="{}">
            <template #default="{ row }">
              <vxe-select v-model="row.type" transfer>
                <vxe-option
                  v-for="item in typeList"
                  :key="item.value"
                  :value="item.value"
                  :label="item.label"
                ></vxe-option>
              </vxe-select>
            </template>
          </vxe-column>
          <vxe-column title="操作" width="100">
            <template #default="{ row }">
              <vxe-button
                type="text"
                v-if="row.type === 'array'"
                status="primary"
                v-db-click
                @click="insertRow(row, 'zTable')"
                >插入</vxe-button
              >
              <vxe-button type="text" status="primary" v-db-click @click="removeRow(row, 'zTable')">删除</vxe-button>
            </template>
          </vxe-column>
        </vxe-table>
        <el-button class="mt10" type="primary" v-db-click @click="insertEvent('zTable')">添加参数</el-button>
        <h4 class="mt10 title">全局Header参数</h4>
        <vxe-table
          class="mt10"
          show-overflow
          keep-source
          ref="zaTable"
          :print-config="{}"
          :export-config="{}"
          :tree-config="{ transform: true, rowField: 'id', parentField: 'parentId' }"
          :data="interfaceData.allHeaderData"
          :row-config="{ keyField: 'id' }"
          :column-config="{ resizable: true }"
        >
          <vxe-column field="attribute" width="300" title="属性" tree-node :edit-render="{}">
            <template #default="{ row }">
              <span>{{ row.attribute || '' }}</span>
            </template>
          </vxe-column>
          <vxe-column field="value" title="参数值" :edit-render="{}">
            <template #default="{ row }">
              <span>{{ row.value || '' }}</span>
            </template>
          </vxe-column>
          <vxe-column field="type" title="类型" width="200" :edit-render="{}">
            <template #default="{ row }">
              <span>{{ row.type || '' }}</span>
            </template>
          </vxe-column>
          <vxe-column field="trip" title="说明" :edit-render="{}">
            <template #default="{ row }">
              <span>{{ row.trip || '' }}</span>
            </template>
          </vxe-column>
        </vxe-table>
      </div>
    </div>
    <div class="res mt10 mb10" v-if="codes">
      <MonacoEditor :codes="codes" :readOnly="true" />
    </div>
  </div>
</template>

<script setup>
import { getCurrentInstance, ref } from 'vue';
import { ElMessage } from 'element-plus';
import request from './request';
import MonacoEditor from './components/MonacoEditor.vue';

const props = defineProps({
  formValidate: {
    type: Object,
    default: () => {
      return {};
    },
  },
  requestTypeList: {
    type: Array,
    default: () => {
      return [];
    },
  },
  typeList: {
    type: Array,
    default: () => {
      return [];
    },
  },
});

const { proxy } = getCurrentInstance();

const xTable = ref(null);
const yTable = ref(null);
const zTable = ref(null);
const zaTable = ref(null);
const tableRefs = { xTable: xTable, yTable: yTable, zTable: zTable, zaTable: zaTable };

const interfaceData = ref(undefined);
const paramsType = ref('Params');
const editor = ref(''); //当前编辑器对象
const codes = ref('');

function requestMethod(url, method, params, data, headerItem) {
  return request({
    url,
    method,
    params,
    data,
    headerItem,
  });
}

interfaceData.value = props.formValidate;
interfaceData.value.request_body = JSON.parse(JSON.stringify(interfaceData.value.request_params));

function insertCopy() {
  proxy
    .$copyText(codes.value)
    .then((message) => {
      ElMessage.success('复制成功');
    })
    .catch((err) => {
      ElMessage.error('复制失败');
    });
}
async function requestData() {
  let url, method, params, body, headers;
  url = interfaceData.value.url;
  method = interfaceData.value.method;
  params = filtersData((await xTable.value.getTableData().tableData) || []);
  body = filtersData((await yTable.value.getTableData().tableData) || []);
  let h = filtersData((await zTable.value.getTableData().tableData) || []);
  let h1 = filtersData((await zaTable.value.getTableData().tableData) || []);
  headers = {
    ...h,
    ...h1,
  };
  requestMethod(url, method, params, body, headers)
    .then((res) => {
      codes.value = res + '';
    })
    .catch((err) => {
      codes.value = JSON.stringify(err);
    });
}
function filtersData(arr) {
  try {
    let x = {};
    arr.map((e) => {
      if (!e.parentId) {
        for (let i in e) {
          if (i == 'attribute') {
            if (e.type !== 'array') {
              x[e[i]] = e.value || '';
            } else {
              let arr = [];
              e.children.map((item, index) => {
                arr[index] = filtersObj(item);
              });
              x[e[i]] = arr;
            }
          }
        }
      }
    });
    return x;
  } catch (error) {
    console.log(error);
  }
}
function filtersObj(obj) {
  let x = {};
  for (let i in obj) {
    if (i == 'attribute') {
      if (obj.type !== 'array') {
        x[obj[i]] = obj.value || '';
      } else {
        let arr = [];
        obj.children.map((item, index) => {
          arr[index] = filtersObj(item);
        });
        x[obj[i]] = arr;
      }
    }
  }
  return x;
}
function changeTab() {
  if (paramsType.value === 'Header') {
    if (!interfaceData.value.headerData) {
      insertEvent('zTable', {
        attribute: 'Content-Type',
        value: 'application/x-www-form-urlencoded',
      });
      insertEvent('zaTable');
    }
  }
}
async function insertEvent(type, d) {
  const $table = tableRefs[type].value;
  let newRow;
  if (type == 'xTable') {
    newRow = {
      attribute: '',
      type: 'string',
      must: 0,
      value: '',
      trip: '',
    };
  } else if (type == 'yTable') {
    newRow = {
      attribute: '',
      type: 'string',
      value: '',
      must: 0,
      trip: '',
    };
  } else if (type == 'zTable') {
    newRow = {
      attribute: '',
      type: '',
      value: '',
      trip: '',
    };
    newRow = { ...newRow, ...d };
  } else if (type == 'zaTable') {
    newRow = {
      attribute: 'token',
      type: 'string',
      value: '',
      must: 0,
      trip: '',
    };
  } else {
    newRow = {
      code: '',
      value: '',
      solution: '',
    };
  }
  const { row: data } = await $table.insertAt(newRow, -1);
  await $table.setActiveCell(data, 'name');
}
async function insertRow(currRow, type) {
  const $table = tableRefs[type].value;
  // 如果 null 则插入到目标节点顶部
  // 如果 -1 则插入到目标节点底部
  // 如果 row 则有插入到效的目标节点该行的位置
  let record;
  if (type == 'xTable') {
    record = {
      attribute: '',
      type: 'string',
      must: 0,
      value: '',
      trip: '',
      id: Date.now(),
      parentId: currRow.id, // 需要指定父节点，自动插入该节点中
    };
  } else {
    record = {
      code: '',
      value: '',
      solution: '',
      id: Date.now(),
      parentId: currRow.id, // 需要指定父节点，自动插入该节点中
    };
  }
  const { row: newRow } = await $table.insertAt(record, -1);
  await $table.setTreeExpand(currRow, true); // 将父节点展开
  await $table.setActiveRow(newRow); // 插入子节点
}
async function removeRow(row, type) {
  const $table = tableRefs[type].value;
  await $table.remove(row);
}
</script>
<style>
.vxe-select--panel.is--transfer {
  z-index: 99999 !important;
}
</style>
<style lang="scss" scoped>
.content {
  padding: 12px;
  .head {
    display: flex;
    align-items: center;
    .item {
      display: flex;
      align-items: center;
      margin-bottom: 12px;
      font-size: 14px;
      .title {
        margin-right: 14px;
      }
    }
  }
}
.copy-btn {
  display: flex;
  justify-content: right;
}
</style>
