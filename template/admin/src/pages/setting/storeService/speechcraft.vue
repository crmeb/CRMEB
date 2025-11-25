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
              <div class="file-name">
                <img v-if="!data.pid" class="icon" src="@/assets/images/file.jpg" />
                <el-tooltip class="item" effect="dark" :content="data.name" placement="top">
                  <div class="text line1">
                    {{ data.name }}
                  </div>
                </el-tooltip>
              </div>
              <span v-show="data.id !== '' && data.id !== 0">
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
          <el-row class="mb14">
            <el-col :span="24">
              <el-button v-auth="['setting-store_service-add']" type="primary" v-db-click @click="add"
                >添加话术</el-button
              >
              <!-- <el-button v-auth="['setting-store_service-add']" type="success" v-db-click @click="addSort">添加分类</el-button> -->
            </el-col>
          </el-row>
          <el-table
            :data="tableList"
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
            <el-table-column label="分类" min-width="120">
              <template slot-scope="scope">
                <span>{{ scope.row.cate_name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="标题" min-width="120">
              <template slot-scope="scope">
                <el-tooltip placement="top" :open-delay="600">
                  <div slot="content">{{ scope.row.title }}</div>
                  <span class="line2">{{ scope.row.title }}</span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="详情" min-width="120">
              <template slot-scope="scope">
                <el-tooltip placement="top" :open-delay="600">
                  <div slot="content">{{ scope.row.message }}</div>
                  <span class="line2">{{ scope.row.message }}</span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="排序" min-width="120">
              <template slot-scope="scope">
                <span>{{ scope.row.sort }}</span>
              </template>
            </el-table-column>
            <el-table-column label="添加时间" min-width="150">
              <template slot-scope="scope">
                <span>{{ scope.row.add_time }}</span>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="170">
              <template slot-scope="scope">
                <a v-db-click @click="edit(scope.row)">编辑</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="del(scope.row, '删除客服', scope.$index)">删除</a>
              </template>
            </el-table-column>
          </el-table>
          <div class="acea-row row-right page">
            <pagination
              v-if="total"
              :total="total"
              :page.sync="tableFrom.page"
              :limit.sync="tableFrom.limit"
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
import {
  wechatSpeechcraft,
  speechcraftCreate,
  speechcraftEdit,
  speechcraftcate,
  speechcraftcateCreate,
  speechcraftcateEdit,
} from '@/api/setting';
export default {
  name: 'index',
  filters: {
    typeFilter(status) {
      const statusMap = {
        wechat: '微信用户',
        routine: '小程序用户',
      };
      return statusMap[status];
    },
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
      isChat: true,
      formValidate3: {
        page: 1,
        limit: 15,
      },
      total3: 0,
      loading3: false,
      modals3: false,
      tableList3: [],
      columns3: [
        {
          title: '用户名称',
          key: 'nickname',
          width: 200,
        },
        {
          title: '客服头像',
          slot: 'headimgurl',
        },
        {
          title: '操作',
          slot: 'action',
        },
      ],
      formValidate5: {
        page: 1,
        limit: 15,
        uid: 0,
        to_uid: 0,
        id: 0,
      },
      total5: 0,
      loading5: false,
      tableList5: [],
      FromData: null,
      formValidate: {
        page: 1,
        limit: 15,
        data: '',
        type: '',
        nickname: '',
      },
      tableList2: [],
      modals: false,
      total: 0,
      tableFrom: {
        page: 1,
        limit: 15,
        cate_id: 0,
      },
      timeVal: [],
      fromList: {
        title: '选择时间',
        custom: true,
        fromTxt: [
          { text: '全部', val: '' },
          { text: '今天', val: 'today' },
          { text: '昨天', val: 'yesterday' },
          { text: '最近7天', val: 'lately7' },
          { text: '最近30天', val: 'lately30' },
          { text: '本月', val: 'month' },
          { text: '本年', val: 'year' },
        ],
      },
      loading: false,
      tableList: [],

      loading2: false,
      total2: 0,
      addFrom: {
        uids: [],
      },
      selections: [],
      rows: {},
      rowRecord: {},
      theme3: 'light',
      labelSort: [],
      sortName: '',
      current: 0,
    };
  },
  created() {
    this.getUserLabelAll();
  },
  methods: {
    getUserLabelAll(key) {
      speechcraftcate().then((res) => {
        let data = res.data.data;
        let obj = {
          name: '全部',
          id: '',
        };
        data.unshift(obj);
        data.forEach((el) => {
          el.status = false;
        });
        if (!key) {
          this.sortName = data[0].id;
          this.tableFrom.cate_id = data[0].id;
          this.getList();
        }
        this.labelSort = data;
      });
    },
    // 添加分类
    addSort() {
      this.$modalForm(speechcraftcateCreate()).then(() => this.getUserLabelAll());
    },
    //编辑标签
    labelEdit(item) {
      this.$modalForm(speechcraftcateEdit(item.id)).then(() => this.getUserLabelAll(1));
    },
    deleteSort(row, tit) {
      let num = this.labelSort.findIndex((e) => {
        return e.id == row.id;
      });
      let delfromData = {
        title: tit,
        num: num,
        url: `app/wechat/speechcraftcate/${row.id}`,
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
    // 点击菜单
    clickMenu(data, name) {
      if (name == 1) {
        this.labelEdit(data);
      } else if (name == 2) {
        this.deleteSort(data, '删除分类');
      }
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
    bindMenuItem(name, index) {
      this.tableFrom.page = 1;
      this.current = index;
      this.labelSort.forEach((el) => {
        el.status = false;
      });
      this.tableFrom.cate_id = name.id;
      this.getList();
    },
    cancel() {
      this.formValidate = {
        page: 1,
        limit: 15,
        data: '',
        type: '',
        nickname: '',
      };
    },
    handleReachBottom() {
      return new Promise((resolve) => {
        this.formValidate.page = this.formValidate.page + 1;
        setTimeout(() => {
          // this.loading2 = true;
          kefucreateApi(this.formValidate)
            .then(async (res) => {
              let data = res.data;
              // this.tableList2 = data.list;
              if (data.list.length > 0) {
                for (let i = 0; i < data.list.length; i++) {
                  this.tableList2.push(data.list[i]);
                }
              }
              this.total2 = data.count;
              this.loading2 = false;
            })
            .catch((res) => {
              this.loading2 = false;
              this.$message.error(res.msg);
            });
          resolve();
        }, 2000);
      });
    },
    // 查看对话
    look(row) {
      this.isChat = false;
      this.rowRecord = row;
      this.getChatlist();
    },
    // 查看对话列表
    getChatlist() {
      this.loading5 = true;
      this.formValidate5.uid = this.rows.uid;
      this.formValidate5.to_uid = this.rowRecord.uid;
      this.formValidate5.id = this.rows.id;
      kefuChatlistApi(this.formValidate5)
        .then(async (res) => {
          let data = res.data;
          this.tableList5 = data.list;
          this.total5 = data.count;
          this.loading5 = false;
        })
        .catch((res) => {
          this.loading5 = false;
          this.$message.error(res.msg);
        });
    },
    pageChange5(index) {
      this.formValidate5.page = index;
      this.getChatlist();
    },
    // 修改成功
    submitFail() {
      this.getList();
    },
    // 聊天记录
    record(row) {
      this.rows = row;
      this.modals3 = true;
      this.isChat = true;
      this.getListRecord();
    },
    // 聊天记录列表
    getListRecord() {
      this.loading3 = true;
      kefuRecordApi(this.formValidate3, this.rows.id)
        .then(async (res) => {
          let data = res.data;
          this.tableList3 = data.list ? data.list : [];
          this.total3 = data.count;
          this.loading3 = false;
        })
        .catch((res) => {
          this.loading3 = false;
          this.$message.error(res.msg);
        });
    },
    pageChange3(index) {
      this.formValidate3.page = index;
      this.getListRecord();
    },
    // 编辑
    edit(row) {
      this.$modalForm(speechcraftEdit(row.id)).then(() => this.getList());
    },
    // 添加
    add() {
      this.$modalForm(speechcraftCreate()).then(() => this.getList());
    },
    // 全选
    onSelectTab(selection) {
      this.selections = selection;
      let data = [];
      this.selections.map((item) => {
        data.push(item.uid);
      });
      this.addFrom.uids = data;
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.data = this.timeVal ? this.timeVal.join('-') : '';
      this.formValidate.page = 1;
      this.getListService();
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.data = tab;
      this.timeVal = [];
      this.formValidate.page = 1;
      this.getListService();
    },
    // 客服列表
    getListService() {
      this.loading2 = true;
      kefucreateApi(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tableList2 = data.list;
          this.total2 = data.count;
          this.tableList2.map((item) => {
            item._isChecked = false;
          });
          this.loading2 = false;
        })
        .catch((res) => {
          this.loading2 = false;
          this.$message.error(res.msg);
        });
    },
    pageChange2(pageIndex) {
      this.formValidate.page = pageIndex;
      this.getListService();
      this.addFrom.uids = [];
    },
    // 搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getListService();
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `/app/wechat/speechcraft/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.tableList.splice(num, 1);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 列表
    getList() {
      this.loading = true;
      wechatSpeechcraft(this.tableFrom)
        .then(async (res) => {
          let data = res.data;
          this.tableList = data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        status: row.status,
      };
      kefusetStatusApi(data)
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 添加客服
    putRemark() {
      if (this.addFrom.uids.length === 0) {
        return this.$message.warning('请选择要添加的客服');
      }
      kefuAddApi(this.addFrom)
        .then(async (res) => {
          this.$message.success(res.msg);
          this.modals = false;
          this.getList();
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
.showOn {
  color: #2d8cf0;
  background: #f0faff;
  z-index: 2;
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
.modelBox {
  ::v-deep,
  .ivu-table-header {
    width: 100% !important;
  }
}
.trees-coadd {
  width: 100%;
  height: 385px;
  .scollhide {
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    overflow-y: scroll;
  }
}
.scollhide::-webkit-scrollbar {
  display: none;
}
::v-deep .ivu-menu-vertical .ivu-menu-item-group-title {
  display: none;
}
::v-deep .ivu-menu-vertical.ivu-menu-light:after {
  display: none;
}
.left-wrapper {
  height: 904px;
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
