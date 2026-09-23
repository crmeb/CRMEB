<template>
  <common-wrapper v-show="!isSortType" :config="configData">
    <view
      class="articleList"
      :class="{
        large: dataConfig.styleConfig.tabVal == 0,
        small: dataConfig.styleConfig.tabVal == 2,
      }"
      v-if="articleList.length"
    >
      <navigator :render-link="false"
        :url="'/pages/extension/news_details/index?id=' + item.id"
        hover-class="none"
        v-for="(item, index) in articleList"
        :key="index"
        :style="[articleItemStyle]"
        class="item"
      >
        <view v-if="dataConfig.styleConfig.tabVal != 0" class="image-wrap">
          <easy-loadimage
            :imageSrc="item.image_input[0]"
            :borderRadius="borderRadius"
            width="100%"
            height="100%"
          ></easy-loadimage>
        </view>
        <view class="text-wrap">
          <view class="title-wrap">
            <view
              class="title"
              :class="{
                line1: dataConfig.styleConfig.tabVal != 1,
                line2: dataConfig.styleConfig.tabVal == 1,
              }"
              :style="[titleStyle]"
              >{{ item.title }}</view
            >
          </view>
          <view v-if="dataConfig.styleConfig.tabVal == 0" class="image-wrap">
            <easy-loadimage
              :imageSrc="item.image_input[0]"
              :borderRadius="borderRadius"
              width="100%"
              height="100%"
            ></easy-loadimage>
          </view>
          <view class="time-wrap" :style="[numberStyle]">
            <view
              v-if="dataConfig.checkboxList.type.includes(0)"
              class="time time-wrap-item"
              :style="[timeStyle]"
              >{{ item.add_time }}</view
            >
            <view v-else class="time time-wrap-item"></view>
            <view class="like-wrap time-wrap-item">
              <view
                v-if="dataConfig.checkboxList.type.includes(1)"
                class="view acea-row row-middle"
                :class="!isLike ? 'on' : ''"
              >
                <text
                  class="iconfont icon-liulan"
                  :style="[iconEyesStyle]"
                ></text
                >{{ item.visit }}
              </view>
              <!-- <view v-if="dataConfig.checkboxList.type.includes(2)" class="like">
								<text class="iconfont icon-ic_Like" :style="[iconLikeStyle]"></text>{{ item.likes }}
							</view> -->
            </view>
          </view>
        </view>
      </navigator>
    </view>
  </common-wrapper>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import commonWrapper from "./commonWrapper.vue";
import { getArticleList } from "@/api/api.js";
import dayjs from "@/plugin/dayjs/dayjs.min.js";
import { LIMIT } from "@/config/app.js";

defineOptions({ name: "articleList" });

const props = defineProps({
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
  isSortType: {
    type: [String, Number],
    default: 0,
  },
});

const articleList = ref([]);

const configData = computed(() => {
  return {
    ...props.dataConfig,
    paddingConfig: props.dataConfig.paddingConfig || {
      isAll: false,
      valList: [
        {
          val: props.dataConfig.topConfig
            ? props.dataConfig.topConfig.val
            : 0,
        },
        {
          val: props.dataConfig.prConfig ? props.dataConfig.prConfig.val : 0,
        },
        {
          val: props.dataConfig.bottomConfig
            ? props.dataConfig.bottomConfig.val
            : 0,
        },
        {
          val: props.dataConfig.prConfig ? props.dataConfig.prConfig.val : 0,
        },
      ],
    },
    marginConfig: props.dataConfig.marginConfig || {
      isAll: false,
      valList: [
        {
          val: props.dataConfig.mbConfig ? props.dataConfig.mbConfig.val : 0,
        },
        {
          val: 0,
        },
        {
          val: 0,
        },
        {
          val: 0,
        },
      ],
    },
  };
});
const isLike = computed(() => {
  let like = true;
  like = props.dataConfig.checkboxList.type.includes(2);
  return like;
});
const borderRadius = computed(() => {
  let borderRadius = `${props.dataConfig.filletImg.val * 2}rpx`;
  if (props.dataConfig.filletImg.type) {
    borderRadius = `${props.dataConfig.filletImg.valList[0].val * 2}rpx ${
      props.dataConfig.filletImg.valList[1].val * 2
    }rpx ${props.dataConfig.filletImg.valList[3].val * 2}rpx ${
      props.dataConfig.filletImg.valList[2].val * 2
    }rpx`;
  }
  return borderRadius;
});
const timeStyle = computed(() => {
  return {
    color: props.dataConfig.timeColor.color[0].item,
  };
});
const iconEyesStyle = computed(() => {
  return {
    color: props.dataConfig.browseColor.color[0].item,
  };
});
// iconLikeStyle() {
// 	return {
// 		'color': this.dataConfig.likeColor.color[0].item,
// 	};
// },
const numberStyle = computed(() => {
  return {
    color: props.dataConfig.statisticColor.color[0].item,
  };
});
const articleItemStyle = computed(() => {
  let borderRadius = `${props.dataConfig.fillet.val * 2}rpx`;
  if (props.dataConfig.fillet.type) {
    borderRadius = `${props.dataConfig.fillet.valList[0].val * 2}rpx ${
      props.dataConfig.fillet.valList[1].val * 2
    }rpx ${props.dataConfig.fillet.valList[3].val * 2}rpx ${
      props.dataConfig.fillet.valList[2].val * 2
    }rpx`;
  }
  return {
    "border-radius": borderRadius,
    background: `linear-gradient(90deg, ${props.dataConfig.bgColor.color[0].item} 0%, ${props.dataConfig.bgColor.color[1].item} 100%)`,
  };
});
const titleStyle = computed(() => {
  let styleObject = {
    color: props.dataConfig.nameColor.color[0].item,
  };
  if (!props.dataConfig.nameConfig.tabVal) {
    styleObject["font-weight"] = "bold";
  }
  return styleObject;
});

onMounted(() => {
  getCidArticle();
});

function getCidArticle() {
  let limit = LIMIT;
  getArticleList(props.dataConfig.selectConfig.activeValue || 0, {
    page: 1,
    // limit: this.dataConfig.numConfig.val >= limit ? limit : this.dataConfig.numConfig.val
    limit: props.dataConfig.numConfig.val,
  }).then((res) => {
    if (props.dataConfig.styleConfig.tabVal == 2) {
      res.data.forEach((item) => {
        item.add_time = dayjs(item.add_time).format("MM-DD HH:mm");
      });
    }
    articleList.value = res.data;
  });
}
</script>

<style lang="scss">
.articleList {
  .item {
    display: flex;
    padding: 20rpx;
    margin: 0;
    background: #ffffff;
    margin-bottom: 20rpx;
    &:last-child {
      margin-bottom: 0;
    }
  }

  .text-wrap {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 0 0 6rpx 24rpx;
  }

  .title-wrap {
    flex: 1;
  }

  .title {
    font-size: 30rpx;
    line-height: 42rpx;
    color: #333333;
  }

  .label-wrap {
    margin-top: 16rpx;
    font-size: 0;
  }

  .label {
    display: inline-flex;
    flex-wrap: nowrap;
    height: 28rpx;
    padding: 0 6rpx;
    border: 1rpx solid #e93323;
    border-radius: 6rpx;
    font-size: 20rpx;
    color: #e93323;
  }

  .image-wrap {
    width: 240rpx;
    height: 152rpx;
  }

  .time-wrap {
    display: flex;
    font-size: 24rpx;
    line-height: 34rpx;
    color: #999999;

    .time-wrap-item:first-child {
      flex: 1;
    }
  }

  .like-wrap {
    display: flex;
  }

  .view {
    flex: 1;
    &.on {
      margin-right: 0;
    }
  }

  .iconfont {
    margin-right: 8rpx;
    font-size: 28rpx;
  }

  &.large {
    .item {
      flex-direction: column;
      padding: 0;
    }

    .text-wrap {
      padding: 32rpx 24rpx;
    }

    .image-wrap {
      width: 100%;
      height: 320rpx;
      margin-top: 24rpx;
    }

    .time-wrap {
      margin-top: 24rpx;
    }
  }

  &.small {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;

    .item {
      flex-direction: column;
      width: calc(50% - 10rpx);
      padding: 0;
      margin-bottom: 20rpx;
      box-sizing: border-box;
    }

    .text-wrap {
      padding: 20rpx 20rpx 18rpx;
      box-sizing: border-box;
    }

    .image-wrap {
      width: 100%;
      height: auto;
      aspect-ratio: 342 / 216;
    }

    .label-wrap {
      margin-top: 16rpx;
    }

    .time-wrap {
      margin-top: 16rpx;
    }

    .like-wrap {
      justify-content: space-between;
    }
  }
}
</style>
