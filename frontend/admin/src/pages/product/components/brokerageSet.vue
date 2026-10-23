<template>
  <div>
    <el-dialog v-model="visible" title="自定义佣金" width="700">
      <el-form :model="formData" label-width="80px">
        <el-form-item label="返佣设置：">
          <el-radio-group v-model="formData.is_sub" @input="changeSubType">
            <el-radio :label="0" :value="0">默认比例</el-radio>
            <el-radio :label="1" :value="1">自定义佣金</el-radio>
          </el-radio-group>
          <div class="fs-12 tip" v-show="formData.is_sub">
            切换到默认比例时，表格中编辑的返佣金额会被清空，请谨慎操作
          </div>
        </el-form-item>
        <el-form-item label-width="0">
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
            <el-table-column min-width="120" align="center">
              <template #header="scope">
                <span>一级返佣</span>
                <el-popover
                  ref="popoverRef_one"
                  placement="top"
                  width="280"
                  trigger="click"
                  v-if="formData.is_sub == 1"
                >
                  <div class="pop-title">批量设置一级返佣</div>
                  <div class="mt-14">
                    <el-radio-group v-model="brokerageSetType">
                      <el-radio :label="0" :value="0">指定价格</el-radio>
                      <el-radio :label="1" :value="1">比例</el-radio>
                    </el-radio-group>
                  </div>
                  <div class="mt10 mb10 acea-row">
                    <el-input type="number" class="popover-input" v-model="brokerage" @input="brokerageReplace">
                      <template #suffix>
                        <span>{{ brokerageSetType ? '%' : '元' }}</span>
                      </template>
                    </el-input>
                    <div class="acea-row row-right row-middle ml14">
                      <el-button @click="closePop">取消</el-button>
                      <el-button type="primary" @click="brokerageOneSetUp">确认</el-button>
                    </div>
                  </div>
                  <template #reference><span class="iconfont iconbianji1"></span></template>
                </el-popover>
              </template>
              <template #default="scope">
                <el-input
                  v-model="scope.row.brokerage"
                  @change="brokerageRowReplace(scope.row)"
                  :disabled="formData.is_sub == 0"
                >
                  <template #suffix>
                    <span>{{ formData.is_sub ? '元' : '%' }}</span>
                  </template>
                </el-input>
                <!-- <div class="flex-x-center" v-show="formData.is_sub == 0">一级返佣：{{ (scope.row.price * store_brokerage_ratio).toFixed(2) }}</div> -->
                <!-- <div class="flex-x-center red" v-show="formData.is_sub == 1 && scope.row.brokerage == 0">
                  佣金不可为0
                </div> -->
                <div
                  class="flex-x-center red"
                  v-show="formData.is_sub == 1 && Number(scope.row.brokerage) > Number(scope.row.price)"
                >
                  佣金不可大于售价
                </div>
              </template>
            </el-table-column>
            <el-table-column min-width="120" align="center">
              <template #header="scope">
                <span>二级返佣</span>
                <el-popover
                  ref="popoverRef_two"
                  placement="top"
                  width="280"
                  trigger="click"
                  v-if="formData.is_sub == 1"
                >
                  <div class="pop-title">批量设置二级返佣</div>
                  <div class="mt-14">
                    <el-radio-group v-model="brokerageSetType">
                      <el-radio :label="0" :value="0">指定价格</el-radio>
                      <el-radio :label="1" :value="1">比例</el-radio>
                    </el-radio-group>
                  </div>
                  <div class="mt10 mb10 acea-row">
                    <el-input type="number" class="popover-input" v-model="brokerage_two">
                      <template #suffix>
                        <span>{{ brokerageSetType ? '%' : '元' }}</span>
                      </template>
                    </el-input>
                    <div class="acea-row row-right row-middle ml14">
                      <el-button @click="closePop">取消</el-button>
                      <el-button type="primary" @click="brokerageTwoSetUp">确认</el-button>
                    </div>
                  </div>

                  <template #reference><span class="iconfont iconbianji1"></span></template>
                </el-popover>
              </template>
              <template #default="scope">
                <el-input
                  type="number"
                  v-model="scope.row.brokerage_two"
                  @change="brokerageTwoRowReplace(scope.row)"
                  :disabled="formData.is_sub == 0"
                >
                  <template #suffix>
                    <span>{{ formData.is_sub ? '元' : '%' }}</span>
                  </template>
                </el-input>
                <!-- <div class="flex-x-center" v-show="formData.is_sub == 0">二级返佣：{{ (scope.row.price * store_brokerage_two).toFixed(2) }}</div> -->
                <!-- <div class="flex-x-center red" v-show="formData.is_sub == 1 && scope.row.brokerage_two == 0">
                  佣金不可为0
                </div> -->
                <div
                  class="flex-x-center red"
                  v-show="formData.is_sub == 1 && Number(scope.row.brokerage_two) > Number(scope.row.price)"
                >
                  佣金不可大于售价
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

defineOptions({ name: 'brokerageSet' });

const props = defineProps({
  productId: {
    type: Number,
    default: 0,
  },
});

const popoverRef_one = ref(null);
const popoverRef_two = ref(null);

const formData = reactive({
  is_sub: 0,
});
const brokerage = ref('');
const brokerage_two = ref('');
const loading = ref(false);
const attrData = ref([]);
const disabled = ref(false);
const brokerageSetType = ref(0);
const store_brokerage_ratio = ref('');
const store_brokerage_two = ref('');
const storeInfo = ref({});
const visible = ref(false);
const defaultAttrData = ref([]);

defineExpose({ visible });

watch(visible, (val) => {
  if (val) {
    getData();
  }
});

function brokerageReplace(value) {
  brokerage.value = value.replace(/^-|\D/g, '');
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
function submitForm() {
  let isSuccess = true,
    step = true;
  if (formData.is_sub) {
    attrData.value.forEach((item) => {
      if (item.brokerage == 0 || item.brokerage_two == 0) {
        isSuccess = true;
      }
      if (Number(item.brokerage) > Number(item.price) || Number(item.brokerage_two) > Number(item.price)) {
        step = false;
      }
    });
  }
  if (!isSuccess) return ElMessage.error('佣金不可为0');
  if (!step) return ElMessage.error('佣金不可为大于售价');
  disabled.value = true;
  let data = {
    ...formData,
    attr_value: attrData.value,
  };
  productBrokerageUpdate(props.productId, 1, data)
    .then((res) => {
      disabled.value = false;
      ElMessage.success(res.msg);
      visible.value = false;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
      disabled.value = false;
    });
}
function brokerageOneSetUp() {
  if (brokerage.value == 0) return ElMessage.error('价格和折扣不可为0');
  if (brokerageSetType.value == 1 && brokerage.value > 100) return ElMessage.error('折扣不可超过100');
  attrData.value.map((item) => {
    if (brokerageSetType.value == 0) {
      item.brokerage = brokerage.value;
    } else {
      item.brokerage = ((brokerage.value / 100) * item.price).toFixed(2);
    }
  });
  closePop();
}
function brokerageTwoSetUp() {
  if (brokerage_two.value == 0) return ElMessage.error('价格和折扣不可为0');
  if (brokerageSetType.value == 1 && brokerage_two.value > 100) return ElMessage.error('折扣不可超过100');
  attrData.value.map((item) => {
    if (brokerageSetType.value == 0) {
      item.brokerage_two = brokerage_two.value;
    } else {
      item.brokerage_two = ((brokerage_two.value / 100) * item.price).toFixed(2);
    }
  });
  closePop();
}
function brokerageRowReplace(item) {
  item.brokerage = item.brokerage.replace(/^-|\D/g, '');
}
function brokerageTwoRowReplace(item) {
  item.brokerage_two = item.brokerage_two.replace(/^-|\D/g, '');
}
function closePop() {
  // ElPopover（Element Plus 2.x）只暴露 hide()，没有旧版 Element UI 的 doClose()
  popoverRef_one.value?.hide?.();
  popoverRef_two.value?.hide?.();
  brokerage_two.value = '';
  brokerage.value = '';
}
function changeSubType(val) {
  attrData.value.map((item, i) => {
    item.brokerage = val == 0 ? storeInfo.value.store_brokerage_ratio : defaultAttrData.value[i].brokerage;
    item.brokerage_two = val == 0 ? storeInfo.value.store_brokerage_two : defaultAttrData.value[i].brokerage_two;
  });
}
function onCancel() {
  visible.value = false;
}
function getData() {
  //获取产品属性
  productBrokerage(props.productId, 1).then((res) => {
    let arr = JSON.parse(JSON.stringify(res.data.attrValue));
    defaultAttrData.value = Object.values(arr);
    attrData.value = Object.values(res.data.attrValue);
    formData.is_sub = res.data.storeInfo.is_sub;
    storeInfo.value = res.data.storeInfo;
    if (formData.is_sub == 0) {
      attrData.value.map((e) => {
        e.brokerage = storeInfo.value.store_brokerage_ratio;
        e.brokerage_two = storeInfo.value.store_brokerage_two;
      });
    }
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
</style>
