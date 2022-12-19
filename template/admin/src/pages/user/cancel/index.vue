<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="levelFrom"
        :model="levelFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col v-bind="grid">
            <FormItem label="状态：" label-for="status1">
              <!-- <Select
                v-model="levelFrom.status"
                placeholder="请选择"
                clearable
                element-id="status1"
                @on-change="userSearchs"
              >
                <Option value="0">待审核</Option>
                <Option value="1">通过</Option>
                <Option value="1">拒绝</Option>
              </Select> -->
              <RadioGroup v-model="levelFrom.status" type="button" @on-change="userSearchs(levelFrom.status)">
                <Radio label="">全部</Radio>
                <Radio label="0">待审核</Radio>
                <Radio label="1">通过</Radio>
                <Radio label="2">拒绝</Radio>
              </RadioGroup>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="用户搜索：" label-for="title">
              <Input
                search
                enter-button
                v-model="levelFrom.keywords"
                placeholder="请输入用户昵称/ID/手机号"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
        </Row>
      </Form>
      <Table
        :columns="columns1"
        :data="levelLists"
        ref="table"
        class="mt25"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="action">
          <a @click="agree(row)">同意</a>
          <Divider type="vertical" />
          <a @click="refuse(row)">拒绝</a>
          <Divider type="vertical" />
          <a @click="remark(row)">备注</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="levelFrom.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="levelFrom.limit"
        />
      </div>
    </Card>
    <!-- 等级任务-->
    <remark ref="remark" @submitFail="submitFail"></remark>
  </div>
</template>
<script>
import { mapState, mapMutations } from 'vuex';
import { userCancelList, userCancelSetMark } from '@/api/user';
import taskList from './handle/task';
import editFrom from '@/components/from/from';
import remark from '@/components/remark/index';
export default {
  name: 'user_level',
  components: { remark },
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      id: '',
      loading: false,
      columns1: [
        {
          title: 'ID',
          key: 'id',
          width: 80,
        },
        {
          title: '昵称',
          key: 'name',
          minWidth: 100,
        },
        {
          title: '手机号',
          key: 'phone',
          minWidth: 100,
        },
        {
          title: '状态',
          key: 'status',
          minWidth: 120,
        },
        {
          title: '申请时间',
          key: 'add_time',
          minWidth: 100,
        },
        {
          title: '审核时间',
          key: 'up_time',
          minWidth: 100,
        },
        {
          title: '备注',
          key: 'remark',
          minWidth: 100,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 120,
        },
      ],
      levelFrom: {
        status: '',
        keywords: '',
        page: 1,
        limit: 15,
      },
      levelLists: [],
      total: 0,
    };
  },
  created() {
    this.getList();
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
  methods: {
    ...mapMutations('userLevel', ['getlevelId']),
    remark(row) {
      this.id = row.id;
      this.$refs.remark.formValidate.remark = row.remark;
      this.$refs.remark.modals = true;
    },
    agree(row) {
      this.delfromData = {
        title: '注销用户',
        url: `/user/cancel/agree/${row.id}`,
        method: 'get',
      };
      this.$modalSure(this.delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    refuse(row) {
      this.delfromData = {
        title: '拒绝注销用户',
        info: '您确认拒绝注销此用户吗?',
        url: `/user/cancel/refuse/${row.id}`,
        method: 'get',
      };
      this.$modalSure(this.delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    submitFail(text) {
      let data = {
        id: this.id,
        mark: text,
      };
      userCancelSetMark(data)
        .then((res) => {
          this.$refs.remark.modals = false;
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((err) => {
          this.$refs.remark.modals = false;
          this.$Message.error(err.msg);
        });
    },

    getList() {
      this.loading = true;
      this.levelFrom.status = this.levelFrom.status || '';
      userCancelList(this.levelFrom)
        .then(async (res) => {
          let data = res.data;
          this.levelLists = data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.levelFrom.page = index;
      this.getList();
    },
    // 表格搜索
    userSearchs() {
      this.levelFrom.page = 1;
      this.getList();
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
