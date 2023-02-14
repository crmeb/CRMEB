<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt listbox">
      <Tabs @on-click="onClickTab" class="mb20">
        <TabPane :label="item.name" :name="item.type" v-for="(item, index) in headeNum" :key="index" />
      </Tabs>
      <Form
        ref="userFrom"
        :model="userFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row :gutter="16">
          <Col span="18">
            <Col span="24">
              <Col v-bind="grid">
                <FormItem label="用户搜索：" label-for="nickname">
                  <Input v-model="userFrom.nickname" placeholder="请输入用户" element-id="nickname" clearable>
                    <Select v-model="field_key" slot="prepend" style="width: 80px">
                      <Option value="all">全部</Option>
                      <Option value="uid">UID</Option>
                      <Option value="phone">手机号</Option>
                      <Option value="nickname">用户昵称</Option>
                    </Select>
                  </Input>
                </FormItem>
              </Col>
            </Col>
          </Col>
          <template v-if="collapse">
            <Col span="18">
              <Col v-bind="grid">
                <FormItem label="用户等级：" label-for="level">
                  <Select v-model="level" placeholder="请选择用户等级" element-id="level" clearable>
                    <Option value="all">全部</Option>
                    <Option :value="item.id" v-for="(item, index) in levelList" :key="index">{{ item.name }}</Option>
                  </Select>
                </FormItem>
              </Col>
              <Col v-bind="grid">
                <FormItem label="用户分组：" label-for="group_id">
                  <Select v-model="group_id" placeholder="请选择用户分组" element-id="group_id" clearable>
                    <Option value="all">全部</Option>
                    <Option :value="item.id" v-for="(item, index) in groupList" :key="index">{{
                      item.group_name
                    }}</Option>
                  </Select>
                </FormItem>
              </Col>
              <Col v-bind="grid">
                <FormItem label="用户标签：" label-for="label_id">
                  <div class="labelInput acea-row row-between-wrapper" @click="openSelectLabel">
                    <div style="width: 90%">
                      <div v-if="selectDataLabel.length">
                        <Tag
                          :closable="false"
                          v-for="(item, index) in selectDataLabel"
                          @on-close="closeLabel(item)"
                          :key="index"
                          >{{ item.label_name }}</Tag
                        >
                      </div>
                      <span class="span" v-else>选择用户关联标签</span>
                    </div>
                    <div class="ivu-icon ivu-icon-ios-arrow-down"></div>
                  </div>
                </FormItem>
              </Col>
            </Col>
            <Col span="18">
              <Col v-bind="grid">
                <FormItem label="付费会员：" label-for="isMember">
                  <!-- <Select
                    v-model="userFrom.isMember"
                    placeholder="请选择付费会员"
                    element-id="isMember"
                    clearable
                    @on-change="changeMember"
                  >
                    <Option :value="1">是</Option>
                    <Option :value="0">否</Option>
                  </Select> -->
                  <RadioGroup v-model="userFrom.isMember" type="button">
                    <Radio label="">
                      <span>全部</span>
                    </Radio>
                    <Radio label="1">
                      <span>是</span>
                    </Radio>
                    <Radio label="0">
                      <span>否</span>
                    </Radio>
                  </RadioGroup>
                </FormItem>
              </Col>
              <Col v-bind="grid">
                <FormItem label="国家：" label-for="country">
                  <Select
                    v-model="userFrom.country"
                    placeholder="请选择国家"
                    element-id="country"
                    clearable
                    @on-change="changeCountry"
                  >
                    <Option value="domestic">中国</Option>
                    <Option value="abroad">外国</Option>
                  </Select>
                </FormItem>
              </Col>
              <Col v-bind="grid" v-if="userFrom.country === 'domestic'">
                <FormItem label="省份：">
                  <Cascader :data="addresData" :value="address" v-model="address" @on-change="handleChange"></Cascader>
                </FormItem>
              </Col>
            </Col>
            <Col span="18">
              <Col v-bind="grid">
                <FormItem label="性别：" label-for="sex">
                  <RadioGroup v-model="userFrom.sex" type="button">
                    <Radio label="">
                      <span>全部</span>
                    </Radio>
                    <Radio label="1">
                      <span>男</span>
                    </Radio>
                    <Radio label="2">
                      <span>女</span>
                    </Radio>
                    <Radio label="0">
                      <span>保密</span>
                    </Radio>
                  </RadioGroup>
                </FormItem>
              </Col>
              <Col v-bind="grid">
                <FormItem label="身份：" label-for="is_promoter">
                  <RadioGroup v-model="userFrom.is_promoter" type="button">
                    <Radio label="">
                      <span>全部</span>
                    </Radio>
                    <Radio label="1">
                      <span>推广员</span>
                    </Radio>
                    <Radio label="0">
                      <span>普通用户</span>
                    </Radio>
                  </RadioGroup>
                </FormItem>
              </Col>
            </Col>
            <Col span="18">
              <Col v-bind="grid">
                <FormItem label="访问情况：" label-for="user_time_type">
                  <Select v-model="user_time_type" placeholder="请选择访问情况" element-id="user_time_type" clearable>
                    <Option value="">全部</Option>
                    <Option value="visitno">时间段未访问</Option>
                    <Option value="visit">时间段访问过</Option>
                    <Option value="add_time">首次访问</Option>
                  </Select>
                </FormItem>
              </Col>
              <Col v-bind="grid" v-if="user_time_type">
                <FormItem label="访问时间：" label-for="user_time">
                  <!--<DatePicker clearable @on-change="onchangeTime" v-model="timeVal" :value="timeVal"  format="yyyy/MM/dd" type="daterange" placement="bottom-end" placeholder="选择时间" v-width="'100%'"></DatePicker>-->
                  <DatePicker
                    :editable="false"
                    @on-change="onchangeTime"
                    :value="timeVal"
                    format="yyyy/MM/dd"
                    type="datetimerange"
                    placement="bottom-start"
                    placeholder="请选择访问时间"
                    style="width: 300px"
                    class="mr20"
                    :options="options"
                  ></DatePicker>
                </FormItem>
              </Col>
            </Col>
            <Col span="18">
              <Col v-bind="grid">
                <FormItem label="下单次数：" label-for="pay_count">
                  <Select v-model="pay_count" placeholder="请选择下单次数" element-id="pay_count" clearable>
                    <Option value="all">全部</Option>
                    <Option value="-1">0次</Option>
                    <Option value="0">1次以上</Option>
                    <Option value="1">2次以上</Option>
                    <Option value="2">3次以上</Option>
                    <Option value="3">4次以上</Option>
                    <Option value="4">5次以上</Option>
                  </Select>
                </FormItem>
              </Col>
            </Col>
          </template>
          <Col span="6" class="ivu-text-right userFrom">
            <FormItem>
              <Button type="primary" icon="ios-search" label="default" class="mr15" @click="userSearchs">搜索</Button>
              <Button class="ResetSearch" @click="reset('userFrom')">重置</Button>
              <a class="ivu-ml-8 font14 ml10" @click="collapse = !collapse">
                <template v-if="!collapse"> 展开 <Icon type="ios-arrow-down" /> </template>
                <template v-else> 收起 <Icon type="ios-arrow-up" /> </template>
              </a>
            </FormItem>
          </Col>
        </Row>
      </Form>
      <Divider dashed />
      <Row type="flex" justify="space-between" class="mt20">
        <Col span="24">
          <Button v-auth="['admin-user-save']" type="primary" class="mr20" @click="edit({ uid: 0 })">添加用户</Button>
          <Button v-auth="['admin-user-coupon']" class="mr20" @click="onSend">发送优惠券</Button>
          <Button
            v-auth="['admin-wechat-news']"
            class="greens mr20"
            size="default"
            @click="onSendPic"
            v-if="userFrom.user_type === 'wechat'"
          >
            <Icon type="md-list"></Icon>
            发送图文消息
          </Button>
          <Button v-auth="['admin-user-group_set']" class="mr20" @click="setGroup">批量设置分组</Button>
          <Button v-auth="['admin-user-set_label']" class="mr20" @click="setLabel">批量设置标签</Button>
          <Button class="mr20" icon="ios-share-outline" @click="exportList">导出</Button>

          <!-- <Button v-auth="['admin-user-synchro']" class="mr20" @click="synchro">同步公众号用户</Button> -->
        </Col>
        <Col span="24" class="userAlert" v-if="selectionList.length">
          <Alert show-icon>
            已选择<i class="userI"> {{ selectionList.length }} </i>项</Alert
          >
        </Col>
      </Row>
      <Table
        :columns="columns"
        :data="userLists"
        class="mt25"
        ref="table"
        highlight-row
        :loading="loading"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
        @on-sort-change="sortChanged"
        @on-select="handleSelectRow"
        @on-select-cancel="handleCancelRow"
        @on-select-all="handleSelectAll"
        @on-select-all-cancel="handleSelectAll"
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
          <div v-if="row.is_del == 1" style="color: red">用户已注销</div>
          <!-- <div v-show="row.vip_name" class="vipName">{{row.vip_name}}</div> -->
        </template>
        <template slot-scope="{ row, index }" slot="isMember">
          <div>{{ row.isMember ? '是' : '否' }}</div>
        </template>
        <!--                <template slot-scope="{ row, index }" slot="status">-->
        <!--                    <i-switch v-model="row.status" :value="row.status" :true-value="1" :false-value="0" @on-change="onchangeIsShow(row)" size="large">-->
        <!--                        <span slot="open">显示</span>-->
        <!--                        <span slot="close">隐藏</span>-->
        <!--                    </i-switch>-->
        <!--                </template>-->
        <template slot-scope="{ row, index }" slot="action">
          <template v-if="row.is_del != 1">
            <a @click="edit(row)">编辑</a>
            <Divider type="vertical" />
            <Dropdown @on-click="changeMenu(row, $event, index)" :transfer="true">
              <a href="javascript:void(0)">
                更多
                <Icon type="ios-arrow-down"></Icon>
              </a>
              <DropdownMenu slot="list">
                <DropdownItem name="1">账户详情</DropdownItem>
                <DropdownItem name="2">积分余额</DropdownItem>
                <DropdownItem name="3">赠送会员</DropdownItem>
                <!--                                <DropdownItem name="4" v-if="row.vip_name">清除等级</DropdownItem>-->
                <DropdownItem name="5">设置分组</DropdownItem>
                <DropdownItem name="6">设置标签</DropdownItem>
                <DropdownItem name="7">修改上级推广人</DropdownItem>
                <DropdownItem name="8" v-if="row.spread_uid">清除上级推广人</DropdownItem>
              </DropdownMenu>
            </Dropdown>
          </template>
          <template v-else>
            <div v-if="row.is_del == 1" style="color: red">已注销</div>
          </template>
        </template>
      </Table>

      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="userFrom.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="userFrom.limit"
        />
      </div>
    </Card>
    <!-- 编辑表单 积分余额-->
    <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
    <!-- 发送优惠券-->
    <send-from ref="sends" :userIds="ids.toString()"></send-from>
    <!-- 会员详情-->
    <user-details ref="userDetails"></user-details>
    <!--发送图文消息 -->
    <Modal v-model="modal13" scrollable title="发送消息" width="1200" height="800" footer-hide class="modelBox">
      <news-category
        v-if="modal13"
        :isShowSend="isShowSend"
        :userIds="ids.toString()"
        :scrollerHeight="scrollerHeight"
        :contentTop="contentTop"
        :contentWidth="contentWidth"
        :maxCols="maxCols"
      ></news-category>
    </Modal>
    <!--修改推广人-->
    <Modal v-model="promoterShow" scrollable title="修改推广人" class="order_box" :closable="false">
      <Form ref="formInline" :model="formInline" :label-width="100" @submit.native.prevent>
        <FormItem v-if="formInline" label="选择推广人：" prop="image">
          <div class="picBox" @click="customer">
            <div class="pictrue" v-if="formInline.image">
              <img v-lazy="formInline.image" />
            </div>
            <div class="upLoad acea-row row-center-wrapper" v-else>
              <Icon type="ios-camera-outline" size="26" />
            </div>
          </div>
        </FormItem>
      </Form>
      <div slot="footer">
        <Button type="primary" @click="putSend('formInline')">提交</Button>
        <Button @click="cancel('formInline')">取消</Button>
      </div>
    </Modal>
    <Modal v-model="customerShow" scrollable title="请选择商城用户" :closable="false" width="50%">
      <customerInfo v-if="customerShow" @imageObject="imageObject"></customerInfo>
    </Modal>
    <Modal v-model="labelShow" scrollable title="请选择用户标签" :closable="false" width="500" :footer-hide="true">
      <userLabel
        v-if="labelShow"
        :uid="labelActive.uid"
        :only_get="!labelActive.uid"
        @close="labelClose"
        @activeData="activeData"
        @onceGetList="onceGetList"
      ></userLabel>
    </Modal>
    <Drawer :closable="false" width="700" v-model="modals" title="用户信息填写">
      <userEdit ref="userEdit" v-if="modals" :userData="userData"></userEdit>
      <div class="demo-drawer-footer">
        <Button style="margin-right: 8px" @click="modals = false">取消</Button>
        <Button type="primary" @click="setUser">提交</Button>
      </div>
    </Drawer>
    <!-- 用户标签 -->
    <Modal
      v-model="selectLabelShow"
      scrollable
      title="请选择用户标签"
      :closable="false"
      width="500"
      :footer-hide="true"
      :mask-closable="false"
    >
      <userLabel
        v-if="selectLabelShow"
        :uid="0"
        ref="userLabel"
        :only_get="true"
        @activeData="activeSelectData"
        @close="labelClose"
      ></userLabel>
    </Modal>
  </div>
</template>

<script>
import userLabel from '@/components/userLabel';
import { mapState } from 'vuex';
import expandRow from './tableExpand.vue';
import userEdit from './handle/userEdit.vue';
import {
  userList,
  getUserData,
  isShowApi,
  editOtherApi,
  giveLevelApi,
  userSetGroup,
  userGroupApi,
  levelListApi,
  userSetLabelApi,
  userLabelApi,
  userSynchro,
  getUserSaveForm,
  giveLevelTimeApi,
  getUserInfo,
  setUser,
  editUser,
  saveSetLabel,
} from '@/api/user';
import { agentSpreadApi } from '@/api/agent';
import { exportUserList } from '@/api/export';
import editFrom from '../../../components/from/from';
import sendFrom from '@/components/sendCoupons/index';
import userDetails from './handle/userDetails';
import newsCategory from '@/components/newsCategory/index';
import city from '@/utils/city';
import customerInfo from '@/components/customerInfo';
export default {
  name: 'user_list',
  components: {
    expandRow,
    editFrom,
    sendFrom,
    userDetails,
    newsCategory,
    customerInfo,
    userLabel,
    userEdit,
  },
  data() {
    return {
      dataLabel: [],
      selectDataLabel: [],
      userData: {},
      modals: false,
      selectLabelShow: false,
      labelShow: false,
      customerShow: false,
      promoterShow: false,
      labelActive: {
        uid: 0,
      },
      formInline: {
        uid: 0,
        spread_uid: 0,
        image: '',
      },
      options: {
        shortcuts: [
          {
            text: '今天',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()));
              return [start, end];
            },
          },
          {
            text: '昨天',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)),
              );
              end.setTime(
                end.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)),
              );
              return [start, end];
            },
          },
          {
            text: '最近7天',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 6)),
              );
              return [start, end];
            },
          },
          {
            text: '最近30天',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)),
              );
              return [start, end];
            },
          },
          {
            text: '本月',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), 1)));
              return [start, end];
            },
          },
          {
            text: '本年',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(start.setTime(new Date(new Date().getFullYear(), 0, 1)));
              return [start, end];
            },
          },
        ],
      },
      collapse: false,
      headeNum: [
        { type: '', name: '全部' },
        { type: 'wechat', name: '微信公众号' },
        { type: 'routine', name: '微信小程序' },
        { type: 'h5', name: 'H5' },
        { type: 'pc', name: 'PC' },
        { type: 'app', name: 'APP' },
      ],
      address: [],
      addresData: city,
      isShowSend: true,
      modal13: false,
      maxCols: 4,
      scrollerHeight: '600',
      contentTop: '130',
      contentWidth: '98%',
      grid: {
        xl: 8,
        lg: 8,
        md: 12,
        sm: 24,
        xs: 24,
      },
      grid2: {
        xl: 18,
        lg: 16,
        md: 12,
        sm: 24,
        xs: 24,
      },
      loading: false,
      total: 0,
      userFrom: {
        label_id: '',
        user_type: '',
        status: '',
        sex: '',
        is_promoter: '',
        country: '',
        isMember: '',
        pay_count: '',
        user_time_type: '',
        user_time: '',
        nickname: '',
        province: '',
        city: '',
        page: 1,
        limit: 15,
        level: '',
        group_id: '',
        field_key: '',
      },
      field_key: '',
      level: '',
      group_id: '',
      label_id: '',
      user_time_type: '',
      pay_count: '',
      columns: [
        {
          type: 'expand',
          width: 40,
          render: (h, params) => {
            return h(expandRow, {
              props: {
                row: params.row,
              },
            });
          },
        },
        {
          type: 'selection',
          width: 60,
          align: 'center',
        },
        {
          title: 'ID',
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
          title: '付费会员',
          slot: 'isMember',
          minWidth: 90,
        },
        {
          title: '用户等级',
          key: 'level',
          minWidth: 90,
        },
        {
          title: '分组',
          key: 'group_id',
          minWidth: 100,
        },
        {
          title: '手机号',
          key: 'phone',
          minWidth: 100,
        },
        {
          title: '用户类型',
          key: 'user_type',
          minWidth: 100,
        },
        {
          title: '余额',
          key: 'now_money',
          sortable: 'custom',
          minWidth: 100,
        },
        // {
        //     title: '状态',
        //     slot: 'status',
        //     minWidth: 100
        // },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 120,
        },
      ],
      userLists: [],
      FromData: null,
      selectionList: [],
      user_ids: '',
      selectedData: [],
      timeVal: [],
      groupList: [],
      levelList: [],
      labelFrom: {
        page: 1,
        limit: '',
      },
      labelLists: [],
      selectedIds: new Set(), //选中合并项的id
      ids: [],
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 100;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  created() {
    this.getList();
  },
  mounted() {
    this.userGroup();
    this.levelLists();
    // this.groupLists();
  },
  methods: {
    setUser() {
      let data = this.$refs.userEdit.formItem;
      let ids = [];
      this.$refs.userEdit.dataLabel.map((i) => {
        ids.push(i.id);
      });
      data.label_id = ids;
      // if (!data.real_name) return this.$Message.warning("请输入真实姓名");
      // if (!data.phone) return this.$Message.warning("请输入手机号");
      // if (!data.pwd) return this.$Message.warning("请输入密码");
      // if (!data.true_pwd) return this.$Message.warning("请输入确认密码");
      if (data.uid) {
        editUser(data)
          .then((res) => {
            this.modals = false;
            this.$Message.success(res.msg);
            this.getList();
          })
          .catch((err) => {
            this.$Message.error(err.msg);
          });
      } else {
        setUser(data)
          .then((res) => {
            this.modals = false;
            this.$Message.success(res.msg);
            this.getList();
          })
          .catch((err) => {
            this.$Message.error(err.msg);
          });
      }
    },
    onceGetList() {
      this.labelActive.uid = 0;
      this.getList();
    },
    // 标签弹窗关闭
    labelClose() {
      this.labelActive.uid = 0;
      this.labelShow = false;
      this.selectLabelShow = false;
    },
    // 提交
    putSend(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          if (!this.formInline.spread_uid) {
            return this.$Message.error('请上传用户');
          }
          agentSpreadApi(this.formInline)
            .then((res) => {
              this.promoterShow = false;
              this.$Message.success(res.msg);
              this.getList();
              this.$refs[name].resetFields();
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
        }
      });
    },

    save() {
      this.modals = true;

      // this.$modalForm(getUserSaveForm())
      //   .then(() => {
      //     this.userFrom.page = 1;
      //     this.getList();
      //   })
      //   .catch((res) => {
      //     this.$Message.error(res.msg);
      //   });
    },
    synchro() {
      userSynchro()
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 分组列表
    groupLists() {
      this.loading = true;
      userLabelApi(this.labelFrom)
        .then(async (res) => {
          let data = res.data;
          this.labelLists = data.list;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    onClickTab(type) {
      this.userFrom.page = 1;
      this.userFrom.user_type = type;
      this.getList();
    },
    userGroup() {
      let data = {
        page: 1,
        limit: '',
      };
      userGroupApi(data).then((res) => {
        this.groupList = res.data.list;
      });
    },
    levelLists() {
      let data = {
        page: 1,
        limit: '',
        title: '',
        is_show: 1,
      };
      levelListApi(data).then((res) => {
        this.levelList = res.data.list;
      });
    },
    // 批量设置分组；
    setGroup() {
      if (this.ids.length === 0) {
        this.$Message.warning('请选择要设置分组的用户');
      } else {
        let uids = { uids: this.ids };
        this.$modalForm(userSetGroup(uids)).then(() => this.$refs.sends.getList());
      }
    },
    // 批量设置标签；
    setLabel() {
      if (this.ids.length === 0) {
        this.$Message.warning('请选择要设置标签的用户');
      } else {
        let uids = { uids: this.ids };
        this.labelActive.uid = 0;
        this.labelShow = true;
        // this.$modalForm(userSetLabelApi(uids)).then(() =>
        //   this.$refs.sends.getList()
        // );
      }
    },
    activeSelectData(data) {
      // let labels = [];
      // if (!data.length) return;
      // data.map((i) => {
      //   labels.push(i.id);
      // });
      this.selectLabelShow = false;
      this.selectDataLabel = data || [];
    },
    // 批量设置标签
    activeData(data) {
      let labels = [];
      if (!data.length) return;
      data.map((i) => {
        labels.push(i.id);
      });
      saveSetLabel({
        uids: this.ids.join(','),
        label_id: labels,
      }).then((res) => {
        this.labelShow = false;
        this.selectedIds = new Set();
        this.getList();
        this.$Message.success(res.msg);
      });
    },
    //是否为付费会员；
    changeMember() {
      this.userFrom.page = 1;
      this.getList();
    },
    // 选择国家
    changeCountry() {
      if (this.userFrom.country === 'abroad' || !this.userFrom.country) {
        this.selectedData = [];
        this.userFrom.province = '';
        this.userFrom.city = '';
        this.address = [];
      }
    },
    // 选择地址
    handleChange(value, selectedData) {
      this.selectedData = selectedData.map((o) => o.label);
      this.userFrom.province = this.selectedData[0];
      this.userFrom.city = this.selectedData[1];
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.userFrom.user_time = this.timeVal.join('-');
    },
    // 操作
    changeMenu(row, name, index) {
      let uid = [];
      uid.push(row.uid);
      let uids = { uids: uid };
      switch (name) {
        case '1':
          this.$refs.userDetails.modals = true;
          this.$refs.userDetails.getDetails(row.uid);
          break;
        case '2':
          this.getOtherFrom(row.uid);
          break;
        case '3':
          this.giveLevelTime(row.uid);
          break;
        case '4':
          this.del(row, '清除 【 ' + row.nickname + ' 】的会员等级', index, 'user');
          break;
        case '5':
          this.$modalForm(userSetGroup(uids)).then(() => this.getList());
          break;
        case '6':
          this.openLabel(row);
          break;
        case '7':
          this.editS(row);
          break;
        default:
          this.del(row, '解除【 ' + row.nickname + ' 】的上级推广人', index, 'tuiguang');
      }
    },
    openLabel(row) {
      this.labelShow = true;
      this.labelActive.uid = row.uid;
    },
    openSelectLabel() {
      this.selectLabelShow = true;
    },
    editS(row) {
      this.promoterShow = true;
      this.formInline.uid = row.uid;
    },
    customer() {
      this.customerShow = true;
    },
    imageObject(e) {
      this.customerShow = false;
      this.formInline.spread_uid = e.uid;
      this.formInline.image = e.image;
    },
    cancel(name) {
      this.promoterShow = false;
      this.$refs[name].resetFields();
    },
    // 赠送会员等级
    giveLevel(id) {
      giveLevelApi(id)
        .then(async (res) => {
          if (res.data.status === false) {
            return this.$authLapse(res.data);
          }
          this.FromData = res.data;
          this.$refs.edits.modals = true;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 赠送会员等级
    giveLevelTime(id) {
      giveLevelTimeApi(id)
        .then(async (res) => {
          if (res.data.status === false) {
            return this.$authLapse(res.data);
          }
          this.FromData = res.data;
          this.$refs.edits.modals = true;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 删除
    del(row, tit, num, name) {
      let delfromData = {
        title: tit,
        num: num,
        url: name === 'user' ? `user/del_level/${row.uid}` : `agent/stair/delete_spread/${row.uid}`,
        method: name === 'user' ? 'DELETE' : 'PUT',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 清除会员删除成功
    submitModel() {
      this.getList();
    },
    // 会员列表
    getList() {
      if (this.selectDataLabel.length) {
        let activeIds = [];
        this.selectDataLabel.forEach((item) => {
          activeIds.push(item.id);
        });
        this.userFrom.label_id = activeIds.join(',');
      }
      this.userFrom.user_type = this.userFrom.user_type || '';
      this.userFrom.status = this.userFrom.status || '';
      this.userFrom.sex = this.userFrom.sex || '';
      this.userFrom.is_promoter = this.userFrom.is_promoter || '';
      this.userFrom.country = this.userFrom.country || '';
      this.userFrom.pay_count = this.pay_count === 'all' ? '' : this.pay_count;
      this.userFrom.user_time_type = this.user_time_type === 'all' ? '' : this.user_time_type;
      this.userFrom.field_key = this.field_key === 'all' ? '' : this.field_key;
      this.userFrom.level = this.level === 'all' ? '' : this.level;
      this.userFrom.group_id = this.group_id === 'all' ? '' : this.group_id;
      this.loading = true;
      userList(this.userFrom)
        .then(async (res) => {
          let data = res.data;
          this.userLists = data.list;
          this.total = data.count;
          this.loading = false;
          this.$nextTick(() => {
            this.setChecked();
          });
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    // 用户导出
    async exportList() {
      if (this.selectDataLabel.length) {
        let activeIds = [];
        this.selectDataLabel.forEach((item) => {
          activeIds.push(item.id);
        });
        this.userFrom.label_id = activeIds.join(',');
      }
      this.userFrom.user_type = this.userFrom.user_type || '';
      this.userFrom.status = this.userFrom.status || '';
      this.userFrom.sex = this.userFrom.sex || '';
      this.userFrom.is_promoter = this.userFrom.is_promoter || '';
      this.userFrom.country = this.userFrom.country || '';
      this.userFrom.pay_count = this.pay_count === 'all' ? '' : this.pay_count;
      this.userFrom.user_time_type = this.user_time_type === 'all' ? '' : this.user_time_type;
      this.userFrom.field_key = this.field_key === 'all' ? '' : this.field_key;
      this.userFrom.level = this.level === 'all' ? '' : this.level;
      this.userFrom.group_id = this.group_id === 'all' ? '' : this.group_id;
      let [th, filekey, data, fileName] = [[], [], [], ''];
      //   let fileName = "";
      let excelData = JSON.parse(JSON.stringify(this.userFrom));
      excelData.page = 1;
      for (let i = 0; i < excelData.page + 1; i++) {
        let lebData = await this.getExcelData(excelData);
        if (!fileName) fileName = lebData.filename;
        if (!filekey.length) {
          filekey = lebData.fileKey;
        }
        if (!th.length) th = lebData.header;
        if (lebData.export.length) {
          data = data.concat(lebData.export);
          excelData.page++;
        } else {
          this.$exportExcel(th, filekey, fileName, data);
          return;
        }
      }
    },
    getExcelData(excelData) {
      return new Promise((resolve, reject) => {
        exportUserList(excelData).then((res) => {
          resolve(res.data);
        });
      });
    },
    pageChange(index) {
      this.selectionList = [];
      this.userFrom.page = index;
      this.getList();
    },
    // 搜索
    userSearchs() {
      this.userFrom.page = 1;
      this.getList();
    },
    // 重置
    reset(name) {
      this.userFrom = {
        user_type: this.userFrom.user_type,
        status: '',
        sex: '',
        is_promoter: '',
        country: '',
        pay_count: '',
        user_time_type: '',
        user_time: '',
        nickname: '',
        field_key: '',
        level: '',
        group_id: '',
        label_id: '',
        page: 1, // 当前页
        limit: 20, // 每页显示条数
      };
      this.field_key = '';
      this.level = '';
      this.group_id = '';
      this.dataLabel = [];
      this.selectDataLabel = [];
      this.user_time_type = '';
      this.pay_count = '';
      this.timeVal = [];
      this.selectedIds = new Set();
      this.getList();
    },
    // 获取编辑表单数据
    getUserFrom(id) {
      getUserInfo(id)
        .then(async (res) => {
          this.modals = true;
          this.userData = res.data;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // getUserFrom(id) {
    //   getUserData(id)
    //     .then(async (res) => {
    //       if (res.data.status === false) {
    //         return this.$authLapse(res.data);
    //       }
    //       this.FromData = res.data;
    //       this.$refs.edits.modals = true;
    //     })
    //     .catch((res) => {
    //       this.$Message.error(res.msg);
    //     });
    // },
    // 获取积分余额表单
    getOtherFrom(id) {
      editOtherApi(id)
        .then(async (res) => {
          if (res.data.status === false) {
            return this.$authLapse(res.data);
          }
          this.FromData = res.data;
          this.$refs.edits.modals = true;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 修改状态
    onchangeIsShow(row) {
      let data = {
        id: row.uid,
        status: row.status,
      };
      isShowApi(data)
        .then(async (res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 点击发送优惠券
    onSend() {
      if (this.ids.length === 0) {
        this.$Message.warning('请选择要发送优惠券的用户');
      } else {
        this.$refs.sends.modals = true;
        this.$refs.sends.getList();
      }
    },
    // 发送图文消息
    onSendPic() {
      if (this.ids.length === 0) {
        this.$Message.warning('请选择要发送图文消息的用户');
      } else {
        this.modal13 = true;
      }
    },
    // 编辑
    edit(row) {
      this.getUserFrom(row.uid);
    },
    // 修改成功
    submitFail() {
      // this.getList();
    },
    // 排序
    sortChanged(e) {
      this.userFrom[e.key] = e.order;
      this.getList();
    },
    //全选和取消全选时触发
    handleSelectAll(selection) {
      if (selection.length === 0) {
        //获取table的数据；
        let data = this.$refs.table.data;
        data.forEach((item) => {
          if (this.selectedIds.has(item.uid)) {
            this.selectedIds.delete(item.uid);
          }
        });
      } else {
        selection.forEach((item) => {
          this.selectedIds.add(item.uid);
        });
      }
      this.$nextTick(() => {
        //确保dom加载完毕
        this.setChecked();
      });
    },
    //  选中某一行
    handleSelectRow(selection, row) {
      this.selectedIds.add(row.uid);
      this.$nextTick(() => {
        //确保dom加载完毕
        this.setChecked();
      });
    },
    //  取消某一行
    handleCancelRow(selection, row) {
      this.selectedIds.delete(row.uid);
      this.$nextTick(() => {
        //确保dom加载完毕
        this.setChecked();
      });
    },
    setChecked() {
      //将new Set()转化为数组
      this.ids = [...this.selectedIds];
      // 找到绑定的table的ref对应的dom，找到table的objData对象，objData保存的是当前页的数据
      let objData = this.$refs.table.objData;
      for (let index in objData) {
        if (this.selectedIds.has(objData[index].uid)) {
          objData[index]._isChecked = true;
        }
      }
    },
  },
};
</script>

<style scoped lang="stylus">
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

.userFrom {
  >>> .ivu-form-item-content {
    margin-left: 0px !important;
  }
}

.userAlert {
  margin-top: 20px;
}

.userI {
  color: #1890FF;
  font-style: normal;
}

img {
  height: 36px;
  display: block;
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

.tabBox_tit {
  width: 60%;
  font-size: 12px !important;
  margin: 0 2px 0 10px;
  letter-spacing: 1px;
  padding: 5px 0;
  box-sizing: border-box;
}

.modelBox {
  >>> .ivu-modal-body {
    padding: 0 16px 16px 16px !important;
  }
}

.vipName {
  color: #dab176;
}

.listbox {
  >>>.ivu-divider-horizontal {
    margin: 0 !important;
  }
}

.labelInput {
  border: 1px solid #dcdee2;
  padding: 0 6px;
  border-radius: 5px;
  min-height: 30px;
  cursor: pointer;

  .span {
    color: #c5c8ce;
  }

  .ivu-icon-ios-arrow-down {
    font-size: 14px;
    color: #808695;
  }
}
.demo-drawer-footer{
        width: 100%;
        position: absolute;
        bottom: 0;
        left: 0;
        border-top: 1px solid #e8e8e8;
        padding: 10px 16px;
        text-align: right;
        background: #fff;
    }
</style>
