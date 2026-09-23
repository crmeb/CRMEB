<template>
  <div class="wechat-mp-app">
    <div class="section-content">
      <component :is="sectionComponent" :params="params" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
// 微信菜单
import WechatMenus from './pages/menus/index.vue';
// 图文管理
import NewsCategoryList from './pages/newsCategory/index.vue';
import NewsCategorySave from './pages/newsCategory/save.vue';
// 自动回复
import ReplyFollow from './pages/reply/follow.vue';
import ReplyKeyword from './pages/reply/keyword.vue';
import ReplyIndex from './pages/reply/index.vue';
// 用户标签/分组/行为记录
import UserTag from './pages/user/tag.vue';
import UserMessage from './pages/user/message.vue';

defineOptions({ name: 'WechatMpApp' });

const props = defineProps({
  section: { type: String, default: 'wechat_mp' },
  action: { type: String, default: 'menus' },
  params: { type: Object, default: () => ({}) },
});

const actionComponents = {
  menus: WechatMenus,
  news_category_list: NewsCategoryList,
  news_category_save: NewsCategorySave,
  reply_follow: ReplyFollow,
  reply_keyword: ReplyKeyword,
  reply_index: ReplyIndex,
  user_tag: UserTag,
  user_group: UserTag,
  user_message: UserMessage,
};

const sectionComponent = computed(() => actionComponents[props.action] || WechatMenus);
</script>

<style scoped>
.wechat-mp-app {
  height: 100%;
}
</style>
