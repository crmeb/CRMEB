<template>
  <div class="agreemant">
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: '0 20px' }">
      <div>
        <el-tabs v-model="currentTab">
          <el-tab-pane :label="item.label" :name="item.value" v-for="(item, index) in headerList" :key="index" />
        </el-tabs>
      </div>
    </el-card>

    <el-row v-loading="spinShow" class="content">
      <el-col :span="16">
        <WangEditor
          v-if="currentAgreement"
          :key="currentAgreement.id"
          style="width: 100%"
          :content="formValidate.content"
          :ai-default-prompt="aiDefaultPrompt"
          :ai-default-system-prompt="aiDefaultSystemPrompt"
          @editorContent="getEditorContent"
        ></WangEditor>
      </el-col>
      <el-col :span="6" style="width: 33%">
        <div class="ifam">
          <div class="content" v-html="content"></div>
        </div>
      </el-col>
    </el-row>

    <el-card :bordered="false" shadow="never" class="fixed-card" :style="{ left: `${fixBottomWidth}` }">
      <div class="acea-row row-center">
        <el-button
          class="bnt"
          type="primary"
          v-db-click
          @click="save"
          :loading="loadingExist"
          :disabled="spinShow || !formValidate.id"
          >保存</el-button
        >
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { ElMessage } from 'element-plus';
import WangEditor from '@/components/wangEditor/index.vue';
import { getAgreementList, getAgreements, setAgreements } from '@/api/system';
import { useThemeConfigStore } from '@/store/module/themeConfig';

const themeConfigStore = useThemeConfigStore();

const loadingExist = ref(false);
const currentTab = ref('');
// 协议列表由后端动态返回（系统协议 + 已安装插件协议），
// 插件协议随插件安装/卸载自动出现与移除，无需前端维护枚举
const headerList = ref([]);
// 列表用于定位协议类型；内容与 AI 提示词在切换时重新获取
const agreementData = ref([]);

// 当前协议的 AI 写作默认提示词（随协议数据下发）：
// prompt 为 AI 弹窗输入框默认提示词（用户提示词），systemPrompt 为系统提示词
// （生成时按行拆分为 prompts 逐条下发，页面未开启系统提示词编辑区时作为隐藏默认值生效）
const aiDefaultPrompt = computed(() => currentAgreement.value?.user_prompt || '');
const aiDefaultSystemPrompt = computed(() => currentAgreement.value?.system_prompt || '');

const currentAgreement = ref(null);
let detailRequestId = 0;

const formValidate = reactive({
  id: 0,
  type: 0,
  title: '',
  content: '',
});
const content = ref('');
const spinShow = ref(false);

// 设置是否显示 tagsView
const fixBottomWidth = computed(() => {
  let { layout, isCollapse } = themeConfigStore.themeConfig;
  let w;
  if (['columns'].includes(layout)) {
    if (isCollapse) {
      w = '85px';
    } else {
      w = '265px';
    }
  } else if (['classic'].includes(layout)) {
    if (isCollapse) {
      w = '85px';
    } else {
      w = '180px';
    }
  } else if (['defaults', 'classic'].includes(layout)) {
    if (isCollapse) {
      w = '64px';
    } else {
      w = '180px';
    }
  } else {
    w = '0px';
  }
  return w;
});

// 拉取协议列表并填充 tabs；首个协议默认选中
function fetchList() {
  spinShow.value = true;
  getAgreementList()
    .then((res) => {
      spinShow.value = false;
      agreementData.value = res.data || [];
      headerList.value = agreementData.value.map((item) => ({
        label: item.plugin_name ? `${item.title}（${item.plugin_name}）` : item.title,
        value: String(item.id),
      }));
      // 保持当前选中项，否则默认选中第一个
      if (!headerList.value.some((item) => item.value === currentTab.value)) {
        currentTab.value = headerList.value[0]?.value || '';
      }
    })
    .catch(() => {
      spinShow.value = false;
    });
}

// 将当前选中协议数据填充到表单与预览
function fillForm() {
  const info = currentAgreement.value;
  if (!info) {
    formValidate.id = 0;
    formValidate.type = 0;
    formValidate.title = '';
    formValidate.content = '';
    content.value = '';
    return;
  }
  formValidate.id = info.id;
  formValidate.type = info.type;
  formValidate.title = info.title;
  formValidate.content = info.content;
  content.value = info.content;
}

async function changeTab(tabId) {
  const requestId = ++detailRequestId;
  const selected = agreementData.value.find((item) => String(item.id) === tabId);
  currentAgreement.value = null;
  fillForm();
  if (!selected) return;
  spinShow.value = true;
  try {
    const res = await getAgreements(selected.type);
    if (requestId !== detailRequestId) return;
    if (!res.data?.id) throw new Error('协议不存在，请刷新页面');
    currentAgreement.value = res.data;
    fillForm();
  } catch (err) {
    if (requestId === detailRequestId) ElMessage.error(err.msg || err.message || '协议加载失败');
  } finally {
    if (requestId === detailRequestId) spinShow.value = false;
  }
}

function save() {
  if (spinShow.value || loadingExist.value || !formValidate.id) return;
  loadingExist.value = true;
  formValidate.content = content.value;
  setAgreements({
    ...formValidate,
    system_prompt: aiDefaultSystemPrompt.value,
    user_prompt: aiDefaultPrompt.value,
  })
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    })
    .finally(() => {
      loadingExist.value = false;
    });
}
function getEditorContent(html) {
  content.value = html;
}

watch(currentTab, changeTab);
fetchList();
</script>

<style lang="scss" scoped>
:deep(.el-tabs__item) {
  height: 54px !important;
  line-height: 54px !important;
}
.agreemant {
  background-color: var(--prev-bg-white);
}
.content {
  padding: 10px 16px;
}
.ifam {
  width: 344px;
  height: 644px;
  background: url('../../../assets/images/ag-phone.png') no-repeat center top;
  background-size: 344px 644px;
  padding: 40px 20px;
  padding-top: 50px;
  margin: 0 auto 0 20px;

  /* 深色模式：白色手机壳图改为深色面板，与富文本编辑区同观感 */
  html[data-theme='dark'] & {
    background: var(--prev-bg-white);
    border: 1px solid var(--prev-border-color-light);
    border-radius: 24px;
  }

  .content {
    height: 560px;
    overflow: hidden;
    scrollbar-width: none; /* firefox */
    -ms-overflow-style: none; /* IE 10+ */
    overflow-x: hidden;
    overflow-y: auto;
  }
  .content::-webkit-scrollbar {
    display: none; /* Chrome Safari */
  }
}
.new_tab {
  :deep(.ivu-tabs-nav .ivu-tabs-tab) {
    padding: 4px 16px 20px !important;
    font-weight: 500;
  }
}
</style>
