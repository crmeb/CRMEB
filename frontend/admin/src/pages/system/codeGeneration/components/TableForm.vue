<template>
  <div class="main">
    <el-alert closable class="mb14">
      <template v-slot:title>crud生成说明</template>
      <p>
        1、字段配置中表存在生成的字段为表内列的信息,并且主键、伪删除字段不允许设置为列，主键默认展示在列表中，伪删除字段不允许展示
      </p>
      <p>2、在字段配置中新建表时，主键不需要增加列，会自动增加一行主键id</p>
      <p>3、在字段配置中，表单类型为不生成时创建后不会生成对应的表单项</p>
      <p>4、添加字段id、create_time、update_time、delete_time为不可用字段</p>
    </el-alert>
    <div class="df mb14">
      <el-button class="mr20" type="primary" v-db-click @click="addRow">添加一行</el-button>
      <el-checkbox class="mr10" v-model="isCreate" @change="addCreate">添加与修改时间</el-checkbox>
      <el-checkbox class="mr10" v-model="isDelete" @change="addDelete">伪删除</el-checkbox>
    </div>
    <div>
      <el-table
        ref="selection"
        :data="tableField"
        empty-text="暂无数据"
        highlight-current-row
        v-loading="loading"
        max-height="600"
      >
        <el-table-column label="" min-width="40">
          <template #default="scope">
            <div class="drag" v-if="!disabledInput(scope.$index)">
              <img class="handle" src="@/assets/images/drag-icon.png" alt="" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="表单名" min-width="130">
          <template #default="scope">
            <el-input
              v-model="scope.row.table_name"
              :disabled="disabledInput(scope.$index) && scope.row.field == 'id'"
              @change="(e) => changeComment(e, scope.$index)"
            ></el-input>
          </template>
        </el-table-column>
        <el-table-column label="表单类型" min-width="130">
          <template #default="scope">
            <el-select
              clearable
              v-model="scope.row.from_type"
              :disabled="disabledInput(scope.$index) && scope.row.field == 'id'"
              @change="(e) => fromTypeChange(e, scope.$index)"
            >
              <el-option
                v-for="item in fromTypeList"
                :value="item.value"
                :key="item.value"
                :label="item.label"
              ></el-option>
            </el-select>
          </template>
        </el-table-column>
        <el-table-column label="数据字典" min-width="130">
          <template #default="scope">
            <div class="table-options" v-if="['select', 'radio', 'checkbox'].includes(scope.row.from_type)">
              <el-select clearable v-model="scope.row.dictionary_id">
                <el-option
                  v-for="item in dictionaryList"
                  :value="item.id"
                  :key="item.id"
                  :label="item.name"
                ></el-option>
              </el-select>
              <!-- <i class="el-icon-edit create" v-db-click @click="eidtOptions(scope.$index)" /> -->
            </div>
            <div v-else>--</div>
          </template>
        </el-table-column>
        <el-table-column label="必填" width="50">
          <template #default="scope">
            <el-checkbox
              v-model="scope.row.required"
              :disabled="disabledInput(scope.$index) && scope.row.field == 'id'"
            ></el-checkbox>
          </template>
        </el-table-column>

        <el-table-column label="查询方式" min-width="130">
          <template #default="scope">
            <el-select
              clearable
              v-model="scope.row.search"
              :disabled="disabledInput(scope.$index)"
              placeholder="请选择"
            >
              <el-option
                :label="item.label"
                :value="item.value"
                v-for="item in searchType"
                :key="item.value"
              ></el-option>
            </el-select>
          </template>
        </el-table-column>
        <el-table-column label="列表" width="50">
          <template #default="scope">
            <el-checkbox
              v-model="scope.row.is_table"
              :disabled="disabledInput(scope.$index) && scope.row.field == 'id'"
            ></el-checkbox>
          </template>
        </el-table-column>
        <el-table-column label="字段名称" min-width="120">
          <template #default="scope">
            <el-input
              :disabled="disabledInput(scope.$index)"
              v-model="scope.row.field"
              @blur="changeField(scope.$index)"
            ></el-input>
          </template>
        </el-table-column>
        <el-table-column label="字段类型" min-width="130">
          <template #default="scope">
            <el-select
              v-model="scope.row.field_type"
              :disabled="disabledInput(scope.$index)"
              clearable
              @change="changeItemField($event, scope.$index)"
            >
              <el-option v-for="item in columnTypeList" :value="item" :key="item" :label="item"></el-option>
            </el-select>
          </template>
        </el-table-column>
        <el-table-column label="长度" min-width="80">
          <template #default="scope">
            <el-input
              v-if="scope.row.field_type !== 'enum'"
              v-model="scope.row.limit"
              :disabled="disabledInput(scope.$index)"
            ></el-input>
            <el-select
              v-else
              v-model="scope.row.limit"
              multiple
              filterable
              allow-create
              clearable
              default-first-option
              placeholder="请添加"
            />
          </template>
        </el-table-column>
        <el-table-column label="默认值" min-width="180">
          <template #default="scope">
            <el-input
              class="input-with-select"
              v-model="scope.row.default"
              :disabled="disabledInput(scope.$index) || scope.row.default_type !== '1'"
            >
              <template #prepend
                ><el-select
                  class="code-table-sel"
                  clearable
                  v-model="scope.row.default_type"
                  :disabled="disabledInput(scope.$index)"
                  placeholder="请选择"
                  style="width: 100px"
                >
                  <el-option
                    :label="item.label"
                    :value="item.value"
                    v-for="item in defaultType"
                    :key="item.value"
                  ></el-option> </el-select
              ></template>
            </el-input>
            <!-- <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value"> </el-option> -->
          </template>
        </el-table-column>
        <el-table-column label="字段描述" min-width="130">
          <template #default="scope">
            <el-input v-model="scope.row.comment" :disabled="disabledInput(scope.$index)"></el-input>
          </template>
        </el-table-column>

        <el-table-column label="关联表" min-width="130">
          <template #default="scope">
            <el-cascader
              clearable
              filterable
              v-model="scope.row.hasOne"
              :disabled="disabledInput(scope.$index) && scope.row.field == 'id'"
              :options="associationTable"
              :props="props2"
            ></el-cascader>
          </template>
        </el-table-column>
        <el-table-column label="索引" width="50">
          <template #default="scope">
            <el-checkbox
              v-model="scope.row.index"
              :disabled="disabledInput(scope.$index) && scope.row.field == 'id'"
            ></el-checkbox>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="100">
          <template #default="scope">
            <a v-if="!scope.row.primaryKey && !disabledInput(scope.$index)" v-db-click @click="del(row, scope.$index)"
              >删除</a
            >
            <span v-else>--</span>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <el-dialog
      v-model="optionsModal"
      title="字典配置"
      @close="beforeChange"
      :close-on-click-modal="false"
      width="600px"
    >
      <div class="options-list">
        <el-form ref="form" :inline="true" label-width="80px">
          <div class="mb10">
            <el-form-item label="字典名称：">
              <el-input class="mr10" v-model="dictionaryName" placeholder="字典名称" style="width: 310px" />
            </el-form-item>
          </div>
          <div class="item" v-for="(item, index) in optionsList" :key="index">
            <el-form-item label="数据名称：">
              <el-input class="mr10" v-model="item.label" placeholder="label" style="width: 150px" />
            </el-form-item>
            <el-form-item label="数据值：">
              <el-input class="mr10" v-model="item.value" placeholder="value" style="width: 150px" />
            </el-form-item>
            <div style="display: inline-block; margin-bottom: 14px">
              <i
                v-if="index == optionsList.length - 1"
                class="el-icon-circle-plus-outline add"
                title="新增"
                v-db-click
                @click="addOneOptions"
              />
              <i
                v-if="index > 0"
                class="el-icon-remove-outline delete"
                title="删除"
                v-db-click
                @click="delOneOptions(index)"
              />
            </div>
          </div>
        </el-form>
      </div>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="optionsModal = false">取 消</el-button>
          <el-button type="primary" v-db-click @click="addOptions">确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import {
  crudMenus,
  crudColumnType,
  crudDataDictionary,
  crudFilePath,
  crudAssociationTable,
  crudAssociationTableName,
  crudDataDictionaryList,
  saveCrudDataDictionaryList,
  getDataDictionaryList,
} from '@/api/systemCodeGeneration';
import Sortable from 'sortablejs';

const props = defineProps({
  foundation: {
    type: Object,
    default: () => {
      return {};
    },
  },
  id: {
    type: [String, Number],
  },
});

const selection = ref(null);
const form = ref(null);

const foundationRules = {};
const menusList = ref([]);
const columnTypeList = ref([]);
const optionsModal = ref(false);
const isCreate = ref(false);
const isDelete = ref(false);
const fromTypeList = ref([]);
const loading = ref(false);
const tableField = ref([]);
const optionsList = ref([]);
const index = ref(0);
const deleteField = ref([]);
const searchType = ref([]);
const dictionaryName = ref(''); // 字典名称
const defaultType = ref([]); // 默认类型
const associationTable = ref([]); // 关联表
const dictionaryList = ref([]);
const props2 = {
  lazy: true,
  options: associationTable,
  checkStrictly: true,
  lazyLoad(node, resolve) {
    const { value } = node;
    if (value) {
      crudAssociationTableName(value).then((res) => {
        resolve(res.data);
      });
    }
    // 通过调用resolve将子节点数据返回，通知组件数据加载完成
  },
};
let dictionaryId = 0;
let sortable = null;

function beforeChange() {
  getCrudDataDictionary();
}
function setSort() {
  // ref一定跟table上面的ref一致
  const el = selection.value.$el.querySelectorAll('.el-table__body-wrapper > table > tbody')[0];
  sortable = Sortable.create(el, {
    ghostClass: 'sortable-ghost',
    handle: '.handle',
    setData: function (dataTransfer) {
      dataTransfer.setData('Text', '');
    },
    // 监听拖拽事件结束时触发
    onEnd: (evt) => {
      if (evt.newIndex === 0) {
        setTimeout(() => {
          elChangeExForArray(evt.oldIndex, evt.newIndex, tableField.value, true);
        }, 100);
      } else {
        elChangeExForArray(evt.oldIndex, evt.newIndex, tableField.value);
      }
    },
  });
}
function elChangeExForArray(index1, index2, array, init) {
  const arr = array;
  const temp = array[index1];
  const tempt = array[index2];
  if (init) {
    arr[index2] = tempt;
    arr[index1] = temp;
  } else {
    arr[index1] = tempt;
    arr[index2] = temp;
  }
  tableField.value = [];
  nextTick((e) => {
    tableField.value = arr;
  });
}
function disabledInput(index) {
  let fieldInfo = tableField.value[index];
  let res = ['addTimestamps', 'addSoftDelete'].includes(tableField.value[index].field_type);
  if (fieldInfo.primaryKey) {
    res = true;
  } else if (fieldInfo.field === 'delete_time' && fieldInfo.field_type === 'timestamp') {
    res = true;
  } else if (
    (fieldInfo.field === 'create_time' || fieldInfo.field === 'update_time') &&
    fieldInfo.field_type === 'timestamp'
  ) {
    res = true;
  }
  return res;
}
function initfield() {
  tableField.value = [];
}
function changeItemField(e, i) {
  if (e === 'addSoftDelete') {
    tableField.value[i]['comment'] = '伪删除';
  }
  if (e === 'addTimestamps') {
    tableField.value[i]['comment'] = '添加和修改时间';
  }
}
function eidtOptions(i) {
  index.value = i;
  dictionaryId = tableField.value[i].dictionary_id || 0;
  optionsModal.value = true;
  if (dictionaryId) {
    crudDataDictionaryList(dictionaryId).then((res) => {
      dictionaryName.value = res.data.name;
      optionsList.value = res.data.value || [{ label: '', value: '' }];
    });
  } else {
    optionsList.value = [{ label: '', value: '' }];
  }
}
function addOptions() {
  // tableField.value[index.value].options = optionsList.value;
  let d = {
    name: dictionaryName.value,
    value: optionsList.value,
  };
  saveCrudDataDictionaryList(dictionaryId, d).then((res) => {
    optionsModal.value = false;
    getCrudDataDictionary();
  });
}
function changeRadio(status) {
  tableField.value = [];
  if (status) {
    addRow();
  }
}
function initTableName() {
  tableField.value = [];
}
function addRow() {
  for (let i = 0; i < tableField.value.length; i++) {
    const el = tableField.value[i];
    if ((!el.field || !el.field_type) && !['addTimestamps', 'addSoftDelete'].includes(el.field_type)) {
      return ElMessage.warning('请先完善上一条数据');
    }
    if (
      el.is_table &&
      !el.table_name &&
      !Number(el.primaryKey) &&
      !['addTimestamps', 'addSoftDelete'].includes(el.field_type)
    ) {
      return ElMessage.warning('请输入列表名');
    }
  }
  let i = tableField.value.length;
  let spliceIndex = 0;
  tableField.value.map((e) => {
    if (e.field === 'create_time' || e.field === 'update_time') {
      spliceIndex++;
    }
    if (e.field === 'delete_time') {
      spliceIndex++;
    }
  });
  i = tableField.value.length - spliceIndex;
  tableField.value.splice(i, 0, {
    field: '',
    field_type: '',
    default: '',
    default_type: '-1',
    comment: '',
    required: false,
    is_table: true,
    table_name: '',
    limit: '',
    primaryKey: 0,
    from_type: '',
    search: '',
    dictionary_id: 0,
    hasOne: [],
    index: false,
  });
  // tableField.value.push();
}
function addCreate(status) {
  if (status) {
    let haveCre = tableField.value.findIndex((e) => e.field === 'create_time');
    let haveUp = tableField.value.findIndex((e) => e.field === 'update_time');
    if (haveCre > 0 || haveUp > 0) {
      nextTick((e) => {
        isCreate.value = false;
      });
      return ElMessage.warning('已存在 create_time或update_time');
    }
    let data = [
      {
        field: 'create_time',
        field_type: 'timestamp',
        default: '',
        default_type: '-1',
        comment: '添加时间',
        required: false,
        is_table: false,
        table_name: '添加时间',
        limit: '',
        primaryKey: 0,
        from_type: '',
        search: '',
        dictionary_id: 0,
        hasOne: [],
        index: false,
      },
      {
        field: 'update_time',
        field_type: 'timestamp',
        default_type: '-1',
        default: '',
        comment: '修改时间',
        required: false,
        is_table: false,
        table_name: '修改时间',
        limit: '',
        primaryKey: 0,
        from_type: '',
        search: '',
        dictionary_id: 0,
        hasOne: [],
        index: false,
      },
    ];
    tableField.value = [...tableField.value, ...data];
  } else {
    let i = tableField.value.findIndex((e) => e.field === 'create_time');
    tableField.value.splice(i, 2);
  }
}
function addDelete(status) {
  if (status) {
    let haveDel = tableField.value.findIndex((e) => e.field === 'delete_time');
    if (haveDel > 0) {
      isDelete.value = false;
      return ElMessage.warning('已存在 delete_time');
    }
    let data = [
      {
        field: 'delete_time',
        field_type: 'timestamp',
        default: '',
        default_type: '-1',
        comment: '伪删除',
        required: false,
        is_table: false,
        table_name: '伪删除',
        limit: '',
        primaryKey: 0,
        from_type: '',
        search: '',
        dictionary_id: 0,
        hasOne: [],
        index: false,
      },
    ];
    tableField.value = [...tableField.value, ...data];
  } else {
    let i = tableField.value.findIndex((e) => e.field === 'delete_time');
    tableField.value.splice(i, 1);
  }
}
function changeField(index) {
  if (tableField.value[index].field) {
    for (let i = 0; i < tableField.value.length; i++) {
      const e = tableField.value[i];
      if (['id', 'create_time', 'update_time', 'delete_time'].includes(tableField.value[index].field)) {
        ElMessage.warning('列表中已存在该字段名称');
        tableField.value[index].field = '';
        return;
      }
    }
  }
}
function changeComment(e, index) {
  if (!tableField.value[index].comment) tableField.value[index].comment = e;
}
function fromTypeChange(e, index) {
  fromTypeList.value.map((item) => {
    if (item.value == e) {
      tableField.value[index].limit = item.limit || '';
      tableField.value[index].field_type = item.field_type || '';
    }
  });
  // if (!tableField.value[index].comment) tableField.value[index].comment = e;
}
function getCrudMenus() {
  crudMenus().then((res) => {
    menusList.value = res.data;
  });
  crudColumnType().then((res) => {
    columnTypeList.value = res.data.types;
    fromTypeList.value = res.data.form;
    defaultType.value = res.data.default_type;
    searchType.value = res.data.search_type;
  });
  getCrudDataDictionary();
  crudAssociationTable().then((res) => {
    associationTable.value = res.data;
  });
}
function getCrudDataDictionary() {
  getDataDictionaryList().then((res) => {
    dictionaryList.value = res.data.list;
  });
}
function getCrudAssociationTableName(name) {
  crudAssociationTableName(name).then((res) => {
    console.log(res);
  });
}
function del(row, index) {
  tableField.value.splice(index, 1);
  if (props.id) {
    deleteField.value.push(row.field);
  }
}
function addOneOptions() {
  optionsList.value.push({
    label: '',
    value: '',
  });
}
function delOneOptions(i) {
  optionsList.value.splice(i, 1);
}

getCrudMenus();

onMounted(() => {
  nextTick((e) => {
    setSort();
  });
});

defineExpose({ tableField, isCreate, isDelete, deleteField });
</script>
<style lang="scss" scoped>
.form-width {
  width: 500px;
}
.item {
  display: flex;
  margin-bottom: 10px;
  .row {
    width: 140px;
    margin-right: 10px;
  }
}
.table-options {
  display: flex;
  align-items: center;
  .create {
    font-size: 16px;
    margin-left: 10px;
    cursor: pointer;
  }
}
.options-list {
  .item {
    display: flex;
    align-items: center;
    .add {
      font-size: 18px;
      cursor: pointer;
      margin-right: 5px;
      // color: #2d8cf0;
    }
    .delete {
      font-size: 18px;
      cursor: pointer;
      color: #fb0144;
    }
  }
}
:deep(.el-input-group__prepend .el-select) {
  width: 86px;
}
.drag {
  display: flex;
  align-items: center;
  justify-content: center;
  .handle {
    width: 9px;
    height: 15px;
  }
}
.code-table-sel :deep(.el-input__inner) {
  border: none;
  border-color: transparent;
  background-color: transparent;
}
:deep(.el-input-group__prepend div.el-select .el-input__inner) {
  height: 28px !important;
  line-height: 28px !important;
}
:deep(.el-input-group__prepend div.el-select .el-input--small .el-input__icon) {
  line-height: 28px;
}
</style>
