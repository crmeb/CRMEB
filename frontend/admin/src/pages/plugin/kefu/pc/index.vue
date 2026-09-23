<template>
  <div class="kefu-layouts">
    <div class="content-wrapper">
      <baseHeader :kefuInfo="kefuInfo" :online="online" @setOnline="setOnline"></baseHeader>
      <div class="container">
        <chatList @setDataId="setDataId" :userOnline="userOnline" :newRecored="newRecored"></chatList>
        <div class="chat-content">
          <div class="chat-body">
            <!-- 原 happy-scroll（Vue2-only）改为原生滚动：scrollTop 变化即滚动到该处，触顶触发 @vertical-start 等价逻辑 -->
            <div ref="chatScrollBox" class="chat-scroll-box" @scroll="onChatScroll">
              <div style="width: 600px; padding: 20px" id="chat_scroll" ref="scrollBox" v-loading="isLoad">
                <div
                  class="chat-item"
                  v-for="(item, index) in records"
                  :key="index"
                  :class="[{ 'right-box': item.uid == kefuInfo.uid }]"
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
                      <!-- 商品卡片 -->
                      <template v-if="item.msn_type == 5 && item.productInfo">
                        <div class="product-wrapper">
                          <div class="img-box">
                            <img v-lazy="item.productInfo.image" alt="" />
                          </div>
                          <div class="info">
                            <div class="name line1">{{ item.productInfo.store_name }}</div>
                            <div class="price">￥{{ item.productInfo.price }}</div>
                          </div>
                        </div>
                      </template>
                      <!-- 订单卡片 -->
                      <template v-if="item.msn_type == 6 && item.orderInfo">
                        <div class="order-wrapper">
                          <div class="img-box">
                            <img v-lazy="item.orderInfo.image" alt="" />
                          </div>
                          <div class="order-info">
                            <div class="name line1">{{ item.orderInfo.title }}</div>
                            <div class="sku">订单号：{{ item.orderInfo.order_no }}</div>
                            <div class="price-box">
                              <span>￥{{ item.orderInfo.pay_price }}</span>
                              <span class="more"
                                >{{ item.orderInfo.pay_status_name
                                }}{{ item.orderInfo.status_name ? '·' + item.orderInfo.status_name : '' }}</span
                              >
                            </div>
                          </div>
                        </div>
                      </template>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="chat-textarea">
            <div class="textarea-box">
              <el-input
                ref="chatInput"
                v-paste="handleParse"
                v-model="chatCon"
                type="textarea"
                :rows="4"
                resize="none"
                @keydown.enter="listen($event)"
                placeholder="请输入内容，按 Enter 发送，Shift+Enter 换行"
              />
              <!-- 工具栏收纳进输入卡片底行：功能图标居左、发送按钮居右 -->
              <div class="input-toolbar">
                <div class="left-wrappers">
                  <div class="icon-item" v-db-click @click.stop="isEmoji = !isEmoji">
                    <span class="iconfont iconbiaoqing1"></span>
                  </div>
                  <div class="icon-item">
                    <el-upload
                      :show-file-list="false"
                      :headers="header"
                      :data="uploadData"
                      :on-success="handleSuccess"
                      accept="image/*"
                      :on-format-error="handleFormatError"
                      :action="upload"
                      :before-upload="beforeUpload"
                    >
                      <span class="iconfont icontupian1"></span>
                    </el-upload>
                  </div>
                  <div class="icon-item" v-db-click @click.stop.stop="isMsg = true">
                    <span class="iconfont iconliaotian"></span>
                  </div>
                  <div class="right-wrapper">
                    <div class="icon-item" v-db-click @click.stop="isTransfer = !isTransfer">
                      <span class="iconfont iconzhuanjie"></span>
                      <span>转接</span>
                    </div>
                    <div class="transfer-box" v-if="isTransfer">
                      <transfer
                        @close="msgClose"
                        @transferPeople="transferPeople"
                        :userUid="userActive.to_uid"
                      ></transfer>
                    </div>
                    <div class="transfer-bg" v-if="isTransfer" v-db-click @click.stop="isTransfer = false"></div>
                  </div>
                </div>
                <el-button class="send-btn" type="primary" :disabled="disabled" v-db-click @click.stop="sendText">
                  <el-icon><Top /></el-icon>
                </el-button>
              </div>
              <!-- 表情面板：紧贴表情图标上方弹出；默认/QQ 两组表情 tab 切换 -->
              <div class="emoji-box" v-show="isEmoji">
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
                <div class="emoji-list" v-if="emojiTab === 'default'">
                  <div class="emoji-item" v-for="(emoji, index) in emojiList" :key="index">
                    <i class="em" :class="emoji" v-db-click @click.stop="select(emoji)"></i>
                  </div>
                </div>
                <div class="emoji-list" v-else>
                  <div
                    class="emoji-item qq"
                    v-for="(item, index) in qqEmojis"
                    :key="index"
                    :title="item.name"
                    v-db-click
                    @click.stop="selectQq(item)"
                  >
                    {{ item.char }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div>
          <rightMenu
            :isTourist="tourist"
            :isEnd="userActive.is_end"
            :uid="userActive.to_uid"
            :webType="userActive.type"
          ></rightMenu>
        </div>
      </div>
      <!-- 用户标签 -->
      <el-dialog v-model="isMsg" title="客服话术" class="none-radius isMsgbox" width="720px">
        <msgWindow v-if="isMsg" @close="msgClose" @activeTxt="activeTxt"></msgWindow>
      </el-dialog>
    </div>
  </div>
</template>

<script setup>
import __asset_notice_0 from '@/assets/video/notice.wav';

const mp3 = new Audio(__asset_notice_0);

// 浏览器自动播放策略：页面刷新后未经用户点击/按键前 play() 会被拒绝（表现为有提醒无声音），
// 首次交互时静音播放一次立即复位以解锁；解锁失败无需处理，下次交互会再次触发
function unlockAudio() {
  mp3
    .play()
    .then(() => {
      mp3.pause();
      mp3.currentTime = 0;
    })
    .catch(() => {});
  window.removeEventListener('click', unlockAudio);
  window.removeEventListener('keydown', unlockAudio);
}
window.addEventListener('click', unlockAudio);
window.addEventListener('keydown', unlockAudio);

// 播放新消息提示音：受右上角提示音开关控制，关闭时不响铃
function playNotice() {
  if (window.localStorage.getItem('kefu_sound_off') === '1') return;
  mp3.play().catch(() => {
    // 自动播放策略拒绝时静默失败：用户尚未交互，等解锁后新消息即可正常响铃
  });
}
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import dayjs from 'dayjs';
import Setting from '@/setting';
import baseHeader from './components/baseHeader';
import chatList from './components/chatList';
import rightMenu from './components/rightMenu';
import emojiList, { qqEmojis } from '@/utils/emoji';
import { Socket } from '@/pages/plugin/kefu/libs/socket';
import msgWindow from './components/msgWindow';
import transfer from './components/transfer';
import { serviceList, uploadImg, unreadNumApi } from '@/pages/plugin/kefu/api';
import { getCookies, removeCookies } from '@/libs/util';
import { serviceInfo } from '@/pages/plugin/kefu/api_mobile';
import { isPicUpload } from '@/utils';
import { useMediaStore } from '@/store/module/media';

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

defineOptions({ name: 'index' });

const router = useRouter();
// 移动端直接访问 PC 工作台时自动跳转手机版客服页面（与客服登录页/工作台入口的设备分流口径一致）
if (useMediaStore().isMobile) {
  router.replace(Setting.routePre + '/kefu/mobile_list');
}

// 指令粘贴指令定义（局部指令，v-paste）
const vPaste = {
  mounted(el, binding, vnode) {
    el.addEventListener('paste', function (event) {
      //这里直接监听元素的粘贴事件
      binding.value(event);
    });
  },
};

const scrollBox = ref(null);
const chatScrollBox = ref(null);
const chatInput = ref(null);

const isEmoji = ref(false);
const emojiTab = ref('default'); // 表情面板当前 tab：default=默认表情 qq=QQ表情
const chatCon = ref('');
const emojiGroup = chunk(emojiList, 20); // 表情列表
const html = ref('');
const userActive = ref({}); //左侧用户列表选中信息
const kefuInfo = ref({}); //客服信息
const isMsg = ref(false);
const isTransfer = ref(false);
const activeMsg = ref(''); // 选中的话术
const chatListData = ref([]);
const text = ref('');
const limit = 20;
const upperId = ref(0);
const online = ref(true); //当前客服在线状态
const scrollTop = ref(0);
const isScroll = ref(true);
const oldHeight = ref(0);
const isLoad = ref(false);

// 替代 HappyScroll 的 scroll-top 属性：scrollTop 变化即把聊天容器滚到该位置
watch(scrollTop, (val) => {
  const box = chatScrollBox.value;
  if (box) box.scrollTop = val;
});
// 原组件 @vertical-start 等价：滚动到顶部时加载历史消息
function onChatScroll() {
  const box = chatScrollBox.value;
  if (box && box.scrollTop <= 0) scrollHandler();
}
const upload = ref('');
const header = ref({});
const uploadData = ref({
  filename: 'file',
});
const userOnline = ref({});
const newRecored = ref({}); //新对话信息
const scrollNum = ref(0); //滚动次数
const transferId = ref(''); //转接id
const bodyClose = ref(false);
const tourist = ref(0);

const disabled = computed(() => {
  if (chatCon.value.length == 0) {
    return true;
  } else {
    return false;
  }
});
const records = computed(() => {
  return chatListData.value.map((item, index) => {
    // 聊天记录时间用中文格式（dayjs 默认英文 locale 也只影响相对时间等文案，数字格式无影响）
    item.time = dayjs(item.add_time * 1000).format('M月D日 H:mm');
    if (index) {
      if (item.add_time - chatListData.value[index - 1].add_time >= 300) {
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

// 组件销毁标记（替代原 this._isDestroyed 判断）
let destroyed = false;
let ws = null;
// 客服登录态失效标记：服务端踢人前下发 error 帧（如"授权失败!"）时置位，close 后据此跳登录页而非重连
let authExpired = false;
// 服务端 error 帧（踢人提示，区别于 socket_error 连接错误 / err_tip 业务提示）
function onSocketAuthError(data) {
  if (data && data.msg) authExpired = true;
}

onBeforeUnmount(() => {
  destroyed = true;
  // 离开客服页时停止标题滚动，避免跑马灯残留到其他后台页面
  stopTitleScroll();
  window.document.title = kefuInfo.value.site_name;
  if (ws) {
    ws.$off('socket_open', onSocketOpen);
    ws.$off('error', onSocketAuthError);
    ws.$off('close', onSocketClose);
  }
});

// created
upload.value = Setting.apiBaseURL + '/kefu/upload';
serviceInfo().then((res) => {
  kefuInfo.value = res.data;
  if (kefuInfo.value.site_name) {
    document.title = kefuInfo.value.site_name;
  } else {
    kefuInfo.value.site_name = '';
  }
});

onMounted(() => {
  window.addEventListener('click', function () {
    isEmoji.value = false;
  });
  setTimeout((e) => {
    Socket.then((socket) => {
      if (destroyed) return;
      ws = socket;
      ws.send({
        type: 'kefu_login',
        data: getCookies('kefu_token'),
      });
      ws.$on('socket_open', onSocketOpen);
      ws.$on('close', onSocketClose);
      ws.$on(['reply', 'chat'], (data) => {
        if (data.msn_type == 1) {
          data.msn = replace_em(data.msn);
        }
        if (data.msn_type == 2) {
          if (data.msn.indexOf('[') == -1) {
            data.msn = replace_em(`[${data.msn}]`);
          }
        }
        if (data.to_uid == userActive.value.to_uid || data.uid == userActive.value.to_uid) {
          chatListData.value.push(data);
          nextTick(function () {
            // 立即定位到最新消息，避免等 800ms 期间最新一条只露出半截
            scrollToBottom();
            // 消息内图片异步加载后高度会变化，稍后再校正一次
            setTimeout(scrollToBottom, 800);
          });
        }
      });
      ws.$on('reply', (data) => {
        // 游客消息不区分会话状态（会话列表/会话结束），统一播放提示音，避免漏提醒
        if (Number(data.is_tourist) === 1) playNotice();
      });
      ws.$on('socket_error', () => {
        ElMessage.error('连接失败');
      });
      ws.$on('err_tip', (data) => {
        ElMessage.error(data.msg);
      });
      //用户上线提醒广播
      ws.$on('user_online', (data) => {
        userOnline.value = data;
      });
      //用户未读消息条数更改
      ws.$on('mssage_num', (data) => {
        if (data.num > 0) {
          playNotice();
          // 有新未读消息时，标签页标题滚动提醒
          startTitleScroll();
        }
        chatListData.value.forEach((item) => {
          if (item.to_uid == data.uid) {
            item.mssage_num = data.num;
          }
        });
        if (data.recored.id) {
          newRecored.value = data.recored;
        }
        // 以服务端未读总数为准：全部已读（如客服点开会话后网关回执）则停止标题滚动
        unreadNumApi()
          .then((res) => {
            if (Number(res.data.user) + Number(res.data.tourist) === 0) {
              stopTitleScroll();
            }
          })
          .catch(() => {});
      });
    });
  }, 2000);
  header.value['Authori-zation'] = 'Bearer ' + getCookies('kefu_token');
  text.value = replace_em('[em-smiling_imp]');
});

function onSocketOpen(key) {
  if (key == 2) {
    ws.send({
      type: 'kefu_login',
      data: getCookies('kefu_token'),
    });
  }
}
function onSocketClose(data) {
  if (data.key == 2) {
    // 授权失效（服务端踢人前下发 error 帧）：停止无意义重连，清理登录态并引导重新登录
    if (authExpired) {
      authExpired = false;
      removeCookies('kefu_token');
      removeCookies('kefu_expires_time');
      removeCookies('kefuInfo');
      removeCookies('kefu_uuid');
      ElMessage.error('客服登录已失效，请重新登录');
      setTimeout(() => {
        router.replace({ path: Setting.routePre + '/kefu' });
      }, 800);
      return;
    }
    ElMessage.error('连接断开，正在尝试重连...');
    setTimeout(() => {
      ws.init(2);
    }, 2000);
  }
}
function beforeUpload(file) {
  return isPicUpload(file);
}
function handleFormatError(file) {
  ElMessage.error('上传图片只能是 jpg、jpg、jpeg、gif 格式!');
}
function bindEnter(e) {}
//微信截图上传图片时触发
function handleParse(e) {
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
    ElMessage({
      type: 'warning',
      message: '上传的文件必须为图片且无法复制本地图片且无法同时复制多张图片',
    });
    return;
  }
  update(file);
}
function update(e) {
  // 上传照片
  let file = e;
  let param = new FormData(); // 创建form对象
  param.append('filename', 'file'); // 通过append向form对象添加数据进去
  param.append('file', file); // 通过append向form对象添加数据进去
  // 添加请求头
  uploadImg(param).then((res) => {
    sendMsg(res.data.url, 3);
  });
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
function setOnline(data) {
  Socket.then((ws) => {
    ws.send({
      data: {
        online: data,
      },
      type: 'online',
    });
  });
  online.value = data;
}
// 阻止浏览器默认换行操作；Enter 发送 / Shift+Enter 换行，输入法组词期间的回车只确认候选词不发送
function listen(event) {
  if (!event.shiftKey && event.keyCode == 13 && !event.isComposing) {
    // 阻止 textarea 默认插入换行：keydown 清空输入框后，浏览器默认行为仍会把 \n 写入，导致光标落到第二行
    event.preventDefault();
    if (event.target.value == '') {
      return ElMessage.error('请输入消息');
    }
    sendMsg(event.target.value, 1);
    chatCon.value = '';
    nextTick(() => chatInput.value.focus());
  }
}
// 输入框选择表情
function select(data) {
  let val = `[${data}]`;
  chatCon.value += val;
  isEmoji.value = false;
}
// 输入框选择 QQ 表情（Unicode 原生字符直接追加，随文本发送）
function selectQq(item) {
  chatCon.value += item.char;
  isEmoji.value = false;
}
// 聊天表情转换
function replace_em(str) {
  str = str.replace(/\[([^\[\]]+)\]/g, "<span class='em $1'/></span>");
  return str;
}
// 标签页标题滚动提醒定时器（有未读消息时启用）
let titleTimer = null;

// 根据当前选中会话计算标签页正常标题
function pageTitle() {
  const active = userActive.value;
  const siteName = kefuInfo.value.site_name || '';
  // 未选中任何会话（如列表为空）时只显示站点名
  if (!active || !active.to_uid) return siteName;
  return active.nickname ? `正在和${active.nickname}对话中 - ${siteName}` : '正在和游客对话中 - ' + siteName;
}

// 有未读消息时让浏览器标签页标题跑马灯滚动，提醒客服及时查看
function startTitleScroll() {
  // 已在滚动中则不叠加定时器
  if (titleTimer) return;
  const text = `您有新的消息，请及时查看　${kefuInfo.value.site_name || ''}　`;
  let index = 0;
  titleTimer = window.setInterval(() => {
    // 从左向右循环截取文本，形成跑马灯滚动效果
    window.document.title = text.slice(index) + text.slice(0, index);
    index = (index + 1) % text.length;
  }, 300);
}

// 未读消息清零后停止标题滚动并恢复正常标题
function stopTitleScroll() {
  if (!titleTimer) return;
  window.clearInterval(titleTimer);
  titleTimer = null;
  window.document.title = pageTitle();
}
// 获取列表用户信息
function setDataId(data) {
  userActive.value = data;
  window.document.title = pageTitle();
  chatListData.value = [];
  upperId.value = 0;
  oldHeight.value = 0;
  isScroll.value = true;
  // 是否游客改由选中会话项自身携带：会话列表用户与游客混排，与 tab 无关；未选中会话时重置为非游客
  tourist.value = data && data.is_tourist == 1 ? 1 : 0;
  if (data) {
    Socket.then((ws) => {
      ws.send({
        data: {
          id: userActive.value.to_uid,
        },
        type: 'to_chat',
      });
    });
    getChatList();
  } else {
    // 未选中任何会话（如切到空列表的 tab）时，通知网关解除“正在对话”状态：
    // 否则网关仍视客服停留在上一个会话，新消息按已读的 reply 下发，既无未读数也无提示音
    Socket.then((ws) => {
      ws.send({
        data: {
          id: 0,
        },
        type: 'to_chat',
      });
    });
  }
}
function msgClose() {
  isTransfer.value = false;
}
// 话术选中
function activeTxt(data) {
  chatCon.value = data;
  isMsg.value = false;
}
// 文本发送
function sendText() {
  sendMsg(chatCon.value, 1);
  chatCon.value = '';
  nextTick(() => chatInput.value.focus());
}

// 统一发送处理
function sendMsg(msn, type) {
  let obj = {
    type: 'chat',
    data: {
      msn,
      type,
      to_uid: userActive.value.to_uid,
      is_tourist: tourist.value,
    },
  };
  Socket.then((ws) => {
    ws.send(obj);
  });
}
function send(type, data) {
  Socket.send({
    data,
    type,
  });
}
// 获取聊天列表
function getChatList() {
  serviceList({
    limit: limit,
    uid: userActive.value.to_uid,
    upperId: upperId.value,
    is_tourist: tourist.value,
  }).then((res) => {
    res.data.forEach((el) => {
      if (el.msn_type == 1) {
        el.msn = replace_em(el.msn);
      } else if (el.msn_type == 2) {
        el.msn = replace_em(`[${el.msn}]`);
      }
    });
    let selector = '';
    if (upperId.value == 0) {
      selector = '';
    } else {
      selector = `chat_${chatListData.value[0].id}`;
    }

    // chatListData.value = res.data.concat(chatListData.value)
    chatListData.value = [...res.data, ...chatListData.value];
    upperId.value = res.data.length > 0 ? res.data[0].id : 0;
    isLoad.value = false;
    nextTick(() => {
      // scrollToTop()
      isScroll.value = res.data.length >= limit;
      setPageScrollTo(selector);
    });
  });
}
// 滚动到消息列表底部：给 scrollTop 赋一个超过最大滚动值的数，浏览器会自动钳制到底部
function scrollToBottom() {
  const box = chatScrollBox.value;
  if (box) box.scrollTop = box.scrollHeight;
}
// 设置页面滚动位置
function setPageScrollTo(selector) {
  nextTick(() => {
    if (selector) {
      setTimeout(() => {
        let num = parseFloat(document.getElementById(selector).offsetTop) - 60;
        scrollTop.value = num;
      }, 0);
    } else {
      var container = document.querySelector('#chat_scroll');
      scrollTop.value = container.offsetHeight;
      setTimeout((res) => {
        if (scrollTop.value != scrollBox.value.offsetHeight) {
          scrollTop.value = document.querySelector('#chat_scroll').offsetHeight;
        }
      }, 300);
    }
  });
}
//滚动到顶部
function scrollHandler() {
  if (isScroll.value && upperId.value) {
    isLoad.value = true;
    getChatList();
  }
}
// 滚动条动画
function scrollToTop(duration) {
  var container = document.querySelector('#chat_scroll');
  scrollTop.value = container.offsetHeight - oldHeight.value;
  setTimeout((res) => {
    scrollTop.value = scrollBox.value.offsetHeight - oldHeight.value;
  }, 300);
}
// 客服转接
function transferPeople(data) {
  transferId.value = data.id;
  isTransfer.value = false;
  ElMessage.success('转接成功');
  Socket.then((ws) => {
    ws.send({
      type: 'to_chat',
      data: { id: data.uid },
    });
  });
}
// 客服转接确定
function transferOk() {}
</script>

<style lang="scss" scoped>
@import '@/plugin/emoji-awesome/css/google.min.css';
:deep(textarea.ivu-input) {
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
  height: 810px;
  margin: 0 auto;
  background: #fff;
  // 整体面板：圆角 + 投影，与灰色背景形成悬浮卡片层次（不加浅色边框，蓝 header 外圈会显白线）
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  // 裁掉子元素直角（如蓝色渐变 header），使顶部圆角生效
  overflow: hidden;
  .container {
    flex: 1;
    display: flex;
    .chat-content {
      width: 600px;
      height: 100%;
      display: flex;
      flex-direction: column;
      border-right: 1px solid #ececec;
      .chat-body {
        flex: 1;
        // flex 子项默认 min-height:auto，不置 0 则内容撑高后滚动条不生效
        min-height: 0;
        position: relative;
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
            background: #f5f5f5;
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
            .product-wrapper {
              display: flex;
              width: 320px;
              padding: 9px;
              .img-box {
                flex: none;
                width: 80px;
                height: 80px;

                img {
                  width: 100%;
                  height: 100%;
                  border-radius: 5px;
                }
              }
              .info {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                width: 224px;
                margin-left: 10px;
                font-size: 12px;
                .name {
                  font-size: 14px;
                }
                .price {
                  font-size: 16px;
                  color: #ff0000;
                }
              }
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
                  color: #ff0000;
                  .more {
                    font-size: 12px;
                    color: var(--prev-color-primary);
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
                background: #cde0ff;
              }
            }
            &.gary .msg-wrapper {
              background: #f5f5f5;
            }
          }
        }
      }
      .chat-textarea {
        flex: none;
        // 输入卡片：textarea 与底部工具栏同处一个圆角卡片，聚焦时卡片描边主色
        .textarea-box {
          position: relative;
          margin: 0 20px 16px;
          border: 1px solid var(--prev-border-color-light);
          border-radius: 6px;
          transition: border-color 0.2s;
          &:focus-within {
            border-color: var(--prev-color-primary);
          }
          :deep(.el-textarea) {
            width: 100%;
          }
        }
        // 卡片内底部工具栏：功能图标居左、发送按钮居右
        .input-toolbar {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 0 10px 10px;
          .left-wrappers {
            display: flex;
            align-items: center;
            gap: 16px;
          }
          .icon-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: var(--prev-color-text-regular);
            cursor: pointer;
            transition: color 0.2s;
            .iconfont {
              font-size: 20px;
            }
            &:hover {
              color: var(--prev-color-primary);
            }
          }
          .right-wrapper {
            position: relative;
            .transfer-box {
              z-index: 60;
              position: absolute;
              left: 0;
              bottom: calc(100% + 8px);
              width: 140px;
              background: #fff;
              border: 1px solid var(--prev-border-color-lighter);
              border-radius: 6px;
              box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
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
        }
        // 表情面板：紧贴表情图标上方弹出（悬浮于输入框上，不脱离触发位置）；默认/QQ 两组 tab 切换
        .emoji-box {
          z-index: 70;
          position: absolute;
          left: 0;
          bottom: 42px;
          display: flex;
          flex-direction: column;
          width: 60%;
          padding: 10px;
          border: 1px solid var(--prev-border-color-lighter);
          border-radius: 6px;
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
          background: #fff;
          height: 280px;
          .emoji-tabs {
            display: flex;
            flex: none;
            gap: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--prev-border-color-lighter);
            .tab-item {
              padding: 2px 12px;
              font-size: 12px;
              color: var(--prev-color-text-regular);
              cursor: pointer;
              border-radius: 4px;
              transition: color 0.2s, background 0.2s;
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
            // flex 子项默认 min-height:auto，不置 0 则内容撑高后滚动条不生效
            min-height: 0;
            // 固定 10 列网格等分对齐，避免 flex + 固定边距在窄容器下断行错位
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            grid-auto-rows: 28px;
            align-items: center;
            justify-items: center;
            padding-top: 10px;
            overflow: auto;
            // 细滚动条贴面板右缘，不挤占表情格
            // 注意：不能同时设 scrollbar-width/scrollbar-color，新版 Chrome 会因标准属性生效而忽略下方 ::-webkit-scrollbar 自定义样式
            &::-webkit-scrollbar {
              width: 4px;
            }
            &::-webkit-scrollbar-thumb {
              background: #c1c1c1;
              border-radius: 2px;
            }
            &::-webkit-scrollbar-track {
              background: transparent;
            }
            .emoji-item {
              cursor: pointer;
              // QQ 表情为 Unicode 字符，用字号呈现
              &.qq {
                width: 100%;
                font-size: 20px;
                line-height: 1;
                text-align: center;
              }
            }
          }
        }
      }
    }
  }
}
// 输入卡片内的圆形发送按钮（Enter 发送、Shift+Enter 换行，placeholder 已提示）
.send-btn {
  width: 34px;
  height: 34px;
  padding: 0;
  font-size: 17px;
  border-radius: 50%;
  background: var(--prev-color-primary);
  &:hover,
  &:focus {
    background: var(--prev-color-primary-light-3);
  }
  &[disabled] {
    background: var(--prev-color-text-placeholder);
    color: #fff;
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
/* 替代 happy-scroll 的原生滚动容器（撑满 .chat-body，隐藏横向、5px 细滚动条） */
.chat-scroll-box {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  overflow-y: auto;
  overflow-x: hidden;
  scrollbar-width: thin;
  &::-webkit-scrollbar {
    width: 5px;
    height: 5px;
  }
  &::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
  }
  &::-webkit-scrollbar-track {
    background: transparent;
  }
}
:deep(.chat-scroll-content) {
  width: 100%;
  .demo-spin-icon-load {
    animation: ani-demo-spin 1s linear infinite;
  }

  @-webkit-keyframes ani-demo-spin {
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

  @-moz-keyframes ani-demo-spin {
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

  @-ms-keyframes ani-demo-spin {
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

  @-o-keyframes ani-demo-spin {
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
  :deep(.ivu-modal-body) {
    padding: 0;
  }
}
.emoji-box::-webkit-scrollbar {
  width: 0;
}
.textarea-box :deep(.ivu-input:focus) {
  box-shadow: none;
}
.textarea-box :deep(.el-textarea__inner) {
  border: none;
  // Element Plus 的输入框边框由 box-shadow 绘制，只清 border 不生效；聚焦描边由输入卡片 :focus-within 承担
  box-shadow: none;
  resize: none;
  padding: 10px 12px;
  font-size: 14px;
  line-height: 22px;
  background: transparent;
  border-radius: 6px;
}
</style>
