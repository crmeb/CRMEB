<template>
  <div style="width: 100%">
    <Drawer title="用户详情" :closable="false" width="900" scrollable v-model="modals">
      <Spin size="large" fix v-if="spinShow"></Spin>
      <div class="acea-row">
        <div class="avatar mr15"><img :src="psInfo.avatar" /></div>
        <div class="dashboard-workplace-header-tip">
          <p class="dashboard-workplace-header-tip-title" v-text="psInfo.nickname || '-'"></p>
          <div class="dashboard-workplace-header-tip-desc">
            <span class="dashboard-workplace-header-tip-desc-sp" v-for="(item, index) in detailsData" :key="index">{{
              item.title + '：' + item.value
            }}</span>
          </div>
        </div>
      </div>

      <Row type="flex" justify="space-between" class="mt25">
        <!-- <Col span="4" class="user_menu">
          <Menu :theme="theme2" :active-name="activeName" @on-select="changeType">
            <MenuItem :name="item.val" v-for="(item, index) in list" :key="index">
              
            </MenuItem>
          </Menu>
        </Col> -->
        <Col span="24">
          <Tabs class="mb20" :value="activeName" @on-click="changeType">
            <TabPane :name="item.val" v-for="(item, index) in list" :key="index" :label="item.label"></TabPane>
          </Tabs>
        </Col>

        <Col span="24">
          <Table
            :columns="columns"
            :data="userLists"
            max-height="400"
            ref="table"
            :loading="loading"
            no-userFrom-text="暂无数据"
            no-filtered-userFrom-text="暂无筛选结果"
          >
            <template slot-scope="{ row }" slot="number">
              <div :class="row.pm ? 'plusColor' : 'reduceColor'">
                {{ row.pm ? '+' + row.number : '-' + row.number }}
              </div>
            </template>
          </Table>
          <div class="acea-row row-right page">
            <Page
              :total="total"
              :current="userFrom.page"
              show-elevator
              show-total
              @on-change="pageChange"
              :page-size="userFrom.limit"
            />
          </div>
        </Col>
      </Row>
    </Drawer>
  </div>
</template>

<script>
import { detailsApi, infoApi } from '@/api/user';

export default {
  name: 'userDetails',
  data() {
    return {
      theme2: 'light',
      list: [
        { val: 'order', label: '消费记录' },
        { val: 'integral', label: '积分明细' },
        { val: 'sign', label: '签到记录' },
        { val: 'coupon', label: '持有优惠券' },
        { val: 'balance_change', label: '余额变动' },
        { val: 'spread', label: '好友关系' },
      ],
      modals: false,
      spinShow: false,
      detailsData: [],
      userId: 0,
      loading: false,
      userFrom: {
        type: 'order',
        page: 1, // 当前页
        limit: 20, // 每页显示条数
      },
      total: 0,
      columns: [],
      userLists: [],
      psInfo: {},
      activeName: 'order',
    };
  },
  created() {},
  methods: {
    // 会员详情
    getDetails(id) {
      this.activeName = 'order';
      this.userId = id;
      this.spinShow = true;
      detailsApi(id)
        .then(async (res) => {
          if (res.status === 200) {
            let data = res.data;
            this.detailsData = data.headerList;
            this.psInfo = data.ps_info;
            this.changeType('order');
            this.spinShow = false;
          } else {
            this.spinShow = false;
            this.$Message.error(res.msg);
          }
        })
        .catch((res) => {
          this.spinShow = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.userFrom.page = index;
      this.changeType(this.userFrom.type);
    },
    // tab选项
    changeType(name) {
      this.loading = true;
      this.userFrom.type = name;
      this.activeName = name;
      if (this.userFrom.type === '') {
        this.userFrom.type = 'order';
      }
      let data = {
        id: this.userId,
        datas: this.userFrom,
      };
      infoApi(data)
        .then(async (res) => {
          if (res.status === 200) {
            let data = res.data;
            this.userLists = data.list;
            this.total = data.count;
            switch (this.userFrom.type) {
              case 'order':
                this.columns = [
                  {
                    title: '订单ID',
                    key: 'order_id',
                    minWidth: 160,
                  },
                  {
                    title: '收货人',
                    key: 'real_name',
                    minWidth: 100,
                  },
                  {
                    title: '商品数量',
                    key: 'total_num',
                    minWidth: 90,
                  },
                  {
                    title: '实付金额',
                    key: 'pay_price',
                    minWidth: 120,
                  },
                  {
                    title: '交易完成时间',
                    key: 'pay_time',
                    minWidth: 120,
                  },
                ];
                break;
              case 'integral':
                this.columns = [
                  {
                    title: '来源/用途',
                    key: 'title',
                    minWidth: 120,
                  },
                  {
                    title: '积分变化',
                    key: 'number',
                    minWidth: 120,
                  },
                  {
                    title: '变化前积分',
                    key: 'balance',
                    minWidth: 120,
                  },
                  {
                    title: '日期',
                    key: 'add_time',
                    minWidth: 120,
                  },
                  {
                    title: '备注',
                    key: 'mark',
                    minWidth: 120,
                  },
                ];
                break;
              case 'sign':
                this.columns = [
                  {
                    title: '动作',
                    key: 'title',
                    minWidth: 120,
                  },
                  {
                    title: '获得积分',
                    key: 'number',
                    minWidth: 120,
                  },
                  {
                    title: '签到时间',
                    key: 'add_time',
                    minWidth: 120,
                  },
                  {
                    title: '备注',
                    key: 'mark',
                    minWidth: 120,
                  },
                ];
                break;
              case 'coupon':
                this.columns = [
                  {
                    title: '优惠券名称',
                    key: 'coupon_title',
                    minWidth: 120,
                  },
                  {
                    title: '面值',
                    key: 'coupon_price',
                    minWidth: 120,
                  },
                  {
                    title: '有效期(天)',
                    key: 'coupon_time',
                    minWidth: 120,
                  },
                  {
                    title: '兑换时间',
                    key: '_add_time',
                    minWidth: 120,
                  },
                ];
                break;
              case 'balance_change':
                this.columns = [
                  {
                    title: '动作',
                    key: 'title',
                    minWidth: 120,
                  },
                  {
                    title: '余额变动',
                    slot: 'number',
                    minWidth: 120,
                  },
                  {
                    title: '当前余额',
                    key: 'balance',
                    minWidth: 120,
                  },
                  {
                    title: '创建时间',
                    key: 'add_time',
                    minWidth: 120,
                  },
                  {
                    title: '备注',
                    key: 'mark',
                    minWidth: 120,
                  },
                ];
                break;
              default:
                this.columns = [
                  {
                    title: 'ID',
                    key: 'uid',
                    minWidth: 120,
                  },
                  {
                    title: '昵称',
                    key: 'nickname',
                    minWidth: 120,
                  },
                  {
                    title: '等级',
                    key: 'type',
                    minWidth: 120,
                  },
                  {
                    title: '加入时间',
                    key: 'add_time',
                    minWidth: 120,
                  },
                ];
            }
            this.loading = false;
          } else {
            this.loading = false;
            this.$Message.error(res.msg);
          }
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style lang="less" scoped>
.avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  overflow: hidden;
  img {
    width: 100%;
    height: 100%;
  }
}
.dashboard-workplace {
  &-header {
    &-avatar {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      margin-right: 16px;
      font-weight: 600;
    }

    &-tip {
      width: 82%;
      display: inline-block;
      vertical-align: middle;

      &-title {
        font-size: 13px;
        color: #000000;
        margin-bottom: 12px;
      }

      &-desc {
        &-sp {
          width: 33.33%;
          color: #17233d;
          font-size: 13px;
          display: inline-block;
        }
      }
    }

    &-extra {
      .ivu-col {
        p {
          text-align: right;
        }

        p:first-child {
          span:first-child {
            margin-right: 4px;
          }

          span:last-child {
            color: #808695;
          }
        }

        p:last-child {
          font-size: 22px;
        }
      }
    }
  }
}
</style>
<style scoped lang="stylus">
.user_menu >>> .ivu-menu {
  width: 100% !important;
}
</style>
