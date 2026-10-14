<template>
  <el-dialog v-model="modal" @closed="onCancel" title="商品规格" width="1000px">
    <el-form
      v-loading="spinShow"
      ref="formDynamicRef"
      :model="formDynamic"
      :rules="rules"
      class="attrFrom"
      label-width="120px"
      label-position="right"
      @submit.prevent
    >
      <el-row :gutter="24">
        <el-col :span="24">
          <el-col :span="8">
            <el-form-item label="规格模板名称：" prop="rule_name">
              <el-input placeholder="请输入规格模板名称" :maxlength="20" v-model.trim="formDynamic.rule_name" />
            </el-form-item>
          </el-col>
        </el-col>
        <el-col :span="23" class="noForm" :key="index">
          <el-form-item label="">
            <div class="specifications">
              <draggable
                group="specifications"
                :list="formDynamic.spec"
                handle=".move-icon"
                animation="300"
                :item-key="specItemKey"
              >
                <template #item="{ element: item, index }">
                  <div class="specifications-item active" :data-spec-idx="index">
                    <div class="move-icon">
                      <span class="iconfont icondrag2"></span>
                    </div>
                    <i class="del el-icon-error" @click="handleRemoveRole(index)"></i>
                    <div class="specifications-item-box">
                      <div class="lineBox"></div>
                      <div class="specifications-item-name mb18">
                        <el-input
                          v-model="item.value"
                          placeholder="规格名称"
                          class="specifications-item-name-input"
                          maxlength="30"
                          show-word-limit
                        ></el-input>
                      </div>
                      <div class="rulesBox ml30">
                        <draggable class="item" :list="item.detail" handle=".drag" :item-key="detailItemKey">
                          <template #item="{ element: det, index: indexn }">
                            <div class="mr10 spec drag">
                              <i class="el-icon-error" @click="handleRemove2(item.detail, indexn)"></i>

                              <el-input
                                style="width: 120px"
                                v-model="det.value"
                                placeholder="规格值"
                                maxlength="30"
                                @keyup.enter="onDetailEnter(item, index, det)"
                              >
                                <template #prefix>
                                  <span class="iconfont icondrag2"></span>
                                </template>
                              </el-input>
                            </div>
                          </template>
                          <template #footer>
                            <div class="addfont" type="text" v-db-click @click="addAttrValue(item, index)">
                              添加规格值
                            </div>
                          </template>
                        </draggable>
                      </div>
                    </div>
                  </div>
                </template>
              </draggable>
              <el-button v-if="formDynamic.spec.length < 4" v-db-click @click="handleAddRole()">添加新规格</el-button>
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
import { ref, reactive, nextTick } from 'vue';
import { ElMessage } from 'element-plus';
import { ruleAddApi, ruleInfoApi } from '@/api/product';
import draggable from 'vuedraggable';

defineOptions({ name: 'addAttr' });

const emit = defineEmits(['getList']);

const formDynamicRef = ref(null);

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
  rule_name: [{ required: true, message: '请输入规格模板名称', trigger: 'blur' }],
};
const formDynamic = ref({
  rule_name: '',
  spec: [],
});
const attrsName = ref('');
const attrsVal = ref('');
const formDynamicNameData = ref([]);
const isBtn = ref(false);
const formDynamicName = ref([]);
const results = ref([]);
const result = ref([]);
const ids = ref(0);

// vuedraggable4 item-key：spec 为对象数组用 indexOf 即可；detail 前端持有 { id, value } 对象，
// key 必须取稳定 id，若取值本身，输入一个字符 key 就变、节点被重建导致输入框失焦
let detailUid = 0;
const specItemKey = (element) => formDynamic.value.spec.indexOf(element);
const detailItemKey = (element) => element.id;

// 父组件（index.vue）通过模板 ref 驱动弹窗，script setup 默认封闭，必须显式暴露
defineExpose({ modal, getIofo });

// 添加规格值：直接在列表末尾插入一个空输入框并聚焦（不弹层）
function addAttrValue(item, index) {
  item.detail.push({ id: ++detailUid, value: '' });
  nextTick(() => {
    const box = document.querySelector(`.specifications-item[data-spec-idx="${index}"]`);
    const inputs = box ? box.querySelectorAll('input[placeholder="规格值"]') : [];
    if (inputs.length) inputs[inputs.length - 1].focus();
  });
}
// 回车提交：值非空时追加下一个空规格值并聚焦，支持连续录入
function onDetailEnter(item, index, det) {
  if (det.value.trim()) addAttrValue(item, index);
}
// 删除规格
function handleRemoveRole(index) {
  formDynamic.value.spec.splice(index, 1);
  if (!formDynamic.value.spec.length) {
    formDynamic.value.spec = [];
  }
}
function handleAddRole() {
  let data = {
    value: formDynamic.value.attrsName,
    detail: [],
  };
  formDynamic.value.spec.push(data);
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
// 添加按钮
function addBtn() {
  isBtn.value = true;
}
// 详情
function getIofo(row) {
  spinShow.value = true;
  ids.value = row.id;
  ruleInfoApi(row.id)
    .then((res) => {
      formDynamic.value = res.data.info;
      // 接口返回的 detail 是字符串数组，转为带稳定 id 的对象供渲染，提交时还原
      formDynamic.value.spec = (formDynamic.value.spec || []).map((item) => ({
        value: item.value,
        detail: (item.detail || []).map((v) => ({ id: ++detailUid, value: typeof v === 'string' ? v : v.value })),
      }));
      spinShow.value = false;
    })
    .catch((res) => {
      spinShow.value = false;
      ElMessage.error(res.msg);
    });
}
// 提交
function handleSubmit(name) {
  formDynamicRef.value.validate((valid) => {
    if (valid) {
      if (formDynamic.value.spec.length === 0) {
        return ElMessage.warning('请至少添加一条商品规格！');
      }
      // 内联输入可能留空：提交前过滤空规格值并还原为字符串数组（不改动编辑中的数据）
      const spec = formDynamic.value.spec.map((item) => ({
        value: item.value,
        detail: item.detail.map((d) => (d.value || '').trim()).filter((v) => v !== ''),
      }));
      modal_loading.value = true;
      setTimeout(() => {
        ruleAddApi({ rule_name: formDynamic.value.rule_name, spec }, ids.value)
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
      }, 1200);
    } else {
      return false;
    }
  });
}
function clear() {
  formDynamicRef.value.resetFields();
  formDynamic.value.spec = [];
  isBtn.value = false;
  attrsName.value = '';
  attrsVal.value = '';
  ids.value = 0;
}
// 取消
function offAttrName() {
  isBtn.value = false;
}
// 删除
function handleRemove(index) {
  formDynamic.value.spec.splice(index, 1);
}
// 删除属性
function handleRemove2(item, index) {
  item.splice(index, 1);
}
// 添加规则名称
function createAttrName() {
  if (attrsName.value && attrsVal.value) {
    let data = {
      value: attrsName.value,
      detail: [attrsVal.value],
    };
    formDynamic.value.spec.push(data);
    var hash = {};
    formDynamic.value.spec = formDynamic.value.spec.reduce(function (item, next) {
      /* eslint-disable */
      hash[next.value] ? '' : (hash[next.value] = true && item.push(next));
      return item;
    }, []);
    attrsName.value = '';
    attrsVal.value = '';
    isBtn.value = false;
  } else {
    ElMessage.warning('请添加规格名称或规格值');
  }
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
.spec {
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
.spec:hover {
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
