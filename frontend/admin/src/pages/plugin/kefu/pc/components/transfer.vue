<template>
  <div>
    <div class="list-wrapper">
      <div class="user-item" v-for="(item, index) in labelLists" :key="index" v-db-click @click="bindActive(item)">
        <img v-lazy="item.avatar" alt="" />
        <p class="line1">{{ item.wx_name }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { transferList, serviceTransfer } from '@/pages/plugin/kefu/api';

defineOptions({ name: 'transfer' });

const props = defineProps({
  userUid: {
    type: [String, Number],
    default: '',
  },
});

const emit = defineEmits(['transferPeople', 'close']);

const loading = ref(false);
const currentChoose = ref('');
const labelLists = ref([]);
const name = ref('');

onMounted(() => {
  getList();
});

function getList() {
  transferList({
    nickname: name.value,
    uid: props.userUid,
  }).then((res) => {
    labelLists.value = res.data.list;
  });
}
function bindActive(item) {
  // emit('transferPeople',item)
  serviceTransfer({
    uid: props.userUid,
    kefuToUid: item.uid,
  })
    .then((res) => {
      ElMessage.success(res.msg);
      emit('close');
    })
    .catch((error) => {
      ElMessage.error(error.msg);
    });
}
</script>

<style lang="scss" scoped>
.list-wrapper {
  .user-item {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    cursor: pointer;
    &:last-child {
      margin-bottom: 0;
    }
    img {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      margin-right: 8px;
    }
    p {
      width: 80%;
      color: #333;
      font-size: 13px;
    }
  }
}
</style>
