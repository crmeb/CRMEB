<template>
  <div style="position: relative">
    <div v-if="type === '2'" class="verify-img-out" :style="{ height: parseInt(setSize.imgHeight) + vSpace + 'px' }">
      <div class="verify-img-panel" :style="{ width: setSize.imgWidth, height: setSize.imgHeight }">
        <img
          :src="backImgBase ? 'data:image/png;base64,' + backImgBase : defaultImg"
          alt=""
          style="width: 100%; height: 100%; display: block"
        />
        <div v-show="showRefresh" class="verify-refresh" v-db-click @click="refresh">
          <i class="iconfont icon-refresh" />
        </div>
        <transition name="tips">
          <span v-if="tipWords" class="verify-tips" :class="passFlag ? 'suc-bg' : 'err-bg'">{{ tipWords }}</span>
        </transition>
      </div>
    </div>
    <!-- 公共部分 -->
    <div
      class="verify-bar-area"
      :style="{ width: setSize.imgWidth, height: barSize.height, 'line-height': barSize.height }"
    >
      <span class="verify-msg" v-text="text" />
      <div
        class="verify-left-bar"
        :style="{
          width: leftBarWidth !== undefined ? leftBarWidth : barSize.height,
          height: barSize.height,
          'border-color': leftBarBorderColor,
          transaction: transitionWidth,
        }"
      >
        <span class="verify-msg" v-text="finishText" />
        <div
          class="verify-move-block"
          :style="{
            width: barSize.height,
            height: barSize.height,
            'background-color': moveBlockBackgroundColor,
            left: moveBlockLeft,
            transition: transitionLeft,
          }"
          @touchstart="start"
          @mousedown="start"
        >
          <i :class="['verify-icon iconfont', iconClass]" :style="{ color: iconColor }" />
          <div
            v-if="type === '2'"
            class="verify-sub-block"
            :style="{
              width: Math.floor((parseInt(setSize.imgWidth) * 47) / 310) + 'px',
              height: setSize.imgHeight,
              top: '-' + (parseInt(setSize.imgHeight) + vSpace) + 'px',
              'background-size': setSize.imgWidth + ' ' + setSize.imgHeight,
            }"
          >
            <img
              :src="'data:image/png;base64,' + blockBackImgBase"
              alt=""
              style="width: 100%; height: 100%; display: block"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
/**
 * VerifySlide
 * @description 滑块
 * */
import { computed, getCurrentInstance, nextTick, onMounted, ref, watch } from 'vue';
import { aesEncrypt } from './../utils/ase';
import { resetSize } from './../utils/util';
import { ajCaptcha, ajCaptchaCheck } from '../../../api/common';

defineOptions({ name: 'VerifySlide' });

const { proxy } = getCurrentInstance();

const props = defineProps({
  captchaType: {
    type: String,
  },
  type: {
    type: String,
    default: '1',
  },
  // 弹出式pop，固定fixed
  mode: {
    type: String,
    default: 'fixed',
  },
  vSpace: {
    type: Number,
    default: 5,
  },
  explain: {
    type: String,
    default: '向右滑动完成验证',
  },
  imgSize: {
    type: Object,
    default() {
      return {
        width: '310px',
        height: '155px',
      };
    },
  },
  blockSize: {
    type: Object,
    default() {
      return {
        width: '50px',
        height: '50px',
      };
    },
  },
  barSize: {
    type: Object,
    default() {
      return {
        width: '310px',
        height: '40px',
      };
    },
  },
  defaultImg: {
    type: String,
    default: '',
  },
});

const secretKey = ref(''); // 后端返回的加密秘钥 字段
const passFlag = ref(''); // 是否通过的标识
const backImgBase = ref(''); // 验证码背景图片
const blockBackImgBase = ref(''); // 验证滑块的背景图片
const backToken = ref(''); // 后端返回的唯一token值
const startMoveTime = ref(''); // 移动开始的时间
const endMovetime = ref(''); // 移动结束的时间
const tipsBackColor = ref(''); // 提示词的背景颜色
const tipWords = ref('');
const text = ref('');
const finishText = ref('');
const setSize = ref({
  imgHeight: 0,
  imgWidth: 0,
  barHeight: 0,
  barWidth: 0,
});
const top = ref(0);
const left = ref(0);
const moveBlockLeft = ref(undefined);
const leftBarWidth = ref(undefined);
// 移动中样式
const moveBlockBackgroundColor = ref(undefined);
const leftBarBorderColor = ref('#ddd');
const iconColor = ref(undefined);
const iconClass = ref('icon-right');
const status = ref(false); // 鼠标状态
const isEnd = ref(false); // 是够验证完成
const showRefresh = ref(true);
const transitionLeft = ref('');
const transitionWidth = ref('');

const barArea = computed(() => proxy.$el.querySelector('.verify-bar-area'));
const resetSizeFn = resetSize;

// 拖动起始坐标（无需响应式）
let startLeft = 0;

watch(
  () => props.type,
  () => {
    init();
  },
  { immediate: true },
);

onMounted(() => {
  // 禁止拖拽
  proxy.$el.onselectstart = function () {
    return false;
  };
});

function init() {
  text.value = props.explain;
  getPictrue();
  nextTick(() => {
    const size = resetSizeFn(proxy); // 重新设置宽度高度
    for (const key in size) {
      setSize.value[key] = size[key];
    }
    proxy.$parent.$emit('ready', proxy);
  });

  var _this = proxy;

  window.removeEventListener('touchmove', function (e) {
    _this.move(e);
  });
  window.removeEventListener('mousemove', function (e) {
    _this.move(e);
  });

  // 鼠标松开
  window.removeEventListener('touchend', function () {
    _this.end();
  });
  window.removeEventListener('mouseup', function () {
    _this.end();
  });

  window.addEventListener('touchmove', function (e) {
    move(e);
  });
  window.addEventListener('mousemove', function (e) {
    move(e);
  });

  // 鼠标松开
  window.addEventListener('touchend', function () {
    end();
  });
  window.addEventListener('mouseup', function () {
    end();
  });
}

// 鼠标按下
function start(e) {
  e = e || window.event;
  if (!e.touches) {
    // 兼容PC端
    var x = e.clientX;
  } else {
    // 兼容移动端
    var x = e.touches[0].pageX;
  }
  startLeft = Math.floor(x - barArea.value.getBoundingClientRect().left);
  startMoveTime.value = +new Date(); // 开始滑动的时间
  if (isEnd.value == false) {
    text.value = '';
    moveBlockBackgroundColor.value = '#337ab7';
    leftBarBorderColor.value = '#337AB7';
    iconColor.value = '#fff';
    e.stopPropagation();
    status.value = true;
  }
}
// 鼠标移动
function move(e) {
  e = e || window.event;
  if (status.value && isEnd.value == false) {
    if (!e.touches) {
      // 兼容PC端
      var x = e.clientX;
    } else {
      // 兼容移动端
      var x = e.touches[0].pageX;
    }
    var bar_area_left = barArea.value.getBoundingClientRect().left;
    var move_block_left = x - bar_area_left; // 小方块相对于父元素的left值
    if (move_block_left >= barArea.value.offsetWidth - parseInt(parseInt(props.blockSize.width) / 2) - 2) {
      move_block_left = barArea.value.offsetWidth - parseInt(parseInt(props.blockSize.width) / 2) - 2;
    }
    if (move_block_left <= 0) {
      move_block_left = parseInt(parseInt(props.blockSize.width) / 2);
    }
    // 拖动后小方块的left值
    moveBlockLeft.value = move_block_left - startLeft + 'px';
    leftBarWidth.value = move_block_left - startLeft + 'px';
  }
}

// 鼠标松开
function end() {
  endMovetime.value = +new Date();
  var _this = proxy;
  // 判断是否重合
  if (status.value && isEnd.value == false) {
    var moveLeftDistance = parseInt((moveBlockLeft.value || '').replace('px', ''));
    moveLeftDistance = (moveLeftDistance * 310) / parseInt(setSize.value.imgWidth);
    const data = {
      captchaType: props.captchaType,
      pointJson: secretKey.value
        ? aesEncrypt(JSON.stringify({ x: moveLeftDistance, y: 5.0 }), secretKey.value)
        : JSON.stringify({ x: moveLeftDistance, y: 5.0 }),
      token: backToken.value,
    };
    ajCaptchaCheck(data)
      .then((res) => {
        moveBlockBackgroundColor.value = '#5cb85c';
        leftBarBorderColor.value = '#5cb85c';
        iconColor.value = '#fff';
        iconClass.value = 'icon-check';
        showRefresh.value = false;
        isEnd.value = true;
        if (props.mode == 'pop') {
          setTimeout(() => {
            proxy.$parent.clickShow = false;
            refresh();
          }, 1500);
        }
        passFlag.value = true;
        tipWords.value = `${((endMovetime.value - startMoveTime.value) / 1000).toFixed(2)}s验证成功`;
        var captchaVerification = secretKey.value
          ? aesEncrypt(backToken.value + '---' + JSON.stringify({ x: moveLeftDistance, y: 5.0 }), secretKey.value)
          : backToken.value + '---' + JSON.stringify({ x: moveLeftDistance, y: 5.0 });
        setTimeout(() => {
          tipWords.value = '';
          proxy.$parent.closeBox();
          proxy.$parent.$emit('success', { captchaVerification });
        }, 1000);
      })
      .catch((res) => {
        moveBlockBackgroundColor.value = '#d9534f';
        leftBarBorderColor.value = '#d9534f';
        iconColor.value = '#fff';
        iconClass.value = 'icon-close';
        passFlag.value = false;
        setTimeout(function () {
          refresh();
        }, 1000);
        proxy.$parent.$emit('error', proxy);
        tipWords.value = '验证失败';
        setTimeout(() => {
          tipWords.value = '';
        }, 1000);
      });
    status.value = false;
  }
}

function refresh() {
  showRefresh.value = true;
  finishText.value = '';

  transitionLeft.value = 'left .3s';
  moveBlockLeft.value = 0;

  leftBarWidth.value = undefined;
  transitionWidth.value = 'width .3s';

  leftBarBorderColor.value = '#ddd';
  moveBlockBackgroundColor.value = '#fff';
  iconColor.value = '#000';
  iconClass.value = 'icon-right';
  isEnd.value = false;

  getPictrue();
  setTimeout(() => {
    transitionWidth.value = '';
    transitionLeft.value = '';
    text.value = props.explain;
  }, 300);
}

// 请求背景图片和验证图片
function getPictrue() {
  const data = {
    captchaType: props.captchaType,
    clientUid: localStorage.getItem('slider'),
    ts: Date.now(), // 现在的时间戳
  };
  ajCaptcha(data)
    .then((res) => {
      backImgBase.value = res.data.originalImageBase64;
      blockBackImgBase.value = res.data.jigsawImageBase64;
      backToken.value = res.data.token;
      secretKey.value = res.data.secretKey;
    })
    .catch((res) => {
      tipWords.value = res.msg;
      backImgBase.value = null;
      blockBackImgBase.value = null;
    });
}

// 父组件 Verify.vue 通过 $refs.instance.refresh() 调用
defineExpose({ refresh });
</script>
