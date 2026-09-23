<template>
  <!-- 会员价 -->
  <el-row>
    <el-col :span="24">
      <el-form-item label="会员专属：">
        <el-radio-group v-model="formValidate.vip_product">
          <el-radio :label="0" :value="0">不限制</el-radio>
          <el-radio :label="1" :value="1">仅会员可见</el-radio>
          <el-radio :label="2" :value="2">仅会员可买</el-radio>
        </el-radio-group>
        <div class="tip">
          会员只是付费会员，不限制：所有用户可见可买，仅会员可见：仅会员可见可买，仅会员可买：所有人可见仅会员可买
        </div>
      </el-form-item>
    </el-col>
    <el-col :span="8">
      <el-form-item label="付费会员价：">
        <el-checkbox :true-value="1" :false-value="0" v-model="formValidate.is_vip" @change="onVipChange"
          >开启单独设置</el-checkbox
        >
      </el-form-item>
    </el-col>
    <el-col :span="24" v-if="formValidate.is_vip == 1">
      <!--单规格会员价-->
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
              <template v-else-if="item.slot === 'original_price'">
                <span>{{ oneFormValidate[0].original_price }}</span>
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
                  @input="changeVipPrice(0, 'oneFormValidate')"
                ></el-input-number>
              </template>
              <template v-else-if="item.slot === 'vip_proportion'">
                <el-input-number
                  :controls="false"
                  v-model="oneFormValidate[0].vip_proportion"
                  :min="0"
                  :max="9999999999"
                  class="priceBox input-number-unit-class"
                  class-unit="%"
                  @input="changeDiscount(0, 'oneFormValidate')"
                ></el-input-number>
              </template>
            </template>
          </el-table-column>
        </el-table>
      </el-form-item>
      <!--多规格会员价-->
      <el-form-item label="批量设置：" v-if="formValidate.spec_type === 1">
        <span class="brokerage" v-if="formValidate.is_vip == 1">
          会员价：<el-input-number
            :controls="false"
            placeholder="请输入会员价"
            :min="0"
            :max="9999999999"
            class="columnsBox input_width input-number-unit-class"
            class-unit="元"
            :value="manyVipPrice"
            @input="(val) => emit('update:manyVipPrice', val)"
            @focus="emit('update:manyVipDiscount', undefined)"
          ></el-input-number>
        </span>
        <span class="brokerage" v-if="formValidate.is_vip == 1">
          会员折扣：<el-input-number
            :controls="false"
            placeholder="请输入折扣比例"
            :min="0"
            :max="9999999999"
            class="columnsBox input_width input-number-unit-class"
            class-unit="%"
            :value="manyVipDiscount"
            @input="(val) => emit('update:manyVipDiscount', val)"
            @focus="emit('update:manyVipPrice', undefined)"
          ></el-input-number>
        </span>
        <el-button type="primary" v-db-click @click="brokerageSetUp">批量设置</el-button>
      </el-form-item>
      <el-form-item
        label="商品属性："
        v-if="formValidate.spec_type == 1 && formValidate.is_vip && manyFormValidate.length && columnsInstal2"
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
              <template v-else-if="item.slot === 'original_price'">
                <span>{{ manyFormValidate[scope.$index + 1].original_price }}</span>
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
                  :value="manyFormValidate[scope.$index + 1].vip_price"
                  :min="0"
                  :max="9999999999"
                  class="priceBox input-number-unit-class"
                  class-unit="元"
                  @input="
                    (val) => {
                      const newData = [...manyFormValidate];
                      newData[scope.$index + 1].vip_price = val;
                      emit('update:manyFormValidate', newData);
                      changeVipPrice(scope.$index + 1);
                    }
                  "
                ></el-input-number>
              </template>
              <template v-else-if="item.slot === 'vip_proportion'">
                <el-input-number
                  :controls="false"
                  :value="manyFormValidate[scope.$index + 1].vip_proportion"
                  :min="0"
                  :max="9999999999"
                  class="priceBox input-number-unit-class"
                  class-unit="%"
                  @input="
                    (val) => {
                      const newData = [...manyFormValidate];
                      newData[scope.$index + 1].vip_proportion = val;
                      emit('update:manyFormValidate', newData);
                      changeDiscount(scope.$index + 1);
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
defineOptions({ name: 'PriceCommission' });

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
  // 批量设置的金额/折扣，undefined 表示未填写（会员价与折扣互斥清空时也会置为 undefined），不可声明 required
  manyVipPrice: {
    type: Number,
    default: undefined,
  },
  manyVipDiscount: {
    type: Number,
    default: undefined,
  },
});

const emit = defineEmits([
  'checkAllGroupChange',
  'changeVipPrice',
  'changeDiscount',
  'brokerageSetUp',
  'update:manyVipPrice',
  'update:manyVipDiscount',
  'update:manyFormValidate',
]);

function checkAllGroupChange(val) {
  emit('checkAllGroupChange', val);
}
function onVipChange() {
  emit('checkAllGroupChange');
}
function changeVipPrice(index, type) {
  emit('changeVipPrice', index, type);
}
function changeDiscount(index, type) {
  emit('changeDiscount', index, type);
}
function brokerageSetUp() {
  emit('brokerageSetUp');
}
</script>
<style lang="scss" scoped>
@use '../productAdd.scss' as *;
</style>
