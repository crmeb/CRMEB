<template>
  <view
    class="aleart"
    v-if="aleartStatus"
    :style="'background-image: url(' + giftbag + ');'"
  >
    <!-- 海报绘制画布：尺寸与海报一致，移出可视区；组件内 canvas 需配合组件实例使用 -->
    <canvas canvas-id="posterCanvas" class="poster-canvas"></canvas>
    <template v-if="!posterImageStatus">
      <text class="iconfont icon-cha2 close" @click="posterImageClose"></text>
      <view class="from">{{ $t(`赠送给好友一份礼物`) }}</view>
      <view class="message">{{ giftData.message }}</view>
      <view class="aleart-body">
        <image class="goods-img" :src="giftData.image" mode="aspectFill"></image>
      </view>
      <view class="title line1">
        {{ $t(giftData.title) }}
      </view>
      <!-- 二维码仅用于生成海报，不在弹窗中展示 -->
      <!-- #ifdef H5 || APP-PLUS -->
      <view class="qrcode-hidden">
        <zb-code
          ref="qrcode"
          :show="codeShow"
          :cid="cid"
          :val="codeVal"
          :size="size"
          :unit="unit"
          :background="background"
          :foreground="foreground"
          :pdground="pdground"
          :icon="codeIcon"
          :iconSize="iconsize"
          :onval="onval"
          :loadMake="loadMake"
          @result="qrR"
        />
      </view>
      <!-- #endif -->
      <!-- #ifdef H5 -->
      <view class="btn" @click="copyLink()">
        {{ $t("复制礼物链接") }}
      </view>
      <!-- #endif -->
      <!-- #ifndef H5 -->
      <button class="btn" open-type="share" hover-class="none">
        {{ $t(`送给好友`) }}
      </button>
      <!-- #endif -->
      <view class="btn-clear" @click="getPoster()">
        {{ $t("保存海报") }}
      </view>
    </template>
    <template v-if="posterImageStatus">
      <text class="iconfont icon-cha2 close" @click="posterImageClose"></text>
      <image class="poster-img" :src="posterImage"></image>
      <!-- #ifdef H5 -->
      <view class="keep">{{ $t(`长按图片可以保存到手机`) }}</view>
      <!-- #endif -->
    </template>
  </view>
</template>

<script setup>
import { ref, watch, getCurrentInstance } from 'vue';
import { storeToRefs } from 'pinia';
import { HTTP_REQUEST_URL } from "@/config/app";
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
// #ifdef H5
import Auth from '@/libs/wechat';
// #endif

const props = defineProps({
  giftData: {
    type: Object,
  },
  aleartStatus: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'shareH5']);

const appStore = useAppStore();
const { uid } = storeToRefs(appStore);
// 组件内 canvas（小程序端 createCanvasContext/canvasToTempFilePath 必须传组件实例）
const instance = getCurrentInstance();

// 原代码未在 data 声明、通过 this.shareQrcode 访问的字段（undefined）
let shareQrcode;

const aleartData = ref({});
const bag = HTTP_REQUEST_URL + "/statics/images/canvas-bag.png";
const giftBorder = HTTP_REQUEST_URL + "/statics/images/gift-border.png";
const giftbag = HTTP_REQUEST_URL + "/statics/images/gift-bag.png";
//二维码参数（仅供海报生成，弹窗不展示）
const codeShow = ref(false);
const cid = "1";
const codeVal = ref(""); // 要生成的二维码值
const size = 200; // 二维码大小
const unit = "px"; // 单位
const background = "#FFF"; // 背景色
const foreground = "#000"; // 前景色
const pdground = "#000"; // 角标色
const codeIcon = ""; // 二维码图标
const iconsize = 40; // 二维码图标大小
const lv = 3; // 二维码容错级别 ， 一般不用设置，默认就行
const onval = true; // val值变化时自动重新生成二维码
const loadMake = true; // 组件加载完成后自动生成二维码
const PromotionCode = ref("");
const posterImageStatus = ref(false);
const posterImage = ref("");

watch(() => props.aleartStatus, (status) => {
  if (!status) {
    aleartData.value = {};
    // 关闭时重置海报状态，避免下次打开直接显示上一次的旧海报
    posterImageStatus.value = false;
    posterImage.value = "";
  } else {
    // #ifdef H5
    codeVal.value =
      window.location.origin +
      "/pages/goods/receive_gift/index?id=" +
      props.giftData.id +
      "&spid=" +
      uid.value;
    // #endif
    // #ifdef APP-PLUS
    codeVal.value =
      HTTP_REQUEST_URL +
      "/pages/goods/receive_gift/index?id=" +
      props.giftData.id +
      "&spid=" +
      uid.value;
    // #endif
    // #ifdef MP
    PromotionCode.value = props.giftData.code;
    // #endif
  }
});

function copyLink() {
  uni.setClipboardData({
    data: codeVal.value,
  });
}

function qrR(res) {
  // #ifdef H5
  if (!Auth.isWeixin() || shareQrcode != "1") {
    PromotionCode.value = res;
  }
  // #endif
  // #ifdef APP-PLUS
  PromotionCode.value = res;
  // #endif
}

//隐藏弹窗
function posterImageClose() {
  posterImageStatus.value = false;
  emit("close", false);
}

/**
 * 绘制海报
 * 头像、二维码可能缺失（订单详情不返回赠送人头像、二维码异步生成），缺失时跳过对应绘制
 * @param {Array} loadedImages [背景, 商品边框, 商品图, 头像, 二维码]
 * @param {string} name 赠送人昵称（可能为空）
 * @param {string} store_name 商品标题
 * @return {Promise<string>} 海报临时文件路径
 */
function drawPoster(loadedImages, name, store_name) {
  // 截断标题函数
  function truncateTitle(title, maxLength) {
    if (title.length > maxLength) {
      return title.substring(0, maxLength) + "...";
    }
    return title;
  }
  // 获取canvas上下文（组件内 canvas 需传组件实例）
  const ctx = uni.createCanvasContext("posterCanvas", instance);
  return new Promise((resolve, reject) => {
    uni.getImageInfo({
      src: loadedImages[0],
      fail: (err) => reject(err),
      success: () => {
        try {
          // 海报尺寸
          const posterWidth = 375;
          const posterHeight = 579;
          // 绘制背景图
          ctx.drawImage(loadedImages[0], 0, 0, posterWidth, posterHeight);
          ctx.save();

          // 头像和标题的布局
          const avatarSize = 22; // 头像尺寸
          const nickname = name || ""; // 昵称
          const title = i18n.t(`赠送给好友一份礼物`); // 标题文字
          const titleFontSize = 14; // 标题字号
          const nicknameFontSize = 14; // 昵称字号
          const padding = 10; // 元素之间的间距
          const avatarImage = loadedImages[3]; // 头像（可能为空）
          const hasAvatar = !!avatarImage;

          // 计算标题/昵称宽度，按实际存在的元素计算总宽并居中
          ctx.setFontSize(titleFontSize);
          const titleWidth = ctx.measureText(title).width;
          const nicknameWidth = nickname ? ctx.measureText(nickname).width : 0;
          const totalWidth =
            (hasAvatar ? avatarSize + padding : 0) +
            (nickname ? nicknameWidth + padding : 0) +
            titleWidth;

          // 计算起始绘制位置（水平居中）
          const startX = (posterWidth - totalWidth) / 2;
          const startY = 77; // 距离顶部的距离
          let cursorX = startX;

          // 绘制头像（无头像时不占位，标题自动居中）
          if (hasAvatar) {
            const avatarX = startX + avatarSize / 2; // 头像中心点 X
            const avatarY = startY + avatarSize / 2; // 头像中心点 Y
            ctx.save(); // 保存画布状态
            ctx.beginPath();
            ctx.arc(avatarX, avatarY, avatarSize / 2, 0, Math.PI * 2); // 绘制圆形路径
            ctx.clip(); // 裁剪圆形区域
            ctx.drawImage(avatarImage, startX, startY, avatarSize, avatarSize); // 绘制头像
            ctx.restore(); // 恢复画布状态
            cursorX += avatarSize + padding;
          }

          // 绘制昵称（可能为空）
          if (nickname) {
            ctx.setFontSize(nicknameFontSize);
            ctx.setTextAlign("left");
            ctx.fillText(nickname, cursorX, startY + avatarSize - 5); // 调整文字垂直居中
            cursorX += nicknameWidth + padding;
          }

          // 绘制标题
          ctx.setFontSize(titleFontSize);
          ctx.setTextAlign("left");
          ctx.fillText(title, cursorX, startY + avatarSize - 5);

          // 商品图尺寸
          const productImageSize = 225; // 商品图尺寸为 225px x 225px

          // 绘制商品图边框
          const productBorderX = (posterWidth - productImageSize) / 2; // 水平居中
          const productBorderY = startY + avatarSize + 31; // 距离头像和标题的间距
          ctx.drawImage(
            loadedImages[1],
            productBorderX,
            productBorderY,
            productImageSize,
            productImageSize
          );

          // 绘制商品图
          const productImagePadding = 10; // 商品图与边框的内边距
          const productImageX = productBorderX + productImagePadding;
          const productImageY = productBorderY + productImagePadding + 11;
          const productImageInnerSize =
            productImageSize - 2 * productImagePadding; // 商品图实际绘制尺寸
          ctx.drawImage(
            loadedImages[2],
            productImageX,
            productImageY,
            productImageInnerSize,
            productImageInnerSize - 10
          );

          // 绘制商品标题
          const productTitle = store_name || "";
          const maxTitleLength = 20; // 标题最大长度
          const truncatedTitle = truncateTitle(productTitle, maxTitleLength); // 截断标题
          ctx.setFontSize(14);
          ctx.setTextAlign("center");
          ctx.fillText(
            truncatedTitle,
            posterWidth / 2,
            productBorderY + productImageSize + 26
          );

          // 绘制分享二维码（异步生成，可能为空）
          if (loadedImages[4]) {
            const qrCodeSize = 100;
            const qrCodeX = (posterWidth - qrCodeSize) / 2;
            const qrCodeY = productBorderY + productImageSize + 63; // 距离商品标题的间距
            ctx.drawImage(
              loadedImages[4],
              qrCodeX,
              qrCodeY,
              qrCodeSize,
              qrCodeSize
            );
          }

          // 绘制完成
          ctx.draw(false, () => {
            // 生成海报图片
            uni.canvasToTempFilePath(
              {
                canvasId: "posterCanvas",
                width: posterWidth,
                height: posterHeight,
                success: (res) => {
                  resolve(res.tempFilePath);
                },
                fail: (err) => {
                  reject(err);
                },
              },
              instance
            );
          });
        } catch (err) {
          reject(err);
        }
      },
    });
  });
}

function loadImage(src) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.crossOrigin = "anonymous"; // 允许跨域
    img.src = src;
    img.onload = () => {
      resolve(img);
    };
    img.onerror = (err) => reject(err);
  });
}

function share() {
  emit("shareH5");
}

/**
 * 等待二维码生成（zb-code 异步出图，避免绘制时图片地址为空）
 * @param {number} timeout 超时毫秒
 * @return {Promise<string>} 二维码图片地址，超时返回空串
 */
function waitForPromotionCode(timeout = 3000) {
  return new Promise((resolve) => {
    if (PromotionCode.value) {
      resolve(PromotionCode.value);
      return;
    }
    const start = Date.now();
    const timer = setInterval(() => {
      if (PromotionCode.value || Date.now() - start >= timeout) {
        clearInterval(timer);
        resolve(PromotionCode.value || "");
      }
    }, 50);
  });
}

/**
 * 海报图片同源化（H5）
 * 跨域图片会污染 canvas，导致 toDataURL/getImageData 抛 SecurityError；
 * 后端自身域名（HTTP_REQUEST_URL）的图片改为相对路径，由开发环境 vite 代理走同源，
 * 生产同源部署下同样可用；其他 CDN 域名保持原样，由其自行提供 CORS。
 * @param {string} url 原始图片地址
 * @return {string} 可用于绘制/下载的图片地址
 */
function toSameOriginUrl(url) {
  if (!url) return "";
  // #ifdef H5
  if (/^https?:\/\//i.test(url)) {
    try {
      const target = new URL(url, window.location.origin);
      if (target.origin === window.location.origin) {
        return target.href;
      }
      const apiOrigin = new URL(HTTP_REQUEST_URL).origin;
      if (target.origin === apiOrigin) {
        return target.pathname + target.search;
      }
    } catch (e) {
      return url;
    }
  }
  // #endif
  return url;
}

async function getPoster() {
  // 顺序固定：[背景, 商品边框, 商品图, 头像, 二维码]；空项在绘制时跳过
  const qrCode = await waitForPromotionCode();
  const images = [
    toSameOriginUrl(bag),
    toSameOriginUrl(giftBorder),
    toSameOriginUrl(props.giftData.image || ""),
    toSameOriginUrl(props.giftData.avatar || ""),
    toSameOriginUrl(qrCode),
  ];
  // #ifdef MP
  const postImg = ["", "", "", "", ""];
  await Promise.all(
    images.map(
      (src, index) =>
        new Promise((resolve) => {
          if (!src) {
            resolve();
            return;
          }
          uni.downloadFile({
            url: src,
            success: (res) => {
              if (res.statusCode == 200) postImg[index] = res.tempFilePath;
              resolve();
            },
            fail: () => resolve(),
          });
        })
    )
  );
  goPoster(postImg);
  // #endif
  // #ifndef MP
  goPoster(images);
  // #endif
}

function goPoster(postImg) {
  drawPoster(postImg, props.giftData.nickname, props.giftData.title)
    .then((posterPath) => {
      // #ifdef APP-PLUS || MP
      savePosterPathMp(posterPath);
      // #endif
      // #ifdef H5
      posterImage.value = posterPath;
      posterImageStatus.value = true;
      // #endif
    })
    .catch((err) => {
      console.error("海报生成失败:", err);
      uni.showToast({ title: i18n.t(`海报生成失败`), icon: "none" });
    });
}

// #ifdef APP-PLUS || MP
function savePosterPathMp(url) {
  uni.saveImageToPhotosAlbum({
    filePath: url,
    success: function (res) {
      util.Tips({
        title: i18n.t(`保存成功`),
        icon: "success",
      });
    },
    fail: function (res) {
      util.Tips({
        title: i18n.t(`保存失败`),
      });
    },
  });
}
// #endif

function savePic(url) {
  var a = document.createElement("a"); // 生成一个a元素
  a.download = "Gift"; // 设置图片名称
  a.style.display = "none";
  a.href = url; // 将生成的URL设置为a.href属性
  document.body.appendChild(a); // 将a标签追加到文档对象中
  a.click(); // 触发a的单击事件
  a.remove(); // 一次性的，用完就删除a标签
}
</script>

<style lang="scss" scoped>
.aleart {
  width: 600rpx;
  height: 980rpx;
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  z-index: 999;
  top: 50%;
  margin-top: -490rpx;
  background-color: #fff;
  border-radius: 32rpx;
  background-size: 100% 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  .poster-img {
    width: 100%;
    height: 100%;
    border-radius: 32rpx;
  }
  .from {
    font-size: 28rpx;
    font-weight: 500;
    color: #333333;
    margin-bottom: 16rpx;
  }
  .message {
    font-weight: 400;
    font-size: 26rpx;
    color: #999999;
    margin-bottom: 42rpx;
  }
  .aleart-body {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 396rpx;
    height: 419rpx;
    background-image: url("../../static/gift-border.png");
    background-size: 100% 100%;
    margin-bottom: 16rpx;
    overflow: hidden;
    .goods-img {
      width: 360rpx;
      height: 360rpx;
      margin-top: 24rpx;
    }
  }
  .title {
    max-width: 80%;
    font-weight: 400;
    font-size: 28rpx;
    color: #333333;
    margin-bottom: 12rpx;
    text-align: center;
  }
  .btn,
  .btn-clear {
    width: 396rpx;
    height: 80rpx;
    line-height: 80rpx;
    border-radius: 20px;
    text-align: center;
    font-size: 28rpx;
  }
  .btn {
    color: #fff;
    background: linear-gradient(90deg, #ff7931 0%, #e93323 100%);
  }
  .btn-clear {
    margin-top: 20rpx;
    color: #e93323;
    border: 1px solid #e93323;
  }
  .keep {
    font-size: 24rpx;
    font-weight: bold;
    color: rgba(255, 255, 255, 0.7);
    position: fixed;
    right: calc(50% - 130rpx);
    bottom: -45rpx;
    display: block;
  }
  .close {
    font-size: 50rpx;
    font-weight: bold;
    color: #fff;
    position: fixed;
    right: calc(50% - 23rpx);
    bottom: -110rpx;
    display: block;
  }
}

/* 海报画布：尺寸与海报一致，移出可视区但仍参与布局（canvas 需要有效宽高） */
.poster-canvas {
  position: fixed;
  left: -2000px;
  top: 0;
  width: 375px;
  height: 579px;
  z-index: -1;
}

/* 二维码画布只服务海报生成，视觉上不出现 */
.qrcode-hidden {
  position: absolute !important;
  width: 0 !important;
  height: 0 !important;
  overflow: hidden !important;
  opacity: 0 !important;
  pointer-events: none !important;
}
</style>
