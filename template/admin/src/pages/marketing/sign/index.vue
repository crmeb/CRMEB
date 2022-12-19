<template>
  <div :style="bgcolors">
    <div class="box-wrapper">
      <div v-if="name == 'sign_day_num'" class="iframe" :bordered="false">
        <div class="iframe-box">
          <img v-if="bgimg == 1" src="../../../assets/images/bluesgin.png" />
          <img v-if="bgimg == 2" src="../../../assets/images/greesgin.png" />
          <img v-if="bgimg == 3" src="../../../assets/images/redsgin.png" />
          <img v-if="bgimg == 4" src="../../../assets/images/pinksgin.png" />
          <img v-if="bgimg == 5" src="../../../assets/images/oragesgin.png" />
        </div>
      </div>

      <div v-if="name == 'sign_day_num'" style="margin-left: 20px">
        <div class="table_box">
          <div type="flex">
            <div v-bind="grid">
              <div class="title">签到天数设置</div>
              <Button
                type="primary"
                icon="md-add"
                @click="groupAdd('添加数据')"
                style="margin-left: 14px; margin-top: 30px"
                >添加数据</Button
              >
            </div>
          </div>
          <div class="table">
            <Table
              :columns="columns1"
              :data="cmsList"
              ref="table"
              class="mt25"
              :loading="loading"
              highlight-row
              no-userFrom-text="暂无数据"
              no-filtered-userFrom-text="暂无筛选结果"
            >
              <template slot-scope="{ row, index }" slot="status">
                <i-switch
                  v-model="row.status"
                  :value="row.status"
                  :true-value="1"
                  :false-value="0"
                  @on-change="onchangeIsShow(row)"
                  size="large"
                >
                  <span slot="open">显示</span>
                  <span slot="close">隐藏</span>
                </i-switch>
              </template>
              <template slot-scope="{ row, index }" slot="action">
                <a @click="edit(row, '编辑')">编辑</a>
                <Divider type="vertical" />
                <a @click="del(row, '删除这条信息', index)">删除</a>
              </template>
            </Table>
          </div>
        </div>
      </div>
    </div>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import WangEditor from '@/components/wangEditor/index.vue';
import Setting from '@/setting';
import { getColorChange } from '@/api/diy';
import { mapState } from 'vuex';
import editFrom from '@/components/from/from';
import { productGetTempKeysApi, uploadType } from '@/api/product';
import {
  groupAllApi,
  groupDataListApi,
  groupSaveApi,
  openAdvSave,
  groupDataAddApi,
  groupDataHeaderApi,
  groupDataEditApi,
  groupDataSetApi,
  setAgreement,
} from '@/api/system';
import linkaddress from '@/components/linkaddress';
import { getCookies } from '@/libs/util';

export default {
  name: 'list',
  components: {
    editFrom,
    linkaddress,
    WangEditor,
  },
  computed: {
    bgcolors() {
      return {
        '--color-theme': this.bgCol,
      };
    },
    labelWidth() {
      return this.isMobile ? undefined : 120;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
    ...mapState('admin/layout', ['menuCollapse']),
  },
  data() {
    return {
      formValidate: {
        content: '',
      },
      ruleValidate: {},
      myConfig: {
        autoHeightEnabled: false, // 编辑器不自动被内容撑高
        initialFrameHeight: 500, // 初始容器高度
        initialFrameWidth: '100%', // 初始容器宽度
        UEDITOR_HOME_URL: '/admin/UEditor/',
        serverUrl: '',
      },
      a: 0, //判断的隐私协议
      guide: 0,
      bgimg: 0,
      columns1: [],
      bgCol: '',
      name: 'sign_day_num',
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      loading: false,
      sginList: [],
      progress: 0, // 进度条默认0
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
      url: '',
      BaseURL: Setting.apiBaseURL.replace(/adminapi/, ''),
      pageId: 55,
      theme3: 'light',
      tabList: [],
      upload_type: '', //视频上传类型 1 本地上传 2 3 4 OSS上传
      uploadData: {}, // 上传参数
      lastObj: {
        add_time: '',
        comment: '',
        gid: '',
        id: '',
        img: '',
        link: '',
        sort: '',
        status: 1,
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
      groupAll: [],
      activeIndex: 0,
      sortName: null,
      activeIndexs: 0,
      cmsList: [],
      loadingExist: false,
      formItem: {
        time: '',
        type: 'pic',
        status: 1,
        value: [],
        video_link: '',
      },
      fileUrl: Setting.apiBaseURL + '/file/upload',
      cardUrl: Setting.apiBaseURL + '/file/upload/1',
      header: {},
      type: 0,
      upload: {
        videoIng: false, // 是否显示进度条；
      },
    };
  },
  created() {
    this.color();
    this.info();
    this.guide = 0;
    this.a = 0;
    this.url = '';
    this.getListHeader();
  },
  mounted() {},
  methods: {
    color() {
      getColorChange('color_change').then((res) => {
        switch (res.data.status) {
          case 1:
            this.bgCol = '#3875EA';
            this.bgimg = 1;
            break;
          case 2:
            this.bgCol = '#00C050';
            this.bgimg = 2;
            break;
          case 3:
            this.bgCol = '#E93323';
            this.bgimg = 3;
            break;
          case 4:
            this.bgCol = '#FF448F';
            this.bgimg = 4;
            break;
          case 5:
            this.bgCol = '#FE5C2D';
            this.bgimg = 5;
            break;
        }
      });
    },
    // 添加表单
    groupAdd() {
      this.$modalForm(groupDataAddApi({ gid: this.pageId, config_name: this.name }, 'setting/group_data/create')).then(
        () => {
          this.url = this.BaseURL + 'pages/users/user_sgin/index';
          this.info();
        },
      );
    },
    info() {
      groupDataListApi({ config_name: this.name }, 'setting/group_data')
        .then(async (res) => {
          this.tabList = res.data;
          this.cmsList = res.data.list;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
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
    save() {
      if (this.a == 1) {
        this.onsubmit('formValidate');
      } else if (this.guide == 2) {
        this.formItem.value = this.tabList.list;
        openAdvSave(this.formItem).then((res) => {
          this.$Message.success(res.msg);
        });
      } else {
        this.loadingExist = true;
        groupSaveApi({
          gid: this.pageId,
          config_name: this.name,
          data: this.tabList.list,
        })
          .then((res) => {
            this.loadingExist = false;
            this.$Message.success(res.msg);
          })
          .catch((err) => {
            this.loadingExist = false;
            this.$Message.error(err.msg);
          });
      }
    },
    getListHeader() {
      this.loading = true;
      groupDataHeaderApi({ config_name: this.name }, 'setting/sign_data/header')
        .then((res) => {
          let data = res.data;
          let header = data.header;
          let index = [];
          this.columns1 = header;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    // 编辑
    edit(row) {
      this.$modalForm(
        groupDataEditApi({ gid: this.pageId, config_name: this.name }, 'setting/group_data/' + row.id + '/edit'),
      ).then(() => {
        this.info();
        this.url = this.BaseURL + 'pages/users/user_sgin/index';
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: 'setting/group_data/' + row.id,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.info();
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 修改是否显示
    onchangeIsShow(row) {
      groupDataSetApi('setting/group_data/set_status/' + row.id + '/' + row.status)
        .then(async (res) => {
          this.url = this.BaseURL + '/pages/users/user_sgin/index';
          this.$Message.success(res.msg);
          this.info();
        })
        .catch((res) => {
          this.url = this.BaseURL + '/pages/users/user_sgin/index';
          this.$Message.error(res.msg);
        });
    },
    getGroupAll() {
      groupAllApi()
        .then(async (res) => {
          this.groupAll = res.data;
          this.sortName = res.data[0].config_name;
          this.pageId = res.data[0].id;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 提交数据
    onsubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          setAgreement(this.formValidate)
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
  },
};
</script>

<style scoped lang="stylus">
/deep/ .ivu-menu-vertical .ivu-menu-item-group-title {
  display: none;
}

/deep/ .ivu-menu-vertical.ivu-menu-light:after {
  display: none;
}

/deep/.ivu-form-item-content {
  margin-left: 0px !important;
}

.nofont {
  text-align: center;
  line-height: 123px;
}

.nofonts {
  text-align: center;
  line-height: 105px;
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

.form {
  .goodsTitle {
    margin-bottom: 25px;
  }

  .goodsTitle ~ .goodsTitle {
    margin-top: 20px;
  }

  .goodsTitle .title {
    border-bottom: 2px solid #1890ff;
    padding: 0 8px 12px 5px;
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

.agreement-box {
  width: 310px;
  height: 550px;
  border-radius: 10px;
  background: rgba(0, 0, 0, 0);
  border: 1px solid #EEEEEE;
  opacity: 1;
  position: relative;

  .template {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    border-radius: 10px;
    background-color: #817e81;
  }

  .htmls_box {
    font-size: 12px;
    width: 259px;
    height: 430px;
    border-radius: 10px;
    background-color: #fff;
    position: absolute;
    top: 58px;
    left: 26px;

    .htmls_top {
      position: absolute;
      top: 8px;
      left: 0;
      height: 34px;
      text-align: center;
      width: 100%;
      line-height: 35px;
      font-weight: 600;
      font-size: 20px;
    }

    .htmls_font {
      position: absolute;
      bottom: 0;
      left: 0;
      padding: 15px 15px;
      text-align: center;
      width: 100%;

      div {
        height: 35px;
        line-height: 35px;
        border-radius: 20px;
      }

      .ok {
        background-color: #f33316;
        color: #FFFFFF;
      }
    }

    .htmls {
      position: absolute;
      background-color: #fff;
      top: 50px;
      left: 0;
      width: 259px;
      height: 281px;
      border-radius: 4px;
      overflow: auto;
      padding: 5px 15px;
      word-break: break-word;
    }

    .htmls::-webkit-scrollbar {
      display: none;
    }
  }
}

.Bbox {
  width: 495px;
  display: flex;
  flex-wrap: wrap;
}

.item {
  margin-right: 15px;
  border: 1px dashed #dbdbdb;
  padding-bottom: 10px;
  padding-right: 15px;
  padding-top: 20px;
}

.items {
  margin-right: 15px;
  border: 1px dashed #dbdbdb;
  padding-bottom: 10px;
  padding-top: 15px;
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

        .input-box {
          flex: 1;
        }
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

.table {
  width: 700px;
  color: #515a6e;
  font-size: 14px;
  background-color: #fff;
  margin-left: 20px;
}

.contents {
  width: 150px;

  .right-box {
    margin-left: 40px;
  }

  .title-text {
    width: 500px;
  }
}

.pciframe {
  margin-left: 20px;
  width: 430px;
  height: 280px;
  background: #FFFFFF;
  border: 1px solid #EEEEEE;
  border-radius: 13px;
  position: relative;

  img {
    width: 100%;
    height: 100%;
  }

  .pciframe-box {
    background: rgba(0, 0, 0, 0);
    // border: 1px solid #EEEEEE;
    border-radius: 4px;
  }

  .pcmoddile_goods {
    position: absolute;
    top: 69px;
    width: 171px;
    height: 140px;
    border-top-left-radius: 2px;
    border-bottom-left-radius: 2px;
    left: 65px;
    background-color: #fff;
  }

  .pcswiperimg_goods {
    height: 140px;
    background-color: #f5f5f5;

    img {
      width: 100%;
      height: 100%;
    }
  }
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

.swiperimg {
  width: 310px;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;

  img {
    width: 100%;
    height: 100%;
  }
}

.swiperimg_goods {
  width: 284px;
  height: 124px;
  border-radius: 4px;
  line-height: 99px;
  text-align: center;
  background-color: #f5f5f5;

  img {
    width: 100%;
    height: 100%;
  }
}

.title {
  padding: 0 0 13px 0;
  font-weight: bold;
  font-size: 15px;
  border-left: 2px solid #1890FF;
  height: 23px;
  padding-left: 10px;
}

.title-text {
  padding: 0 0 0px 16px;
  color: #999;
  font-size: 12px;
  margin-top: 10px;
}

.content {
  // width 510px;
  .right-box {
    margin-left: 40px;
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

.iframe {
  margin-left: 20px;
  position: relative;
  width: 310px;
  // height: 550px;
  background: #FFFFFF;
  border: 1px solid #EEEEEE;
  opacity: 1;
  border-radius: 10px;
}

.moddile {
  position: absolute;
  width: 310px;
  height: 550px;
  top: 0px;
  opacity: 0;
  left: 0px;
  border-radius: 4px;
}

.moddile_box {
  position: absolute;
  top: 0px;
  width: 310px;
  height: 115px;
  border-top-left-radius: 4px;
  border-top-right-radius: 4px;
  left: 0px;
  background-color: #f5f5f5;
}

.moddile_goods {
  position: absolute;
  top: 12px;
  width: 284px;
  height: 124px;
  /* border-top-left-radius: 10px; */
  /* border-top-right-radius: 10px; */
  border-radius: 5px;
  left: 13px;
  line-height: 99px;
  text-align: center;
  background-color: #f5f5f5;
}

.iframe-box {
  width: 310px;
  height: 100%;
  border-radius: 10px;
  // margin: 30px 15px 0px 15px
  background: rgba(0, 0, 0, 0);
  border: 1px solid #EEEEEE;
  opacity: 1;

  img {
    width: 100%;
    height: 100%;
  }
}

.left-wrapper {
  min-width: 213px;
  background: #fff;
  border-right: 1px solid #dcdee2;
}

.menu-item {
  position: relative;
  display: flex;
  justify-content: space-between;
  word-break: break-all;

  .icon-box {
    z-index: 3;
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    display: none;
  }

  &:hover .icon-box {
    display: block;
  }

  .right-menu {
    z-index: 10;
    position: absolute;
    right: -106px;
    top: -11px;
    width: auto;
    min-width: 121px;
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

.ivu-menu {
  z-index: auto;
}

.icondrag2 {
  font-size: 26px;
  color: #d8d8d8;
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

.upload-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  background: #ccc;
}

.iconfont {
  color: #DDDDDD;
  font-size: 28px;
}

.iframe-boxs::-webkit-scrollbar {
  display: none;
}

.sgin_iframe::-webkit-scrollbar {
  display: none;
}

.iframe-boxs {
  width: 310px;
  height: 550px;
  border-radius: 10px;
  background: rgba(0, 0, 0, 0);
  border: 1px solid #EEEEEE;
  opacity: 1;
  overflow: auto;

  .moneyBox {
    background-color: var(--color-theme);
    height: 414px;
    border-radius: 10px;

    .box1 {
      text-align: center;
      color: #FFFFFF;
      padding-bottom: 15px;

      .font1 {
        padding-top: 20px;
        // padding-bottom 15px
        font-size: 12px;
        opacity: 0.6;
      }

      .font2 {
        font-size: 30px;
        font-style: normal;
        opacity: 0.9;
      }
    }

    .moneyBox_content {
      background-color: #FFFFFF;
      height: 317px;
      border-radius: 4px;

      .box2 {
        display: flex;
        justify-content: space-around;
        height: 35px;
        line-height: 35px;
        margin-bottom: 10px;

        div:nth-child(1) {
          font-weight: bold;
          border-bottom: 2px solid var(--color-theme);
        }
      }

      .box3 {
        padding: 0px 10px;
        display: flex;
        justify-content: left;
        flex-wrap: wrap;

        .box3_box {
          width: 90px;
          height: 55px;
          border-radius: 9px;
          background-color: #f4f4f4;
          color: #888;
          margin-bottom: 10px;
          text-align: center;
          padding-top: 3px;
          font-size: 19px;
          margin-right: 3px;
          margin-left: 3px;

          .font {
            font-size: 11px;
            font-style: normal;
          }
        }

        .box3_box:nth-child(1) {
          width: 90px;
          height: 55px;
          border-radius: 9px;
          background-color: var(--color-theme);
          color: #FFFFFF;
          text-align: center;
          padding-top: 3px;
          margin-right: 3px;
          margin-left: 3px;
        }

        .other {
          line-height: 55px;
        }
      }

      .box4 {
        padding: 0px 10px;

        .tips {
          font-size: 14px;
          color: #333333;
          font-weight: 800;
          margin-bottom: 7px;
          margin-top: 10px;
        }

        .tips-samll {
          font-size: 12px;
          color: #333333;
          margin-bottom: 7px;

          p {
            margin: 2px 0px;
          }
        }
      }

      .box5 {
        font-size: 15px;
        width: 225px;
        height: 40px;
        border-radius: 25px;
        margin: 23px auto 0 auto;
        line-height: 40px;
        text-align: center;
        background-color: var(--color-theme);
        color: #FFFFFF;
      }
    }
  }
}

.bnt {
  // width 80px!important
}

/deep/.i-layout-page-header {
  height: 66px;
  background-color: #fff;
  border-bottom: 1px solid #e8eaec;
}

/deep/.ivu-page-header {
  border-bottom: unset;
  position: fixed;
  z-index: 9;
  width: 100%;
}

/deep/.i-layout-page-header {
  height: 66px;
  background-color: #fff;
  border-bottom: 1px solid #e8eaec;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.box-wrapper {
  display: flex;
  flex-wrap: nowrap;
  padding: 20px;
  background-color: #fff;
  border-radius: 5px;
  margin: 20px;
}

.iview-video-style {
  width: 100%;
  height: 180px;
  border-radius: 10px;
  background-color: #707070;
  margin-top: 10px;
  position: relative;
  overflow: hidden;
}

.iview-video-style .iconv {
  color: #fff;
  line-height: 180px;
  width: 50px;
  height: 50px;
  display: inherit;
  font-size: 26px;
  position: absolute;
  top: -74px;
  left: 50%;
  margin-left: -25px;
}

.iview-video-style .mark {
  position: absolute;
  width: 100%;
  height: 30px;
  top: 0;
  background-color: rgba(0, 0, 0, 0.5);
  text-align: center;
}
</style>
