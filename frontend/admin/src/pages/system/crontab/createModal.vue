<template>
  <div>
    <el-drawer
      v-model="modal"
      :title="formValidate.id ? '编辑定时任务' : '添加定时任务'"
      size="1000px"
      @closed="initData"
    >
      <el-form v-if="modal" class="pb-20" ref="formValidateRef" :model="formValidate" label-width="97px" label-colon>
        <el-form-item label="任务名称：" v-if="currentTab === '1'">
          <el-input v-model="formValidate.name" type="text" placeholder="请输入任务名称"></el-input>
        </el-form-item>
        <el-form-item label="执行周期：" required>
          <div class="cycle-row">
            <el-select v-model="formValidate.type" class="cycle-select">
              <el-option v-for="item in typeList" :key="item.value" :value="item.value" :label="item.name"></el-option>
            </el-select>
            <el-select v-if="formValidate.type == 6" v-model="formValidate.week" class="cycle-select">
              <el-option v-for="item in weekList" :key="item.value" v-bind="item"></el-option>
            </el-select>
            <div v-if="[8].includes(formValidate.type)" class="input-number-wrapper">
              <el-input-number :controls="false" v-model="formValidate.month" :max="12" :min="1"></el-input-number>
              <span class="suffix">月</span>
            </div>
            <div v-if="[4, 7, 8].includes(formValidate.type)" class="input-number-wrapper">
              <el-input-number
                :controls="false"
                v-model="formValidate.day"
                :max="formValidate.type === 4 ? 10000 : 31"
                :min="1"
              ></el-input-number>
              <span class="suffix">日</span>
            </div>
            <div v-if="[3, 4, 5, 6, 7, 8].includes(formValidate.type)" class="input-number-wrapper">
              <el-input-number :controls="false" v-model="formValidate.hour" :max="23" :min="0"></el-input-number>
              <span class="suffix">时</span>
            </div>
            <div v-if="[2, 3, 4, 5, 6, 7, 8].includes(formValidate.type)" class="input-number-wrapper">
              <el-input-number
                :controls="false"
                v-model="formValidate.minute"
                :max="formValidate.type === 2 ? 36000 : 59"
                :min="0"
              ></el-input-number>
              <span class="suffix">分</span>
            </div>
            <div v-if="[1, 5, 6, 7].includes(formValidate.type)" class="input-number-wrapper">
              <el-input-number
                :controls="false"
                v-model="formValidate.second"
                :max="formValidate.type === 1 ? 36000 : 59"
                :min="0"
              ></el-input-number>
              <span class="suffix">秒</span>
            </div>
            <div class="trip">{{ trip }}</div>
          </div>
        </el-form-item>
        <el-form-item label="任务说明：">
          <el-input
            v-model="formValidate.content"
            type="textarea"
            :autosize="{ minRows: 3, maxRows: 5 }"
            placeholder="请输入任务说明"
          ></el-input>
        </el-form-item>
        <el-form-item label="执行代码：" v-if="currentTab === '1'">
          <div ref="container" id="container" class="monaco-editor"></div>
        </el-form-item>
        <el-form-item label="开发密码：" v-if="currentTab === '1'">
          <el-input
            v-model="formValidate.password"
            type="password"
            placeholder="请输入系统开发密码，开发密码在crmeb/config/filesystem.php中修改password"
          ></el-input>
        </el-form-item>
        <el-form-item label="是否开启：">
          <el-switch :active-value="1" :inactive-value="0" v-model="formValidate.is_open" size="large">
            <template #open><span>开启</span></template>
            <template #close><span>关闭</span></template>
          </el-switch>
        </el-form-item>
      </el-form>
      <span class="dialog-footer">
        <el-button v-db-click @click="modal = false">取 消</el-button>
        <el-button type="primary" v-db-click @click="handleSubmit">提 交</el-button>
      </span>
    </el-drawer>
  </div>
</template>

<script setup>
import { ref, shallowRef, watch, nextTick } from 'vue';
import { ElMessage } from 'element-plus';
import * as monaco from '@/libs/monaco';
import { timerTask as timerTaskApi, timerInfo as timerInfoApi, saveTimer as saveTimerApi } from '@/api/system';

const props = defineProps({
  currentTab: {
    type: String,
    default: '0',
  },
});

const emit = defineEmits(['submitAsk']);

const formValidateRef = ref(null);

const modal = ref(false);
const typeList = [
  {
    name: '每隔N秒',
    value: 1,
  },
  {
    name: '每隔N分钟',
    value: 2,
  },
  {
    name: '每隔N小时',
    value: 3,
  },
  {
    name: '每隔N天',
    value: 4,
  },
  {
    name: '每天',
    value: 5,
  },
  {
    name: '每星期',
    value: 6,
  },
  {
    name: '每月',
    value: 7,
  },
  {
    name: '每年',
    value: 8,
  },
];
const task = ref({});
const loading = ref(false);
const formValidate = ref({
  name: '',
  mark: 'customTimer', //键
  content: '',
  is_open: 0,
  type: 1,
  month: 1,
  week: 1,
  day: 1,
  hour: 1,
  minute: 1,
  second: 1,
  customCode: '',
});
const trip = ref('');
const weekList = [
  { label: '周一', value: 1 },
  { label: '周二', value: 2 },
  { label: '周三', value: 3 },
  { label: '周四', value: 4 },
  { label: '周五', value: 5 },
  { label: '周六', value: 6 },
  { label: '周日', value: 7 },
];
// Monaco 实例体积庞大且内部含 DOM 引用，必须用 shallowRef 存放，避免被深度代理导致页面卡死
const editor = shallowRef(null); //当前编辑器对象

watch(
  formValidate,
  (nVal) => {
    switch (nVal.type) {
      case 1:
        trip.value = `每隔${nVal.second}秒执行一次`;
        break;
      case 2:
        trip.value = `每隔${nVal.minute}分钟执行一次`;
        break;
      case 3:
        trip.value = `每隔${nVal.hour}小时的${nVal.minute}分执行一次`;
        break;
      case 4:
        trip.value = `每隔${nVal.day}天的${nVal.hour}时${nVal.minute}分执行一次`;
        break;
      case 5:
        trip.value = `每天${nVal.hour}时${nVal.minute}分${nVal.second}秒执行一次`;
        break;
      case 6:
        trip.value = `每个星期${nVal.week}的${nVal.hour}时${nVal.minute}分${nVal.second}秒执行一次`;
        break;
      case 7:
        trip.value = `每月${nVal.day}日的${nVal.hour}时${nVal.minute}分${nVal.second}秒执行一次`;
        break;
      case 8:
        trip.value = `每年${nVal.month}月${nVal.day}日的${nVal.hour}时${nVal.minute}分${nVal.second}秒执行一次`;
        break;
    }
  },
  {
    immediate: true,
    deep: true,
  },
);

// 原 mapMutations('admin/layout', ['setCopyrightShow'])：admin/layout 模块不存在，从未生效，已省略

function modalOpen() {
  if (props.currentTab === '1') {
    initEditor();
  }
}
/**
 * 初始化编辑器
 */
function initEditor(conetnt = '') {
  try {
    nextTick(() => {
      // 初始化编辑器，确保dom已经渲染
      editor.value = monaco.editor.create(document.getElementById('container'), {
        value: conetnt, //编辑器初始显示文字
        language: 'php', //语言支持自行查阅demo
        automaticLayout: true, //自动布局
        theme: 'vs-dark', //官方自带三种主题vs, hc-black, or vs-dark
        foldingStrategy: 'indentation', // 代码可分小段折叠
        overviewRulerBorder: false, // 不要滚动条的边框
        minimap: { enabled: false },
        scrollbar: {
          vertical: 'hidden',
          horizontal: 'hidden',
        },
        wordWrap: 'on',
        autoIndent: true, // 自动布局
        tabSize: 4, // tab缩进长度
        autoClosingOvertype: 'always',
        readOnly: false,
      });
    });
  } catch (error) {
    console.log(error);
  }
}
function timerTask() {
  timerTaskApi().then((res) => {
    task.value = res.data;
  });
}
function initData(status) {
  formValidate.value = {
    name: '',
    mark: '',
    content: '',
    is_open: 0,
    type: 1,
    month: 1,
    week: 1,
    day: 1,
    hour: 1,
    minute: 1,
    second: 1,
    customCode: '',
  };
  modal.value = false;
}
function timerInfo(id) {
  if (id) {
    timerInfoApi(id).then((res) => {
      modal.value = true;
      formValidate.value = res.data;
      initEditor(res.data.customCode || '');
    });
  } else {
    modal.value = true;
    initEditor(
      "<?php\n\n//示例代码\n\n//直接写入数据库\n\\think\\facade\\Db::name('cache')->insert(['key' => 'custom_timer_' . rand(), 'result' => rand(), 'expire_time' => 0]);\n\n//调用系统方法\napp()->make(\\app\\services\\other\\CacheServices::class)->setDbCache('custom_timer_' . rand(), rand());",
    );
  }
}
// 提交
function handleSubmit() {
  if (props.currentTab === '1') {
    formValidate.value.customCode = editor.value.getValue();
    formValidate.value.mark = 'customTimer';
  }
  if (!formValidate.value.mark) {
    return ElMessage.error({
      message: '请选择任务名称',
      onClose: () => {
        // loading.value = false;
      },
    });
  }
  saveTimer(formValidate.value);
}
function taskChange(task) {
  // formValidate.value.mark = task.value;
}
function saveTimer(data) {
  saveTimerApi(data)
    .then((res) => {
      ElMessage.success({
        message: res.msg,
      });
      emit('submitAsk');
      modal.value = false;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

timerTask();

defineExpose({ timerInfo });
</script>

<style lang="scss" scoped>
.pb-20 {
  padding-bottom: 20px;
}

/* 执行周期一行：Element Plus 的 form-item 内容区为 flex，控件直接并排 */
.cycle-row {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
  width: 100%;

  .cycle-select {
    width: 110px;
  }
}

.input-number-wrapper {
  position: relative;
  width: 110px;

  :deep(.el-input-number) {
    width: 100%;
  }

  :deep(.el-input__inner) {
    padding-right: 28px;
  }

  .suffix {
    position: absolute;
    top: 0;
    right: 0;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 100%;
    font-size: 12px;
    color: var(--prev-color-text-regular);
    pointer-events: none;
  }
}

.trip {
  width: 100%;
  color: #aaa;
}

.ml30 {
  margin-left: 30px;
}
.dialog-footer {
  // 固定在底部
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 1;
  padding: 10px 20px;
  background-color: var(--prev-bg-white);
  border-top: 1px solid #e8e8e8;
  display: flex;
  justify-content: center;
}
.monaco-editor {
  width: 100%;
  border: 1px solid var(--prev-border-color-base);
  border-radius: 4px;
  height: 400px;
  overflow: hidden;
}
</style>
