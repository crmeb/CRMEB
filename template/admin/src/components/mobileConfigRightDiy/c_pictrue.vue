<template>
  <div class="mobile-page">
    <div v-if="isUpdate">
      <Divider />
      <div class="title">布局</div>
      <div class="tip">选定布局区域，在下方添加图片，建议添加比例一致的图片</div>
      <div class="advert">
        <div class="advertItem01 acea-row" v-if="style === 0" v-for="(item, index) in configData.picList" :key="index">
          <img :src="item.image" v-if="item.image" />
          <div class="empty-box" v-else>尺寸不限</div>
        </div>
        <div class="advertItem02 acea-row" v-if="style === 1">
          <div
            class="item"
            :class="currentIndex === index ? 'on' : ''"
            @click="currentTab(index, configData)"
            v-for="(item, index) in configData.picList"
          >
            <img :src="item.image" v-if="item.image" />
            <div class="empty-box" v-else>
              <div>
                <div>宽375像素</div>
                <div>高度不限</div>
              </div>
            </div>
          </div>
        </div>
        <div class="advertItem02 advertItem03 acea-row" v-if="style === 2">
          <div
            class="item"
            :class="currentIndex === index ? 'on' : ''"
            @click="currentTab(index, configData)"
            v-for="(item, index) in configData.picList"
          >
            <img :src="item.image" v-if="item.image" />
            <div class="empty-box" v-else>
              <div>
                <div>宽250像素</div>
                <div>高度不限</div>
              </div>
            </div>
          </div>
        </div>
        <div class="advertItem04 acea-row" v-if="style === 3">
          <div class="item" :class="currentIndex === 0 ? 'on' : ''" @click="currentTab(0, configData)">
            <img :src="configData.picList[0].image" v-if="configData.picList[0].image" />
            <div class="empty-box" v-else>375*375像素或同比例</div>
          </div>
          <div class="item">
            <div class="pic" :class="currentIndex === 1 ? 'on' : ''" @click="currentTab(1, configData)">
              <img :src="configData.picList[1].image" v-if="configData.picList[1].image" />
              <div class="empty-box" v-else>375*188像素或同比例</div>
            </div>
            <div class="pic" :class="currentIndex === 2 ? 'on' : ''" @click="currentTab(2, configData)">
              <img :src="configData.picList[2].image" v-if="configData.picList[2].image" />
              <div class="empty-box" v-else>375*188像素或同比例</div>
            </div>
          </div>
        </div>
        <div class="advertItem02 advertItem05 acea-row" v-if="style === 4">
          <div
            class="item"
            :class="currentIndex === index ? 'on' : ''"
            @click="currentTab(index, configData)"
            v-for="(item, index) in configData.picList"
          >
            <img :src="item.image" v-if="item.image" />
            <div class="empty-box" v-else>宽188像素高度不限</div>
          </div>
        </div>
        <div class="advertItem06 acea-row" v-if="style === 5">
          <div
            class="item"
            :class="currentIndex === index ? 'on' : ''"
            @click="currentTab(index, configData)"
            v-for="(item, index) in configData.picList"
          >
            <img :src="item.image" v-if="item.image" />
            <div class="empty-box" v-else>375*188像素或同比例</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_pictrue',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  data() {
    return {
      defaults: {},
      configData: {},
      style: 0,
      isUpdate: false, // 重新渲染
      currentIndex: 0,
      arrayObj: {
        image: '',
        link: '',
      },
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      if (this.configObj.hasOwnProperty('timestamp')) {
        this.isUpdate = true;
      } else {
        this.isUpdate = false;
      }
      this.$set(this, 'configData', this.configObj[this.configNme]);
    });
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.$set(this, 'configData', nVal[this.configNme]);
        this.style = nVal.tabConfig.tabVal;
        this.isUpdate = true;
        this.$set(this, 'isUpdate', true);
      },
      deep: true,
    },
    'configObj.tabConfig.tabVal': {
      handler(nVal, oVal) {
        this.count = this.defaults.tabConfig.tabList[nVal].count;
        this.picArrayConcat(this.count);
        this.configData.picList.splice(nVal + 1);
        this.currentIndex = 0;
        let list = this.defaults.menuConfig.list[0];
        if (this.configData.picList[0]) {
          list.img = this.configData.picList[0].image;
          list.info[0].value = this.configData.picList[0].link;
        }
      },
      deep: true,
    },
  },
  methods: {
    currentTab(e, data) {
      this.currentIndex = e;
      this.configData.tabVal = e;
      if (this.defaults.menuConfig.isCube) {
        let list = this.defaults.menuConfig.list[0];
        if (data.picList[e] && data.picList[e].image) {
          list.img = data.picList[e].image;
          list.info[0].value = data.picList[e].link;
        } else {
          list.img = '';
          list.info[0].value = '';
        }
      }
    },
    picArrayConcat(count) {
      for (let i = this.configData.picList.length; i < count; i++) {
        this.configData.picList.push(this.arrayObj);
      }
    },
  },
};
</script>
<style scoped lang="stylus">
/deep/ .ivu-divider-horizontal {
    margin 12px 0
}
.empty-box {
    color: #8c8c8c;
    font-size 12px;
    border-radius 0;
    background-color #eee;
    width 100%;
    border 1px solid #ddd;
    text-align center;
}

.mobile-page {
    .tip {
        font-size 12px;
        color: rgba(0, 0, 0, .45);
        margin-bottom 2px;
    }

    .advert {
        cursor pointer;

        .advertItem01 {
            width 100%;
            height 100%;
            .empty-box {
                height 366px;
                border 1px solid #ddd;
            }

            img {
                width 100%;
                height 100%;
                border 1px solid #1890ff;
            }
        }

        .advertItem02 {
            width 100%

            .item {
                width 50%;
                height 100%;

                img {
                    width 100%;
                    height 100%;
                }

                &.on{
                    img{
                        border:1px solid #1890ff!important
                    }

                   .empty-box{
                       border:1px solid #1890ff!important
                       color #1890ff
                   }
                }

                .empty-box {
                    height 189.5px;
                    border-right 1px solid #eee;
                }

                &:nth-child(2) {
                    .empty-box {
                        border-right 1px solid #ddd;
                    }
                }
            }
        }

        .advertItem03 {
            .item {
                width 33.3333%;

                .empty-box {
                    height 126.4px;
                    border-right 1px solid #eee;
                }

                &:nth-child(2) {
                    .empty-box {
                        border-right 1px solid #eee;
                    }
                }

                &:nth-child(3) {
                    .empty-box {
                        border-right 1px solid #ddd;
                    }
                }
            }
        }

        .advertItem04 {
            .item {
                width 50%;
                height 189.5px;

                .empty-box {
                    height 100%;
                }

                img {
                    width 100%;
                    height 100%;
                }

                &.on{
                    img{
                        border:1px solid #1890ff!important
                    }

                    .empty-box{
                        border:1px solid #1890ff!important
                        color #1890ff
                    }
                }

                .pic {
                    width 100%;
                    height 94.75px;

                    &.on{
                        img{
                            border:1px solid #1890ff!important
                        }

                        .empty-box{
                            border:1px solid #1890ff!important
                            color #1890ff
                        }
                    }
                }

                &:nth-child(1) {
                    .empty-box {
                        border-right 1px solid #eee;
                    }
                }

                &:nth-child(2) {
                    .pic {
                        &:nth-child(2) {
                            .empty-box {
                                border-top 1px solid #eee;
                            }
                        }
                    }
                }
            }
        }

        .advertItem05 {
            .item {
                width 25%;

                .empty-box {
                    height 94.75px;
                }

                &:nth-child(4) {
                    .empty-box {
                        border-right 1px solid #ddd;
                    }
                }

                &:nth-child(2) {
                    .empty-box {
                        border-right 1px solid #eee;
                    }
                }
            }
        }

        .advertItem06 {
            .item {
                width 50%;
                height 95px;

                img {
                    width 100%;
                    height 100%;
                }

                &.on{
                    img{
                        border:1px solid #1890ff!important
                    }

                    .empty-box{
                        border:1px solid #1890ff!important
                        color #1890ff
                    }
                }

                .empty-box {
                    height 100%;
                    border-right 1px solid #eee;
                    border-bottom 1px solid #eee;
                }

                &:nth-child(2) {
                    .empty-box {
                        border-right 1px solid #ddd;
                    }
                }

                &:nth-child(3) {
                    .empty-box {
                        border-bottom 1px solid #ddd;
                    }
                }

                &:nth-child(4) {
                    .empty-box {
                        border-right 1px solid #ddd;
                        border-bottom 1px solid #ddd;
                    }
                }
            }
        }
    }
}
</style>
