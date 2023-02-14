<template>
  <div>
    <Modal
      v-model="modal"
      :title="formValidate.id ? '编辑定时任务' : '添加定时任务'"
      width="900"
      @on-ok="handleSubmit"
      @on-cancel="modal = true"
      @on-visible-change="initData"
    >
      <Form ref="formValidate" :model="formValidate" :label-width="97" label-colon>
        <FormItem label="任务名称" required>
          <Row :gutter="16">
            <Col span="20">
              <Select v-model="formValidate.mark" label-in-value @on-change="taskChange">
                <Option v-for="(value, name) in task" :key="name" :value="name">{{ value }}</Option>
              </Select>
            </Col>
          </Row>
        </FormItem>
        <FormItem label="执行周期" required>
          <Row :gutter="14">
            <Col span="4">
              <Select v-model="formValidate.type">
                <Option v-for="item in typeList" :key="item.value" :value="item.value">{{ item.name }}</Option>
              </Select>
            </Col>
            <Col v-if="formValidate.type == 6" span="4">
              <Select v-model="formValidate.week">
                <Option v-for="item in 7" :key="item" :value="item">{{ item | formatWeek }}</Option>
              </Select>
            </Col>
            <Col v-if="[4, 7].includes(formValidate.type)" span="4">
              <div class="input-number-wrapper">
                <InputNumber
                  v-model="formValidate.day"
                  :max="formValidate.type === 4 ? 10000 : 31"
                  :min="1"
                ></InputNumber>
                <span class="suffix">日</span>
              </div>
            </Col>
            <Col v-if="[3, 5, 6, 7].includes(formValidate.type)" span="4">
              <div class="input-number-wrapper">
                <InputNumber v-model="formValidate.hour" :max="23" :min="0"></InputNumber>
                <span class="suffix">时</span>
              </div>
            </Col>
            <Col span="4" v-if="[2, 5, 6, 7].includes(formValidate.type)">
              <div class="input-number-wrapper">
                <InputNumber
                  v-model="formValidate.minute"
                  :max="formValidate.type === 2 ? 36000 : 59"
                  :min="0"
                ></InputNumber>
                <span class="suffix">分</span>
              </div>
            </Col>
            <Col span="4" v-if="[1, 5, 6, 7].includes(formValidate.type)">
              <div class="input-number-wrapper">
                <InputNumber
                  v-model="formValidate.second"
                  :max="formValidate.type === 1 ? 36000 : 59"
                  :min="0"
                ></InputNumber>
                <span class="suffix">秒</span>
              </div>
            </Col>
          </Row>
          <Row :gutter="12">
            <div class="trip">{{ trip }}</div>
          </Row>
        </FormItem>
        <FormItem label="任务说明">
          <Row :gutter="10">
            <Col span="20">
              <Input
                v-model="formValidate.content"
                type="textarea"
                :autosize="{ minRows: 5, maxRows: 5 }"
                placeholder="请输入任务说明"
              ></Input>
            </Col>
          </Row>
        </FormItem>
        <FormItem label="是否开启">
          <Row :gutter="10">
            <Col span="12">
              <i-switch v-model="formValidate.is_open" :true-value="1" :false-value="0" size="large">
                <span slot="open">开启</span>
                <span slot="close">关闭</span>
              </i-switch>
            </Col>
          </Row>
        </FormItem>
      </Form>
    </Modal>
  </div>
</template>

<script>
import { mapMutations } from 'vuex';
import { timerTask, timerInfo, saveTimer, updateTimer } from '@/api/system';
export default {
  filters: {
    formatWeek(value) {
      return ['周一', '周二', '周三', '周四', '周五', '周六', '周日'][value - 1];
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
            this.trip = `每隔${nVal.hour}小时执行一次`;
            break;
          case 4:
            this.trip = `每隔${nVal.day}天执行一次`;
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
      if (!status) {
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
      }
    },
    timerInfo(id) {
      console.log(id);

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
      console.log(this.formValidate);
      if (!this.formValidate.mark) {
        return this.$Message.error({
          content: '请选择任务名称',
          onClose: () => {
            // this.loading = false;
          },
        });
      }
      this.saveTimer(this.formValidate);
    },
    taskChange(task) {
      this.formValidate.mark = task.value;
    },
    saveTimer(data) {
      saveTimer(data)
        .then((res) => {
          this.$Message.success({
            content: res.msg,
            onClose: () => {
              this.$emit('submitAsk');
            },
          });
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
  },
};
</script>

<style lang="stylus" scoped>
.form-card {
  margin-bottom: 74px;

  >>> .ivu-card-body {
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

  >>> .ivu-input-number-handler-wrap {
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
 .trip{
    padding-left 15px
    color #aaa
  }
</style>
