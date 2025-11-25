<template>
  <div class="select-word" v-if="configData">
    <div class="c_row-item">
      <el-col class="label" :span="4">
        {{ configData.title }}
      </el-col>
      <el-col :span="19" class="slider-box">
        <div class="inputs" v-for="(item, index) in configData.list" :key="index">
          <el-input
            :icon="index > 1 ? 'ios-trash-outline' : ''"
            v-model="item.val"
            maxlength="10"
            placeholder="选填，不超过十个字"
            @on-click="bindDelete(index)"
          />
        </div>
        <div class="button acea-row row-between-wrapper" :class="configData.list.length == 0 ? 'on' : ''">
          <div class="bnt acea-row row-center-wrapper" @click="addHotTxt">
            <span class="iconfont iconjia"></span>
            添加单个选项
          </div>
          <Poptip placement="bottom" trigger="click" width="256" transfer padding="8px" v-model="visible">
            <div class="bnt acea-row row-center-wrapper">
              <span class="iconfont iconjia"></span>
              批量添加选项
            </div>
            <div class="batchItem on" slot="content">
              <div class="title">批量添加选项</div>
              <div class="tips">可按回车键添加多个选项</div>
              <el-input v-model="batchWord" type="textarea" :autosize="{ minRows: 3, maxRows: 5 }" />
              <div class="batchBnt acea-row row-right">
                <el-button @click.stop="cancel(1)">取消</el-button>
                <el-button type="primary" class="ml10" @click.stop="cancel(2)">确定</el-button>
              </div>
            </div>
          </Poptip>
        </div>
      </el-col>
    </div>
  </div>
</template>
<script>
export default {
  name: 'c_select_item',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  data() {
    return {
      defaults: {},
      configData: {},
      batchWord: '',
      visible: false,
    };
  },
  created() {
    this.defaults = this.configObj;
    this.configData = this.configObj[this.configNme];
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.configData = nVal[this.configNme];
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    cancel(num) {
      this.visible = false;
      if (num == 2) {
        let arr = this.batchWord.split('\n');
        let arrNew = [];
        arr.forEach((item) => {
          let obj = {};
          obj['val'] = item;
          arrNew.push(obj);
        });
        this.configData.list = this.configData.list.concat(arrNew);
      }
      this.batchWord = '';
    },
    addHotTxt() {
      let obj = {
        val: '',
      };
      this.configData.list.push(obj);
    },
    // 删除数组
    bindDelete(index) {
      this.configData.list.splice(index, 1);
    },
  },
};
</script>

<style scoped lang="scss">
.poptipOn {
  display: none;
}
::v-deeptextarea.ivu-input {
  resize: none;
}
.batchItem {
  padding-left: 2px;
  padding-right: 2px;
  .title {
    font-size: 13px;
    color: #333;
    font-weight: 400;
    margin-top: 6px;
  }
  .tips {
    font-size: 12px;
    color: #999999;
    font-weight: 400;
    margin-top: 3px;
    margin-bottom: 10px;
  }
  .batchBnt {
    margin-top: 12px;
  }
}
.select-word {
  margin-bottom: 20px;
  .c_row-item {
    align-items: baseline !important;
    text-align: right;
    color: #666;
    ::v-deep.ivu-input-suffix i {
      color: #999;
      font-size: 18px;
    }
    .button {
      margin-top: 20px;
      &.on {
        margin-top: 0;
      }
      .bnt {
        width: 143px;
        height: 32px;
        border: 1px solid #dcdfe6;
        border-radius: 5px;
        font-size: 13px;
        cursor: pointer;
        .iconfont {
          font-size: 12px;
          color: var(--prev-color-primary);
          margin-right: 5px;
        }
      }
    }
    .inputs {
      margin-bottom: 10px;
    }
  }
}
</style>
