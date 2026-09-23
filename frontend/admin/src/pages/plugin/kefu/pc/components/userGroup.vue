<template>
  <div class="label-wrapper">
    <div class="label-box">
      <div class="list">
        <div
          class="label-item"
          :class="{ on: label.id == groupId }"
          v-for="(label, j) in labelList"
          :key="j"
          v-db-click
          @click="selectLabel(label)"
        >
          {{ label.group_name }}
        </div>
      </div>
    </div>
    <!-- <div class="footer">
            <el-button type="primary" class="btns" v-db-click @click="subBtn">确定</el-button>
            <el-button type="primary" class="btns" ghost v-db-click @click="cancel">取消</el-button>
        </div> -->
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { userLabel, userLabelPut } from '@/pages/plugin/kefu/api';

defineOptions({ name: 'userLabel' });

const props = defineProps({
  uid: {
    type: [String, Number],
    default: '',
  },
  groupId: {
    type: [String, Number],
    default: '',
  },
  labelList: {
    type: Array,
    default: () => {
      [];
    },
  },
});

const emit = defineEmits(['editUserLabel']);

const activeIds = ref([]);
const labelLists = ref([]);

// 原代码将结果误写到 prop labelList 上，此处改写本地变量（该函数从未被调用，为死代码）
function getList() {
  userLabel(props.uid).then((res) => {
    res.data.map((el) => {
      el.label.map((label) => {
        if (label.disabled) {
          activeIds.value.push(label.id);
        }
      });
    });
    labelLists.value = res.data;
  });
}
function selectLabel(label) {
  emit('editUserLabel', label.id);
}
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
</style>
