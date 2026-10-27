<template>
  <view class="goodList" :style="[bgRadius]" v-if="tempArr.length > 0">
    <common-wrapper :config="configData">
      <!-- Header Section -->
      <view
        class="header-box"
        :style="[headerBoxStyle]"
        v-if="headerText || headerImg"
      >
        <view
          class="title-text"
          v-if="headerType == 0"
          :style="[titleTextStyle]"
        >
          {{ headerText }}
        </view>
        <view class="title-img" v-else :style="[titleImgBoxStyle]">
          <image
            :src="headerImg"
            mode="widthFix"
            :style="[titleImgStyle]"
            v-if="headerImg"
          ></image>
        </view>
      </view>

      <view v-if="tempArr.length > 0" class="list">
        <!-- 单列 -->
        <view v-if="styleConfig == 0">
          <view
            class="w-full flex justify-between item bg--w111-fff p-20"
            :style="[bgColor, bgRadius]"
            v-for="(item, index) in tempArr"
            :key="index"
            @tap="goDetail(item)"
          >
            <easy-loadimage
              :image-src="item.image"
              width="224rpx"
              height="224rpx"
              :borderRadius="imgStyle"
            ></easy-loadimage>
            <view class="flex-1 flex-col justify-between pl-20">
              <view
                class="w-full fs-28 h-80 lh-40rpx line2"
                v-if="checkboxInfo.includes(0)"
                :style="[productStyle]"
              >
                <text v-if="item.brand_name" class="brand-tag">{{
                  item.brand_name
                }}</text>
                {{ item.store_name }}
              </view>
              <view
                class="flex items-end flex-wrap mt-8 w-full"
                v-if="
                  checkboxInfo.includes(1) &&
                  item.label_list &&
                  item.label_list.length
                "
              >
                <BaseTag
                  :text="label.name"
                  :color="label.font_color"
                  :background="label.bg_color"
                  :borderColor="label.border_color"
                  :circle="label.border_color ? true : false"
                  :imgSrc="label.image"
                  v-for="(label, idx) in item.label_list"
                  :key="idx"
                ></BaseTag>
              </view>
              <view class="flex-between-center" v-if="onlyShowPrice">
                <baseMoney
                  :money="item.price"
                  symbolSize="24"
                  integerSize="40"
                  decimalSize="24"
                  weight
                  :color="priceColor"
                ></baseMoney>
                <view @tap.stop="addCartChange(item, index)" v-if="!showBtn">
                  <view
                    class="w-96 h-56 rd-28rpx flex-center fs-24 text--w111-fff"
                    v-if="btnStyle == 0"
                    :style="[btnBgColor]"
                    >{{ $t(`购买`) }}</view
                  >
                  <view
                    class="rd-24rpx w-44 h-44"
                    :style="[btnTextColor]"
                    v-else-if="btnStyle == 1"
                  >
                    <view class="flex-center cart-btn">
                      <text class="iconfont icon-ic_increase-2 fs-26"></text>
                    </view>
                  </view>
                  <view
                    class="rd-24rpx w-44 h-44"
                    :style="[btnTextColor]"
                    v-else
                  >
                    <view class="flex-center cart-btn">
                      <text class="iconfont icon-ic_ShoppingCart1 fs-26"></text>
                    </view>
                  </view>
                </view>
              </view>
              <view v-else>
                <view class="flex-y-center mt-4 pb-10">
                  <baseMoney
                    :money="item.price"
                    symbolSize="24"
                    integerSize="40"
                    decimalSize="24"
                    weight
                    :color="priceColor"
                    v-if="checkboxInfo.includes(2)"
                  ></baseMoney>
                  <view
                    class="flex h-26 lh-28rpx rd-14rpx bg--w111-F7E9CD fs-22 ml-8"
                    v-if="
                      Number(item.vip_price) > 0 && checkboxInfo.includes(5)
                    "
                  >
                    <text
                      class="inline-block h-26 lh-28rpx svip_rd fs-18 bg--w111-484643 text--w111-FDDAA4 px-8"
                      >SVIP</text
                    >
                    <text class="px-8 fs-22 SemiBold"
                      >{{ $t(`¥`) }}{{ item.vip_price }}</text
                    >
                  </view>
                </view>
                <view class="flex justify-between items-end relative">
                  <view class="flex-y-center">
                    <text
                      class="fs-22"
                      v-if="checkboxInfo.includes(3)"
                      :style="[uniStyle]"
                      >{{ $t(`已售`) }}{{ item.sales
                      }}{{ item.unit_name }}</text
                    >
                    <text
                      class="fs-22 text--w111-999 pl-16"
                      v-if="checkboxInfo.includes(4)"
                      >评分{{ item.star || 0 }}</text
                    >
                  </view>
                  <view
                    class="absolute right-0 bottom-0"
                    @tap.stop="addCartChange(item, index)"
                    v-if="!showBtn"
                  >
                    <view
                      class="w-96 h-56 rd-28rpx flex-center fs-24 text--w111-fff"
                      v-if="btnStyle == 0"
                      :style="[btnBgColor]"
                      >{{ $t(`购买`) }}</view
                    >
                    <view
                      class="rd-24rpx w-44 h-44"
                      :style="[btnTextColor]"
                      v-else-if="btnStyle == 1"
                    >
                      <view class="flex-center cart-btn">
                        <text class="iconfont icon-ic_increase-2 fs-26"></text>
                      </view>
                    </view>
                    <view
                      class="rd-24rpx w-44 h-44"
                      :style="[btnTextColor]"
                      v-else
                    >
                      <view class="flex-center cart-btn">
                        <text
                          class="iconfont icon-ic_ShoppingCart1 fs-26"
                        ></text>
                      </view>
                    </view>
                  </view>
                </view>
              </view>
            </view>
          </view>
        </view>
        <!-- 两列瀑布流 -->
        <view class="wf-page" v-if="goodStyleConfig == 1">
          <!-- left -->
          <view class="item-box">
            <view id="left" v-if="leftList.length">
              <view
                v-for="(item, index) in leftList"
                :key="index"
                class="wf-item"
                @tap="goDetail(item)"
                :style="[bgColor, bgRadius]"
              >
                <view class="pictrue">
                  <easy-loadimage
                    mode="widthFix"
                    :image-src="item.image"
                    width="100%"
                    height="346rpx"
                    :borderRadius="imgStyle"
                  ></easy-loadimage>
                </view>
                <view class="info_box" :style="[bgRadius2]">
                  <view
                    class="w-full line2 fs-28 text--w111-333 lh-40rpx"
                    v-if="checkboxInfo.includes(0)"
                    :style="[productStyle]"
                  >
                    <text v-if="item.brand_name" class="brand-tag">{{
                      item.brand_name
                    }}</text>
                    {{ item.store_name }}
                  </view>
                  <view
                    class="flex items-end flex-wrap mt-8 w-full"
                    v-if="
                      checkboxInfo.includes(1) &&
                      item.label_list &&
                      item.label_list.length
                    "
                  >
                    <BaseTag
                      :text="label.name"
                      :color="label.font_color"
                      :background="label.bg_color"
                      :borderColor="label.border_color"
                      :circle="label.border_color ? true : false"
                      :imgSrc="label.image"
                      v-for="(label, idx) in item.label_list"
                      :key="idx"
                    ></BaseTag>
                  </view>
                  <view class="flex-between-center mt-20" v-if="onlyShowPrice">
                    <baseMoney
                      :money="item.price"
                      symbolSize="24"
                      integerSize="40"
                      decimalSize="24"
                      weight
                      :color="priceColor"
                    ></baseMoney>
                    <view
                      @tap.stop="addCartChange(item, index)"
                      v-if="!showBtn"
                    >
                      <view
                        class="rd-24rpx w-44 h-44"
                        :style="[btnTextColor]"
                        v-if="btnStyle == 0"
                      >
                        <view class="flex-center cart-btn">
                          <text
                            class="iconfont icon-ic_increase-2 fs-26"
                          ></text>
                        </view>
                      </view>
                      <view
                        class="rd-24rpx w-44 h-44"
                        :style="[btnTextColor]"
                        v-else
                      >
                        <view class="flex-center cart-btn">
                          <text
                            class="iconfont icon-ic_ShoppingCart1 fs-26"
                          ></text>
                        </view>
                      </view>
                    </view>
                  </view>
                  <view v-else >
                    <view class="flex-y-center flex-no-wrap mt-8">
                      <baseMoney
                        :money="item.price"
                        symbolSize="24"
                        integerSize="40"
                        decimalSize="24"
                        weight
                        :color="priceColor"
                        v-if="checkboxInfo.includes(2)"
                      ></baseMoney>
                      <view
                        class="inline-block white-nowrap h-26 lh-28rpx rd-14rpx bg--w111-F7E9CD fs-22 ml-8"
                        v-if="
                          Number(item.vip_price) > 0 && checkboxInfo.includes(5)
                        "
                      >
                        <text
                          class="inline-block h-26 lh-28rpx svip_rd fs-18 bg--w111-484643 text--w111-FDDAA4 px-8"
                          >SVIP</text
                        >
                        <text class="px-8 fs-22 SemiBold"
                          >{{ $t(`¥`) }}{{ item.vip_price }}</text
                        >
                      </view>
                    </view>
                    <view class="flex-between-center mt-20">
                      <text
                        class="fs-22"
                        v-if="checkboxInfo.includes(3)"
                        :style="[uniStyle]"
                        >{{ $t(`已售`) }}{{ item.sales
                        }}{{ item.unit_name }}</text
                      >
                      <text v-else></text>
                      <view
                        @tap.stop="addCartChange(item, index)"
                        v-if="!showBtn"
                      >
                        <view
                          class="rd-24rpx w-44 h-44"
                          :style="[btnTextColor]"
                          v-if="btnStyle == 0"
                        >
                          <view class="flex-center cart-btn">
                            <text
                              class="iconfont icon-ic_increase-2 fs-26"
                            ></text>
                          </view>
                        </view>
                        <view
                          class="rd-24rpx w-44 h-44"
                          :style="[btnTextColor]"
                          v-else
                        >
                          <view class="flex-center cart-btn">
                            <text
                              class="iconfont icon-ic_ShoppingCart1 fs-26"
                            ></text>
                          </view>
                        </view>
                      </view>
                    </view>
                  </view>
                </view>
              </view>
            </view>
          </view>
          <!-- right -->
          <view class="item-box">
            <view id="right" v-if="rightList.length">
              <view
                v-for="(item, index) in rightList"
                :key="index"
                class="wf-item"
                @tap="goDetail(item)"
                :style="[bgColor, bgRadius]"
              >
                <view class="pictrue">
                  <easy-loadimage
                    mode="widthFix"
                    :image-src="item.image"
                    width="100%"
                    height="346rpx"
                    :borderRadius="imgStyle"
                  ></easy-loadimage>
                </view>
                <view class="info_box" :style="[bgRadius2]">
                  <view
                    class="w-full line2 fs-28 text--w111-333 lh-40rpx"
                    v-if="checkboxInfo.includes(0)"
                    :style="[productStyle]"
                  >
                    <text v-if="item.brand_name" class="brand-tag">{{
                      item.brand_name
                    }}</text>
                    {{ item.store_name }}
                  </view>
                  <view
                    class="flex items-end flex-wrap mt-8 w-full"
                    v-if="
                      checkboxInfo.includes(1) &&
                      item.label_list &&
                      item.label_list.length
                    "
                  >
                    <BaseTag
                      :text="label.name"
                      :color="label.font_color"
                      :background="label.bg_color"
                      :borderColor="label.border_color"
                      :circle="label.border_color ? true : false"
                      :imgSrc="label.image"
                      v-for="(label, idx) in item.label_list"
                      :key="idx"
                    ></BaseTag>
                  </view>
                  <view class="flex-between-center mt-20" v-if="onlyShowPrice">
                    <baseMoney
                      :money="item.price"
                      symbolSize="24"
                      integerSize="40"
                      decimalSize="24"
                      weight
                      :color="priceColor"
                    ></baseMoney>
                    <view
                      @tap.stop="addCartChange(item, index)"
                      v-if="!showBtn"
                    >
                      <view
                        class="rd-24rpx w-44 h-44"
                        :style="[btnTextColor]"
                        v-if="btnStyle == 0"
                      >
                        <view class="flex-center cart-btn">
                          <text
                            class="iconfont icon-ic_increase-2 fs-26"
                          ></text>
                        </view>
                      </view>
                      <view
                        class="rd-24rpx w-44 h-44"
                        :style="[btnTextColor]"
                        v-else
                      >
                        <view class="flex-center cart-btn">
                          <text
                            class="iconfont icon-ic_ShoppingCart1 fs-26"
                          ></text>
                        </view>
                      </view>
                    </view>
                  </view>
                  <view v-else>
                    <view class="flex-y-center mt-8">
                      <baseMoney
                        :money="item.price"
                        symbolSize="24"
                        integerSize="40"
                        decimalSize="24"
                        weight
                        :color="priceColor"
                        v-if="checkboxInfo.includes(2)"
                      ></baseMoney>
                      <view
                        class="flex h-26 lh-28rpx rd-14rpx bg--w111-F7E9CD fs-22 ml-8"
                        v-if="
                          Number(item.vip_price) > 0 && checkboxInfo.includes(5)
                        "
                      >
                        <text
                          class="inline-block h-26 lh-28rpx svip_rd fs-18 bg--w111-484643 text--w111-FDDAA4 px-8"
                          >SVIP</text
                        >
                        <text class="px-8 fs-22 SemiBold"
                          >{{ $t(`¥`) }}{{ item.vip_price }}</text
                        >
                      </view>
                    </view>
                    <view class="flex-between-center mt-20">
                      <text
                        class="fs-22"
                        v-if="checkboxInfo.includes(3)"
                        :style="[uniStyle]"
                        >{{ $t(`已售`) }}{{ item.sales
                        }}{{ item.unit_name }}</text
                      >
                      <text v-else></text>
                      <view
                        @tap.stop="addCartChange(item, index)"
                        v-if="!showBtn"
                      >
                        <view
                          class="rd-24rpx w-44 h-44"
                          :style="[btnTextColor]"
                          v-if="btnStyle == 0"
                        >
                          <view class="flex-center cart-btn">
                            <text
                              class="iconfont icon-ic_increase-2 fs-26"
                            ></text>
                          </view>
                        </view>
                        <view
                          class="rd-24rpx w-44 h-44"
                          :style="[btnTextColor]"
                          v-else
                        >
                          <view class="flex-center cart-btn">
                            <text
                              class="iconfont icon-ic_ShoppingCart1 fs-26"
                            ></text>
                          </view>
                        </view>
                      </view>
                    </view>
                  </view>
                </view>
              </view>
            </view>
          </view>
        </view>
        <!-- 两列展示(横向) -->
        <view
          class="pt-32 pr-24 pb-32 pl-24 bg--w111-fff"
          :style="[bgRadius, bgColor]"
          v-if="goodStyleConfig == 3 && dataConfig.name !== 'goodRecommend'"
        >
          <view class="grid-column-2 grid-gap-20rpx">
            <view
              class="flex"
              v-for="(item, index) in tempArr"
              :key="index"
              @tap="goDetail(item)"
              :style="[bgRadius]"
            >
              <easy-loadimage
                mode="widthFix"
                :image-src="item.image"
                width="144rpx"
                height="144rpx"
                :borderRadius="imgStyle"
              ></easy-loadimage>
              <view class="flex-1 pl-20">
                <view
                  class="w-full fs-26 h-72 lh-36rpx line2 mb-20"
                  v-if="checkboxInfo.includes(0)"
                  :style="[productStyle]"
                >
                  <text v-if="item.brand_name" class="brand-tag">{{
                    item.brand_name
                  }}</text>
                  {{ item.store_name }}
                </view>
                <baseMoney
                  :money="item.price"
                  symbolSize="24"
                  integerSize="40"
                  decimalSize="24"
                  weight
                  :color="priceColor"
                  v-if="checkboxInfo.includes(2)"
                ></baseMoney>
              </view>
            </view>
          </view>
        </view>
        <!-- 三列 -->
        <view
          class="pt-32 pr-24 pb-32 pl-24 bg--w111-fff"
          :style="[bgColor, bgRadius]"
          v-if="goodStyleConfig == 2"
        >
          <view class="grid-column-3 grid-gap-20rpx">
            <view
              v-for="(item, index) in tempArr"
              :key="index"
              @tap="goDetail(item)"
              :style="[bgRadius]"
            >
              <easy-loadimage
                mode="widthFix"
                :image-src="item.image"
                width="100%"
                height="210rpx"
                :borderRadius="imgStyle"
              ></easy-loadimage>
              <view
                class="w-full fs-28 h-80 lh-40rpx line2 mt-20"
                v-if="checkboxInfo.includes(0)"
                :style="[productStyle]"
              >
                <text v-if="item.brand_name" class="brand-tag">{{
                  item.brand_name
                }}</text>
                {{ item.store_name }}
              </view>
              <view class="flex-between-center mt-14">
                <baseMoney
                  :money="item.price"
                  symbolSize="24"
                  integerSize="40"
                  decimalSize="24"
                  weight
                  :color="priceColor"
                  v-if="checkboxInfo.includes(2)"
                ></baseMoney>
                <view @tap.stop="addCartChange(item, index)" v-if="!showBtn">
                  <view
                    class="rd-24rpx w-44 h-44"
                    :style="[btnTextColor]"
                    v-if="btnStyle == 0"
                  >
                    <view class="flex-center cart-btn">
                      <text class="iconfont icon-ic_increase-2 fs-26"></text>
                    </view>
                  </view>
                  <view
                    class="rd-24rpx w-44 h-44"
                    :style="[btnTextColor]"
                    v-else
                  >
                    <view class="flex-center cart-btn">
                      <text class="iconfont icon-ic_ShoppingCart1 fs-26"></text>
                    </view>
                  </view>
                </view>
              </view>
            </view>
          </view>
        </view>
        <!-- 大图展示 -->
        <view v-if="goodStyleConfig == 4">
          <view
            class="w-full bg--w111-fff item"
            :style="[bgColor, bgRadius]"
            v-for="(item, index) in tempArr"
            :key="index"
            @tap="goDetail(item)"
          >
            <easy-loadimage
              mode="widthFix"
              :image-src="item.image"
              width="100%"
              height="360rpx"
              :borderRadius="imgStyle"
            ></easy-loadimage>
            <view class="p-24">
              <view
                class="w-full line1 fs-28 text--w111-333 lh-40rpx"
                v-if="checkboxInfo.includes(0)"
                :style="[productStyle]"
              >
                <text v-if="item.brand_name" class="brand-tag">{{
                  item.brand_name
                }}</text>
                {{ item.store_name }}
              </view>
              <view
                class="flex items-end flex-wrap mt-8 w-full"
                v-if="
                  checkboxInfo.includes(1) &&
                  item.label_list &&
                  item.label_list.length
                "
              >
                <BaseTag
                  :text="label.name"
                  :color="label.font_color"
                  :background="label.bg_color"
                  :borderColor="label.border_color"
                  :circle="label.border_color ? true : false"
                  :imgSrc="label.image"
                  v-for="(label, idx) in item.label_list"
                  :key="idx"
                ></BaseTag>
              </view>
              <view class="flex-between-center" v-if="onlyShowPrice">
                <baseMoney
                  :money="item.price"
                  symbolSize="24"
                  integerSize="40"
                  decimalSize="24"
                  weight
                  :color="priceColor"
                ></baseMoney>
                <view @tap.stop="addCartChange(item, index)" v-if="!showBtn">
                  <view
                    class="w-96 h-56 rd-28rpx flex-center fs-24 text--w111-fff"
                    v-if="btnStyle == 0"
                    :style="[btnBgColor]"
                    >{{ $t(`购买`) }}</view
                  >
                  <view
                    class="rd-24rpx w-44 h-44"
                    :style="[btnTextColor]"
                    v-else-if="btnStyle == 1"
                  >
                    <view class="flex-center cart-btn">
                      <text class="iconfont icon-ic_increase-2 fs-26"></text>
                    </view>
                  </view>
                  <view
                    class="rd-24rpx w-44 h-44"
                    :style="[btnTextColor]"
                    v-else
                  >
                    <view class="flex-center cart-btn">
                      <text class="iconfont icon-ic_ShoppingCart1 fs-26"></text>
                    </view>
                  </view>
                </view>
              </view>
              <view v-else>
                <view class="flex-y-center mt-8">
                  <baseMoney
                    :money="item.price"
                    symbolSize="24"
                    integerSize="40"
                    decimalSize="24"
                    weight
                    :color="priceColor"
                    v-if="checkboxInfo.includes(2)"
                  ></baseMoney>
                  <view
                    class="flex h-26 lh-28rpx rd-14rpx bg--w111-F7E9CD fs-22 ml-8"
                    v-if="
                      Number(item.vip_price) > 0 && checkboxInfo.includes(5)
                    "
                  >
                    <text
                      class="inline-block h-26 lh-28rpx svip_rd fs-18 bg--w111-484643 text--w111-FDDAA4 px-8"
                      >SVIP</text
                    >
                    <text class="px-8 fs-22 SemiBold"
                      >{{ $t(`¥`) }}{{ item.vip_price }}</text
                    >
                  </view>
                </view>
                <view class="flex justify-between items-end">
                  <view class="flex-y-center">
                    <text
                      class="fs-22 text--w111-999"
                      v-if="checkboxInfo.includes(3)"
                      :style="[uniStyle]"
                      >{{ $t(`已售`) }}{{ item.sales
                      }}{{ item.unit_name }}</text
                    >
                    <text
                      class="fs-22 text--w111-999 pl-16"
                      v-if="checkboxInfo.includes(4)"
                      >{{ $t(`评分`) }}{{ item.star || 0 }}</text
                    >
                  </view>
                  <view @tap.stop="addCartChange(item, index)" v-if="!showBtn">
                    <view
                      class="w-96 h-56 rd-28rpx flex-center fs-24 text--w111-fff"
                      v-if="btnStyle == 0"
                      :style="[btnBgColor]"
                      >{{ $t(`购买`) }}</view
                    >
                    <view
                      class="rd-24rpx w-44 h-44"
                      :style="[btnTextColor]"
                      v-else-if="btnStyle == 1"
                    >
                      <view class="flex-center cart-btn">
                        <text class="iconfont icon-ic_increase-2 fs-26"></text>
                      </view>
                    </view>
                    <view
                      class="rd-24rpx w-44 h-44"
                      :style="[btnTextColor]"
                      v-else
                    >
                      <view class="flex-center cart-btn">
                        <text
                          class="iconfont icon-ic_ShoppingCart1 fs-26"
                        ></text>
                      </view>
                    </view>
                  </view>
                </view>
              </view>
            </view>
          </view>
        </view>
        <!-- 横向滑动 -->
        <view
          class="pt-32 pb-32 pl-24 bg--w111-fff"
          :style="[bgRadius, bgColor]"
          v-if="
            goodStyleConfig == 5 ||
            (goodStyleConfig == 3 && dataConfig.name === 'goodRecommend')
          "
        >
          <scroll-view
            scroll-x="true"
            show-scrollbar="false"
            class="goods-scroll-view w-full"
          >
            <view class="goods-scroll-row">
              <view
                class="goods-scroll-item"
                v-for="(item, index) in tempArr"
                :key="index"
                @tap="goDetail(item)"
                :style="[bgRadius]"
              >
                <easy-loadimage
                  mode="widthFix"
                  :image-src="item.image"
                  width="200rpx"
                  height="200rpx"
                  :borderRadius="imgStyle"
                ></easy-loadimage>
                <view
                  class="w-200 fs-28 h-80 lh-40rpx line2 break_word mt-20"
                  v-if="checkboxInfo.includes(0)"
                  :style="[productStyle]"
                >
                  <text v-if="item.brand_name" class="brand-tag">{{
                    item.brand_name
                  }}</text>
                  {{ item.store_name }}
                </view>
                <view class="flex-between-center mt-8">
                  <baseMoney
                    :money="item.price"
                    symbolSize="24"
                    integerSize="40"
                    decimalSize="24"
                    weight
                    :color="priceColor"
                    v-if="checkboxInfo.includes(2)"
                  ></baseMoney>
                  <view @tap.stop="addCartChange(item, index)" v-if="!showBtn">
                    <view
                      class="rd-24rpx w-44 h-44"
                      :style="[btnTextColor]"
                      v-if="btnStyle == 0"
                    >
                      <view class="flex-center cart-btn">
                        <text class="iconfont icon-ic_increase-2 fs-26"></text>
                      </view>
                    </view>
                    <view
                      class="rd-24rpx w-44 h-44"
                      :style="[btnTextColor]"
                      v-else
                    >
                      <view class="flex-center cart-btn">
                        <text class="iconfont icon-ic_ShoppingCart1 fs-26"></text>
                      </view>
                    </view>
                  </view>
                </view>
              </view>
            </view>
          </scroll-view>
        </view>
      </view>
    </common-wrapper>
    <productWindow
      :attr="attr"
      :isShow="1"
      :iSplus="1"
      :iScart="1"
      :fangda="false"
      type="2"
      :limitNum="storeInfo.limit_num"
      :minQty="storeInfo.min_qty"
      :unitName="storeInfo.unit_name"
      @myevent="onMyEvent"
      @ChangeAttr="ChangeAttr"
      @ChangeCartNum="ChangeCartNumDuo"
      @attrVal="attrVal"
      @iptCartNum="iptCartNum"
      @goCat="goCatNum"
      id="product-window"
    ></productWindow>
  </view>
</template>

<script setup>
import { ref, computed, watch, nextTick, getCurrentInstance, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { getProductslist, getAttr, postCartNum } from "@/api/store.js";
import { cartDel } from "@/api/order.js";
import { getCartCounts } from "@/api/order.js";
import { toLogin } from "@/libs/login.js";
import { useAppStore } from "@/store/modules/app";
import { useIndexDataStore } from "@/store/modules/indexData";
import { goShopDetail } from "@/libs/order.js";
import productWindow from "@/components/productWindow";
import commonWrapper from "./commonWrapper.vue";
import util from "@/utils/util.js";
import { LIMIT } from "@/config/app";

defineOptions({ name: "goodList" });

const emit = defineEmits(["detail"]);

const props = defineProps({
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
  list: {
    type: Array,
    default: () => [],
  },
  isSortType: {
    type: [String, Number],
    default: 0,
  },
});

const appStore = useAppStore();
const indexDataStore = useIndexDataStore();
const { isLogin, uid } = storeToRefs(appStore);
const { cartNum } = storeToRefs(indexDataStore);

// ============================== 组件自身 data ==============================
const tempArr = ref([]);
const type = ref(0);
const attr = ref({
  cartAttr: false,
  productAttr: [],
  productSelect: {},
});
const id = ref(0);
const productValue = ref([]);
const attrValue = ref(""); //已选属性
const storeName = ref(""); //多属性产品名称
const storeInfo = ref({});
const allList = ref([]); // 全部列表
const leftList = ref([]); // 左边列表
const rightList = ref([]); // 右边列表
const mark = ref(0); // 列表标记
const boxHeight = ref([]); // 下标0和1分别为左列和右列高度

// ================ mixins/skuSelect.js 内联（唯一消费方，mixin 已拆除） ================
// 遗留判断：以下变量在原 mixin/组件中从未声明（this.totalPrice 等恒为 undefined 的实例属性），
// 以同名 undefined 变量保持同等语义（相关分支现代码路径不可达，见 ChangeSubDel 等）
var totalPrice, page, loadend, cartData, updateNum, cart_num;

watch(
  () => props.list,
  (val) => {
    if (val && val.length) tempArr.value = val;
  },
  { deep: true }
);
// 监听列表数据变化
watch(
  tempArr,
  (nVal, oVal) => {
    // 如果数据为空或新的列表数据少于旧的列表数据（通常为下拉刷新或切换排序或使用筛选器），初始化变量
    if (
      !tempArr.value.length ||
      (tempArr.value.length === updateNum &&
        tempArr.value.length <= allList.value.length)
    ) {
      allList.value = [];
      leftList.value = [];
      rightList.value = [];
      boxHeight.value = [];
      mark.value = 0;
    }
    // 如果列表有值，调用waterfall方法

    if (tempArr.value.length) {
      allList.value = tempArr.value;
      leftList.value = [];
      rightList.value = [];
      boxHeight.value = [];
      allList.value.forEach((v, i) => {
        if (
          allList.value.length < 3 ||
          (allList.value.length <= 7 && allList.value.length - i > 1) ||
          (allList.value.length > 7 && allList.value.length - i > 2)
        ) {
          if (i % 2) {
            rightList.value.push(v);
          } else {
            leftList.value.push(v);
          }
        }
      });
      if (allList.value.length < 3) {
        mark.value = allList.value.length + 1;
      } else if (allList.value.length <= 7) {
        mark.value = allList.value.length - 1;
      } else {
        mark.value = allList.value.length - 2;
      }
      if (mark.value < allList.value.length) {
        waterFall();
      }
    }
  },
  { immediate: true, deep: true }
);
// 监听标记，当标记发生变化，则执行下一个item排序
watch(mark, () => {
  const len = allList.value.length;
  if (mark.value < len && mark.value !== 0 && boxHeight.value.length) {
    waterFall();
  }
});
watch(
  () => props.dataConfig,
  () => {
    productslist();
  }
);

// ============================== computed ==============================
const showHeader = computed(() => {
  // You might want to control visibility based on config, but admin doesn't seem to have a global 'show header' switch for this component,
  // it just has header settings. Assuming always show if configured.
  return true;
});
const headerType = computed(() => {
  return props.dataConfig.headerType ? props.dataConfig.headerType.tabVal : 0;
});
const headerText = computed(() => {
  return props.dataConfig.headerText ? props.dataConfig.headerText.value : "";
});
const headerImg = computed(() => {
  return props.dataConfig.headerImg ? props.dataConfig.headerImg.url : "";
});
const headerBoxStyle = computed(() => {
  const alignMap = ["left", "center", "right"];
  const align = props.dataConfig.headerAlign
    ? alignMap[props.dataConfig.headerAlign.tabVal]
    : "left";
  return {
    "text-align": align,
    "margin-bottom": "20rpx",
    padding: "0 20rpx",
  };
});
const titleTextStyle = computed(() => {
  const alignMap = ["left", "center", "right"];
  const align = props.dataConfig.headerAlign
    ? alignMap[props.dataConfig.headerAlign.tabVal]
    : "left";
  const color =
    props.dataConfig.headerColor &&
    props.dataConfig.headerColor.color &&
    props.dataConfig.headerColor.color[0]
      ? props.dataConfig.headerColor.color[0].item
      : "#333";
  const fontSize = props.dataConfig.headerFontSize
    ? props.dataConfig.headerFontSize.val * 2 + "rpx"
    : "32rpx";
  const fontWeight =
    props.dataConfig.headerTextConfig &&
    props.dataConfig.headerTextConfig.tabVal == 0
      ? "bold"
      : "normal";
  const fontStyle =
    props.dataConfig.headerTextConfig &&
    props.dataConfig.headerTextConfig.tabVal == 2
      ? "italic"
      : "normal";

  return {
    color: color,
    "font-size": fontSize,
    "font-weight": fontWeight,
    "font-style": fontStyle,
    "text-align": align,
  };
});
const titleImgBoxStyle = computed(() => {
  const alignMap = ["left", "center", "right"];
  const align = props.dataConfig.headerAlign
    ? alignMap[props.dataConfig.headerAlign.tabVal]
    : "left";
  return {
    "text-align": align,
  };
});
const titleImgStyle = computed(() => {
  return {
    height: "auto",
    display: "inline-block", // inline-block allows text-align on parent to work
  };
});
const configData = computed(() => {
  return {
    ...props.dataConfig,
  };
});
const bgRadius = computed(() => {
  let borderRadius = `${props.dataConfig.fillet.val * 2}rpx`;
  if (props.dataConfig.fillet.type) {
    borderRadius = `${props.dataConfig.fillet.valList[0].val * 2}rpx ${
      props.dataConfig.fillet.valList[1].val * 2
    }rpx ${props.dataConfig.fillet.valList[3].val * 2}rpx ${
      props.dataConfig.fillet.valList[2].val * 2
    }rpx`;
  }
  return {
    borderRadius: borderRadius,
  };
});
const bgRadius2 = computed(() => {
  let borderRadius = `0 0 ${props.dataConfig.fillet.val * 2}rpx ${
    props.dataConfig.fillet.val * 2
  }rpx`;
  if (props.dataConfig.fillet.type) {
    borderRadius = `0 0 ${props.dataConfig.fillet.valList[3].val * 2}rpx ${
      props.dataConfig.fillet.valList[2].val * 2
    }rpx`;
  }
  return {
    borderRadius: borderRadius,
  };
});
const bgColor = computed(() => {
  const moduleColor = props.dataConfig.moduleColor;
  const colors = moduleColor && moduleColor.color ? moduleColor.color : [];
  const left = colors[0] && colors[0].item ? colors[0].item : "#fff";
  const right = colors[1] && colors[1].item ? colors[1].item : left;
  return {
    background: `linear-gradient(90deg, ${left} 0%, ${right} 100%)`,
  };
});
const styleConfig = computed(() => {
  return props.dataConfig.styleConfig.tabVal;
});
/*商品图片圆角样式*/
const imgStyle = computed(() => {
  let borderRadius = `${props.dataConfig.filletImg.val * 2}rpx`;
  if (props.dataConfig.styleConfig.tabVal == 1) {
    borderRadius = `${props.dataConfig.filletImg.val * 2}rpx ${
      props.dataConfig.filletImg.val * 2
    }rpx 0 0`;
  }
  if (props.dataConfig.filletImg.type) {
    borderRadius = `${props.dataConfig.filletImg.valList[0].val * 2}rpx ${
      props.dataConfig.filletImg.valList[1].val * 2
    }rpx ${props.dataConfig.filletImg.valList[3].val * 2}rpx ${
      props.dataConfig.filletImg.valList[2].val * 2
    }rpx`;
    if (props.dataConfig.styleConfig.tabVal == 1) {
      borderRadius = `${props.dataConfig.filletImg.valList[0].val * 2}rpx ${
        props.dataConfig.filletImg.valList[1].val * 2
      }rpx 0 0`;
    }
  }
  let imgRadius = `${props.dataConfig.fillet.val * 2}rpx ${
    props.dataConfig.fillet.val * 2
  }rpx 0 0`;
  if (props.dataConfig.fillet.type) {
    imgRadius = `${props.dataConfig.fillet.valList[0].val * 2}rpx ${
      props.dataConfig.fillet.valList[1].val * 2
    }rpx 0 0`;
  }
  return props.dataConfig.name == "promotionList" ? imgRadius : borderRadius;
});
/*商品名称样式*/
const productStyle = computed(() => {
  return {
    color: props.dataConfig.toneConfig.tabVal
      ? props.dataConfig.goodsNameColor.color[0].item
      : "#333",
    fontWeight: props.dataConfig.goodsName.tabVal ? "normal" : "bold",
  };
});
/* 展示信息 */
const checkboxInfo = computed(() => {
  return props.dataConfig.checkboxInfo.type;
});
/* 价格颜色 */
const priceColor = computed(() => {
  return props.dataConfig.toneConfig.tabVal
    ? props.dataConfig.goodsPriceColor.color[0].item
    : "var(--view-theme)";
});
/* 划线价颜色 */
const otPriceColor = computed(() => {
  return props.dataConfig.goodsPriceColor.color[0].item;
});
const btnStyle = computed(() => {
  return props.dataConfig.bntStyleConfig.tabVal;
});
const showBtn = computed(() => {
  return props.dataConfig.cartConfig.tabVal;
});
/* 按钮颜色 */
const btnBgColor = computed(() => {
  return {
    background: props.dataConfig.toneCartConfig.tabVal
      ? `linear-gradient(90deg,${props.dataConfig.bntBgColor.color[0].item} 0%,${props.dataConfig.bntBgColor.color[1].item} 100%)`
      : "linear-gradient(90deg, var(--view-theme) 0%, var(--view-gradient) 100%)",
  };
});
const btnTextColor = computed(() => {
  return {
    color: "#FFFFFF",
    background: props.dataConfig.toneCartConfig.tabVal
      ? `linear-gradient(90deg, ${props.dataConfig.bntBgColor.color[0].item} 0%, ${props.dataConfig.bntBgColor.color[1].item} 100%)`
      : "linear-gradient(90deg, var(--view-theme) 0%, var(--view-gradient) 100%)",
  };
});
const uniStyle = computed(() => {
  return {
    color: props.dataConfig.toneConfig.tabVal
      ? props.dataConfig.soldNumColor.color[0].item || "#999"
      : "#999",
  };
});
/*商品数量*/
const numberConfig = computed(() => {
  return props.dataConfig.numberConfig.val;
});
/*商品模板*/
const goodStyleConfig = computed(() => {
  return props.dataConfig.styleConfig.tabVal;
});
/*检索条件  0综合 1销量 2价格*/
const goodsSort = computed(() => {
  return props.dataConfig.goodsSort.tabVal;
});
/*按照什么方式选择商品 1 指定商品 3指定分类 4 商品标签 */
const typeConfig = computed(() => {
  return props.dataConfig.typeConfig.activeValue;
});
const bntConfig = computed(() => {
  return props.dataConfig.bntConfig.tabVal;
});
const onlyShowPrice = computed(() => {
  if (
    checkboxInfo.value.toString() == "0,2" ||
    checkboxInfo.value.toString() == "2,0" ||
    checkboxInfo.value.toString() == "2"
  ) {
    return true;
  } else {
    return false;
  }
});

const instance = getCurrentInstance();
onMounted(() => {
  productslist();
});

// ============================== methods（组件自身） ==============================
function observeVideo() {
  let observer = uni.createIntersectionObserver(instance.proxy, { observeAll: true });
  observer.relativeToViewport().observe(".video", (res) => {
    if (res.intersectionRatio) {
      uni.createVideoContext(res.id, instance.proxy).play();
    } else {
      uni.createVideoContext(res.id, instance.proxy).pause();
    }
  });
}
function productslist() {
  if (props.list && props.list.length) {
    tempArr.value = props.list;
    return;
  }
  let limit = LIMIT;
  let data = {};
  if (typeConfig.value == 1) {
    // 指定商品：保存后的配置只保留 goodsList.ids（ID 数组），goodsList.list 仅预览/旧数据存在，两者兼容
    const selectGoods = props.dataConfig.goodsList || {};
    const ids = (selectGoods.ids && selectGoods.ids.length
      ? selectGoods.ids
      : (selectGoods.list || []).map((item) => item.id)
    )
      .filter(Boolean)
      .join(",");
    if (ids) {
      data = { ids };
    } else {
      tempArr.value = [];
      return;
    }
  } else if (typeConfig.value == 3) {
    data = {
      priceOrder: goodsSort.value == 2 ? "desc" : "",
      salesOrder: goodsSort.value == 1 ? "desc" : "",
      cate_id: props.dataConfig.classList.classVal
        ? props.dataConfig.classList.classVal.join(",")
        : "",
      limit: numberConfig.value,
    };
  } else if (typeConfig.value == 4) {
    data = {
      priceOrder: goodsSort.value == 2 ? "desc" : "",
      salesOrder: goodsSort.value == 1 ? "desc" : "",
      store_label_id: props.dataConfig.goodsLabel && props.dataConfig.goodsLabel.activeValue
        ? props.dataConfig.goodsLabel.activeValue.join(",")
        : "",
      limit: numberConfig.value,
    };
  }
  getProductslist(data).then((res) => {
    tempArr.value = res.data;
  });
}
function goDetail(item) {
  goShopDetail(item, appStore.uid).then((res) => {
    uni.navigateTo({
      url: `/pages/goods_details/index?id=${item.id}`,
    });
  });
}
// 商品详情接口；
function getAttrs(goodsId) {
  getAttr(goodsId, 0).then((res) => {
    attr.value["productAttr"] = res.data.productAttr;
    productValue.value = res.data.productValue;
    storeInfo.value = res.data.storeInfo;
    DefaultSelect();
  });
}
function addCartChange(item, index) {
  if (bntConfig.value == 1) {
    if (item.spec_type) {
      goCartDuo(item);
    } else {
      goCartDan(item, index);
    }
  } else {
    goDetail(item);
  }
}
function getCartNum() {
  getCartCounts().then((res) => {
    indexDataStore.setCartNum(res.data.count);
  });
}
// 瀑布流排序
function waterFall() {
  const i = mark.value;
  if (i == 0) {
    // 初始化，从左边开始插入
    leftList.value.push(allList.value[i]);
    // 更新左边列表高度
    getViewHeight(0);
  } else if (i == 1) {
    // 第二个item插入，默认为右边插入
    rightList.value.push(allList.value[i]);
    // 更新右边列表高度
    getViewHeight(1);
  } else {
    // 根据左右列表高度判断下一个item应该插入哪边
    if (!boxHeight.value.length) {
      rightList.value.length < leftList.value.length
        ? rightList.value.push(allList.value[i])
        : leftList.value.push(allList.value[i]);
    } else {
      const leftOrRight = boxHeight.value[0] > boxHeight.value[1] ? 1 : 0;
      if (leftOrRight) {
        rightList.value.push(allList.value[i]);
      } else {
        leftList.value.push(allList.value[i]);
      }
    }
    // 更新插入列表高度
    getViewHeight();
  }
}
// 获取列表高度
function getViewHeight() {
  // 使用nextTick，确保页面更新结束后，再请求高度
  nextTick(() => {
    setTimeout(() => {
      uni
        .createSelectorQuery()
        .in(instance.proxy)
        .select("#right")
        .boundingClientRect((res) => {
          res ? (boxHeight.value[1] = res.height) : "";
          uni
            .createSelectorQuery()
            .in(instance.proxy)
            .select("#left")
            .boundingClientRect((res) => {
              res ? (boxHeight.value[0] = res.height) : "";
              mark.value = mark.value + 1;
            })
            .exec();
        })
        .exec();
    }, 100);
  });
}

// ================ 以下为 mixins/skuSelect.js 内联的方法（原 mixin 唯一消费方） ================
function updateFun(e, num) {
  if (e.cartNum) {
    tempArr.value.forEach((item) => {
      if (item.id == e.id) {
        item.cart_num = e.cartNum
      }
    })
    // 只有门店页面过来时调用
    if (num) {
      getCartNum();
    }
  }
}
/**
 * 默认选中属性
 *
 */
function DefaultSelect() {
  const productAttr = Array.isArray(attr.value.productAttr)
    ? attr.value.productAttr
    : [];
  const productValues = productValue.value && typeof productValue.value === "object"
    ? productValue.value
    : {};
  // Keep the actual SKU key. Single-spec products such as virtual goods use
  // keys like "默认", which must not be reconstructed from an empty selection.
  const availableSku = Object.entries(productValues).find(([, sku]) => Number(sku?.stock) > 0);
  const selectedKey = availableSku ? availableSku[0] : "";
  const productSelect = availableSku ? availableSku[1] : null;
  const value = selectedKey ? selectedKey.split(",") : [];

  for (let i = 0; i < productAttr.length; i++) {
    productAttr[i]["index"] = value[i];
  }

  attr.value.productSelect["store_name"] = storeName.value;
  if (productSelect && productAttr.length) {
    attr.value.productSelect["image"] = productSelect.image;
    attr.value.productSelect["price"] = productSelect.price;
    attr.value.productSelect["stock"] = productSelect.stock;
    attr.value.productSelect["unique"] = productSelect.unique;
    attr.value.productSelect["cart_num"] = storeInfo.value.min_qty;
    attr.value.productSelect['vip_price'] = productSelect.vip_price;
    attrValue.value = selectedKey;
  } else if (!productSelect && productAttr.length) {
    attr.value.productSelect["image"] = storeInfo.value.image;
    attr.value.productSelect["price"] = storeInfo.value.price;
    attr.value.productSelect["stock"] = 0;
    attr.value.productSelect["unique"] = "";
    attr.value.productSelect["cart_num"] = 0;
    attrValue.value = "";
    attr.value.productSelect['vip_price'] = storeInfo.value.vip_price;
  } else if (!productSelect && !productAttr.length) {
    attr.value.productSelect["image"] = storeInfo.value.image;
    attr.value.productSelect["price"] = storeInfo.value.price;
    attr.value.productSelect["stock"] = storeInfo.value.stock;
    attr.value.productSelect["unique"] = storeInfo.value.unique || "";
    attr.value.productSelect["cart_num"] = storeInfo.value.min_qty;
    attrValue.value = "";
    attr.value.productSelect['vip_price'] = storeInfo.value.vip_price;
  }
}
/**
 * 属性变动赋值
 *
 */
function ChangeAttr(res) {
  let productSelect = productValue.value[res];
  if (productSelect && productSelect.stock >= 0) {
    attr.value.productSelect["image"] = productSelect.image;
    attr.value.productSelect["price"] = productSelect.price;
    attr.value.productSelect["stock"] = productSelect.stock;
    attr.value.productSelect["unique"] = productSelect.unique;
    attr.value.productSelect['vip_price'] = productSelect.vip_price;
    attr.value.productSelect["cart_num"] = storeInfo.value.min_qty;
    attrValue.value = res;
  } else {
    attr.value.productSelect['image'] = storeInfo.value.image;
    attr.value.productSelect['price'] = storeInfo.value.price;
    attr.value.productSelect['stock'] = 0;
    attr.value.productSelect['unique'] = '';
    attr.value.productSelect['cart_num'] = 0;
    attr.value.productSelect['vip_price'] = storeInfo.value.vip_price;
    attrValue.value = '';
  }
}
function attrVal(val) {
  attr.value.productAttr[val.indexw]['index'] = attr.value.productAttr[val.indexw].attr_values[val
    .indexn];
}
/**
 * 购物车手动填写
 *
 */
function iptCartNum(e) {
  // attr.value.productSelect['cart_num'] = e;
  if (e) {
    let number = storeInfo.value.min_qty;
    if (Number.isInteger(parseInt(e)) && parseInt(e) >= storeInfo.value.min_qty) {
      number = parseInt(e);
    }
    nextTick((e) => {
      attr.value.productSelect['cart_num'] = e < 0 ? storeInfo.value.min_qty : number;
    });
  }
}
function onMyEvent() {
  attr.value['cartAttr'] = false;
}
// 改变多属性购物车
function ChangeCartNumDuo(changeValue) {
  //获取当前变动属性
  let productSelect = productValue.value[attrValue.value];
  //如果没有属性,赋值给商品默认库存
  if (productSelect === undefined && !attr.value.productAttr.length)
    productSelect = attr.value.productSelect;
  //无属性值即库存为0；不存在加减；
  if (productSelect === undefined) return;
  let stock = productSelect.stock || 0;
  let num = attr.value.productSelect;
  ChangeCartNum(changeValue, num, stock, 1);
}
// 改变单属性购物车
function ChangeCartNumDan(changeValue, index, item) {
  let num = tempArr.value[index];
  let stock = tempArr.value[index].stock;
  ChangeCartNum(changeValue, num, stock, 0, item.id);
}
function ChangeSubDel(event) {
  let list = cartData.cartList,
    ids = [];
  list.forEach(item => {
    ids.push(item.id)
  });
  cartDel(ids.join(",")).then(res => {
    cartData['cartList'] = [];
    cartData.iScart = false;
    totalPrice = 0.00;
    page = 1;
    loadend = false;
    tempArr.value = [];
    productslist();
    getCartNum();
  })
}
function ChangeOneDel(id, index) {
  let list = cartData.cartList;
  cartDel(id.toString()).then(res => {
    list.splice(index, 1);
    if (!list.length) {
      cartData.iScart = false;
      page = 1;
      loadend = false;
      tempArr.value = [];
      productslist();
    };
    getCartNum();
  })
}
// 多规格加入购物车；
function goCatNum() {
  goCat(1, id.value, 1);
}
function closeList(e) {
  cartData['iScart'] = e;
}
// 已经加入购物车时的购物加减；
function ChangeCartList(changeValue, index) {
  let list = cartData.cartList;
  let num = list[index];
  let stock = list[index].trueStock;
  ChangeCartNum(changeValue, num, stock, 0, num.product_id, index, 1);
  if (!list.length) {
    cartData.iScart = false;
    page = 1;
    loadend = false;
    tempArr.value = [];
    productslist();
  }
}
// 购物车加减计算函数
function ChangeCartNum(changeValue, num, stock, isDuo, id, index, cart) {
  if (changeValue) {
    num.cart_num++;
    if (num.cart_num > stock) {
      if (isDuo) {
        attr.value.productSelect['cart_num'] = stock ? stock : storeInfo.value.min_qty;
        cart_num = stock ? stock : 1;
      } else {
        num.cart_num = stock ? stock : 0;
        tempArr.value = tempArr.value;
        cartData['cartList'] = cartData.cartList;
      }
      return util.Tips({
        title: "该产品没有更多库存了"
      });
    } else {
      if (!isDuo) {
        if (cart) {
          goCat(0, id, 1, 1, num.product_attr_unique);
        } else {
          goCat(0, id, 1);
        }
      }
    }
  } else {
    num.cart_num--;
    if (num.cart_num == 0) {
      cartData.cartList.splice(index, 1);
      if (isDuo) {
      attr.value.productSelect['cart_num'] = storeInfo.value.min_qty;
      cart_num = 1;
      }
    }
    if (num.cart_num < 0) {
      if (isDuo) {
        attr.value.productSelect['cart_num'] = storeInfo.value.min_qty;
        cart_num = 1;
      } else {
        num.cart_num = 0;
        tempArr.value = tempArr.value;
        cartData['cartList'] = cartData.cartList;
      }
    } else {
      if (!isDuo) {
        if (cart) {
          goCat(0, id, 0, 1, num.product_attr_unique);
        } else {
          goCat(0, id, 0);
        }
      }
    }
  }
  tempArr.value.forEach((item)=>{
    if(item.id == id){
      item.cart_num = num.cart_num;
    }
  })
}
/*
 * 加入购物车
 */
function goCat(duo, goodsId, type, cart, unique) {
  if (duo) {
    let productSelect = productValue.value[attrValue.value];
    //如果有属性,没有选择,提示用户选择

    if (
      attr.value.productAttr.length &&
      productSelect === undefined
    ) {
      return util.Tips({
        title: "产品库存不足，请选择其它属性"
      });
    }
  }
  let q = {
    product_id: goodsId,
    num: duo ? attr.value.productSelect.cart_num : 1,
    type: type,
    unique: duo ? attr.value.productSelect.unique : cart ? unique : ""
  };

  postCartNum(q)
    .then(function(res) {
      if (duo) {
        attr.value.cartAttr = false;
        // page = 1;
        // loadend = false;
        tempArr.value.forEach((item, index) => {
          if (item.id == id.value) {
            let arrtStock = attr.value.productSelect.stock
            let objNum = parseInt(item.cart_num) + parseInt(attr.value.productSelect.cart_num);
            item.cart_num = objNum > arrtStock?arrtStock:objNum
          }
        })
        // productslist();
      }

      util.Tips({
        title: res.msg || '加入购物车成功'
      });

      getCartNum();
      // DIY 商品列表无分类页悬浮购物车，不调用 getCartList
    })
    .catch(err => {
      return util.Tips({
        title: (err && (err.msg || err.message)) || err
      });
    });
}
function goCartDuo(item, num) {
  if (!isLogin.value) {
    toLogin();
  } else {
    // 预售 / 自定义表单 / direct 模式：列表不支持加购，进详情购买
    if (item.cart_button == 0) {
      //num:表示从首页点击进来的
      let page1 = `/pages/goods_details/index?id=${item.id}&fromType=1`;
      let page2 = `/pages/goods_details/index?id=${item.id}`;
      uni.navigateTo({
        url: num ? page2 : page1
      });
    } else {
      storeName.value = item.store_name;
      getAttrs(item.id);
      id.value = item.id;
      attr.value['cartAttr'] = true;
    }
  }
}
// 点击默认单属性购物车
function goCartDan(item, index, num) {
  if (!isLogin.value) {
    toLogin();
  } else {
    // 预售 / 自定义表单 / direct 模式：列表不支持加购，进详情购买
    if (item.cart_button == 0) {
      uni.navigateTo({
        url: `/pages/goods_details/index?id=${item.id}&fromType=1`
      });
    } else {
      tempArr.value[index].cart_num = 1;
      // num表示从门店过来的；
      // if(num){
      // 	indexDataStore.setCartNum(parseInt(cartNum.value)+1)
      // }
      tempArr.value = tempArr.value;
      goCat(0, item.id, 1);
    }
  }
}
</script>

<style lang="scss" scoped>
$page-padding: 10px;
$grid-gap: 10px;
.wf-page {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  .item-box {
    width: 48.5%;
    margin-bottom: 20rpx;
  }
}
.wf-item {
  width: 100%;
  margin-bottom: 20rpx;
}
.wf-page1 .wf-item {
  margin-top: 20rpx;
  border-radius: 20rpx;
  padding-bottom: 0;
}
.item ~ .item {
  margin-top: 20rpx;
}
.info_box {
  padding: 16rpx 20rpx;
  border-radius: 0 0 20rpx 20rpx;
}
.bg--w111-484643 {
  background: linear-gradient(90deg, #484643 0%, #1f1b17 100%);
}
.text--w111-FDDAA4 {
  color: #fddaa4;
}
.svip_rd {
  border-radius: 14rpx 0 8rpx 14rpx;
}
.cart-btn {
  // background:rgba(255,255,255,0.9);
  width: 100%;
  height: 100%;
}
.goods-scroll-view {
  white-space: nowrap;
}
.goods-scroll-row {
  display: inline-flex;
  flex-wrap: nowrap;
  align-items: flex-start;
  min-width: 100%;
}
.goods-scroll-item {
  flex: 0 0 200rpx;
  width: 200rpx;
  margin-right: 20rpx;
  white-space: normal;
}
</style>
