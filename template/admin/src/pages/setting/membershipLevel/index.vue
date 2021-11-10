<template>
  <div>
    <div class="i-layout-page-header mb20">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <Card :bordered="false" dis-hover>
      <div class="headers">
        <div class="search">
          <div>
            <span>是否显示：</span>
            <Select v-model="formValidate.status" style="width: 200px">
              <Option value="">全部</Option>
              <Option :value="1">显示</Option>
              <Option :value="0">不显示</Option>
            </Select>
          </div>
          <div>
            <span>等级名称：</span>
            <Input
              v-model="formValidate.keyword"
              placeholder="请输入等级名称"
              style="width: 200px"
            />
          </div>
          <Button type="primary" @click="search">搜索</Button>
          <Button type="success" icon="md-add" @click="groupAdd()" class="ml20"
            >添加数据</Button
          >
        </div>
      </div>
      <Row type="flex">
        <Col v-bind="grid"> </Col>
      </Row>
      <Table
        :columns="columns1"
        :data="tabList"
        ref="table"
        class="mt25"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row }" slot="image">
          <viewer>
            <div class="tabBox-img">
              <img v-lazy="row.image" />
            </div>
          </viewer>
        </template>
        <template slot-scope="{ row }" slot="status">
          <i-switch
            v-model="row.status"
            :value="row.status"
            :true-value="1"
            :false-value="0"
            @on-change="onchangeIsShow(row)"
            size="large"
          >
            <span slot="open">显示</span>
            <span slot="close">隐藏</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="addTask(row)">等级任务</a>
          <Divider type="vertical" />
          <a @click="edit(row, '编辑')">编辑</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除这条信息', index)">删除</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="formValidate.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="formValidate.limit"
        />
      </div>
    </Card>
    <div class="task-modal">
      <Modal v-model="modal2" title="添加任务" footer-hide width="1000">
        <div class="header">
          <h4>搜索条件</h4>
          <div class="search">
            <div>
              <span>是否显示：</span>
              <Select v-model="taskData.status" style="width: 200px">
                <Option value="">全部</Option>
                <Option :value="1">显示</Option>
                <Option :value="0">不显示</Option>
              </Select>
            </div>
            <div>
              <span>任务名称：</span>
              <Input
                v-model="taskData.keyword"
                placeholder="请输入任务名称"
                style="width: 200px"
              />
            </div>
            <Button type="primary" @click="searchTask">搜索</Button>
          </div>
        </div>
        <div>
          <div class="add-task">
            <Button type="primary" @click="taskAdd()">添加等级任务</Button>
          </div>
          <div>
            <Table
              :columns="columns2"
              :data="taskTabList"
              ref="table"
              class="mt25"
              :loading="taskLoading"
              highlight-row
              no-userFrom-text="暂无数据"
              no-filtered-userFrom-text="暂无筛选结果"
            >
              <template slot-scope="{ row }" slot="status">
                <i-switch
                  v-model="row.status"
                  :value="row.status"
                  :true-value="1"
                  :false-value="0"
                  @on-change="onchangeTaskIsShow(row)"
                  size="large"
                >
                  <span slot="open">开启</span>
                  <span slot="close">关闭</span>
                </i-switch>
              </template>
              <template slot-scope="{ row, index }" slot="action">
                <a @click="editTask(row, '编辑')">编辑</a>
                <Divider type="vertical" />
                <a @click="delTask(row, '删除这条信息', index)">删除</a>
              </template>
            </Table>
            <div class="acea-row row-right page">
              <Page
                :total="taskTotal"
                :current="taskData.page"
                show-elevator
                show-total
                @on-change="pageTaskChange"
                :page-size="taskData.limit"
              />
            </div>
          </div>
        </div>
      </Modal>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex";
import {
  membershipDataAddApi,
  membershipDataListApi,
  membershipDataEditApi,
  membershipSetApi,
  levelTaskSetApi,
  levelTaskListDataAddApi,
  levelTaskDataEditApi,
  levelTaskDataAddApi,
} from "@/api/membershipLevel";
export default {
  name: "list",
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      modal1: false,
      modal2: false,
      formValidate: {
        status: "",
        page: 1,
        limit: 20,
        gid: 0,
      },
      taskData: {
        keyword: "",
        page: 1,
        limit: 20,
        status: "",
      },
      total: 0,
      taskTotal: 0,
      tabList: [],
      taskTabList: [],
      columns1: [
        {
          key: "id",
          minWidth: 35,
          title: "ID",
        },
        {
          slot: "image",
          minWidth: 35,
          title: "图标",
        },
        {
          key: "name",
          minWidth: 35,
          title: "名称",
        },
        {
          key: "grade",
          minWidth: 35,
          title: "等级",
        },
        {
          key: "one_brokerage",
          minWidth: 35,
          title: "一级返佣上浮比例(%)",
        },
        {
          key: "two_brokerage",
          minWidth: 35,
          title: "二级返佣上浮比例(%)",
        },
        {
          slot: "status",
          minWidth: 35,
          title: "是否显示",
        },
        {
          fixed: "right",
          minWidth: 120,
          slot: "action",
          title: "操作",
        },
      ],
      columns2: [
        {
          key: "id",
          minWidth: 35,
          title: "ID",
        },
        {
          key: "name",
          minWidth: 35,
          title: "名称",
        },
        {
          key: "type_name",
          minWidth: 35,
          title: "任务类型",
        },
        {
          key: "number",
          minWidth: 35,
          title: "限定数量",
        },
        {
          slot: "status",
          minWidth: 35,
          title: "是否显示",
        },
        {
          key: "sort",
          minWidth: 35,
          title: "排序",
        },
        {
          fixed: "right",
          minWidth: 120,
          slot: "action",
          title: "操作",
        },
      ],
      FromData: null,
      loading: false,
      taskLoading: false,
      titleType: "group",
      groupAll: [],
      theme3: "light",
      labelSort: [],
      sortName: null,
      current: 0,
      model1: "",
      value1: "",
    };
  },
  computed: {
    ...mapState("admin/layout", ["isMobile"]),
    labelWidth() {
      return this.isMobile ? undefined : 75;
    },
    labelPosition() {
      return this.isMobile ? "top" : "right";
    },
  },
  watch: {
    $route(to, from) {
      if (this.$route.params.id) {
      } else {
      }
    },
  },
  mounted() {
    this.getList();
  },
  methods: {
    bindMenuItem(name, index) {
      this.current = index;
      this.formValidate.gid = name.id;
      this.getListHeader();
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      membershipDataListApi(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    // 列表
    getTaskList() {
      this.taskLoading = true;
      levelTaskListDataAddApi(this.taskData)
        .then(async (res) => {
          let data = res.data;
          this.taskTabList = data.list;
          this.taskTotal = data.count;
          this.taskLoading = false;
        })
        .catch((res) => {
          this.taskLoading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.formValidate.page = index;
      this.getList();
    },
    pageTaskChange(index) {
      this.taskData.page = index;
      this.getList();
    },
    // 表格搜索
    search() {
      this.formValidate.page = 1;
      this.getList();
    },
    searchTask() {
      this.taskData.page = 1;
      this.getTaskList();
    },
    // 添加表单
    groupAdd() {
      this.$modalForm(
        membershipDataAddApi({}, "/agent/level/create")
      ).then(() => this.getList());
    },
    taskAdd() {
      this.$modalForm(
        levelTaskDataAddApi(
          {},
          "/agent/level_task/create?level_id=" + this.taskData.id
        )
      ).then(() => this.getTaskList());
    },
    // 修改是否显示
    onchangeIsShow(row) {
      membershipSetApi(`agent/level/set_status/${row.id}/${row.status}`)
        .then(async (res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 修改是否显示
    onchangeTaskIsShow(row) {
      levelTaskSetApi(`agent/level_task/set_status/${row.id}/${row.status}`)
        .then(async (res) => {
          this.$Message.success(res.msg);
          this.getTaskList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    //添加等级任务
    addTask(row) {
      this.id = row.id;
      this.modal2 = true;
      this.taskData.id = row.id;
      this.getTaskList();
    },
    // 编辑
    edit(row) {
      let data = {
        gid: row.gid,
      };
      this.$modalForm(
        membershipDataEditApi(data, `agent/level/${row.id}/edit`)
      ).then(() => this.getList());
    },
    // 编辑
    editTask(row) {
      let data = {
        gid: row.gid,
      };
      this.$modalForm(
        levelTaskDataEditApi(data, `agent/level_task/${row.id}/edit`)
      ).then(() => this.getTaskList());
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `agent/level/${row.id}`,
        method: "DELETE",
        ids: "",
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.tabList.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 删除
    delTask(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `agent/level_task/${row.id}`,
        method: "DELETE",
        ids: "",
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.taskTabList.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="stylus">
/deep/ .ivu-menu-vertical .ivu-menu-item-group-title {
  display: none;
}

/deep/ .ivu-menu-vertical.ivu-menu-light:after {
  display: none;
}

.left-wrapper {
  height: 904px;
  background: #fff;
  border-right: 1px solid #dcdee2;
}

.menu-item {
  // z-index 50
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

.tabBox-img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}

.ivu-menu {
  z-index: auto;
}

.header, .headers {
  display: flex;
  flex-direction: column;
  background-color: #f2f2f2;
  padding: 8px;

  .search {
    display: flex;
    align-items: center;

    >div {
      margin-right: 10px;
    }
  }
}

.search /deep/ .ivu-select-selection {
  border: 1px solid #dcdee2 !important;
}

.headers {
  background-color: #fff;
}

/deep/ .ivu-modal-mask {
  z-index: 100 !important;
}

/deep/ .ivu-modal-wrap {
  z-index: 100 !important;
}

.add-task {
  margin: 10px 0;
}
</style>
