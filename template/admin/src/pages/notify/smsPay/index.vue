<template>
  <div v-loading="spinShow">
    <div class="i-layout-page-header header_top">
      <div class="i-layout-page-header fl_header">
        <router-link :to="{ path: $routeProStr + '/setting/sms/sms_config/index' }"
          ><el-button size="small" type="text">返回</el-button></router-link
        >
        <el-divider direction="vertical"></el-divider>
        <span class="ivu-page-header-title mr20" style="padding: 0">{{ $route.meta.title }}</span>
      </div>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-tabs v-model="isChecked" @tab-click="onChangeType">
        <el-tab-pane label="短信" name="sms"></el-tab-pane>
        <el-tab-pane label="商品采集" name="copy"></el-tab-pane>
        <el-tab-pane label="物流查询" name="expr_query"></el-tab-pane>
        <el-tab-pane label="电子面单打印" name="expr_dump"></el-tab-pane>
      </el-tabs>
      <el-row :gutter="16" class="mt50">
        <el-col :span="24" class="ivu-text-left mb20">
          <el-col :xs="12" :sm="6" :md="4" :lg="2" class="mr20">
            <span class="ivu-text-right ivu-block">当前剩余条数：</span>
          </el-col>
          <el-col :xs="11" :sm="13" :md="19" :lg="20">
            <span>{{ numbers }}</span>
          </el-col>
        </el-col>
        <el-col :span="24" class="ivu-text-left mb20">
          <el-col :xs="12" :sm="6" :md="4" :lg="2" class="mr20">
            <span class="ivu-text-right ivu-block">选择套餐：</span>
          </el-col>
          <el-col :xs="11" :sm="13" :md="19" :lg="20">
            <el-row :gutter="20">
              <el-col v-for="(item, index) in list" :key="index" :xxl="4" :xl="8" :lg="8" :md="12" :sm="24" :xs="24">
                <div
                  class="list-goods-list-item mb15"
                  :class="{ active: index === current }"
                  v-db-click
                  @click="check(item, index)"
                >
                  <div class="list-goods-list-item-title" :class="{ active: index === current }">
                    ¥ <i>{{ item.price }}</i>
                  </div>
                  <div class="list-goods-list-item-price" :class="{ active: index === current }">
                    <span>{{ all[isChecked] }}条数: {{ item.num }}</span>
                  </div>
                </div>
              </el-col>
            </el-row>
          </el-col>
        </el-col>
        <el-col :span="24" class="ivu-text-left mb20" v-if="checkList">
          <el-col :xs="12" :sm="6" :md="4" :lg="2" class="mr20">
            <span class="ivu-text-right ivu-block">充值条数：</span>
          </el-col>
          <el-col :xs="11" :sm="13" :md="19" :lg="20">
            <span>{{ checkList.num }}</span>
          </el-col>
        </el-col>
        <el-col :span="24" class="ivu-text-left mb20" v-if="checkList">
          <el-col :xs="12" :sm="6" :md="4" :lg="2" class="mr20">
            <span class="ivu-text-right ivu-block">支付金额：</span>
          </el-col>
          <el-col :xs="11" :sm="13" :md="19" :lg="20">
            <span class="list-goods-list-item-number">￥{{ checkList.price }}</span>
          </el-col>
        </el-col>
        <el-col :span="24" class="ivu-text-left mb20">
          <el-col :xs="12" :sm="6" :md="4" :lg="2" class="mr20">
            <span class="ivu-text-right ivu-block">付款方式：</span>
          </el-col>
          <el-col :xs="11" :sm="13" :md="19" :lg="20">
            <span class="list-goods-list-item-pay"
              >微信支付<i v-if="code.invalid">{{ '  （ 支付码过期时间：' + code.invalid + ' ）' }}</i></span
            >
          </el-col>
        </el-col>
        <el-col :span="24">
          <el-col :xs="12" :sm="6" :md="4" :lg="3" class="mr20">&nbsp;</el-col>
          <el-col :xs="11" :sm="13" :md="19" :lg="20">
            <div class="list-goods-list-item-code mr20"><img v-lazy="code.code_url" v-if="code.code_url" /></div>
          </el-col>
        </el-col>
      </el-row>
    </el-card>
  </div>
</template>

<script>
import { smsPriceApi, payCodeApi, isLoginApi, serveInfoApi } from '@/api/setting';
export default {
  name: 'smsPay',
  data() {
    return {
      all: { sms: '短信', copy: '商品采集', expr_query: '物流查询', expr_dump: '电子面单打印' },
      isChecked: 'sms',
      numbers: '',
      account: '',
      list: [],
      current: 0,
      checkList: {},
      spinShow: false,
      code: {},
    };
  },
  created() {
    this.isChecked = this.$route.query.type;
    this.onIsLogin();
  },
  methods: {
    // 查看是否登录
    onIsLogin() {
      this.spinShow = true;
      isLoginApi()
        .then(async (res) => {
          let data = res.data;
          if (!data.status) {
            this.$message.warning('请先登录');
            this.$router.push({
              path: this.$routeProStr + '/setting/sms/sms_config/index?url=' + this.$route.path,
              query: {
                type: this.$route.query.type,
              },
            });
          } else {
            this.getServeInfo();
            this.getPrice();
          }
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 平台用户信息
    getServeInfo() {
      serveInfoApi()
        .then(async (res) => {
          let data = res.data;
          switch (this.isChecked) {
            case 'sms':
              this.numbers = data.sms.num;
              break;
            case 'copy':
              this.numbers = data.copy.num;
              break;
            case 'expr_dump':
              this.numbers = data.dump.num;
              break;
            default:
              this.numbers = data.query.num;
              break;
          }
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    onChangeType() {
      this.current = 0;
      this.getPrice();
      this.getServeInfo();
    },
    // 支付套餐
    getPrice() {
      this.spinShow = true;
      smsPriceApi({ type: this.isChecked })
        .then(async (res) => {
          setTimeout(() => {
            this.spinShow = false;
          }, 800);
          let data = res.data;
          this.list = data.data;
          this.checkList = this.list[0];
          this.getCode(this.checkList);
        })
        .catch((res) => {
          this.spinShow = false;
          this.$message.error(res.msg);
          this.list = [];
        });
    },
    // 选中
    check(item, index) {
      this.spinShow = true;
      this.current = index;
      setTimeout(() => {
        this.getCode(item);
        this.checkList = item;
        this.spinShow = false;
      }, 800);
    },
    // 支付码
    getCode(item) {
      let data = {
        pay_type: 'weixin',
        meal_id: item.id,
        price: item.price,
        num: item.num,
        type: item.type,
      };
      payCodeApi(data)
        .then(async (res) => {
          this.code = res.data;
        })
        .catch((res) => {
          this.code = '';
          this.$message.error(res.msg);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.active {
  background: #0091ff;
  box-shadow: 0px 6px 20px 0px rgba(0, 145, 255, 0.3);
  color: #fff !important;
}
.list-goods-list-item {
  border: 1px solid #dadfe6;
  padding: 20px 10px;
  box-sizing: border-box;
  border-radius: 3px;
}
.list-goods-list {
  &-item {
    text-align: center;
    position: relative;
    cursor: pointer;
    img {
      width: 60%;
    }
    .ivu-tag {
      position: absolute;
      top: 10px;
      right: 10px;
    }
    &-title {
      font-size: 16px;
      font-weight: bold;
      color: #0091ff;
      margin-bottom: 3px;
      i {
        font-size: 30px;
        font-style: normal;
      }
    }
    &-desc {
      font-size: 14px;
      color: #808695;
    }
    &-price {
      font-size: 14px;
      color: #000000;
      s {
        color: #c5c8ce;
      }
    }
    &-number {
      font-size: 14px;
      color: #ed4014;
    }
    &-pay {
      font-size: 14px;
      color: #00c050;
      i {
        font-size: 12px;
        font-style: normal;
        color: #6d7278;
      }
    }
    &-code {
      width: 130px;
      height: 130px;
      img {
        width: 100%;
        height: 100%;
      }
    }
  }
}
</style>
