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
          <Col>
            <FormItem label="状态：">
              <Select
                style="width: 200px"
                v-model="formValidate.status"
                placeholder="请选择状态"
                element-id="group_id"
                clearable
                @on-change="userSearchs"
              >
                <Option value="all">全部</Option>
                <Option :value="item.id" v-for="(item, index) in statusList" :key="index">{{
                  item.status_name
                }}</Option>
              </Select>
            </FormItem>
          </Col>
          <Col>
            <FormItem label="搜索：">
              <Input
                style="width: 250px"
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
            <template slot-scope="{ row, index }" slot="images">
              <div class="pictrue-box" v-if="row.images.length">
                <div v-viewer v-for="(item, index) in row.images || []" :key="index">
                  <img class="pictrue mr10" v-lazy="item" :src="item" />
                </div>
              </div>
            </template>
            <template slot-scope="{ row, index }" slot="status">
              <Tag>{{ row.status == 0 ? '申请中' : row.status == 1 ? '已同意' : '已拒绝' }}</Tag>
            </template>
            <template slot-scope="{ row, index }" slot="division_end_time">
              <span> {{ row.division_end_time }}</span>
            </template>
            <template slot-scope="{ row, index }" slot="action">
              <a v-if="row.status == 0" @click="groupAdd(row.id, 1)">同意</a>
              <Divider v-if="row.status == 0" type="vertical" />
              <a v-if="row.status == 0" @click="groupAdd(row.id, 0)">拒绝</a>
              <Divider type="vertical" v-if="row.status == 0" />
              <a @click="del(row, '删除申请', index)">删除</a>
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
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { divisionList, divisionFrom, isShowApi, clerkList } from '@/api/agent';
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
      statusList: [
        {
          status_name: '申请中',
          id: 0,
        },
        {
          status_name: '已同意',
          id: 1,
        },
        {
          status_name: '已拒绝',
          id: 2,
        },
      ],
      columns: [
        {
          title: '用户UID',
          key: 'uid',
          width: 80,
        },
        {
          title: '代理商名称',
          key: 'agent_name',
          minWidth: 150,
        },
        {
          title: '代理商电话',
          key: 'phone',
          minWidth: 100,
        },
        {
          title: '事业部ID',
          key: 'division_id',
          minWidth: 100,
        },
        {
          title: '申请图片',
          slot: 'images',
          minWidth: 100,
        },
        {
          title: '申请时间',
          key: 'add_time',
          minWidth: 100,
        },
        {
          title: '申请状态',
          slot: 'status',
          minWidth: 100,
        },
        {
          title: '邀请码',
          key: 'division_invite',
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
        status: 'all',
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
      divisionList(this.formValidate)
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
    groupAdd(id, type) {
      this.$modalForm(divisionFrom(id, type))
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
        uid: row.id,
        url: `agent/division/del_apply/${row.id}`,
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

.pictrue-box {
  display: flex;
  align-item: center;
}

.pictrue {
  width: 25px;
  height: 25px;
}
</style>
