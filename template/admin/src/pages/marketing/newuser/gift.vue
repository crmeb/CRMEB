<template>
  <div v-loading="spinShow">
    <div class="article-manager">
      <el-card :bordered="false" shadow="never" class="ivu-mt fromBox">
        <el-form ref="formRef" :model="formData" label-width="100px">
          <el-form-item label="赠送余额(元)：">
            <el-input-number
              class="form-width"
              v-model="formData.reward_money"
              placeholder="请输入赠送余额"
              :min="0"
            ></el-input-number>
            <div class="tips-info">新用户奖励金额，必须大于等于0，0为不赠送</div>
          </el-form-item>
          <el-form-item label="赠送积分：">
            <el-input-number
              class="form-width"
              v-model="formData.reward_integral"
              placeholder="请输入赠送积分数量"
              :min="0"
            ></el-input-number>
            <div class="tips-info">新用户奖励积分，必须大于等于0，0为不赠送</div>
          </el-form-item>
          <el-form-item label="赠送优惠券：">
            <div v-if="formData.reward_coupon.length" class="mb10">
              <el-tag
                class="mr10"
                closable
                v-for="(item, index) in formData.reward_coupon"
                :key="index"
                @close="handleClose(index)"
                >{{ item.title }}</el-tag
              >
            </div>
            <el-button v-db-click @click="addCoupon">选择优惠券</el-button>
          </el-form-item>
          <el-form-item label="">
            <el-button type="primary" v-db-click @click="submitForm">确认</el-button>
          </el-form-item>
        </el-form>
      </el-card>
    </div>
    <coupon-list ref="couponTemplates" :updateIds="updateIds" @nameId="nameId"></coupon-list>
  </div>
</template>

<script>
import couponList from '@/components/couponList';
import { editNewbie, getNewbie } from '@/api/marketing';
export default {
  name: 'NewUserGift',
  components: { couponList },
  data() {
    return {
      spinShow: false,
      formData: {
        reward_money: 0,
        reward_integral: 0,
        reward_coupon: [],
        updateIds: [],
      },
    };
  },
  created() {
    this.getInfo();
  },
  methods: {
    //对象数组去重；
    uniqueArray(arr) {
      const seen = {};
      return arr.filter((item) => {
        console.log(item)
        item.title =
          item.use_min_price !== '0.00'
            ? `${item.title} | 满${item.use_min_price}元 减 ${item.coupon_price}元`
            : `${item.title} | ${item.coupon_price}元 无门槛券`;
        delete item.use_min_price;
        delete item.coupon_price;
        const key = JSON.stringify(item); // 使用 JSON.stringify 生成唯一键
        if (seen[key]) {
          return false;
        } else {
          seen[key] = true;
          return true;
        }
      });
    },
    // 获取优惠券id数据
    nameId(id, names) {
      this.formData.reward_coupon = this.uniqueArray(names);
    },
    // 添加优惠券
    addCoupon() {
      this.$refs.couponTemplates.isTemplate = true;
      this.$refs.couponTemplates.tableList();
    },
    handleClose(index) {
      this.formData.reward_coupon.splice(index, 1);
    },
    getInfo() {
      this.spinShow = true;
      getNewbie()
        .then((res) => {
          this.spinShow = false;
          this.formData = res.data;
          this.updateIds = res.data.reward_coupon.map((item) => item.id);
        })
        .catch((err) => {
          this.spinShow = false;
          this.$message.error('获取失败');
        });
    },
    // 提交表单
    submitForm() {
      this.spinShow = true;
      editNewbie(this.formData)
        .then((res) => {
          this.spinShow = false;
          this.$message.success('提交成功');
        })
        .catch((err) => {
          this.spinShow = false;
          this.$message.error('提交失败');
        });
    },
  },
};
</script>
<style lang="scss" scoped></style>
