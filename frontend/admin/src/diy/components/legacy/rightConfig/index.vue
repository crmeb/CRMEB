<template>
  <div class="right-box" v-if="rCom.length">
    <div class="title-bar">模块配置</div>
    <div class="mobile-config" v-if="rCom.length">
      <div v-for="(item, key) in rCom" :key="key">
        <component
          :is="toolCom[item.components.name]"
          :moduleName="name.name"
          :name="item.configNme"
          :configData="configData"
          :configNum="configNum"
        ></component>
      </div>
      <div style="text-align: center" v-if="rCom.length">
        <el-button type="primary" style="width: 100%; margin: 0 auto; height: 40px" v-db-click @click="saveConfig"
          >保存</el-button
        >
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { getCategory as getCategoryApi, getByCategory as getByCategoryApi, diySave, storeStatus } from '@diy/api';
import toolCom from '@diy/components/legacy/diyComponents/index.js';
import bus from '@/utils/bus';
import { useMorenStore } from '@diy/store/legacy/moren';

defineOptions({ name: 'rightConfig' });

const props = defineProps({
  name: {
    type: Object,
    default: {},
  },
  pageId: {
    type: Number,
    default: 0,
  },
  configNum: {
    type: [Number, String],
    default: 'default',
  },
});

const morenStore = useMorenStore();

const defultArr = computed(() => morenStore.component);

const rCom = ref([]);
const configData = ref({});
const isShow = ref(true);
const categoryList = ref([]);
const status = ref(0);

watch(
  () => props.name,
  (nVal, oVal) => {
    rCom.value = [];
    configData.value = morenStore.defaultConfig[nVal.name];
    if (!configData.value.hasOwnProperty(props.configNum)) {
      let defaultObj = JSON.parse(JSON.stringify(configData.value.defaultVal));
      configData.value[nVal.num] = defaultObj;
      morenStore.upDataName(configData.value);
    }
    setTimeout(function () {
      rCom.value = morenStore.component[nVal.name].list;
    }, 30);
    if (configData.value[nVal.num].selectConfig) {
      let type = configData.value[nVal.num].selectConfig.type ? configData.value[nVal.num].selectConfig.type : 0;
      if (type) {
        getByCategory();
      } else {
        getCategory();
      }
    }
  },
  { deep: true },
);

watch(
  defultArr,
  (nVal, oVal) => {
    rCom.value = [];
    let tempArr = objToArray(nVal);
    rCom.value = nVal[props.name.name].list;
  },
  { deep: true },
);

onMounted(() => {
  getStoreStatus();
});

function getStoreStatus() {
  storeStatus().then((res) => {
    status.value = parseInt(res.data.store_status);
  });
}
function getCategory() {
  getCategoryApi().then((res) => {
    let data = [];
    res.data.map((item) => {
      data.push({
        title: item.title,
        pid: item.pid,
        activeValue: item.id.toString(),
      });
    });
    configData.value[props.name.num].selectConfig.list = data;
    bus.emit('upData', data);
  });
}
//获取二级分类
function getByCategory() {
  getByCategoryApi().then((res) => {
    let data = [];
    res.data.map((item) => {
      data.push({
        title: item.cate_name,
        pid: item.pid,
        activeValue: item.id.toString(),
      });
    });
    configData.value[props.name.num].selectConfig.list = data;
    bus.emit('upData', data);
  });
}
// 保存数据
function saveConfig() {
  let data = morenStore.defaultConfig;
  if (props.name.name == 'tabBar') {
    if (!status.value) {
      let list = data.tabBar.default.tabBarList.list;
      for (let i = 0; i < list.length; i++) {
        if (list[i].link == '/pages/storeList/index' || list[i].link == 'pages/storeList/index') {
          return ElMessage.error('请先开启您的周边功能(/pages/storeList/index)');
        }
      }
    }
    if (data.tabBar.default.tabBarList.list.length < 2) {
      return ElMessage.error('您最少应添加2个导航');
    }
  }

  diySave(props.pageId, {
    value: data,
  }).then((res) => {
    ElMessage.success('保存成功');
  });
}
// 对象转数组
function objToArray(array) {
  var arr = [];
  for (var i in array) {
    arr.push(array[i]);
  }
  return arr;
}
</script>

<style lang="scss" scoped>
.right-box {
  width: 700px;
  margin-left: 50px;
  border: 1px solid #ddd;
  border-radius: 4px;
  height: 700px;
  overflow-y: scroll;
  &::-webkit-scrollbar {
    /* 滚动条整体样式 */
    width: 4px; /* 高宽分别对应横竖滚动条的尺寸 */
    height: 1px;
  }
  &::-webkit-scrollbar-thumb {
    /* 滚动条里面小方块 */
    border-radius: 4px;
    box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
    background: #535353;
  }
  &::-webkit-scrollbar-track {
    /* 滚动条里面轨道 */
    box-shadow: inset 0 0 5px #fff;
    border-radius: 4px;
    background: #fff;
  }
}
.title-bar {
  width: 100%;
  height: 38px;
  line-height: 38px;
  padding-left: 24px;
  color: #333;
  border-radius: 4px;
  border-bottom: 1px solid #eee;
}
</style>
