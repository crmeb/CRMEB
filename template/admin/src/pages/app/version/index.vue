<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row class="mb20">
        <el-col :span="24">
          <el-button type="primary" v-db-click @click="add" class="mr10">发布版本</el-button>
        </el-col>
      </el-row>
      <el-table
        :data="tableList"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="版本号" width="80">
          <template slot-scope="scope">
            <el-tooltip
              effect="light"
              v-if="scope.row.is_new"
              trigger="hover"
              placement="top-start"
              content="当前为最新线上版本!"
            >
              <i class="el-icon-s-promotion" style="font-size: 16px; color: red"></i>
            </el-tooltip>
            {{ scope.row.version }}
          </template>
        </el-table-column>
        <el-table-column label="平台类型" min-width="90">
          <template slot-scope="scope">
            <div>
              <span>{{ scope.row.platform === 1 ? '安卓' : '苹果' }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="升级信息" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.info }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否强制" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.is_force === 1 ? '强制' : '非强制' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="发布日期" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="下载地址" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.url }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除版本', scope.$index)">删除</a>
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
import { versionList, versionCrate } from '@/api/system';
export default {
  name: 'index',
  computed: {
    ...mapState('media', ['isMobile']),
    ...mapState('userLevel', ['categoryId']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  data() {
    return {
      verModal: false,
      total: 20,
      tableFrom: {
        page: 1,
        limit: 15,
      },
      loading: false,
      tableList: [],
    };
  },
  created() {
    this.getList();
  },
  methods: {
    // 修改成功
    submitFail() {
      this.getList();
    },
    // 聊天记录
    record(row) {
      this.rows = row;
      this.modals3 = true;
      this.isChat = true;
      this.getListRecord();
    },
    // 添加
    add() {
      this.$modalForm(versionCrate(0)).then((res) => {
        this.getList();
      });
    },
    // 版本信息列表
    getList() {
      this.loading = true;
      versionList()
        .then((res) => {
          this.tableList = res.data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((err) => {
          this.$message.error(err.msg);
          this.loading = false;
        });
    },
    // 添加
    edit(row) {
      this.$modalForm(versionCrate(row.id)).then((res) => {
        this.getList();
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `system/version_del/${row.id}`,
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
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.$message.success('成功!');
        } else {
          this.$message.error('失败!');
        }
      });
    },
    handleReset(name) {
      this.$refs[name].resetFields();
    },
    pageChange() {},
  },
};
</script>

<style lang="scss" scoped></style>
