<template>
  <div>
    <pages-header
      ref="pageHeader"
      :title="$route.params.id ? '编辑砍价商品' : '添加砍价商品'"
      :backUrl="$routeProStr + '/marketing/store_bargain/index'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-row class="mt30 acea-row row-middle row-center">
        <el-col :span="20">
          <steps :stepList="stepList" :isActive="current"></steps>
        </el-col>
        <el-col :span="23" v-loading="spinShow">
          <el-form
            class="form mt30"
            ref="formValidate"
            :rules="ruleValidate"
            :model="formValidate"
            @on-validate="validate"
            :label-width="labelWidth"
            :label-position="labelPosition"
            @submit.native.prevent
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
                      format="yyyy-MM-dd HH:mm"
                      value-format="yyyy-MM-dd HH:mm"
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
              <el-col :span="24" v-if="formValidate.virtual_type == 0">
                <el-form-item label="运费设置：" :prop="formValidate.freight != 1 ? 'freight' : ''">
                  <el-radio-group v-model="formValidate.freight">
                    <el-radio :label="2">固定邮费</el-radio>
                    <el-radio :label="3">运费模板</el-radio>
                  </el-radio-group>
                </el-form-item>
              </el-col>
              <el-col
                :span="24"
                v-if="formValidate.freight != 3 && formValidate.freight != 1 && formValidate.virtual_type == 0"
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
                      class="content_width input-number-unit-class"
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
                      class="content_width input-number-unit-class"
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
                      class="content_width input-number-unit-class"
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
                      <template slot-scope="scope">
                        <el-radio type="index" v-model="templateRadio" :label="scope.$index" @input="getTemplateRow"
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
                      <template slot-scope="scope">
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
                @click="next('formValidate')"
                >{{ current === 3 ? '提交' : '下一步' }}</el-button
              >
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
    </el-card>
    <!-- 选择商品-->
    <el-dialog :visible.sync="modals" title="商品列表" class="paymentFooter" width="1000px">
      <goods-list ref="goodslist" @getProductId="getProductId"></goods-list>
    </el-dialog>

    <!-- 上传图片-->
    <el-dialog :visible.sync="modalPic" width="950px" title="上传商品图" :close-on-click-modal="false">
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

<script>
import { mapState } from 'vuex';
import goodsList from '@/components/goodsList/index';
import uploadPictures from '@/components/uploadPictures';
import { bargainInfoApi, bargainCreatApi, productAttrsApi } from '@/api/marketing';
import { productGetTemplateApi } from '@/api/product';
import freightTemplate from '@/components/freightTemplate/index';
import WangEditor from '@/components/wangEditor/index.vue';
import steps from '@/components/steps/index';

export default {
  name: 'storeBargainCreate',
  components: {
    goodsList,
    uploadPictures,
    freightTemplate,
    WangEditor,
    steps,
  },
  data() {
    return {
      templateRadio: 0,
      submitOpen: false,
      spinShow: false,
      myConfig: {
        autoHeightEnabled: false, // 编辑器不自动被内容撑高
        initialFrameHeight: 500, // 初始容器高度
        initialFrameWidth: '100%', // 初始容器宽度
        UEDITOR_HOME_URL: '/UEditor/',
        serverUrl: '',
      },
      stepList: ['选择砍价商品', '填写基础信息', '修改商品详情', '修改商品规则'],
      isChoice: '',
      current: 0,
      modalPic: false,
      grid: {
        xl: 12,
        lg: 20,
        md: 24,
        sm: 24,
        xs: 24,
      },
      grid2: {
        xl: 8,
        lg: 8,
        md: 12,
        sm: 24,
        xs: 24,
      },
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
      modals: false,
      modal_loading: false,
      images: [],
      templateList: [],
      columns: [],
      specsData: [],
      formValidate: {
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
        logistics: [], //选择物流方式
        freight: 2, //运费设置
        postage: 1, //设置运费金额
        is_commission: 0,
      },
      description: '',
      rule: '',
      ruleValidate: {
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
        // bargain_max_price: [
        //     { required: true, type: 'number', message: '请输单次砍价最大金额', trigger: 'blur' }
        // ],
        // bargain_min_price: [
        //     { required: true, type: 'number', message: '单次砍价最小金额', trigger: 'blur' }
        // ],
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
      },
      currentid: 0,
      picTit: '',
      tableIndex: 0,
      copy: 0,
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '140px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  mounted() {
    if (this.$route.params.id !== '0' && this.$route.params.id) {
      this.copy = this.$route.params.copy;
      this.current = 1;
      this.getInfo();
    }
    this.productGetTemplate();
  },
  methods: {
    // 详情内容
    getEditorContent(data) {
      this.description = data;
    },
    // 规则内容
    getEditorContent2(data) {
      this.rule = data;
    },
    // 添加运费模板
    freight() {
      this.$refs.template.id = 0;
      this.$refs.template.isTemplate = true;
    },
    // 砍价规格；
    productAttrs(row) {
      let that = this;
      productAttrsApi(row.id, 2)
        .then((res) => {
          let data = res.data.info;
          that.columns = data.header;
          // that.columns.unshift(radio);
          that.specsData = data.attrs;
          that.formValidate.items = data.items;
          that.$set(that.formValidate, 'attrs', [that.specsData[0]]);
        })
        .catch((res) => {
          that.$message.error(res.msg);
        });
    },
    getTemplateRow(index) {
      this.currentid = index;
      this.$set(this.formValidate, 'attrs', [this.specsData[index]]);
    },
    // 获取运费模板；
    productGetTemplate() {
      productGetTemplateApi().then((res) => {
        this.templateList = res.data;
      });
    },
    // 商品id
    getProductId(row) {
      this.modal_loading = false;
      this.modals = false;
      setTimeout(() => {
        this.formValidate = {
          // attrs: row.attrs,
          images: row.slider_image,
          info: row.store_info,
          title: row.store_name,
          store_name: row.store_name,
          image: row.image,
          unit_name: row.unit_name,
          price: 0, // 不取商品中的原价
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
          description: row.description, // 不取商品中的
          rule: '',
          id: 0,
          product_id: row.id,
          temp_id: row.temp_id,
          logistics: row.temp_id ? row.temp_id : ['1'], //选择物流方式
          freight: row.freight, //运费设置
          postage: row.postage, //设置运费金额
          custom_form: row.custom_form, //自定义表单数据
          virtual_type: row.virtual_type, //虚拟商品类型
          is_commission: row.is_commission,
        };
        this.productAttrs(row);
      }, 500);
    },
    cancel() {
      this.modals = false;
    },
    // 移动
    handleDragStart(e, item) {
      this.dragging = item;
    },
    handleDragEnd(e, item) {
      this.dragging = null;
    },
    // 首先把div变成可以放置的元素，即重写dragenter/dragover
    handleDragOver(e) {
      e.dataTransfer.dropEffect = 'move';
    },
    handleDragEnter(e, item) {
      e.dataTransfer.effectAllowed = 'move';
      if (item === this.dragging) {
        return;
      }
      const newItems = [...this.formValidate.images];
      const src = newItems.indexOf(this.dragging);
      const dst = newItems.indexOf(item);
      newItems.splice(dst, 0, ...newItems.splice(src, 1));
      this.formValidate.images = newItems;
    },
    // 具体日期
    onchangeTime(e) {
      this.formValidate.section_time = e;
    },
    // 详情
    getInfo() {
      this.spinShow = true;
      bargainInfoApi(this.$route.params.id)
        .then(async (res) => {
          let that = this;
          let info = res.data.info;
          this.formValidate = info;
          this.formValidate.rule = info.rule === null ? '' : info.rule;
          this.$set(this.formValidate, 'items', info.attrs.items);
          this.description = this.formValidate.description;
          this.columns = info.attrs.header;
          this.specsData = info.attrs.value;
          let defaultAttrs = [];
          info.attrs.value.forEach(function (item, index) {
            if (item.opt) {
              defaultAttrs.push(item);
              that.$set(that, 'currentid', index);
              that.$set(that, 'templateRadio', index);
              that.$set(that.formValidate, 'attrs', defaultAttrs);
            }
          });
          this.spinShow = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.$message.error(res.msg);
        });
    },
    // 下一步
    next(name) {
      if (this.current === 3) {
        this.formValidate.description = this.description;
        this.formValidate.rule = this.rule;
        this.$refs[name].validate((valid) => {
          if (valid) {
            if (this.copy == 1) this.formValidate.copy = 1;
            this.formValidate.id = this.$route.params.id || 0;
            this.submitOpen = true;
            bargainCreatApi(this.formValidate)
              .then(async (res) => {
                this.submitOpen = false;
                this.$message.success(res.msg);
                setTimeout(() => {
                  this.$router.push({
                    path: this.$routeProStr + '/marketing/store_bargain/index',
                  });
                }, 500);
              })
              .catch((res) => {
                this.submitOpen = false;
                this.$message.error(res.msg);
              });
          } else {
            return false;
          }
        });
      } else if (this.current === 1) {
        this.$refs[name].validate((valid) => {
          if (valid) {
            if (this.currentid === '') {
              return this.$message.error('请选择属性规格');
            } else {
              let val = this.specsData[this.currentid];
              // let formValidate = this.formValidate.attrs[0];
              // formValidate.price = val.price;
              // formValidate.min_price = val.min_price;
              // formValidate.quota = val.quota;
              if (this.formValidate.attrs[0].quota <= 0) {
                return this.$message.error('砍价限量必须大于0');
              }
              if (this.formValidate.attrs[0].quota > this.formValidate.attrs[0]['stock']) {
                return this.$message.error('砍价限量不能超过规格库存');
              }
            }
            this.current += 1;
            setTimeout((e) => {
              this.formValidate.description += ' ';
            }, 0);
          } else {
            return this.$message.warning('请完善您的信息');
          }
        });
      } else {
        if (this.formValidate.image) {
          this.current += 1;
          if (this.current == 3) {
            setTimeout((e) => {
              this.formValidate.rule += ' ';
            }, 0);
          }
        } else {
          this.$message.warning('请选择商品');
        }
      }
    },
    // 上一步
    step() {
      this.current--;
    },
    // 内容
    getContent(val) {
      this.formValidate.description = val;
    },
    // 规则
    getRole(val) {
      this.formValidate.rule = val;
    },
    // 点击商品图
    modalPicTap(tit, picTit, index) {
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
          break;
        default:
          this.specsData[this.tableIndex].pic = pc.att_dir;
          this.formValidate.attrs[0].pic = pc.att_dir;
      }
      this.modalPic = false;
    },
    // 获取多张图信息
    getPicD(pc) {
      this.images = pc;
      this.images.map((item) => {
        this.formValidate.images.push(item.att_dir);
        this.formValidate.images = this.formValidate.images.splice(0, 10);
      });
      this.modalPic = false;
    },
    handleRemove(i) {
      this.images.splice(i, 1);
      this.formValidate.images.splice(i, 1);
    },
    // 选择商品
    changeGoods() {
      this.modals = true;
      this.$nextTick((e) => {
        this.$refs.goodslist.formValidate.is_show = -1;
        this.$refs.goodslist.formValidate.type = 3;
        this.$refs.goodslist.getList();
        this.$refs.goodslist.goodsCategory();
      });
    },
    // 表单验证
    validate(prop, status, error) {
      if (status === false) {
        this.$message.error(error);
      }
    },
    // 添加自定义弹窗
    addCustomDialog(editorId) {
      window.UE.registerUI(
        'test-dialog',
        function (editor, uiName) {
          // 创建 dialog
          let dialog = new window.UE.ui.Dialog({
            // 指定弹出层中页面的路径，这里只能支持页面，路径参考常见问题 2
            iframeUrl: this.$routeProStr + '/widget.images/index.html?fodder=dialog',
            // 需要指定当前的编辑器实例
            editor: editor,
            // 指定 dialog 的名字
            name: uiName,
            // dialog 的标题
            title: '上传图片',
            // 指定 dialog 的外围样式
            cssRules: 'width:960px;height:550px;padding:20px;',
          });
          this.dialog = dialog;
          var btn = new window.UE.ui.Button({
            name: 'dialog-button',
            title: '上传图片',
            cssRules: `background-image: url(../../../assets/images/icons.png);background-position: -726px -77px;`,
            onclick: function () {
              // 渲染dialog
              dialog.render();
              dialog.open();
            },
          });
          return btn;
        },
        37,
      );
    },
  },
};
</script>

<style lang="scss" scoped>
.content_width {
  width: 460px;
}
.grey {
  color: #999;
  font-size: 12px;
}
.maxW ::v-deep .ivu-select-dropdown {
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
