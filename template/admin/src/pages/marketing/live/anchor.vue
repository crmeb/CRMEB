<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Row type="flex">
        <Col v-bind="grid">
          <Button v-auth="['admin-user-label_add']" type="primary" icon="md-add" @click="add">添加主播</Button>
        </Col>
      </Row>
      <Table
        :columns="columns1"
        :data="labelLists"
        ref="table"
        class="mt25"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="icons">
          <viewer>
            <div class="tabBox_img">
              <img v-lazy="row.icon" />
            </div>
          </viewer>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row.id)">修改</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除分组', index)">删除</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page :total="total" show-elevator show-total @on-change="pageChange" :page-size="labelFrom.limit" />
      </div>
    </Card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { liveAuchorList, liveAuchorAdd, liveAuchorDel } from '@/api/live';
export default {
  name: 'anchor',
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      loading: false,
      columns1: [
        {
          title: 'ID',
          key: 'id',
          minWidth: 120,
        },
        {
          title: '名称',
          key: 'name',
          minWidth: 300,
        },
        {
          title: '电话',
          key: 'phone',
          minWidth: 300,
        },
        {
          title: '微信号',
          key: 'wechat',
          minWidth: 300,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 120,
        },
      ],
      labelFrom: {
        kerword: '',
        page: 1,
        limit: 15,
      },
      labelLists: [],
      total: 0,
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
    // 添加
    add() {
      this.$modalForm(liveAuchorAdd(0)).then(() => this.getList());
    },
    // 修改
    edit(id) {
      this.$modalForm(liveAuchorAdd(id)).then(() => this.getList());
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `live/anchor/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.labelLists.splice(num, 1);

          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 分组列表
    getList() {
      this.loading = true;
      liveAuchorList(this.labelFrom)
        .then(async (res) => {
          let data = res.data;
          this.labelLists = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.labelFrom.page = index;
      this.getList();
    },
  },
};
</script>

<style scoped></style>
