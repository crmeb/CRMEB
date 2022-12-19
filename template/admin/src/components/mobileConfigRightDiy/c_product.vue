<template>
  <div class="c_product" v-if="configData">
    <div class="title">{{ configData.title }}</div>
    <div class="list-box">
      <draggable class="dragArea list-group" :list="configData.list" group="peoples" handle=".move-icon">
        <div
          class="item"
          v-for="(item, index) in configData.list"
          :key="index"
          @click="activeBtn(index)"
          v-model="configData.tabCur"
        >
          <div class="move-icon">
            <span class="iconfont-diy icondrag2"></span>
          </div>
          <div class="content">
            <div class="con-item" v-for="(list, key) in item.chiild" :key="key">
              <span>{{ list.title }}</span>
              <div style="width: 100%" @click="getLink(index, key, item)">
                <Input
                  :icon="key && !item.link ? 'ios-arrow-forward' : ''"
                  :readonly="key && !item.link ? true : false"
                  v-model="list.val"
                  :placeholder="list.pla"
                  :maxlength="list.max"
                />
              </div>
            </div>
            <div class="con-item" v-if="item.link">
              <span>{{ item.link.title }}</span>
              <Select v-model="item.link.activeVal" style="" @on-change="sliderChange(index)">
                <Option v-for="(item, j) in item.link.optiops" :value="item.value" :key="j">{{ item.label }} </Option>
              </Select>
            </div>
          </div>
          <div class="delete" @click.stop="bindDelete(index)">
            <Icon type="ios-close-circle" size="26" />
          </div>
        </div>
      </draggable>
    </div>
    <div v-if="configData.list">
      <div class="add-btn" @click="addHotTxt" v-if="configData.list.length < configData.max">
        <Button type="primary" ghost style="width: 100%; height: 40px; border-color: #1890ff; color: #1890ff"
          >添加模块</Button
        >
      </div>
    </div>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import vuedraggable from 'vuedraggable';
import linkaddress from '@/components/linkaddress';

export default {
  name: 'c_product',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
    index: {
      type: null,
    },
  },
  components: {
    linkaddress,
    draggable: vuedraggable,
  },
  data() {
    return {
      defaults: {},
      configData: {},
      itemObj: {},
      activeIndex: 0,
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
    });
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
      },
      deep: true,
    },
  },
  methods: {
    linkUrl(e) {
      this.configData.list[this.activeIndex].chiild[1].val = e;
    },
    getLink(index, key, item) {
      if (!key || item.link) {
        return;
      }
      this.activeIndex = index;
      this.$refs.linkaddres.modals = true;
    },
    addHotTxt() {
      if (this.configData.list.length == 0) {
        let storage = window.localStorage;
        this.itemObj = JSON.parse(storage.getItem('itemObj'));
        if (this.itemObj.link) {
          this.itemObj.link.activeVal = 0;
        }
        this.itemObj.chiild[0].val = '';
        this.itemObj.chiild[1].val = '';
        this.configData.list.push(this.itemObj);
      } else {
        let obj = JSON.parse(JSON.stringify(this.configData.list[this.configData.list.length - 1]));
        if (obj.chiild[0].empty) {
          obj.chiild[0].val = '';
          obj.chiild[1].val = '';
        }
        this.configData.list.push(obj);
      }
    },
    // 删除数组
    bindDelete(index) {
      if (this.configData.list.length == 1) {
        let itemObj = this.configData.list[0];
        this.itemObj = itemObj;
        let storage = window.localStorage;
        storage.setItem('itemObj', JSON.stringify(itemObj));
      }
      this.configData.list.splice(index, 1);
      this.configData.tabCur = 0;
      this.$emit('getConfig', { name: 'delete', indexs: 0 });
    },
    sliderChange(index) {
      this.configData.tabCur = index;
      this.$emit('getConfig', { name: 'product', indexs: index });
    },
    activeBtn(index) {
      this.configData.tabCur = index;
      this.$emit('getConfig', { name: 'product', indexs: index });
    },
  },
};
</script>

<style scoped lang="stylus">
/deep/.ivu-input
    font-size 13px!important
.c_product
    border-bottom 1px solid rgba(0,0,0,0.05);
    padding-bottom 20px
    margin-bottom 20px
    .list-box
        .item
            position relative
            display flex
            margin-top 23px
            padding 18px 20px 18px 0
            border: 1px solid rgba(238, 238, 238, 1);
            box-shadow: 0 0 10px #eee;

            .delete
                position absolute
                right 0
                top 0
                right: -13px;
                top: -14px;
                color #999999
                cursor pointer

        .move-icon
            display flex
            align-items center
            justify-content center
            width 50px
            cursor move

        .content
            flex 1

            .con-item
                display flex
                align-items center
                margin-bottom 15px

                &:last-child
                    margin-bottom 0
                span
                    width 45px
                    font-size 13px

    .add-btn
        margin-top 18px
.title
    padding-top 20px
    font-size 12px
    color #999
.iconfont-diy
    color #DDDDDD
    font-size 38px
</style>
