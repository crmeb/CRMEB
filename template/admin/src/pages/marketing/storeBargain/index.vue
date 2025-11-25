<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="tableFrom"
          :model="tableFrom"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
          inline
        >
          <el-form-item label="砍价状态：">
            <el-select
              placeholder="请选择"
              v-model="tableFrom.status"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="1" label="开启"></el-option>
              <el-option value="0" label="关闭"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="商品搜索：" label-for="store_name">
            <el-input
              clearable
              placeholder="请输入砍价名称，ID"
              v-model="tableFrom.store_name"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button v-auth="['marketing-store_bargain-create']" type="primary" v-db-click @click="add"
        >添加砍价商品</el-button
      >
      <el-button v-auth="['export-storeBargain']" class="export" icon="ios-share-outline" v-db-click @click="exportList"
        >导出</el-button
      >
      <el-table
        :data="tableList"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
        class="mt14"
      >
        <el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <div>{{ scope.row.id }}</div>
          </template>
        </el-table-column>
        <el-table-column label="砍价图片" min-width="80">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="砍价名称" min-width="150">
          <template slot-scope="scope">
            <el-tooltip placement="top" :open-delay="600">
              <div slot="content">{{ scope.row.title }}</div>
              <span class="line2">{{ scope.row.title }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="砍价价格" min-width="100">
          <template slot-scope="scope">
            <div>{{ scope.row.price }}</div>
          </template>
        </el-table-column>
        <el-table-column label="最低价" min-width="100">
          <template slot-scope="scope">
            <div>{{ scope.row.min_price }}</div>
          </template>
        </el-table-column>
        <el-table-column label="参与人数" min-width="100">
          <template slot-scope="scope">
            <div>{{ scope.row.count_people_all }}</div>
          </template>
        </el-table-column>
        <el-table-column label="帮忙砍价人数" min-width="100">
          <template slot-scope="scope">
            <div>{{ scope.row.count_people_help }}</div>
          </template>
        </el-table-column>
        <el-table-column label="砍价成功人数" min-width="100">
          <template slot-scope="scope">
            <div>{{ scope.row.count_people_success }}</div>
          </template>
        </el-table-column>
        <el-table-column label="限量" min-width="80">
          <template slot-scope="scope">
            <div>{{ scope.row.quota_show }}</div>
          </template>
        </el-table-column>
        <el-table-column label="限量剩余" min-width="80">
          <template slot-scope="scope">
            <div>{{ scope.row.quota }}</div>
          </template>
        </el-table-column>
        <el-table-column label="活动状态" min-width="100">
          <template slot-scope="scope">
            <el-tag size="medium" v-show="scope.row.start_name === '进行中'">进行中</el-tag>
            <el-tag size="medium" type="warning" v-show="scope.row.start_name === '未开始'">未开始</el-tag>
            <el-tag size="medium" type="info" v-show="scope.row.start_name === '已结束'">已结束</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="活动时间" min-width="180">
          <template slot-scope="scope">
            <p>开始：{{ scope.row.start_time }}</p>
            <p>结束：{{ scope.row.stop_time }}</p>
          </template>
        </el-table-column>
        <el-table-column label="上架状态" min-width="100">
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
            <a v-if="scope.row.stop_status === 0" v-db-click @click="edit(scope.row, 0)">编辑</a>
            <el-divider v-if="scope.row.stop_status === 0" direction="vertical" />
            <a v-db-click @click="edit(scope.row, 1)">复制</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除砍价商品', scope.$index)">删除</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="viewInfo(scope.row)">统计</a>
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
import { bargainListApi, bargainSetStatusApi, stroeBargainApi } from '@/api/marketing';
import { formatDate } from '@/utils/validate';
import { exportBargainList } from '@/api/export';
export default {
  name: 'marketing_storeBargain',
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm:ss');
      }
    },
  },
  data() {
    return {
      loading: false,
      columns1: [
        {
          title: 'ID',
          key: 'id',
          width: 80,
        },
        {
          title: '砍价图片',
          slot: 'image',
          minWidth: 90,
        },
        {
          title: '砍价名称',
          key: 'title',
          minWidth: 130,
        },
        {
          title: '砍价价格',
          key: 'price',
          minWidth: 100,
        },
        {
          title: '最低价',
          key: 'min_price',
          minWidth: 100,
        },
        {
          title: '参与人数',
          key: 'count_people_all',
          minWidth: 100,
        },
        {
          title: '帮忙砍价人数',
          key: 'count_people_help',
          minWidth: 100,
        },
        {
          title: '砍价成功人数',
          key: 'count_people_success',
          minWidth: 100,
        },
        {
          title: '限量',
          key: 'quota_show',
          minWidth: 100,
        },
        {
          title: '限量剩余',
          key: 'quota',
          minWidth: 100,
        },
        {
          title: '活动状态',
          slot: 'start_name',
          minWidth: 100,
        },
        {
          title: '结束时间',
          slot: 'stop_time',
          minWidth: 150,
        },
        {
          title: '上架状态',
          slot: 'status',
          minWidth: 130,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 160,
        },
      ],
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
        store_name: '',
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
      this.$router.push({ path: this.$routeProStr + '/marketing/store_bargain/create' });
    },
    // 用户导出
    async exportList() {
      this.tableFrom.status = this.tableFrom.status || '';
      let [th, filekey, data, fileName] = [[], [], [], ''];
      let excelData = JSON.parse(JSON.stringify(this.tableFrom));
      excelData.page = 1;
      excelData.limit = 50;
      for (let i = 0; i < excelData.page + 1; i++) {
        let lebData = await this.getExcelData(excelData);
        if (!fileName) fileName = lebData.filename;
        if (!filekey.length) {
          filekey = lebData.fileKey;
        }
        if (!th.length) th = lebData.header;
        if (lebData.export.length) {
          data = data.concat(lebData.export);
          excelData.page++;
        } else {
          this.$exportExcel(th, filekey, fileName, data);
          return;
        }
      }
    },
    getExcelData(excelData) {
      return new Promise((resolve, reject) => {
        exportBargainList(excelData).then((res) => {
          resolve(res.data);
        });
      });
    },
    // 编辑 / 复制  type 0 编辑 1 复制
    edit(row, type) {
      this.$router.push({
        path: this.$routeProStr + `/marketing/store_bargain/create/${row.id}/${type}`,
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/bargain/${row.id}`,
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
    viewInfo(row) {
      this.$router.push({
        path: this.$routeProStr + '/marketing/store_bargain/statistics/' + row.id,
      });
    },
    // 列表
    getList() {
      this.loading = true;
      this.tableFrom.status = this.tableFrom.status || '';
      this.tableFrom.product_id = this.$route.params.product_id || '';
      bargainListApi(this.tableFrom)
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
      bargainSetStatusApi(data)
        .then(async (res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
          row.status = !row.status;
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
