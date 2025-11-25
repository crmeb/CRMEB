<template>
  <div
    class="mobile-page"
    :style="{
      background: bottomBgColor,
      marginTop: mTop + 'px',
      paddingTop: topConfig + 'px',
      paddingBottom: bottomConfig + 'px',
      paddingLeft: prConfig + 'px',
      paddingRight: prConfig + 'px',
    }"
  >
    <div class="advert" :style="style === 10 ? 'height:100%' : 'height:' + (379 - prConfig * 2) + 'px'">
      <div class="advertItem07" v-if="style === 0">
        <div
          class="item"
          v-for="(item, index) in picList"
          :key="index"
          :style="{ border: imgConfig + 'px solid ' + bottomBgColor }"
        >
          <img
            class="img"
            :src="item.image"
            v-if="item.image"
            :style="{
              borderRadius: imgRadius,
            }"
          />
          <div
            class="empty-box"
            v-else
            :style="{
              borderRadius: imgRadius,
            }"
          >
            <img src="../../assets/images/shan.png" />
          </div>
        </div>
      </div>
      <div class="advertItem02 acea-row" v-if="style === 1">
        <div
          class="item"
          v-for="(item, index) in picList"
          :key="index"
          :style="{ border: imgConfig + 'px solid ' + bottomBgColor }"
        >
          <img
            class="img"
            :src="item.image"
            v-if="item.image"
            :style="{
              borderRadius: imgRadius,
            }"
          />
          <div
            class="empty-box"
            v-else
            :style="{
              borderRadius: imgRadius,
            }"
          >
            <img src="../../assets/images/shan.png" />
          </div>
        </div>
      </div>
      <div class="advertItem02 advertItem03 acea-row" v-if="style === 2">
        <div
          class="item"
          v-for="(item, index) in picList"
          :key="index"
          :style="{ border: imgConfig + 'px solid ' + bottomBgColor }"
        >
          <img
            class="img"
            :src="item.image"
            v-if="item.image"
            :style="{
              borderRadius: imgRadius,
            }"
          />
          <div
            class="empty-box"
            v-else
            :style="{
              borderRadius: imgRadius,
            }"
          >
            <img src="../../assets/images/shan.png" />
          </div>
        </div>
      </div>
      <div class="advertItem07 advertItem08" v-if="style === 3">
        <div class="item acea-row">
          <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img
              class="img"
              :src="picList[0].image"
              v-if="picList[0].image"
              :style="{
                borderRadius: imgRadius,
              }"
            />
            <div
              class="empty-box"
              v-else
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
          <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img
              class="img"
              :src="picList[1].image"
              v-if="picList[1].image"
              :style="{
                borderRadius: imgRadius,
              }"
            />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
        </div>
        <div class="item" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
          <img class="img" :src="picList[2].image" v-if="picList[2].image" :style="{ borderRadius: imgRadius }" />
          <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
            <img src="../../assets/images/shan.png" />
          </div>
        </div>
      </div>
      <div class="advertItem07 advertItem08" v-if="style === 4">
        <div class="item" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
          <img class="img" :src="picList[0].image" v-if="picList[0].image" :style="{ borderRadius: imgRadius }" />
          <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
            <img src="../../assets/images/shan.png" />
          </div>
        </div>
        <div class="item acea-row">
          <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[1].image" v-if="picList[1].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
          <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[2].image" v-if="picList[2].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
        </div>
      </div>
      <div class="advertItem04 acea-row" v-if="style === 5">
        <div class="item" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
          <img class="img" :src="picList[0].image" v-if="picList[0].image" :style="{ borderRadius: imgRadius }" />
          <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
            <img src="../../assets/images/shan.png" />
          </div>
        </div>
        <div class="item">
          <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[1].image" v-if="picList[1].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
          <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[2].image" v-if="picList[2].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
        </div>
      </div>
      <div class="advertItem04 acea-row" v-if="style === 6">
        <div class="item">
          <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[0].image" v-if="picList[0].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
          <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[1].image" v-if="picList[1].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
        </div>
        <div class="item" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
          <img class="img" :src="picList[2].image" v-if="picList[2].image" :style="{ borderRadius: imgRadius }" />
          <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
            <img src="../../assets/images/shan.png" />
          </div>
        </div>
      </div>
      <div class="advertItem06 acea-row" v-if="style === 7">
        <div
          class="item"
          v-for="(item, index) in picList"
          :key="index"
          :style="{ border: imgConfig + 'px solid ' + bottomBgColor }"
        >
          <img class="img" :src="item.image" v-if="item.image" :style="{ borderRadius: imgRadius }" />
          <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
            <img src="../../assets/images/shan.png" />
          </div>
        </div>
      </div>
      <div class="advertItem07 advertItem08" v-if="style === 8">
        <div class="item acea-row">
          <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[0].image" v-if="picList[0].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
          <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[1].image" v-if="picList[1].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
        </div>
        <div class="items acea-row">
          <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[2].image" v-if="picList[2].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
          <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[3].image" v-if="picList[3].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
          <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[4].image" v-if="picList[4].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
        </div>
      </div>
      <div class="advertItem04 acea-row" v-if="style === 9">
        <div class="item" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
          <img class="img" :src="picList[0].image" v-if="picList[0].image" :style="{ borderRadius: imgRadius }" />
          <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
            <img src="../../assets/images/shan.png" />
          </div>
        </div>
        <div class="item">
          <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[1].image" v-if="picList[1].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
          <div class="pic acea-row">
            <div class="picItem" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img class="img" :src="picList[2].image" v-if="picList[2].image" :style="{ borderRadius: imgRadius }" />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="../../assets/images/shan.png" />
              </div>
            </div>
            <div class="picItem" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img class="img" :src="picList[3].image" v-if="picList[3].image" :style="{ borderRadius: imgRadius }" />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="../../assets/images/shan.png" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <template v-if="style === 10">
        <div
          class="advertItem01 acea-row"
          v-for="(item, index) in picList"
          :key="index"
          :style="{ border: imgConfig + 'px solid ' + bottomBgColor }"
        >
          <img class="img" :src="item.image" v-if="item.image" :style="{ borderRadius: imgRadius }" />
          <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
            <img src="../../assets/images/shan.png" />
          </div>
        </div>
      </template>
      <template v-if="style === 11">
        <div
          class="advertItem11 acea-row"
          v-for="(item, index) in docPicList"
          :key="index"
          :style="{
            border: imgConfig + 'px solid ' + bottomBgColor,
            width: (item.doc.w / 375) * 100 + '%',
            height: (item.doc.h / 375) * 100 + '%',
            left: (item.doc.startX / 375) * 100 + '%',
            top: (item.doc.startY / 375) * 100 + '%',
          }"
        >
          <img class="img" :src="item.img" v-if="item.img" :style="{ borderRadius: imgRadius }" />
          <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
            <img src="../../assets/images/shan.png" />
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
export default {
  name: 'picture_cube',
  cname: '图片魔方',
  configName: 'c_picture_cube',
  icon: '#iconzujian-tupianmofang',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'pictureCube', // 外面匹配名称
  props: {
    index: {
      type: null,
    },
    num: {
      type: null,
    },
  },
  computed: {
    ...mapState('mobildConfig', ['defaultArray']),
  },
  watch: {
    pageData: {
      handler(nVal) {
        this.setConfig(nVal);
      },
      deep: true,
    },
    num: {
      handler(nVal) {
        let data = this.$store.state.mobildConfig.defaultArray[nVal];
        this.setConfig(data);
      },
      deep: true,
    },
    defaultArray: {
      handler() {
        let data = this.$store.state.mobildConfig.defaultArray[this.num];
        this.setConfig(data);
      },
      deep: true,
    },
  },
  data() {
    return {
      // 默认初始化数据禁止修改
      defaultConfig: {
        cname: '图片魔方',
        name: 'pictureCube',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '展示设置',
        titleShow: '展示设置',
        titleContent: '内容设置',
        titleRight: '图片魔方',
        titleCurrency: '通用样式',
        styleConfig: {
          title: '选择风格',
          tabVal: 0,
          count: 2,
          type: 'pictureCube',
        },
        picStyle: {
          tabVal: 0,
          picList: [],
          docPicList: [],
        },

        menuConfig: {
          title: '',
          maxList: 1,
          isCube: 1,
          list: [
            {
              img: '',
              info: [
                {
                  title: '链接',
                  tips: '请输入链接',
                  value: '',
                  max: 100,
                },
              ],
            },
          ],
        },
        imgConfig: {
          title: '图片间距',
          val: 0,
          min: 0,
        },
        filletImg: {
          title: '图片圆角',
          type: 0,
          list: [
            {
              val: '全部',
              icon: 'iconcaozuo-zhengti',
            },
            {
              val: '单个',
              icon: 'iconcaozuo-bianjiao',
            },
          ],
          valName: '圆角值',
          val: 0,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
        },
        bottomBgColor: {
          title: '底部背景',
          default: [
            {
              item: '#f5f5f5',
            },
          ],
          color: [
            {
              item: '#f5f5f5',
            },
          ],
        },
        topConfig: {
          title: '上边距',
          val: 0,
          min: 0,
        },
        bottomConfig: {
          title: '下边距',
          val: 0,
          min: 0,
        },
        prConfig: {
          title: '左右边距',
          val: 0,
          min: 0,
        },
        mbConfig: {
          title: '页面上间距',
          val: 0,
          min: 0,
        },
      },
      pageData: {},
      style: 0,
      picList: [],
      docPicList: [],
      bottomBgColor: [],
      mTop: 0,
      topConfig: 0,
      bottomConfig: 0,
      prConfig: 0,
      imgRadius: 0,
      imgConfig: 0,
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.pageData = this.$store.state.mobildConfig.defaultArray[this.num];
      this.setConfig(this.pageData);
    });
  },
  methods: {
    setConfig(data) {
      if (!data) return;
      if (data.mbConfig) {
        this.style = data.styleConfig.tabVal;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.prConfig = data.prConfig.val;
        this.mTop = data.mbConfig.val;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        let filletImg = data.filletImg.type;
        let filletValImg = data.filletImg.val;
        let valListImg = data.filletImg.valList;
        this.imgRadius = filletImg
          ? valListImg[0].val + 'px ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + valListImg[2].val + 'px'
          : filletValImg + 'px';
        this.imgConfig = data.imgConfig.val;
        if (data.styleConfig.tabVal !== 11) {
          if (!data.picStyle.picList.length) {
            this.picList = [
              {
                image: '',
                link: '',
              },
            ];
          } else {
            this.picList = data.picStyle.picList;
          }
        } else {
          data.picStyle.docPicList.map((e, i) => {
            data.picStyle.docPicList[i].img = data.picStyle.picList[i].image;
            data.picStyle.docPicList[i].link = data.picStyle.picList[i].link;
          });
          this.$set(this, 'docPicList', data.picStyle.docPicList);
        }
      }
    },
  },
};
</script>
<style scoped lang="scss">
.empty-box {
  background: #f3f9ff;
  img {
    width: 65px;
    height: 50px;
  }
}

.img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.advertItem07 {
  width: 100%;
  height: 100%;

  .item {
    width: 100%;
    height: 50%;
  }

  .items {
    width: 100%;
    height: 50%;

    .pic {
      width: 33.3333%;
      height: 100%;
    }
  }
}

.advertItem08 {
  .item {
    .pic {
      width: 50%;
      height: 100%;
    }
  }
}

.mobile-page {
  .advert {
    position: relative;

    .advertItem11 {
      position: absolute;
    }

    .advertItem01 {
      width: 100%;
      height: 100%;

      .empty-box {
        width: 100%;
        height: 379px;

        .icontupian {
          font-size: 50px;
          color: #999;
        }
      }
    }

    .advertItem02 {
      width: 100%;
      height: 100%;

      .item {
        width: 50%;
        height: 100%;

        .empty-box {
          width: 100%;
          height: 100%;
        }
      }
    }

    .advertItem03 {
      .item {
        width: 33.3333%;
        height: 100%;

        .empty-box {
          width: 100%;
          height: 100%;
        }
      }
    }

    .advertItem04 {
      width: 100%;
      height: 100%;

      .item {
        width: 50%;
        height: 100%;

        .empty-box {
          width: 100%;
          height: 100%;
        }

        .pic {
          width: 100%;
          height: 50%;

          .picItem {
            width: 50%;
            height: 100%;
          }
        }
      }
    }

    .advertItem05 {
      .item {
        width: 25%;

        .empty-box {
          width: 100%;
          height: 94.75px;
        }
      }
    }

    .advertItem06 {
      width: 100%;
      height: 100%;

      .item {
        width: 50%;
        height: 50%;

        .empty-box {
          width: 100%;
          height: 100%;
        }
      }
    }
  }
}
</style>
