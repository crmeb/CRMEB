<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{padding:0}">
      <div class="padding-add">
        <el-form
            ref="tableFrom"
            :model="tableFrom"
            :label-width="labelWidth"
            label-position="right"
            @submit.native.prevent
            inline
        >
          <el-form-item label="秒杀状态：">
            <el-select placeholder="请选择" clearable v-model="tableFrom.status" @change="userSearchs" class="form_content_width">
              <el-option value="1" label="开启"></el-option>
              <el-option value="0" label="关闭"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="商品搜索：" label-for="store_name">
            <el-input
                placeholder="请输入商品名称，ID"
                v-model="tableFrom.store_name"
                class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button
          v-auth="['marketing-store_seckill-create']"
          type="primary"
          @click="add"
      >添加秒杀商品</el-button>
      <el-button v-auth="['export-storeSeckill']" class="export" @click="exports">导出</el-button>
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
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品图片" min-width="90">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
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
        <el-table-column label="活动简介" min-width="100">
          <template slot-scope="scope">
            <el-tooltip placement="top" :open-delay="600">
              <div slot="content">{{ scope.row.info }}</div>
              <span class="line2">{{ scope.row.info }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="原价" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.ot_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="秒杀价" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="限量" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.quota_show }}</span>
          </template>
        </el-table-column>
        <el-table-column label="限量剩余" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.quota }}</span>
          </template>
        </el-table-column>
        <el-table-column label="秒杀状态" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.start_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="结束时间" min-width="100">
          <template slot-scope="scope">
            <span> {{ scope.row.stop_time | formatDate }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="100">
          <template slot-scope="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <a v-if="scope.row.stop_status === 0" @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical" v-if="scope.row.stop_status === 0" />
            <a @click="copy(scope.row)">复制</a>
            <el-divider direction="vertical"></el-divider>
            <a @click="del(scope.row, '删除秒杀商品', scope.$index)">删除</a>
            <el-divider direction="vertical"></el-divider>
            <a @click="viewInfo(scope.row)">统计</a>
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
import { seckillListApi, seckillStatusApi, storeSeckillApi } from '@/api/marketing';
import { formatDate } from '@/utils/validate';
import { exportSeckillList } from '@/api/export.js';

export default {
  name: 'marketing_storeSeckill',
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
      columns1: [
        {
          title: 'ID',
          key: 'id',
          width: 80,
        },
        {
          title: '商品图片',
          slot: 'image',
          minWidth: 90,
        },
        {
          title: '活动标题',
          key: 'title',
          minWidth: 130,
        },
        {
          title: '活动简介',
          key: 'info',
          minWidth: 100,
        },
        {
          title: '原价',
          key: 'ot_price',
          minWidth: 100,
        },
        {
          title: '秒杀价',
          key: 'price',
          minWidth: 100,
        },
        {
          title: '限量',
          key: 'quota_show',
          minWidth: 130,
        },
        {
          title: '限量剩余',
          key: 'quota',
          minWidth: 130,
        },
        {
          title: '秒杀状态',
          key: 'start_name',
          minWidth: 100,
        },
        {
          title: '结束时间',
          slot: 'stop_time',
          minWidth: 100,
        },
        {
          title: '状态',
          slot: 'status',
          minWidth: 100,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 130,
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
      this.$router.push({ path: this.$routeProStr + '/marketing/store_seckill/create' });
    },
    // 导出
    async exports() {
      let [th, filekey, data, fileName] = [[], [], [], ''];
      let excelData = JSON.parse(JSON.stringify(this.tableFrom));
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
        exportSeckillList(excelData).then((res) => {
          resolve(res.data);
        });
      });
    },

    // 编辑
    edit(row) {
      this.$router.push({
        path: this.$routeProStr + '/marketing/store_seckill/create/' + row.id + '/0',
      });
    },
    // 一键复制
    copy(row) {
      this.$router.push({
        path: this.$routeProStr + '/marketing/store_seckill/create/' + row.id + '/1',
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/seckill/${row.id}`,
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
        path: this.$routeProStr + '/marketing/store_seckill/statistics/' + row.id,
      });
    },
    // 列表
    getList() {
      this.loading = true;
      this.tableFrom.status = this.tableFrom.status || '';
      seckillListApi(this.tableFrom)
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
      seckillStatusApi(data)
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

<style scoped lang="stylus">
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
