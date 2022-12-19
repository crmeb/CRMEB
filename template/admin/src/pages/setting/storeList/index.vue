<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <div class="mb20">
        <Tabs v-model="artFrom.type" @on-click="onClickTab">
          <TabPane :label="headeNum.show.name + '(' + headeNum.show.num + ')'" name="0" />
          <TabPane :label="headeNum.hide.name + '(' + headeNum.hide.num + ')'" name="1" />
          <TabPane :label="headeNum.recycle.name + '(' + headeNum.recycle.num + ')'" name="2" />
        </Tabs>
      </div>
      <Form
        ref="artFrom"
        :model="artFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col v-bind="grid" class="mr">
            <FormItem label="提货点搜索：" label-for="store_name">
              <Input
                search
                enter-button
                placeholder="请输入提货点名称,电话"
                v-model="artFrom.keywords"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
          <!--                    <Col v-bind="grid">-->
          <!--                        <Button class="mr">导出</Button>-->
          <!--                    </Col>-->
        </Row>
      </Form>
      <Row type="flex" v-auth="['setting-merchant-system_store-save']">
        <Col v-bind="grid">
          <Button v-auth="['setting-merchant-system_store-save']" type="primary" icon="md-add" @click="add"
            >添加提货点</Button
          >
        </Col>
      </Row>
      <Table
        :columns="columns"
        :data="storeLists"
        ref="table"
        class="mt25"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="image">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="row.image" />
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="is_show">
          <i-switch
            v-model="row.is_show"
            :value="row.is_show"
            :true-value="1"
            :false-value="0"
            @on-change="onchangeIsShow(row.id, row.is_show)"
            size="large"
            >>
            <span slot="open">显示</span>
            <span slot="close">隐藏</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row.id)">编辑</a>
          <Divider type="vertical" />
          <a v-if="row.is_del == 0" @click="del(row, '删除提货点', index)">删除</a>
          <a v-else @click="del(row, '恢复提货点', index)">恢复</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="artFrom.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="artFrom.limit"
        />
      </div>
    </Card>
    <system-store ref="template"></system-store>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { storeGetHeaderApi, merchantStoreApi, storeSetShowApi } from '@/api/setting';
import systemStore from '@/components/systemStore/index';
export default {
  name: 'setting_store',
  components: { systemStore },
  computed: {
    ...mapState('media', ['isMobile']),
    ...mapState('userLevel', ['categoryId']),
    labelWidth() {
      return this.isMobile ? undefined : 85;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  data() {
    return {
      grid: {
        xl: 10,
        lg: 10,
        md: 12,
        sm: 24,
        xs: 24,
      },
      headeNum: {
        show: { name: '', num: 0 },
        hide: { name: '', num: 0 },
        recycle: { name: '', num: 0 },
      },
      artFrom: {
        page: 1,
        limit: 15,
        type: '0',
        keywords: '',
      },
      loading: false,
      columns: [
        {
          title: 'ID',
          key: 'id',
          width: 80,
          sortable: true,
        },
        {
          title: '提货点图片',
          slot: 'image',
          minWidth: 100,
        },
        {
          title: '提货点名称',
          key: 'name',
          minWidth: 100,
        },
        {
          title: '提货点电话',
          key: 'phone',
          minWidth: 100,
        },
        {
          title: '地址',
          key: 'detailed_address',
          minWidth: 100,
        },
        {
          title: '营业时间',
          key: 'day_time',
          minWidth: 100,
        },
        {
          title: '是否显示',
          slot: 'is_show',
          minWidth: 100,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 120,
        },
      ],
      storeLists: [],
      total: 0,
    };
  },
  mounted() {
    this.storeHeade();
    this.getList();
  },
  methods: {
    // 获取表单头部信息；
    storeHeade() {
      let that = this;
      storeGetHeaderApi()
        .then((res) => {
          that.headeNum = res.data.count;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    getList() {
      let that = this;
      that.loading = true;
      merchantStoreApi(that.artFrom)
        .then((res) => {
          that.loading = false;
          that.storeLists = res.data.list;
          that.total = res.data.count;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 搜索；
    userSearchs() {
      this.artFrom.page = 1;
      this.getList();
    },
    // 切换导航；
    onClickTab() {
      this.artFrom.page = 1;
      this.artFrom.keywords = '';
      this.getList();
    },
    pageChange(index) {
      this.artFrom.page = index;
      this.getList();
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `merchant/store/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.storeLists.splice(num, 1);
          this.storeHeade();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 添加提货点；
    add() {
      this.$refs.template.isTemplate = true;
    },
    onchangeIsShow(id, is_show) {
      let that = this;
      storeSetShowApi(id, is_show).then((res) => {
        that.$Message.success(res.msg);
        that.getList();
        that.storeHeade();
      });
    },
    edit(id) {
      this.$refs.template.isTemplate = true;
      this.$refs.template.getInfo(id);
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
