<template>
  <el-radio-group v-model="selectIndexTime" type="button" @input="onSelectTime">
    <el-radio-button :label="index" v-for="(item, index) in options.shortcuts" :key="index">{{
      item.text
    }}</el-radio-button>
  </el-radio-group>
</template>

<script>
import { formatDate } from '@/utils/validate';

export default {
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time);
        return formatDate(date, 'yyyy/MM/dd');
      }
    },
  },
  data() {
    return {
      timeVal: [],
      options: this.$timeOptions,
      selectIndexTime: '',
    };
  },
  methods: {
    onSelectTime(e) {
      let time = [
        this.$options.filters.formatDate(this.dateToMs(this.$timeOptions.shortcuts[this.selectIndexTime].value()[0])),
        this.$options.filters.formatDate(this.dateToMs(this.$timeOptions.shortcuts[this.selectIndexTime].value()[1])),
      ];
      this.$emit('selectDate', time.join('-'));
    },
    dateToMs(date) {
      let result = new Date(date).getTime();
      return result;
    },
  },
};
</script>

<style></style>
