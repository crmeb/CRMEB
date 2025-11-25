<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="levelFrom"
          :model="levelFrom"
          :label-width="labelWidth"
          label-position="right"
          @submit.native.prevent
          inline
        >
          <el-form-item label="搜索：" label-for="keyword">
            <el-input clearable v-model="levelFrom.name" placeholder="请输入模板名称" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-button type="primary" v-db-click @click="freight">添加运费模板</el-button>
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
        <el-table-column label="模板名称" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="计费方式" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="指定包邮" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.appoint }}</span>
          </template>
        </el-table-column>
        <el-table-column label="排序" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.sort }}</span>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="100">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row.id)">修改</a>
            <el-divider direction="vertical" v-if="scope.row.id !== 1" />
            <a v-db-click @click="del(scope.row, '删除模版', index)" v-if="scope.row.id !== 1">删除</a>
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
    <!-- 运费模板-->
    <freight-template
      v-if="isTemplate"
      ref="template"
      @addSuccess="getList"
      @close="
        () => {
          isTemplate = false;
        }
      "
    ></freight-template>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { templatesApi } from '@/api/setting';
import freightTemplate from '@/components/freightTemplate/index';

export default {
  name: 'setting_templates',
  components: { freightTemplate },
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

      levelFrom: {
        name: '',
        page: 1,
        limit: 15,
      },
      levelLists: [],
      total: 0,
      FromData: null,
      isTemplate: false,
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
    // 添加运费模板
    freight() {
      this.isTemplate = true;
      this.$nextTick((e) => {
        this.$refs.template.id = 0;
        this.$refs.template.isTemplate = true;
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `setting/shipping_templates/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.levelLists.splice(num, 1);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 运费模板列表
    getList() {
      this.loading = true;
      templatesApi(this.levelFrom)
        .then(async (res) => {
          let data = res.data;
          this.levelLists = data.data;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 编辑
    edit(id) {
      this.isTemplate = true;
      this.$nextTick((e) => {
        this.$refs.template.isTemplate = true;
        this.$refs.template.editFrom(id);
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
