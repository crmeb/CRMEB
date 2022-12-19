<template>
  <div class="chat-box">
    <div class="head-box">
      <div class="back" @click="goBack"><span class="iconfont iconfanhui"></span></div>
      <div class="title">{{ nickname ? nickname + '-' : '' }}对话详情</div>
    </div>
    <!-- 商品信息 -->
    <div class="broadcast-details_box" v-if="productId && productInfo.id">
      <div class="broadcast_details_img">
        <img :src="productInfo.image" />
      </div>
      <div class="broadcast_details_picBox">
        <div class="broadcast_details_tit" v-text="productInfo.store_name"></div>
        <div class="acea-row row-between">
          <div class="broadcast_details_pic">
            ￥{{ productInfo.price }}<span class="broadcast_details_pic_num">￥{{ productInfo.ot_price }}</span>
          </div>
          <div class="broadcast_details_btn" @click="sendProduct">发送客服</div>
        </div>
      </div>
    </div>
    <!-- 订单发送 -->
    <div class="broadcast_box" v-if="orderId && orderInfo.id">
      <div class="broadcast-details_num broadcast_num">
        <span>订单号：{{ orderInfo.order_id }}</span>
        <span>{{ orderInfo.add_time_y }} {{ orderInfo.add_time_h }}</span>
      </div>
      <div class="broadcast-details_box">
        <div class="broadcast_details_img">
          <img :src="cartInfo.productInfo.image" />
          <div class="broadcast_details_model">{{ orderInfo.cartInfo ? orderInfo.cartInfo.length : 0 }}件商品</div>
        </div>
        <div class="broadcast_details_picBox">
          <div class="broadcast_details_tit">
            {{ cartInfo.productInfo.store_name }}
          </div>
          <div class="acea-row row-between">
            <div class="broadcast_details_pic">
              ￥{{ cartInfo.productInfo.price
              }}<text class="broadcast_details_pic_num">￥{{ cartInfo.productInfo.ot_price }}</text>
            </div>
            <div class="broadcast_details_btn" @click="sendOrder">发送客服</div>
          </div>
        </div>
      </div>
    </div>
    <div class="chat-scroll-box">
      <vue-scroll
        :ops="ops"
        ref="scrollBox"
        @refresh-activate="handleActivate"
        @refresh-start="handleStart"
        @refresh-before-deactivate="handleBeforeDeactivate"
        @refresh-deactivate="handleDeactivate"
      >
        <div class="slot-refresh" slot="refresh-deactive"></div>
        <div class="slot-refresh" slot="refresh-beforeDeactive"></div>
        <div id="chatBox" class="chat" ref="chat" style="padding: 0.3rem">
          <div v-for="(item, index) in records" :key="index" :id="`chat_${item.id}`">
            <div class="day-box" v-if="item.show">{{ item.time }}</div>
            <div class="chat-item" :class="{ 'right-box': item.to_uid == toUid }">
              <img class="avatar" :src="item.avatar" mode="" />
              <!-- 消息 -->
              <div class="msg-box" v-if="item.msn_type == 1" v-html="item.msn"></div>
              <!-- 图片 -->
              <div class="img-box" v-if="item.msn_type == 3" v-viewer><img v-lazy="item.msn" mode="widthFix" /></div>
              <!-- 商品 -->
              <div class="product-box" v-if="item.msn_type == 5" @click="goProduct(item)">
                <img :src="item.productInfo.image" />
                <div class="info">
                  <div class="price"><span>￥</span>{{ item.productInfo.price }}</div>
                  <div class="name line2">{{ item.productInfo.store_name }}</div>
                </div>
              </div>
              <!-- 订单 -->
              <div class="order-box" v-if="item.msn_type == 6" @click="goOrderDetail(item)">
                <div class="title">订单ID: {{ item.orderInfo.order_id }}</div>
                <div class="info">
                  <img :src="item.orderInfo.cartInfo[0].productInfo.image" />
                  <div class="product-info">
                    <div class="name line2">{{ item.orderInfo.cartInfo[0].productInfo.store_name }}</div>
                    <div class="price">¥{{ item.orderInfo.cartInfo[0].productInfo.price }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </vue-scroll>
    </div>
    <div class="footer-box">
      <div class="words" v-if="userToken" @click="showWords">
        <Upload
          :show-upload-list="false"
          :action="fileUrl"
          :before-upload="beforeUpload"
          :data="uploadData"
          :headers="header"
          :multiple="true"
          :on-success="handleSuccess"
          :format="['jpg', 'jpeg', 'png', 'gif']"
          :on-format-error="handleFormatError"
          style="margin-top: 1px; display: inline-block"
        >
          <span class="iconfont icontupian3"></span>
        </Upload>
      </div>
      <div class="input-box">
        <Input v-model="con" placeholder="请输入内容" />
        <span class="iconfont iconfasong" @click="sendText" :class="{ isSend: isSend }"></span>
      </div>
      <div class="emoji" @click="openBox(1)"><span class="iconfont iconbiaoqing2"></span></div>
    </div>
    <!-- 表情 -->
    <div class="banner slider-banner" v-show="isSwiper">
      <swiper class="swiper-wrapper" ref="mySwiper" :options="swiperOptions">
        <swiper-slide v-for="(emojiList, index) in emojiGroup" :key="index">
          <i class="em" :class="emoji" v-for="emoji in emojiList" :key="emoji" @click="addEmoji(emoji)"></i>
        </swiper-slide>
      </swiper>
    </div>
  </div>
</template>

<script>
import Setting from '@/setting';
import { Socket } from '@/libs/socket';
import util from '@/libs/util';
import emojiList from '@/utils/emoji';
import { serviceList, serviceListApi, getOrderApi, chatListApi, productApi } from '@/api/kefu';
import { getCookies, removeCookies, setCookies } from '@/libs/util';
const chunk = function (arr, num) {
  num = num * 1 || 1;
  var ret = [];
  arr.forEach(function (item, i) {
    if (i % num === 0) {
      ret.push([]);
    }
    ret[ret.length - 1].push(item);
  });
  return ret;
};

export default {
  name: 'chat_mobile',
  data() {
    return {
      ops: {
        vuescroll: {
          mode: 'slide',
          enable: false,
          auto: false,
          autoLoadDistance: 0,
          pullRefresh: {
            enable: true,
            auto: false,
            autoLoadDistance: 0,
            tips: {
              deactive: '',
              active: '上拉加载更多',
              start: 'Loading...',
              beforeDeactive: ' ',
            },
          },
          pushLoad: {
            enable: false,
          },
        },
        bar: {
          background: '#393232',
          opacity: '.5',
          size: '2px',
        },
      },
      swiperOptions: {},
      status: false,
      loading: false,
      isTool: false,
      isSwiper: false,
      isWords: false,
      autoplay: false,
      circular: true,
      interval: 3000,
      duration: 500,
      emojiGroup: chunk(emojiList, 21),
      con: '',
      toUid: '',
      limit: 15,
      upperId: 0,
      chatList: [],
      kefuInfo: {},
      scrollTop: 0,
      active: true,
      isScroll: true,
      oldHeight: 0,
      selector: '',
      transferList: [], //转接列表
      isTransfer: false,
      uploadData: {}, // 上传参数
      header: {},
      fileUrl: '',
      userToken: '',
      tourist_uid: '', //游客的uid
      orderId: '',
      orderInfo: '',
      cartInfo: '',
      productId: '',
      productInfo: '',
      tourist_avatar: '',
    };
  },
  computed: {
    isSend() {
      if (this.con.length == 0) {
        return false;
      } else {
        return true;
      }
    },
    records() {
      return this.chatList.map((item, index) => {
        item.time = this.$moment(item.add_time * 1000).format('MMMDo h:mm');
        if (index) {
          if (item.add_time - this.chatList[index - 1].add_time >= 300) {
            item.show = true;
          } else {
            item.show = false;
          }
        } else {
          item.show = true;
        }
        return item;
      });
    },
  },
  created() {
    let token = localStorage.getItem('LOGIN_STATUS_TOKEN') || '';
    this.fileUrl = Setting.apiBaseURL.replace('adminapi', 'kefuapi') + '/tourist/upload';
    this.userToken = token;
    this.toUid = this.$route.query.toUid || '';
    this.nickname = this.$route.query.nickname || '';
    this.orderId = this.$route.query.orderId || '';
    this.productId = this.$route.query.product_id || '';
  },
  mounted() {
    if (!this.$wechat._isMobile()) this.$router.replace('/kefu/appChat');
    let that = this;
    this.getServiceList();
    if (this.userToken) {
      this.getOrderInfo();
      this.getGoodsInfo();
    }
    // 上传头部token
    this.header['Authori-zation'] = 'Bearer ' + getCookies('kefu_token');
    Socket.then((ws) => {
      if (this.userToken) {
        ws.send({
          type: 'login',
          data: this.userToken,
        });
      }
      // 消息接收
      ws.$on(['reply', 'chat'], (data) => {
        if (data.msn_type == 1 || data.msn_type == 2) {
          data.msn = this.replace_em(data.msn);
        }
        this.chatList.push(data);
        this.$nextTick(() => {
          this.$refs['scrollBox'].refresh();
          this.scrollBom();
        });
        setTimeout((res) => {
          this.$refs['scrollBox'].refresh();
        }, 300);
      });
      ws.$on('socket_error', () => {
        this.$Message.error('连接失败');
      });
      ws.$on('error', () => {
        this.$Message.error('连接失败');
      });
      ws.$on('to_transfer', (data) => {
        ws.send({
          data: {
            id: data.toUid,
          },
          type: 'to_chat',
        });
      });
      ws.$on('online', (data) => {
        if (data.online == 0 && data.uid == that.toUid) {
          that.$Modal.confirm({
            title: '提示',
            content: '客服已离线，是否需要反馈？',
            okText: '确定',
            cancelText: '取消',
            onOk: () => {
              that.$router.replace({
                path: '/kefu/mobile_feedback',
              });
            },
          });
        }
      });
    });
    this.$nextTick(() => {});
  },
  methods: {
    goBack() {
      this.$router.go(-1);
    },
    handleFormatError(file) {
      this.$Message.error('上传图片只能是 jpg、jpg、jpeg、gif 格式!');
    },
    // 获取商品信息
    getGoodsInfo() {
      if (!this.productId) return;
      productApi(this.productId)
        .then((res) => {
          this.productInfo = res.data;
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    // 获取订单信息
    getOrderInfo() {
      if (!this.orderId) return;
      getOrderApi(this.orderId, {
        token: this.userToken,
      }).then((res) => {
        this.orderInfo = res.data;
        if (this.orderInfo.add_time_h) {
          this.orderInfo.add_time_h = this.orderInfo.add_time_h.substring(
            0,
            this.orderInfo.add_time_h.lastIndexOf(':'),
          );
        }
        if (this.orderInfo.cartInfo.length) {
          this.cartInfo = this.orderInfo.cartInfo[0];
        }
      });
    },
    // 获取随机客服
    getServiceList() {
      serviceListApi({
        token: this.userToken,
      })
        .then((res) => {
          this.toUid = res.data.uid;
          this.tourist_uid = res.data.tourist_uid;
          document.title = res.data.nickname;
          this.tourist_avatar = res.data.tourist_avatar;
          if (this.userToken) {
            this.getChatList();
          }
          let obj = {
            data: {
              id: res.data.uid,
              tourist_uid: this.tourist_uid,
            },
            type: 'to_chat',
          };
          Socket.then((ws) => {
            ws.send(obj);
          });
        })
        .catch((error) => {
          this.$Message.error(error.msg);
          setTimeout((res) => {
            this.$router.replace({
              path: '/kefu/mobile_feedback',
            });
          }, 2000);
        });
    },
    // 上传之前
    beforeUpload(file) {
      const isImage = file.type === 'image/jpeg' || file.type === 'image/png';
      if (!isImage) {
        this.$Message.error('上传图片只能是 JPG、PNG 格式!');
      }
      this.uploadData = {
        filename: file,
        token: this.userToken,
      };
      let promise = new Promise((resolve) => {
        this.$nextTick(function () {
          resolve(true);
        });
      });
      return promise;
    },
    // 上传成功
    handleSuccess(res, file, fileList) {
      if (res.status === 200) {
        this.$Message.success(res.msg);
        this.sendMsg(res.data.url, 3);
      } else {
        this.$Message.error(res.msg);
      }
    },
    // 滚动到底部
    scrollBom() {
      setTimeout((res) => {
        let num = parseFloat(document.getElementById('chatBox').offsetHeight);
        if (this.$refs['scrollBox']) {
          this.$refs['scrollBox'].scrollTo(
            {
              y: num,
            },
            300,
          );
        }
      }, 300);
    },
    // 订单详情
    goOrderDetail(item) {
      this.$router.push({
        path: `/kefu/orderDetail/${item.orderInfo.id}`,
      });
    },
    // 底部功能区打开
    openBox(key) {
      if (key == 1) {
        this.isTool = false;
        this.isSwiper = !this.isSwiper;
      } else {
        this.isSwiper = false;
        this.isTool = !this.isTool;
      }
      this.$refs['scrollBox'].refresh();
      this.$nextTick(() => {
        this.scrollBom();
      });
    },
    showWords() {
      this.isWords = true;
    },

    // 转接
    goTransfer() {
      this.isTransfer = true;
    },
    // 转接关闭
    closeTransfer() {
      this.transferList.forEach((el, index) => {
        el.isCheck = false;
      });
      this.isTransfer = false;
    },
    // 商品信息
    goodsInfo() {
      this.$router.push({
        path: '/kefu/goods/list?toUid=' + this.toUid,
      });
    },
    // 表情点击
    addEmoji(item) {
      let val = `[${item}]`;
      this.con += val;
    },
    // 聊天表情转换
    replace_em(str) {
      str = str.replace(/\[em-([\s\S]*)\]/g, "<span class='em em-$1'/></span>");
      return str;
    },
    // 获取聊天列表
    getChatList() {
      let self = this;
      chatListApi({
        limit: this.limit,
        uid: this.toUid,
        upperId: this.upperId,
        token: this.userToken,
      }).then((res) => {
        var sH = 0;
        res.data.forEach((el) => {
          if (el.msn_type == 1 || el.msn_type == 2) {
            el.msn = this.replace_em(el.msn);
          }
        });
        let selector = '';
        if (this.upperId == 0) {
          selector = `chat_${res.data[res.data.length - 1].id}`;
        } else {
          selector = `chat_${this.chatList[0].id}`;
        }
        this.selector = selector;
        this.chatList = [...res.data, ...this.chatList];
        this.loading = false;
        this.isScroll = res.data.length >= this.limit;
        this.$refs['scrollBox'].refresh();
        this.$nextTick(() => {
          this.$emit('change', true);
          let num = parseFloat(document.getElementById(selector).offsetTop) - 60;
          this.$refs['scrollBox'].scrollTo(
            {
              y: num,
            },
            0,
          );
        });
      });
    },
    // 发送订单
    sendOrder() {
      this.sendMsg(this.orderId, 6);
      this.orderId = 0;
      this.orderInfo = {};
    },
    // 发送商品
    sendProduct() {
      this.sendMsg(this.productId, 5);
      this.productId = 0;
      this.productInfo = {};
    },
    // 发送消息
    sendText() {
      if (!this.isSend) {
        this.$Message.error('请输入内容');
      }
      this.sendMsg(this.con, 1);
      this.con = '';
    },
    // ws发送
    sendMsg(msn, type) {
      let obj = {
        type: 'chat',
        data: {
          msn,
          type,
          is_tourist: this.userToken ? 0 : 1,
          to_uid: this.toUid,
          tourist_uid: this.tourist_uid,
          form_type: this.$wechat.isWeixin() ? 1 : 3,
          tourist_avatar: this.userToken ? '' : this.tourist_avatar,
        },
      };
      Socket.then((ws) => {
        ws.send(obj);
      });
    },
    // 图片上传
    uploadImg() {
      let self = this;
      self.$util.uploadImageOne('upload/image', function (res) {
        if (res.status == 200) {
          self.sendMsg(res.data.url, 3);
        }
      });
    },
    //  商品详情页
    goProduct(item) {
      let url = window.location.protocol + '//' + window.location.host + '/pages/goods_details/index?id=' + item.msn;
      window.open(url, '_blank');
    },
    // 用户订单
    goAdminOrder() {
      // this.$router.push({
      //     path:'/kefu/orderList/0/'+this.toUid
      // })
      let url =
        window.location.protocol +
        '//' +
        window.location.host +
        '/pages/goods/order_details/index?order_id=' +
        item.msn;
      window.open(url, '_blank');
    },
    // 滚动到底部
    height() {
      let self = this;
      var scrollTop = 0;
      let info = uni.createSelectorQuery().select('.chat');
      setTimeout((res) => {
        info
          .boundingClientRect(function (data) {
            //data - 各种参数
            scrollTop = data.height;
            if (self.active) {
              self.scrollTop = parseInt(scrollTop) + 500;
            } else {
              self.scrollTop = parseInt(scrollTop) + 100;
            }
          })
          .exec();
      }, 1000);
    },
    handleActivate(vm, refreshDom) {
      this.upperId = this.chatList[0].id;
    },
    handleStart(vm, refreshDom, done) {
      setTimeout(() => {
        // load finished
        done();
      }, 2000);
    },
    handleBeforeDeactivate(vm, refreshDom, done) {
      if (this.userToken) {
        this.getChatList();
        this.$on('change', (data) => {
          if (data) done();
        });
      } else {
        done();
      }
    },
    handleDeactivate(vm, refreshDom) {
      let num = parseFloat(document.getElementById(this.selector).offsetTop) - 60;
      this.$refs['scrollBox'].scrollTo(
        {
          y: num,
        },
        0,
      );
    },
  },
};
</script>

<style lang="stylus" scoped>
.head-box{
    position relative
    display flex
    align-items center
    justify-content center
    color #fff
    height 45px
    background: linear-gradient(85deg, #3875EA 0%, #1890FC 100%);
    span{
        position absolute
        width 45px
        height 100%
        left 0
        top 0
        text-align center
        line-height 45px
    }
}
.chat-box {
    display: flex;
    flex-direction: column;
    height: 100%;
    height: 100vh;
    background #F0F1F2
    .head-box {
        background: linear-gradient(85deg, #3875EA 0%, #1890FC 100%);

        .title-hd {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            height: 43px;
            padding: 0 .3rem;
            color: #fff;

            .icon-fanhui {
                position: absolute;
                left: .3rem;
                top: 50%;
                transform: translateY(-50%);
            }

            .icon-gengduo2 {
                /* #ifdef MP */
                position: absolute;
                right: 2.1rem;
                top: 50%;
                transform: translateY(-50%);
                /* #endif */
            }
        }
    }

    .scroll-box {
        flex: 1;
    }

    .footer-box {
        display: flex;
        align-items: center;
        height: 1rem;
        padding: 0 .3rem;
        color: rgba(0,0,0,0.8);
        background: #f7f7f7;
        .words{
            margin-right .18rem
        }
        .words .iconfont {
            font-size: .5rem
        }

        .input-box {
            display: flex;
            align-items: center;
            flex 1
            height: .64rem;
            padding-right: 0.05rem;
            background-color: #fff;
            border-radius: .32rem;
            overflow hidden

            input {
                flex: 1;
                padding-left: .2rem;
                height: 100%;
                border transparent !important

            }
            >>> .ivu-input{
                font-size .28rem !important
            }
            >>> .ivu-input, .ivu-input:hover, .ivu-input:focus {
                border transparent
                box-shadow: none;
            }

            .iconfont {
                font-size: .5rem;
                color: #ccc;
                font-weight: normal;
            }

            .isSend {
                color: #3875EA;
            }
        }

        .emoji .iconfont {
            margin-left: .18rem;
            font-size: .5rem;
        }

        .more .iconfont {
            margin-left: .18rem;
            font-size: .5rem;
        }

    }
}

.tool-wrapper {
    display: flex;
    justify-content: space-between;
    padding: .45rem .6rem;
    background: #fff;
    font-size: .24rem;

    .tool-item {
        text-align: center;

        img {
            width: 1.04rem;
            height: 1.04rem;
        }
    }
}

.slider-banner {
    padding-bottom .25rem
    background: #fff;

    .em {
        display: inline-block;
        width: .5rem;
        height: .5rem;
        margin: .4rem 0 0 .5rem;
    }
}

.words-mask {
    z-index: 50;
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);

    .content {
        position: absolute;
        left: 0;
        right: 0;
        top: 1.14rem;
        bottom: 0;
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 0.06rem 0.06rem 0px 0px;

        .title-box {
            padding: 0 .3rem .3rem;
            position: relative;
            border-bottom 1px solid #F5F6F9
            .tab-box{
                position relative
                display flex
                justify-content space-between
                padding .4rem 2.2rem .3rem
                font-size .32rem
                color #9F9F9F
                .on{
                    color #3875EA
                    font-weight bold
                }
                .right-icon{
                    position absolute
                    right 0
                    top 50%
                    transform translateY(-50%)
                    .iconfont{
                        margin-left .2rem
                        font-size .48rem
                        color #C8CAD0
                    }
                }
            }
            .input-box{
                display: flex;
                align-items: center;
                width: 6.9rem;
                height: .64rem;
                padding-right: 0.05rem;
                margin-left: .18rem;
                border-radius: .32rem;
                overflow hidden

                >>> .ivu-input{
                    background #F5F6F9
                }
                >>> .ivu-input, .ivu-input:hover, .ivu-input:focus {
                    border transparent
                    box-shadow: none;
                }
            }
            .icon-cha1 {
                position: absolute;
                right: 0;
                top: 50%;
                transform: translateY(-50%);
            }
        }

        .scroll-box {
            flex: 1;
            display flex
            overflow: hidden;
            .scroll-left{
                width 1.76rem
                height 100%
                overflow-y scroll
                -webkit-overflow-scrolling touch
                background #F5F6F9
                .left-item{
                    position relative
                    display flex
                    align-items center
                    justify-content center
                    width 100%
                    height 1.09rem
                    color #282828
                    font-size .26rem
                    &.active{
                        color #3875EA
                        background #fff
                        &:after{
                            content ' '
                            position: absolute;
                            left 0
                            top 50%
                            transform translateY(-50%)
                            width 0.06rem
                            height .46rem
                            background #3875EA
                        }
                    }
                    &.add_cate{
                        color #9F9F9F
                        font-size .26rem
                        .iconfont{
                            margin-right 0.1rem
                            font-size .24rem
                        }
                    }
                }
            }
            .right-box{
                flex 1
                overflow scroll
                -webkit-overflow-scrolling touch
            }
            .msg-item {
                padding: .25rem .3rem;
                color #888888
                font-size .28rem
                .title{
                    margin-right .2rem
                    color #282828
                }
                &.add-mg{
                    display flex
                    align-items center
                    justify-content flex-end
                    font-size .28rem
                    padding .15rem .3rem
                    .iconfont{
                        font-size .36rem
                        margin-right .1rem
                    }
                }
            }
        }
    }
}

.chat-scroll-box {
    flex: 1;
    overflow: hidden;
    .day-box{
        margin-bottom .2rem
        font-size .24rem
        color #999
        text-align: center;
    }
    .chat-item {
        display: flex;
        margin-bottom: .36rem;
        font-size .28rem
        .avatar {
            width: .8rem;
            height: .8rem;
            border-radius: 50%;
        }

        .msg-box {
            display: flex;
            align-items: center;
            max-width: 4.52rem;
            margin-left: .22rem;
            padding: .1rem .24rem;
            background: #fff;
            border-radius: .14rem;
            word-break: break-all;
            font-size .28rem
        }

        .img-box {
            width: 2.7rem;
            margin-left: .22rem;

            img {
                width: 2.7rem;
                border-radius 6px
            }
        }

        .product-box {
            width: 4.52rem;
            background-color: #fff;
            border-radius: .14rem;
            overflow: hidden;
            margin-left: .22rem;

            img {
                width: 4.52rem;
            }

            .info {
                padding: .16rem .26rem;

                .price {
                    font-size: .36rem;
                    color: #F74C31;

                    text {
                        font-size: .28rem;
                    }
                }
            }
        }

        .order-box {
            width: 4.52rem;
            margin-left: .22rem;
            background-color: #fff;
            border-radius: .14rem;

            .title {
                padding: .15rem .2rem;
                font-size: .26rem;
                color: #282828;
                border-bottom: 1px solid #ECEFF8;
            }

            .info {
                display: flex;
                padding: .2rem;

                img {
                    width: 1.24rem;
                    height: 1.24rem;
                    border-radius: 0.06rem;
                }

                .product-info {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    margin-left: .16rem;

                    .name {
                        font-size: .26rem;
                    }

                    .price {
                        font-size: .3rem;
                        color: #F74C31;
                    }
                }
            }
        }

        &.right-box {
            flex-direction: row-reverse;

            .msg-box {
                margin-left: 0;
                margin-right: .22rem;
                background-color: #9cec60;

                color #333
            }

            .img-box {
                margin-left: 0;
                margin-right: .22rem;
            }

            .product-box {
                margin-left: 0;
                margin-right: .22rem;
            }

            .order-box {
                margin-left: 0;
                margin-right: .22rem;
            }
        }

        .em {
            margin: 0;
        }
    }
}
.transfer-mask
    z-index 30
    position fixed
    left 0
    top 0
    width 100%
    height 100%
    background rgba(0,0,0,0.5)
    .content
        position absolute
        left 0
        bottom 0
        transform translateY(100%)
        top 2.5rem
        right 0
        display flex
        flex-direction column
        background #fff
        border-radius: .16rem .16rem 0px 0px;
        &.on
            animation up .2s linear
            animation-fill-mode: forwards;
        .title
            position relative
            display flex
            align-items center
            justify-content center
            height 1.1rem
            font-size .32rem
            font-weight bold
            color #282828
            .iconfont
                position absolute
                right .3rem
                top 50%
                transform translateY(-50%)
                color #C8CAD0
                font-size .44rem
        .list-wrapper
            flex 1
            padding-left .3rem
            overflow-y scroll
            -webkit-overflow-scrolling touch
            .list-item
                display flex
                align-items center
                padding .16rem 0
                border-bottom 1px solid #ECEFF8
                .check-box
                    width .72rem
                .avatar-box img
                    width .9rem
                    height .9rem
                    border-radius 0.06rem
                .nickName
                    margin-left .28rem
                    color #282828
                    font-size .3rem
                    font-weight bold
        .btn
            width 6.9rem
            height .86rem
            margin .5rem auto
            color #fff
            background #3875EA
            font-size .3rem
            border-radius: .43rem !important;
.broadcast-details_box {
    display: flex;
    background: #fff;
    border-radius: 6px;
    padding: .24rem;
}

.broadcast_details_img {
    width: 1.4rem;
    height: 1.4rem;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
}

.broadcast_details_img img {
    width: 100%;
    height: 100%;
}

.broadcast_details_picBox {
    width: 75%;
    margin-left: .24rem;
}

.broadcast_details_tit {
    font-size: .28rem;
    color: #333333;
    height: .85rem;
    font-weight: 800;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    text-align: left !important;
}

.broadcast_details_pic {
    font-size: .36rem;
    color: #e93323;
    text-align: left;
}

.broadcast_details_pic_num {
    text-decoration: line-through;
    font-size: .28rem;
    color: rgba(0, 0, 0, 0.5);
    margin-left: 0.1rem;
}

.broadcast_details_btn {
    width: 1.3rem;
    height: .5rem;
    background: #e83323;
    opacity: 1;
    border-radius: 1.25rem;
    color: #fff;
    font-size: .24rem;
    text-align: center;
    line-height: .5rem;
}

.broadcast-details_num {
    width: 100%;
    height: .8rem;
    line-height: .8rem;
    color: #000000;
    font-size: .26rem;
    display: flex;
    justify-content: space-between;
    background: #fff;
    border-bottom: 1px dashed rgba(0, 0, 0, 0.2);
    padding: 0 .24rem;
}
</style>
<style>
@keyframes up {
  0% {
    transform: translateY(100%);
  }
  100% {
    transform: translateY(0);
  }
}
.emoji-outer {
  position: absolute;
  right: 0.5rem;
  bottom: 0.3rem;
  width: 0.5rem;
  height: 0.5rem;
}
</style>
