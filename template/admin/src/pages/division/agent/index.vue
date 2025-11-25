<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
          inline
        >
          <el-form-item label="搜索：">
            <el-input
              clearable
              placeholder="请输入姓名、UID"
              v-model="formValidate.keyword"
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
      <el-row class="ivu-mt box-wrapper">
        <el-col :xs="24" :sm="24" ref="rightBox">
          <el-row>
            <el-col v-bind="grid">
              <el-button type="primary" v-db-click @click="groupAdd('0')" class="mr20">添加代理商</el-button>
            </el-col>
          </el-row>
          <el-table
            :data="userLists"
            ref="table"
            class="mt14"
            v-loading="loading"
            highlight-current-row
            no-formValidate-text="暂无数据"
            no-filtered-formValidate-text="暂无筛选结果"
          >
            <el-table-column label="用户UID" width="100">
              <template slot-scope="scope">
                <span>{{ scope.row.uid }}</span>
              </template>
            </el-table-column>
            <el-table-column label="头像" min-width="90">
              <template slot-scope="scope">
                <div class="tabBox_img" v-viewer>
                  <img v-lazy="scope.row.avatar" />
                </div>
              </template>
            </el-table-column>
            <el-table-column label="名称" min-width="130">
              <template slot-scope="scope">
                <div class="acea-row">
                  <div v-text="scope.row.division_name" class="ml10"></div>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="分销比例" min-width="130">
              <template slot-scope="scope">
                <span> {{ scope.row.division_percent }}%</span>
              </template>
            </el-table-column>
            <el-table-column label="员工数量" min-width="130">
              <template slot-scope="scope">
                <span>{{ scope.row.agent_count }}</span>
              </template>
            </el-table-column>
            <el-table-column label="截止时间" min-width="130">
              <template slot-scope="scope">
                <span>{{ scope.row.division_end_time }}</span>
              </template>
            </el-table-column>
            <el-table-column label="状态" min-width="130">
              <template slot-scope="scope">
                <el-switch
                  :active-value="1"
                  :inactive-value="0"
                  v-model="scope.row.division_status"
                  :value="scope.row.division_status"
                  @change="onchangeIsShow(scope.row)"
                  size="large"
                >
                </el-switch>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="220">
              <template slot-scope="scope">
                <a v-db-click @click="staffAdd(scope.row.uid)">添加员工</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="jump(scope.row.uid)">查看员工</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="groupAdd(scope.row.uid)">编辑</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="del(scope.row, '删除代理商', scope.$index, 2)">删除</a>
              </template>
            </el-table-column>
          </el-table>
          <div class="acea-row row-right page">
            <pagination
              v-if="total"
              :total="total"
              :page.sync="formValidate.page"
              :limit.sync="formValidate.limit"
              @pagination="getList"
            />
          </div>
        </el-col>
      </el-row>
    </el-card>
    <el-dialog :visible.sync="staffModal" title="员工列表" class="order_box" width="1000px">
      <el-table
        :data="clerkLists"
        ref="table"
        class="mt20"
        v-loading="loading"
        highlight-current-row
        no-formValidate-text="暂无数据"
        no-filtered-formValidate-text="暂无筛选结果"
      >
        <el-table-column label="用户UID" width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="头像" min-width="120">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="姓名" min-width="130">
          <template slot-scope="scope">
            <div class="acea-row">
              <i class="el-icon-male mr10" v-show="scope.row.sex === '男'" style="color: #2db7f5; font-size: 15px"></i>
              <i
                class="el-icon-female mr10"
                v-show="scope.row.sex === '女'"
                style="color: #ed4014; font-size: 15px"
              ></i>
              <div v-text="scope.row.nickname" class=""></div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="分销比例" min-width="130">
          <template slot-scope="scope">
            <span> {{ scope.row.division_percent }}%</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="120">
          <template slot-scope="scope">
            <a v-db-click @click="del(scope.row, '删除员工', scope.$index, 3)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total2"
          :total="total2"
          :page.sync="clerkReqData.page"
          :limit.sync="clerkReqData.limit"
          @pagination="getClerkList"
        />
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { regionList, agentFrom, isShowApi, clerkList, staffAddFrom } from '@/api/agent';
import { formatDate } from '@/utils/validate';
export default {
  name: 'agent_extra',
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      total: 0,
      total2: 0,
      userLists: [],
      formInline: {
        uid: 0,
        proportion: 0,
        image: '',
      },
      FromData: null,
      loading: false,
      current: 0,
      formValidate: {
        page: 1,
        limit: 15,
        keyword: '',
      },
      staffModal: false,
      clerkReqData: {
        uid: 0,
        page: 1,
        limit: 15,
      },
      clerkLists: [],
    };
  },
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '50px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  mounted() {
    this.getList();
  },
  methods: {
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
    jump(uid) {
      this.clerkReqData.uid = uid;
      this.getClerkList();
    },
    getClerkList() {
      this.clerkReqData.division_type = 3;
      clerkList(this.clerkReqData).then((res) => {
        this.clerkLists = res.data.list;
        this.total2 = res.data.count;
        this.staffModal = true;
      });
    },
    // 列表
    getList() {
      this.loading = true;
      this.formValidate.division_type = 2;
      regionList(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.userLists = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 添加表单
    groupAdd(id) {
      this.$modalForm(agentFrom(id))
        .then((res) => {
          this.getList();
        })
        .catch((err) => {});
    },
    //添加员工表单
    staffAdd(id) {
      this.$modalForm(staffAddFrom(id))
        .then((res) => {
          this.getList();
        })
        .catch((err) => {});
    },
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.uid,
        status: row.division_status,
      };
      isShowApi(data)
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 编辑
    edit(row) {},
    // 删除
    del(row, tit, num, type) {
      let delfromData = {
        title: tit,
        method: 'DELETE',
        uid: row.uid,
        url: `agent/division/del/${type}/${row.uid}`,
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          if (type == 2) {
            this.userLists.splice(num, 1);
          } else {
            this.clerkLists.splice(num, 1);
          }
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.ivu-form-item {
  margin-bottom: 0;
}
.picBox {
  display: inline-block;
  cursor: pointer;
  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
  }
  .pictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 10px;

    img {
      width: 100%;
      height: 100%;
    }
  }
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
  &:hover .icon-box {
    display: block;
  }
}
.icon-box {
  z-index: 3;
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  display: none;
}
.right-menu {
  z-index: 10;
  position: absolute;
  right: -106px;
  top: -11px;
  width: auto;
  min-width: 121px;
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
</style>
