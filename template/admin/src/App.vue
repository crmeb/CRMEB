<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script>
import { on, off } from 'iview/src/utils/dom';
import { setMatchMedia } from 'iview/src/utils/assist';

import { mapMutations } from 'vuex';

setMatchMedia();

export default {
  name: 'app',
  provide() {
    return {
      reload: this.reload,
    };
  },
  methods: {
    ...mapMutations('media', ['setDevice']),
    handleWindowResize() {
      this.handleMatchMedia();
    },
    handleMatchMedia() {
      const matchMedia = window.matchMedia;

      if (matchMedia('(max-width: 600px)').matches) {
        var deviceWidth = document.documentElement.clientWidth || window.innerWidth;
        let css = 'calc(100vw/7.5)';
        document.documentElement.style.fontSize = css;
        this.setDevice('Mobile');
      } else if (matchMedia('(max-width: 992px)').matches) {
        this.setDevice('Tablet');
      } else {
        this.setDevice('Desktop');
      }
    },
    reload() {
      this.isRouterAlive = false;
      this.$nextTick(function () {
        this.isRouterAlive = true;
      });
    },
  },
  mounted() {
    on(window, 'resize', this.handleWindowResize);
    this.handleMatchMedia();
  },
  beforeDestroy() {
    off(window, 'resize', this.handleWindowResize);
  },
};
</script>

<style lang="less">
.size {
  width: 100%;
  height: 100%;
}
html,
body {
  .size;
  overflow: hidden;
  margin: 0;
  padding: 0;
}
#app {
  .size;
}
.dialog-fade-enter-active {
  animation: anim-open 0.3s;
}
.dialog-fade-leave-active {
  animation: anim-close 0.3s;
}
@keyframes anim-open {
  0% {
    transform: translate3d(100%, 0, 0);
    opacity: 0;
  }
  100% {
    transform: translate3d(0, 0, 0);
    opacity: 1;
  }
}
@keyframes anim-close {
  0% {
    transform: translate3d(0, 0, 0);
    opacity: 1;
  }
  100% {
    transform: translate3d(100%, 0, 0);
    opacity: 0;
  }
}
.ivu-modal-wrap /deep/ .connect_customerServer_img {
  display: none;
}
.right-box .ivu-color-picker .ivu-select-dropdown {
  position: absolute;
  // width: 300px !important;
  left: -73px !important;
}
</style>
