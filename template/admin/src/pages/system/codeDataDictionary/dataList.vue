<template>
  <div>
    <div class="i-layout-page-header header-title">
      <div class="fl_header">
        <el-button
          class="btn-back"
          icon="el-icon-arrow-left"
          size="small"
          type="text"
          v-db-click
          @click="$router.go(-1)"
          >返回</el-button
        >
        <el-divider direction="vertical"></el-divider>
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button v-auth="['system-crud-data_dictionary']" type="primary" v-db-click @click="add"
        >添加数据字典</el-button
      >
      <el-table
        :data="dictionaryList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
        row-key="id"
        :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
      >
        <el-table-column label="ID" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="字典名称" min-width="100"> </el-table-column>
        <el-table-column prop="value" label="字典数据" min-width="100"> </el-table-column>
        <el-table-column prop="sort" label="排序" min-width="100"> </el-table-column>
        <el-table-column prop="add_time" label="添加时间" min-width="200"> </el-table-column>
        <el-table-column fixed="right" label="操作" width="200">
          <template slot-scope="scope">
            <a v-db-click @click="addSub(scope.row.id)">添加下级</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="eidtOptions(scope.row.id)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="from.page"
          :limit.sync="from.limit"
          @pagination="getCrudDataDictionary"
        />
      </div>
    </el-card>
  </div>
</template>
<script>
import { mapState } from 'vuex';
import { getDataDictionaryInfoList, getDataDictionaryInfo } from '@/api/systemCodeGeneration';

export default {
  name: 'user_level',
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
      from: {
        name: '',
        page: 1,
        limit: 15,
      },
      dictionaryList: [],
      optionsModal: false,
      dictionaryName: '',
      optionsList: [],
      levelLists: [],
      total: 0,
      FromData: null,
      imgName: '',
      visible: false,
      titleType: 'level',
      dictionaryId: 0,
    };
  },
  created() {
    this.from.id = this.$route.query.id;
    this.getCrudDataDictionary();
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
    addSub(pid) {
      this.$modalForm(getDataDictionaryInfo(this.$route.query.id, 0, pid))
        .then((res) => {
          this.getCrudDataDictionary();
        })
        .catch((err) => {});
    },
    eidtOptions(id) {
      this.$modalForm(getDataDictionaryInfo(this.$route.query.id, id, 0))
        .then((res) => {
          this.getCrudDataDictionary();
        })
        .catch((err) => {});
    },
    getCrudDataDictionary() {
      getDataDictionaryInfoList(this.from).then((res) => {
        this.dictionaryList = res.data.list;
        this.total = res.data.count;
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `system/crud/data_dictionary/info_del/${row.id}`,
        method: 'delete',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.getCrudDataDictionary();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 添加
    add() {
      this.$modalForm(getDataDictionaryInfo(this.$route.query.id, 0, 0))
        .then((res) => {
          this.getCrudDataDictionary();
        })
        .catch((err) => {});
    },
    // 表格搜索
    searchs() {
      this.from.page = 1;
      this.getCrudDataDictionary();
    },
    dataOptions(id) {
      this.$router.push({
        path: this.$routeProStr + '/system/code_data_dictionary_datalist',
        query: { id: id },
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.options-list {
  .item {
    display: flex;
    align-items: center;
    .add {
      font-size: 18px;
      cursor: pointer;
      margin-right: 5px;
      // color: #2d8cf0;
    }
    .delete {
      font-size: 18px;
      cursor: pointer;
      color: #fb0144;
    }
  }
}
</style>
