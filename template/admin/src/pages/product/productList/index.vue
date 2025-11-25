<template>
  <div class="article-manager">
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form ref="artFrom" :model="artFrom" label-width="80px" label-position="right" inline @submit.native.prevent>
          <div class="acea-row search-form">
            <div class="search-form-box">
              <el-form-item label="商品搜索：" label-for="store_name">
                <el-input
                  clearable
                  placeholder="请输入商品名称/关键字/ID"
                  v-model="artFrom.store_name"
                  class="form_content_width"
                />
              </el-form-item>
              <el-form-item label="商品类型：">
                <el-select v-model="artFrom.virtual_type" clearable placeholder="全部" class="form_content_width">
                  <el-option label="全部" value="" />
                  <el-option label="普通商品" value="0" />
                  <el-option label="卡密商品" value="1" />
                  <el-option label="优惠券商品" value="2" />
                  <el-option label="虚拟商品" value="3" />
                </el-select>
              </el-form-item>
              <el-form-item label="商品分类：" label-for="pid">
                <el-cascader
                  v-model="artFrom.cate_id"
                  size="small"
                  :options="treeSelect"
                  :props="{ multiple: false, emitPath: false, checkStrictly: true }"
                  clearable
                  class="form_content_width"
                ></el-cascader>
              </el-form-item>
              <el-form-item label="配送方式：">
                <el-select v-model="artFrom.logistics" clearable placeholder="全部" class="form_content_width">
                  <el-option label="全部" value="" />
                  <el-option label="快递配送" value="1" />
                  <el-option label="到店自提" value="2" />
                </el-select>
              </el-form-item>
              <template v-if="collapse">
                <el-form-item label="商品标签：" label-for="store_name">
                  <div class="labelInput acea-row row-between-wrapper form_content_width" @click="openStoreLabel">
                    <div style="width: 90%">
                      <div v-if="storeLabelList.length">
                        <el-tag
                          class="mr5"
                          closable
                          v-for="(item, index) in storeLabelList"
                          :key="index"
                          @close="closeStoreLabel(item)"
                          >{{ item.label_name }}</el-tag
                        >
                      </div>
                      <span class="span" v-else>选择商品标签</span>
                    </div>
                    <div class="iconfont iconxiayi"></div>
                  </div>
                </el-form-item>
                <el-form-item label="商品规格：">
                  <el-select v-model="artFrom.spec_type" clearable placeholder="全部" class="form_content_width">
                    <el-option label="全部" value="" />
                    <el-option label="单规格" value="0" />
                    <el-option label="多规格" value="1" />
                  </el-select>
                </el-form-item>
                <el-form-item label="会员专属：">
                  <el-select v-model="artFrom.vip_product" clearable placeholder="全部" class="form_content_width">
                    <el-option label="全部" value="" />
                    <el-option label="否" value="0" />
                    <el-option label="是" value="1" />
                  </el-select>
                </el-form-item>

                <el-form-item label="添加时间：">
                  <el-date-picker
                    class="form_range_content_width"
                    clearable
                    v-model="timeVal"
                    type="daterange"
                    :editable="false"
                    @change="onchangeTime"
                    format="yyyy/MM/dd"
                    value-format="yyyy/MM/dd"
                    start-placeholder="开始日期"
                    end-placeholder="结束日期"
                    :picker-options="pickerOptions"
                    style="width: 250px"
                  ></el-date-picker>
                </el-form-item>
                <el-form-item label="库存：" label-for="store_name">
                  <el-input
                    clearable
                    placeholder="最小值"
                    v-model="artFrom.stock_s[0]"
                    class="form_range_content_width"
                  />
                  ~
                  <el-input
                    clearable
                    placeholder="最大值"
                    v-model="artFrom.stock_s[1]"
                    class="form_range_content_width"
                  />
                </el-form-item>
                <el-form-item label="价格：" label-for="store_name">
                  <el-input
                    clearable
                    placeholder="最小值"
                    v-model="artFrom.price_s[0]"
                    class="form_range_content_width"
                  />
                  ~
                  <el-input
                    clearable
                    placeholder="最大值"
                    v-model="artFrom.price_s[1]"
                    class="form_range_content_width"
                  />
                </el-form-item>
                <el-form-item label="销量：" label-for="store_name">
                  <el-input
                    clearable
                    placeholder="最小值"
                    v-model="artFrom.sales_s[0]"
                    class="form_range_content_width"
                  />
                  ~
                  <el-input
                    clearable
                    placeholder="最大值"
                    v-model="artFrom.sales_s[1]"
                    class="form_range_content_width"
                  />
                </el-form-item>
              </template>
            </div>
            <div class="search-form-sub">
              <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
              <el-button class="ResetSearch" v-db-click @click="reset">重置</el-button>
              <a class="ivu-ml-8 font12 ml10" v-db-click @click="collapse = !collapse">
                <template v-if="!collapse"> 展开 <i class="el-icon-arrow-down" /> </template>
                <template v-else> 收起 <i class="el-icon-arrow-up" /> </template>
              </a>
            </div>
          </div>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16" :body-style="{ padding: '0 20px 20px' }">
      <el-tabs v-model="artFrom.type" @tab-click="onClickTab">
        <el-tab-pane
          :label="item.name + '(' + item.count + ')'"
          :name="item.type.toString()"
          v-for="(item, index) in headeNum"
          :key="index"
        />
      </el-tabs>
      <div class="Button">
        <router-link v-auth="['product-product-save']" :to="$routeProStr + '/product/add_product'"
          ><el-button type="primary" class="mr14">添加商品</el-button></router-link
        >
        <el-button v-auth="['product-crawl-save']" type="success" class="mr14" v-db-click @click="onCopy"
          >商品采集</el-button
        >
        <el-dropdown class="bnt mr14" @command="batchSelect">
          <el-button>批量修改<i class="el-icon-arrow-down el-icon--right"></i></el-button>
          <el-dropdown-menu slot="dropdown">
            <el-dropdown-item :command="1">商品分类</el-dropdown-item>
            <el-dropdown-item :command="2">物流设置</el-dropdown-item>
            <el-dropdown-item :command="3">购买送积分</el-dropdown-item>
            <el-dropdown-item :command="4">购买送优惠券</el-dropdown-item>
            <el-dropdown-item :command="5">关联用户标签</el-dropdown-item>
            <el-dropdown-item :command="6">活动推荐</el-dropdown-item>
            <el-dropdown-item v-auth="['product-product-product_show']" v-if="artFrom.type === '1'" :command="7"
              >批量下架</el-dropdown-item
            >
            <el-dropdown-item v-auth="['product-product-product_show']" v-if="artFrom.type === '2'" :command="8"
              >批量上架</el-dropdown-item
            >
            <el-dropdown-item v-auth="['product-product-product_show']" :command="9">设置商品标签</el-dropdown-item>
            <el-dropdown-item v-auth="['product-product-product_show']" v-if="artFrom.type !== '6'" :command="11"
              >移到回收站</el-dropdown-item
            >
            <el-dropdown-item v-auth="['product-product-product_show']" v-if="artFrom.type == '6'" :command="12"
              >恢复商品</el-dropdown-item
            >
          </el-dropdown-menu>
        </el-dropdown>
        <el-dropdown class="bnt mr14" @command="goodsMove">
          <el-button>商品迁移<i class="el-icon-arrow-down el-icon--right"></i></el-button>
          <el-dropdown-menu slot="dropdown">
            <el-dropdown-item :command="1">商品导入</el-dropdown-item>
            <el-dropdown-item :command="2">商品导出</el-dropdown-item>
          </el-dropdown-menu>
        </el-dropdown>
        <el-button v-auth="['export-storeProduct']" class="export" v-db-click @click="onExports(0)">数据导出</el-button>
      </div>
      <el-table
        ref="table"
        :data="tableList"
        class="ivu-mt mt14"
        v-loading="loading"
        highlight-current-row
        :row-key="getRowKey"
        @selection-change="handleSelectRow"
        empty-text="暂无数据"
      >
        <el-table-column type="expand" width="50" v-if="['1', '2'].includes(artFrom.type)">
          <template slot-scope="scope">
            <expandRow :row="scope.row"></expandRow>
          </template>
        </el-table-column>
        <el-table-column type="selection" width="60" :reserve-selection="true"> </el-table-column>
        <el-table-column label="商品ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品图" min-width="90">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="商品名称" min-width="250">
          <template slot-scope="scope">
            <span>{{ scope.row.store_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="参与活动" width="90">
          <template slot-scope="scope">
            <el-tag
              class="mb5 cup"
              v-if="scope.row.activityExist.bargain"
              type=""
              @click="activityDetail(scope.row, 0)"
              effect="dark"
            >
              砍价
            </el-tag>
            <el-tag
              class="mb5 cup"
              v-if="scope.row.activityExist.combination"
              type="success"
              @click="activityDetail(scope.row, 1)"
              effect="dark"
            >
              拼团
            </el-tag>
            <el-tag
              class="mb5 cup"
              v-if="scope.row.activityExist.seckill"
              type="warning"
              @click="activityDetail(scope.row, 2)"
              effect="dark"
            >
              秒杀
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="商品类型" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.product_type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品售价" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="销量" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.sales }}</span>
          </template>
        </el-table-column>
        <el-table-column label="库存" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.stock }}</span>
          </template>
        </el-table-column>
        <el-table-column label="排序" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.sort }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="100">
          <template slot-scope="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_show"
              :value="scope.row.is_show"
              :disabled="scope.row.stop_status ? true : false"
              @change="changeSwitch(scope.row)"
              size="large"
              active-text="上架"
              inactive-text="下架"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" minWidth="100">
          <template slot-scope="scope">
            <!-- <a v-db-click @click="look(scope.row)">查看</a>
            <el-divider direction="vertical"></el-divider> -->
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <el-dropdown size="small">
              <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item>
                  <router-link :to="{ path: $routeProStr + '/product/product_reply/' + scope.row.id }"
                    ><a>查看评论</a></router-link
                  >
                </el-dropdown-item>
                <el-dropdown-item v-db-click @click.native="openModal(scope.row, 'vipPriceSet')"
                  >会员价管理</el-dropdown-item
                >
                <el-dropdown-item v-db-click @click.native="openModal(scope.row, 'brokerageSet')"
                  >佣金管理</el-dropdown-item
                >
                <el-dropdown-item
                  v-if="artFrom.type === '6'"
                  v-db-click
                  @click.native="del(scope.row, '恢复商品', scope.$index)"
                  >恢复商品</el-dropdown-item
                >
                <el-dropdown-item
                  v-if="artFrom.type === '6'"
                  v-db-click
                  @click.native="fullDel(scope.row, '彻底删除', scope.$index)"
                  >彻底删除</el-dropdown-item
                >
                <el-dropdown-item v-else v-db-click @click.native="del(scope.row, '移入回收站', scope.$index)"
                  >移到回收站</el-dropdown-item
                >
              </el-dropdown-menu>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="artFrom.page"
          :limit.sync="artFrom.limit"
          @pagination="getDataList"
        />
      </div>
      <attribute :attrTemplate="attrTemplate" v-on:changeTemplate="changeTemplate"></attribute>
    </el-card>
    <!-- 生成淘宝京东表单-->
    <el-dialog
      :visible.sync="modals"
      class="Box"
      title="复制淘宝、天猫、京东、苏宁、1688"
      :close-on-click-modal="false"
      width="720px"
    >
      <tao-bao ref="taobaos" v-if="modals" @on-close="onClose"></tao-bao>
    </el-dialog>
    <el-dialog
      :visible.sync="batchModal"
      class="batch-box"
      title="批量设置"
      :show-close="true"
      :close-on-click-modal="false"
      width="540px"
    >
      <el-form
        class="batchFormData"
        ref="batchFormData"
        :rules="ruleBatch"
        :model="batchFormData"
        label-width="90px"
        label-position="right"
        @submit.native.prevent
      >
        <el-row :gutter="24">
          <el-col :span="24" v-if="batchType == 1">
            <!--            <el-divider content-position="left">基础设置</el-divider>-->
            <el-form-item label="商品分类：" prop="cate_id">
              <!-- <el-select v-model="batchFormData.cate_id" placeholder="请选择商品分类" multiple class="perW20">
                <el-option v-for="item in treeSelect" :disabled="item.pid === 0" :value="item.id" :key="item.id">{{
                  item.html + item.cate_name
                }}</el-option>
              </el-select> -->
              <el-cascader
                v-model="batchFormData.cate_id"
                size="small"
                :options="treeSelect"
                :props="{ multiple: true, emitPath: false, checkStrictly: true }"
                clearable
                style="width: 400px"
              ></el-cascader>
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="batchType == 2">
            <el-form-item label="物流方式：" prop="logistics">
              <el-checkbox-group v-model="batchFormData.logistics" @change="logisticsBtn">
                <el-checkbox label="1">快递</el-checkbox>
                <el-checkbox label="2">到店</el-checkbox>
              </el-checkbox-group>
            </el-form-item>
            <el-form-item label="运费设置：">
              <el-radio-group v-model="batchFormData.freight">
                <!-- <el-radio :label="1">包邮</el-radio> -->
                <el-radio :label="2">固定邮费</el-radio>
                <el-radio :label="3">运费模板</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="" v-if="batchFormData.freight == 2">
              <div class="acea-row">
                <el-input-number
                  :controls="false"
                  :min="0"
                  v-model="batchFormData.postage"
                  placeholder="请输入金额"
                  class="perW20 maxW"
                />
              </div>
            </el-form-item>
            <el-form-item label="" v-if="batchFormData.freight == 3" prop="temp_id">
              <div class="acea-row">
                <el-select v-model="batchFormData.temp_id" clearable placeholder="请选择运费模板" style="width: 414px">
                  <el-option
                    v-for="(item, index) in templateList"
                    :value="item.id"
                    :key="index"
                    :label="item.name"
                  ></el-option>
                </el-select>
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="[3, 4, 5, 6].includes(batchType)">
            <!--            <el-divider content-position="left" v-if="[3, 4, 5, 6].includes(batchType)">营销设置</el-divider>-->
            <el-form-item label="赠送积分：" prop="give_integral" v-if="batchType == 3">
              <el-input-number
                :controls="false"
                v-model="batchFormData.give_integral"
                :min="0"
                :max="9999999999"
                placeholder="请输入积分"
                style="width: 100%"
              />
            </el-form-item>
            <el-form-item label="赠送优惠券：" v-if="batchType == 4">
              <div v-if="couponName.length" class="mb20">
                <el-tag closable v-for="(item, index) in couponName" :key="index" @close="handleClose(item)">{{
                  item.title
                }}</el-tag>
              </div>
              <el-button type="primary" v-db-click @click="addCoupon">添加优惠券</el-button>
            </el-form-item>
            <el-form-item label="关联标签：" prop="label_id" v-if="batchType == 5">
              <div class="acea-row label_width">
                <div class="labelInput acea-row row-between-wrapper" v-db-click @click="openLabel">
                  <div style="width: auto">
                    <div v-if="dataLabel.length">
                      <el-tag
                        class="m-r-2"
                        closable
                        v-for="(item, index) in dataLabel"
                        @close="closeLabel(item)"
                        :key="index"
                        >{{ item.label_name }}</el-tag
                      >
                    </div>
                    <span class="span" v-else>选择用户关联标签</span>
                  </div>
                  <div class="iconfont iconxiayi"></div>
                </div>
              </div>
            </el-form-item>
            <el-form-item label="商品推荐：" v-if="batchType == 6">
              <el-checkbox-group v-model="batchFormData.recommend">
                <el-checkbox label="is_hot">热卖单品</el-checkbox>
                <!-- <el-checkbox label="is_benefit">促销单品</el-checkbox> -->
                <el-checkbox label="is_best">精品推荐</el-checkbox>
                <el-checkbox label="is_new">首发新品</el-checkbox>
                <el-checkbox label="is_good">优品推荐</el-checkbox>
              </el-checkbox-group>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button v-db-click @click="clearBatchData">取 消</el-button>
        <el-button type="primary" v-db-click @click="batchSub">确 定</el-button>
      </span>
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
        :defaultLabelList="goodsLabelList"
        @activeLabel="activeGoodsLabel"
        @close="labelClose"
      ></goodsLabel>
    </el-dialog>
    <!-- 用户标签 -->
    <el-dialog
      :visible.sync="labelShow"
      title="请选择用户标签"
      width="540px"
      :show-close="true"
      :close-on-click-modal="false"
    >
      <userLabel ref="userLabel" @activeData="activeData" @close="labelClose"></userLabel>
    </el-dialog>
    <!-- 商品弹窗 -->
    <div v-if="isProductBox">
      <div class="bg" v-db-click @click="isProductBox = false"></div>
      <goodsDetail :goodsId="goodsId"></goodsDetail>
    </div>
    <coupon-list ref="couponTemplates" @nameId="nameId" :couponids="batchFormData.coupon_ids"></coupon-list>
    <!-- 商品导入 -->
    <el-dialog
      :visible.sync="importShow"
      title="商品导入"
      width="900px"
      :show-close="true"
      :close-on-click-modal="false"
    >
      <goodsImport v-if="importShow" @close="importShow = false"></goodsImport>
    </el-dialog>
    <brokerageSet ref="brokerageSet" :productId="productId"></brokerageSet>
    <vipPriceSet ref="vipPriceSet" :productId="productId"></vipPriceSet>
    <!-- 商品标签 -->
    <el-dialog :visible.sync="storeLabelShow" title="选择商品标签" width="540">
      <storeLabelList
        v-if="storeLabelShow"
        ref="storeLabel"
        @activeData="activeStoreData"
        @close="storeLabelClose"
      ></storeLabelList>
    </el-dialog>
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
import { exportProductList, exportProductExport } from '@/api/export';
import settings from '@/setting';
import goodsImport from './components/goodsImport.vue';
import brokerageSet from '../components/brokerageSet.vue';
import vipPriceSet from '../components/vipPriceSet.vue';
import {
  getGoodHeade,
  getGoods,
  PostgoodsIsShow,
  cascaderListApi, // 分类列表
  productShowApi,
  productUnshowApi,
  storeProductApi,
  batchSetting,
  productGetTemplateApi,
  productLabelUseListApi,
  productBatchDelete,
} from '@/api/product';
import userLabel from '@/components/labelList';
import storeLabelList from '@/components/storeLabelList';
import goodsLabel from '@/components/goodsLabel';

export default {
  name: 'product_productList',
  components: {
    expandRow,
    attribute,
    taoBao,
    goodsDetail,
    userLabel,
    couponList,
    goodsImport,
    brokerageSet,
    vipPriceSet,
    storeLabelList,
    goodsLabel,
  },
  computed: {
    ...mapState('userLevel', ['categoryId']),
  },
  data() {
    return {
      routePre: settings.routePre,
      pickerOptions: this.$timeOptions,
      template: false,
      modals: false,
      importShow: false,
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
        store_name: '',
        spec_type: '',
        logistics: '',
        vip_product: '',
        is_gift: '',
        sales_s: ['', ''],
        stock_s: ['', ''],
        price_s: ['', ''],
        store_label_id: [],
        time: '',
        virtual_type: '',
      },
      list: [],
      tableList: [],
      headeNum: [],
      loading: false,
      data: [],
      total: 0,
      attrTemplate: false,
      ids: [],
      goodsId: '',
      isProductBox: false,
      treeSelect: [],
      multipleSelection: [],
      showBrokerage: false,
      showVipPrice: false,
      storeLabelShow: false,
      tagShow: false,
      productId: 0,
      storeLabelList: [],
      goodsLabelList: [],
      timeVal: [],
      collapse: false,
    };
  },
  watch: {
    $route() {
      if (this.$route.fullPath === this.$routeProStr + '/product/product_list?type=5') {
        this.getPath();
      }
    },
  },
  created() {},
  activated() {
    this.goodHeade();
    this.goodsCategory();
    this.getLabelList();
    if (this.$route.fullPath === this.$routeProStr + '/product/product_list?type=5') {
      this.getPath();
    } else {
      this.getDataList();
    }
  },
  methods: {
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.artFrom.time = this.timeVal ? this.timeVal.join('-') : '';
      this.artFrom.page = 1;
      this.getDataList();
    },
    // 标签弹窗关闭
    storeLabelClose() {
      this.storeLabelShow = false;
    },
    getLabelList() {
      productLabelUseListApi()
        .then((res) => {
          res.data.map((el) => {
            if (el.list && el.list.length) {
              el.list.map((label) => {
                label.active = false;
              });
            }
          });
          this.goodsLabelList = res.data;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    openStoreLabel(row) {
      this.storeLabelShow = true;
      this.$nextTick((e) => {
        this.$refs.storeLabel.storeLabel(JSON.parse(JSON.stringify(this.storeLabelList)));
      });
    },
    closeStoreLabel(label) {
      let index = this.storeLabelList.indexOf(this.storeLabelList.filter((d) => d.id == label.id)[0]);
      this.storeLabelList.splice(index, 1);
      this.getLabelId(this.storeLabelList);
    },
    activeStoreData(storeDataLabel) {
      this.storeLabelShow = false;
      this.storeLabelList = storeDataLabel;
      this.getLabelId(storeDataLabel);
    },
    getLabelId(storeDataLabel) {
      let storeActiveIds = [];
      storeDataLabel.forEach((item) => {
        storeActiveIds.push(item.id);
      });
      this.artFrom.store_label_id = storeActiveIds;
      this.artFrom.page = 1;
      this.getDataList();
    },
    activityDetail(row, type) {
      let name = '';
      if (type === 0) {
        name = 'marketing_storeBargain';
      } else if (type === 1) {
        name = 'marketing_combinalist';
      } else if (type === 2) {
        name = 'marketing_storeSeckill';
      }
      this.$router.push({
        name,
        params: {
          product_id: row.id,
        },
      });
    },
    openModal(row, type) {
      this.productId = row.id;
      this.$refs[type].visible = true;
    },
    batchSub() {
      let data = this.batchFormData;
      data.ids = this.ids;
      data.type = this.batchType;
      let activeIds = [];
      this.dataLabel.forEach((item) => {
        activeIds.push(item.id);
      });
      data.label_id = activeIds;
      if (this.batchType == 2 && !this.batchFormData.logistics.length) {
        return this.$message.warning('请选择物流方式');
      }
      batchSetting(data)
        .then((res) => {
          this.$message.success(res.msg);
          this.getDataList();
          this.clearBatchData(false);
          this.ids = [];
        })
        .catch((err) => {
          this.$message.error(err.msg);
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
          is_gift: null,
          label_list: [],
        };
        this.dataLabel = [];
      }
      this.batchModal = false;
      this.$refs.table.clearSelection();
    },
    // 批量设置商品
    batchSelect(type) {
      if (!this.ids.length) {
        this.$message.warning('请选择要修改的商品');
      } else if (type === 7) {
        this.onDismount();
      } else if (type === 8) {
        this.onShelves();
      } else if (type === 9) {
        this.batchType = type;
        this.tagShow = true;
      } else if (type === 11) {
        this.batchGoodsSetting('全部移到回收站', 1);
      } else if (type === 12) {
        this.batchGoodsSetting('恢复选中商品', 2);
      } else {
        this.batchType = type;
        this.batchModal = true;
        this.productGetTemplate();
      }
    },
    batchGoodsSetting(tit, type) {
      let url = type == 1 ? 'product/product/batch_delete' : 'product/product/batch_recover';
      let delfromData = {
        title: tit,
        url,
        method: 'post',
        ids: {
          ids: this.ids,
        },
        un: 1,
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.goodHeade();
          this.getDataList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    goodsMove(type) {
      if (type === 1) {
        this.onImport();
      } else {
        this.onExports(2);
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
      this.tagShow = false;
    },
    // 选择商品标签
    activeGoodsLabel(data) {
      console.log(data, 'data');
      this.tagShow = false;
      this.batchFormData.label_list = Array.from(new Set(data));
      this.batchSub();
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
    openLabel() {
      this.labelShow = true;
      // this.$refs.userLabel.setLabel(JSON.parse(JSON.stringify(this.dataLabel)));
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
      this.artFrom.page = 1;
      this.artFrom.type = this.$route.query.type.toString();
      this.getDataList();
    },
    onImport() {
      this.importShow = true;
    },
    // 导出
    async onExports(type) {
      let [th, filekey, data, fileName] = [[], [], [], ''];
      let excelData = JSON.parse(JSON.stringify(this.artFrom));
      excelData.page = 1;
      excelData.limit = 50;
      excelData.ids = this.ids;
      for (let i = 0; i < excelData.page + 1; i++) {
        let lebData = await this.getExcelData(excelData, type);
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
    getExcelData(excelData, type) {
      let fun = type ? exportProductExport : exportProductList;
      return new Promise((resolve, reject) => {
        fun(excelData).then((res) => {
          resolve(res.data);
        });
      });
    },
    freight() {
      this.$refs.template.isTemplate = true;
    },
    // 批量上架
    onShelves() {
      if (this.ids.length === 0) {
        this.$message.warning('请选择要上架的商品');
      } else {
        let data = {
          ids: this.ids,
        };
        productShowApi(data)
          .then((res) => {
            this.$message.success(res.msg);
            this.goodHeade();
            this.getDataList();
          })
          .catch((res) => {
            this.$message.error(res.msg);
          });
      }
    },
    // 批量下架
    onDismount() {
      if (this.ids.length === 0) {
        this.$message.warning('请选择要下架的商品');
      } else {
        let data = {
          ids: this.ids,
        };
        productUnshowApi(data)
          .then((res) => {
            this.$message.success(res.msg);
            this.artFrom.page = 1;
            this.goodHeade();
            this.getDataList();
          })
          .catch((res) => {
            this.$message.error(res.msg);
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
    getRowKey(row) {
      return row.id;
    },
    //  选中某一行
    handleSelectRow(selection) {
      const uniqueArr = [];
      const ids = [];
      for (let i = 0; i < selection.length; i++) {
        const item = selection[i];
        if (!ids.includes(item.id)) {
          uniqueArr.push(item);
          ids.push(item.id);
        }
      }
      this.ids = ids;
      this.multipleSelection = uniqueArr;
    },
    // 添加淘宝商品成功
    onClose() {
      this.modals = false;
    },
    // 复制淘宝
    onCopy() {
      this.$router.push({
        path: this.$routeProStr + '/product/add_product',
        query: { type: -1 },
      });
      // this.modals = true
    },
    // tab选择
    onClickTab() {
      this.artFrom.page = 1;
      this.multipleSelection = [];
      this.$refs.table.clearSelection();
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
      getGoodHeade(this.artFrom)
        .then((res) => {
          this.headeNum = res.data.list;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
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
            // this.setChecked();
            this.showSelectData();
          });
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    showSelectData() {
      if (this.multipleSelection.length > 0) {
        // 判断是否存在勾选过的数据
        this.tableList.forEach((row) => {
          // 获取数据列表接口请求到的数据
          this.multipleSelection.forEach((item) => {
            // 勾选到的数据
            if (row.id === item.id) {
              this.$refs.table.toggleRowSelection(item, true); // 若有重合，则回显该条数据
            }
          });
        });
      }
    },
    // 表格搜索
    userSearchs() {
      this.artFrom.page = 1;
      this.goodHeade();
      this.getDataList();
    },
    // 上下架
    changeSwitch(row) {
      PostgoodsIsShow(row.id, row.is_show)
        .then((res) => {
          this.$message.success(res.msg);
          this.goodHeade();
          this.getDataList();
        })
        .catch((res) => {
          row.is_show = !row.is_show ? 1 : 0;
          this.$message.error(res.msg);
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
      this.$router.push({ path: this.$routeProStr + '/product/add_product/' + row.id });
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
          this.$message.success(res.msg);
          this.tableList.splice(num, 1);
          this.goodHeade();
          this.getDataList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    fullDel(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `product/full_del/${row.id}`,
        method: 'DELETE',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.tableList.splice(num, 1);
          this.goodHeade();
          this.getDataList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 重置
    reset(name) {
      this.artFrom = {
        page: 1,
        limit: 15,
        cate_id: '',
        type: '1',
        store_name: '',
        spec_type: '',
        logistics: '',
        vip_product: '',
        is_gift: '',
        sales_s: ['', ''],
        stock_s: ['', ''],
        price_s: ['', ''],
        store_label_id: [],
        time: '',
        virtual_type: '',
      };
      this.storeLabelList = [];
      this.tableList = [];
      this.total = 0;
      this.timeVal = [];
      this.getDataList();
    },
  },
};
</script>
<style scoped lang="scss">
::v-deep .el-tabs__item {
  height: 54px !important;
  line-height: 54px !important;
}
::v-deep .ivu-modal-mask {
  z-index: 999 !important;
}

::v-deep .ivu-modal-wrap {
  z-index: 999 !important;
}

.Box {
  ::v-deep .ivu-modal-body {
    height: 700px;
    overflow: auto;
  }
}

.batch-box {
  ::v-deep .ivu-modal-body {
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

::v-deep .happy-scroll-content {
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
  padding: 0 15px 0 10px;
  border-radius: 5px;
  min-height: 30px;
  cursor: pointer;
  font-size: 12px;

  .span {
    color: #c5c8ce;
  }

  .iconxiayi {
    margin-left: 5px;
    font-size: 12px;
  }
}
.el-dropdown-link {
  cursor: pointer;
  color: var(--prev-color-primary);
  font-size: 12px;
}
.el-icon-arrow-down {
  font-size: 12px;
}
.el-dropdown-menu__item {
  a {
    color: #606266;
  }
}
.label_width {
  width: 400px;
}
.search-form {
  display: flex;
  justify-content: space-between;
  .search-form-box {
    display: flex;
    flex-wrap: wrap;
    flex: 1;
  }
  .search-form-sub {
    display: flex;
    align-items: baseline;
  }
}
</style>
