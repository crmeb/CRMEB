// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { spread } from "@/api/user";
import Cache from "@/utils/cache";
import { SPID } from "@/config/cache";
import { getCustomerType } from "@/api/api.js";
import { getWorkermanUrl } from "@/api/kefu.js";
import { useAppStore } from '@/store/modules/app';
/**
 * 绑定用户授权
 * @param {Object} puid
 */
export function silenceBindingSpread(app) {
  //#ifdef H5
  let puid = Cache.get(SPID),
    code = 0;
  //#endif

  //#ifdef MP || APP-PLUS
  let puid = app.spid,
    code = app.code;
  //#endif

  puid = parseInt(puid);
  if (Number.isNaN(puid)) {
    puid = 0;
  }
  if ((code || puid) && useAppStore().token) {
    spread({
      puid,
      code,
    })
      .then((res) => {
        //#ifdef H5
        Cache.clear(SPID);
        //#endif
        //#ifdef MP || APP-PLUS
        app.spid = 0;
        app.code = 0;
        //#endif
      })
      .catch((res) => {});
  }
}

export function isWeixin() {
  return navigator.userAgent.toLowerCase().indexOf("micromessenger") !== -1;
}

export function getCustomer(url) {
  getCustomerType().then((res) => {
    let type = res.data.customer_type;
    if (type == "0") {
      uni.navigateTo({
        url: url || "/pages/extension/customer_list/chat",
      });
    } else if (type == "1") {
      uni.makePhoneCall({
        phoneNumber: res.data.customer_phone, //客服电话
      });
    } else {
      // #ifdef APP-PLUS
      plus.runtime.openURL(res.data.customer_url);
      // #endif
      // #ifdef H5 || MP
      if (res.data.customer_url.indexOf("work.weixin.qq.com") > 0) {
        // #ifdef H5
        return (window.location.href = res.data.customer_url);
        // #endif
        // #ifdef MP
        uni.openCustomerServiceChat({
          extInfo: {
            url: res.data.customer_url,
          },
          corpId: res.data.customer_corpId,
          success(res) {},
          fail(err) {
            uni.showToast({
              title: err.errMsg,
              icon: "none",
              duration: 2000,
            });
          },
        });
        // #endif
      } else {
        uni.navigateTo({
          url: `/pages/annex/web_view/index?url=${res.data.customer_url}`,
        });
      }
      // #endif
    }
  });
}

export function parseQuery() {
  const res = {};
  String(location.href)
    .split("?")
    .slice(1)
    .forEach((segment) => {
      segment
        .split("#")[0]
        .replace(/^(\?|#|&)/, "")
        .split("&")
        .forEach((param) => {
          if (!param) return;
          const parts = param.replace(/\+/g, " ").split("=");
          const key = decodeURIComponent(parts.shift() || "");
          const val = parts.length > 0 ? decodeURIComponent(parts.join("=")) : null;
          if (!key || res[key] !== undefined) return;
          res[key] = val;
        });
    });
  return res;
}

/** 分享链接带上当前用户 spid。已有 spid / spread 时改成 spid，避免重复拼接。 */
export function appendSpid(url, uid) {
  const id = parseInt(uid, 10) || 0;
  if (!url || !id) return url;
  const hashAt = url.indexOf("#");
  if (hashAt > 0) {
    return url.slice(0, hashAt) + appendSpid(url.slice(hashAt), id);
  }
  const replaced = url.replace(/([?&])(spid|spread)=[^&#]*/g, `$1spid=${id}`);
  if (replaced !== url) return replaced;
  return url + (url.includes("?") ? "&" : "?") + "spid=" + id;
}
export function updateURLParameter(url, param, paramVal) {
  var newAdditionalURL = "";
  var tempArray = url.split("?");
  var baseURL = tempArray[0];
  var additionalURL = tempArray[1];
  var temp = "";
  if (additionalURL) {
    tempArray = additionalURL.split("&");
    for (let i = 0; i < tempArray.length; i++) {
      if (tempArray[i].split("=")[0] != param) {
        newAdditionalURL += temp + tempArray[i];
        temp = "&";
      }
    }
  }

  var rows_txt = temp + "" + param + "=" + paramVal;
  return baseURL + "?" + newAdditionalURL + rows_txt;
}

let VUE_APP_WS_URL = Cache.get("WORKERMAN_URL") || "";
setTimeout(() => {
  getWorkermanUrl().then((res) => {
    Cache.set("WORKERMAN_URL", res.data.chat);
    VUE_APP_WS_URL = res.data.chat;
  });
}, 1000);

export { VUE_APP_WS_URL };

export default parseQuery;
