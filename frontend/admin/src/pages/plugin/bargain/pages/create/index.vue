<template>
  <div>
    <pages-header
      :title="$route.params.id ? '编辑砍价商品' : '添加砍价商品'"
      :backUrl="routePre + '/marketing/bargain/product'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-row class="mt30 acea-row row-middle row-center">
        <el-col :span="20">
          <steps :stepList="stepList" :isActive="current"></steps>
        </el-col>
        <el-col :span="23" v-loading="spinShow">
          <el-form
            class="form mt30"
            ref="formValidateRef"
            :rules="ruleValidate"
            :model="formValidate"
            @on-validate="validate"
            :label-width="labelWidth"
            :label-position="labelPosition"
            @submit.prevent
          >
            <el-form-item label="选择商品：" prop="image_input" v-show="current === 0">
              <div class="picBox" v-db-click @click="changeGoods">
                <div class="pictrue" v-if="formValidate.image">
                  <img v-lazy="formValidate.image" />
                </div>
                <div class="upLoad acea-row row-center-wrapper" v-else>
                  <i class="el-icon-goods" style="font-size: 24px"></i>
                </div>
              </div>
            </el-form-item>
            <el-row v-show="current === 1">
              <el-col :span="24">
                <el-form-item label="商品主图：" prop="image">
                  <div class="picBox" v-db-click @click="modalPicTap('dan', 'danFrom')">
                    <div class="pictrue" v-if="formValidate.image">
                      <img v-lazy="formValidate.image" />
                    </div>
                    <div class="upLoad acea-row row-center-wrapper" v-else>
                      <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                    </div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="商品轮播图：" prop="images">
                  <div class="acea-row">
                    <div
                      class="pictrue"
                      v-for="(item, index) in formValidate.images"
                      :key="index"
                      draggable="true"
                      @dragstart="handleDragStart($event, item)"
                      @dragover.prevent="handleDragOver($event, item)"
                      @dragenter="handleDragEnter($event, item)"
                      @dragend="handleDragEnd($event, item)"
                    >
                      <img v-lazy="item" />
                      <i class="el-icon-circle-close btndel" v-db-click @click="handleRemove(index)"></i>
                    </div>
                    <div
                      v-if="formValidate.images.length < 10"
                      class="upLoad acea-row row-center-wrapper"
                      v-db-click
                      @click="modalPicTap('duo')"
                    >
                      <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                    </div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-col v-bind="grid">
                  <el-form-item label="砍价活动名称：" prop="title" label-for="title">
                    <el-input
                      placeholder="请输入砍价活动名称"
                      v-model="formValidate.title"
                      class="content_width"
                      maxlength="30"
                      show-word-limit
                    />
                  </el-form-item>
                </el-col>
              </el-col>
              <el-col :span="24">
                <el-col v-bind="grid">
                  <el-form-item label="砍价活动简介：" prop="info" label-for="info">
                    <el-input
                      placeholder="请输入砍价活动简介"
                      type="textarea"
                      :rows="4"
                      v-model="formValidate.info"
                      class="content_width"
                      maxlength="100"
                      show-word-limit
                    />
                  </el-form-item>
                </el-col>
              </el-col>
              <el-col :span="24">
                <el-form-item label="活动时间：" prop="section_time">
                  <div>
                    <el-date-picker
                      clearable
                      :editable="false"
                      type="datetimerange"
                      format="YYYY-MM-DD HH:mm"
                      value-format="YYYY-MM-DD HH:mm"
                      range-separator="-"
                      start-placeholder="开始日期"
                      end-placeholder="结束日期"
                      @change="onchangeTime"
                      style="width: 460px"
                      v-model="formValidate.section_time"
                    ></el-date-picker>
                    <div class="grey">设置活动开启结束时间，用户可以在设置时间内发起参与砍价</div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24" v-if="formValidate.virtual_type == 0">
                <el-form-item label="物流方式：" prop="logistics">
                  <el-checkbox-group v-model="formValidate.logistics">
                    <el-checkbox label="1">快递</el-checkbox>
                    <el-checkbox label="2">到店</el-checkbox>
                  </el-checkbox-group>
                </el-form-item>
              </el-col>
              <el-col :span="24" v-if="formValidate.virtual_type == 0 && formValidate.logistics.includes('1')">
                <el-form-item label="运费设置：" :prop="formValidate.freight != 1 ? 'freight' : ''">
                  <el-radio-group v-model="formValidate.freight">
                    <el-radio :label="2" :value="2">固定邮费</el-radio>
                    <el-radio :label="3" :value="3">运费模板</el-radio>
                  </el-radio-group>
                </el-form-item>
              </el-col>
              <el-col
                :span="24"
                v-if="
                  formValidate.freight != 3 &&
                  formValidate.freight != 1 &&
                  formValidate.virtual_type == 0 &&
                  formValidate.logistics.includes('1')
                "
              >
                <el-form-item label="">
                  <div class="acea-row">
                    <el-input-number
                      :controls="false"
                      :min="0"
                      :max="9999999999"
                      v-model="formValidate.postage"
                      placeholder="请输入金额"
                      class="content_width"
                    />
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24" v-if="formValidate.freight == 3 && formValidate.virtual_type == 0">
                <el-form-item label="" prop="temp_id">
                  <div class="acea-row">
                    <el-select
                      v-model="formValidate.temp_id"
                      clearable
                      placeholder="请选择运费模板"
                      class="content_width"
                    >
                      <el-option
                        v-for="(item, index) in templateList"
                        :value="item.id"
                        :key="index"
                        :label="item.name"
                      ></el-option>
                    </el-select>
                    <span class="addfont" v-db-click @click="freight">新增运费模板</span>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="砍价人数：" prop="people_num" label-for="people_num">
                  <div>
                    <el-input-number
                      :controls="false"
                      placeholder="请输入砍价人数"
                      element-id="people_num"
                      :min="2"
                      :max="10000"
                      :precision="0"
                      v-model="formValidate.people_num"
                      class="input-number-unit-class"
                      class-unit="人"
                    />
                    <div class="grey">需要多少人砍价成功</div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="帮砍次数：" prop="bargain_num" label-for="bargain_num">
                  <div>
                    <el-input-number
                      :controls="false"
                      placeholder="请输入帮砍次数"
                      :min="1"
                      :max="10000"
                      :precision="0"
                      v-model="formValidate.bargain_num"
                      class="input-number-unit-class"
                      class-unit="次"
                    />
                    <div class="grey">
                      单个商品用户可以帮砍的次数，例：次数设置为1，甲和乙同时将商品A的砍价链接发给丙，丙只能帮甲或乙其中一个人砍价
                    </div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="单位：" prop="unit_name" label-for="unit_name">
                  <el-input
                    placeholder="请输入单位"
                    element-id="unit_name"
                    v-model="formValidate.unit_name"
                    class="content_width"
                  />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="购买数量限制：" prop="num">
                  <div>
                    <el-input-number
                      :controls="false"
                      placeholder="购买数量限制"
                      :min="1"
                      :max="10000"
                      :precision="0"
                      v-model="formValidate.num"
                      class="input-number-unit-class"
                      :class-unit="formValidate.unit_name || '件'"
                    />
                    <div class="grey">单个活动每个用户发起砍价次数限制</div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="排序：">
                  <el-input-number
                    :controls="false"
                    placeholder="请输入排序"
                    element-id="sort"
                    :min="0"
                    :max="10000"
                    :precision="0"
                    v-model="formValidate.sort"
                    class="content_width"
                  />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="砍价是否参与分销：" props="is_commission" label-for="is_commission">
                  <div>
                    <el-switch
                      class="defineSwitch"
                      :active-value="1"
                      :inactive-value="0"
                      v-model="formValidate.is_commission"
                      size="large"
                      active-text="开启"
                      inactive-text="关闭"
                    >
                    </el-switch>
                    <div class="grey">商品是否参与商城分销返佣</div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="活动状态：" props="status" label-for="status">
                  <el-switch
                    class="defineSwitch"
                    :active-value="1"
                    :inactive-value="0"
                    v-model="formValidate.status"
                    size="large"
                    active-text="开启"
                    inactive-text="关闭"
                  >
                  </el-switch>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="规格选择：">
                  <el-table :data="specsData" border>
                    <el-table-column width="50">
                      <template #default="scope">
                        <el-radio
                          type="index"
                          v-model="templateRadio"
                          :label="scope.$index"
                          :value="scope.$index"
                          @change="getTemplateRow"
                          >&nbsp;</el-radio
                        >
                      </template>
                    </el-table-column>
                    <el-table-column
                      :label="item.title"
                      :min-width="item.minWidth"
                      v-for="(item, index) in columns"
                      :key="index"
                    >
                      <template #default="scope">
                        <template v-if="item.key">
                          <div>
                            <span>{{ scope.row[item.key] }}</span>
                          </div>
                        </template>
                        <template v-else-if="item.slot === 'pic'">
                          <div
                            class="acea-row row-middle row-center-wrapper"
                            v-db-click
                            @click="modalPicTap('dan', 'danTable', scope.$index)"
                          >
                            <div class="pictrue pictrueTab" v-if="scope.row.pic">
                              <img v-lazy="scope.row.pic" />
                            </div>
                            <div class="upLoad pictrueTab acea-row row-center-wrapper" v-else>
                              <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                            </div>
                          </div>
                        </template>
                        <template v-else-if="item.slot === 'price'">
                          <el-input-number
                            :controls="false"
                            v-model="scope.row.price"
                            :min="0"
                            :precision="2"
                            class="priceBox"
                            :active-change="false"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'min_price'">
                          <el-input-number
                            :controls="false"
                            v-model="scope.row.min_price"
                            :min="0"
                            :precision="2"
                            class="priceBox"
                            :active-change="false"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'quota'">
                          <el-input-number
                            :controls="false"
                            v-model="scope.row.quota"
                            :min="1"
                            active-change
                            class="priceBox"
                          ></el-input-number>
                        </template>
                      </template>
                    </el-table-column>
                  </el-table>
                </el-form-item>
              </el-col>
            </el-row>
            <div v-if="current === 2">
              <el-form-item label="内容：">
                <WangEditor
                  style="width: 90%"
                  :content="formValidate.description"
                  :ai-default-prompt="aiDefaultPrompt"
                  @editorContent="getEditorContent"
                ></WangEditor>
              </el-form-item>
            </div>
            <div v-if="current === 3">
              <el-form-item label="规则：">
                <WangEditor
                  style="width: 90%"
                  :content="formValidate.rule"
                  @editorContent="getEditorContent2"
                ></WangEditor>
              </el-form-item>
            </div>
            <el-form-item>
              <el-button
                v-if="current !== 0"
                class="submission"
                v-db-click
                @click="step"
                :disabled="($route.params.id && $route.params.id !== '0' && current === 1) || current === 0"
                >上一步</el-button
              >
              <el-button
                type="primary"
                :disabled="submitOpen && current === 3"
                class="submission"
                v-db-click
                @click="next"
                >{{ current === 3 ? '提交' : '下一步' }}</el-button
              >
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
    </el-card>
    <!-- 选择商品-->
    <el-dialog v-model="modals" title="商品列表" class="paymentFooter" width="1000px">
      <goods-list ref="goodslist" @getProductId="getProductId"></goods-list>
    </el-dialog>

    <!-- 上传图片-->
    <el-dialog v-model="modalPic" width="950px" title="上传商品图" :close-on-click-modal="false">
      <uploadPictures
        :isChoice="isChoice"
        @getPic="getPic"
        @getPicD="getPicD"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="modalPic"
      ></uploadPictures>
    </el-dialog>
    <!-- 运费模板-->
    <freight-template ref="template" @addSuccess="productGetTemplate"></freight-template>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import goodsList from '@/components/goodsList/index';
import uploadPictures from '@/components/uploadPictures';
import { bargainInfoApi, bargainCreatApi, productAttrsApi } from '@/api/marketing';
import { productGetTemplateApi } from '@/api/product';
import freightTemplate from '@/components/freightTemplate/index';
import WangEditor from '@/components/wangEditor/index.vue';
import steps from '@/components/steps/index';
import setting from '@/setting';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'bargainCreate' });

const route = useRoute();
const router = useRouter();
const routePre = setting.routePre;
const { isMobile } = storeToRefs(useMediaStore());

const labelWidth = computed(() => (isMobile.value ? undefined : '140px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const formValidateRef = ref(null);
const goodslist = ref(null);
const template = ref(null);

const templateRadio = ref(0);
const submitOpen = ref(false);
const spinShow = ref(false);
const myConfig = {
  autoHeightEnabled: false,
  initialFrameHeight: 500,
  initialFrameWidth: '100%',
  UEDITOR_HOME_URL: '/UEditor/',
  serverUrl: '',
};
const stepList = ['选择砍价商品', '填写基础信息', '修改商品详情', '修改商品规则'];
const isChoice = ref('');
const current = ref(0);
const modalPic = ref(false);
const grid = {
  xl: 12,
  lg: 20,
  md: 24,
  sm: 24,
  xs: 24,
};
const grid2 = {
  xl: 8,
  lg: 8,
  md: 12,
  sm: 24,
  xs: 24,
};
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
const modals = ref(false);
const modal_loading = ref(false);
const images = ref([]);
const templateList = ref([]);
const columns = ref([]);
const specsData = ref([]);
const formValidate = ref({
  images: [],
  info: '',
  title: '',
  store_name: '',
  image: '',
  unit_name: '',
  price: 0,
  min_price: 0,
  bargain_max_price: 10,
  bargain_min_price: 0.01,
  cost: 0,
  bargain_num: 1,
  people_num: 2,
  stock: 1,
  sales: 0,
  sort: 0,
  num: 1,
  give_integral: 0,
  is_postage: 0,
  is_hot: 0,
  status: 0,
  section_time: [],
  description: '',
  rule: '',
  id: 0,
  product_id: 0,
  temp_id: '',
  attrs: [],
  items: [],
  logistics: [],
  freight: 2,
  postage: 1,
  is_commission: 0,
});
// AI 写作默认提示词：优先带入当前商品名称（弹窗内未被用户修改过的提示词会跟随刷新）
const aiDefaultPrompt = computed(() => {
  const title = (formValidate.value.store_name || formValidate.value.title || '').trim();
  return title
    ? `请围绕商品《${title}》撰写一篇产品推广文章，介绍产品亮点、使用场景与适用人群，结构清晰，800字左右。`
    : '写一篇产品推广文章，介绍产品亮点、使用场景与适用人群，结构清晰，800字左右。';
});
const description = ref('');
const rule = ref('');
const ruleValidate = {
  image: [{ required: true, message: '请选择主图', trigger: 'change' }],
  images: [
    {
      required: true,
      type: 'array',
      message: '请选择主图',
      trigger: 'change',
    },
    {
      type: 'array',
      min: 1,
      message: 'Choose two hobbies at best',
      trigger: 'change',
    },
  ],
  title: [{ required: true, message: '请输入砍价活动名称', trigger: 'blur' }],
  info: [{ required: true, message: '请输入砍价活动简介', trigger: 'blur' }],
  store_name: [{ required: true, message: '请输入砍价商品名称', trigger: 'blur' }],
  section_time: [
    {
      required: true,
      type: 'array',
      message: '请选择活动时间',
      trigger: 'change',
    },
  ],
  unit_name: [{ required: true, message: '请输入单位', trigger: 'blur' }],
  price: [
    {
      required: true,
      type: 'number',
      message: '请输入原价',
      trigger: 'blur',
    },
  ],
  min_price: [
    {
      required: true,
      type: 'number',
      message: '请输入最低购买价',
      trigger: 'blur',
    },
  ],
  cost: [
    {
      required: true,
      type: 'number',
      message: '请输入成本价',
      trigger: 'blur',
    },
  ],
  bargain_num: [
    {
      required: true,
      type: 'number',
      message: '请输入帮砍次数',
      trigger: 'blur',
    },
  ],
  people_num: [
    {
      required: true,
      type: 'number',
      message: '请输入砍价人数',
      trigger: 'blur',
    },
  ],
  stock: [
    {
      required: true,
      type: 'number',
      message: '请输入库存',
      trigger: 'blur',
    },
  ],
  num: [
    {
      required: true,
      type: 'number',
      message: '请输入单次允许购买数量',
      trigger: 'blur',
    },
  ],
  temp_id: [
    {
      required: true,
      message: '请选择运费模板',
      trigger: 'change',
      type: 'number',
    },
  ],
};
const currentid = ref(0);
const picTit = ref('');
const tableIndex = ref(0);
const copy = ref(0);
const dragging = ref(null);

onMounted(() => {
  if (route.params.id !== '0' && route.params.id) {
    copy.value = route.params.copy;
    current.value = 1;
    getInfo();
  }
  productGetTemplate();
});

function getEditorContent(data) {
  description.value = data;
}
function getEditorContent2(data) {
  rule.value = data;
}
function freight() {
  template.value.id = 0;
  template.value.isTemplate = true;
}
function productAttrs(row) {
  productAttrsApi(row.id, 2)
    .then((res) => {
      let data = res.data.info;
      columns.value = data.header;
      specsData.value = data.attrs;
      formValidate.value.items = data.items;
      formValidate.value['attrs'] = [specsData.value[0]];
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function getTemplateRow(index) {
  currentid.value = index;
  formValidate.value['attrs'] = [specsData.value[index]];
}
function productGetTemplate() {
  productGetTemplateApi().then((res) => {
    templateList.value = res.data;
  });
}
function getProductId(row) {
  modal_loading.value = false;
  modals.value = false;
  setTimeout(() => {
    formValidate.value = {
      images: row.slider_image,
      info: row.store_info,
      title: row.store_name,
      store_name: row.store_name,
      image: row.image,
      unit_name: row.unit_name,
      price: 0,
      min_price: 0,
      bargain_max_price: 10,
      bargain_min_price: 0.01,
      cost: row.cost,
      bargain_num: 1,
      people_num: 2,
      stock: row.stock,
      sales: row.sales,
      sort: row.sort,
      num: 1,
      give_integral: row.give_integral,
      is_postage: row.is_postage,
      is_hot: row.is_hot,
      status: 0,
      section_time: [],
      description: '',
      rule: '',
      id: 0,
      product_id: row.id,
      temp_id: row.temp_id,
      logistics: row.temp_id ? ['2'] : ['1'],
      freight: row.freight,
      postage: row.postage,
      custom_form: row.custom_form,
      virtual_type: row.virtual_type,
      is_commission: row.is_commission,
      description: row.description,
    };
    productAttrs(row);
  }, 500);
}
function cancel() {
  modals.value = false;
}
function handleDragStart(e, item) {
  dragging.value = item;
}
function handleDragEnd(e, item) {
  dragging.value = null;
}
function handleDragOver(e) {
  e.dataTransfer.dropEffect = 'move';
}
function handleDragEnter(e, item) {
  e.dataTransfer.effectAllowed = 'move';
  if (item === dragging.value) {
    return;
  }
  const newItems = [...formValidate.value.images];
  const src = newItems.indexOf(dragging.value);
  const dst = newItems.indexOf(item);
  newItems.splice(dst, 0, ...newItems.splice(src, 1));
  formValidate.value.images = newItems;
}
function onchangeTime(e) {
  formValidate.value.section_time = e;
}
function getInfo() {
  spinShow.value = true;
  bargainInfoApi(route.params.id)
    .then(async (res) => {
      let info = res.data.info;
      formValidate.value = info;
      formValidate.value.rule = info.rule === null ? '' : info.rule;
      formValidate.value['items'] = info.attrs.items;
      description.value = formValidate.value.description;
      columns.value = info.attrs.header;
      specsData.value = info.attrs.value;
      let defaultAttrs = [];
      info.attrs.value.forEach(function (item, index) {
        if (item.opt) {
          defaultAttrs.push(item);
          currentid.value = index;
          templateRadio.value = index;
          formValidate.value['attrs'] = defaultAttrs;
        }
      });
      spinShow.value = false;
    })
    .catch((res) => {
      spinShow.value = false;
      ElMessage.error(res.msg);
    });
}
function next() {
  if (current.value === 3) {
    formValidate.value.description = description.value;
    formValidate.value.rule = rule.value;
    formValidateRef.value.validate((valid) => {
      if (valid) {
        if (copy.value == 1) formValidate.value.copy = 1;
        formValidate.value.id = route.params.id || 0;
        submitOpen.value = true;
        bargainCreatApi(formValidate.value)
          .then(async (res) => {
            submitOpen.value = false;
            ElMessage.success(res.msg);
            setTimeout(() => {
              router.push({
                path: routePre + '/marketing/bargain/product',
              });
            }, 500);
          })
          .catch((res) => {
            submitOpen.value = false;
            ElMessage.error(res.msg);
          });
      } else {
        return false;
      }
    });
  } else if (current.value === 1) {
    formValidateRef.value.validate((valid) => {
      if (valid) {
        if (currentid.value === '') {
          return ElMessage.error('请选择属性规格');
        } else {
          if (formValidate.value.attrs[0].quota <= 0) {
            return ElMessage.error('砍价限量必须大于0');
          }
          if (formValidate.value.attrs[0].quota > formValidate.value.attrs[0]['stock']) {
            return ElMessage.error('砍价限量不能超过规格库存');
          }
        }
        current.value += 1;
      } else {
        return ElMessage.warning('请完善您的信息');
      }
    });
  } else {
    if (formValidate.value.image) {
      current.value += 1;
      if (current.value == 3) {
        setTimeout((e) => {
          formValidate.value.rule += ' ';
        }, 0);
      }
    } else {
      ElMessage.warning('请选择商品');
    }
  }
}
function step() {
  current.value--;
}
function getContent(val) {
  formValidate.value.description = val;
}
function getRole(val) {
  formValidate.value.rule = val;
}
function modalPicTap(tit, picTitVal, index) {
  modalPic.value = true;
  isChoice.value = tit === 'dan' ? '单选' : '多选';
  picTit.value = picTitVal;
  tableIndex.value = index;
}
function getPic(pc) {
  switch (picTit.value) {
    case 'danFrom':
      formValidate.value.image = pc.att_dir;
      break;
    default:
      specsData.value[tableIndex.value].pic = pc.att_dir;
      formValidate.value.attrs[0].pic = pc.att_dir;
  }
  modalPic.value = false;
}
function getPicD(pc) {
  images.value = pc;
  images.value.map((item) => {
    formValidate.value.images.push(item.att_dir);
    formValidate.value.images = formValidate.value.images.splice(0, 10);
  });
  modalPic.value = false;
}
function handleRemove(i) {
  images.value.splice(i, 1);
  formValidate.value.images.splice(i, 1);
}
function changeGoods() {
  modals.value = true;
  nextTick((e) => {
    goodslist.value.formValidate.is_show = -1;
    goodslist.value.formValidate.type = 3;
    goodslist.value.getList();
    goodslist.value.goodsCategory();
  });
}
function validate(prop, status, error) {
  if (status === false) {
    ElMessage.error(error);
  }
}
</script>

<style lang="scss" scoped>
.content_width {
  width: 460px;
}
.grey {
  color: #999;
  font-size: 12px;
}
.maxW :deep(.ivu-select-dropdown) {
  max-width: 600px;
}
.ivu-table-wrapper {
  border-left: 1px solid #dcdee2;
  border-top: 1px solid #dcdee2;
}
.tabBox_img {
  width: 50px;
  height: 50px;
}
.tabBox_img img {
  width: 100%;
  height: 100%;
}
.priceBox {
  width: 100%;
}
.form {
  .picBox {
    display: inline-block;
    cursor: pointer;
  }
  .pictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 15px;
    display: inline-block;
    position: relative;
    cursor: pointer;

    img {
      width: 100%;
      height: 100%;
    }
    .btndel {
      position: absolute;
      z-index: 9;
      width: 20px !important;
      height: 20px !important;
      left: 46px;
      top: -4px;
    }
  }
  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
    cursor: pointer;
  }
  .col {
    color: #2d8cf0;
    cursor: pointer;
  }
}
.addfont {
  font-size: 12px;
  color: var(--prev-color-primary);
  margin-left: 14px;
  cursor: pointer;
  margin-left: 10px;
  cursor: pointer;
}
</style>
