<template>
  <div class="line-box" v-if="configData">
    <div class="input-box">
      <draggable
        class="dragArea list-group"
        :list="configData.list"
        group="peoples"
        handle=".icon"
        :item-key="hotWordItemKey"
      >
        <template #item="{ element: item, index }">
          <div class="input-item">
            <div class="icon">
              <span class="iconfont iconxingzhuangjiehe"></span>
            </div>
            <el-input v-model="item.val" maxlength="10" placeholder="选填，不超过十个字" />
            <!-- <el-select v-model="item.val">
              <el-option v-for="(val, index) in wordList" :value="val.name" :key="index">{{ val.name }}</el-option>
            </el-select> -->
            <div class="delete" @click.stop="bindDelete(index)">
              <span class="iconfont icondel_2"></span>
            </div>
          </div>
        </template>
      </draggable>
      <div class="add-btn" @click="addHotTxt" v-if="configData.list.length < 20">
        <el-button class="btn" type="primary" ghost> <span class="iconfont iconjiahao"></span>添加 </el-button>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { getWordsAll } from '@diy/api';
import draggable from 'vuedraggable';

defineOptions({ name: 'c_hot_word' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const hotWordList = ref([]);
const hotIndex = ref(1);
const defaults = ref({});
const configData = ref({});
const wordList = ref([]);

// vuedraggable4 item-key（数据无唯一 id，用索引）
const hotWordItemKey = (element) => configData.value.list.indexOf(element);

watch(
  () => props.configObj,
  (nVal) => {
    // this.hotWordList = nVal.hotList
    configData.value = nVal[props.configNme];
  },
  { immediate: true, deep: true },
);

function wordsAll() {
  getWordsAll()
    .then((res) => {
      wordList.value = res.data;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
function addHotTxt() {
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
  configData.value.list.push(obj);
  // emit('input', hotWordList.value);
}
// 删除数组
function bindDelete(index) {
  configData.value.list.splice(index, 1);
}
</script>

<style scoped lang="scss">
:deep(.ivu-select-arrow) {
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

        .iconfont {
          font-size: 16px;
          color: #dddddd;
        }
      }

      :deep(.ivu-input) {
        flex: 1;
        height: 36px;
        font-size: 13px !important;
      }
    }
  }
}
</style>
