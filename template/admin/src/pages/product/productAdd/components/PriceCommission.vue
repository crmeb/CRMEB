<template>
  <!-- 会员价/佣金 -->
  <el-row>
    <el-col :span="24">
      <el-form-item label="付费会员专属：">
        <el-switch :active-value="1" :inactive-value="0" v-model="formValidate.vip_product" size="large">
          <span slot="open">开启</span>
          <span slot="close">关闭</span>
        </el-switch>
        <div class="tips-info">开启后仅付费会员可以看见并购买此商品</div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="单独设置：">
        <el-checkbox-group v-model="formValidate.is_sub" @change="checkAllGroupChange">
          <el-checkbox :label="1">佣金设置（数字即返佣金额）</el-checkbox>
          <el-checkbox :label="0">付费会员价</el-checkbox>
        </el-checkbox-group>
      </el-form-item>
    </el-col>
    <el-col :span="24" v-if="formValidate.is_sub.length">
      <!--单规格返佣-->
      <el-form-item label="商品属性：" v-if="formValidate.spec_type === 0">
        <el-table :data="oneFormValidate">
          <el-table-column
            :label="item.title"
            :min-width="item.minWidth"
            v-for="(item, index) in columnsInstall"
            :key="index"
          >
            <template slot-scope="scope">
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
              <template v-else-if="item.slot === 'fictitious'">
                <el-button
                  v-if="!row.coupon_id && formValidate.virtual_type == 2"
                  v-db-click
                  @click="addGoodsCoupon(scope.$index, 'oneFormValidate')"
                  >选择优惠券</el-button
                >
                <span
                  class="see"
                  v-else-if="row.coupon_id && formValidate.virtual_type == 2"
                  v-db-click
                  @click="see(row, 'manyFormValidate', scope.$index)"
                  >{{ row.coupon_name }}</span
                >
                <el-button
                  v-else-if="!row.virtual_list.length && !row.stock && formValidate.virtual_type == 1"
                  v-db-click
                  @click="addVirtual(scope.$index, 'oneFormValidate')"
                  >添加卡密</el-button
                >
                <span
                  class="see"
                  v-else-if="(row.virtual_list.length || row.stock) && formValidate.virtual_type == 1"
                  v-db-click
                  @click="see(row, 'oneFormValidate', scope.$index)"
                  >已设置</span
                >
              </template>
              <template v-else-if="item.slot === 'brokerage'">
                <el-input-number
                  :controls="false"
                  v-model="oneFormValidate[0].brokerage"
                  :min="0"
                  :max="9999999999"
                  class="priceBox input-number-unit-class"
                  class-unit="元"
                ></el-input-number>
              </template>
              <template v-else-if="item.slot === 'brokerage_two'">
                <el-input-number
                  :controls="false"
                  v-model="oneFormValidate[0].brokerage_two"
                  :min="0"
                  :max="9999999999"
                  class="priceBox input-number-unit-class"
                  class-unit="元"
                ></el-input-number>
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
      <!--多规格返佣-->
      <el-form-item label="批量设置：" v-if="formValidate.spec_type === 1">
        <span v-if="formValidate.is_sub.indexOf(1) > -1">
          <span class="brokerage">一级返佣：</span
          ><el-input-number
            :controls="false"
            placeholder="请输入一级返佣"
            class="columnsBox input_width input-number-unit-class"
            class-unit="元"
            :value="manyBrokerage"
            @input="(val) => $emit('update:manyBrokerage', val)"
          >
          </el-input-number>
          <span class="brokerage">二级返佣：</span
          ><el-input-number
            :controls="false"
            placeholder="请输入二级返佣"
            class="columnsBox input_width input-number-unit-class"
            class-unit="元"
            :value="manyBrokerageTwo"
            @input="(val) => $emit('update:manyBrokerageTwo', val)"
          ></el-input-number>
        </span>
        <span class="brokerage" v-if="formValidate.is_sub.indexOf(0) > -1">
          会员价：<el-input-number
            :controls="false"
            placeholder="请输入会员价"
            :min="0"
            :max="9999999999"
            class="columnsBox input_width input-number-unit-class"
            class-unit="元"
            :value="manyVipPrice"
            @input="(val) => $emit('update:manyVipPrice', val)"
            @focus="$emit('update:manyVipDiscount', undefined)"
          ></el-input-number>
        </span>
        <span class="brokerage" v-if="formValidate.is_sub.indexOf(0) > -1">
          会员折扣：<el-input-number
            :controls="false"
            placeholder="请输入折扣比例"
            :min="0"
            :max="9999999999"
            class="columnsBox input_width input-number-unit-class"
            class-unit="%"
            :value="manyVipDiscount"
            @input="(val) => $emit('update:manyVipDiscount', val)"
            @focus="$emit('update:manyVipPrice', undefined)"
          ></el-input-number>
        </span>
        <el-button type="primary" v-db-click @click="brokerageSetUp">批量设置</el-button>
      </el-form-item>
      <el-form-item
        label="商品属性："
        v-if="formValidate.spec_type == 1 && formValidate.is_sub.length && manyFormValidate.length && columnsInstal2"
      >
        <el-table :data="manyFormValidate.slice(1)">
          <el-table-column
            :label="item.title"
            :min-width="item.minWidth"
            v-for="(item, index) in columnsInstal2"
            :key="index"
          >
            <template slot-scope="scope">
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
              <template v-else-if="item.slot === 'fictitious'">
                <el-button
                  v-if="!row.coupon_id && formValidate.virtual_type == 2"
                  v-db-click
                  @click="addGoodsCoupon(scope.$index + 1, 'manyFormValidate')"
                  >选择优惠券</el-button
                >
                <span
                  class="see"
                  v-else-if="row.coupon_id && formValidate.virtual_type == 2"
                  v-db-click
                  @click="see(row, 'manyFormValidate', scope.$index + 1)"
                  >{{ row.coupon_name }}</span
                >
                <el-button
                  v-else-if="!row.virtual_list.length && !row.stock && formValidate.virtual_type == 1"
                  v-db-click
                  @click="addVirtual(scope.$index + 1, 'manyFormValidate')"
                  >添加卡密</el-button
                >
                <span
                  class="see"
                  v-else-if="(row.virtual_list.length || row.stock) && formValidate.virtual_type == 1"
                  v-db-click
                  @click="see(row, 'manyFormValidate', scope.$index + 1)"
                  >已设置</span
                >
              </template>
              <template v-else-if="item.slot === 'volume'">
                <span>{{ manyFormValidate[scope.$index + 1].volume }}</span>
              </template>
              <template v-else-if="item.slot === 'brokerage'">
                <el-input-number
                  :controls="false"
                  :value="manyFormValidate[scope.$index + 1].brokerage"
                  :min="0"
                  :max="9999999999"
                  class="priceBox input-number-unit-class"
                  class-unit="元"
                  @input="
                    (val) => {
                      const newData = [...manyFormValidate];
                      newData[scope.$index + 1].brokerage = val;
                      $emit('update:manyFormValidate', newData);
                    }
                  "
                ></el-input-number>
              </template>
              <template v-else-if="item.slot === 'brokerage_two'">
                <el-input-number
                  :controls="false"
                  :value="manyFormValidate[scope.$index + 1].brokerage_two"
                  :min="0"
                  :max="9999999999"
                  class="priceBox input-number-unit-class"
                  class-unit="元"
                  @input="
                    (val) => {
                      const newData = [...manyFormValidate];
                      newData[scope.$index + 1].brokerage_two = val;
                      $emit('update:manyFormValidate', newData);
                    }
                  "
                ></el-input-number>
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
                      $emit('update:manyFormValidate', newData);
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
                      $emit('update:manyFormValidate', newData);
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

<script>
export default {
  name: 'PriceCommission',
  props: {
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
    manyBrokerage: {
      type: Number | undefined,
      required: true,
    },
    manyBrokerageTwo: {
      type: Number | undefined,
      required: true,
    },
    manyVipPrice: {
      type: Number | undefined,
      required: true,
    },
    manyVipDiscount: {
      type: Number | undefined,
      required: true,
    },
  },
  methods: {
    checkAllGroupChange(val) {
      this.$emit('checkAllGroupChange', val);
    },
    changeVipPrice(index, type) {
      this.$emit('changeVipPrice', index, type);
    },
    changeDiscount(index, type) {
      this.$emit('changeDiscount', index, type);
    },
    brokerageSetUp() {
      this.$emit('brokerageSetUp');
    },
  },
};
</script>
<style lang="scss" scoped>
@use '../productAdd.scss' as *;
</style>
