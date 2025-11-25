<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          label-position="right"
          @submit.native.prevent
          inline
        >
          <el-form-item label="上架状态：">
            <el-select
              v-model="formValidate.is_show"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option :value="1" label="上架"></el-option>
              <el-option :value="0" label="下架"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="拼团搜索：" prop="store_name" label-for="store_name">
            <el-input
              clearable
              placeholder="请输入请输入拼团名称/ID"
              v-model="formValidate.store_name"
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
      <el-button v-auth="['marketing-store_combination-create']" type="primary" v-db-click @click="add"
        >添加拼团商品</el-button
      >
      <el-button v-auth="['export-storeCombination']" class="export" v-db-click @click="exports">导出</el-button>
      <el-table
        :data="tableList"
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
        <el-table-column label="拼团图片" min-width="90">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="拼团名称" min-width="130">
          <template slot-scope="scope">
            <el-tooltip placement="top" :open-delay="600">
              <div slot="content">{{ scope.row.title }}</div>
              <span class="line2">{{ scope.row.title }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="原价" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.ot_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="拼团价" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="拼团人数" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.count_people }}</span>
          </template>
        </el-table-column>
        <el-table-column label="参与人数" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.count_people_all }}</span>
          </template>
        </el-table-column>
        <el-table-column label="成团数量" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.count_people_pink }}</span>
          </template>
        </el-table-column>
        <el-table-column label="限量" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.quota_show }}</span>
          </template>
        </el-table-column>
        <el-table-column label="限量剩余" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.quota }}</span>
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
        <el-table-column label="上架状态" min-width="150">
          <template slot-scope="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_show"
              :value="scope.row.is_show"
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
            <a v-if="scope.row.stop_status === 0" v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical" v-if="scope.row.stop_status === 0" />
            <a v-db-click @click="copy(scope.row)">复制</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除拼团商品', scope.$index)">删除</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="viewInfo(scope.row)">统计</a>
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
  </div>
</template>

<script>
import { combinationListApi, combinationSetStatusApi, storeCombinationApi } from '@/api/marketing';
import { mapState } from 'vuex';
import { formatDate } from '@/utils/validate';
import { exportCombinationList } from '@/api/export.js';

export default {
  name: 'marketing_combinalist',
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
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      formValidate: {
        is_show: '',
        store_name: '',
        page: 1,
        limit: 15,
      },
      value: '',
      tableList: [],
      total: 0,
      statisticsList: [],
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
    // 导出
    async exports() {
      let [th, filekey, data, fileName] = [[], [], [], ''];
      let excelData = JSON.parse(JSON.stringify(this.formValidate));
      excelData.page = 1;
      excelData.limit = 200;
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
        exportCombinationList(excelData).then((res) => {
          resolve(res.data);
        });
      });
    },

    // 添加
    add() {
      this.$router.push({ path: this.$routeProStr + '/marketing/store_combination/create' });
    },
    // 编辑
    edit(row) {
      this.$router.push({
        path: this.$routeProStr + '/marketing/store_combination/create/' + row.id + '/0',
      });
    },
    // 一键复制
    copy(row) {
      this.$router.push({
        path: this.$routeProStr + '/marketing/store_combination/create/' + row.id + '/1',
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/combination/${row.id}`,
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
        path: this.$routeProStr + '/marketing/store_combination/statistics/' + row.id,
      });
    },
    // 列表
    getList() {
      this.loading = true;
      // this.formValidate.is_show = this.formValidate.is_show
      this.formValidate.product_id = this.$route.params.product_id || '';

      combinationListApi(this.formValidate)
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
      this.formValidate.page = 1;
      this.getList();
    },
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        status: row.is_show,
      };
      combinationSetStatusApi(data)
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
          row.is_show = !row.is_show;
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
