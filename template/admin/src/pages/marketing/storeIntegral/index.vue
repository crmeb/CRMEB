<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="tableFrom"
          :model="tableFrom"
          :label-width="labelWidth"
          label-position="right"
          @submit.native.prevent
          inline
        >
          <el-form-item label="创建时间：">
            <el-date-picker
              clearable
              v-model="timeVal"
              type="daterange"
              :editable="false"
              @change="onchangeTime"
              format="yyyy/MM/dd"
              value-format="yyyy/MM/dd"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              :picker-options="pickerOptions"
              style="width: 250px"
            ></el-date-picker>
          </el-form-item>
          <el-form-item label="上架状态：">
            <el-select
              placeholder="请选择"
              clearable
              v-model="tableFrom.is_show"
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="1" label="上架"></el-option>
              <el-option value="0" label="下架"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="商品搜索：" label-for="store_name">
            <el-input placeholder="请输入商品名称，ID" v-model="tableFrom.store_name" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button
        v-auth="['marketing-store_integral-create']"
        type="primary"
        icon="md-add"
        v-db-click
        @click="add"
        class="mr10"
        >添加积分商品</el-button
      >
      <el-table
        :data="tableList"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
        class="mt14"
      >
        <el-table-column label="ID" min-width="50">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品图片" min-width="40">
          <template slot-scope="scope">
            <viewer>
              <div class="tabBox_img">
                <img v-lazy="scope.row.image" />
              </div>
            </viewer>
          </template>
        </el-table-column>
        <el-table-column label="活动标题" min-width="130">
          <template slot-scope="scope">
            <el-tooltip placement="top" :open-delay="600">
              <div slot="content">{{ scope.row.title }}</div>
              <span class="line2">{{ scope.row.title }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="兑换积分" min-width="60">
          <template slot-scope="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="限量" min-width="60">
          <template slot-scope="scope">
            <span>{{ scope.row.quota_show }}</span>
          </template>
        </el-table-column>
        <el-table-column label="限量剩余" min-width="60">
          <template slot-scope="scope">
            <span>{{ scope.row.quota }}</span>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="排序" min-width="60">
          <template slot-scope="scope">
            <span>{{ scope.row.sort }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="60">
          <template slot-scope="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_show"
              :value="scope.row.is_show"
              @change="onchangeIsShow(scope.row)"
              size="large"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="200">
          <template slot-scope="scope">
            <a v-db-click @click="orderList(scope.row)">兑换记录</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="copy(scope.row)">复制</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除积分商品', scope.$index)">删除</a>
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
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { integralProductListApi, integralIsShowApi, storeSeckillApi } from '@/api/marketing';
import { formatDate } from '@/utils/validate';
export default {
  name: 'marketing_storeIntegral',
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd');
      }
    },
  },
  data() {
    return {
      loading: false,
      pickerOptions: this.$timeOptions,
      tableList: [],
      timeVal: [],
      grid: {
        xl: 7,
        lg: 10,
        md: 12,
        sm: 24,
        xs: 24,
      },
      tableFrom: {
        integral_time: '',
        is_show: '',
        store_name: '',
        page: 1,
        limit: 15,
      },
      total: 0,
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
  activated() {
    this.getList();
  },
  methods: {
    // 添加
    add() {
      this.$router.push({ path: this.$routeProStr + '/marketing/store_integral/create' });
    },
    orderList(row) {
      this.$router.push({
        path: this.$routeProStr + '/marketing/store_integral/order_list',
        query: {
          product_id: row.id,
        },
      });
    },
    // 导出
    exports() {
      let formValidate = this.tableFrom;
      let data = {
        start_status: formValidate.start_status,
        status: formValidate.status,
        store_name: formValidate.store_name,
      };
      storeSeckillApi(data)
        .then((res) => {
          location.href = res.data[0];
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 编辑
    edit(row) {
      this.$router.push({
        path: this.$routeProStr + '/marketing/store_integral/create/' + row.id + '/0',
      });
    },
    // 一键复制
    copy(row) {
      this.$router.push({
        path: this.$routeProStr + '/marketing/store_integral/create/' + row.id + '/1',
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/integral/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.tableFrom.page = 1;
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 列表
    getList() {
      this.loading = true;
      this.tableFrom.start_status = this.tableFrom.start_status || '';
      this.tableFrom.is_show = this.tableFrom.is_show || '';
      integralProductListApi(this.tableFrom)
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
    // 表格搜索
    userSearchs() {
      this.tableFrom.page = 1;
      this.getList();
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.integral_time = this.timeVal ? this.timeVal.join('-') : '';
      this.getList();
    },
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        is_show: row.is_show,
      };
      integralIsShowApi(data)
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
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
