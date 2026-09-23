<template>
  <div>
    <div class="labelChange" :class="change === true ? 'on' : ''">
      <div class="priceTitle cor32">
        用户标签
        <span class="iconfontYI icon-guanbi" v-db-click @click="close"></span>
      </div>
      <div class="label-wrapper">
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
      </div>
      <div class="footer">
        <el-button type="primary" class="btn" v-db-click @click="subBtn">确定</el-button>
      </div>
    </div>
    <div class="maskModel" @touchmove.prevent v-show="change === true"></div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { userLabelPut } from '@/pages/plugin/kefu/api';

defineOptions({ name: 'userLaber' });

const props = defineProps({
  change: Boolean,
  uid: Number,
  labelList: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['editLabel', 'closeChange']);

const activeIds = ref([]);

watch(
  () => props.labelList,
  function (data) {
    data.map((el) => {
      el.label.map((label) => {
        if (label.disabled) {
          activeIds.value.push(label.id);
        }
      });
    });
  },
);

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
  let unLaberids = [];
  props.labelList.map((item) => {
    item.label.map((i) => {
      if (i.disabled == false) {
        unLaberids.push(i.id);
      }
    });
  });
  userLabelPut(props.uid, {
    label_ids: activeIds.value,
    un_label_ids: unLaberids,
  })
    .then((res) => {
      ElMessage.success(res.msg);
      emit('editLabel', false);
    })
    .catch((error) => {
      ElMessage.error(error.msg);
    });
}
function close() {
  emit('closeChange', false);
}
</script>
<style lang="scss" scoped>
.label-wrapper {
  height: 9rem;
  overflow: scroll;
  .list {
    display: flex;
    flex-wrap: wrap;
    .label-item {
      margin: 0.2rem 0.3rem 0.1rem 0;
      padding: 0 0.2rem;
      background: #eeeeee;
      color: #282828;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.28rem;
      height: 0.56rem;
      line-height: 0.56rem;
      &.on {
        color: #fff;
        background: #3875ea;
      }
    }
  }
}
.footer {
  margin-top: 0.25rem;
}
.btn {
  width: 100%;
  height: 0.76rem;
  border-radius: 43px;
  background: #3875ea;
}
.title {
  font-size: 0.32rem;
  color: #282828;
}
</style>
<style scoped lang="scss">
.label {
  &-title {
    margin-bottom: 0.25rem;
  }
}
.label-box {
  margin-bottom: 10px;
}

.priceTitle {
  position: relative;
  text-align: center;
  .iconfontYI {
    position: absolute;
    font-size: 0.2rem;
    right: 0.13rem;
    top: 0.11rem;
    width: 0.2rem;
    height: 0.2rem;
    line-height: 0.2rem;
  }
}
.labelCheck {
  :deep(.ivu-checkbox) {
    display: none !important;
  }
  :deep(.ivu-checkbox-wrapper-checked.ivu-checkbox-border) {
    background: #3875ea;
    color: #fff;
  }
}
.labelChange {
  padding: 0.3rem;
  position: fixed;
  width: 90%;
  height: 11.1rem;
  background-color: #fff;
  border-radius: 0.1rem;
  top: 50%;
  left: 50%;
  margin-left: -3.4rem;
  margin-top: -5.6rem;
  z-index: 99;
  transition: all 0.3s ease-in-out 0s;
  -webkit-transition: all 0.3s ease-in-out 0s;
  -o-transition: all 0.3s ease-in-out 0s;
  -moz-transition: all 0.3s ease-in-out 0s;
  -webkit-transform: scale(0);
  -o-transform: scale(0);
  -moz-transform: scale(0);
  -ms-transform: scale(0);
  transform: scale(0);
  opacity: 0;
}
.cor32 {
  font-size: 0.32rem;
  color: #282828;
}
.mb80 {
  margin-bottom: 0.5rem;
}
.on {
  opacity: 1;
  transform: scale(1);
  -webkit-transform: scale(1);
  -o-transform: scale(1);
  -moz-transform: scale(1);
  -ms-transform: scale(1);
}
</style>
