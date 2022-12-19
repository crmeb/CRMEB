<template>
  <div class="users">
    <Card :bordered="false" dis-hover>
      <div class="acea-row row-top">
        <div class="left" :style="colorStyle">
          <div class="header" :class="userData.status == 3 ? 'bgColor' : ''">
            <div class="top acea-row row-between-wrapper">
              <div class="picTxt acea-row row-middle">
                <div class="pictrue">
                  <img src="../../../assets/images/f.png" />
                </div>
                <div class="txt">
                  <div class="name">用户名称用户名称</div>
                  <div class="phone acea-row row-center-wrapper">
                    绑定手机号<span class="iconfont iconjinru"></span>
                  </div>
                </div>
              </div>
              <div class="acea-row row-middle">
                <div class="news">
                  <span class="iconfont icons-kefu"></span>
                  <div class="num">6</div>
                </div>
                <span class="iconfont iconshezhi"></span>
              </div>
            </div>
            <div class="center acea-row row-around">
              <div class="item">
                <div class="num">0.00</div>
                <div class="font">我的余额</div>
              </div>
              <div class="item">
                <div class="num">65749</div>
                <div class="font">当前积分</div>
              </div>
              <div class="item">
                <div class="num">25</div>
                <div class="font">优惠券</div>
              </div>
            </div>
            <div class="bottom acea-row row-between-wrapper" v-if="userData.status == 1">
              <div>会员到期 2022-12-31</div>
              <div class="renew">立即续费<span class="iconfont iconjinru"></span></div>
            </div>
            <div class="bottomB acea-row row-between" v-if="userData.status == 3">
              <div class="vip"><img src="../../../assets/images/member01.png" />开通会员VIP</div>
              <div>会员可享多项权益<span class="iconfont iconjinru"></span></div>
            </div>
          </div>
          <div class="member acea-row row-between-wrapper" v-if="userData.status == 2">
            <div class="text">
              <div class="title">会员可享多项权益</div>
              <div>会员剩余360天</div>
            </div>
            <div class="bnt">立即续费</div>
          </div>
          <div class="orderCenter on dotted" :class="current == 4 ? 'solid' : ''" @click="currentShow(4)">
            <div class="title acea-row row-between-wrapper">
              <div>订单中心</div>
              <div class="all">查看全部<span class="iconfont iconjinru"></span></div>
            </div>
            <div class="list acea-row row-around">
              <div class="item">
                <div class="iconfont" :class="order.dfk"></div>
                <div>待付款</div>
              </div>
              <div class="item">
                <div class="iconfont" :class="order.dfh"></div>
                <div>待发货</div>
              </div>
              <div class="item">
                <div class="iconfont" :class="order.dsh"></div>
                <div>待收货</div>
              </div>
              <div class="item">
                <div class="iconfont" :class="order.dpj"></div>
                <div>待评价</div>
              </div>
              <div class="item">
                <div class="iconfont" :class="order.sh"></div>
                <div>售后/退款</div>
              </div>
            </div>
          </div>
          <div class="carousel dotted" :class="current == 1 ? 'solid' : ''" @click="currentShow(1)">
            <swiper :options="swiperOption" class="swiperimg" v-if="userData.routine_my_banner.length">
              <swiper-slide class="swiperimg" v-for="(item, index) in userData.routine_my_banner" :key="index">
                <img :src="item.pic" />
              </swiper-slide>
              <div class="swiper-pagination" slot="pagination"></div>
            </swiper>
            <div v-else class="default">暂无广告数据</div>
          </div>
          <div class="orderCenter service dotted" :class="current == 2 ? 'solid' : ''" @click="currentShow(2)">
            <div class="title acea-row row-between-wrapper">
              <div>我的服务</div>
            </div>
            <div class="list acea-row">
              <div class="item" v-for="(item, index) in MyMenus" :key="index" v-if="item.pic">
                <div class="pictrue">
                  <img :src="item.pic" v-if="item.pic && item.pic != ''" />
                  <span class="iconfont icontupian1" v-else></span>
                </div>
                <div>{{ item.name ? item.name : '服务名称' }}</div>
              </div>
            </div>
          </div>
          <div class="orderCenter service dotted" :class="current == 3 ? 'solid' : ''" @click="currentShow(3)">
            <div class="title acea-row row-between-wrapper">
              <div>商家管理</div>
            </div>
            <div class="list acea-row">
              <div class="item" v-for="(item, index) in storeMenu" :key="index" v-if="item.pic">
                <div class="pictrue">
                  <img :src="item.pic" />
                </div>
                <div>{{ item.name }}</div>
              </div>
            </div>
          </div>
        </div>
        <div class="right">
          <div class="title">页面设置</div>
          <div class="c_row-item">
            <Col class="label" span="4"> 页面风格： </Col>
            <Col span="20" class="slider-box">
              <RadioGroup v-model="userData.status">
                <Radio :label="1">
                  <Icon></Icon>
                  <span>样式1</span>
                </Radio>
                <Radio :label="2">
                  <Icon></Icon>
                  <span>样式2</span>
                </Radio>
                <Radio :label="3">
                  <Icon></Icon>
                  <span>样式3</span>
                </Radio>
              </RadioGroup>
            </Col>
          </div>
          <div class="c_row-item" v-if="current == 4">
            <Col class="label" span="4"> 订单中心： </Col>
            <Col span="20" class="slider-box">
              <RadioGroup v-model="userData.order_status" @on-change="orderStyle">
                <Radio :label="1">
                  <Icon></Icon>
                  <span>样式1</span>
                </Radio>
                <Radio :label="2">
                  <Icon></Icon>
                  <span>样式2</span>
                </Radio>
                <Radio :label="3">
                  <Icon></Icon>
                  <span>样式3</span>
                </Radio>
                <Radio :label="4">
                  <Icon></Icon>
                  <span>样式4</span>
                </Radio>
                <Radio :label="5">
                  <Icon></Icon>
                  <span>样式5</span>
                </Radio>
              </RadioGroup>
            </Col>
          </div>
          <div class="c_row-item acea-row row-top" v-if="current == 1">
            <Col class="label" span="4"> 广告位： </Col>
            <Col span="20" class="slider-box">
              <i-switch v-model="userData.my_banner_status" style="margin-bottom: 12px" />
              <div class="info">建议尺寸：690 * 240px，拖拽图片可调整图片显示顺序哦，最多添加五张</div>
              <uploadPic :listData="userData.routine_my_banner" :type="2"></uploadPic>
            </Col>
          </div>
          <div class="c_row-item acea-row row-top" v-if="current == 2">
            <Col class="label" span="4"> 我的服务： </Col>
            <Col span="20" class="slider-box">
              <div class="info">建议尺寸：86 * 86px，拖拽图片可调整图片显示顺序哦</div>
              <uploadPic :listData="MyMenus"></uploadPic>
            </Col>
          </div>
          <div class="c_row-item acea-row row-top" v-if="current == 3">
            <Col class="label" span="4"> 商家管理： </Col>
            <Col span="20" class="slider-box">
              <div class="info">建议尺寸：86 * 86px，拖拽图片可调整图片显示顺序哦，最多添加五张</div>
              <uploadPic :listData="storeMenu" :type="1"></uploadPic>
            </Col>
          </div>
        </div>
      </div>
    </Card>
  </div>
</template>

<script>
import { getMember, memberSave } from '@/api/diy';
import uploadPic from './components/uploadPic';
export default {
  name: 'users',
  components: {
    uploadPic,
  },
  props: {},
  data() {
    return {
      swiperOption: {
        //显示分页
        pagination: {
          el: '.swiper-pagination',
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
      userData: {
        my_banner_status: true,
        routine_my_banner: [],
        routine_my_menus: [],
        status: '',
        order_status: '',
      },
      MyMenus: [{ pic: '', url: '', name: '', sort: 1, status: 1 }],
      storeMenu: [{ pic: '', url: '', name: '', sort: 1, status: 1 }],
      current: 1,
      colorStyle: '',
      order: {},
      order01: {
        dfk: 'icondaifukuan1',
        dfh: 'icondaifahuo1',
        dsh: 'icondaishouhuo1',
        dpj: 'icondaipingjia1',
        sh: 'iconshouhou_tuikuan',
      },
      order02: {
        dfk: 'icondaifukuan-lan',
        dfh: 'icondaifahuo-lan',
        dsh: 'icondaishouhuo-lan',
        dpj: 'icondaipingjia-lan',
        sh: 'iconshouhou-tuikuan-lan',
      },
      order03: {
        dfk: 'icondaifukuan-ju',
        dfh: 'icondaifahuo-ju',
        dsh: 'icondaishouhuo-ju',
        dpj: 'icondaipingjia-ju',
        sh: 'iconshouhou-tuikuan-ju',
      },
      order04: {
        dfk: 'icondaifukuan-fen',
        dfh: 'icondaifahuo-fen',
        dsh: 'icondaishouhuo-fen',
        dpj: 'icondaipingjia-fen',
        sh: 'icona-shouhoutuikuan-fen',
      },
      order05: {
        dfk: 'icondaifukuan-lv',
        dfh: 'icondaifahuo-lv',
        dsh: 'icondaishouhuo-lv',
        dpj: 'icondaipingjia-lv',
        sh: 'iconshouhou-tuikuan-lv',
      },
    };
  },
  created() {
    this.getInfo();
  },
  methods: {
    currentShow(type) {
      this.current = type;
    },
    switchOrder(status) {
      switch (status) {
        case 1:
          this.order = this.order01;
          break;
        case 2:
          this.order = this.order02;
          break;
        case 3:
          this.order = this.order03;
          break;
        case 4:
          this.order = this.order04;
          break;
        case 5:
          this.order = this.order05;
          break;
        default:
          this.order = this.order01;
          break;
      }
    },
    orderStyle(e) {
      this.switchOrder(e);
    },
    getInfo() {
      let green =
        '--view-theme: #42CA4D;--view-priceColor:#FF7600;--view-minorColor:rgba(108, 198, 94, 0.5);--view-minorColorT:rgba(66, 202, 77, 0.1);--view-bntColor:#FE960F;';
      let red =
        '--view-theme: #e93323;--view-priceColor:#e93323;--view-minorColor:rgba(233, 51, 35, 0.5);--view-minorColorT:rgba(233, 51, 35, 0.1);--view-bntColor:#FE960F;';
      let blue =
        '--view-theme: #1DB0FC;--view-priceColor:#FD502F;--view-minorColor:rgba(58, 139, 236, 0.5);--view-minorColorT:rgba(9, 139, 243, 0.1);--view-bntColor:#22CAFD;';
      let pink =
        '--view-theme: #FF448F;--view-priceColor:#FF448F;--view-minorColor:rgba(255, 68, 143, 0.5);--view-minorColorT:rgba(255, 68, 143, 0.1);--view-bntColor:#282828;';
      let orange =
        '--view-theme: #FE5C2D;--view-priceColor:#FE5C2D;--view-minorColor:rgba(254, 92, 45, 0.5);--view-minorColorT:rgba(254, 92, 45, 0.1);--view-bntColor:#FDB000;';
      getMember().then((res) => {
        this.userData.status = res.data.status;
        this.userData.order_status = res.data.order_status;
        this.userData.my_banner_status = res.data.my_banner_status;
        let storeMenu = [];
        let myMenu = [];
        this.switchOrder(res.data.order_status);
        switch (res.data.color_change) {
          case 1:
            this.colorStyle = blue;
            break;
          case 2:
            this.colorStyle = green;
            break;
          case 3:
            this.colorStyle = red;
            break;
          case 4:
            this.colorStyle = pink;
            break;
          case 5:
            this.colorStyle = orange;
            break;
          default:
            this.colorStyle = red;
            break;
        }
        res.data.routine_my_banner.forEach((el, index, arr) => {
          if (el.pic.length) {
            el.pic = el.pic[0];
          }
        });
        if (res.data.routine_my_banner.length) {
          this.userData.routine_my_banner = res.data.routine_my_banner;
        } else {
          this.userData.routine_my_banner = [];
        }
        res.data.routine_my_menus.forEach((el, index, arr) => {
          if (el.pic.length) {
            el.pic = el.pic[0];
          }
          if (
            el.url == '/pages/admin/order/index' ||
            el.url == '/pages/admin/order_cancellation/index' ||
            el.name == '客服接待'
          ) {
            storeMenu.push(el);
          } else {
            myMenu.push(el);
          }
        });
        if (myMenu.length) {
          this.MyMenus = myMenu;
        }
        if (storeMenu.length) {
          this.storeMenu = storeMenu;
        }
      });
    },
    onSubmit() {
      this.userData.routine_my_menus = this.MyMenus.concat(this.storeMenu);
      this.$emit('parentFun', true);
      memberSave(this.userData)
        .then((res) => {
          this.$emit('parentFun', false);
          this.$Message.success(res.msg);
        })
        .catch((err) => {
          this.$Message.error(err.msg);
          this.$emit('parentFun', false);
        });
    },
  },
};
</script>
<style scoped lang="stylus">
/* 定义滑块 内阴影+圆角 */
::-webkit-scrollbar-thumb {
  -webkit-box-shadow: inset 0 0 6px #ddd;
}

::-webkit-scrollbar {
  width: 4px !important; /* 对垂直流动条有效 */
}

.default {
  background-color: #fff;
  text-align: center;
  height: 50px;
  line-height: 50px;
  border-radius: 8px;
}

.bgColor {
  background-color: unset !important;

  .top {
    .picTxt {
      .txt {
        .name {
          color: #333 !important;
        }

        .phone {
          color: rgba(51, 51, 51, 0.8) !important;
          background-color: rgba(51, 51, 51, 0.13) !important;
        }
      }
    }

    .news {
      .iconfont {
        color: #333 !important;
      }

      .num {
        background-color: var(--view-theme) !important;
        color: #fff !important;
      }
    }

    .iconshezhi {
      color: #333 !important;
    }
  }

  .center {
    color: rgba(51, 51, 51, 0.7) !important;

    .num {
      color: #333 !important;
    }
  }
}

.dotted {
  border: 1px dashed #2d8cf0;
  cursor: pointer;
}

.solid {
  border: 1px solid #2d8cf0 !important;
}

.c_row-item {
  .slider-box {
    .info {
      font-size: 13px;
      color: #999999;
    }
  }
}

.bottomB {
  width: 270px;
  height: 62px;
  background: #343A48;
  border-radius: 8px 8px 0px 0px;
  padding: 11px 15px 0 15px;
  margin: 10px auto 0 auto;
  color: #BBBBBB;
  font-size: 13px;
  z-index: 0;
  position: relative;

  .iconfont {
    font-size: 11px;
  }

  .vip {
    font-size: 13px;
    font-weight: bold;
    color: #F8D5A8;

    img {
      width: 18px;
      height: 18px;
      display: inline-block;
      vertical-align: middle;
      margin-right: 9px;
    }
  }
}

.member {
  background-image: url('../../../assets/images/user_vip.png');
  background-repeat: no-repeat;
  background-size: 100%;
  width: 270px;
  height: 48px;
  margin: -42px auto 0 auto;
  position: relative;
  z-index: 9;
  margin-bottom: 13px;
  padding: 0 20px 0 45px;

  .text {
    color: rgba(174, 90, 42, 0.8);
    font-size: 12px;

    .title {
      font-size: 12px;
    }
  }

  .bnt {
    width: 70px;
    height: 23px;
    background-color: #fff;
    border-radius: 14px;
    color: #AE5A2A;
    text-align: center;
    line-height: 26px;
    font-size: 12px;
  }
}

.carousel {
  margin: 10px 18px 0 18px;

  .swiperimg {
    width: 100%;
    height: 63px;
    border-radius: 8px;

    img {
      width: 100%;
      height: 100%;
      border-radius: 8px;
    }
  }
}

.swiper-pagination-fraction, .swiper-pagination-custom, .swiper-container-horizontal > .swiper-pagination-bullets {
  bottom: 2px;
}

/deep/.swiper-pagination-bullet {
  width: 4px;
  height: 4px;
}

/deep/.swiper-pagination-bullet-active {
  background: #fff;
}

.users {
  .left {
    background: #F7F7F7;
    width: 310px;
    height: 550px;
    overflow-x: hidden;
    overflow-y: auto;
    padding-bottom: 1px;
    border-radius: 10px;
    margin-right: 30px;
    border: 1px solid #eee;

    .header {
      background-color: var(--view-theme);
      background-image: url('../../../assets/images/user01.png');
      background-size: 100%;
      background-repeat: no-repeat;
      width: 100%;
      height: 150px;
      position: relative;
      margin-bottom: 14px;

      .top {
        padding: 19px 20px 0 20px;

        .picTxt {
          .pictrue {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 10px;

            img {
              width: 100%;
              height: 100%;
              border-radius: 50%;
            }
          }

          .txt {
            .name {
              font-size: 12px;
              color: #fff;
              font-weight: 600;

              img {
                width: 40px;
                height: 15px;
                margin-left: 7px;
                vertical-align: middle;
              }
            }

            .phone {
              width: 86px;
              height: 21px;
              border-radius: 13px;
              background-color: rgba(16, 55, 72, 0.2);
              font-size: 11px;
              color: #fff;
              margin-top: 4px;

              .iconfont {
                font-size: 11px;
              }
            }
          }
        }

        .news {
          position: relative;
          margin-right: 18px;

          .iconfont {
            font-size: 22px;
            color: #fff;
          }

          .num {
            position: absolute;
            width: 14px;
            height: 14px;
            background: #FFFFFF;
            border-radius: 50%;
            font-size: 9px;
            color: var(--view-theme);
            text-align: center;
            line-height: 14px;
            top: 3px;
            right: -4px;
          }
        }

        .iconshezhi {
          font-size: 22px;
          color: #fff;
        }
      }

      .center {
        text-align: center;
        color: rgba(255, 255, 255, 0.8);
        margin-top: 9px;

        .num {
          font-size: 15px;
          font-weight: 600;
          color: rgba(255, 255, 255, 1);
        }

        .font {
          font-size: 13px;
        }

        .item {
          &~.item {
            position: relative;

            &:before {
              position: absolute;
              content: '';
              width: 1px;
              height: 14px;
              background-color: rgba(255, 255, 255, 0.3);
              left: -36px;
              top: 50%;
              margin-top: -7px;
            }
          }
        }
      }

      .bottom {
        background-image: url('../../../assets/images/member.png');
        width: 287px;
        height: 34px;
        background-size: 100%;
        background-repeat: no-repeat;
        position: absolute;
        bottom: -6px;
        padding: 0 17px 0 44px;
        font-size: 13px;
        color: #905100;
        right: 8px;

        .renew {
          font-size: 12px;

          .iconjinru {
            font-size: 11px;
          }
        }
      }
    }

    .orderCenter {
      background: #FFFFFF;
      border-radius: 8px;
      margin: 0 18px 10px 18px;
      text-align: center;
      padding: 15px 0;

      &.on {
        position: relative;
        margin-top: -5px;
      }

      .title {
        padding: 0 15px;
        font-size: 13px;
        color: #282828;
        font-weight: 600px;

        .all {
          font-size: 12px;
          color: #666666;

          .iconfont {
            font-size: 12px;
            margin-left: 2px;
          }
        }
      }

      .list {
        margin-top: 10px;

        .item {
          font-size: 12px;
          color: #454545;

          .iconfont {
            font-size: 20px;
            color: var(--view-theme);
          }
        }
      }

      &.service {
        padding: 15px 0 0 0;
        margin-top: 10px;

        .list {
          .item {
            width: 25%;
            margin-bottom: 10px;

            .pictrue {
              width: 23px;
              height: 23px;
              margin: 0 auto 8px auto;
              font-size: 12px;

              img {
                width: 100%;
                height: 100%;
              }
            }
          }
        }
      }
    }
  }

  .right {
    width: 540px;

    /deep/.ivu-radio-wrapper {
      font-size: 13px;
      margin-right: 20px;
    }

    .title {
      font-size: 14px;
      color: rgba(0, 0, 0, 0.85);
      position: relative;
      font-weight: bold;

      &:before {
        content: '';
        position: absolute;
        width: 2px;
        height: 14px;
        background: #1890FF;
        top: 50%;
        margin-top: -7px;
        left: -8px;
      }
    }

    .c_row-item {
      margin-top: 24px;
    }
  }
}
</style>
