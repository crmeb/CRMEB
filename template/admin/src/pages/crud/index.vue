<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Row type="flex">
        <Col v-bind="grid">
          <Button type="primary" icon="md-add" @click="add">添加</Button>
        </Col>
      </Row>
      <Table
        :columns="columns"
        :data="dataList"
        ref="table"
        class="mt25"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row)">修改</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除', index)">删除</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page :total="total" show-elevator show-total @on-change="pageChange" :page-size="from.limit" />
      </div>
    </Card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { crudApi, getList, getCreateApi, getEditApi } from '@/api/crud.js';

export default {
  name: 'user_recharge',
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
      columns: [],
      from: {
        page: 1,
        limit: 15,
      },
      dataList: [],
      total: 0,
      methodApi: {},
      curdKey: '',
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 75;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  beforeRouteUpdate(to, from, next) {
    this.from.page = 1;
    this.getCrudApi(to.params.table_name);
    next();
  },
  created() {
    this.getCrudApi(this.$route.params.table_name);
  },
  methods: {
    getCrudApi(tableName) {
      crudApi(tableName).then((res) => {
        this.methodApi = res.data.route;
        this.curdKey = res.data.key;
        res.data.columns.push({
          title: '操作',
          slot: 'action',
          fixed: 'right',
          width: 100,
          align: 'center',
        });
        res.data.columns.map((item) => {
          if (item.from_type === 'frameImageOne') {
            item.render = (h, params) => {
              return h(
                'div',
                {
                  class: 'tabBox_img',
                  directives: [
                    {
                      name: 'viewer',
                    },
                  ],
                },
                [
                  h('img', {
                    directives: [
                      {
                        name: 'lazy',
                        value: params.row[item.slot],
                      },
                    ],
                  }),
                ],
              );
            };
          } else if (item.from_type === 'frameImages') {
            item.render = (h, params) => {
              let image = params.row[item.slot] || [];
              let imageH = [];
              image.map((item) => {
                imageH.push(
                  h('img', {
                    directives: [
                      {
                        name: 'lazy',
                        value: item,
                      },
                    ],
                  }),
                );
              });
              return h(
                'div',
                {
                  class: 'tabBox_img',
                  directives: [
                    {
                      name: 'viewer',
                    },
                  ],
                },
                imageH,
              );
            };
          }
        });
        this.columns = res.data.columns;
        this.getList();
      });
    },
    // 添加
    add() {
      let url = this.methodApi.create;
      this.$modalForm(getCreateApi(url)).then(() => this.getList());
    },
    //列表
    getList() {
      this.loading = true;
      let url = this.methodApi.index;
      getList(url, this.from)
        .then(async (res) => {
          let data = res.data;
          this.dataList = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    //分页
    pageChange(index) {
      this.from.page = index;
      this.getList();
    },
    // 修改
    edit(row) {
      let url = this.methodApi.edit.replace('<id>', row[this.curdKey]);
      this.$modalForm(getEditApi(url)).then(() => this.getList());
    },
    // 删除
    del(row, tit, num) {
      let url = this.methodApi.delete.replace('<id>', row[this.curdKey]);
      let delfromData = {
        title: tit,
        num: num,
        url: url,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="stylus"></style>
