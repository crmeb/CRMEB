<template>
  <div>
    <el-drawer
      :visible.sync="modal"
      :title="formValidate.id ? '编辑定时任务' : '添加定时任务'"
      size="1000px"
      @closed="initData"
    >
      <el-form v-if="modal" class="pb-20" ref="formValidate" :model="formValidate" label-width="97px" label-colon>
        <el-form-item label="任务名称：" v-if="currentTab === '1'">
          <el-row :gutter="10">
            <el-col :span="24">
              <el-input v-model="formValidate.name" type="text" placeholder="请输入任务名称"></el-input>
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item label="执行周期：" required>
          <el-row :gutter="14">
            <el-col :span="4">
              <el-select v-model="formValidate.type">
                <el-option
                  v-for="item in typeList"
                  :key="item.value"
                  :value="item.value"
                  :label="item.name"
                ></el-option>
              </el-select>
            </el-col>
            <el-col v-if="formValidate.type == 6" :span="4">
              <el-select v-model="formValidate.week">
                <el-option v-for="item in weekList" :key="item.value" v-bind="item"></el-option>
              </el-select>
            </el-col>
            <el-col v-if="[8].includes(formValidate.type)" :span="4">
              <div class="input-number-wrapper">
                <el-input-number :controls="false" v-model="formValidate.month" :max="12" :min="1"></el-input-number>
                <span class="suffix">月</span>
              </div>
            </el-col>
            <el-col v-if="[4, 7, 8].includes(formValidate.type)" :span="4">
              <div class="input-number-wrapper">
                <el-input-number
                  :controls="false"
                  v-model="formValidate.day"
                  :max="formValidate.type === 4 ? 10000 : 31"
                  :min="1"
                ></el-input-number>
                <span class="suffix">日</span>
              </div>
            </el-col>
            <el-col v-if="[3, 4, 5, 6, 7, 8].includes(formValidate.type)" :span="4">
              <div class="input-number-wrapper">
                <el-input-number
                  controls-position="right"
                  v-model="formValidate.hour"
                  :max="23"
                  :min="0"
                ></el-input-number>
                <span class="suffix">时</span>
              </div>
            </el-col>
            <el-col v-if="[2, 3, 4, 5, 6, 7, 8].includes(formValidate.type)" :span="4">
              <div class="input-number-wrapper">
                <el-input-number
                  controls-position="right"
                  v-model="formValidate.minute"
                  :max="formValidate.type === 2 ? 36000 : 59"
                  :min="0"
                ></el-input-number>
                <span class="suffix">分</span>
              </div>
            </el-col>
            <el-col v-if="[1, 5, 6, 7].includes(formValidate.type)" :span="4">
              <div class="input-number-wrapper">
                <el-input-number
                  controls-position="right"
                  v-model="formValidate.second"
                  :max="formValidate.type === 1 ? 36000 : 59"
                  :min="0"
                ></el-input-number>
                <span class="suffix">秒</span>
              </div>
            </el-col>
          </el-row>
          <el-row :gutter="12">
            <div class="trip">{{ trip }}</div>
          </el-row>
        </el-form-item>
        <el-form-item label="任务说明：">
          <el-row :gutter="10">
            <el-col :span="24">
              <el-input
                v-model="formValidate.content"
                type="textarea"
                :autosize="{ minRows: 3, maxRows: 5 }"
                placeholder="请输入任务说明"
              ></el-input>
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item label="执行代码：" v-if="currentTab === '1'">
          <el-row :gutter="10">
            <el-col :span="24">
              <div ref="container" id="container" class="monaco-editor"></div>
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item label="开发密码：" v-if="currentTab === '1'">
          <el-row :gutter="10">
            <el-col :span="24">
              <el-input
                v-model="formValidate.password"
                type="password"
                placeholder="请输入系统开发密码，开发密码在crmeb/config/filesystem.php中修改password"
              ></el-input>
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item label="是否开启：">
          <el-row :gutter="10">
            <el-col :span="12">
              <el-switch :active-value="1" :inactive-value="0" v-model="formValidate.is_open" size="large">
                <span slot="open">开启</span>
                <span slot="close">关闭</span>
              </el-switch>
            </el-col>
          </el-row>
        </el-form-item>
      </el-form>
      <span class="dialog-footer">
        <el-button v-db-click @click="modal = false">取 消</el-button>
        <el-button type="primary" v-db-click @click="handleSubmit">提 交</el-button>
      </span>
    </el-drawer>
  </div>
</template>

<script>
import * as monaco from 'monaco-editor';
import { mapMutations } from 'vuex';
import { timerTask, timerInfo, saveTimer, updateTimer } from '@/api/system';
export default {
  props: {
    currentTab: {
      type: String,
      default: '0',
    },
  },
  data() {
    return {
      modal: false,
      typeList: [
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
      ],
      task: {},
      loading: false,
      formValidate: {
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
      },
      trip: '',
      weekList: [
        { label: '周一', value: 1 },
        { label: '周二', value: 2 },
        { label: '周三', value: 3 },
        { label: '周四', value: 4 },
        { label: '周五', value: 5 },
        { label: '周六', value: 6 },
        { label: '周日', value: 7 },
      ],
      editor: '', //当前编辑器对象
    };
  },
  watch: {
    formValidate: {
      handler(nVal, oVal) {
        switch (nVal.type) {
          case 1:
            this.trip = `每隔${nVal.second}秒执行一次`;
            break;
          case 2:
            this.trip = `每隔${nVal.minute}分钟执行一次`;
            break;
          case 3:
            this.trip = `每隔${nVal.hour}小时的${nVal.minute}分执行一次`;
            break;
          case 4:
            this.trip = `每隔${nVal.day}天的${nVal.hour}时${nVal.minute}分执行一次`;
            break;
          case 5:
            this.trip = `每天${nVal.hour}时${nVal.minute}分${nVal.second}秒执行一次`;
            break;
          case 6:
            this.trip = `每个星期${nVal.week}的${nVal.hour}时${nVal.minute}分${nVal.second}秒执行一次`;
            break;
          case 7:
            this.trip = `每月${nVal.day}日的${nVal.hour}时${nVal.minute}分${nVal.second}秒执行一次`;
            break;
          case 8:
            this.trip = `每年${nVal.month}月${nVal.day}日的${nVal.hour}时${nVal.minute}分${nVal.second}秒执行一次`;
            break;
        }
      },
      immediate: true,
      deep: true,
    },
  },
  created() {
    this.timerTask();
  },
  methods: {
    ...mapMutations('admin/layout', ['setCopyrightShow']),
    modalOpen() {
      if (this.currentTab === '1') {
        this.initEditor();
      }
    },
    /**
     * 初始化编辑器
     */
    initEditor(conetnt = '') {
      try {
        let that = this;
        that.$nextTick(() => {
          // 初始化编辑器，确保dom已经渲染
          that.editor = monaco.editor.create(document.getElementById('container'), {
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
    },
    timerTask() {
      timerTask().then((res) => {
        this.task = res.data;
      });
    },
    initData(status) {
      this.formValidate = {
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
      this.modal = false;
    },
    timerInfo(id) {
      if (id) {
        timerInfo(id).then((res) => {
          this.modal = true;
          this.formValidate = res.data;
          this.initEditor(res.data.customCode || '');
        });
      } else {
        this.modal = true;
        this.initEditor(
          "<?php\n\n//示例代码\n\n//直接写入数据库\n\\think\\facade\\Db::name('cache')->insert(['key' => 'custom_timer_' . rand(), 'result' => rand(), 'expire_time' => 0]);\n\n//调用系统方法\napp()->make(\\app\\services\\other\\CacheServices::class)->setDbCache('custom_timer_' . rand(), rand());",
        );
      }
    },
    // 提交
    handleSubmit() {
      console.log(this.currentTab);
      if (this.currentTab === '1') {
        this.formValidate.customCode = this.editor.getValue();
        this.formValidate.mark = 'customTimer';
      }
      if (!this.formValidate.mark) {
        return this.$message.error({
          message: '请选择任务名称',
          onClose: () => {
            // this.loading = false;
          },
        });
      }
      this.saveTimer(this.formValidate);
    },
    taskChange(task) {
      // this.formValidate.mark = task.value;
    },
    saveTimer(data) {
      saveTimer(data)
        .then((res) => {
          this.$message.success({
            message: res.msg,
          });
          this.$emit('submitAsk');
          this.modal = false;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.form-card {
  margin-bottom: 74px;

  ::v-deep .ivu-card-body {
    padding: 30px 0;
  }
}
.pb-20 {
  padding-bottom: 20px;
}
.btn-card {
  position: fixed;
  right: 0;
  bottom: 0;
  left: 200px;
  z-index: 2;
  text-align: center;
}

.input-number-wrapper {
  position: relative;
  display: inline-block;
  width: 100%;
  vertical-align: middle;
  line-height: normal;

  .ivu-input-number {
    width: 100%;
    padding-right: 35px;
  }

  ::v-deep .ivu-input-number-handler-wrap {
    right: 35px;
  }

  .suffix {
    position: absolute;
    top: 0;
    right: 0;
    z-index: 1;
    width: 35px;
    height: 100%;
    text-align: center;
    font-size: 12px;
    line-height: 33px;
    color: #333333;
  }
}

.trip {
  padding-left: 15px;
  color: #aaa;
}
::v-deep .el-input-number__increase,
::v-deep .el-input-number__decrease {
  display: none;
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
  background-color: #fff;
  border-top: 1px solid #e8e8e8;
  display: flex;
  justify-content: center;
}
.monaco-editor {
  border: 1px solid var(--prev-border-color-base);
  border-radius: 4px;
  height: 400px;
  overflow: hidden;
}
</style>
