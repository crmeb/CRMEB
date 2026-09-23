<template>
  <div>
    <div class="i-layout-page-header header-title">
      <div class="header-main">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
        <span class="header-description">
          <el-icon class="header-description-icon"><InfoFilled /></el-icon>
          维护分销代理商协议内容，支持 AI 辅助写作，代理商入驻申请时展示。
        </span>
      </div>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form label-width="85px" @submit.prevent v-loading="spinShow">
        <el-form-item label="协议内容：">
          <WangEditor
            :content="agreement.content"
            :ai-default-prompt="aiDefaultPrompt"
            :ai-default-system-prompt="aiDefaultSystemPrompt"
            @editorContent="getEditorContent"
          ></WangEditor>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" v-db-click @click="memberAgreementSave">保存</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import WangEditor from '@/components/wangEditor/index.vue';
import { agentAgreement, agentAgreementSave } from '@/api/user';

const ueConfig = {
  autoHeightEnabled: false,
  initialFrameHeight: 500,
  initialFrameWidth: '100%',
  UEDITOR_HOME_URL: '/UEditor/',
  serverUrl: '',
};
const agreement = ref({
  content: '',
  id: 0,
});
const spinShow = ref(false);

// AI 写作默认提示词随协议数据下发（user_prompt 为 AI 弹窗输入框默认提示词，
// system_prompt 为系统提示词，生成时按行拆分为 prompts 逐条下发）
const aiDefaultPrompt = ref('');
const aiDefaultSystemPrompt = ref('');

function getEditorContent(data) {
  agreement.value.content = data;
}
function memberAgreement() {
  spinShow.value = true;
  agentAgreement()
    .then((res) => {
      spinShow.value = false;
      const { title, content, status, id, system_prompt, user_prompt } = res.data;
      agreement.value.content = content;
      agreement.value.id = id || 0;
      aiDefaultPrompt.value = user_prompt || '';
      aiDefaultSystemPrompt.value = system_prompt || '';
    })
    .catch((err) => {
      ElMessage.error(err.msg);
      spinShow.value = false;
    });
}
// 保存
function memberAgreementSave() {
  agentAgreementSave(agreement.value)
    .then((res) => {
      ElMessage.success('保存成功');
      memberAgreement();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

memberAgreement();
</script>

<style lang="scss" scoped>
:deep(.ivu-form-item-content) {
  line-height: unset !important;
}
</style>
