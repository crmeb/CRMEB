<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="formValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col v-bind="grid">
            <FormItem label="数据搜索：" label-for="status2">
              <Input
                search
                enter-button
                placeholder="请输入ID,KEY,数据组名称,简介"
                v-model="formValidate.title"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
        </Row>
        <Row type="flex">
          <Col v-bind="grid">
            <Button type="primary" icon="md-add" @click="groupAdd('添加数据组')" class="mr20">添加数据组</Button>
          </Col>
        </Row>
      </Form>
      <Table
        :columns="columns1"
        :data="tabList"
        ref="table"
        class="mt25"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="statuss">
          <i-switch
            v-model="row.status"
            :value="row.status"
            :true-value="1"
            :false-value="0"
            @on-change="onchangeIsShow(row)"
            size="large"
          >
            <span slot="open">显示</span>
            <span slot="close">隐藏</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="goList(row)">数据列表</a>
          <Divider type="vertical" />
          <a @click="edit(row, '编辑')">编辑</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除数据组', index)">删除</a>
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
    <!-- 新增 编辑-->
    <group-from
      ref="groupfroms"
      :titleFrom="titleFrom"
      :groupId="groupId"
      :addId="addId"
      @getList="getList"
    ></group-from>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import groupFrom from './components/groupFrom';
import { groupListApi } from '@/api/system';
export default {
  name: 'group',
  components: { groupFrom },
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      formValidate: {
        page: 1,
        limit: 20,
        title: '',
      },
      loading: false,
      tabList: [],
      total: 0,
      columns1: [
        {
          title: 'ID',
          key: 'id',
          width: 80,
        },
        {
          title: 'KEY',
          key: 'config_name',
          minWidth: 130,
        },
        {
          title: '数据组名称',
          key: 'name',
          minWidth: 130,
        },
        {
          title: '简介',
          key: 'info',
          minWidth: 130,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 150,
        },
      ],
      FromData: null,
      titleFrom: '',
      groupId: 0,
      addId: '',
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
  mounted() {
    this.getList();
  },
  methods: {
    // 跳转到组合数据列表页面
    goList(row) {
      this.$router.push({
        path: '/admin/system/config/system_group/list/' + row.id,
      });
    },
    // 列表
    getList() {
      this.loading = true;
      groupListApi(this.formValidate)
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
    // 表格搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
    // 点击添加
    groupAdd(title) {
      this.$refs.groupfroms.modals = true;
      this.titleFrom = title;
      this.addId = 'addId';
      this.groupId = 0;
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `setting/group/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.tabList.splice(num, 1);
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 编辑
    edit(row, title) {
      this.titleFrom = title;
      this.groupId = row.id;
      this.$refs.groupfroms.fromData(row.id);
      this.$refs.groupfroms.modals = true;
      this.addId = '';
    },
  },
};
</script>

<style scoped></style>
