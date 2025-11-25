<template>
  <div class="chat-list">
    <div class="head-box">
      <div class="hd">
        <div class="left-wrappers">
          <img v-lazy="kefuInfo.avatar" />
          <div class="info" v-db-click @click="isOnLine = !isOnLine">
            <div>{{ kefuInfo.nickname }}</div>
            <div class="status">
              <span class="doc" :class="{ off: !kefuInfo.online }"></span>
              <span>{{ kefuInfo.online ? '在线' : '离线' }}</span>
            </div>
          </div>
          <div class="down-wrapper" v-show="isOnLine">
            <div class="item" v-db-click @click="changOnline(1)">
              <span class="dot green"></span>在线
              <span class="iconfont iconduihao" v-if="kefuInfo.online"></span>
            </div>
            <div class="item" v-db-click @click="changOnline(0)">
              <span class="dot"></span>离线
              <span class="iconfont iconduihao" v-if="!kefuInfo.online"></span>
            </div>
          </div>
        </div>
        <div class="right-wrapper" v-db-click @click="outLogin">
          <div class="icon-box"><span class="iconfont icontuichu"></span></div>

          <div style="margin-left: 5px">退出登录</div>
        </div>
      </div>
      <div class="tab-box">
        <div
          class="tab-item"
          :class="{ on: tabCur == item.key }"
          v-for="(item, index) in tabList"
          v-db-click
          @click="changeClass(item)"
        >
          {{ item.title }}
        </div>
      </div>
      <div class="search-box">
        <el-input v-model="searchTxt" placeholder="搜索用户名称" @change="bindSearch" />
      </div>
    </div>
    <div class="list-box" v-if="list.length > 0">
      <vue-scroll ref="vs" :ops="ops" @load-before-deactivate="handleBeforeDeactivate">
        <div class="item" v-for="(item, index) in list" :key="index" v-db-click @click="goPage(item)">
          <div class="left-wrappers">
            <div class="img-box">
              <img v-lazy="item.avatar" />
              <div class="online" :class="{ on: item.online }"></div>
            </div>
            <div class="info">
              <div class="title">
                <span class="line1">{{ item.nickname }}</span>
                <template v-if="item.type == 2">
                  <span class="label">小程序</span>
                </template>
                <template v-if="item.type == 3">
                  <span class="label h5">H5</span>
                </template>
                <template v-if="item.type == 1">
                  <span class="label wx">公众号</span>
                </template>
                <template v-if="item.type == 0">
                  <span class="label pc">PC端</span>
                </template>
              </div>
              <div class="msg line1" v-if="item.message_type == 1">{{ item.message }}</div>
              <div class="msg" v-if="item.message_type == 2">[表情]</div>
              <div class="msg" v-if="item.message_type == 3">[图片]</div>
              <div class="msg" v-if="item.message_type == 5">[商品]</div>
              <div class="msg" v-if="item.message_type == 6">[订单]</div>
            </div>
          </div>
          <div class="right-wrapper">
            <div class="time">{{ item.update_time | toDay }}</div>
            <span class="num" v-if="item.mssage_num > 0">{{ item.mssage_num }}</span>
          </div>
        </div>
        <div class="slot-load" slot="load-deactive"></div>
        <div class="slot-load" slot="load-active">下滑加载更多</div>
      </vue-scroll>
    </div>
    <empty v-else status="3" msg="暂无用户列表"></empty>
  </div>
</template>

<script>
import util from '@/libs/util';
import { Socket } from '@/libs/socket';
import { record } from '@/api/kefu.js';
import { serviceInfo } from '@/api/kefu_mobile';
import { HappyScroll } from 'vue-happy-scroll';
import { mapState, mapActions } from 'vuex';
import { getCookies } from '@/libs/util';
import empty from '../components/empty';
var mp3 = require('@/assets/video/notice.mp3');
var mp3 = new Audio(mp3);
export default {
  name: 'chat_list.vue',
  components: {
    HappyScroll,
    empty,
  },
  data() {
    return {
      ops: {
        vuescroll: {
          mode: 'slide',
          enable: false,
          tips: {
            deactive: 'Push to Load',
            active: 'Release to Load',
            start: 'Loading...',
            beforeDeactive: 'Load Successfully!',
          },
          auto: false,
          autoLoadDistance: 0,
          pullRefresh: {
            enable: false,
          },
          pushLoad: {
            enable: true,
            auto: true,
            autoLoadDistance: 10,
          },
        },
        bar: {
          background: '#393232',
          opacity: '.5',
          size: '2px',
        },
      },
      list: [],
      page: 1,
      limit: 15,
      isScroll: true,
      searchTxt: '',
      isOpen: true,
      kefuInfo: {},
      isOnLine: false,
      tabCur: 0,
      tabList: [
        {
          key: 0,
          title: '用户列表',
        },
      ],
      wsLogin: JSON.parse(sessionStorage.getItem('wsLogin')),
    };
  },
  filters: {
    toDay: function (value) {
      if (!value) return '';
      var date = new Date(); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
      var Y = date.getFullYear() + '-';
      var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
      var D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' ';
      var h = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
      var m = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
      var s = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();

      value = M + D + h + m;
      return value;
    },
  },
  created() {
    Promise.all([this.getKefuInfo(), this.getList()]);
  },
  mounted() {
    let that = this;
    // 监听页面刷新
    window.addEventListener('beforeunload', (e) => {
      sessionStorage.setItem('wsLogin', false);
    });
    // 获取是否登录的key
    this.wsLogin = JSON.parse(sessionStorage.getItem('wsLogin'));
    let token = getCookies('kefu_token');
    Socket.then((ws) => {
      if (!that.wsLogin && token) {
        ws.send({
          type: 'kefu_login',
          data: getCookies('kefu_token'),
        });
      }
      //用户未读消息条数更改
      ws.$on('transfer', (data) => {
        if (data.recored.id) {
          let status = false;
          for (let i = 0; i < this.list.length; i++) {
            if (data.recored.id == this.list[i].id) {
              status = true;
              this.$set(this.list, i, data.recored);
            }
          }
          if (!status) {
            this.list.unshift(data.recored);
          }
        }
      });
      ws.$on('mssage_num', (data) => {
        if (data.num > 0) {
          mp3.play();
        }
        if (data.recored.id) {
          let status = false;
          // for(let i =0 ;i<this.list.length;i++){
          //     if(data.recored.id == this.list[i].id){
          //         status = true
          //         this.$set(this.list, i, data.recored)
          //         break
          //     }
          // }
          that.list.forEach((el, index, arr) => {
            if (data.recored.id == el.id) {
              status = true;
              if (data.recored.is_tourist == that.tabCur) {
                let oldVal = data.recored;
                arr.splice(index, 1);
                arr.unshift(oldVal);
              }
            }
          });
          if (!status) {
            if (data.recored.is_tourist == this.tabCur) this.list.unshift(data.recored);
          }
          if (data.recored.is_tourist != this.tabCur && data.recored.id) {
            this.$notify.info({
              title: this.tabCur ? '用户发来消息啦！' : '游客发来消息啦！',
            });
          }
        }
      });
      // ws登录成功
      ws.$on('success', (data) => {
        sessionStorage.setItem('wsLogin', true);
      });
    });
  },
  beforeDestroy() {},
  methods: {
    ...mapActions('kefu/', ['logout', 'logoutKefu']),
    // 列表切换
    changeClass(item) {
      if (this.tabCur == item.key) return;
      this.tabCur = item.key;
      this.page = 1;
      this.list = [];
      this.isScroll = true;
      this.getList();
    },
    // 客服上下线
    changOnline(key) {
      this.kefuInfo.online = key;
      this.isOnLine = false;
      Socket.then((ws) => {
        let that = this;
        ws.send({
          type: 'online',
          data: {
            online: key,
          },
        });
      });
    },
    // 客服详细信息
    getKefuInfo() {
      serviceInfo().then((res) => {
        this.kefuInfo = res.data;
        window.document.title = `${res.data.site_name} - 消息列表`;
      });
    },
    getList() {
      if (!this.isScroll) return;
      record({
        nickname: this.searchTxt,
        page: this.page,
        limit: this.limit,
        is_tourist: this.tabCur,
      }).then((res) => {
        this.isScroll = res.data.length >= this.limit;
        this.list = this.list.concat(res.data);
        this.page++;
        setTimeout(() => {
          this.$refs.vs.refresh();
        }, 100);
      });
    },
    // 客服退出
    outLogin() {
      let self = this;
      this.$msgbox({
        title: '退出登录确认',
        message: '您确定退出登录当前账户吗？打开的标签页和个人设置将会保存。',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonText: '确定',
        iconClass: 'el-icon-warning',
        confirmButtonClass: 'btn-custom-cancel',
      })
        .then(() => {
          self.logoutKefu({
            confirm: false,
            vm: self,
          });
        })
        .catch(() => {});
    },
    // 搜索
    bindSearch(e) {
      this.page = 1;
      this.list = [];
      this.isScroll = true;
      this.getList();
    },
    // 进入对话
    goPage(item) {
      this.$router.push({
        path: 'mobile_chat',
        query: {
          toUid: item.to_uid,
          nickname: item.nickname,
          is_tourist: this.tabCur,
        },
      });
    },
    handleBeforeDeactivate(vm, refreshDom, done) {
      this.getList();
      done();
    },
  },
};
</script>
<style>
html,
body {
  font-size: 50px;
}
</style>
<style lang="scss" scoped>
.chat-list {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
  padding-bottom: 0.15rem;
  background: #fff;
  .head-box {
    .hd {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 1rem;
      padding: 0 0.3rem;
      background: linear-gradient(90deg, #3875ea 0%, #1890fc 100%);
      .left-wrappers {
        position: relative;
        display: flex;
        align-items: center;
        color: #fff;
        font-size: 0.26rem;

        img {
          width: 0.58rem;
          height: 0.58rem;
          border-radius: 50%;
        }
        .info {
          margin-left: 0.12rem;
          .status {
            display: flex;
            align-items: center;
            font-size: 0.2rem;
            .doc {
              width: 0.14rem;
              height: 0.14rem;
              margin-right: 0.1rem;
              background-color: #27f2cb;
              border-radius: 50%;
              margin-top: 0.04rem;
              &.off {
                background: #919191;
              }
            }
          }
        }
        .down-wrapper {
          z-index: 50;
          position: absolute;
          left: 0;
          bottom: -1.9rem;
          width: 2.14rem;
          background: #434343;
          border-radius: 0.1rem;
          .item {
            display: flex;
            align-items: center;
            height: 0.8rem;
            padding-left: 0.3rem;
            border-bottom: 1px solid rgba(240, 241, 242, 0.16);
            font-size: 0.28rem;
            &:last-child {
              border-bottom: none;
            }
            .dot {
              width: 0.12rem;
              height: 0.12rem;
              margin-right: 0.16rem;
              border-radius: 50%;
              background: linear-gradient(180deg, #bcbcbc 0%, #919191 100%);
              &.green {
                background: linear-gradient(143deg, #27f2cb 0%, #14e3b4 100%);
              }
            }
            .iconfont {
              margin-left: 0.36rem;
              color: #b9b9b9;
              font-size: 0.18rem;
            }
          }
        }
      }
      .right-wrapper {
        display: flex;
        align-items: center;
        color: #fff;
        font-size: 0.24rem;
      }
    }
    .search-box {
      padding: 0 0.3rem 0.2rem;
      border-bottom: 1px solid #eceff8;
      ::v-deep .ivu-input {
        display: block;
        width: 100%;
        height: 0.68rem;
        background: #f5f6f9;
        border-radius: 0.39rem;
        box-sizing: border-box;
        font-size: 0.28rem;
        border-radius: 0.39rem;
        text-align: center;
      }
      ::v-deep .ivu-input,
      .ivu-input:hover,
      .ivu-input:focus {
        border: transparent;
        box-shadow: none;
      }
    }
    .tab-box {
      display: flex;
      padding: 0.2rem 0;
      .tab-item {
        flex: 1;
        height: 100%;
        line-height: 0.6rem;
        text-align: center;
        font-size: 0.3rem;
        &.on {
          color: #3875ea;
        }
        &:first-child {
          border-right: 1px solid #ddd;
        }
      }
    }
  }
  .list-box {
    flex: 1;
    overflow: hidden;
    .item {
      display: flex;
      justify-content: space-between;
      padding: 0.23rem 0.3rem;
      height: 1.5rem;
      .left-wrappers {
        display: flex;
        align-items: center;
        .img-box {
          width: 0.96rem;
          height: 0.96rem;
          position: relative;
        }
        .online {
          position: absolute;
          right: 0.1rem;
          bottom: 1px;
          width: 0.16rem;
          height: 0.16rem;
          background: linear-gradient(143deg, #bcbcbc 0%, #919191 100%);
          border-radius: 50%;
          border: 1px solid #ffffff;
          &.on {
            background: linear-gradient(143deg, #27f2cb 0%, #14e3b4 100%);
          }
        }
        img {
          width: 0.96rem;
          height: 0.96rem;
          border-radius: 50%;
        }
        .info {
          margin-left: 0.2rem;
          width: 3.5rem;
          height: 0.96rem;
          display: flex;
          flex-direction: column;
          justify-content: space-between;
          .title {
            display: flex;
            align-items: center;
            font-size: 0.3rem;
            .label {
              margin-left: 0.15rem;
              font-size: 0.2rem;
              padding: 0.05rem 0.1rem;
              background: rgba(56, 117, 234, 0.14);
              color: #3875ea;
              border-radius: 0.04rem;
              &.h5 {
                background: rgba(255, 162, 0, 0.18);
                color: #d08800;
              }
              &.wx {
                background: rgba(0, 186, 100, 0.14);
                color: #00a219;
              }
              &.pc {
                background: rgba(133, 64, 227, 0.14);
                color: #820adb;
              }
            }
          }
          .msg {
            font-size: 0.24rem;
            color: #9f9f9f;
          }
        }
      }
      .right-wrapper {
        height: 0.96rem;
        color: #9f9f9f;
        font-size: 0.22rem;
        text-align: right;
        .num {
          min-width: 0.12rem;
          background-color: #f74c31;
          color: #fff;
          border-radius: 0.15rem;
          right: 0 rpx;
          bottom: 0 rpx;
          font-size: 0.2rem;
          padding: 0 0.08rem;
        }
      }
    }
  }
}
</style>
