<template>
  <div class="ivu-global-footer i-copyright" v-if="isShow">
    <div class="ivu-global-footer-links" v-if="!copyright">
      <a :href="item.href" target="_blank" v-for="(item, index) in links" :key="index">{{ item.title }}</a>
    </div>
    <div class="ivu-global-footer-copyright" v-if="copyright">{{ copyright }}</div>
    <div class="ivu-global-footer-copyright" v-else>
      Copyright © 2016~2026
      <a href="https://www.crmeb.com" target="_blank">{{ version }}</a>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue';
import { getCrmebCopyRight } from '@/api/system';
import { Session } from '@/utils/storage.js';
import { useUserInfoStore } from '@/store/module/userInfo';

defineOptions({ name: 'i-copyright' });

const links = ref([
  {
    title: '官网',
    key: '官网',
    href: 'https://www.crmeb.com',
  },
  {
    title: '社区',
    key: '社区',
    href: 'https://www.crmeb.com/ask',
  },
  {
    title: '文档',
    key: '文档',
    href: 'http://doc.crmeb.com',
  },
]);
const copyright = ref('');
const version = ref('');
const isShow = ref(false);

// created
const userInfoStore = useUserInfoStore();
version.value = userInfoStore.version;
getCrmebCopyRight().then((res) => {
  copyright.value = res.data.copyrightContext;
  Session.set('copyright', res.data.copyrightContext);
  isShow.value = true;
});
</script>
<style lang="scss">
.ivu-global-footer {
  /* margin: 48px 0 24px 0; */
  /* padding: 0 16px; */
  margin: 5px 0px;
  text-align: center;
  box-sizing: border-box;
}
.i-copyright {
  flex: 0 0 auto;
}
.ivu-global-footer-links {
  margin-bottom: 5px;
}
.ivu-global-footer-links a:not(:last-child) {
  margin-right: 40px;
}
.ivu-global-footer-links a {
  font-size: 14px;
  color: #808695;
  transition: all 0.2s ease-in-out;
}
.ivu-global-footer-copyright {
  color: #808695;
  font-size: 14px;
}
.ivu-global-footer-copyright a {
  color: #808695;
}
</style>
