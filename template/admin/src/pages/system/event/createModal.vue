<template>
  <div>
    <el-drawer
      :visible.sync="modal"
      :title="formValidate.id ? '编辑事件' : '添加事件'"
      size="1000px"
      @closed="initData"
    >
      <el-form v-if="modal" class="pb-20" ref="formValidate" :model="formValidate" label-width="97px" label-colon>
        <el-form-item label="事件名称：" required>
          <el-row :gutter="16">
            <el-col :span="20">
              <el-input v-model="formValidate.name" placeholder="请输入事件名称"></el-input>
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item label="事件类型：" required>
          <el-row :gutter="16">
            <el-col :span="20">
              <el-select v-model="formValidate.mark" @change="taskChange">
                <el-option v-for="(item, name) in task" :key="name" :value="item.value" :label="item.label"></el-option>
              </el-select>
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item label="事件说明：">
          <el-row :gutter="10">
            <el-col :span="24">
              <el-input
                v-model="formValidate.content"
                type="textarea"
                :autosize="{ minRows: 3, maxRows: 5 }"
                placeholder="请输入事件说明"
              ></el-input>
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item label="执行代码：">
          <el-row :gutter="10">
            <el-col :span="24">
              <div ref="container" id="container" class="monaco-editor"></div>
              <!-- <div class="copy-tag">
                <el-tag
                  class="item"
                  size="small"
                  v-for="(i, k, index) in copyData"
                  :key="index"
                  v-db-click
                  @click="onCopy(k)"
                >
                  {{ i }}
                </el-tag>
              </div> -->
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item label="可用参数：" v-if="copyData">
          <el-row :gutter="10">
            <el-col :span="24">
              <el-input
                class="text-area"
                v-model="copyData"
                type="textarea"
                :autosize="{ minRows: 7, maxRows: 7 }"
                placeholder="请输入事件说明"
                readonly
              ></el-input>
              <!-- <span class="text-area">{{ copyData }}</span> -->
            </el-col>
          </el-row>
        </el-form-item>

        <el-form-item label="开发密码：" required>
          <el-row :gutter="10">
            <el-col :span="24">
              <el-input v-model="formValidate.password" type="password" placeholder="请输入系统开发密码，开发密码在crmeb/config/filesystem.php中修改password"></el-input>
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
import { eventTask, eventInfo, eventSave } from '@/api/system';
export default {
  data() {
    return {
      modal: false,
      task: [],
      loading: false,
      formValidate: {
        mark: '', //键
        content: '',
        is_open: 0,
        name: '',
        password: '',
        customCode: '',
      },
      copyData: '',
      trip: '',
      editor: '', //当前编辑器对象
    };
  },
  created() {
    this.eventTask();
  },
  methods: {
    ...mapMutations('admin/layout', ['setCopyrightShow']),
    taskChange(item) {
      // 获取选中值对应 task 中的的data的值
      let taskData = this.task.find((i) => i.value === item);
      this.copyData = taskData.data;
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
    eventTask() {
      eventTask().then((res) => {
        this.task = res.data;
      });
    },
    // onCopy(copyData) {
    //   let data = `$data['${copyData}']`;
    //   this.$copyText(data)
    //     .then((message) => {
    //       this.$message.success('复制成功');
    //     })
    //     .catch((err) => {
    //       this.$message.error('复制失败');
    //     });
    // },
    initData(status) {
      this.formValidate = {
        name: '',
        mark: '',
        is_open: 0,
        content: '',
        password: '',
        customCode: '',
      };
      this.copyData = '';
      this.modal = false;
    },
    eventInfo(id) {
      if (!id) {
        this.modal = true;
        this.initEditor(
          "<?php\n\n//示例代码\n//参数使用实例  $data['uid']\n\n//直接写入数据库\n\\think\\facade\\Db::name('cache')->insert(['key' => 'custom_event_' . rand(), 'result' => $data['nickname'] . rand(), 'expire_time' => 0]);\n\n//调用系统方法\napp()->make(\\app\\services\\other\\CacheServices::class)->setDbCache('custom_event_' . rand(), $data['nickname']);",
        );
        return;
      }
      eventInfo(id).then((res) => {
        this.modal = true;
        this.formValidate = res.data;
        let taskData = this.task.find((i) => i.value === res.data.mark);
        this.copyData = taskData.data;
        this.initEditor(res.data.customCode || '');
      });
    },
    // 提交
    handleSubmit() {
      this.formValidate.customCode = this.editor.getValue();
      if (!this.formValidate.mark) {
        return this.$message.error({
          message: '请选择事件类型',
          onClose: () => {
            // this.loading = false;
          },
        });
      }
      this.eventSave(this.formValidate);
    },
    eventSave(data) {
      eventSave(data)
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

.copy-tag {
  display: flex;
  flex-wrap: wrap;

  .item {
    margin: 5px;
    cursor: pointer;
  }
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

.text-area {
  white-space: pre-wrap;
  word-break: break-word;
}
</style>
