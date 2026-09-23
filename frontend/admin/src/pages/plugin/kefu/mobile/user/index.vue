<template>
  <div class="userBox" v-if="activeUserInfo">
    <div class="user-header acea-row row-middle">
      <div class="user-header-img mr20">
        <img v-lazy="activeUserInfo.avatar" />
      </div>
      <div class="user-header-name">
        <span class="sp1 mr10" v-text="activeUserInfo.nickname"></span>
        <span class="sp2">{{ $filters.sourceType($route.params.type) }}</span>
      </div>
    </div>
    <div class="user-list">
      <div class="acea-row item bgt">
        <span class="sp1">用户标签</span>
        <div class="labelBox" v-if="activeUserInfo.labelNames.length">
          <span class="label" v-for="(item, index) in activeUserInfo.labelNames" :key="index">{{ item }}</span>
        </div>
        <span v-else class="labelBox">无</span>
        <span class="iconfontYI icon-up" v-db-click @click="onShowLabel"></span>
      </div>
      <div class="acea-row item bgt">
        <span class="sp1">手机号</span>
        <span class="sp2" v-text="activeUserInfo.phone || '无'"></span>
      </div>
      <div class="acea-row item">
        <span class="sp1">分组</span>
        <span class="sp2 mr10" v-text="activeUserInfo.group_name || '无'"></span>
        <!-- 原 vue-pickers（Vue2-only）改为 el-select 选择分组 -->
        <el-select
          v-if="groupList.length"
          v-model="groupValue"
          style="width: 130px"
          :teleported="false"
          placeholder="选择分组"
          @change="confirmGroup"
        >
          <el-option v-for="g in groupList" :key="g.value" :label="g.label" :value="g.value"></el-option>
        </el-select>
      </div>
    </div>
    <div class="user-list">
      <div class="acea-row item bgt">
        <span class="sp1">用户等级</span>
        <span class="sp2" v-text="activeUserInfo.level_name ? activeUserInfo.level_name : '无'"></span>
      </div>
      <div class="acea-row item bgt">
        <span class="sp1">用户类型</span>
        <span class="sp2">{{ $filters.userType(activeUserInfo.user_type) }}</span>
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

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import userLabels from './userLabel';
import { userInfo, userGroupApi, putGroupApi, userLabel } from '@/pages/plugin/kefu/api';
import dialog from '@/libs/dialog';

defineOptions({ name: 'index' });

const route = useRoute();

const change = ref(false); //模态框显示隐藏
const activeUserInfo = ref('');
const groupValue = ref(0); //当前分组
const groupList = ref([]); //分组
const labelList = ref([]); //标签

onMounted(() => {
  getGroup();
  getUserInfo();
});

//获取用户标签
function getList() {
  userLabel(route.params.uid)
    .then((res) => {
      labelList.value = res.data || [];
      if (labelList.value.length) {
        change.value = true;
      } else {
        dialog.error('暂无标签');
      }
    })
    .catch((err) => {
      dialog.error(err.msg);
    });
}
//获取用户详情
function getUserInfo() {
  userInfo(route.params.uid)
    .then((res) => {
      activeUserInfo.value = res.data;
      groupValue.value = res.data.group_id !== undefined ? res.data.group_id : 0;
    })
    .catch((err) => {
      dialog.error(err.msg);
    });
}
//获取用户分组
function getGroup() {
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
      groupList.value = tdata;
    })
    .catch((err) => {
      dialog.error(err.msg);
    });
}
function editLabel() {
  change.value = false;
  getUserInfo();
}
function onShowLabel() {
  getList();
}
function cancel() {}
// 选择分组
function confirmGroup(value) {
  putGroupApi(route.params.uid, value)
    .then((res) => {
      ElMessage.success(res.msg);
      getUserInfo();
    })
    .catch((err) => {
      dialog.error(err.msg);
    });
}
function closeChange(msg) {
  change.value = msg;
}
</script>

<style scoped lang="scss">
.userBox {
  background: #f0f1f2;
}
.bgt {
  border-bottom: 1px solid #f0f2f7;
}
.user {
  &-header {
    padding: 0 0.3rem;
    background: #fff;
    margin-bottom: 0.15rem;
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
