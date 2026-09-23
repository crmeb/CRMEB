<template>
  <div v-loading="spinShow">
    <pages-header
      ref="pageHeader"
      :title="$route.params.id ? '编辑秒杀商品' : '添加秒杀商品'"
      :backUrl="$routeProStr + '/marketing/seckill/product'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-row class="mt30 acea-row row-middle row-center">
        <el-col :span="20">
          <steps :stepList="stepList" :isActive="current"></steps>
        </el-col>
        <el-col :span="23">
          <el-form
            class="form mt30"
            ref="formValidateRef"
            :model="formValidate"
            :rules="ruleValidate"
            :label-width="labelWidth"
            :label-position="labelPosition"
            @submit.prevent
          >
            <!-- Step 1: 选择商品 -->
            <el-form-item label="选择商品：" prop="image_input" v-if="current === 0">
              <div class="picBox" v-db-click @click="changeGoods">
                <div class="pictrue" v-if="formValidate.image">
                  <img v-lazy="formValidate.image" />
                </div>
                <div class="upLoad acea-row row-center-wrapper" v-else>
                  <i class="el-icon-goods" style="font-size: 24px"></i>
                </div>
              </div>
            </el-form-item>
            <!-- Step 2: 基础信息 -->
            <el-col v-show="current === 1">
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
                  <el-form-item label="商品标题：" prop="title" label-for="title">
                    <el-input
                      clearable
                      placeholder="请输入商品标题"
                      v-model="formValidate.title"
                      class="content_width"
                      maxlength="80"
                      show-word-limit
                    />
                  </el-form-item>
                </el-col>
              </el-col>
              <el-col :span="24">
                <el-col v-bind="grid">
                  <el-form-item label="秒杀活动简介：" prop="info" label-for="info">
                    <el-input
                      placeholder="请输入秒杀活动简介"
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
                      type="daterange"
                      format="YYYY-MM-DD"
                      value-format="YYYY-MM-DD"
                      range-separator="-"
                      start-placeholder="开始日期"
                      end-placeholder="结束日期"
                      @change="onchangeTime"
                      class="content_width"
                      v-model="formValidate.section_time"
                    >
                    </el-date-picker>
                    <div class="grey">设置活动开启结束时间，用户可以在有效时间内参与秒杀</div>
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
                <el-form-item label="运费设置：">
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
                      class="input-number-unit-class"
                      class-unit="元"
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
                <el-form-item label="开始时间：" prop="time_id">
                  <div>
                    <el-select v-model="formValidate.time_id" multiple class="content_width">
                      <el-option
                        v-for="item in timeList"
                        :value="item.id"
                        :key="item.id"
                        :label="item.time_name"
                      ></el-option>
                    </el-select>
                    <div class="grey">
                      选择产品开始时间段，该时间段内用户可参与购买；其它时间段会显示活动未开始或已结束。如活动超过一天，则活动期内，每天都会定时开启
                    </div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="总购买数量限制：" prop="num">
                  <div>
                    <el-input-number
                      :controls="false"
                      :min="1"
                      placeholder="请输入数量限制"
                      element-id="num"
                      :precision="0"
                      :max="10000"
                      v-model="formValidate.num"
                      class="content_width"
                    />
                    <div class="grey">
                      活动有效期内每个用户可购买该商品总数限制。例如设置为4，表示本次活动有效期内，每个用户最多可购买总数4个
                    </div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="单次购买数量限制：" prop="once_num">
                  <div>
                    <el-input-number
                      :controls="false"
                      :min="1"
                      placeholder="请输入单次购买数量限制"
                      element-id="once_num"
                      :precision="0"
                      :max="10000"
                      v-model="formValidate.once_num"
                      class="content_width"
                    />
                    <div class="grey">
                      用户参与秒杀时，一次购买最大数量限制。例如设置为2，表示参与秒杀时，用户一次购买数量最大可选择2个
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
                <el-form-item label="排序：">
                  <el-input-number
                    :controls="false"
                    placeholder="请输入排序"
                    element-id="sort"
                    :precision="0"
                    :max="10000"
                    :min="0"
                    v-model="formValidate.sort"
                    class="content_width"
                  />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="是否参与分销：" label-for="is_commission">
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
                <el-form-item label="活动状态：" label-for="status">
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
                  <el-table
                    ref="multipleTable"
                    :row-key="getRowKeys"
                    :data="specsData"
                    border
                    @selection-change="changeCheckbox"
                  >
                    <el-table-column type="selection" :reserve-selection="true" width="55"> </el-table-column>
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
            </el-col>
            <!-- Step 3: 商品详情 -->
            <el-row v-show="current === 2">
              <el-col :span="24">
                <el-form-item label="内容：">
                  <WangEditor
                    style="width: 90%"
                    :content="formValidate.description"
                    :ai-default-prompt="aiDefaultPrompt"
                    @editorContent="getEditorContent"
                  >
                  </WangEditor>
                </el-form-item>
              </el-col>
            </el-row>
            <el-col :span="24">
              <el-form-item>
                <el-button
                  class="submission"
                  v-db-click
                  @click="step"
                  :disabled="($route.params.id && current === 1) || current === 0"
                  >上一步</el-button
                >
                <el-button
                  :disabled="submitOpen && current === 2"
                  type="primary"
                  class="submission"
                  v-db-click
                  @click="next('formValidate')"
                >
                  {{ current === 2 ? '提交' : '下一步' }}
                </el-button>
              </el-form-item>
            </el-col>
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
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import goodsList from '@/components/goodsList/index';
import WangEditor from '@/components/wangEditor/index.vue';
import uploadPictures from '@/components/uploadPictures';
import { seckillInfoApi, seckillAddApi, seckillTimeListApi, productAttrsApi } from '../../api';
import { productGetTemplateApi } from '@/api/product';
import freightTemplate from '@/components/freightTemplate/index';
import steps from '@/components/steps/index';
import setting from '@/setting';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'seckillProductCreate' });

const route = useRoute();
const router = useRouter();
const routePre = setting.routePre;
const mediaStore = useMediaStore();

const pageHeader = ref(null);
const formValidateRef = ref(null);
const multipleTable = ref(null);
const goodslist = ref(null);
const template = ref(null);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '135px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const stepList = ref(['选择秒杀商品', '填写基础信息', '修改商品详情']);
const submitOpen = ref(false);
const spinShow = ref(false);
const isChoice = ref('');
const current = ref(0);
const modalPic = ref(false);
const grid = { xl: 12, lg: 20, md: 24, sm: 24, xs: 24 };
const gridPic = { xl: 6, lg: 8, md: 12, sm: 12, xs: 12 };
const gridBtn = { xl: 4, lg: 8, md: 8, sm: 8, xs: 8 };
const modals = ref(false);
const images = ref([]);
const formValidate = ref({
  images: [],
  info: '',
  title: '',
  image: '',
  unit_name: '',
  logistics: ['1'],
  freight: 2,
  postage: 1,
  sort: 0,
  num: 1,
  once_num: 1,
  give_integral: 0,
  section_time: [],
  is_hot: 0,
  status: 0,
  description: '',
  id: 0,
  product_id: 0,
  temp_id: '',
  time_id: [],
  attrs: [],
  items: [],
  is_commission: 0,
});
// AI 写作默认提示词：优先带入当前商品标题（弹窗内未被用户修改过的提示词会跟随刷新）
const aiDefaultPrompt = computed(() => {
  const title = (formValidate.value.title || '').trim();
  return title
    ? `请围绕商品《${title}》撰写一篇产品推广文章，介绍产品亮点、使用场景与适用人群，结构清晰，800字左右。`
    : '写一篇产品推广文章，介绍产品亮点、使用场景与适用人群，结构清晰，800字左右。';
});
const description = ref('');
const templateList = ref([]);
const timeList = ref([]);
const columns = ref([]);
const specsData = ref([]);
const picTit = ref('');
const tableIndex = ref(0);
const copy = ref(0);
const ruleValidate = ref({
  images: [{ required: true, type: 'array', message: '请选择轮播图', trigger: 'change', min: 1 }],
  title: [{ required: true, message: '请输入商品标题', trigger: 'blur' }],
  info: [{ required: true, message: '请输入秒杀活动简介', trigger: 'blur' }],
  section_time: [{ required: true, type: 'array', message: '请选择活动时间', trigger: 'change' }],
  unit_name: [{ required: true, message: '请输入单位', trigger: 'blur' }],
  num: [{ required: true, type: 'number', message: '请输入购买数量限制', trigger: 'blur' }],
  once_num: [{ required: true, type: 'number', message: '请输入单次购买数量', trigger: 'blur' }],
  temp_id: [{ required: true, message: '请选择运费模板', trigger: 'change', type: 'number' }],
  time_id: [{ required: true, message: '请选择开始时间', trigger: 'change', type: 'array' }],
});
// 非响应式内部状态（原代码未在 data 中声明）
let dragging = null;
let modal_loading = false;

onMounted(() => {
  if (route.params.id) {
    copy.value = parseInt(route.params.copy) || 0;
    current.value = 1;
    getInfo();
  }
  productGetTemplate();
  seckillTimeList();
});

function getEditorContent(data) {
  description.value = data;
}
function freight() {
  template.value.id = 0;
  template.value.isTemplate = true;
}
function productAttrs(rows) {
  productAttrsApi(rows.id, 1)
    .then((res) => {
      let data = res.data.info;
      specsData.value = data.attrs;
      specsData.value.forEach(function (item, index) {
        specsData.value[index]['id'] = index;
      });
      formValidate.value.items = data.items;
      columns.value = data.header;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function changeCheckbox(selection) {
  formValidate.value.attrs = selection;
}
function seckillTimeList() {
  seckillTimeListApi()
    .then((res) => {
      timeList.value = res.data.list.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function productGetTemplate() {
  productGetTemplateApi().then((res) => {
    templateList.value = res.data;
  });
}
function getProductId(row) {
  modal_loading = false;
  modals.value = false;
  setTimeout(() => {
    formValidate.value = {
      images: row.slider_image,
      info: row.store_info,
      title: row.store_name,
      image: row.image,
      unit_name: row.unit_name,
      price: 0,
      ot_price: row.ot_price,
      cost: row.cost,
      sales: row.sales,
      stock: row.stock,
      sort: row.sort,
      num: 1,
      once_num: 1,
      give_integral: row.give_integral,
      postage: row.postage,
      section_time: [],
      is_postage: row.is_postage,
      is_hot: row.is_hot,
      status: 0,
      description: '',
      id: 0,
      product_id: row.id,
      temp_id: row.temp_id,
      logistics: row.logistics,
      freight: row.freight,
      custom_form: row.custom_form,
      virtual_type: row.virtual_type,
      is_commission: row.is_commission,
    };
    productAttrs(row);
    goodslist.value.productRow = null;
  }, 500);
}
function cancel() {
  modals.value = false;
}
function onchangeTime(e) {
  formValidate.value.section_time = e;
}
function getInfo() {
  spinShow.value = true;
  seckillInfoApi(route.params.id)
    .then(async (res) => {
      let info = res.data.info;
      formValidate.value = info;
      formValidate.value.items = info.attrs.items;
      columns.value = info.attrs.header;
      specsData.value = info.attrs.value;
      specsData.value.forEach(function (item, index) {
        specsData.value[index]['id'] = index;
      });
      let attr = [];
      for (let index in info.attrs.value) {
        if (info.attrs.value[index]._checked) attr.push(info.attrs.value[index]);
      }
      formValidate.value.attrs = attr;
      attr.forEach((row) => {
        multipleTable.value.toggleRowSelection(row, true);
      });
      spinShow.value = false;
    })
    .catch((res) => {
      spinShow.value = false;
      ElMessage.error(res.msg);
    });
}
function getRowKeys(row) {
  return row.id;
}
function next(name) {
  if (current.value === 2) {
    formValidate.value.description = description.value;
    formValidateRef.value.validate((valid) => {
      if (valid) {
        if (copy.value == 1) formValidate.value.copy = 1;
        formValidate.value.id = Number(route.params.id) || 0;
        submitOpen.value = true;
        seckillAddApi(formValidate.value)
          .then(async (res) => {
            submitOpen.value = false;
            ElMessage.success(res.msg);
            setTimeout(() => {
              router.push({ path: routePre + '/marketing/seckill/product' });
            }, 500);
          })
          .catch((res) => {
            submitOpen.value = false;
            ElMessage.error(res.msg);
          });
      }
    });
  } else if (current.value === 1) {
    formValidateRef.value.validate((valid) => {
      if (valid) {
        if (!formValidate.value.attrs) return ElMessage.error('请选择属性规格');
        for (let index in formValidate.value.attrs) {
          if (formValidate.value.attrs[index].price > formValidate.value.attrs[index]['ot_price'])
            return ElMessage.error('秒杀价格不能大于原价');
          if (formValidate.value.attrs[index].quota <= 0) return ElMessage.error('秒杀限量必须大于0');
          if (formValidate.value.attrs[index].quota > formValidate.value.attrs[index]['stock'])
            return ElMessage.error('秒杀限量不能超过规格库存');
        }
        current.value += 1;
      }
    });
  } else {
    if (formValidate.value.images) current.value += 1;
    else ElMessage.warning('请选择商品');
  }
}
function step() {
  current.value--;
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
      if (formValidate.value.attrs && formValidate.value.attrs.length)
        specsData.value[tableIndex.value]['_checked'] = true;
      specsData.value[tableIndex.value].pic = pc.att_dir;
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
  nextTick(() => {
    goodslist.value.formValidate.is_show = -1;
    goodslist.value.formValidate.type = 3;
    goodslist.value.getList();
    goodslist.value.goodsCategory();
  });
}
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
  if (item === dragging) return;
  const newItems = [...formValidate.value.images];
  const src = newItems.indexOf(dragging);
  const dst = newItems.indexOf(item);
  newItems.splice(dst, 0, ...newItems.splice(src, 1));
  formValidate.value.images = newItems;
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
.priceBox {
  width: 100%;
}
.addfont {
  font-size: 12px;
  color: var(--prev-color-primary);
  margin-left: 10px;
  cursor: pointer;
}
</style>
