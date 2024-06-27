<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row>
        <el-col v-bind="grid">
          <el-button v-auth="['admin-template']" type="primary" v-db-click @click="add">添加模板</el-button>
        </el-col>
      </el-row>
      <el-table
        :data="list"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="页面ID" width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="页面名称" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="页面类型" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.template_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="更新时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.update_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <div style="display: inline-block" v-if="scope.row.status != 1">
              <a v-db-click @click="setStatus(scope.row, index)">设为首页</a>
            </div>
            <el-divider direction="vertical" v-if="scope.row.status != 1" />
            <div style="display: inline-block" v-if="scope.row.status || scope.row.type">
              <a v-db-click @click="edit(scope.row)">编辑</a>
            </div>
            <el-divider direction="vertical" v-if="scope.row.status || scope.row.type" />
            <template>
              <el-dropdown size="small" @command="changeMenu(scope.row, index, $event)" :transfer="true">
                <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item command="1" v-show="!scope.row.type">设置默认数据</el-dropdown-item>
                  <el-dropdown-item command="2" v-show="!scope.row.type">恢复默认数据</el-dropdown-item>
                  <el-dropdown-item command="3" v-show="scope.row.id != 1">删除模板</el-dropdown-item>
                </el-dropdown-menu>
              </el-dropdown>
            </template>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    <el-dialog
      :visible.sync="isTemplate"
      title="开发移动端链接"
      width="470px"
      :show-close="true"
      :close-on-click-modal="false"
    >
      <div class="article-manager">
        <el-card :bordered="false" shadow="never" class="ivu-mt">
          <el-form
            ref="formItem"
            :model="formItem"
            label-width="120px"
            label-position="right"
            :rules="ruleValidate"
            @submit.native.prevent
          >
            <el-row :gutter="24">
              <el-col :span="24">
                <el-col v-bind="grid">
                  <el-form-item label="开发移动端链接：" prop="link" label-for="link">
                    <el-input v-model="formItem.link" placeholder="http://localhost:8080" />
                  </el-form-item>
                </el-col>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button v-db-click @click="cancel">取消</el-button>
        <el-button type="primary" v-db-click @click="handleSubmit('formItem')">提交</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import { diyList, diyDel, setStatus, recovery, getDiyCreate, getRecovery } from '@/api/diy';
import { getCookies, setCookies } from '@/libs/util';
import { mapState } from 'vuex';
export default {
  name: 'devise_list',
  data() {
    return {
      grid: {
        xl: 18,
        lg: 18,
        md: 18,
        sm: 24,
        xs: 24,
      },
      loading: false,
      list: [],
      isTemplate: false,
      formItem: {
        id: 0,
        link: '',
      },
      ruleValidate: {
        link: [{ required: true, message: '请输入移动端链接', trigger: 'blur' }],
      },
    };
  },
  created() {
    this.formItem.link = getCookies('moveLink');
    this.getList();
  },
  methods: {
    cancel() {
      this.$refs['formItem'].resetFields();
    },
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          setCookies('moveLink', this.formItem.link);
          this.$router.push({
            path: this.$routeProStr + '/setting/pages/diy',
            query: { id: this.formItem.id, type: 1 },
          });
        } else {
          return false;
        }
      });
    },
    changeMenu(row, index, name) {
      switch (name) {
        case '1':
          this.setDefault(row);
          break;
        case '2':
          this.recovery(row);
          break;
        case '3':
          this.del(row, '删除此模板', index);
          break;
        default:
      }
    },
    //设置默认数据
    setDefault(row) {
      getRecovery(row.id)
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    // 添加
    add() {
      this.$modalForm(getDiyCreate()).then(() => this.getList());
    },
    // 获取列表
    getList() {
      this.loading = true;
      diyList().then((res) => {
        this.loading = false;
        this.list = res.data.list;
      });
    },
    // 编辑
    edit(row) {
      this.formItem.id = row.id;
      if (row.type) {
        this.isTemplate = true;
      } else {
        this.$router.push({ path: this.$routeProStr + '/setting/pages/diy', query: { id: row.id, type: 0 } });
      }
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `diy/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 使用模板
    setStatus(row) {
      setStatus(row.id)
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((error) => {
          this.$message.error(error.msg);
        });
    },
    recovery(row) {
      recovery(row.id).then((res) => {
        this.$message.success(res.msg);
        this.getList();
      });
    },
  },
};
</script>

<style scoped></style>
