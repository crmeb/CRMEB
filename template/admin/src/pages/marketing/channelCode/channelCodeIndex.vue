<template>
  <div>
    <Row class="ivu-mt box-wrapper">
      <Col span="3" class="left-wrapper">
        <Menu :theme="theme3" :active-name="sortName" width="auto">
          <MenuGroup>
            <MenuItem
              :name="item.id"
              class="menu-item"
              :class="index === current ? 'showOn' : ''"
              v-for="(item, index) in labelSort"
              :key="index"
              @click.native="bindMenuItem(item, index)"
            >
              {{ item.cate_name }}
              <div class="icon-box" v-if="index != 0">
                <Icon type="ios-more" size="24" @click.stop="showMenu(item)" />
              </div>
              <div class="right-menu ivu-poptip-inner" v-show="item.status" v-if="index != 0">
                <div class="ivu-poptip-body" @click="labelEdit(item)">
                  <div class="ivu-poptip-body-content">
                    <div class="ivu-poptip-body-content-inner">编辑分类</div>
                  </div>
                </div>
                <div class="ivu-poptip-body" @click="deleteSort(item, '删除分类', index)">
                  <div class="ivu-poptip-body-content">
                    <div class="ivu-poptip-body-content-inner">删除分类</div>
                  </div>
                </div>
              </div>
            </MenuItem>
          </MenuGroup>
        </Menu>
      </Col>
      <Col span="21" ref="rightBox">
        <Card :bordered="false" dis-hover>
          <Row type="flex" class="mb20">
            <Col span="20">
              <Button v-auth="['marketing-channel_code-create']" type="primary" icon="md-add" @click="add" class="mr10"
                >新建二维码</Button
              >
              <Button
                v-auth="['marketing-channel_code-create']"
                type="success"
                icon="md-add"
                @click="addSort"
                style="margin-left: 10px"
                >添加分组</Button
              >
            </Col>
            <Col span="4">
              <Input
                v-model="tableFrom.name"
                search
                @on-search="userSearchs"
                enter-button="搜索"
                placeholder="请输入二维码名称"
              />
            </Col>
          </Row>
          <Table
            :columns="columns1"
            :data="tableList"
            :loading="loading"
            highlight-row
            no-userFrom-text="暂无数据"
            no-filtered-userFrom-text="暂无筛选结果"
          >
            <template slot-scope="{ row, index }" slot="image">
              <div class="tabBox_img" v-viewer>
                <img v-lazy="row.image" />
              </div>
            </template>
            <template slot-scope="{ row, index }" slot="avatar">
              <div class="tabBox_img" v-viewer>
                <img v-lazy="row.avatar" />
              </div>
            </template>
            <template slot-scope="{ row, index }" slot="label_name">
              <div v-if="row.label_name.length">
                <Tag :checkable="false" color="primary" v-for="(item, index) in row.label_name" :key="index">{{
                  item
                }}</Tag>
              </div>
              <div v-else>--</div>
            </template>
            <template slot-scope="{ row, index }" slot="add_time">
              <span v-if="row.stop === 0"> 永久 </span>
              <span v-if="row.stop === 1"> {{ row.add_time }} - {{ row.end_time }}</span>
              <span v-if="row.stop === -1">已过期</span>
            </template>
            <template slot-scope="{ row, index }" slot="status">
              <i-switch
                v-model="row.status"
                :value="row.status"
                :true-value="1"
                :false-value="0"
                :disabled="row.lottery_status == 2 ? true : false"
                @on-change="onchangeIsShow(row)"
                size="large"
              >
                <span slot="open">开启</span>
                <span slot="close">关闭</span>
              </i-switch>
            </template>

            <template slot-scope="{ row, index }" slot="action">
              <a @click="edit(row)">编辑</a>
              <Divider type="vertical" />
              <a @click="del(row, '删除二维码', index)">删除</a>
              <Divider type="vertical" />
              <Dropdown @on-click="changeMenu(row, $event)" transfer="true">
                <a href="javascript:void(0)"
                  >更多
                  <Icon type="ios-arrow-down"></Icon>
                </a>
                <DropdownMenu slot="list">
                  <DropdownItem name="1">下载</DropdownItem>
                  <DropdownItem name="2">统计</DropdownItem>
                  <DropdownItem name="3">用户列表</DropdownItem>
                </DropdownMenu>
              </Dropdown>
            </template>
          </Table>
          <div class="acea-row row-right page">
            <Page :total="total" show-elevator show-total @on-change="pageChange" :page-size="tableFrom.limit" />
          </div>
        </Card>
      </Col>
    </Row>
    <Modal v-model="modals" scrollable footer-hide closable title="渠道码用户列表" :mask-closable="false" width="900">
      <Table
        ref="selection"
        :columns="columns4"
        :data="tabList"
        no-data-text="暂无数据"
        highlight-row
        max-height="400"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="avatar">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="row.avatar" />
          </div>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total2"
          show-elevator
          show-total
          :loading="loading2"
          @on-change="pageChangeUser"
          :page-size="userData.limit"
        />
      </div>
    </Modal>
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
      return this.isMobile ? undefined : 80;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
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
      tabList: [],

      formValidate5: {
        page: 1,
        limit: 15,
        uid: 0,
        to_uid: 0,
        id: 0,
      },
      total2: 0,
      loading2: false,
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
      columns4: [
        {
          title: 'UID',
          key: 'uid',
          minWidth: 120,
        },
        {
          title: '用户头像',
          slot: 'avatar',
          minWidth: 120,
        },
        {
          title: '用户昵称',
          key: 'nickname',
          minWidth: 120,
        },
      ],
      columns1: [
        {
          title: '二维码',
          slot: 'image',
          width: 80,
        },
        {
          title: '二维码名称',
          key: 'name',
          minWidth: 120,
        },
        {
          title: '总关注数',
          key: 'follow',
          minWidth: 120,
        },
        {
          title: '昨日新增关注',
          key: 'y_follow',
          minWidth: 120,
        },
        {
          title: '用户标签',
          slot: 'label_name',
          minWidth: 60,
        },
        {
          title: '时间',
          slot: 'add_time',
          minWidth: 120,
        },
        {
          title: '关联推广员',
          slot: 'avatar',
          minWidth: 60,
        },
        {
          title: '状态',
          slot: 'status',
          minWidth: 60,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 150,
        },
      ],
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
  methods: {
    changeMenu(row, name) {
      this.orderId = row.id;
      switch (name) {
        case '1':
          this.downLoadCode(row.image);
          break;
        case '2':
          this.$router.push({
            path: '/admin/marketing/channel_code/code_statistic?id=' + row.id,
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
      if (!url) return this.$Message.warning('暂无二维码');
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
    // 用列表翻页
    pageChangeUser(index) {
      this.userData.page = index;
      this.getUserList();
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
          this.$Message.error(res.msg);
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
    deleteSort(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `app/wechat_qrcode/cate/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.labelSort.splice(num, 1);
          this.labelSort = [];
          this.getUserLabelAll();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
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
        path: '/admin/marketing/channel_code/create?id=' + row.id,
      });
    },
    // 添加
    add() {
      this.$router.push({
        path: '/admin/marketing/channel_code/create',
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
          this.$Message.error(res.msg);
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
          this.$Message.success(res.msg);
          this.tableList.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
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
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.tableFrom.page = index;
      this.getList();
    },
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        status: row.status,
      };
      wechatQrcodeStatusApi(data)
        .then(async (res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="stylus">
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
  >>>, .ivu-table-header {
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

// margin-left: 18px;
.scollhide::-webkit-scrollbar {
  display: none;
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
