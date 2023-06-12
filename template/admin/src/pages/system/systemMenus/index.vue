<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="roleData"
        :model="roleData"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col v-bind="grid">
            <FormItem label="规则状态：">
              <Select v-model="roleData.is_show" placeholder="请选择" clearable @on-change="getData">
                <Option value="1">显示</Option>
                <Option value="0">不显示</Option>
              </Select>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="按钮名称：" prop="status2" label-for="status2">
              <Input v-model="roleData.keyword" search enter-button placeholder="请输入按钮名称" @on-search="getData" />
            </FormItem>
          </Col>
        </Row>
        <Row type="flex">
          <Col v-bind="grid">
            <Button type="primary" @click="menusAdd('添加规则')" icon="md-add">添加规则 </Button>
          </Col>
        </Row>
      </Form>
      <vxe-table
        :border="false"
        class="vxeTable mt25"
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
        <vxe-table-column field="unique_auth" title="前端权限" min-width="200"></vxe-table-column>
        <vxe-table-column field="menu_path" title="路由" min-width="240" tooltip="true">
          <template v-slot="{ row }">
            <span v-if="row.auth_type == 1">菜单：{{ row.menu_path }}</span>
            <span v-if="row.auth_type == 3">按钮</span>
            <span v-if="row.auth_type == 2">接口：[{{ row.methods }}]{{ row.api_url }}</span>
          </template>
        </vxe-table-column>
        <vxe-table-column field="flag" title="规则状态" min-width="120">
          <template v-slot="{ row }">
            <i-switch
              v-model="row.is_show"
              :value="row.is_show"
              :true-value="1"
              :false-value="0"
              @on-change="onchangeIsShow(row)"
              size="large"
            >
              <span slot="open">开启</span>
              <span slot="close">关闭</span>
            </i-switch>
          </template>
        </vxe-table-column>
        <vxe-table-column field="mark" title="备注" min-width="120"></vxe-table-column>
        <vxe-table-column field="date" title="操作" align="right" width="250" fixed="right">
          <template v-slot="{ row }">
            <span>
              <a @click="addRoute(row)" v-if="row.auth_type === 1 || row.auth_type === 3">选择权限</a>
              <Divider type="vertical" v-if="row.auth_type === 1 || row.auth_type === 3"/>
              <a @click="addE(row, '添加子菜单')" v-if="row.auth_type === 1">添加下级</a>
              <!-- <a @click="addE(row, '添加规则')" v-else>添加规则</a> -->
            </span>
            <Divider type="vertical" v-if="row.auth_type === 1" />
            <a @click="edit(row, '编辑')">编辑</a>
            <Divider type="vertical" />
            <a @click="del(row, '删除规则')">删除</a>
          </template>
        </vxe-table-column>
      </vxe-table>
    </Card>
    <menus-from
      :formValidate="formValidate"
      :titleFrom="titleFrom"
      @getList="getList"
      @changeMenu="getMenusUnique"
      ref="menusFrom"
      @clearFrom="clearFrom"
    ></menus-from>
    <Modal
      v-model="ruleModal"
      scrollable
      width="1100"
      title="权限列表"
      @on-ok="addRouters"
      @on-cancel="ruleModal = false"
      @on-visible-change="modalchange"
    >
      <div class="search-rule">
        <Alert
          >1.接口可多选，可重复添加；<br>2.添加路由按照路由规则进行添加，即可在开发工具->接口管理里面点击同步；<br>3.同步完成即可在此选择对应的接口；</Alert
        >
        <Input
          class="mr10"
          v-model="searchRule"
          placeholder="输入关键词搜索"
          clearable
          style="width: 300px"
          ref="search"
          @on-enter="searchRules"
          @on-clear="searchRules"
        />
        <Button class="mr10" type="primary" @click="searchRules">搜索</Button>
        <Button @click="init">重置</Button>
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
            @click="selectRule(item)"
          >
            <div>接口名称：{{ item.name }}</div>
            <div>请求方式：{{ item.method }}</div>
            <div>接口地址：{{ item.path }}</div>
          </div>
        </div>
      </div>
      <!-- <Tabs v-model="routeType" @on-click="changTab">
        <TabPane :label="item.name" :name="'' + index" v-for="(item, index) in foundationList" :key="item"></TabPane>
      </Tabs> -->
    </Modal>
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
import formCreate from '@form-create/iview';
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
        is_show: '',
        keyword: '',
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
    };
  },
  components: { menusFrom, formCreate: formCreate.$form() },
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 75;
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
          this.$Message.error(res.msg);
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
        is_show: row.is_show,
      };
      isShowApi(data)
        .then(async (res) => {
          this.$Message.success(res.msg);
          this.$store.dispatch('menus/getMenusNavList');
        })
        .catch((res) => {
          this.$Message.error(res.msg);
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
            this.$Message.error(res.msg);
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
          this.$Message.success(res.msg);
          this.getData();
          this.getMenusUnique();
          // this.$store.dispatch('menus/getMenusNavList');
        })
        .catch((res) => {
          this.$Message.error(res.msg);
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
          this.$Message.error(res.msg);
        });
    },
    // 编辑
    edit(row, title, index) {
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
    //         this.$Message.error(res.msg);
    //     })
    // },
    // 列表
    getData() {
      this.loading = true;
      this.roleData.is_show = this.roleData.is_show || '';
      getTable(this.roleData)
        .then(async (res) => {
          this.tableData = res.data;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
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
    /deep/ .el-tree-node__children .el-tree-node .el-tree-node__content {
      padding-left: 14px !important;
    }
  }
}
</style>
