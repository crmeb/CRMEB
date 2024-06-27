<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="levelFrom"
          :model="from"
          :label-width="labelWidth"
          :label-position="labelPosition"
          inline
          @submit.native.prevent
        >
          <el-form-item label="字典名称：" label-for="name">
            <el-input clearable v-model="from.name" placeholder="请输入字典名称" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="searchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
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
      >
        <el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="字典名称" min-width="100"> </el-table-column>
        <el-table-column prop="mark" label="数据标识" min-width="200"> </el-table-column>
        <el-table-column prop="level" label="类型" min-width="200">
          <template slot-scope="scope">
            <span>{{ scope.row.level ? '多级' : '一级' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="add_time" label="添加时间" min-width="200"> </el-table-column>
        <el-table-column fixed="right" label="操作" width="200">
          <template slot-scope="scope">
            <a v-db-click @click="eidtOptions(scope.row.id)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="dataOptions(scope.row.id)">数据管理</a>
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
import {
  getDataDictionaryList,
  getDataDictionaryForm,
  crudDataDictionaryList,
  saveCrudDataDictionaryList,
} from '@/api/systemCodeGeneration';

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
    eidtOptions(id) {
      this.$modalForm(getDataDictionaryForm(id))
        .then((res) => {
          this.getCrudDataDictionary();
        })
        .catch((err) => {});
    },
    getCrudDataDictionary() {
      getDataDictionaryList(this.from).then((res) => {
        this.dictionaryList = res.data.list;
        this.total = res.data.count;
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `system/crud/data_dictionary_list/del/${row.id}`,
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
      this.$modalForm(getDataDictionaryForm(0))
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
