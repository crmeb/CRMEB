<template>
  <div>
    <Modal
      v-model="modals"
      scrollable
      footer-hide
      closable
      :title="listTitle === 'man' ? '统计推广人列表' : '推广订单'"
      :mask-closable="false"
      width="900"
      @on-cancel="onCancel"
    >
      <div class="table_box">
        <Form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          class="tabform"
          @submit.native.prevent
        >
          <Row :gutter="24" type="flex" justify="end">
            <Col span="24" class="ivu-text-left">
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
            <Col span="24" class="ivu-text-left">
              <Col :xl="15" :lg="15" :md="20" :sm="24" :xs="24">
                <FormItem label="用户类型：">
                  <RadioGroup v-model="formValidate.type" type="button" @on-change="userSearchs" class="mr">
                    <Radio
                      :label="item.val"
                      v-for="(item, i) in listTitle === 'man' ? fromList.fromTxt2 : fromList.fromTxt3"
                      :key="i"
                    >
                      {{ item.text }}
                    </Radio>
                  </RadioGroup>
                </FormItem>
              </Col>
              <Col :xl="15" :lg="15" :md="20" :sm="24" :xs="24" v-if="listTitle === 'man'">
                <FormItem label="搜索：">
                  <Input
                    search
                    enter-button
                    placeholder="请输入请姓名、电话、UID"
                    v-model="formValidate.nickname"
                    style="width: 90%"
                    @on-search="userSearchs"
                  ></Input>
                </FormItem>
              </Col>
              <Col :xl="15" :lg="15" :md="20" :sm="24" :xs="24" v-if="listTitle === 'order'">
                <FormItem label="订单号：">
                  <Input
                    search
                    enter-button
                    placeholder="请输入请订单号"
                    v-model="formValidate.order_id"
                    style="width: 90%"
                    @on-search="userSearchs"
                  ></Input>
                </FormItem>
              </Col>
            </Col>
          </Row>
        </Form>
      </div>
      <Table
        ref="selection"
        :columns="columns4"
        :data="tabList"
        :loading="loading"
        no-data-text="暂无数据"
        highlight-row
        max-height="400"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="add_time">
          <div>{{ row.spread_time | formatDate }}</div>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page :total="total" show-elevator show-total @on-change="pageChange" :page-size="formValidate.limit" />
      </div>
    </Modal>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { stairListApi } from '@/api/agent';
import { formatDate } from '@/utils/validate';
export default {
  name: 'promotersList',
  // props: {
  //     listTitle: {
  //         type: String,
  //         default: ''
  //     }
  // },
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
        fromTxt2: [
          { text: '全部', val: '' },
          { text: '一级推广人', val: 1 },
          { text: '二级推广人', val: 2 },
        ],
        fromTxt3: [
          { text: '全部', val: '' },
          { text: '一级推广人订单', val: 1 },
          { text: '二级推广人订单', val: 2 },
        ],
      },
      formValidate: {
        limit: 15,
        page: 1,
        nickname: '',
        data: '',
        type: '',
        order_id: '',
        uid: 0,
      },
      loading: false,
      tabList: [],
      total: 0,
      timeVal: [],
      columns4: [],
      listTitle: '',
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 100;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  methods: {
    onCancel() {
      this.formValidate = {
        limit: 7,
        page: 1,
        nickname: '',
        data: '',
        type: '',
        order_id: '',
        uid: 0,
      };
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.data = this.timeVal.join('-');
      this.getList(this.rowsList, this.listTitle);
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.data = tab;
      this.timeVal = [];
      this.getList(this.rowsList, this.listTitle);
    },
    // 列表
    getList(row, tit) {
      this.listTitle = tit;
      this.rowsList = row;
      this.loading = true;
      let url = '';
      if (this.listTitle === 'man') {
        url = 'agent/stair';
      } else {
        url = 'agent/stair/order';
      }
      this.formValidate.uid = row.uid;
      stairListApi(url, this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list;
          this.total = data.count;
          if (this.listTitle === 'man') {
            this.columns4 = [
              {
                title: 'UID',
                minWidth: 80,
                key: 'uid',
              },
              {
                title: '头像',
                key: 'avatar',
                minWidth: 80,
                render: (h, params) => {
                  return h('viewer', [
                    h(
                      'div',
                      {
                        style: {
                          width: '36px',
                          height: '36px',
                          borderRadius: '4px',
                          cursor: 'pointer',
                        },
                      },
                      [
                        h('img', {
                          attrs: {
                            src: params.row.avatar ? params.row.avatar : require('../../../assets/images/moren.jpg'),
                          },
                          style: {
                            width: '100%',
                            height: '100%',
                          },
                        }),
                      ],
                    ),
                  ]);
                },
              },
              {
                title: '用户信息',
                key: 'nickname',
                minWidth: 120,
              },
              {
                title: '是否推广员',
                key: 'promoter_name',
                minWidth: 100,
              },
              {
                title: '推广人数',
                key: 'spread_count',
                sortable: true,
                minWidth: 90,
              },
              {
                title: '订单数',
                key: 'order_count',
                sortable: true,
                minWidth: 90,
              },
              {
                title: '绑定时间',
                slot: 'add_time',
                sortable: true,
                minWidth: 130,
              },
            ];
          } else {
            this.columns4 = [
              {
                title: '订单ID',
                key: 'order_id',
              },
              {
                title: '用户信息',
                key: 'user_info',
              },
              {
                title: '时间',
                key: '_add_time',
              },
              {
                title: '返佣金额',
                key: 'brokerage_price',
                render: (h, params) => {
                  return h('viewer', [h('span', params.row.brokerage_price || 0)]);
                },
              },
            ];
          }
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.tabList = [];
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.formValidate.page = index;
      this.getList(this.rowsList, this.listTitle);
    },
    // 搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList(this.rowsList, this.listTitle);
    },
  },
};
</script>

<style scoped></style>
