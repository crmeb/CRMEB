<template>
  <!-- 营销设置 -->
  <el-row>
    <el-col :span="24">
      <el-form-item label="购买送积分：" prop="give_integral">
        <el-input-number
          :controls="false"
          v-model="formValidate.give_integral"
          :min="0"
          :max="9999999999"
          placeholder="请输入积分"
          class="input_width input-number-unit-class"
          class-unit="积分"
        />
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="购买送优惠券：">
        <div v-if="couponName.length" class="mb10">
          <el-tag class="mr10" closable v-for="(item, index) in couponName" :key="index" @close="handleClose(item)">{{
            item.title
          }}</el-tag>
        </div>
        <el-button type="primary" v-db-click @click="addCoupon">选择优惠券</el-button>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="关联用户标签：" prop="label_id">
        <div style="display: flex">
          <div class="labelInput acea-row row-between-wrapper" v-db-click @click="openLabel">
            <div style="width: 90%">
              <div v-if="dataLabel.length">
                <el-tag closable v-for="(item, index) in dataLabel" @close="closeLabel(item)" :key="index">{{
                  item.label_name
                }}</el-tag>
              </div>
              <span class="span" v-else>选择用户关联标签</span>
            </div>
            <div class="iconfont iconxiayi"></div>
          </div>
          <span class="addfont" v-db-click @click="addLabel">新增标签</span>
        </div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <div class="line"></div>
    </el-col>
    <el-col v-if="formValidate.virtual_type == 0" :span="24">
      <el-form-item label="起购数量：">
        <el-input-number
          :controls="false"
          :min="1"
          :max="9999999999"
          :precision="0"
          v-model="formValidate.min_qty"
          placeholder="请输入起购数量"
          class="input_width input-number-unit-class"
          :class-unit="formValidate.unit_name || '件'"
        />
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="是否限购：">
        <el-switch
          v-model="formValidate.is_limit"
          class="defineSwitch"
          active-text="开启"
          inactive-text="关闭"
          :active-value="1"
          :inactive-value="0"
          size="large"
        >
        </el-switch>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="限购类型：" v-if="formValidate.is_limit">
        <el-radio-group v-model="formValidate.limit_type">
          <el-radio :label="1" :value="1">单次限购</el-radio>
          <el-radio :label="2" :value="2">单人限购</el-radio>
        </el-radio-group>
        <div class="tip">单次限购是限制每次下单最多购买的数量，单人限购是限制一个用户总共可以购买的数量</div>
      </el-form-item>
    </el-col>
    <el-col :span="24" v-if="formValidate.is_limit">
      <el-form-item label="限购数量：" prop="limit_num">
        <div class="acea-row row-middle">
          <el-input-number
            :controls="false"
            placeholder="请输入限购数量"
            :precision="0"
            :min="1"
            v-model="formValidate.limit_num"
            class="input_width input-number-unit-class"
            :class-unit="formValidate.unit_name || '件'"
          />
        </div>
      </el-form-item>
    </el-col>
    <el-col v-if="formValidate.is_limit" :span="24">
      <div class="line"></div>
    </el-col>
    <el-col :span="24" v-if="formValidate.virtual_type == 0 || formValidate.virtual_type == 3">
      <el-form-item label="预售商品：">
        <el-switch
          v-model="formValidate.presale"
          class="defineSwitch"
          active-text="开启"
          inactive-text="关闭"
          :active-value="1"
          :inactive-value="0"
          size="large"
        >
        </el-switch>
      </el-form-item>
    </el-col>
    <el-col :span="24" v-if="formValidate.presale">
      <el-form-item label="预售活动时间：" prop="presale_time">
        <div class="acea-row row-middle">
          <el-date-picker
            clearable
            :editable="false"
            type="datetimerange"
            format="YYYY-MM-DD HH:mm"
            value-format="YYYY-MM-DD HH:mm"
            range-separator="-"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            @change="onchangeTime"
            v-model="formValidate.presale_time"
          ></el-date-picker>
        </div>
        <div class="tip">设置活动开启结束时间，用户可以在设置时间内发起参与预售</div>
      </el-form-item>
    </el-col>
    <el-col :span="24" v-if="formValidate.presale">
      <el-form-item label="发货时间：" prop="presale_day">
        <div class="acea-row row-middle">
          <span class="mr10">预售活动结束后</span>
          <el-input-number
            class="w-80 input-number-unit-class"
            :controls="false"
            placeholder="请输入发货时间"
            :precision="0"
            :min="1"
            class-unit="天"
            v-model="formValidate.presale_day"
          />
          <span class="ml10"> 之内 </span>
        </div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <div class="line"></div>
    </el-col>
    <el-col :span="24">
      <el-form-item label="活动优先级：">
        <draggable
          class="color-list acea-row row-middle"
          :list="formValidate.activity"
          group="color"
          :item-key="activityItemKey"
        >
          <template #item="{ element: color }">
            <div class="color-item" :class="activity[color]">{{ color }}</div>
          </template>
        </draggable>
        <div class="tip">可拖动按钮调整活动的优先展示顺序</div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="优品推荐商品：">
        <div class="picBox">
          <div class="pictrue" v-for="(item, index) in formValidate.recommend_list" :key="index">
            <img v-lazy="item.image" />
            <i class="el-icon-error btndel" v-db-click @click="handleRemoveRecommend(index)"></i>
          </div>
          <div class="upLoad acea-row row-center-wrapper" v-db-click @click="changeGoods">
            <i class="el-icon-picture-outline" style="font-size: 24px"></i>
          </div>
        </div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <div class="line"></div>
    </el-col>
    <el-col :span="24">
      <el-form-item label="已售数量：">
        <el-input-number
          :controls="false"
          :min="0"
          :max="9999999999"
          v-model="formValidate.ficti"
          placeholder="请输入虚拟销量"
          class="input_width input-number-unit-class"
          :class-unit="formValidate.unit_name || '件'"
        />
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="排序：">
        <el-input-number
          :controls="false"
          :min="0"
          :max="9999999999"
          v-model="formValidate.sort"
          placeholder="请输入数字越大越靠前"
          class="input_width"
        />
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <div class="line"></div>
    </el-col>
    <el-col :span="24" v-if="formValidate.virtual_type == 0">
      <el-form-item label="开启送礼：">
        <el-switch
          v-model="formValidate.is_gift"
          class="defineSwitch"
          active-text="开启"
          inactive-text="关闭"
          :active-value="1"
          :inactive-value="0"
          size="large"
        >
        </el-switch>
        <div class="tip">开启送礼后，移动端商品详情的底部菜单显示送礼按钮</div>
      </el-form-item>
    </el-col>
    <el-col :span="24" v-if="formValidate.virtual_type == 0 && formValidate.is_gift">
      <el-form-item label="礼品附加费：">
        <el-input-number
          :controls="false"
          :min="0"
          :max="100000"
          v-model="formValidate.gift_price"
          placeholder="请输入礼品附加费"
          class="input_width input-number-unit-class"
          class-unit="元"
        />
        <div class="tip">送礼下单时，订单默认无运费，此费用可用于负担商品运费、产品包装等附加费用</div>
      </el-form-item>
    </el-col>
  </el-row>
</template>

<script setup>
import draggable from 'vuedraggable';

defineOptions({ name: 'MarketingSetting' });

defineProps({
  formValidate: {
    type: Object,
    required: true,
  },
  couponName: {
    type: Array,
    default: () => [],
  },
  dataLabel: {
    type: Array,
    default: () => [],
  },
  activity: {
    type: Object,
    default: () => ({}),
  },
});

// vuedraggable4 item-key（activity 为字符串数组，元素即键）
function activityItemKey(color) {
  return color;
}

const emit = defineEmits([
  'handleClose',
  'addCoupon',
  'openLabel',
  'closeLabel',
  'addLabel',
  'onchangeTime',
  'handleRemoveRecommend',
  'changeGoods',
]);

function handleClose(tag) {
  emit('handleClose', tag);
}
function addCoupon() {
  emit('addCoupon');
}
function openLabel() {
  emit('openLabel');
}
function closeLabel() {
  emit('closeLabel');
}
function addLabel() {
  emit('addLabel');
}
function onchangeTime(val) {
  emit('onchangeTime', val);
}
function handleRemoveRecommend(index) {
  emit('handleRemoveRecommend', index);
}
function changeGoods(val) {
  emit('changeGoods', val);
}
</script>
<style lang="scss" scoped>
@use '../productAdd.scss' as *;
</style>
