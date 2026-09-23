<template>
  <el-radio-group v-model="selectIndexTime" type="button" @input="onSelectTime">
    <el-radio-button :label="index" :value="index" v-for="(item, index) in options.shortcuts" :key="index">{{
      item.text
    }}</el-radio-button>
  </el-radio-group>
</template>

<script setup>
import { ref } from 'vue';
import { formatDate } from '@/utils/validate';
import timeOptions from '@/libs/timeOptions';

const emit = defineEmits(['selectDate']);

const timeVal = ref([]);
const options = timeOptions;
const selectIndexTime = ref('');

function formatDateLocal(time) {
  if (time !== 0) {
    let date = new Date(time);
    return formatDate(date, 'yyyy/MM/dd');
  }
}

function onSelectTime(e) {
  let time = [
    formatDateLocal(dateToMs(timeOptions.shortcuts[selectIndexTime.value].value()[0])),
    formatDateLocal(dateToMs(timeOptions.shortcuts[selectIndexTime.value].value()[1])),
  ];
  emit('selectDate', time.join('-'));
}
function dateToMs(date) {
  let result = new Date(date).getTime();
  return result;
}
</script>

<style></style>
