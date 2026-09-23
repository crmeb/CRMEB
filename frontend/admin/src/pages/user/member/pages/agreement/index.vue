<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form label-width="85px" @submit.prevent>
        <el-form-item label="协议名称：">
          <el-input v-model="agreement.title"></el-input>
        </el-form-item>
        <el-form-item label="协议内容：">
          <WangEditor
            :content="agreement.content"
            :ai-default-prompt="aiDefaultPrompt"
            :ai-default-system-prompt="aiDefaultSystemPrompt"
            @editorContent="getEditorContent"
          ></WangEditor>
        </el-form-item>
        <el-form-item label="开启状态：">
          <el-switch :active-value="1" :inactive-value="0" v-model="agreement.status" size="large"></el-switch>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" v-db-click @click="saveAgreement">保存</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import WangEditor from '@/components/wangEditor/index.vue';
import { memberAgreement, memberAgreementSave } from '../../api';

defineOptions({ name: 'memberAgreement' });

const id = ref(0);
const spinShow = ref(false);
const agreement = ref({ title: '', content: '', status: 1, system_prompt: '', user_prompt: '' });

// AI 写作默认提示词随协议数据下发（user_prompt 为 AI 弹窗输入框默认提示词，
// system_prompt 为系统提示词，生成时按行拆分为 prompts 逐条下发）
const aiDefaultPrompt = ref('');
const aiDefaultSystemPrompt = ref('');

// created
fetchAgreement();

function getEditorContent(data) {
  agreement.value.content = data;
}

function fetchAgreement() {
  spinShow.value = true;
  memberAgreement()
    .then((res) => {
      spinShow.value = false;
      const { title, content, status, id: agreementId, system_prompt, user_prompt } = res.data;
      agreement.value = { title, content, status };
      id.value = agreementId;
      aiDefaultPrompt.value = user_prompt || '';
      aiDefaultSystemPrompt.value = system_prompt || '';
    })
    .catch((err) => {
      ElMessage.error(err);
      spinShow.value = false;
    });
}

function saveAgreement() {
  memberAgreementSave(id.value, agreement.value)
    .then((res) => {
      ElMessage.success('保存成功');
      fetchAgreement();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
</script>
