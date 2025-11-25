<template>
  <div
    class="page-footer"
    :style="{ background: bgColor, paddingTop: topConfig + 'px', paddingBottom: bottomConfig + 'px' }"
    v-if="navConfig == 0"
  >
    <div class="foot-item" :class="navStyleConfig == 1 ? 'on' : ''" v-for="(item, index) in menuList" :key="index">
      <div v-if="navStyleConfig != 1">
        <img :src="item.imgList ? item.imgList[0] : noPic" alt="" v-if="index == isSpecial" />
        <img :src="item.imgList ? item.imgList[1] : noPic" alt="" v-else />
      </div>
      <div v-if="navStyleConfig != 2">
        <p v-if="index == isSpecial" :style="{ color: toneConfig ? activeTxtColor : colorStyle.theme }">
          {{ item.name || '自定义' }}
        </p>
        <p v-else :style="{ color: toneConfig ? txtColor : '#1A1A1A' }">{{ item.name || '自定义' }}</p>
      </div>
    </div>
  </div>
  <div
    class="page-footer page-footer2"
    v-else
    :style="{
      background: bgColor2,
      paddingTop: topConfig + 'px',
      paddingBottom: bottomConfig + 'px',
      marginLeft: prConfig + 'px',
      marginRight: prConfig + 'px',
      borderRadius: bgRadius,
    }"
  >
    <div class="list">
      <div class="foot-item" :class="navStyleConfig == 1 ? 'on' : ''" v-for="(item, index) in menuList" :key="index">
        <div v-if="navStyleConfig != 1">
          <img :src="item.imgList ? item.imgList[0] : noPic" alt="" v-if="index == isSpecial" />
          <img :src="item.imgList ? item.imgList[1] : noPic" alt="" v-else />
        </div>
        <div v-if="navStyleConfig != 2">
          <p v-if="index == isSpecial" :style="{ color: toneConfig ? activeTxtColor : colorStyle.theme }">
            {{ item.name || '自定义' }}
          </p>
          <p v-else :style="{ color: toneConfig ? txtColor : '#1A1A1A' }">{{ item.name || '自定义' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import theme from '@/mixins/theme';
export default {
  name: 'index.vue',
  props: {
    configObj: {
      type: Object,
      default: function () {
        return {};
      },
    },
    configNme: {
      type: String,
      default: '',
    },
  },
  mixins: [theme],
  data() {
    return {
      txtColor: '',
      activeTxtColor: '',
      bgColor: '',
      bgColor2: '',
      menuList: [],
      isSpecial: 2,
      toneConfig: 0,
      topConfig: 0,
      bottomConfig: 0,
      navStyleConfig: 0,
      navConfig: 0,
      prConfig: 0,
      mTop: 0,
      bgRadius: 0,
      noPic: require('../../assets/images/noPictrue.png'),
    };
  },
  computed: {
    ...mapState('mobildConfig', ['pageFooter']),
  },
  watch: {
    pageFooter: {
      handler(nVal, oVal) {
        this.setConfig(nVal);
      },
      deep: true,
    },
  },
  mounted() {
    let data = this.$store.state.mobildConfig.pageFooter;
    this.setConfig(data);
  },
  methods: {
    setConfig(data) {
      this.txtColor = data.txtColor.color[0].item;
      this.activeTxtColor = data.activeTxtColor.color[0].item;
      this.bgColor = data.bgColor.color[0].item;
      this.bgColor2 = data.bgColor2.color[0].item;
      this.navStyleConfig = data.navStyleConfig.tabVal;
      this.toneConfig = data.toneConfig.tabVal;
      this.navConfig = data.navConfig.tabVal;
      this.topConfig = data.topConfig.val;
      this.bottomConfig = data.bottomConfig.val;
      this.prConfig = data.prConfig.val;
      this.mTop = data.mbConfig.val;
      let fillet = data.fillet.type;
      let filletVal = data.fillet.val;
      let valList = data.fillet.valList;
      this.bgRadius = fillet
        ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
        : filletVal + 'px';
      this.$store.commit('mobildConfig/footType', this.navConfig);
      this.$store.commit('mobildConfig/footBottom', this.mTop);
      this.menuList = [];
      this.$set(this, 'menuList', data.menuList.length ? data.menuList : 5);
      if (data.status.title == '是否显示') {
        this.isSpecial = 2;
      } else {
        this.isSpecial = 0;
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.page-footer2 {
  backdrop-filter: blur(10px);
  .list {
    display: contents;
  }
}
.page-footer {
  display: flex;
  background: #fff;
  .foot-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 50px;
    &.on {
      p {
        font-size: 16px;
      }
    }
    img {
      width: 24px;
      height: 24px;
    }
    p {
      font-size: 12px;
      color: #282828;
      margin-top: 1px;
      &.on {
        color: #00a4f8;
      }
    }
  }
}
</style>
