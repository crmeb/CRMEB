<template>
  <div>
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <router-link :to="{ path: '/admin/marketing/store_integral/index' }"
          ><Button icon="ios-arrow-back" size="small" class="mr20">返回</Button></router-link
        >
        <span class="ivu-page-header-title mr20" v-text="$route.params.id ? '编辑积分商品' : '添加积分商品'"></span>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Row type="flex" class="mt30 acea-row row-middle row-center">
        <Col span="23">
          <Form
            class="form mt30"
            ref="formValidate"
            :model="formValidate"
            :rules="ruleValidate"
            :label-width="labelWidth"
            :label-position="labelPosition"
            @submit.native.prevent
          >
            <FormItem label="选择商品：" prop="image_input">
              <div class="picBox" @click="changeGoods">
                <div class="pictrue" v-for="(item, index) in goodsImg" :key="index">
                  <img v-lazy="item.image" v-if="item.image" />
                  <span class="iconfont icondel_1" @click.stop="bindDelete(index)"></span>
                </div>

                <div class="upLoad acea-row row-center-wrapper">
                  <Icon type="ios-camera-outline" size="26" class="iconfonts" />
                </div>
              </div>
            </FormItem>
            <Col type="flex">
              <Col span="24">
                <FormItem label="规格选择：">
                  <Table
                    :data="specsData"
                    :columns="columns"
                    border
                    class="mt25"
                    highlight-row
                    :loading="loading"
                    @on-selection-change="changeCheckbox"
                  >
                    <template slot-scope="{ row, index }" slot="pic">
                      <div
                        class="acea-row row-middle row-center-wrapper"
                        @click="modalPicTap('dan', 'danTable', index)"
                      >
                        <div class="pictrue pictrueTab" v-if="row.pic">
                          <img v-lazy="row.pic" />
                        </div>
                        <div class="upLoad pictrueTab acea-row row-center-wrapper" v-else>
                          <Icon type="ios-camera-outline" size="21" class="iconfont" />
                        </div>
                      </div>
                    </template>
                  </Table>
                </FormItem>
              </Col>
              <Col span="24">
                <FormItem label="上架状态：" props="is_show" label-for="is_show">
                  <RadioGroup element-id="is_show" v-model="formValidate.is_show">
                    <Radio :label="0">关闭</Radio>
                    <Radio :label="1" class="radio">开启</Radio>
                  </RadioGroup>
                </FormItem>
              </Col>
            </Col>
            <Col span="24">
              <FormItem>
                <Button
                  class="submission mr15"
                  @click="step"
                  v-show="current !== 0"
                  :disabled="$route.params.id && current === 1"
                  >上一步
                </Button>
                <Button
                  :disabled="submitOpen && current === 1"
                  type="primary"
                  class="submission"
                  @click="next('formValidate')"
                  v-text="'提交'"
                ></Button>
              </FormItem>
            </Col>
          </Form>
          <Spin size="large" fix v-if="spinShow"></Spin>
        </Col>
      </Row>
    </Card>
    <!-- 选择商品-->
    <Modal
      v-model="modals"
      title="商品列表"
      class="paymentFooter"
      footerHide
      scrollable
      width="900"
      @on-cancel="cancel"
    >
      <goods-list v-if="modals" ref="goodslist" :ischeckbox="true" @getProductId="getProductId"></goods-list>
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
      :z-index="1"
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
  </div>
</template>

<script>
import { mapState } from 'vuex';
import goodsList from '@/components/goodsList/index';
import uploadPictures from '@/components/uploadPictures';
import { integralAddBatch, productAttrsApi, integralInfoApi } from '@/api/marketing';

export default {
  name: 'storeIntegralCreate',
  components: { goodsList, uploadPictures },
  data() {
    return {
      loading: false,
      goodsImg: [],
      productsId: [],
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
        UEDITOR_HOME_URL: '/admin/UEditor/',
        serverUrl: '',
      },
      modals: false,
      modal_loading: false,
      images: [],
      formValidate: {
        is_show: 0,
        attrs: [],
      },
      templateList: [],
      timeList: [],
      columns: [],
      specsData: [],
      picTit: '',
      tableIndex: 0,
      ruleValidate: {
        price: [
          {
            required: true,
            type: 'number',
            message: '请输入兑换积分',
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
      },
      copy: 0,
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 135;
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
  },
  methods: {
    // 规格；
    productAttrs(rows) {
      this.loading = true;
      return new Promise((resolve, reject) => {
        let that = this;
        productAttrsApi(rows.product_id, 4)
          .then((res) => {
            let data = res.data.info;
            let selection = {
              type: 'selection',
              width: 60,
              align: 'center',
            };
            that.specsData = that.specsData.concat(data.attrs);
            that.specsData.forEach(function (item, index) {
              that.$set(that.specsData[index], 'id', index);
            });
            that.columns = data.header;
            that.columns.unshift(selection);
            that.inputChange(data);
            resolve();
            this.loading = false;
          })
          .catch((res) => {
            this.loading = false;

            reject();
            that.$Message.error(res.msg);
          });
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
          minWidth: 120,
          render: (h, params) => {
            return h('div', [
              h('InputNumber', {
                props: {
                  min: 0,
                  precision: 0,
                  value: key === 'price' ? params.row.price : params.row.quota,
                },
                on: {
                  'on-change': (e) => {
                    key === 'price' ? (params.row.price = e) : (params.row.quota = e);
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
    // 商品id
    async getProductId(row) {
      for (let i = 0; i < this.goodsImg.length; i++) {
        const element = this.goodsImg[i];
        if (element.product_id === row.product_id) {
          this.$Message.error('该商品已经添加');
          return;
        }
      }
      this.modal_loading = false;
      this.modals = false;
      this.goodsImg = this.goodsImg.concat(row);
      // this.goodsImg.push({
      //   image: row.image,
      //   id: row.id,
      // });
      for (let j = 0; j < row.length; j++) {
        const element = row[j];
        await this.productAttrs(element);
      }
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
      integralInfoApi(this.$route.params.id)
        .then(async (res) => {
          let that = this;
          let info = res.data.info;
          let selection = {
            type: 'selection',
            width: 60,
            align: 'center',
          };
          this.formValidate = info;
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
    bindDelete(index) {
      let delId = this.goodsImg[index].product_id;
      this.goodsImg.splice(index, 1);
      this.loading = true;
      // 删除逻辑待完善
    },
    // 下一步
    next(name) {
      let that = this;
      if (!this.formValidate.attrs.length) {
        return that.$Message.error('请先选择商品规格');
      }
      for (let index in that.formValidate.attrs) {
        if (that.formValidate.attrs[index].quota <= 0) {
          return that.$Message.error('商品限量必须大于0');
        }
      }
      this.$refs[name].validate((valid) => {
        if (valid) {
          if (this.copy == 1) this.formValidate.copy = 1;
          this.formValidate.id = Number(this.$route.params.id) || 0;
          this.submitOpen = true;
          integralAddBatch(this.formValidate)
            .then(async (res) => {
              this.submitOpen = false;
              this.$Message.success(res.msg);
              setTimeout(() => {
                this.$router.push({
                  path: '/admin/marketing/store_integral/index',
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
    // 添加自定义弹窗
    addCustomDialog(editorId) {
      window.UE.registerUI(
        'test-dialog',
        function (editor, uiName) {
          // 创建 dialog
          let dialog = new window.UE.ui.Dialog({
            // 指定弹出层中页面的路径，这里只能支持页面，路径参考常见问题 2
            iframeUrl: '/admin/widget.images/index.html?fodder=dialog',
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
          // 参考上面的自定义按钮
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
        37 /* 指定添加到工具栏上的那个位置，默认时追加到最后 */,
        editorId /* 指定这个UI是哪个编辑器实例上的，默认是页面上所有的编辑器都会添加这个按钮 */,
      );
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
    display: flex;
    align-items: center;
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

    .icondel_1 {
      position: absolute;
      right: -10px;
      top: -16px;
      color: #999999;
      font-size: 28px;
      cursor: pointer;
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
