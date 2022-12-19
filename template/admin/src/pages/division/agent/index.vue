<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="formValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24" align="middle">
          <Col v-bind="grid">
            <FormItem label="搜索：" label-for="status">
              <Input
                style="width: 300px"
                search
                enter-button
                placeholder="请输入姓名、UID"
                v-model="formValidate.keyword"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
        </Row>
      </Form>
    </Card>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Row class="ivu-mt box-wrapper">
        <Col :xs="24" :sm="24" ref="rightBox">
          <Table
            :columns="columns"
            :data="userLists"
            ref="table"
            class="mt25"
            :loading="loading"
            highlight-row
            no-formValidate-text="暂无数据"
            no-filtered-formValidate-text="暂无筛选结果"
          >
            <template slot-scope="{ row, index }" slot="avatars">
              <div class="tabBox_img" v-viewer>
                <img v-lazy="row.avatar" />
              </div>
            </template>
            <template slot-scope="{ row, index }" slot="nickname">
              <div class="acea-row">
                <Icon type="md-male" v-show="row.sex === '男'" color="#2db7f5" size="15" class="mr5" />
                <Icon type="md-female" v-show="row.sex === '女'" color="#ed4014" size="15" class="mr5" />
                <div v-text="row.nickname"></div>
              </div>
              <!--                    <div v-show="row.vip_name" class="vipName">{{row.vip_name}}</div>-->
            </template>
            <template slot-scope="{ row, index }" slot="status">
              <i-switch
                v-model="row.division_status"
                :value="row.division_status"
                :true-value="1"
                :false-value="0"
                @on-change="onchangeIsShow(row)"
                size="large"
              >
                <span slot="open">显示</span>
                <span slot="close">隐藏</span>
              </i-switch>
            </template>
            <template slot-scope="{ row, index }" slot="division_end_time">
              <span> {{ row.division_end_time }}</span>
            </template>
            <template slot-scope="{ row, index }" slot="division_percent">
              <span> {{ row.division_percent }}%</span>
            </template>
            <template slot-scope="{ row, index }" slot="action">
              <a @click="jump(row.uid)">查看员工</a>
              <Divider type="vertical" />
              <a @click="groupAdd(row.uid)">编辑</a>
              <Divider type="vertical" />
              <a @click="del(row, '删除代理商', index)">删除</a>
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
        </Col>
      </Row>
    </Card>
    <Modal v-model="staffModal" scrollable title="员工列表" class="order_box" width="800" footer-hide>
      <Table
        :columns="columns2"
        :data="clerkLists"
        ref="table"
        class="mt25"
        :loading="loading"
        highlight-row
        no-formValidate-text="暂无数据"
        no-filtered-formValidate-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="avatars">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="row.avatar" />
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="nickname">
          <div class="acea-row">
            <Icon type="md-male" v-show="row.sex === '男'" color="#2db7f5" size="15" class="mr5" />
            <Icon type="md-female" v-show="row.sex === '女'" color="#ed4014" size="15" class="mr5" />
            <div v-text="row.nickname"></div>
          </div>
          <!--                    <div v-show="row.vip_name" class="vipName">{{row.vip_name}}</div>-->
        </template>
        <template slot-scope="{ row, index }" slot="agent_end_time">
          <span> {{ row.agent_end_time | formatDate }}</span>
        </template>
        <template slot-scope="{ row, index }" slot="division_percent">
          <span> {{ row.division_percent }}%</span>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total2"
          :current="clerkReqData.page"
          show-elevator
          show-total
          @on-change="clerkPageChange"
          :page-size="clerkReqData.limit"
        />
      </div>
    </Modal>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { regionList, agentFrom, isShowApi, clerkList } from '@/api/agent';
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
      columns2: [
        {
          title: '用户UID',
          key: 'uid',
          width: 80,
        },
        {
          title: '头像',
          slot: 'avatars',
          minWidth: 60,
        },
        {
          title: '姓名',
          slot: 'nickname',
          minWidth: 150,
        },
        {
          title: '分销比例',
          slot: 'division_percent',
          minWidth: 100,
        },
        {
          title: '订单数量',
          key: 'order_count',
          minWidth: 100,
        },
      ],
      columns: [
        {
          title: '用户UID',
          key: 'uid',
          width: 80,
        },
        {
          title: '头像',
          slot: 'avatars',
          minWidth: 60,
        },
        {
          title: '姓名',
          slot: 'nickname',
          minWidth: 150,
        },
        {
          title: '分销比例',
          slot: 'division_percent',
          minWidth: 100,
        },
        {
          title: '员工数量',
          key: 'agent_count',
          minWidth: 100,
        },
        {
          title: '订单数量',
          key: 'order_count',
          minWidth: 100,
        },
        {
          title: '截止时间',
          slot: 'division_end_time',
          minWidth: 100,
        },
        {
          title: '状态',
          slot: 'status',
          minWidth: 100,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 120,
        },
      ],
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
      return this.isMobile ? undefined : 75;
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
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.formValidate.page = index;
      this.getList();
    },
    clerkPageChange() {
      this.clerkReqData.page = index;
      this.getClerkList();
    },
    // 添加表单
    groupAdd(id) {
      this.$modalForm(agentFrom(id))
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
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 编辑
    edit(row) {},
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        method: 'DELETE',
        uid: row.uid,
        url: `agent/division/del/2/${row.uid}`,
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.userLists.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="stylus">
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
  z-index: 50;
  position: relative;
  display: flex;
  justify-content: space-between;
  word-break: break-all;
}

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

.tabBox_img {
  width: 36px;

  height 36px {
    border-radius: 4px;
  }

  cursor pointer {
    img {
      width: 100%;
      height: 100%;
    }
  }
}
</style>
