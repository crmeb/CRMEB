<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row class="mb20">
        <el-col :span="24">
          <el-button type="primary" v-db-click @click="add" class="mr10">创建链接</el-button>
        </el-col>
      </el-row>
      <el-table
        :data="tableList"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="编号" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="名称" width="180">
          <template slot-scope="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="跳转地址" width="180">
          <template slot-scope="scope">
            <span>{{ scope.row.path }}</span>
          </template>
        </el-table-column>
        <el-table-column label="系统链接(编辑不改变)" min-width="200">
          <template slot-scope="scope">
            <span>{{ scope.row.http_url }}</span>
            <a class="ml10" v-db-click @click="onCopy(scope.row.http_url)">复制</a>
          </template>
        </el-table-column>
        <el-table-column label="微信链接(编辑改变)" min-width="200">
          <template slot-scope="scope">
            <span>{{ scope.row.url }}</span>
            <a class="ml10" v-db-click @click="onCopy(scope.row.url)">复制</a>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="到期时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.expire_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除链接', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="tableFrom.page"
          :limit.sync="tableFrom.limit"
          @pagination="routineSchemeList"
        />
      </div>
    </el-card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { routineSchemeList, routineSchemeForm, routineSchemeDel } from '@/api/app';
export default {
  name: 'index',
  computed: {
    ...mapState('media', ['isMobile']),
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
    this.routineSchemeList();
  },
  methods: {
    // 添加
    add() {
      this.$modalForm(routineSchemeForm(0)).then((res) => {
        this.routineSchemeList();
      });
    },
    onCopy(copyData) {
      this.$copyText(copyData)
        .then((message) => {
          this.$message.success('复制成功');
        })
        .catch((err) => {
          this.$message.error('复制失败');
        });
    },
    // 列表
    routineSchemeList() {
      this.loading = true;
      routineSchemeList(this.tableFrom)
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
      this.$modalForm(routineSchemeForm(row.id)).then((res) => {
        this.routineSchemeList();
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `app/routine/scheme_del/${row.id}`,
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
  },
};
</script>

<style lang="scss" scoped></style>
