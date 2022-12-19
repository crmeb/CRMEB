<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Table
        ref="selection"
        :columns="columns4"
        :data="tabList"
        :loading="loading"
        no-data-text="暂无数据"
        highlight-row
        class="mt25"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row }" slot="nickname">
          <span>{{ row.admin_id + ' / ' + row.admin_name }}</span>
        </template>
      </Table>
    </Card>
  </div>
</template>

<script>
import { fileListApi } from '@/api/system';
export default {
  name: 'systemFile',
  data() {
    return {
      loading: false,
      tabList: [],
      columns4: [
        {
          title: '类型',
          key: 'type',
          minWidth: 100,
        },
        {
          title: '文件地址',
          key: 'filename',
          minWidth: 250,
        },
        {
          title: '校验码',
          key: 'cthash',
          minWidth: 200,
        },
        {
          title: '上次访问时间',
          key: 'atime',
          minWidth: 150,
        },
        {
          title: '上次修改时间',
          key: 'mtime',
          minWidth: 150,
        },
        {
          title: '上次改变时间',
          key: 'ctime',
          minWidth: 150,
        },
      ],
    };
  },
  created() {
    this.getList();
  },
  methods: {
    // 列表
    getList() {
      this.loading = true;
      fileListApi()
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped></style>
