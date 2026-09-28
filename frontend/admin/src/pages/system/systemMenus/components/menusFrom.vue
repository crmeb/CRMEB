<template>
  <div>
    <el-dialog v-model="modals" width="540px" :title="titleFrom" :close-on-click-modal="false" @closed="handleClose">
      <el-form ref="formValidateRef" :model="formValidate" label-width="80px" @submit.prevent>
        <el-row :gutter="24">
          <el-col v-bind="grid">
            <el-form-item label="类型：">
              <el-radio-group v-model="formValidate.auth_type" @input="changeAuthType">
                <el-radio :label="item.value" :value="item.value" v-for="(item, i) in optionsRadio" :key="i">
                  <span>{{ item.label }}</span>
                </el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item :label="!authType ? '接口名称：' : '按钮名称：'" prop="menu_name">
              <div class="add">
                <el-input
                  v-model="formValidate.menu_name"
                  :placeholder="!authType ? '请输入接口名称' : '请输入按钮名称'"
                >
                </el-input>
                <!-- <el-button class="ml10 df" v-show="!authType" v-db-click @click="getRuleList()" icon="ios-apps"></el-button> -->
              </div>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="父级分类：">
              <el-cascader
                :options="menuList"
                :props="{ checkStrictly: true }"
                v-model="formValidate.path"
                filterable
                style="width: 100%"
              ></el-cascader>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid" v-if="authType != 2">
            <el-form-item label="页面地址：" prop="menu_path">
              <el-input v-model="formValidate.menu_path" placeholder="请输入页面地址" @change="changeUnique">
                <template #prepend>
                  <span>{{ routePre }}</span>
                </template>
              </el-input>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid" v-if="authType == 2">
            <el-form-item label="请求方式：" prop="methods">
              <el-select v-model="formValidate.methods">
                <el-option value="GET" label="GET"></el-option>
                <el-option value="POST" label="POST"></el-option>
                <el-option value="PUT" label="PUT"></el-option>
                <el-option value="DELETE" label="DELETE"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid" v-if="authType == 2">
            <el-form-item label="接口地址：" prop="api_url">
              <el-input v-model="formValidate.api_url" placeholder="请输入接口地址" @change="changeUnique"> </el-input>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="权限标识：" prop="unique_auth">
              <el-input v-model="formValidate.unique_auth" placeholder="请输入权限标识"></el-input>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid" v-if="authType === 1 && !iconHidden">
            <el-form-item label="图标：">
              <el-input v-model="formValidate.icon" placeholder="请选择图标，点击右面图标">
                <template #append><el-button :icon="Picture" v-db-click @click="iconClick"></el-button></template>
              </el-input>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="备注：">
              <el-input v-model="formValidate.mark" placeholder="请输入备注" number></el-input>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="排序：">
              <el-input type="number" v-model="formValidate.sort" placeholder="请输入排序" number></el-input>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="状态：">
              <el-radio-group v-model="formValidate.is_show" @input="changeShow">
                <el-radio :label="item.value" :value="item.value" v-for="(item, i) in isShowRadio" :key="i">
                  <span>{{ item.label }}</span>
                </el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="handleReset">取 消</el-button>
          <el-button type="primary" v-db-click @click="handleSubmit()">提 交</el-button>
        </span></template
      >
    </el-dialog>
    <select-icon v-model="modal12" @select="iconChange" />
    <el-dialog v-model="ruleModal" width="1100px" title="权限列表" @closed="modalchange">
      <div class="search-rule">
        <el-input
          class="mr10"
          v-model="searchRule"
          placeholder="输入关键词搜索"
          clearable
          style="width: 300px"
          ref="searchRef"
        />
        <el-button type="primary" v-db-click @click="searchRules">搜索</el-button>
        <el-button v-db-click @click="init">重置</el-button>
      </div>
      <div class="rule">
        <div
          class="rule-list"
          v-show="!arrs.length || arrs.includes(index)"
          :class="{ 'select-rule': arrs.includes(index) }"
          v-for="(item, index) in ruleList"
          :key="index"
          v-db-click
          @click="selectRule(item)"
        >
          <div>接口名称：{{ item.real_name }}</div>
          <div>请求方式：{{ item.method }}</div>
          <div>接口地址：{{ item.rule }}</div>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { ElMessage } from 'element-plus';
import { addMenusApi, addMenus, getRuleList } from '@/api/systemMenus';
import { Picture } from '@element-plus/icons-vue';
import SelectIcon from '@/components/selectIcon/index.vue';
import Setting from '@/setting';

defineOptions({ name: 'menusFrom' });

const routePre = Setting.routePre;

const props = defineProps({
  formVal: {
    type: Object,
    default: null,
  },
  titleFrom: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['selectRule', 'changeMenu', 'updateRow', 'clearFrom']);

const formValidateRef = ref(null);
const searchRef = ref(null);

const arrs = ref([]);
const searchRule = ref('');
const grid = {
  xl: 22,
  lg: 22,
  md: 22,
  sm: 22,
  xs: 22,
};
const modals = ref(false);
const modal12 = ref(false);
const FromData = ref([]);
const valids = ref(false);
const list2 = ref([]);
const ruleModal = ref(false);
const ruleList = ref([]);
const authType = ref(1);
const formValidate = ref({});
const isShowRadio = [
  { value: 1, label: '开启' },
  { value: 0, label: '关闭' },
];

watch(
  () => props.formVal,
  (val) => {
    formValidate.value = val;
  },
);
watch(
  () => formValidate.value.header,
  function (n) {
    formValidate.value.is_header = n ? 1 : 0;
  },
);
watch(
  () => formValidate.value.auth_type,
  function (n) {
    if (n === undefined) {
      n = 1;
    }
    authType.value = n;
  },
);
watch(
  () => formValidate.value.data,
  function (n) {},
);
// 图标约定：一级面性、二级线性、三级无图标。
// path 是祖先 id 链（0 为"顶级菜单"占位节点，真实菜单 id 从 1 起），
// 菜单层级 = 非零祖先数 + 1；挂在二级菜单下（三级及以上）时隐藏图标并清空已选值。
const iconHidden = computed(() => {
  const path = formValidate.value.path;
  if (!Array.isArray(path)) return false;
  const parentDepth = path.filter((v) => Number(v) !== 0).length;
  return parentDepth + 1 >= 3;
});
watch(
  () => formValidate.value.path,
  () => {
    if (iconHidden.value && formValidate.value.icon) formValidate.value.icon = '';
  },
  { deep: true },
);

/* eslint-disable */
const optionsList = computed(() => {
  let a = [];
  FromData.value.map((item) => {
    if ('pid' === item.field) {
      a = item.options;
    }
  });
  return a;
});
const headerOptionsList = computed(() => {
  let a = [];
  FromData.value.map((item) => {
    if ('header' === item.field) {
      a = item.options;
    }
  });
  return a;
});
const optionsListmodule = computed(() => {
  let a = [];
  FromData.value.map((item) => {
    if ('module' === item.field) {
      a = item.options;
    }
  });
  return a;
});
const optionsRadio = computed(() => {
  let a = [];
  FromData.value.map((item) => {
    if ('auth_type' === item.field) {
      a = item.options;
    }
  });
  return a;
});
const isheaderRadio = computed(() => {
  let a = [];
  FromData.value.map((item) => {
    if ('is_header' === item.field) {
      a = item.options;
    }
  });
  return a;
});
// isShowRadio() {
//   let a = [];
//   FromData.value.map((item) => {
//     if ('is_show' === item.field) {
//       a = item.options;
//     }
//   });
//   return a;
// },
const isShowPathRadio = computed(() => {
  let a = [];
  FromData.value.map((item) => {
    if ('is_show_path' === item.field) {
      a = item.options;
    }
  });
  return a;
});
const menuList = computed(() => {
  let a = [];
  FromData.value.map((item) => {
    if ('menu_list' === item.field) {
      a = item.props.options;
    }
  });
  // 父级分类最多选到二级：顶级/一级/二级可直接选中，三级及以上仅用于展示完整路径，禁选。
  // checkStrictly 允许直接选中带下级的一、二级节点，不必选到底层叶子。
  const markDisabled = (nodes, depth) =>
    nodes.map((node) => {
      const item = { ...node };
      // value 0 是"顶级菜单"占位节点，始终可选
      if (Number(item.value) !== 0) {
        if (depth >= 3) item.disabled = true;
        if (item.children?.length) item.children = markDisabled(item.children, depth + 1);
      }
      return item;
    });
  return markDisabled(a, 1);
});

function handleClose() {
  formValidate.value = {};
}
// 获取权限列表
function getRuleListLocal() {
  getRuleList().then((res) => {
    ruleList.value = res.data;
    ruleModal.value = true;
  });
}
function modalchange() {
  arrs.value = [];
  ruleModal.value = '';
  ruleModal.value = false;
}
function changeUnique(val) {
  let value = routePre + val.target.value;
  if (value.slice(0, 1) === '/') value = value.replace('/', '');
  formValidate.value.unique_auth = value.replaceAll('/', '-');
}
function changeAuthType(val) {
  authType.value = val;
}
function changeShow(val) {
  formValidate.value.is_show = val;
}
function selectRule(data) {
  emit('selectRule', data);
  nextTick((e) => {
    ruleModal.value = false;
  });
}
// 搜索规则
function searchRules() {
  if (searchRule.value.trim()) {
    arrs.value = [];
    for (var i = 0; i < ruleList.value.length; i++) {
      if (ruleList.value[i].real_name.indexOf(searchRule.value) !== -1) {
        arrs.value.push(i);
      }
    }
  } else {
    arrs.value = [];
  }
}
function init() {
  searchRule.value = '';
  arrs.value = [];
}
function handleCreate1(val) {
  headerOptionsList.value.push({
    value: val,
    label: val,
  });
}
// 获取新增表单
function getAddFrom() {
  addMenus()
    .then(async (res) => {
      FromData.value = res.data.rules;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function iconClick() {
  modal12.value = true;
}
function iconChange(n) {
  formValidate.value.icon = n;
  modal12.value = false;
}
// 提交
function handleSubmit() {
  //判断是否选择父级分类
  if (formValidate.value.path) {
    let length = formValidate.value.path.length;
    formValidate.value.pid = formValidate.value.path[length - 1] || 0;
  }
  if (iconHidden.value) formValidate.value.icon = '';
  let data = {
    url: formValidate.value.id ? `/setting/menus/${formValidate.value.id}` : '/setting/menus',
    method: formValidate.value.id ? 'put' : 'post',
    datas: formValidate.value,
  };
  if (!formValidate.value.menu_name) {
    return ElMessage.warning('请填写菜单/按钮/接口名称');
  }
  if (!formValidate.value.menu_path && authType.value != 2) {
    return ElMessage.warning('请填写页面/按钮地址');
  }
  if (!formValidate.value.api_url && authType.value == 2) {
    return ElMessage.warning('请填写接口地址');
  }
  valids.value = true;
  addMenusApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
      modals.value = false;
      emit('changeMenu');
      // 编辑保存后传回更新数据，父组件只更新本地行不刷新列表
      if (formValidate.value.id) {
        emit('updateRow', { ...formValidate.value });
      }
      getAddFrom();
      // useMenusStore().getMenusNavList();
    })
    .catch((res) => {
      valids.value = false;
      ElMessage.error(res.msg);
    });
}
function handleReset() {
  modals.value = false;
  formValidateRef.value.resetFields();
  emit('clearFrom');
}

defineExpose({ modals, valids, getAddFrom });
</script>

<style scoped>
.search-rule {
  display: flex;
  align-items: center;
  padding: 10px;
  background-color: #f2f2f2;
}

.rule {
  display: flex;
  flex-wrap: wrap;
  max-height: 700px;
  overflow: scroll;
}

/*定义滚动条高宽及背景 高宽分别对应横竖滚动条的尺寸*/
.rule::-webkit-scrollbar {
  width: 10px;
  height: 10px;
  background-color: #f5f5f5;
}

/*定义滚动条轨道 内阴影+圆角*/
.rule::-webkit-scrollbar-track {
  border-radius: 4px;
  background-color: #f5f5f5;
}

/*定义滑块 内阴影+圆角*/
.rule::-webkit-scrollbar-thumb {
  border-radius: 4px;
  background-color: #555;
}

.rule-list {
  background-color: #f8f5f5;
  width: 32%;
  margin: 5px;
  border-radius: 3px;
  padding: 10px;
  color: var(--prev-color-text-regular);
  cursor: pointer;
  transition: all 0.1s;
}

.rule-list:hover {
  background-color: #c5d1dd;
}

.rule-list div {
  white-space: nowrap;
}

.select-rule {
  background-color: #c5d1dd;
}

.add {
  display: flex;
  align-items: center;
}

.df {
  display: flex;
  justify-content: center;
}
</style>
