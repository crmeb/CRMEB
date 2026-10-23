<template>
  <div>
    <el-dialog v-model="visible" title="自定义会员价" width="900"
      ><el-form :model="formData" label-width="120px">
        <el-form-item label="会员专属：">
          <el-radio-group v-model="formData.vip_product">
            <el-radio :label="0" :value="0">不限制（所有人可见可买）</el-radio>
            <el-radio :label="1" :value="1">仅会员可见可买</el-radio>
            <el-radio :label="2" :value="2">所有人可见仅会员可买</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="付费会员价：">
          <el-switch
            v-model="formData.is_vip"
            :active-value="1"
            :inactive-value="0"
            active-text="开启"
            inactive-text="关闭"
            size="large"
            class="defineSwitch"
            @change="changeVip"
          >
          </el-switch>
        </el-form-item>
        <el-form-item label-width="0px">
          <el-table border max-height="460" :data="attrData" style="width: 100%">
            <el-table-column prop="pic" label="规格图" min-width="90" align="center">
              <template #default="scope">
                <div class="tabBox_img m-auto" v-viewer>
                  <img v-lazy="scope.row.pic || scope.row.image" />
                </div>
              </template>
            </el-table-column>
            <el-table-column prop="suk" label="产品规格" min-width="120" align="center">
              <template #default="scope">{{ formatSuk(scope.row.suk) }}</template>
            </el-table-column>
            <el-table-column prop="price" label="售价" min-width="120" align="center"></el-table-column>
            <el-table-column min-width="140" align="center" v-if="formData.is_vip == 1">
              <template #header="scope">
                <span>付费会员价</span>
                <el-popover
                  ref="vipSetPopover"
                  v-model:visible="vipSetPopoverPopver"
                  placement="top"
                  width="290"
                  trigger="click"
                >
                  <div class="pop-title">批量修改本列</div>
                  <div class="mt-14">
                    <el-radio-group v-model="vipSetType">
                      <el-radio :label="0" :value="0">指定价格</el-radio>
                      <el-radio :label="1" :value="1">折扣</el-radio>
                      <el-radio :label="2" :value="2">减现</el-radio>
                    </el-radio-group>
                  </div>
                  <div class="mt10 mb10 acea-row row-middle">
                    <span class="mr5" v-show="vipSetType == 2">减</span>
                    <el-input type="number" class="popover-input" v-model="vipSetNum">
                      <template #suffix>
                        <span v-show="vipSetType == 0">元</span>
                        <span v-show="vipSetType == 1">%</span>
                      </template>
                    </el-input>
                    <div class="acea-row row-right row-middle ml14">
                      <el-button @click="closeVipSet">取消</el-button>
                      <el-button type="primary" class="ml-14" @click="vipSetConfirm">确认</el-button>
                    </div>
                  </div>
                  <template #reference>
                    <span class="iconfont iconbianji1"></span>
                  </template>
                </el-popover>
              </template>
              <template #default="scope">
                <el-input type="number" v-model="scope.row.vip_price" @change="vipRowReplace(scope.row)">
                  <template #suffix>
                    <span>元</span>
                  </template>
                </el-input>
                <div class="flex-x-center red" v-show="scope.row.vip_price == 0">会员价不可为0</div>
                <div class="flex-x-center red" v-show="Number(scope.row.vip_price) > Number(scope.row.price)">
                  会员价不可大于售价
                </div>
              </template>
            </el-table-column>
          </el-table>
        </el-form-item>
      </el-form>
      <template #footer
        ><div class="dialog-footer">
          <el-button @click="onCancel">取消</el-button>
          <el-button type="primary" @click="submitForm" :disabled="disabled" class="ml-14">确认</el-button>
        </div></template
      >
    </el-dialog>
  </div>
</template>
<script setup>
import { ref, reactive, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { productBrokerage, productBrokerageUpdate } from '@/api/product';

defineOptions({ name: 'vipPriceSet' });

const props = defineProps({
  productId: {
    type: Number,
    default: 0,
  },
});

const vipSetPopover = ref(null);

const formData = reactive({
  is_vip: 0,
  vip_product: 0,
});
const brokerage = ref('');
const brokerage_two = ref('');
const loading = ref(false);
const attrData = ref([]);
const disabled = ref(false);
const levelList = ref([]);
const vipSetType = ref(0);
const vipSetNum = ref('');
const levelSetType = ref(0);
const levelSetNum = ref('');
const visible = ref(false);
const vipSetPopoverPopver = ref(false);

defineExpose({ visible });
const popoverTwo = ref(false);

watch(visible, (val) => {
  if (val) {
    getData();
  }
});

function vipPriceReplace(value) {
  vipSetNum.value = value;
}
// 实物商品 SKU 的 suk 存的是规格 JSON（如 {"颜色":"红色","尺寸":"大"}），转为可读文本；非 JSON 原样显示
function formatSuk(suk) {
  if (suk === null || suk === undefined || suk === '') return '';
  try {
    const obj = JSON.parse(suk);
    if (obj && typeof obj === 'object') {
      return Object.entries(obj)
        .map(([key, val]) => `${key}:${val}`)
        .join(' ');
    }
  } catch (e) {
    // 非 JSON 格式（如旧数据的纯文本规格）直接返回
  }
  return String(suk);
}
function levelPriceReplace(value) {
  levelSetNum.value = value;
}
function submitForm() {
  let isSuccess = true,
    step = true;
  if (formData.is_vip) {
    attrData.value.forEach((item) => {
      if (item.vip_price == 0 && formData.is_vip) {
        isSuccess = false;
      }
      if (Number(item.vip_price) > Number(item.price) && formData.is_vip) {
        step = false;
      }
    });
  }
  if (!isSuccess) return ElMessage.error('会员价不可为0');
  if (!step) return ElMessage.error('会员价不可大于售价');
  disabled.value = true;
  let data = {
    ...formData,
    attr_value: attrData.value,
  };
  productBrokerageUpdate(props.productId, 2, data)
    .then((res) => {
      ElMessage.success(res.msg);
      disabled.value = false;
      visible.value = false;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
      disabled.value = false;
    });
}
function closeVipSet() {
  // ElPopover（Element Plus 2.x）只暴露 hide()，没有旧版 Element UI 的 doClose()
  vipSetPopoverPopver.value = false;
  vipSetPopover.value?.hide?.();
  vipSetType.value = 0;
  vipSetNum.value = '';
}
function closeLevelSet(i) {
  popoverTwo.value = false;
  // this.$refs['popoverRef_' + i].doClose(); //关闭的
  levelSetType.value = 0;
  levelSetNum.value = '';
}
function vipSetConfirm() {
  if (vipSetNum.value == 0) return ElMessage.error('会员价不可为0');
  if (vipSetType.value == 1 && vipSetNum.value > 100) return ElMessage.error('折扣不可超过100');
  attrData.value.map((item) => {
    if (vipSetType.value == 0) {
      item.vip_price = vipSetNum.value;
    } else if (vipSetType.value == 1) {
      item.vip_price = ((vipSetNum.value / 100) * item.price).toFixed(2);
    } else {
      item.vip_price = item.price - vipSetNum.value;
    }
  });
  closeVipSet();
}
function levelSetConfirm(index) {
  if (levelSetNum.value == 0) return ElMessage.error('等级会员价不可为0');
  if (levelSetType.value == 1 && levelSetNum.value > 100) return ElMessage.error('折扣不可超过100');
  attrData.value.map((item) => {
    if (levelSetType.value == 0) {
      item.level_price[index].price = levelSetNum.value;
    } else if (levelSetType.value == 1) {
      item.level_price[index].price = ((levelSetNum.value / 100) * item.price).toFixed(2);
    } else {
      item.level_price[index].price = item.price - levelSetNum.value;
    }
  });
  closeLevelSet(index);
}
function onCancel() {
  visible.value = false;
}
function changeLevelType(type) {
  attrData.value.map((item) => {
    item['level_price'] = levelList.value.map((val) => {
      return {
        id: val.id,
        price: type == 1 ? val.discount * 100 : 0,
      };
    });
  });
}
function vipRowReplace(row) {
  // row.vip_price = row.vip_price.replace(/^-|\D/g, '');
}
function levelRowReplace(row, i) {
  row.level_price[i].price = row.level_price[i].price.replace(/^-|\D/g, '');
}
function getData() {
  //获取产品属性
  productBrokerage(props.productId, 2).then((res) => {
    formData.is_vip = res.data.storeInfo.is_vip;
    formData.vip_product = res.data.storeInfo.vip_product;
    attrData.value = Object.values(res.data.attrValue);
    // this.levelList = res.data.level_list;
    // if (res.data.level_list.length) {
    //   this.attrData.map((item) => {
    //     if (!item.level_price.length) {
    //       this.$set(
    //         item,
    //         'level_price',
    //         res.data.level_list.map((val) => {
    //           return {
    //             id: val.id,
    //             price: res.data.storeInfo.is_vip == 1 ? val.discount * 100 : 0,
    //           };
    //         }),
    //       );
    //     }
    //   });
    // }
  });
}
function changeVip(is_vip) {
  attrData.value.map((item) => {
    item['vip_price'] = is_vip == 1 ? item.price : 0;
  });
}
</script>
<style scoped>
.iconbianji1 {
  font-size: 12px;
  padding-left: 4px;
  cursor: pointer;
}

.popover-input {
  width: 100px;
}

.w-250 {
  width: 250px;
}

.pop-title {
  font-size: 14px;
  font-weight: bold;
  margin-bottom: 14px;
}

.red {
  color: #e93323;
}

.h-23 {
  height: 23px;
}
</style>
