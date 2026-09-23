<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" v-loading="loading">
      <!-- 离线自动回复 -->
      <el-divider content-position="left">离线自动回复</el-divider>
      <el-input
        v-model="form.offline_reply"
        type="textarea"
        :rows="4"
        maxlength="200"
        show-word-limit
        placeholder="请输入客服全部离线时的提示文案"
      />
      <div class="tips-info">客服全部离线时，用户发起咨询收到的提示文案；留空则显示默认文案</div>

      <!-- 首次自动打招呼 -->
      <el-divider content-position="left">首次自动打招呼</el-divider>
      <el-input
        v-model="form.first_greeting"
        type="textarea"
        :rows="4"
        maxlength="200"
        show-word-limit
        placeholder="请输入首次接入时自动发送的欢迎语"
      />
      <div class="tips-info">
        用户首次接入客服会话时，客服自动发送的欢迎语；与「自动回复」中关键词为「欢迎语」的记录同步
      </div>

      <!-- 聊天窗右侧说明 -->
      <el-divider content-position="left">聊天窗右侧说明</el-divider>
      <WangEditor
        style="width: 100%"
        :content="form.kf_adv"
        :ai-default-prompt="AI_PROMPTS.prompt"
        :ai-default-system-prompt="AI_PROMPTS.systemPrompt"
        @editorContent="getEditorContent"
      />
      <div class="tips-info">展示在客服聊天窗口右侧的说明内容（支持图文富文本），留空则不展示</div>

      <div class="form-actions mt20">
        <el-button type="primary" v-db-click :loading="saveLoading" @click="save">保存</el-button>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import WangEditor from '@/components/wangEditor/index.vue';
import { kefuConfigGetApi, kefuConfigSaveApi } from '@/api/app';

defineOptions({ name: 'kefuConfig' });

// 「聊天窗右侧说明」AI 写作默认提示词（prompt 为 AI 弹窗输入框默认提示词，用户提示词；
// systemPrompt 为系统提示词，生成时按行拆分逐条下发，弹窗未开启系统提示词编辑区时作为隐藏默认值生效）
const AI_PROMPTS = {
  prompt:
    '请为商城客服聊天窗口撰写一段展示在右侧的客服说明，介绍服务时间、可咨询的问题范围（订单查询、物流跟踪、退换货等）和留言反馈提示，亲切友好，150字左右。',
  systemPrompt:
    '你是一名电商客服运营文案编辑，擅长撰写展示给买家看的客服服务说明。请根据用户要求直接撰写说明正文。\n' +
    '语言风格：面向买家在线阅读，亲切友好、简洁明了，可适量使用表情符号增加亲和力；\n' +
    '内容范围：围绕客服服务须知展开，覆盖服务时间、可咨询范围（订单、物流、退换货、产品咨询等）、自助查询指引、留言反馈说明即可，不写协议条款与争议解决内容；\n' +
    '输出要求：内容将展示在聊天窗口右侧窄栏，使用简短段落或 - 列表组织，全文控制在 200 字以内；使用 Markdown 格式，重点内容用 **加粗**；直接输出说明正文，不要输出任何解释说明或与正文无关的内容。',
};

const loading = ref(false);
const saveLoading = ref(false);
const form = reactive({
  offline_reply: '',
  first_greeting: '',
  kf_adv: '',
});
// 富文本编辑器实时内容（保存时使用）
const kfAdvHtml = ref('');

onMounted(() => {
  getConfig();
});

// 获取客服配置
function getConfig() {
  loading.value = true;
  kefuConfigGetApi()
    .then((res) => {
      form.offline_reply = res.data.offline_reply || '';
      form.first_greeting = res.data.first_greeting || '';
      form.kf_adv = res.data.kf_adv || '';
      kfAdvHtml.value = form.kf_adv;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    })
    .finally(() => {
      loading.value = false;
    });
}

// 富文本内容变化回调
function getEditorContent(html) {
  kfAdvHtml.value = html;
}

// 保存客服配置
function save() {
  saveLoading.value = true;
  kefuConfigSaveApi({
    offline_reply: form.offline_reply,
    first_greeting: form.first_greeting,
    kf_adv: kfAdvHtml.value,
  })
    .then((res) => {
      ElMessage.success(res.msg);
      getConfig();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    })
    .finally(() => {
      saveLoading.value = false;
    });
}
</script>
