<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row class="mb20">
        <el-col :span="24">
          <el-button v-auth="['setting-store_service-add']" type="primary" v-db-click @click="add" class="mr10"
            >添加客服</el-button
          >
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
        <el-table-column label="客服头像" min-width="90">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="客服名称" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.wx_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="客服状态" min-width="130">
          <template slot-scope="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除客服', scope.$index)">删除</a>
            <el-divider direction="vertical" v-if="scope.row.status" />
            <a v-db-click @click="goChat(scope.row)" v-if="scope.row.status">进入工作台</a>
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

    <!--聊天记录-->
    <el-dialog :visible.sync="modals3" title="聊天记录" width="720px">
      <div v-if="isChat" class="modelBox">
        <el-table
          v-loading="loading3"
          highlight-current-row
          no-userFrom-text="暂无数据"
          no-filtered-userFrom-text="暂无筛选结果"
          :data="tableList3"
        >
          <el-table-column label="用户名称" width="200">
            <template slot-scope="scope">
              <span>{{ scope.row.nickname }}</span>
            </template>
          </el-table-column>
          <el-table-column label="客服头像" min-width="90">
            <template slot-scope="scope">
              <div class="tabBox_img" v-viewer>
                <img v-lazy="scope.row.headimgurl" />
              </div>
            </template>
          </el-table-column>
          <el-table-column label="操作" fixed="right" width="170">
            <template slot-scope="scope">
              <a v-db-click @click="look(scope.row)">查看对话</a>
            </template>
          </el-table-column>
        </el-table>
        <div class="acea-row row-right page">
          <pagination
            v-if="total3"
            :total="total3"
            :page.sync="formValidate3.page"
            :limit.sync="formValidate3.limit"
            @pagination="getListRecord"
          />
        </div>
      </div>
      <div v-if="!isChat">
        <el-button type="primary" v-db-click @click="isChat = true">返回聊天记录</el-button>
        <el-table
          v-loading="loading5"
          highlight-current-row
          no-userFrom-text="暂无数据"
          class="mt14"
          no-filtered-userFrom-text="暂无筛选结果"
          :data="tableList5"
        >
          <el-table-column label="用户名称" min-width="200">
            <template slot-scope="scope">
              <span>{{ scope.row.nickname }}</span>
            </template>
          </el-table-column>
          <el-table-column label="用户头像" min-width="90">
            <template slot-scope="scope">
              <div class="tabBox_img" v-viewer>
                <img v-lazy="scope.row.avatar" />
              </div>
            </template>
          </el-table-column>
          <el-table-column label="发送消息" min-width="200">
            <template slot-scope="scope">
              <span>{{ scope.row.msn }}</span>
            </template>
          </el-table-column>
          <el-table-column label="发送时间" min-width="200">
            <template slot-scope="scope">
              <span>{{ scope.row.add_time }}</span>
            </template>
          </el-table-column>
        </el-table>
        <div class="acea-row row-right page">
          <pagination
            v-if="total5"
            :total="total5"
            :page.sync="formValidate5.page"
            :limit.sync="formValidate5.limit"
            @pagination="getChatlist"
          />
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { setCookies } from '@/libs/util';
import {
  kefuListApi,
  kefucreateApi,
  kefuaddApi,
  kefuAddApi,
  kefusetStatusApi,
  kefuEditApi,
  kefuRecordApi,
  kefuChatlistApi,
  kefuLogin,
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
    ...mapState('userLevel', ['categoryId']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  data() {
    return {
      isChat: true,
      formValidate3: {
        page: 1,
        limit: 15,
      },
      total3: 0,
      loading3: false,
      modals3: false,
      tableList3: [],
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
      eidtLoading: false,
    };
  },
  created() {
    this.getList();
  },
  methods: {
    // 进入工作台
    goChat(item) {
      kefuLogin(item.id)
        .then((res) => {
          var url = '';
          if (res.data.token) {
            let expires = this.getExpiresTime(res.data.exp_time);
            setCookies('kefu_token', res.data.token, expires);
            setCookies('kefu_uuid', res.data.kefuInfo.uid, expires);
            setCookies('kefu_expires_time', res.data.exp_time, expires);
            setCookies('kefuInfo', res.data.kefuInfo, expires);
            if (this.$store.state.media.isMobile) {
              url = window.location.protocol + '//' + window.location.host + '/kefu/mobile_list';
            } else {
              url = window.location.protocol + '//' + window.location.host + '/kefu/pc_list';
            }

            window.open(url, '_blank');
          }
        })
        .catch((error) => {
          this.$message.error(error.msg);
        });
    },
    getExpiresTime(expiresTime) {
      let nowTimeNum = Math.round(new Date() / 1000);
      let expiresTimeNum = expiresTime - nowTimeNum;
      return parseFloat(parseFloat(parseFloat(expiresTimeNum / 60) / 60) / 24);
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
    // 编辑
    edit(row) {
      if (this.eidtLoading) return;
      this.eidtLoading = true;
      this.$modalForm(kefuEditApi(row.id))
        .then(() => {
          this.getList();
          this.eidtLoading = false;
        })
        .catch(() => {
          this.eidtLoading = false;
        });
    },
    // 添加
    add() {
      this.$modalForm(kefuaddApi()).then(() => this.getList());
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
      this.loading2 = true(this.formValidate)
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
          tkefucreateApihis.loading2 = false;
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
        url: `app/wechat/kefu/${row.id}`,
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
      kefuListApi(this.tableFrom)
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
          this.getList();
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
</style>
