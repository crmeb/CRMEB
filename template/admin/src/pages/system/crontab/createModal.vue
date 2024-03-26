<template>
  <div>
    <el-dialog
      :visible.sync="modal"
      :title="formValidate.id ? '编辑定时任务' : '添加定时任务'"
      width="1000px"
      @closed="initData"
    >
      <el-form ref="formValidate" :model="formValidate" label-width="97px" label-colon>
        <el-form-item label="任务名称：" required>
          <el-row :gutter="16">
            <el-col :span="20">
              <el-select v-model="formValidate.mark">
                <el-option v-for="(value, name) in task" :key="name" :value="name" :label="value"></el-option>
              </el-select>
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
            <el-col v-if="[4, 7].includes(formValidate.type)" :span="4">
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
            <el-col v-if="[3, 4, 5, 6, 7].includes(formValidate.type)" :span="4">
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
            <el-col v-if="[2, 3, 4, 5, 6, 7].includes(formValidate.type)" :span="4">
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
            <el-col :span="20">
              <el-input
                v-model="formValidate.content"
                type="textarea"
                :autosize="{ minRows: 5, maxRows: 5 }"
                placeholder="请输入任务说明"
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
      <span slot="footer" class="dialog-footer">
        <el-button @click="modal = false">取 消</el-button>
        <el-button type="primary" @click="handleSubmit">提 交</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import { mapMutations } from 'vuex';
import { timerTask, timerInfo, saveTimer, updateTimer } from '@/api/system';
export default {
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
      ],
      task: {},
      loading: false,
      formValidate: {
        mark: '', //键
        content: '',
        is_open: 0,
        type: 6,
        week: 1,
        day: 1,
        hour: 1,
        minute: 30,
        second: 0,
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
    };
  },
  created() {
    this.timerTask();
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
        }
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    ...mapMutations('admin/layout', ['setCopyrightShow']),
    timerTask() {
      timerTask().then((res) => {
        this.task = res.data;
      });
    },
    initData(status) {
      this.formValidate = {
        mark: '',
        content: '',
        is_open: 0,
        type: 6,
        week: 1,
        day: 1,
        hour: 1,
        minute: 30,
        second: 0,
      };
      this.modal = false;
    },
    timerInfo(id) {
      timerInfo(id).then((res) => {
        this.modal = true;
        this.formValidate = res.data;
        // this.formValidate.name = name;
        // this.formValidate.mark = mark;
        // this.formValidate.content = content;
        // this.formValidate.is_open = is_open;
        // this.formValidate.type = type;
      });
    },
    // 提交
    handleSubmit() {
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
            onClose: () => {
              this.$emit('submitAsk');
            },
          });
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
  },
};
</script>

<style lang="stylus" scoped>
.form-card {
  margin-bottom: 74px;

  ::v-deep .ivu-card-body {
    padding: 30px 0;
  }
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
::v-deep .el-input-number__increase,::v-deep .el-input-number__decrease{
  display none
}
  .ml30{
    margin-left 30px;
  }
</style>
