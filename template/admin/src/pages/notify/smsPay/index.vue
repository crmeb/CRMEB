<template>
  <div>
    <div class="i-layout-page-header header_top">
      <div class="i-layout-page-header fl_header">
        <router-link :to="{ path: '/admin/setting/sms/sms_config/index' }"
          ><Button icon="ios-arrow-back" size="small" type="text">返回</Button></router-link
        >
        <Divider type="vertical" />
        <span class="ivu-page-header-title mr20" style="padding: 0">{{ $route.meta.title }}</span>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Tabs v-model="isChecked" @on-click="onChangeType">
        <TabPane label="短信" name="sms"></TabPane>
        <TabPane label="商品采集" name="copy"></TabPane>
        <TabPane label="物流查询" name="expr_query"></TabPane>
        <TabPane label="电子面单打印" name="expr_dump"></TabPane>
      </Tabs>
      <Row :gutter="16" class="mt50">
        <Col span="24" class="ivu-text-left mb20">
          <Col :xs="12" :sm="6" :md="4" :lg="2" class="mr20">
            <span class="ivu-text-right ivu-block">当前剩余条数：</span>
          </Col>
          <Col :xs="11" :sm="13" :md="19" :lg="20">
            <span>{{ numbers }}</span>
          </Col>
        </Col>
        <Col span="24" class="ivu-text-left mb20">
          <Col :xs="12" :sm="6" :md="4" :lg="2" class="mr20">
            <span class="ivu-text-right ivu-block">选择套餐：</span>
          </Col>
          <Col :xs="11" :sm="13" :md="19" :lg="20">
            <Row :gutter="20">
              <Col v-for="(item, index) in list" :key="index" :xxl="4" :xl="8" :lg="8" :md="12" :sm="24" :xs="24">
                <div
                  class="list-goods-list-item mb15"
                  :class="{ active: index === current }"
                  @click="check(item, index)"
                >
                  <div class="list-goods-list-item-title" :class="{ active: index === current }">
                    ¥ <i>{{ item.price }}</i>
                  </div>
                  <div class="list-goods-list-item-price" :class="{ active: index === current }">
                    <span>{{ all[isChecked] }}条数: {{ item.num }}</span>
                  </div>
                </div>
              </Col>
            </Row>
          </Col>
        </Col>
        <Col span="24" class="ivu-text-left mb20" v-if="checkList">
          <Col :xs="12" :sm="6" :md="4" :lg="2" class="mr20">
            <span class="ivu-text-right ivu-block">充值条数：</span>
          </Col>
          <Col :xs="11" :sm="13" :md="19" :lg="20">
            <span>{{ checkList.num }}</span>
          </Col>
        </Col>
        <Col span="24" class="ivu-text-left mb20" v-if="checkList">
          <Col :xs="12" :sm="6" :md="4" :lg="2" class="mr20">
            <span class="ivu-text-right ivu-block">支付金额：</span>
          </Col>
          <Col :xs="11" :sm="13" :md="19" :lg="20">
            <span class="list-goods-list-item-number">￥{{ checkList.price }}</span>
          </Col>
        </Col>
        <Col span="24" class="ivu-text-left mb20">
          <Col :xs="12" :sm="6" :md="4" :lg="2" class="mr20">
            <span class="ivu-text-right ivu-block">付款方式：</span>
          </Col>
          <Col :xs="11" :sm="13" :md="19" :lg="20">
            <span class="list-goods-list-item-pay"
              >微信支付<i v-if="code.invalid">{{ '  （ 支付码过期时间：' + code.invalid + ' ）' }}</i></span
            >
          </Col>
        </Col>
        <Col span="24">
          <Col :xs="12" :sm="6" :md="4" :lg="3" class="mr20">&nbsp;</Col>
          <Col :xs="11" :sm="13" :md="19" :lg="20">
            <div class="list-goods-list-item-code mr20"><img v-lazy="code.code_url" v-if="code.code_url" /></div>
          </Col>
        </Col>
        <Spin size="large" fix v-if="spinShow"></Spin>
      </Row>
    </Card>
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
            this.$Message.warning('请先登录');
            this.$router.push('/admin/setting/sms/sms_config/index?url=' + this.$route.path);
          } else {
            this.getServeInfo();
            this.getPrice();
          }
        })
        .catch((res) => {
          this.$Message.error(res.msg);
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
          this.$Message.error(res.msg);
        });
    },
    onChangeType(val) {
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
          this.$Message.error(res.msg);
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
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style lang="less" scoped>
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
