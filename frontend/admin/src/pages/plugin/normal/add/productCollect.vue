<template>
  <div class="Box" v-loading="spinShow">
    <div>
      <div class="tips">
        生成的商品默认是没有上架的，请手动上架商品！
        <a href="https://doc.crmeb.com/single/v5/7785" v-if="copyConfig.copy_type == 2" target="_blank">如何配置密钥</a>
        <span v-else
          >您当前剩余{{ copyConfig.copy_num }}条采集次数，<span class="add" v-db-click @click="mealPay()"
            >增加采集次数</span
          ></span
        >
      </div>
      <div>商品采集设置：设置 > 系统设置 > 第三方接口设置 > 采集商品配置</div>
    </div>
    <el-form class="formValidate mt20" ref="formValidateRef" label-width="80px" label-position="right" @submit.prevent>
      <el-form-item label="链接地址：">
        <el-input clearable v-model="soure_link" placeholder="请输入链接地址" class="numPut" />
        <el-button type="primary" class="ml15" v-db-click @click="add">确定</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { crawlFromApi, copyConfigApi } from '@/api/product';
import Setting from '@/setting';

defineOptions({ name: 'productCollect' });

const emit = defineEmits(['on-close', 'get-platforms']);
const router = useRouter();

const formValidateRef = ref(null);

const soure_link = ref('');
const spinShow = ref(false);
const grid = {
  xl: 8,
  lg: 8,
  md: 12,
  sm: 24,
  xs: 24,
};
const grid2 = {
  xl: 12,
  lg: 12,
  md: 12,
  sm: 24,
  xs: 24,
};
const copyConfig = reactive({
  copy_type: 2,
  copy_num: 0,
  platforms: [],
});
const artFrom = reactive({
  type: 'taobao',
  url: '',
});

onMounted(() => {
  getCopyConfig();
});

function mealPay() {
  router.push({ path: Setting.routePre + '/setting/yihaotong/index' });
}
function getCopyConfig() {
  copyConfigApi().then((res) => {
    copyConfig.copy_type = res.data.copy_type;
    copyConfig.copy_num = res.data.copy_num;
    copyConfig.platforms = res.data.platforms || [];
    emit('get-platforms', copyConfig.platforms);
  });
}
// 生成表单
function add() {
  if (soure_link.value) {
    var reg = /(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?/;
    if (!reg.test(soure_link.value)) {
      return ElMessage.warning('请输入以http开头的地址！');
    }
    spinShow.value = true;
    artFrom.url = soure_link.value;
    crawlFromApi(artFrom)
      .then((res) => {
        let info = res.data.productInfo;
        emit('on-close', info);
        spinShow.value = false;
      })
      .catch((res) => {
        spinShow.value = false;
        ElMessage.error(res.msg);
      });
  } else {
    ElMessage.warning('请输入链接地址！');
  }
}
</script>

<style lang="scss" scoped>
:deep(.ivu-form-item-content) {
  line-height: unset !important;
}
.Box .ivu-radio-wrapper {
  margin-right: 25px;
}
.add {
  color: #2d8cf0;
  cursor: pointer;
}
.Box .numPut {
  width: 414px !important;
}
</style>
