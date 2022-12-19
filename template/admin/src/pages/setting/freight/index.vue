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
            <FormItem label="搜索：" label-for="keyword">
              <Input
                search
                enter-button
                v-model="levelFrom.keyword"
                placeholder="请输入物流公司名称或者编码"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
        </Row>
        <Row type="flex">
          <Col v-bind="grid">
            <Button type="primary" icon="md-add" @click="syncExpress">同步物流公司</Button>
          </Col>
        </Row>
      </Form>
      <Table
        :columns="columns1"
        :data="levelLists"
        ref="table"
        class="mt25"
        :loading="loading"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="is_shows">
          <i-switch
            v-model="row.is_show"
            :value="row.is_show"
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
          <a @click="edit(row)">编辑</a>
          <!--                    <Divider type="vertical" />-->
          <!--                    <a @click="del(row, '删除物流公司', index)">删除</a>-->
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
  </div>
</template>
<script>
import { mapState } from 'vuex';
import {
  freightCreateApi,
  freightListApi,
  freightEditApi,
  freightStatusApi,
  freightSyncExpressApi,
} from '@/api/setting';
export default {
  name: 'user_level',
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
          width: 80,
        },
        {
          title: '物流公司名称',
          key: 'name',
          minWidth: 100,
        },
        {
          title: '编码',
          key: 'code',
          minWidth: 120,
        },
        {
          title: '排序',
          key: 'sort',
          sortable: true,
          minWidth: 100,
        },
        {
          title: '是否显示',
          slot: 'is_shows',
          minWidth: 120,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 120,
        },
      ],
      levelFrom: {
        keyword: '',
        page: 1,
        limit: 20,
      },
      levelLists: [],
      total: 0,
      FromData: null,
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
      return this.isMobile ? 'top' : 'left';
    },
  },
  methods: {
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `freight/express/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.levelLists.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        status: row.is_show,
      };
      freightStatusApi(data)
        .then(async (res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 等级列表
    getList() {
      this.loading = true;
      freightListApi(this.levelFrom)
        .then(async (res) => {
          let data = res.data;
          this.levelLists = data.list;
          this.total = data.count;
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
    // 添加
    add() {
      this.$modalForm(freightCreateApi()).then(() => this.getList());
      // freightCreateApi().then(async res => {
      //     this.FromData = res.data;
      //     this.$refs.edits.modals = true;
      // }).catch(res => {
      //     this.$Message.error(res.msg);
      // })
    },
    // 编辑
    edit(row) {
      this.$modalForm(freightEditApi(row.id)).then(() => this.getList());
      // freightEditApi(row.id).then(async res => {
      //     this.FromData = res.data;
      //     this.$refs.edits.modals = true;
      // }).catch(res => {
      //     this.$Message.error(res.msg);
      // })
    },
    // 表格搜索
    userSearchs() {
      this.levelFrom.page = 1;
      this.getList();
    },
    syncExpress() {
      freightSyncExpressApi()
        .then(async (res) => {
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

<style scoped lang="stylus">
.tabBox_img
    width 36px
    height 36px
    border-radius:4px
    cursor pointer
    img
        width 100%
        height 100%
</style>
