<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="userFrom"
          :model="userFrom"
          label-width="80px"
          label-position="right"
          @submit.native.prevent
          inline
        >
          <div class="acea-row search-form" v-if="!collapse">
            <div>
              <el-form-item label="用户标签：" label-for="label_id">
                <div class="labelInput acea-row row-between-wrapper" @click="openSelectLabel">
                  <div style="width: 222px">
                    <div v-if="selectDataLabel.length">
                      <el-tag
                        v-for="(item, index) in selectDataLabel"
                        :key="index"
                        closable
                        class="mr10"
                        @close="handleClose(item)"
                        >{{ item.label_name }}</el-tag
                      >
                    </div>
                    <span class="span" v-else>选择用户关联标签</span>
                  </div>
                  <div class="ivu-icon ivu-icon-ios-arrow-down"></div>
                </div>
              </el-form-item>
              <el-form-item label="用户等级：" label-for="level">
                <el-select v-model="level" placeholder="请选择用户等级" clearable class="form_content_width">
                  <el-option value="all" label="全部">全部</el-option>
                  <el-option
                    :value="item.id"
                    v-for="(item, index) in levelList"
                    :key="index"
                    :label="item.name"
                  ></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="用户搜索：" label-for="nickname">
                <el-input v-model="userFrom.nickname" placeholder="请输入用户" clearable class="form_content_width">
                  <el-select v-model="field_key" slot="prepend" style="width: 100px">
                    <el-option value="all" label="全部"></el-option>
                    <el-option value="uid" label="UID"></el-option>
                    <el-option value="phone" label="手机号"></el-option>
                    <el-option value="nickname" label="用户昵称"></el-option>
                  </el-select>
                </el-input>
              </el-form-item>
            </div>
            <el-form-item class="search-form-sub">
              <el-button type="primary" @click="userSearchs">搜索</el-button>
              <el-button class="ResetSearch" @click="reset('userFrom')">重置</el-button>
              <a class="ivu-ml-8 font12 ml10" @click="collapse = !collapse">
                <template v-if="!collapse"> 展开 <i class="el-icon-arrow-down" /> </template>
                <template v-else> 收起 <i class="el-icon-arrow-up" /> </template>
              </a>
            </el-form-item>
          </div>
          <div v-if="collapse" class="acea-row search-form">
            <div class="search-form-box">
              <el-form-item label="用户搜索：" label-for="nickname">
                <el-input v-model="userFrom.nickname" placeholder="请输入用户" clearable class="form_content_width">
                  <el-select v-model="field_key" slot="prepend" style="width: 100px">
                    <el-option value="all" label="全部"></el-option>
                    <el-option value="uid" label="UID"></el-option>
                    <el-option value="phone" label="手机号"></el-option>
                    <el-option value="nickname" label="用户昵称"></el-option>
                  </el-select>
                </el-input>
              </el-form-item>
              <el-form-item label="用户标签：" label-for="label_id">
                <div class="labelInput acea-row row-between-wrapper" @click="openSelectLabel">
                  <div style="width: 222px">
                    <div v-if="selectDataLabel.length">
                      <el-tag :closable="false" v-for="(item, index) in selectDataLabel" :key="index" class="mr10">{{
                        item.label_name
                      }}</el-tag>
                    </div>
                    <span class="span" v-else>选择用户关联标签</span>
                  </div>
                  <div class="ivu-icon ivu-icon-ios-arrow-down"></div>
                </div>
              </el-form-item>
              <el-form-item label="下单次数：">
                <el-select v-model="pay_count" placeholder="请选择下单次数" clearable class="form_content_width">
                  <el-option value="all" label="全部"></el-option>
                  <el-option value="-1" label="0次"></el-option>
                  <el-option value="0" label="1次以上"></el-option>
                  <el-option value="1" label="2次以上"></el-option>
                  <el-option value="2" label="3次以上"></el-option>
                  <el-option value="3" label="4次以上"></el-option>
                  <el-option value="4" label="5次以上"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="用户分组：">
                <el-select v-model="group_id" placeholder="请选择用户分组" clearable class="form_content_width">
                  <el-option value="all" label="全部"></el-option>
                  <el-option
                    :value="item.id"
                    v-for="(item, index) in groupList"
                    :key="index"
                    :label="item.group_name"
                  ></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="用户等级：" label-for="level">
                <el-select v-model="level" placeholder="请选择用户等级" clearable class="form_content_width">
                  <el-option value="all" label="全部">全部</el-option>
                  <el-option
                    :value="item.id"
                    v-for="(item, index) in levelList"
                    :key="index"
                    :label="item.name"
                  ></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="身份：">
                <el-select v-model="userFrom.is_promoter" placeholder="请选择" clearable class="form_content_width">
                  <el-option value="" label="全部"></el-option>
                  <el-option value="1" label="推广员"></el-option>
                  <el-option value="0" label="普通用户"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="付费会员：" label-for="isMember">
                <el-select v-model="userFrom.isMember" placeholder="请选择" clearable class="form_content_width">
                  <el-option value="" label="全部"></el-option>
                  <el-option value="1" label="是"></el-option>
                  <el-option value="0" label="否"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="访问情况：" label-for="user_time_type">
                <el-select v-model="user_time_type" placeholder="请选择访问情况" clearable class="form_content_width">
                  <el-option value="" label="全部"></el-option>
                  <el-option value="visitno" label="时间段未访问"></el-option>
                  <el-option value="visit" label="时间段访问过"></el-option>
                  <el-option value="add_time" label="首次访问"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="访问时间：" label-for="user_time" v-if="user_time_type">
                <el-date-picker
                  clearable
                  v-model="timeVal"
                  type="daterange"
                  :editable="false"
                  @change="onchangeTime"
                  format="yyyy/MM/dd"
                  value-format="yyyy/MM/dd"
                  start-placeholder="开始日期"
                  end-placeholder="结束日期"
                  :picker-options="pickerOptions"
                  style="width: 250px"
                ></el-date-picker>
              </el-form-item>
              <el-form-item label="地区：" label-for="country">
                <el-select
                  v-model="userFrom.country"
                  placeholder="请选择国家"
                  clearable
                  @change="changeCountry"
                  class="form_content_width"
                >
                  <el-option value="domestic" label="中国"></el-option>
                  <el-option value="abroad" label="外国"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="省份：" v-if="userFrom.country === 'domestic'">
                <el-cascader
                  :options="addresData"
                  :value="address"
                  v-model="address"
                  @change="handleChange"
                  clearable
                  style="width: 250px"
                ></el-cascader>
              </el-form-item>
            </div>

            <el-form-item class="search-form-sub">
              <el-button type="primary" label="default" @click="userSearchs">搜索</el-button>
              <el-button class="ResetSearch" @click="reset('userFrom')">重置</el-button>
              <a class="ivu-ml-8 font12 ml10" @click="collapse = !collapse">
                <template v-if="!collapse"> 展开 <i class="el-icon-arrow-down" /> </template>
                <template v-else> 收起 <i class="el-icon-arrow-up" /> </template>
              </a>
            </el-form-item>
          </div>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16" :body-style="{ padding: '0 20px 20px' }">
      <el-tabs v-model="userFrom.user_type" @tab-click="onClickTab">
        <el-tab-pane :label="item.name" :name="item.type" v-for="(item, index) in headeNum" :key="index" />
      </el-tabs>
      <el-row :gutter="24" justify="space-between">
        <el-col :span="24">
          <el-button v-auth="['admin-user-save']" type="primary" @click="edit({ uid: 0 })">添加用户</el-button>
          <el-button v-auth="['admin-user-coupon']" @click="onSend">发送优惠券</el-button>
          <el-button
            v-auth="['admin-wechat-news']"
            class="greens mr10"
            @click="onSendPic"
            v-if="userFrom.user_type === 'wechat'"
          >
            发送图文消息
          </el-button>
          <el-button v-auth="['admin-user-group_set']" @click="setGroup">批量设置分组</el-button>
          <el-button v-auth="['admin-user-set_label']" @click="setLabel">批量设置标签</el-button>
          <el-button class="mr10" @click="exportList">导出</el-button>

          <!-- <el-button v-auth="['admin-user-synchro']" class="mr20" @click="synchro">同步公众号用户</el-button> -->
        </el-col>
        <el-col :span="24" class="userAlert" v-if="selectionList.length">
          <el-alert show-icon>
            <template slot="title">
              已选择<i class="userI"> {{ selectionList.length }} </i>项
            </template>
          </el-alert>
        </el-col>
      </el-row>
      <el-table
        :data="userLists"
        class="mt16"
        ref="table"
        highlight-current-row
        v-loading="loading"
        empty-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
        @sort-change="sortChanged"
        @select="handleSelectRow"
        @select-all="handleSelectAll"
      >
        <el-table-column type="expand">
          <template slot-scope="scope">
            <expandRow :row="scope.row"></expandRow>
          </template>
        </el-table-column>
        <el-table-column type="selection" :selectable="isSel" width="55"> </el-table-column>
        <el-table-column label="用户ID" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="头像" min-width="60">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="姓名" min-width="150">
          <template slot-scope="scope">
            <div class="acea-row">
              <i class="el-icon-male" v-show="scope.row.sex === '男'" style="color: #2db7f5; font-size: 15px"></i>
              <i class="el-icon-female" v-show="scope.row.sex === '女'" style="color: #ed4014; font-size: 15px"></i>
              <div v-text="scope.row.nickname" class=""></div>
            </div>
            <div v-if="scope.row.is_del == 1" style="color: red">用户已注销</div>
          </template>
        </el-table-column>
        <el-table-column label="付费会员" min-width="90">
          <template slot-scope="scope">
            <div>{{ scope.row.isMember ? '是' : '否' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="用户等级" min-width="90">
          <template slot-scope="scope">
            <div>{{ scope.row.level }}</div>
          </template>
        </el-table-column>
        <el-table-column label="分组" min-width="100">
          <template slot-scope="scope">
            <div>{{ scope.row.group_id }}</div>
          </template>
        </el-table-column>
        <el-table-column label="手机号" min-width="100">
          <template slot-scope="scope">
            <div>{{ scope.row.phone }}</div>
          </template>
        </el-table-column>
        <el-table-column label="用户类型" min-width="100">
          <template slot-scope="scope">
            <div>{{ scope.row.user_type }}</div>
          </template>
        </el-table-column>
        <el-table-column label="余额" prop="now_money" min-width="100" :sortable="true">
          <template slot-scope="scope">
            <div>{{ scope.row.now_money }}</div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template slot-scope="scope">
            <template v-if="scope.row.is_del != 1">
              <a @click="userDetail(scope.row)">详情</a>

              <el-divider direction="vertical"></el-divider>
              <el-dropdown size="small" @command="changeMenu(scope.row, $event, scope.$index)" :transfer="true">
                <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
                <el-dropdown-menu slot="dropdown">
                  <!-- <el-dropdown-item command="1">编辑</el-dropdown-item> -->
                  <el-dropdown-item command="2">积分余额</el-dropdown-item>
                  <el-dropdown-item command="3">赠送会员</el-dropdown-item>
                  <!--                                <el-dropdown-item command="4" v-if="row.vip_name">清除等级</el-dropdown-item>-->
                  <el-dropdown-item command="5">设置分组</el-dropdown-item>
                  <el-dropdown-item command="6">设置标签</el-dropdown-item>
                  <el-dropdown-item command="7">修改上级推广人</el-dropdown-item>
                  <el-dropdown-item command="8" v-if="scope.row.spread_uid">清除上级推广人</el-dropdown-item>
                </el-dropdown-menu>
              </el-dropdown>
            </template>
            <template v-else>
              <a @click="userDetail(scope.row)">详情</a>
            </template>
          </template>
        </el-table-column>
      </el-table>

      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="userFrom.page"
          :limit.sync="userFrom.limit"
          @pagination="pageChange"
        />
      </div>
    </el-card>
    <!-- 编辑表单 积分余额-->
    <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
    <!-- 发送优惠券-->
    <send-from ref="sends" :userIds="ids.toString()"></send-from>
    <!-- 会员详情-->
    <user-details ref="userDetails"></user-details>
    <!--发送图文消息 -->
    <el-dialog :visible.sync="modal13" title="发送消息" width="1200px" class="modelBox">
      <news-category
        v-if="modal13"
        :isShowSend="isShowSend"
        :userIds="ids.toString()"
        :scrollerHeight="scrollerHeight"
        :contentTop="contentTop"
        :contentWidth="contentWidth"
        :maxCols="maxCols"
      ></news-category>
    </el-dialog>
    <!--修改推广人-->
    <el-dialog :visible.sync="promoterShow" title="修改推广人" width="540px" :show-close="true">
      <el-form ref="formInline" :model="formInline" label-width="100px" @submit.native.prevent>
        <el-form-item v-if="formInline" label="选择推广人：" prop="image">
          <div class="picBox" @click="customer">
            <div class="pictrue" v-if="formInline.image">
              <img v-lazy="formInline.image" />
            </div>
            <div class="upLoad acea-row row-center-wrapper" v-else>
              <i class="el-icon-user"></i>
            </div>
          </div>
        </el-form-item>
      </el-form>
      <div class="acea-row row-right mt20">
        <el-button @click="cancel('formInline')">取消</el-button>
        <el-button type="primary" @click="putSend('formInline')">提交</el-button>
      </div>
    </el-dialog>
    <el-dialog :visible.sync="customerShow" title="请选择商城用户" :show-close="true" width="1000px">
      <customerInfo v-if="customerShow" @imageObject="imageObject"></customerInfo>
    </el-dialog>
    <el-dialog :visible.sync="labelShow" title="请选择用户标签" width="540px" :show-close="true">
      <userLabel
        v-if="labelShow"
        :uid="labelActive.uid"
        :only_get="!labelActive.uid"
        @close="labelClose"
        @activeData="activeData"
        @onceGetList="onceGetList"
      ></userLabel>
    </el-dialog>
    <el-drawer :visible.sync="modals" :wrapperClosable="false" size="720" title="用户信息填写">
      <userEdit ref="userEdit" v-if="modals" :userData="userData"></userEdit>
      <div class="acea-row row-center">
        <el-button @click="modals = false">取消</el-button>
        <el-button type="primary" @click="setUser">提交</el-button>
      </div>
    </el-drawer>
    <!-- 用户标签 -->
    <el-dialog
      :visible.sync="selectLabelShow"
      title="请选择用户标签"
      width="540px"
      :show-close="true"
      :close-on-click-modal="false"
    >
      <userLabel
        v-if="selectLabelShow"
        :uid="0"
        ref="userLabel"
        :only_get="true"
        @activeData="activeSelectData"
        @close="labelClose"
      ></userLabel>
    </el-dialog>
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
import customerInfo from '@/components/customerInfo';
import { cityList } from '@/api/app';

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
      pickerOptions: this.$timeOptions,
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
      addresData: [],
      isShowSend: true,
      modal13: false,
      maxCols: 4,
      scrollerHeight: '600',
      contentTop: '130',
      contentWidth: '98%',
      grid: {
        xl: 6,
        lg: 6,
        md: 8,
        sm: 12,
        xs: 24,
      },
      grid2: {
        xl: 8,
        lg: 8,
        md: 8,
        sm: 12,
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
      selectedIds: [], //选中合并项的id
      ids: [],
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
  },
  created() {
    this.getList();
    this.getCityList();
  },
  mounted() {
    this.userGroup();
    this.levelLists();
    // this.groupLists();
  },
  methods: {
    getCityList() {
      cityList().then((res) => {
        this.addresData = res.data;
      });
    },
    setUser() {
      let data = this.$refs.userEdit.formItem;
      let ids = [];
      this.$refs.userEdit.dataLabel.map((i) => {
        ids.push(i.id);
      });
      data.label_id = ids;
      // if (!data.real_name) return this.$message.warning("请输入真实姓名");
      // if (!data.phone) return this.$message.warning("请输入手机号");
      // if (!data.pwd) return this.$message.warning("请输入密码");
      // if (!data.true_pwd) return this.$message.warning("请输入确认密码");
      if (data.uid) {
        editUser(data)
          .then((res) => {
            this.modals = false;
            this.$message.success(res.msg);
            this.getList();
          })
          .catch((err) => {
            this.$message.error(err);
          });
      } else {
        setUser(data)
          .then((res) => {
            this.modals = false;
            this.$message.success(res.msg);
            this.getList();
          })
          .catch((err) => {
            this.$message.error(err.msg);
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
            return this.$message.error('请上传用户');
          }
          agentSpreadApi(this.formInline)
            .then((res) => {
              this.promoterShow = false;
              this.$message.success(res.msg);
              this.getList();
              this.$refs[name].resetFields();
            })
            .catch((res) => {
              this.$message.error(res.msg);
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
      //     this.$message.error(res.msg);
      //   });
    },
    synchro() {
      userSynchro()
        .then((res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    isSel(row) {
      return !!!row.is_del;
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
          this.$message.error(res.msg);
        });
    },
    onClickTab() {
      this.userFrom.page = 1;
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
        this.$message.warning('请选择要设置分组的用户');
      } else {
        let uids = { uids: this.ids };
        this.$modalForm(userSetGroup(uids)).then(() => {
          this.ids = [];
          this.selectedIds = [];
          this.getList();
        });
      }
    },
    // 批量设置标签；
    setLabel() {
      if (this.ids.length === 0) {
        this.$message.warning('请选择要设置标签的用户');
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
      this.selectLabelShow = false;
      this.selectDataLabel = data || [];
      if (this.selectDataLabel.length) {
        let activeIds = [];
        this.selectDataLabel.map((item) => {
          activeIds.push(item.id);
        });
        this.userFrom.label_id = activeIds.join(',');
      } else {
        this.userFrom.label_id = '';
      }
    },
    handleClose(tag) {
      let i = this.selectDataLabel.findIndex((item) => item.id === tag.id);
      if (i !== -1) {
        this.selectDataLabel.splice(i, 1);
      }
      this.$nextTick(() => {
        if (this.selectDataLabel.length) {
          let activeIds = [];
          this.selectDataLabel.map((item) => {
            activeIds.push(item.id);
          });
          this.userFrom.label_id = activeIds.join(',');
        } else {
          this.userFrom.label_id = '';
        }
      });
      // this.userSearchs();
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
        this.$message.success(res.msg);
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
    handleChange(selectedData) {
      this.selectedData = selectedData.map((o) => o.label);
      this.userFrom.province = this.selectedData[0];
      this.userFrom.city = this.selectedData[1];
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.userFrom.user_time = this.timeVal ? this.timeVal.join('-') : '';
    },
    userDetail(row) {
      this.$refs.userDetails.modals = true;
      this.$refs.userDetails.getDetails(row.uid);
    },
    // 操作
    changeMenu(row, name, index) {
      let uid = [];
      uid.push(row.uid);
      let uids = { uids: uid };
      switch (name) {
        case '1':
          this.edit(row);
          break;
        case '2':
          this.getOtherFrom(row.uid);
          break;
        case '3':
          this.giveLevelTime(row.uid);
          break;
        case '4':
          this.del(row, '清除 【 ' + this.tenText(row.nickname) + ' 】的会员等级', index, 'user');
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
          this.del(row, '解除【 ' + this.tenText(row.nickname) + ' 】的上级推广人', index, 'tuiguang');
      }
    },
    tenText(str) {
      if (str.length > 10) {
        //如果字符长度超过10，后面的字符就变成...可自行调整长度和代替字符
        str = str.substr(0, 10) + '...'; //截取从第一个字符开始，往后取10个字符，剩余的用...代替
      }
      return str;
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
      this.formInline = {
        uid: 0,
        spread_uid: 0,
        image: '',
      };
    },
    // 赠送会员等级
    giveLevel(id) {
      this.$modalForm(giveLevelApi(id)).then(() => this.getList(1));

      // giveLevelApi(id)
      //   .then(async (res) => {
      //     if (res.data.status === false) {
      //       return this.$authLapse(res.data);
      //     }

      //     this.FromData = res.data;
      //     this.$refs.edits.modals = true;
      //   })
      //   .catch((res) => {
      //     this.$message.error(res.msg);
      //   });
    },
    // 赠送会员等级
    giveLevelTime(id) {
      this.$modalForm(giveLevelTimeApi(id)).then(() => this.getList(1));

      // giveLevelTimeApi(id)
      //   .then(async (res) => {
      //     if (res.data.status === false) {
      //       return this.$authLapse(res.data);
      //     }
      //     this.FromData = res.data;
      //     this.$refs.edits.modals = true;
      //   })
      //   .catch((res) => {
      //     this.$message.error(res.msg);
      //   });
    },
    // 删除
    del(row, tit, num, name) {
      let delfromData = {
        title: tit,
        num: num,
        url: name === 'user' ? `user/del_level/${row.uid}` : `agent/stair/delete_spread/${row.uid}`,
        method: name === 'user' ? 'DELETE' : 'PUT',
        ids: '',
        width: 600,
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 清除会员删除成功
    submitModel() {
      this.getList();
    },
    // 会员列表
    getList() {
      // if (this.selectDataLabel.length) {
      //   let activeIds = [];
      //   this.selectDataLabel.forEach((item) => {
      //     activeIds.push(item.id);
      //   });
      //   this.userFrom.label_id = activeIds.join(',');
      // }
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
          this.$message.error(res.msg);
        });
    },
    // 用户导出
    async exportList() {
      if (this.ids.length) {
        this.userFrom.ids = this.ids;
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
    pageChange() {
      this.selectionList = [];
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
          this.$message.error(res.msg);
        });
    },
    // 获取积分余额表单
    getOtherFrom(id) {
      this.$modalForm(editOtherApi(id)).then(() => this.getList(1));
    },
    // 修改状态
    onchangeIsShow(row) {
      let data = {
        id: row.uid,
        status: row.status,
      };
      isShowApi(data)
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 点击发送优惠券
    onSend() {
      if (this.ids.length === 0) {
        this.$message.warning('请选择要发送优惠券的用户');
      } else {
        this.$refs.sends.modals = true;
        this.$refs.sends.getList();
      }
    },
    // 发送图文消息
    onSendPic() {
      if (this.ids.length === 0) {
        this.$message.warning('请选择要发送图文消息的用户');
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
    sortChanged(e, props, order) {
      this.userFrom[e.prop] = e.order;
      this.getList();
    },
    //全选和取消全选时触发
    handleSelectAll(selection) {
      let ids = [];
      selection.map((e) => {
        ids.push(e.uid);
      });
      this.selectedIds = ids;
      this.$nextTick(() => {
        //确保dom加载完毕
        this.setChecked();
      });
    },
    //  选中某一行
    handleSelectRow(selection, row) {
      let ids = [];
      selection.map((e) => {
        ids.push(e.uid);
      });
      this.selectedIds = ids;
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

<style scoped lang="scss">
::v-deep .el-tabs__item {
  height: 54px !important;
  line-height: 54px !important;
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
    font-size: 24px;
    font-weight: 500;
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
  ::v-deep .ivu-form-item-content {
    margin-left: 0px !important;
  }
}

.userAlert {
  margin-top: 20px;
}

.userI {
  color: var(--prev-color-primary);
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
  ::v-deep .ivu-modal-body {
    padding: 0 16px 16px 16px !important;
  }
}

.vipName {
  color: #dab176;
}

.listbox {
  ::v-deep .ivu-divider-horizontal {
    margin: 0 !important;
  }
}

.labelInput {
  width: 250px;
  border: 1px solid #dcdee2;
  padding: 0 15px;
  border-radius: 5px;
  min-height: 30px;
  cursor: pointer;
  font-size: 12px;
  .span {
    color: #c5c8ce;
  }

  .ivu-icon-ios-arrow-down {
    font-size: 14px;
    color: #808695;
  }
}

.demo-drawer-footer {
  width: 100%;
  position: absolute;
  bottom: 0;
  left: 0;
  border-top: 1px solid #e8e8e8;
  padding: 10px 16px;
  text-align: right;
  background: #fff;
}
.search-form {
  display: flex;
  justify-content: space-between;
  .search-form-box {
    display: flex;
    flex-wrap: wrap;
    flex: 1;
  }
}
.search-form-sub {
  display: flex;
}
</style>
