<template>
    <div class="ivu-global-footer i-copyright">
       <div class="ivu-global-footer-links">
          <a :href="item.href" target="_blank" v-for="(item,index) in links" :key="index">{{item.title}}</a>
       </div>
       <div class="ivu-global-footer-copyright">{{copyright}}</div>
    </div>
</template>
<script>
export default {
  name: 'i-copyright',
  data () {
    return {
      links: [
        {
          title: '官网',
          key: '官网',
          href: 'https://www.crmeb.com'
        },
        {
          title: '社区',
          key: '社区',
          href: 'http://bbs.crmeb.net'
        },
        {
          title: '文档',
          key: '文档',
          href: 'https://help.crmeb.net'
        }
      ],
      copyright: 'Copyright © 2021 | 西安众邦网络科技有限公司'
    }
  },
  mounted () {
    this.getVersion();
  },
  methods: {
    getVersion () {
      let version = this.$store.state.userInfo.version
      this.copyright += version ? '  |  ' + version : ''
    }
  }
}
</script>
<style lang="less">
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
       transition: all .2s ease-in-out;
     }
    .ivu-global-footer-copyright {
      color: #808695;
      font-size: 14px;
    }
</style>
