<template>
  <div>
    <el-row class="ivu-mt box-wrapper" ref="warpper">
      <el-col :span="4" class="left-wrapper">
        <div class="tree_tit" v-db-click @click="addSort">
          <i class="el-icon-circle-plus"></i>
          添加分组
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
                <el-tooltip class="item" effect="dark" :content="data.cate_name" placement="top">
                  <div class="text line1">
                    {{ data.cate_name }}
                  </div>
                </el-tooltip>
              </div>
              <span v-if="data.id">
                <el-dropdown @command="(command) => clickMenu(data, command)">
                  <i class="el-icon-more el-icon--right"></i>
                  <template slot="dropdown">
                    <el-dropdown-menu>
                      <el-dropdown-item command="1">编辑分组</el-dropdown-item>
                      <el-dropdown-item v-if="data.id" command="2">删除分组</el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
              </span>
            </span>
          </el-tree>
        </div>
      </el-col>
      <el-col :span="20" ref="rightBox">
        <el-card :bordered="false" shadow="never" class="left-radius-none">
          <el-row class="mb14">
            <el-col :span="18">
              <el-button v-auth="['marketing-channel_code-create']" type="primary" v-db-click @click="add"
                >新建渠道码</el-button
              >
              <!-- <el-button v-auth="['marketing-channel_code-create']" type="success" v-db-click @click="addSort">添加分组</el-button> -->
            </el-col>
            <el-col :span="6">
              <div class="flex">
                <el-input class="mr10" v-model="tableFrom.name" search placeholder="请输入渠道码名称"> </el-input>
                <el-button type="primary" v-db-click @click="userSearchs">搜索</el-button>
              </div>
            </el-col>
          </el-row>
          <el-table
            :data="tableList"
            v-loading="loading"
            highlight-current-row
            no-userFrom-text="暂无数据"
            no-filtered-userFrom-text="暂无筛选结果"
          >
            <el-table-column label="渠道码" width="80">
              <template slot-scope="scope">
                <div class="tabBox_img" v-viewer>
                  <img v-lazy="scope.row.image" />
                </div>
              </template>
            </el-table-column>
            <el-table-column label="渠道码名称" min-width="80">
              <template slot-scope="scope">
                <span>{{ scope.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="总关注数" min-width="80">
              <template slot-scope="scope">
                <span>{{ scope.row.follow }}</span>
              </template>
            </el-table-column>
            <el-table-column label="昨日新增关注" min-width="80">
              <template slot-scope="scope">
                <span>{{ scope.row.y_follow }}</span>
              </template>
            </el-table-column>
            <el-table-column label="用户标签" min-width="80">
              <template slot-scope="scope">
                <el-tag class="label-name" v-for="(item, index) in scope.row.label_name" :key="index">{{
                  item
                }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="时间" min-width="80">
              <template slot-scope="scope">
                <span v-if="scope.row.stop === 0"> 永久 </span>
                <span v-if="scope.row.stop === 1">
                  <div>{{ scope.row.add_time }}</div>
                  <div>-</div>
                  <div>{{ scope.row.end_time }}</div>
                </span>
                <span v-if="scope.row.stop === -1">已过期</span>
              </template>
            </el-table-column>
            <el-table-column label="关联推广员" min-width="80">
              <template slot-scope="scope">
                <div class="tabBox_img" v-viewer>
                  <img v-lazy="scope.row.avatar" />
                </div>
              </template>
            </el-table-column>
            <el-table-column label="状态" min-width="80">
              <template slot-scope="scope">
                <el-switch
                  class="defineSwitch"
                  :active-value="1"
                  :inactive-value="0"
                  v-model="scope.row.status"
                  :value="scope.row.status"
                  :disabled="scope.row.lottery_status == 2 ? true : false"
                  @change="onchangeIsShow(scope.row)"
                  size="large"
                  active-text="开启"
                  inactive-text="关闭"
                >
                </el-switch>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="170">
              <template slot-scope="scope">
                <a v-db-click @click="edit(scope.row)">编辑</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="del(scope.row, '删除二维码', scope.$index)">删除</a>
                <el-divider direction="vertical"></el-divider>
                <el-dropdown size="small" @command="changeMenu(scope.row, $event)" :transfer="true">
                  <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
                  <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item command="1">下载</el-dropdown-item>
                    <el-dropdown-item command="2">统计</el-dropdown-item>
                    <el-dropdown-item command="3">用户列表</el-dropdown-item>
                  </el-dropdown-menu>
                </el-dropdown>
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
    <el-dialog :visible.sync="modals" title="渠道码用户列表" :close-on-click-modal="false" width="900px">
      <el-table ref="selection" :data="tabList" empty-text="暂无数据" highlight-current-row max-height="400">
        <el-table-column label="UID" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户头像" min-width="120">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="用户昵称" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total2"
          :total="total2"
          :page.sync="userData.page"
          :limit.sync="userData.limit"
          @pagination="getUserList"
        />
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import {
  wechatQrcodeList,
  wechatQrcodeCreate,
  wechatQrcodeTree,
  wechatQrcodeStatusApi,
  getUserList,
} from '@/api/setting';
import { scrollTop } from '@/libs/util';

export default {
  name: 'marketing_channel_code',
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
      isChat: true,
      formValidate3: {
        page: 1,
        limit: 15,
      },
      total3: 0,
      loading3: false,
      modals3: false,
      tabList: [],
      formValidate5: {
        page: 1,
        limit: 15,
        uid: 0,
        to_uid: 0,
        id: 0,
      },
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
        name: '',
      },
      userData: {
        id: 0,
        page: 1,
        limit: 15,
      },
      timeVal: [],
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
      uid: 0,
    };
  },
  activated() {
    this.getUserLabelAll();
  },
  mounted() {},
  methods: {
    changeMenu(row, name) {
      this.orderId = row.id;
      switch (name) {
        case '1':
          this.downLoadCode(row.image);
          break;
        case '2':
          this.$router.push({
            path: this.$routeProStr + '/marketing/channel_code/code_statistic?id=' + row.id,
          });
          break;
        case '3':
          this.modals = true;
          this.userData.id = row.id;
          this.getUserList();
          this.break;
        default:
      }
    },
    downLoadCode(url) {
      if (!url) return this.$message.warning('暂无二维码');
      var image = new Image();
      image.src = url;
      // 解决跨域 Canvas 污染问题
      image.setAttribute('crossOrigin', 'anonymous');
      image.onload = function () {
        var canvas = document.createElement('canvas');
        canvas.width = image.width;
        canvas.height = image.height;
        var context = canvas.getContext('2d');
        context.drawImage(image, 0, 0, image.width, image.height);

        var url = canvas.toDataURL(); //得到图片的base64编码数据
        var a = document.createElement('a'); // 生成一个a元素
        var event = new MouseEvent('click'); // 创建一个单击事件
        a.download = name || 'photo'; // 设置图片名称
        a.href = url; // 将生成的URL设置为a.href属性
        a.dispatchEvent(event); // 触发a的单击事件
      };
    },
    // 获取渠道码用户列表
    getUserList() {
      getUserList(this.userData)
        .then(async (res) => {
          let data = res.data;
          let arr = [];
          data.list.map((i) => {
            arr.push(i.user);
          });
          this.tabList = arr;
          this.total2 = data.count;
          this.loading2 = false;
        })
        .catch((res) => {
          this.loading = false;
          this.tabList = [];
          this.$message.error(res.msg);
        });
    },

    // 获取分组
    getUserLabelAll(key) {
      wechatQrcodeTree().then((res) => {
        let data = res.data.data;
        let obj = {
          cate_name: '全部',
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
      this.$modalForm(wechatQrcodeCreate(0)).then(() => this.getUserLabelAll());
    },
    //编辑标签
    labelEdit(item) {
      this.$modalForm(wechatQrcodeCreate(item.id)).then(() => this.getUserLabelAll(1));
    },
    deleteSort(row, tit) {
      let num = this.labelSort.findIndex((e) => {
        return e.id == row.id;
      });
      let delfromData = {
        title: tit,
        num: num,
        url: `app/wechat_qrcode/cate/del/${row.id}`,
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
        limit: 10,
        data: '',
        type: '',
        nickname: '',
      };
    },
    // 编辑
    edit(row) {
      this.$router.push({
        path: this.$routeProStr + '/marketing/channel_code/create?id=' + row.id,
      });
    },
    // 添加
    add() {
      this.$router.push({
        path: this.$routeProStr + '/marketing/channel_code/create',
      });
    },
    //
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
    // 搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `/app/wechat_qrcode/del/${row.id}`,
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
      wechatQrcodeList(this.tableFrom)
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
      wechatQrcodeStatusApi(data)
        .then(async (res) => {
          this.$message.success(res.msg);
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
.label-name {
  margin: 2px 2px;
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
