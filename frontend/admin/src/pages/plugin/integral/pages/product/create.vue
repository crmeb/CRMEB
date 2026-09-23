<template>
  <div>
    <pages-header
      :title="$route.params.id ? '编辑积分商品' : '添加积分商品'"
      :backUrl="$routeProStr + '/marketing/integral/product'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-row class="mt30 acea-row row-middle row-center" v-loading="spinShow">
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
            <!-- 第一步：选择商品 -->
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
            <!-- 第二步：基础信息 -->
            <el-col v-show="current === 1">
              <el-col :span="24">
                <el-form-item prop="image">
                  <template #label
                    ><div class="custom-label">
                      <div>
                        <div>商品主图</div>
                        <div>(750*750)</div>
                      </div>
                      <div>：</div>
                    </div></template
                  >
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
                <el-form-item prop="images">
                  <template #label
                    ><div class="custom-label">
                      <div>
                        <div>商品轮播图</div>
                        <div>(750*750)</div>
                      </div>
                      <div>：</div>
                    </div></template
                  >
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
                <el-form-item label="用户兑换数量限制：" prop="num">
                  <div class="acea-row row-middle">
                    <el-input-number
                      :controls="false"
                      :min="1"
                      :max="9999999999"
                      placeholder="请输入数量限制"
                      element-id="num"
                      :precision="0"
                      v-model="formValidate.num"
                      class="content_width"
                    />
                    <div class="ml10 grey">每个用户可购买该商品总数限制</div>
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
                    v-model="formValidate.sort"
                    class="content_width"
                  />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="上架状态：" props="is_show" label-for="status">
                  <el-radio-group element-id="is_show" v-model="formValidate.is_show">
                    <el-radio :label="1" :value="1" class="radio">开启</el-radio>
                    <el-radio :label="0" :value="0">关闭</el-radio>
                  </el-radio-group>
                </el-form-item>
              </el-col>
              <el-col v-bind="grid2">
                <el-form-item label="热门推荐：" props="is_host" label-for="is_host">
                  <el-radio-group element-id="is_host" v-model="formValidate.is_host">
                    <el-radio :label="1" :value="1" class="radio">开启</el-radio>
                    <el-radio :label="0" :value="0">关闭</el-radio>
                  </el-radio-group>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="规格选择：">
                  <el-table
                    ref="multipleTable"
                    :data="specsData"
                    border
                    :row-key="getRowKeys"
                    class="mt14"
                    highlight-current-row
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
                            :precision="0"
                            class="priceBox"
                            :active-change="false"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'quota'">
                          <el-input-number
                            :controls="false"
                            v-model="scope.row.quota"
                            :min="1"
                            class="priceBox"
                          ></el-input-number>
                        </template>
                      </template>
                    </el-table-column>
                  </el-table>
                </el-form-item>
              </el-col>
            </el-col>
            <!-- 第三步：商品详情 -->
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
                  >{{ current === 2 ? '提交' : '下一步' }}</el-button
                >
              </el-form-item>
            </el-col>
          </el-form>
        </el-col>
      </el-row>
    </el-card>
    <!-- 选择商品 -->
    <el-dialog v-model="modals" title="商品列表" class="paymentFooter" width="1000px">
      <goods-list ref="goodslist" @getProductId="getProductId"></goods-list>
    </el-dialog>
    <!-- 上传图片 -->
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
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import goodsList from '@/components/goodsList/index';
import WangEditor from '@/components/wangEditor/index.vue';
import uploadPictures from '@/components/uploadPictures';
import steps from '@/components/steps/index';
import { integralAddApi, productAttrsApi, integralInfoApi } from '../../api';
import setting from '@/setting';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'integralProductCreate' });

const route = useRoute();
const router = useRouter();
const routePre = setting.routePre;
const mediaStore = useMediaStore();

const formValidateRef = ref(null);
const multipleTable = ref(null);
const goodslist = ref(null);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '135px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const submitOpen = ref(false);
const spinShow = ref(false);
const isChoice = ref('');
const current = ref(0);
const modalPic = ref(false);
const grid = { xl: 12, lg: 20, md: 24, sm: 24, xs: 24 };
const grid2 = { xl: 8, lg: 12, md: 12, sm: 24, xs: 24 };
const gridPic = { xl: 6, lg: 8, md: 12, sm: 12, xs: 12 };
const gridBtn = { xl: 4, lg: 8, md: 8, sm: 8, xs: 8 };
const stepList = ref(['选择积分商品', '填写基础信息', '修改商品详情']);
const modals = ref(false);
const modal_loading = ref(false);
const formValidate = ref({
  images: [],
  title: '',
  image: '',
  unit_name: '',
  price: 0,
  sales: 0,
  stock: 0,
  sort: 0,
  num: 1,
  once_num: 1,
  is_host: 0,
  is_show: 1,
  description: '',
  id: 0,
  product_id: 0,
  attrs: [],
  items: [],
});
// AI 写作默认提示词：优先带入当前商品标题（弹窗内未被用户修改过的提示词会跟随刷新）
const aiDefaultPrompt = computed(() => {
  const title = (formValidate.value.title || '').trim();
  return title
    ? `请围绕商品《${title}》撰写一篇产品推广文章，介绍产品亮点、使用场景与适用人群，结构清晰，800字左右。`
    : '写一篇产品推广文章，介绍产品亮点、使用场景与适用人群，结构清晰，800字左右。';
});
const description = ref('');
const columns = ref([]);
const specsData = ref([]);
const picTit = ref('');
const tableIndex = ref(0);
const copy = ref(0);
const ruleValidate = ref({
  image: [{ required: true, message: '请选择主图', trigger: 'change' }],
  images: [
    { required: true, type: 'array', message: '请选择轮播图', trigger: 'change' },
    { type: 'array', min: 1, message: '至少选择一张轮播图', trigger: 'change' },
  ],
  title: [{ required: true, message: '请输入商品标题', trigger: 'blur' }],
  unit_name: [{ required: true, message: '请输入单位', trigger: 'blur' }],
  num: [{ required: true, type: 'number', message: '请输入兑换数量限制', trigger: 'blur' }],
});
// 非响应式内部状态（原代码未在 data 中声明）
let dragging = null;

onMounted(() => {
  if (route.params.id) {
    copy.value = route.params.copy || 0;
    current.value = 1;
    getInfo();
  }
});

function getEditorContent(data) {
  description.value = data;
}
function productAttrs(rows) {
  productAttrsApi(rows.id, 4)
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
function getProductId(row) {
  modal_loading.value = false;
  modals.value = false;
  setTimeout(() => {
    formValidate.value = {
      images: row.slider_image || [],
      title: row.store_name,
      image: row.image,
      unit_name: row.unit_name,
      price: 0,
      sales: row.sales,
      stock: row.stock,
      sort: row.sort,
      num: 1,
      once_num: 1,
      is_host: 0,
      is_show: 1,
      description: row.description || '',
      id: 0,
      product_id: row.id,
      attrs: [],
      items: [],
    };
    productAttrs(row);
  }, 500);
}
function getInfo() {
  spinShow.value = true;
  integralInfoApi(route.params.id)
    .then(async (res) => {
      let info = res.data.info;
      formValidate.value = info;
      formValidate.value['items'] = info.attrs.items;
      columns.value = info.attrs.header;
      specsData.value = info.attrs.value;
      specsData.value.forEach(function (item, index) {
        specsData.value[index]['id'] = index;
      });
      let attr = [];
      for (let index in info.attrs.value) {
        if (info.attrs.value[index]._checked) {
          attr.push(info.attrs.value[index]);
        }
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
        integralAddApi(formValidate.value)
          .then(async (res) => {
            submitOpen.value = false;
            ElMessage.success(res.msg);
            setTimeout(() => {
              router.push({ path: routePre + '/marketing/integral/product' });
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
        if (!formValidate.value.attrs || !formValidate.value.attrs.length) {
          return ElMessage.error('请选择属性规格');
        }
        for (let index in formValidate.value.attrs) {
          if (formValidate.value.attrs[index].quota <= 0) {
            return ElMessage.error('兑换次数必须大于0');
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
    } else {
      ElMessage.warning('请选择商品');
    }
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
      if (formValidate.value.attrs && formValidate.value.attrs.length) {
        specsData.value[tableIndex.value]['_checked'] = true;
      }
      specsData.value[tableIndex.value].pic = pc.att_dir;
  }
  modalPic.value = false;
}
function getPicD(pc) {
  pc.map((item) => {
    formValidate.value.images.push(item.att_dir);
    formValidate.value.images = formValidate.value.images.splice(0, 10);
  });
  modalPic.value = false;
}
function handleRemove(i) {
  formValidate.value.images.splice(i, 1);
}
function changeGoods() {
  modals.value = true;
  nextTick(() => {
    goodslist.value.formValidate.is_show = -1;
    goodslist.value.formValidate.type = 7;
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
.custom-label {
  display: inline-flex;
  line-height: 1.5;
}
.grey {
  color: #999;
}
.tabBox_img {
  width: 50px;
  height: 50px;
  margin: 0 auto;
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
}
</style>
