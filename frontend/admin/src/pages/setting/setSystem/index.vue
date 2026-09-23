<template>
  <div v-loading="spinShow">
    <div class="i-layout-page-header header-title" v-if="!headerList.length">
      <span class="ivu-page-header-title">{{ title || $route.meta.title }}</span>
    </div>
    <div class="article-manager">
      <el-card :bordered="false" shadow="never" class="ivu-mt fromBox" :body-style="{ padding: '0 20px 20px' }">
        <el-tabs v-model="currentTab" @tab-change="changeTab" v-if="headerList.length">
          <el-tab-pane
            :icon="item.icon"
            :label="item.label"
            :name="item.value.toString()"
            v-for="(item, index) in headerList"
            :key="index"
          />
        </el-tabs>
        <el-tabs v-model="childrenId" @tab-change="changeChildTab" v-if="headerChildrenList.length">
          <el-tab-pane
            :label="item.label"
            :name="item.id.toString()"
            v-for="(item, index) in headerChildrenList"
            :key="index"
          ></el-tab-pane>
        </el-tabs>
        <form-create
          :option="option"
          :rule="rules"
          @submit="onSubmit"
          v-if="rules.length"
          style="padding-top: 20px"
        ></form-create>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import { headerListApi, dataFromApi } from '@/api/setting';
import request from '@/libs/request';
import { authLapse } from '@/utils/authLapse';
import { normalizeFormCreateRule } from '@/utils/formCreateIcon';
import { useAppStore } from '@/store/module/app';

defineOptions({ name: 'setting_setSystem' });

const route = useRoute();
const appStore = useAppStore();

const rules = ref([]);
const option = {
  form: {
    labelWidth: '120px',
  },
  submitBtn: {
    col: {
      span: 3,
      push: 3,
    },
  },
  global: {
    upload: {
      props: {
        onSuccess(res, file) {
          if (res.status === 200) {
            file.url = res.data.src;
          } else {
            ElMessage.error(res.msg);
          }
        },
      },
    },
    frame: {
      props: {
        closeBtn: false,
        okBtn: false,
      },
    },
  },
};
const spinShow = ref(false);
const FromData = ref(null);
const currentTab = ref('');
const headerList = ref([]);
const headerChildrenList = ref([]);
const childrenId = ref('');
const title = ref('');

watch(route, (to, from) => {
  headerChildrenList.value = [];
  getAllData();
});

function childrenList(index) {
  const item = headerList.value.find((item) => item.value.toString() === currentTab.value);
  if (!item) return;

  const children = Array.isArray(item.children) ? item.children : [];
  headerChildrenList.value = children;

  if (children.length) {
    childrenId.value = children[index ? index : 0].id.toString();
    getFrom(childrenId.value);
    return;
  }

  // 当前分类没有子分类时，直接读取当前分类，不再设置 childrenId 触发额外处理。
  childrenId.value = '';
  getFrom(item.id);
}
// 头部tab
function getHeader(index) {
  spinShow.value = true;
  return new Promise((resolve, reject) => {
    if (route.query.tab_id) {
      currentTab.value = route.query.tab_id;
    }
    let tab_id = route.params.tab_id ? route.params.tab_id : route.query.tab_id;
    let data = {
      pid: tab_id ? tab_id : 0,
    };
    headerListApi(data)
      .then(async (res) => {
        let config = res.data.config_tab;
        headerList.value = config;
        if (!config || !config.length) {
          currentTab.value = '';
          childrenId.value = '';
          spinShow.value = false;
          getFrom(tab_id);
          return;
        }
        if (route.query.tab_id) {
          currentTab.value = route.query.tab_id;
        } else {
          currentTab.value = config[index ? index : 0].value.toString();
        }
        childrenList(index ? 1 : 0);
        resolve(currentTab.value);
        spinShow.value = false;
      })
      .catch((err) => {
        spinShow.value = false;
        ElMessage.error(err);
      });
  });
}
// 表单
function getFrom(tabId) {
  spinShow.value = true;
  return new Promise((resolve, reject) => {
    const ids = tabId || childrenId.value || currentTab.value || route.params.tab_id;
    let data = {
      tab_id: Number(ids),
    };
    // 统一使用 setting 路由下的 edit_basics 接口，通过 tab_id 区分不同配置分类
    let url = 'setting/config/edit_basics';
    dataFromApi(data, url)
      .then(async (res) => {
        spinShow.value = false;
        if (res.data.status === false) {
          return authLapse(res.data);
        }
        FromData.value = res.data;
        const formRules = res.data.rules.map(normalizeFormCreateRule);
        addColon(formRules);
        rules.value = formRules;
        title.value = res.data.title;
        // 同步路由 meta.title：多标签/keep-alive 场景下存在多个组件实例，
        // 页面标题回退显示 $route.meta.title，需一并更新为当前配置分组名
        if (res.data.title && route.meta) {
          route.meta.title = res.data.title;
        }
      })
      .catch((res) => {
        spinShow.value = false;
        ElMessage.error(res.msg);
      });
  });
}
function addColon(arr) {
  for (let i = 0; i < arr.length; i++) {
    const c = arr[i];
    c.title += ':';
    if (c.control) {
      for (let j = 0; j < c.control.length; j++) {
        const e = c.control[j];
        if (e.rule.length) {
          addColon(e.rule);
        }
      }
    }
  }
}
async function getAllData() {
  // 同一个路由组件从一个配置分类切换到另一个分类时会被 Vue 复用，
  // 必须先清理上一个分类的选中状态，避免旧 currentTab 覆盖新 tab_id。
  currentTab.value = '';
  headerList.value = [];
  headerChildrenList.value = [];
  childrenId.value = '';
  rules.value = [];
  FromData.value = null;
  title.value = '';
  if (route.query.from === 'download') {
    await getHeader(2);
  } else {
    childrenId.value = '';
    await getHeader();
  }
}
// 选择
function changeTab() {
  childrenList();
}
function changeChildTab() {
  if (childrenId.value) {
    getFrom(childrenId.value);
  }
}
// 提交表单 group
function onSubmit(formData) {
  request({
    url: FromData.value.action,
    method: FromData.value.method,
    data: formData,
  })
    .then((res) => {
      ElMessage.success(res.msg);
      if (formData.site_name) {
        localStorage.setItem('ADMIN_TITLE', formData.site_name);
        appStore.setAdminTitle(formData.site_name);
        window.document.title = `${formData.site_name} - 系统设置`;
      }
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

getAllData();
</script>

<style scoped lang="scss">
:deep(.el-tabs__header) {
  margin: unset;
}
:deep(.el-tabs__item) {
  height: 54px !important;
  line-height: 54px !important;
}
:deep(.el-input-number) {
  width: 414px;
}
:deep(.el-input) {
  width: 414px;
}
:deep(.el-input-number .el-input__inner) {
  text-align: unset;
}
.ivu-tabs {
  margin-bottom: 18px;
}

.fromBox {
  min-height: calc(100vh - 200px);
  margin-top: 0px !important;
}

.article-manager :deep(.ivu-form-item) {
  margin-bottom: 20px !important;
}
// :deep(.form-create .el-button){
//   float: right;
// }
body :deep(.el-dialog .el-dialog__header) {
  border: none !important;
}
:deep(.el-form-item--small .el-form-item__label) {
  line-height: 14px;
  // margin-top: 10px;
}
</style>
