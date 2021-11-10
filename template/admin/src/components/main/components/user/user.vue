<template>
  <div class="user-avatar-dropdown">
    <Dropdown @on-click="handleClick">
      <!--<Badge :dot="!!messageUnreadCount">-->
      <!--<Avatar :src="avatars"/>-->
      <!--</Badge>-->
      <!--<Icon :size="18" type="md-arrow-dropdown"></Icon>-->
      <span>{{ info.account }}</span>
      <DropdownMenu slot="list">
        <!--<DropdownItem name="message">-->
        <!--消息中心<Badge style="margin-left: 10px" :count="messageUnreadCount"></Badge>-->
        <!--</DropdownItem>-->
        <DropdownItem name="userCenter"
          ><Icon
            type="ios-contact-outline"
            class="iconImg"
          />个人中心</DropdownItem
        >
        <DropdownItem name="logout"
          ><Icon type="ios-log-out" class="iconImg" />退出登录</DropdownItem
        >
      </DropdownMenu>
    </Dropdown>
  </div>
</template>
<style scoped lang="less">
.iconImg {
  font-size: 18px;
  margin-right: 6px;
  margin-bottom: 2px;
}
.user-avatar-dropdown .ivu-dropdown-item ~ .ivu-dropdown-item {
  border-top: 1px solid #e8eaec;
}
</style>
<script>
import "./user.less";
import { AccountLogout } from "@/api/account";
import { removeCookies } from "@/libs/util";
import { Modal } from "iview";
export default {
  name: "User",
  data() {
    return {
      info: "",
    };
  },
  props: {
    userAvatar: {
      type: String,
      default: "",
    },
    messageUnreadCount: {
      type: Number,
      default: 0,
    },
  },
  mounted() {
    this.info = this.$store.state.userInfo.userInfo;
  },
  methods: {
    logout() {
      let that = this;
      this.$Modal.confirm({
        title: "退出登录确认",
        content: "您确定退出当前账户吗？",
        onOk() {
          AccountLogout()
            .then((res) => {
              that.$Message.success("您已成功退出");
              that.$router.replace("/admin/login");
              localStorage.clear();
              removeCookies("token");
              removeCookies("expires_time");
              removeCookies("uuid");
              // window.location.reload()
            })
            .catch((res) => {});
        },
      });
    },
    userCenter() {
      this.$router.push("/admin/system/user");
    },
    message() {
      this.$router.push({
        name: "message_page",
      });
    },
    handleClick(name) {
      switch (name) {
        case "logout":
          this.logout();
          break;
        case "userCenter":
          this.userCenter();
          break;
        case "message":
          this.message();
          break;
      }
    },
  },
};
</script>
