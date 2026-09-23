<template>
  <div class="chat-box">
    <div class="head-box">
      <div class="back" v-db-click @click="goBack"><span class="iconfont iconfanhui"></span></div>
      <div class="title">{{ nickname }} - 对话详情</div>
    </div>
    <div class="chat-scroll-box">
      <!-- vue-scroll 为 Vue2 专用库且未再全局注册，改用原生滚动容器（触顶加载更早的历史消息） -->
      <div class="chat-scroll-inner" ref="scrollBox" @scroll="handleChatScroll">
        <div id="chatBox" class="chat" ref="chat" style="padding: 0.3rem 0.3rem 0">
          <div v-for="(item, index) in records" :key="index" :id="`chat_${item.id}`">
            <div class="day-box" v-if="item.show">{{ item.time }}</div>
            <div class="chat-item" :class="{ 'right-box': item.uid == kefuInfo.uid }">
              <img
                class="avatar"
                v-lazy="item.avatar"
                mode=""
                v-db-click
                @click="goUserInfo(item, item.uid == kefuInfo.uid)"
              />
              <!-- 消息 -->
              <div class="msg-box" v-if="item.msn_type == 1" v-html="item.msn"></div>
              <!-- 图片 -->
              <div class="img-box" v-if="item.msn_type == 3" v-viewer><img v-lazy="item.msn" mode="widthFix" /></div>
              <!-- 商品卡片 -->
              <div class="product-box" v-if="item.msn_type == 5 && item.productInfo">
                <img v-lazy="item.productInfo.image" alt="" />
                <div class="info">
                  <p class="name line1">{{ item.productInfo.store_name }}</p>
                  <p class="price">￥{{ item.productInfo.price }}</p>
                </div>
              </div>
              <!-- 订单卡片 -->
              <div class="order-box" v-if="item.msn_type == 6 && item.orderInfo">
                <div class="title">订单</div>
                <div class="info">
                  <img v-lazy="item.orderInfo.image" alt="" />
                  <div class="product-info">
                    <p class="name line1">{{ item.orderInfo.title }}</p>
                    <p class="price">￥{{ item.orderInfo.pay_price }}</p>
                    <p class="status-txt">
                      {{ item.orderInfo.pay_status_name
                      }}{{ item.orderInfo.status_name ? ' · ' + item.orderInfo.status_name : '' }}
                    </p>
                  </div>
                </div>
                <p class="order-no">订单号：{{ item.orderInfo.order_no }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-box">
      <div class="words" v-db-click @click="showWords"><span class="iconfont iconhuashu1"></span></div>
      <div class="input-box">
        <el-input v-model="con" placeholder="请输入内容" style="font-size: 0.28rem" />
        <span class="iconfont iconfasong" v-db-click @click="sendText" :class="{ isSend: isSend }"></span>
      </div>
      <div class="emoji" v-db-click @click="openBox(1)"><span class="iconfont iconbiaoqing2"></span></div>
      <div class="more" v-db-click @click="openBox(2)"><span class="iconfont icongengduozhankai1"></span></div>
    </div>
    <!-- 工具 -->
    <div class="tool-wrapper" v-if="isTool">
      <div class="tool-item">
        <el-upload
          :show-file-list="false"
          :action="fileUrl"
          :before-upload="beforeUpload"
          :data="uploadData"
          :headers="header"
          :multiple="true"
          :on-success="handleSuccess"
          accept="image/*"
          :on-format-error="handleFormatError"
        >
          <img src="../static/tool-01.png" mode="" />
          <div>图片</div>
        </el-upload>
      </div>
      <div class="tool-item" v-db-click @click="goTransfer">
        <img src="../static/tool-02.png" mode="" />
        <div>转接</div>
      </div>
    </div>
    <!-- 表情 -->
    <div class="banner slider-banner" v-show="isSwiper">
      <!-- 表情类型 tab：默认/QQ，与客服工作台及用户端表情保持同步 -->
      <div class="emoji-tabs">
        <span class="tab-item" :class="{ on: emojiTab === 'default' }" v-db-click @click="emojiTab = 'default'"
          >默认表情</span
        >
        <span class="tab-item" :class="{ on: emojiTab === 'qq' }" v-db-click @click="emojiTab = 'qq'">QQ表情</span>
      </div>
      <!-- TODO(vue3): swiper11 组件不再支持 :options 写法，仅保留滑动结构 -->
      <swiper class="swiper-wrapper" ref="mySwiper" :options="swiperOptions" v-if="emojiTab === 'default'">
        <swiper-slide v-for="(emojiList, index) in emojiGroup" :key="index">
          <i class="em" :class="emoji" v-for="emoji in emojiList" :key="emoji" v-db-click @click="addEmoji(emoji)"></i>
        </swiper-slide>
      </swiper>
      <!-- QQ 表情：Unicode 原生字符，点击直接追加到输入框，随文本发送 -->
      <div class="emoji-list" v-else>
        <span
          class="qq-item"
          :title="item.name"
          v-db-click
          @click="addQqEmoji(item)"
          v-for="(item, index) in qqEmojis"
          :key="index"
          >{{ item.char }}</span
        >
      </div>
    </div>
    <!-- 常用语 -->
    <words :isWords="isWords" @closeBox="closeBox" @selectMsg="selectMsg"></words>
    <!-- 转接 -->
    <div class="transfer-mask" v-if="isTransfer">
      <div class="content" :class="{ on: isTransfer }">
        <div class="title">转接客服<span class="iconfont iconcha" v-db-click @click="closeTransfer"></span></div>
        <div class="list-wrapper">
          <el-radio-group v-model="activeKF">
            <el-radio
              class="list-item"
              v-for="(item, index) in transferList"
              :label="item.uid"
              :value="item.uid"
              :key="index"
            >
              <div class="avatar-box">
                <img v-lazy="item.avatar" alt="" />
              </div>
              <p class="nickName">{{ item.wx_name }}</p>
            </el-radio>
          </el-radio-group>
        </div>
        <el-button class="btn" v-db-click @click="confirm">确定</el-button>
      </div>
    </div>
  </div>
</template>

<script setup>
import __asset_notice_0 from '@/assets/video/notice.mp3';

const mp3 = new Audio(__asset_notice_0);
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import dayjs from 'dayjs';
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';
import Setting from '@/setting';
import words from '../components/words';
import { Socket } from '@/pages/plugin/kefu/libs/socket';
import emojiList, { qqEmojis } from '@/utils/emoji';
import { serviceList, transferList as transferListApi, serviceTransfer } from '@/pages/plugin/kefu/api';
import { getCookies } from '@/libs/util';
import { isPicUpload } from '@/utils';

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

defineOptions({ name: 'adminChat_index' });

const route = useRoute();
const router = useRouter();

const scrollBox = ref(null);
const chat = ref(null);
const mySwiper = ref(null);

const swiperOptions = ref({});
const status = ref(false);
const loading = ref(false);
const isTool = ref(false);
const isSwiper = ref(false);
const emojiTab = ref('default'); // 表情面板当前 tab：default=默认表情 qq=QQ表情
const isWords = ref(false);
const autoplay = ref(false);
const circular = ref(true);
const interval = ref(3000);
const duration = ref(500);
const emojiGroup = chunk(emojiList, 21);

const con = ref('');
const toUid = ref('');
const limit = 15;
const upperId = ref(0);
const chatList = ref([]);
const kefuInfo = ref({});
const scrollTop = ref(0);
const active = ref(true);
const isScroll = ref(true);
const oldHeight = ref(0);
const selector = ref('');
const transferList = ref([]); //转接列表
const isTransfer = ref(false);
const uploadData = ref({}); // 上传参数
const header = ref({});
const fileUrl = ref('');
const tourist = ref(0);
const activeKF = ref('');
const nickname = ref('');

const isSend = computed(() => {
  if (con.value.length == 0) {
    return false;
  } else {
    return true;
  }
});
// 消息时间分隔线格式：当天只显示时间，同年显示月日+时间，跨年补年份（与消息列表规则一致，避免英文月份）
function formatChatTime(addTime) {
  const day = dayjs(addTime * 1000);
  const now = dayjs();
  if (day.isSame(now, 'day')) return day.format('H:mm');
  if (day.isSame(now, 'year')) return day.format('M月D日 H:mm');
  return day.format('YYYY年M月D日 H:mm');
}
const records = computed(() => {
  if (!chatList.value.length) return;
  return chatList.value.map((item, index) => {
    item.time = formatChatTime(item.add_time);
    if (index) {
      if (item.add_time - chatList.value[index - 1].add_time >= 300) {
        item.show = true;
      } else {
        item.show = false;
      }
    } else {
      item.show = true;
    }
    return item;
  });
});

// 历史分页请求进行中标记：防止触顶滚动连续触发重复加载
const historyLoading = ref(false);

// created
fileUrl.value = Setting.apiBaseURL + '/kefu/upload';
toUid.value = route.query.toUid || '';
nickname.value = route.query.nickname || '';
// cookie 中的 kefuInfo 为 JSON 字符串；历史版本曾写入非法值 "[object Object]"，解析失败回退空对象，避免 setup 抛错导致整页白屏
try {
  kefuInfo.value = JSON.parse(getCookies('kefuInfo') || '{}');
} catch (e) {
  kefuInfo.value = {};
}
Promise.all([getChatList(), getTransferList()]);

// 消息接收：具名 handler，卸载时需 $off——Socket 事件总线是模块级单例，反复进出会话若不解绑会重复注册、一条消息 push 多次
function onChatMessage(data) {
  if (data.msn_type == 1 || data.msn_type == 2) {
    data.msn = replace_em(data.msn);
  }
  chatList.value.push(data);

  nextTick(() => {
    scrollBom();
  });
}
// 收到游客消息提示音
function onReplyNotice(data) {
  // 游客消息不区分当前所在 tab（用户列表/游客列表），统一播放提示音，避免漏提醒
  if (Number(data.is_tourist) === 1) mp3.play();
}

onMounted(() => {
  window.document.title = `${route.query.nickname || ''} - 对话详情`;
  // 上传头部token
  header.value['Authori-zation'] = 'Bearer ' + getCookies('kefu_token');
  let isLogin = JSON.parse(sessionStorage.getItem('wsLogin'));
  Socket.then((ws) => {
    if (isLogin) {
      ws.send({
        data: {
          id: toUid.value,
        },
        type: 'to_chat',
      });
    } else {
      ws.send({
        type: 'kefu_login',
        data: getCookies('kefu_token'),
      });
    }
    // 消息接收
    ws.$on(['reply', 'chat'], onChatMessage);
    ws.$on('reply', onReplyNotice);
    ws.$on('socket_error', () => {
      // 原 this.$util.Tips（遗留全局），改用 ElMessage
      ElMessage.error('连接失败');
    });
  });
});

onBeforeUnmount(() => {
  Socket.then((ws) => {
    // 解绑本页监听，防止重复注册导致消息重复显示
    ws.$off(['reply', 'chat'], onChatMessage);
    ws.$off('reply', onReplyNotice);
    ws.send({
      data: {
        id: 0,
      },
      type: 'to_chat',
    });
  });
});

function goBack() {
  router.go(-1);
}
function handleFormatError(file) {
  ElMessage.error('上传图片只能是 jpg、jpg、jpeg、gif 格式!');
}
// 用户详情
function goUserInfo(item, status) {
  if (!status) {
    router.push({
      path: `${Setting.routePre}/kefu/user/index/${item.uid}/${item.type}`,
    });
  }
}
// 上传之前
function beforeUpload(file) {
  return isPicUpload(file);
}
// 上传成功
function handleSuccess(res, file, fileList) {
  if (res.status === 200) {
    ElMessage.success(res.msg);
    sendMsg(res.data.url, 3);
  } else {
    ElMessage.error(res.msg);
  }
}
// 滚动到底部
function scrollBom() {
  setTimeout((res) => {
    if (scrollBox.value) {
      scrollBox.value.scrollTop = scrollBox.value.scrollHeight;
    }
  }, 300);
}
// 底部功能区打开
function openBox(key) {
  if (key == 1) {
    isTool.value = false;
    isSwiper.value = !isSwiper.value;
  } else {
    isSwiper.value = false;
    isTool.value = !isTool.value;
  }
  nextTick(() => {
    scrollBom();
  });
}
function showWords() {
  isWords.value = true;
}

// 转接
function goTransfer() {
  isTransfer.value = true;
}
// 转接关闭
function closeTransfer() {
  transferList.value.forEach((el, index) => {
    el.isCheck = false;
  });
  isTransfer.value = false;
}
// 转接确认
function confirm() {
  if (activeKF.value) {
    serviceTransfer({
      uid: toUid.value,
      kefuToUid: activeKF.value,
    })
      .then((res) => {
        transferList.value.forEach((el, index) => {
          el.isCheck = false;
        });
        ElMessage.success(res.msg);
        isTransfer.value = false;
      })
      .catch((error) => {
        ElMessage.error(error.msg);
      });
  } else {
    ElMessage.error('请选择转接客服');
  }
}
// 表情点击
function addEmoji(item) {
  let val = `[${item}]`;
  con.value += val;
}
// QQ 表情点击（Unicode 原生字符直接追加，随文本发送）
function addQqEmoji(item) {
  con.value += item.char;
}
// 聊天表情转换
function replace_em(str) {
  str = str.replace(/\[em-([\s\S]*)\]/g, "<span class='em em-$1'/></span>");
  return str;
}
// 获取聊天列表
function getChatList() {
  serviceList({
    limit: limit,
    uid: toUid.value,
    upperId: upperId.value,
    is_tourist: route.query.is_tourist,
  })
    .then((res) => {
      historyLoading.value = false;
      res.data.forEach((el) => {
        if (el.msn_type == 1 || el.msn_type == 2) {
          el.msn = replace_em(el.msn);
        }
      });
      // 锚点：首屏定位到最新一条；历史分页定位到本次加载前的旧首条，避免视口跳动
      let selectorStr = '';
      if (upperId.value == 0) {
        selectorStr = `chat_${res.data[res.data.length - 1].id}`;
      } else {
        selectorStr = `chat_${chatList.value[0].id}`;
      }
      selector.value = selectorStr;
      chatList.value = [...res.data, ...chatList.value];
      loading.value = false;
      isScroll.value = res.data.length >= limit;
      nextTick(() => {
        if (upperId.value == 0) {
          scrollBom();
        } else {
          scrollToAnchor(selectorStr);
        }
      });
    })
    .catch(() => {
      historyLoading.value = false;
    });
}
// 滚动到指定消息锚点：用 getBoundingClientRect 差值计算，不依赖 offsetParent
function scrollToAnchor(selectorStr) {
  const anchor = document.getElementById(selectorStr);
  if (anchor && scrollBox.value) {
    const boxRect = scrollBox.value.getBoundingClientRect();
    const anchorRect = anchor.getBoundingClientRect();
    scrollBox.value.scrollTop += anchorRect.top - boxRect.top - 60;
  }
}
// 原生滚动触顶：加载更早的历史消息（替代原 vue-scroll 下拉刷新）
function handleChatScroll(e) {
  const el = e.target;
  if (el.scrollTop <= 10 && isScroll.value && !historyLoading.value && chatList.value.length) {
    historyLoading.value = true;
    upperId.value = chatList.value[0].id;
    getChatList();
  }
}
// 发送消息
function sendText() {
  if (!isSend.value) {
    return ElMessage.error('请输入内容');
  }
  sendMsg(con.value, 1);
  con.value = '';
}
// ws发送
function sendMsg(msn, type) {
  let obj = {
    type: 'chat',
    data: {
      msn,
      type,
      to_uid: toUid.value,
    },
  };
  Socket.then((ws) => {
    ws.send(obj);
  });
}
// 图片上传（死代码：原实现依赖已移除的 this.$util，全库无调用）
function uploadImg() {
  // self.$util.uploadImageOne('upload/image', function (res) {
  //   if (res.status == 200) {
  //     self.sendMsg(res.data.url, 3);
  //   }
  // });
}
// 常用于选择
function selectWords(item) {
  isWords.value = false;
  sendMsg(item.message, 1);
}
// 滚动到底部（死代码：原实现依赖 uni-app 的 uni.createSelectorQuery，全库无调用）
function height() {
  // let info = uni.createSelectorQuery().select('.chat');
  // ...
}
// 转接列表
function getTransferList() {
  transferListApi({
    uid: toUid.value,
  }).then((res) => {
    res.data.list.forEach((item, index) => {
      item.isCheck = false;
    });
    transferList.value = res.data.list;
  });
}
// 关闭常用语
function closeBox() {
  isWords.value = false;
}
// 选择话术
function selectMsg(data) {
  con.value += data;
  isWords.value = false;
}
</script>
<style>
html,
body {
  font-size: 50px;
}
</style>
<style lang="scss" scoped>
.head-box {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  height: 45px;
  background: linear-gradient(85deg, #3875ea 0%, #1890fc 100%);
  span {
    position: absolute;
    width: 45px;
    height: 100%;
    left: 0;
    top: 0;
    text-align: center;
    line-height: 45px;
  }
}
.chat-box {
  display: flex;
  flex-direction: column;
  height: 100%;
  // height: 100vh;
  background: #f0f1f2;
  .head-box {
    background: linear-gradient(85deg, #3875ea 0%, #1890fc 100%);
    .title-hd {
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      height: 43px;
      padding: 0 0.3rem;
      color: #fff;
      .icon-fanhui {
        position: absolute;
        left: 0.3rem;
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
    padding: 0 0.3rem;
    color: rgba(0, 0, 0, 0.8);
    background: #f7f7f7;
    .words .iconfont {
      font-size: 0.5rem;
    }
    .input-box {
      display: flex;
      align-items: center;
      width: 4.92rem;
      height: 0.64rem;
      padding-right: 0.05rem;
      margin-left: 0.18rem;
      background-color: #fff;
      border-radius: 0.32rem;
      overflow: hidden;

      input {
        flex: 1;
        padding-left: 0.2rem;
        height: 100%;
        border: transparent !important;
      }
      :deep(.el-input__inner),
      .el-input__inner:hover,
      .el-input__inner:focus {
        border: transparent;
        box-shadow: none;
      }
      .iconfont {
        font-size: 0.5rem;
        color: #ccc;
        font-weight: normal;
      }
      .isSend {
        color: #3875ea;
      }
    }
    .emoji .iconfont {
      margin-left: 0.18rem;
      font-size: 0.5rem;
    }
    .more .iconfont {
      margin-left: 0.18rem;
      font-size: 0.5rem;
    }
  }
}
.tool-wrapper {
  display: flex;
  padding: 0.35rem 0.3rem 0.45rem;
  background: #fff;
  border-top: 1px solid #ececec;
  font-size: 0.24rem;
  color: #666;
  // 微信风格工具网格：图标居中在上、文字在下，靠左依次排列
  .tool-item {
    width: 1.5rem;
    text-align: center;
    :deep(.el-upload) {
      display: block;
      width: 100%;
    }
    img {
      display: block;
      width: 0.96rem;
      height: 0.96rem;
      margin: 0 auto 0.14rem;
      border-radius: 0.16rem;
    }
  }
}
.slider-banner {
  padding-bottom: 0.25rem;
  background: #fff;
  .emoji-tabs {
    display: flex;
    gap: 8px;
    padding: 8px 11px;
    border-bottom: 1px solid #ececec;
    .tab-item {
      padding: 2px 10px;
      font-size: 12px;
      color: #999;
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
  .em {
    display: inline-block;
    width: 0.5rem;
    height: 0.5rem;
    margin: 0.4rem 0 0 0.5rem;
  }
  // QQ 表情为 Unicode 字符，用字号呈现（px 单位不受页面 rem 基准影响）
  .emoji-list {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    align-items: center;
    justify-items: center;
    max-height: 180px;
    overflow-y: auto;
    padding-top: 8px;
    .qq-item {
      font-size: 20px;
      line-height: 1;
      font-style: normal;
      cursor: pointer;
      padding: 4px 0;
    }
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
      padding: 0 0.3rem 0.3rem;
      position: relative;
      border-bottom: 1px solid #f5f6f9;
      .tab-box {
        position: relative;
        display: flex;
        justify-content: space-between;
        padding: 0.4rem 2.2rem 0.3rem;
        font-size: 0.32rem;
        color: #9f9f9f;
        .on {
          color: #3875ea;
          font-weight: bold;
        }
        .right-icon {
          position: absolute;
          right: 0;
          top: 50%;
          transform: translateY(-50%);
          .iconfont {
            margin-left: 0.2rem;
            font-size: 0.48rem;
            color: #c8cad0;
          }
        }
      }
      .input-box {
        display: flex;
        align-items: center;
        width: 6.9rem;
        height: 0.64rem;
        padding-right: 0.05rem;
        margin-left: 0.18rem;
        border-radius: 0.32rem;
        overflow: hidden;
        :deep(.el-input__inner) {
          background: #f5f6f9;
        }
        :deep(.el-input__inner),
        .el-input__inner:hover,
        .el-input__inner:focus {
          border: transparent;
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
      display: flex;
      overflow: hidden;
      .scroll-left {
        width: 1.76rem;
        height: 100%;
        overflow-y: scroll;
        -webkit-overflow-scrolling: touch;
        background: #f5f6f9;
        .left-item {
          position: relative;
          display: flex;
          align-items: center;
          justify-content: center;
          width: 100%;
          height: 1.09rem;
          color: #282828;
          font-size: 0.26rem;
          &.active {
            color: #3875ea;
            background: #fff;
            &:after {
              content: ' ';
              position: absolute;
              left: 0;
              top: 50%;
              transform: translateY(-50%);
              width: 0.06rem;
              height: 0.46rem;
              background: #3875ea;
            }
          }
          &.add_cate {
            color: #9f9f9f;
            font-size: 0.26rem;
            .iconfont {
              margin-right: 0.1rem;
              font-size: 0.24rem;
            }
          }
        }
      }
      .right-box {
        flex: 1;
        overflow: scroll;
        -webkit-overflow-scrolling: touch;
      }
      .msg-item {
        padding: 0.25rem 0.3rem;
        color: #888888;
        font-size: 0.28rem;
        .title {
          margin-right: 0.2rem;
          color: #282828;
        }
        &.add-mg {
          display: flex;
          align-items: center;
          justify-content: flex-end;
          font-size: 0.28rem;
          padding: 0.15rem 0.3rem;
          .iconfont {
            font-size: 0.36rem;
            margin-right: 0.1rem;
          }
        }
      }
    }
  }
}
.chat-scroll-box {
  flex: 1;
  overflow: hidden;
  // 聊天记录滚动容器（替代未注册的 vue-scroll）：触顶加载历史、scrollBom 滚底
  .chat-scroll-inner {
    height: 100%;
    overflow-y: auto;
  }
  .day-box {
    margin-bottom: 0.2rem;
    font-size: 0.24rem;
    color: #999;
    text-align: center;
  }
  .chat-item {
    display: flex;
    margin-bottom: 0.36rem;
    font-size: 0.28rem;
    .avatar {
      width: 0.8rem;
      height: 0.8rem;
      border-radius: 50%;
    }
    .msg-box {
      display: flex;
      align-items: center;
      max-width: 4.52rem;
      margin-left: 0.22rem;
      padding: 0.1rem 0.24rem;
      background: #fff;
      border-radius: 0.14rem;
      word-break: break-all;
      color: #333;
    }
    .img-box {
      width: 2.7rem;
      margin-left: 0.22rem;

      img {
        width: 2.7rem;
        border-radius: 6px;
      }
    }
    .product-box {
      width: 4.52rem;
      background-color: #fff;
      border-radius: 0.14rem;
      overflow: hidden;
      margin-left: 0.22rem;

      img {
        width: 4.52rem;
      }
      .info {
        padding: 0.16rem 0.26rem;
        .name {
          font-size: 0.26rem;
          color: #282828;
        }
        .price {
          font-size: 0.36rem;
          color: #f74c31;

          text {
            font-size: 0.28rem;
          }
        }
      }
    }
    .order-box {
      width: 4.52rem;
      margin-left: 0.22rem;
      background-color: #fff;
      border-radius: 0.14rem;
      .title {
        padding: 0.15rem 0.2rem;
        font-size: 0.26rem;
        color: #282828;
        border-bottom: 1px solid #eceff8;
      }
      .info {
        display: flex;
        padding: 0.2rem;

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
          margin-left: 0.16rem;
          .name {
            font-size: 0.26rem;
          }
          .price {
            font-size: 0.3rem;
            color: #f74c31;
          }
          .status-txt {
            font-size: 0.22rem;
            color: #999;
          }
        }
      }
      .order-no {
        padding: 0 0.2rem 0.15rem;
        font-size: 0.2rem;
        color: #999;
        word-break: break-all;
      }
    }
    &.right-box {
      flex-direction: row-reverse;
      .msg-box {
        margin-left: 0;
        margin-right: 0.22rem;
        background-color: #9cec60;
      }
      .img-box {
        margin-left: 0;
        margin-right: 0.22rem;
      }
      .product-box {
        margin-left: 0;
        margin-right: 0.22rem;
      }
      .order-box {
        margin-left: 0;
        margin-right: 0.22rem;
      }
    }
    .em {
      margin: 0;
    }
  }
}
.transfer-mask {
  z-index: 30;
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  .content {
    position: absolute;
    left: 0;
    bottom: 0;
    transform: translateY(100%);
    top: 2.5rem;
    right: 0;
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 0.16rem 0.16rem 0px 0px;
    &.on {
      animation: up 0.2s linear;
      animation-fill-mode: forwards;
    }
    .title {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 1.1rem;
      font-size: 0.32rem;
      font-weight: bold;
      color: #282828;
      .iconfont {
        position: absolute;
        right: 0.3rem;
        top: 50%;
        transform: translateY(-50%);
        color: #c8cad0;
        font-size: 0.44rem;
      }
    }
    .list-wrapper {
      flex: 1;
      padding-left: 0.3rem;
      overflow-y: scroll;
      -webkit-overflow-scrolling: touch;
      .list-item {
        display: flex;
        align-items: center;
        padding: 0.16rem 0;
        border-bottom: 1px solid #f0f2f7;
        .check-box {
          width: 0.72rem;
        }
        .avatar-box img {
          width: 0.9rem;
          height: 0.9rem;
          border-radius: 0.06rem;
        }
        .nickName {
          margin-left: 0.28rem;
          color: #282828;
          font-size: 0.3rem;
          font-weight: bold;
        }
      }
    }
    .btn {
      width: 6.9rem;
      height: 0.86rem;
      margin: 0.5rem auto;
      color: #fff;
      background: #3875ea;
      font-size: 0.3rem;
      border-radius: 0.43rem !important;
    }
  }
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
