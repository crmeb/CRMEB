<template>
  <div class="label-wrapper">
    <div class="list-box">
      <template v-if="isUser">
        <div class="label-box" v-for="(item, index) in labelList" :key="index">
          <div class="title" v-if="item.children">{{ item.label_name }}</div>
          <div class="list" v-if="item.children && item.children.length">
            <div
              class="label-item"
              :class="{ on: label.disabled }"
              v-for="(label, j) in item.children"
              :key="j"
              v-db-click
              @click="selectLabel(label)"
            >
              {{ label.label_name }}
            </div>
          </div>
        </div>
      </template>
      <div v-if="!isUser">暂无标签</div>
    </div>
    <div class="footer">
      <el-button class="btns" ghost v-db-click @click="cancel">取消</el-button>
      <el-button type="primary" class="btns" v-db-click @click="subBtn">确定</el-button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { productUserLabel } from '@/api/product';

defineOptions({ name: 'userLabel' });

const emit = defineEmits(['activeData', 'close']);

const labelList = ref([]);
const dataLabel = ref([]);
const isUser = ref(false);

function inArray(search, array) {
  for (let i in array) {
    if (array[i].id == search) {
      return true;
    }
  }
  return false;
}

// 用户标签
function setLabel() {
  // dataLabel.value = data;
  productUserLabel()
    .then((res) => {
      res.data.map((el) => {
        if (el.children) {
          isUser.value = true;
          el.children.map((label) => {
            if (inArray(label.id, dataLabel.value)) {
              label.disabled = true;
            } else {
              label.disabled = false;
            }
          });
        }
      });
      labelList.value = res.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

function selectLabel(label) {
  if (label.disabled) {
    let index = dataLabel.value.indexOf(dataLabel.value.filter((d) => d.id == label.id)[0]);
    dataLabel.value.splice(index, 1);
    label.disabled = false;
  } else {
    dataLabel.value.push({ label_name: label.label_name, id: label.id });
    label.disabled = true;
  }
}
// 确定
function subBtn() {
  emit('activeData', JSON.parse(JSON.stringify(dataLabel.value)));
}
function cancel() {
  emit('close');
}

// mounted
setLabel();
</script>

<style lang="scss" scoped>
.label-wrapper {
  .list {
    display: flex;
    flex-wrap: wrap;
    .label-item {
      margin: 10px 8px 10px 0;
      padding: 3px 8px;
      background: #eeeeee;
      color: #333333;
      border-radius: 2px;
      cursor: pointer;
      font-size: 12px;
      &.on {
        color: #fff;
        background: var(--prev-color-primary);
      }
    }
  }
  .footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 40px;
    button {
      margin-left: 10px;
    }
  }
}
.btn {
  width: 60px;
  height: 24px;
}
.title {
  font-size: 13px;
}
.list-box {
  overflow-y: auto;
  overflow-x: hidden;
  max-height: 240px;
}
.label-box {
  margin-bottom: 10px;
}
</style>
