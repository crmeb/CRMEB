<template>
  <div>
    <div class="i-layout-page-header header_top">
      <div class="i-layout-page-header fl_header">
        <!-- <router-link :to="{ path: '/admin/marketing/lottery/index' }"
          ><Button icon="ios-arrow-back" size="small" type="text"
            >返回</Button
          ></router-link
        > -->
        <!-- <Divider type="vertical" /> -->
        <span
          class="ivu-page-header-title mr20"
          style="padding: 0"
          v-text="$route.params.id ? '编辑抽奖信息' : '添加抽奖信息'"
        ></span>
      </div>
    </div>

    <Card :bordered="false" dis-hover class="ivu-mt">
      <div>
        <Tabs v-model="formValidate.factor" @on-click="onClickTab">
          <TabPane v-for="(item, index) in tabs" :label="item.name" :name="item.type" :key="index" />
        </Tabs>
      </div>
      <Row type="flex" class="mt30 acea-row row-middle row-center">
        <Col span="23">
          <Form
            class="form mt30"
            ref="formValidate"
            :rules="ruleValidate"
            :model="formValidate"
            @on-validate="validate"
            :label-width="labelWidth"
            :label-position="labelPosition"
            @submit.native.prevent
          >
            <Row type="flex">
              <Col span="24">
                <FormItem label="活动名称：" prop="name" label-for="name">
                  <Input class="perW30" placeholder="请输入活动名称" element-id="name" v-model="formValidate.name" />
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="活动时间：">
                  <div class="acea-row row-middle">
                    <DatePicker
                      :editable="false"
                      type="datetimerange"
                      format="yyyy-MM-dd HH:mm"
                      placeholder="请选择活动时间"
                      @on-change="onchangeTime"
                      class="perW30"
                      v-model="formValidate.period"
                    ></DatePicker>
                  </div>
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="参与用户：" prop="attends_user" label-for="attends_user">
                  <RadioGroup element-id="attends_user" v-model="formValidate.attends_user" @on-change="changeUsers">
                    <Radio :label="1" class="radio">全部用户</Radio>
                    <Radio :label="2">部分用户</Radio>
                  </RadioGroup>
                </FormItem>
              </Col>
              <Col span="24" v-if="formValidate.attends_user == 2">
                <FormItem label="" :prop="formValidate.attends_user == 2 ? 'user_level' : ''">
                  <div class="acea-row row-middle">
                    <Select multiple v-model="formValidate.user_level" class="perW30" placeholder="请选择用户等级">
                      <Option v-for="item in userLevelListApi" :value="item.id" :key="item.id">{{ item.name }}</Option>
                    </Select>
                  </div>
                </FormItem>
              </Col>
              <Col span="24" v-if="formValidate.attends_user == 2">
                <FormItem label="" :prop="formValidate.attends_user == 2 ? 'is_svip' : ''">
                  <div class="acea-row row-middle">
                    <Select v-model="formValidate.is_svip" class="perW30" clearable placeholder="请选择是否是付费会员">
                      <Option v-for="item in templateList" :value="item.id" :key="item.id">{{ item.name }}</Option>
                    </Select>
                  </div>
                </FormItem>
              </Col>
              <Col span="24" v-if="formValidate.attends_user == 2">
                <FormItem label="" :prop="formValidate.attends_user == 2 ? 'user_label' : ''">
                  <div class="acea-row row-middle">
                    <!-- <Select
                      multiple
                      v-model="formValidate.user_label"
                      class="perW30"
                      placeholder="请选择用户标签"
                    >
                      <Option
                        v-for="item in userLabelList"
                        :value="String(item.id)"
                        :key="item.id"
                        >{{ item.label_name }}</Option
                      >
                    </Select> -->
                    <div class="labelInput acea-row row-between-wrapper" @click="selectLabelShow = true">
                      <div class="">
                        <div v-if="selectDataLabel.length">
                          <Tag
                            :closable="false"
                            v-for="(item, index) in selectDataLabel"
                            @on-close="closeLabel(item)"
                            :key="index"
                            >{{ item.label_name }}</Tag
                          >
                        </div>
                        <span class="span" v-else>选择用户标签</span>
                      </div>
                      <div class="ivu-icon ivu-icon-ios-arrow-down"></div>
                    </div>
                  </div>
                  <div class="ml100 grey">三个条件都设置后,必须这些条件都满足的用户才能参加抽奖</div>
                </FormItem>
              </Col>
              <Col span="24" v-if="formValidate.factor == 5">
                <FormItem
                  label="抽奖次数："
                  :prop="formValidate.factor == 5 ? 'lottery_num_term' : ''"
                  label-for="status"
                >
                  <RadioGroup element-id="lottery_num_term" v-model="formValidate.lottery_num_term">
                    <Radio :label="1" class="radio">每天N次</Radio>
                    <Radio :label="2">每人N次</Radio>
                  </RadioGroup>
                </FormItem>
              </Col>
              <Col span="24" v-if="formValidate.factor == 5">
                <FormItem
                  label="邀请新用户最多可获得抽奖"
                  :prop="formValidate.factor == 5 ? 'lottery_num' : ''"
                  label-for="lottery_num"
                >
                  <div class="acea-row row-middle">
                    <div class="mr10 grey"></div>
                    <InputNumber
                      placeholder=""
                      element-id="lottery_num"
                      :min="1"
                      :precision="0"
                      v-model="formValidate.lottery_num"
                      class="perW20"
                    />
                    <div class="ml10 grey">次</div>
                  </div>
                </FormItem>
              </Col>
              <Col span="24" v-if="formValidate.factor == 5">
                <FormItem
                  label="邀请一位新用户关注公众号可获得抽奖"
                  :prop="formValidate.factor == 5 ? 'spread_num' : ''"
                  label-for="spread_num"
                >
                  <div class="acea-row row-middle">
                    <div class="mr10 grey"></div>
                    <InputNumber
                      placeholder=""
                      element-id="spread_num"
                      :min="1"
                      :precision="0"
                      v-model="formValidate.spread_num"
                      class="perW20"
                    />
                    <div class="ml10 grey">次</div>
                  </div>
                </FormItem>
              </Col>
              <Col span="24" v-if="formValidate.factor == 1 || formValidate.factor == 3 || formValidate.factor == 4">
                <FormItem
                  :label="formValidate.factor == 1 ? '抽奖消耗积分：' : '抽奖次数：'"
                  :prop="
                    formValidate.factor == 1 || formValidate.factor == 3 || formValidate.factor == 4 ? 'factor_num' : ''
                  "
                  label-for="factor_num"
                >
                  <div class="acea-row row-middle">
                    <div class="mr10 grey"></div>
                    <InputNumber
                      placeholder=""
                      element-id="factor_num"
                      :min="1"
                      :precision="0"
                      v-model="formValidate.factor_num"
                      class="perW20"
                    />
                    <div class="ml10 grey" v-if="formValidate.factor !== 1">次</div>
                  </div>
                </FormItem>
              </Col>
            </Row>
            <Row>
              <Col span="24">
                <FormItem label="规格选择：" prop="prize">
                  <Table :data="specsData" :columns="columns" border :draggable="true" @on-drag-drop="onDragDrop">
                    <template slot-scope="{ row, index }" slot="image">
                      <div class="acea-row row-middle row-center-wrapper" @click="modalPicTap('dan', 'goods', index)">
                        <div class="pictrue pictrueTab" v-if="row.image">
                          <img v-lazy="row.image" />
                        </div>
                        <div class="upLoad pictrueTab acea-row row-center-wrapper" v-else>
                          <Icon type="ios-camera-outline" size="21" class="iconfonts" />
                        </div>
                      </div>
                    </template>
                    <template slot-scope="{ row, index }" slot="total">
                      <InputNumber
                        v-model="row.total"
                        :max="99999"
                        :min="0"
                        :precision="0"
                        class="priceBox"
                        @on-change="
                          (data) => {
                            changeTotal(data, index);
                          }
                        "
                      ></InputNumber>
                    </template>
                    <template slot-scope="{ row, index }" slot="chance">
                      <InputNumber
                        v-model="row.chance"
                        :max="100"
                        :min="0"
                        :precision="0"
                        class="priceBox"
                        @on-change="
                          (data) => {
                            changeChance(data, index);
                          }
                        "
                      ></InputNumber>
                    </template>
                    <template slot-scope="{ row, index }" slot="type">
                      <div>{{ row.type | typeName }}</div>
                    </template>
                    <template slot-scope="{ row, index }" slot="setting">
                      <Button class="submission mr15" @click="editGoods(index)">编辑</Button>
                      <!-- <Button
                        v-if="row.type !== 1"
                        class="submission mr15"
                        @click="deleteGoods(index)"
                        >删除</Button
                      > -->
                    </template>
                  </Table>
                  <Button v-if="specsData.length < 8" type="primary" class="submission mr15 mt20" @click="addGoods"
                    >添加商品</Button
                  >
                </FormItem>
                <FormItem>
                  <div class="pl60 grey">
                    奖品必须设置为8个，列表中拖拽可调整奖品在九宫中的位置
                    <Poptip placement="bottom" width="380">
                      <a>查看位置示例图</a>
                      <div class="api" slot="content">
                        <img src="../../../assets/images/lotteryTest.png" alt="" />
                      </div>
                    </Poptip>
                  </div>
                </FormItem>
              </Col>
            </Row>
            <div>
              <FormItem
                v-if="formValidate.factor != 3 && formValidate.factor != 4"
                :prop="formValidate.factor != 3 && formValidate.factor != 4 ? 'image' : ''"
              >
                <div class="custom-label" slot="label">
                  <div>
                    <div>活动背景图</div>
                    <div>(750*750)</div>
                  </div>
                  <div>：</div>
                </div>
                <div class="acea-row">
                  <div class="pictrue" v-if="formValidate.image">
                    <img v-lazy="formValidate.image" />
                    <Button shape="circle" icon="md-close" @click.native="handleRemove()" class="btndel"></Button>
                  </div>
                  <div v-else class="upLoad acea-row row-center-wrapper" @click="modalPicTap('dan', 'danFrom')">
                    <Icon type="ios-camera-outline" size="26" class="iconfonts" />
                  </div>
                </div>
              </FormItem>
              <FormItem
                v-if="formValidate.factor != 3 && formValidate.factor != 4"
                label="中奖名单："
                :prop="formValidate.factor != 3 && formValidate.factor != 4 ? 'is_all_record' : ''"
                label-for="is_all_record"
              >
                <RadioGroup element-id="is_all_record" v-model="formValidate.is_all_record">
                  <Radio :label="0">关闭</Radio>
                  <Radio :label="1" class="radio">开启</Radio>
                </RadioGroup>
              </FormItem>
              <FormItem
                v-if="formValidate.factor != 3 && formValidate.factor != 4"
                label="个人中奖记录："
                :prop="formValidate.factor != 3 && formValidate.factor != 4 ? 'is_personal_record' : ''"
                label-for="is_personal_record"
              >
                <RadioGroup element-id="is_personal_record" v-model="formValidate.is_personal_record">
                  <Radio :label="0">关闭</Radio>
                  <Radio :label="1" class="radio">开启</Radio>
                </RadioGroup>
              </FormItem>
              <FormItem
                v-if="formValidate.factor != 3 && formValidate.factor != 4"
                label="活动规则："
                prop="is_content"
                label-for="is_content"
              >
                <RadioGroup element-id="is_content" v-model="formValidate.is_content">
                  <Radio :label="0">关闭</Radio>
                  <Radio :label="1" class="radio">开启</Radio>
                </RadioGroup>
              </FormItem>
              <FormItem
                label=""
                :prop="
                  formValidate.factor != 3 && formValidate.factor != 4 && formValidate.is_content == 1 ? 'content' : ''
                "
                v-show="formValidate.factor != 3 && formValidate.factor != 4 && formValidate.is_content == 1"
              >
                <WangEditor
                  style="width: 90%"
                  :content="formValidate.content"
                  @editorContent="getEditorContent"
                ></WangEditor>
              </FormItem>
              <FormItem label="活动状态：" prop="status" label-for="status">
                <RadioGroup element-id="status" v-model="formValidate.status">
                  <Radio :label="0">关闭</Radio>
                  <Radio :label="1" class="radio">开启</Radio>
                </RadioGroup>
              </FormItem>
            </div>
            <FormItem>
              <Button type="primary" class="submission" :loading="submitOpen" @click="next('formValidate')">
                <div v-if="!submitOpen">提交</div>
                <div v-else>提交中</div>
              </Button>
            </FormItem>
            <Spin size="large" fix v-if="spinShow"></Spin>
          </Form>
        </Col>
      </Row>
    </Card>

    <!-- 上传图片-->
    <Modal
      v-model="modalPic"
      width="950px"
      scrollable
      footer-hide
      closable
      title="上传商品图"
      :mask-closable="false"
      :z-index="1"
    >
      <uploadPictures :isChoice="isChoice" @getPic="getPic" v-if="modalPic"></uploadPictures>
    </Modal>
    <!-- 上传图片-->
    <Modal
      v-model="addGoodsModel"
      width="60%"
      scrollable
      footer-hide
      closable
      :title="title"
      :mask-closable="false"
      :z-index="1"
    >
      <addGoods v-if="addGoodsModel" @addGoodsData="addGoodsData" :editData="editData"></addGoods>
    </Modal>
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
        :selectDataLabel="selectDataLabel"
        @activeData="activeSelectData"
        @close="labelClose"
      ></userLabel>
    </Modal>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import goodsList from '@/components/goodsList/index';
import uploadPictures from '@/components/uploadPictures';
import userLabel from '@/components/userLabel';
import addGoods from './addGoods';
import { lotteryNewDetailApi, lotteryDetailApi, lotteryCreateApi, lotteryEditApi } from '@/api/lottery'; //详情 创建 编辑
import { lotteryFrom } from './formRule/lotteryFrom';
import { labelListApi } from '@/api/product';
import { levelListApi } from '@/api/user';
import WangEditor from '@/components/wangEditor/index.vue';

import { formatDate } from '@/utils/validate';
import { formatRichText } from '@/utils/editorImg';

export default {
  name: 'lotteryCreate',
  components: {
    goodsList,
    uploadPictures,
    addGoods,
    WangEditor,
    userLabel,
  },
  data() {
    return {
      selectDataLabel: [],
      selectLabelShow: false,
      content: '',
      tabs: [
        {
          name: '积分抽取',
          type: '1',
        },
        {
          name: '订单支付',
          type: '3',
        },
        {
          name: '订单评价',
          type: '4',
        },
      ],
      title: '添加商品',
      loading: false,
      userLabelList: [], //用户标签列表
      userLevelListApi: [], //用户等级列表
      submitOpen: false,
      spinShow: false,
      addGoodsModel: false,
      editData: {},
      myConfig: {
        autoHeightEnabled: false, // 编辑器不自动被内容撑高
        initialFrameHeight: 500, // 初始容器高度
        initialFrameWidth: '100%', // 初始容器宽度
        UEDITOR_HOME_URL: '/admin/UEditor/',
        serverUrl: '',
      },
      isChoice: '单选',
      current: 0,
      modalPic: false,
      modal_loading: false,
      images: [],
      templateList: [
        { id: 0, name: '非付费会员' },
        { id: 1, name: '付费会员' },
      ],
      columns: [
        {
          title: '序号',
          type: 'index',
          width: 60,
          align: 'center',
        },
        {
          title: '图片',
          slot: 'image',
          align: 'center',
          minWidth: 120,
        },
        {
          title: '名称',
          align: 'center',
          minWidth: 80,
          key: 'name',
        },
        {
          title: '奖品',
          slot: 'type',
          align: 'center',
          minWidth: 80,
        },
        {
          title: '提示语',
          key: 'prompt',
          align: 'center',
          minWidth: 80,
        },
        {
          title: '数量',
          slot: 'total',
          align: 'center',
          minWidth: 80,
        },
        {
          title: '奖品权重',
          slot: 'chance',
          align: 'center',
          minWidth: 80,
        },
        {
          title: '奖品概率',
          key: 'probability',
          align: 'center',
          minWidth: 80,
        },
        {
          title: '操作',
          slot: 'setting',
          align: 'center',
          minWidth: 80,
        },
      ],
      specsData: [
        {
          type: 1, //类型 1：未中奖 2：积分  3:余额  4：红包 5:优惠券 6：站内商品
          name: '', //活动名称
          num: 10, //奖品数量
          image: '', //奖品图片
          chance: 0, //中奖权重
          total: 0, //奖品数量
          prompt: '', //提示语
        },
        {
          type: 1, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
          name: '', //活动名称
          num: 0, //奖品数量
          image: '', //奖品图片
          chance: 0, //中奖权重
          total: 0, //奖品数量
          prompt: '', //提示语
        },
        {
          type: 1, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
          name: '', //活动名称
          num: 0, //奖品数量
          image: '', //奖品图片
          chance: 0, //中奖权重
          total: 0, //奖品数量
          prompt: '', //提示语
        },
        {
          type: 1, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
          name: '', //活动名称
          num: 0, //奖品数量
          image: '', //奖品图片
          chance: 0, //中奖权重
          total: 0, //奖品数量
          prompt: '', //提示语
        },
        {
          type: 1, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
          name: '', //活动名称
          num: 0, //奖品数量
          image: '', //奖品图片
          chance: 0, //中奖权重
          total: 0, //奖品数量
          prompt: '', //提示语
        },
        {
          type: 1, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
          name: '', //活动名称
          num: 0, //奖品数量
          image: '', //奖品图片
          chance: 0, //中奖权重
          total: 0, //奖品数量
          prompt: '', //提示语
        },
        {
          type: 1, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
          name: '', //活动名称
          num: 0, //奖品数量
          image: '', //奖品图片
          chance: 0, //中奖权重
          total: 0, //奖品数量
          prompt: '', //提示语
        },
        {
          type: 1, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
          name: '', //活动名称
          num: 0, //奖品数量
          image: '', //奖品图片
          chance: 0, //中奖权重
          total: 0, //奖品数量
          prompt: '', //提示语
        },
      ],
      formValidate: {
        images: [],
        name: '', //活动名称
        desc: '', //活动描述
        image: '', //活动背景图
        factor: '1', //抽奖类型：1:积分 2:余额 3：下单支付成功 4:订单评价',5:关注
        factor_num: 1, //获取一次抽奖的条件数量
        attends_user: 1, //参与用户1：所有  2：部分
        user_level: [], //参与用户等级
        user_label: [], //参与用户标签
        is_svip: '-1', //参与用户是否付费会员
        prize_num: 0, //奖品数量
        period: [], //活动时间
        prize: [], //奖品数组
        lottery_num_term: 1, //抽奖次数限制：1：每天2：每人
        lottery_num: 1, //抽奖次数
        spread_num: 1, //关注推广获取抽奖次数
        is_all_record: 0, //中奖纪录展示
        is_personal_record: 0, //个人中奖纪录展示
        is_content: 0, //活动规格是否展示
        content: '', //富文本内容
        status: 0, //状态
      },
      ruleValidate: lotteryFrom,
      currentid: '',
      picTit: '',
      tableIndex: 0,
      copy: 0,
      editIndex: null,
      id: '',
      copy: 0,
    };
  },
  filters: {
    typeName(type) {
      if (type == 1) {
        return '未中奖';
      } else if (type == 2) {
        return '积分';
      } else if (type == 3) {
        return '余额';
      } else if (type == 4) {
        return '红包';
      } else if (type == 5) {
        return '优惠券';
      } else if (type == 6) {
        return '商品';
      }
    },
  },
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 135;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  mounted() {
    this.getInfo();
    this.labelListApi();
    this.levelListApi();
  },
  methods: {
    changeUsers(e) {
      if (e == 1) {
        this.formValidate.user_level = []; //参与用户等级
        this.formValidate.user_label = []; //参与用户标签
        this.formValidate.is_svip = '-1'; //参与用户是否付费会员
        this.selectDataLabel = []; //参与用户是否付费会员
      }
    },
    // 标签弹窗关闭
    labelClose() {
      this.selectLabelShow = false;
    },
    activeSelectData(data) {
      this.selectLabelShow = false;
      this.selectDataLabel = data;
    },
    onClickTab(e) {
      this.formValidate.factor = e;
      this.getInfo(e);
    },
    getEditorContent(data) {
      this.content = data;
    },
    //用户标签列表
    labelListApi() {
      labelListApi().then((res) => {
        this.userLabelList = res.data.list;
      });
    },
    //用户等级列表
    levelListApi() {
      levelListApi().then((res) => {
        this.userLevelListApi = res.data.list;
      });
    },
    // 具体日期
    onchangeTime(e) {
      this.$nextTick(() => {
        this.$set(this.formValidate, 'period', e);
      });
    },
    // 详情
    getInfo(e) {
      this.spinShow = true;
      lotteryNewDetailApi(this.formValidate.factor)
        .then((res) => {
          if (res.status == 200 && !Array.isArray(res.data)) {
            this.formValidate = res.data;
            this.formValidate.user_level = res.data.user_level || [];
            this.selectDataLabel = res.data.user_label || [];
            this.formValidate.is_svip = res.data.is_svip;
            this.content = res.data.is_content ? res.data.content : '';
            this.formValidate.factor = res.data.factor.toString();
            this.formValidate.period = [
              this.formatDate(res.data.start_time) || '',
              this.formatDate(res.data.end_time) || '',
            ];

            this.specsData = res.data.prize;
            this.getProbability();
          } else {
            this.formValidate = {
              images: [],
              name: '', //活动名称
              desc: '', //活动描述
              image: '', //活动背景图
              factor: e.toString(), //抽奖类型：1:积分 2:余额 3：下单支付成功 4:订单评价',5:关注
              factor_num: 1, //获取一次抽奖的条件数量
              attends_user: 1, //参与用户1：所有  2：部分
              user_level: [], //参与用户等级
              user_label: [], //参与用户标签
              is_svip: '-1', //参与用户是否付费会员
              prize_num: 0, //奖品数量
              period: [], //活动时间
              prize: [], //奖品数组
              lottery_num_term: 1, //抽奖次数限制：1：每天2：每人
              lottery_num: 1, //抽奖次数
              spread_num: 1, //关注推广获取抽奖次数
              is_all_record: 0, //中奖纪录展示
              is_personal_record: 0, //个人中奖纪录展示
              is_content: 0, //活动规格是否展示
              content: '', //富文本内容
              status: 0, //状态
            };
            this.specsData = [
              {
                type: 1, //类型 1：未中奖 2：积分  3:余额  4：红包 5:优惠券 6：站内商品
                name: '', //活动名称
                num: 10, //奖品数量
                image: '', //奖品图片
                chance: 0, //中奖权重
                total: 0, //奖品数量
                prompt: '', //提示语
              },
              {
                type: 1, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
                name: '', //活动名称
                num: 0, //奖品数量
                image: '', //奖品图片
                chance: 0, //中奖权重
                total: 0, //奖品数量
                prompt: '', //提示语
              },
              {
                type: 1, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
                name: '', //活动名称
                num: 0, //奖品数量
                image: '', //奖品图片
                chance: 0, //中奖权重
                total: 0, //奖品数量
                prompt: '', //提示语
              },
              {
                type: 1, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
                name: '', //活动名称
                num: 0, //奖品数量
                image: '', //奖品图片
                chance: 0, //中奖权重
                total: 0, //奖品数量
                prompt: '', //提示语
              },
              {
                type: 1, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
                name: '', //活动名称
                num: 0, //奖品数量
                image: '', //奖品图片
                chance: 0, //中奖权重
                total: 0, //奖品数量
                prompt: '', //提示语
              },
              {
                type: 1, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
                name: '', //活动名称
                num: 0, //奖品数量
                image: '', //奖品图片
                chance: 0, //中奖权重
                total: 0, //奖品数量
                prompt: '', //提示语
              },
              {
                type: 1, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
                name: '', //活动名称
                num: 0, //奖品数量
                image: '', //奖品图片
                chance: 0, //中奖权重
                total: 0, //奖品数量
                prompt: '', //提示语
              },
              {
                type: 1, //类型 1：未中奖2：积分  3:余额  4：红包 5:优惠券 6：站内商品
                name: '', //活动名称
                num: 0, //奖品数量
                image: '', //奖品图片
                chance: 0, //中奖权重
                total: 0, //奖品数量
                prompt: '', //提示语
              },
            ];
          }
        })
        .catch((err) => {});
      this.spinShow = false;
    },
    // 下一步
    next(name) {
      this.formValidate.prize = this.specsData;
      if (this.formValidate.is_content) {
        this.formValidate.content = formatRichText(this.content);
      }
      if (this.formValidate.attends_user == 2) {
        if (this.selectDataLabel.length) {
          let activeIds = [];
          this.selectDataLabel.forEach((item) => {
            activeIds.push(item.id);
          });
          this.formValidate.user_label = activeIds;
        }
      }
      if (this.submitOpen) return false;
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.submitOpen = true;
          if (this.formValidate.id && !this.copy) {
            lotteryEditApi(this.formValidate.id, this.formValidate)
              .then(async (res) => {
                this.$Message.success(res.msg);
                this.submitOpen = false;
                // setTimeout(() => {
                //   this.submitOpen = false;
                //   this.$router.push({
                //     path: "/admin/marketing/lottery/recording_list",
                //   });
                // }, 500);
              })
              .catch((res) => {
                this.submitOpen = false;
                this.$Message.error(res.msg);
              });
          } else {
            lotteryCreateApi(this.formValidate)
              .then(async (res) => {
                this.submitOpen = false;
                this.$Message.success(res.msg);
                // setTimeout(() => {
                //   this.submitOpen = false;
                //   this.$router.push({
                //     path: "/admin/marketing/lottery/recording_list",
                //   });
                // }, 500);
              })
              .catch((res) => {
                this.submitOpen = false;
                this.$Message.error(res.msg);
              });
          }
        } else {
          return false;
        }
      });
    },
    // 上一步
    step() {
      this.current--;
    },
    // 点击商品图
    modalPicTap(tit, picTit, index) {
      this.modalPic = true;
      this.isChoice = tit === 'dan' ? '单选' : '多选';
      this.picTit = picTit || '';
      this.tableIndex = index;
    },
    // 获取单张图片信息
    getPic(pc) {
      switch (this.picTit) {
        case 'danFrom':
          this.formValidate.image = pc.att_dir;
          break;
        default:
          this.specsData[this.tableIndex].image = pc.att_dir;
      }
      this.modalPic = false;
    },
    handleRemove() {
      this.formValidate.image = '';
    },
    // 表单验证
    validate(prop, status, error) {
      if (status === false) {
        this.$Message.error(error);
        return false;
      } else {
        return true;
      }
    },
    //新增商品
    addGoods() {
      this.addGoodsModel = true;
      this.title = '添加商品';
      this.editData = {};
    },
    //编辑商品
    editGoods(index) {
      this.addGoodsModel = true;
      this.title = '添加奖品';
      this.editData = this.specsData[index];
      this.editIndex = index;
    },
    //删除商品
    deleteGoods(index) {
      this.specsData.splice(index, 1);
    },
    //获取数组中某个字段之和
    sumArr(arr, name) {
      let arrData = [];
      for (let i = 0; i < arr.length; i++) {
        arrData.push(arr[i][name]);
      }
      return eval(arrData.join('+'));
    },
    addGoodsData(data) {
      this.editIndex != null
        ? this.$set(this.specsData, [this.editIndex], data)
        : this.specsData.length < 8
        ? this.specsData.push(data)
        : this.$Message.warning('最多添加8个奖品');
      this.getProbability();
      this.addGoodsModel = false;
      this.editIndex = null;
    },
    changeChance(data, index) {
      this.$set(this.specsData[index], 'chance', data);
      this.$nextTick((e) => {
        this.getProbability();
      });
    },
    changeTotal(data, index) {
      this.$set(this.specsData[index], 'total', data);
    },
    //获取商品中奖概率
    getProbability() {
      let sum = 0;
      sum = this.sumArr(this.specsData, 'chance');
      for (let j = 0; j < this.specsData.length; j++) {
        if (sum == 0) {
          this.$set(this.specsData[j], 'probability', '0%');
        } else {
          this.$set(this.specsData[j], 'probability', ((this.specsData[j].chance / sum) * 100).toFixed(2) + '%');
        }
      }
    },
    //修改排序
    onDragDrop(a, b) {
      this.specsData.splice(b, 1, ...this.specsData.splice(a, 1, this.specsData[b]));
    },
    //时间格式转换
    formatDate(time) {
      if (time) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      } else {
        return '';
      }
    },
  },
};
</script>

<style scoped lang="stylus">
.custom-label {
  display: inline-flex;
  line-height: 1.5;
}

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
    width: 58px;
    height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 0px;
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

.labelInput {
  border: 1px solid #dcdee2;
  padding: 0 6px;
  width: 30%;
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
</style>
