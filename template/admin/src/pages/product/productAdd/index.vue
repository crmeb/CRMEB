<template>
  <div class="" id="shopp-manager" v-loading="spinShow">
    <pages-header
      ref="pageHeader"
      :title="$route.params.id ? '编辑商品' : '添加商品'"
      :backUrl="$routeProStr + '/product/product_list'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16" :body-style="{ padding: '0px 20px' }">
      <el-tabs v-model="currentTab">
        <el-tab-pane v-for="(item, index) in headTab" :key="index" :label="item.tit" :name="item.name"></el-tab-pane>
      </el-tabs>
      <el-form
        class="formValidate mt20"
        ref="formValidate"
        :rules="ruleValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <!-- 基础信息-->
        <basic-info
          v-show="currentTab === '1'"
          :isCai="type"
          :formValidate="formValidate"
          :goodsType="goodsType"
          :treeSelect="treeSelect"
          :tileLabelList="tileLabelList"
          :progress="progress"
          :upload="upload"
          :videoIng="videoIng"
          @virtualbtn="virtualbtn"
          @handleDragStart="handleDragStart"
          @handleDragOver="handleDragOver"
          @handleDragEnter="handleDragEnter"
          @handleDragEnd="handleDragEnd"
          @handleRemove="handleRemove"
          @modalPicTap="modalPicTap"
          @addVideo="addVideo"
          @delVideo="delVideo"
          @addCate="addCate"
          @addGoodsTag="addGoodsTag"
        ></basic-info>

        <!-- 规格库存-->
        <spec-stock
          ref="specStock"
          v-show="currentTab === '2'"
          :formValidate="formValidate"
          :ruleList="ruleList"
          :attrs="attrs"
          :manyFormValidate="manyFormValidate"
          :oneFormValidate="oneFormValidate"
          :tableKey="tableKey"
          :oneFormBatch="oneFormBatch"
          :formDynamic="formDynamic"
          :canSel="canSel"
          @changeSpec="changeSpec"
          @confirm="confirm"
          @onMoveSpec="onMoveSpec"
          @changeCurrentIndex="changeCurrentIndex"
          @handleRemoveRole="handleRemoveRole"
          @attrChangeValue="attrChangeValue"
          @handleFocus="handleFocus"
          @addPic="addPic"
          @handleRemove2="handleRemove2"
          @attrDetailChangeValue="attrDetailChangeValue"
          @handleBlur="handleBlur"
          @handleSelImg="handleSelImg"
          @handleRemoveImg="handleRemoveImg"
          @handleShowPop="handleShowPop"
          @createAttr="createAttr"
          @handleAddRole="handleAddRole"
          @handleSaveAsTemplate="handleSaveAsTemplate"
          @batchAdd="batchAdd"
          @batchDel="batchDel"
          @modalPicTap="modalPicTap"
          @changeDefaultSelect="changeDefaultSelect"
          @changeDefaultShow="changeDefaultShow"
          @addGoodsCoupon="addGoodsCoupon"
          @see="see"
          @addVirtual="addVirtual"
        ></spec-stock>

        <!-- 商品详情-->
        <product-detail
          v-show="currentTab === '3'"
          :contents="contents"
          :content="content"
          @getEditorContent="getEditorContent"
        ></product-detail>

        <!-- 物流设置-->
        <logistics-setting
          v-show="headTab.length === 7 ? currentTab === '4' : false"
          :formValidate="formValidate"
          :templateList="templateList"
          @logisticsBtn="logisticsBtn"
          @addTemp="addTemp"
        ></logistics-setting>

        <!-- 会员价/佣金 -->
        <price-commission
          v-show="headTab.length === 7 ? currentTab === '5' : currentTab === '4'"
          :formValidate="formValidate"
          :oneFormValidate="oneFormValidate"
          :manyFormValidate="manyFormValidate"
          :columnsInstall="columnsInstall"
          :columnsInstal2="columnsInstal2"
          :manyBrokerage.sync="manyBrokerage"
          :manyBrokerageTwo.sync="manyBrokerageTwo"
          :manyVipPrice.sync="manyVipPrice"
          :manyVipDiscount.sync="manyVipDiscount"
          @checkAllGroupChange="checkAllGroupChange"
          @changeVipPrice="changeVipPrice"
          @changeDiscount="changeDiscount"
          @brokerageSetUp="brokerageSetUp"
        ></price-commission>

        <!-- 营销设置-->
        <marketing-setting
          v-show="headTab.length === 7 ? currentTab === '6' : currentTab === '5'"
          :formValidate="formValidate"
          :couponName="couponName"
          :dataLabel="dataLabel"
          :activity="activity"
          @handleClose="handleClose"
          @addCoupon="addCoupon"
          @openLabel="openLabel"
          @closeLabel="closeLabel"
          @addLabel="addLabel"
          @onchangeTime="onchangeTime"
          @handleRemoveRecommend="handleRemoveRecommend"
          @changeGoods="changeGoods"
        ></marketing-setting>

        <!-- 其他设置-->
        <other-setting
          v-show="headTab.length === 7 ? currentTab === '7' : currentTab === '6'"
          :formValidate="formValidate"
          :customBtn.sync="customBtn"
          :paramsType="paramsType"
          :paramsTypeList="paramsTypeList"
          :protectionList="protectionList"
          :CustomList="CustomList"
          @modalPicTap="modalPicTap"
          @changeParamsType="changeParamsType"
          @deleteRow="deleteRow"
          @handleAddParams="handleAddParams"
          @addProtection="addProtection"
          @customMessBtn="customMessBtn"
          @delcustom="delcustom"
          @addcustom="addcustom"
        ></other-setting>

        <el-form-item>
          <el-button v-if="currentTab !== '1'" v-db-click @click="upTab">上一步</el-button>
          <el-button
            class="submission"
            v-if="currentTab !== '7' && formValidate.virtual_type == 0"
            v-db-click
            @click="downTab"
            >下一步</el-button
          >
          <el-button
            class="submission"
            v-if="currentTab !== '6' && formValidate.virtual_type != 0"
            v-db-click
            @click="downTab"
            >下一步</el-button
          >
          <el-button
            type="primary"
            class="submission"
            v-db-click
            @click="handleSubmit('formValidate')"
            v-if="$route.params.id || currentTab !== '1'"
            >保存</el-button
          >
        </el-form-item>
      </el-form>
      <el-dialog :visible.sync="modalPic" width="950px" scrollable title="上传商品图" :close-on-click-modal="false">
        <uploadPictures
          :isChoice="isChoice"
          @getPic="getPic"
          @getPicD="getPicD"
          :gridBtn="gridBtn"
          :gridPic="gridPic"
          v-if="modalPic"
        ></uploadPictures>
      </el-dialog>
      <el-dialog
        :visible.sync="addVirtualModel"
        width="720px"
        title="添加卡密"
        :show-close="true"
        :close-on-click-modal="false"
        @closed="initVirtualData"
      >
        <div class="trip"></div>
        <div class="type-radio">
          <el-form label-width="85px">
            <el-form-item label="卡密类型：">
              <el-radio-group v-model="disk_type" size="large">
                <el-radio :label="1">固定卡密</el-radio>
                <el-radio :label="2">一次性卡密</el-radio>
              </el-radio-group>
              <div v-if="disk_type == 1">
                <div class="stock-disk">
                  <el-input v-model="disk_info" size="large" type="textarea" :rows="4" placeholder="填写卡密信息" />
                </div>
                <div class="stock-input">
                  <!-- <el-input type="number" v-model="stock" size="large" :min='0' placeholder="填写库存数量">
                    <span slot="append">件</span>
                  </el-input> -->
                  <el-input-number :controls="false" :max="100000" :min="1" :step="1" :precision="0" v-model="stock" />
                  <span class="pl10">件</span>
                </div>
              </div>
              <div class="scroll-virtual" v-if="disk_type == 2">
                <div class="virtual-data mb10" v-for="(item, index) in virtualList" :key="index">
                  <span class="mr10 virtual-title">卡号{{ index + 1 }}：</span>
                  <el-input
                    class="mr10"
                    type="text"
                    v-model.trim="item.key"
                    style="width: 150px"
                    placeholder="请输入卡号(非必填)"
                  ></el-input>
                  <span class="mr10 virtual-title">卡密{{ index + 1 }}：</span>
                  <el-input
                    class="mr10"
                    type="text"
                    v-model.trim="item.value"
                    style="width: 150px"
                    placeholder="请输入卡密"
                  ></el-input>
                  <span class="deteal-btn" v-db-click @click="removeVirtual(index)">删除</span>
                </div>
              </div>
              <div class="add-more" v-if="disk_type == 2">
                <el-button class="h-33" type="primary" v-db-click @click="handleAdd">新增</el-button>
                <el-upload
                  class="ml10"
                  :action="cardUrl"
                  :data="uploadData"
                  :headers="header"
                  :on-success="upFile"
                  :before-upload="beforeUpload"
                >
                  <el-button>导入卡密</el-button>
                </el-upload>
              </div>
            </el-form-item>
          </el-form>
        </div>
        <span slot="footer" class="dialog-footer">
          <el-button v-db-click @click="closeVirtual">取 消</el-button>
          <el-button type="primary" v-db-click @click="upVirtual">确 定</el-button>
        </span>
      </el-dialog>
    </el-card>
    <freightTemplate
      :template="template"
      v-on:changeTemplate="changeTemplate"
      @addSuccess="productGetTemplate"
      ref="templates"
    ></freightTemplate>
    <add-attr ref="addattr" @getList="userSearchs"></add-attr>
    <coupon-list
      ref="couponTemplates"
      @nameId="nameId"
      :couponids="formValidate.coupon_ids"
      :updateIds="updateIds"
      :updateName="updateName"
    ></coupon-list>
    <coupon-list ref="goodsCoupon" many="one" :luckDraw="true" @getCouponId="goodsCouponId"></coupon-list>
    <!-- 生成淘宝京东表单-->
    <el-dialog
      :visible.sync="modals"
      @closed="cancel"
      class="Box"
      title="复制淘宝、天猫、京东、苏宁、1688"
      :close-on-click-modal="false"
      width="720px"
    >
      <tao-bao ref="taobaos" v-if="modals" @on-close="onClose"></tao-bao>
    </el-dialog>
    <el-dialog :visible.sync="goods_modals" title="商品列表" footerHide class="paymentFooter" scrollable width="1000px">
      <goods-list v-if="goods_modals" ref="goodslist" :ischeckbox="true" @getProductId="getProductId"></goods-list>
    </el-dialog>
    <!-- 用户标签 -->
    <el-dialog
      :visible.sync="labelShow"
      title="请选择用户标签"
      :show-close="true"
      width="540px"
      :close-on-click-modal="false"
    >
      <userLabel ref="userLabel" @activeData="activeData" @close="labelClose"></userLabel>
    </el-dialog>
    <!-- 商品标签 -->
    <el-dialog
      :visible.sync="tagShow"
      title="请选择商品标签"
      :show-close="true"
      width="540px"
      :close-on-click-modal="false"
    >
      <goodsLabel
        ref="goodsLabel"
        :defaultLabelList="labelList"
        @activeLabel="activeLabel"
        @close="labelClose"
      ></goodsLabel>
    </el-dialog>
  </div>
</template>

<script>
import userLabel from '@/components/labelList';
import useLabel from '@/components/goodsLabel/useLabel';
import goodsLabel from '@/components/goodsLabel';
import { mapState } from 'vuex';
import uploadPictures from '@/components/uploadPictures';
import freightTemplate from '@/components/freightTemplate';
import couponList from '@/components/couponList';
import addAttr from '../productAttr/addAttr';
import goodsList from '@/components/goodsList/index';
import taoBao from './taoBao';
import { userLabelAddApi } from '@/api/user';
import {
  productInfoApi,
  cascaderListApi,
  productAddApi,
  generateAttrApi,
  productGetRuleApi,
  productGetTemplateApi,
  productGetTempKeysApi,
  checkActivityApi,
  productCache,
  cacheDelete,
  uploadType,
  importCard,
  productCreateApi,
  getProductTypeConfig,
  ruleAddApi,
  paramListApi,
  paramInfoApi,
  productProtectionListApi,
  productLabelUseListApi,
} from '@/api/product';
import Setting from '@/setting';
import { getCookies } from '@/libs/util';
import { uploadByPieces } from '@/utils/upload'; //引入uploadByPieces方法
import { isFileUpload, isVideoUpload, arraysEqual } from '@/utils';
import checkArray from '@/libs/permission';
import {
  GoodsTableHead,
  VirtualTableHead,
  VirtualTableHead2,
  columns2,
  columns3,
  CustomList,
  RuleValidate,
} from './defaultData.js';
import BasicInfo from './components/BasicInfo.vue';
import SpecStock from './components/SpecStock.vue';
import ProductDetail from './components/ProductDetail.vue';
import LogisticsSetting from './components/LogisticsSetting.vue';
import PriceCommission from './components/PriceCommission.vue';
import MarketingSetting from './components/MarketingSetting.vue';
import OtherSetting from './components/OtherSetting.vue';
import { formatRichText } from '@/utils/editorImg';

export default {
  name: 'ProductAdd',
  components: {
    uploadPictures,
    freightTemplate,
    addAttr,
    couponList,
    taoBao,
    goodsList,
    userLabel,
    goodsLabel,
    useLabel,
    BasicInfo,
    SpecStock,
    ProductDetail,
    LogisticsSetting,
    PriceCommission,
    MarketingSetting,
    OtherSetting,
  },
  data() {
    return {
      labelShow: false,
      tagShow: false,
      dataLabel: [],
      headTab: [
        { tit: '基础信息', name: '1' },
        { tit: '规格库存', name: '2' },
        { tit: '商品详情', name: '3' },
        { tit: '物流设置', name: '4' },
        { tit: '会员价/佣金', name: '5' },
        { tit: '营销设置', name: '6' },
        { tit: '其他设置', name: '7' },
      ],
      virtual: [
        { tit: '普通商品', id: 0, tit2: '物流发货' },
        { tit: '卡密/网盘', id: 1, tit2: '自动发货' },
        { tit: '优惠券', id: 2, tit2: '自动发货' },
        { tit: '虚拟商品', id: 3, tit2: '虚拟发货' },
      ],
      seletVideo: 0, //选择视频类型
      customBtn: 0, //自定义留言开关
      content: '',
      contents: '',
      fileUrl: Setting.apiBaseURL + '/file/upload',
      fileUrl2: Setting.apiBaseURL + '/file/video_upload',
      cardUrl: Setting.apiBaseURL + '/file/upload/1',
      upload_type: '', //视频上传类型 1 本地上传 2 3 4 OSS上传
      uploadData: {}, // 上传参数
      header: {},
      type: 0,
      modals: false,
      goods_modals: false,
      spinShow: false,
      openSubimit: false,
      virtualList: [
        {
          key: '',
          value: '',
        },
      ],
      // 批量设置表格data
      oneFormBatch: [
        {
          pic: '',
          price: void 0,
          cost: void 0,
          ot_price: void 0,
          stock: void 0,
          bar_code: '',
          bar_code_number: '',
          weight: void 0,
          volume: void 0,
          virtual_list: [],
        },
      ],

      // 规格数据
      formDynamic: {
        attrsName: '',
        attrsVal: '',
      },
      disk_type: 1, //卡密类型
      tabIndex: 0,
      tabName: '',
      formDynamicNameData: [],
      isBtn: false,
      columns2: columns2,
      columns3: columns3,
      columns: [],
      columnsInstall: [],
      columnsInstal2: [],
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
      //自定义留言下拉选择
      CustomList: CustomList,
      //自定义留言内容
      currentIndex: 0,

      formValidate: {
        disk_info: '', //卡密类型
        logistics: ['1'], //选择物流方式
        freight: 2, //运费设置
        postage: 0, //设置运费金额
        recommend: [], //商品推荐
        presale_day: 1, //预售发货时间-结束
        presale: false, //预售商品开关
        is_limit: false,
        limit_type: 0,
        limit_num: 0,
        vip_product: false, //付费会员专属开关
        custom_form: [], //自定义留言
        store_name: '',
        cate_id: [],
        label_id: [],
        keyword: '',
        unit_name: '',
        store_info: '',
        image: '',
        recommend_image: '',
        slider_image: [],
        description: '',
        ficti: 0,
        give_integral: 0,
        sort: 0,
        is_show: 1,
        is_gift: 0, // 开启送礼品
        gift_price: 0,
        is_hot: 0,
        is_benefit: 0,
        is_best: 0,
        is_new: 0,
        is_good: 0,
        is_postage: 0,
        is_sub: [],
        recommend_list: [],
        params_list: [], //商品参数
        virtual_type: 0,
        // is_sub: 0,
        id: 0,
        spec_type: 0,
        is_virtual: 0,
        video_link: '',
        // postage: 0,
        temp_id: '',
        attrs: [],
        items: [
          {
            pic: '',
            price: 0,
            cost: 0,
            ot_price: 0,
            stock: 0,
            bar_code: '',
            bar_code_number: '',
          },
        ],
        activity: ['默认', '秒杀', '砍价', '拼团'],
        couponName: [],
        header: [],
        selectRule: '',
        coupon_ids: [],
        command_word: '',
        min_qty: 1,
        label_list: [],
        protection_list: [],
      },
      ruleList: [],
      templateList: [],
      createBnt: true,
      showIput: false,
      manyFormValidate: [],
      // 单规格表格data
      oneFormValidate: [
        {
          pic: '',
          price: 0,
          cost: 0,
          ot_price: 0,
          stock: 0,
          bar_code: '',
          bar_code_number: '',
          weight: 0,
          volume: 0,
          brokerage: 0,
          brokerage_two: 0,
          vip_price: 0,
          virtual_list: [],
          coupon_id: 0,
        },
      ],
      images: [],
      imagesTable: '',
      currentTab: '1',
      isChoice: '',
      loading: false,
      modalPic: false,
      addVirtualModel: false,
      template: false,
      uploadList: [],
      treeSelect: [],
      picTit: '',
      tableIndex: 0,
      ruleValidate: RuleValidate,
      manyBrokerage: undefined,
      manyBrokerageTwo: undefined,
      manyVipPrice: undefined,
      manyVipDiscount: undefined,
      upload: {
        videoIng: false, // 是否显示进度条；
      },
      videoIng: false, // 是否显示进度条；
      progress: 0, // 进度条默认0
      stock: 0,
      disk_info: '',
      videoLink: '',
      attrs: [],
      activity: { 默认: 'red', 秒杀: 'blue', 砍价: 'green', 拼团: 'yellow' },
      couponName: [],
      updateIds: [],
      updateName: [],
      couponIds: '',
      couponNames: [],
      rakeBack: [
        {
          title: '一级返佣(元)',
          slot: 'brokerage',
          align: 'center',
          width: 95,
        },
        {
          title: '二级返佣(元)',
          slot: 'brokerage_two',
          align: 'center',
          width: 95,
        },
      ],
      member: [
        {
          title: '会员价',
          slot: 'vip_price',
          align: 'center',
          width: 95,
        },
        {
          title: '会员折扣',
          slot: 'vip_proportion',
          align: 'center',
          width: 95,
        },
      ],
      columnsInstalM: [],
      moveIndex: '',
      addValue: '',
      visible: false,
      typeConfig: [],
      goodsType: [],
      paramsTypeList: [],
      paramsType: null,
      canSel: true, // 规格图片添加判断
      changeAttrValue: '', //修改的规格值
      tableKey: 0,
      protectionList: [], // 服务保障
      labelList: [],
      tileLabelList: [],
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '120px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
    labelBottom() {
      return this.isMobile ? undefined : '15px';
    },
  },
  watch: {
    typeConfig(val) {
      if (val.length) {
        // 对virtual中的id等于val中的id的
        this.goodsType = this.virtual.filter((item) => {
          return val.includes(item.id + '');
        });
      } else {
        this.goodsType = this.virtual;
      }
    },
  },
  beforeRouteUpdate(to, from, next) {
    this.bus.$emit('onTagsViewRefreshRouterView', this.$route.path);
    next();
  },
  created() {
    this.columns = this.columns2.slice(0, 8);
    this.getToken();
  },
  async mounted() {
    if (this.$route.params.id !== '0' && this.$route.params.id) {
      await this.getInfo();
    } else if (this.$route.params.id === '0') {
      this.getProductCache();
    } else {
      this.getproductLabelUseListApi();
    }
    if (this.$route.query.type) {
      this.modals = true;
      this.type = this.$route.query.type;
    } else {
      this.type = 0;
    }
    this.goodsCategory();
    this.productGetRule();
    this.productGetTemplate();
    this.paramsGetTemplate();
    this.uploadType();
    this.productConfig();
    this.watchActivity();
    this.getProtectionList();
  },
  methods: {
    getProductCache() {
      productCache()
        .then((res) => {
          let data = res.data.info;
          this.getproductLabelUseListApi();

          if (!Array.isArray(data)) {
            let cate_id = data.cate_id.map(Number);
            let label_id = data.label_id.map(Number);
            this.attrs = data.items || [];
            let ids = [];
            if (data.coupons) {
              data.coupons.map((item) => {
                ids.push(item.id);
              });
              this.couponName = data.coupons;
            }

            this.formValidate = data;
            this.dataLabel = data.label_id;
            this.formValidate.coupon_ids = ids;
            this.updateIds = ids;
            this.updateName = data.coupons;
            this.formValidate.cate_id = cate_id;
            this.oneFormValidate = data.attrs;
            this.generateHeader(this.attrs);
            this.formValidate.logistics = data.logistics || ['1'];
            this.formValidate.header = [];
            this.manyFormValidate = data.attrs;
            this.spec_type = data.spec_type;
            this.formValidate.is_virtual = data.is_virtual;
            this.formValidate.custom_form = data.custom_form || [];
            if (this.formValidate.custom_form.length != 0) {
              this.customBtn = 1;
            }
            this.attrs.map((item) => {
              if (item.add_pic) this.canSel = false;
            });
            this.virtualbtn(data.virtual_type, 1);
            if (data.spec_type === 0) {
              this.manyFormValidate = [];
            } else {
              this.createBnt = true;
              this.oneFormValidate = [
                {
                  pic: data.image,
                  price: 0,
                  cost: 0,
                  ot_price: 0,
                  stock: 0,
                  bar_code: '',
                  bar_code_number: '',
                  weight: 0,
                  volume: 0,
                  brokerage: 0,
                  brokerage_two: 0,
                  vip_price: 0,
                  virtual_list: [],
                  coupon_id: 0,
                },
              ];
            }
            this.watchActivity();
            this.spinShow = false;
          }
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    getProtectionList() {
      productProtectionListApi({ page: 0, limit: 0, status: 1 }).then((res) => {
        this.protectionList = res.data.list;
      });
    },
    getproductLabelUseListApi() {
      productLabelUseListApi().then((res) => {
        // 合并数组中所有的list
        this.tileLabelList = res.data.flatMap((item) => item.list);
        let labelList = res.data;
        if (this.formValidate.label_list.length) {
          this.formValidate.label_list.map((el) => {
            labelList.map((re) => {
              re.list.map((label) => {
                if (label.id === el) {
                  label.active = true;
                } else {
                  label.active = false;
                }
              });
            });
          });
        } else {
          labelList.map((el) => {
            el.list.map((label) => {
              label.active = false;
            });
          });
        }
        this.labelList = labelList;
      });
    },
    addProtection() {
      this.$router.push({ path: this.$routeProStr + '/product/protection/list' });
    },
    productConfig() {
      getProductTypeConfig().then((res) => {
        this.typeConfig = res.data;
      });
    },
    beforeUpload(file) {
      return isFileUpload(file);
    },
    // 分片上传
    videoSaveToUrl(file) {
      if (isVideoUpload(file)) {
        uploadByPieces({
          file: file, // 视频实体
          pieceSize: 3, // 分片大小
          success: (data) => {
            this.formValidate.video_link = data.file_path;
            this.progress = 100;
          },
          error: (e) => {
            this.$message.error(e.msg);
          },
          uploading: (chunk, allChunk) => {
            this.videoIng = true;
            let st = Math.floor((chunk / allChunk) * 100);
            this.progress = st;
          },
        });
      }
      return false;
    },
    // 类型选择/填入内容判断
    virtualbtn(index, type) {
      if (type != 1) {
        if (this.$route.params.id) return this.$message.error('编辑商品不支持切换商品类型');
        this.formValidate.is_sub = [];
        let id = this.$route.params.id;
        if (id) {
          checkActivityApi(id)
            .then((res) => {})
            .catch((res) => {
              this.formValidate.spec_type = this.spec_type;
              this.$message.error(res.msg);
            });
        } else {
          if (this.formValidate.spec_type == 1) {
            this.generate(1);
          }
        }
      }
      // 定义基础商品和虚拟商品的标签页配置
      const baseHeadTabs = [
        { tit: '基础信息', name: '1' },
        { tit: '规格库存', name: '2' },
        { tit: '商品详情', name: '3' },
        { tit: '物流设置', name: '4' },
        { tit: '会员价/佣金', name: '5' },
        { tit: '营销设置', name: '6' },
        { tit: '其他设置', name: '7' },
      ];
      const virtualHeadTabs = [
        { tit: '基础信息', name: '1' },
        { tit: '规格库存', name: '2' },
        { tit: '商品详情', name: '3' },
        { tit: '会员价/佣金', name: '4' },
        { tit: '营销设置', name: '5' },
        { tit: '其他设置', name: '6' },
      ];

      switch (index) {
        case 0: // 普通商品
          this.formValidate.virtual_type = 0;
          this.formValidate.is_virtual = 0;
          this.headTab = baseHeadTabs;
          break;

        case 1: // 卡密/网盘商品
          this.formValidate.virtual_type = 1;
          this.formValidate.postage = 0;
          this.formValidate.is_virtual = 1;
          this.headTab = virtualHeadTabs;
          break;

        case 2: // 优惠券商品
          this.formValidate.virtual_type = 2;
          this.formValidate.is_virtual = 1;
          this.headTab = virtualHeadTabs;
          break;

        case 3: // 虚拟商品
          this.formValidate.virtual_type = 3;
          this.formValidate.is_virtual = 1;
          this.headTab = virtualHeadTabs;
          break;
      }
    },
    // 新增分类
    addCate() {
      this.$modalForm(productCreateApi()).then(() => this.goodsCategory());
    },
    // 物流方式选择
    logisticsBtn(e) {
      this.formValidate.logistics = e;
    },
    // 新增标签
    addLabel() {
      this.$modalForm(userLabelAddApi(0)).then(() => this.userLabel());
    },
    // 选择标签
    addGoodsTag() {
      this.tagShow = true;
    },
    // 自定义留言 开启关闭
    customMessBtn(e) {
      if (!e) {
        this.formValidate.custom_form = [];
      }
      this.customBtn = e;
    },
    // 自定义留言 新增表单
    addcustom() {
      if (this.formValidate.custom_form.length > 9) {
        this.$message.warning('最多添加10条');
      } else {
        this.formValidate.custom_form.push({
          title: '',
          label: 'text',
          value: '',
          status: false,
        });
      }
    },
    // 删除
    delcustom(index) {
      this.formValidate.custom_form.splice(index, 1);
    },
    // 预售具体日期
    onchangeTime(e) {
      this.formValidate.presale_time = e;
    },
    // 商品详情
    getEditorContent(data) {
      this.content = data;
    },
    cancel() {
      this.modals = false;
    },
    // 上传头部token
    getToken() {
      this.header['Authori-zation'] = 'Bearer ' + getCookies('token');
    },
    // 导入卡密
    upFile(res) {
      importCard({ file: res.data.src }).then((res) => {
        this.virtualList = this.virtualList.concat(res.data);
      });
    },
    //获取视频上传类型
    uploadType() {
      uploadType().then((res) => {
        this.upload_type = res.data.upload_type;
      });
    },
    // 初始化数据展示
    infoData(data, isCopy) {
      let cate_id = data.cate_id.map(Number);
      let label_id = data.label_id.map(Number);
      this.attrs = data.items || [];
      let ids = [];
      data.coupons.map((item) => {
        ids.push(item.id);
      });
      this.formValidate = data;
      this.seletVideo = data.seletVideo;
      this.contents = data.description;
      this.couponName = data.coupons;
      this.formValidate.coupon_ids = ids;
      this.updateIds = ids;
      this.dataLabel = data.label_id;
      this.updateName = data.coupons;
      this.virtualbtn(data.virtual_type, 1);
      this.formValidate.logistics = data.logistics || ['1'];
      this.formValidate.custom_form = data.custom_form || [];
      if (this.formValidate.custom_form.length != 0) {
        this.customBtn = 1;
      }
      this.formValidate.cate_id = cate_id;
      if (data.attr) {
        this.oneFormValidate = [data.attr];
        this.oneFormValidate[0].vip_proportion = (
          (this.oneFormValidate[0].vip_price / this.oneFormValidate[0].price) *
          100
        ).toFixed(2);
      }
      this.getproductLabelUseListApi();

      this.formValidate.header = [];
      this.spec_type = data.spec_type;
      this.formValidate.spec_type = this.spec_type;
      this.formValidate.is_virtual = data.is_virtual;
      this.attrs.map((item) => {
        if (item.add_pic) this.canSel = false;
      });
      if (data.spec_type === 0) {
        this.manyFormValidate = [];
      } else {
        this.createBnt = true;
        this.oneFormValidate = [
          {
            pic: '',
            price: 0,
            cost: 0,
            ot_price: 0,
            stock: 0,
            bar_code: '',
            bar_code_number: '',
            weight: 0,
            volume: 0,
            brokerage: 0,
            brokerage_two: 0,
            vip_price: 0,
            virtual_list: [],
            coupon_id: 0,
          },
        ];

        this.generateHeader(this.attrs);
        this.manyFormValidate = [...this.oneFormBatch, ...data.attrs];
      }

      setTimeout((e) => {
        this.checkAllGroup(data.is_sub);
      }, 1000);
      this.watchActivity();
    },
    //关闭淘宝弹窗并生成数据；
    onClose(data) {
      this.modals = false;
      this.infoData(data, 1);
    },

    checkMove(evt) {
      this.moveIndex = evt.draggedContext.index;
    },
    end() {
      this.moveIndex = '';
      this.generate(1);
    },
    // 单独设置会员设置
    checkAllGroupChange(data) {
      this.checkAllGroup(data);
    },
    checkAllGroup(data) {
      let endLength = this.attrs.length + 3;
      if (this.formValidate.spec_type === 0) {
        if (data.length === 2) {
          this.columnsInstall = this.columns2.slice(0, endLength).concat(this.rakeBack).concat(this.member);
        } else if (data.indexOf(0) > -1) {
          this.columnsInstall = this.columns2.slice(0, endLength).concat(this.member);
        } else if (data.indexOf(1) > -1) {
          this.columnsInstall = this.columns2.slice(0, endLength).concat(this.rakeBack);
        } else {
          this.columnsInstall = this.columns2.slice(0, endLength);
        }
      } else {
        if (data.length === 2) {
          this.columnsInstal2 = this.columnsInstalM
            .slice(0, endLength + 1)
            .concat(this.rakeBack)
            .concat(this.member);
        } else if (data.indexOf(0) > -1) {
          this.columnsInstal2 = this.columnsInstalM.slice(0, endLength).concat(this.member);
        } else if (data.indexOf(1) > -1) {
          this.columnsInstal2 = this.columnsInstalM.slice(0, endLength).concat(this.rakeBack);
        } else {
          this.columnsInstal2 = this.columnsInstalM.slice(0, endLength);
        }
      }
    },
    // 添加优惠券
    addCoupon() {
      this.$refs.couponTemplates.isTemplate = true;
      this.$refs.couponTemplates.tableList();
    },
    // 规格中优惠券查看
    see(data, name, index) {
      this.tabName = name;
      this.tabIndex = index;

      if (this.formValidate.virtual_type === 1) {
        if (data.disk_info != '') {
          this.disk_type = 1;
          this.disk_info = data.disk_info;
          this.stock = data.stock;
        } else if (data.virtual_list.length) {
          this.disk_type = 2;
          this.virtualList = data.virtual_list;
        }
        this.addVirtualModel = true;
      } else {
        this.$refs.goodsCoupon.isTemplate = true;
        this.$refs.goodsCoupon.tableList(3);
      }
    },
    // 修改分佣比例
    changeDiscount(index, type = 'manyFormValidate') {
      // 根据分佣比例 vip_proportion 修改会员价 保留2位小数
      this[type][index].vip_price = (this[type][index].price * (this[type][index].vip_proportion / 100)).toFixed(2);
    },
    // 修改会员价
    changeVipPrice(index, type = 'manyFormValidate') {
      // 根据会员价计算出分佣比例
      this[type][index].vip_proportion = ((this[type][index].vip_price / this[type][index].price) * 100).toFixed(2);
    },
    // 添加优惠券
    addGoodsCoupon(index, name) {
      this.tabIndex = index;
      this.tabName = name;
      this.$refs.goodsCoupon.isTemplate = true;
      this.$refs.goodsCoupon.tableList(3);
    },
    addVirtual(index, name) {
      this.tabIndex = index;
      this.tabName = name;
      this.addVirtualModel = true;
    },
    // 提交卡密信息
    upVirtual() {
      if (this.disk_type == 2) {
        for (let i = 0; i < this.virtualList.length; i++) {
          const element = this.virtualList[i];
          if (!element.value) {
            this.$message.error('请输入所有卡密');
            return;
          }
        }
        this.$set(this[this.tabName][this.tabIndex], 'virtual_list', this.virtualList);
        this.$set(this[this.tabName][this.tabIndex], 'stock', this.virtualList.length);
        this.virtualList = [
          {
            key: '',
            value: '',
          },
        ];
        this.$set(this[this.tabName][this.tabIndex], 'disk_info', '');
      } else {
        if (!this.disk_info.length) {
          return this.$message.error('请填写卡密信息');
        }
        if (!this.stock) {
          return this.$message.error('请填写库存数量');
        }
        this.$set(this[this.tabName][this.tabIndex], 'stock', Number(this.stock));
        this.$set(this[this.tabName][this.tabIndex], 'stock', Number(this.stock));
        this.$set(this[this.tabName][this.tabIndex], 'disk_info', this.disk_info);
        this.$set(this[this.tabName][this.tabIndex], 'virtual_list', []);
      }
      this.addVirtualModel = false;
      this.closeVirtual();
    },
    //  初始化卡密数据信息
    closeVirtual() {
      this.addVirtualModel = false;
      this.virtualList = [
        {
          key: '',
          value: '',
        },
      ];
      this.disk_info = '';
      this.stock = 0;
    },
    //对象数组去重；
    uniqueArray(arr) {
      const seen = {};
      return arr.filter((item) => {
        const key = JSON.stringify(item); // 使用 JSON.stringify 生成唯一键
        if (seen[key]) {
          return false;
        } else {
          seen[key] = true;
          return true;
        }
      });
    },
    // 获取优惠券id数据
    nameId(id, names) {
      this.formValidate.coupon_ids = id;
      this.couponName = this.uniqueArray(names);
    },
    // 获取优惠券信息
    goodsCouponId(data) {
      this.$set(this[this.tabName][this.tabIndex], 'coupon_id', data.id);
      this.$set(this[this.tabName][this.tabIndex], 'coupon_name', data.title);
      this.$refs.goodsCoupon.isTemplate = false;
    },
    handleClose(name) {
      let index = this.couponName.indexOf(name);
      this.couponName.splice(index, 1);
      let couponIds = this.formValidate.coupon_ids;
      couponIds.splice(index, 1);
      this.updateIds = couponIds;
      this.updateName = this.couponName;
    },
    // 添加运费模板
    addTemp() {
      this.$refs.templates.isTemplate = true;
    },
    addVideo() {
      this.$videoModal((e) => {
        this.formValidate.video_link = e;
      });
    },
    // 删除视频；
    delVideo() {
      this.$set(this.formValidate, 'video_link', '');
      this.$set(this, 'progress', 0);
      this.videoIng = false;
      this.upload.videoIng = false;
    },
    zh_uploadFile() {
      if (this.seletVideo == 1) {
        this.formValidate.video_link = this.videoLink;
      } else {
        this.$refs.refid.click();
      }
    },
    // 上传视频
    zh_uploadFile_change(evfile) {
      let suffix = evfile.target.files[0].name.substr(evfile.target.files[0].name.indexOf('.'));
      if (suffix.indexOf('.mp4') === -1) {
        return this.$message.error('只能上传MP4文件');
      }
      let types = {
        key: evfile.target.files[0].name,
        contentType: evfile.target.files[0].type,
      };
      productGetTempKeysApi(types)
        .then((res) => {
          this.$videoCloud
            .videoUpload({
              type: res.data.type,
              evfile: evfile,
              res: res,
              uploading(status, progress) {
                this.upload.videoIng = status;
                if (res.status == 200) {
                  this.progress = 100;
                }
              },
            })
            .then((res) => {
              this.formValidate.video_link = res.url;
              this.$message.success('视频上传成功');
              this.upload.videoIng = false;
            })
            .catch((res) => {
              this.$message.error(res);
            });
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 上一页；
    upTab() {
      this.currentTab = (Number(this.currentTab) - 1).toString();
    },
    // 下一页；
    downTab() {
      this.currentTab = (Number(this.currentTab) + 1).toString();
    },
    // 属性弹窗回调函数；
    userSearchs() {
      this.productGetRule();
    },
    // 添加规则；
    addRule() {
      this.$refs.addattr.modal = true;
    },
    // 批量设置分佣；
    brokerageSetUp() {
      if (this.formValidate.is_sub.indexOf(1) > -1) {
        if (this.manyBrokerage <= 0 || this.manyBrokerageTwo <= 0) {
          return this.$message.error('请填写返佣金额后进行批量添加');
        }
      } else if (this.formValidate.is_sub.indexOf(0) > -1) {
        if (this.manyVipPrice <= 0) {
          return this.$message.error('请填写会员价后进行批量添加');
        }
      }
      if (this.formValidate.is_sub.length === 2) {
        if (this.manyBrokerage <= 0 || this.manyBrokerageTwo <= 0) {
          return this.$message.error('请填写完金额后进行批量添加');
        }
        if (this.manyVipPrice > 0 && this.manyVipDiscount > 0) {
          return this.$message.error('会员价和会员折扣只能二选一添加');
        }
      }
      for (let val of this.manyFormValidate) {
        this.manyBrokerage != undefined && this.$set(val, 'brokerage', this.manyBrokerage);
        this.manyBrokerageTwo != undefined && this.$set(val, 'brokerage_two', this.manyBrokerageTwo);
        if (this.manyVipPrice != undefined) {
          this.$set(val, 'vip_price', this.manyVipPrice);
          this.$set(val, 'vip_proportion', ((val.vip_price / val.price) * 100).toFixed(2));
        } else {
          this.$set(val, 'vip_proportion', this.manyVipDiscount);
          this.$set(val, 'vip_price', (val.price * (this.manyVipDiscount / 100)).toFixed(2));
        }
      }
    },
    // 批量设置会员价
    vipPriceSetUp() {
      if (this.manyVipPrice <= 0) {
        return this.$message.error('请填写会员价在进行批量添加');
      } else {
        for (let val of this.manyFormValidate) {
          this.$set(val, 'vip_price', this.manyVipPrice);
        }
      }
    },
    // 新增卡密
    handleAdd() {
      this.virtualList.push({
        key: '',
        value: '',
      });
    },
    // 初始化卡密信息
    initVirtualData(status) {
      this.virtualList = [
        {
          key: '',
          value: '',
        },
      ];
    },
    removeVirtual(index) {
      this.virtualList.splice(index, 1);
    },
    // 清空批量规格信息
    batchDel() {
      this.oneFormBatch = [
        {
          pic: '',
          price: void 0,
          cost: void 0,
          ot_price: void 0,
          stock: void 0,
          bar_code: '',
          bar_code_number: '',
          weight: void 0,
          volume: void 0,
          virtual_list: [],
        },
      ];
    },
    confirm(name) {
      this.createBnt = true;
      this.formValidate.selectRule = name;
      this.attrs = [];
      if (this.formValidate.selectRule.trim().length <= 0) {
        return this.$message.error('请选择属性');
      }
      this.ruleList.forEach((item, index) => {
        if (item.rule_name === this.formValidate.selectRule) {
          this.attrs = [...item.rule_value];
        }
      });
      this.canSel = true;
      this.generateAttr(this.attrs);
    },
    // 选择规格模板
    handleCommand(e) {},
    // 获取商品属性模板；
    productGetRule() {
      productGetRuleApi().then((res) => {
        this.ruleList = res.data;
      });
    },
    // 获取运费模板；
    productGetTemplate() {
      productGetTemplateApi().then((res) => {
        this.templateList = res.data;
      });
    },
    paramsGetTemplate() {
      paramListApi().then((res) => {
        this.paramsTypeList = res.data.list;
      });
    },
    changeParamsType(e) {
      e ? this.getParams(e) : (this.formValidate.params_list = []);
    },
    getParams(id) {
      paramInfoApi(id).then((res) => {
        this.formValidate.params_list = res.data.value;
      });
    },
    isSubset(arr1, arr2) {
      // 将数组转换为 Set，以便进行高效的包含检查
      const set1 = new Set(arr1);
      const set2 = new Set(arr2);

      // 检查 set2 中的每个元素是否都在 set1 中
      for (let elem of set2) {
        if (!set1.has(elem)) {
          return false;
        }
      }
      return true;
    },
    // 批量添加
    batchAdd() {
      let arr = [];
      for (let val of this.attrs) {
        if (this.oneFormBatch[0][val.value]) {
          arr.push(this.oneFormBatch[0][val.value]);
        }
      }

      // 批量设置商品规格属性
      const batchFields = [
        'pic',
        'price',
        'cost',
        'ot_price',
        'stock',
        'weight',
        'volume',
        'bar_code',
        'bar_code_number',
      ];
      // const defaultFields = ['bar_code', 'bar_code_number'];

      for (let val of this.manyFormValidate) {
        const batch = this.oneFormBatch[0];
        // 如果存在筛选条件且满足条件,或无筛选条件时
        if (!arr.length || this.isSubset(val.attr_arr, arr)) {
          // 设置有值的批量字段
          batchFields.forEach((field) => {
            if (batch[field] && batch[field] !== undefined) {
              if (field === 'pic' && batch[field]) {
                this.$set(val, field, batch[field]);
              } else if (field != 'pic') {
                this.$set(val, field, batch[field]);
              }
            }
          });

          // 设置默认字段
          // defaultFields.forEach((field) => {
          //   this.$set(val, field, batch[field]);
          // });
        }
      }
    },
    changeSpecImg(arr, img) {
      // 判断是否存在规格图
      let isHas = false;
      for (let i = 1; i < this.manyFormValidate.length; i++) {
        let item = this.manyFormValidate[i];
        if (item.pic && this.isSubset(item.attr_arr, arr)) {
          isHas = true;
          break;
        }
      }
      if (isHas) {
        this.$confirm('可以同步修改下方该规格图片，确定要替换吗？', '提示', {
          confirmButtonText: '替换',
          cancelButtonText: '暂不',
          type: 'warning',
        })
          .then(() => {
            for (let val of this.manyFormValidate) {
              if (this.isSubset(val.attr_arr, arr)) {
                this.$set(val, 'pic', img);
              }
            }
          })
          .catch(() => {});
      } else {
        for (let val of this.manyFormValidate) {
          if (this.isSubset(val.attr_arr, arr)) {
            this.$set(val, 'pic', img);
          }
        }
      }
    },
    // 立即生成
    generate(type, isCopy, arr) {
      this.manyFormValidate = [];
      this.formValidate.header = [];
    },
    clearAttr() {
      this.formDynamic.attrsName = '';
      this.formDynamic.attrsVal = '';
    },

    // 删除规格
    handleRemoveRole(index) {
      this.attrs.splice(index, 1);
      this.manyFormValidate.splice(index, 1);
      if (!this.attrs.length) {
        this.formValidate.header = [];
        this.manyFormValidate = [];
      } else {
        this.generateAttr(this.attrs);
      }
    },
    // 删除表格中 对应属性
    delAttrTable(val) {
      for (let i = 0; i < this.manyFormValidate.length; i++) {
        let item = this.manyFormValidate[i];
        if (item.attr_arr && item.attr_arr.includes(val)) {
          this.manyFormValidate.splice(i, 1);
          i--;
        }
      }
    },
    // 删除属性
    handleRemove2(item, index, val) {
      // 删除 manyFormValidate中 title = item.value 的属性值
      item.splice(index, 1);
      // this.generateAttr(this.attrs);
      this.delAttrTable(val);
    },
    // 新增规格
    handleAddRole() {
      let data = {
        value: this.formDynamic.attrsName,
        add_pic: 0,
        detail: [],
      };
      this.attrs.push(data);
    },
    handleAddParams() {
      let data = {
        name: '',
        value: '',
      };
      this.formValidate.params_list.push(data);
    },
    handleSaveAsTemplate() {
      this.$prompt('', '请输入模板名称', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
      })
        .then(({ value }) => {
          let spec = this.attrs.map((item) => {
            return {
              value: item.value,
              detail: item.detail.map((e) => e.value),
            };
          });
          let formDynamic = {
            rule_name: value,
            spec: spec,
          };
          ruleAddApi(formDynamic, 0)
            .then((res) => {
              this.$message.success(res.msg);
              this.productGetRule();
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        })
        .catch(() => {});
    },
    // 新增一条属性
    addOneAttr(val, val2) {
      this.generateAttr(this.attrs, val2);
    },
    handleFocus(val) {
      this.changeAttrValue = val;
    },
    handleBlur() {
      this.changeAttrValue = '';
    },
    handleSelImg(item) {
      this.$imgModal((e) => {
        item.pic = e.att_dir;
        this.changeSpecImg([item.value], e.att_dir);
      });
    },
    handleRemoveImg(item) {
      item.pic = '';
    },
    // 规格名称改变
    attrChangeValue(i, val) {
      if (val.trim().length && this.attrs[i].detail.length) {
        this.generateHeader(this.attrs);
        if (this.manyFormValidate.length) {
          this.manyFormValidate.map((item, i) => {
            if (i > 0) {
              if (Object.keys(item.detail).includes(this.changeAttrValue)) {
                item.detail[val] = item.detail[this.changeAttrValue];
                item[val] = item[this.changeAttrValue];
                delete item.detail[this.changeAttrValue];
                delete item[this.changeAttrValue];
              }
            }
          });
          this.changeAttrValue = val;
        }
      } else {
        this.generateAttr(this.attrs);
      }
    },
    // 规格值改变
    attrDetailChangeValue(val, i) {
      if (this.manyFormValidate.length) {
        let key = this.attrs[i].value;
        this.manyFormValidate.map((item, i) => {
          if (i > 0) {
            if (Object.keys(item.detail).includes(key) && item.detail[key] === this.changeAttrValue) {
              item.detail[key] = val;
              let index = item.attr_arr.findIndex((item) => item === this.changeAttrValue);
              item.attr_arr[index] = val;
            }
          }
        });
        this.changeAttrValue = val;
      } else {
        this.generateAttr(this.attrs, 1);
      }
    },
    // 规格图片添加开关
    addPic(e, i) {
      if (e) {
        this.attrs.map((item, ii) => {
          if (ii !== i) {
            this.$set(item, 'add_pic', 0);
          }
        });
        this.canSel = false;
      } else {
        this.canSel = true;
      }
    },
    // 规格拖拽排序后
    onMoveSpec() {
      this.generateAttr(this.attrs);
    },
    changeCurrentIndex(i) {
      this.currentIndex = i;
    },
    // 生成商品规格表头
    generateHeader(data) {
      let specificationsColumns = data.map((item) => ({
        title: item.value,
        key: item.value,
        minWidth: 140,
        fixed: 'left',
      }));
      let arr;
      if ([1, 2].includes(Number(this.formValidate.virtual_type))) {
        arr = [...specificationsColumns, ...VirtualTableHead];
        // 找到slot 等于 fictitious 将title改为规格名称
        this.formValidate.header.map((item) => {
          if (item.slot === 'fictitious') {
            item.title = this.formValidate.virtual_type == 1 ? '添加卡密/网盘' : '选择优惠券';
          }
        });
      } else if (this.formValidate.virtual_type == 3) {
        arr = [...specificationsColumns, ...VirtualTableHead2];
      } else {
        arr = [...specificationsColumns, ...GoodsTableHead];
      }
      this.$set(this.formValidate, 'header', arr);
      this.tableKey += 1;
      this.columnsInstalM = arr;
    },
    /*
     * 生成属性
     * @param {Array} data 规格数据
     * */
    generateAttr(data, val) {
      this.generateHeader(data);
      const combinations = this.generateCombinations(data);
      console.log('规格组合总数：' + combinations.length);
      const virtualType = this.formValidate.virtual_type;
      // 如果combinations数量超过 500，则分批次生成属性
      let rows = [];
      if (combinations.length > 500) {
        const batchSize = Math.ceil(combinations.length / 500);
        for (let i = 0; i < combinations.length; i += batchSize) {
          setTimeout((e) => {
            let d = this.generateAttrBatch(data, combinations.slice(i, i + batchSize), val);
            rows = [...rows, ...d];
            this.manyFormValidate = [...this.oneFormBatch, ...rows];
          }, 0);
        }
      } else {
        rows = this.generateAttrBatch(data, combinations, val);
        this.manyFormValidate = [...this.oneFormBatch, ...rows];
      }
    },
    // 生成属性批次
    generateAttrBatch(data, combinations, val) {
      const existingItems = this.manyFormValidate.slice(1); // 排除第一项默认数据

      const rows = combinations.map((combination) => {
        const row = {
          attr_arr: combination,
          detail: {},
          title: '',
          key: '',
          price: 0,
          pic: '',
          ot_price: 0,
          cost: 0,
          stock: 0,
          is_show: 1,
          is_default_select: 0,
          unique: '',
          weight: '',
          volume: '',
          brokerage: 0,
          brokerage_two: 0,
          vip_price: 0,
          vip_proportion: 0,
        };

        // 设置虚拟类型相关属性
        if (this.formValidate.virtual_type === 1) {
          row.virtual_list = [];
          row.disk_info = '';
        } else if (this.formValidate.virtual_type === 2) {
          row.coupon_id = 0;
          row.coupon_name = '';
        }

        // 处理规格属性
        data.forEach((item, i) => {
          const value = combination[i];
          row[item.value] = value;
          row.title = item.value;
          row.key = item.value;
          row.detail[item.value] = value;

          // 查找匹配的现有规格项
          const matchedItem = existingItems.find((item) => item.attr_arr && arraysEqual(item.attr_arr, combination));

          if (matchedItem) {
            Object.assign(row, {
              price: matchedItem.price,
              cost: matchedItem.cost,
              ot_price: matchedItem.ot_price,
              stock: matchedItem.stock,
              pic: matchedItem.pic,
              unique: matchedItem.unique || '',
              weight: matchedItem.weight || '',
              volume: matchedItem.volume || '',
              is_show: matchedItem.is_show || 1,
              is_default_select: matchedItem.is_default_select || 0,
              volume: matchedItem.volume || 0,
              bar_code_number: matchedItem.bar_code_number || 0,
              is_virtual: matchedItem.is_virtual,
              brokerage: matchedItem.brokerage,
              brokerage_two: matchedItem.brokerage_two,
              vip_price: matchedItem.vip_price,
              vip_proportion: matchedItem.vip_proportion,
            });

            if (this.formValidate.virtual_type === 1) {
              row.virtual_list = matchedItem.virtual_list;
              row.disk_info = matchedItem.disk_info;
            } else if (this.formValidate.virtual_type === 2 && matchedItem.coupon_id) {
              row.coupon_id = matchedItem.coupon_id;
              row.coupon_name = matchedItem.coupon_name;
            }
          } else if (item.add_pic && combination.includes(val)) {
            const picItem = item.detail.find((e) => combination.includes(e.value));
            if (picItem) row.pic = picItem.pic;
          }
        });
        return row;
      });
      return rows;
    },
    // 切换默认选中规格
    changeDefaultSelect(e, index) {
      // 一个开启 其他关闭
      this.manyFormValidate.map((item, i) => {
        if (i !== index) {
          item.is_default_select = 0;
        }
      });
      if (e) this.manyFormValidate[index].is_show = 1;
    },
    // 改变是否显示
    changeDefaultShow(index) {
      // 如果默认选中开启 则不可隐藏
      if (this.manyFormValidate[index].is_default_select === 1) {
        this.manyFormValidate[index].is_show = 1;
        this.$message.error('默认规格不可隐藏');
      }
    },
    // 生成规格组合
    generateCombinations(arr, prefix = []) {
      if (arr.length === 0) {
        return [prefix];
      }
      const [first, ...rest] = arr;
      return first.detail.flatMap((detail) => this.generateCombinations(rest, [...prefix, detail.value]));
    },
    // 添加属性
    createAttr(num, idx) {
      if (num) {
        // 判断是否存在同样熟悉
        var isExist = this.attrs[idx].detail.some((item) => item.value === num);
        if (isExist) {
          this.$message.error('规格值已存在');
          return;
        }
        this.attrs[idx].detail.push({ value: num, pic: '' });
        if (this.manyFormValidate.length) {
          this.addOneAttr(this.attrs[idx].value, num);
        } else {
          this.generateAttr(this.attrs);
        }

        this.$refs.specStock.$refs['popoverRef_' + idx][0].doClose(); //关闭的
        this.clearAttr();
        setTimeout(() => {
          if (this.$refs.specStock.$refs['popoverRef_' + idx]) {
            //重点是以下两句
            this.$refs.specStock.$refs['popoverRef_' + idx][0].doShow(); //打开的
            //重点是以上两句
          }
        }, 20);
      } else {
        this.$refs.specStock.$refs['popoverRef_' + idx][0].doClose(); //关闭的
      }
    },
    handleShowPop(index) {
      this.$refs.specStock.$refs['inputRef_' + index][0].focus();
    },
    // 商品分类；
    goodsCategory() {
      cascaderListApi(1)
        .then((res) => {
          this.treeSelect = res.data;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 改变规格
    changeSpec() {
      this.formValidate.is_sub = [];
      let id = this.$route.params.id;
      if (id) {
        checkActivityApi(id)
          .then((res) => {})
          .catch((res) => {
            this.formValidate.spec_type = this.spec_type;
            this.$message.error(res.msg);
          });
      }
    },
    // 详情
    getInfo() {
      this.spinShow = true;
      productInfoApi(this.$route.params.id)
        .then(async (res) => {
          let data = res.data.productInfo;
          this.infoData(data);
          this.spinShow = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.$message.error(res.msg);
        });
    },
    handleRemove(i) {
      this.images.splice(i, 1);
      this.formValidate.slider_image.splice(i, 1);
      this.oneFormValidate[0].pic = this.formValidate.slider_image[0];
    },
    // 关闭图片上传模态框
    changeCancel(msg) {
      this.modalPic = false;
    },
    // 点击商品图
    modalPicTap(tit, picTit = '', index = 0) {
      this.modalPic = true;
      this.isChoice = tit === 'dan' ? '单选' : '多选';
      this.picTit = picTit;
      this.tableIndex = index;
    },
    // 获取单张图片信息
    getPic(pc) {
      switch (this.picTit) {
        case 'danFrom':
          this.formValidate.image = pc.att_dir;
          if (!this.$route.params.id) {
            if (this.formValidate.spec_type === 0) {
              this.oneFormValidate[0].pic = pc.att_dir;
            } else {
              this.manyFormValidate.map((item) => {
                item.pic = pc.att_dir;
              });
              this.oneFormBatch[0].pic = pc.att_dir;
            }
          }
          break;
        case 'danTable':
          this.oneFormValidate[this.tableIndex].pic = pc.att_dir;
          break;
        case 'duopi':
          this.oneFormBatch[this.tableIndex].pic = pc.att_dir;
          break;
        case 'recommend_image':
          this.formValidate.recommend_image = pc.att_dir;
          break;
        default:
          if (this.manyFormValidate.length) this.manyFormValidate[this.tableIndex].pic = pc.att_dir;
      }
      this.modalPic = false;
    },
    deleteRow(index) {
      this.formValidate.params_list.splice(index, 1);
    },
    // 获取多张图信息
    getPicD(pc) {
      this.images = pc;
      this.images.map((item) => {
        this.formValidate.slider_image.push(item.att_dir);
        this.formValidate.slider_image = this.formValidate.slider_image.splice(0, 10);
      });
      this.oneFormValidate[0].pic = this.formValidate.slider_image[0];
      this.modalPic = false;
    },
    // 提交
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.formValidate.type = this.type;
          let arr = this.formValidate.spec_type === 0 ? this.oneFormValidate : this.manyFormValidate;
          let item = JSON.parse(JSON.stringify(arr));
          if (this.formValidate.spec_type === 1) {
            if (item.length < 2) return this.$message.warning('商品规格-规格数量最少1个');
            // 删除第一项
            item.shift();
          }
          for (let i = 0; i < item.length; i++) {
            if (item[i].stock > 1000000) {
              return this.$message.error('规格库存-库存超出系统范围(1000000)');
            }
          }
          if (this.formValidate.is_sub[0] === 1) {
            for (let i = 0; i < item.length; i++) {
              if (item[i].brokerage === null || item[i].brokerage_two === null) {
                return this.$message.error('营销设置- 一二级返佣不能为空');
              }
            }
          } else {
            for (let i = 0; i < item.length; i++) {
              if (item[i].vip_price === null) {
                return this.$message.error('营销设置-会员价不能为空');
              }
            }
          }
          if (this.formValidate.is_sub.length === 2) {
            for (let i = 0; i < item.length; i++) {
              if (item[i].brokerage === null || item[i].brokerage_two === null || item[i].vip_price === null) {
                return this.$message.error('营销设置- 一二级返佣和会员价不能为空');
              }
            }
          }
          if (this.formValidate.freight == 3 && !this.formValidate.temp_id) {
            return this.$message.warning('商品信息-运费模板不能为空');
          }
          let activeIds = [];
          this.dataLabel.forEach((item) => {
            activeIds.push(item.id);
          });
          this.formValidate.label_id = activeIds;
          if (this.openSubimit) return;
          this.openSubimit = true;
          this.formValidate.description = formatRichText(this.content);
          if (this.formValidate.spec_type === 0) {
            this.formValidate.attrs = item;
            this.formValidate.header = [];
            this.formValidate.items = [];
            this.formValidate.is_copy = 0;
          } else {
            this.formValidate.items = this.attrs;
            this.formValidate.attrs = item;
            this.formValidate.is_copy = 1;
          }
          productAddApi(this.formValidate)
            .then(async (res) => {
              this.openSubimit = false;
              this.$message.success(res.msg);
              if (this.$route.params.id === '0') {
                cacheDelete().catch((err) => {
                  this.$message.error(err.msg);
                });
              }
              setTimeout(() => {
                this.openSubimit = false;
                this.$router.push({ path: this.$routeProStr + '/product/product_list' });
              }, 500);
            })
            .catch((res) => {
              setTimeout((e) => {
                this.openSubimit = false;
              }, 1000);
              this.$message.error(res.msg);
            });
        } else {
          if (!this.formValidate.store_name) {
            return this.$message.warning('商品信息-商品名称不能为空');
          } else if (!this.formValidate.cate_id.length) {
            return this.$message.warning('商品信息-商品分类不能为空');
          } else if (!this.formValidate.unit_name) {
            return this.$message.warning('商品信息-商品单位不能为空');
          } else if (!this.formValidate.slider_image.length) {
            return this.$message.warning('商品信息-商品轮播图不能为空');
          } else if (!this.formValidate.logistics.length && !this.formValidate.virtual_type) {
            return this.$message.warning('物流设置-至少选择一种物流方式');
          } else if (!this.formValidate.temp_id && this.formValidate.freight == 3) {
            return this.$message.warning('商品信息-运费模板不能为空');
          }
        }
      });
    },
    changeTemplate(msg) {
      this.template = msg;
    },
    // 表单验证
    validate(prop, status, error) {
      if (status === false) {
        this.$message.warning(error);
      }
    },
    // 移动
    handleDragStart(e, item) {
      this.dragging = item;
    },
    handleDragEnd(e, item) {
      this.dragging = null;
    },
    handleDragOver(e) {
      e.dataTransfer.dropEffect = 'move';
    },
    handleDragEnter(e, item) {
      e.dataTransfer.effectAllowed = 'move';
      if (item === this.dragging) {
        return;
      }
      const newItems = [...this.formValidate.slider_image];
      const src = newItems.indexOf(this.dragging);
      const dst = newItems.indexOf(item);
      newItems.splice(dst, 0, ...newItems.splice(src, 1));
      this.formValidate.slider_image = newItems;
    },
    //对象数组去重；
    unique(arr) {
      const res = new Map();
      return arr.filter((arr) => !res.has(arr.product_id) && res.set(arr.product_id, 1));
    },
    // 商品id
    getProductId(data) {
      this.goods_modals = false;
      this.formValidate.recommend_list = this.unique(this.formValidate.recommend_list.concat(data));
    },
    // 选择推荐商品
    changeGoods() {
      this.goods_modals = true;
      this.$refs.goodslist.getList();
      this.$refs.goodslist.goodsCategory();
    },
    // 选择用户标签
    activeData(dataLabel) {
      this.labelShow = false;
      this.dataLabel = dataLabel;
    },
    // 选择商品标签
    activeLabel(data) {
      this.tagShow = false;
      this.formValidate.label_list = Array.from(new Set(data));
    },
    // 标签弹窗关闭
    labelClose() {
      this.labelShow = false;
      this.tagShow = false;
    },
    // 删除用户标签
    closeLabel(label) {
      let index = this.dataLabel.indexOf(this.dataLabel.filter((d) => d.id == label.id)[0]);
      this.dataLabel.splice(index, 1);
    },
    // 打开选择用户标签
    openLabel(row) {
      this.labelShow = true;
    },
    handleRemoveRecommend(i) {
      this.formValidate.recommend_list.splice(i, 1);
    },
    // 打开的营销活动标签
    watchActivity() {
      let marketing = [];
      // 使用对象映射优化权限判断逻辑
      const permissionMap = {
        默认: true,
        秒杀: 'seckill',
        砍价: 'bargain',
        拼团: 'combination',
      };
      this.formValidate.activity.forEach((el) => {
        if (permissionMap[el] === true || (permissionMap[el] && checkArray(permissionMap[el]))) {
          marketing.push(el);
        }
      });
      this.formValidate.activity = marketing;
    },
  },
};
</script>
<style lang="scss" scoped>
@use './productAdd.scss' as *;
</style>
