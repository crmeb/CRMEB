<template>
  <div class="" id="shopp-manager" v-loading="spinShow">
    <pages-header
      ref="pageHeader"
      :title="route.params.id ? '编辑实物商品' : '添加实物商品'"
      :backUrl="routeProStr + '/product/normal/list'"
      description="分步完成基础信息、规格库存与详情设置，保存后可上架销售。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16" :body-style="{ padding: '0px 20px' }">
      <el-tabs v-model="currentTab">
        <el-tab-pane v-for="(item, index) in headTab" :key="index" :label="item.tit" :name="item.name"></el-tab-pane>
      </el-tabs>
      <el-form
        class="formValidate mt20"
        ref="formValidateRef"
        :rules="ruleValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.prevent
      >
        <!-- 基础信息-->
        <basic-info
          v-show="currentTab === '1'"
          :isCai="type"
          :formValidate="formValidate"
          :treeSelect="treeSelect"
          :tileLabelList="tileLabelList"
          :progress="progress"
          :upload="upload"
          :videoIng="videoIng"
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
          @createAttr="createAttr"
          @handleAddRole="handleAddRole"
          @handleSaveAsTemplate="handleSaveAsTemplate"
          @batchAdd="batchAdd"
          @batchDel="batchDel"
          @modalPicTap="modalPicTap"
          @changeDefaultSelect="changeDefaultSelect"
          @changeDefaultShow="changeDefaultShow"
          @see="see"
        ></spec-stock>

        <!-- 商品详情-->
        <product-detail
          v-show="currentTab === '3'"
          :contents="contents"
          :content="content"
          :store-name="formValidate.store_name"
          @getEditorContent="getEditorContent"
        ></product-detail>

        <!-- 物流设置-->
        <logistics-setting
          v-show="currentTab === '4'"
          :formValidate="formValidate"
          :templateList="templateList"
          @deliveryMethodsChange="onDeliveryMethodsChange"
          @freightConfigChange="onFreightConfigChange"
          @addTemp="addTemp"
        ></logistics-setting>

        <!-- 会员价 -->
        <price-commission
          v-show="currentTab === '5'"
          :formValidate="formValidate"
          :oneFormValidate="oneFormValidate"
          :manyFormValidate="manyFormValidate"
          :columnsInstall="columnsInstall"
          :columnsInstal2="columnsInstal2"
          v-model:manyVipPrice="manyVipPrice"
          v-model:manyVipDiscount="manyVipDiscount"
          @checkAllGroupChange="checkAllGroupChange"
          @changeVipPrice="changeVipPrice"
          @changeDiscount="changeDiscount"
          @brokerageSetUp="brokerageSetUp"
        ></price-commission>

        <!-- 营销设置-->
        <marketing-setting
          v-show="currentTab === '6'"
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
          v-show="currentTab === '7'"
          :formValidate="formValidate"
          v-model:customBtn="customBtn"
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
          <el-button class="submission" v-if="currentTab !== '7'" v-db-click @click="downTab">下一步</el-button>
          <el-button
            type="primary"
            class="submission"
            v-db-click
            @click="handleSubmit()"
            v-if="route.params.id || currentTab !== '1'"
            >保存</el-button
          >
        </el-form-item>
      </el-form>
    </el-card>
    <el-dialog v-model="modalPic" width="950px" scrollable title="上传商品图" :close-on-click-modal="false">
      <uploadPictures
        :isChoice="isChoice"
        @getPic="getPic"
        @getPicD="getPicD"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="modalPic"
      ></uploadPictures>
    </el-dialog>
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
      v-model="modals"
      @closed="cancel"
      class="Box"
      :title="copyTitle"
      :close-on-click-modal="false"
      width="720px"
    >
      <product-collect
        ref="productCollectRef"
        v-if="modals"
        @on-close="onClose"
        @get-platforms="onGetPlatforms"
      ></product-collect>
    </el-dialog>
    <el-dialog v-model="goods_modals" title="商品列表" footerHide class="paymentFooter" scrollable width="1000px">
      <goods-list v-if="goods_modals" ref="goodslist" :ischeckbox="true" @getProductId="getProductId"></goods-list>
    </el-dialog>
    <!-- 用户标签 -->
    <el-dialog
      v-model="labelShow"
      title="请选择用户标签"
      :show-close="true"
      width="540px"
      :close-on-click-modal="false"
    >
      <userLabel ref="userLabelRef" @activeData="activeData" @close="labelClose"></userLabel>
    </el-dialog>
    <!-- 商品标签 -->
    <el-dialog v-model="tagShow" title="请选择商品标签" :show-close="true" width="540px" :close-on-click-modal="false">
      <goodsLabel
        ref="goodsLabelRef"
        :defaultLabelList="labelList"
        @activeLabel="activeLabel"
        @close="labelClose"
      ></goodsLabel>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick, onMounted, getCurrentInstance } from 'vue';
import { useRoute, useRouter, onBeforeRouteUpdate } from 'vue-router';
import { ElMessage, ElMessageBox } from 'element-plus';
import userLabel from '@/components/labelList';
import useLabel from '@/components/goodsLabel/useLabel';
import goodsLabel from '@/components/goodsLabel';
import uploadPictures from '@/components/uploadPictures';
import freightTemplate from '@/components/freightTemplate';
import couponList from '@/components/couponList';
import addAttr from './addAttr';
import goodsList from '@/components/goodsList/index';
import productCollect from './productCollect';
import { userLabelAddApi } from '@/api/user';
import {
  cascaderListApi,
  generateAttrApi,
  productGetRuleApi,
  productGetTemplateApi,
  productGetTempKeysApi,
  checkActivityApi,
  productCache,
  cacheDelete,
  uploadType,
  productCreateApi,
  ruleAddApi,
  paramListApi,
  paramInfoApi,
  productProtectionListApi,
  productLabelUseListApi,
} from '@/api/product';
import { normalProductInfo, normalProductSave, normalProductUpdate } from '@/pages/plugin/normal/normalProduct';
import { pluginListApi } from '@/api/plugin';
import Setting from '@/setting';
import { getCookies } from '@/libs/util';
import { uploadByPieces } from '@/utils/upload'; //引入uploadByPieces方法
import { isFileUpload, isVideoUpload, arraysEqual } from '@/utils';
import videoCloud from '@/utils/videoCloud';
import modalForm from '@/utils/modalForm';
import bus from '@/utils/bus';
import { GoodsTableHead, columns2, columns3, CustomList, RuleValidate } from './defaultData';
import BasicInfo from './components/BasicInfo.vue';
import SpecStock from './components/SpecStock.vue';
import ProductDetail from './components/ProductDetail.vue';
import LogisticsSetting from './components/LogisticsSetting.vue';
import PriceCommission from './components/PriceCommission.vue';
import MarketingSetting from './components/MarketingSetting.vue';
import OtherSetting from './components/OtherSetting.vue';
import { formatRichText } from '@/utils/editorImg';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'ProductAdd' });

const { proxy } = getCurrentInstance();
const route = useRoute();
const router = useRouter();
const mediaStore = useMediaStore();
const routeProStr = Setting.routePre;

const formValidateRef = ref(null);
const specStock = ref(null);
const templates = ref(null);
const addattr = ref(null);
const couponTemplates = ref(null);
const goodsCoupon = ref(null);
const goodslist = ref(null);
const userLabelRef = ref(null);
const goodsLabelRef = ref(null);

const labelShow = ref(false);
const tagShow = ref(false);
const dataLabel = ref([]);
const headTab = ref([
  { tit: '基础信息', name: '1' },
  { tit: '规格库存', name: '2' },
  { tit: '商品详情', name: '3' },
  { tit: '物流设置', name: '4' },
  { tit: '会员价', name: '5' },
  { tit: '营销设置', name: '6' },
  { tit: '其他设置', name: '7' },
]);
const seletVideo = ref(0); //选择视频类型
const customBtn = ref(0); //自定义留言开关
const content = ref('');
const contents = ref('');
const fileUrl = Setting.apiBaseURL + '/file/upload';
const fileUrl2 = Setting.apiBaseURL + '/file/video_upload';
const upload_type = ref(''); //视频上传类型 1 本地上传 2 3 4 OSS上传
const header = ref({});
const type = ref(0);
const modals = ref(false);
const goods_modals = ref(false);
const spinShow = ref(false);
const openSubimit = ref(false);
// 批量设置表格data
const oneFormBatch = ref([
  {
    pic: '',
    price: void 0,
    cost: void 0,
    original_price: void 0,
    stock: void 0,
    bar_code: '',
    bar_code_number: '',
    weight: void 0,
    volume: void 0,
  },
]);

// 规格数据
const formDynamic = ref({
  attrsName: '',
  attrsVal: '',
});
const tabIndex = ref(0);
const tabName = ref('');
const formDynamicNameData = ref([]);
const isBtn = ref(false);
const columns = ref([]);
const columnsInstall = ref([]);
const columnsInstal2 = ref([]);
const gridPic = {
  xl: 6,
  lg: 8,
  md: 12,
  sm: 12,
  xs: 12,
};
const gridBtn = {
  xl: 4,
  lg: 8,
  md: 8,
  sm: 8,
  xs: 8,
};
//自定义留言下拉选择
//自定义留言内容
const currentIndex = ref(0);

const formValidate = ref({
  delivery_methods: ['express'], //选择物流方式
  freight_config: {
    express: { type: 'free', postage: 0, temp_id: 0 }, // 默认包邮
    door_service: { type: 'free', postage: 0 },
  }, //运费配置
  recommend: [], //商品推荐
  presale_day: 1, //预售发货时间-结束
  presale: 0, //预售商品开关
  is_limit: 0,
  limit_type: 0,
  limit_num: 0,
  presale_time: [],
  vip_product: 0, // 会员专属（0不限制/1仅会员可见可买/2所有人可见仅会员可买）
  custom_form: [], //自定义留言
  store_name: '',
  cate_id: [],
  label_id: [],
  keyword: '',
  unit_name: '',
  intro: '',
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
  is_postage: 0,
  is_sub: 0,
  is_vip: 0,
  recommend_list: [],
  params_list: [], //商品参数
  id: 0,
  spec_type: 0,
  attrs: [],
  items: [
    {
      pic: '',
      price: 0,
      cost: 0,
      original_price: 0,
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
  weight: 0,
  volume: 0,
});
const ruleList = ref([]);
const templateList = ref([]);
const createBnt = ref(true);
const showIput = ref(false);
const manyFormValidate = ref([]);
// 单规格表格data
const oneFormValidate = ref([
  {
    pic: '',
    price: 0,
    cost: 0,
    original_price: 0,
    stock: 0,
    bar_code: '',
    bar_code_number: '',
    weight: 0,
    volume: 0,
    brokerage: 0,
    brokerage_two: 0,
    vip_price: 0,
  },
]);
const images = ref([]);
const imagesTable = ref('');
const currentTab = ref('1');
const isChoice = ref('');
const loading = ref(false);
const modalPic = ref(false);
const template = ref(false);
const uploadList = ref([]);
const treeSelect = ref([]);
const picTit = ref('');
const tableIndex = ref(0);
const ruleValidate = RuleValidate;
const manyVipPrice = ref(undefined);
const manyVipDiscount = ref(undefined);
const upload = reactive({
  videoIng: false, // 是否显示进度条；
});
const videoIng = ref(false); // 是否显示进度条；
const progress = ref(0); // 进度条默认0
const videoLink = ref('');
const attrs = ref([]);
const activity = ref({ 默认: 'red', 秒杀: 'blue', 砍价: 'green', 拼团: 'yellow' });
const couponName = ref([]);
const updateIds = ref([]);
const updateName = ref([]);
const couponIds = ref('');
const couponNames = ref([]);
const member = [
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
];
const columnsInstalM = ref([]);
const moveIndex = ref('');
const addValue = ref('');
const visible = ref(false);
const paramsTypeList = ref([]);
const paramsType = ref(null);
const canSel = ref(true); // 规格图片添加判断
const changeAttrValue = ref(''); //修改的规格值
const tableKey = ref(0);
const protectionList = ref([]); // 服务保障
const labelList = ref([]);
const tileLabelList = ref([]);

// this.spec_type 历史写法（未在 data 中声明），用普通变量承接
let specType = 0;
// 拖拽状态（原 this.dragging）
let dragging = null;

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '120px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));
const labelBottom = computed(() => (isMobile.value ? undefined : '15px'));

onBeforeRouteUpdate((to, from, next) => {
  bus.emit('onTagsViewRefreshRouterView', route.path);
  next();
});

// 动态表格名（原 this[type] 访问 oneFormValidate / manyFormValidate）
function formTable(name) {
  return name === 'oneFormValidate' ? oneFormValidate.value : manyFormValidate.value;
}

// created
columns.value = columns2.slice(0, 8);
getToken();

onMounted(async () => {
  if (route.params.id !== '0' && route.params.id) {
    await getInfo();
  } else if (route.params.id === '0') {
    getProductCache();
  } else {
    getproductLabelUseListApi();
  }
  if (route.query.type) {
    modals.value = true;
    type.value = Number(route.query.type);
  } else {
    type.value = 0;
  }
  goodsCategory();
  productGetRule();
  productGetTemplate();
  paramsGetTemplate();
  getUploadType();
  watchActivity();
  getProtectionList();
  getActivityPlugins();
});

function getProductCache() {
  productCache()
    .then((res) => {
      let data = res.data.info;
      getproductLabelUseListApi();

      if (!Array.isArray(data)) {
        let cate_id = data.cate_id.map(Number);
        let label_id = data.label_id.map(Number);
        attrs.value = Array.isArray(data.items)
          ? data.items.map((item) => ({ value: item.name, detail: item.values.map((v) => ({ value: v })) }))
          : [];
        let ids = [];
        if (data.coupons) {
          data.coupons.map((item) => {
            ids.push(item.id);
          });
          couponName.value = data.coupons;
        }

        Object.assign(formValidate.value, data);
        formValidate.value.activity = data.activity || ['默认', '秒杀', '砍价', '拼团'];
        dataLabel.value = data.label_id;
        formValidate.value.coupon_ids = ids;
        updateIds.value = ids;
        updateName.value = data.coupons;
        formValidate.value.cate_id = cate_id;
        oneFormValidate.value = data.attr
          ? [data.attr]
          : Array.isArray(data.attrs)
          ? data.attrs.slice(0, 1).map((item) => ({ ...item }))
          : [];
        generateHeader(attrs.value);
        formValidate.value.delivery_methods = data.delivery_methods || ['express'];
        formValidate.value.header = [];
        manyFormValidate.value = Array.isArray(data.attrs) ? data.attrs : [];
        specType = Number(data.spec_type);
        formValidate.value.spec_type = specType;
        formValidate.value.custom_form = data.custom_form || [];
        if (formValidate.value.custom_form.length != 0) {
          customBtn.value = 1;
        }
        attrs.value.map((item) => {
          if (item.add_pic) canSel.value = false;
        });
        if (specType === 0) {
          manyFormValidate.value = [];
        } else {
          createBnt.value = true;
          oneFormValidate.value = [
            {
              pic: data.image,
              price: 0,
              cost: 0,
              original_price: 0,
              stock: 0,
              bar_code: '',
              bar_code_number: '',
              weight: 0,
              volume: 0,
              brokerage: 0,
              brokerage_two: 0,
              vip_price: 0,
            },
          ];
        }
        watchActivity();
        spinShow.value = false;
      }
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
function getProtectionList() {
  productProtectionListApi({ page: 0, limit: 0, status: 1 }).then((res) => {
    protectionList.value = res.data.list;
  });
}
function getproductLabelUseListApi() {
  productLabelUseListApi().then((res) => {
    // 合并数组中所有的list
    tileLabelList.value = res.data.flatMap((item) => item.list);
    let list = res.data;
    if (formValidate.value.label_list.length) {
      formValidate.value.label_list.map((el) => {
        list.map((re) => {
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
      list.map((el) => {
        el.list.map((label) => {
          label.active = false;
        });
      });
    }
    labelList.value = list;
  });
}
function addProtection() {
  router.push({ path: Setting.routePre + '/product/protection/list' });
}
// 分片上传
function videoSaveToUrl(file) {
  if (isVideoUpload(file)) {
    uploadByPieces({
      file: file, // 视频实体
      pieceSize: 3, // 分片大小
      success: (data) => {
        formValidate.value.video_link = data.file_path;
        progress.value = 100;
      },
      error: (e) => {
        ElMessage.error(e.msg);
      },
      uploading: (chunk, allChunk) => {
        videoIng.value = true;
        let st = Math.floor((chunk / allChunk) * 100);
        progress.value = st;
      },
    });
  }
  return false;
}
// 新增分类
function addCate() {
  modalForm(productCreateApi()).then(() => goodsCategory());
}
// 物流方式选择
function onDeliveryMethodsChange(val) {
  formValidate.value['delivery_methods'] = val;
}
// 运费配置变更
function onFreightConfigChange(config) {
  formValidate.value['freight_config'] = config;
}
// 新增标签
function addLabel() {
  // 原 Vue2 代码为 this.userLabel()（未定义方法，历史遗留死调用），等价改为刷新标签列表
  modalForm(userLabelAddApi(0)).then(() => getproductLabelUseListApi());
}
// 选择标签
function addGoodsTag() {
  tagShow.value = true;
}
// 自定义留言 开启关闭
function customMessBtn(e) {
  if (!e) {
    formValidate.value.custom_form = [];
  }
  customBtn.value = e;
}
// 自定义留言 新增表单
function addcustom() {
  if (formValidate.value.custom_form.length > 9) {
    ElMessage.warning('最多添加10条');
  } else {
    formValidate.value.custom_form.push({
      title: '',
      label: 'text',
      value: '',
      status: false,
    });
  }
}
// 删除
function delcustom(index) {
  formValidate.value.custom_form.splice(index, 1);
}
// 预售具体日期
function onchangeTime(e) {
  formValidate.value.presale_time = e;
}
// 商品详情
function getEditorContent(data) {
  content.value = data;
}
function cancel() {
  modals.value = false;
}
// 上传头部token
function getToken() {
  header.value['Authori-zation'] = 'Bearer ' + getCookies('token');
}
//获取视频上传类型（与 api 的 uploadType 重名，改为 getUploadType）
function getUploadType() {
  uploadType().then((res) => {
    upload_type.value = res.data.upload_type;
  });
}
// 初始化数据展示
function infoData(data, isCopy) {
  let cate_id = data.cate_id.map(Number);
  let label_id = data.label_id.map(Number);
  // 兼容两种 items 来源：中台编辑回填为 {name, values}（规格名在 name、规格值为字符串数组）；
  // 采集回填为 {value, detail}（detail 为字符串数组或 [{value, pic}] 对象数组），规格名统一归一到 value 键
  attrs.value = Array.isArray(data.items)
    ? data.items.map((item) => ({
        value: item.value ?? item.name,
        detail: (item.detail ?? item.values ?? []).map((v) =>
          typeof v === 'string' ? { value: v, pic: '' } : { value: v.value ?? '', pic: v.pic ?? '' },
        ),
        add_pic: item.add_pic ?? 0,
      }))
    : [];
  let ids = [];
  data.coupons.map((item) => {
    ids.push(item.id);
  });
  Object.assign(formValidate.value, data);
  // 兼容旧数据：recommend_list 可能为字符串，统一转换为数组对象
  if (typeof formValidate.value.recommend_list === 'string') {
    formValidate.value.recommend_list = [];
  }
  formValidate.value.is_sub = Number(data.is_sub) || 0;
  // give_integral 为 decimal 字段，接口返回的是 "0.00" 这类字符串，归一化为数字以免整型校验误报
  formValidate.value.give_integral = Number(data.give_integral) || 0;
  formValidate.value.activity = data.activity || ['默认', '秒杀', '砍价', '拼团'];
  seletVideo.value = data.seletVideo;
  contents.value = data.description;
  couponName.value = data.coupons;
  formValidate.value.coupon_ids = ids;
  updateIds.value = ids;
  dataLabel.value = data.label_id;
  updateName.value = data.coupons;
  formValidate.value.delivery_methods = data.delivery_methods || ['express'];
  formValidate.value.custom_form = data.custom_form || [];
  if (formValidate.value.custom_form.length != 0) {
    customBtn.value = 1;
  }
  formValidate.value.cate_id = cate_id;
  if (data.attr) {
    oneFormValidate.value = [data.attr];
    oneFormValidate.value[0].vip_proportion = (
      (oneFormValidate.value[0].vip_price / oneFormValidate.value[0].price) *
      100
    ).toFixed(2);
    oneFormValidate.value[0].weight = data.weight ?? 0;
    oneFormValidate.value[0].volume = data.volume ?? 0;
  }
  getproductLabelUseListApi();

  formValidate.value.header = [];
  specType = Number(data.spec_type);
  formValidate.value.spec_type = specType;
  attrs.value.map((item) => {
    if (item.add_pic) canSel.value = false;
  });
  if (specType === 0) {
    manyFormValidate.value = [];
  } else {
    createBnt.value = true;
    oneFormValidate.value = [
      {
        pic: '',
        price: 0,
        cost: 0,
        original_price: 0,
        stock: 0,
        bar_code: '',
        bar_code_number: '',
        weight: 0,
        volume: 0,
        brokerage: 0,
        brokerage_two: 0,
        vip_price: 0,
      },
    ];

    generateHeader(attrs.value);
    manyFormValidate.value = [...oneFormBatch.value, ...(Array.isArray(data.attrs) ? data.attrs : [])];
  }

  setTimeout((e) => {
    checkAllGroup();
  }, 1000);
  watchActivity();
}
//关闭淘宝弹窗并生成数据；
function onClose(data) {
  modals.value = false;
  infoData(data, 1);
}

// 采集弹窗标题：支持平台列表由后端采集配置接口下发
const copyTitle = ref('商品采集');
function onGetPlatforms(platforms) {
  if (platforms?.length) {
    copyTitle.value = '复制' + platforms.map((item) => item.name).join('、');
  }
}

function checkMove(evt) {
  moveIndex.value = evt.draggedContext.index;
}
function end() {
  moveIndex.value = '';
  generate(1);
}
// 单独设置会员设置
function checkAllGroupChange() {
  checkAllGroup();
}
function checkAllGroup() {
  let endLength = attrs.value.length + 3;
  const hasVip = formValidate.value.is_vip == 1;
  if (formValidate.value.spec_type === 0) {
    columnsInstall.value = hasVip ? columns2.slice(0, endLength).concat(member) : columns2.slice(0, endLength);
  } else {
    columnsInstal2.value = hasVip
      ? columnsInstalM.value.slice(0, endLength).concat(member)
      : columnsInstalM.value.slice(0, endLength);
  }
}
// 添加优惠券
function addCoupon() {
  couponTemplates.value.isTemplate = true;
  couponTemplates.value.tableList();
}
// 规格中优惠券查看
function see(data, name, index) {
  tabName.value = name;
  tabIndex.value = index;
  goodsCoupon.value.isTemplate = true;
  goodsCoupon.value.tableList(3);
}
// 修改分佣比例
function changeDiscount(index, type = 'manyFormValidate') {
  // 根据分佣比例 vip_proportion 修改会员价,写回数字(字符串会破坏 el-input-number 输入)
  formTable(type)[index].vip_price = Number(
    (formTable(type)[index].price * (formTable(type)[index].vip_proportion / 100)).toFixed(2),
  );
}
// 修改会员价
function changeVipPrice(index, type = 'manyFormValidate') {
  // 售价为 0 时无法反推折扣,置 0 防止 Infinity 被钳制成极大值写回
  const row = formTable(type)[index];
  row.vip_proportion = row.price > 0 ? Number(((row.vip_price / row.price) * 100).toFixed(2)) : 0;
}
// 添加优惠券
function addGoodsCoupon(index, name) {
  tabIndex.value = index;
  tabName.value = name;
  goodsCoupon.value.isTemplate = true;
  goodsCoupon.value.tableList(3);
}
//对象数组去重；
function uniqueArray(arr) {
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
}
// 获取优惠券id数据
function nameId(id, names) {
  formValidate.value.coupon_ids = id;
  couponName.value = uniqueArray(names);
}
// 获取优惠券信息
function goodsCouponId(data) {
  formTable(tabName.value)[tabIndex.value]['coupon_id'] = data.id;
  formTable(tabName.value)[tabIndex.value]['coupon_name'] = data.title;
  goodsCoupon.value.isTemplate = false;
}
function handleClose(name) {
  let index = couponName.value.indexOf(name);
  couponName.value.splice(index, 1);
  let couponIds = formValidate.value.coupon_ids;
  couponIds.splice(index, 1);
  updateIds.value = couponIds;
  updateName.value = couponName.value;
}
// 添加运费模板
function addTemp() {
  templates.value.isTemplate = true;
}
function addVideo() {
  proxy.$videoModal((e) => {
    formValidate.value.video_link = e;
  });
}
// 删除视频；
function delVideo() {
  formValidate.value['video_link'] = '';
  progress.value = 0;
  videoIng.value = false;
  upload.videoIng = false;
}
function zh_uploadFile() {
  if (seletVideo.value == 1) {
    formValidate.value.video_link = videoLink.value;
  } else {
    proxy.$refs.refid.click();
  }
}
// 上传视频
function zh_uploadFile_change(evfile) {
  let suffix = evfile.target.files[0].name.substr(evfile.target.files[0].name.indexOf('.'));
  if (suffix.indexOf('.mp4') === -1) {
    return ElMessage.error('只能上传MP4文件');
  }
  let types = {
    key: evfile.target.files[0].name,
    contentType: evfile.target.files[0].type,
  };
  productGetTempKeysApi(types)
    .then((res) => {
      videoCloud
        .videoUpload({
          type: res.data.type,
          evfile: evfile,
          res: res,
          uploading(status, uploadProgress) {
            upload.videoIng = status;
            if (res.status == 200) {
              progress.value = 100;
            }
          },
        })
        .then((res) => {
          formValidate.value.video_link = res.url;
          ElMessage.success('视频上传成功');
          upload.videoIng = false;
        })
        .catch((res) => {
          ElMessage.error(res);
        });
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 上一页；
function upTab() {
  currentTab.value = (Number(currentTab.value) - 1).toString();
}
// 下一页；
function downTab() {
  currentTab.value = (Number(currentTab.value) + 1).toString();
}
// 属性弹窗回调函数；
function userSearchs() {
  productGetRule();
}
// 添加规则；
function addRule() {
  addattr.value.modal = true;
}
// 批量设置会员价/会员折扣（商品页已不再设置佣金）
function brokerageSetUp() {
  if (manyVipPrice.value == undefined && manyVipDiscount.value == undefined) {
    return ElMessage.error('请填写会员价或会员折扣后进行批量添加');
  }
  if (manyVipPrice.value != undefined && manyVipDiscount.value != undefined) {
    return ElMessage.error('会员价和会员折扣只能二选一添加');
  }
  for (let val of manyFormValidate.value) {
    if (manyVipPrice.value != undefined) {
      val['vip_price'] = manyVipPrice.value;
      // 售价为 0 时无法反推折扣,置 0 防止 Infinity
      val['vip_proportion'] = val.price > 0 ? Number(((val.vip_price / val.price) * 100).toFixed(2)) : 0;
    } else {
      val['vip_proportion'] = manyVipDiscount.value;
      val['vip_price'] = Number((val.price * (manyVipDiscount.value / 100)).toFixed(2));
    }
  }
}
// 批量设置会员价
function vipPriceSetUp() {
  if (manyVipPrice.value <= 0) {
    return ElMessage.error('请填写会员价在进行批量添加');
  } else {
    for (let val of manyFormValidate.value) {
      val['vip_price'] = manyVipPrice.value;
    }
  }
}
// 清空批量规格信息
function batchDel() {
  oneFormBatch.value = [
    {
      pic: '',
      price: void 0,
      cost: void 0,
      original_price: void 0,
      stock: void 0,
      bar_code: '',
      bar_code_number: '',
      weight: void 0,
      volume: void 0,
    },
  ];
}
function confirm(name) {
  formValidate.value.selectRule = name;
  attrs.value = [];
  if (formValidate.value.selectRule.trim().length <= 0) {
    return ElMessage.error('请选择属性');
  }
  ruleList.value.forEach((item, index) => {
    if (item.rule_name === formValidate.value.selectRule) {
      attrs.value = [...item.rule_value];
    }
  });
  canSel.value = true;
  generateAttr(attrs.value);
}
// 选择规格模板
function handleCommand(e) {}
// 获取商品属性模板；
function productGetRule() {
  productGetRuleApi().then((res) => {
    ruleList.value = res.data;
  });
}
// 获取运费模板；
function productGetTemplate() {
  productGetTemplateApi().then((res) => {
    templateList.value = res.data;
  });
}
function paramsGetTemplate() {
  paramListApi().then((res) => {
    paramsTypeList.value = res.data.list;
  });
}
function changeParamsType(e) {
  e ? getParams(e) : (formValidate.value.params_list = []);
}
function getParams(id) {
  paramInfoApi(id).then((res) => {
    formValidate.value.params_list = res.data.value;
  });
}
function isSubset(arr1, arr2) {
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
}
// 批量添加
function batchAdd() {
  let arr = [];
  for (let val of attrs.value) {
    if (oneFormBatch.value[0][val.value]) {
      arr.push(oneFormBatch.value[0][val.value]);
    }
  }

  // 批量设置商品规格属性
  const batchFields = [
    'pic',
    'price',
    'cost',
    'original_price',
    'stock',
    'weight',
    'volume',
    'bar_code',
    'bar_code_number',
  ];
  // const defaultFields = ['bar_code', 'bar_code_number'];

  for (let val of manyFormValidate.value) {
    const batch = oneFormBatch.value[0];
    // 如果存在筛选条件且满足条件,或无筛选条件时
    if (!arr.length || isSubset(val.attr_arr, arr)) {
      // 设置有值的批量字段
      batchFields.forEach((field) => {
        if (batch[field] && batch[field] !== undefined) {
          if (field === 'pic' && batch[field]) {
            val[field] = batch[field];
          } else if (field != 'pic') {
            val[field] = batch[field];
          }
        }
      });

      // 设置默认字段
      // defaultFields.forEach((field) => {
      //   this.$set(val, field, batch[field]);
      // });
    }
  }
}
function changeSpecImg(arr, img) {
  // 判断是否存在规格图
  let isHas = false;
  for (let i = 1; i < manyFormValidate.value.length; i++) {
    let item = manyFormValidate.value[i];
    if (item.pic && isSubset(item.attr_arr, arr)) {
      isHas = true;
      break;
    }
  }
  if (isHas) {
    ElMessageBox.confirm('可以同步修改下方该规格图片，确定要替换吗？', '提示', {
      confirmButtonText: '替换',
      cancelButtonText: '暂不',
      type: 'warning',
    })
      .then(() => {
        for (let val of manyFormValidate.value) {
          if (isSubset(val.attr_arr, arr)) {
            val['pic'] = img;
          }
        }
      })
      .catch(() => {});
  } else {
    for (let val of manyFormValidate.value) {
      if (isSubset(val.attr_arr, arr)) {
        val['pic'] = img;
      }
    }
  }
}
// 立即生成
function generate(type, isCopy, arr) {
  manyFormValidate.value = [];
  formValidate.value.header = [];
}
function clearAttr() {
  formDynamic.value.attrsName = '';
  formDynamic.value.attrsVal = '';
}

// 删除规格
function handleRemoveRole(index) {
  attrs.value.splice(index, 1);
  manyFormValidate.value.splice(index, 1);
  if (!attrs.value.length) {
    formValidate.value.header = [];
    manyFormValidate.value = [];
  } else {
    generateAttr(attrs.value);
  }
}
// 删除表格中 对应属性
function delAttrTable(val) {
  for (let i = 0; i < manyFormValidate.value.length; i++) {
    let item = manyFormValidate.value[i];
    if (item.attr_arr && item.attr_arr.includes(val)) {
      manyFormValidate.value.splice(i, 1);
      i--;
    }
  }
}
// 删除属性
function handleRemove2(item, index, val) {
  // 删除 manyFormValidate中 title = item.value 的属性值
  item.splice(index, 1);
  // this.generateAttr(this.attrs);
  delAttrTable(val);
}
// 新增规格
function handleAddRole() {
  let data = {
    value: formDynamic.value.attrsName,
    add_pic: 0,
    detail: [],
  };
  attrs.value.push(data);
}
function handleAddParams() {
  let data = {
    name: '',
    value: '',
  };
  formValidate.value.params_list.push(data);
}
function handleSaveAsTemplate() {
  ElMessageBox.prompt('', '请输入模板名称', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
  })
    .then(({ value }) => {
      let spec = attrs.value.map((item) => {
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
          ElMessage.success(res.msg);
          productGetRule();
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    })
    .catch(() => {});
}
// 新增一条属性
function addOneAttr(val, val2) {
  generateAttr(attrs.value, val2);
}
function handleFocus(val) {
  changeAttrValue.value = val;
}
function handleBlur() {
  changeAttrValue.value = '';
}
function handleSelImg(item) {
  proxy.$imgModal((e) => {
    item.pic = e.att_dir;
    changeSpecImg([item.value], e.att_dir);
  });
}
function handleRemoveImg(item) {
  item.pic = '';
}
// 规格名称改变
function attrChangeValue(i, val) {
  if (val.trim().length && attrs.value[i].detail.length) {
    generateHeader(attrs.value);
    if (manyFormValidate.value.length) {
      manyFormValidate.value.map((item, i) => {
        if (i > 0) {
          if (Object.keys(item.detail).includes(changeAttrValue.value)) {
            item.detail[val] = item.detail[changeAttrValue.value];
            item[val] = item[changeAttrValue.value];
            delete item.detail[changeAttrValue.value];
            delete item[changeAttrValue.value];
          }
        }
      });
      changeAttrValue.value = val;
    }
  } else {
    generateAttr(attrs.value);
  }
}
// 规格值改变
function attrDetailChangeValue(val, i) {
  if (manyFormValidate.value.length) {
    let key = attrs.value[i].value;
    manyFormValidate.value.map((item, i) => {
      if (i > 0) {
        if (Object.keys(item.detail).includes(key) && item.detail[key] === changeAttrValue.value) {
          item.detail[key] = val;
          let index = item.attr_arr.findIndex((item) => item === changeAttrValue.value);
          item.attr_arr[index] = val;
        }
      }
    });
    changeAttrValue.value = val;
  } else {
    generateAttr(attrs.value, 1);
  }
}
// 规格图片添加开关
function addPic(e, i) {
  if (e) {
    attrs.value.map((item, ii) => {
      if (ii !== i) {
        item['add_pic'] = 0;
      }
    });
    canSel.value = false;
  } else {
    canSel.value = true;
  }
}
// 规格拖拽排序后
function onMoveSpec() {
  generateAttr(attrs.value);
}
function changeCurrentIndex(i) {
  currentIndex.value = i;
}
// 生成商品规格表头
function generateHeader(data) {
  let specificationsColumns = data.map((item) => ({
    title: item.value,
    key: item.value,
    minWidth: 140,
    fixed: 'left',
  }));
  let arr = [...specificationsColumns, ...GoodsTableHead];
  formValidate.value['header'] = arr;
  tableKey.value += 1;
  columnsInstalM.value = arr;
}
/*
 * 生成属性
 * @param {Array} data 规格数据
 * */
function generateAttr(data, val) {
  generateHeader(data);
  const combinations = generateCombinations(data);
  // 如果combinations数量超过 500，则分批次生成属性
  let rows = [];
  if (combinations.length > 500) {
    const batchSize = Math.ceil(combinations.length / 500);
    for (let i = 0; i < combinations.length; i += batchSize) {
      setTimeout((e) => {
        let d = generateAttrBatch(data, combinations.slice(i, i + batchSize), val);
        rows = [...rows, ...d];
        manyFormValidate.value = [...oneFormBatch.value, ...rows];
      }, 0);
    }
  } else {
    rows = generateAttrBatch(data, combinations, val);
    manyFormValidate.value = [...oneFormBatch.value, ...rows];
  }
}

function normalizeNumber(value, fallback = null) {
  if (value === '' || value === undefined || value === null) return fallback;
  const number = Number(value);
  return Number.isFinite(number) ? number : fallback;
}

// 生成属性批次
function generateAttrBatch(data, combinations, val) {
  const existingItems = manyFormValidate.value.slice(1); // 排除第一项默认数据

  const rows = combinations.map((combination) => {
    const row = {
      attr_arr: combination,
      detail: {},
      title: '',
      key: '',
      price: 0,
      pic: '',
      original_price: 0,
      cost: 0,
      stock: 0,
      is_show: 1,
      is_default_select: 0,
      unique: '',
      weight: null,
      volume: null,
      brokerage: 0,
      brokerage_two: 0,
      vip_price: 0,
      vip_proportion: 0,
    };

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
          price: normalizeNumber(matchedItem.price, 0),
          cost: normalizeNumber(matchedItem.cost, 0),
          original_price: normalizeNumber(matchedItem.original_price, 0),
          stock: normalizeNumber(matchedItem.stock, 0),
          pic: matchedItem.pic,
          unique: matchedItem.unique || '',
          weight: normalizeNumber(matchedItem.weight),
          volume: normalizeNumber(matchedItem.volume),
          is_show: matchedItem.is_show || 1,
          is_default_select: matchedItem.is_default_select || 0,
          bar_code_number: matchedItem.bar_code_number || 0,
          brokerage: normalizeNumber(matchedItem.brokerage, 0),
          brokerage_two: normalizeNumber(matchedItem.brokerage_two, 0),
          vip_price: normalizeNumber(matchedItem.vip_price),
          vip_proportion: normalizeNumber(matchedItem.vip_proportion),
        });
      } else if (item.add_pic && combination.includes(val)) {
        const picItem = item.detail.find((e) => combination.includes(e.value));
        if (picItem) row.pic = picItem.pic;
      }
    });
    return row;
  });
  return rows;
}
// 切换默认选中规格
function changeDefaultSelect(e, index) {
  // 一个开启 其他关闭
  manyFormValidate.value.map((item, i) => {
    if (i !== index) {
      item.is_default_select = 0;
    }
  });
  if (e) manyFormValidate.value[index].is_show = 1;
}
// 改变是否显示
function changeDefaultShow(index) {
  // 如果默认选中开启 则不可隐藏
  if (manyFormValidate.value[index].is_default_select === 1) {
    manyFormValidate.value[index].is_show = 1;
    ElMessage.error('默认规格不可隐藏');
  }
}
// 生成规格组合
function generateCombinations(arr, prefix = []) {
  if (arr.length === 0) {
    return [prefix];
  }
  const [first, ...rest] = arr;
  return first.detail.flatMap((detail) => generateCombinations(rest, [...prefix, detail.value]));
}
// 添加属性（行内输入框回车/失焦触发，num 为空时忽略）
function createAttr(num, idx) {
  if (num) {
    // 判断是否存在同样属性
    var isExist = attrs.value[idx].detail.some((item) => item.value === num);
    if (isExist) {
      ElMessage.error('规格值已存在');
      return;
    }
    attrs.value[idx].detail.push({ value: num, pic: '' });
    if (manyFormValidate.value.length) {
      addOneAttr(attrs.value[idx].value, num);
    } else {
      generateAttr(attrs.value);
    }
    clearAttr();
  }
}
// 商品分类；
function goodsCategory() {
  cascaderListApi(1)
    .then((res) => {
      treeSelect.value = res.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 改变规格
function changeSpec(val) {
  formValidate.value.is_sub = 0;
}
// 详情
function getInfo() {
  spinShow.value = true;
  normalProductInfo(route.params.id)
    .then(async (res) => {
      let data = res.data;
      infoData(data);
      spinShow.value = false;
    })
    .catch((res) => {
      spinShow.value = false;
      ElMessage.error(res.msg || '加载失败');
    });
}
function handleRemove(i) {
  images.value.splice(i, 1);
  formValidate.value.slider_image.splice(i, 1);
  oneFormValidate.value[0].pic = formValidate.value.slider_image[0];
}
// 关闭图片上传模态框
function changeCancel(msg) {
  modalPic.value = false;
}
// 点击商品图
function modalPicTap(tit, picTitName = '', index = 0) {
  modalPic.value = true;
  isChoice.value = tit === 'dan' ? '单选' : '多选';
  picTit.value = picTitName;
  tableIndex.value = index;
}
// 获取单张图片信息
function getPic(pc) {
  switch (picTit.value) {
    case 'danFrom':
      formValidate.value.image = pc.att_dir;
      if (!route.params.id) {
        if (formValidate.value.spec_type === 0) {
          oneFormValidate.value[0].pic = pc.att_dir;
        } else {
          manyFormValidate.value.map((item) => {
            item.pic = pc.att_dir;
          });
          oneFormBatch.value[0].pic = pc.att_dir;
        }
      }
      break;
    case 'danTable':
      oneFormValidate.value[tableIndex.value].pic = pc.att_dir;
      break;
    case 'duopi':
      oneFormBatch.value[tableIndex.value].pic = pc.att_dir;
      break;
    case 'recommend_image':
      formValidate.value.recommend_image = pc.att_dir;
      break;
    default:
      if (manyFormValidate.value.length) manyFormValidate.value[tableIndex.value].pic = pc.att_dir;
  }
  modalPic.value = false;
}
function deleteRow(index) {
  formValidate.value.params_list.splice(index, 1);
}
// 获取多张图信息
function getPicD(pc) {
  images.value = pc;
  images.value.map((item) => {
    formValidate.value.slider_image.push(item.att_dir);
    formValidate.value.slider_image = formValidate.value.slider_image.splice(0, 10);
  });
  oneFormValidate.value[0].pic = formValidate.value.slider_image[0];
  modalPic.value = false;
}
// 提交
function handleSubmit() {
  formValidateRef.value.validate((valid) => {
    if (valid) {
      formValidate.value.type = type.value;
      // 商品页不再单独设置佣金，保存时统一关闭分销开关
      formValidate.value.is_sub = 0;
      let arr = formValidate.value.spec_type === 0 ? oneFormValidate.value : manyFormValidate.value;
      let item = JSON.parse(JSON.stringify(arr));
      if (formValidate.value.spec_type === 1) {
        if (item.length < 2) return ElMessage.warning('商品规格-规格数量最少1个');
        // 删除第一项
        item.shift();
      }
      for (let i = 0; i < item.length; i++) {
        if (item[i].stock > 1000000) {
          return ElMessage.error('规格库存-库存超出系统范围(1000000)');
        }
      }
      if (formValidate.value.is_vip === 1) {
        for (let i = 0; i < item.length; i++) {
          if (item[i].vip_price === null) {
            return ElMessage.error('营销设置-会员价不能为空');
          }
        }
      }
      if (
        formValidate.value.freight_config &&
        formValidate.value.freight_config.express &&
        formValidate.value.freight_config.express.type === 'template' &&
        !formValidate.value.freight_config.express.temp_id
      ) {
        return ElMessage.warning('商品信息-运费模板不能为空');
      }
      let activeIds = [];
      dataLabel.value.forEach((item) => {
        activeIds.push(item.id);
      });
      formValidate.value.label_id = activeIds;
      if (openSubimit.value) return;
      openSubimit.value = true;
      formValidate.value.description = formatRichText(content.value);
      if (formValidate.value.spec_type === 0) {
        formValidate.value.attrs = item;
        formValidate.value.header = [];
        formValidate.value.items = [];
        formValidate.value.is_copy = 0;
      } else {
        formValidate.value.items = attrs.value;
        formValidate.value.attrs = item;
        formValidate.value.is_copy = 1;
      }
      formValidate.value.product_type = 'normal';
      if (formValidate.value.spec_type === 0 && oneFormValidate.value.length > 0) {
        formValidate.value.weight = oneFormValidate.value[0].weight ?? 0;
        formValidate.value.volume = oneFormValidate.value[0].volume ?? 0;
      }
      if (formValidate.value.id) {
        normalProductUpdate(formValidate.value.id, formValidate.value)
          .then(async (res) => {
            openSubimit.value = false;
            ElMessage.success(res.msg);
            if (route.params.id === '0') {
              cacheDelete().catch((err) => {
                ElMessage.error(err.msg);
              });
            }
            setTimeout(() => {
              openSubimit.value = false;
              router.push({ path: Setting.routePre + '/product/normal/list' });
            }, 500);
          })
          .catch((res) => {
            setTimeout((e) => {
              openSubimit.value = false;
            }, 1000);
            ElMessage.error(res.msg);
          });
      } else {
        normalProductSave(formValidate.value)
          .then(async (res) => {
            openSubimit.value = false;
            ElMessage.success(res.msg);
            cacheDelete().catch((err) => {
              ElMessage.error(err.msg);
            });
            setTimeout(() => {
              openSubimit.value = false;
              router.push({ path: Setting.routePre + '/product/normal/list' });
            }, 500);
          })
          .catch((res) => {
            setTimeout((e) => {
              openSubimit.value = false;
            }, 1000);
            ElMessage.error(res.msg);
          });
      }
    } else {
      if (!formValidate.value.store_name) {
        return ElMessage.warning('商品信息-商品名称不能为空');
      } else if (!formValidate.value.cate_id.length) {
        return ElMessage.warning('商品信息-商品分类不能为空');
      } else if (!formValidate.value.unit_name) {
        return ElMessage.warning('商品信息-商品单位不能为空');
      } else if (!formValidate.value.slider_image.length) {
        return ElMessage.warning('商品信息-商品轮播图不能为空');
      } else if (!formValidate.value.delivery_methods || !formValidate.value.delivery_methods.length) {
        return ElMessage.warning('物流设置-至少选择一种物流方式');
      } else if (
        formValidate.value.freight_config &&
        formValidate.value.freight_config.express &&
        formValidate.value.freight_config.express.type === 'template' &&
        !formValidate.value.freight_config.express.temp_id
      ) {
        return ElMessage.warning('商品信息-运费模板不能为空');
      }
    }
  });
}
function changeTemplate(msg) {
  template.value = msg;
}
// 表单验证
function validate(prop, status, error) {
  if (status === false) {
    ElMessage.warning(error);
  }
}
// 移动
function handleDragStart(e, item) {
  dragging = item;
}
function handleDragEnd(e, item) {
  dragging = null;
}
function handleDragOver(e) {
  e.dataTransfer.dropEffect = 'move';
}
function handleDragEnter(e, item) {
  e.dataTransfer.effectAllowed = 'move';
  if (item === dragging) {
    return;
  }
  const newItems = [...formValidate.value.slider_image];
  const src = newItems.indexOf(dragging);
  const dst = newItems.indexOf(item);
  newItems.splice(dst, 0, ...newItems.splice(src, 1));
  formValidate.value.slider_image = newItems;
}
//对象数组去重；
function unique(arr) {
  const res = new Map();
  return arr.filter((arr) => !res.has(arr.product_id) && res.set(arr.product_id, 1));
}
// 商品id
function getProductId(data) {
  goods_modals.value = false;
  formValidate.value.recommend_list = unique(formValidate.value.recommend_list.concat(data));
}
// 选择推荐商品
function changeGoods() {
  goods_modals.value = true;
  nextTick(() => {
    if (goodslist.value) {
      goodslist.value.getList();
      goodslist.value.goodsCategory();
    }
  });
}
// 选择用户标签
function activeData(list) {
  labelShow.value = false;
  dataLabel.value = list;
}
// 选择商品标签
function activeLabel(data) {
  tagShow.value = false;
  formValidate.value.label_list = Array.from(new Set(data));
}
// 标签弹窗关闭
function labelClose() {
  labelShow.value = false;
  tagShow.value = false;
}
// 删除用户标签
function closeLabel(label) {
  let index = dataLabel.value.indexOf(dataLabel.value.filter((d) => d.id == label.id)[0]);
  dataLabel.value.splice(index, 1);
}
// 打开选择用户标签
function openLabel(row) {
  labelShow.value = true;
}
function handleRemoveRecommend(i) {
  formValidate.value.recommend_list.splice(i, 1);
}
// 打开的营销活动标签
// 活动插件（秒杀/砍价/拼团）安装并启用后才出现在活动优先级里；全都没有时由组件隐藏整行
const activityPluginKeywords = { 秒杀: 'seckill', 砍价: 'bargain', 拼团: 'combination' };
const activityLoaded = ref(false);
const enabledActivityPlugins = ref([]);
const activityOptions = computed(() => [
  '默认',
  ...Object.keys(activityPluginKeywords).filter((name) => enabledActivityPlugins.value.includes(name)),
]);
function getActivityPlugins() {
  pluginListApi()
    .then((res) => {
      const enabled = [];
      for (const item of res.data || []) {
        if (!item.installed || !item.enabled) continue;
        const marker = `${item.key || ''} ${item.path || ''}`.toLowerCase();
        for (const [name, keyword] of Object.entries(activityPluginKeywords)) {
          if (marker.includes(keyword) && !enabled.includes(name)) enabled.push(name);
        }
      }
      enabledActivityPlugins.value = enabled;
      activityLoaded.value = true;
      watchActivity();
    })
    .catch(() => {
      // 查询失败时按"仅有默认"处理，避免误展示未安装的活动
      activityLoaded.value = true;
      watchActivity();
    });
}
function watchActivity() {
  // 插件状态未返回前不裁剪，避免编辑回填的已保存活动被误删
  if (!activityLoaded.value || !formValidate.value.activity) return;
  const available = activityOptions.value;
  formValidate.value.activity = formValidate.value.activity.filter((el) => available.includes(el));
}
</script>
<style lang="scss" scoped>
@use './productAdd.scss' as *;
</style>
