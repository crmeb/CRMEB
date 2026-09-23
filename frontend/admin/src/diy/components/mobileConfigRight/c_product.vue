<template>
  <div class="c_product" v-if="configData">
    <div class="title" v-if="configData.title">{{ configData.title }}</div>
    <div class="list-box">
      <draggable
        class="dragArea list-group"
        :list="configData.list"
        group="peoples"
        handle=".move-icon"
        :item-key="productItemKey"
      >
        <template #item="{ element: item, index }">
          <div class="item" @click="activeBtn(index)">
            <!-- v-model="configData.tabCur" -->
            <div class="move-icon">
              <span class="iconfont iconxingzhuangjiehe"></span>
            </div>
            <div class="content">
              <div class="con-item" v-for="(list, key) in item.chiild" :key="key">
                <span>{{ list.title }}</span>
                <div style="width: 100%">
                  <el-input v-model="list.val" :placeholder="list.pla">
                    <template #suffix
                      ><i v-if="list.title == '链接'" class="el-icon-link" @click="getLink(index, key, item)"
                    /></template>
                  </el-input>
                </div>
              </div>
              <div class="con-item" v-if="configData.type">
                <span>状态</span>
                <el-switch v-model="item.show" />
              </div>
              <div class="con-item" v-if="item.link">
                <span>{{ item.link.title }}</span>
                <el-select v-model="item.link.activeVal" style="" @change="(e) => sliderChange(index, e)">
                  <el-option
                    v-for="(item, j) in item.link.optiops"
                    :value="item.value"
                    :key="j"
                    :label="item.label"
                  ></el-option>
                </el-select>
              </div>
            </div>
            <div class="delete" @click.stop="bindDelete(index)">
              <i class="el-icon-circle-close" style="font-size: 20px" />
            </div>
          </div>
        </template>
      </draggable>
    </div>
    <div v-if="configData.list">
      <div class="add-btn" @click="addHotTxt" v-if="configData.list.length < configData.max">
        <el-button class="btn" type="primary" ghost> <span class="iconfont iconjiahao"></span>添加 </el-button>
      </div>
    </div>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import draggable from 'vuedraggable';
import linkaddress from '@/components/linkaddress';

defineOptions({ name: 'c_product' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
  index: {
    type: null,
  },
});

const emit = defineEmits(['getConfig']);

const linkaddres = ref(null);
const defaults = ref({});
const configData = ref({});
const itemObj = ref({});
const activeIndex = ref(0);

// vuedraggable4 item-key（数据无唯一 id，用索引）
const productItemKey = (element) => configData.value.list.indexOf(element);

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    configData.value = props.configObj[props.configNme];
  });
});

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
  },
  { deep: true },
);

function linkUrl(e) {
  configData.value.list[activeIndex.value].chiild[1].val = e;
}
function getLink(index, key, item) {
  if (!key || item.link) {
    return;
  }
  activeIndex.value = index;
  linkaddres.value.modals = true;
}
function addHotTxt() {
  if (configData.value.list.length == 0) {
    let storage = window.localStorage;
    itemObj.value = JSON.parse(storage.getItem('itemObj'));
    if (itemObj.value.link) {
      itemObj.value.link.activeVal = 0;
    }
    itemObj.value.chiild[0].val = '';
    itemObj.value.chiild[1].val = '';
    configData.value.list.push(itemObj.value);
  } else {
    let obj = JSON.parse(JSON.stringify(configData.value.list[configData.value.list.length - 1]));
    obj.chiild.forEach((item) => {
      item.val = '';
    });
    configData.value.list.push(obj);
  }
}
// 删除数组
function bindDelete(index) {
  if (configData.value.list.length == 1) {
    let item = configData.value.list[0];
    itemObj.value = item;
    let storage = window.localStorage;
    storage.setItem('itemObj', JSON.stringify(item));
  }
  configData.value.list.splice(index, 1);
  configData.value.tabCur = 0;
  emit('getConfig', { name: 'delete', indexs: 0 });
}
function sliderChange(index) {
  configData.value.tabCur = index;
  emit('getConfig', { name: 'product', indexs: index });
}
function activeBtn(index) {
  configData.value.tabCur = index;
  emit('getConfig', { name: 'product', indexs: index });
}
</script>

<style scoped lang="scss">
:deep(.ivu-input) {
  font-size: 12px !important;
}

:deep(.ivu-input-word-count) {
  color: #bbbbbb;
}

:deep(.ivu-input-icon) {
  color: #bbbbbb;
}

.c_product {
  margin-bottom: 20px;
  padding: 0 15px 20px 15px;

  .list-box {
    .item {
      position: relative;
      display: flex;
      padding: 18px 20px 18px 0;
      background-color: #f9f9f9;
      border-radius: 3px;

      .delete {
        position: absolute;
        right: -13px;
        top: -14px;
        color: #ccc;
        cursor: pointer;
      }
    }

    .move-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      cursor: move;
    }

    .content {
      flex: 1;

      .con-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;

        &:last-child {
          margin-bottom: 0;
        }

        span {
          width: 45px;
          font-size: 12px;
          color: #999;
        }
      }
    }
  }

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
}

.title {
  padding-top: 20px;
  font-size: 12px;
  color: #999;
}

.iconfont {
  color: #dddddd;
  font-size: 16px;
}
</style>
