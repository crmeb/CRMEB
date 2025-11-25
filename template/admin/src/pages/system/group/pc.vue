<template>
  <div>
    <!-- <div class="i-layout-page-header header-title">
      <span class="ivu-page-header-title mr20">{{ $route.meta.title }}</span>
      <div>
        <div style="float: right">
          <el-button class="bnt" type="primary" v-db-click @click="save">保存</el-button>
        </div>
      </div>
    </div> -->
    <pages-header ref="pageHeader" :title="$route.meta.title">
      <el-button class="bnt" type="primary" v-db-click @click="save">保存</el-button>
    </pages-header>
    <el-card :bordered="false" shadow="never" class="h100 mt16">
      <el-row class="box-wrapper">
        <el-col :xs="24" :sm="24" :md="6" :lg="3">
          <div class="left_box">
            <div class="left_cont" :class="pageId == 1 ? 'on' : ''" v-db-click @click="menu(1)">网站LOGO</div>
            <div
              class="left_cont"
              :class="pageId == 'pc_home_banner' ? 'on' : ''"
              v-db-click
              @click="menu('pc_home_banner')"
            >
              首页轮播图
            </div>
            <div class="left_cont" :class="pageId == 3 ? 'on' : ''" v-db-click @click="menu(3)">客服页面广告</div>
            <div class="left_cont" :class="pageId == 4 ? 'on' : ''" v-db-click @click="menu(4)">顶部菜单配置</div>
            <div class="left_cont" :class="pageId == 5 ? 'on' : ''" v-db-click @click="menu(5)">友情链接配置</div>
            <div class="left_cont" :class="pageId == 6 ? 'on' : ''" v-db-click @click="menu(6)">关于我们</div>
          </div>
        </el-col>
        <div style="display: flex; width: 83%">
          <el-col v-if="pageId == 1 || pageId == 'pc_home_banner'" class="pciframe" :bordered="false" shadow="never">
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
          </el-col>
          <el-col v-if="pageId == 3" class="pciframe" :bordered="false" shadow="never">
            <img src="../../../assets/images/kefu.png" class="pciframe-box" />
            <div class="box3_sile">
              <!-- {{formValidate}} -->
              <div v-html="formValidate.content"></div>
            </div>
          </el-col>
          <el-col v-if="pageId == 'pc_home_banner'">
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
                        <div class="img-box imgBoxs" v-db-click @click="modalPicTap('单选', index)">
                          <img :src="item.image" alt="" v-if="item.image" />
                          <div class="upload-box" v-else>
                            <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                          </div>
                          <div
                            class="delect-btn"
                            style="line-height: 0px"
                            v-db-click
                            @click.stop="bindDelete(item, index)"
                          >
                            <i class="el-icon-circle-close" style="font-size: 24px" />
                          </div>
                        </div>
                        <div class="info">
                          <div class="info-item">
                            <span>图片名称：</span>
                            <div class="input-box">
                              <el-input v-model="item.title" placeholder="请填写名称" />
                            </div>
                          </div>
                          <div class="info-item">
                            <span>链接地址：</span>
                            <!-- v-db-click @click="link(index)"-->
                            <div class="input-box">
                              <el-input v-model="item.url" placeholder="请填写链接" />
                            </div>
                          </div>
                        </div>
                      </div>
                    </draggable>
                    <div>
                      <el-dialog
                        :visible.sync="modalPic"
                        width="950px"
                        title="上传商品图"
                        :close-on-click-modal="false"
                      >
                        <uploadPictures
                          :isChoice="isChoice"
                          @getPic="getPic"
                          :gridBtn="gridBtn"
                          :gridPic="gridPic"
                          v-if="modalPic"
                        ></uploadPictures>
                      </el-dialog>
                    </div>
                  </div>
                  <template>
                    <div class="add-btn">
                      <el-button
                        type="primary"
                        ghost
                        style="width: 100px; height: 35px; background-color: var(--prev-color-primary); color: #ffffff"
                        v-db-click
                        @click="addBox"
                        >添加图片
                      </el-button>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </el-col>
          <el-col v-if="pageId == 1">
            <div class="content">
              <div class="right-box">
                <div class="hot_imgs">
                  <div class="title">页面设置</div>
                  <div class="title-text">建议尺寸：140px * 60px</div>
                  <div class="title-text">除LOGO图标外，页面其他内容仅供参考</div>
                  <div class="list-box">
                    <div class="img-boxs" v-db-click @click="modalPicTap('单选', 0)">
                      <img :src="pclogo" alt="" />
                      <div class="img_font"></div>
                      <div class="img_fonts">更换图片</div>
                    </div>
                    <div>
                      <el-dialog
                        :visible.sync="modalPic"
                        width="950px"
                        title="上传商品图"
                        :close-on-click-modal="false"
                      >
                        <uploadPictures
                          :isChoice="isChoice"
                          @getPic="getPic"
                          :gridBtn="gridBtn"
                          :gridPic="gridPic"
                          v-if="modalPic"
                        ></uploadPictures>
                      </el-dialog>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </el-col>
          <el-col v-if="pageId == 3" :xs="24" :sm="24" :md="12" :lg="14" style="margin-left: 40px">
            <div class="table_box">
              <el-row>
                <el-col v-bind="grid">
                  <div class="title">客服广告内容：</div>
                </el-col>
              </el-row>
              <div>
                <el-form
                  class="form"
                  ref="formValidate"
                  :model="formValidate"
                  :rules="ruleValidate"
                  :label-width="0"
                  :label-position="labelPosition"
                  @submit.native.prevent
                >
                  <el-form-item label="" prop="content" style="margin: 0px">
                    <WangEditor class="mt10" :content="content" @editorContent="getEditorContent"></WangEditor>
                  </el-form-item>
                </el-form>
              </div>
            </div>
          </el-col>
          <el-col v-if="pageId == 4" :xs="24" :sm="24" :md="12" :lg="14" style="margin-left: 40px">
            <div class="content">
              <div class="right-box">
                <div class="hot_imgs">
                  <div class="title">顶部菜单设置</div>
                  <div class="list-box">
                    <draggable class="dragArea list-group" :list="menuList" group="peoples" handle=".move-icon">
                      <div class="item" v-for="(item, index) in menuList" :key="index">
                        <div class="move-icon">
                          <span class="iconfont icondrag2"></span>
                        </div>
                        <div class="delect-btn" style="line-height: 0px" v-db-click @click.stop="menuDelete(index)">
                          <i class="el-icon-circle-close" style="font-size: 24px" />
                        </div>
                        <div class="info">
                          <div class="info-item">
                            <span>菜单名称：</span>
                            <div class="input-box">
                              <el-input v-model="item.title" placeholder="请填写名称" />
                            </div>
                          </div>
                          <div class="info-item">
                            <span>链接地址：</span>
                            <!-- v-db-click @click="link(index)"-->
                            <div class="input-box">
                              <el-input v-model="item.url" placeholder="请填写链接" />
                            </div>
                          </div>
                          <!-- <div class="info-item">
                            <span>是否需要登录：</span>
                            <div class="input-box">
                              <el-switch v-model="item.auth" active-value="1" inactive-value="0"> </el-switch>
                            </div>
                          </div> -->
                        </div>
                      </div>
                    </draggable>
                  </div>
                  <template>
                    <div class="add-btn">
                      <el-button
                        type="primary"
                        ghost
                        style="width: 100px; height: 35px; background-color: var(--prev-color-primary); color: #ffffff"
                        v-db-click
                        @click="addMenu"
                        >添加菜单
                      </el-button>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </el-col>
          <el-col v-if="pageId == 5" :xs="24" :sm="24" :md="12" :lg="14" style="margin-left: 40px">
            <div class="content">
              <div class="right-box">
                <div class="hot_imgs">
                  <div class="title">友情链接配置</div>
                  <div class="list-box">
                    <draggable class="dragArea list-group" :list="linkList" group="peoples" handle=".move-icon">
                      <div class="item" v-for="(item, index) in linkList" :key="index">
                        <div class="move-icon">
                          <span class="iconfont icondrag2"></span>
                        </div>
                        <div
                          class="delect-btn"
                          style="line-height: 0px"
                          v-db-click
                          @click.stop="linkDelete(item, index)"
                        >
                          <i class="el-icon-circle-close" style="font-size: 24px" />
                        </div>
                        <div class="info">
                          <div class="info-item">
                            <span>链接名称：</span>
                            <div class="input-box">
                              <el-input v-model="item.title" placeholder="请填写名称" />
                            </div>
                          </div>
                          <div class="info-item">
                            <span>链接地址：</span>
                            <!-- v-db-click @click="link(index)"-->
                            <div class="input-box">
                              <el-input v-model="item.url" placeholder="请填写链接" />
                            </div>
                          </div>
                        </div>
                      </div>
                    </draggable>
                  </div>
                  <template>
                    <div class="add-btn">
                      <el-button
                        type="primary"
                        ghost
                        style="width: 100px; height: 35px; background-color: var(--prev-color-primary); color: #ffffff"
                        v-db-click
                        @click="addLink"
                        >添加链接
                      </el-button>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </el-col>
          <el-col v-if="pageId == 6" :xs="24" :sm="24" :md="24" :lg="24" style="margin-left: 40px">
            <div class="content">
              <div class="right-box">
                <div class="hot_imgs">
                  <div class="title">关于我们-详情</div>
                  <WangEditor
                    style="width: 100%"
                    :content="formValidate.content"
                    @editorContent="getEditorContent"
                  ></WangEditor>
                </div>
              </div>
            </div>
          </el-col>
        </div>
      </el-row>
    </el-card>
    <!-- <div class="save">
			<el-button type="primary" v-db-click @click="save" >保存</el-button>
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
import { pcHomeMenusSave, pcHomeMenus } from '@/api/setting';
import draggable from 'vuedraggable';
import uploadPictures from '@/components/uploadPictures';
import linkaddress from '@/components/linkaddress';
import { getAgreements, setAgreements } from '@/api/system';

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
      content: '',
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
      menuList: [],
      linkList: [],
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
        UEDITOR_HOME_URL: '/UEditor/',
        serverUrl: '',
      },
      activeIndexs: 0,
    };
  },
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '120px';
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
    // 提交数据
    onsubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          setKfAdv(this.formValidate)
            .then(async (res) => {
              this.$message.success(res.msg);
            })
            .catch((res) => {
              this.$message.error(res.msg);
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
          this.content = data.content;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    getAboutUs(id) {
      this.formValidate.content = '';
      getAgreements(id).then((res) => {
        this.formValidate.content = res.data.content;
      });
    },
    setAboutUs(id) {
      if (this.formValidate.content == '') return this.$message.warning('请输入内容');
      let data = {
        id: id,
        content: this.formValidate.content,
        type: id,
        title: '关于我们',
      };

      setAgreements(data).then((res) => {
        this.$message.success(res.msg);
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
            this.$message.error(res.msg);
          });
      } else if (this.pageId == 1) {
        pcLogoApi('pc_logo').then((res) => {
          this.pclogo = res.data.value;
        });
      } else if (this.pageId == 3) {
        this.getKfAdv();
      } else if (this.pageId == 4) {
        this.getMenuList();
      } else if (this.pageId == 5) {
        this.getLinkList();
      } else if (this.pageId == 6) {
        this.getAboutUs(7);
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
          this.$message.warning('最多添加5张');
        } else {
          let obj = JSON.parse(JSON.stringify(this.lastObj));
          this.tabList.list.push(obj);
        }
      }
    },
    addMenu() {
      if (this.menuList.length >= 6) {
        return this.$message.warning('最多添加6个菜单');
      }
      this.menuList.push({
        title: '',
        url: '',
      });
    },
    addLink() {
      if (this.linkList.length >= 20) {
        return this.$message.warning('最多添加20个链接');
      }
      this.linkList.push({
        title: '',
        url: '',
      });
    },
    // 删除
    bindDelete(item, index) {
      if (this.tabList.list.length == 1) {
        this.lastObj = this.tabList.list[0];
      }
      this.tabList.list.splice(index, 1);
    },
    menuDelete(index) {
      console.log(index);
      this.menuList.splice(index, 1);
    },
    // 友情链接
    linkDelete(index) {
      this.linkList.splice(index, 1);
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
            this.$message.success(res.msg);
          })
          .catch((err) => {
            this.$message.error(err.msg);
          });
      } else if (this.pageId == 1) {
        pcLogoSave({ pc_logo: this.pclogo })
          .then((res) => {
            this.$message.success(res.msg);
          })
          .catch((err) => {
            this.$message.error(err.msg);
          });
      } else if (this.pageId == 3) {
        this.onsubmit('formValidate');
      } else if (this.pageId == 4) {
        this.saveMenu('pc_home_menus');
      } else if (this.pageId == 5) {
        this.saveMenu('pc_home_links');
      } else if (this.pageId == 6) {
        this.setAboutUs(7);
      }
    },
    getMenuList() {
      pcHomeMenus('pc_home_menus')
        .then((res) => {
          this.menuList = res.data.list;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    getLinkList() {
      pcHomeMenus('pc_home_links')
        .then((res) => {
          this.linkList = res.data.list;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    // 菜单保存
    saveMenu(config_name) {
      let data = {
        config_name: config_name,
        data: this.pageId == 5 ? this.linkList : this.menuList,
      };
      pcHomeMenusSave(data)
        .then((res) => {
          this.$message.success(res.msg);
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    link(index) {
      this.activeIndexs = index;
      this.$refs.linkaddres.modals = true;
    },
  },
};
</script>
<style>
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
<style scoped lang="scss">
::v-deep .ivu-menu-vertical .ivu-menu-item-group-title {
  display: none;
}

::v-deep .ivu-menu-vertical.ivu-menu-light:after {
  display: none;
}
.ivu-mt {
  min-height: calc(100vh - 280px);
}
.nofonts {
  text-align: center;
  line-height: 137px;
}

.save {
  width: 100%;
  margin: 0 auto;
  text-align: center;
  background-color: #fff;
  bottom: 0;
  padding: 16px;
  border-top: 3px solid #f5f7f9;
}

.imgBoxs {
  background-color: #cccccc;
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
    background-color: var(--prev-color-primary);
    color: #ffffff;
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
        border-bottom: 2px solid var(--prev-color-primary);
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
        color: var(--prev-color-primary);
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
    white-space: nowrap;
  }
}

.on {
  background-color: var(--prev-bg-main-color);
  color: var(--prev-color-primary);
  border-right: 2px solid var(--prev-color-primary);
}

.pciframe {
  margin-left: 20px;
  width: 430px;
  height: 280px;
  background: #ffffff;
  border: 1px solid #eeeeee;
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
    word-break: break-word;
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
      margin-top: 14px;

      .move-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        // height: 80px;
        cursor: move;
        color: #d8d8d8;
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
        color: #f56c6c;
        background-color: #fff;
        cursor: pointer;
        border-radius: 50%;
        .iconfont {
          font-size: 28px;
        }
      }
    }
  }

  .add-btn {
    margin-top: 14px;
  }
}

.iconfont {
  color: #dddddd;
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
    color: #ffffff;
    text-align: center;
    line-height: 24px;
  }
}

.item {
  border: 1px dashed #ccc;
  border-radius: 6px;
  padding: 15px 15px 10px 0px;
}

.title {
  border-left: 2px solid var(--prev-color-primary);
  padding-left: 10px;
  font-weight: bold;
  margin-bottom: 10px;
}

::v-deep .ivu-form-item-content {
  margin-left: 0px !important;
}

::v-deep .i-layout-page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
</style>
