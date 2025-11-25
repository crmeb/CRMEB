<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          label-position="right"
          inline
          @submit.native.prevent
          class="tabform"
        >
          <el-form-item label="直播状态：">
            <el-select v-model="formValidate.status" clearable @change="selChange" class="form_content_width">
              <el-option
                v-for="(item, index) in treeData.withdrawal"
                :value="item.value"
                :key="index"
                :label="item.title"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="搜索：">
            <el-input
              clearable
              placeholder="请输入直播间名称/ID/主播昵称/微信号"
              v-model="formValidate.kerword"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="selChange">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button v-auth="['setting-system_menus-add']" type="primary" v-db-click @click="menusAdd('添加直播间')"
        >添加直播间</el-button
      >
      <el-button v-auth="['setting-system_menus-add']" v-db-click @click="syncRoom" style="margin-left: 20px"
        >同步直播间</el-button
      >
      <el-table
        :data="tabList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="直播间ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="直播间名称" min-width="35">
          <template slot-scope="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="主播昵称" min-width="35">
          <template slot-scope="scope">
            <span>{{ scope.row.anchor_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="主播微信号" min-width="35">
          <template slot-scope="scope">
            <span>{{ scope.row.anchor_wechat }}</span>
          </template>
        </el-table-column>
        <el-table-column label="直播开始时间" min-width="35">
          <template slot-scope="scope">
            <span>{{ scope.row.start_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="计划结束时间" min-width="35">
          <template slot-scope="scope">
            <span>{{ scope.row.end_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" min-width="35">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="显示状态" min-width="35">
          <template slot-scope="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_show"
              :value="scope.row.is_show"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="直播状态" min-width="35">
          <template slot-scope="scope">
            <div>{{ scope.row.live_status | liveReviewStatusFilter }}</div>
          </template>
        </el-table-column>
        <el-table-column label="排序" min-width="35">
          <template slot-scope="scope">
            <div>{{ scope.row.sort }}</div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <a v-db-click @click="detail(scope.row, '详情')">详情</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除这条信息', scope.$index)">删除</a>
            <el-divider direction="vertical" v-if="scope.row.live_status == 102" />
            <a v-if="scope.row.live_status == 102" v-db-click @click="addGoods(scope.row)">添加商品</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="formValidate.page"
          :limit.sync="formValidate.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
    <!--详情-->
    <el-dialog :visible.sync="modals" title="直播间详情" class="paymentFooter" width="720px">
      <details-from ref="studioDetail" />
    </el-dialog>
    <!-- 添加商品 -->
    <el-dialog :visible.sync="isShowBox" title="添加商品" class="paymentFooter" width="720px">
      <!--            <addGoods :datas="activeItem" @getData="getData" ref="liveAdd"></addGoods>-->
      <goods-list
        ref="goodslist"
        @getProductId="getProductId"
        v-if="isShowBox"
        :selectIds="selectIds"
        :ischeckbox="true"
        :liveStatus="true"
      ></goods-list>
    </el-dialog>
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
      selectIds: [],
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
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
        path: this.$routeProStr + '/marketing/live/add_live_room',
      });
    },
    // 直播间显示隐藏
    onchangeIsShow({ id, is_show }) {
      liveShow(id, is_show)
        .then((res) => {
          this.$message.success(res.msg);
        })
        .catch((error) => {
          this.$message.error(error.msg);
        });
    },
    //  详情
    detail(row) {
      this.modals = true;
      this.$refs.studioDetail.getData(row.id);
    },
    // 直播间添加商品
    addGoods(row) {
      this.selectIds = row.product_ids;
      this.activeItem = row;
      this.isShowBox = true;
    },
    getData(data) {
      liveRoomGoodsAdd({
        room_id: this.activeItem.id,
        goods_ids: data,
      })
        .then((res) => {
          this.$message.success(res.msg);
          this.isShowBox = false;
          this.$refs.liveAdd.goodsList = [];
        })
        .catch((error) => {
          this.$message.error(error.msg);
          this.isShowBox = false;
          this.$refs.liveAdd.goodsList = [];
        });
    },
    // 同步直播间
    syncRoom() {
      liveSyncRoom()
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((error) => {
          this.$message.error(error.msg);
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
          this.$message.success(res.msg);
          this.tabList.splice(num, 1);

          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
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

<style lang="scss" scoped>
::v-deep .goodList .ivu-input-group {
  width: 200% !important;
}
</style>
