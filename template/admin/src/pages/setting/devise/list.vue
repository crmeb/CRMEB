<template>
  <div>
    <div class="i-layout-page-header">
      <span class="ivu-page-header-title mr20">页面装修</span>
      <div>
        <div style="float: right" v-if="cardShow == 1 || cardShow == 2">
          <Button class="bnt" type="primary" @click="submit" :loading="loadingExist">保存</Button>
          <Button class="bnt ml20" @click="reast">重置</Button>
        </div>
      </div>
    </div>

    <Row class="ivu-mt box-wrapper">
      <Col span="3" class="left-wrapper">
        <Menu :theme="theme3" :active-name="1" width="auto">
          <MenuGroup>
            <MenuItem
              :name="item.id"
              v-for="(item, index) in menuList"
              :key="index"
              @click.native="bindMenuItem(index)"
            >
              {{ item.name }}
            </MenuItem>
          </MenuGroup>
        </Menu>
      </Col>
      <Col span="21" class="right-wrapper">
        <Card :bordered="false" dis-hover v-if="cardShow == 0">
          <Row v-if="cardShow == 0">
            <Col style="width: 310px; height: 550px; margin-right: 30px; position: relative" v-if="isDiy">
              <iframe class="iframe-box" :src="imgUrl" frameborder="0" ref="iframe"></iframe>
              <div class="mask"></div>
            </Col>
            <Col :span="isDiy ? '' : 24" v-bind="isDiy ? grid : ''" :class="isDiy ? 'table' : ''">
              <div class="acea-row row-between-wrapper">
                <Row type="flex">
                  <Col v-bind="grid">
                    <div class="button acea-row row-middle">
                      <Button type="primary" icon="md-add" @click="add">添加专题页</Button>
                    </div>
                  </Col>
                </Row>
              </div>
              <Table
                :columns="columns1"
                :data="list"
                ref="table"
                class="mt25"
                :loading="loading"
                highlight-row
                no-userFrom-text="暂无数据"
                no-filtered-userFrom-text="暂无筛选结果"
              >
                <template slot-scope="{ row, index }" slot="region">
                  <div class="font-blue">首页</div>
                </template>
                <template slot-scope="{ row, index }" slot="type_name">
                  <Tag color="primary" v-if="row.is_diy">{{ row.type_name }}{{ row.id }}</Tag>
                  <Tag color="warning" v-else>{{ row.type_name }}</Tag>
                  <Tag color="success" v-if="row.status == 1">首页</Tag>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                  <div style="display: inline-block" v-if="row.status || row.is_diy" @click="edit(row)">
                    <a
                      v-if="row.is_diy === 1"
                      class="target"
                      ref="target"
                      :href="`${url}/admin/setting/pages/diy_index?id=${row.id}&name=${row.template_name || 'moren'}`"
                      target="_blank"
                    >
                      编辑</a
                    >
                    <a v-else class="target">编辑</a>
                  </div>
                  <Divider type="vertical" v-if="(row.status || row.is_diy) && row.id != 1 && row.status != 1" />

                  <div style="display: inline-block" v-if="row.id != 1 && row.status != 1">
                    <a @click="del(row, '删除此模板', index)">删除</a>
                  </div>
                  <Divider type="vertical" v-if="(row.id != 1 && row.status != 1) || row.is_diy" />
                  <div style="display: inline-block" v-if="row.is_diy">
                    <a @click="preview(row, index)">预览</a>
                  </div>
                  <Divider type="vertical" v-if="row.is_diy && row.status != 1" />
                  <div style="display: inline-block" v-if="row.status != 1">
                    <a @click="setStatus(row, index)">设为首页</a>
                  </div>

                  <!-- <Divider type="vertical" v-if="row.status != 1" />
                  <template>
                    <Dropdown @on-click="changeMenu(row, index, $event)">
                      <a href="javascript:void(0)"
                        >更多
                        <Icon type="ios-arrow-down"></Icon>
                      </a>
                      <DropdownMenu slot="list">
                        <DropdownItem name="1" v-show="!row.type"
                          >设置默认数据</DropdownItem
                        >
                        <DropdownItem name="2" v-show="!row.type"
                          >恢复默认数据</DropdownItem
                        >
                        <DropdownItem name="3" v-show="row.id != 1"
                          >删除模板</DropdownItem
                        >
                      </DropdownMenu>
                    </Dropdown>
                  </template> -->
                </template>
              </Table>
              <div class="acea-row row-right page">
                <Page
                  :total="total"
                  :current="diyFrom.page"
                  show-elevator
                  show-total
                  @on-change="pageChange"
                  :page-size="diyFrom.limit"
                />
              </div>
            </Col>
          </Row>
        </Card>
        <goodClass v-else-if="cardShow == 1" ref="category" @parentFun="getChildData"></goodClass>
        <users v-else ref="users" @parentFun="getChildData"></users>
      </Col>
    </Row>
    <Modal
      v-model="isTemplate"
      scrollable
      footer-hide
      closable
      title="开发移动端链接"
      :z-index="1"
      width="500"
      @on-cancel="cancel"
    >
      <div class="article-manager">
        <Card :bordered="false" dis-hover class="ivu-mt">
          <Form
            ref="formItem"
            :model="formItem"
            :label-width="120"
            label-position="right"
            :rules="ruleValidate"
            @submit.native.prevent
          >
            <Row type="flex" :gutter="24">
              <Col span="24">
                <Col v-bind="grid">
                  <FormItem label="开发移动端链接：" prop="link" label-for="link">
                    <Input v-model="formItem.link" placeholder="http://localhost:8080" />
                  </FormItem>
                </Col>
              </Col>
            </Row>
            <Row type="flex">
              <Col v-bind="grid">
                <Button type="primary" class="ml20" @click="handleSubmit('formItem')" style="width: 100%">提交</Button>
              </Col>
            </Row>
          </Form>
        </Card>
      </div>
    </Modal>
    <Modal v-model="modal" title="预览" footer-hide>
      <div>
        <div v-viewer class="acea-row row-around code">
          <div class="acea-row row-column-around row-between-wrapper">
            <div class="QRpic" ref="qrCodeUrl"></div>
            <span class="mt10">公众号二维码</span>
          </div>
          <div class="acea-row row-column-around row-between-wrapper">
            <div class="QRpic">
              <img v-lazy="qrcodeImg" />
            </div>
            <span class="mt10">小程序二维码</span>
          </div>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script>
import Setting from '@/setting';
import ClipboardJS from 'clipboard';
import { diyList, diyDel, setStatus, recovery, getRoutineCode, getDiyCreate, setDefault } from '@/api/diy';
import { mapState } from 'vuex';
import QRCode from 'qrcodejs2';
import goodClass from './goodClass';
import users from './users';
import { getCookies, setCookies } from '@/libs/util';
export default {
  name: 'devise_list',
  computed: {
    ...mapState('admin/layout', ['menuCollapse']),
  },
  components: {
    goodClass,
    users,
  },
  data() {
    return {
      grid: {
        sm: 10,
        md: 12,
        lg: 19,
      },
      loading: false,
      theme3: 'light',
      menuList: [
        {
          name: '商城首页',
          id: 1,
        },
        {
          name: '商品分类',
          id: 2,
        },
        {
          name: '个人中心',
          id: 3,
        },
      ],
      columns1: [
        {
          title: '页面ID',
          key: 'id',
          width: 80,
        },
        {
          title: '模板名称',
          key: 'name',
          minWidth: 100,
        },
        {
          title: '模板类型',
          slot: 'type_name',
          minWidth: 100,
        },
        {
          title: '添加时间',
          key: 'add_time',
          minWidth: 100,
        },
        {
          title: '更新时间',
          key: 'update_time',
          minWidth: 100,
        },
        {
          title: '操作',
          slot: 'action',
          // fixed: "right",
          minWidth: 180,
        },
      ],
      list: [],
      imgUrl: '',
      modal: false,
      BaseURL: Setting.apiBaseURL.replace(/adminapi/, ''),
      cardShow: 0,
      loadingExist: false,
      isDiy: 1,
      qrcodeImg: '',
      diyFrom: {
        type: '',
        page: 1,
        limit: 10,
      },
      total: 0,
      formItem: {
        id: 0,
        link: '',
      },
      isTemplate: false,
      ruleValidate: {
        link: [{ required: true, message: '请输入移动端链接', trigger: 'blur' }],
      },
      url: window.location.origin,
    };
  },
  created() {
    this.getList();
    this.imgUrl = `${location.origin}/pages/index/index?type=iframeWindow`;
  },
  mounted: function () {},
  methods: {
    cancel() {
      this.$refs['formItem'].resetFields();
    },
    getChildData(e) {
      this.loadingExist = e;
    },
    submit() {
      if (this.cardShow == 1) {
        this.$refs.category.onSubmit();
      } else {
        this.$refs.users.onSubmit();
      }
    },
    reast() {
      if (this.cardShow == 1) {
        this.$refs.category.onSubmit(1);
      } else {
        this.$refs.users.getInfo();
      }
    },
    bindMenuItem(index) {
      this.cardShow = index;
    },
    onCopy() {
      this.$Message.success('复制预览链接成功');
    },
    onError() {
      this.$Message.error('复制预览链接失败');
    },
    //生成二维码
    creatQrCode(id) {
      this.$refs.qrCodeUrl.innerHTML = '';
      let url = `${this.BaseURL}pages/annex/special/index?id=${id}`;
      var qrcode = new QRCode(this.$refs.qrCodeUrl, {
        text: url, // 需要转换为二维码的内容
        width: 160,
        height: 160,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H,
      });
    },
    //小程序二维码
    routineCode(id) {
      getRoutineCode(id)
        .then((res) => {
          this.qrcodeImg = res.data.image;
        })
        .catch((err) => {
          this.$Message.error(err);
        });
    },
    preview(row) {
      this.modal = true;
      this.creatQrCode(row.id);
      this.routineCode(row.id);
    },
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          setCookies('moveLink', this.formItem.link);
          this.$router.push({
            path: '/admin/setting/pages/diy',
            query: { id: this.formItem.id, type: 1 },
          });
        } else {
          return false;
        }
      });
    },
    changeMenu(row, index, name) {
      switch (name) {
        case '1':
          this.setDefault(row);
          break;
        case '2':
          this.recovery(row);
          break;
        case '3':
          this.del(row, '删除此模板', index);
          break;
        default:
      }
    },
    //设置默认数据
    setDefault(row) {
      setDefault(row.id)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    // 获取列表
    getList() {
      // let storage = window.localStorage;
      // this.imgUrl = storage.getItem("imgUrl");
      let that = this;
      this.loading = true;
      diyList(this.diyFrom).then((res) => {
        this.loading = false;
        let data = res.data;
        this.list = data.list;
        this.total = data.count;
      });
    },
    pageChange(status) {
      this.diyFrom.page = status;
      this.getList();
    },
    // 编辑
    edit(row) {
      this.formItem.id = row.id;
      if (!row.is_diy) {
        if (!row.status) {
          this.$Message.error('请先设为首页在进行编辑');
        } else {
          this.$router.push({
            path: '/admin/setting/pages/diy',
            query: { id: row.id, type: 0 },
          });
        }
      }
    },
    // 添加
    // add() {
    //   this.$modalForm(getDiyCreate()).then(() => this.getList());
    // },
    // 添加
    add() {
      this.$router.push({
        path: '/admin/setting/pages/diy_index',
        query: { id: 0, name: '首页', type: 1 },
      });
    },
    // 删除
    del(row) {
      let delfromData = {
        title: '删除',
        num: 2000,
        url: 'diy/del/' + row.id,
        method: 'DELETE',
        data: {
          type: 1,
        },
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 使用模板
    async setStatus(row) {
      this.$Modal.confirm({
        title: '提示',
        content: '<p>是否把该模板设为首页</p>',
        onOk: () => {
          setStatus(row.id, {
            type: 1,
          })
            .then((res) => {
              this.$Message.success(res.msg);
              this.$Modal.remove();
              this.getList();
              // if (res.data.status) {
              //   this.$Message.success(res.data.msg);
              //   this.$Modal.remove();
              //   this.getList();
              // } else {
              //   setTimeout((e) => {
              //     this.$Modal.confirm({
              //       title: "提示",
              //       content: "<p>尚未安装模板，请购买安装后再试！</p>",
              //       loading: false,
              //       okText: "点击购买",
              //       onOk: () => {
              //         window.open("http://s.crmeb.com/goods_cate", `_blank`);
              //       },
              //     });
              //   }, 200);
              // }
            })
            .catch((res) => {
              this.$Modal.remove();
              this.$Message.error(res.msg);
            });
        },
      });
    },
    recovery(row) {
      recovery(row.id).then((res) => {
        this.$Message.success(res.msg);
        this.getList();
      });
    },
  },
};
</script>

<style scoped lang="stylus">
.ivu-mt {
  background-color: #fff;
  padding-bottom: 50px;
}

.bnt {
  width: 80px !important;
}

.iframe-box {
  width: 100%;
  height: 100%;
  border-radius: 10px;
  border: 1px solid #eee;
}

.mask {
  position: absolute;
  left: 0;
  width: 100%;
  top: 0;
  height: 100%;
  background-color: rgba(0, 0, 0, 0);
}

/deep/.ivu-menu-vertical .ivu-menu-item, .ivu-menu-vertical .ivu-menu-submenu-title {
  text-align: center;
}

/deep/.i-layout-page-header {
  height: 66px;
  background-color: #fff;
  border-bottom: 1px solid #e8eaec;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/deep/.ivu-page-header {
  border-bottom: unset;
  position: fixed;
  z-index: 9;
  width: 100%;
}

/deep/ .ivu-menu-vertical .ivu-menu-item-group-title {
  display: none;
}

/deep/ .ivu-menu-vertical.ivu-menu-light:after {
  display: none;
}

/deep/ .ivu-menu {
  z-index: 0 !important;
}

/deep/ .ivu-row {
  display: flex;
}

@media (max-width: 2175px) {
  .table {
    display: block;
    flex: 0 0 76%;
    max-width: 76%;
  }
}

@media (max-width: 2010px) {
  .table {
    display: block;
    flex: 0 0 75%;
    max-width: 75%;
  }
}

@media (max-width: 1860px) {
  .table {
    display: block;
    flex: 0 0 70%;
    max-width: 70%;
  }
}

@media (max-width: 1597px) {
  .table {
    display: block;
    flex: 0 0 65%;
    max-width: 65%;
  }
}

@media (max-width: 1413px) {
  .table {
    display: block;
    flex: 0 0 60%;
    max-width: 60%;
  }
}

@media (max-width: 1275px) {
  .table {
    display: block;
    flex: 0 0 55%;
    max-width: 55%;
  }
}

@media (max-width: 1168px) {
  .table {
    display: block;
    flex: 0 0 48%;
    max-width: 48%;
  }
}

.code {
  position: relative;
}

.QRpic {
  width: 160px;
  height: 160px;

  img {
    width: 100%;
    height: 100%;
  }
}

.left-wrapper {
  background: #fff;
  border-right: 1px solid #dcdee2;
}

.picCon {
  width: 280px;
  height: 510px;
  background: #FFFFFF;
  border: 1px solid #EEEEEE;
  border-radius: 25px;

  .pictrue {
    width: 250px;
    height: 417px;
    border: 1px solid #EEEEEE;
    opacity: 1;
    border-radius: 10px;
    margin: 30px auto 0 auto;

    img {
      width: 100%;
      height: 100%;
      border-radius: 10px;
    }
  }

  .circle {
    width: 36px;
    height: 36px;
    background: #FFFFFF;
    border: 1px solid #EEEEEE;
    border-radius: 50%;
    margin: 13px auto 0 auto;
  }
}
</style>
