<template>
  <div class="edit">
    <pages-header
      v-if="!props.drawerMode"
      ref="pageHeader"
      :title="$route.meta.title"
      :backUrl="backUrl"
    ></pages-header>
    <div class="tabs mt16">
      <el-row :gutter="32">
        <el-col :span="32" class="demo-tabs-style1" style="padding: 16px">
          <el-tabs v-if="tabsList.length" v-model="tagName" @tab-change="changeTabs">
            <el-tab-pane v-for="(item, index) in tabsList" :key="index" :name="item.slot" :label="item.title">
              <el-form class="form-sty" ref="formDataRef" :model="formData" :rules="ruleValidate" label-width="85px">
                <el-form-item label="通知场景：">
                  <el-input v-model="formData.scene_title" style="width: 500px" />
                </el-form-item>
                <!-- 渠道标题存为 template_title：仅钉钉用作机器人消息标题；站内信/邮件有独立标题字段，其余渠道发送时不使用 -->
                <el-form-item v-if="item.slot === 'is_dingtalk'" label="渠道标题：">
                  <el-input v-model="formData.channel_title" style="width: 500px" />
                </el-form-item>
                <div v-if="item.slot === 'is_system' && !loading">
                  <el-form-item label="通知标题：">
                    <el-input
                      v-model="formData.system_title"
                      placeholder="请输入通知标题"
                      style="width: 500px"
                    ></el-input>
                  </el-form-item>
                  <el-form-item label="通知内容：">
                    <div class="content">
                      <el-input
                        ref="contentInput"
                        id="system_text"
                        v-model="formData.system_text"
                        type="textarea"
                        :autosize="{ minRows: 5, maxRows: 8 }"
                        placeholder="请输入通知内容"
                        style="width: 500px"
                      >
                      </el-input>
                      <variable-picker :list="variableList" @select="insertVariable" />
                    </div>
                    <div class="tip" v-if="variableList.length">可点击右下角 { } 图标插入自定义变量</div>
                  </el-form-item>
                  <el-form-item label="状态：" prop="is_system">
                    <el-radio-group v-model="formData.is_system">
                      <el-radio :label="1" :value="1">开启</el-radio>
                      <el-radio :label="2" :value="2">关闭</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </div>
                <div v-if="item.slot === 'is_sms' && !loading">
                  <el-form-item label="短信模版ID：">
                    <el-input v-model="formData.sms_id" placeholder="短信模版ID" style="width: 500px"></el-input>
                  </el-form-item>
                  <el-form-item label="通知内容：">
                    <div class="content">
                      <el-input
                        ref="contentInput"
                        id="sms_text"
                        v-model="formData.sms_text"
                        type="textarea"
                        :autosize="{ minRows: 5, maxRows: 8 }"
                        placeholder="请输入通知内容"
                        style="width: 500px"
                      ></el-input>
                      <variable-picker :list="variableList" @select="insertVariable" />
                    </div>
                    <div class="tip" v-if="variableList.length">可点击右下角 { } 图标插入自定义变量</div>
                  </el-form-item>
                  <el-form-item label="状态：" prop="is_sms">
                    <el-radio-group v-model="formData.is_sms">
                      <el-radio :label="1" :value="1">开启</el-radio>
                      <el-radio :label="2" :value="2" :disabled="formData.mark === 'verify_code'">关闭</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </div>
                <div v-else-if="item.slot === 'is_wechat' && !loading">
                  <el-form-item label="模板编号：">
                    <el-input
                      v-model="formData.tempkey"
                      placeholder="请输入通模板编号"
                      style="width: 500px"
                      disabled
                    ></el-input>
                  </el-form-item>
                  <el-form-item label="模板ID：">
                    <el-input v-model="formData.tempid" placeholder="请输入模板ID" style="width: 500px"></el-input>
                  </el-form-item>
                  <el-form-item label="模板：">
                    <div class="content">
                      <el-input
                        ref="contentInput"
                        v-model="formData.content"
                        type="textarea"
                        :autosize="{ minRows: 5, maxRows: 8 }"
                        placeholder="请输入模板"
                        style="width: 500px"
                        @input="handleContentChange"
                      ></el-input>
                    </div>
                    <div class="tip">复制微信模板详情内容进来，会自动解析出下方字段</div>
                  </el-form-item>
                  <el-form-item label="字段：">
                    <keys-list
                      :key-list="keyList"
                      :variableList="variableList"
                      @add="handleAdd"
                      @remove="handleRemove"
                    />
                  </el-form-item>
                  <el-form-item label="跳转链接：">
                    <div class="content">
                      <el-input
                        v-model="formData.wechat_link"
                        placeholder="请输入模版跳转链接，可携带参数"
                        style="width: 500px"
                      ></el-input>
                      <el-button class="ml10" v-db-click @click="openLinkPicker('wechat_link')">选择链接</el-button>
                    </div>
                  </el-form-item>
                  <el-form-item label="跳转小程序：" prop="wechat_to_routine">
                    <el-radio-group v-model="formData.wechat_to_routine">
                      <el-radio :label="1" :value="1">开启</el-radio>
                      <el-radio :label="0" :value="0">关闭</el-radio>
                    </el-radio-group>
                    <div class="tip">开启之后，点击模版消息，跳转小程序对应的页面，需要小程序已经审核上线才可使用</div>
                  </el-form-item>
                  <el-form-item label="状态：" prop="is_wechat">
                    <el-radio-group v-model="formData.is_wechat">
                      <el-radio :label="1" :value="1">开启</el-radio>
                      <el-radio :label="2" :value="2">关闭</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </div>
                <div v-else-if="item.slot === 'is_routine' && !loading">
                  <el-form-item label="模板编号：">
                    <el-input
                      v-model="formData.tempkey"
                      placeholder="请输入通模板编号"
                      style="width: 500px"
                      disabled
                    ></el-input>
                  </el-form-item>
                  <el-form-item label="模板ID：">
                    <el-input v-model="formData.tempid" placeholder="请输入模板ID" style="width: 500px"></el-input>
                  </el-form-item>
                  <el-form-item label="模板：">
                    <div class="content">
                      <el-input
                        ref="contentInput"
                        v-model="formData.content"
                        type="textarea"
                        :autosize="{ minRows: 5, maxRows: 8 }"
                        placeholder="请输入模板"
                        style="width: 500px"
                        @input="handleContentChange"
                      ></el-input>
                    </div>
                    <div class="tip">复制小程序模板详情内容进来，会自动解析出下方字段</div>
                  </el-form-item>
                  <el-form-item label="字段：">
                    <keys-list
                      :key-list="keyList"
                      :variableList="variableList"
                      @add="handleAdd"
                      @remove="handleRemove"
                    />
                  </el-form-item>
                  <el-form-item label="跳转链接：">
                    <div class="content">
                      <el-input
                        v-model="formData.routine_link"
                        placeholder="请输入模版跳转链接，可携带参数"
                        style="width: 500px"
                      ></el-input>
                      <el-button class="ml10" v-db-click @click="openLinkPicker('routine_link')">选择链接</el-button>
                    </div>
                  </el-form-item>
                  <el-form-item label="状态：" prop="is_routine">
                    <el-radio-group v-model="formData.is_routine">
                      <el-radio :label="1" :value="1">开启</el-radio>
                      <el-radio :label="2" :value="2">关闭</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </div>

                <div v-else-if="item.slot === 'is_ent_wechat' && !loading">
                  <el-form-item label="通知内容：">
                    <div class="content">
                      <el-input
                        ref="contentInput"
                        id="ent_wechat_text"
                        v-model="formData.ent_wechat_text"
                        type="textarea"
                        :autosize="{ minRows: 5, maxRows: 8 }"
                        placeholder="请输入通知内容"
                        style="width: 500px"
                      ></el-input>
                      <variable-picker :list="variableList" @select="insertVariable" />
                    </div>
                    <div class="tip" v-if="variableList.length">可点击右下角 { } 图标插入自定义变量</div>
                  </el-form-item>
                  <el-form-item label="机器人链接：">
                    <div class="content">
                      <el-input v-model="formData.url" placeholder="请输入机器人链接" style="width: 500px"></el-input>
                    </div>
                  </el-form-item>
                  <el-form-item label="状态：" prop="is_ent_wechat">
                    <el-radio-group v-model="formData.is_ent_wechat">
                      <el-radio :label="1" :value="1">开启</el-radio>
                      <el-radio :label="2" :value="2">关闭</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </div>
                <div v-else-if="item.slot === 'is_dingtalk' && !loading">
                  <el-form-item label="通知内容：">
                    <div class="content">
                      <el-input
                        ref="contentInput"
                        id="dingtalk_text"
                        v-model="formData.dingtalk_text"
                        type="textarea"
                        :autosize="{ minRows: 5, maxRows: 8 }"
                        placeholder="请输入通知内容"
                        style="width: 500px"
                      ></el-input>
                      <variable-picker :list="variableList" @select="insertVariable" />
                    </div>
                    <div class="tip" v-if="variableList.length">可点击右下角 { } 图标插入自定义变量</div>
                  </el-form-item>
                  <el-form-item label="Webhook地址：">
                    <el-input
                      v-model="formData.webhook"
                      placeholder="请输入钉钉机器人 Webhook 地址"
                      style="width: 500px"
                    ></el-input>
                  </el-form-item>
                  <el-form-item label="状态：" prop="is_dingtalk">
                    <el-radio-group v-model="formData.is_dingtalk">
                      <el-radio :label="1" :value="1">开启</el-radio>
                      <el-radio :label="2" :value="2">关闭</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </div>
                <div v-else-if="item.slot === 'is_email' && !loading">
                  <el-form-item label="邮件标题：">
                    <el-input
                      v-model="formData.email_title"
                      placeholder="请输入邮件标题"
                      style="width: 500px"
                    ></el-input>
                  </el-form-item>
                  <el-form-item label="邮件内容：">
                    <div class="content">
                      <el-input
                        ref="contentInput"
                        id="email_text"
                        v-model="formData.email_text"
                        type="textarea"
                        :autosize="{ minRows: 5, maxRows: 8 }"
                        placeholder="请输入邮件内容"
                        style="width: 500px"
                      ></el-input>
                      <variable-picker :list="variableList" @select="insertVariable" />
                    </div>
                    <div class="tip" v-if="variableList.length">可点击右下角 { } 图标插入自定义变量</div>
                  </el-form-item>
                  <el-form-item label="状态：" prop="is_email">
                    <el-radio-group v-model="formData.is_email">
                      <el-radio :label="1" :value="1">开启</el-radio>
                      <el-radio :label="2" :value="2">关闭</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </div>
                <!-- 渠道未就绪提醒：置于各输入框下方，无 label 的表单项与输入框左对齐 -->
                <el-form-item v-if="formData.readiness_reasons?.length">
                  <div class="tips-info">{{ formData.readiness_reasons.join('、') }}</div>
                </el-form-item>
                <el-form-item>
                  <el-button type="primary" v-db-click @click="handleSubmit()">提交</el-button>
                </el-form-item>
              </el-form>
            </el-tab-pane>
          </el-tabs>
          <el-empty v-else-if="!loading" description="该消息暂无可用的通知渠道"></el-empty>
        </el-col>
      </el-row>
    </div>
    <!-- 选择链接弹窗：与 DIY 页面共用组件，结果回填到当前操作的跳转链接字段 -->
    <linkaddress ref="linkAddressRef" @linkUrl="handleLinkSelected" />
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { getNotificationInfo, getNotificationSave } from '@/api/notification.js';
import VariablePicker from './VariablePicker.vue';
import keysList from './components/keysList.vue';
import linkaddress from '@/components/linkaddress';
import Setting from '@/setting';

const route = useRoute();
const router = useRouter();

const props = defineProps({
  drawerMode: { type: Boolean, default: false },
  notificationId: { type: [String, Number], default: 0 },
  from: { type: String, default: '' },
});
const emit = defineEmits(['close', 'saved']);

const pageHeader = ref(null);
const formDataRef = ref(null);
const contentInput = ref(null);

const tabs = [
  {
    title: '系统通知',
    slot: 'is_system',
  },
  {
    title: '短信通知',
    slot: 'is_sms',
  },
  {
    title: '微信模板消息',
    slot: 'is_wechat',
  },
  {
    title: '微信小程序提醒',
    slot: 'is_routine',
  },
  {
    title: '企业微信',
    slot: 'is_ent_wechat',
  },
  {
    title: '钉钉',
    slot: 'is_dingtalk',
  },
  {
    title: '邮件',
    slot: 'is_email',
  },
];
const tabsList = ref([]);
const formData = ref({});
const id = ref(0);
const loading = ref(true);
const tagName = ref('is_system');
const ruleValidate = {
  name: [
    {
      required: true,
      message: '请输入通知场景',
      trigger: 'blur',
    },
  ],
  title: [
    {
      required: true,
      message: '请输入通知场景',
      trigger: 'blur',
    },
  ],
  content: [
    {
      required: true,
      message: '请输入通知内容',
      trigger: 'blur',
    },
  ],
};
const keyList = ref([]);
const variableList = ref([]); // 可用变量列表，来自 eb_notice_config.variable，点击插入到模板
const reqToken = ref(0); // 请求令牌，仅最后一次请求的结果允许写入页面状态

// 返回地址：根据来源列表页动态计算，默认回会员通知页
// 编辑页有会员通知、平台通知两个入口，固定地址无法兼顾
const backUrl = computed(() => {
  const from = props.from || route.query.from;
  // 仅允许白名单来源，防止恶意参数
  const allow = { member: 'notification/member', platform: 'notification/platform' };
  const sub = allow[from] || 'notification/member';
  return Setting.routePre + '/setting/' + sub;
});

function changeTabs() {
  getData(id.value, tagName.value);
}
function getData(pid, name, init) {
  loading.value = true;
  formData.value = {};
  // 每次发起请求令牌自增，旧响应到达时直接丢弃，防止慢响应覆盖新渠道数据
  const token = ++reqToken.value;
  getNotificationInfo(pid, name)
    .then((res) => {
      if (token !== reqToken.value) return; // 已过期响应，丢弃
      // 数据结构校验：后端返回空数组/缺 channels（如 id 无效）时给出明确提示，避免页面无提示空白
      if (!res.data || Array.isArray(res.data) || !res.data.channels) {
        loading.value = false;
        ElMessage.error('通知配置不存在，请从消息管理列表重新进入');
        return;
      }
      const channels = res.data.channels || {};
      if (!tabsList.value.length) {
        tabs.map((v) => {
          // 关闭的模板也可配置；未安装的渠道不由后端注册中心展示。
          if (channels[v.slot]) {
            tabsList.value.push(v);
          }
        });
      }
      // 首次进入选择消息实际登记的第一个渠道，而非固定从系统通知开始。
      if (init) {
        const target = tabsList.value.length ? tabsList.value[0].slot : '';
        if (target && target !== res.data.selected_channel) {
          tagName.value = target;
          getData(pid, target);
          return;
        }
        if (target) tagName.value = target;
      }
      formData.value = res.data;
      formData.value.type = res.data.selected_channel; // 当前渠道标识
      formData.value.id = pid;
      // key_list 后端为关联数组 {模板字段: 业务变量}，前端转为数组 [{key, value}] 供 keysList 组件编辑
      const kl = res.data.key_list || {};
      keyList.value = Array.isArray(kl)
        ? kl.map((i) => ({ key: i.key || '', value: i.value || '' }))
        : Object.keys(kl).map((k) => ({ key: k, value: kl[k] || '' }));
      variableList.value = res.data.variables || [];
      loading.value = false;
    })
    .catch((err) => {
      if (token !== reqToken.value) return; // 已过期响应，丢弃
      // 修复：失败时必须复位 loading 并关闭渲染锁，否则表单永久空白
      loading.value = false;
      ElMessage.error(typeof err === 'object' && err.msg ? err.msg : err || '加载失败');
    });
}

function handleSubmit() {
  formData.value.status = formData.value[tagName.value] ?? formData.value.status;
  // key_list 提交时由数组 [{key,value}] 转回关联数组 {模板字段: 业务变量}，过滤掉字段名为空的行
  const keyObj = {};
  keyList.value.forEach((item) => {
    if (item.key && item.key.trim()) {
      keyObj[item.key.trim()] = item.value || '';
    }
  });
  formData.value.key_list = keyObj;
  getNotificationSave(formData.value)
    .then((res) => {
      ElMessage.success('设置成功');
      getData(id.value, tagName.value);
      emit('saved');
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
function handleReset() {
  emit('close');
}
// 点击变量标签，将 {key} 插入到当前渠道通知内容输入框的光标位置
function insertVariable(key) {
  // 当前 tab 渠道 → 对应的 v-model 字段名
  const fieldMap = {
    is_system: 'system_text',
    is_sms: 'sms_text',
    is_wechat: 'content',
    is_routine: 'content',
    is_ent_wechat: 'ent_wechat_text',
    is_dingtalk: 'dingtalk_text',
    is_email: 'email_text',
  };
  const field = fieldMap[tagName.value];
  if (!field) return;
  const insertText = '{' + key + '}';
  const cur = formData.value[field] || '';
  // 优先在光标位置插入，取当前渲染的内容输入框 ref
  const inputComp = contentInput.value;
  if (inputComp && inputComp.$el) {
    const textarea = inputComp.$el.querySelector('textarea');
    if (textarea) {
      // 输入框未聚焦时（如刚切换 tab），追加到末尾，避免在开头插入
      const focused = document.activeElement === textarea;
      const start = focused && textarea.selectionStart != null ? textarea.selectionStart : cur.length;
      const end = focused && textarea.selectionEnd != null ? textarea.selectionEnd : cur.length;
      const next = cur.slice(0, start) + insertText + cur.slice(end);
      formData.value[field] = next;
      const pos = start + insertText.length;
      nextTick(() => {
        textarea.focus();
        textarea.setSelectionRange(pos, pos);
      });
      return;
    }
  }
  // 兜底：追加到末尾
  formData.value[field] = cur + insertText;
}
// 模板内容变化时，从 {{字段名.data}} 自动提取字段名生成 keyList
// 保留已选的变量映射，同时保留用户手动添加的字段（不在模板中的）
function handleContentChange(e) {
  const regex = /{{(.*?)\./g;
  const newKeys = [];
  let match;
  while ((match = regex.exec(e))) {
    const k = match[1].trim();
    // 去重，避免同一字段重复出现
    if (k && !newKeys.includes(k)) {
      newKeys.push(k);
    }
  }
  // 保留已有映射，区分手动添加的空行（key 为空）
  const oldMap = {};
  const manualItems = [];
  keyList.value.forEach((item) => {
    if (item.key) {
      oldMap[item.key] = item.value;
    } else {
      // 保留用户刚点"新增"但还没填 key 的空行
      manualItems.push(item);
    }
  });
  // 模板提取的字段，保留已选变量
  const result = newKeys.map((k) => ({ key: k, value: oldMap[k] || '' }));
  // 保留用户手动添加但不在模板中的字段
  newKeys.forEach((k) => delete oldMap[k]);
  Object.keys(oldMap).forEach((k) => result.push({ key: k, value: oldMap[k] }));
  // 追加空行
  manualItems.forEach((item) => result.push(item));
  keyList.value = result;
}
// 删除一条模板字段映射
function handleRemove(index) {
  keyList.value.splice(index, 1);
}
// 新增一条模板字段映射
function handleAdd() {
  keyList.value.push({ key: '', value: '' });
}

// 跳转链接选择：记录当前操作的字段（公众号/小程序），弹窗确认后回填对应链接
const linkAddressRef = ref(null);
const linkTargetField = ref('');
function openLinkPicker(field) {
  linkTargetField.value = field;
  linkAddressRef.value.modals = true;
}
function handleLinkSelected(url) {
  if (linkTargetField.value) {
    formData.value[linkTargetField.value] = url;
  }
}

id.value = props.drawerMode ? props.notificationId : route.query.id;
// 直接访问编辑页未携带 id 参数时，提示并返回来源列表页，避免整页空白
if (!id.value) {
  ElMessage.error('缺少消息ID参数，请从消息管理列表进入');
  if (!props.drawerMode) router.replace(backUrl.value);
} else {
  getData(id.value, '', 1);
}
</script>

<style scoped lang="scss">
.edit {
}
.header_top {
  margin-bottom: 10px;
}
.demo-tabs-style1 > .ivu-tabs-card > .ivu-tabs-content {
  height: 120px;
  margin-top: -16px;
}

.demo-tabs-style1 > .ivu-tabs-card > .ivu-tabs-content > .ivu-tabs-tabpane {
  background: #fff;
  padding: 16px;
}

.demo-tabs-style1 > .ivu-tabs.ivu-tabs-card > .ivu-tabs-bar .ivu-tabs-tab {
  border-color: transparent;
}

.demo-tabs-style1 > .ivu-tabs-card > .ivu-tabs-bar .ivu-tabs-tab-active {
  border-color: #fff;
}

.tabs {
  padding: 0 30px;
  background-color: #fff;
}

.trip {
  color: rgb(146, 139, 139);
  background-color: #f2f2f2;
  margin-left: 80px;
  border-radius: 4px;
  padding: 15px;
}

.content {
  display: flex;
  position: relative;
}

.form-sty {
  margin-top: 20px;
}
</style>
