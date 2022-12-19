<template>
  <div class="line-box" v-if="configData">
    <div class="title">
      <p>搜索热词</p>
      <span>热词最多20个词，鼠标拖拽左侧圆点可调整</span>
    </div>
    <div class="input-box">
      <draggable class="dragArea list-group" :list="configData.list" group="peoples" handle=".icon">
        <div class="input-item" v-for="(item, index) in configData.list" :key="index">
          <div class="icon">
            <Icon type="ios-keypad" size="20" />
          </div>
          <Input v-model="item.val" maxlength="10" placeholder="选填，不超过十个字" />
          <div class="delete" @click.stop="bindDelete(index)">
            <Icon type="ios-close-circle" size="20" />
          </div>
        </div>
      </draggable>
      <div class="add-btn" @click="addHotTxt" v-if="configData.list.length < 20">
        <Button type="primary" ghost style="width: 100%; height: 40px; border-color: #1890ff; color: #1890ff"
          >添加热词</Button
        >
      </div>
    </div>
  </div>
</template>
<script>
import vuedraggable from 'vuedraggable';
export default {
  name: 'c_hot_word',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  components: {
    draggable: vuedraggable,
  },
  data() {
    return {
      hotWordList: [],
      hotIndex: 1,
      defaults: {},
      configData: {},
    };
  },
  created() {
    this.defaults = this.configObj;
    this.configData = this.configObj[this.configNme];
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        // this.hotWordList = nVal.hotList
        this.configData = nVal[this.configNme];
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    addHotTxt() {
      // let obj = {}
      // if(this.configData.list.length){
      //     obj = JSON.parse(JSON.stringify(this.configData.list[this.configData.list.length - 1]))
      // }else {
      //     obj = {
      //         val: ''
      //     }
      // }
      let obj = {
        val: '',
      };
      this.configData.list.push(obj);
      // this.$emit('input', this.hotWordList);
    },
    // 删除数组
    bindDelete(index) {
      this.configData.list.splice(index, 1);
    },
  },
};
</script>

<style scoped lang="stylus">
.line-box
    margin-top 20px
    padding 10px 0 20px
    border-top 1px solid rgba(0,0,0,.05)
    border-bottom 1px solid rgba(0,0,0,.05)
    .title
        p
            font-size 14px
            color #000000
        span
            color #999999
    .input-box
        margin-top 10px
        .add-btn
            margin-top 18px
        .input-item
            display flex
            align-items center
            margin-bottom 15px
            position relative
            .delete{
                position: absolute;
                right: -7px;
                top: -8px;
                color: #999;
            }
            .icon
                display flex
                align-items center
                justify-content center
                width 36px
                cursor move
            /deep/.ivu-input
                flex 1
                height 36px
                font-size 13px!important
</style>
