<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="artFrom"
          :model="artFrom"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
          inline
        >
          <el-form-item label="提货点名称：">
            <el-select v-model="artFrom.store_id" clearable @change="userSearchs" class="form_content_width">
              <el-option v-for="item in storeSelectList" :value="item.id" :key="item.id" :label="item.name"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-button v-auth="['merchant-store_staff-create']" type="primary" v-db-click @click="add">添加核销员</el-button>
      <el-table
        :data="storeLists"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="头像" min-width="90">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="微信名称" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="核销员名称" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.staff_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="所属提货点" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="150">
          <template slot-scope="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row.id, scope.row.status)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row.id)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除核销员', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="artFrom.page"
          :limit.sync="artFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import {
  storeStaffApi,
  storeStaffCreateApi,
  merchantStoreListApi,
  storeStaffSetShowApi,
  storeStaffEditApi,
} from '@/api/setting';
export default {
  name: 'setting_staff',
  components: {},
  computed: {
    ...mapState('media', ['isMobile']),
    ...mapState('userLevel', ['categoryId']),
    labelWidth() {
      return this.isMobile ? undefined : '90px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
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
      artFrom: {
        page: 1,
        limit: 15,
        store_id: '',
      },
      loading: false,
      storeLists: [],
      storeSelectList: [],
      total: 0,
    };
  },
  mounted() {
    this.getList();
    this.storeList();
  },
  methods: {
    storeList() {
      let that = this;
      merchantStoreListApi().then((res) => {
        that.storeSelectList = res.data;
      });
    },
    getList() {
      let that = this;
      that.loading = true;
      storeStaffApi(that.artFrom)
        .then((res) => {
          that.loading = false;
          that.storeLists = res.data.list;
          that.total = res.data.count;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 搜索；
    userSearchs() {
      this.artFrom.page = 1;
      this.getList();
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `merchant/store_staff/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.storeLists.splice(num, 1);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 添加核销员；
    add() {
      this.$modalForm(storeStaffCreateApi(0)).then(() => this.getList());
    },
    onchangeIsShow(id, is_show) {
      let that = this;
      storeStaffSetShowApi(id, is_show).then((res) => {
        that.$message.success(res.msg);
        that.getList();
      });
    },
    edit(id) {
      this.$modalForm(storeStaffEditApi(id)).then(() => this.getList());
    },
  },
};
</script>

<style lang="scss" scoped>
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;
  img {
    width: 100%;
    height: 100%;
  }
}
</style>
