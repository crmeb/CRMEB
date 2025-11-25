<template>
  <div class="line-box" v-if="configData">
    <div class="input-box">
      <draggable class="dragArea list-group" :list="configData.list" group="peoples" handle=".icon">
        <div class="input-item" v-for="(item, index) in configData.list" :key="index">
          <div class="icon">
            <span class="iconfont-diy iconxingzhuangjiehe"></span>
          </div>
          <el-input v-model="item.val" maxlength="10" placeholder="选填，不超过十个字"  />
          <!-- <el-select v-model="item.val">
            <el-option v-for="(val, index) in wordList" :value="val.name" :key="index">{{ val.name }}</el-option>
          </el-select> -->
          <div class="delete" @click.stop="bindDelete(index)">
            <span class="iconfont icondel_2"></span>
          </div>
        </div>
      </draggable>
      <div class="add-btn" @click="addHotTxt" v-if="configData.list.length < 20">
        <el-button class="btn" type="primary" ghost> <span class="iconfont iconjiahao"></span>添加 </el-button>
      </div>
    </div>
  </div>
</template>
<script>
import { getWordsAll } from '@/api/diy';
import vuedraggable from 'vuedraggable';
export default {
  name: 'c_hot_word',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  components: {
    draggable: vuedraggable,
  },
  data() {
    return {
      hotWordList: [],
      hotIndex: 1,
      defaults: {},
      configData: {},
      wordList: [],
    };
  },
  created() {
    this.defaults = this.configObj;
    this.configData = this.configObj[this.configNme];
    // this.wordsAll();
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        // this.hotWordList = nVal.hotList
        this.configData = nVal[this.configNme];
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    wordsAll() {
      getWordsAll()
        .then((res) => {
          this.wordList = res.data;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    addHotTxt() {
      // let obj = {}
      // if(this.configData.list.length){
      //     obj = JSON.parse(JSON.stringify(this.configData.list[this.configData.list.length - 1]))
      // }else {
      //     obj = {
      //         val: ''
      //     }
      // }
      let obj = {
        val: '',
      };
      this.configData.list.push(obj);
      // this.$emit('input', this.hotWordList);
    },
    // 删除数组
    bindDelete(index) {
      this.configData.list.splice(index, 1);
    },
  },
};
</script>

<style scoped lang="scss">
::v-deep .ivu-select-arrow {
  color: #cccccc;
}

.line-box {
  padding: 0 15px;

  .input-box {
    .add-btn {
      margin-top: 20px;

      .btn {
        width: 100%;
        height: 36px;
        border-color: #eeeeee;
        color: #666666;

        .iconfont {
          font-size: 11px;
          margin-right: 5px;
        }
      }
    }

    .input-item {
      display: flex;
      align-items: center;
      margin-bottom: 15px;

      .delete {
        color: #cccccc;
        width: 30px;
        text-align: right;

        .iconfont {
          font-size: 14px;
        }
      }

      .icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        cursor: move;

        .iconfont-diy {
          font-size: 16px;
          color: #dddddd;
        }
      }

      ::v-deep .ivu-input {
        flex: 1;
        height: 36px;
        font-size: 13px !important;
      }
    }
  }
}
</style>
