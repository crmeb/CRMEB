<template>
  <div style="position: relative">
    <div class="verify-img-out">
      <div
        class="verify-img-panel"
        :style="{
          width: setSize.imgWidth,
          height: setSize.imgHeight,
          'background-size': setSize.imgWidth + ' ' + setSize.imgHeight,
          'margin-bottom': vSpace + 'px',
        }"
      >
        <div v-show="showRefresh" class="verify-refresh" style="z-index: 3" v-db-click @click="refresh">
          <i class="iconfont icon-refresh" />
        </div>
        <img
          ref="canvas"
          :src="pointBackImgBase ? 'data:image/png;base64,' + pointBackImgBase : defaultImg"
          alt=""
          style="width: 100%; height: 100%; display: block"
          v-db-click
          @click="bindingClick ? canvasClick($event) : undefined"
        />

        <div
          v-for="(tempPoint, index) in tempPoints"
          :key="index"
          class="point-area"
          :style="{
            'background-color': '#1abd6c',
            color: '#fff',
            'z-index': 9999,
            width: '20px',
            height: '20px',
            'text-align': 'center',
            'line-height': '20px',
            'border-radius': '50%',
            position: 'absolute',
            top: parseInt(tempPoint.y - 10) + 'px',
            left: parseInt(tempPoint.x - 10) + 'px',
          }"
        >
          {{ index + 1 }}
        </div>
      </div>
    </div>
    <!-- 'height': barSize.height, -->
    <div
      class="verify-bar-area"
      :style="{
        width: setSize.imgWidth,
        color: barAreaColor,
        'border-color': barAreaBorderColor,
        'line-height': barSize.height,
      }"
    >
      <span class="verify-msg">{{ text }}</span>
    </div>
  </div>
</template>
<script setup>
/**
 * VerifyPoints
 * @description 点选
 * */
import { getCurrentInstance, nextTick, ref, watch } from 'vue';
import { resetSize, _code_chars, _code_color1, _code_color2 } from './../utils/util';
import { aesEncrypt } from './../utils/ase';
import { ajCaptcha, ajCaptchaCheck } from '../../../api/common';

defineOptions({ name: 'VerifyPoints' });

const { proxy } = getCurrentInstance();

const props = defineProps({
  // 弹出式pop，固定fixed
  mode: {
    type: String,
    default: 'fixed',
  },
  captchaType: {
    type: String,
  },
  // 间隔
  vSpace: {
    type: Number,
    default: 5,
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

const secretKey = ref(''); // 后端返回的ase加密秘钥
const checkNum = ref(3); // 默认需要点击的字数
const fontPos = ref([]); // 选中的坐标信息
const checkPosArr = ref([]); // 用户点击的坐标
const num = ref(1); // 点击的记数
const pointBackImgBase = ref(''); // 后端获取到的背景图片
const poinTextList = ref([]); // 后端返回的点击字体顺序
const backToken = ref(''); // 后端返回的token值
const setSize = ref({
  imgHeight: 0,
  imgWidth: 0,
  barHeight: 0,
  barWidth: 0,
});
const tempPoints = ref([]);
const text = ref('');
const barAreaColor = ref(undefined);
const barAreaBorderColor = ref(undefined);
const showRefresh = ref(true);
const bindingClick = ref(true);

const canvas = ref(null);
const resetSizeFn = resetSize;

watch(
  () => props.type,
  () => {
    init();
  },
  { immediate: true },
);

function init() {
  // 加载页面
  fontPos.value.splice(0, fontPos.value.length);
  checkPosArr.value.splice(0, checkPosArr.value.length);
  num.value = 1;
  getPictrue();
  nextTick(() => {
    setSize.value = resetSizeFn(proxy); // 重新设置宽度高度
    proxy.$parent.$emit('ready', proxy);
  });
}
function canvasClick(e) {
  checkPosArr.value.push(getMousePos(canvas.value, e));
  if (num.value == checkNum.value) {
    num.value = createPoint(getMousePos(canvas.value, e));
    // 按比例转换坐标值
    checkPosArr.value = pointTransfrom(checkPosArr.value, setSize.value);
    // 等创建坐标执行完
    setTimeout(() => {
      // var flag = comparePos(fontPos.value, checkPosArr.value);
      // 发送后端请求
      var captchaVerification = secretKey.value
        ? aesEncrypt(backToken.value + '---' + JSON.stringify(checkPosArr.value), secretKey.value)
        : backToken.value + '---' + JSON.stringify(checkPosArr.value);
      const data = {
        captchaType: props.captchaType,
        pointJson: secretKey.value
          ? aesEncrypt(JSON.stringify(checkPosArr.value), secretKey.value)
          : JSON.stringify(checkPosArr.value),
        token: backToken.value,
      };
      ajCaptchaCheck(data).then((res) => {
        if (res.repCode == '0000') {
          barAreaColor.value = '#4cae4c';
          barAreaBorderColor.value = '#5cb85c';
          text.value = '验证成功';
          bindingClick.value = false;
          if (props.mode == 'pop') {
            setTimeout(() => {
              proxy.$parent.clickShow = false;
              refresh();
            }, 1500);
          }
          proxy.$parent.$emit('success', { captchaVerification });
        } else {
          proxy.$parent.$emit('error', proxy);
          barAreaColor.value = '#d9534f';
          barAreaBorderColor.value = '#d9534f';
          text.value = '验证失败';
          setTimeout(() => {
            refresh();
          }, 700);
        }
      });
    }, 400);
  }
  if (num.value < checkNum.value) {
    num.value = createPoint(getMousePos(canvas.value, e));
  }
}

// 获取坐标
function getMousePos(obj, e) {
  var x = e.offsetX;
  var y = e.offsetY;
  return { x, y };
}
// 创建坐标点
function createPoint(pos) {
  tempPoints.value.push(Object.assign({}, pos));
  return ++num.value;
}
function refresh() {
  tempPoints.value.splice(0, tempPoints.value.length);
  barAreaColor.value = '#000';
  barAreaBorderColor.value = '#ddd';
  bindingClick.value = true;
  fontPos.value.splice(0, fontPos.value.length);
  checkPosArr.value.splice(0, checkPosArr.value.length);
  num.value = 1;
  getPictrue();
  text.value = '验证失败';
  showRefresh.value = true;
}

// 请求背景图片和验证图片
function getPictrue() {
  const data = {
    captchaType: props.captchaType,
    clientUid: localStorage.getItem('point'),
    ts: Date.now(), // 现在的时间戳
  };
  ajCaptcha(data).then((res) => {
    if (res.repCode == '0000') {
      pointBackImgBase.value = res.repData.originalImageBase64;
      backToken.value = res.repData.token;
      secretKey.value = res.repData.secretKey;
      poinTextList.value = res.repData.wordList;
      text.value = '请依次点击【' + poinTextList.value.join(',') + '】';
    } else {
      text.value = res.repMsg;
    }

    // 判断接口请求次数是否失效
    if (res.repCode == '6201') {
      pointBackImgBase.value = null;
    }
  });
}
// 坐标转换函数
function pointTransfrom(pointArr, imgSize) {
  var newPointArr = pointArr.map((p) => {
    const x = Math.round((310 * p.x) / parseInt(imgSize.imgWidth));
    const y = Math.round((155 * p.y) / parseInt(imgSize.imgHeight));
    return { x, y };
  });
  return newPointArr;
}

// 父组件 Verify.vue 通过 $refs.instance.refresh() 调用
defineExpose({ refresh });
</script>
