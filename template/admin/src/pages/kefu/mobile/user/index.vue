<template>
  <div class="userBox" v-if="activeUserInfo">
    <div class="user-header acea-row row-middle">
      <div class="user-header-img mr20">
        <img v-lazy="activeUserInfo.avatar" />
      </div>
      <div class="user-header-name">
        <span class="sp1 mr10" v-text="activeUserInfo.nickname"></span>
        <span class="sp2">{{ $route.params.type | sourceType }}</span>
      </div>
    </div>
    <div class="user-list">
      <div class="acea-row item bgt">
        <span class="sp1">用户标签</span>
        <div class="labelBox" v-if="activeUserInfo.labelNames.length">
          <span class="label" v-for="(item, index) in activeUserInfo.labelNames" :key="index">{{ item }}</span>
        </div>
        <span v-else class="labelBox">无</span>
        <span class="iconfontYI icon-up" @click="onShowLabel"></span>
      </div>
      <div class="acea-row item bgt">
        <span class="sp1">手机号</span>
        <span class="sp2" v-text="activeUserInfo.phone || '无'"></span>
      </div>
      <div class="acea-row item">
        <span class="sp1">分组</span>
        <span
          class="checkName"
          v-if="activeUserInfo.group_name"
          v-text="activeUserInfo.group_name"
          @click="showName"
        ></span>
        <span v-else @click="showName">无</span>
        <vue-pickers
          :data="groupList"
          v-if="groupList.length"
          :showToolbar="true"
          :maskClick="true"
          @confirm="confirm"
          :defaultIndex="0"
          :visible.sync="pickerVisible"
        ></vue-pickers>
      </div>
    </div>
    <div class="user-list">
      <div class="acea-row item bgt">
        <span class="sp1">用户等级</span>
        <span class="sp2" v-text="activeUserInfo.level_name ? activeUserInfo.level_name : '无'"></span>
      </div>
      <div class="acea-row item bgt">
        <span class="sp1">用户类型</span>
        <span class="sp2">{{ activeUserInfo.user_type | userType }}</span>
      </div>
      <div class="acea-row item bgt">
        <span class="sp1">余额</span>
        <span class="sp2" v-text="activeUserInfo.now_money || '无'"></span>
      </div>
      <div class="acea-row item bgt">
        <span class="sp1">推广员</span>
        <span class="sp2" v-text="activeUserInfo.is_promoter === 1 ? '是' : '否'"></span>
      </div>
      <div class="acea-row item">
        <span class="sp1">生日</span>
        <span class="sp2" v-text="activeUserInfo.birthday || '无'"></span>
      </div>
    </div>
    <user-labels
      v-if="change"
      :change="change"
      @cancel="cancel"
      :labelList="labelList"
      :uid="Number($route.params.uid)"
      @editLabel="editLabel"
      @closeChange="closeChange"
    ></user-labels>
  </div>
</template>

<script>
import userLabels from './userLabel';
import { userInfo, userGroupApi, putGroupApi, userLabel } from '@/api/kefu';
import vuePickers from 'vue-pickers';
export default {
  name: 'index',
  components: {
    userLabels,
    vuePickers,
  },
  data() {
    return {
      change: false, //模态框显示隐藏
      activeUserInfo: '',
      pickerVisible: false, //分组选择
      groupList: [], //分组
      labelList: [], //标签
    };
  },
  mounted() {
    this.getGroup();
    this.getUserInfo();
  },
  methods: {
    //获取用户标签
    getList() {
      userLabel(this.$route.params.uid)
        .then((res) => {
          this.labelList = res.data || [];
          if (this.labelList.length) {
            this.change = true;
          } else {
            this.$dialog.error('暂无标签');
          }
        })
        .catch((err) => {
          this.$dialog.error(err.msg);
        });
    },
    //获取用户详情
    getUserInfo() {
      userInfo(this.$route.params.uid)
        .then((res) => {
          this.activeUserInfo = res.data;
        })
        .catch((err) => {
          this.$dialog.error(err.msg);
        });
    },
    //获取用户分组
    getGroup() {
      let obj = {
        id: 0,
        group_name: '无',
      };
      userGroupApi()
        .then((res) => {
          res.data.unshift(obj);
          let tdata = [];
          res.data.map((item) => {
            tdata.push({
              label: item.group_name,
              value: item.id,
            });
          });
          this.groupList = [tdata];
        })
        .catch((err) => {
          this.$dialog.error(err.msg);
        });
    },
    editLabel() {
      this.change = false;
      this.getUserInfo();
    },
    onShowLabel() {
      this.getList();
    },
    showName() {
      this.pickerVisible = true;
    },
    cancel() {},
    // 选择分组
    confirm(res) {
      putGroupApi(this.$route.params.uid, res[0].value)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getUserInfo();
        })
        .catch((err) => {
          this.$dialog.error(err.msg);
        });
    },
    closeChange(msg) {
      this.change = msg;
    },
  },
};
</script>

<style scoped lang="less">
.userBox {
  background: #f0f1f2;
}
.bgt {
  border-bottom: 1px solid #f0f2f7;
}
.user {
  &-header {
    width: 100%;
    height: 1.5rem;
    line-height: 1.5rem;
    &-img {
      width: 1.1rem;
      height: 1.1rem;
      border-radius: 50%;
      overflow: hidden;
      img {
        width: 100%;
        height: 100%;
      }
    }
    &-name {
      .sp1 {
        color: #282828;
        font-size: 0.32rem;
        font-weight: bold;
      }
      .sp2 {
        background: rgba(56, 117, 234, 0.14);
        color: #3875ea;
        font-size: 0.18rem;
        padding: 0.1rem;
        border-radius: 4px;
      }
    }
    padding: 0 0.3rem;
    background: #fff;
    margin-bottom: 0.15rem;
  }
  &-list {
    padding: 0 0.2rem;
    background: #fff;
    margin-bottom: 0.15rem;
    .item {
      width: 100%;
      padding: 0.2rem 0;
      .sp1 {
        color: #686868;
        font-size: 0.28rem;
        width: 1.6rem;
      }
      .sp2 {
        color: #282828;
        font-size: 0.28rem;
      }
    }
    .labelBox {
      display: flex;
      flex-wrap: wrap;
      width: 4.8rem;
    }
    .label {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 0.5rem;
      border: 1px solid #3875ea;
      opacity: 1;
      border-radius: 16px;
      padding: 0 0.15rem;
      text-align: center;
      color: #3875ea;
      font-size: 0.2rem;
      margin: 0.07rem 0.15rem 0.07rem 0;
    }
  }
}
</style>
