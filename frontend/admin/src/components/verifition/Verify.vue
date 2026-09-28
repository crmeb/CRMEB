<template>
  <div v-show="showBox" :class="mode == 'pop' ? 'mask' : ''">
    <div :class="mode == 'pop' ? 'verifybox' : ''" :style="{ 'max-width': parseInt(imgSize.width) + 30 + 'px' }">
      <div v-if="mode == 'pop'" class="verifybox-top">
        请完成安全验证
        <span class="verifybox-close" v-db-click @click="closeBox">
          <i class="iconfont icon-close" />
        </span>
      </div>
      <div class="verifybox-bottom" :style="{ padding: mode == 'pop' ? '15px' : '0' }">
        <!-- 验证码容器 -->
        <component
          :is="componentType"
          v-if="componentType"
          ref="instance"
          :captcha-type="captchaType"
          :type="verifyType"
          :figure="figure"
          :arith="arith"
          :mode="mode"
          :v-space="vSpace"
          :explain="explain"
          :img-size="imgSize"
          :block-size="blockSize"
          :bar-size="barSize"
          :default-img="defaultImg"
        />
      </div>
    </div>
  </div>
</template>
<script setup>
/**
 * Verify 验证码组件
 * @description 分发验证码使用
 * */
import { computed, getCurrentInstance, onMounted, ref, watch } from 'vue';
import __asset_default_0 from '@/assets/images/default.jpg';

import VerifySlide from './Verify/VerifySlide';
import VerifyPoints from './Verify/VerifyPoints';

defineOptions({ name: 'Vue2Verify' });

const { proxy } = getCurrentInstance();

const props = defineProps({
  // 双语化
  locale: {
    require: false,
    type: String,
    default() {
      // 默认语言不输入为浏览器语言
      if (navigator.language) {
        var language = navigator.language;
      } else {
        var language = navigator.browserLanguage;
      }
      return language;
    },
  },
  captchaType: {
    type: String,
    required: true,
  },
  figure: {
    type: Number,
  },
  arith: {
    type: Number,
  },
  mode: {
    type: String,
    default: 'pop',
  },
  vSpace: {
    type: Number,
  },
  explain: {
    type: String,
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
  },
  barSize: {
    type: Object,
  },
});

const clickShow = ref(false);
// 内部类型
const verifyType = ref(undefined);
// 所用组件类型（Vue3 script setup 下动态组件需使用组件对象）
const componentType = ref(undefined);
// 默认图片
const defaultImg = __asset_default_0;

const instance = ref(null);

const showBox = computed(() => {
  if (props.mode == 'pop') {
    return clickShow.value;
  } else {
    return true;
  }
});

watch(
  () => props.captchaType,
  (captchaType) => {
    switch (captchaType.toString()) {
      case 'blockPuzzle':
        verifyType.value = '2';
        componentType.value = VerifySlide;
        break;
      case 'clickWord':
        verifyType.value = '';
        componentType.value = VerifyPoints;
        break;
    }
  },
  { immediate: true },
);

onMounted(() => {
  uuid();
});

// 生成 uuid
function uuid() {
  var s = [];
  var hexDigits = '0123456789abcdef';
  for (var i = 0; i < 36; i++) {
    s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
  }
  s[14] = '4'; // bits 12-15 of the time_hi_and_version field to 0010
  s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1); // bits 6-7 of the clock_seq_hi_and_reserved to 01
  s[8] = s[13] = s[18] = s[23] = '-';

  var slider = 'slider' + '-' + s.join('');
  var point = 'point' + '-' + s.join('');
  // 判断下是否存在 slider
  if (!localStorage.getItem('slider')) {
    localStorage.setItem('slider', slider);
  }
  if (!localStorage.getItem('point')) {
    localStorage.setItem('point', point);
  }
}
/**
 * i18n
 * @description 兼容vue-i18n 调用$t来转换ok
 * @param {String} text-被转换的目标
 * @return {String} i18n的结果
 * */
function i18n(text) {
  if (proxy.$t) {
    return proxy.$t(text);
  } else {
    // 兼容不存在的语言
    return text;
  }
}
/**
 * refresh
 * @description 刷新
 * */
function refresh() {
  if (instance.value && instance.value.refresh) {
    instance.value.refresh();
  }
}
function closeBox() {
  clickShow.value = false;
  refresh();
}
function show() {
  if (props.mode == 'pop') {
    clickShow.value = true;
  }
}

// 子组件（VerifySlide/VerifyPoints）通过 $parent.clickShow/$parent.closeBox 驱动；登录页通过 ref.show() 驱动
defineExpose({ clickShow, closeBox, show, refresh });
</script>
<style>
.verifybox {
  position: relative;
  box-sizing: border-box;
  border-radius: 2px;
  border: 1px solid var(--prev-border-color-light);
  background-color: var(--prev-bg-white);
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
}
.verifybox-top {
  padding: 0 15px;
  height: 50px;
  line-height: 50px;
  text-align: left;
  font-size: 16px;
  color: var(--prev-color-text-regular);
  border-bottom: 1px solid var(--prev-border-color-light);
  box-sizing: border-box;
}
.verifybox-bottom {
  padding: 15px;
  box-sizing: border-box;
}
.verifybox-close {
  position: absolute;
  top: 13px;
  right: 9px;
  width: 24px;
  height: 24px;
  text-align: center;
  cursor: pointer;
}
.mask {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1001;
  width: 100%;
  height: 100vh;
  background: rgba(0, 0, 0, 0.3);
  /* display: none; */
  transition: all 0.5s;
}
.verify-tips {
  position: absolute;
  left: 0px;
  bottom: 0px;
  width: 100%;
  height: 30px;
  line-height: 30px;
  color: #fff;
}
.suc-bg {
  background-color: rgba(92, 184, 92, 0.5);
  filter: progid:DXImageTransform.Microsoft.gradient(startcolorstr=#7f5CB85C, endcolorstr=#7f5CB85C);
}
.err-bg {
  background-color: rgba(217, 83, 79, 0.5);
  filter: progid:DXImageTransform.Microsoft.gradient(startcolorstr=#7fD9534F, endcolorstr=#7fD9534F);
}
.tips-enter,
.tips-leave-to {
  bottom: -30px;
}
.tips-enter-active,
.tips-leave-active {
  transition: bottom 0.5s;
}
/* ---------------------------- */
/*常规验证码*/
.verify-code {
  font-size: 20px;
  text-align: center;
  cursor: pointer;
  margin-bottom: 5px;
  border: 1px solid #ddd;
}

.cerify-code-panel {
  height: 100%;
  overflow: hidden;
}

.verify-code-area {
  float: left;
}

.verify-input-area {
  float: left;
  width: 60%;
  padding-right: 10px;
}

.verify-change-area {
  line-height: 30px;
  float: left;
}

.varify-input-code {
  display: inline-block;
  width: 100%;
  height: 25px;
}

.verify-change-code {
  color: #337ab7;
  cursor: pointer;
}

.verify-btn {
  width: 200px;
  height: 30px;
  background-color: #337ab7;
  color: #ffffff;
  border: none;
  margin-top: 10px;
}

/*滑动验证码*/
.verify-bar-area {
  position: relative;
  background: var(--prev-bg-white);
  text-align: center;
  -webkit-box-sizing: content-box;
  -moz-box-sizing: content-box;
  box-sizing: content-box;
  border: 1px solid var(--prev-border-color-base);
  -webkit-border-radius: 4px;
}

.verify-bar-area .verify-move-block {
  position: absolute;
  top: 0px;
  left: 0;
  background: var(--prev-bg-white);
  cursor: pointer;
  -webkit-box-sizing: content-box;
  -moz-box-sizing: content-box;
  box-sizing: content-box;
  box-shadow: 0 0 2px #888888;
  -webkit-border-radius: 1px;
}

.verify-bar-area .verify-move-block:hover {
  background-color: #337ab7;
  color: #ffffff;
}

.verify-bar-area .verify-left-bar {
  position: absolute;
  top: -1px;
  left: -1px;
  background: #f0fff0;
  cursor: pointer;
  -webkit-box-sizing: content-box;
  -moz-box-sizing: content-box;
  box-sizing: content-box;
  border: 1px solid #ddd;
}
.verify-img-panel {
  margin: 0;
  -webkit-box-sizing: content-box;
  -moz-box-sizing: content-box;
  box-sizing: content-box;
  border-top: 1px solid #ddd;
  border-bottom: 1px solid #ddd;
  border-radius: 3px;
  position: relative;
}

.verify-img-panel .verify-refresh {
  width: 25px;
  height: 25px;
  text-align: center;
  padding: 5px;
  cursor: pointer;
  position: absolute;
  top: 0;
  right: 0;
  z-index: 2;
}

.verify-img-panel .icon-refresh {
  font-size: 20px;
  color: #fff;
}

.verify-img-panel .verify-gap {
  background-color: #fff;
  position: relative;
  z-index: 2;
  border: 1px solid #fff;
}

.verify-bar-area .verify-move-block .verify-sub-block {
  position: absolute;
  text-align: center;
  z-index: 3;
  /* border: 1px solid #fff; */
}

.verify-bar-area .verify-move-block .verify-icon {
  font-size: 18px;
}

.verify-bar-area .verify-msg {
  z-index: 3;
}

/*字体图标的css*/
/*@font-face {font-family: "iconfont";*/
/*src: url('../fonts/iconfont.eot?t=1508229193188'); !* IE9*!*/
/*src: url('../fonts/iconfont.eot?t=1508229193188#iefix') format('embedded-opentype'), !* IE6-IE8 *!*/
/*url('data:application/x-font-woff;charset=utf-8;base64,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') format('woff'),*/
/*url('../fonts/iconfont.ttf?t=1508229193188') format('truetype'), !* chrome, firefox, opera, Safari, Android, iOS 4.2+*!*/
/*url('../fonts/iconfont.svg?t=1508229193188#iconfont') format('svg'); !* iOS 4.1 - *!*/
/*}*/

.iconfont {
  font-family: 'iconfont' !important;
  font-size: 16px;
  font-style: normal;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.icon-check:before {
  content: ' ';
  display: block;
  width: 16px;
  height: 16px;
  position: absolute;
  margin: auto;
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
  z-index: 9999;
  background-image: url('data:image/png;base64,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');
  background-size: contain;
}

.icon-close:before {
  content: ' ';
  display: block;
  width: 16px;
  height: 16px;
  position: absolute;
  margin: auto;
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
  z-index: 9999;
  background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAADIEAYAAAD9yHLdAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlzAAAASAAAAEgARslrPgAADwRJREFUeNrt3V1sU+cZwPHndTAjwZ0mbZPKR/hKm0GqtiJJGZ9CIrMCawJoUksvOpC2XSi4gMMEKaaSO0qFEEhgFCQSqWOVWqJEGJJuyYYeCG9QCIOhQvYlgGCIFmatrVSUhzixO8ujNM1gSZOfPye857/7wYlfPg5xj5/n/fExyIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABATizsWti1sCs/v6y0rLSsdMaMZ/Y8s+eZPZMnm54LQO6kn/fp/UB6v2B6LrdRpgcwZf7e+Xvn7505MxAIBAKBrVt1ja5RNdXVaqlaqpbOmTP0/+u9eq/ee/euFEqhFH/ySeOjwIe6urr299of6jb//3fT2AMhcWVlZWVnZ3Ln6uD6uj2/eLF3SJV1VVapWqaraqendOnX+n3/hf5Cf3HzpupW3aq7qSl5LHkseay+/nLt5drLtbdvm96eXPNZQJQqn1Q+qTzS73+vN+gNesObb2q7tEv7xImZ/kv6kr6kL/X3q0PqkDpUXx/aFNoU2rRz53l1Xp1X/f2mtxTAcMv1cr1c5jQfb37evf1ujrpkR7p6byZqagaVZOXl/E/WCM1UnP/v5cf64/f+utnbzg7O+zrTt7a9qbXp7neaTgChVeqD0gOmBP/5RHdPHlS7f/CbrlEplVLJiqt0iqtv/51NBqNRqP37pnecgDpI42CgtT9OTJ1PO0sjLbt6PX6/V6/Z/+1LG5nPHJg2HnsdMaMZ+vq4XDsM7ORjXX7dT3d3XPGiY3MzsrNzf37t07d+7cuXPn0tPTDQ0N09PTU1NTDQ0NHR0dKysrNzc3g4ODu7u719fX09LSjh07tm/fPn/+/N69e3/89ddf+/bti4uLq6qqsrOzY2Nj58+fv3v37v3793/7X0NDQ0tLS0FBQUlJye3bt19//fW1a9empKTMmzdvaGhoYGBw8+bNT58+raioOHr06P379z/++OPa2tqysrLe3t6rV6+++uorIyOjsrJSU1MzNjZ29+7d27dvX3V1dT4+PtXV1UePHp09e/bHH3+cmJiwtbXNzMwcHBysra1nzpzZt2/ffvbZZ7/88su2bdu8vLzi4+NnzZo1e/ZsXV3dpUuXXF1djYyMvLy8Hj16tG7dunnz5h07dmzZsmXz5s2FhYXl5eVjY2M3btxo2bLlypUrS0tLPT09MzMzf/jhB0dHxw4dOnh5efn5+WZmZu7u7mFhYV6v9/79+2/dujU9Pb2lpeWDBw+cnJy2bNmydetWUVHRlStX7t27t7W1XVhYOHfunKenp1KpNDQ0HD58eOfOnYmJiampqZaWlqampoKCgvHx8cePH+fl5WVkZISHh5cvX25jY3N3d9fS0nJwcMjOznZ2du7Zs+fcuXOnT5+urq7u6OiwsLDQ3NyMioo4ODhsNrvD4aiuru7s7MzPz0+n062trW1tbU+ePKmurk6n0z08Po8ePXr06Kmvrz98+LBHjx7BwcHExMSqqirpQ/fu3RUVFRcsWLBmzZqCggK5XB4cHKyurv7555/9/f2Tk5N7enouX76ckZHxxo0b8fHxSUlJu3bt+uDBg/Xr19+7d+/+/futra2FED/99NPXX3/98uXL586d6+vrO3LkSElJyZQpU1JTUz///PP79+/v37//2rVrUVFRGRkZOTk5GxsbFxcXgwcPDgkJWVlZubi4PHnyZPPmzXFxcY0aNXr06KGOjo5QKj4+PqdPnx44cCBJkqenJx8fH0dHR0NDw6KiogsXLixatGhgYOATJ04oikpLSx89evTYsWM//fRTfX19RUWFvb19ZGTkggULjh071q5du379+r1793758mVhYeHYsWNNTU0FBQU9evQICgqqr6/38PBYsGDBnj17Lly4EBIScvPmzcePH79///6goCAgIGAYf/DgwZs3b/r4+Ny8eTM0NLS8vLypqen06dNbtmypqalJS0u7d+/e+fPnx8bGpqen37x5s6mpKTMzMz8/Pycnp7m5uY6Ozg8fPnzz5k1+fn5FRUVMTEyampp6eXn9+vUrf39/AODo6Pjw4cPZs2e/+eabgoICSUlJycnJHTp0cHR2njx5MiEhYdCgQUFBQVNTU3t7+6BBg7y8vHJycgICAnx8fG7durVr1y4nJ6e4uFhfX3/58uXAgQMTEhJi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等级');
  background-size: contain;
}

.icon-right:before {
  content: ' ';
  display: block;
  width: 16px;
  height: 16px;
  position: absolute;
  margin: auto;
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
  background-size: cover;
  z-index: 9999;
  background-image: url('data:image/png;base64,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');
  background-size: contain;
}

.icon-refresh:before {
  content: ' ';
  display: block;
  width: 16px;
  height: 16px;
  position: absolute;
  margin: auto;
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
  z-index: 9999;
  background-image: url('data:image/png;base64,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aWtrIkSMNDQ0LCwtTU1M///yzp6cnAFy6dGnlypWlpaVnzpxpaWlJT0/Py8t76NChmTNnKmXv48ePe3t7Hz9+PHjwYFVVlUgkunHjxtWrV6ekpFy/fl0kEo0aNSohIaG4uHjv3r2VlZXh4eFarba0tHTp0qXExERdXV1dXV1PT89hw4ZNnjz5+vXrY8eOffHFFx9//PHIyMjz589///vfnT9/vn///pmZmaysrFWrVpWVlR07dmxkZLRz586oqKhRo0Zt3br13Llzt2/fLisry8vLc3JyUlNTW1tbS0tLBwcHFy5c8Pb2nj179sSJE+fOnUtOTm5ra6utra2lpfXhw4cDBw5IkuTn51dXV9fV1ZWXl3dzc+vWrVuPHj3OnTv34sWL+/fvr6+vf/Pmja2t7bfffps5c+batWudTmdhYeHp06fPnTv34sWLz58/r6urCwB8//33d+/eHR8f/5///CciImJgYLB+/frz58/369dvzJgxGRkZFy5cuHjxovv3709ISGhtbR04cODSpUslEsnY2FhVVVVRUdHTp0+vX78+f/78ISEh06dPnz17ViqVdnZ2b968uXnz5nPnzq1atarDhw+PGjXq3r1727ZtW7p06b1797Kzs3t7ez/88MP58+cfOXJEX1/f4cOHT58+XVmIjIwMDw8PCQnJzMzMzMwMDAyUSqVWq0+dOlWj0RQUFHTq1GnhwoU1NTWDBg1asWJFVVXV09NzyJAh3bt3b9y48fr161u2bKmpqXn8+HFAQEhra+vw4cNJSUkqlapTp07jxo1TqVSn05WSknLlypVQKNTZ2amtre3o6Ojo6Pjiiy8MDQ1VVT1+/PjMmTMzMDBofXwAoNFoVq1alZGRcfLkyb179xqNxvv372dlZd2+fXvhwoX9+/cfP348LCysr6+PIAjdunUTiUTXrl1fu3btggULdu7cKRAITJs2LTAwcPXq1XfeeSczM7OIiMjRo0c7d+7szJkzZ86cCQoKqqysHD58ePPmzaWlpW/fvh06dCgwMLBp06bIyMht27ZpNNrgwYPjxo1bsWJFbm6un5+fl5dXXV39/v37m5qaQkJCQkNDw8DAQKfT2drajh07VlJSsnXr1i1btnz99df79+//+eefoVAo+vv7a2pq5s2bBwAePXqUl5c3b9687OzsHTt2cBzXr1+/w4cPy8rKmpqajhw5snLlSgBwOp0nTpxobGy8desWvV7/8uXLn3/+eXFx8fr165ubmzMzM9nZ2WFhYUql8siRI2vXrr1z586WLVsCAgKSk5PFYnFzc3N2dm5raw8ICPjss8/mzp2blZVVWFiYv7//tWvXfv/994GBgampqbq6usiQIQUFBb/99tq1a9fCwsK9e/c2NjZeunRJS0vLyMhgMBi++uorRVF9+/aVJMXFxZ09e3ZkZGRCQsL48eMbGxtPnTrVp0+fyMhIkUg0ePDgmJiYQ4cOzZo1SyAQaDTa9u3bKysrq6ur8/LyGhsbx8bG3rx5U1RUlFQqra6uXrBgQVRU1Pbt2/UecEFBwdSpUxcsWLB9+/aCggJFUQ0NDWfPnp2VlfXs2bN///59eXn5nDlz6urqLly48Pfff+/YsaPFYnny5MmsrKxVq1b9+/cPCQlxc3OzsLBwdXWtqqrKzs4+ceKEp6dnz549jx49unPnzqioKIVC8dtvv33zzTcTJ05saGhobW1dt27dvn37Zs2aZWZmms3mN998c+zYMU1NTUtLy9fXV6/X37VrVyMjIzU1NYODg0NDQ8PDw8PCwtra2pKSkpubm5mZmU6ns7a2dnV1tbe3j4yMrKystLS0rKxsdHS0o6Pj6dOnZ86c+eGHH3799VfZPHnyzp07u7q63rx5U1hYmFarffbs2eTk5EmTJq1fv56amnp4eBQUFNTW1h48eDB27NixY8eOGhoaVq9effr06e3bt9fW1t65c2dkZNTZ2RkUFDR69Oj169cPHDhQqVRGR0fPmDEjOjr6+vXr1tbWP/744+nTp+/fv//27dvPnj1ramoqKirOnDlz9erVDx48+Oqrr0pKSiwWy7vvvpufn19NTc2xY8cePXq0ra3t7e3dtWvXW7duyWSyjIwMf3//3Nzcfv36icViLpd74MABgUDQp0+fvn37LliwgMlkPnDgQFBQ0ODBg4OCgnx8fIyNjVeuXKmtrW1ubm5ubp6XlycQCK5fv37kyJHr169PTk6uq6tTqVQDAwP37Nlz/vz5t2/fnjhxorGx8ZUrV/Ly8qZPn967d29GRsbYsWMBICwszN3dvdaxCAQCbW1tJSUlaWlpqampKpVq586dUVFRHo+npKRECPnjjz+mpaUdM2aMu7v7tWvXFi9ePDs7W1VVzcvLk6Y4AACpVKqpqWllZWVlZeXl5aVSqc6dO6elpTk5OWm12tvba2lpGY1GR48e1ev1paXlycnJcrns4eGRn5+/aNGiwsLCrVu21tfXq9XqrVu31tfX1tfXP3r0aFhY2JUrVxQKRYcOHWJjY5ctW1ZQUDBq1Kijo2OnTp369ev3448/qNTa19fX1NSsqqr65JNPGhsbb9y4kZaWlp6ernPnzsOHD9fW1kZERDx9+nTo0KHvvvsuLi6uVqvpdLqgoOCmTZtmzZoVFxf34MEDlUoVERFhbW19+vTp/v37MxqNZrOpra1NTk6eMmUKh8MxMTHx8fGVSqW/v//+/ftxcXEOhwMQRkdHL1y4sG3btqFDh1q0aHH37l1jY2OnTp0qLy+vra09ePDgnDlz8vLyGhoaLl26pHPl3r17c3Nz09PTV65c2djYePnyZUlJSXFx8ZYtWyRJqqurq6qq/L9/8uTJy5cvd+7cuaKiory8vNzc3ISEhJs3b5aVlXX37l2RSNTR0XHy5Mlz584dPHhQrVZv2bJl7969W7ZsadeuXRs3bszJyRHPY/z48Tt37uzbt29CQuLEiRP+/v6BgYFJSUmLFy9WVVUNDQ1dtGhRSUnJ2bNnz58/37p16759+xwdHU+dOlWhUJw/f/7+/fvr6+tPnz4dMmSIj4/Pnj179uzZs5MnTx48eJCdnZ2VlXXixInm5uYeHh7z5s2rtHZ4eHjDhg1qtXrRokUbNmzYtm1bfn5+qampmzdvLiwsHDJkiI+Pz8WLF6VSqZOTE4PBUFZWplevXkVFRZmZmTdv3mxtbf3888/V1dVr1qzRarUHDhxISkqqr6+fPHlyRUVFdnb2vHnzRCJRREREb968GRYWtmHDhoqKik2bNp0+fbq+vv6sWbPOnj2bl5cXERHh4+Ojqanp7OxcsmRJTU3NxIkTV65cOXHixOTk5ISEhPT09IMHD164cOHs2bOlpaVz586lUqmxsfGmTZtmzpyp1+tHRERcuHChqqrKzs5+9dVXycnJSUlJn3/+uVqt3rp1a/Pmza2trUeOHBGJRFKp1MLCoqqq6uLFi6GhoYGBwT179oSHh/fr16+oqEilUoIg7t27FxERUb9+/Tp16igejycmJqampmZlZXXr1q2goEAkEp0+fXrmzJn9+/e/u1rgpEmTamtrq6ioWLFiRUFBIUmqVqteXl5qtVokEo2MjBQKha+//npCQuK9994TiUQdHR0xMTEymSwsLHzy5Ek4HN60adO2bduioqLGjRtXUlISHx8fERGhqanJycl5/vz5ysrK1atXq6ioKBQKVa/XFy9e/OWXXyQnJ9fX1x85cqS8vHz//v379u2jepi8vDw8PLxjx46ioqLc3FyBQHDmzJmJEye6ubmFhobq6+tFUtGtW7cwMDC3bt1atGiRSqVqamru379fW1ububn5wIEDcrncw8Nj3bp1Fy5caGtrm5ub++6776ZNm1xdXT/99NN9+/YNDQ1VqVQymUylUg0MDPLz8zMzM//5zX9VVFRMmDBBp9MlJiZeu3YtLy8vJyfn2rVrGo1mzJgx+/fvb2trW79+fbFY3Lp164CAgLi4uP79+9+7d09dXV0sFh88ePDGjRvZ2dkZGRlhYWEDAwN1dXX79u1rfHwCgdCvX793796tVqt1dXW5ubmZmZlmszk0NNTT0zMzM7O0tPTw8KhSqVQqFR0dndjY2LCwMIvFOn/+fGxsbG5u7uXLl5ubm8+fP1+r1W7duvXo0aPjx48XFRWlpqYqFAqRSFRYWHjixImGhoaioqLu3bs3efJkdXW1k5NTVFTUjRs3FEWlrq5+8uTJ48ePm5iYqNVqU6ZMyczMrFarX3/9dUtLy97e/vHjxzk5OYIg5OTkdO7cua1bt+r1+nPnzk1ISGhsbFQqlSdPnjx79uyRI0fkcrlCofD58+eFQlFYWNi1a9dGRERMTEx8fHyampoPHjwQiURqtVqn052enl5RUREcHDx69OjExMSioqK2tvalS5cCAgJcLldxcbFGo9FoNE1NTUNDQzU1NYODg0KhMDQ0zM7OVlZW1tfX/+///k+v10+cOJGQkJCQkBAUFFS/fv2qq6vDw8OlUqlUKkUiUWNj4/79+zMzM5cuXQoJCRGJRKmpqa2trampqVarVafTefDggVarLS4uThTFmTNncnNzW1pa5ubmDh8+vHjxora2Njc39/Lly8PDw6urq1KpVHZ2dk1NzfHjx09MTKRSqYGBgU5OTrGxsaGhoampqREREefPnx8aGqqsrFRXV5eYmOjj43PkyJGWlhalVFhY2M3NLScnp6CgQKVS+fv7m5mZqdfrZ8yYoVQq39/fb2trW61W9+7du3Tp0mg0SlGUhqP/G7lw4cKMGTMcHBzWrFlz//59Q0PD/v37WSzWwMDgli1bVq9evWHDhpUrV+7Zs6e0tDQhIeHq1atLlixJT09fsmTJHj16KBQKhUIRHR29fft2d3f3LVu2HDt2LDs7+/79+9euXTt+/PhOndoIDAzMmzevpKQkPz//8uXLGo1mxIgRV65cefnyZW9v78jISGFhYWhoaFFR0bZt2yZMmODs7Dx16lR+fj4gIPATTzzh5eWVk5MjFAo1Go0mk+nt27flcrlUKh0aGhoSErJhw4bo6Gij0RgREeHl5TV8+PCGDRuqqqo6Ojp+//39ORCPT1Kp1NWrV9fV1c2ePfsf//hHa2vrsWPHxsfHq6qqpkyZYmBgEB0dPXv2bJFINDMzU6/XR0REJCYmqm0l+/fvj4iIKCkpEYvFn3766apVq4KCgnJyciYmJmpraw8cOKCU+vDDDwsKCjo7Oy9cuuTq6lq4cGFkZOSPP/74+PHjSqVScXGxm5vbxo0bDQYDAAgEgpUrV06fPl0ikWzZsqWkpOT58+fq6uqjR48+/fTT8fHxhw8ftrS0fPTRRy9dunT8+PGmpqaffvopPT196NChAPDDDz+kUql79+5pbm4uKir6+Pjs3r0bFRW1dOnS7OxsXV1djuNGjRr16aefZmVlqZQaGhqam5v37du3Zs2awsLCTp06JSQkLF++fPr06fPnz7ezs9O/f39/f//+/fu//PLLK1eu9PHxWbVq1ebNm2svPBw4cMDQ0LB///5qtdrJyampqUmSdOnSpeTk5Ojo6IULF6rV6qamJlVV1ev1rl27FhIScv36daVUc3Pzhg0b6urqzp49O3fuXEFBQXp6+qRJk/Ly8lauXElLSzM3N9fT0+vQoYNS6t27d0eOHOncubO2tnbIkCF79+5tZ2e3cOHCXbt2cRzXr1//6NGjmzdv3rt37/Tp07169UpMTGxqaurs7Fy3bp2dnZ2VlXUoNpvN5uDBg506dWpv376EhIRHjx7JZLKysrLDhw/v3LnTzMyMxWLp6Ojcv3//yJEjJ0+eFIlEjY2NAQEBCwsLTU1NIyMjV6xYUVNTM27cOAFBUlJSfvz48e/fvyv0D9SXLFly+fJlHx+fM2fOlClTFApFp9M5OTmFh4efPXv22rVrr1y58uTJE0VRe/bsmT9/vqur65dffvH19fX19a2qqjpx4oShoeHAgQNNTU2FhYWvXr1avHjxiRMnDAwMgoODp06dun///iFDhjx//vzKlSump6f/+uuvgoKC+vXrJxAIPP3002PHjnV2dq6rq3t4eAwePLh///5aWlqHDh0URfXp0yctLa2goCA7O3vhwoWZmZmm0+nBgwfr6+t7enoqFIrDhw/PmDEjJSXl6dOn/fv3HzhwID4+Pisrq1evXsvLy7q6uj4+PuvXr7906ZLJZBobGxcVFf3xxx/37t2br6/v6NGj169f/+KLL4Ig1NTUrFy5UiAQPHbs2DVr1sTGxo4fP37r1q1XrlxZs2bNoUOHBgUFqdfrABAXF1dbW9uBAwc++eSTX3/9lUgkunjxYnJycnx8/E2bNq1atUpPT0+lUi9dupSdnf2///0vIyMjNDR0yZIlRUVFT548qamp+ffff8PDw11cXH777bdevXr5+fnFdFvf1NR09erVy5Ytq6mpuXz5cj8/v0ePHgKBIDY21svLa+jQoUOGDPnwww/lcnncuHF6enrqdDqFQnHu3Llz5syp1+tDQkJ++OGHq1evLliwID4+/vjx4y+//LLRaHTv3j0xMbF79+4XLlyws7O7desWXV198ODBhoaGRkZGTp48GRgYeOvWrYiIiBEjRgQEBMgUS0pKEggEe/bsWb16daIoik5OTklJ6cKFCwcOHLDZbHFxcUZGhiRJdXV1S0vL8ePH9+3bV1lZuXXr1oiIiGvXrg0NDd24cePUqVMvXbr09u3biRMn5s2bV1RUtG/fvmvXruXm5p4/f56IiIiYmJi4uDg2NjYnJ+fy5ctz585du3YtLS1tx44dT548KSgoqKmpOX36dGlp6d+/f5s3b+bk5GzZsmX9+vXffvttVFTUuXPnzp07N2rUKLPZPHz4cFRU1M6dO5csWVJaWjps2LDAwMDAwEDfvn21Wi0gIKC4uHjChAlr1qzZsmWLVqtNTU2Vk5OztbVdu3ZtTU3NzMzMycnZvHnzlClT5s+fP3To0O7duxcuXHj58mU4HG5paRk0aFBsbOyxY8dOnjwZHh5eVFQUFhZmZ2cXFhbm5eU1adKkrVu3ampqnj59+sSJE7du3RoUFDRp0qTx48er1WqRSFRYWDhr1qzZs2cfPXq0ubl5yZIlnZ2dY2Ji1q9f36BBg9TUVIFAcOLEiRMnTvTq1evGjRsRERFardbBwaGwsNDV1fX48eM7d+6srq5etGjRnTt3cnNzq6urFQgE0dHR48aNmz59+ptvvsnNzS05OVkkEvXu3bu2tvaXX365Y8eOR48eubi4dOvWbcqUKXPmzCkvL6+oqCgvL3/8+PGXX36ZmZm5du2aUmpkZNTMmTOHDRtWUFBw8eJFh8MRFbfHYDD4+PhkZGQUFhZGRERWVlY/+OCDHTt2HDly5MyZM6dPn27ZsqWkpOTOnTu9vLxUKtW0adNMTU3XrVs3ZcqUKVOm5Ofnq9VqDx8+PH369L59+1RVVTdv3mxtbfX19XV2dm5ubr5161ZNTU1RUdHU1NTGjRsPHTrUy8srMjIyODiYj4/P1atXr1y5smnTpmvXrs2aNctgMPz+++8Gg6GiomLhwoXjxo2bMWNGaWnp+fPnS0tLS0vLBw8ePHDgQFBQ0Ny5c3fs2LH69esnLy+vUCjUrl27zp07m5qa5s6dW1RUVFZWlp6e/t1338XHx9fW1p4/f14ikZw6dUooFP71r3/t2LHjoEGDXFxc7t+/v7S09Pr168LCQp1ONzo6Wq/Xq9VqPz//1KlT165dW7169ZIlS/72t7/JZLKAgIBly5ZhYWEvXrxYunRpUFDQpEmTcnNz33zzzdDQ0Ly8vHPnzk2ZMqVz586CIG7evHn58mVnZ+dTp05VVVXdv3//iRMn5HL5xIkTr1y5kpCQsG3btl9//fXw4cNzc3Ofeuqp5s2bCwQCLpebSj179uzcuXOJiYkbN25csGBBWFjY+PHjHz9+PHny5O3bt6dOnapSqTKZjNlsjoyMzM3NnT17dkJCwn/+8x8zZ87U6XRaWlrm5uYGBweFQmFUVNXV1fXw8FAoFL/++mt6evqMGTPi4uIuXry4e/fuMpmMpaVlYmIiFouFQuH69euFQnHi42/duk2n0/X19du3b9+6dWtoaFhcXKzX66dPn672MPR6vVar1Wq1RkVFaTTa4sWLZ8yYUVhY+Msvv9Tr9RMnTnR3d587d27//v3Z2dm3bt06depUpVIJAKmpqXl5eSdPnjx+/HhBQUFhYWF+fn5mszkzM1Or1XK53MDAoNFoLCkpMRqNGo2mpaX14MEDRVGdO3dWq9V2dnbXrl1TStXW1jYyMlpbW4OCgtxcXU1NTQ0NDe/fv29rawvdJhQK9fT07Ny5c/LkyaWlpRUVFWfPnu3s7Dxw4MDExMSVK1e++eab/Pz8O3fuvHnzZlNTEwDk5eXdv3//jRs3srOzu3fvHhgYePr06Y0bN9asWePu7t6yZcsNGzZER0cfOnSoUCgMDAyUSCRv376NyWSioqKqqqoePHhgMBgajSYxMXH37t1mswkICFi0aFGr1Xrx4sW6des+f/7c3Ny8ceNGT0/PJUuWqNVqRVHFxcVNTU2FhYU1NTXV1dU1NjZ+8cUXqVSqzP97liRJd+7cOX/+fGtra0dHR19f3+bNm5OSkjQajeXl5YmJiWvWrHF0dDQwMNDV1XX8+HFJSUmBgYG7d+8eN26cRqMJDQ2VJHUg1t7ePnr06Pjx45cvX0ZRzM/P37p1a2VlZW1t7b1793JyclavXq3T6ebOnUsp9cCBA3fu3BEREXGJsi4uLnp6es6cOXP+/PnQ0NDY2NhTp07du3fv06dPDx8+PCgoSFVVFQgEamhoqNFoWrduXW5u7r179yRJGo1GqVSeOnWqqqpKq9Xm5+dXVVXl5OQUFhYWFRUlJSX9/Pzq6+vfvn17y5Yt/fv379SpU5cuXerVq1dUVFRaWtqECRNqa2u3b9/u3r17+PDhoaGhvXv3Hjt2LC0trbq6Wq/Xr169Wl1dfevWrVu3bsXHx3/7i927d7/99tsXLlywtLR88eLF33//XRTF+fPnZ8+evWHDBkVRHj9+/M477+Tn579y5UpzczMh5Ny5c+fPn//0009ms/l//ud/Ll26NG/evIKCgh49eqyurq6iokKn0x08ePDLL7+Ul5enpqZardbExOT+/fudOnUKCwsrKipkMlmfPn2GDRtmaGio0WhUKpUHDhyYmpr6/PnzDx8+HDlypLm52c3NbdWqVS9evOjTpw8A8PT0HDp0qLu7e+HChUFBQdbW1vfv31+r1d6/f3///v3R0dEymaygoMDR0XHatGkKhUKr1V65ckWj0RQKhePHj//444/q6+vPnDkTEhLy9/cPCgoqKioaGhpUKpWCgoLw8PD4+Phcu3YtLS1tx44dGo0mLi5OkjScPHly8+bNY2NjBw4c+OOPPzp37uzq6pqXl9fW1lb5EfXv3//ixYuFQuHYsWNms3np0qVms1mn0w0bNuzIkSN8Fy7Xr18PCQmxtLT05MkTd+7c0el0Fy5c2Lx589y5c8PCwnQ63cSJE5OSklRUVGzdujUoKEin0x04cODEiRNms3np0qWpqalms3nz5s3Q0FBNTc3BgwfXrVs3ZcqUKVOm5ObmFhUVubi4+OWXXxYXF3fq1KnIyMhjx44lJSU1NzcnJyfXr1/fsmXL+vXrV65c2dDQcOrUqUmTJm3YsEFr3N/fP3369OPHjzs7O48ePXrgwAE1NTU9PT2FQnHq1KmwsDAvL6+4uDg3N3flypU9e/ZMmDBhypQp7u7uP/78+YMHD1RVVZ08edLT0/PAgQPXr1+fM2fO7Nixw87O7unTp7q6ura2duLEiRs2bAgKCkpISGhsbJSZmVlaWioUikmTJrm5uUVFRdu2bTM3N7d///60Wi0QCEhISBAIBJFIfPTRR5ubmzk5Ob/++mtubm5ZWVn9+/c/dOjQV199de/evVarNXny5Fu3bj1z5syAAQNcXV2nP//8c1NT07p160JCQjZv3mxtbf30k09mzpyZn58fGBi4aNGio0ePHjp0qJ2dnaWlpfn5+cnJySZMmODm5paZmamqqsrNzX3+/Hlubu6OHTteeuml8ePH7927NzY2NisrS6/XX7p0aXh4eFhYWGNj46ioKPv27UtJSdHr9c+fP0+lUvfu3WttbX3v3j0nJ6eAgAAVFX344YczZ84cM2aMg4NDaGjo2rVr9+7dW7t2bW5u7oEDB8aNG9eqVavExETZ2dn9+vXbtm3bMhgMTk5ORqOxuLhYr9evXbtWXV396tWrU6dOpaSkHDhwIDMz88iRIxUKhU6nW7ly5a5du4KDg2MwGElJScXFxUePHm1sbMzMzGxsbFxdXaVSqdbW1sDAwODg4Li4uISEhBUrVnh4eIwdO1YsFm99PUKIjo5etWpVZmZmVFTUwYMHm5ubW1tb+/fv/+TJk1u3bnV1dS0qKlqxYkViYuKDBw86OTkFBgYWLVq0bNmy/Pz8jIwMrVYbERGhra1taGgYNWqUm5tbamrqm2++GRUVtXHjxn5+fqNGjfLx8QEAXV3durXvD9XX1paePHnyySefHDp0aHBw8Oeff27bti0sLOz+/fslEomfn9+VK1c++eST8+fP79y5UyAQGBQUlJOTY2BgEBgYePXq1dOnT//9739PnTr1v//+u3Xr1uDg4KVLl7S0tHx8fAAQEhLSoEGDkpKS1q5dW1lZOW3aNKFQeOrUqVOnTl24cOHs2bOJiYmzZ8+eP38+MDDQ2dk5KipKq9UGBQUlJSWlp6f36dPn2rVrXbp06dmzZ9zc3M6dO5eSklJcXDxx4sTjx4+nTZt27dq1q1evTp8+PTw8vHv3bgKB4Isvvnjw4EFgYGBAQMA///wzc+bMrl27tmyTzWYLCgqeeuqp9957z9PTc+HChYmJiTExMQcOHLh582Z9fX1VXV8RExMTEhLy9/e/efMmOTn50KFDp0+fPnXqlKZp/fr16969e/PmzcPDw8PJySkqKjp+/Ph9+/ZVm5a9e/eusLDw+PHjhw8fPnz48MuXL588eTIsLMzT07u9tdXW1l68ePHy5cuFQuHAgQNms3n8+PFms7lo0aKD Bw8eHDt2bPfu3ePj45OSkm7evGlsbGxkZNTQ0HDr1q2bNm1as2bNqVOnJk+efOjQoU5OTm5ubg0MDA4ODg4PDw8PDw8LC1u2bJmmaWfOnLly5YrRaKyoqHj8+PH79+9fvHjx+fPnHQ7HqVOn1q1b99atW+FwuLi4eP369VdffVUn2TQ0NDw8PFJSUnJycmw2m0wmE4lE4+Pjhw4dSklJ6dmzp4eHx6FDh3Jycs6ePRsUFDRv3jyFQnH16tUajWbevHlms7lPnz5qtbrDhw/37NlTo9Fcu3atUCg8deoUAMjJyRoMhpEjR7a0tDx69KhYLEZERKSlpSUlJbVa7eHhoa2tbW1tvWHDhq1bt+bk5Cil5s+fv3bt2n5+fvv27VVVVaWkpIyNjXv37l2zZs3OnTsHBgZmZWVFRka2adMmMzPzxYsXhUJhZGTk1KlTERER69atc3JyWr9+/dq1a0VRSklJ2bhxY1hY2IkTJ6xWq0Kh+Oyzz2w2m81mS0vLBw8ePH36dHFx8SeffOKvv/7q5eVlYmIiFou5ubnZ2NhsNlskEtnZ2Y2Nja2srCYmJiorK2NjY4uLix0dHU+fPu3t7b169eru3bvx8fHh4eFarTYwMHDq1KlDhw7Nnj17w4YNr1+/PnPmzIEDB4yMjGo0Gnt7e/fv31+v1xsYGOzfv/+iRYtiY2MLCgpqamqmTp2qUCgqKirOnDnz6tWrZWVlv/zyy507dyYlJe3fv//s2bM1NTUPHz7s7e3dunUrISHh/v37r1y5kpCQYDab8/Ly8vLykpOTq6ur09PTm5qaDh8+XF1dffXq1cjIyKqqqrS0tNzc3NTU1IqKipUrVz579mx1dfW0adMuX758+vTpmZmZ69evB4Cbm9vp06dXrlyZlZVVWVmZmZn58OFDR0fH+vXrjx8/vnv37pCQkLCwsDVr1sTGxpaXl5eXl9fU1Dx9+nTq1Kl8Fw4Azs7OFy9ePH36dGVlZUVFxSeffCISiW7cuPHDDz8EgMDAwAcPHpTrt7q6uqOjo7Ozc1ZWVk5OzmPHjunp6V26dFmzZs3o6OjY2Fh3d/eLFy/KZLKAgIBLly5ZWVlFRUXXrl2bmppevnz50qVLx48fP3LkyJo1a1xdXbOzswMCAvr166dSqQYHBx88eLBarXbp0qVJkyaFhoa2adMmvV4/Hh5eQEDAhg0btm7dum/fvjExMVOnThUIBDdu3BgXF7dp06ZRo0a5ubnFxcWtW7dOSEioubm5ubm5vr6+tLS0cePGxcXFffv2LSgoMDY2rkWLFnaePHnS1dW1sLDYv3//iBEj/P39oVAoPT29W7duyWTyjIwMDw/PzMzUaDQ3N7dTp05qtZqRkdG4ceP6+vqmpqampqb3799vbGxkMtl//vOf4+LiIiIi5s6dK5fLTUlJOXny5ODg4C9dugAAPj4+48ePP3Xq1EmTJu3cuTM4OHjp0qXV1dVms1kqlUeOHBl4/YCAAF9fX4VC8ffff584caKrq+vKlSsvXbp069atFy9erNFoNDU1nTJlyhtvvCEWi5OSklavXm1jY1Ov1xMSEiwtLT1//vyYMWOcnJxUKtX8+fNnzpxpa2u7fft2ampqZWVlf/7iF7Vs2fLw4cPhcPjkyZOnTp1avXp1R0fHjRs37t+/v7m5uZubW3Bw8MDAwIyMDK1Wm5CQUFZWtmbNmiNHjtTr9TKZ7Pr169evX8/NzU2n00+fPh09enTfvn2VUrm5uZmZmSkpKdXV1devXw8LC6uurq6trS1JUm9v76ysrIODg7W1tYeHh6urq0KhcObMmV9++cXGxqZOp4uOjp47d27Dhg1mszk4OHj69OnBwcGqqqr8/PxWrVqVu7t7dHR0VFQ0NzfX0dHZvHnzihUr2tvbN27c2MjIyOLiotFoNDEx8dSpU3Nzc4VCYWRkZGRkpFAorFarem9v78iRI5OTk4cOHZoxY8aoUaM8PT1LS0uLiorq6+v9/f1zcnKcnZ3PnDmjo6NjYWGhubn5+fPnr127Fh4eHhUVVVNT05w5c5o1a5aZmanVav34449KqcOHD+/Xr19MTMwXX3yxatWqBgYG2traBgYG8+bNmz9//t27d0+aNm2an5/f0NBw5cqVn3/+WavVxsXFZWVlrVy5MsAn+fDDD19//fVZs2YtWLCgvLw8KyuromgymRQKhWAwGB4e3qRJk86dO9fV1XXjxo2TJ0+eM2cOKysrVVWlUinp6+u7u7tnzpxZWFiYSqUNDQ1fvXo1Ly+vtra2qqrq3r17s7OzS0tLXV1dHR0dUVFRzc3N8/LyVlZWmpubc3Nzd+7cOXv27BUrVvTr1y80NJSSkiII4urVq+np6Z6enrm5ubq6urt37169ej18+HBnZ+egoKDExMSVK1c+ePBgcHDwr7/+WlRUVF5ertfrt2/f3rlzZ05OzmeffUYIKSoqSktLq6ioqKqqqqqqatasWUVFxbvvvhMQELBx40YnJ6eRI0d+/PHHWVlZGRkZUVFRu3fv/uWXX+RyuZaWlmg0GhERkZaWlpeXl5WVVVBQ0NramkgkcubMmWvXrq2urp46dWrbtm0PHz68e/fu559/ztXV9e+//xYIBN98882VK1dOnz49MzOzpqZmu3btGjVq9Mknn/z8888ymUwgEPTq1csY+x8ZGRm1tbVZWVnq6uq6urpWVVVVVVUsFistLT1//nxpaenWrVurq6v79euHw+GkpKREREQ0NTWlp6e/fft2X1/fS5cu+Xz+S5cuqdVqIpGYO3fu3Llzl5aWRkREqNfra9euXb169TNnzqSnp4+JiZFIlJiYuG3btk2bNqVSqU6ns7CwCAoK0tLSYmNj09LS7OzsLly4oNVqnZyc+vTp09u3b0eNGlVWVlZUVLRy5crExMRjx44lJyfXr1+/YcOGjRkzxs7ObvXq1Z07dz579myFQrF169bt27dPp9MlEom1a9euWrVqzZo169atO2nSpEuXLkZGRs6cOfO///1PR0dHU1PT5cuXz549+/LLL9fU1IwcOfLLL7/Mzc39xz/+YTKZMjMzfX19XV1dhUKhrVu3EhIS1q9fHx4e/sorryQnJ9fV1S0tLY2MjGzZsiUnJ6der6+pqfHw8AgLC0tJSampqQcPHqyqqpJKpUY6A263e+nSpSdPnvT19e3SpYtara5fv37w4MGzZ8+eNWvW4cOHk5KS1q1bl5KS8s9//jM1NfWLL75YW1t7//79LperVCqj0TgwMHDOnDnhcra1tR0/fvybN2/m5uZ6eHjExMQ0NDQ6nS4tLX348GH//v0zMzM//fTT7du3R0REbNy4MZFIvH//frFY3L17d//+/T/88ENRUVFGRsba2trq6urq6uq2traFhYW1tbVms1ksFkVRHT9+PDw8nJiYaG5u/orX6O7u3rRp0z///LNOp4uJiZFIlJOTc+XKlYCAAIMHD65atarKysrnn3+ek5MTHR0dGBhYsmRJV1fX9u3bl5SUODg4BAQEfPTRR1KpVGNj48aNG69cuZKXl9fc3LzXrl2lpKRSU1MLCgoGBwdLpdIFCxY0NjYuXry4Y8eOampqBgYGGo1mbm7O19fXw8MjOzt7x44dc3JyUlNTN2zYoFarW1paenp6Ghsbt2/fPi4ubv/+/QkJCUVFRVarNSgoqEWLFpycnAoKCk6fPl2j0dTU1CwsLM6dO7dgwQKxWMzX17ehoeGjjz7CZrM9PT3/+eefkydPJoTo2bPn9OnTExISZs+eHRMTc+bMmYYNG9bW1r777rs9evSwsLDw8vI6deoUFRXV1NS0tLQcOnTo0KFD8+bNGzRoUFZW1rp16yNHjtRqdfny5c8//7y7u3v37t0dHR2HDRvWpEmTZs2aPXXqVExMjFqtdvnyZbFYHBkZmZOTM2XKlPXr10+dOvXGjRtXr161sbEJDQ2VyWRKSkra29u9vLwMDQ1rbGxUVT148ODRo0c/+ugjgUDQ0tIyMjK+/PLL4uJiT0/Pn376aXJyMkdHx9jYWKFQ2NnZubq6mp+fP2HChHPnzj1z5sypU6fOnz//7bff5ufnZ2RkfPPNN0eMGLF79+7e3l6BQHD06NGVK1du3LjxzJkz165dO3fuXFJSkpeXl5WVZWJi8uDBA7FYHBsbGx0dXVJSkpOTc+rUqZ07d27cuLFHjx7Lli1TStXX1+/fv39XV9fQ0PD48eNms3nmzJkHDhyYmZnZ2NhERETk5uZevnx5UFBQZGSkr6/v+vXr7969u1+/frGxsb/++uumpqTCwsLS0tINGzZ069atS5cuRUdHq9XqjIwMLy+vdevW3blzx8/PD4fD6e/vHxMTI5vNhoSETp06BQdHV61aFR0dnZiYWHxMTIyLi1Or1V6vl0gkcnNzb9++3c3NLSIiYuHChStWrBgQEHDhwgWhUDhy5Mh4PF5UVNTQ0NDZ2RkVFfXbb7+dOHHi2LFjY8aMsbS0zM3NtbW1dejQoYmJiT/++OPHH3+clJQ0duxYZ2fn3NxcR0fH2NhYd3f3s2fPHjhwYOvWrXFxcc+fP1+pVGNjY5cvX7ZixYrU1NTExMS0tLSOHTtWVFQUFxdXV1d37twZHh5+7949W1tbW1tbWFiYnp7+888/R0RElJSUPHnyJC4uLiQkxN3d/UsvvVRUVLS3t1+8eLFp06aVK1dKpdK4uDgdHR2BQHD37t3Kysr169fPnDnzgQce0Gq1X375ZUlJibW19enTp1KpNGLEiB9//DFQ4Oeff1Yqlf/5z3+GDh2ampoaERFx6tSptLS0zp07a7Vat2zZMmPGjH//+9/ff/+dTqcdHR0XLlywsLDYv3//zp07nzx5IhKJTp8+3czMrKenp7b2/SksLOTk5MTHxy9evHjVqlV79+5NSUkxMzMLCQmJi4tr1qxZaWnpP//5z/Pnz1dWVh4+fJiZmfnhhx+mTZuWnJw8ZcoUJycnS0tLly5dmpeX5+Hh8emnn+bl5fX09AiCcPLkyS1btkyePHlKSkpDhw6tXLnS29s7KChow4YNZ8+enZKSsnnz5m3btg0ZMuStW7fUarW5ufnOnTtnzpzZtGlTXV199+7d06dPz5w5I5vNxo0b17Vr12PHjp0/f77ZbE5MTFy4cGHChAkbNmwQi8W9e/d+//33Xbt2hUJheHj4xIkT165dGxYWtnPnzn379k2bNm2qq6v79+9/9erVo0ePjho1qrS0NDY2ViqVdu3aFRYWNmfOnDp16rS1tW1ubm5hYeH06dNqtTp27NhLly79+vXLwsJiz549GRkZly5dev369dq1a0+cOHHGjBmrVq1SVVUdO3asra1ds2bNTp062djYqNVqQ0NDb9y40cfHJy4uTqlUWltbq6iokCTp0aNHREQEq9V68uTJyZMnh4aGmpqaFhYWnp6eDx8+vHjxIi8vr6mpydvbe+rUqUeOHLlw4cK6detEIpHMzEwh5NChQ2vWrHngwIGTJ0+2sbFpamr2z3+GYRiBQHDhwgULFizIzMxcv379cXFx165dO3jw4P79+2MwGLGxsenp6cePH1+7di0wMLBZs2baNv6cJEn//v1vaGgQi8Vffvnl8ePH169fHxERsXXr1pCQkH379m3YsGHlypVqtdrW1jY4OLhjx47BgcG8efMGDw/foEGDt2/f/uWXX7Zu3erh4TFt2rQLFy5IS0u7e/fusrKy5s2b5+XlBQUFLVq0CAgI+PXXX48dO3bs2LG2trY1a9Zcvny5pqamubm5VCqVSqVffPHFTZs2qdXqwsLCtLQ0g8HQ0dGhKGrChAlDhgwZPXq0UCjcunUrISHh6tWr1apVJ06cOH/+fHFx8Zw5c86dO7dv3758+fKwsDAnJ6dbt26Fh4cXFham0+lMTU1zc3MzMjJqaGhUq9WFCxfm5eWtXLmyrq5Oo9E0Nzfv37//0KFDhw4dampqWlhYqNVqkUgUFxd38eJFmUw2NjY2NDQ0NTXdd99906dPf+jQoW3atNHr9ampqVevXn3+/HlnZ+eQIUNyc3OlUun+/fubm5vLly8vLi6ePHlyz549Fy5cOHLkSH9//5EjR4yMjGQyGUNDQ41GY2hoqFQqy8rKfvrpp7/99tuhoeH06dMqlarTp0+vXbu2devW33///apVq7p06TJ9+nSRSNShQ4cBAwaEQuGMGTP0er1cLkdMTGxsbKxSqWQyGUNDQyMjI3Nzc41GY2trq9VqFYvFarVWWlqal5fXy8sLh8MxPT1dJpNNmDBBp9MlJCQ0NjbW1tZ2c3Kam5vz8vKUSiU2NjY0NDRUVDQ1NV2xYsW+ffuSk5MnTZo0Y8aMlpYWtVqdnJw8cODAqVOnjh49mpmZ2djYqNVqIyMjvb29R44cOXHixIiIiPT09BEjRphMpmAwGIlE0tDQsLy8PDk5+ciRI1Kp5OLi8uTJE41Gk5WVVVpaGh8fHxUVlZOTk56e3unTp3U6naIoqampkydPnjRpUl5eXlJSUltbW1paGhkZqdfrXV1dPT09jY2NDQ0NXV1dXV1dRUVFXV1dPT29S5cu8/HxysrKvLy8FhYWcrnc0dFRq9UGBQUlJCRcvXpVr9c/efKko6Nja2tra2vr6+u7d+8eHh7uvffee/To0cHBwUePHm1sbGxsbIyIiAgICEAoFIFAoLCwMC8vz8vLe+bMmdra2tra2l5UVERERERGRhQKRSaTqaurM5nM5s2bXV1dN2zYUKvVduzYMSgoKD8/Pzo6unfv3qtXr4yMjJYtW+7bty8oKAgNDVUpVUNDw927d7OzsysqKv7+++9mszkvL0+v16elpR05cqS6uvrJkydra2v/+c9/WlhYmM1md+/evWfPnvXr1xcUFDRq1MjExOTmzZsPHjyIjY1VSpWWllZQUKBWq4OCgnx9fdPT0xcVFZWVlRUVFR07dnR0dLS1tS0oKGhsbGxsbKyiouLm5lYsFi8vL+Li4srKyi5dumRlZdXQ0CAIwt3dXSaTLVy4MDU1Va/Xa2pq3rx589y5c+fl5RUKhdHR0V6vt7CwKCgoeHh4JCQkqNVqOp3e2to6MTGxurr6+Pj4+Pj4+/t/AXjw4MGGhobq6+uFQmFoaKher19QUKDRaCgpKVhrjCjUGhIS8scff1y7dq2wsDA8PNzExAQAx8fHn3/+eXl5+e7du1QqVWFhYZFIdOnSpaOjo4eHR0REqNVqERERMplMpVK1Wi0SiW7durVjx47ExERHR8fS0tLFixe/+eabZ8+eVVRUnD59+uHDh0+fPt2wYcPXX38tiqIePHiwZs2a4uLiCRMmvHXrVsCXAgo5f/6/PwD/7X/p+4p1GwAAAABJRU5ErkJggg==');
  background-size: contain;
}
</style>
