<template>
  <div class="numbox" v-if="datas[name]">
    <div class="c_row-item" v-for="(item, index) in datas[name].list" :key="index">
      <div class="dif" v-if="item.title === '链接'">
        <el-col class="label" :span="4">
          <span>{{ item.title }}</span>
        </el-col>
        <el-col :span="19" class="slider-box">
          <div class="input-box" v-db-click @click="getLink(index)">
            <el-input v-model="item.val" :placeholder="item.pla" :maxlength="item.max">
              <template #suffix><i class="el-icon-link" @click="getLink(index)" /></template>
            </el-input>
          </div>
        </el-col>
      </div>
      <div class="dif" v-else>
        <el-col class="label" :span="4">
          <span>{{ item.title }}</span>
        </el-col>
        <el-col :span="19" class="slider-box">
          <el-input v-model="item.val" :placeholder="item.pla" :maxlength="item.max" style="text-align: right" />
        </el-col>
      </div>
    </div>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import linkaddress from '@/components/linkaddress';

defineOptions({ name: 'c_input_list' });

const props = defineProps({
  name: {
    type: String,
  },
  configData: {
    type: null,
  },
  configNum: {
    type: [Number, String],
    default: 'default',
  },
});

const defaults = ref({});
const datas = ref(props.configData[props.configNum]);
const activeIndex = ref(0);
const linkaddres = ref(null);

watch(
  () => props.configData,
  (nVal) => {
    datas.value = nVal[props.configNum];
  },
  { immediate: true, deep: true },
);

function getLink(index) {
  activeIndex.value = index;
  linkaddres.value.modals = true;
}
function linkUrl(e) {
  datas.value[props.name].list[activeIndex.value].val = e;
}
</script>

<style lang="scss" scoped>
.numbox {
  margin: 20px 0 10px 0;

  span {
    width: 80px;
    color: #999;
  }
}
.c_row-item {
  width: 100%;
  & ~ .c_row-item {
    margin-top: 20px;
  }
}
.dif {
  display: flex;
  align-items: center;
}
.slider-box {
  margin-left: 10px;
  width: 350px;
}
</style>
