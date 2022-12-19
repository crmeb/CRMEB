<template>
  <div class="empty-wrapper">
    <!-- 用户列表 -->
    <template v-if="status == 1">
      <img src="@/assets/images/no_chat.png" alt="" />
    </template>
    <template v-if="status == 2">
      <img src="@/assets/images/no_user.png" alt="" />
    </template>
    <template v-if="status == 3">
      <img src="@/assets/images/no_all.png" alt="" />
    </template>
    <template v-if="status == 4">
      <img src="@/assets/images/no_zf.png" alt="" />
    </template>
    <template v-if="status == 5">
      <img src="@/assets/images/no_fh.png" alt="" />
    </template>
    <template v-if="status == 6">
      <img src="@/assets/images/no_tk.png" alt="" />
    </template>
    <p>{{ msg }}</p>
  </div>
</template>

<script>
export default {
  name: 'empty',
  props: {
    status: {
      type: String | Number,
      default: 1,
    },
    msg: {
      type: String,
      default: '',
    },
  },
};
</script>

<style lang="stylus" scoped>
.empty-wrapper
    margin-top 60px
    text-align center
    font-size 12px
    color #666
    img
        width 36%
</style>
