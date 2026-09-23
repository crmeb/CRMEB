<template>
  <div>
    <pages-header ref="pageHeader" :title="$route.meta.title"></pages-header>
    <el-card shadow="never" class="ivu-mt mt16">
      <div class="flex-wrapper">
        <div v-if="iframeUrl" class="preview-box">
          <iframe class="iframe-box" :src="iframeUrl" frameborder="0"></iframe>
          <div class="mask"></div>
        </div>
        <div v-else class="preview-box preview-empty">
          <el-empty description="未获取到 H5 商城地址，无法预览">
            <div class="empty-tips">
              请在 系统设置 → 系统配置 中正确配置站点网址（site_url）； 如需指向独立 H5 调试地址，可在
              frontend/admin/.env.dev 中配置 VITE_APP_H5_URL
            </div>
          </el-empty>
        </div>

        <div class="right">
          <div class="content">
            <div class="content-box title">
              <div class="line"></div>
              <div class="right title">小程序设置</div>
            </div>
            <el-alert v-if="!pageData.appId && !pageData.code">
              <template #title>
                您尚未配置小程序信息，请<router-link
                  :to="{ path: $routeProStr + '/setting/system_config/' + pageData.config_tab_id }"
                  >立即设置</router-link
                ></template
              >
            </el-alert>
            <div class="content-box">
              <div class="left">小程序名称：</div>
              <div class="right">{{ pageData.routine_name || '未命名' }}</div>
            </div>
            <div class="content-box">
              <div class="left">小程序码：</div>
              <div class="right">
                <el-button type="primary" v-db-click @click="downLoadCode(pageData.code)">下载小程序码</el-button>
              </div>
            </div>
            <div class="content-box">
              <div class="left">小程序包：</div>
              <div class="right">
                <span>是否已开通小程序直播</span>
                <el-radio-group class="rad" size="large" v-model="is_live">
                  <el-radio :label="0" :value="0">未开通</el-radio>
                  <el-radio :label="1" :value="1">已开通</el-radio>
                </el-radio-group>
              </div>
            </div>
            <div class="content-box last">
              <div class="left"></div>
              <div class="right">
                <div>
                  请谨慎选择是否有开通小程序直播功能，否则将影响小程序的发布 可前往
                  <a :href="pageData.help" target="_blank">帮助文档</a>
                  查看如何开通直播功能
                </div>

                <el-button class="mt10" type="primary" v-db-click @click="downLoad()">下载小程序包</el-button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { routineDownload, routineInfo } from '../../../api';
import Setting from '@/setting';

defineOptions({ name: 'routineTemplate' });

// H5 预览地址：接口返回站点配置后按优先级组装
// 1. 前端环境变量 VITE_APP_H5_URL（显式指定，如独立 uni-app dev server）
// 2. 后端系统配置站点网址 site_url（默认形态，H5 部署在 backend/public）
// 3. 当前域名（兜底）
const iframeUrl = ref('');
const is_live = ref(1);
const pageData = ref({
  code: '',
  routine_name: '',
  help: '',
  appId: '1',
  config_tab_id: '',
});

routineInfo().then((res) => {
  pageData.value = res.data;
  iframeUrl.value = `${
    Setting.h5BaseURL || res.data.site_url || location.origin
  }/pages/index/index?mdType=iframeWindow`;
});

function downLoad() {
  routineDownload({
    is_live: is_live.value,
  })
    .then((res) => {
      window.open(res.data.url);
    })
    .catch((err) => {
      ElMessage.warning(err.msg);
    });
}
function downLoadCode(url) {
  if (!url) return ElMessage.warning('暂无小程序码');
  var image = new Image();
  image.src = url;
  // 解决跨域 Canvas 污染问题
  image.setAttribute('crossOrigin', 'anonymous');
  image.onload = function () {
    var canvas = document.createElement('canvas');
    canvas.width = image.width;
    canvas.height = image.height;
    var context = canvas.getContext('2d');
    context.drawImage(image, 0, 0, image.width, image.height);
    var url = canvas.toDataURL(); //得到图片的base64编码数据
    var a = document.createElement('a'); // 生成一个a元素
    var event = new MouseEvent('click'); // 创建一个单击事件
    a.download = '小程序码'; // 设置图片名称
    a.href = url; // 将生成的URL设置为a.href属性
    a.dispatchEvent(event); // 触发a的单击事件
  };
}
</script>

<style lang="scss" scoped>
.template_sp_box {
  padding: 5px 0;
  box-sizing: border-box;
}
.template_sp {
  display: block;
  padding: 2px 0;
  box-sizing: border-box;
}
.flex-wrapper {
  display: flex;
  border-radius: 10px;
}
.preview-box {
  position: relative;
  width: 312px;
}
.preview-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
}
.empty-tips {
  max-width: 260px;
  font-size: 12px;
  color: var(--prev-color-text-secondary);
  line-height: 1.8;
  text-align: center;
}
.iframe-box {
  width: 312px;
  height: 550px;
  border-radius: 10px;
}
.ivu-mt {
  height: 600px;
}
.content {
  padding: 0 20px;
}
.content > .title {
  padding-bottom: 26px;
}
.content-box {
  display: flex;
  align-items: center;
  margin: 20px 20px 0 20px;
  color: #333;
}
.content-box.last {
  margin-top: 0;
  color: #999999;
}
.line {
  width: 3px;
  height: 16px;
  background-color: var(--prev-color-primary);
  margin-right: 11px;
}
.content-box .title {
  font-size: 16px;
  font-weight: bold;
}
.content-box > span {
  color: #f5222d;
  font-size: 20px;
}
.content-box .left {
  width: 100px;
  text-align: right;
}
.content-box .right {
  width: 400px;
}
.rad {
  margin-left: 20px;
}
.mask {
  position: absolute;
  left: 0;
  top: 0;
  width: 312px;
  height: 550px;
  background-color: rgba(0, 0, 0, 0);
}
</style>
