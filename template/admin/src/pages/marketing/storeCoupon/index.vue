<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form
        ref="tableFrom"
        :model="tableFrom"
        :label-width="labelWidth"
        label-position="right"
        @submit.native.prevent
      >
        <el-row :gutter="24">
          <el-col v-bind="grid">
            <el-form-item label="是否有效：" label-for="status">
              <el-select
                v-model="tableFrom.status"
                placeholder="请选择"
                clearable
                element-id="status"
                @change="userSearchs"
              >
                <el-option value="1" label="有效"></el-option>
                <el-option value="0" label="无效"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="优惠券名称：" label-for="title">
              <el-input
                search
                enter-button
                v-model="tableFrom.title"
                placeholder="请输入优惠券名称"
                @on-search="userSearchs"
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col v-bind="grid">
            <el-button
              v-auth="['admin-marketing-store_coupon-add']"
              type="primary"
              icon="md-add"
              v-db-click
              @click="add"
              >添加优惠券</el-button
            >
          </el-col>
        </el-row>
      </el-form>
      <el-table
        :data="tableList"
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
        <el-table-column label="优惠券名称" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="优惠券类型" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="面值" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.coupon_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="最低消费额" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.use_min_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="有效期限(天)" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.coupon_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="排序" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.sort }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否有效" min-width="130">
          <template slot-scope="scope">
            <i class="el-icon-check" v-if="scope.row.status === 1" style="color: #0092dc; font-size: 14px" />
            <i class="el-icon-close" v-else style="color: #ed5565; font-size: 14px" />
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template slot-scope="scope">
            <span> {{ scope.row.add_time | formatDate }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <a v-db-click @click="couponInvalid(scope.row, '修改优惠券', index)" v-if="scope.row.status">立即失效</a>
            <el-divider direction="vertical" v-if="scope.row.status" />
            <a
              v-db-click
              @click="couponSend(scope.row)"
              v-if="scope.row.status"
              v-auth="['admin-marketing-store_coupon-push']"
              >发布</a
            >
            <el-divider direction="vertical" v-if="scope.row.status" />
            <a v-db-click @click="couponDel(scope.row, '删除优惠券', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="tableFrom.page"
          :limit.sync="tableFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
    <!--表单编辑-->
    <edit-from :FromData="FromData" @changeType="changeType" ref="edits"></edit-from>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { couponListApi, couponCreateApi, couponEditeApi, couponSendApi } from '@/api/marketing';
import editFrom from '@/components/from/from';
import { formatDate } from '@/utils/validate';
export default {
  name: 'storeCoupon',
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  components: { editFrom },
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
          title: '优惠券名称',
          key: 'title',
          minWidth: 150,
        },
        {
          title: '优惠券类型',
          key: 'type',
          minWidth: 80,
        },
        {
          title: '面值',
          key: 'coupon_price',
          minWidth: 100,
        },
        {
          title: '最低消费额',
          key: 'use_min_price',
          minWidth: 100,
        },
        {
          title: '有效期限(天)',
          key: 'coupon_time',
          minWidth: 120,
        },
        {
          title: '排序',
          key: 'sort',
          minWidth: 80,
        },
        {
          title: '是否有效',
          slot: 'status',
          minWidth: 90,
        },
        {
          title: '添加时间',
          slot: 'add_time',
          minWidth: 150,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 170,
        },
      ],
      tableFrom: {
        status: '',
        title: '',
        page: 1,
        limit: 15,
      },
      tableList: [],
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
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  methods: {
    // 失效
    couponInvalid(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/coupon/status/${row.id}`,
        method: 'PUT',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 发布
    couponSend(row) {
      this.$modalForm(couponSendApi(row.id)).then(() => this.getList());
    },
    // 删除
    couponDel(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/coupon/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.tableList.splice(num, 1);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 列表
    getList() {
      this.loading = true;
      this.tableFrom.status = this.tableFrom.status || '';
      couponListApi(this.tableFrom)
        .then(async (res) => {
          let data = res.data;
          this.tableList = data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    pageChange(index) {
      this.tableFrom.page = index;
      this.getList();
    },
    changeType(data) {
      this.type = data;
    },
    // 添加
    add() {
      // this.$modalForm(couponCreateApi()).then(() => this.getList());
      this.addType(0);
    },
    addType(type) {
      couponCreateApi(type)
        .then(async (res) => {
          if (res.data.status === false) {
            return this.$authLapse(res.data);
          }
          this.FromData = res.data;
          this.$refs.edits.modals = true;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 编辑
    edit(row) {
      this.$modalForm(couponEditeApi(row.id)).then(() => this.getList());
    },
    // 表格搜索
    userSearchs() {
      this.tableFrom.page = 1;
      this.getList();
    },
    // 修改成功
    submitFail() {
      this.getList();
    },
  },
};
</script>

<style scoped>
.ivu-col:nth-of-type(1) .ivu-form-item .ivu-form-item-label {
  width: 80px !important;
}
.ivu-col:nth-of-type(1) .ivu-form-item .ivu-form-item-content {
  margin-left: 80px !important;
}
</style>
