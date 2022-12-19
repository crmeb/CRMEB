<template>
  <div class="kefu-layouts">
    <div class="content-wrapper">
      <baseHeader :kefuInfo="kefuInfo" :online="online" @setOnline="setOnline" @search="bindSearch"></baseHeader>
      <div class="container">
        <chatList
          @setDataId="setDataId"
          @changeType="changeType"
          :userOnline="userOnline"
          :newRecored="newRecored"
          :searchData="searchData"
        ></chatList>
        <div class="chat-content">
          <div class="chat-body">
            <happy-scroll size="5" resize hide-horizontal :scroll-top="scrollTop" @vertical-start="scrollHandler">
              <div style="width: 600px; padding: 20px" id="chat_scroll" ref="scrollBox">
                <Spin v-show="isLoad">
                  <Icon type="ios-loading" size="18" class="demo-spin-icon-load"></Icon>
                  <div>Loading</div>
                </Spin>
                <div
                  class="chat-item"
                  v-for="(item, index) in records"
                  :key="index"
                  :class="[{ 'right-box': item.uid == kefuInfo.uid }, { gary: item.msn_type == 5 }]"
                  :id="`chat_${item.id}`"
                >
                  <div class="time" v-show="item.show">{{ item.time }}</div>
                  <div class="flex-box">
                    <div class="avatar">
                      <img v-lazy="item.avatar" alt="" />
                    </div>
                    <div class="msg-wrapper">
                      <!-- 文档 -->
                      <template v-if="item.msn_type <= 2">
                        <div class="txt-wrapper pad16" v-html="item.msn"></div>
                      </template>
                      <!-- 图片 -->
                      <template v-if="item.msn_type == 3">
                        <div class="img-wraper" v-viewer>
                          <img v-lazy="item.msn" alt="" />
                        </div>
                      </template>
                      <!-- 商品 -->
                      <template v-if="item.msn_type == 5">
                        <div class="order-wrapper pad16">
                          <div class="img-box">
                            <img :src="item.productInfo.image" alt="" />
                          </div>
                          <div class="order-info">
                            <div class="name line1">
                              {{ item.productInfo.store_name }}
                            </div>
                            <div class="sku">
                              库存：{{ item.productInfo.stock }} 销量：{{
                                parseInt(item.productInfo.sales) +
                                parseInt(item.productInfo.ficti ? item.productInfo.ficti : 0)
                              }}
                            </div>
                            <div class="price-box">
                              <div class="num">¥ {{ item.productInfo.price }}</div>
                              <a herf="javascript:;" class="more" @click.stop="lookGoods(item)">查看商品 ></a>
                            </div>
                          </div>
                        </div>
                      </template>
                      <!-- 订单 -->
                      <template v-if="item.msn_type == 6 && (item.orderInfo.length > 0 || item.orderInfo.id)">
                        <div class="order-wrapper pad16">
                          <div class="img-box">
                            <img :src="item.orderInfo.cartInfo[0].productInfo.image" alt="" />
                          </div>
                          <div class="order-info">
                            <div class="name line1">
                              {{ item.orderInfo.order_id }}
                            </div>
                            <div class="sku">商品数量：{{ item.orderInfo.total_num }}</div>
                            <div class="price-box">
                              <div class="num">¥ {{ item.orderInfo.pay_price }}</div>
                              <a href="javascript:;" class="more" @click.stop="lookOrder(item)">查看订单 ></a>
                            </div>
                          </div>
                        </div>
                      </template>
                    </div>
                  </div>
                </div>
              </div>
            </happy-scroll>
          </div>
          <div class="chat-textarea">
            <div class="chat-btn-wrapper">
              <div class="left-wrapper">
                <div class="icon-item" @click.stop="isEmoji = !isEmoji">
                  <span class="iconfont iconbiaoqing1"></span>
                </div>
                <div class="icon-item">
                  <Upload
                    :show-upload-list="false"
                    :headers="header"
                    :data="uploadData"
                    :on-success="handleSuccess"
                    :format="['jpg', 'jpeg', 'png', 'gif']"
                    :on-format-error="handleFormatError"
                    :action="upload"
                  >
                    <span class="iconfont icontupian1"></span>
                  </Upload>
                </div>
                <div class="icon-item" @click.stop.stop="isMsg = true">
                  <span class="iconfont iconliaotian"></span>
                </div>
              </div>
              <div class="right-wrapper">
                <div class="icon-item" @click.stop="isTransfer = !isTransfer">
                  <span class="iconfont iconzhuanjie"></span>
                  <span>转接</span>
                </div>
                <div class="transfer-box" v-if="isTransfer">
                  <transfer @close="msgClose" @transferPeople="transferPeople" :userUid="userActive.to_uid"></transfer>
                </div>
                <div class="transfer-bg" v-if="isTransfer" @click.stop="isTransfer = false"></div>
              </div>
              <!-- 表情 -->
              <div class="emoji-box" v-show="isEmoji">
                <div class="emoji-item" v-for="(emoji, index) in emojiList" :key="index">
                  <i class="em" :class="emoji" @click.stop="select(emoji)"></i>
                </div>
              </div>
            </div>
            <div class="textarea-box" style="position: relative">
              <Input
                v-paste="handleParse"
                v-model="chatCon"
                type="textarea"
                :rows="4"
                @on-keydown="listen($event)"
                placeholder="请输入文字内容"
                style="font-size: 14px"
              />
              <div class="send-btn">
                <Button class="btns" type="primary" :disabled="disabled" @click.stop="sendText">发送</Button>
              </div>
            </div>
          </div>
        </div>
        <div>
          <rightMenu
            :isTourist="tourist"
            :uid="userActive.to_uid"
            :webType="userActive.type"
            @bindPush="bindPush"
          ></rightMenu>
        </div>
      </div>
      <!-- 用户标签 -->
      <Modal v-model="isMsg" :mask="true" class="none-radius isMsgbox" width="600" :footer-hide="true">
        <msgWindow v-if="isMsg" @close="msgClose" @activeTxt="activeTxt"></msgWindow>
      </Modal>
      <!-- 商品弹窗 -->
      <div v-if="isProductBox">
        <div class="bg" @click.stop="isProductBox = false"></div>
        <goodsDetail :goodsId="goodsId"></goodsDetail>
      </div>
      <!-- 订单详情 -->
      <div v-if="isOrder">
        <Modal v-model="isOrder" title="订单信息" width="700" :footer-hide="true" :mask="true" class="none-radius">
          <orderDetail :orderId="orderId"></orderDetail>
        </Modal>
      </div>
    </div>
  </div>
</template>

<script>
var mp3 = require('../../../assets/video/notice.wav');
var mp3 = new Audio(mp3);
import Setting from '@/setting';
import { HappyScroll } from 'vue-happy-scroll';
import baseHeader from './components/baseHeader';
import chatList from './components/chatList';
import rightMenu from './components/rightMenu';
import emojiList from '@/utils/emoji';
import { Socket } from '@/libs/socket';
import util from '@/libs/util';
import msgWindow from './components/msgWindow';
import transfer from './components/transfer';
import { serviceList, uploadImg } from '@/api/kefu';
import goodsDetail from './components/goods_detail';
import orderDetail from './components/order_detail';
import { mapState } from 'vuex';
import { getCookies, removeCookies, setCookies } from '@/libs/util';
import { serviceInfo } from '@/api/kefu_mobile';
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
  name: 'index',
  components: {
    baseHeader,
    chatList,
    rightMenu,
    msgWindow,
    transfer,
    HappyScroll,
    goodsDetail,
    orderDetail,
  },
  data() {
    return {
      isEmoji: false,
      chatCon: '',
      emojiGroup: chunk(emojiList, 20), // 表情列表
      emojiList: emojiList,
      html: '',
      userActive: {}, //左侧用户列表选中信息
      kefuInfo: {}, //客服信息
      isMsg: false,
      isTransfer: false,
      activeMsg: '', // 选中的话术
      chatList: [],
      text: '',
      limit: 20,
      upperId: 0,
      online: true, //当前客服在线状态
      scrollTop: 0,
      isScroll: true,
      oldHeight: 0,
      isLoad: false,
      isProductBox: false,
      goodsId: '',
      isOrder: false,
      orderId: '',
      upload: '',
      header: {},
      uploadData: {
        filename: 'file',
      },
      userOnline: {},
      newRecored: {}, //新对话信息
      searchData: '', // 搜索文字
      scrollNum: 0, //滚动次数
      transferId: '', //转接id
      bodyClose: false,
      tourist: 0,
    };
  },
  computed: {
    ...mapState({
      socketStatus: (state) => state.admin.kefu.socketStatus,
    }),
    disabled() {
      if (this.chatCon.length == 0) {
        return true;
      } else {
        return false;
      }
    },
    records() {
      return this.chatList.map((item, index) => {
        item.time = this.$moment(item.add_time * 1000).format('MMMDo H:mm');
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
  // 指令粘贴指令定义
  directives: {
    paste: {
      bind(el, binding, vnode) {
        el.addEventListener('paste', function (event) {
          //这里直接监听元素的粘贴事件
          binding.value(event);
        });
      },
    },
  },
  watch: {
    // socketStatus:{
    //     handler(nVal,Val){
    //         if(nVal){
    //             Socket.send({
    //                 data: util.cookies.kefuGet('token'),
    //                 type: "kefu_login"
    //             });
    //         }
    //     },
    //     deep:true
    // }
  },
  created() {
    this.upload = Setting.apiBaseURL.replace('adminapi', 'kefuapi') + '/upload';
    serviceInfo().then((res) => {
      this.kefuInfo = res.data;
      if (this.kefuInfo.site_name) {
        document.title = this.kefuInfo.site_name;
      } else {
        this.kefuInfo.site_name = '';
      }
    });
  },
  mounted() {
    let self = this;
    window.addEventListener('click', function () {
      self.isEmoji = false;
    });
    setTimeout((e) => {
      Socket.then((ws) => {
        ws.send({
          type: 'kefu_login',
          data: getCookies('kefu_token'),
        });
        ws.$on(['reply', 'chat'], (data) => {
          if (data.msn_type == 1) {
            data.msn = this.replace_em(data.msn);
          }
          if (data.msn_type == 2) {
            if (data.msn.indexOf('[') == -1) {
              data.msn = this.replace_em(`[${data.msn}]`);
            }
          }
          this.chatList.push(data);
          this.$nextTick(function () {
            setTimeout(() => {
              var container = document.querySelector('#chat_scroll');
              this.scrollTop = container.offsetHeight;
            }, 800);
          });
        });
        ws.$on('reply', (data) => {
          // mp3.play();
        });
        ws.$on('socket_error', () => {
          this.$Message.error('连接失败');
        });
        ws.$on('err_tip', (data) => {
          this.$Message.error(data.msg);
        });
        //用户上线提醒广播
        ws.$on('user_online', (data) => {
          this.userOnline = data;
        });
        //用户未读消息条数更改
        ws.$on('mssage_num', (data) => {
          if (data.num > 0) {
            mp3.play();
          }
          this.chatList.forEach((item) => {
            if (item.to_uid == data.uid) {
              item.mssage_num = data.num;
            }
          });
          if (data.recored.id) {
            this.newRecored = data.recored;
          }
        });
      });
    }, 2000);
    this.header['Authori-zation'] = 'Bearer ' + getCookies('kefu_token');
    this.text = this.replace_em('[em-smiling_imp]');
    // Socket.init(this,'kefu');
  },
  methods: {
    handleFormatError(file) {
      this.$Message.error('上传图片只能是 jpg、jpg、jpeg、gif 格式!');
    },
    bindEnter(e) {},
    //微信截图上传图片时触发
    handleParse(e) {
      let file = null;
      if (
        e.clipboardData &&
        e.clipboardData.items[0] &&
        e.clipboardData.items[0].type &&
        e.clipboardData.items[0].type.indexOf('image') > -1
      ) {
        //这里就是判断是否有粘贴进来的文件且文件为图片格式
        file = e.clipboardData.items[0].getAsFile();
      } else {
        this.$message({
          type: 'warning',
          message: '上传的文件必须为图片且无法复制本地图片且无法同时复制多张图片',
        });
        return;
      }
      this.update(file);
    },
    update(e) {
      // 上传照片
      let file = e;
      let param = new FormData(); // 创建form对象
      param.append('filename', 'file'); // 通过append向form对象添加数据进去
      param.append('file', file); // 通过append向form对象添加数据进去
      // 添加请求头
      uploadImg(param).then((res) => {
        this.sendMsg(res.data.url, 3);
      });
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
    //订单详情
    lookOrder(item) {
      this.orderId = item.orderInfo.id;
      this.isOrder = true;
    },
    setOnline(data) {
      Socket.then((ws) => {
        ws.send({
          data: {
            online: data,
          },
          type: 'online',
        });
      });
      this.online = data;
    },
    // 阻止浏览器默认换行操作
    listen(e) {
      if (e.shiftKey && e.keyCode == 13) {
        console.log('换行');
      } else if (e.keyCode == 13) {
        if (e.target.value == '') {
          return this.$Message.error('请输入消息');
        }
        this.sendMsg(e.target.value, 1);
        this.chatCon = '';
      }
    },
    // 输入框选择表情
    select(data) {
      let val = `[${data}]`;
      this.chatCon += val;
      this.isEmoji = false;
    },
    // 聊天表情转换
    replace_em(str) {
      str = str.replace(/\[em-([\s\S]*)\]/g, "<span class='em em-$1'/></span>");
      return str;
    },
    // 获取是否游客
    changeType(data) {
      this.tourist = data;
    },
    // 获取列表用户信息
    setDataId(data) {
      this.userActive = data;
      this.chatList = [];
      this.upperId = 0;
      this.oldHeight = 0;
      this.isScroll = true;
      if (data) {
        window.document.title = data.nickname
          ? `正在和${data.nickname}对话中 - ${this.kefuInfo.site_name}`
          : '正在和游客对话中 - ' + this.kefuInfo.site_name;

        Socket.then((ws) => {
          ws.send({
            data: {
              id: this.userActive.to_uid,
            },
            type: 'to_chat',
          });
        });
        this.getChatList();
      } else {
        window.document.title = this.kefuInfo.site_name;
      }
    },
    msgClose() {
      this.isTransfer = false;
    },
    // 话术选中
    activeTxt(data) {
      this.chatCon = data;
      this.isMsg = false;
    },
    // 文本发送
    sendText() {
      this.sendMsg(this.chatCon, 1);
      this.chatCon = '';
    },

    // 统一发送处理
    sendMsg(msn, type) {
      let obj = {
        type: 'chat',
        data: {
          msn,
          type,
          to_uid: this.userActive.to_uid,
          is_tourist: this.tourist,
        },
      };
      Socket.then((ws) => {
        ws.send(obj);
      });
    },
    send(type, data) {
      Socket.send({
        data,
        type,
      });
    },
    // 获取聊天列表
    getChatList() {
      serviceList({
        limit: this.limit,
        uid: this.userActive.to_uid,
        upperId: this.upperId,
        is_tourist: this.tourist,
      }).then((res) => {
        res.data.forEach((el) => {
          if (el.msn_type == 1) {
            el.msn = this.replace_em(el.msn);
          } else if (el.msn_type == 2) {
            el.msn = this.replace_em(`[${el.msn}]`);
          }
        });
        let selector = '';
        if (this.upperId == 0) {
          selector = '';
        } else {
          selector = `chat_${this.chatList[0].id}`;
        }

        // this.chatList = res.data.concat(this.chatList)
        this.chatList = [...res.data, ...this.chatList];
        this.upperId = res.data.length > 0 ? res.data[0].id : 0;
        this.isLoad = false;
        this.$nextTick(() => {
          // this.scrollToTop()
          this.isScroll = res.data.length >= this.limit;
          this.setPageScrollTo(selector);
        });
      });
    },
    // 设置页面滚动位置
    setPageScrollTo(selector) {
      this.$nextTick(() => {
        if (selector) {
          setTimeout(() => {
            let num = parseFloat(document.getElementById(selector).offsetTop) - 60;
            this.scrollTop = num;
          }, 0);
        } else {
          var container = document.querySelector('#chat_scroll');
          this.scrollTop = container.offsetHeight;
          setTimeout((res) => {
            if (this.scrollTop != this.$refs.scrollBox.offsetHeight) {
              this.scrollTop = document.querySelector('#chat_scroll').offsetHeight;
            }
          }, 300);
        }
      });
    },
    //滚动到顶部
    scrollHandler() {
      let self = this;
      if (this.isScroll && this.upperId) {
        this.isLoad = true;
        this.getChatList();
      }
    },
    // 滚动条动画
    scrollToTop(duration) {
      var container = document.querySelector('#chat_scroll');
      this.scrollTop = container.offsetHeight - this.oldHeight;
      setTimeout((res) => {
        this.scrollTop = this.$refs.scrollBox.offsetHeight - this.oldHeight;
      }, 300);
    },
    // 商品推送
    bindPush(data) {
      this.sendMsg(data, 5);
    },
    // 商品详情
    lookGoods(item) {
      this.goodsId = item.msn;
      this.isProductBox = true;
    },
    // 搜索用户
    bindSearch(data) {
      this.searchData = data;
      this.oldHeight = 0;
      this.upperId = 0;
      this.isScroll = false;
    },
    // 客服转接
    transferPeople(data) {
      this.transferId = data.id;
      this.isTransfer = false;
      this.$Message.success('转接成功');
      Socket.then((ws) => {
        ws.send({
          type: 'to_chat',
          data: { id: data.uid },
        });
      });
    },
    // 客服转接确定
    transferOk() {},
  },
};
</script>

<style lang="stylus" scoped>
@import '../../../styles/emoji-awesome/css/google.min.css';

textarea.ivu-input {
  border: none;
  resize: none;
}

.kefu-layouts {
  padding-top: 30px;
  height: 100%;
  display: flex;
  background: #ccc;
  overflow: scroll;
}

.content-wrapper {
  display: flex;
  flex-direction: column;
  width: 1200px;
  height: 808px;
  margin: 0 auto;
  background: #fff;

  .container {
    flex: 1;
    display: flex;

    .chat-content {
      width: 600px;
      height: 100%;
      border-right: 1px solid #ECECEC;

      .chat-body {
        height: 530px;

        .chat-item {
          margin-bottom: 10px;

          .time {
            text-align: center;
            color: #999999;
            font-size: 14px;
            margin: 18px 0;
          }

          .flex-box {
            display: flex;
          }

          .avatar {
            width: 40px;
            height: 40px;
            margin-right: 16px;

            img {
              display: block;
              width: 100%;
              height: 100%;
              border-radius: 50%;
            }
          }

          .msg-wrapper {
            max-width: 320px;
            background: #F5F5F5;
            border-radius: 10px;
            color: #000000;
            font-size: 14px;
            overflow: hidden;

            .txt-wrapper {
              word-break: break-all;
              white-space: pre-wrap;
            }

            .pad16 {
              padding: 9px;
            }

            .img-wraper img {
              max-width: 100%;
              height: auto;
              display: block;
            }

            .order-wrapper {
              display: flex;
              width: 320px;

              .img-box {
                width: 60px;
                height: 60px;

                img {
                  width: 100%;
                  height: 100%;
                  border-radius: 5px;
                }
              }

              .order-info {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                width: 224px;
                margin-left: 10px;
                font-size: 12px;

                .price-box {
                  display: flex;
                  align-items: center;
                  justify-content: space-between;
                  font-size: 14px;
                  color: #FF0000;

                  .more {
                    font-size: 12px;
                    color: #1890FF;
                  }
                }

                .name {
                  font-size: 14px;
                }

                .sku {
                  margin: 1px 0;
                  color: #999999;
                }
              }
            }
          }

          &.right-box {
            .flex-box {
              flex-direction: row-reverse;

              .avatar {
                margin-right: 0;
                margin-left: 16px;
              }

              .msg-wrapper {
                background: #CDE0FF;
              }
            }

            &.gary .msg-wrapper {
              background: #f5f5f5;
            }
          }
        }
      }

      .chat-textarea {
        height: 214px;
        border-top: 1px solid #ECECEC;

        .chat-btn-wrapper {
          position: relative;
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 15px 0;

          .left-wrapper {
            display: flex;
            align-items: center;

            .icon-item {
              display: flex;
              align-items: center;
              margin-left: 20px;
              cursor: pointer;

              .iconfont {
                font-size: 22px;
                color: #333333;
              }
            }
          }

          .right-wrapper {
            position: relative;
            padding-right: 20px;

            .icon-item {
              display: flex;
              align-items: center;
              font-size: 15px;
              color: #333;
              cursor: pointer;

              span {
                margin-left: 10px;
              }
            }

            .transfer-box {
              z-index: 60;
              position: absolute;
              right: 1px;
              bottom: 43px;
              width: 140px;
              background: #fff;
              box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
              padding: 16px;
            }

            .transfer-bg {
              z-index: 50;
              position: fixed;
              left: 0;
              top: 0;
              width: 100%;
              height: 100%;
              background: transparent;
            }
          }

          .emoji-box {
            position: absolute;
            left: 0;
            top: 0;
            transform: translateY(-100%);
            display: flex;
            flex-wrap: wrap;
            width: 60%;
            padding: 15px 9px;
            box-shadow: 0px 0px 13px 1px rgba(0, 0, 0, 0.1);
            background: #fff;
            overflow: auto;
            height: 240px;

            .emoji-item {
              margin-right: 13px;
              margin-bottom: 8px;
              cursor: pointer;

              &:nth-child(10n) {
                margin-right: 0;
              }
            }
          }
        }
      }
    }
  }
}

.send-btn {
  position: absolute;
  right: 0;
  bottom: 10px;
  display: flex;
  justify-content: flex-end;
  margin-top: 10px;
  margin-right: 10px;
  width: 80px;

  .btns {
    width: 100%;
    background: #3875EA;

    &[disabled] {
      background: #CCCCCC;
      color: #fff;
    }
  }
}

.bg {
  z-index: 100;
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
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

.isMsgbox {
  >>> .ivu-modal-body {
    padding: 0;
  }
}
.emoji-box::-webkit-scrollbar {
  width: 0;
}
</style>
