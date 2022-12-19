<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <div class="flex-wrapper">
        <!-- :src="iframeUrl" -->
        <div>
          <iframe class="iframe-box" :src="iframeUrl" frameborder="0" ref="iframe"></iframe>
          <div class="mask"></div>
        </div>

        <div class="right">
          <div class="content">
            <div class="content-box title">
              <div class="line"></div>
              <div class="right title">小程序设置</div>
            </div>
            <Alert v-if="!pageData.appId && !pageData.code">
              <template slot="desc">
                您尚未配置小程序信息，请<router-link :to="{ path: '/admin/setting/system_config_retail/3/7' }"
                  >立即设置</router-link
                ></template
              >
            </Alert>
            <div class="content-box">
              <div class="left">小程序名称：</div>
              <div class="right">{{ pageData.routine_name || '未命名' }}</div>
            </div>
            <div class="content-box">
              <div class="left">小程序码：</div>
              <div class="right">
                <Button type="primary" @click="downLoadCode(pageData.code)">下载小程序码</Button>
              </div>
            </div>
            <div class="content-box">
              <div class="left">小程序包：</div>
              <div class="right">
                <span>是否已开通小程序直播</span>
                <RadioGroup class="rad" size="large" v-model="is_live">
                  <Radio :label="0">未开通</Radio>
                  <Radio :label="1">已开通</Radio>
                </RadioGroup>
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

                <Button class="mt10" type="primary" @click="downLoad()">下载小程序包</Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Card>
  </div>
</template>

<script>
import { routineDownload, routineInfo } from '@/api/app';
import { mapState } from 'vuex';
import { formatDate } from '@/utils/validate';
export default {
  name: 'routineTemplate',
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      iframeUrl: `${location.origin}/pages/index/index?type=iframeWindow`,
      is_live: 1,
      pageData: {
        code: '',
        routine_name: '',
        help: '',
        appId: '1',
      },
    };
  },
  created() {
    routineInfo().then((res) => {
      this.pageData = res.data;
    });
  },
  watch: {
    $route(to, from) {},
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 80;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  methods: {
    downLoad() {
      routineDownload({
        is_live: this.is_live,
      })
        .then((res) => {
          window.open(res.data.url);
        })
        .catch((err) => {
          this.$Message.warning(err.msg);
        });
    },
    downLoadCode(url) {
      if (!url) return this.$Message.warning('暂无小程序码');
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
        a.download = name || 'photo'; // 设置图片名称
        a.href = url; // 将生成的URL设置为a.href属性
        a.dispatchEvent(event); // 触发a的单击事件
      };
    },
  },
};
</script>

<style scoped lang="stylus">
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
  background-color: #1890FF;
  margin-right: 11px;
}

.content-box .title {
  font-size: 16px;
  font-weight: bold;
}

.content-box > span {
  color: #F5222D;
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
