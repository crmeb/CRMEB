<template>
  <div class="numbox" v-if="datas[name]">
    <div
      class="c_row-item"
      v-for="(item, index) in datas[name].list"
      :key="index"
    >
      <div class="dif" v-if="item.title === '链接'">
        <Col class="label" span="4">
          <span>{{ item.title }}</span>
        </Col>
        <Col span="19" class="slider-box">
          <div class="input-box" @click="getLink(index)">
            <Input
              v-model="item.val"
              :placeholder="item.pla"
              :maxlength="item.max"
              icon="ios-arrow-forward"
              readonly
            />
          </div>
        </Col>
      </div>
      <div class="dif" v-else>
        <Col class="label" span="4">
          <span>{{ item.title }}</span>
        </Col>
        <Col span="19" class="slider-box">
          <Input
            v-model="item.val"
            :placeholder="item.pla"
            :maxlength="item.max"
            style="text-align: right"
          />
        </Col>
      </div>
    </div>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import linkaddress from "@/components/linkaddress";

export default {
  name: "c_input_list",
  props: {
    name: {
      type: String,
    },
    configData: {
      type: null,
    },
    configNum: {
      type: Number | String,
      default: "default",
    },
  },
  components: {
    linkaddress,
  },
  data() {
    return {
      defaults: {},
      datas: this.configData[this.configNum],
      activeIndex: 0,
    };
  },
  mounted() {},
  watch: {
    configData: {
      handler(nVal, oVal) {
        this.datas = nVal[this.configNum];
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    getLink(index) {
      this.activeIndex = index;
      this.$refs.linkaddres.modals = true;
    },
    linkUrl(e) {
      this.datas[this.name].list[this.activeIndex].val = e;
    },
  },
};
</script>

<style scoped lang="stylus">
.numbox {
  margin: 20px 0 10px 0;

  span {
    width: 80px;
    color: #999;
  }
}

.c_row-item {
  width: 100%;

  &~.c_row-item {
    margin-top: 20px;
  }
}

.dif {
  display: flex;
  align-items: center;
}

.slider-box {
  margin-left: 10px;
  width: 350px;
}
</style>