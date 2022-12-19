<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="formValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        class="tabform"
        @submit.native.prevent
      >
        <Row :gutter="24" type="flex">
          <Col span="24">
            <FormItem label="审核状态：">
              <RadioGroup type="button" v-model="formValidate.status" class="mr15" @on-change="selChange">
                <Radio :label="itemn.value" v-for="(itemn, indexn) in treeData.withdrawal" :key="indexn">
                  {{ itemn.title }}
                </Radio>
              </RadioGroup>
            </FormItem>
          </Col>
          <Col span="24">
            <FormItem label="搜索：">
              <Input
                search
                enter-button
                @on-search="selChange"
                placeholder="请输入商品名称/ID"
                element-id="name"
                v-model="formValidate.kerword"
                style="width: 30%"
              />
            </FormItem>
          </Col>
        </Row>
        <Row type="flex">
          <Col v-bind="grid">
            <Button v-auth="['setting-system_menus-add']" type="primary" @click="menusAdd('添加直播间')" icon="md-add"
              >添加商品
            </Button>
            <Button
              v-auth="['setting-system_menus-add']"
              icon="md-list"
              type="success"
              @click="syncGoods"
              style="margin-left: 20px"
              >同步商品
            </Button>
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
        <template slot-scope="{ row, index }" slot="name">
          <div class="product_box">
            <div v-viewer>
              <img :src="row.product.image" alt="" />
            </div>
            <div class="txt">{{ row.name }}</div>
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="cost_price">
          <div>{{ row.cost_price }}</div>
        </template>
        <template slot-scope="{ row, index }" slot="stock">
          <div>{{ row.product.stock }}</div>
        </template>
        <template slot-scope="{ row, index }" slot="status">
          <div>{{ row.audit_status | liveStatusFilter }}</div>
        </template>
        <template slot-scope="{ row, index }" slot="is_mer_show">
          <i-switch
            v-model="row.is_show"
            :value="row.is_show"
            :true-value="1"
            :false-value="0"
            @on-change="onchangeIsShow(row)"
            size="large"
            :disabled="row.audit_status != 2"
          >
            <span slot="open">显示</span>
            <span slot="close">隐藏</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row, '编辑')">详情</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除这条信息', index)">删除</a>
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
    <!--详情-->
    <Modal v-model="modals" title="商品详情" class="paymentFooter" scrollable width="700" :footer-hide="true">
      <goodsFrom ref="goodsDetail" />
    </Modal>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { liveGoods, liveSyncGoods, liveGoodsDetail, liveGoodsShow } from '@/api/live';
import goodsFrom from './components/goods_detail';
export default {
  name: 'live',
  components: {
    goodsFrom,
  },
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
        status: '',
        kerword: '',
        page: 1,
        limit: 20,
      },
      treeData: {
        withdrawal: [
          {
            title: '全部',
            value: '',
          },
          {
            title: '待审核',
            value: 0,
          },
          {
            title: '已通过',
            value: 1,
          },
          {
            title: '未通过',
            value: -1,
          },
        ],
      },
      columns1: [
        { key: 'product_id', title: '商品ID', minWidth: 35 },
        { slot: 'name', minWidth: 35, title: '商品名称' },
        { key: 'price', minWidth: 35, title: '直播价' },
        { slot: 'cost_price', minWidth: 35, title: '原价' },
        { slot: 'stock', minWidth: 35, title: '库存' },
        { slot: 'status', minWidth: 35, title: '审核状态' },
        { slot: 'is_mer_show', title: '是否显示', minWidth: 80 },
        // {"key": "sort", "title": "排序", "minWidth": 35},
        { slot: 'action', fixed: 'right', title: '操作', minWidth: 120 },
      ],
      tabList: [],
      loading: false,
      modals: false,
      total: 0,
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 80;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  mounted() {
    this.getList();
  },
  methods: {
    // 分页
    pageChange(index) {
      this.formValidate.page = index;
      this.getList();
    },
    // 直播间显示隐藏
    onchangeIsShow({ id, is_show }) {
      liveGoodsShow(id, is_show)
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((error) => {
          this.$Message.error(error.msg);
        });
    },
    // 列表数据
    getList() {
      this.loading = true;
      liveGoods(this.formValidate)
        .then((res) => {
          this.total = res.data.count;
          this.tabList = res.data.list;
          this.loading = false;
        })
        .catch((error) => {
          this.$Message.error(error.msg);
          this.loading = false;
        });
    },
    // 选择
    selChange() {
      this.formValidate.page = 1;
      this.getList();
    },
    // 添加商品
    menusAdd() {
      this.$router.push({
        path: '/admin/marketing/live/add_live_goods',
      });
    },
    // 同步商品
    syncGoods() {
      liveSyncGoods()
        .then((res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((error) => {
          this.$Message.error(res.msg);
        });
    },
    edit(row) {
      this.modals = true;
      this.$refs.goodsDetail.getData(row.id);
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `live/goods/del/${row.id}`,
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
  },
};
</script>

<style scoped lang="stylus">
.product_box
    display flex
    align-items center
    img
        width 36px
        height 36px
    .txt
        margin-left 10px
        color #000
        font-size 12px
</style>
