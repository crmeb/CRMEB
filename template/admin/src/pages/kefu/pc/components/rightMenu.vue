<template>
  <div class="right-wrapper">
    <template v-if="curStatus == 0">
      <div class="user-wrapper" v-if="activeUserInfo">
        <div class="user">
          <div class="avatar">
            <img v-lazy="activeUserInfo.avatar" alt="" />
          </div>
          <div class="name line1">{{ activeUserInfo.nickname }}</div>
          <div class="label">
            <template v-if="webType == 2">
              <span class="label routine">小程序</span>
            </template>
            <template v-if="webType == 3">
              <span class="label H5">H5</span>
            </template>
            <template v-if="webType == 1">
              <span class="label wechat">公众号</span>
            </template>
            <template v-if="webType == 0">
              <span class="label pc">PC端</span>
            </template>
          </div>
        </div>
        <div class="user-info">
          <div class="item">
            <span>手机号</span>
            {{ activeUserInfo.phone || '暂无' }}
          </div>
          <!-- <div class="item">
                        <span>分组</span>
                        <el-select v-model="activeUserInfo.group_id" size="small" @change="onChange" style="flex:1;">
                            <el-option v-for="item in userGroup" :value="item.id" :key="item.value">{{ item.group_name }}</el-option>
                        </el-select>
                    </div> -->
          <div class="label-list">
            <span>分组</span>
            <div class="con">
              <div class="label-item">{{ activeUserInfo.group_name }}</div>
            </div>
            <div class="right-icon" v-db-click @click.stop="isUserGroup = true">
              <i class="el-icon-arrow-right" style="font-size: 14px"></i>
            </div>
          </div>
          <div class="label-list">
            <span>用户标签</span>
            <div class="con">
              <div class="label-item" v-for="(item, index) in activeUserInfo.labelNames" :key="index">
                {{ item }}
              </div>
            </div>
            <div class="right-icon" v-db-click @click.stop="isUserLabel = true">
              <i class="el-icon-arrow-right" style="font-size: 14px"></i>
            </div>
          </div>
        </div>
        <div class="user-info">
          <div class="item">
            <span>用户等级</span>
            {{ activeUserInfo.level_name }}
          </div>
          <div class="item">
            <span>推荐人</span>
            {{ activeUserInfo.spread_name }}
          </div>
          <div class="item">
            <span>用户类型</span>
            {{ activeUserInfo.user_type | typeFilters }}
          </div>
          <div class="item">
            <span>余额</span>
            {{ activeUserInfo.now_money }}
          </div>
          <div class="item"><span>推广员</span>{{ activeUserInfo.is_promoter ? '是' : '否' }}</div>
          <div class="item">
            <span>生日</span>
            {{ activeUserInfo.birthday | getDay }}
          </div>
        </div>
      </div>
      <empty v-else status="2" msg="暂无用户信息"></empty>
    </template>
    <template v-if="curStatus == 1">
      <div class="order-wrapper">
        <div class="tab-head">
          <div
            class="tab-item"
            v-for="(item, index) in menuList"
            :key="index"
            :class="{ active: orderConfig.type === item.key }"
            v-db-click
            @click.stop="bindTab(item)"
          >
            {{ item.title }}
          </div>
        </div>
        <div class="search-box">
          <el-input
            class="search_box"
            prefix="ios-search"
            @on-enter="orderSearch"
            placeholder="搜索订单编号"
            v-model="orderConfig.searchTxt"
          />
        </div>
        <div v-if="orderList.length > 0">
          <div v-infinite-scroll="orderReachBottom" class="right-scroll">
            <div class="order-list">
              <div class="order-item" v-for="(item, index) in orderList" :key="index">
                <div class="head">
                  <div class="left">
                    <div class="font-box">
                      <span class="iconfont icondaishouhuo" v-if="item.status == 1"></span>
                      <span class="iconfont icondaifahuo" v-if="item.status == 0"></span>
                      <span class="iconfont icondaipingjia" v-if="item.status == 2"></span>
                      <span class="iconfont iconshouhou-tuikuan" v-if="item.status < 0"></span>
                    </div>
                    {{ item._status._title }}
                  </div>
                  <div class="time">{{ item._pay_time }}</div>
                </div>
                <div class="goods-list" :class="{ auto: !isOrderHidden }">
                  <div class="goods-item" v-for="goods in item.cartInfo" :key="goods.id">
                    <div class="img-box">
                      <img :src="goods.productInfo.image" alt="" />
                    </div>
                    <div class="info">
                      <div class="name line1">
                        {{ goods.productInfo.store_name }}
                      </div>
                      <div class="sku">
                        {{ goods.productInfo.attrInfo.suk }}
                      </div>
                      <div class="price">¥{{ goods.productInfo.price }} x {{ goods.cart_num }}</div>
                    </div>
                  </div>
                </div>
                <div
                  class="more-box"
                  v-if="item.cartInfo.length > 2"
                  v-db-click
                  @click.stop="isOrderHidden = !isOrderHidden"
                >
                  <span>{{ isOrderHidden ? '展开' : '合上' }}</span>
                </div>
                <div class="order-info">
                  <div class="info-item"><span>订单编号：</span>{{ item.order_id }}</div>
                  <div class="info-item">
                    <span>{{ item.refund_status == 1 ? '发起时间' : '付款时间' }}：</span
                    >{{ item.refund_status == 1 ? item.add_time : item._pay_time }}
                  </div>
                  <div class="info-item"><span>邮费：</span>¥ {{ item.pay_postage }}</div>
                  <div class="info-item"><span>实收款：</span>¥ {{ item.pay_price }}</div>
                </div>
                <div class="btn-wrapper">
                  <el-button
                    class="btn"
                    type="primary"
                    v-if="item._status._type == 1 && item._status._type != 0 && item.shipping_type != 2"
                    v-db-click
                    @click.stop="openDelivery(item)"
                    >发货</el-button
                  >
                  <el-button
                    class="btn"
                    type="primary"
                    v-if="item.refund_status == 1"
                    v-db-click
                    @click.stop="orderRecord(item.id)"
                    >退款</el-button
                  >
                  <el-button
                    class="btn"
                    ghost
                    v-db-click
                    type="primary"
                    @click.stop="orderPaid(item.id)"
                    v-if="item.pay_type == 'offline' && item.paid == 0"
                    >确认付款</el-button
                  >
                  <el-button
                    class="btn"
                    ghost
                    v-db-click
                    @click.stop="orderEdit(item.id)"
                    v-if="item._status._type == 0"
                    >改价</el-button
                  >
                  <el-button v-if="item.refund_status == 0" class="btn" ghost v-db-click @click.stop="bindRemark(item)"
                    >备注</el-button
                  >
                </div>
              </div>
            </div>
          </div>
        </div>
        <empty v-if="orderList.length == 0 && orderConfig.type === ''" status="3" msg="暂无订单信息"></empty>
        <empty v-if="orderList.length == 0 && orderConfig.type === 0" status="4" msg="暂无未支付订单"></empty>
        <empty v-if="orderList.length == 0 && orderConfig.type == 1" status="5" msg="暂无未发货订单"></empty>
        <empty v-if="orderList.length == 0 && orderConfig.type == -1" status="6" msg="暂无退款订单"></empty>
      </div>
    </template>
    <template v-if="curStatus == 2">
      <div class="goods-wrapper">
        <div class="goods-tab">
          <div
            class="tab-item"
            v-for="(item, index) in goodsTab"
            :key="index"
            :class="{ active: goodsConfig.type === item.key }"
            v-db-click
            @click.stop="bindGoodsTab(item)"
          >
            {{ item.title }}
          </div>
        </div>
        <div class="search-box">
          <el-input
            class="search_box"
            @on-enter="productSearch"
            v-model="storeName"
            prefix="ios-search"
            placeholder="搜索商品名称/ID"
          />
        </div>
        <div class="list-wrapper" v-if="goodsConfig.buyList.length > 0">
          <div v-infinite-scroll="goodsReachBottom" class="right-scroll">
            <div class="list-item" v-for="(item, index) in goodsConfig.buyList" :key="index">
              <div class="img-box">
                <img :src="item.image" alt="" />
              </div>
              <div class="info">
                <div class="name line1">{{ item.store_name }}</div>
                <div class="sku">
                  <span>库存：{{ item.stock }}</span>
                  <span>销量：{{ item.sales }}</span>
                </div>
                <div class="price">
                  <span>¥{{ item.price }}</span>
                  <div class="push" v-db-click @click.stop="pushGoods(item)">推送</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <empty v-else status="3" msg="暂无商品信息"></empty>
      </div>
    </template>
    <!-- 发货弹窗 -->
    <el-dialog :visible.sync="isDelivery" title="订单发送货">
      <delivery
        v-if="isDelivery"
        :virtualType="virtual_type"
        @close="deliveryClose"
        @ok="deliveryOk"
        :orderId="orderId"
      ></delivery>
    </el-dialog>
    <!-- 订单备注 -->
    <el-dialog :visible.sync="isRemarks" title="请修改内容" width="470px" :show-close="true" class="none-radius">
      <remarks :remarkId="remarkId" v-if="isRemarks" @close="deliveryClose" @remarkSuccess="remarkSuccess"></remarks>
    </el-dialog>
    <!-- 用户标签 -->
    <el-dialog title="选择用户标签" :visible.sync="isUserLabel" width="470px" class="label-box" :show-close="true">
      <p class="label-head" slot="header">
        <span>选择用户标签</span>
      </p>
      <userLabel v-if="isUserLabel" @close="deliveryClose" :uid="uid" @editLabel="editLabel"></userLabel>
    </el-dialog>
    <!-- 用户标签 -->
    <el-dialog :visible.sync="isUserGroup" title="选择分组" width="470px" class="label-box" :show-close="true">
      <p class="label-head" slot="header">
        <span>选择分组</span>
      </p>
      <userGroup
        v-if="isUserGroup"
        :groupId="activeUserInfo.group_id"
        :labelList="userGroup"
        @close="deliveryClose"
        :uid="uid"
        @editUserLabel="editUserLabel"
      ></userGroup>
    </el-dialog>
  </div>
</template>

<script>
import delivery from './delivery';
import remarks from './remarks';
import userLabel from './userLabel';
import userGroup from './userGroup';
import {
  userInfo,
  getorderList,
  orderEdit,
  orderRecord,
  productCart,
  productHot,
  productVisit,
  userGroupApi,
  putGroupApi,
} from '@/api/kefu';
import empty from '../../components/empty';
import dayjs from 'dayjs';
export default {
  name: 'rightMenu',
  components: {
    delivery,
    remarks,
    userLabel,
    userGroup,
    empty,
  },
  props: {
    isTourist: {
      type: String | Number,
      default: 0,
    },
    status: {
      type: String | Number,
      default: '',
    },
    //用户uid
    uid: {
      type: String | Number,
      default: '',
    },
    webType: {
      type: String | Number,
      default: '',
    },
  },
  filters: {
    statusFilters: function (value) {
      const statusMap = {
        '-1': '申请退款',
        '-2': '退货成功',
        0: '待发货',
        1: '待收货',
        2: '已收货',
        3: '待评价',
        '-1': '已退款',
      };
      return statusMap[value];
    },
    getDay(val) {
      if (val) {
        return dayjs.unix(val).format('YYYY年M月D日');
      }
    },
    typeFilters(value) {
      const statusMap = {
        h5: 'H5',
        wechat: '公众号',
        routine: '小程序',
        pc: 'PC',
      };
      return statusMap[value];
    },
  },
  data() {
    return {
      userGroup: [],
      userGroupSelect: [],
      model1: '',
      curMenuIndex: 0,
      virtual_type: 0,
      menuList: [
        {
          key: '',
          title: '全部',
        },
        {
          key: 0,
          title: '未支付',
        },
        {
          key: 1,
          title: '未发货',
        },
        {
          key: -1,
          title: '退款中',
        },
      ],
      activeUserInfo: '', //用户详情
      curStatus: this.status,
      limit: 15,
      orderConfig: {
        page: 1,
        type: '',
        searchTxt: '',
      },
      orderList: [],
      isOrderScroll: true,
      isOrderHidden: true,
      isDelivery: false, // 发货弹窗
      isRemarks: false, // 备注弹窗
      isUserGroup: false, //分组弹窗
      goodsTab: [
        {
          key: 0,
          title: '购买',
        },
        {
          key: 1,
          title: '足迹',
        },
        {
          key: 2,
          title: '热销',
        },
      ],
      isGoodsScroll: true,
      page: 1,
      goodsConfig: {
        type: 0,
        buyList: [],
      },
      isUserLabel: false,
      remarkId: '',
      orderId: '',
      storeName: '',
    };
  },
  watch: {
    uid(nVal, oVal) {
      if (nVal != oVal && this.isTourist == 0) {
        this.orderConfig.page = 1;
        this.isOrderScroll = true;
        this.orderList = [];
        this.page = 1;
        this.isGoodsScroll = true;
        this.goodsConfig.buyList = [];
        Promise.all[(this.getUserInfo(), this.getOrderList(), this.getUserGroup())];
        if (this.goodsConfig.type == 0) {
          this.productCart();
        } else if (this.goodsConfig.type == 1) {
          this.productVisit();
        } else {
          this.productHot();
        }
      }
    },
    isTourist(nVal, oVal) {
      if (nVal == 1) {
        this.activeUserInfo = '';
        this.orderList = [];
        this.goodsConfig.buyList = [];
      }
    },
  },
  mounted() {
    let self = this;
    this.bus.$on('selectRightMenu', (arg) => {
      this.curStatus = arg;
    });
    if (this.uid && this.isTourist == 0)
      Promise.all[(this.getUserInfo(), this.getOrderList(), this.productCart(), this.getUserGroup())];
  },
  methods: {
    // 设置分组
    onChange(e) {
      if (e) {
      }
    },
    //获取分组
    getUserGroup() {
      userGroupApi().then((res) => {
        this.userGroup = res.data;
      });
    },
    // 订单发货
    openDelivery(item) {
      this.orderId = item.id;
      this.virtual_type = item.virtual_type;
      this.isDelivery = true;
    },
    // 订单发货成功
    deliveryOk() {
      this.orderConfig.page = 1;
      this.isOrderScroll = true;
      this.orderList = [];
      this.getOrderList();
      this.isDelivery = false;
    },
    // 订单备注
    bindRemark(item) {
      this.remarkId = item.order_id;
      this.isRemarks = true;
    },
    remarkSuccess() {
      this.remarkId = '';
      this.isRemarks = false;
    },
    //获取左侧用户列表用户详情
    getUserInfo() {
      userInfo(this.uid)
        .then((res) => {
          this.activeUserInfo = res.data;
        })
        .catch((error) => {
          this.activeUserInfo = '';
        });
    },
    // 获取订单列表
    getOrderList() {
      if (!this.isOrderScroll) return;
      getorderList(this.uid, {
        page: this.orderConfig.page,
        limit: this.limit,
        type: this.orderConfig.type,
        search: this.orderConfig.searchTxt,
      }).then((res) => {
        this.orderConfig.page += 1;
        this.isOrderScroll = res.data.length >= this.limit;
        this.orderList = this.orderList.concat(res.data);
      });
    },
    // 订单tab
    bindTab(item) {
      if (this.orderConfig.type === item.key) return;
      this.orderConfig.type = item.key;
      if (this.uid) {
        this.orderConfig.page = 1;
        this.isOrderScroll = true;
        this.orderList = [];
        this.getOrderList();
      }
    },
    // 订单回车
    orderSearch() {
      this.isOrderScroll = true;
      this.orderList = [];
      this.orderConfig.page = 1;
      this.getOrderList();
    },
    // 关闭发货模态框
    deliveryClose() {
      this.isUserLabel = false;
      this.isDelivery = false;
      this.isRemarks = false;
      this.isUserGroup = false;
    },
    // 订单改价
    orderEdit(id) {
      this.$modalForm(orderEdit(id)).then(() => {
        this.orderConfig.page = 1;
        this.isOrderScroll = true;
        this.orderList = [];
        this.getOrderList();
      });
    },
    orderPaid(id) {
      this.$modalSure({
        title: '修改订单为已支付',
        url: `/order/pay_offline/${id}`,
        method: 'post',
        ids: '',
      })
        .then((res) => {
          this.orderConfig.page = 1;
          this.isOrderScroll = true;
          this.orderList = [];
          this.getOrderList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 订单退款
    orderRecord(id) {
      this.$modalForm(orderRecord(id)).then(() => this.getOrderList());
    },
    // 订单加载更多
    orderReachBottom() {
      this.getOrderList();
    },
    // 商品加载更多
    goodsReachBottom() {
      if (this.goodsConfig.type == 0) {
        this.productCart();
      } else if (this.goodsConfig.type == 1) {
        this.productVisit();
      } else {
        this.productHot();
      }
    },
    // 商品信息tab
    bindGoodsTab(item) {
      if (this.goodsConfig.type == item.key) return;
      this.goodsConfig.type = item.key;
      this.page = 1;
      this.isGoodsScroll = true;
      this.goodsConfig.buyList = [];
      if (item.key == 0) {
        this.productCart();
      } else if (item.key == 1) {
        this.productVisit();
      } else {
        this.productHot();
      }
    },
    // 商品购买记录
    productCart() {
      if (!this.isGoodsScroll) return;
      productCart(this.uid, {
        store_name: this.storeName,
        page: this.page,
        limit: this.limit,
      }).then((res) => {
        this.page += 1;
        this.isGoodsScroll = res.data.length >= this.limit;
        this.goodsConfig.buyList = this.goodsConfig.buyList.concat(res.data);
      });
    },
    // 商品足迹
    productVisit() {
      if (!this.isGoodsScroll) return;
      productVisit(this.uid, {
        store_name: this.storeName,
        page: this.page,
        limit: this.limit,
      }).then((res) => {
        this.page += 1;
        this.isGoodsScroll = res.data.length >= this.limit;
        this.goodsConfig.buyList = this.goodsConfig.buyList.concat(res.data);
      });
    },
    // 热销商品
    productHot() {
      productHot(this.uid, {
        store_name: this.storeName,
        page: this.page,
        limit: this.limit,
      }).then((res) => {
        this.page += 1;
        this.isGoodsScroll = res.data.length >= this.limit;
        this.goodsConfig.buyList = this.goodsConfig.buyList.concat(res.data);
      });
    },
    // 修改用户标签
    editLabel() {
      this.isUserLabel = false;
      this.getUserInfo();
    },
    editUserLabel(id) {
      this.isUserGroup = false;
      putGroupApi(this.uid, id).then((res) => {
        this.$message.success(res.msg);
        this.getUserInfo();
      });
    },
    // 商品推送
    pushGoods(item) {
      this.$emit('bindPush', item.id);
    },
    // 商品搜索
    productSearch() {
      this.page = 1;
      this.isGoodsScroll = true;
      this.goodsConfig.buyList = [];
      if (this.goodsConfig.type == 0) {
        this.productCart();
      } else if (this.goodsConfig.type == 1) {
        this.productVisit();
      } else {
        this.productHot();
      }
    },
  },
};
</script>

<style lang="scss" scoped>
::v-deep .ivu-select .ivu-select-dropdown,
::v-deep .ivu-date-picker .ivu-select-dropdown {
  top: unset !important;
}
.right-scroll {
  max-height: 650px;
  overflow-y: scroll;
}
.right-wrapper {
  width: 280px;
  .user-wrapper {
    padding: 0 8px;
    .user {
      display: flex;
      align-items: center;
      padding: 16px 0;
      color: #6440c2;

      border-bottom: 1px solid #ececec;
      .avatar {
        width: 42px;
        height: 42px;

        img {
          display: block;
          width: 100%;
          height: 100%;
          border-radius: 50%;
        }
      }
      .name {
        max-width: 150px;
        margin-left: 10px;
        font-size: 16px;
        color: rgba(0, 0, 0, 0.65);
      }
      .label {
        margin-left: 5px;
        font-size: 12px;
        border-radius: 2px;
        padding: 2px 5px;
        &.H5 {
          background: #faf1d0;
          color: #dc9a04;
        }
        &.wechat {
          background: rgba(64, 194, 73, 0.16);
          color: #40c249;
        }
        &.pc {
          background: rgba(100, 64, 194, 0.16);
          color: #6440c2;
        }
        .routine {
          color: #3875ea;
          background: #d8e5ff;
        }
      }
    }
  }
}
.user-info {
  padding-top: 15px;
  padding-bottom: 10px;
  border-bottom: 1px solid #ececec;
  .item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    font-size: 13px;
    color: #333;

    span {
      width: 70px;
      font-size: 13px;
      color: #666;
    }
  }
  .label-list {
    position: relative;
    display: flex;

    span {
      width: 70px;
      font-size: 13px;
      color: #666;
    }
    .con {
      display: flex;
      flex-wrap: wrap;
      flex: 1;
      .label-item {
        margin-right: 8px;
        margin-bottom: 8px;
        padding: 0 5px;
        color: var(--prev-color-primary);
        background: rgba(24, 144, 255, 0.1);
        font-size: 13px;
      }
    }
    .right-icon {
      position: absolute;
      right: 0;
      top: 0;
      cursor: pointer;
    }
  }
}
.order-wrapper {
  .tab-head {
    display: flex;
    align-items: center;
    height: 46px;
    border-bottom: 1px solid #ececec;
    .tab-item {
      position: relative;
      flex: 1;
      text-align: center;
      font-size: 14px;
      cursor: pointer;
      &.active {
        color: var(--prev-color-primary);
        font-size: 15px;
        font-weight: 600;
        &::after {
          content: ' ';
          position: absolute;
          left: 0;
          bottom: -12px;
          width: 100%;
          height: 2px;
          background: var(--prev-color-primary);
        }
      }
    }
  }
  .search-box {
    padding: 0 8px;
    margin-top: 12px;
    ::v-deep .ivu-input {
      border-radius: 17px;
    }
  }
  .order-list {
    padding: 0 8px;
    margin-top: 10px;
  }
  .order-item {
    margin-bottom: 18px;
    .head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 36px;
      padding: 0 10px;
      background: #f5f5f5;
      font-size: 13px;
      .left {
        display: flex;
        align-items: center;
        color: var(--prev-color-primary);
        .font-box {
          margin-right: 5px;
          .iconfont {
            font-size: 18px;
          }
        }
      }
    }
    .goods-list {
      max-height: 152px;
      overflow: hidden;
      &.auto {
        max-height: none;
      }
      .goods-item {
        display: flex;
        margin-top: 15px;
        .img-box {
          width: 60px;
          height: 60px;

          img {
            display: block;
            width: 100%;
            height: 100%;
            border-radius: 2px;
          }
        }
        .info {
          display: flex;
          flex-direction: column;
          justify-content: space-between;
          width: 180px;
          margin-left: 10px;
          font-size: 14px;
          .sku {
            font-size: 12px;
            color: #999999;
          }
        }
      }
    }
  }
  .more-box {
    text-align: right;
    color: var(--prev-color-primary);
    font-size: 13px;
    padding-right: 10px;

    span {
      cursor: pointer;
    }
  }
  .order-info {
    margin-top: 15px;
    .info-item {
      margin-bottom: 5px;
      font-size: 13px;

      span {
        display: inline-block;
        width: 70px;
        text-align: right;
      }
    }
  }
  .btn-wrapper {
    margin-top: 10px;
    .btn {
      &:last-child {
        margin-right: 0;
      }
    }
  }
}
.goods-wrapper {
  .goods-tab {
    display: flex;
    justify-content: space-between;
    padding: 0 40px;
    border-bottom: 1px solid #ececec;
    .tab-item {
      position: relative;
      height: 50px;
      line-height: 50px;
      font-size: 14px;
      cursor: pointer;
      &.active {
        color: var(--prev-color-primary);
        &::after {
          content: ' ';
          position: absolute;
          left: 0;
          bottom: 0;
          width: 100%;
          height: 2px;
          background: var(--prev-color-primary);
        }
      }
    }
  }
  .search-box {
    margin-top: 10px;
    padding: 0 8px;
    ::v-deep .ivu-input {
      border-radius: 17px;
    }
  }
  .list-wrapper {
    padding: 0 8px;
    .list-item {
      display: flex;
      margin-top: 15px;
      .img-box {
        width: 60px;
        height: 60px;

        img {
          display: block;
          width: 100%;
          height: 100%;
          border-radius: 2px;
        }
      }
      .info {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        width: 180px;
        margin-left: 10px;
        font-size: 14px;
        .sku {
          font-size: 12px;
          color: #999999;

          span {
            margin-right: 10px;
          }
        }
        .price {
          display: flex;
          justify-content: space-between;
          color: #ff0000;
          .push {
            color: var(--prev-color-primary);
            cursor: pointer;
          }
        }
      }
    }
  }
}
.label-box {
  ::v-deep .ivu-modal-header {
    padding: 0;
    border: 0;
    background: #fff;
    height: 50px;
    border-radius: 6px;
  }
  .label-head {
    height: 50px;
    line-height: 50px;
    text-align: center;
    font-size: 13px;
    color: #333333;
    border-bottom: 1px solid #f0f0f0;
  }
}
</style>
