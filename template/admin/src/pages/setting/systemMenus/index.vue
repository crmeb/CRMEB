<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="roleData"
          :model="roleData"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
          inline
        >
          <el-form-item label="按钮名称：" prop="status2" label-for="status2">
            <el-input clearable v-model="roleData.keyword" placeholder="请输入按钮名称" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="getData">查询</el-button>
          </el-form-item>
          <!-- <el-row >
            <el-col v-bind="grid">
              <el-button type="primary" v-db-click @click="menusAdd('添加规则')">添加规则 </el-button>
            </el-col>
          </el-row> -->
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <vxe-table
        :border="false"
        class="vxeTable"
        highlight-hover-row
        highlight-current-row
        :loading="loading"
        ref="xTable"
        header-row-class-name="false"
        :tree-config="tabconfig"
        :data="tableData"
        row-id="id"
      >
        <vxe-table-column field="menu_name" tree-node title="按钮名称" min-width="100"></vxe-table-column>
        <vxe-table-column field="menu_path" title="类型" min-width="240" tooltip="true">
          <template v-slot="{ row }">
            <span v-if="row.auth_type == 1">菜单：{{ row.menu_path }}</span>
            <span v-if="row.auth_type == 3">按钮</span>
            <span v-if="row.auth_type == 2">数据权限</span>
          </template>
        </vxe-table-column>
        <vxe-table-column field="sort" title="排序" width="150"></vxe-table-column>
        <vxe-table-column field="flag" title="是否显示" width="150">
          <template v-slot="{ row }">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="row.is_show_path"
              :value="row.is_show_path"
              @change="onchangeIsShow(row)"
              size="large"
            >
            </el-switch>
          </template>
        </vxe-table-column>
        <vxe-table-column field="date" title="操作" align="center" width="150" fixed="right">
          <template v-slot="{ row }">
            <a v-db-click @click="edit(row, '编辑')">编辑</a>
          </template>
        </vxe-table-column>
      </vxe-table>
    </el-card>
    <menus-from
      :formValidate="formValidate"
      :titleFrom="titleFrom"
      @getList="getList"
      @changeMenu="changeMenu"
      ref="menusFrom"
      @clearFrom="clearFrom"
    ></menus-from>
    <el-dialog :visible.sync="ruleModal" width="1100px" title="权限列表" @closed="modalchange">
      <div class="search-rule">
        <el-alert
          title="基础接口，可多选，并且添加后不会再展示出现；删除权限后才会出现；公共接口，可多选，并且添加后会继续展示；"
        ></el-alert>
        <el-input
          class="mr10"
          v-model="searchRule"
          placeholder="输入关键词搜索"
          clearable
          style="width: 300px"
          ref="search"
          @on-enter="searchRules"
          @on-clear="searchRules"
        />
        <el-button type="primary" v-db-click @click="searchRules">搜索</el-button>
        <el-button v-db-click @click="init">重置</el-button>
      </div>
      <div class="route-list">
        <div class="tree">
          <el-tree
            ref="treeBox"
            :data="ruleCateList"
            :highlight-current="true"
            :props="defaultProps"
            node-key="id"
            :default-expanded-keys="expandedKeys"
            :current-node-key="nodeKey"
            @node-click="handleNodeClick"
          ></el-tree>
        </div>
        <div class="rule">
          <div
            class="rule-list"
            v-show="!arrs.length || arrs.includes(item.id)"
            :class="{ 'select-rule': seletRouteIds.includes(item.id) }"
            v-for="(item, index) in children"
            :key="index"
            v-db-click
            @click="selectRule(item)"
          >
            <div>接口名称：{{ item.name }}</div>
            <div>请求方式：{{ item.method }}</div>
            <div>接口地址：{{ item.path }}</div>
          </div>
        </div>
      </div>
      <!-- <el-tabs v-model="routeType" @on-click="changTab">
        <el-tab-pane :label="item.name" :name="'' + index" v-for="(item, index) in foundationList" :key="item"></el-tab-pane>
      </el-tabs> -->
      <span slot="footer" class="dialog-footer">
        <el-button v-db-click @click="ruleModal = false">取 消</el-button>
        <el-button type="primary" v-db-click @click="addRouters">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import {
  getTable,
  menusDetailsApi,
  isShowApi,
  editMenus,
  getRuleList,
  menusBatch,
  getMenusUnique,
  menusRuleCate,
} from '@/api/systemMenus';
import formCreate from '@form-create/element-ui';
import menusFrom from './components/menusFrom';
import { formatFlatteningRoutes, findFirstNonNullChildren, findFirstNonNullChildrenKeys } from '@/libs/system';

export default {
  name: 'systemMenus',
  data() {
    return {
      children: [],
      expandedKeys: [],
      tabconfig: { children: 'children', reserve: true, accordion: true },
      spinShow: false,
      ruleModal: false,
      searchRule: '',
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      roleData: {
        is_show: 1,
        keyword: '',
        // auth_type: 3,
      },
      defaultProps: {
        children: 'children',
        label: 'name',
      },
      ruleCateList: [], //权限树
      loading: false,
      tableData: [],
      FromData: null,
      icons: '',
      formValidate: {},
      titleFrom: '',
      modalTitleSs: '',
      routeType: '0',
      arrs: [],
      foundationList: [], // 基础接口列表
      openList: [], // 公开接口列表
      seletRoute: [], // 选中路由
      seletRouteIds: [], // 选中id
      menusId: 0, // 选中分类id
      nodeKey: 0, // 选中节点
      openId: '',
    };
  },
  components: { menusFrom, formCreate: formCreate.$form() },
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  mounted() {
    this.getData();
  },
  methods: {
    init() {
      this.searchRule = '';
      this.searchRules();
    },
    addRouters() {
      let data = {
        menus: this.seletRoute,
      };
      menusBatch(data)
        .then((res) => {
          this.getData();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    selectRule(data) {
      if (this.seletRouteIds.includes(data.id)) {
        let i = this.seletRouteIds.findIndex((e) => e == data.id);
        this.seletRouteIds.splice(i, 1);
        this.seletRoute.splice(i, 1);
      } else {
        this.seletRouteIds.push(data.id);
        this.seletRoute.push({
          menu_name: data.name,
          unique_auth: '',
          api_url: data.path,
          path: this.menusId,
          method: data.method,
        });
      }
    },
    changTab(name) {
      this.routeType = name;
      let index = parseInt(name);
      this.children = this.foundationList[index] ? this.foundationList[index].children : [];
      this.searchRules();
    },
    // 搜索规则
    searchRules() {
      if (this.searchRule.trim()) {
        this.arrs = [];
        let arr = this.foundationList;
        for (var i = 0; i < arr.length; i++) {
          if (arr[i].name.indexOf(this.searchRule) !== -1) {
            this.arrs.push(arr[i].id);
          }
        }
      } else {
        this.arrs = [];
      }
    },
    addRoute(row) {
      this.menusId = row.id;
      this.routeType = '0';
      // this.getRuleList();
      menusRuleCate().then((res) => {
        this.ruleCateList = res.data;
        this.ruleModal = true;
        if (res.data.length) {
          this.$nextTick((e) => {
            this.expandedKeys = findFirstNonNullChildrenKeys(res.data[0], []);
            this.nodeKey = findFirstNonNullChildren(res.data).id;
            this.$refs.treeBox.setCurrentKey(this.nodeKey);
            this.getRuleList(this.nodeKey);
          });
        }
      });
    },
    handleNodeClick(data) {
      this.getRuleList(data.id);
    },
    modalchange() {
      this.seletRouteIds = [];
      this.seletRoute = [];
    },
    // 获取权限列表
    getRuleList(cate_id) {
      getRuleList(cate_id).then((res) => {
        this.foundationList = res.data;
        this.children = res.data;
        this.searchRules();

        // this.openList = [];
        // this.seletRouteIds = [];
        // this.seletRoute = [];
      });
    },
    // 修改规则状态
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        is_show_path: row.is_show_path,
        is_show: -1,
      };
      isShowApi(data)
        .then(async (res) => {
          this.$message.success(res.msg);
          this.$store.dispatch('menus/getMenusNavList');
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 请求列表
    getList() {
      this.formValidate = Object.assign({}, this.$options.data().formValidate);
      this.getData();
    },

    // 清除表单数据
    clearFrom() {
      this.formValidate = Object.assign({}, this.$options.data().formValidate);
    },
    // 添加子菜单
    addE(row, title) {
      this.formValidate = {};
      let pid = row.id.toString();
      if (pid) {
        menusDetailsApi(row.id)
          .then(async (res) => {
            this.formValidate.path = res.data.path;
            this.formValidate.path.push(row.id);
            this.formValidate.pid = pid;
            this.$refs.menusFrom.modals = true;
            this.$refs.menusFrom.valids = false;
            this.titleFrom = title;
            this.formValidate.auth_type = 1;
            this.formValidate.is_show = 0;
            this.formValidate.is_show_path = 0;
          })
          .catch((res) => {
            this.$message.error(res.msg);
          });
      } else {
        this.formValidate.pid = pid;
        this.$refs.menusFrom.modals = true;
        this.$refs.menusFrom.valids = false;
        this.titleFrom = title;
        this.formValidate.auth_type = 1;
        this.formValidate.is_show = 0;
        this.formValidate.is_show_path = 0;
      }
      // this.formValidate.pid = row.id.toString();
      // this.$refs.menusFrom.modals = true;
      // this.$refs.menusFrom.valids = false;
      // this.titleFrom = title;
      // this.formValidate.auth_type = 1;
      // this.formValidate.is_show = '0';
    },
    // 删除
    del(row, tit) {
      let delfromData = {
        title: tit,
        url: `/setting/menus/${row.id}`,
        method: 'DELETE',
        ids: '',
      };

      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.getData();
          this.getMenusUnique();
          // this.$store.dispatch('menus/getMenusNavList');
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 规则详情
    menusDetails(id) {
      menusDetailsApi(id)
        .then(async (res) => {
          this.formValidate = res.data;
          this.$refs.menusFrom.modals = true;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 编辑
    edit(row, title, index) {
      this.openId = row.id;
      this.formValidate = {};
      this.menusDetails(row.id);
      this.titleFrom = title;
      this.$refs.menusFrom.valids = false;
      this.$refs.menusFrom.getAddFrom(row.id);
    },
    // 添加
    menusAdd(title) {
      this.formValidate = {};
      this.$refs.menusFrom.modals = true;
      this.$refs.menusFrom.valids = false;
      // this.formValidate = Object.assign(this.$data, this.$options.formValidate());
      this.titleFrom = title;
      this.formValidate.auth_type = 1;
      this.formValidate.is_show = 0;
      this.formValidate.is_show_path = 0;
    },
    // 新增页面表单
    // getAddFrom () {
    //     this.spinShow = true;
    //     addMenus(this.roleData).then(async res => {
    //         this.FromData = res.data;
    //         this.$refs.menusFrom.modals = true;
    //         this.spinShow = false;
    //     }).catch(res => {
    //         this.spinShow = false;
    //         this.$message.error(res.msg);
    //     })
    // },
    // 列表
    getData() {
      this.loading = true;
      getTable(this.roleData)
        .then(async (res) => {
          this.tableData = res.data;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    changeMenu(data) {
      this.changeData(this.tableData, data);
      this.getMenusUnique();
    },
    changeData(arr, data) {
      let arrKey = Object.keys(data);
      arr.map((e) => {
        if (e.id == this.openId) {
          arrKey.map((el) => {
            e[el] = data[el];
          });
        } else if (e.children) {
          this.changeData(e.children, data);
        }
      });
    },
    getMenusUnique() {
      getMenusUnique().then((res) => {
        let data = res.data;
        this.$store.commit('userInfo/uniqueAuth', data.uniqueAuth);
        this.$store.commit('menus/getmenusNav', data.menus);
        this.$store.dispatch('routesList/setRoutesList', data.menus);
        let arr = formatFlatteningRoutes(this.$router.options.routes);
        this.formatTwoStageRoutes(arr);
        let routes = formatFlatteningRoutes(data.menus);
        this.$store.commit('menus/setOneLvRoute', routes);
        this.bus.$emit('routesListChange');
      });
    },
    formatTwoStageRoutes(arr) {
      if (arr.length <= 0) return false;
      const newArr = [];
      const cacheList = [];
      arr.forEach((v) => {
        if (v && v.meta && v.meta.keepAlive) {
          newArr.push({ ...v });
          cacheList.push(v.name);
          this.$store.dispatch('keepAliveNames/setCacheKeepAlive', cacheList);
        }
      });
      return newArr;
    },
    // 关闭按钮
    cancel() {
      this.$emit('onCancel');
    },
  },
};
</script>

<style scoped lang="scss">
.vxeTable {
  > .vxe-table--header-wrapper {
    background: #fff !important;
  }

  .icon {
    font-size: 20px;
  }
}
::v-deep .vxe-table--render-default .vxe-table--border-line {
  z-index: 2 !important;
}
.rule {
  display: flex;
  flex-wrap: wrap;
  overflow-y: scroll;
  height: max-content;
  max-height: 600px;
  flex: 1;
}
.tree::-webkit-scrollbar {
  width: 2px;
  background-color: #f5f5f5;
}
/*定义滚动条高宽及背景 高宽分别对应横竖滚动条的尺寸*/
.rule::-webkit-scrollbar {
  width: 10px;
  height: 10px;
  background-color: #f5f5f5;
}

/*定义滚动条轨道 内阴影+圆角*/
.rule::-webkit-scrollbar-track {
  border-radius: 4px;
  background-color: #f5f5f5;
}

/*定义滑块 内阴影+圆角*/
.rule::-webkit-scrollbar-thumb {
  border-radius: 4px;
  background-color: #ccc;
}

.rule-list {
  background-color: #f2f2f2;
  width: 48.5%;
  height: max-content;
  margin: 5px;
  border-radius: 3px;
  padding: 10px;
  color: #333;
  cursor: pointer;
  transition: all 0.1s;
}

.rule-list:hover {
  background-color: #badbfb;
}

.rule-list div {
  white-space: nowrap;
}

.select-rule {
  background-color: #badbfb;
}
.route-list {
  display: flex;
  margin-top: 10px;

  .tree {
    width: 200px;
    overflow-y: scroll;
    max-height: 600px;
    ::v-deep .el-tree-node__children .el-tree-node .el-tree-node__content {
      padding-left: 14px !important;
    }
  }
}
</style>
