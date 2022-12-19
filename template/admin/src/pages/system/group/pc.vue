<template>
  <div>
    <!-- <div class="i-layout-page-header">
			<PageHeader
					class="product_tabs"
					:title="$route.meta.title"
					hidden-breadcrumb
			>
				<div slot="title">
					<div style="float: left;">
						<span v-text="$route.meta.title" class="mr20"></span>
					</div>
					<div style="float: right;">
						<Button class="bnt" type="primary" @click="save">保存</Button>
					</div><strong></strong>
				</div>
			</PageHeader>
		</div> -->
    <div class="i-layout-page-header">
      <span class="ivu-page-header-title mr20">{{ $route.meta.title }}</span>
      <div>
        <div style="float: right">
          <Button class="bnt" type="primary" @click="save">保存</Button>
        </div>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Row class="ivu-mt box-wrapper">
        <Col :xs="24" :sm="24" :md="6" :lg="3" class="left-wrapper">
          <div class="left_box">
            <div class="left_cont" :class="pageId == 1 ? 'on' : ''" @click="menu(1)">网站LOGO</div>
            <div class="left_cont" :class="pageId == 'pc_home_banner' ? 'on' : ''" @click="menu('pc_home_banner')">
              首页轮播图
            </div>
            <div class="left_cont" :class="pageId == 3 ? 'on' : ''" @click="menu(3)">客服页面广告</div>
          </div>
        </Col>
        <div style="display: flex; width: 83%">
          <Col v-if="pageId == 1 || pageId == 'pc_home_banner'" class="pciframe" :bordered="false" dis-hover>
            <img src="../../../assets/images/pcbanner.png" class="pciframe-box" />
            <div v-if="pageId == 1" class="logoimg">
              <img :src="pclogo" />
            </div>
            <div v-if="pageId == 'pc_home_banner'" class="pcmoddile_goods">
              <div class="nofonts" v-if="tabList.list == ''">暂无照片，请添加~</div>
              <swiper v-else :options="swiperOption" class="pcswiperimg_goods">
                <swiper-slide class="spcwiperimg_goods" v-for="(item, index) in tabList.list" :key="index">
                  <img :src="item.image" />
                </swiper-slide>
              </swiper>
            </div>
          </Col>
          <Col v-if="pageId == 3" class="pciframe" :bordered="false" dis-hover>
            <img src="../../../assets/images/kefu.png" class="pciframe-box" />
            <div class="box3_sile">
              <!-- {{formValidate}} -->
              <div v-html="formValidate.content"></div>
            </div>
          </Col>
          <Col v-if="pageId == 'pc_home_banner'">
            <div class="content">
              <div class="right-box">
                <div class="hot_imgs">
                  <div class="title">轮播图设置</div>
                  <div class="title-text">建议尺寸：690 * 240px，拖拽图片可调整图片顺序哦，最多添加五张。</div>
                  <div class="title-text">除轮播图外，页面其他内容仅供参考</div>
                  <div class="list-box">
                    <draggable
                      v-if="pageId == 'pc_home_banner'"
                      class="dragArea list-group"
                      :list="tabList.list"
                      group="peoples"
                      handle=".move-icon"
                    >
                      <div class="item" v-for="(item, index) in tabList.list" :key="index">
                        <div class="move-icon">
                          <span class="iconfont icondrag2"></span>
                        </div>
                        <div class="img-box imgBoxs" @click="modalPicTap('单选', index)">
                          <img :src="item.image" alt="" v-if="item.image" />
                          <div class="upload-box" v-else>
                            <Icon type="ios-camera-outline" size="36" />
                          </div>
                          <div class="delect-btn" style="line-height: 0px" @click.stop="bindDelete(item, index)">
                            <Icon type="md-close-circle" size="26" />
                          </div>
                        </div>
                        <div class="info">
                          <div class="info-item">
                            <span>图片名称：</span>
                            <div class="input-box">
                              <Input v-model="item.title" placeholder="请填写名称" />
                            </div>
                          </div>
                          <div class="info-item">
                            <span>链接地址：</span>
                            <!-- @click="link(index) icon="ios-arrow-forward" "-->
                            <div class="input-box">
                              <Input v-model="item.url" placeholder="选择链接" />
                            </div>
                          </div>
                        </div>
                      </div>
                    </draggable>
                    <div>
                      <Modal
                        v-model="modalPic"
                        width="950px"
                        scrollable
                        footer-hide
                        closable
                        title="上传商品图"
                        :mask-closable="false"
                        :z-index="999"
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
                  </div>
                  <template>
                    <div class="add-btn">
                      <Button
                        type="primary"
                        ghost
                        style="width: 100px; height: 35px; background-color: #1890ff; color: #ffffff"
                        @click="addBox"
                        >添加图片
                      </Button>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </Col>
          <Col v-if="pageId == 1">
            <div class="content">
              <div class="right-box">
                <div class="hot_imgs">
                  <div class="title">页面设置</div>
                  <div class="title-text">建议尺寸：140px * 60px</div>
                  <div class="title-text">除LOGO图标外，页面其他内容仅供参考</div>
                  <div class="list-box">
                    <div class="img-boxs" @click="modalPicTap('单选', 0)">
                      <img :src="pclogo" alt="" />
                      <div class="img_font"></div>
                      <div class="img_fonts">更换图片</div>
                    </div>
                    <div>
                      <Modal
                        v-model="modalPic"
                        width="950px"
                        scrollable
                        footer-hide
                        closable
                        title="上传商品图"
                        :mask-closable="false"
                        :z-index="999"
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
                  </div>
                </div>
              </div>
            </div>
          </Col>
          <Col v-if="pageId == 3" :xs="24" :sm="24" :md="12" :lg="14" style="margin-left: 40px">
            <div class="table_box">
              <Row type="flex">
                <Col v-bind="grid">
                  <div class="title">隐私权限页面展示：</div>
                </Col>
              </Row>
              <div>
                <Form
                  class="form"
                  ref="formValidate"
                  :model="formValidate"
                  :rules="ruleValidate"
                  :label-width="labelWidth"
                  :label-position="labelPosition"
                  @submit.native.prevent
                >
                  <div class="goodsTitle acea-row"></div>
                  <FormItem label="" prop="content" style="margin: 0px">
                    <WangEditor :content="formValidate.content" @editorContent="getEditorContent"></WangEditor>
                  </FormItem>
                </Form>
              </div>
            </div>
          </Col>
        </div>
      </Row>
    </Card>
    <!-- <div class="save">
			<Button type="primary" @click="save" >保存</Button>
		</div> -->
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import WangEditor from '@/components/wangEditor/index.vue';
import { diyGetInfo, diySave } from '@/api/diy';
import editFrom from '@/components/from/from';
import {
  groupDataListApi,
  groupSaveApi,
  groupDataAddApi,
  pcLogoApi,
  pcLogoSave,
  getKfAdv,
  setKfAdv,
} from '@/api/system';
import draggable from 'vuedraggable';
import uploadPictures from '@/components/uploadPictures';
import linkaddress from '@/components/linkaddress';
export default {
  name: 'list',
  components: {
    editFrom,
    draggable,
    uploadPictures,
    linkaddress,
    WangEditor,
  },
  data() {
    return {
      ruleValidate: {},
      formValidate: {
        content: '',
      },
      pclogo: '',
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      swiperOption: {
        //显示分页
        pagination: {
          el: '.swiper-pagination',
        },
        //设置点击箭头
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
        //自动轮播
        autoplay: {
          delay: 2000,
          //当用户滑动图片后继续自动轮播
          disableOnInteraction: false,
        },
        //开启循环模式
        loop: false,
      },
      pageId: 1,
      tabList: [],
      lastObj: {
        add_time: '',
        config_name: '',
        id: '',
        image: '',
        sort: 1,
        status: 1,
        title: '',
        url: '',
      },
      isChoice: '单选',
      modalPic: false,
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
      activeIndex: 0,
      myConfig: {
        autoHeightEnabled: false, // 编辑器不自动被内容撑高
        initialFrameHeight: 500, // 初始容器高度
        initialFrameWidth: '100%', // 初始容器宽度
        UEDITOR_HOME_URL: '/admin/UEditor/',
        serverUrl: '',
      },
      activeIndexs: 0,
    };
  },
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 120;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  mounted() {
    this.menu(1);
    this.info();
  },
  methods: {
    getEditorContent(data) {
      this.formValidate.content = data;
    },
    linkUrl(e) {
      this.tabList.list[this.activeIndexs].url = e;
      // item.url = e
    },
    getContent(val) {
      this.formValidate.content = val;
    },
    // 提交数据
    onsubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          setKfAdv(this.formValidate)
            .then(async (res) => {
              this.$Message.success(res.msg);
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
        } else {
          return false;
        }
      });
    },
    //详情
    getKfAdv() {
      getKfAdv()
        .then(async (res) => {
          let data = res.data;
          this.formValidate = {
            content: data.content,
          };
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    // 添加表单
    groupAdd() {
      this.$modalForm(groupDataAddApi({ config_name: this.pageId }, 'setting/group_data/create')).then(() =>
        this.info(),
      );
    },
    info() {
      if (this.pageId == 'pc_home_banner') {
        groupDataListApi({ config_name: this.pageId }, 'setting/group_data')
          .then(async (res) => {
            this.tabList = res.data;
            this.tabList.list.forEach((item, index, array) => {
              if (typeof item.image != 'string' && item.image != 'undefined') {
                item.image = item.image[0];
              }
            });
          })
          .catch((res) => {
            this.$Message.error(res.msg);
          });
      }
      if (this.pageId == 1) {
        pcLogoApi('pc_logo').then((res) => {
          this.pclogo = res.data.value;
        });
      }
      if (this.pageId == 3) {
        this.getKfAdv();
      }
    },
    menu(id) {
      this.pageId = id;
      this.info();
    },
    addBox() {
      if (this.tabList.list.length == 0) {
        this.tabList.list.push(this.lastObj);
        this.lastObj = {
          add_time: '',
          comment: '',
          gid: '',
          id: '',
          img: '',
          link: '',
          sort: '',
          status: 1,
        };
      } else {
        if (this.tabList.list.length == 5) {
          this.$Message.warning('最多添加五张呦');
        } else {
          let obj = JSON.parse(JSON.stringify(this.lastObj));
          this.tabList.list.push(obj);
        }
      }
    },
    // 删除
    bindDelete(item, index) {
      if (this.tabList.list.length == 1) {
        this.lastObj = this.tabList.list[0];
      }
      this.tabList.list.splice(index, 1);
    },
    // 点击图文封面
    modalPicTap(title, index) {
      this.activeIndex = index;
      this.modalPic = true;
    },
    // 获取图片信息
    getPic(pc) {
      this.$nextTick(() => {
        if (this.pageId == 'pc_home_banner') {
          this.tabList.list[this.activeIndex].image = pc.att_dir;
        } else {
          this.pclogo = pc.att_dir;
        }
        this.modalPic = false;
      });
    },
    save() {
      if (this.pageId == 'pc_home_banner') {
        groupSaveApi({ config_name: this.pageId, data: this.tabList.list })
          .then((res) => {
            this.$Message.success(res.msg);
          })
          .catch((err) => {
            this.$Message.error(err.msg);
          });
      }
      if (this.pageId == 1) {
        pcLogoSave({ pc_logo: this.pclogo })
          .then((res) => {
            this.$Message.success(res.msg);
          })
          .catch((err) => {
            this.$Message.error(err.msg);
          });
      }
      if (this.pageId == 3) {
        this.onsubmit('formValidate');
      }
    },
    link(index) {
      this.activeIndexs = index;
      this.$refs.linkaddres.modals = true;
    },
  },
};
</script>
<style type="text/css">
.box3_sile::-webkit-scrollbar {
  display: none;
}
.box3_sile {
  width: 92px;
  height: auto;
  overflow: auto;
}
.box3_sile img {
  width: 92px;
}
</style>
<style scoped lang="stylus">
/deep/ .ivu-menu-vertical .ivu-menu-item-group-title {
  display: none;
}

/deep/ .ivu-menu-vertical.ivu-menu-light:after {
  display: none;
}

.nofonts {
  text-align: center;
  line-height: 137px;
}

.save {
  width: 100%;
  margin: 0 auto;
  text-align: center;
  background-color: #FFF;
  bottom: 0;
  padding: 16px;
  border-top: 3px solid #f5f7f9;
}

.imgBoxs {
  background-color: #CCCCCC;
  line-height: 80px;
  text-align: center;
}

.link {
  display: inline-block;
  width: 100%;
  height: 32px;
  line-height: 1.5;
  padding: 4px 7px;
  border: 1px solid #dcdee2;
  border-radius: 4px;
  background-color: #fff;
  position: relative;
  cursor: text;
  transition: border 0.2s ease-in-out, background 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  font-size: 13px;
  font-family: PingFangSC-Regular;
  line-height: 22px;
  color: rgba(0, 0, 0, 0.25);
  opacity: 1;
  cursor: pointer;

  .you {
    color: #999999;
    float: right;
    margin-right: 11px;
  }
}

.box {
  border-top: 3px solid #f5f7f9;
  padding: 10px;
  padding-top: 25px;
  width: 100%;

  .save {
    background-color: #1890FF;
    color: #FFFFFF;
    width: 71px;
    height: 30px;
    margin: 0 auto;
    text-align: center;
    line-height: 30px;
    cursor: pointer;
  }
}

.box3 {
  margin-left: 20px;
  width: 730px;

  .article-manager {
    margin-top: 24px;

    .form {
      width: max-content;

      .goodsTitle {
        border-bottom: 1px solid rgba(0, 0, 0, 0.09);
        margin-bottom: 25px;
      }

      .goodsTitle ~ .goodsTitle {
        margin-top: 20px;
      }

      .goodsTitle .title {
        border-bottom: 2px solid #1890ff;
        // padding: 0 8px 12px 5px;
        color: #000;
        font-size: 14px;
      }

      .goodsTitle .icons {
        font-size: 15px;
        margin-right: 8px;
        color: #999;
      }

      .add {
        font-size: 12px;
        color: #1890ff;
        padding: 0 12px;
        cursor: pointer;
      }

      .radio {
        margin-right: 20px;
      }

      .upLoad {
        width: 58px;
        height: 58px;
        line-height: 58px;
        border: 1px dotted rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        background: rgba(0, 0, 0, 0.02);
      }

      .iconfont {
        color: #898989;
      }

      .pictrue {
        width: 60px;
        height: 60px;
        border: 1px dotted rgba(0, 0, 0, 0.1);
        margin-right: 10px;
      }

      .pictrue img {
        width: 100%;
        height: 100%;
      }
    }
  }
}

.left_box {
  .left_cont {
    margin-bottom: 12px;
    cursor: pointer;
    padding: 14px 24px;
  }
}

.left-wrapper {
  // height 904px
  background: #fff;
  border-right: 1px solid #dcdee2;
}

.on {
  color: #1890ff;
  background-color: #f0faff;
  border-right: 2px solid #1890ff;
}

.pciframe {
  margin-left: 20px;
  width: 430px;
  height: 280px;
  background: #FFFFFF;
  border: 1px solid #EEEEEE;
  border-radius: 16px;
  position: relative;

  img {
    width: 430px;
    height: 280px;
    border-radius: 10px;
  }

  .pciframe-box {
    width: 430px;
    height: 280px;
    background: rgba(0, 0, 0, 0);
    // border: 1px solid #EEEEEE;
    border-radius: 10px;
  }

  .box3_sile {
    position: absolute;
    top: 34px;
    right: 85px;
    width: 92px;
    height: 201px;
    background-color: #fff;
  }

  .pcmoddile_goods {
    position: absolute;
    top: 49px;
    width: 429px;
    height: 160px;
    left: 0px;
    background-color: #fff;
  }

  .pcswiperimg_goods {
    width: 399px;
    height: 140px;
    background-color: #f5f5f5;

    img {
      width: 100%;
      height: 100%;
      border-radius: 0px;
    }
  }
}

.content {
  // width 510px;
  max-width: 730px;

  .right-box {
    margin-left: 40px;
  }
}

.title-text {
  padding: 0 0 0px 16px;
  color: #999;
  font-size: 12px;
  margin-top: 10px;
}

.hot_imgs {
  margin-bottom: 20px;

  .title {
    font-size: 14px;
  }

  .list-box {
    .item {
      position: relative;
      display: flex;
      margin-top: 20px;

      .move-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 80px;
        cursor: move;
        color: #D8D8D8;
      }

      .img-box {
        position: relative;
        width: 80px;
        height: 80px;

        img {
          width: 100%;
          height: 100%;
        }
      }

      .info {
        flex: 1;
        margin-left: 22px;

        .info-item {
          display: flex;
          align-items: center;
          margin-bottom: 10px;

          span {
            // width 40px
            font-size: 13px;
          }

          .input-box {
            flex: 1;
          }
        }
      }

      .delect-btn {
        position: absolute;
        right: -12px;
        top: -12px;
        color: #999999;

        .iconfont {
          font-size: 28px;
          color: #999;
        }
      }
    }
  }

  .add-btn {
    margin-top: 20px;
  }
}

.iconfont {
  color: #DDDDDD;
  font-size: 28px;
}

.logoimg {
  position: absolute;
  top: 19px;
  left: 4px;
  width: 60px;
  height: 25px;
  border-radius: 0;

  img {
    width: 100%;
    height: 100%;
    border-radius: 0px !important;
  }
}

.img-boxs {
  position: relative;
  width: 76px;
  height: 76px;
  background: rgba(0, 0, 0, 0);
  border-radius: 6px;
  overflow: hidden;
  margin-top: 18px;

  img {
    width: 100%;
    height: 100%;
  }

  .img_font {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 24px;
    background: #000000;
    opacity: 0.4;
    border-radius: 0px 0px 6px 6px;
  }

  .img_fonts {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 24px;
    border-radius: 0px 0px 6px 6px;
    color: #FFFFFF;
    text-align: center;
    line-height: 24px;
  }
}

.item {
  border: 1px dashed #CCC;
  padding: 15px 15px 10px 0px;
}

.title {
  border-left: 2px solid #1890FF;
  padding-left: 10px;
  font-weight: bold;
  margin-bottom: 10px;
}

/deep/.ivu-form-item-content {
  margin-left: 0px !important;
}

/deep/.i-layout-page-header {
  height: 66px;
  background-color: #fff;
  border-bottom: 1px solid #e8eaec;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
</style>
