<template>
  <div class="" id="shopp-manager" v-loading="spinShow">
    <pages-header
      ref="pageHeader"
      :title="editId ? '编辑网盘商品' : '添加网盘商品'"
      :backUrl="routePre + '/product/virtual/list'"
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
        <!-- 基础信息 -->
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

        <!-- 规格库存 -->
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
          @addGoodsCoupon="addGoodsCoupon"
          @see="see"
          @addVirtual="addVirtual"
        ></spec-stock>

        <!-- 商品详情 -->
        <product-detail
          v-show="currentTab === '3'"
          :contents="contents"
          :content="content"
          :store-name="formValidate.store_name"
          @getEditorContent="getEditorContent"
        ></product-detail>

        <!-- 会员价/佣金 -->
        <price-commission
          v-show="currentTab === '4'"
          :formValidate="formValidate"
          :oneFormValidate="oneFormValidate"
          :manyFormValidate="manyFormValidate"
          :columnsInstall="columnsInstall"
          :columnsInstal2="columnsInstal2"
          v-model:manyBrokerage="manyBrokerage"
          v-model:manyBrokerageTwo="manyBrokerageTwo"
          v-model:manyVipPrice="manyVipPrice"
          v-model:manyVipDiscount="manyVipDiscount"
          @checkAllGroupChange="checkAllGroupChange"
          @changeVipPrice="changeVipPrice"
          @changeDiscount="changeDiscount"
          @brokerageSetUp="brokerageSetUp"
        ></price-commission>

        <!-- 营销设置 -->
        <marketing-setting
          v-show="currentTab === '5'"
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

        <!-- 其他设置 -->
        <other-setting
          v-show="currentTab === '6'"
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

        <el-form-item class="mt20">
          <el-button v-if="currentTab !== '1'" @click="upTab">上一项</el-button>
          <el-button v-if="currentTab !== '6'" type="primary" @click="downTab">下一项</el-button>
          <el-button type="primary" :loading="saving" @click="handleSubmit">保存</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 图片选择组件 -->
    <el-dialog v-model="modalPic" width="950px" scrollable title="上传商品图" :close-on-click-modal="false">
      <uploadPictures
        :isChoice="isChoice"
        @getPic="getPic"
        @getPicD="getPicD"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="modalPic"
      />
    </el-dialog>

    <!-- 优惠券列表 -->
    <coupon-list
      ref="couponTemplates"
      @nameId="nameId"
      :couponids="formValidate.coupon_ids"
      :updateIds="updateIds"
      :updateName="updateName"
    />

    <!-- 用户标签 -->
    <el-dialog
      v-model="labelShow"
      title="请选择用户标签"
      :show-close="true"
      width="540px"
      :close-on-click-modal="false"
    >
      <userLabel @activeData="activeData" @close="labelClose" />
    </el-dialog>

    <!-- 商品标签 -->
    <el-dialog v-model="tagShow" title="请选择商品标签" :show-close="true" width="540px" :close-on-click-modal="false">
      <goodsLabel :defaultLabelList="labelList" @activeLabel="activeLabel" @close="labelClose" />
    </el-dialog>

    <!-- 推荐商品 -->
    <el-dialog v-model="goods_modals" title="商品列表" footerHide class="paymentFooter" scrollable width="1000px">
      <goods-list v-if="goods_modals" ref="goodslist" :ischeckbox="true" @getProductId="getProductId" />
    </el-dialog>

    <!-- 运费模板 -->
    <freightTemplate
      :template="template"
      v-on:changeTemplate="changeTemplate"
      @addSuccess="productGetTemplate"
      ref="templates"
    />

    <!-- 属性模板 -->
    <add-attr ref="addattr" @getList="userSearchs" />
  </div>
</template>

<script setup>
import { computed, getCurrentInstance, nextTick, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage, ElMessageBox } from 'element-plus';
import { virtualApi } from '../api';
import { cascaderListApi } from '@/api/product';
import {
  productGetRuleApi,
  productGetTemplateApi,
  ruleAddApi,
  paramListApi,
  paramInfoApi,
  productProtectionListApi,
  productLabelUseListApi,
} from '@/api/product';
import uploadPictures from '@/components/uploadPictures';
import couponList from '@/components/couponList';
import freightTemplate from '@/components/freightTemplate';
import addAttr from '../../../../pages/product/productAttr/addAttr';
import goodsList from '@/components/goodsList/index';
import BasicInfo from '../../../../pages/product/productAdd/components/BasicInfo.vue';
import SpecStock from '../../../../pages/product/productAdd/components/SpecStock.vue';
import ProductDetail from '../../../../pages/product/productAdd/components/ProductDetail.vue';
import PriceCommission from '../../../../pages/product/productAdd/components/PriceCommission.vue';
import MarketingSetting from '../../../../pages/product/productAdd/components/MarketingSetting.vue';
import OtherSetting from '../../../../pages/product/productAdd/components/OtherSetting.vue';
import LogisticsSetting from '../../../../pages/product/productAdd/components/LogisticsSetting.vue';
import {
  VirtualTableHead2,
  columns2,
  CustomList,
  RuleValidate,
} from '../../../../pages/product/productAdd/defaultData.js';
import Setting from '@/setting';
import { formatRichText } from '@/utils/editorImg';
import { useMediaStore } from '@/store/module/media';
import userLabel from '@/components/labelList';
import goodsLabel from '@/components/goodsLabel';
import modalForm from '@/utils/modalForm';

// 模板 :rules 绑定的是小驼峰 ruleValidate，需从导入的 RuleValidate 取一份（与实物商品页一致）
const ruleValidate = RuleValidate;

defineOptions({ name: 'virtualProductAdd' });

const { proxy } = getCurrentInstance();
const router = useRouter();
const routePre = Setting.routePre;
const mediaStore = useMediaStore();

const props = defineProps({
  editId: { type: Number, default: 0 },
});

const pageHeader = ref(null);
const formValidateRef = ref(null);
const specStock = ref(null);
const couponTemplates = ref(null);
const goodslist = ref(null);
const templates = ref(null);
const addattr = ref(null);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '120px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const spinShow = ref(false);
const saving = ref(false);
const currentTab = ref('1');
const headTab = ref([
  { tit: '基础信息', name: '1' },
  { tit: '规格库存', name: '2' },
  { tit: '商品详情', name: '3' },
  { tit: '会员价/佣金', name: '4' },
  { tit: '营销设置', name: '5' },
  { tit: '其他设置', name: '6' },
]);
const type = ref(0);
// 网盘商品类型固定为3
const formValidate = ref({
  store_name: '',
  store_info: '',
  cate_id: [],
  label_id: [],
  slider_image: [],
  image: '',
  recommend_image: '',
  description: '',
  keyword: '',
  unit_name: '件',
  price: 0,
  ot_price: 0,
  cost: 0,
  stock: 0,
  ficti: 0,
  give_integral: 0,
  sort: 0,
  is_show: 1,
  is_vip: 0,
  is_vip_admin: 0,
  vip_product: 0,
  spec_type: 0,
  is_sub: 0,
  coupon_ids: [],
  couponName: [],
  label_list: [],
  params_list: [],
  protection_list: [],
  custom_form: [],
  recommend: [], //商品推荐
  recommend_list: [],
  activity: ['默认', '秒杀', '砍价', '拼团'],
  presale: 0,
  presale_time: [],
  is_limit: 0,
  limit_type: 0,
  limit_num: 0,
  min_qty: 1,
  logistics: ['1'],
  freight: 2,
  postage: 0,
  temp_id: '',
  // 视频
  video_link: '',
  video_open: 0,
  seletVideo: 0,
  // 网盘扩展字段
  download_url: '',
  download_password: '',
  is_auto_send: 1,
  // SKU
  attrs: [],
  items: [],
  header: [],
  selectRule: '',
  // 类型
  virtual_type: 3,
  is_virtual: 0,
  type: 0,
});
// 单规格
const oneFormValidate = ref([
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
    // 网盘信息（SKU 级，单规格时仅一条）
    download_url: '',
    download_password: '',
  },
]);
// 多规格
const manyFormValidate = ref([]);
// 批量设置
const oneFormBatch = ref([
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
  },
]);
const attrs = ref([]);
const ruleList = ref([]);
const templateList = ref([]);
const formDynamic = ref({ attrsName: '', attrsVal: '' });
const canSel = ref(true);
const tableKey = ref(0);
const changeAttrValue = ref('');
// 基础信息
const goodsType = ref([{ id: 3, tit: '网盘商品', tit2: '网盘发货' }]);
const treeSelect = ref([]);
const tileLabelList = ref([]);
const labelList = ref([]);
// 图片上传
const modalPic = ref(false);
const isChoice = ref('');
const picTit = ref('');
const tableIndex = ref(0);
const progress = ref(0);
const videoIng = ref(false);
const upload = ref({ videoIng: false });
const gridPic = { xl: 6, lg: 8, md: 12, sm: 12, xs: 12 };
const gridBtn = { xl: 4, lg: 8, md: 8, sm: 8, xs: 8 };
// 商品详情
const content = ref('');
const contents = ref('');
// 运费
const template = ref(false);
// 物流设置
const logistics = ref([]);
// 商品参数
const paramsType = ref(null);
const paramsTypeList = ref([]);
// 服务保障
const protectionList = ref([]);
// 自定义表单
const customBtn = ref(0);
// CustomList 由顶部 import 直接暴露给模板
// 营销
const couponName = ref([]);
const updateIds = ref([]);
const updateName = ref([]);
const dataLabel = ref([]);
const labelShow = ref(false);
const tagShow = ref(false);
const activity = ref({ 默认: 'red', 秒杀: 'blue', 砍价: 'green', 拼团: 'yellow' });
// 会员/佣金
const manyBrokerage = ref(undefined);
const manyBrokerageTwo = ref(undefined);
const manyVipPrice = ref(undefined);
const manyVipDiscount = ref(undefined);
const columnsInstall = ref([]);
const columnsInstal2 = ref([]);
const rakeBack = [
  { title: '一级返佣(元)', slot: 'brokerage', align: 'center', width: 95 },
  { title: '二级返佣(元)', slot: 'brokerage_two', align: 'center', width: 95 },
];
const member = [
  { title: '会员价', slot: 'vip_price', align: 'center', width: 95 },
  { title: '会员折扣', slot: 'vip_proportion', align: 'center', width: 95 },
];
const columnsInstalM = ref([]);
// 商品标签
const goods_modals = ref(false);
const videoLink = ref('');
// 非响应式内部状态（原代码未在 data 中声明）
let dragging = null;
let currentIndex = 0;

// ----- 初始化数据加载 -----
async function loadCategory() {
  try {
    const res = await cascaderListApi(1);
    treeSelect.value = res.data || [];
  } catch (e) {
    treeSelect.value = [];
  }
}
async function loadProduct() {
  spinShow.value = true;
  try {
    const res = await virtualApi('/product/' + props.editId);
    const data = res.data || {};
    formValidate.value.store_name = data.store_name || '';
    formValidate.value.store_info = data.store_info || '';
    formValidate.value.price = data.price || 0;
    formValidate.value.ot_price = data.ot_price || 0;
    formValidate.value.cost = data.cost || 0;
    formValidate.value.stock = data.stock || 0;
    formValidate.value.ficti = data.ficti || 0;
    formValidate.value.unit_name = data.unit_name || '件';
    formValidate.value.sort = data.sort || 0;
    formValidate.value.is_show = data.is_show !== undefined ? data.is_show : 1;
    formValidate.value.is_vip = data.is_vip || 0;
    formValidate.value.is_vip_admin = data.is_vip_admin || 0;
    formValidate.value.vip_product = data.vip_product !== undefined ? data.vip_product : 0;
    formValidate.value.keyword = data.keyword || '';
    formValidate.value.description = data.description || '';
    formValidate.value.give_integral = data.give_integral || 0;
    formValidate.value.is_auto_send = data.is_auto_send !== undefined ? data.is_auto_send : 1;
    formValidate.value.spec_type = data.spec_type !== undefined ? data.spec_type : 0;
    formValidate.value.is_sub = data.is_sub ?? 0;
    formValidate.value.recommend = data.recommend || [];
    formValidate.value.recommend_list = data.recommend_list || [];
    formValidate.value.label_list = data.label_list || [];
    formValidate.value.params_list = data.params_list || [];
    formValidate.value.protection_list = data.protection_list || [];
    formValidate.value.custom_form = data.custom_form || [];
    customBtn.value = data.custom_form && data.custom_form.length > 0 ? 1 : 0;
    formValidate.value.logistics = data.logistics || ['1'];
    formValidate.value.freight = data.freight !== undefined ? data.freight : 2;
    formValidate.value.postage = data.postage || 0;
    formValidate.value.temp_id = data.temp_id || '';
    formValidate.value.min_qty = data.min_qty || 1;
    formValidate.value.is_limit = data.is_limit || false;
    formValidate.value.limit_type = data.limit_type || 0;
    formValidate.value.limit_num = data.limit_num || 0;
    formValidate.value.presale = data.presale || false;
    formValidate.value.presale_time = data.presale_time || [];
    formValidate.value.video_open = data.video_open || 0;
    formValidate.value.seletVideo = data.seletVideo || 0;
    formValidate.value.video_link = data.video_link || '';
    if (data.slider_image) {
      const images =
        typeof data.slider_image === 'string' ? JSON.parse(data.slider_image || '[]') : data.slider_image || [];
      formValidate.value.slider_image = images;
      formValidate.value.image = images[0] || '';
    }
    // 分类回显：级联选项的 value 是数字，字符串 ID 无法命中节点，这里统一转成数字
    const cate = data.cate_id;
    const cateArr = Array.isArray(cate) ? cate : cate ? String(cate).split(',') : [];
    formValidate.value.cate_id = cateArr.map((v) => Number(v)).filter((v) => !Number.isNaN(v) && v > 0);
    // 规格回显（优先使用 attr/attrs 中的数据）
    if (data.attr && typeof data.attr === 'object') {
      oneFormValidate.value[0].price = parseFloat(data.attr.price) || 0;
      oneFormValidate.value[0].cost = parseFloat(data.attr.cost) || 0;
      oneFormValidate.value[0].ot_price = parseFloat(data.attr.ot_price) || 0;
      oneFormValidate.value[0].stock = parseInt(data.attr.stock) || 0;
      oneFormValidate.value[0].pic = data.attr.pic || formValidate.value.image;
      oneFormValidate.value[0].vip_price = parseFloat(data.attr.vip_price) || 0;
      oneFormValidate.value[0].brokerage = parseFloat(data.attr.brokerage) || 0;
      oneFormValidate.value[0].brokerage_two = parseFloat(data.attr.brokerage_two) || 0;
      // 单规格网盘信息（SKU 级）
      oneFormValidate.value[0].download_url = data.attr.download_url || '';
      oneFormValidate.value[0].download_password = data.attr.download_password || '';
    } else {
      oneFormValidate.value[0].price = data.price || 0;
      oneFormValidate.value[0].cost = data.cost || 0;
      oneFormValidate.value[0].ot_price = data.ot_price || 0;
      oneFormValidate.value[0].stock = data.stock || 0;
      oneFormValidate.value[0].pic = formValidate.value.image;
    }
    // 多规格回显：重建规格组与表格数据（含各 SKU 网盘信息）；单规格值也会只有一条 SKU，不设下限会漏回显
    if (data.spec_type === 1 && Array.isArray(data.attrs) && data.attrs.length >= 1) {
      restoreManySku(data.attrs);
    }
    // 商品详情回显
    contents.value = data.description || '';
    // 触发 checkAllGroup 构建会员价/佣金表格列
    setTimeout(() => {
      checkAllGroup(formValidate.value.is_sub);
    }, 500);
  } catch (e) {
    ElMessage.error('加载商品信息失败');
  }
  spinShow.value = false;
}
function productGetRule() {
  productGetRuleApi().then((res) => {
    ruleList.value = res.data;
  });
}
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
function getProtectionList() {
  productProtectionListApi({ page: 0, limit: 0, status: 1 }).then((res) => {
    protectionList.value = res.data.list;
  });
}
function getproductLabelUseListApi() {
  productLabelUseListApi().then((res) => {
    tileLabelList.value = res.data.flatMap((item) => item.list);
    let labelListData = res.data;
    if (formValidate.value.label_list.length) {
      formValidate.value.label_list.map((el) => {
        labelListData.map((re) => {
          re.list.map((label) => {
            if (label.id === el) label.active = true;
            else label.active = false;
          });
        });
      });
    } else {
      labelListData.map((el) => {
        el.list.map((label) => {
          label.active = false;
        });
      });
    }
    labelList.value = labelListData;
  });
}
// ----- 商品类型切换（固定为网盘商品）-----
function virtualbtn(index, type) {
  // 网盘商品类型固定为3，不切换
}
// ----- Tab 切换 -----
function upTab() {
  currentTab.value = (Number(currentTab.value) - 1).toString();
}
function downTab() {
  currentTab.value = (Number(currentTab.value) + 1).toString();
}
// ----- 基础信息事件 -----
function addCate() {
  modalForm(import('@/api/product').then((m) => m.productCreateApi())).then(() => loadCategory());
}
function addGoodsTag() {
  tagShow.value = true;
}
function addVideo() {
  proxy.$videoModal((e) => {
    formValidate.value.video_link = e;
  });
}
function delVideo() {
  formValidate.value.video_link = '';
  progress.value = 0;
  upload.value.videoIng = false;
}
function handleDragStart(e, item) {
  dragging = item;
}
function handleDragEnd() {
  dragging = null;
}
function handleDragOver(e) {
  e.dataTransfer.dropEffect = 'move';
}
function handleDragEnter(e, item) {
  if (item === dragging) return;
  const newItems = [...formValidate.value.slider_image];
  const src = newItems.indexOf(dragging);
  const dst = newItems.indexOf(item);
  newItems.splice(dst, 0, ...newItems.splice(src, 1));
  formValidate.value.slider_image = newItems;
}
function handleRemove(i) {
  formValidate.value.slider_image.splice(i, 1);
  oneFormValidate.value[0].pic = formValidate.value.slider_image[0] || '';
}
// ----- 图片选择 -----
function modalPicTap(tit, picTitVal = '', index = 0) {
  modalPic.value = true;
  isChoice.value = tit === 'dan' ? '单选' : '多选';
  picTit.value = picTitVal;
  tableIndex.value = index;
}
function getPic(pc) {
  switch (picTit.value) {
    case 'danFrom':
      formValidate.value.image = pc.att_dir;
      if (!props.editId) {
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
    default:
      if (manyFormValidate.value.length) manyFormValidate.value[tableIndex.value].pic = pc.att_dir;
  }
  modalPic.value = false;
}
function getPicD(pc) {
  pc.map((item) => {
    formValidate.value.slider_image.push(item.att_dir);
    formValidate.value.slider_image = formValidate.value.slider_image.splice(0, 10);
  });
  oneFormValidate.value[0].pic = formValidate.value.slider_image[0] || '';
  modalPic.value = false;
}
// ----- 规格库存事件 -----
function changeSpec() {
  formValidate.value.is_sub = 0;
}
function confirm(name) {
  formValidate.value.selectRule = name;
  attrs.value = [];
  if (!name.trim()) return ElMessage.error('请选择属性');
  ruleList.value.forEach((item) => {
    if (item.rule_name === name) attrs.value = [...item.rule_value];
  });
  canSel.value = true;
  generateAttr(attrs.value);
}
function onMoveSpec() {
  generateAttr(attrs.value);
}
function changeCurrentIndex(i) {
  currentIndex = i;
}
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
function handleAddRole() {
  attrs.value.push({
    value: formDynamic.value.attrsName,
    add_pic: 0,
    detail: [],
  });
}
function handleSaveAsTemplate() {
  if (!attrs.value.length) return ElMessage.warning('请先添加规格');
  ElMessageBox.prompt('', '请输入模板名称', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
  })
    .then(({ value }) => {
      let spec = attrs.value.map((item) => ({
        value: item.value,
        detail: item.detail.map((e) => e.value),
      }));
      ruleAddApi({ rule_name: value, spec }, 0).then((res) => {
        ElMessage.success(res.msg);
        productGetRule();
      });
    })
    .catch(() => {});
}
// 添加属性（行内输入框回车/失焦触发，num 为空时忽略）
function createAttr(num, idx) {
  if (num) {
    const isExist = attrs.value[idx].detail.some((item) => item.value === num);
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
function addOneAttr(val, val2) {
  generateAttr(attrs.value, val2);
}
function clearAttr() {
  formDynamic.value.attrsName = '';
  formDynamic.value.attrsVal = '';
}
function addPic(e, i) {
  if (e) {
    attrs.value.map((item, ii) => {
      if (ii !== i) item['add_pic'] = 0;
    });
    canSel.value = false;
  } else {
    canSel.value = true;
  }
}
function handleRemove2(item, index, val) {
  item.splice(index, 1);
  delAttrTable(val);
}
function delAttrTable(val) {
  for (let i = 0; i < manyFormValidate.value.length; i++) {
    let item = manyFormValidate.value[i];
    if (item.attr_arr && item.attr_arr.includes(val)) {
      manyFormValidate.value.splice(i, 1);
      i--;
    }
  }
}
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
function batchAdd() {
  let arr = [];
  for (let val of attrs.value) {
    if (oneFormBatch.value[0][val.value]) arr.push(oneFormBatch.value[0][val.value]);
  }
  const batchFields = ['pic', 'price', 'cost', 'ot_price', 'stock', 'weight', 'volume', 'bar_code', 'bar_code_number'];
  for (let val of manyFormValidate.value) {
    const batch = oneFormBatch.value[0];
    if (!arr.length || isSubset(val.attr_arr, arr)) {
      batchFields.forEach((field) => {
        if (batch[field] && batch[field] !== undefined) {
          if (field === 'pic' && batch[field]) val[field] = batch[field];
          else if (field !== 'pic') val[field] = batch[field];
        }
      });
    }
  }
}
function batchDel() {
  oneFormBatch.value = [
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
    },
  ];
}
function isSubset(arr1, arr2) {
  const set1 = new Set(arr1);
  const set2 = new Set(arr2);
  for (let elem of set2) {
    if (!set1.has(elem)) return false;
  }
  return true;
}
function userSearchs() {
  productGetRule();
}
function generateHeader(data) {
  let specificationsColumns = data.map((item) => ({
    title: item.value,
    key: item.value,
    minWidth: 140,
    fixed: 'left',
  }));
  let arr = [...specificationsColumns, ...VirtualTableHead2];
  formValidate.value['header'] = arr;
  tableKey.value += 1;
  columnsInstalM.value = arr;
}
function generateAttr(data, val) {
  generateHeader(data);
  let arr = data;
  const combinations = generateCombinations(arr);
  let rows = [];
  if (combinations.length > 500) {
    const batchSize = Math.ceil(combinations.length / 500);
    for (let i = 0; i < combinations.length; i += batchSize) {
      setTimeout(() => {
        let d = generateAttrBatch(arr, combinations.slice(i, i + batchSize), val);
        rows = [...rows, ...d];
        manyFormValidate.value = [...oneFormBatch.value, ...rows];
      }, 0);
    }
  } else {
    rows = generateAttrBatch(arr, combinations, val);
    manyFormValidate.value = [...oneFormBatch.value, ...rows];
  }
}
function generateAttrBatch(data, combinations, val) {
  let existingItems = manyFormValidate.value.slice(1);
  const rows = combinations.map((combination) => {
    let row = {
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
      download_url: '',
      download_password: '',
    };
    data.forEach((item, i) => {
      const value = combination[i];
      row[item.value] = value;
      row.title = item.value;
      row.key = item.value;
      row.detail[item.value] = value;
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
          brokerage: matchedItem.brokerage,
          brokerage_two: matchedItem.brokerage_two,
          vip_price: matchedItem.vip_price,
          vip_proportion: matchedItem.vip_proportion,
          download_url: matchedItem.download_url || '',
          download_password: matchedItem.download_password || '',
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
// 多规格编辑回显：根据后端返回的 SKU 列表（含规格映射 detail 与网盘信息）重建规格组与表格
function restoreManySku(skuRows) {
  // 注意：形参不能叫 attrs，否则遮蔽外层同名 ref，规格编辑区拿不到数据、保存时 items 也会提交空数组
  const specMap = {};
  skuRows.forEach((a) => {
    const detail = a.detail || {};
    Object.keys(detail).forEach((name) => {
      if (!specMap[name]) specMap[name] = [];
      const value = detail[name];
      if (!specMap[name].includes(value)) specMap[name].push(value);
    });
  });
  attrs.value = Object.keys(specMap).map((name) => ({
    value: name,
    add_pic: 0,
    detail: specMap[name].map((value) => ({ value, pic: '' })),
  }));
  if (!attrs.value.length) return;
  formValidate.value.items = attrs.value;
  generateHeader(attrs.value);
  const combinations = generateCombinations(attrs.value);
  const rows = generateAttrBatch(attrs.value, combinations);
  // 回填各 SKU 的价格/库存/网盘信息
  rows.forEach((row) => {
    const values = row.attr_arr || [];
    const match = skuRows.find((a) => {
      const detailValues = a.detail ? Object.values(a.detail) : [];
      return arraysEqual(detailValues, values);
    });
    if (match) {
      row.price = parseFloat(match.price) || 0;
      row.cost = parseFloat(match.cost) || 0;
      row.ot_price = parseFloat(match.ot_price) || 0;
      row.stock = parseInt(match.stock) || 0;
      row.unique = match.unique || '';
      // 规格图：SKU 表 image → 行 pic（用户端规格图/编辑回显）
      row.pic = match.pic || '';
      row.download_url = match.download_url || '';
      row.download_password = match.download_password || '';
    }
  });
  manyFormValidate.value = [...oneFormBatch.value, ...rows];
  formValidate.value.spec_type = 1;
  formValidate.value.is_copy = 1;
  // 默认选中第一条 SKU
  if (manyFormValidate.value.length > 1) {
    manyFormValidate.value[1].is_default_select = 1;
  }
}
function generateCombinations(arr, prefix = []) {
  if (arr.length === 0) return [prefix];
  const [first, ...rest] = arr;
  return first.detail.flatMap((detail) => generateCombinations(rest, [...prefix, detail.value]));
}
function arraysEqual(a, b) {
  if (!a || !b) return false;
  if (a.length !== b.length) return false;
  for (let i = 0; i < a.length; i++) {
    if (a[i] !== b[i]) return false;
  }
  return true;
}
function changeSpecImg(arr, img) {
  let isHas = false;
  for (let i = 1; i < manyFormValidate.value.length; i++) {
    if (manyFormValidate.value[i].pic && isSubset(manyFormValidate.value[i].attr_arr, arr)) {
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
          if (isSubset(val.attr_arr, arr)) val['pic'] = img;
        }
      })
      .catch(() => {});
  } else {
    for (let val of manyFormValidate.value) {
      if (isSubset(val.attr_arr, arr)) val['pic'] = img;
    }
  }
}
function changeDefaultSelect(e, index) {
  manyFormValidate.value.map((item, i) => {
    if (i !== index) item.is_default_select = 0;
  });
  if (e) manyFormValidate.value[index].is_show = 1;
}
function changeDefaultShow(index) {
  if (manyFormValidate.value[index].is_default_select === 1) {
    manyFormValidate.value[index].is_show = 1;
    ElMessage.error('默认规格不可隐藏');
  }
}
function see(data, name, index) {}
function addVirtual(index, name) {}
function addGoodsCoupon(index, name) {}
// ----- 商品详情 -----
function getEditorContent(data) {
  content.value = data;
}
// ----- 会员价/佣金 -----
function checkAllGroupChange() {
  checkAllGroup();
}
function checkAllGroup() {
  let endLength = attrs.value.length + 3;
  const hasCommission = formValidate.value.is_sub == 1;
  const hasVip = formValidate.value.is_vip == 1;
  if (formValidate.value.spec_type === 0) {
    if (hasCommission && hasVip) columnsInstall.value = columns2.slice(0, endLength).concat(rakeBack).concat(member);
    else if (hasVip) columnsInstall.value = columns2.slice(0, endLength).concat(member);
    else if (hasCommission) columnsInstall.value = columns2.slice(0, endLength).concat(rakeBack);
    else columnsInstall.value = columns2.slice(0, endLength);
  } else {
    if (hasCommission && hasVip)
      columnsInstal2.value = columnsInstalM.value
        .slice(0, endLength + 1)
        .concat(rakeBack)
        .concat(member);
    else if (hasVip) columnsInstal2.value = columnsInstalM.value.slice(0, endLength).concat(member);
    else if (hasCommission) columnsInstal2.value = columnsInstalM.value.slice(0, endLength).concat(rakeBack);
    else columnsInstal2.value = columnsInstalM.value.slice(0, endLength);
  }
}
function changeDiscount(index, type = 'manyFormValidate') {
  const list = type === 'oneFormValidate' ? oneFormValidate.value : manyFormValidate.value;
  list[index].vip_price = (list[index].price * (list[index].vip_proportion / 100)).toFixed(2);
}
function changeVipPrice(index, type = 'manyFormValidate') {
  const list = type === 'oneFormValidate' ? oneFormValidate.value : manyFormValidate.value;
  list[index].vip_proportion = ((list[index].vip_price / list[index].price) * 100).toFixed(2);
}
function brokerageSetUp() {
  if (formValidate.value.is_sub == 1) {
    if (manyBrokerage.value <= 0 || manyBrokerageTwo.value <= 0) return ElMessage.error('请填写返佣金额后进行批量添加');
  }
  if (formValidate.value.is_vip == 1) {
    if (manyVipPrice.value <= 0) return ElMessage.error('请填写会员价后进行批量添加');
  }
  if (formValidate.value.is_sub == 1 && formValidate.value.is_vip == 1) {
    if (manyBrokerage.value <= 0 || manyBrokerageTwo.value <= 0) return ElMessage.error('请填写完金额后进行批量添加');
    if (manyVipPrice.value > 0 && manyVipDiscount.value > 0) return ElMessage.error('会员价和会员折扣只能二选一添加');
  }
  for (let val of manyFormValidate.value) {
    manyBrokerage.value !== undefined && (val['brokerage'] = manyBrokerage.value);
    manyBrokerageTwo.value !== undefined && (val['brokerage_two'] = manyBrokerageTwo.value);
    if (manyVipPrice.value !== undefined) {
      val['vip_price'] = manyVipPrice.value;
      val['vip_proportion'] = ((val.vip_price / val.price) * 100).toFixed(2);
    } else if (manyVipDiscount.value !== undefined) {
      val['vip_proportion'] = manyVipDiscount.value;
      val['vip_price'] = (val.price * (manyVipDiscount.value / 100)).toFixed(2);
    }
  }
}
// ----- 营销设置 -----
function addCoupon() {
  couponTemplates.value.isTemplate = true;
  couponTemplates.value.tableList();
}
function handleClose(name) {
  let index = couponName.value.indexOf(name);
  couponName.value.splice(index, 1);
  let couponIds = formValidate.value.coupon_ids;
  couponIds.splice(index, 1);
  updateIds.value = couponIds;
  updateName.value = couponName.value;
}
function nameId(id, names) {
  formValidate.value.coupon_ids = id;
  couponName.value = uniqueArray(names);
}
function uniqueArray(arr) {
  const seen = {};
  return arr.filter((item) => {
    const key = JSON.stringify(item);
    if (seen[key]) return false;
    seen[key] = true;
    return true;
  });
}
function onchangeTime(e) {
  formValidate.value.presale_time = e;
}
function openLabel() {
  labelShow.value = true;
}
function closeLabel(label) {
  let index = dataLabel.value.indexOf(dataLabel.value.filter((d) => d.id == label.id)[0]);
  dataLabel.value.splice(index, 1);
}
function addLabel() {
  modalForm(import('@/api/user').then((m) => m.userLabelAddApi(0))).then(() => userLabelRefresh());
}
function handleRemoveRecommend(i) {
  formValidate.value.recommend_list.splice(i, 1);
}
function changeGoods() {
  goods_modals.value = true;
  nextTick(() => {
    goodslist.value.getList();
    goodslist.value.goodsCategory();
  });
}
function getProductId(data) {
  goods_modals.value = false;
  formValidate.value.recommend_list = unique(formValidate.value.recommend_list.concat(data));
}
function unique(arr) {
  const res = new Map();
  return arr.filter((arr) => !res.has(arr.product_id) && res.set(arr.product_id, 1));
}
// ----- 标签 -----
function activeData(dataLabelVal) {
  labelShow.value = false;
  dataLabel.value = dataLabelVal;
}
function activeLabel(data) {
  tagShow.value = false;
  formValidate.value.label_list = Array.from(new Set(data));
}
function labelClose() {
  labelShow.value = false;
  tagShow.value = false;
}
function userLabelRefresh() {
  getproductLabelUseListApi();
}
// ----- 其他设置 -----
function customMessBtn(e) {
  if (!e) formValidate.value.custom_form = [];
  customBtn.value = e;
}
function addcustom() {
  if (formValidate.value.custom_form.length > 9) ElMessage.warning('最多添加10条');
  else formValidate.value.custom_form.push({ title: '', label: 'text', value: '', status: false });
}
function delcustom(index) {
  formValidate.value.custom_form.splice(index, 1);
}
function changeParamsType(e) {
  e ? getParams(e) : (formValidate.value.params_list = []);
}
function getParams(id) {
  paramInfoApi(id).then((res) => {
    formValidate.value.params_list = res.data.value;
  });
}
function deleteRow(index) {
  formValidate.value.params_list.splice(index, 1);
}
function handleAddParams() {
  formValidate.value.params_list.push({ name: '', value: '' });
}
function addProtection() {
  router.push({ path: routePre + '/product/protection/list' });
}
// ----- 物流设置 -----
function addTemp() {
  templates.value.isTemplate = true;
}
function changeTemplate(msg) {
  template.value = msg;
}
function logisticsBtn(e) {
  formValidate.value.logistics = e;
}
// ----- 保存 -----
function handleSubmit() {
  if (formValidate.value.spec_type === 1 && manyFormValidate.value.length < 2) {
    return ElMessage.warning('商品规格-规格数量最少1个');
  }
  if (!formValidate.value.store_name) return ElMessage.warning('商品名称不能为空');
  if (!formValidate.value.cate_id.length) return ElMessage.warning('请选择商品分类');
  if (!formValidate.value.slider_image.length) return ElMessage.warning('请上传商品图片');

  // 会员价/佣金验证
  if (formValidate.value.is_sub == 1 || formValidate.value.is_vip == 1) {
    let item;
    if (formValidate.value.spec_type === 0) {
      item = JSON.parse(JSON.stringify(oneFormValidate.value));
    } else {
      item = JSON.parse(JSON.stringify(manyFormValidate.value));
      item.shift();
    }
    if (formValidate.value.is_vip == 1) {
      for (let i = 0; i < item.length; i++) {
        if (item[i].vip_price === null || item[i].vip_price <= 0) {
          return ElMessage.error('营销设置-会员价不能为空或0');
        }
      }
    }
  }

  saving.value = true;

  let item;
  if (formValidate.value.spec_type === 0) {
    item = JSON.parse(JSON.stringify(oneFormValidate.value));
    formValidate.value.attrs = item;
    formValidate.value.items = [];
  } else {
    item = JSON.parse(JSON.stringify(manyFormValidate.value));
    item.shift();
    formValidate.value.items = attrs.value;
    formValidate.value.attrs = item;
    formValidate.value.is_copy = 1;
  }
  if (formValidate.value.spec_type === 0 && oneFormValidate.value.length) {
    formValidate.value.price = oneFormValidate.value[0].price || 0;
    formValidate.value.ot_price = oneFormValidate.value[0].ot_price || 0;
    formValidate.value.cost = oneFormValidate.value[0].cost || 0;
    formValidate.value.stock = oneFormValidate.value[0].stock || 0;
  }
  formValidate.value.description = formatRichText(content.value);

  const submitData = {
    store_name: formValidate.value.store_name,
    store_info: formValidate.value.store_info || '',
    cate_id: formValidate.value.cate_id,
    label_id: formValidate.value.label_id || [],
    slider_image: formValidate.value.slider_image,
    image: formValidate.value.slider_image[0] || '',
    price: Number(formValidate.value.price) || 0,
    ot_price: Number(formValidate.value.ot_price) || 0,
    cost: Number(formValidate.value.cost) || 0,
    stock: Number(formValidate.value.stock) || 0,
    unit_name: formValidate.value.unit_name || '件',
    ficti: Number(formValidate.value.ficti) || 0,
    description: formValidate.value.description || '',
    keyword: formValidate.value.keyword || '',
    sort: Number(formValidate.value.sort) || 0,
    give_integral: Number(formValidate.value.give_integral) || 0,
    is_vip: formValidate.value.is_vip,
    is_show: formValidate.value.is_show,
    vip_product: Number(formValidate.value.vip_product) || 0,
    // 网盘信息已下移到 SKU 级（随 attrs 逐条提交），商品级仅保留全局"自动发货"开关
    is_auto_send: formValidate.value.is_auto_send ?? 1,
    product_type: 'virtual',
    // 规格数据
    spec_type: formValidate.value.spec_type,
    items: formValidate.value.items || [],
    attrs: formValidate.value.attrs || [],
    // 扩展数据
    params_list: formValidate.value.params_list || [],
    protection_list: formValidate.value.protection_list || [],
    custom_form: formValidate.value.custom_form || [],
    coupon_ids: formValidate.value.coupon_ids || [],
    label_list: formValidate.value.label_list || [],
    is_sub: formValidate.value.is_sub ?? 0,
    presale_time: formValidate.value.presale_time || [],
    // VIP/佣金
    oneFormValidate: oneFormValidate.value || [],
    manyFormValidate: manyFormValidate.value || [],
    min_qty: formValidate.value.min_qty || 1,
    is_limit: Number(formValidate.value.is_limit) || 0,
    limit_type: formValidate.value.limit_type || 0,
    limit_num: formValidate.value.limit_num || 0,
    freight: formValidate.value.freight || 2,
    postage: formValidate.value.postage || 0,
    temp_id: formValidate.value.temp_id || '',
    recommend_list: formValidate.value.recommend_list || [],
    // 营销设置
    recommend: formValidate.value.recommend || [],
    activity: formValidate.value.activity || [],
    // 视频
    video_link: formValidate.value.video_link || '',
    video_open: formValidate.value.video_open || 0,
    seletVideo: formValidate.value.seletVideo || 0,
    // 物流
    logistics: formValidate.value.logistics || [],
    // 网盘类型
    virtual_type: 3,
    is_virtual: 0,
    // 类型标识
    type: type.value || 0,
  };

  const request = props.editId
    ? virtualApi('/product/' + props.editId, { method: 'PUT', body: JSON.stringify(submitData) })
    : virtualApi('/product', { method: 'POST', body: JSON.stringify(submitData) });

  request
    .then(() => {
      ElMessage.success(props.editId ? '更新成功' : '添加成功');
      goBack();
    })
    .catch((e) => {
      ElMessage.error(e.message);
    })
    .finally(() => {
      saving.value = false;
    });
}
function goBack() {
  const routePath = routePre + '/product/virtual/list';
  router.push({ path: routePath });
}

onMounted(() => {
  // created：初始化数据加载
  formValidate.value['virtual_type'] = 3;
  formValidate.value.virtual_type = 3;
  loadCategory();
  productGetRule();
  productGetTemplate();
  paramsGetTemplate();
  getProtectionList();
  getproductLabelUseListApi();
  if (props.editId) loadProduct();
});

// add/:id? 是同一路由记录，「新增」与「编辑」共用组件实例，路由参数变化不会重新走 onMounted；
// 不监听 editId 的话，长会话里编辑页会显示上一个页面（新增表单/另一单）残留的规格与表格数据
watch(
  () => props.editId,
  (val) => {
    if (val) {
      loadProduct();
    } else {
      resetAddForm();
    }
  },
);

// 恢复新增态：清掉编辑残留的商品与规格数据
function resetAddForm() {
  Object.assign(formValidate.value, {
    store_name: '',
    store_info: '',
    price: 0,
    ot_price: 0,
    cost: 0,
    stock: 0,
    ficti: 0,
    unit_name: '件',
    sort: 0,
    is_show: 1,
    keyword: '',
    give_integral: 0,
    is_sub: 0,
    spec_type: 0,
    cate_id: [],
    label_list: [],
    recommend_list: [],
    slider_image: [],
    image: '',
    custom_form: [],
    params_list: [],
    protection_list: [],
    attrs: [],
    items: [],
    header: [],
  });
  attrs.value = [];
  manyFormValidate.value = [];
  oneFormValidate.value = [
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
      download_url: '',
      download_password: '',
    },
  ];
  contents.value = '';
}
</script>

<style scoped></style>
