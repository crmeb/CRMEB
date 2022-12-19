<template>
  <div class="article-manager">
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="formValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col span="24">
            <FormItem label="时间选择：">
              <RadioGroup
                v-model="formValidate.data"
                type="button"
                @on-change="selectChange(formValidate.data)"
                class="mr"
              >
                <Radio :label="item.val" v-for="(item, i) in fromList.fromTxt" :key="i">{{ item.text }}</Radio>
              </RadioGroup>
              <DatePicker
                :editable="false"
                @on-change="onchangeTime"
                :value="timeVal"
                format="yyyy/MM/dd"
                type="daterange"
                placement="bottom-end"
                placeholder="请选择时间"
                style="width: 200px"
              ></DatePicker>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="砍价状态：">
              <Select v-model="formValidate.status" placeholder="请选择" clearable @on-change="userSearchs">
                <Option :value="1">进行中</Option>
                <Option :value="2">已失败</Option>
                <Option :value="3">已成功</Option>
              </Select>
            </FormItem>
          </Col>
        </Row>
      </Form>
      <Table
        :columns="columns1"
        :data="tableList"
        class="mt25"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="avatar">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="row.avatar" />
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="nickname">
          <span> {{ row.nickname + ' / ' + row.uid }}</span>
        </template>
        <template slot-scope="{ row, index }" slot="title">
          <span> {{ row.title }}</span>
        </template>
        <template slot-scope="{ row, index }" slot="add_time">
          <span> {{ row.add_time }}</span>
        </template>
        <template slot-scope="{ row, index }" slot="datatime">
          <span> {{ row.datatime }}</span>
        </template>
        <template slot-scope="{ row, index }" slot="status">
          <Tag color="blue" v-show="row.status === 1">进行中</Tag>
          <Tag color="volcano" v-show="row.status === 2">已失败</Tag>
          <Tag color="cyan" v-show="row.status === 3">已成功</Tag>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="Info(row)">查看详情</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="formValidate.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="formValidate.limit"
        />
      </div>
    </Card>

    <!-- 详情模态框-->
    <Modal
      v-model="modals"
      class="tableBox"
      scrollable
      footer-hide
      closable
      title="查看详情"
      :mask-closable="false"
      width="750"
    >
      <Table
        ref="selection"
        :columns="columns2"
        :data="tabList3"
        :loading="loading2"
        no-data-text="暂无数据"
        highlight-row
        max-height="600"
        size="small"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="nickname">
          <span> {{ row.nickname + ' / ' + row.uid }}</span>
        </template>
        <template slot-scope="{ row, index }" slot="avatar">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="row.avatar" />
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <Tag color="cyan" v-show="row.is_refund === 1">已退款</Tag>
          <Tag color="volcano" v-show="row.is_refund === 0">未退款</Tag>
        </template>
      </Table>
    </Modal>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { formatDate } from '@/utils/validate';
import { bargainUserListApi, bargainUserInfoApi } from '@/api/marketing';
export default {
  name: 'bargainList',
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  // components: { cardsData },
  data() {
    return {
      cardLists: [],
      modals: false,
      fromList: {
        title: '选择时间',
        custom: true,
        fromTxt: [
          { text: '全部', val: '' },
          { text: '今天', val: 'today' },
          { text: '昨天', val: 'yesterday' },
          { text: '最近7天', val: 'lately7' },
          { text: '最近30天', val: 'lately30' },
          { text: '本月', val: 'month' },
          { text: '本年', val: 'year' },
        ],
      },
      grid: {
        xl: 7,
        lg: 10,
        md: 12,
        sm: 12,
        xs: 24,
      },
      loading: false,
      formValidate: {
        status: '',
        data: '',
        page: 1,
        limit: 15,
      },
      columns1: [
        {
          title: '头像',
          slot: 'avatar',
          minWidth: 100,
        },
        {
          title: '发起用户',
          slot: 'nickname',
          minWidth: 150,
        },
        {
          title: '开启时间',
          key: 'add_time',
          minWidth: 150,
        },
        {
          title: '砍价商品',
          key: 'title',
          minWidth: 300,
        },
        {
          title: '最低价',
          key: 'bargain_price_min',
          minWidth: 120,
        },
        {
          title: '当前价',
          key: 'now_price',
          minWidth: 100,
        },
        {
          title: '总砍价次数',
          key: 'people_num',
          minWidth: 100,
        },
        {
          title: '剩余砍价次数',
          key: 'num',
          minWidth: 100,
        },
        {
          title: '结束时间',
          key: 'datatime',
          minWidth: 150,
        },
        {
          title: '状态',
          slot: 'status',
          minWidth: 100,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 170,
        },
      ],
      tableList: [],
      total: 0,
      timeVal: [],
      loading2: false,
      tabList3: [],
      columns2: [
        {
          title: '用户ID',
          key: 'uid',
          width: 80,
        },
        {
          title: '用户头像',
          slot: 'avatar',
        },
        {
          title: '用户名称',
          slot: 'nickname',
          minWidth: 100,
        },
        {
          title: '砍价金额',
          key: 'price',
        },
        {
          title: '砍价时间',
          key: 'add_time',
          minWidth: 100,
        },
      ],
      rows: {},
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 75;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  created() {
    this.getList();
  },
  methods: {
    // 查看详情
    Info(row) {
      this.modals = true;
      this.rows = row;
      bargainUserInfoApi(row.id)
        .then(async (res) => {
          let data = res.data;
          this.tabList3 = data.list;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.data = this.timeVal[0] ? this.timeVal.join('-') : '';
      this.formValidate.page = 1;
      this.getList();
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.page = 1;
      this.formValidate.data = tab;
      this.timeVal = [];
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      this.formValidate.status = this.formValidate.status || '';
      bargainUserListApi(this.formValidate)
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
      this.formValidate.page = index;
      this.getList();
    },
    // 表格搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
  },
};
</script>

<style scoped lang="stylus">
/deep/.ivu-tag-cyan .ivu-tag-text{
    color #19be6b!important
}
.ivu-tag-cyan{
    background: rgba(25,190,170,0.1);
    border-color: #19be6b!important;
}
.tabBox_img
    width 36px
    height 36px
    border-radius:4px
    cursor pointer
    img
        width 100%
        height 100%
</style>
