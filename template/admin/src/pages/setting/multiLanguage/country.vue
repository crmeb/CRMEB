<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt mb10" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          label-position="right"
          @submit.native.prevent
          inline
        >
          <el-form-item label="搜索：">
            <div class="acea-row row-middle">
              <el-input
                clearable
                placeholder="请输入语言Code"
                v-model="formValidate.keyword"
                class="form_content_width"
              />
            </div>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="selChange">搜索</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never">
      <el-row>
        <el-col v-bind="grid">
          <el-button type="primary" v-db-click @click="add">添加语言地区</el-button>
        </el-col>
      </el-row>
      <el-table ref="table" :data="tabList" class="ivu-mt mt14" v-loading="loading" empty-text="暂无数据">
        <el-table-column label="编号" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="浏览器语言识别码" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.code }}</span>
          </template>
        </el-table-column>
        <el-table-column label="语言说明" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="关联语言" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.link_lang }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="100">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除地区语言', scope.$index)">删除</a>
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
import { mapState } from 'vuex';
import { langCountryList, langCountryForm } from '@/api/setting';
export default {
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      formValidate: {
        keyword: '',
        page: 1,
        limit: 20,
      },
      total: 0,
      loading: false,
      tabList: [],
      code: null,
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
  mounted() {
    this.getList();
  },
  methods: {
    // 添加
    add() {
      this.$modalForm(langCountryForm(0)).then(() => this.getList());
    },
    edit(row) {
      this.$modalForm(langCountryForm(row.id)).then(() => this.getList());
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `setting/lang_country/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.tabList.splice(num, 1);
          // this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    selChange() {
      this.formValidate.page = 1;
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      langCountryList(this.formValidate)
        .then(async (res) => {
          this.loading = false;
          this.tabList = res.data.list;
          this.total = res.data.count;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
  },
};
</script>
<style lang="scss" scoped>
.ivu-mt .type .item {
  margin: 3px 0;
}
.tabform {
  margin-bottom: 10px;
}
.Refresh {
  font-size: 12px;
  color: var(--prev-color-primary);
  cursor: pointer;
}
.ivu-form-item {
  margin-bottom: 10px;
}
.status ::v-deep .item ~ .item {
  margin-left: 6px;
}
.status ::v-deep .statusVal {
  margin-bottom: 7px;
}

/* .ivu-mt ::v-deep .ivu-table-header */
/* border-top:1px dashed #ddd!important */
.type {
  padding: 3px 0;
  box-sizing: border-box;
}
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
