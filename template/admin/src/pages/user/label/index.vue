<template>
  <div>
    <el-row class="ivu-mt box-wrapper">
      <el-col v-bind="grid1" class="left-wrapper">
        <div class="tree_tit" v-db-click @click="addSort">
          <i class="el-icon-circle-plus"></i>
          添加分类
        </div>
        <div class="tree">
          <el-tree
            :data="labelSort"
            node-key="id"
            default-expand-all
            highlight-current
            :expand-on-click-node="false"
            @node-click="bindMenuItem"
            :current-node-key="treeId"
          >
            <span class="custom-tree-node" slot-scope="{ data }">
              <span class="file-name">
                <img v-if="!data.pid" class="icon" src="@/assets/images/file.jpg" />
                {{ data.name }}</span
              >
              <span v-if="data.id">
                <el-dropdown @command="(command) => clickMenu(data, command)">
                  <i class="el-icon-more el-icon--right"></i>
                  <template slot="dropdown">
                    <el-dropdown-menu>
                      <el-dropdown-item command="1">编辑分类</el-dropdown-item>
                      <el-dropdown-item v-if="data.id" command="2">删除分类</el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
              </span>
            </span>
          </el-tree>
        </div>
      </el-col>
      <el-col v-bind="grid2" ref="rightBox">
        <el-card :bordered="false" shadow="never">
          <el-row>
            <el-col>
              <el-button v-auth="['admin-user-label_add']" type="primary" v-db-click @click="add">添加标签</el-button>
              <!-- <el-button v-auth="['admin-user-label_add']" type="success" v-db-click @click="addSort">添加分类</el-button> -->
            </el-col>
          </el-row>
          <el-table
            :data="labelLists"
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
            <el-table-column label="标签名称" width="80">
              <template slot-scope="scope">
                <span>{{ scope.row.label_name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="分类名称" min-width="80">
              <template slot-scope="scope">
                <span>{{ scope.row.cate_name }}</span>
              </template>
            </el-table-column>
            <el-table-column fixed="right" label="操作" width="100">
              <template slot-scope="scope">
                <a v-db-click @click="edit(scope.row.id)">修改</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="del(scope.row, '删除分类', scope.$index)">删除</a>
              </template>
            </el-table-column>
          </el-table>
          <div class="acea-row row-right page">
            <pagination
              v-if="total"
              :total="total"
              :page.sync="labelFrom.page"
              :limit.sync="labelFrom.limit"
              @pagination="getList"
            />
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { userLabelAll, userLabelApi, userLabelAddApi, userLabelEdit, userLabelCreate } from '@/api/user';
export default {
  name: 'user_label',
  data() {
    return {
      treeId: '',
      grid1: {
        xl: 4,
        lg: 4,
        md: 6,
        sm: 8,
        xs: 0,
      },
      grid2: {
        xl: 20,
        lg: 20,
        md: 18,
        sm: 16,
        xs: 24,
      },

      loading: false,
      labelFrom: {
        page: 1,
        limit: 15,
        label_cate: '',
      },
      labelLists: [],
      total: 0,
      theme3: 'light',
      labelSort: [],
      sortName: '',
      current: 0,
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
  created() {
    this.getUserLabelAll();
  },
  methods: {
    // 添加
    add() {
      this.$modalForm(userLabelAddApi(0, this.labelFrom.label_cate)).then(() => this.getList());
    },
    // 分组列表
    getList() {
      this.loading = true;
      userLabelApi(this.labelFrom)
        .then(async (res) => {
          let data = res.data;
          this.labelLists = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 修改
    edit(id) {
      this.$modalForm(userLabelAddApi(id)).then(() => this.getList());
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `user/user_label/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.labelLists.splice(num, 1);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 标签分类
    getUserLabelAll(key) {
      userLabelAll().then((res) => {
        let obj = {
          name: '全部',
          id: '',
        };
        res.data.unshift(obj);
        res.data.forEach((el) => {
          el.status = false;
        });
        if (!key) {
          this.sortName = res.data[0].id;
          this.labelFrom.label_cate = res.data[0].id;
          this.getList();
        }
        this.labelSort = res.data;
      });
    },
    // 显示标签小菜单
    showMenu(item) {
      this.labelSort.forEach((el) => {
        if (el.id == item.id) {
          el.status = item.status ? false : true;
        } else {
          el.status = false;
        }
      });
    },
    //编辑标签
    labelEdit(item) {
      this.$modalForm(userLabelEdit(item.id)).then(() => this.getUserLabelAll(1));
    },
    // 添加分类
    addSort() {
      this.$modalForm(userLabelCreate()).then(() => this.getUserLabelAll());
    },
    deleteSort(row, tit) {
      let num = this.labelSort.findIndex((e) => {
        return e.id == row.id;
      });
      let delfromData = {
        title: tit,
        num: num,
        url: `user/user_label_cate/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.labelSort.splice(num, 1);
          this.labelSort = [];
          this.getUserLabelAll();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    clickMenu(data, name) {
      if (name == 1) {
        this.labelEdit(data);
      } else if (name == 2) {
        this.deleteSort(data, '删除分类');
      }
    },
    bindMenuItem(name, index) {
      this.labelFrom.page = 1;
      this.current = index;
      this.labelSort.forEach((el) => {
        el.status = false;
      });
      this.labelFrom.label_cate = name.id;
      this.getList();
    },
  },
};
</script>

<style lang="stylus" scoped>
.showOn {
  color: #2d8cf0;
  background: #f0faff;
  z-index: 2;
}

::v-deep .ivu-menu-vertical .ivu-menu-item-group-title {
  display: none;
}

::v-deep .ivu-menu-vertical.ivu-menu-light:after {
  display: none;
}

.left-wrapper {
  height: 920px;
  background: #fff;
  border-right: 1px solid #f2f2f2;
}

.menu-item {
  z-index: 50;
  position: relative;
  display: flex;
  justify-content: space-between;
  word-break: break-all;

  .icon-box {
    z-index: 3;
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    display: none;
  }

  &:hover .icon-box {
    display: block;
  }

  .right-menu {
    z-index: 10;
    position: absolute;
    right: -106px;
    top: -11px;
    width: auto;
    min-width: 121px;
  }
}
</style>
