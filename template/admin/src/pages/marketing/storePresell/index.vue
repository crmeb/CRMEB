<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form
        ref="tableFrom"
        :model="tableFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <el-row :gutter="24">
          <el-col v-bind="grid">
            <el-form-item label="预售活动状态：">
              <el-select placeholder="请选择活动状态" v-model="tableFrom.time_type" clearable @change="userSearchs">
                <el-option value="0" label="全部"></el-option>
                <el-option value="1" label="未开始"></el-option>
                <el-option value="2" label="正在进行"></el-option>
                <el-option value="3" label="已结束"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="预售商品状态：">
              <el-select placeholder="请选择商品状态" v-model="tableFrom.status" clearable @change="userSearchs">
                <el-option value="" label="全部"></el-option>
                <el-option value="1" label="上架"></el-option>
                <el-option value="0" label="下架"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="商品搜索：" label-for="title">
              <el-input
                search
                enter-button
                placeholder="请输入商品名称/ID"
                v-model="tableFrom.title"
                @on-search="userSearchs"
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb20">
          <el-col v-bind="grid">
            <el-button
              v-auth="['marketing-store_bargain-create']"
              type="primary"
              icon="md-add"
              v-db-click
              @click="add"
              class="mr10"
              >添加预售商品</el-button
            >
            <!-- <el-button
              v-auth="['export-storeBargain']"
              class="export"
              icon="ios-share-outline"
              v-db-click @click="exports"
              >导出</el-button
            > -->
          </el-col>
        </el-row>
      </el-form>
      <el-table
        :data="tableList"
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
        <el-table-column label="预售图片" min-width="90">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="预售名称" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="预售价格" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="已售商品数" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.sales }}</span>
          </template>
        </el-table-column>
        <el-table-column label="限量" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.quota_show }}</span>
          </template>
        </el-table-column>
        <el-table-column label="限量剩余" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.quota }}</span>
          </template>
        </el-table-column>
        <el-table-column label="活动时间" min-width="130">
          <template slot-scope="scope">
            <div>起: {{ scope.row.start_time | formatDate }}</div>
            <div>止: {{ scope.row.stop_time | formatDate }}</div>
          </template>
        </el-table-column>
        <el-table-column label="预售状态" min-width="130">
          <template slot-scope="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="上架"
              inactive-text="下架"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider v-if="scope.row.stop_status === 0" direction="vertical" />
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除预售商品', scope.$index)">删除</a>
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
import { presellListApi, advanceSetStatusApi, stroeBargainApi } from '@/api/marketing';
import { formatDate } from '@/utils/validate';
export default {
  name: 'storeBargain',
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  data() {
    return {
      loading: false,
      tableList: [],
      grid: {
        xl: 7,
        lg: 10,
        md: 12,
        sm: 24,
        xs: 24,
      },
      tableFrom: {
        status: '',
        time_type: 0,
        title: '',
        page: 1,
        limit: 15,
      },
      tableFrom2: {
        status: '',
        store_name: '',
        export: 1,
      },
      total: 0,
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '100px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  created() {
    this.getList();
  },
  methods: {
    // 添加
    add() {
      this.$router.push({ path: this.$routeProStr + '/marketing/presell/create/0' });
    },
    // 导出
    exports() {
      let formValidate = this.tableFrom;
      let data = {
        status: formValidate.status,
        store_name: formValidate.store_name,
      };
      stroeBargainApi(data)
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
        path: this.$routeProStr + '/marketing/presell/create/' + row.id + '/0',
      });
    },
    // 一键复制
    copy(row) {
      this.$router.push({
        path: this.$routeProStr + '/marketing/presell/create/' + row.id + '/1',
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/advance/${row.id}`,
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
      presellListApi(this.tableFrom)
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
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        status: row.status,
      };
      advanceSetStatusApi(data)
        .then(async (res) => {
          this.getList();
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
