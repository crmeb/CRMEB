<template>
  <div class="chat-room">
    <div class="room" :class="{ win: !chatOptions.popup }" v-db-click @click="roomClick">
      <div v-drag class="head">
        <div class="image">
          <img
            :src="(serviceData && serviceData.avatar) || defaultAvatar"
            @error="replaceBrokenImage($event, defaultAvatar)"
          />
        </div>
        <div class="name">{{ serviceData && serviceData.nickname }}</div>
        <div
          :class="['iconfont', muted ? 'icon-shengyinjingyinxianxing' : 'icon-shengyinyinliang']"
          v-db-click
          @click.stop="muted = !muted"
        ></div>
        <div class="iconfont icon-guanbi5" v-db-click @click.stop="close"></div>
      </div>
      <div class="main">
        <div class="chat">
          <div class="record" @scroll="onScroll" ref="record">
            <div id="chat_scroll" ref="scrollBox" v-loading="loading">
              <ul>
                <template v-for="item in records" :key="item.id">
                  <li :class="{ right: item.uid === serviceData.tourist_uid }" :id="`chat_${item.id}`">
                    <div v-if="item.show" class="time-tag">
                      {{ item.add_time }}
                    </div>
                    <div class="avatar">
                      <img :src="item.avatar || defaultAvatar" @error="replaceBrokenImage($event, defaultAvatar)" />
                    </div>
                    <div class="content" ref="chatContent">
                      <div v-if="item.msn_type === 1" class="text" v-html="item.msn"></div>
                      <div v-if="item.msn_type === 2" class="image">
                        <div class="text">
                          <i :class="`em ${item.msn}`"></i>
                        </div>
                      </div>
                      <div v-if="item.msn_type === 3" class="image" v-viewer>
                        <img :src="item.msn || defaultImage" @error="replaceBrokenImage($event, defaultImage)" />
                      </div>
                      <!-- 商品卡片 -->
                      <div v-if="item.msn_type === 5 && item.productInfo" class="goods">
                        <div class="thumb">
                          <img
                            :src="item.productInfo.image || defaultImage"
                            @error="replaceBrokenImage($event, defaultImage)"
                          />
                        </div>
                        <div class="intro">
                          <div class="name">{{ item.productInfo.store_name }}</div>
                          <div class="group">
                            <span class="money">￥{{ item.productInfo.price }}</span>
                          </div>
                        </div>
                      </div>
                      <!-- 订单卡片 -->
                      <div v-if="item.msn_type === 6 && item.orderInfo" class="order">
                        <div class="thumb">
                          <img
                            :src="item.orderInfo.image || defaultImage"
                            @error="replaceBrokenImage($event, defaultImage)"
                          />
                        </div>
                        <div class="intro">
                          <div class="name">{{ item.orderInfo.title }}</div>
                          <div class="attr">订单号：{{ item.orderInfo.order_no }}</div>
                          <div class="group">
                            <span class="money">￥{{ item.orderInfo.pay_price }}</span>
                            <span
                              >{{ item.orderInfo.pay_status_name
                              }}{{ item.orderInfo.status_name ? '·' + item.orderInfo.status_name : '' }}</span
                            >
                          </div>
                        </div>
                      </div>
                    </div>
                  </li>
                </template>
              </ul>
            </div>
          </div>
          <div class="editor">
            <div class="editor-hd">
              <div>
                <button class="emoji-btn" title="表情" v-db-click @click.stop="emojiSwitch">
                  <span class="iconfont iconbiaoqing1"></span>
                </button>
                <button title="图片" v-if="kufuToken">
                  <el-upload
                    :show-file-list="false"
                    :action="uploadAction"
                    :before-upload="beforeUpload"
                    accept="image/*"
                    :on-format-error="handleFormatError"
                    :data="uploadData"
                    :on-success="uploadSuccess"
                    :on-error="uploadError"
                  >
                    <span class="iconfont icontupian1"></span>
                  </el-upload>
                </button>
              </div>
              <!--                            <div>-->
              <!--                                <button class="end" v-db-click @click="chatEnd">-->
              <!--                                    <i class="iconfont icon-guanji"></i>结束-->
              <!--                                </button>-->
              <!--                            </div>-->
              <!-- 表情 -->
              <div class="emoji-panel" v-if="emojiShow">
                <!-- 表情类型 tab：默认/QQ，与客服工作台及用户端表情保持同步 -->
                <div class="emoji-tabs">
                  <span
                    class="tab-item"
                    :class="{ on: emojiTab === 'default' }"
                    v-db-click
                    @click.stop="emojiTab = 'default'"
                    >默认表情</span
                  >
                  <span class="tab-item" :class="{ on: emojiTab === 'qq' }" v-db-click @click.stop="emojiTab = 'qq'"
                    >QQ表情</span
                  >
                </div>
                <div class="emoji-list">
                  <template v-if="emojiTab === 'default'">
                    <i
                      class="em"
                      :class="emoji"
                      v-db-click
                      @click.stop="selectEmoji(emoji)"
                      v-for="(emoji, index) in emojiList"
                      :key="index"
                    ></i>
                  </template>
                  <template v-else>
                    <span
                      class="qq-item"
                      :title="item.name"
                      v-db-click
                      @click.stop="selectQq(item)"
                      v-for="(item, index) in qqEmojis"
                      :key="index"
                      >{{ item.char }}</span
                    >
                  </template>
                </div>
              </div>
            </div>
            <div class="editor-bd">
              <textarea v-model="chatCont" placeholder="请输入文字内容" @keydown.enter="ctrlEnter"></textarea>
            </div>
            <div class="editor-ft">
              <button :disabled="!chatCont" v-db-click @click.stop="sendMessage">发送</button>
            </div>
          </div>
        </div>
        <div class="notice">
          <div v-if="notice" class="rich" v-html="notice"></div>
          <div class="copy">
            <span v-if="copyright">{{ copyright }}</span>
            <a v-else href="http://www.crmeb.com/" target="_blank">CRMEB提供技术支持</a>
          </div>
        </div>
      </div>
      <audio ref="audio" :src="audioSrc"></audio>
    </div>
    <feed-back @closeChange="closeChange($event)" v-if="change" :change="change"></feed-back>
  </div>
</template>

<script setup>
import 'emoji-awesome/dist/css/google.min.css';
import emojiList, { qqEmojis } from '@/utils/emoji';
import defaultAvatar from '@/assets/images/moren.jpg';
import defaultImage from '@/assets/images/image_default.png';
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { ElMessage } from 'element-plus';
import { Socket } from '@/pages/plugin/kefu/libs/socket';
import Setting from '@/setting';
import Auth from '@/libs/wechat';
import Cookies from 'js-cookie';
import { chatListApi, serviceListApi, getAdvApi } from '@/pages/plugin/kefu/api';
import feedBack from './feedback';
import { isPicUpload } from '@/utils';
import { Session } from '@/utils/storage.js';

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

defineOptions({ name: 'ChatRoom', auth: false });

const props = defineProps({
  chatOptions: {
    type: Object,
    default: function () {
      return {
        show: false,
      };
    },
  },
});

const emit = defineEmits(['chat-close']);

// 拖拽指令（局部指令，v-drag）
const vDrag = {
  mounted: function (el) {
    let x = 0;
    let y = 0;
    let l = 0;
    let t = 0;
    let isDown = false;
    el.onmousedown = function (e) {
      x = e.clientX;
      y = e.clientY;
      l = el.parentNode.offsetLeft;
      t = el.parentNode.offsetTop;
      isDown = true;
      el.style.cursor = 'move';
      window.onmousemove = function (e) {
        if (isDown == false) {
          return;
        }
        let nx = e.clientX;
        let ny = e.clientY;
        let nl = nx - (x - l);
        let nt = ny - (y - t);
        el.parentNode.style.left = nl + 'px';
        el.parentNode.style.top = nt + 'px';
      };
      window.onmouseup = function () {
        isDown = false;
        el.style.cursor = 'default';
        window.onmousemove = null;
        window.onmouseup = null;
      };
      return false;
    };
  },
};

const record = ref(null);
const scrollBox = ref(null);
const audio = ref(null);

const locations = ref(`${location.origin}`);
const change = ref(false);
const emojiGroup = chunk(emojiList, 20); // 表情列表
const emojiShow = ref(false);
const emojiTab = ref('default'); // 表情面板当前 tab：default=默认表情 qq=QQ表情
const recordList = ref([]);
const limit = 20;
const loading = ref(false);
const finished = ref(false);
const chatCont = ref('');
const service = ref(null);
const serviceData = ref({});
const uploadAction = ref('');
const notice = ref('');
const muted = ref(false);
const audioSrc = ref('');
const upperId = ref(0);
const uploadData = ref({});
const is_tourist = ref(1); // 0登录状态，1未登录状态游客
const text = ref('');
const isLoad = ref(false);
const page = ref(1);
const tourist_avatar = ref(''); //游客头像
const tourist_uid = ref(''); //游客id
const toUid = ref(''); //客服id
const kufuToken = ref(''); // token
const copyright = ref(Session.get('copyright') || '');

watch(muted, (value) => {
  // 原实现读写 data 中的 audio（恒为 null 会抛错），这里直接绑定模板 audio 元素
  if (audio.value) audio.value.muted = value;
});

const records = computed(() => {
  return recordList.value.map((item, index) => {
    if (index) {
      if (new Date(item.add_time) - new Date(recordList.value[index - 1].add_time) >= 300000) {
        item.show = true;
      } else {
        item.show = false;
      }
    } else {
      item.show = false;
    }
    return item;
  });
});

// created
if (location.href.indexOf('kefu') != -1) uploadAction.value = Setting.apiBaseURL + '/kefu/tourist/upload';
let token = Cookies.get('auth._token.local1');
kufuToken.value = token ? token.split('Bearer ')[1] : '';

let chatSocket = null;

function closeEmojiPanel() {
  emojiShow.value = false;
}

// WebSocket 推送和首屏历史接口可能同时返回同一条消息，按消息 id 合并，避免欢迎语显示两次
function mergeMessage(data) {
  const index = recordList.value.findIndex((item) => String(item.id) === String(data.id));
  if (index === -1) {
    recordList.value.push(data);
  } else {
    recordList.value[index] = { ...recordList.value[index], ...data };
  }
}

function onChatMessage(data) {
  if (data.msn_type == 1) {
    data.msn = replace_em(data.msn);
  }
  mergeMessage(data);
  setTimeout(() => {
    nextTick(() => {
      if (record.value) record.value.scrollTop = record.value.scrollHeight - record.value.clientHeight;
    });
  }, 300);
}

function onTransfer(data) {
  toUid.value = data.toUid;
  chatSocket?.send({
    data: {
      id: toUid.value,
    },
    type: 'to_chat',
  });
}

function onSocketError() {
  ElMessage.error('连接失败');
}

function onSocketTip(data) {
  ElMessage.error(data.msg);
}

function onSocketSuccess() {
  is_tourist.value = 0;
}

function onServiceOnline(data) {
  if (Number(data.online) === 0 && String(data.uid) === String(toUid.value)) {
    change.value = true;
  }
}

// 来源标识与响应式布局解耦：公众号=1、移动 H5=3、桌面浏览器=0
function getFormType() {
  if (Auth.isWeixin()) return 1;
  return Auth._isMobile() ? 3 : 0;
}

onMounted(() => {
  window.addEventListener('click', closeEmojiPanel);
  getNotice();
  Socket.then((ws) => {
    chatSocket = ws;
    if (kufuToken.value) {
      ws.send({
        type: 'login',
        data: kufuToken.value,
      });
    }
    getService();
    ws.$on(['reply', 'chat'], onChatMessage);
    ws.$on('to_transfer', onTransfer);
    ws.$on('socket_error', onSocketError);
    ws.$on('err_tip', onSocketTip);
    ws.$on('success', onSocketSuccess);
    ws.$on('online', onServiceOnline);
  });
  text.value = replace_em('[em-smiling_imp]');
});

onBeforeUnmount(() => {
  window.removeEventListener('click', closeEmojiPanel);
  if (!chatSocket) return;
  chatSocket.$off(['reply', 'chat'], onChatMessage);
  chatSocket.$off('to_transfer', onTransfer);
  chatSocket.$off('socket_error', onSocketError);
  chatSocket.$off('err_tip', onSocketTip);
  chatSocket.$off('success', onSocketSuccess);
  chatSocket.$off('online', onServiceOnline);
});

// 关闭
function closeChange(msg) {
  change.value = msg;
}
// 统一发送处理
function sendMsg(msn, type) {
  let obj = {
    type: 'chat',
    data: {
      msn,
      type,
      is_tourist: is_tourist.value,
      to_uid: toUid.value,
      tourist_uid: tourist_uid.value,
      tourist_avatar: tourist_avatar.value,
      form_type: getFormType(),
    },
  };
  Socket.then((ws) => {
    ws.send(obj);
  });
}
// 游客身份本地持久化：缓存游客 uid、头像与对接客服 uid，刷新页面后复用同一身份，聊天记录才连续
const TOURIST_STORAGE_KEY = 'kefu_tourist_info';

function getLocalTourist() {
  try {
    return JSON.parse(localStorage.getItem(TOURIST_STORAGE_KEY)) || {};
  } catch (e) {
    return {};
  }
}

function setLocalTourist(data) {
  localStorage.setItem(TOURIST_STORAGE_KEY, JSON.stringify(data));
}

function replaceBrokenImage(event, fallback) {
  const image = event.currentTarget;
  if (image.dataset.fallbackApplied) return;
  image.dataset.fallbackApplied = 'true';
  image.src = fallback;
}

// 随机客服
function getService() {
  const local = getLocalTourist();
  serviceListApi({
    token: kufuToken.value || '',
    tourist_uid: local.uid || 0,
    to_uid: local.toUid || 0,
  })
    .then((res) => {
      serviceData.value = res.data;
      upperId.value = 0;
      toUid.value = res.data.uid;
      // 游客模式优先复用本地身份；登录用户按 token 返回的真实 uid
      tourist_uid.value = local.uid || res.data.tourist_uid;
      tourist_avatar.value = local.avatar || res.data.tourist_avatar;
      // serviceData.tourist_uid 用于判定消息左右位置，需与本地复用的身份保持一致
      serviceData.value.tourist_uid = tourist_uid.value;
      if (!kufuToken.value && tourist_uid.value) {
        setLocalTourist({ uid: tourist_uid.value, avatar: tourist_avatar.value, toUid: toUid.value });
      }
      let obj = {
        data: {
          id: res.data.uid,
          tourist_uid: tourist_uid.value,
          form_type: getFormType(),
        },
        type: 'to_chat',
      };
      Socket.then((ws) => {
        ws.send(obj);
      });
      getRecordList();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
      change.value = true;
    });
}
function roomClick(event) {
  // if (
  //     !event.target.classList.contains("emoji-panel") &&
  //     !event.target.classList.contains("emoji-btn") &&
  //     !event.target.classList.contains("icon-biaoqing") &&
  //     this.emojiShow
  // ) {
  //     this.emojiShow = false;
  // }
}
// enter 发送
function ctrlEnter(e) {
  if (e.keyCode == 13) {
    e.preventDefault();
  }
  if (chatCont.value.trim()) {
    sendMessage();
  }
}
// 关闭聊天窗口
function close() {
  emit('chat-close');
}
// 选择表情
function selectEmoji(data) {
  let val = `[${data}]`;
  chatCont.value += val;
  emojiShow.value = false;
}
// 选择 QQ 表情（Unicode 原生字符直接追加，随文本发送）
function selectQq(item) {
  chatCont.value += item.char;
  emojiShow.value = false;
}
// 聊天表情转换
function replace_em(str) {
  str = str.replace(/\[em-([\s\S]*)\]/g, "<span class='em em-$1'/></span>");
  return str;
}
function onScroll(event) {
  if (event.target.scrollTop <= 30) {
    getRecordList();
  }
}
// 聊天记录
function getRecordList() {
  if (loading.value) {
    return;
  }
  if (finished.value) {
    return;
  }
  loading.value = true;
  chatListApi({
    uid: serviceData.value.uid,
    limit: limit,
    upperId: upperId.value,
    token: kufuToken.value,
    tourist_uid: tourist_uid.value,
  })
    .then((res) => {
      if (res.data.length === 0) return (loading.value = false);
      res.data.forEach((el) => {
        if (el.msn_type == 1) {
          el.msn = replace_em(el.msn);
        }
      });
      let selector = '';
      if (upperId.value == 0) {
        selector = `chat_${res.data[res.data.length - 1].id}`;
      } else {
        selector = `chat_${recordList.value[0].id}`;
      }
      const currentIds = new Set(recordList.value.map((item) => String(item.id)));
      recordList.value = [...res.data.filter((item) => !currentIds.has(String(item.id))), ...recordList.value];
      upperId.value = res.data.length > 0 ? res.data[0].id : 0;
      loading.value = false;
      finished.value = res.data.length < limit;
      nextTick(function () {
        setPageScrollTo(selector);
      });
    })
    .catch((err) => {
      ElMessage.error(err.msg);
      loading.value = false;
    });
}
// 设置页面滚动位置
function setPageScrollTo(selector) {
  nextTick(() => {
    if (selector) {
      setTimeout(() => {
        let num = parseFloat(document.getElementById(selector).offsetTop) - 60;
        record.value.scrollTop = num;
      }, 0);
    } else {
      var container = document.querySelector('#chat_scroll');
      record.value.scrollTop = container.offsetHeight;
      setTimeout((res) => {
        if (record.value.scrollTop != scrollBox.value.offsetHeight) {
          record.value.scrollTop = document.querySelector('#chat_scroll').offsetHeight;
        }
      }, 300);
    }
  });
}
// 表情包显示隐藏
function emojiSwitch() {
  emojiShow.value = !emojiShow.value;
}
// 发送消息
function sendMessage() {
  sendMsg(chatCont.value, 1);
  chatCont.value = '';
}
// 死代码：原实现依赖已移除的 this.$auth（全库无调用），保留结构
function chat(data) {
  if (audio.value) {
    audio.value.play();
  }
  recordList.value.push(data);
  nextTick(() => {
    record.value.scrollTop = record.value.scrollHeight - record.value.clientHeight;
  });
}
function chatEnd() {
  if (navigator.userAgent.indexOf('MSIE') > 0) {
    if (navigator.userAgent.indexOf('MSIE 6.0') > 0) {
      window.opener = null;
      window.close();
    } else {
      window.open('', '_top');
      window.top.close();
    }
  } else if (navigator.userAgent.indexOf('Firefox') > 0) {
    window.location.href = 'about:blank ';
  } else {
    window.opener = null;
    window.open('', '_self', '');
    window.close();
  }
}
// 广告
function getNotice() {
  getAdvApi().then((res) => {
    notice.value = res.data.content;
  });
}
function beforeUpload(file) {
  if (isPicUpload(file)) {
    uploadData.value = {
      filename: file,
      token: kufuToken.value,
    };
    let promise = new Promise((resolve) => {
      nextTick(function () {
        resolve(true);
      });
    });
    return promise;
  }
}
function handleFormatError(file) {
  ElMessage.error('上传图片只能是 jpg、jpg、jpeg、gif 格式!');
}
function uploadSuccess(res) {
  sendMsg(res.data.url, 3);
}
function uploadError(error) {
  ElMessage.error(error);
}
</script>

<style lang="scss" scoped>
@import '@/plugin/emoji-awesome/css/google.min.css';
li {
  list-style-type: none;
}
.chat-room {
  .room {
    border-radius: 10px;
    position: fixed;
    top: calc(50% - 327px);
    left: calc(50% - 365px);
    z-index: 999;
    display: flex;
    flex-direction: column;
    width: 730px;
    height: 654px;
    background-color: #ffffff;
    overflow: hidden;
    box-shadow: 1px 1px 15px 0 rgba(0, 0, 0, 0.1);

    &.win {
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
    }

    .head {
      display: flex;
      align-items: center;
      height: 50px;
      padding-right: 15px;
      padding-left: 20px;
      background: linear-gradient(270deg, var(--prev-color-primary) 0%, #3875ea 100%);

      .image {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        overflow: hidden;

        img {
          display: block;
          width: 100%;
          height: 100%;
          object-fit: cover;
        }
      }

      .name {
        flex: 1;
        min-width: 0;
        margin-left: 15px;
        font-size: 16px;
        color: #ffffff;
      }

      .iconfont {
        width: 25px;
        height: 25px;
        font-size: 16px;
        line-height: 25px;
        text-align: center;
        color: #ffffff;
        cursor: pointer;
      }
    }

    .main {
      flex: 1;
      display: flex;
      min-height: 0;

      .chat {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
      }

      .record {
        flex: 1;
        min-height: 0;
        overflow-x: hidden;
        overflow-y: auto;

        &::-webkit-scrollbar {
          display: none;
        }

        ul {
          padding: 20px;
        }

        li {
          ~ li {
            margin-top: 20px;
          }

          &::after {
            content: '';
            display: block;
            height: 0;
            clear: both;
            visibility: hidden;
          }

          &.right {
            .avatar {
              float: right;
            }

            .content {
              text-align: right;

              > div {
                text-align: left;
              }
            }
          }
        }

        .time-tag {
          padding-top: 10px;
          padding-bottom: 30px;
          text-align: center;
          color: #999999;
        }

        .avatar {
          float: left;
          width: 40px;
          height: 40px;
          border-radius: 50%;
          overflow: hidden;

          &.right {
            float: right;
          }

          img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
          }
        }

        .content {
          margin-right: 56px;
          margin-left: 56px;
        }

        .text {
          display: inline-block;
          min-height: 41px;
          padding: 10px 12px;
          border-radius: 10px;
          background-color: #f5f5f5;
          font-size: 15px;
          line-height: 21px;
          color: #000000;
        }

        .image {
          display: inline-block;
          max-width: 50%;
          border-radius: 10px;
          overflow: hidden;

          img {
            display: block;
            max-width: 100%;
          }
        }

        .goods,
        .order {
          display: inline-flex;
          align-items: center;
          width: 320px;
          padding: 10px 13px;
          border-radius: 10px;
          background-color: #f5f5f5;
        }

        .thumb {
          width: 60px;
          height: 60px;
          border-radius: 5px;
          overflow: hidden;

          img {
            display: block;
            width: 100%;
            height: 100%;
          }
        }

        .intro {
          flex: 1;
          min-width: 0;
          margin-left: 10px;

          .name {
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            font-size: 15px;
            color: #000000;
          }

          .attr {
            margin-top: 5px;
            font-size: 12px;
            color: #999999;

            span {
              vertical-align: middle;

              ~ span {
                margin-left: 10px;
              }
            }
          }

          .group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 5px;

            .money {
              font-size: 14px;
              color: #ff0000;
            }

            a {
              font-size: 12px;
              color: var(--prev-color-primary);
            }
          }
        }
      }

      .editor {
        display: flex;
        flex-direction: column;
        height: 162px;
        border-top: 1px solid #ececec;

        > div {
          &:first-child {
            font-size: 0;
          }
        }

        button {
          border: none;
          background: none;
          outline: none;

          ~ button {
            margin-left: 20px;
          }

          &.end {
            font-size: 15px;
          }

          &:hover {
            color: var(--prev-color-primary);

            .iconfont {
              color: var(--prev-color-primary);
            }
          }
        }

        .editor-hd {
          position: relative;
          display: flex;
          justify-content: space-between;
          align-items: center;
          height: 50px;
          padding-right: 20px;
          padding-left: 20px;

          .iconfont {
            line-height: 1;
            color: #333333;
          }

          .emoji-panel {
            position: absolute;
            bottom: 100%;
            left: 5px;
            display: flex;
            flex-direction: column;
            width: 390px;
            height: 240px;
            padding-bottom: 0;
            border: 1px solid #ececec;
            margin-bottom: 5px;
            box-sizing: border-box;
            background-color: #ffffff;
            box-shadow: 1px 0 16px 0 rgba(0, 0, 0, 0.05);
            overflow: hidden;

            .emoji-tabs {
              display: flex;
              gap: 8px;
              padding: 10px 0 8px 10px;
              border-bottom: 1px solid #ececec;

              .tab-item {
                padding: 2px 12px;
                font-size: 12px;
                color: #999999;
                cursor: pointer;
                border-radius: 4px;

                &:hover {
                  color: var(--prev-color-primary);
                }

                &.on {
                  color: var(--prev-color-primary);
                  background: var(--prev-color-primary-light-9);
                }
              }
            }

            .emoji-list {
              flex: 1;
              min-height: 0;
              max-height: none;
              padding-bottom: 10px;
              overflow-y: auto;

              &::-webkit-scrollbar {
                width: 4px;
              }

              &::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 2px;
              }
            }

            .em {
              width: 28px;
              height: 28px;
              padding: 4px;
              margin-top: 10px;
              margin-left: 10px;
              box-sizing: border-box;

              &:hover {
                background-color: #ececec;
              }
            }

            // QQ 表情为 Unicode 字符，用字号呈现
            .qq-item {
              display: inline-block;
              width: 28px;
              height: 28px;
              padding: 3px 4px 4px;
              margin-top: 10px;
              margin-left: 10px;
              box-sizing: border-box;
              font-size: 20px;
              line-height: 21px;
              text-align: center;
              font-style: normal;
              cursor: pointer;

              &:hover {
                background-color: #ececec;
              }
            }
          }
        }

        .icon-biaoqing1,
        .icon-tupian1 {
          font-size: 22px;
        }

        .icon-guanji {
          margin-right: 5px;
          font-size: 15px;
        }

        .editor-bd {
          flex: 1;
          min-height: 0;

          textarea {
            display: block;
            width: 100%;
            height: 100%;
            padding-right: 20px;
            padding-left: 20px;
            border: none;
            outline: none;
            resize: none;
            white-space: pre-wrap;
            overflow-wrap: break-word;

            &::-webkit-scrollbar {
              display: none;
            }
          }
        }

        .editor-ft {
          display: flex;
          justify-content: flex-end;
          align-items: center;
          padding-right: 20px;
          padding-bottom: 20px;

          button {
            width: 68px;
            height: 26px;
            border: none;
            border-radius: 3px;
            background-color: #3875ea;
            outline: none;
            font-size: 13px;
            color: #ffffff;

            &:disabled {
              background-color: #cccccc;
            }
          }
        }
      }

      .notice {
        display: flex;
        flex-direction: column;
        width: 260px;
        border-left: 1px solid #ececec;

        .rich {
          flex: 1;
          min-height: 0;
          padding: 18px 18px 0;
          overflow-x: hidden;
          overflow-y: auto;

          &::-webkit-scrollbar {
            display: none;
          }

          :deep(img) {
            width: 100%;
          }

          :deep(video) {
            width: 100%;
          }
        }

        .copy {
          padding-top: 15px;
          padding-bottom: 15px;
          font-size: 12px;
          text-align: center;
          color: #cccccc !important;
          text-decoration: none;
        }
      }
    }
  }
}

// 单入口响应式布局：仅调整展示，不重新挂载页面或 Socket
@media (max-width: 900px) {
  .chat-room .room .main .notice {
    display: none;
  }
}

@media (max-width: 767px) {
  .chat-room {
    width: 100%;
    height: 100dvh;
    padding: 4px;
    box-sizing: border-box;
    background: var(--prev-bg-main-color);

    .room,
    .room.win {
      top: 4px;
      left: 4px;
      width: calc(100% - 8px);
      height: calc(100dvh - 8px);
      border-radius: 6px;
      box-shadow: none;

      .head {
        flex: none;
        height: 56px;
        padding: 0 16px;

        .image {
          width: 34px;
          height: 34px;
        }

        .name {
          margin-left: 10px;
          font-size: 16px;
        }

        .icon-guanbi5 {
          display: none;
        }
      }

      .main {
        width: 100%;

        .chat {
          background: var(--prev-bg-main-color);
        }

        .record {
          background: var(--prev-bg-main-color);

          ul {
            padding: 16px 12px;
          }

          li ~ li {
            margin-top: 16px;
          }

          .time-tag {
            padding: 6px 0 18px;
            color: var(--prev-color-text-secondary);
          }

          .avatar {
            width: 36px;
            height: 36px;
          }

          .content {
            margin-right: 48px;
            margin-left: 48px;
          }

          .text {
            max-width: 100%;
            min-height: 36px;
            padding: 8px 10px;
            border-radius: 6px;
            background: var(--prev-bg-white);
            font-size: 15px;
          }

          .image {
            max-width: 72%;
          }

          .goods,
          .order {
            width: min(280px, calc(100vw - 108px));
            border-radius: 6px;
            background: var(--prev-bg-white);
          }
        }

        .editor {
          position: relative;
          display: block;
          flex: none;
          height: 152px;
          margin: 0 12px 12px;
          overflow: visible;
          box-sizing: border-box;
          background: transparent;

          .editor-hd {
            position: absolute;
            z-index: 2;
            bottom: 10px;
            left: 14px;
            height: 32px;
            padding: 0;

            > div:first-child {
              display: flex;
              align-items: center;
              height: 32px;
            }

            button {
              display: flex;
              align-items: center;
              justify-content: center;
              width: 32px;
              height: 32px;
              color: var(--prev-color-text-regular);

              ~ button {
                margin-left: 10px;
              }
            }

            .emoji-panel {
              display: flex;
              flex-direction: column;
              bottom: calc(100% + 8px);
              left: 0;
              width: min(360px, calc(100vw - 48px));
              height: min(240px, calc(100dvh - 220px));
              max-height: none;
              padding-bottom: 0;
              box-sizing: border-box;
              border-color: var(--prev-border-color-lighter);
              background: var(--prev-bg-white);
              overflow: hidden;

              .emoji-tabs {
                flex: none;
              }

              .emoji-list {
                flex: 1;
                min-height: 0;
                max-height: none;
                padding-bottom: 10px;
                overflow-y: auto;
              }
            }
          }

          .editor-bd {
            height: 100%;
            overflow: hidden;
            border: 1px solid var(--prev-border-color-lighter);
            border-radius: 6px;
            box-sizing: border-box;
            background: var(--prev-bg-white);
            transition: border-color 0.2s ease;

            textarea {
              box-sizing: border-box;
              padding: 14px 16px 52px;
              border-radius: inherit;
              background: transparent;
              font-size: 15px;
            }
          }

          &:focus-within .editor-bd {
            border-color: var(--prev-color-primary);
          }

          .editor-ft {
            position: absolute;
            right: 14px;
            bottom: 10px;
            z-index: 2;
            padding: 0;

            button {
              width: 68px;
              height: 32px;
              border-radius: 4px;
              background: var(--prev-color-primary);

              &:disabled {
                background: var(--prev-color-text-placeholder);
              }
            }
          }
        }
      }
    }
  }
}
</style>
