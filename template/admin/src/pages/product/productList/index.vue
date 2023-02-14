<template>
  <div class="article-manager">
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Tabs class="mb20" v-model="artFrom.type" @on-click="onClickTab">
        <TabPane
          :label="item.name + '(' + item.count + ')'"
          :name="item.type.toString()"
          v-for="(item, index) in headeNum"
          :key="index"
        />
      </Tabs>
      <Form ref="artFrom" :model="artFrom" :label-width="75" label-position="right" @submit.native.prevent>
        <Row type="flex" :gutter="24">
          <Col v-bind="grid">
            <FormItem label="商品分类：" label-for="pid">
              <Select v-model="artFrom.cate_id" placeholder="请选择商品分类" clearable @on-change="userSearchs">
                <Option v-for="item in treeSelect" :value="item.id" :key="item.id">{{
                  item.html + item.cate_name
                }}</Option>
              </Select>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="商品搜索：" label-for="store_name">
              <Input
                search
                enter-button
                placeholder="请输入商品名称/关键字/ID"
                v-model="artFrom.store_name"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
        </Row>
      </Form>
      <div class="Button">
        <router-link v-auth="['product-product-save']" :to="'/admin/product/add_product'"
          ><Button type="primary" class="bnt mr15" icon="md-add">添加商品</Button></router-link
        >
        <Button v-auth="['product-crawl-save']" type="success" class="bnt mr15" @click="onCopy">商品采集</Button>
        <Dropdown class="bnt mr15" @on-click="batchSelect" :transfer="true">
          <Button type="info">
            批量修改
            <Icon type="ios-arrow-down"></Icon>
          </Button>
          <DropdownMenu slot="list">
            <DropdownItem :name="1">商品分类</DropdownItem>
            <DropdownItem :name="2">物流设置</DropdownItem>
            <DropdownItem :name="3">购买送积分</DropdownItem>
            <DropdownItem :name="4">购买送优惠券</DropdownItem>
            <DropdownItem :name="5">关联用户标签</DropdownItem>
            <DropdownItem :name="6">活动推荐</DropdownItem>
          </DropdownMenu>
        </Dropdown>
        <Button
          v-auth="['product-product-product_show']"
          class="bnt mr15"
          @click="onDismount"
          v-show="artFrom.type === '1'"
          >批量下架</Button
        >
        <Button
          v-auth="['product-product-product_show']"
          class="bnt mr15"
          @click="onShelves"
          v-show="artFrom.type === '2'"
          >批量上架</Button
        >
        <Button v-auth="['export-storeProduct']" class="export" icon="ios-share-outline" @click="exports">导出</Button>
      </div>
      <Table
        ref="table"
        :columns="artFrom.type !== '1' && artFrom.type !== '2' ? columns2 : columns"
        :data="tableList"
        class="ivu-mt"
        :loading="loading"
        highlight-row
        @on-select="handleSelectRow"
        @on-select-cancel="handleCancelRow"
        @on-select-all="handleSelectAll"
        @on-select-all-cancel="handleSelectAll"
        no-data-text="暂无数据"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row }" slot="id">
          {{ row.id }}
        </template>
        <template slot-scope="{ row }" slot="image">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="row.image" />
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="state">
          <i-switch
            v-model="row.is_show"
            :value="row.is_show"
            :true-value="1"
            :false-value="0"
            @on-change="changeSwitch(row)"
            size="large"
          >
            <span slot="open">上架</span>
            <span slot="close">下架</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="look(row)">查看</a>
          <Divider type="vertical" />
          <a @click="edit(row)">编辑</a>
          <Divider type="vertical" />
          <router-link :to="{ path: '/admin/product/product_reply/' + row.id }"><a>查看评论</a></router-link>
          <Divider type="vertical" />
          <a @click="del(row, '恢复商品', index)" v-if="artFrom.type === '6'">恢复商品</a>
          <a @click="del(row, '移入回收站', index)" v-else>移到回收站</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="artFrom.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="artFrom.limit"
        />
      </div>
      <attribute :attrTemplate="attrTemplate" v-on:changeTemplate="changeTemplate"></attribute>
    </Card>
    <!-- 生成淘宝京东表单-->
    <Modal
      v-model="modals"
      class="Box"
      scrollable
      footer-hide
      closable
      title="复制淘宝、天猫、京东、苏宁、1688"
      :mask-closable="false"
      width="1200"
      height="500"
    >
      <tao-bao ref="taobaos" v-if="modals" @on-close="onClose"></tao-bao>
    </Modal>
    <Modal
      v-model="batchModal"
      class="batch-box"
      scrollable
      closable
      title="批量设置"
      :mask-closable="false"
      width="1000"
      @on-ok="batchSub"
      @on-visible-change="clearBatchData"
    >
      <Form
        class="batchFormData"
        ref="batchFormData"
        :rules="ruleBatch"
        :model="batchFormData"
        :label-width="120"
        label-position="right"
        @submit.native.prevent
      >
        <Row :gutter="24" type="flex">
          <Col span="24" v-if="batchType == 1">
            <Divider orientation="left">基础设置</Divider>
            <FormItem label="商品分类：" prop="cate_id">
              <Select v-model="batchFormData.cate_id" placeholder="请选择商品分类" multiple class="perW20">
                <Option v-for="item in treeSelect" :disabled="item.pid === 0" :value="item.id" :key="item.id">{{
                  item.html + item.cate_name
                }}</Option>
              </Select>
            </FormItem>
          </Col>
          <Col span="24" v-if="batchType == 2">
            <Divider orientation="left">物流设置</Divider>
            <FormItem label="物流方式：" prop="logistics">
              <CheckboxGroup v-model="batchFormData.logistics" @on-change="logisticsBtn">
                <Checkbox label="1">快递</Checkbox>

                <Checkbox label="2">到店核销</Checkbox>
              </CheckboxGroup>
            </FormItem>
            <FormItem label="运费设置：">
              <RadioGroup v-model="batchFormData.freight">
                <!-- <Radio :label="1">包邮</Radio> -->
                <Radio :label="2">固定邮费</Radio>
                <Radio :label="3">运费模板</Radio>
              </RadioGroup>
            </FormItem>
            <FormItem label="" v-if="batchFormData.freight == 2">
              <div class="acea-row">
                <InputNumber :min="0" v-model="batchFormData.postage" placeholder="请输入金额" class="perW20 maxW" />
              </div>
            </FormItem>
            <FormItem label="" v-if="batchFormData.freight == 3" prop="temp_id">
              <div class="acea-row">
                <Select v-model="batchFormData.temp_id" clearable placeholder="请选择运费模板" class="perW20 maxW">
                  <Option v-for="(item, index) in templateList" :value="item.id" :key="index">{{ item.name }}</Option>
                </Select>
              </div>
            </FormItem>
          </Col>
          <Col span="24" v-if="[3, 4, 5, 6].includes(batchType)">
            <Divider orientation="left" v-if="[3, 4, 5, 6].includes(batchType)">营销设置</Divider>
            <FormItem label="赠送积分：" prop="give_integral" v-if="batchType == 3">
              <InputNumber v-model="batchFormData.give_integral" :min="0" :max="999999" placeholder="请输入积分" />
            </FormItem>
            <FormItem label="赠送优惠券：" v-if="batchType == 4">
              <div v-if="couponName.length" class="mb20">
                <Tag closable v-for="(item, index) in couponName" :key="index" @on-close="handleClose(item)">{{
                  item.title
                }}</Tag>
              </div>
              <Button type="primary" @click="addCoupon">添加优惠券</Button>
            </FormItem>
            <FormItem label="关联用户标签：" prop="label_id" v-if="batchType == 5">
              <div style="display: flex">
                <div class="labelInput acea-row row-between-wrapper" @click="openLabel">
                  <div style="width: 90%">
                    <div v-if="dataLabel.length">
                      <Tag closable v-for="(item, index) in dataLabel" @on-close="closeLabel(item)" :key="index">{{
                        item.label_name
                      }}</Tag>
                    </div>
                    <span class="span" v-else>选择用户关联标签</span>
                  </div>
                  <div class="iconfont iconxiayi"></div>
                </div>
              </div>
            </FormItem>
            <FormItem label="商品推荐：" v-if="batchType == 6">
              <CheckboxGroup v-model="batchFormData.recommend">
                <Checkbox label="is_hot">热卖单品</Checkbox>
                <Checkbox label="is_benefit">促销单品</Checkbox>
                <Checkbox label="is_best">精品推荐</Checkbox>
                <Checkbox label="is_new">首发新品</Checkbox>
                <Checkbox label="is_good">优品推荐</Checkbox>
              </CheckboxGroup>
            </FormItem>
          </Col>
        </Row>
      </Form>
    </Modal>
    <!-- 用户标签 -->
    <Modal
      v-model="labelShow"
      scrollable
      title="请选择用户标签"
      :closable="false"
      width="500"
      :footer-hide="true"
      :mask-closable="false"
    >
      <userLabel ref="userLabel" @activeData="activeData" @close="labelClose"></userLabel>
    </Modal>
    <!-- 商品弹窗 -->
    <div v-if="isProductBox">
      <div class="bg" @click="isProductBox = false"></div>
      <goodsDetail :goodsId="goodsId"></goodsDetail>
    </div>
    <coupon-list ref="couponTemplates" @nameId="nameId" :couponids="batchFormData.coupon_ids"></coupon-list>
  </div>
</template>

<script>
import expandRow from './tableExpand.vue';
import attribute from './attribute';
import toExcel from '../../../utils/Excel.js';
import { mapState } from 'vuex';
import taoBao from './taoBao';
import goodsDetail from './components/goodsDetail.vue';
import couponList from '@/components/couponList';
import { exportProductList } from '@/api/export';

import {
  getGoodHeade,
  getGoods,
  PostgoodsIsShow,
  treeListApi, // 分类列表
  productShowApi,
  productUnshowApi,
  storeProductApi,
  batchSetting,
  productGetTemplateApi,
} from '@/api/product';
import userLabel from '@/components/labelList';

export default {
  name: 'product_productList',
  components: { expandRow, attribute, taoBao, goodsDetail, userLabel, couponList },
  computed: {
    ...mapState('userLevel', ['categoryId']),
  },
  data() {
    return {
      template: false,
      modals: false,
      batchModal: false,
      labelShow: false,
      batchType: 1, // 批量设置类型
      batchFormData: {
        cate_id: [],
        logistics: [],
        freight: 2,
        postage: 0,
        temp_id: null,
        give_integral: 0,
        label_id: [],
        coupon_ids: [],
        recommend: [],
      },
      ruleBatch: {},
      couponName: [], // 优惠券
      dataLabel: [], // 标签
      templateList: [], // 运费模版
      grid: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 24,
        xs: 24,
      },
      artFrom: {
        page: 1,
        limit: 15,
        cate_id: '',
        type: '1',
        store_name: ''
      },
      list: [],
      tableList: [],
      headeNum: [],
      treeSelect: [],
      loading: false,
      columns: [
        {
          type: 'expand',
          width: 50,
          render: (h, params) => {
            return h(expandRow, {
              props: {
                row: params.row,
              },
            });
          },
        },
        {
          type: 'selection',
          width: 60,
          align: 'center',
        },
        {
          title: '商品ID',
          key: 'id',
          width: 80,
        },
        {
          title: '商品图',
          slot: 'image',
          minWidth: 80,
        },
        {
          title: '商品名称',
          key: 'store_name',
          minWidth: 250,
        },
        {
          title: '商品类型',
          key: 'product_type',
          minWidth: 100,
        },
        {
          title: '商品售价',
          key: 'price',
          minWidth: 90,
        },
        {
          title: '销量',
          key: 'sales',
          minWidth: 90,
        },
        {
          title: '库存',
          key: 'stock',
          minWidth: 80,
        },
        {
          title: '排序',
          key: 'sort',
          minWidth: 70,
        },
        {
          title: '状态',
          slot: 'state',
          width: 100,
          filters: [
            {
              label: '上架',
              value: 1,
            },
            {
              label: '下架',
              value: 0,
            },
          ],
          filterMethod(value, row) {
            return row.is_show === value;
          },
          filterMultiple: false,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 220,
        },
      ],
      data: [],
      total: 0,
      attrTemplate: false,
      selectedIds: new Set(), //选中合并项的id
      ids: [],
      goodsId: '',
      isProductBox: false,
      treeSelect: [],
    };
  },
  watch: {
    $route() {
      if (this.$route.fullPath === '/admin/product/product_list?type=5') {
        this.getPath();
      }
    },
  },
  created() {},
  activated() {
    this.goodHeade();
    this.goodsCategory();
    if (this.$route.fullPath === '/admin/product/product_list?type=5') {
      this.getPath();
    } else {
      this.getDataList();
    }
  },
  methods: {
    batchSub() {
      console.log(this.selectedIds, this.ids);
      let data = this.batchFormData;
      data.ids = this.ids;
      data.type = this.batchType;
      let activeIds = [];
      this.dataLabel.forEach((item) => {
        activeIds.push(item.id);
      });
      data.label_id = activeIds;
      batchSetting(data)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getDataList();
          this.clearBatchData();
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    clearBatchData(status) {
      if (!status) {
        this.batchFormData = {
          cate_id: [],
          logistics: [],
          freight: 0,
          postage: null,
          temp_id: null,
          give_integral: null,
          label_id: [],
          coupon_ids: [],
          recommend: [],
        };
        this.ids = [];
        this.dataLabel = [];
        this.clearAll(false);
      }
    },
    // 批量设置商品
    batchSelect(type) {
      console.log(type);
      if (!this.ids.length) {
        this.$Message.warning('请选择要设置的商品');
      } else {
        this.batchType = type;
        this.batchModal = true;
        this.productGetTemplate();
      }
    },
    activeData(dataLabel) {
      this.labelShow = false;
      this.dataLabel = dataLabel;
    },
    nameId(id, names) {
      this.batchFormData.coupon_ids = id;
      this.couponName = this.unique(names);
    },
    handleClose(name) {
      let index = this.couponName.indexOf(name);
      this.couponName.splice(index, 1);
      this.formValidate.coupon_ids.splice(index, 1);
    },
    //对象数组去重；
    unique(arr) {
      const res = new Map();
      return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
    },
    // 获取运费模板；
    productGetTemplate() {
      productGetTemplateApi().then((res) => {
        this.templateList = res.data;
      });
    },
    // 标签弹窗关闭
    labelClose() {
      this.labelShow = false;
    },
    look(row) {
      this.goodsId = row.id;
      this.isProductBox = true;
    },
    // 物流方式
    logisticsBtn(e) {
      this.batchFormData.logistics = e;
    },
    // 关联用户标签
    openLabel(row) {
      this.labelShow = true;
      this.$refs.userLabel.userLabel(JSON.parse(JSON.stringify(this.dataLabel)));
    },
    closeLabel(label) {
      let index = this.dataLabel.indexOf(this.dataLabel.filter((d) => d.id == label.id)[0]);
      this.dataLabel.splice(index, 1);
    },
    // 添加优惠券
    addCoupon() {
      this.$refs.couponTemplates.isTemplate = true;
      this.$refs.couponTemplates.tableList();
    },
    getPath() {
      this.columns2 = [...this.columns];
      if (name !== '1' && name !== '2') {
        this.columns2.shift({
          type: 'selection',
          width: 60,
          align: 'center',
        });
      }
      this.artFrom.page = 1;
      this.artFrom.type = this.$route.query.type.toString();
      this.getDataList();
    },
    // 导出
    async exports() {
      let [th, filekey, data, fileName] = [[], [], [], ''];
      let excelData = JSON.parse(JSON.stringify(this.artFrom));
      excelData.page = 1;
      excelData.limit = 50;
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
        exportProductList(excelData).then((res) => {
          resolve(res.data);
        });
      });
    },
    changeTemplate(e) {
      // this.template = e;
    },
    freight() {
      this.$refs.template.isTemplate = true;
    },
    // 批量上架
    onShelves() {
      if (this.ids.length === 0) {
        this.$Message.warning('请选择要上架的商品');
      } else {
        let data = {
          ids: this.ids,
        };
        productShowApi(data)
          .then((res) => {
            this.$Message.success(res.msg);
            this.goodHeade();
            this.getDataList();
          })
          .catch((res) => {
            this.$Message.error(res.msg);
          });
      }
    },
    // 批量下架
    onDismount() {
      if (this.ids.length === 0) {
        this.$Message.warning('请选择要下架的商品');
      } else {
        let data = {
          ids: this.ids,
        };
        productUnshowApi(data)
          .then((res) => {
            this.$Message.success(res.msg);
            this.artFrom.page = 1;
            this.goodHeade();
            this.getDataList();
          })
          .catch((res) => {
            this.$Message.error(res.msg);
          });
      }
    },

    // 全选
    // onSelectTab (selection) {
    //     let data = []
    //     selection.map((item) => {
    //         data.push(item.id)
    //     })
    //     this.ids = data
    // },
    //全选和取消全选时触发
    handleSelectAll(selection) {
      if (selection.length === 0) {
        //获取table的数据；
        let data = this.$refs.table.data;
        data.forEach((item) => {
          if (this.selectedIds.has(item.id)) {
            this.selectedIds.delete(item.id);
          }
        });
      } else {
        selection.forEach((item) => {
          this.selectedIds.add(item.id);
        });
      }
      this.$nextTick(() => {
        //确保dom加载完毕
        this.setChecked();
      });
    },
    //  选中某一行
    handleSelectRow(selection, row) {
      this.selectedIds.add(row.id);
      this.$nextTick(() => {
        //确保dom加载完毕
        this.setChecked();
      });
    },
    clearAll(status) {
      this.$refs.table.selectAll(status);
    },
    //  取消某一行
    handleCancelRow(selection, row) {
      this.selectedIds.delete(row.id);
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
        if (this.selectedIds.has(objData[index].id)) {
          objData[index]._isChecked = true;
        }
      }
    },
    // 添加淘宝商品成功
    onClose() {
      this.modals = false;
    },
    // 复制淘宝
    onCopy() {
      this.$router.push({
        path: '/admin/product/add_product',
        query: { type: -1 },
      });
      // this.modals = true
    },
    // tab选择
    onClickTab(name) {
      this.artFrom.type = name;
      this.columns2 = [...this.columns];
      if (name !== '1' && name !== '2') {
        this.columns2.shift({
          type: 'selection',
          width: 60,
          align: 'center',
        });
      }
      this.artFrom.page = 1;
      this.selectedIds.clear();
      this.getDataList();
    },
    // 下拉树
    handleCheckChange(data) {
      let value = '';
      let title = '';
      this.list = [];
      this.artFrom.cate_id = 0;
      data.forEach((item, index) => {
        value += `${item.id},`;
        title += `${item.title},`;
      });
      value = value.substring(0, value.length - 1);
      title = title.substring(0, title.length - 1);
      this.list.push({
        value,
        title,
      });
      this.artFrom.cate_id = value;
      this.getDataList();
    },
    // 获取商品表单头数量
    goodHeade() {
      getGoodHeade()
        .then((res) => {
          this.headeNum = res.data.list;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 商品分类；
    goodsCategory() {
      treeListApi(1)
        .then((res) => {
          this.treeSelect = res.data;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 商品列表；
    getDataList() {
      this.loading = true;
      this.artFrom.cate_id = this.artFrom.cate_id || '';
      getGoods(this.artFrom)
        .then((res) => {
          let data = res.data;
          this.tableList = data.list;
          this.total = res.data.count;
          this.$nextTick(() => {
            //确保dom加载完毕
            this.setChecked();
          });
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(status) {
      this.artFrom.page = status;
      this.getDataList();
    },
    // 表格搜索
    userSearchs() {
      this.artFrom.page = 1;
      this.getDataList();
    },
    // 上下架
    changeSwitch(row) {
      PostgoodsIsShow(row.id, row.is_show)
        .then((res) => {
          this.$Message.success(res.msg);
          this.goodHeade();
          this.getDataList();
        })
        .catch((res) => {
          row.is_show = !row.is_show ? 1 : 0;
          this.$Message.error(res.msg);
        });
    },
    // 数据导出；
    exportData: function () {
      let th = ['商品名称', '商品简介', '商品分类', '价格', '库存', '销量', '收藏人数'];
      let filterVal = ['store_name', 'store_info', 'cate_name', 'price', 'stock', 'sales', 'collect'];
      this.where.page = 'nopage';
      getGoods(this.where).then((res) => {
        let data = res.data.map((v) => filterVal.map((k) => v[k]));
        let fileTime = Date.parse(new Date());
        let [fileName, fileType, sheetName] = ['商户数据_' + fileTime, 'xlsx', '商户数据'];
        toExcel({ th, data, fileName, fileType, sheetName });
      });
    },
    // 属性弹出；
    attrTap() {
      this.attrTemplate = true;
    },
    changeTemplate(msg) {
      this.attrTemplate = msg;
    },
    // 编辑
    edit(row) {
      this.$router.push({ path: '/admin/product/add_product/' + row.id });
    },
    // 确认
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `product/product/${row.id}`,
        method: 'DELETE',
        ids: '',
        un: 1,
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.tableList.splice(num, 1);
          this.goodHeade();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 删除成功
    // submitModel () {
    //     this.tableList.splice(this.delfromData.num, 1);
    //     this.goodHeade();
    // }
  },
};
</script>
<style scoped lang="stylus">
/deep/.ivu-modal-mask {
  z-index: 999 !important;
}

/deep/.ivu-modal-wrap {
  z-index: 999 !important;
}

.Box {
  >>> .ivu-modal-body {
    height: 700px;
    overflow: auto;
  }
}

.batch-box {
  >>> .ivu-modal-body {
    overflow: auto;
    min-height: 350px;
  }
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

.bg {
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 11;
}

/deep/.happy-scroll-content {
  width: 100%;

  .demo-spin-icon-load {
    animation: ani-demo-spin 1s linear infinite;
  }

  @keyframes ani-demo-spin {
    from {
      transform: rotate(0deg);
    }

    50% {
      transform: rotate(180deg);
    }

    to {
      transform: rotate(360deg);
    }
  }

  .demo-spin-col {
    height: 100px;
    position: relative;
    border: 1px solid #eee;
  }
}

.labelInput {
  border: 1px solid #dcdee2;
  width: 20%;
  padding: 0 5px;
  border-radius: 5px;
  min-height: 30px;
  cursor: pointer;

  .span {
    color: #c5c8ce;
  }

  .iconxiayi {
    font-size: 12px;
  }
}
</style>
