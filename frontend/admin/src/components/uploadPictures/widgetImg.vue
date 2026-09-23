<template>
  <div class="box">
    <upload-from
      :isChoice="isChoiceD"
      @getPicD="getPicD"
      :gridPic="gridPic"
      :gridBtn="gridBtn"
      :isIframe="isIframe"
      v-if="route.query.fodder === 'dialog' || route.query.type === 'many'"
    ></upload-from>
    <upload-from
      :isChoice="isChoice"
      :isIframe="isIframe"
      @getPic="getPic"
      :gridPic="gridPic"
      :gridBtn="gridBtn"
      v-else
    ></upload-from>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import uploadFrom from './index';

defineOptions({ name: 'widgetImg' });

const route = useRoute();

const isChoice = ref('单选');
const isChoiceD = ref('多选');
const isIframe = ref(true); //是否全屏打开选择图片
const gridPic = ref({
  xl: 4,
  lg: 4,
  md: 8,
  sm: 12,
  xs: 12,
});
const gridBtn = ref({
  xl: 4,
  lg: 4,
  md: 4,
  sm: 8,
  xs: 8,
});

function getPicD(pc) {
  let pcs = window.form_create_helper.get(route.query.fodder) || [];
  pc = pc.map((item) => {
    return item.att_dir;
  });
  let concatPc = pcs.concat(pc);
  let pcList = Array.from(new Set(concatPc));
  form_create_helper.set(route.query.fodder, pcList);
  form_create_helper.close(route.query.fodder);
}
function getPic(pc) {
  form_create_helper.set(route.query.fodder, pc.satt_dir);
  form_create_helper.close(route.query.fodder);
}
</script>

<style lang="scss" scoped>
.box {
  width: 100%;
  background: #fff;
}
</style>
