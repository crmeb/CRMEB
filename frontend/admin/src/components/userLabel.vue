<template>
  <div class="label-wrapper">
    <div v-if="!labelList[0]" class="nonefont">暂无标签</div>
    <template v-else>
      <div v-if="is_batch" class="flex flex-y-center mb20">
        <div class="title mr10">设置类型</div>
        <el-radio-group v-model="label_type">
          <el-radio :label="0" :value="0">统一设置</el-radio>
          <el-radio :label="1" :value="1">增加</el-radio>
          <el-radio :label="2" :value="2">减少</el-radio>
        </el-radio-group>
      </div>
      <div class="label-box" v-for="(item, index) in labelList" :key="index">
        <div class="title">{{ item.name }}</div>
        <div class="list">
          <div
            class="label-item"
            :class="{ on: label.disabled }"
            v-for="(label, j) in item.label"
            :key="j"
            v-db-click
            @click="selectLabel(label)"
          >
            {{ label.label_name }}
          </div>
        </div>
      </div>
    </template>
    <div class="acea-row row-right mt20">
      <el-button v-db-click @click="cancel">取 消</el-button>
      <el-button type="primary" v-db-click @click="subBtn">确 定</el-button>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { getUserLabel, putUserLabel } from '@/api/user';

defineOptions({ name: 'userLabel' });

const props = defineProps({
  uid: {
    type: [String, Number],
    default: 0,
  },
  only_get: {
    default: false,
  },
  selectDataLabel: {
    type: Array,
    default: () => {
      [];
    },
  },
  // 是否支持批量设置
  is_batch: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['activeData', 'close', 'onceGetList']);

const labelList = ref([]);
const activeIds = ref([]);
const unLaberids = ref([]);
// 批量设置标签 label_type  统一设置 增加 减少
const label_type = ref(0);

watch(
  () => props.uid,
  (nVal, oVal) => {
    if (nVal != oVal) {
      getList();
    }
  },
  { deep: true },
);

watch(
  () => props.is_batch,
  (nVal, oVal) => {},
  { deep: true, immediate: true },
);

onMounted(() => {
  getList();
});

function getList() {
  getUserLabel(props.uid || 0).then((res) => {
    if (props.selectDataLabel && props.selectDataLabel.length) {
      props.selectDataLabel.map((el) => {
        res.data.map((re) => {
          re.label.map((label) => {
            if (label.id === el.id) {
              label.disabled = true;
            }
          });
        });
      });
    }
    res.data.map((el) => {
      el.label.map((label) => {
        if (label.disabled) {
          activeIds.value.push(label.id);
        }
      });
    });
    labelList.value = res.data;
  });
}
function selectLabel(label) {
  if (label.disabled) {
    let index = activeIds.value.indexOf(label.id);
    activeIds.value.splice(index, 1);
    label.disabled = false;
  } else {
    activeIds.value.push(label.id);
    label.disabled = true;
  }
}
// 确定
function subBtn() {
  let unLabelIds = [];
  if (props.only_get) {
    labelList.value.map((item) => {
      item.label.map((i) => {
        if (i.disabled == true) {
          unLabelIds.push({ id: i.id, label_name: i.label_name });
        }
      });
    });
    emit('activeData', unLabelIds, label_type.value);
    return;
  }
  labelList.value.map((item) => {
    item.label.map((i) => {
      if (i.disabled == false) {
        unLabelIds.push(i.id);
      }
    });
  });
  unLaberids.value = unLabelIds;
  putUserLabel(props.uid, {
    label_ids: activeIds.value,
    un_label_ids: unLaberids.value,
  })
    .then((res) => {
      emit('onceGetList');
      activeIds.value = [];
      unLabelIds.value = [];
      ElMessage.success(res.msg);
      emit('close');
    })
    .catch((error) => {
      ElMessage.error(error.msg);
    });
}
function cancel() {
  activeIds.value = [];
  unLaberids.value = [];
  emit('close');
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
.label-box {
  margin-bottom: 10px;
}
.btn {
  width: 60px;
  height: 24px;
}
.title {
  font-size: 13px;
}
.nonefont {
  text-align: center;
  padding-top: 20px;
}
</style>
