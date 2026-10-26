<template>
  <!-- 网盘商品：仅会员价（无分销、无会员折扣） -->
  <el-row>
    <el-col :span="24">
      <el-form-item label="付费会员专属：">
        <el-radio-group v-model="formValidate.vip_product">
          <el-radio :label="0" :value="0">不限制（所有人可见可买）</el-radio>
          <el-radio :label="1" :value="1">仅会员可见可买</el-radio>
          <el-radio :label="2" :value="2">所有人可见仅会员可买</el-radio>
        </el-radio-group>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="会员价设置：">
        <el-checkbox v-model="formValidate.is_vip" :true-value="1" :false-value="0" @change="checkAllGroupChange"
          >付费会员价</el-checkbox
        >
      </el-form-item>
    </el-col>
    <el-col :span="24" v-if="formValidate.is_vip == 1">
      <!-- 单规格会员价 -->
      <el-form-item label="商品属性：" v-if="formValidate.spec_type === 0">
        <el-table :data="oneFormValidate">
          <el-table-column
            :label="item.title"
            :min-width="item.minWidth"
            v-for="(item, index) in columnsInstall"
            :key="index"
          >
            <template #default="scope">
              <template v-if="item.key">
                <div>
                  <span>{{ scope.row[item.key] }}</span>
                </div>
              </template>
              <template v-else-if="item.slot === 'pic'">
                <div class="pictrue pictrueTab">
                  <img v-lazy="oneFormValidate[0].pic" />
                </div>
              </template>
              <template v-else-if="item.slot === 'price'">
                <span>{{ oneFormValidate[0].price }}</span>
              </template>
              <template v-else-if="item.slot === 'cost'">
                <span>{{ oneFormValidate[0].cost }}</span>
              </template>
              <template v-else-if="item.slot === 'ot_price'">
                <span>{{ oneFormValidate[0].ot_price }}</span>
              </template>
              <template v-else-if="item.slot === 'stock'">
                <span>{{ oneFormValidate[0].stock }}</span>
              </template>
              <template v-else-if="item.slot === 'bar_code'">
                <span>{{ oneFormValidate[0].bar_code }}</span>
              </template>
              <template v-else-if="item.slot === 'bar_code_number'">
                <span>{{ oneFormValidate[0].bar_code_number }}</span>
              </template>
              <template v-else-if="item.slot === 'weight'">
                <span>{{ oneFormValidate[0].weight }}</span>
              </template>
              <template v-else-if="item.slot === 'vip_price'">
                <el-input-number
                  :controls="false"
                  v-model="oneFormValidate[0].vip_price"
                  :min="0"
                  :max="9999999999"
                  class="priceBox input-number-unit-class"
                  class-unit="元"
                ></el-input-number>
              </template>
            </template>
          </el-table-column>
        </el-table>
      </el-form-item>
      <!-- 多规格批量设置会员价 -->
      <el-form-item label="批量设置：" v-if="formValidate.spec_type === 1">
        <span class="brokerage">
          会员价：<el-input-number
            :controls="false"
            placeholder="请输入会员价"
            :min="0"
            :max="9999999999"
            class="columnsBox input_width input-number-unit-class"
            class-unit="元"
            :model-value="manyVipPrice"
            @update:model-value="(val) => emit('update:manyVipPrice', val)"
          ></el-input-number>
        </span>
        <el-button type="primary" v-db-click @click="brokerageSetUp">批量设置</el-button>
      </el-form-item>
      <el-form-item
        label="商品属性："
        v-if="formValidate.spec_type == 1 && manyFormValidate.length && columnsInstal2"
      >
        <el-table :data="manyFormValidate.slice(1)">
          <el-table-column
            :label="item.title"
            :min-width="item.minWidth"
            v-for="(item, index) in columnsInstal2"
            :key="index"
          >
            <template #default="scope">
              <template v-if="item.key">
                <div>
                  <span>{{ scope.row.detail[item.key] }}</span>
                </div>
              </template>
              <template v-else-if="item.slot === 'pic'">
                <div class="pictrue pictrueTab">
                  <img v-lazy="manyFormValidate[scope.$index + 1].pic" />
                </div>
              </template>
              <template v-else-if="item.slot === 'price'">
                <span>{{ manyFormValidate[scope.$index + 1].price }}</span>
              </template>
              <template v-else-if="item.slot === 'cost'">
                <span>{{ manyFormValidate[scope.$index + 1].cost }}</span>
              </template>
              <template v-else-if="item.slot === 'ot_price'">
                <span>{{ manyFormValidate[scope.$index + 1].ot_price }}</span>
              </template>
              <template v-else-if="item.slot === 'stock'">
                <span>{{ manyFormValidate[scope.$index + 1].stock }}</span>
              </template>
              <template v-else-if="item.slot === 'bar_code'">
                <span>{{ manyFormValidate[scope.$index + 1].bar_code }}</span>
              </template>
              <template v-else-if="item.slot === 'bar_code_number'">
                <span>{{ manyFormValidate[scope.$index + 1].bar_code_number }}</span>
              </template>
              <template v-else-if="item.slot === 'weight'">
                <span>{{ manyFormValidate[scope.$index + 1].weight }}</span>
              </template>
              <template v-else-if="item.slot === 'volume'">
                <span>{{ manyFormValidate[scope.$index + 1].volume }}</span>
              </template>
              <template v-else-if="item.slot === 'vip_price'">
                <el-input-number
                  :controls="false"
                  :model-value="manyFormValidate[scope.$index + 1].vip_price"
                  :min="0"
                  :max="9999999999"
                  class="priceBox input-number-unit-class"
                  class-unit="元"
                  @update:model-value="
                    (val) => {
                      updateManyFormValue(scope.$index + 1, 'vip_price', val);
                    }
                  "
                ></el-input-number>
              </template>
            </template>
          </el-table-column>
        </el-table>
      </el-form-item>
    </el-col>
  </el-row>
</template>

<script setup>
defineOptions({ name: 'VirtualPriceCommission' });

const props = defineProps({
  formValidate: {
    type: Object,
    required: true,
  },
  oneFormValidate: {
    type: Array,
    required: true,
  },
  manyFormValidate: {
    type: Array,
    required: true,
  },
  columnsInstall: {
    type: Array,
    required: true,
  },
  columnsInstal2: {
    type: Array,
    required: true,
  },
  manyVipPrice: {
    type: Number,
    default: undefined,
  },
});

const emit = defineEmits([
  'checkAllGroupChange',
  'brokerageSetUp',
  'update:manyVipPrice',
  'update:manyFormValidate',
]);

function checkAllGroupChange(val) {
  emit('checkAllGroupChange', val);
}
function updateManyFormValue(index, key, value) {
  const newData = props.manyFormValidate.map((item, itemIndex) =>
    itemIndex === index ? { ...item, [key]: value } : item,
  );
  emit('update:manyFormValidate', newData);
}
function brokerageSetUp() {
  emit('brokerageSetUp');
}
</script>
<style lang="scss" scoped>
@use '../../../../product/productAdd/productAdd.scss' as *;
</style>
