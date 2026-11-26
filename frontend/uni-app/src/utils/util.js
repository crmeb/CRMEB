// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { TOKENNAME, HTTP_REQUEST_URL } from "../config/app.js";
import i18n from "./lang.js";
import { pathToBase64 } from "@/plugin/image-tools/index.js";
import { useAppStore } from "@/store/modules/app";
// #ifdef APP-PLUS
import permision from "./permission.js";
// #endif
export default {
  /**
   * opt  object | string
   * to_url object | string
   * 例:
   * this.Tips('/pages/test/test'); 跳转不提示
   * this.Tips({title:'提示'},'/pages/test/test'); 提示并跳转
   * this.Tips({title:'提示'},{tab:1,url:'/pages/index/index'}); 提示并跳转值table上
   * tab=1 一定时间后跳转至 table上
   * tab=2 一定时间后跳转至非 table上
   * tab=3 一定时间后返回上页面
   * tab=4 关闭所有页面，打开到应用内的某个页面
   * tab=5 关闭当前页面，跳转到应用内的某个页面
   */
  Tips: function (opt, to_url) {
    if (typeof opt == "string") {
      to_url = opt;
      opt = {};
    }
    // 兼容接口/异常对象：非字符串时取 msg/message，避免 toast 出现空黑框
    let title = opt.title || "";
    if (title && typeof title !== "string") {
      title = title.msg || title.message || "";
    }
    title = title == null ? "" : String(title);
    let icon = opt.icon || "none",
      endtime = opt.endtime || 2000,
      success = opt.success;
    if (title)
      uni.showToast({
        title: title,
        icon: icon,
        duration: endtime,
        success,
      });
    if (to_url != undefined) {
      if (typeof to_url == "object") {
        let tab = to_url.tab || 1,
          url = to_url.url || "";
        switch (tab) {
          case 1:
            //一定时间后跳转至 table
            setTimeout(function () {
              uni.switchTab({
                url: url,
              });
            }, endtime);
            break;
          case 2:
            //跳转至非table页面
            setTimeout(function () {
              uni.navigateTo({
                url: url,
              });
            }, endtime);
            break;
          case 3:
            //返回上页面
            setTimeout(function () {
              // #ifndef H5
              uni.navigateBack({
                delta: parseInt(url),
              });
              // #endif
              // #ifdef H5
              history.back();
              // #endif
            }, endtime);
            break;
          case 4:
            //关闭所有页面，打开到应用内的某个页面
            setTimeout(function () {
              uni.reLaunch({
                url: url,
              });
            }, endtime);
            break;
          case 5:
            //关闭当前页面，跳转到应用内的某个页面
            setTimeout(function () {
              uni.redirectTo({
                url: url,
              });
            }, endtime);
            break;
        }
      } else if (typeof to_url == "function") {
        setTimeout(function () {
          to_url && to_url();
        }, endtime);
      } else {
        //没有提示时跳转不延迟
        setTimeout(
          function () {
            uni.navigateTo({
              url: to_url,
            });
          },
          title ? endtime : 0,
        );
      }
    }
  },
  /**
   * 移除数组中的某个数组并组成新的数组返回
   * @param array array 需要移除的数组
   * @param int index 需要移除的数组的键值
   * @param string | int 值
   * @return array
   *
   */
  ArrayRemove: function (array, index, value) {
    const valueArray = [];
    if (array instanceof Array) {
      for (let i = 0; i < array.length; i++) {
        if (typeof index == "number" && array[index] != i) {
          valueArray.push(array[i]);
        } else if (typeof index == "string" && array[i][index] != value) {
          valueArray.push(array[i]);
        }
      }
    }
    return valueArray;
  },
  /**
   * 生成海报获取文字
   * @param string text 为传入的文本
   * @param int num 为单行显示的字节长度
   * @return array
   */
  textByteLength: function (text, num) {
    let strLength = 0;
    let rows = 1;
    let str = 0;
    let arr = [];
    for (let j = 0; j < text.length; j++) {
      if (text.charCodeAt(j) > 255) {
        strLength += 2;
        if (strLength > rows * num) {
          strLength++;
          arr.push(text.slice(str, j));
          str = j;
          rows++;
        }
      } else {
        strLength++;
        if (strLength > rows * num) {
          arr.push(text.slice(str, j));
          str = j;
          rows++;
        }
      }
    }
    arr.push(text.slice(str, text.length));
    return [strLength, arr, rows]; //  [处理文字的总字节长度，每行显示内容的数组，行数]
  },

  /**
   * 获取商品分享海报
   * @param array arr2 海报素材
   * @param string store_name 素材文字
   * @param string price 价格
   * @param string ot_price 原始价格
   * @param function successFn 回调函数
   *
   *
   */
  PosterCanvas: function (arr2, store_name, price, ot_price, successFn) {
    let that = this;
    uni.showLoading({
      title: i18n.t(`海报生成中`),
      mask: true,
    });
    const ctx = uni.createCanvasContext("myCanvas");
    ctx.clearRect(0, 0, 0, 0);

    /**
     * 只能获取合法域名下的图片信息,本地调试无法获取
     *
     */
    ctx.fillStyle = "#fff";
    ctx.fillRect(0, 0, 750, 1250);
    uni.getImageInfo({
      src: arr2[0],
      success: function (res) {
        const WIDTH = res.width;
        const HEIGHT = res.height;
        // ctx.drawImage(arr2[0], 0, 0, WIDTH, 1050);
        ctx.drawImage(arr2[1], 0, 0, WIDTH, WIDTH);
        ctx.save();
        let r = 110;
        let d = r * 2;
        let cx = 480;
        let cy = 790;
        ctx.arc(cx + r, cy + r, r, 0, 2 * Math.PI);
        // ctx.clip();
        ctx.drawImage(arr2[2], cx, cy, d, d);
        ctx.restore();
        const CONTENT_ROW_LENGTH = 20;
        let [contentLeng, contentArray, contentRows] = that.textByteLength(
          store_name,
          CONTENT_ROW_LENGTH,
        );
        if (contentRows > 2) {
          contentRows = 2;
          let textArray = contentArray.slice(0, 2);
          textArray[textArray.length - 1] += "…";
          contentArray = textArray;
        }
        ctx.setTextAlign("left");
        ctx.setFontSize(36);
        ctx.setFillStyle("#000");
        // let contentHh = 36 * 1.5;
        let contentHh = 36;
        for (let m = 0; m < contentArray.length; m++) {
          if (m) {
            ctx.fillText(contentArray[m], 50, 1000 + contentHh * m + 18, 1100);
          } else {
            ctx.fillText(contentArray[m], 50, 1000 + contentHh * m, 1100);
          }
        }
        ctx.setTextAlign("left");
        ctx.setFontSize(72);
        ctx.setFillStyle("#DA4F2A");
        ctx.fillText(i18n.t(`￥`) + price, 40, 820 + contentHh);

        ctx.setTextAlign("left");
        ctx.setFontSize(36);
        ctx.setFillStyle("#999");
        // 商品海报划线价
        if (ot_price) {
          ctx.fillText(i18n.t(`￥`) + ot_price, 50, 876 + contentHh);
          var underline = function (
            ctx,
            text,
            x,
            y,
            size,
            color,
            thickness,
            offset,
          ) {
            var width = ctx.measureText(text).width;
            switch (ctx.textAlign) {
              case "center":
                x -= width / 2;
                break;
              case "right":
                x -= width;
                break;
            }

            y += size + offset;

            ctx.beginPath();
            ctx.strokeStyle = color;
            ctx.lineWidth = thickness;
            ctx.moveTo(x, y);
            ctx.lineTo(x + width, y);
            ctx.stroke();
          };
          underline(ctx, i18n.t(`￥`) + ot_price, 55, 865, 36, "#999", 2, 0);
        }
        ctx.setTextAlign("left");
        ctx.setFontSize(28);
        ctx.setFillStyle("#999");
        ctx.fillText(i18n.t(`长按或扫描查看`), 490, 1030 + contentHh);
        ctx.draw(true, function () {
          uni.canvasToTempFilePath({
            canvasId: "myCanvas",
            fileType: "png",
            destWidth: WIDTH,
            destHeight: HEIGHT,
            success: function (res) {
              uni.hideLoading();
              successFn && successFn(res.tempFilePath);
            },
          });
        });
      },
      fail: function (err) {
        uni.hideLoading();
        that.Tips({
          title: i18n.t(`无法获取图片信息`),
        });
      },
    });
  },
  /**
   * 获取砍价/拼团海报
   * @param array arr2 海报素材 背景图
   * @param string store_name 素材文字
   * @param string price 价格
   * @param string ot_price 原始价格
   * @param function successFn 回调函数
   *
   *
   */
  bargainPosterCanvas: function (
    arr2,
    title,
    label,
    msg,
    price,
    wd,
    hg,
    successFn,
    component,
  ) {
    let that = this;
    // 固定设计稿像素，必须与 canvas 的 width/height 一致
    const W = wd || 750;
    const H = hg || 1334;
    const theme = "#E93323";
    const themeDeep = "#C81E12";
    const productSrc = arr2[1];
    const qrSrc = arr2[2];

    const loadImage = (src) =>
      new Promise((resolve) => {
        if (!src) {
          resolve(null);
          return;
        }
        uni.getImageInfo({
          src,
          success: (res) => resolve(res),
          fail: () => resolve(null),
        });
      });

    const roundPath = (ctx, x, y, w, h, r, topOnly) => {
      const radius = Math.min(r, w / 2, h / 2);
      const br = topOnly ? 0 : radius;
      ctx.beginPath();
      ctx.moveTo(x + radius, y);
      ctx.arcTo(x + w, y, x + w, y + h, radius);
      ctx.arcTo(x + w, y + h, x, y + h, br);
      ctx.arcTo(x, y + h, x, y, br);
      ctx.arcTo(x, y, x + w, y, radius);
      ctx.closePath();
    };

    const fillRoundRect = (ctx, x, y, w, h, r, color) => {
      roundPath(ctx, x, y, w, h, r, false);
      ctx.setFillStyle(color);
      ctx.fill();
    };

    const drawCoverImage = (ctx, src, info, x, y, w, h, radius, topOnly) => {
      if (!info) {
        roundPath(ctx, x, y, w, h, radius, topOnly);
        ctx.setFillStyle("#F3F4F6");
        ctx.fill();
        return;
      }
      const iw = info.width || w;
      const ih = info.height || h;
      const scale = Math.max(w / iw, h / ih);
      const dw = iw * scale;
      const dh = ih * scale;
      const dx = x + (w - dw) / 2;
      const dy = y + (h - dh) / 2;
      ctx.save();
      roundPath(ctx, x, y, w, h, radius, topOnly);
      ctx.clip();
      ctx.drawImage(src, dx, dy, dw, dh);
      ctx.restore();
    };

    Promise.all([loadImage(productSrc), loadImage(qrSrc)]).then(
      ([productInfo, qrInfo]) => {
        const ctx = uni.createCanvasContext("myCanvas", component);
        ctx.clearRect(0, 0, W, H);

        // ===== 背景（分层过渡，避免上下硬切）=====
        ctx.setFillStyle(theme);
        ctx.fillRect(0, 0, W, H);
        for (let i = 0; i < 420; i++) {
          const a = (i / 420) * 0.28;
          ctx.setFillStyle(`rgba(120, 10, 6, ${a})`);
          ctx.fillRect(0, 520 + i, W, 1);
        }
        ctx.setFillStyle("rgba(255,255,255,0.08)");
        ctx.beginPath();
        ctx.arc(700, -30, 220, 0, Math.PI * 2);
        ctx.fill();
        ctx.beginPath();
        ctx.arc(-70, 280, 180, 0, Math.PI * 2);
        ctx.fill();

        ctx.setFillStyle("rgba(255,255,255,0.85)");
        ctx.setTextAlign("center");
        ctx.setFontSize(22);
        ctx.fillText(i18n.t(`帮我砍一刀`), W / 2, 56);
        ctx.setFillStyle("#FFFFFF");
        ctx.setFontSize(40);
        ctx.fillText(i18n.t(`邀请好友帮砍价`), W / 2, 108);

        // ===== 主图 + 一体信息底栏（标题/价格/二维码，无中间白条留白）=====
        const cardX = 42;
        const cardW = 666;
        const radius = 28;
        const pad = 28;
        const imgH = 700;
        const qrSize = 188;
        const infoH = 268;
        const cardH = imgH + infoH;
        const cardY = Math.floor((H - cardH + 36) / 2);

        let [, lines] = that.textByteLength(title || "", 18);
        if (lines.length > 2) {
          lines = lines.slice(0, 2);
          lines[1] = (lines[1] || "").replace(/.{0,1}$/, "…");
        }

        fillRoundRect(ctx, cardX + 5, cardY + 12, cardW, cardH, radius, "rgba(0,0,0,0.2)");
        fillRoundRect(ctx, cardX, cardY, cardW, cardH, radius, "#FFFFFF");

        drawCoverImage(ctx, productSrc, productInfo, cardX, cardY, cardW, imgH, radius, true);
        fillRoundRect(ctx, cardX + 22, cardY + 22, 100, 38, 19, theme);
        ctx.setFillStyle("#FFFFFF");
        ctx.setTextAlign("center");
        ctx.setFontSize(22);
        ctx.fillText(i18n.t(`砍价`), cardX + 72, cardY + 48);

        const infoTop = cardY + imgH;
        ctx.setFillStyle("#FFF7F5");
        ctx.fillRect(cardX, infoTop, cardW, infoH);

        const qrX = cardX + cardW - pad - qrSize;
        const qrY = infoTop + (infoH - qrSize) / 2;
        fillRoundRect(ctx, qrX - 8, qrY - 8, qrSize + 16, qrSize + 16, 14, "#FFFFFF");
        if (qrInfo) {
          ctx.drawImage(qrSrc, qrX, qrY, qrSize, qrSize);
        } else {
          fillRoundRect(ctx, qrX, qrY, qrSize, qrSize, 8, "#EEEEEE");
        }

        const leftX = cardX + pad;
        const leftMax = qrX - leftX - 20;
        ctx.setFillStyle("#1A1A1A");
        ctx.setTextAlign("left");
        ctx.setFontSize(28);
        lines.forEach((line, idx) => {
          ctx.fillText(line, leftX, infoTop + 42 + idx * 36, leftMax);
        });

        const tagText = label || i18n.t(`已砍至`);
        const tagW = Math.max(90, String(tagText).length * 20 + 28);
        const priceBase = infoTop + 42 + lines.length * 36 + 18;
        fillRoundRect(ctx, leftX, priceBase, tagW, 32, 16, theme);
        ctx.setFillStyle("#FFFFFF");
        ctx.setTextAlign("center");
        ctx.setFontSize(18);
        ctx.fillText(tagText, leftX + tagW / 2, priceBase + 22);

        ctx.setFillStyle(theme);
        ctx.setTextAlign("left");
        ctx.setFontSize(24);
        ctx.fillText(i18n.t(`￥`), leftX + tagW + 12, priceBase + 26);
        ctx.setFontSize(48);
        ctx.fillText(String(price || "0"), leftX + tagW + 40, priceBase + 30);

        ctx.setFillStyle("#8F3F38");
        ctx.setFontSize(22);
        ctx.fillText(msg || i18n.t(`扫码帮好友砍价`), leftX, priceBase + 66, leftMax);
        ctx.setFillStyle("#AAAAAA");
        ctx.setFontSize(20);
        ctx.fillText(i18n.t(`长按识别图中二维码`), leftX, priceBase + 100, leftMax);

        ctx.draw(false, () => {
          setTimeout(() => {
            uni.canvasToTempFilePath(
              {
                canvasId: "myCanvas",
                fileType: "png",
                quality: 1,
                destWidth: W,
                destHeight: H,
                success: (res) => {
                  successFn && successFn(res.tempFilePath);
                  uni.hideLoading();
                },
                fail: function () {
                  uni.hideLoading();
                  that.Tips({
                    title: i18n.t(`海报生成失败`),
                  });
                },
              },
              component,
            );
          }, 80);
        });
      },
    );
  },
  /**
   * 用户分销海报（底图 + 二维码 + 昵称）
   * @param {string[]} arr2 [二维码, 背景图]
   * @param {string} nickname
   * @param {string} sitename
   * @param {number} index 兼容旧调用；现统一画到 spreadPosterCanvas
   * @param {number} w 设计宽，默认 750
   * @param {number} h 设计高，默认 1334
   * @param {Function} [successFn]
   * @param {object} [component] 组件实例，H5/小程序自定义组件内绘制必传
   * @returns {Promise<string>}
   */
  userPosterCanvas: function (
    arr2,
    nickname,
    sitename,
    index,
    w,
    h,
    successFn,
    component,
  ) {
    const W = w > 0 ? w : 750;
    const H = h > 0 ? h : 1334;
    const canvasId = "spreadPosterCanvas";
    const qrSrc = arr2[0];
    const bgSrc = arr2[1];

    const loadImage = (src) =>
      new Promise((resolve) => {
        if (!src) {
          resolve(null);
          return;
        }
        uni.getImageInfo({
          src,
          success: (res) => resolve(res),
          fail: () => resolve(null),
        });
      });

    return new Promise((resolve, reject) => {
      Promise.all([loadImage(bgSrc), loadImage(qrSrc)]).then(([bgInfo, qrInfo]) => {
        if (!bgInfo || !bgSrc) {
          reject(new Error(i18n.t(`无法获取图片信息`)));
          return;
        }
        if (!qrInfo || !qrSrc) {
          reject(new Error(i18n.t(`二维码生成失败`)));
          return;
        }

        const ctx = uni.createCanvasContext(canvasId, component);
        ctx.clearRect(0, 0, W, H);
        ctx.setFillStyle("#ffffff");
        ctx.fillRect(0, 0, W, H);
        // 底图按 cover 铺满，避免比例拉伸变形
        const iw = bgInfo.width || W;
        const ih = bgInfo.height || H;
        const scale = Math.max(W / iw, H / ih);
        const dw = iw * scale;
        const dh = ih * scale;
        ctx.drawImage(bgSrc, (W - dw) / 2, (H - dh) / 2, dw, dh);

        // 相对设计稿比例（与历史分销海报留白区一致）
        const codex = 0.1906;
        const codey = 0.7746;
        const codeSize = 0.21666;
        const namex = 0.4283;
        const namey = 0.8215;
        const markx = 0.4283;
        const marky = 0.8685;
        const qrBox = W * codeSize;
        ctx.drawImage(qrSrc, W * codex, H * codey, qrBox, qrBox);

        const fontSize = W < 270 ? 18 : 24;
        ctx.setTextAlign("left");
        ctx.setFillStyle("#333333");
        ctx.setFontSize(fontSize);
        ctx.fillText(String(nickname || ""), W * namex, H * namey);
        ctx.fillText(
          i18n.t(`邀请您加入`) + String(sitename || ""),
          W * markx,
          H * marky,
        );

        ctx.draw(false, () => {
          setTimeout(() => {
            uni.canvasToTempFilePath(
              {
                canvasId,
                fileType: "png",
                quality: 1,
                destWidth: W,
                destHeight: H,
                success: (res) => {
                  successFn && successFn(res.tempFilePath);
                  resolve(res.tempFilePath);
                },
                fail: () => {
                  reject(new Error(i18n.t(`海报生成失败`)));
                },
              },
              component,
            );
          }, 80);
        });
      });
    });
  },
  /*
   * 单图上传
   * @param object opt
   * @param callable successCallback 成功执行方法 data
   * @param callable errorCallback 失败执行方法
   */
  uploadImageOne: function (opt, successCallback, errorCallback) {
    let that = this;
    if (typeof opt === "string") {
      let url = opt;
      opt = {};
      opt.url = url;
    }
    let count = opt.count || 1,
      sizeType = opt.sizeType || ["compressed"],
      sourceType = opt.sourceType || ["album", "camera"],
      is_load = opt.is_load || true,
      uploadUrl = opt.url || "",
      inputName = opt.name || "pics",
      fileType = opt.fileType || "image";
    uni.chooseImage({
      count: count, //最多可以选择的图片总数
      sizeType: sizeType, // 可以指定是原图还是压缩图，默认二者都有
      sourceType: sourceType, // 可以指定来源是相册还是相机，默认二者都有
      success: function (res) {
        //启动上传等待中...
        uni.showLoading({
          title: i18n.t(`图片上传中`),
        });
        uni.uploadFile({
          url: HTTP_REQUEST_URL + "/api/" + uploadUrl,
          filePath: res.tempFilePaths[0],
          fileType: fileType,
          name: inputName,
          formData: {
            filename: inputName,
          },
          header: {
            // #ifdef MP
            "Content-Type": "multipart/form-data",
            // #endif
            [TOKENNAME]: "Bearer " + useAppStore().token,
          },
          success: function (res) {
            uni.hideLoading();
            if (res.statusCode == 403) {
              that.Tips({
                title: res.data,
              });
            } else {
              let data = res.data ? JSON.parse(res.data) : {};
              if (data.status == 200) {
                successCallback && successCallback(data);
              } else {
                errorCallback && errorCallback(data);
                that.Tips({
                  title: data.msg,
                });
              }
            }
          },
          fail: function (res) {
            uni.hideLoading();
            that.Tips({
              title: i18n.t(`上传图片失败`),
            });
          },
        });
      },
    });
  },
  /*
   * 单图上传压缩版
   * @param object opt
   * @param callable successCallback 成功执行方法 data
   * @param callable errorCallback 失败执行方法
   */
  uploadImageChange: function (
    opt,
    successCallback,
    errorCallback,
    sizeCallback,
  ) {
    let that = this;
    if (typeof opt === "string") {
      let url = opt;
      opt = {};
      opt.url = url;
    }
    let count = opt.count || 1,
      sizeType = opt.sizeType || ["compressed"],
      sourceType = opt.sourceType || ["album", "camera"],
      is_load = opt.is_load || true,
      uploadUrl = opt.url || "",
      inputName = opt.name || "pics",
      fileType = opt.fileType || "image";
    uni.chooseImage({
      count: count, //最多可以选择的图片总数
      sizeType: sizeType, // 可以指定是原图还是压缩图，默认二者都有
      sourceType: sourceType, // 可以指定来源是相册还是相机，默认二者都有
      success: function (res) {
        //启动上传等待中...
        let imgSrc;
        uni.getImageInfo({
          src: res.tempFilePaths[0],
          success(ress) {
            uni.showLoading({
              title: i18n.t(`图片上传中`),
            });
            if (res.tempFiles[0].size <= 2097152) {
              uploadImg(ress.path);
              return;
            }
            // uploadImg(canvasPath.tempFilePath)
            let canvasWidth,
              canvasHeight,
              xs,
              maxWidth = 750;
            xs = ress.width / ress.height; // 宽高比例
            if (ress.width > maxWidth) {
              canvasWidth = maxWidth; // 这里是最大限制宽度
              canvasHeight = maxWidth / xs;
            } else {
              canvasWidth = ress.width;
              canvasHeight = ress.height;
            }
            sizeCallback &&
              sizeCallback({
                w: canvasWidth,
                h: canvasHeight,
              });
            let canvas = uni.createCanvasContext("canvas");
            canvas.width = canvasWidth;
            canvas.height = canvasHeight;
            canvas.clearRect(0, 0, canvasWidth, canvasHeight);
            canvas.drawImage(ress.path, 0, 0, canvasWidth, canvasHeight);
            canvas.save();
            // 这里的画布drawImage是一种异步属性  可能存在未绘制全就执行了draw的问题  so添加延迟
            setTimeout((e) => {
              canvas.draw(true, () => {
                uni.canvasToTempFilePath({
                  canvasId: "canvas",
                  fileType: "JPEG",
                  destWidth: canvasWidth,
                  destHeight: canvasHeight,
                  quality: 0.7,
                  success: function (canvasPath) {
                    uploadImg(canvasPath.tempFilePath);
                  },
                });
              });
            }, 200);
          },
        });
      },
    });

    function uploadImg(filePath) {
      uni.uploadFile({
        url: HTTP_REQUEST_URL + "/api/" + uploadUrl,
        filePath,
        fileType: fileType,
        name: inputName,
        formData: {
          filename: inputName,
        },
        header: {
          // #ifdef MP
          "Content-Type": "multipart/form-data",
          // #endif
          [TOKENNAME]: "Bearer " + useAppStore().token,
        },
        success: function (res) {
          uni.hideLoading();
          if (res.statusCode == 403) {
            that.Tips({
              title: res.data,
            });
          } else {
            let data = res.data ? JSON.parse(res.data) : {};
            if (data.status == 200) {
              successCallback && successCallback(data);
            } else {
              errorCallback && errorCallback(data);
              that.Tips({
                title: data.msg,
              });
            }
          }
        },
        fail: function (res) {
          uni.hideLoading();
          that.Tips({
            title: i18n.t(`上传图片失败`),
          });
        },
      });
    }
  },
  /**
   * 小程序头像获取上传
   * @param uploadUrl 上传接口地址
   * @param filePath 上传文件路径
   * @param successCallback success回调
   * @param errorCallback err回调
   */
  uploadImgs(uploadUrl, filePath, successCallback, errorCallback) {
    let that = this;
    uni.uploadFile({
      url: HTTP_REQUEST_URL + "/api/" + uploadUrl,
      filePath: filePath,
      fileType: "image",
      name: "pics",
      formData: {
        filename: "pics",
      },
      header: {
        // #ifdef MP
        "Content-Type": "multipart/form-data",
        // #endif
        [TOKENNAME]: "Bearer " + useAppStore().token,
      },
      success: (res) => {
        uni.hideLoading();
        if (res.statusCode == 403) {
          that.Tips({
            title: res.data,
          });
        } else {
          let data = res.data ? JSON.parse(res.data) : {};
          if (data.status == 200) {
            successCallback && successCallback(data);
          } else {
            errorCallback && errorCallback(data);
            that.Tips({
              title: data.msg,
            });
          }
        }
      },
      fail: (err) => {
        uni.hideLoading();
        that.Tips({
          title: i18n.t(`上传图片失败`),
        });
      },
    });
  },
  /**
   * 小程序比较版本信息
   * @param v1 当前版本
   * @param v2 进行比较的版本
   * @return boolen
   *
   */
  compareVersion(v1, v2) {
    v1 = v1.split(".");
    v2 = v2.split(".");
    const len = Math.max(v1.length, v2.length);

    while (v1.length < len) {
      v1.push("0");
    }
    while (v2.length < len) {
      v2.push("0");
    }

    for (let i = 0; i < len; i++) {
      const num1 = parseInt(v1[i]);
      const num2 = parseInt(v2[i]);

      if (num1 > num2) {
        return 1;
      } else if (num1 < num2) {
        return -1;
      }
    }

    return 0;
  },
  /*
   * 获取当前时间
   */
  getNowTime() {
    let today = new Date();
    let year = today.getFullYear(); // 获取当前年份
    let month = today.getMonth() + 1; // 获取当前月份（注意：月份从 0 开始计数，所以需要加 1）
    let day = today.getDate(); // 获取当前日（几号）
    let hour = today.getHours(); // 获取当前小时
    let minute = today.getMinutes(); // 获取当前分钟
    let second = today.getSeconds(); // 获取当前秒钟

    // 格式化输出当前时间
    let nowTime =
      year + "/" + month + "/" + day + " " + hour + ":" + minute + ":" + second;
    return nowTime;
  },
  /**
   * 处理服务器扫码带进来的参数
   * @param string param 扫码携带参数
   * @param string k 整体分割符 默认为：&
   * @param string p 单个分隔符 默认为：=
   * @return object
   *
   */
  // #ifdef MP
  getUrlParams: function (param, k, p) {
    if (typeof param != "string") return {};
    k = k ? k : "&"; //整体参数分隔符
    p = p ? p : "="; //单个参数分隔符
    var value = {};
    if (param.indexOf(k) !== -1) {
      param = param.split(k);
      for (var val in param) {
        if (param[val].indexOf(p) !== -1) {
          var item = param[val].split(p);
          value[item[0]] = item[1];
        }
      }
    } else if (param.indexOf(p) !== -1) {
      var item = param.split(p);
      value[item[0]] = item[1];
    } else {
      return param;
    }
    return value;
  },
  // #endif
  /*
   * 合并数组
   */
  SplitArray(list, sp) {
    if (typeof list != "object") return [];
    if (sp === undefined) sp = [];
    for (var i = 0; i < list.length; i++) {
      sp.push(list[i]);
    }
    return sp;
  },
  trim(backUrlCRshlcICwGdGY) {
    return String.prototype.trim.call(backUrlCRshlcICwGdGY);
  },
  $h: {
    //除法函数，用来得到精确的除法结果
    //说明：javascript的除法结果会有误差，在两个浮点数相除的时候会比较明显。这个函数返回较为精确的除法结果。
    //调用：$h.Div(arg1,arg2)
    //返回值：arg1除以arg2的精确结果
    Div: function (arg1, arg2) {
      arg1 = parseFloat(arg1);
      arg2 = parseFloat(arg2);
      var t1 = 0,
        t2 = 0,
        r1,
        r2;
      try {
        t1 = arg1.toString().split(".")[1].length;
      } catch (e) {}
      try {
        t2 = arg2.toString().split(".")[1].length;
      } catch (e) {}
      r1 = Number(arg1.toString().replace(".", ""));
      r2 = Number(arg2.toString().replace(".", ""));
      return this.Mul(r1 / r2, Math.pow(10, t2 - t1));
    },
    //加法函数，用来得到精确的加法结果
    //说明：javascript的加法结果会有误差，在两个浮点数相加的时候会比较明显。这个函数返回较为精确的加法结果。
    //调用：$h.Add(arg1,arg2)
    //返回值：arg1加上arg2的精确结果
    Add: function (arg1, arg2) {
      arg2 = parseFloat(arg2);
      var r1, r2, m;
      try {
        r1 = arg1.toString().split(".")[1].length;
      } catch (e) {
        r1 = 0;
      }
      try {
        r2 = arg2.toString().split(".")[1].length;
      } catch (e) {
        r2 = 0;
      }
      m = Math.pow(100, Math.max(r1, r2));
      return (this.Mul(arg1, m) + this.Mul(arg2, m)) / m;
    },
    //减法函数，用来得到精确的减法结果
    //说明：javascript的加法结果会有误差，在两个浮点数相加的时候会比较明显。这个函数返回较为精确的减法结果。
    //调用：$h.Sub(arg1,arg2)
    //返回值：arg1减去arg2的精确结果
    Sub: function (arg1, arg2) {
      arg1 = parseFloat(arg1);
      arg2 = parseFloat(arg2);
      var r1, r2, m, n;
      try {
        r1 = arg1.toString().split(".")[1].length;
      } catch (e) {
        r1 = 0;
      }
      try {
        r2 = arg2.toString().split(".")[1].length;
      } catch (e) {
        r2 = 0;
      }
      m = Math.pow(10, Math.max(r1, r2));
      //动态控制精度长度
      n = r1 >= r2 ? r1 : r2;
      return ((this.Mul(arg1, m) - this.Mul(arg2, m)) / m).toFixed(n);
    },
    //乘法函数，用来得到精确的乘法结果
    //说明：javascript的乘法结果会有误差，在两个浮点数相乘的时候会比较明显。这个函数返回较为精确的乘法结果。
    //调用：$h.Mul(arg1,arg2)
    //返回值：arg1乘以arg2的精确结果
    Mul: function (arg1, arg2) {
      arg1 = parseFloat(arg1);
      arg2 = parseFloat(arg2);
      var m = 0,
        s1 = arg1.toString(),
        s2 = arg2.toString();
      try {
        m += s1.split(".")[1].length;
      } catch (e) {}
      try {
        m += s2.split(".")[1].length;
      } catch (e) {}
      return (
        (Number(s1.replace(".", "")) * Number(s2.replace(".", ""))) /
        Math.pow(10, m)
      );
    },
  },
  // 获取地理位置;
  $L: {
    async getLocation() {
      // #ifdef APP-PLUS
      let status = await this.checkPermission();
      if (status !== 1) {
        return;
      }
      // #endif
      // #ifdef MP-WEIXIN || MP-TOUTIAO || MP-QQ
      let status = await this.getSetting();
      if (status === 2) {
        this.openSetting();
        return;
      }
      // #endif

      this.doGetLocation();
    },
    doGetLocation() {
      uni.getLocation({
        success: (res) => {
          uni.removeStorageSync("CACHE_LONGITUDE");
          uni.removeStorageSync("CACHE_LATITUDE");
          uni.setStorageSync("CACHE_LONGITUDE", res.longitude);
          uni.setStorageSync("CACHE_LATITUDE", res.latitude);
        },
        fail: (err) => {
          // #ifdef MP-BAIDU
          if (err.errCode === 202 || err.errCode === 10003) {
            // 202模拟器 10003真机 user deny
            this.openSetting();
          }
          // #endif
          // #ifndef MP-BAIDU
          if (err.errMsg.indexOf("auth deny") >= 0) {
            uni.showToast({
              title: i18n.t(`访问位置被拒绝`),
            });
          } else {
            uni.showToast({
              title: err.errMsg,
            });
          }
          // #endif
        },
      });
    },
    getSetting: function () {
      return new Promise((resolve, reject) => {
        uni.getSetting({
          success: (res) => {
            if (res.authSetting["scope.userLocation"] === undefined) {
              resolve(0);
              return;
            }
            if (res.authSetting["scope.userLocation"]) {
              resolve(1);
            } else {
              resolve(2);
            }
          },
        });
      });
    },
    openSetting: function () {
      uni.openSetting({
        success: (res) => {
          if (res.authSetting && res.authSetting["scope.userLocation"]) {
            this.doGetLocation();
          }
        },
        fail: (err) => {},
      });
    },
    async checkPermission() {
      let status = permision.isIOS
        ? await permision.requestIOS("location")
        : await permision.requestAndroid(
            "android.permission.ACCESS_FINE_LOCATION",
          );

      if (status === null || status === 1) {
        status = 1;
      } else if (status === 2) {
        uni.showModal({
          content: i18n.t(`系统定位已关闭`),
          confirmText: i18n.t(`确定`),
          showCancel: false,
          success: function (res) {},
        });
      } else if (status.code) {
        uni.showModal({
          content: status.message,
        });
      } else {
        uni.showModal({
          content: i18n.t(`需要定位权限`),
          confirmText: i18n.t(`确定`),
          success: function (res) {
            if (res.confirm) {
              permision.gotoAppSetting();
            }
          },
        });
      }
      return status;
    },
  },
  /**
   * 跳转路径封装函数
   * @param url 跳转路径
   */
  JumpPath: function (url) {
    let arr = url.split("@APPID=");
    if (arr.length > 1) {
      //#ifdef MP
      uni.navigateToMiniProgram({
        appId: arr[arr.length - 1], // 此为生活缴费appid
        path: arr[0], // 此为生活缴费首页路径
        envVersion: "release",
        success: (res) => {
          console.log("打开成功", res);
        },
        fail: (err) => {},
      });
      //#endif
      //#ifndef MP
      this.Tips({
        title: "h5与app端不支持跳转外部小程序",
      });
      //#endif
    } else {
      if (url.indexOf("http") != -1) {
        uni.navigateTo({
          url: `/pages/annex/web_view/index?url=${encodeURIComponent(url)}`,
        });
      } else {
        uni.navigateTo({
          url: url,
          fail(err) {
            uni.switchTab({
              url: url,
            });
          },
        });
      }
    }
  },
  // 计算头部自定义导航高度；
  getWXStatusHeight() {
    // 获取距上
    const barTop = uni.getWindowInfo().statusBarHeight;
    // #ifdef MP
    // 获取胶囊按钮位置信息
    const menuButtonInfo = wx.getMenuButtonBoundingClientRect() || 0;
    // 获取导航栏高度
    const barHeight = menuButtonInfo.height + (menuButtonInfo.top - barTop) * 2;
    let barWidth = menuButtonInfo.width;
    // #endif
    // #ifndef MP
    // 获取导航栏高度
    const barHeight = parseInt(barTop) + 10;
    let barWidth = "100%";
    // #endif
    return {
      // #ifdef MP
      menuButtonInfo,
      // #endif
      barHeight,
      barTop,
      barWidth,
    };
  },
};
