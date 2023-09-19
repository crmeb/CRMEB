<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{padding:0}">
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
            <el-input
                clearable
                v-model="from.name"
                placeholder="请输入字典名称"
                class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="searchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button v-auth="['system-crud-data_dictionary']" type="primary" @click="add">添加数据字典</el-button>
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
        <el-table-column prop="name" label="字典名称" min-width="100">
        </el-table-column>
        <el-table-column label="字典数据" min-width="180">
          <template slot-scope="scope">
            <el-tag v-for="(item,i) in scope.row.value" :key="i">{{item.label+' => '+item.value}}</el-tag>
          </template>
        </el-table-column>
        <el-table-column fixed="right" label="操作" width="120">
          <template slot-scope="scope">
            <a @click="eidtOptions(scope.row.id)">修改</a>
            <el-divider direction="vertical"></el-divider>
            <a @click="del(scope.row, '删除', scope.$index)">删除</a>
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
    <el-dialog :visible.sync="optionsModal" title="字典配置" @close="beforeChange" :close-on-click-modal="false" width="600px">
      <div class="options-list">
        <el-form ref="form" :inline="true" label-width="80px">
        <div class="mb10">
          <el-form-item label="字典名称：">
            <el-input class="mr10" v-model="dictionaryName" placeholder="字典名称" style="width: 310px" />
          </el-form-item>
        </div>
        <div class="item" v-for="(item, index) in optionsList" :key="index">
          <el-form-item label="数据名称：">
            <el-input class="mr10" v-model="item.label" placeholder="label" style="width: 150px;" />
          </el-form-item>
          <el-form-item label="数据值：">
            <el-input class="mr10" v-model="item.value" placeholder="value" style="width: 150px" />
          </el-form-item>
          <div style="display: inline-block;margin-bottom: 14px">
            <i v-if="index == optionsList.length - 1"  class="el-icon-circle-plus-outline add" title="新增" @click="addOneOptions" />
            <i v-if="index > 0"  class="el-icon-remove-outline delete" title="删除" @click="delOneOptions(index)" />
          </div>
        </div>
        </el-form>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="optionsModal = false">取 消</el-button>
        <el-button type="primary" @click="addOptions">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>

import { mapState } from 'vuex';
import {
  crudDataDictionary,
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
      dictionaryList:[],
      optionsModal:false,
      dictionaryName:'',
      optionsList:[],
      levelLists: [],
      total: 0,
      FromData: null,
      imgName: '',
      visible: false,
      titleType: 'level',
      dictionaryId:0,
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
    beforeChange(){
      this.getCrudDataDictionary();
    },
    addOneOptions() {
      this.optionsList.push({
        label: '',
        value: '',
      });
    },
    delOneOptions(i) {
      this.optionsList.splice(i, 1);
    },
    eidtOptions(id) {
      this.dictionaryId = id;
      this.optionsModal = true;
      crudDataDictionaryList(this.dictionaryId).then((res) => {
        this.dictionaryName = res.data.name;
        this.optionsList = res.data.value || [{ label: '', value: '' }];
      });
    },
    addOptions() {
      let d = {
        name: this.dictionaryName,
        value: this.optionsList,
      };
      saveCrudDataDictionaryList(this.dictionaryId, d).then((res) => {
        this.optionsModal = false;
        this.dictionaryId = 0;
        this.dictionaryName = '';
        this.getCrudDataDictionary();
        this.$message.success(res.msg);
      }).catch(res => {
        this.$message.error(res.msg);
      });
    },
    getCrudDataDictionary() {
      crudDataDictionary(this.from).then((res) => {
        this.dictionaryList = res.data.list;
        this.total = res.data.count;
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `system/crud/data_dictionary/${row.id}`,
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
      this.optionsModal = true;
      this.dictionaryId = 0;
      this.dictionaryName = '';
      this.optionsList =[{ label: '', value: '' }];
    },
    // 表格搜索
    searchs() {
      this.from.page = 1;
      this.getCrudDataDictionary();
    },
    // 修改成功
    submitFail() {
      this.getList();
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
