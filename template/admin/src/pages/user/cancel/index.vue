<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form
        ref="levelFrom"
        :model="levelFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <el-row :gutter="24">
          <el-col v-bind="grid">
            <el-form-item label="状态：" label-for="status1">
              <el-radio-group v-model="levelFrom.status" type="button" @input="userSearchs(levelFrom.status)">
                <el-radio-button label="">全部</el-radio-button>
                <el-radio-button label="0">待审核</el-radio-button>
                <el-radio-button label="1">通过</el-radio-button>
                <el-radio-button label="2">拒绝</el-radio-button>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="用户搜索：" label-for="title">
              <el-input
                search
                enter-button
                v-model="levelFrom.keywords"
                placeholder="请输入用户昵称/ID/手机号"
                @on-search="userSearchs"
              />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <el-table
        :data="levelLists"
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
        <el-table-column label="昵称" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="手机号" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.phone }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.status }}</span>
          </template>
        </el-table-column>
        <el-table-column label="申请时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="审核时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.up_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.remark }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <a v-db-click @click="agree(scope.row)">同意</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="refuse(scope.row)">拒绝</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="remark(scope.row)">备注</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="levelFrom.page"
          :limit.sync="levelFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
    <!-- 等级任务-->
    <remark ref="remark" @submitFail="submitFail"></remark>
  </div>
</template>
<script>
import { mapState, mapMutations } from 'vuex';
import { userCancelList, userCancelSetMark } from '@/api/user';
import taskList from './handle/task';
import editFrom from '@/components/from/from';
import remark from '@/components/remark/index';
export default {
  name: 'user_level',
  components: { remark },
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      id: '',
      loading: false,
      levelFrom: {
        status: '',
        keywords: '',
        page: 1,
        limit: 15,
      },
      levelLists: [],
      total: 0,
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
    ...mapMutations('userLevel', ['getlevelId']),
    remark(row) {
      this.id = row.id;
      this.$refs.remark.formValidate.remark = row.remark;
      this.$refs.remark.modals = true;
    },
    agree(row) {
      this.delfromData = {
        title: '注销用户',
        url: `/user/cancel/agree/${row.id}`,
        method: 'get',
      };
      this.$modalSure(this.delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    refuse(row) {
      this.delfromData = {
        title: '拒绝注销用户',
        info: '您确认拒绝注销此用户吗?',
        url: `/user/cancel/refuse/${row.id}`,
        method: 'get',
      };
      this.$modalSure(this.delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    submitFail(text) {
      let data = {
        id: this.id,
        mark: text,
      };
      userCancelSetMark(data)
        .then((res) => {
          this.$refs.remark.modals = false;
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((err) => {
          this.$refs.remark.modals = false;
          this.$message.error(err.msg);
        });
    },

    getList() {
      this.loading = true;
      this.levelFrom.status = this.levelFrom.status || '';
      userCancelList(this.levelFrom)
        .then(async (res) => {
          let data = res.data;
          this.levelLists = data.list;
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
      this.levelFrom.page = 1;
      this.getList();
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
