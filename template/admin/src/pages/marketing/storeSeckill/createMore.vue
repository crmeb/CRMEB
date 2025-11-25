<template>
  <div v-loading="spinShow">
    <pages-header
      ref="pageHeader"
      :title="$route.params.id ? '编辑秒杀商品' : '添加秒杀商品'"
      :backUrl="$routeProStr + '/marketing/store_seckill/list'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-row class="mt30 acea-row row-middle row-center">
        <el-col :span="20">
          <steps :stepList="stepList" :isActive="current" @stepActive="stepActive"></steps>
        </el-col>
        <el-col :span="23">
          <el-form
            class="form mt30"
            ref="formValidate"
            :model="formValidate"
            :label-width="labelWidth"
            :label-position="labelPosition"
            @submit.native.prevent
          >
            <el-col v-show="current === 0">
              <el-col :span="24">
                <el-col v-bind="grid">
                  <el-form-item label="活动标题：" label-for="title">
                    <el-input
                      clearable
                      placeholder="请输入活动标题"
                      v-model="formValidate.title"
                      class="content_width"
                      maxlength="80"
                      show-word-limit
                    />
                  </el-form-item>
                </el-col>
              </el-col>

              <el-col :span="24">
                <el-form-item label="活动时间：">
                  <div>
                    <el-date-picker
                      clearable
                      :editable="false"
                      type="daterange"
                      format="yyyy-MM-dd"
                      value-format="yyyy-MM-dd"
                      range-separator="-"
                      start-placeholder="开始日期"
                      end-placeholder="结束日期"
                      @change="onchangeTime"
                      class="content_width"
                      v-model="formValidate.section_time"
                    ></el-date-picker>
                    <div class="grey">设置活动开启结束时间，用户可以在有效时间内参与秒杀</div>
                  </div>
                </el-form-item>
              </el-col>

              <el-col :span="24">
                <el-form-item label="开始时间：">
                  <div>
                    <el-select v-model="formValidate.time_ids" multiple class="content_width">
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
                <el-form-item label="总购买数量限制：">
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
                <el-form-item label="单次购买数量限制：">
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
                <el-form-item label="秒杀是否参与分销：" props="is_commission" label-for="is_commission">
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
            </el-col>
            <el-row v-show="current === 1">
              <el-col :span="24">
                <div class="acea-row row-between-wrapper">
                  <div>
                    <el-button type="primary" @click="addGoods">添加商品</el-button>
                    <el-button @click="batchSet" class="ml20" :disabled="!isAllChecked && !checkPidList.length"
                      >批量设置</el-button
                    >
                    <el-button @click="delAll" class="ml20" :disabled="!isAllChecked && !checkPidList.length"
                      >批量删除</el-button
                    >
                  </div>
                  <div class="goodsWord">
                    <el-form-item label="商品搜索：">
                      <el-input
                        class="w_input240"
                        v-model="keyword"
                        placeholder="请输入商品关键词"
                        @input="searchWord"
                      ></el-input>
                    </el-form-item>
                  </div>
                </div>
              </el-col>
              <el-col :span="24">
                <div class="vxeTable">
                  <vxe-table
                    border="inner"
                    ref="xTree"
                    :column-config="{ resizable: true }"
                    row-id="id"
                    :tree-config="{ children: 'attrs', reserve: true }"
                    @checkbox-all="checkboxAll"
                    @checkbox-change="checkboxItem"
                    :data="searchTableData.length || keyword ? searchTableData : tableData"
                  >
                    <vxe-column type="checkbox" title="多选" width="100" tree-node></vxe-column>
                    <vxe-column field="info" title="商品信息" min-width="300">
                      <template v-slot="{ row }">
                        <div class="flex imgPic row-middle">
                          <viewer>
                            <div class="pictrue"><img v-lazy="row.parent == 1 ? row.image : row.pic" /></div>
                          </viewer>
                          <div class="info">
                            <el-tooltip max-width="200" placement="bottom" transfer>
                              <span class="line2">{{ row.store_name }}{{ row.suk }}</span>
                              <p slot="content">{{ row.store_name }}{{ row.suk }}</p>
                            </el-tooltip>
                          </div>
                        </div>
                      </template>
                    </vxe-column>
                    <vxe-column field="cost" title="成本价" min-width="80"></vxe-column>
                    <vxe-column field="product_price" title="售价" min-width="80"></vxe-column>
                    <vxe-column field="price" title="秒杀价" min-width="150">
                      <template v-slot="{ row }">
                        <div v-if="row.parent == 1">——</div>
                        <vxe-input
                          v-else
                          v-model="row.price"
                          min="0"
                          placeholder="请输入秒杀价"
                          type="float"
                          digits="2"
                          step="1"
                        ></vxe-input>
                      </template>
                    </vxe-column>
                    <vxe-column field="quota" title="限量" min-width="150">
                      <template v-slot="{ row }">
                        <div v-if="row.parent == 1">——</div>
                        <vxe-input
                          v-else
                          v-model="row.quota"
                          min="0"
                          placeholder="请输入限量"
                          type="integer"
                        ></vxe-input>
                      </template>
                    </vxe-column>
                    <vxe-column field="stock" title="库存" min-width="90"></vxe-column>
                    <vxe-column field="status" title="是否开启" min-width="100">
                      <template v-slot="{ row }">
                        <el-switch v-model="row.status" :active-value="1" :inactive-value="0" size="large">
                          <span slot="open">上架</span>
                          <span slot="close">下架</span>
                        </el-switch>
                      </template>
                    </vxe-column>
                    <vxe-column field="date" title="操作" min-width="100" fixed="right" align="center">
                      <template v-slot="{ row }">
                        <a @click="del(row, $event)" v-if="row.parent == 1">删除</a>
                      </template>
                    </vxe-column>
                  </vxe-table>
                </div>
              </el-col>
            </el-row>
            <el-col class="mt20" :span="24">
              <el-form-item>
                <el-button class="submission" v-db-click @click="step" :disabled="current === 0">上一步 </el-button>
                <el-button
                  :disabled="submitOpen && current === 1"
                  type="primary"
                  class="submission"
                  v-db-click
                  @click="next('formValidate')"
                  >{{ current === 1 ? '提交' : '下一步' }}</el-button
                >
              </el-form-item>
            </el-col>
          </el-form>
        </el-col>
      </el-row>
    </el-card>
    <!-- 选择商品-->
    <el-dialog :visible.sync="modals" title="商品列表" class="paymentFooter" width="1000px">
      <goods-list ref="goodslist" :ischeckbox="true" isdiy :goodsType="1" @getProductId="getProductId"></goods-list>
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
    <el-dialog :visible.sync="modalsSet" title="批量设置" @close="batchVisibleChange">
      <el-form
        ref="formBatch"
        :model="formBatch"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <el-form-item label="秒杀价：" prop="price">
          <el-input
            class="w_input315"
            v-model="formBatch.price"
            min="0"
            placeholder="请输入秒杀价"
            type="float"
            digits="2"
            step="1"
          ></el-input>
        </el-form-item>
        <el-form-item label="限量：" prop="quota">
          <el-input
            class="w_input315"
            v-model="formBatch.quota"
            min="0"
            placeholder="请输入限量"
            type="integer"
          ></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer">
        <el-button @click="modalsSet = false">取消</el-button>
        <el-button type="primary" @click="okBatch">保存</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import goodsList from '@/components/goodsList/index';
import WangEditor from '@/components/wangEditor/index.vue';
import uploadPictures from '@/components/uploadPictures';
import { seckillActivityInfoApi, seckillActivityAddApi, seckillTimeListApi } from '@/api/marketing';
import { productGetTemplateApi } from '@/api/product';
import freightTemplate from '@/components/freightTemplate/index';
import steps from '@/components/steps/index';

export default {
  name: 'storeSeckillCreate',
  components: {
    goodsList,
    uploadPictures,
    WangEditor,
    freightTemplate,
    steps,
  },
  data() {
    return {
      stepList: ['填写基础信息', '选择秒杀商品'],
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
        lg: 12,
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
        UEDITOR_HOME_URL: '/UEditor/',
        serverUrl: '',
      },
      modals: false,
      modal_loading: false,
      images: [],
      formValidate: {
        title: '',
        section_time: [],
        time_ids: [],
        num: 0,
        once_num: 0,
        status: 1,
        product_infos: [],
      },
      formBatch: {
        price: '',
        quota: '',
      },
      templateList: [],
      timeList: [],
      columns: [],
      specsData: [],
      picTit: '',
      tableIndex: 0,
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
        title: [{ required: true, message: '请输入商品标题', trigger: 'blur' }],
        info: [{ required: true, message: '请输入秒杀活动简介', trigger: 'blur' }],
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
            message: '请输入秒杀价',
            trigger: 'blur',
          },
        ],
        ot_price: [
          {
            required: true,
            type: 'number',
            message: '请输入原价',
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
        temp_id: [
          {
            required: true,
            message: '请选择运费模板',
            trigger: 'change',
            type: 'number',
          },
        ],
        time_ids: [
          {
            required: true,
            message: '请选择开始时间',
            trigger: 'change',
            type: 'Array',
          },
        ],
      },
      copy: 0,
      modalsSet: false,
      isAllChecked: false,
      checkPidList: [], //父级有关id集合 （需求禁止删除子级，用于删除整个商品）
      searchTableData: [],
      tableData: [],
      keyword: '',
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '135px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  mounted() {
    if (this.$route.params.id) {
      this.current = 0;
      this.getInfo();
    }
    this.productGetTemplate();
    this.seckillTimeList();
  },
  methods: {
    stepActive(index){
      this.current = index;
    },
    addGoods() {
      this.modals = true;
    },
    //批量设置
    batchSet() {
      this.modalsSet = true;
    },
    //删除
    del(row) {
      // this.tableData = this.tableData.filter((item) => item.id !== row.id);
      if (this.searchTableData.length) {
        this.searchTableData.forEach((i, index) => {
          if (row.id == i.id) {
            this.searchTableData.splice(index, 1);
          }
        });
        this.tableData.forEach((i, index) => {
          if (row.id == i.id) {
            return this.tableData.splice(index, 1);
          }
        });
      } else {
        this.tableData.forEach((i, index) => {
          if (row.id == i.id) {
            return this.tableData.splice(index, 1);
          }
        });
      }
      if (this.isAllChecked && !this.tableData.length) {
        this.isAllChecked = false;
        this.checkPidList = [];
      } else {
        let index = this.checkPidList.indexOf(row.id);
        this.checkPidList.splice(index, 1);
      }
    },
    //批量删除
    delAll() {
      if (this.isAllChecked && (this.tableData.length == this.searchTableData.length || !this.searchTableData.length)) {
        this.tableData = [];
      } else {
        this.tableData = this.tableData.filter((item) => !this.checkPidList.some((ele) => ele === item.id));
      }
      this.checkPidList = [];
      this.isAllChecked = false;
    },
    cancel() {
      this.modals = false;
    },
    batchVisibleChange() {
      this.formBatch.price = '';
      this.formBatch.quota = '';
    },
    searchWord() {
      let list = [];
      console.log(this.tableData, this.keyword);
      this.tableData.forEach((item) => {
        let obj = item.store_name.indexOf(this.keyword);
        if (obj != -1) {
          list.push(item);
        }
      });
      console.log(list);
      if (this.keyword) {
        this.searchTableData = list;
      } else {
        this.searchTableData = [];
      }
    },
    checkboxAll() {
      this.isAllChecked = this.$refs.xTree.isAllCheckboxChecked();
      if (!this.isAllChecked) {
        this.checkPidList = [];
      }
    },
    checkboxItem(e) {
      let id = parseInt(e.rowid);
      if (e.row.product_id) {
        let pIndex = this.checkPidList.indexOf(e.row.product_id);
        if (pIndex !== -1 && !e.checked) {
          this.checkPidList = this.checkPidList.filter((item) => item !== e.row.product_id);
        }
        if (pIndex === -1 && e.checked) {
          this.checkPidList.push(e.row.product_id);
        }
      } else {
        let pIndex = this.checkPidList.indexOf(id);
        if (pIndex !== -1 && !e.checked) {
          this.checkPidList = this.checkPidList.filter((item) => item !== id);
        }
        if (pIndex === -1 && e.checked) {
          this.checkPidList.push(id);
        }
      }
      this.isAllChecked = this.$refs.xTree.isAllCheckboxChecked();
    },
    // 添加运费模板
    freight() {
      this.$refs.template.id = 0;
      this.$refs.template.isTemplate = true;
    },

    // 多选
    changeCheckbox(selection) {
      this.formValidate.attrs = selection;
    },
    seckillTimeList() {
      let that = this;
      seckillTimeListApi()
        .then((res) => {
          that.timeList = res.data.list.data;
        })
        .catch((res) => {
          that.$message.error(res.msg);
        });
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
        this.$message.error(error);
      }
    },
    // 商品id
    getProductId(data) {
      console.log(data, 'data');
      this.modals = false;
      let listChecked = JSON.parse(JSON.stringify(data));
      listChecked.forEach((item) => {
        item.parent = 1;
        item.status = 1;
        item.isAllChecked = true;
        item.attrs.forEach((value) => {
          value.cate_name = item.cate_name;
          value.store_label = item.store_label;
          value.product_price = item.price;
          value.status = 1;
        });
      });
      let list = this.tableData.concat(listChecked);
      let uni = this.unique(list);
      this.tableData = uni;
    },
    //对象数组去重；
    unique(arr) {
      const res = new Map();
      return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
    },
    cancel() {
      this.modals = false;
    },
    okBatch() {
      if (this.formBatch.price == '' && this.formBatch.quota == '') {
        return this.$Message.error('请输入秒杀价或限量');
      }
      if (this.isAllChecked && (this.tableData.length == this.searchTableData.length || !this.searchTableData.length)) {
        this.tableData.forEach((item) => {
          item.attrs.forEach((j) => {
            if (this.formBatch.price != '') {
              j.price = this.formBatch.price;
            }
            if (this.formBatch.quota != '') {
              j.quota = this.formBatch.quota;
            }
          });
        });
      } else {
        for (let i = 0; i < this.tableData.length; i++) {
          for (let j = 0; j < this.checkPidList.length; j++) {
            if (this.tableData[i].id == this.checkPidList[j]) {
              this.tableData[i].attrs.forEach((x) => {
                if (this.formBatch.price != '') {
                  x.price = this.formBatch.price;
                }
                // 批量设置限量不为空，则修改规格上架的限量
                if (this.formBatch.quota != '' && x.status) {
                  x.quota = this.formBatch.quota;
                }
              });
            }
          }
        }
      }
      this.modalsSet = false;
    },
    // 具体日期
    onchangeTime(e) {
      this.formValidate.section_time = e;
    },
    // 详情
    getInfo() {
      this.spinShow = true;
      seckillActivityInfoApi(this.$route.params.id)
        .then(async (res) => {
          this.formValidate = res.data;
          this.tableData = res.data.product_infos;
          this.tableData.forEach((item) => {
            item.parent = 1;
            item.isAllChecked = true;
            item.attrs.forEach((value) => {
              value.cate_name = item.cate_name;
              value.store_label = item.store_label;
            });
          });
          this.spinShow = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.$message.error(res.msg);
        });
    },
    getRowKeys(row) {
      return row.id;
    },
    changePrice(e, index) {
      this.$set(this.specsData[index], 'price', e);
    },
    // 下一步
    next(name) {
      let that = this;
      if (this.current === 1) {
        this.formValidate.id = Number(this.$route.params.id) || 0;
        this.submitOpen = true;
        let product_infos = [];
        this.tableData.forEach((item) => {
          product_infos.push({
            id: item.id,
            status: item.status,
            attrs: item.attrs,
          });
          this.formValidate.product_infos = product_infos;
        });
        seckillActivityAddApi(this.formValidate)
          .then(async (res) => {
            this.submitOpen = false;
            this.$message.success(res.msg);
            setTimeout(() => {
              this.$router.push({
                path: this.$routeProStr + '/marketing/store_seckill/index',
              });
            }, 500);
          })
          .catch((res) => {
            this.submitOpen = false;
            this.$message.error(res.msg);
          });
      } else {
        this.current += 1;
      }
    },
    // 上一步
    step() {
      this.current--;
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
        // case 'danTable':
        //     this.specsData[this.tableIndex].pic = pc.att_dir;
        //     break;
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
      this.$nextTick((e) => {
        this.$refs.goodslist.formValidate.is_show = -1;
        this.$refs.goodslist.formValidate.type = 3;
        this.$refs.goodslist.getList();
        this.$refs.goodslist.goodsCategory();
      });
    }, // 移动
    handleDragStart(e, item) {
      this.dragging = item;
    },
    handleDragEnd(e, item) {
      this.dragging = null;
    },
    // 首先把div变成可以放置的元素，即重写dragenter/dragover
    handleDragOver(e) {
      e.dataTransfer.dropEffect = 'move'; // e.dataTransfer.dropEffect="move";//在dragenter中针对放置目标来设置!
    },
    handleDragEnter(e, item) {
      e.dataTransfer.effectAllowed = 'move'; // 为需要移动的元素设置dragstart事件
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

<style lang="scss" scoped>
.content_width {
  width: 460px;
}
.maxW ::v-deep .ivu-select-dropdown {
  max-width: 600px;
}
.grey {
  color: #999;
  font-size: 12px;
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
