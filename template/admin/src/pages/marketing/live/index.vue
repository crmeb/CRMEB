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
            <FormItem label="直播状态：">
              <RadioGroup type="button" v-model="formValidate.status" class="mr15" @on-change="selChange">
                <Radio :label="itemn.value" v-for="(itemn, indexn) in treeData.withdrawal" :key="indexn">{{
                  itemn.title
                }}</Radio>
              </RadioGroup>
            </FormItem>
          </Col>
          <Col span="24">
            <FormItem label="搜索：">
              <Input
                search
                enter-button
                @on-search="selChange"
                placeholder="请输入直播间名称/ID/主播昵称/微信号"
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
              >添加直播间</Button
            >
            <Button
              v-auth="['setting-system_menus-add']"
              icon="md-list"
              type="success"
              @click="syncRoom"
              style="margin-left: 20px"
              >同步直播间</Button
            >
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
        <template slot-scope="{ row, index }" slot="is_mer_show">
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
        <template slot-scope="{ row, index }" slot="status">
          <div>{{ row.live_status | liveReviewStatusFilter }}</div>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="detail(row, '详情')">详情</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除这条信息', index)">删除</a>
          <Divider type="vertical" />
          <a @click="addGoods(row)">添加商品</a>
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
    <Modal v-model="modals" title="直播间详情" class="paymentFooter" scrollable width="700" :footer-hide="true">
      <details-from ref="studioDetail" />
    </Modal>
    <!-- 添加商品 -->
    <Modal v-model="isShowBox" title="添加商品" class="paymentFooter" scrollable width="700" :footer-hide="true">
      <!--            <addGoods :datas="activeItem" @getData="getData" ref="liveAdd"></addGoods>-->
      <goods-list
        ref="goodslist"
        @getProductId="getProductId"
        v-if="isShowBox"
        :ischeckbox="true"
        :liveStatus="true"
      ></goods-list>
    </Modal>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { liveList, liveShow, liveRoomGoodsAdd, liveSyncRoom } from '@/api/live';
import detailsFrom from './components/live_detail';
import addGoods from './components/add_goods';
import goodsList from '@/components/goodsList';
export default {
  name: 'live',
  components: {
    detailsFrom,
    addGoods,
    goodsList,
  },
  data() {
    return {
      isShowBox: false,
      modals: false,
      total: 0,
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
            title: '直播中',
            value: 1,
          },
          {
            title: '未开始',
            value: 2,
          },
          {
            title: '已结束',
            value: 3,
          },
        ],
      },
      columns1: [
        { key: 'id', title: '直播间ID', minWidth: 35 },
        { key: 'name', minWidth: 35, title: '直播间名称' },
        { key: 'anchor_name', minWidth: 35, title: '主播昵称' },
        { key: 'anchor_wechat', minWidth: 35, title: '主播微信号' },
        { key: 'start_time', minWidth: 35, title: '直播开始时间' },
        { key: 'end_time', minWidth: 35, title: '计划结束时间' },
        { key: 'add_time', minWidth: 35, title: '创建时间' },
        { slot: 'is_mer_show', title: '显示状态', minWidth: 80 },
        { slot: 'status', minWidth: 35, title: '直播状态' },
        { key: 'sort', minWidth: 35, title: '排序' },
        { slot: 'action', fixed: 'right', title: '操作', minWidth: 120 },
      ],
      tabList: [],
      loading: false,
      activeItem: {},
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
  created() {
    this.getList();
  },
  methods: {
    // 获取直播列表
    getList() {
      this.loading = true;
      liveList(this.formValidate).then((res) => {
        this.total = res.data.count;
        this.tabList = res.data.list;
        this.loading = false;
      });
    },
    // 选择
    selChange() {
      this.formValidate.page = 1;
      this.getList();
    },
    // 添加直播间
    menusAdd() {
      this.$router.push({
        path: '/admin/marketing/live/add_live_room',
      });
    },
    // 分页
    pageChange(index) {
      this.formValidate.page = index;
      this.getList();
    },
    // 直播间显示隐藏
    onchangeIsShow({ id, is_show }) {
      liveShow(id, is_show)
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((error) => {
          this.$Message.error(error.msg);
        });
    },
    //  详情
    detail(row) {
      this.modals = true;
      this.$refs.studioDetail.getData(row.id);
    },
    // 直播间添加商品
    addGoods(row) {
      this.activeItem = row;
      this.isShowBox = true;
    },
    getData(data) {
      liveRoomGoodsAdd({
        room_id: this.activeItem.id,
        goods_ids: data,
      })
        .then((res) => {
          this.$Message.success(res.msg);
          this.isShowBox = false;
          this.$refs.liveAdd.goodsList = [];
        })
        .catch((error) => {
          this.$Message.error(error.msg);
          this.isShowBox = false;
          this.$refs.liveAdd.goodsList = [];
        });
    },
    // 同步直播间
    syncRoom() {
      liveSyncRoom()
        .then((res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((error) => {
          this.$Message.error(res.msg);
        });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `live/room/del/${row.id}`,
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
    getProductId(data) {
      let arr = [];
      data.map((el) => {
        arr.push(el.product_id);
      });
      this.getData(arr);
    },
  },
};
</script>

<style scoped>
/deep/ .goodList .ivu-input-group {
  width: 200% !important;
}
</style>
