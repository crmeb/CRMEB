<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{padding:0}">
      <div class="padding-add">
        <el-form
            ref="levelFrom"
            :model="levelFrom"
            :label-width="labelWidth"
            :label-position="labelPosition"
            inline
            @submit.native.prevent
        >
          <el-form-item label="等级状态：" label-for="status1">
            <el-select
                v-model="levelFrom.is_show"
                placeholder="请选择"
                clearable
                element-id="status1"
                @change="userSearchs"
                class="form_content_width"
            >
              <el-option value="1" label="显示"></el-option>
              <el-option value="0" label="不显示"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="等级名称：" label-for="title">
            <el-input
                clearable
                v-model="levelFrom.title"
                placeholder="请输入等级名称"
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
      <el-button v-auth="['admin-user-level_add']" type="primary" v-db-click @click="add"
      >添加用户等级</el-button>
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
        <el-table-column label="等级图标" min-width="100">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.icon" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="等级背景图" min-width="100">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="等级名称" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="等级" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.grade }}</span>
          </template>
        </el-table-column>
        <el-table-column label="享受折扣" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.discount }}</span>
          </template>
        </el-table-column>
        <el-table-column label="经验值要求" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.exp_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否显示" min-width="100">
          <template slot-scope="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_show"
              :value="scope.row.is_show"
              size="large"
              @change="onchangeIsShow(scope.row)"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column fixed="right" label="操作" width="100">
          <template slot-scope="scope">
            <el-dropdown size="small" @command="changeMenu(scope.row, $event, scope.$index)" :transfer="true">
              <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
              <el-dropdown-menu slot="dropdown">
                <!--                                                <el-dropdown-item name="1">等级任务</el-dropdown-item>-->
                <el-dropdown-item command="3">编辑等级</el-dropdown-item>
                <el-dropdown-item command="2">删除等级</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
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
    <task-list ref="tasks"></task-list>
  </div>
</template>
<script>
import { mapState, mapMutations } from 'vuex';
import { levelListApi, setShowApi, createApi } from '@/api/user';
import taskList from './handle/task';
export default {
  name: 'user_level',
  components: { taskList },
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
        is_show: '',
        title: '',
        page: 1,
        limit: 15,
      },
      levelLists: [],
      total: 0,
      FromData: null,
      imgName: '',
      visible: false,
      levelId: 0,
      modalTitleSs: '',
      titleType: 'level',
      modelTask: false,
      num: 0,
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
    // 操作
    changeMenu(row, name, num) {
      this.levelId = row.id;
      switch (name) {
        case '1':
          this.getlevelId(this.levelId);
          this.$refs.tasks.modals = true;
          this.$refs.tasks.getList();
          break;
        case '3':
          this.edit(row);
          break;
        default:
          this.del(row, '删除等级', num);
      }
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `user/user_level/delete/${row.id}`,
        method: 'put',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.levelLists.splice(num, 1);
          this.total--;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 删除成功
    // submitModel () {
    //     this.levelLists.splice(this.delfromData.num, 1)
    // },
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        is_show: row.is_show,
      };
      setShowApi(data)
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 等级列表
    getList() {
      this.loading = true;
      this.levelFrom.is_show = this.levelFrom.is_show || '';
      levelListApi(this.levelFrom)
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
    // 添加
    add() {
      this.levelId = 0;
      this.$modalForm(createApi({ id: this.levelId })).then(() => this.getList());
    },
    // 编辑
    edit(row) {
      this.levelId = row.id;
      this.$modalForm(createApi({ id: this.levelId })).then(() => this.getList());
      this.getlevelId(this.levelId);
    },
    // 表格搜索
    userSearchs() {
      this.levelFrom.page = 1;
      this.getList();
    },
    // 修改成功
    submitFail() {
      this.getList();
    },
  },
};
</script>

<style scoped lang="stylus">
.tabBox_img
    width 36px
    height 36px
    border-radius:4px
    cursor pointer
    img
        width 100%
        height 100%
</style>
