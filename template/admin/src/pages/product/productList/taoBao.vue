<template>
  <div class="Box">
    <Card>
      <div>
        生成的商品默认是没有上架的，请手动上架商品！
        <a href="http://help.crmeb.net/crmeb-v4/1863579" v-if="copyConfig.copy_type == 2" target="_blank"
          >如何配置密钥</a
        >
        <span v-else
          >您当前剩余{{ copyConfig.copy_num }}条采集次数，<a href="#" @click="mealPay('copy')">增加采集次数</a></span
        >
      </div>
      <div>商品采集设置：设置 > 系统设置 > 第三方接口设置 > 采集商品配置</div>
    </Card>
    <Form
      class="formValidate mt20"
      ref="formValidate"
      :model="formValidate"
      :rules="ruleInline"
      :label-width="120"
      label-position="right"
      @submit.native.prevent
    >
      <Row :gutter="24" type="flex">
        <!--<Col span="24">-->
        <!--<FormItem label=""  label-for="">-->
        <!--<RadioGroup v-model="artFrom.type">-->
        <!--<Radio label="taobao">淘宝</Radio>-->
        <!--<Radio label="tmall">天猫</Radio>-->
        <!--<Radio label="jd">京东</Radio>-->
        <!--<Radio label="pdd">拼多多</Radio>-->
        <!--<Radio label="suning">苏宁</Radio>-->
        <!--<Radio label="1688">1688</Radio>-->
        <!--</RadioGroup>-->
        <!--</FormItem>-->
        <!--</Col>-->
        <Col span="15">
          <FormItem label="链接地址：">
            <Input
              search
              enter-button="确定"
              v-model="soure_link"
              placeholder="请输入链接地址"
              class="numPut"
              @on-search="add"
            />
          </FormItem>
        </Col>
        <div>
          <div v-if="isData">
            <Col span="24" class="">
              <FormItem label="商品名称：" prop="store_name">
                <Input v-model="formValidate.store_name" placeholder="请输入商品名称" />
              </FormItem>
            </Col>
            <Col span="24">
              <FormItem label="商品简介：" prop="store_info" label-for="store_info">
                <Input v-model="formValidate.store_info" type="textarea" :rows="3" placeholder="请输入商品简介" />
              </FormItem>
            </Col>
            <Col span="24">
              <FormItem label="商品分类：" prop="cate_id">
                <Select v-model="formValidate.cate_id" multiple>
                  <Option v-for="item in treeSelect" :disabled="item.pid === 0" :value="item.id" :key="item.id">{{
                    item.html + item.cate_name
                  }}</Option>
                </Select>
              </FormItem>
            </Col>
            <Col v-bind="grid">
              <FormItem label="商品关键字：" prop="keyword" label-for="keyword">
                <Input v-model="formValidate.keyword" placeholder="请输入商品关键字" />
              </FormItem>
            </Col>
            <Col v-bind="grid">
              <FormItem label="单位：" prop="unit_name" label-for="unit_name">
                <Input v-model="formValidate.unit_name" placeholder="请输入单位" />
              </FormItem>
            </Col>
            <Col v-bind="grid">
              <FormItem label="虚拟销量：" label-for="ficti">
                <InputNumber class="perW100" v-model="formValidate.ficti" placeholder="请输入虚拟销量" />
              </FormItem>
            </Col>
            <Col v-bind="grid">
              <FormItem label="积分：" label-for="give_integral">
                <InputNumber class="perW100" v-model="formValidate.give_integral" placeholder="请输入积分" />
              </FormItem>
            </Col>
            <Col v-bind="grid">
              <FormItem label="运费模板：" prop="temp_id">
                <Select v-model="formValidate.temp_id" clearable>
                  <Option v-for="(item, index) in templateList" :value="item.id" :key="index">{{ item.name }}</Option>
                </Select>
              </FormItem>
            </Col>
            <!--<Col v-bind="grid">-->
            <!--<FormItem label="邮费："  label-for="postage">-->
            <!--<InputNumber  v-model="formValidate.postage" placeholder="请输入邮费"  />-->
            <!--</FormItem>-->
            <!--</Col>-->
            <Col span="24">
              <FormItem label="商品图：">
                <div class="pictrueBox">
                  <div class="pictrue" v-if="formValidate.image" v-viewer>
                    <img v-lazy="formValidate.image" />
                  </div>
                </div>
              </FormItem>
            </Col>
            <Col span="24">
              <FormItem label="商品轮播图：">
                <div class="acea-row" v-viewer>
                  <div
                    class="lunBox mr15"
                    v-for="(item, index) in formValidate.slider_image"
                    :key="index"
                    draggable="true"
                    @dragstart="handleDragStart($event, item)"
                    @dragover.prevent="handleDragOver($event, item)"
                    @dragenter="handleDragEnter($event, item)"
                    @dragend="handleDragEnd($event, item)"
                  >
                    <div class="pictrue"><img v-lazy="item" /></div>
                    <ButtonGroup size="small">
                      <Button @click.native="checked(item, index)">主图</Button>
                      <Button @click.native="handleRemove(index)">移除</Button>
                    </ButtonGroup>
                  </div>
                </div>
              </FormItem>
            </Col>
            <Col span="24">
              <FormItem label="批量设置：" class="labeltop" v-if="formValidate.attrs">
                <Col :xl="23" :lg="24" :md="24" :sm="24" :xs="24">
                  <FormItem>
                    <Table :data="oneFormBatch" :columns="columnsBatch" border>
                      <template slot-scope="{ row, index }" slot="pic">
                        <div class="acea-row row-middle row-center-wrapper" @click="modalPicTap('dan', 'duopi', index)">
                          <div class="pictrue pictrueTab" v-if="oneFormBatch[0].pic">
                            <img v-lazy="oneFormBatch[0].pic" />
                          </div>
                          <div class="upLoad pictrueTab acea-row row-center-wrapper" v-else>
                            <Icon type="ios-camera-outline" size="21" class="iconfont" />
                          </div>
                        </div>
                      </template>
                      <template slot-scope="{ row, index }" slot="price">
                        <InputNumber v-model="oneFormBatch[0].price" :min="0" class="priceBox"></InputNumber>
                      </template>
                      <template slot-scope="{ row, index }" slot="cost">
                        <InputNumber v-model="oneFormBatch[0].cost" :min="0" class="priceBox"></InputNumber>
                      </template>
                      <template slot-scope="{ row, index }" slot="ot_price">
                        <InputNumber v-model="oneFormBatch[0].ot_price" :min="0" class="priceBox"></InputNumber>
                      </template>
                      <template slot-scope="{ row, index }" slot="stock">
                        <InputNumber v-model="oneFormBatch[0].stock" :min="0" class="priceBox"></InputNumber>
                      </template>
                      <template slot-scope="{ row, index }" slot="bar_code">
                        <Input v-model="oneFormBatch[0].bar_code"></Input>
                      </template>
                      <template slot-scope="{ row, index }" slot="weight">
                        <InputNumber v-model="oneFormBatch[0].weight" :min="0" class="priceBox"></InputNumber>
                      </template>
                      <template slot-scope="{ row, index }" slot="volume">
                        <InputNumber v-model="oneFormBatch[0].volume" :min="0" class="priceBox"></InputNumber>
                      </template>
                      <template slot-scope="{ row, index }" slot="action">
                        <a @click="batchAdd">添加</a>
                        <Divider type="vertical" />
                        <a @click="batchDel">清空</a>
                      </template>
                    </Table>
                  </FormItem>
                </Col>
              </FormItem>
            </Col>
            <Col span="24">
              <FormItem label="商品规格：" props="spec_type" label-for="spec_type">
                <!-- 单规格表格-->
                <Col :xl="23" :lg="24" :md="24" :sm="24" :xs="24">
                  <FormItem>
                    <Table :data="items" :columns="columns" border>
                      <template slot-scope="{ row, index }" slot="pic">
                        <div class="acea-row row-middle row-center-wrapper" @click="modalPicTap('dan', index)">
                          <div class="pictrue pictrueTab" v-if="formValidate.attrs[index].pic">
                            <img v-lazy="formValidate.attrs[index].pic" />
                          </div>
                          <div class="upLoad upLoadTab acea-row row-center-wrapper" v-else>
                            <Icon type="ios-camera-outline" size="26" class="iconfont" />
                          </div>
                        </div>
                      </template>
                      <template slot-scope="{ row, index }" slot="price">
                        <InputNumber v-model="formValidate.attrs[index].price" class="priceBox"></InputNumber>
                      </template>
                      <template slot-scope="{ row, index }" slot="cost">
                        <InputNumber v-model="formValidate.attrs[index].cost" class="priceBox"></InputNumber>
                      </template>
                      <template slot-scope="{ row, index }" slot="ot_price">
                        <InputNumber v-model="formValidate.attrs[index].ot_price" class="priceBox"></InputNumber>
                      </template>
                      <template slot-scope="{ row, index }" slot="stock">
                        <InputNumber v-model="formValidate.attrs[index].stock" class="priceBox"></InputNumber>
                      </template>
                      <template slot-scope="{ row, index }" slot="bar_code">
                        <Input v-model="formValidate.attrs[index].bar_code"></Input>
                      </template>
                      <template slot-scope="{ row, index }" slot="weight">
                        <InputNumber v-model="formValidate.attrs[index].weight" :min="0" class="priceBox"></InputNumber>
                      </template>
                      <template slot-scope="{ row, index }" slot="volume">
                        <InputNumber v-model="formValidate.attrs[index].volume" :min="0" class="priceBox"></InputNumber>
                      </template>
                      <template slot-scope="{ row, index }" slot="action">
                        <a @click="delAttrTable(index)">删除</a>
                      </template>
                    </Table>
                  </FormItem>
                </Col>
              </FormItem>
            </Col>
            <Col span="24">
              <FormItem label="商品详情：">
                <WangEditor
                  style="width: 100%"
                  :content="formValidate.description"
                  @editorContent="getEditorContent"
                ></WangEditor>
              </FormItem>
            </Col>
            <Col span="24">
              <FormItem>
                <Button type="primary" :loading="modal_loading" class="submission" @click="handleSubmit('formValidate')"
                  >提交</Button
                >
              </FormItem>
            </Col>
          </div>
          <Spin size="large" fix v-if="spinShow"></Spin>
        </div>
      </Row>
    </Form>
    <Modal
      v-model="modalPic"
      width="950px"
      scrollable
      footer-hide
      closable
      title="上传商品图"
      :mask-closable="false"
      :z-index="9999"
    >
      <uploadPictures
        :isChoice="isChoice"
        @getPic="getPic"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="modalPic"
      ></uploadPictures>
    </Modal>
  </div>
</template>

<script>
import { crawlFromApi, treeListApi, crawlSaveApi, productGetTemplateApi, copyConfigApi } from '@/api/product';
import uploadPictures from '@/components/uploadPictures';
import WangEditor from '@/components/wangEditor/index.vue';

export default {
  name: 'taoBao',
  data() {
    return {
      // 批量设置表格data
      oneFormBatch: [
        {
          pic: '',
          price: 0,
          cost: 0,
          ot_price: 0,
          stock: 0,
          bar_code: '',
          weight: 0,
          volume: 0,
        },
      ],
      columnsBatch: [
        {
          title: '图片',
          slot: 'pic',
          align: 'center',
          minWidth: 80,
        },
        {
          title: '售价',
          slot: 'price',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '成本价',
          slot: 'cost',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '原价',
          slot: 'ot_price',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '库存',
          slot: 'stock',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '商品编号',
          slot: 'bar_code',
          align: 'center',
          minWidth: 120,
        },
        {
          title: '重量（KG）',
          slot: 'weight',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '体积(m³)',
          slot: 'volume',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '操作',
          slot: 'action',
          align: 'center',
          minWidth: 140,
        },
      ],
      modal_loading: false,
      images: '',
      soure_link: '',
      modalPic: false,
      isChoice: '',
      spinShow: false,
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
      columns: [],
      treeSelect: [],
      ruleInline: {
        cate_id: [
          {
            required: true,
            message: '请选择商品分类',
            trigger: 'change',
            type: 'array',
            min: '1',
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
      grid: {
        xl: 8,
        lg: 8,
        md: 12,
        sm: 24,
        xs: 24,
      },
      grid2: {
        xl: 12,
        lg: 12,
        md: 12,
        sm: 24,
        xs: 24,
      },
      formValidate: {
        store_name: '',
        cate_id: [],
        temp_id: '',
        keyword: '',
        unit_name: '',
        store_info: '',
        image: '',
        slider_image: [],
        description: '',
        ficti: 0,
        give_integral: 0,
        is_show: 0,
        price: 0,
        cost: 0,
        ot_price: 0,
        stock: 0,
        soure_link: '',
        description_images: '',
        postage: 0,
        attrs: [],
        items: [],
      },
      items: [
        {
          pic: '',
          price: 0,
          cost: 0,
          ot_price: 0,
          stock: 0,
          bar_code: '',
          weight: 0,
          volume: 0,
        },
      ],
      templateList: [],
      copyConfig: {
        copy_type: 2,
        copy_num: 0,
      },
      isData: false,
      artFrom: {
        type: 'taobao',
        url: '',
      },
      tableIndex: 0,
      content: '',
    };
  },
  components: { WangEditor, uploadPictures },
  computed: {},

  created() {
    this.goodsCategory();
  },
  mounted() {
    this.productGetTemplate();
    this.getCopyConfig();
  },
  methods: {
    mealPay(val) {
      this.$router.push({
        path: '/admin/setting/sms/sms_pay/index',
        query: { type: val },
      });
    },
    batchDel() {
      this.oneFormBatch = [
        {
          pic: '',
          price: 0,
          cost: 0,
          ot_price: 0,
          stock: 0,
          bar_code: '',
          weight: 0,
          volume: 0,
        },
      ];
    },
    batchAdd() {
      let formBatch = this.oneFormBatch[0];
      this.$set(
        this.formValidate,
        'attrs',
        this.formValidate.attrs.map((item) => {
          if (formBatch.pic) {
            item.pic = formBatch.pic;
          }
          if (formBatch.price > 0) {
            item.price = formBatch.price;
          }
          if (formBatch.cost > 0) {
            item.cost = formBatch.cost;
          }
          if (formBatch.ot_price > 0) {
            item.ot_price = formBatch.ot_price;
          }
          if (formBatch.stock > 0) {
            item.stock = formBatch.stock;
          }
          if (formBatch.bar_code) {
            item.bar_code = formBatch.bar_code;
          }
          if (formBatch.weight) {
            item.weight = formBatch.weight;
          }
          if (formBatch.volume) {
            item.weight = formBatch.volume;
          }
          return item;
        }),
      );
    },
    getEditorContent(data) {
      this.content = data;
    },
    // 删除表格中的属性
    delAttrTable(index) {
      this.items.splice(index, 1);
    },
    // 获取运费模板；
    productGetTemplate() {
      productGetTemplateApi().then((res) => {
        this.templateList = res.data;
      });
    },
    getCopyConfig() {
      copyConfigApi().then((res) => {
        this.copyConfig.copy_type = res.data.copy_type;
        this.copyConfig.copy_num = res.data.copy_num;
      });
    },
    // 删除图片
    handleRemove(i) {
      this.formValidate.slider_image.splice(i, 1);
    },
    // 选择主图
    checked(item, index) {
      this.formValidate.image = item;
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
    // 生成表单
    add() {
      if (this.soure_link) {
        var reg = /(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?/;
        if (!reg.test(this.soure_link)) {
          return this.$Message.warning('请输入以http开头的地址！');
        }
        this.spinShow = true;
        this.artFrom.url = this.soure_link;
        crawlFromApi(this.artFrom)
          .then((res) => {
            let info = res.data.info;
            this.columns = info.info.header;
            this.formValidate = info;
            this.formValidate.soure_link = this.soure_link;
            this.formValidate.attrs = info.info.value;
            if (this.formValidate.image) {
              this.oneFormBatch[0].pic = this.formValidate.image;
            }
            this.items = this.formValidate.attrs;
            this.isData = true;
            this.spinShow = false;
          })
          .catch((res) => {
            this.spinShow = false;
            this.$Message.error(res.msg);
          });
      } else {
        this.$Message.warning('请输入链接地址！');
      }
    },
    // 提交
    handleSubmit(name) {
      this.formValidate.description = this.content;
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.modal_loading = true;
          // this.formValidate.attrs = [
          //     {
          //         pic: this.images,
          //         price: this.formValidate.price,
          //         cost: this.formValidate.cost,
          //         ot_price: this.formValidate.ot_price,
          //         stock: this.formValidate.stock,
          //         bar_code: this.formValidate.bar_code,
          //         weight: this.formValidate.weight,
          //         volume: this.formValidate.volume
          //     }
          // ];
          // this.formValidate.items = [];
          crawlSaveApi(this.formValidate)
            .then((res) => {
              this.$Message.success('商品默认为不上架状态请手动上架商品!');
              setTimeout(() => {
                this.modal_loading = false;
              }, 500);
              setTimeout(() => {
                this.$emit('on-close');
              }, 600);
            })
            .catch((res) => {
              this.modal_loading = false;
              this.$Message.error(res.msg);
            });
        } else {
          if (!this.formValidate.cate_id) {
            this.$Message.warning('请填写商品分类！');
          }
        }
      });
    },
    // 点击商品图
    modalPicTap(tit, index) {
      this.modalPic = true;
      this.isChoice = tit === 'dan' ? '单选' : '多选';
      this.tableIndex = index;
    },
    // 获取单张图片信息
    getPic(pc) {
      if (this.tableIndex === 'duopi') {
        this.oneFormBatch[0].pic = pc.att_dir;
      } else {
        this.formValidate.attrs[this.tableIndex].pic = pc.att_dir;
      }
      this.modalPic = false;
    },
    handleDragStart(e, item) {
      this.dragging = item;
    },
    handleDragEnd(e, item) {
      this.dragging = null;
    },
    // 首先把div变成可以放置的元素，即重写dragenter/dragover
    handleDragOver(e) {
      // e.dataTransfer.dropEffect="move";//在dragenter中针对放置目标来设置!
      e.dataTransfer.dropEffect = 'move';
    },
    handleDragEnter(e, item) {
      // 为需要移动的元素设置dragstart事件
      e.dataTransfer.effectAllowed = 'move';
      if (item === this.dragging) {
        return;
      }
      const newItems = [...this.formValidate.slider_image];
      const src = newItems.indexOf(this.dragging);
      const dst = newItems.indexOf(item);
      newItems.splice(dst, 0, ...newItems.splice(src, 1));
      this.formValidate.slider_image = newItems;
    },
    // 添加自定义弹窗
    addCustomDialog(editorId) {
      window.UE.registerUI(
        'test-dialog',
        function (editor, uiName) {
          // 创建 dialog
          let dialog = new window.UE.ui.Dialog({
            iframeUrl: '/admin/widget.images/index.html?fodder=dialog',
            editor: editor,
            name: uiName,
            title: '上传图片',
            cssRules: 'width:960px;height:550px;padding:20px;',
          });
          this.dialog = dialog;
          let btn = new window.UE.ui.Button({
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
      // window.UE.registerUI('test-dialog', function (editor, uiName) {
      //     let dialog = new window.UE.ui.Dialog({
      //         iframeUrl: '/admin/widget.images/index.html?fodder=dialog',
      //         editor: editor,
      //         name: uiName,
      //         title: '上传图片',
      //         cssRules: 'width:960px;height:550px;padding:20px;'
      //     })
      //     this.dialog = dialog
      //     var btn = new window.UE.ui.Button({
      //         name: 'dialog-button',
      //         title: '上传图片',
      //         cssRules: `background-image: url(../../../assets/images/icons.png);background-position: -726px -77px;`,
      //         onclick: function () {
      //             dialog.render()
      //             dialog.open()
      //         }
      //     })
      //     return btn
      // }, 37)
    },
  },
};
</script>

<style scoped lang="stylus">
/deep/.ivu-form-item-content {
  line-height: unset !important;
}

.Box .ivu-radio-wrapper {
  margin-right: 25px;
}

.Box .numPut {
  width: 100% !important;
}

.lunBox {
  /* width 80px */
  display: flex;
  flex-direction: column;
  border: 1px solid #0bb20c;
}

.pictrueBox {
  display: inline-block;
}

.pictrue {
  width: 85px;
  height: 85px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  display: inline-block;
  position: relative;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}

.pictrueTab {
  width: 40px !important;
  height: 40px !important;
}

.upLoad {
  width: 40px;
  height: 40px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.02);
  cursor: pointer;
}

.ivu-table-wrapper {
  border-left: 1px solid #dcdee2;
  border-top: 1px solid #dcdee2;
}

.ft {
  color: red;
}
</style>
