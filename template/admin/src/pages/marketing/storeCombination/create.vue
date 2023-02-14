<template>
  <div>
    <div class="i-layout-page-header header_top">
      <div class="i-layout-page-header fl_header">
        <router-link :to="{ path: '/admin/marketing/store_combination/index' }"
          ><Button icon="ios-arrow-back" size="small" type="text">返回</Button></router-link
        >
        <Divider type="vertical" />
        <span
          class="ivu-page-header-title mr20"
          style="padding: 0"
          v-text="$route.params.id ? '编辑拼团商品' : '添加拼团商品'"
        ></span>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Row type="flex" class="mt30 acea-row row-middle row-center">
        <Col span="20">
          <Steps :current="current">
            <Step title="选择拼团商品"></Step>
            <Step title="填写基础信息"></Step>
            <Step title="修改商品详情"></Step>
          </Steps>
        </Col>
        <Col span="23">
          <Form
            class="form mt30"
            ref="formValidate"
            :model="formValidate"
            :rules="ruleValidate"
            @on-validate="validate"
            :label-width="labelWidth"
            :label-position="labelPosition"
            @submit.native.prevent
          >
            <FormItem label="选择商品：" prop="image_input" v-if="current === 0">
              <div class="picBox" @click="changeGoods">
                <div class="pictrue" v-if="formValidate.image">
                  <img v-lazy="formValidate.image" />
                </div>
                <div class="upLoad acea-row row-center-wrapper" v-else>
                  <Icon type="ios-camera-outline" size="26" />
                </div>
              </div>
            </FormItem>
            <Row v-show="current === 1" type="flex">
              <Col span="24">
                <FormItem label="商品主图：" prop="image">
                  <div class="picBox" @click="modalPicTap('dan', 'danFrom')">
                    <div class="pictrue" v-if="formValidate.image">
                      <img v-lazy="formValidate.image" />
                    </div>
                    <div class="upLoad acea-row row-center-wrapper" v-else>
                      <Icon type="ios-camera-outline" size="26" />
                    </div>
                  </div>
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="商品轮播图：" prop="images">
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
                      <Button
                        shape="circle"
                        icon="md-close"
                        @click.native="handleRemove(index)"
                        class="btndel"
                      ></Button>
                    </div>
                    <div
                      v-if="formValidate.images.length < 10"
                      class="upLoad acea-row row-center-wrapper"
                      @click="modalPicTap('duo')"
                    >
                      <Icon type="ios-camera-outline" size="26" />
                    </div>
                  </div>
                </FormItem>
              </Col>
              <Col span="24">
                <Col v-bind="grid">
                  <FormItem label="拼团名称：" prop="title" label-for="title">
                    <Input placeholder="请输入拼团名称" element-id="title" v-model="formValidate.title" />
                  </FormItem>
                </Col>
              </Col>
              <Col span="24">
                <Col v-bind="grid">
                  <FormItem label="拼团简介：" prop="info" label-for="info">
                    <Input
                      placeholder="请输入拼团简介"
                      type="textarea"
                      :rows="4"
                      element-id="info"
                      v-model="formValidate.info"
                    />
                  </FormItem>
                </Col>
              </Col>
              <Col span="24">
                <FormItem label="拼团时间：" prop="section_time">
                  <div class="acea-row row-middle">
                    <DatePicker
                      :editable="false"
                      type="datetimerange"
                      format="yyyy-MM-dd HH:mm"
                      placeholder="请选择活动时间"
                      @on-change="onchangeTime"
                      class="perW30"
                      :value="formValidate.section_time"
                      v-model="formValidate.section_time"
                    ></DatePicker>
                    <div class="ml10 grey">设置活动开启结束时间，用户可以在设置时间内发起参与拼团</div>
                  </div>
                </FormItem>
              </Col>
              <Col span="24" v-if="formValidate.virtual_type == 0">
                <FormItem label="物流方式：" prop="logistics">
                  <CheckboxGroup v-model="formValidate.logistics">
                    <Checkbox label="1">快递</Checkbox>
                    <Checkbox label="2">到店核销</Checkbox>
                  </CheckboxGroup>
                </FormItem>
              </Col>
              <Col span="24" v-if="formValidate.virtual_type == 0">
                <FormItem label="运费设置：" :prop="formValidate.freight != 1 ? 'freight' : ''">
                  <RadioGroup v-model="formValidate.freight">
                    <Radio :label="2">固定邮费</Radio>
                    <Radio :label="3">运费模板</Radio>
                  </RadioGroup>
                </FormItem>
              </Col>
              <Col
                span="24"
                v-if="formValidate.freight != 3 && formValidate.freight != 1 && formValidate.virtual_type == 0"
              >
                <FormItem label="">
                  <div class="acea-row">
                    <InputNumber
                      min="0.01"
                      max="10000"
                      v-model="formValidate.postage"
                      placeholder="请输入金额"
                      class="perW20 maxW"
                    />
                  </div>
                </FormItem>
              </Col>
              <Col span="24" v-if="formValidate.freight == 3 && formValidate.virtual_type == 0">
                <FormItem label="" prop="temp_id">
                  <div class="acea-row">
                    <Select v-model="formValidate.temp_id" clearable placeholder="请选择运费模板" class="perW20 maxW">
                      <Option v-for="(item, index) in templateList" :value="item.id" :key="index">{{
                        item.name
                      }}</Option>
                    </Select>
                    <span class="addfont" @click="freight">新增运费模板</span>
                  </div>
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="拼团时效(单位 小时)：" prop="effective_time">
                  <div class="acea-row row-middle">
                    <InputNumber
                      placeholder="请输入拼团时效(单位 小时)"
                      class="perW20"
                      element-id="effective_time"
                      v-model="formValidate.effective_time"
                    />
                    <div class="ml10 grey">
                      用户发起拼团后开始计时，需在设置时间内邀请到规定好友人数参团，超过时效时间，则系统判定拼团失败，自动发起退款
                    </div>
                  </div>
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="拼团人数：" prop="people">
                  <div class="acea-row row-middle">
                    <InputNumber
                      :min="2"
                      :max="10000"
                      placeholder="请输入拼团人数"
                      :precision="0"
                      element-id="people"
                      v-model="formValidate.people"
                      class="perW20"
                    />
                    <div class="ml10 grey">单次拼团需要参与的用户数</div>
                  </div>
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="虚拟成团补齐人数：" prop="virtualPeople">
                  <div class="acea-row row-middle">
                    <InputNumber
                      placeholder="设置虚拟成团的补齐人数"
                      :precision="0"
                      :max="10000"
                      :min="0"
                      v-model="formValidate.virtualPeople"
                      element-id="virtualPeople"
                      class="perW20"
                    />
                    <div class="ml10 grey">
                      设置虚拟成团的补齐人数，如：5人团设置补齐2人，当团队成员大于等于3人时，拼团结束时自动补齐剩余最多2个位置，不开启虚拟成团请设置为0
                    </div>
                  </div>
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="总购买数量限制：" prop="num">
                  <div class="acea-row row-middle">
                    <InputNumber
                      :min="1"
                      placeholder="请输入总数量限制"
                      :precision="0"
                      :max="10000"
                      element-id="num"
                      v-model="formValidate.num"
                      class="perW20"
                    />
                    <div class="ml10 grey">
                      该商品活动期间内，用户可购买的最大数量。例如设置为4，表示本次活动有效期内，每个用户最多可购买4件
                    </div>
                  </div>
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="单次购买数量限制：" prop="once_num">
                  <div class="acea-row row-middle">
                    <InputNumber
                      :min="1"
                      placeholder="请输入单次购买数量限制"
                      :precision="0"
                      :max="10000"
                      element-id="once_num"
                      v-model="formValidate.once_num"
                      class="perW20"
                    />
                    <div class="ml10 grey">
                      用户参与拼团时，一次购买最大数量限制。例如设置为2，表示每次参与拼团时，用户一次购买数量最大可选择2个
                    </div>
                  </div>
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="单位：" prop="unit_name" label-for="unit_name">
                  <Input
                    placeholder="请输入单位"
                    element-id="unit_name"
                    v-model="formValidate.unit_name"
                    class="perW20"
                  />
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="团长返佣比例：" prop="head_commission">
                  <div class="acea-row row-middle">
                    <InputNumber
                      :min="0"
                      :max="100"
                      placeholder="团长返佣比例"
                      :precision="0"
                      element-id="head_commission"
                      v-model="formValidate.head_commission"
                      class="perW20"
                    />
                    <div class="ml10 grey">
                      拼团成功后，如果团长是分销员，则在订单确认收货时会给团长返一定的佣金，佣金比例是实际支付金额的0-100%
                    </div>
                  </div>
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="拼团是否参与分销：" props="is_commission" label-for="is_commission">
                  <div class="acea-row row-middle">
                    <RadioGroup element-id="is_commission" v-model="formValidate.is_commission">
                      <Radio :label="1" class="radio">开启</Radio>
                      <Radio :label="0">关闭</Radio>
                    </RadioGroup>
                    <div class="ml10 grey">拼团商品是否参与商城分销返佣</div>
                  </div>
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="排序：">
                  <InputNumber
                    placeholder="请输入排序"
                    element-id="sort"
                    :precision="0"
                    :max="10000"
                    :min="0"
                    v-model="formValidate.sort"
                    class="perW10"
                  />
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="热门推荐：" props="is_hot" label-for="is_hot">
                  <RadioGroup element-id="is_hot" v-model="formValidate.is_host">
                    <Radio :label="1" class="radio">开启</Radio>
                    <Radio :label="0">关闭</Radio>
                  </RadioGroup>
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="活动状态：" props="is_show" label-for="is_show">
                  <RadioGroup element-id="status" v-model="formValidate.is_show">
                    <Radio :label="1" class="radio">开启</Radio>
                    <Radio :label="0">关闭</Radio>
                  </RadioGroup>
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="规格选择：">
                  <Table :data="specsData" :columns="columns" border @on-selection-change="changeCheckbox">
                    <template slot-scope="{ row, index }" slot="price">
                      <InputNumber
                        v-model="row.price"
                        :min="0"
                        :precision="2"
                        class="priceBox"
                        :active-change="false"
                        @on-change="
                          (e) => {
                            changePrice(e, index);
                          }
                        "
                      ></InputNumber>
                    </template>
                    <template slot-scope="{ row, index }" slot="pic">
                      <div
                        class="acea-row row-middle row-center-wrapper"
                        @click="modalPicTap('dan', 'danTable', index)"
                      >
                        <div class="pictrue pictrueTab" v-if="row.pic">
                          <img v-lazy="row.pic" />
                        </div>
                        <div class="upLoad pictrueTab acea-row row-center-wrapper" v-else>
                          <Icon type="ios-camera-outline" size="21" />
                        </div>
                      </div>
                    </template>
                  </Table>
                </FormItem>
              </Col>
            </Row>
            <Row v-show="current === 2">
              <Col span="24">
                <FormItem label="内容：">
                  <WangEditor
                    style="width: 90%"
                    :content="formValidate.description"
                    @editorContent="getEditorContent"
                  ></WangEditor>
                </FormItem>
              </Col>
            </Row>
            <FormItem>
              <Button
                class="submission mr15"
                @click="step"
                v-show="current !== 0"
                :disabled="$route.params.id && current === 1"
                >上一步</Button
              >
              <Button
                type="primary"
                :disabled="submitOpen && current === 2"
                class="submission"
                @click="next('formValidate')"
                v-text="current === 2 ? '提交' : '下一步'"
              ></Button>
            </FormItem>
          </Form>
          <Spin size="large" fix v-if="spinShow"></Spin>
        </Col>
      </Row>
    </Card>
    <!-- 选择商品-->
    <Modal
      v-model="modals"
      title="商品列表"
      footerHide
      class="paymentFooter"
      scrollable
      width="900"
      @on-cancel="cancel"
    >
      <goods-list ref="goodslist" @getProductId="getProductId"></goods-list>
    </Modal>
    <!-- 上传图片-->
    <Modal
      v-model="modalPic"
      width="950px"
      scrollable
      footer-hide
      closable
      title="上传商品图"
      :mask-closable="false"
      :z-index="888"
    >
      <uploadPictures
        :isChoice="isChoice"
        @getPic="getPic"
        @getPicD="getPicD"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="modalPic"
      ></uploadPictures>
    </Modal>
    <!-- 运费模板-->
    <freight-template ref="template" @addSuccess="productGetTemplate"></freight-template>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import goodsList from '@/components/goodsList/index';
import WangEditor from '@/components/wangEditor/index.vue';
import uploadPictures from '@/components/uploadPictures';
import { combinationInfoApi, combinationCreatApi, productAttrsApi } from '@/api/marketing';
import { productGetTemplateApi } from '@/api/product';
import freightTemplate from '@/components/freightTemplate/index';
export default {
  name: 'storeCombinationCreate',
  components: {
    goodsList,
    uploadPictures,
    WangEditor,
    freightTemplate,
  },
  data() {
    return {
      submitOpen: false,
      spinShow: false,
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
      myConfig: {
        autoHeightEnabled: false, // 编辑器不自动被内容撑高
        initialFrameHeight: 500, // 初始容器高度
        initialFrameWidth: '100%', // 初始容器宽度
        UEDITOR_HOME_URL: '/admin/UEditor/',
        serverUrl: '',
      },
      modals: false,
      modal_loading: false,
      images: [],
      templateList: [],
      columns: [],
      specsData: [],
      picTit: '',
      tableIndex: 0,
      formValidate: {
        images: [],
        info: '',
        title: '',
        image: '',
        unit_name: '',
        price: 0,
        effective_time: 24,
        stock: 1,
        sales: 0,
        sort: 0,
        postage: 0,
        is_postage: 0,
        is_commission: 0,
        is_host: 0,
        is_show: 0,
        section_time: [],
        description: '',
        id: 0,
        product_id: 0,
        people: 2,
        once_num: 1,
        num: 1,
        temp_id: '',
        attrs: [],
        items: [],
        virtual: 100,
        virtualPeople: 0,
        head_commission: 0,
        logistics: ['1'], //选择物流方式
        freight: 2, //运费设置
        postage: 1, //设置运费金额
      },
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
        title: [{ required: true, message: '请输入拼团名称', trigger: 'blur' }],
        info: [{ required: true, message: '请输入拼团简介', trigger: 'blur' }],
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
            message: '请输入拼团价',
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
        stock: [
          {
            required: true,
            type: 'number',
            message: '请输入库存',
            trigger: 'blur',
          },
        ],
        give_integral: [
          {
            required: true,
            type: 'number',
            message: '请输入赠送积分',
            trigger: 'blur',
          },
        ],
        effective_time: [
          {
            required: true,
            type: 'number',
            message: '请输入拼团时效(单位 小时)',
            trigger: 'blur',
          },
        ],
        people: [
          {
            required: true,
            type: 'number',
            message: '请输入拼团人数',
            trigger: 'blur',
          },
        ],
        num: [
          {
            required: true,
            type: 'number',
            message: '请输入购买数量限制',
            trigger: 'blur',
          },
        ],
        once_num: [
          {
            required: true,
            type: 'number',
            message: '请输入单次购买数量限制',
            trigger: 'blur',
          },
        ],
        virtualPeople: [
          {
            required: true,
            type: 'number',
            message: '请输入虚拟成团补齐人数',
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
      copy: 0,
      description: '',
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 155;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  mounted() {
    if (this.$route.params.id) {
      this.copy = this.$route.params.copy;
      this.current = 1;
      this.getInfo();
    }
    this.productGetTemplate();
  },
  methods: {
    changePrice(e, index) {
      this.$set(this.specsData[index], 'price', e);
    },
    getEditorContent(data) {
      this.description = data;
    },
    // 添加运费模板
    freight() {
      this.$refs.template.id = 0;
      this.$refs.template.isTemplate = true;
    },
    // 拼团规格；
    productAttrs(row) {
      let that = this;
      productAttrsApi(row.id, 3)
        .then((res) => {
          let data = res.data.info;
          let selection = {
            type: 'selection',
            width: 60,
            align: 'center',
          };
          that.specsData = data.attrs;
          that.specsData.forEach(function (item, index) {
            that.$set(that.specsData[index], 'id', index);
          });
          that.formValidate.items = data.items;
          that.columns = data.header;
          that.columns.unshift(selection);
          that.inputChange(data);
        })
        .catch((res) => {
          that.$Message.error(res.msg);
        });
    },
    inputChange(data) {
      let that = this;
      let $index = [];
      data.header.forEach(function (item, index) {
        if (item.type === 1) {
          $index.push({ index: index, key: item.key, title: item.title });
        }
      });
      $index.forEach(function (item, index) {
        let title = item.title;
        let key = item.key;
        let row = {
          title: title,
          key: key,
          align: 'center',
          minWidth: 100,
          render: (h, params) => {
            return h('div', [
              h('InputNumber', {
                props: {
                  min: 1,
                  precision: 0,
                  value: params.row.quota,
                },
                on: {
                  'on-change': (e) => {
                    params.row.quota = e;
                    that.specsData[params.index] = params.row;
                    if (!!that.formValidate.attrs && that.formValidate.attrs.length) {
                      that.formValidate.attrs.forEach((v, index) => {
                        if (v.id === params.row.id) {
                          that.formValidate.attrs.splice(index, 1, params.row);
                        }
                      });
                    }
                  },
                },
              }),
            ]);
          },
        };
        that.columns.splice(item.index, 1, row);
      });
    },
    // 多选
    changeCheckbox(selection) {
      this.formValidate.attrs = selection;
    },
    // 获取运费模板；
    productGetTemplate() {
      productGetTemplateApi().then((res) => {
        this.templateList = res.data;
      });
    },
    // 表单验证
    validate(prop, status, error) {
      if (status === false) {
        this.$Message.error(error);
      }
    },
    // 商品id
    getProductId(row) {
      this.modal_loading = false;
      this.modals = false;
      setTimeout(() => {
        this.formValidate = {
          images: row.slider_image,
          info: row.store_info,
          title: row.store_name,
          image: row.image,
          unit_name: row.unit_name,
          price: 0, // 不取商品中的原价
          effective_time: 24,
          stock: row.stock,
          sales: row.sales,
          sort: row.sort,
          postage: row.postage,
          is_postage: row.is_postage,
          is_commission: 0,
          is_host: row.is_hot,
          is_show: 0,
          section_time: [],
          description: row.description, // 不取商品中的
          id: 0,
          people: 2,
          num: 1,
          once_num: 1,
          product_id: row.id,
          temp_id: row.temp_id,
          virtual: 100,
          virtualPeople: 0,
          logistics: row.logistics, //选择物流方式
          freight: row.freight, //运费设置
          postage: row.postage, //设置运费金额
          custom_form: row.custom_form, //自定义表单数据
          virtual_type: row.virtual_type, //虚拟商品类型
          head_commission: 0,
        };
        this.productAttrs(row);
      }, 500);
    },
    cancel() {
      this.modals = false;
    },
    // 具体日期
    onchangeTime(e) {
      this.formValidate.section_time = e;
    },
    // 详情
    getInfo() {
      this.spinShow = true;
      combinationInfoApi(this.$route.params.id)
        .then(async (res) => {
          let that = this;
          let info = res.data.info;
          let selection = {
            type: 'selection',
            width: 60,
            align: 'center',
          };
          this.formValidate = info;
          this.formValidate.virtualPeople = parseInt(
            this.formValidate.people - this.formValidate.people * (this.formValidate.virtual / 100),
          );
          this.$set(this.formValidate, 'items', info.attrs.items);
          this.columns = info.attrs.header;
          this.columns.unshift(selection);
          this.specsData = info.attrs.value;
          that.specsData.forEach(function (item, index) {
            that.$set(that.specsData[index], 'id', index);
          });
          let data = info.attrs;
          let attr = [];
          for (let index in info.attrs.value) {
            if (info.attrs.value[index]._checked) {
              attr.push(info.attrs.value[index]);
            }
          }
          that.formValidate.attrs = attr;
          that.inputChange(data);
          this.spinShow = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.$Message.error(res.msg);
        });
    },
    // 下一步
    next(name) {
      let that = this;
      if (this.current === 2) {
        this.formValidate.description = this.description;
        this.$refs[name].validate((valid) => {
          if (valid) {
            if (this.copy == 1) this.formValidate.copy = 1;
            this.formValidate.id = Number(this.$route.params.id) || 0;
            this.submitOpen = true;
            this.formValidate.virtual = parseInt(
              ((this.formValidate.people - this.formValidate.virtualPeople) / this.formValidate.people) * 100,
            );
            combinationCreatApi(this.formValidate)
              .then(async (res) => {
                this.submitOpen = false;
                this.$Message.success(res.msg);
                setTimeout(() => {
                  this.$router.push({
                    path: '/admin/marketing/store_combination/index',
                  });
                }, 500);
              })
              .catch((res) => {
                this.submitOpen = false;
                this.$Message.error(res.msg);
              });
          } else {
            return false;
          }
        });
      } else if (this.current === 1) {
        this.$refs[name].validate((valid) => {
          if (valid) {
            if (that.formValidate.people < 2) {
              return that.$Message.error('拼团人数必须大于2');
            }
            if (that.formValidate.num < 0) {
              return that.$Message.error('购买数量限制必须大于0');
            }
            if (that.formValidate.once_num < 0) {
              return that.$Message.error('单次购买数量限制必须大于0');
            }
            if (!that.formValidate.attrs) {
              return that.$Message.error('请选择属性规格');
            } else {
              for (let index in that.formValidate.attrs) {
                if (that.formValidate.attrs[index].quota <= 0) {
                  return that.$Message.error('拼团限量必须大于0');
                }
                if (this.formValidate.attrs[index].quota > this.formValidate.attrs[index]['stock']) {
                  return this.$Message.error('拼团限量不能超过规格库存');
                }
              }
            }
            this.current += 1;
          } else {
            return this.$Message.warning('请完善您的信息');
          }
        });
      } else {
        if (this.formValidate.image) {
          this.current += 1;
        } else {
          this.$Message.warning('请选择商品');
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
          if (!!this.formValidate.attrs && this.formValidate.attrs.length) {
            this.$set(this.specsData[this.tableIndex], '_checked', true);
          }
          this.specsData[this.tableIndex].pic = pc.att_dir;
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
      this.$refs.goodslist.formValidate.is_presale = 0;
      this.$refs.goodslist.getList();
      this.$refs.goodslist.goodsCategory();
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
  },
};
</script>

<style scoped lang="stylus">
.grey {
  color: #999;
}

.maxW /deep/.ivu-select-dropdown {
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
  font-size: 13px;
  color: #1890FF;
  margin-left: 14px;
  cursor: pointer;
  margin-left: 10px;
  cursor: pointer;
}
</style>
