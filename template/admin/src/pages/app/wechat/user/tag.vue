<template>
  <div>
    <Card :bordered="false" dis-hover class="save_from ivu-mt">
      <Button type="primary" icon="md-add" @click="add">{{ '添加' + $route.meta.title }}</Button>
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
        <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row)">编辑</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除标签', index)">删除</a>
        </template>
      </Table>
    </Card>
  </div>
</template>

<script>
import {
  wechatTagListApi,
  wechatTagCreateApi,
  wechatTagEditApi,
  wechatGroupListApi,
  wechatGroupCreateApi,
  wechatGroupEditApi,
} from '@/api/app';
import editFrom from '@/components/from/from';
export default {
  name: 'tag',
  components: { editFrom },
  data() {
    return {
      FromData: null,
      loading: false,
      tabList: [],
      columns1: [
        {
          title: 'ID',
          key: 'id',
          width: 80,
        },
        {
          title: '标签名',
          key: 'name',
          minWidth: 200,
        },
        {
          title: '人数',
          key: 'count',
          minWidth: 120,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 150,
        },
      ],
    };
  },
  watch: {
    $route(to, from) {
      this.getList();
    },
  },
  created() {
    this.getList();
  },
  methods: {
    // 添加
    add() {
      if (this.$route.path === '/admin/app/wechat/wechat_user/user/tag') {
        this.$modalForm(wechatTagCreateApi()).then(() => this.getList());
      } else {
        this.$modalForm(wechatGroupCreateApi()).then(() => this.getList());
      }
    },
    // 编辑
    edit(row) {
      if (this.$route.path === '/admin/app/wechat/wechat_user/user/tag') {
        this.$modalForm(wechatTagEditApi(row.id)).then(() => this.getList());
      } else {
        this.$modalForm(wechatGroupEditApi(row.id)).then(() => this.getList());
      }
    },
    // 删除
    del(row, tit, num) {
      let delfromData = null;
      if (this.$route.path === '/admin/app/wechat/wechat_user/user/tag') {
        delfromData = {
          title: tit,
          num: num,
          url: `app/wechat/tag/${row.id}`,
          method: 'DELETE',
          ids: '',
        };
      } else {
        delfromData = {
          title: tit,
          num: num,
          url: `app/wechat/group/${row.id}`,
          method: 'DELETE',
          ids: '',
        };
      }
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.tabList.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 列表
    getList() {
      this.loading = true;
      let fountion;
      if (this.$route.path === '/admin/app/wechat/wechat_user/user/tag') {
        fountion = wechatTagListApi();
      } else {
        fountion = wechatGroupListApi();
      }
      fountion
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list.list;
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
  },
};
</script>

<style scoped></style>
