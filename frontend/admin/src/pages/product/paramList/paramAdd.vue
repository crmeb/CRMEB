<template>
  <el-dialog v-model="modal" @closed="onCancel" title="商品参数" width="1000px">
    <el-form
      v-loading="spinShow"
      ref="formDynamicRef"
      :model="formDynamic"
      :rules="rules"
      class="attrFrom"
      label-position="right"
      label-width="auto"
      @submit.prevent
    >
      <el-row :gutter="24">
        <el-col :span="24">
          <el-col :span="8">
            <el-form-item label="模板名称：" prop="name">
              <el-input placeholder="请输入模板名称" :maxlength="20" v-model.trim="formDynamic.name" />
            </el-form-item>
          </el-col>
        </el-col>
        <el-col :span="24">
          <el-col :span="8">
            <el-form-item label="排序：" prop="sort">
              <el-input type="number" placeholder="请输入排序" :maxlength="20" v-model.trim="formDynamic.sort" />
            </el-form-item>
          </el-col>
        </el-col>
        <el-col :span="24" class="noForm" :key="index">
          <el-form-item label="">
            <div class="specifications">
              <el-table ref="selectionRef" :data="formDynamic.value">
                <el-table-column width="50">
                  <template #default="scope">
                    <div class="drag" @on-drag-drop="onDragDrop">
                      <img class="handle" src="@/assets/images/drag-icon.png" alt="" />
                    </div>
                  </template>
                </el-table-column>
                <el-table-column label="参数名称" min-width="180">
                  <template #default="scope">
                    <el-input v-model="scope.row.name"></el-input>
                  </template>
                </el-table-column>
                <el-table-column label="参数值" min-width="180">
                  <template #default="scope">
                    <el-input v-model="scope.row.value"></el-input>
                  </template>
                </el-table-column>

                <el-table-column label="操作" fixed="right" width="80">
                  <template #default="scope">
                    <a class="submission mr15" v-db-click @click="deleteRow(scope.$index)">删除</a>
                  </template>
                </el-table-column>
              </el-table>
              <el-button
                v-if="formDynamic.value.length < 8"
                type="primary"
                class="submission mr15 mt20"
                v-db-click
                @click="handleAddRole"
                >添加参数</el-button
              >
            </div>
          </el-form-item>
        </el-col>
      </el-row>
    </el-form>
    <template #footer
      ><span class="dialog-footer">
        <el-button v-db-click @click="onClose">取消</el-button>
        <el-button type="primary" :loading="modal_loading" v-db-click @click="handleSubmit('formDynamic')"
          >确定</el-button
        >
      </span></template
    >
  </el-dialog>
</template>

<script setup>
import { ref, reactive, watch, nextTick, getCurrentInstance } from 'vue';
import { ElMessage } from 'element-plus';
import { paramSaveApi, paramInfoApi } from '@/api/product';
import vuedraggable from 'vuedraggable';
import Sortable from 'sortablejs';

defineOptions({ name: 'addAttr' });

const emit = defineEmits(['getList']);
const { proxy } = getCurrentInstance();

const formDynamicRef = ref(null);
const selectionRef = ref(null);

const spinShow = ref(false);
const modal_loading = ref(false);
const grid = {
  xl: 3,
  lg: 3,
  md: 12,
  sm: 24,
  xs: 24,
};
const modal = ref(false);
const index = ref(1);
const rules = {
  name: [{ required: true, message: '请输入模板名称', trigger: 'blur' }],
};
const formDynamic = ref({
  id: 0,
  name: '',
  sort: 0,
  value: [],
});
const attrsName = ref('');
const attrsVal = ref('');
const formDynamicNameData = ref([]);
const isBtn = ref(false);
const formDynamicName = ref([]);
const results = ref([]);
const result = ref([]);
const ids = ref(0);

let sortable = null;

watch(modal, (val) => {
  if (val) {
    nextTick(() => {
      setSort();
    });
  }
});

function setSort() {
  // ref一定跟table上面的ref一致
  const el = selectionRef.value.$el.querySelectorAll('.el-table__body-wrapper > table > tbody')[0];
  sortable = Sortable.create(el, {
    ghostClass: 'sortable-ghost',
    handle: '.handle',
    setData: function (dataTransfer) {
      dataTransfer.setData('Text', '');
    },
    // 监听拖拽事件结束时触发
    onEnd: (evt) => {
      elChangeExForArray(evt.oldIndex, evt.newIndex, formDynamic.value.value);
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
  formDynamic.value.value = [];
  nextTick((e) => {
    formDynamic.value.value = arr;
  });
}
function handleShowPop(index) {
  proxy.$refs['inputRef_' + index][0].focus();
}
function handleAddRole() {
  let data = {
    name: '',
    value: '',
  };
  formDynamic.value.value.push(data);
}
function onCancel() {
  ids.value = 0;
  clear();
}
function onClose() {
  ids.value = 0;
  clear();
  modal.value = false;
}
function deleteRow(index) {
  formDynamic.value.value.splice(index, 1);
}
// 添加按钮
function addBtn() {
  isBtn.value = true;
}
//修改排序
function onDragDrop(a, b) {
  formDynamic.value.value.splice(b, 1, ...formDynamic.value.value.splice(a, 1, formDynamic.value.value[b]));
}
// 详情
function getIofo(row) {
  ids.value = row.id;
  paramInfoApi(row.id)
    .then((res) => {
      formDynamic.value = res.data;
    })
    .catch((res) => {
      spinShow.value = false;
      ElMessage.error(res.msg);
    });
}

// 父组件（index.vue）通过模板 ref 驱动弹窗，script setup 默认封闭，必须显式暴露
defineExpose({ modal, getIofo });
// 提交
function handleSubmit(name) {
  formDynamicRef.value.validate((valid) => {
    if (valid) {
      if (formDynamic.value.value.length === 0) {
        return ElMessage.warning('请至少添加一条商品参数！');
      }
      modal_loading.value = true;
      paramSaveApi(formDynamic.value)
        .then((res) => {
          ElMessage.success(res.msg);
          setTimeout(() => {
            modal.value = false;
            modal_loading.value = false;
          }, 500);
          setTimeout(() => {
            emit('getList');
            clear();
          }, 600);
        })
        .catch((res) => {
          modal_loading.value = false;
          ElMessage.error(res.msg);
        });
    } else {
      return false;
    }
  });
}
function clear() {
  formDynamicRef.value.resetFields();
  formDynamic.value.value = [];
  formDynamic.value.name = '';
  formDynamic.value.sort = '';
  isBtn.value = false;
  attrsName.value = '';
  attrsVal.value = '';
  ids.value = 0;
}
// 删除
function handleRemove(index) {
  formDynamic.value.value.splice(index, 1);
}
</script>

<style lang="scss" scoped>
.rulesBox {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
}
.attrFrom {
  :deep(.ivu-form-item) {
    margin-bottom: 0px !important;
  }
}
.noForm {
  margin-left: 12px;
}
.add {
  margin-left: 132px;
}
.drag {
  cursor: move;
}
.value {
  display: block;
  margin: 5px 0;
  position: relative;
  .el-icon-error {
    position: absolute;
    display: none;
    right: -3px;
    top: -3px;
    z-index: 9;
  }
}
.value:hover {
  .el-icon-error {
    display: block;
    z-index: 999;
    cursor: pointer;
  }
}
.move-icon {
  width: 30px;
  cursor: move;
  margin-right: 10px;
}
.move-icon .icondrag2 {
  font-size: 26px;
  color: #bbb;
}
.specifications {
  width: 100%;
  .specifications-item:hover {
    background-color: var(--prev-bg-menu-hover-ba-color);
  }
  .specifications-item:hover .del {
    display: block;
  }
  .specifications-item:last-child {
    margin-bottom: 14px;
  }
  .specifications-item {
    position: relative;
    display: flex;
    align-items: center;
    padding: 20px 15px;
    transition: all 0.1s;
    background-color: #fafafa;
    margin-bottom: 10px;
    border-radius: 4px;
    .del {
      display: none;
      position: absolute;
      right: 15px;
      top: 15px;
      font-size: 22px;
      color: var(--prev-color-primary);
      cursor: pointer;
    }
    .specifications-item-box {
      position: relative;
      .lineBox {
        position: absolute;
        left: 13px;
        top: 24px;
        width: 30px;
        height: 45px;
        border-radius: 6px;
        border-left: 1px solid #dcdfe6;
        border-bottom: 1px solid #dcdfe6;
      }
      .specifications-item-name-input {
        width: 200px;
      }
    }
  }
  .rulesBox {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    .item {
      display: flex;
      flex-wrap: wrap;
    }
    .addfont {
      margin-top: 5px;
    }
    :deep(.el-popover) {
      border: none;
      box-shadow: none;
      padding: 0;
      margin-top: 5px;
      line-height: 1.5;
    }
  }
  .addfont {
    display: inline-block;
    font-size: 12px;
    font-weight: 400;
    color: var(--prev-color-primary);
    margin-left: 14px;
    cursor: pointer;
  }
}
</style>
