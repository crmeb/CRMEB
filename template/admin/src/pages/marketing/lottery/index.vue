<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="tableFrom"
        :model="tableFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col>
            <FormItem label="活动类型：" clearable>
              <Select
                style="width: 200px"
                v-model="tableFrom.factor"
                placeholder="请选择活动类型"
                clearable
                @on-change="userSearchs"
              >
                <Option value="1">积分抽取</Option>
                <!-- <Option value="2">余额</Option> -->
                <Option value="3">订单支付</Option>
                <Option value="4">订单评价</Option>
                <!-- <Option value="5">关注公众号</Option> -->
              </Select>
            </FormItem>
          </Col>
          <Col>
            <FormItem label="活动状态：" clearable>
              <Select
                style="width: 200px"
                v-model="tableFrom.start_status"
                placeholder="请选择"
                clearable
                @on-change="userSearchs"
              >
                <Option value="0">未开始</Option>
                <Option value="1">进行中</Option>
                <Option value="-1">已结束</Option>
              </Select>
            </FormItem>
          </Col>

          <Col>
            <FormItem label="上架状态：">
              <Select
                style="width: 200px"
                placeholder="请选择"
                v-model="tableFrom.status"
                clearable
                @on-change="userSearchs"
              >
                <Option value="1">上架</Option>
                <Option value="0">下架</Option>
              </Select>
            </FormItem>
          </Col>
          <Col>
            <FormItem label="抽奖搜索：" label-for="store_name">
              <Input
                search
                enter-button
                style="width: 200px"
                placeholder="请输入抽奖名称，ID"
                v-model="tableFrom.store_name"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
        </Row>
        <Row type="flex" class="mb20">
          <Button v-auth="['marketing-store_bargain-create']" type="primary" icon="md-add" @click="add" class="mr10"
            >添加抽奖</Button
          >
        </Row>
      </Form>
      <Table
        :columns="columns1"
        :data="tableList"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="is_fail">
          <Icon type="md-checkmark" v-if="row.is_fail === 1" color="#0092DC" size="14" />
          <Icon type="md-close" v-else color="#ed5565" size="14" />
        </template>
        <template slot-scope="{ row, index }" slot="image">
          <viewer>
            <div class="tabBox_img">
              <img v-lazy="row.image" />
            </div>
          </viewer>
        </template>

        <template slot-scope="{ row, index }" slot="bargain_min_price">
          <span>{{ row.bargain_min_price }}~{{ row.bargain_max_price }}</span>
        </template>
        <template slot-scope="{ row, index }" slot="status">
          {{ status == 0 ? '开启' : '关闭' }}
        </template>
        <template slot-scope="{ row, index }" slot="time">
          <div>起：{{ row.start_time || '--' }}</div>
          <div>止：{{ row.end_time || '--' }}</div>
        </template>
        <template slot-scope="{ row, index }" slot="status">
          <i-switch
            v-model="row.status"
            :value="row.status"
            :true-value="1"
            :false-value="0"
            :disabled="row.lottery_status == 2 ? true : false"
            @on-change="onchangeIsShow(row)"
            size="large"
          >
            <span slot="open">上架</span>
            <span slot="close">下架</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row)">编辑</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除抽奖', index)">删除</a>
          <Divider type="vertical" />
          <a @click="copy(row)">复制</a>
          <Divider type="vertical" />
          <a @click="getRecording(row)">抽奖记录</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="tableFrom.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="tableFrom.limit"
        />
      </div>
    </Card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { lotteryListApi, lotteryStatusApi } from '@/api/lottery';
import { formatDate } from '@/utils/validate';
export default {
  name: 'storeBargain',
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  data() {
    return {
      loading: false,
      columns1: [
        {
          title: 'ID',
          key: 'id',
          width: 80,
        },
        {
          title: '活动名称',
          key: 'name',
          minWidth: 90,
        },
        {
          title: '活动类型',
          key: 'lottery_type',
          minWidth: 130,
        },
        {
          title: '参与次数',
          key: 'lottery_all',
          minWidth: 100,
        },
        {
          title: '抽奖人数',
          key: 'lottery_people',
          minWidth: 100,
        },
        {
          title: '中奖人数',
          key: 'lottery_win',
          minWidth: 100,
        },
        {
          title: '活动状态',
          key: 'status_name',
          minWidth: 100,
        },
        {
          title: '上架状态',
          slot: 'status',
          minWidth: 100,
        },
        {
          title: '活动时间',
          slot: 'time',
          minWidth: 100,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 130,
        },
      ],
      tableList: [],
      tableFrom: {
        start_status: '',
        status: '',
        store_name: '',
        export: 0,
        page: 1,
        factor: '',
        limit: 15,
      },
      total: 0,
    };
  },
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 80;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  created() {
    this.getList();
  },
  methods: {
    // 添加
    add() {
      this.$router.push({ path: '/admin/marketing/lottery/create' });
    },
    // 编辑
    edit(row) {
      this.$router.push({
        name: 'marketing_create',
        query: {
          id: row.id,
        },
      });
    },
    // 一键复制
    copy(row) {
      this.$router.push({
        name: 'marketing_create',
        query: {
          id: row.id,
          copy: 1,
        },
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/lottery/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.tableList.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    //查看抽奖记录
    getRecording(row) {
      this.$router.push({
        path: `/admin/marketing/lottery/recording_list`,
        query: {
          id: row.id,
        },
      });
    },
    // 列表
    getList() {
      this.loading = true;
      this.tableFrom.start_status = this.tableFrom.start_status || '';
      this.tableFrom.status = this.tableFrom.status || '';
      lotteryListApi(this.tableFrom)
        .then(async (res) => {
          let data = res.data;
          this.tableList = data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.tableFrom.page = index;
      this.getList();
    },
    // 表格搜索
    userSearchs() {
      this.tableFrom.page = 1;
      this.getList();
    },
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        status: row.status,
      };
      lotteryStatusApi(data)
        .then(async (res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
          this.getList();
        });
    },
  },
};
</script>

<style scoped lang="stylus">
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}
</style>
