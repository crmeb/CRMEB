<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
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
              <Col :xl="7" :lg="10" :md="12" :sm="24" :xs="24">
                <FormItem label="操作名称：">
                  <Select v-model="formValidate.type" style="width: 90%" clearable>
                    <Option :value="1">男</Option>
                    <Option :value="2">女</Option>
                    <Option :value="0">保密</Option>
                  </Select>
                </FormItem>
              </Col>
              <Col :xl="7" :lg="10" :md="12" :sm="24" :xs="24">
                <FormItem label="操作用户：">
                  <Input placeholder="请输入用户名称" v-model="formValidate.nickname" style="width: 90%"></Input>
                </FormItem>
              </Col>
              <Col :xl="3" :lg="4" :md="12" :sm="24" :xs="24" class="btn_box">
                <FormItem>
                  <Button type="primary" icon="ios-search" label="default" class="userSearch" @click="userSearchs"
                    >搜索</Button
                  >
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
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="add_time">
          <span> {{ row.add_time ? row.add_time : '' | formatDate }}</span>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page :total="total" show-elevator show-total @on-change="pageChange" :page-size="formValidate.limit" />
      </div>
    </Card>
  </div>
</template>

<script>
import { formatDate } from '@/utils/validate';
import { mapState } from 'vuex';
import { wechatActionListApi } from '@/api/app';
export default {
  name: 'message',
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
      timeVal: [],
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
      formValidate: {
        limit: 15,
        page: 1,
        nickname: '',
        data: '',
        type: '',
      },
      loading: false,
      tabList: [],
      total: 0,
      columns4: [
        {
          title: 'ID',
          width: 80,
          key: 'id',
        },
        {
          title: '操作用户',
          key: 'nickname',
          minWidth: 120,
        },
        {
          title: '操作名称',
          key: 'type_name',
          minWidth: 120,
        },
        {
          title: '关联内容',
          key: 'headimgurl',
          minWidth: 150,
        },
        {
          title: '操作时间',
          slot: 'add_time',
          minWidth: 150,
        },
      ],
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    ...mapState('order', ['orderChartType']),
    labelWidth() {
      return this.isMobile ? undefined : 80;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  created() {
    this.getList();
  },
  methods: {
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.data = this.timeVal.join('-');
      this.getList();
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.data = tab;
      this.timeVal = [];
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      this.formValidate.type = this.formValidate.type ? this.formValidate.type : '';
      wechatActionListApi(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list;
          this.total = data.count;
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
    // 搜索
    userSearchs() {
      this.getList();
    },
    timeChange() {},
    Refresh() {},
  },
};
</script>

<style scoped lang="stylus">
.btn_box >>> .ivu-form-item-content
    margin-left 0 !important
</style>
