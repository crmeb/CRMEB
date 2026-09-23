<template>
  <common_wrapper :config="configObj">
    <div class="member-card" :class="'style' + (styleConfig + 1)">
      <!-- 信息样式 共五种 -->
      <template v-if="styleConfig == 0">
        <div
          class="card-header acea-row row-between-wrapper"
          :class="{
            'style-0': styleConfig == 0,
          }"
        >
          <div class="user-info acea-row row-middle">
            <div class="avatar">
              <img :src="logoConfig" v-if="logoConfig" />
              <div class="empty-box" v-else>
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
            <div class="text">
              <div class="name" :style="{ color: nameColor, fontSize: nameSize + 'px' }">这里是昵称</div>
              <!-- 可以切换显示手机号或ID -->
              <div v-if="userInfoConfig == 0" class="level" :style="{ color: numColor, fontSize: numSize + 'px' }">
                14512349876
              </div>
              <div v-else class="level" :style="{ color: numColor, fontSize: numSize + 'px' }">ID: 9527</div>
            </div>
          </div>
          <!-- 菜单入口 -->
          <div
            class="menu-entry"
            v-if="menuList.length"
            style="position: absolute; right: 10px; top: 14px; z-index: 10"
          >
            <div class="item" v-for="(item, index) in menuList" :key="index" :style="{ marginLeft: '10px' }">
              <template v-if="menuStyle == 0">
                <img :src="item.img" v-if="item.img" class="menu-img" />
              </template>
              <template v-else>
                <span
                  class="mb-iconfont"
                  :class="item.icon"
                  :style="{
                    display: 'inline-block',
                    color: iconStyle.color.color[0].item,
                    fontSize: iconStyle.size.val + 'px',
                    padding: iconStyle.padding.val + 'px',
                    transform: 'rotate(' + iconStyle.rotate.val + 'deg)',
                  }"
                  v-if="item.icon"
                ></span>
              </template>
            </div>
          </div>
        </div>
      </template>
      <!-- Style 2: Simple Card (No Data) -->
      <template v-if="styleConfig == 1">
        <div class="card-header acea-row row-between-wrapper">
          <div class="user-info style-2 acea-row row-between-wrapper">
            <div class="text">
              <div class="name" :style="{ color: nameColor, fontSize: nameSize + 'px' }">这里是昵称</div>
              <div class="level" v-if="userInfoConfig == 0" :style="{ color: numColor, fontSize: numSize + 'px' }">
                14512349876
              </div>
              <div class="level" v-else :style="{ color: numColor, fontSize: numSize + 'px' }">9527</div>
            </div>
            <div class="avatar">
              <img :src="logoConfig" v-if="logoConfig" />
              <div class="empty-box" v-else>
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
          </div>
        </div>
      </template>
      <!-- Style 3: Centered Vertical Layout -->
      <template v-if="styleConfig == 2">
        <div class="card-header acea-row row-center-wrapper">
          <div class="user-info style-3">
            <div class="avatar">
              <img :src="logoConfig" v-if="logoConfig" />
              <div class="empty-box" v-else>
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
            <div class="text">
              <div class="name" :style="{ color: nameColor, fontSize: nameSize + 'px' }">这里是昵称</div>
              <div class="level" v-if="userInfoConfig == 0" :style="{ color: numColor, fontSize: numSize + 'px' }">
                14512349876
              </div>
              <div class="level" v-else :style="{ color: numColor, fontSize: numSize + 'px' }">ID: 9527</div>
            </div>
          </div>
          <!-- Menu Entry -->
          <div
            class="menu-entry"
            v-if="menuList.length"
            style="position: absolute; right: 10px; top: 14px; z-index: 10"
          >
            <div class="item" v-for="(item, index) in menuList" :key="index" :style="{ marginLeft: '10px' }">
              <template v-if="menuStyle == 0">
                <img :src="item.img" v-if="item.img" class="menu-img" />
                <div class="empty-icon" v-else>
                  <img src="@diy/assets/images/shan.png" />
                </div>
              </template>
              <template v-else>
                <span
                  class="mb-iconfont"
                  :class="item.icon"
                  :style="{
                    display: 'inline-block',
                    color: iconStyle.color.color[0].item,
                    fontSize: iconStyle.size.val + 'px',
                    padding: iconStyle.padding.val + 'px',
                    transform: 'rotate(' + iconStyle.rotate.val + 'deg)',
                  }"
                  v-if="item.icon"
                ></span>
              </template>
            </div>
          </div>
        </div>
      </template>
      <!-- Style 4: Split Layout -->
      <template v-if="styleConfig == 3">
        <div class="style-4-container acea-row row-between-wrapper">
          <div class="left-card" :style="moduleCardStyle">
            <div class="user-info acea-row row-middle">
              <div class="avatar">
                <img :src="logoConfig" v-if="logoConfig" />
                <div class="empty-box" v-else>
                  <img src="@diy/assets/images/shan.png" />
                </div>
              </div>
              <div class="text">
                <div class="name" :style="{ color: nameColor, fontSize: nameSize + 'px' }">这里是昵称</div>
                <div class="level" v-if="userInfoConfig == 0" :style="{ color: numColor, fontSize: numSize + 'px' }">
                  14512349876
                </div>
                <div class="level" v-else :style="{ color: numColor, fontSize: numSize + 'px' }">ID: 9527</div>
              </div>
            </div>
            <div class="stats-row card-data acea-row row-around">
              <template v-if="assetMode == 0">
                <div
                  class="item"
                  v-for="(item, index) in dataList"
                  :key="index"
                  :class="{
                    'style-vert': dataStyle == 0,
                    'style-horiz': dataStyle == 1,
                    'style-vert-2': dataStyle == 2,
                  }"
                >
                  <template v-if="dataStyle == 0">
                    <div class="num" :style="{ color: dataNumColor }">{{ item.val }}</div>
                    <div class="label" :style="{ color: dataTitleColor }">{{ item.name }}</div>
                  </template>
                  <template v-if="dataStyle == 1 || dataStyle == 2">
                    <div class="label" :style="{ color: dataTitleColor }">{{ item.name }}</div>
                    <div class="num" :style="{ color: dataNumColor }">{{ item.val }}</div>
                  </template>
                </div>
              </template>
              <template v-if="assetMode == 1">
                <div class="item style-icon" v-for="(item, index) in assetList" :key="index">
                  <div class="icon-box">
                    <img :src="item.img" v-if="assetStyle == 0 && item.img" class="img-icon" />
                    <span
                      class="iconfont"
                      :class="item.icon"
                      :style="{ fontSize: assetIconSize + 'px', color: assetIconColor }"
                      v-else-if="assetStyle == 1 && item.icon"
                    ></span>
                    <div class="empty-icon" v-else><img src="@diy/assets/images/shan.png" /></div>
                  </div>
                  <div class="label" :style="{ fontSize: assetTextSize + 'px', color: assetTextColor }">
                    {{ item.info[0].value }}
                  </div>
                </div>
              </template>
            </div>
          </div>
          <div class="right-card" :style="moduleCardStyle">
            <div class="entry-content" v-if="rightEntryList.length">
              <div
                class="title"
                :style="{ fontSize: '14px', fontWeight: 'bold', marginBottom: '5px', color: moduleTitleColor }"
              >
                {{ rightEntryList[0].info[0].value }}
              </div>
              <div
                class="subtitle"
                style="font-size: 12px; color: #ff9900; margin-bottom: 10px; display: flex; align-items: center"
              >
                {{ rightEntryList[0].info[1].value }}
                <span class="iconfont iconyou" style="font-size: 12px"></span>
              </div>
              <div class="img-box">
                <img :src="rightEntryList[0].img" v-if="rightEntryList[0].img" />
                <div class="empty-icon" v-else><img src="@diy/assets/images/shan.png" /></div>
              </div>
            </div>
          </div>
        </div>
      </template>
      <!-- Style 5: White Card Layout -->
      <template v-if="styleConfig == 4">
        <div class="style-5-container">
          <div class="header acea-row row-between-wrapper">
            <div class="left">
              <div class="avatar">
                <img :src="logoConfig" v-if="logoConfig" />
                <div class="empty-box" v-else>
                  <img src="@diy/assets/images/shan.png" />
                </div>
              </div>
              <div class="text">
                <div class="name" :style="{ color: nameColor, fontSize: nameSize + 'px' }">这里是昵称</div>
                <div class="level" v-if="userInfoConfig == 0" :style="{ color: numColor, fontSize: numSize + 'px' }">
                  14512349876
                </div>
                <div class="level" v-else :style="{ color: numColor, fontSize: numSize + 'px' }">ID: 9527</div>
              </div>
            </div>
            <!-- Menu Entry -->
            <div class="menu-entry" v-if="menuList.length">
              <div class="item" v-for="(item, index) in menuList" :key="index" :style="{ marginLeft: '10px' }">
                <template v-if="menuStyle == 0">
                  <img :src="item.img" v-if="item.img" class="menu-img" />
                </template>
                <template v-else>
                  <span
                    class="mb-iconfont"
                    :class="item.icon"
                    :style="{
                      display: 'inline-block',
                      color: iconStyle.color.color[0].item,
                      fontSize: iconStyle.size.val + 'px',
                      padding: iconStyle.padding.val + 'px',
                      transform: 'rotate(' + iconStyle.rotate.val + 'deg)',
                    }"
                    v-if="item.icon"
                  ></span>
                </template>
              </div>
            </div>
          </div>
          <!-- Asset Grid -->
          <div class="card-data acea-row row-around" v-if="assetList.length">
            <template v-if="assetMode == 0">
              <div
                class="item"
                v-for="(item, index) in dataList"
                :key="index"
                :class="{
                  'style-vert': dataStyle == 0,
                  'style-horiz': dataStyle == 1,
                  'style-vert-2': dataStyle == 2,
                }"
              >
                <template v-if="dataStyle == 0">
                  <div class="num" :style="{ color: dataNumColor }">{{ item.val }}</div>
                  <div class="label" :style="{ color: dataTitleColor }">{{ item.name }}</div>
                </template>
                <template v-if="dataStyle == 1 || dataStyle == 2">
                  <div class="label" :style="{ color: dataTitleColor }">{{ item.name }}</div>
                  <div class="num" :style="{ color: dataNumColor }">{{ item.val }}</div>
                </template>
              </div>
            </template>

            <!-- Graphic Mode -->
            <template v-if="assetMode == 1">
              <div class="item style-icon" v-for="(item, index) in assetList" :key="index">
                <div class="icon-box">
                  <img :src="item.img" v-if="assetStyle == 0 && item.img" class="img-icon" />
                  <span
                    class="iconfont"
                    :class="item.icon"
                    :style="{ fontSize: assetIconSize + 'px', color: assetIconColor }"
                    v-else-if="assetStyle == 1 && item.icon"
                  ></span>
                  <div class="empty-icon" v-else><img src="@diy/assets/images/shan.png" /></div>
                </div>
                <div class="label">{{ item.info[0].value }}</div>
              </div>
            </template>
          </div>
        </div>
      </template>
      <!-- 快捷入口 -->
      <template
        v-if="
          ((assetMode == 0 && checkType.length) || (assetMode == 1 && assetList.length)) &&
          styleConfig != 3 &&
          styleConfig != 4
        "
      >
        <!-- 快捷入口 -->
        <div class="card-data acea-row row-around">
          <!-- Data Mode -->
          <template v-if="assetMode == 0">
            <div
              class="item"
              v-for="(item, index) in dataList"
              :key="index"
              :class="{
                'style-vert': dataStyle == 0,
                'style-horiz': dataStyle == 1,
                'style-vert-2': dataStyle == 2,
              }"
            >
              <template v-if="dataStyle == 0">
                <div class="num" :style="{ color: dataNumColor }">{{ item.val }}</div>
                <div class="label" :style="{ color: dataTitleColor }">{{ item.name }}</div>
              </template>
              <template v-if="dataStyle == 1 || dataStyle == 2">
                <div class="label" :style="{ color: dataTitleColor }">{{ item.name }}</div>
                <div class="num" :style="{ color: dataNumColor }">{{ item.val }}</div>
              </template>
            </div>
          </template>

          <!-- Graphic Mode -->
          <template v-if="assetMode == 1">
            <div class="item style-icon" v-for="(item, index) in assetList" :key="index">
              <div class="icon-box">
                <img :src="item.img" v-if="assetStyle == 0 && item.img" class="img-icon" />
                <span
                  class="iconfont"
                  :class="item.icon"
                  :style="{ fontSize: assetIconSize + 'px', color: assetIconColor }"
                  v-else-if="assetStyle == 1 && item.icon"
                ></span>
                <div class="empty-icon" v-else><img src="@diy/assets/images/shan.png" /></div>
              </div>
              <div class="label" :style="{ color: assetTextColor, fontSize: assetTextSize + 'px' }">
                {{ item.info[0].value }}
              </div>
            </div>
          </template>
        </div>
      </template>
      <!-- 会员样式 共四种 -->
      <template v-if="memberStyleConfig == 0">
        <div class="member-style-1" :style="[cardStyle, { marginTop: memberTopMargin }]">
          <div class="item" v-for="(item, index) in memberList" :key="index">
            <div class="text-box">
              <div class="title" :style="{ color: ms1TitleColor }">{{ item.info[0].value }}</div>
              <div class="subtitle" :style="{ color: ms1DescColor }">
                {{ item.info[1].value }} <span class="iconfont iconyou"></span>
              </div>
            </div>
            <div class="img-box">
              <img :src="item.img" v-if="item.img" />
              <div class="empty-icon" v-else><img src="@diy/assets/images/shan.png" /></div>
            </div>
            <div class="line" v-if="index < memberList.length - 1"></div>
          </div>
        </div>
      </template>

      <!-- Member Style 2 -->
      <template v-if="memberStyleConfig == 1">
        <div class="member-style-2" :style="[cardStyle, { marginTop: memberTopMargin }]">
          <div class="top-row">
            <div class="left-info">
              <div class="title-area">
                <img :src="ms2TitleImage" v-if="ms2TitleType == 1 && ms2TitleImage" class="title-img" />
                <span class="title-text" v-else :style="{ color: ms2TitleColor }">{{ ms2TitleText }}</span>
              </div>
              <div class="intro-text" :style="{ color: ms2IntroColor }">{{ ms2IntroText }}</div>
            </div>
            <div class="right-rights">
              <div class="right-item" v-for="(item, index) in ms2RightsList" :key="index">
                <div class="icon-wrap">
                  <img :src="item.img" v-if="item.img" />
                  <span
                    class="iconfont"
                    :class="item.icon"
                    v-else-if="item.icon"
                    :style="{ color: ms2RightsColor }"
                  ></span>
                  <div class="empty-icon" v-else><img src="@diy/assets/images/shan.png" /></div>
                </div>
                <span class="text" :style="{ color: ms2RightsColor }">{{ item.info[0].value }}</span>
              </div>
              <span class="iconfont iconyou" :style="{ color: ms2RightsColor }"></span>
            </div>
          </div>
          <div class="divider"></div>
          <div class="bottom-row">
            <div class="explain-list">
              <div class="icons" v-if="ms2ExplainIcons">
                <img :src="ms2ExplainIcons" />
              </div>
              <div class="explain-text" :style="{ color: ms2ExplainColor }">{{ ms2ExplainText }}</div>
            </div>
            <div class="action-btn" :style="{ color: ms2ButtonColor, background: ms2ButtonBgColor }">
              {{ ms2ButtonText }}
            </div>
          </div>
        </div>
      </template>

      <!-- Member Style 3 -->
      <template v-if="memberStyleConfig == 2">
        <div class="member-style-3" :style="[cardStyle]">
          <div class="content-wrapper" :style="ms3ContainerStyle">
            <div class="desc" :style="{ color: ms3TitleColor, fontSize: '12px' }">
              {{ ms3TitleText }}
            </div>
            <div
              class="btn"
              :style="{
                color: ms3ButtonColor,
                borderColor: ms3ButtonColor,
                border: '1px solid',
                padding: '4px 12px',
                borderRadius: '14px',
                fontSize: '12px',
              }"
            >
              {{ ms3ButtonText }}
            </div>
          </div>
        </div>
      </template>
      <!-- Member Style 4 -->
      <template v-if="memberStyleConfig == 3">
        <div class="member-style-4" :style="[cardStyle, { marginTop: memberTopMargin }]">
          <div class="left-box">
            <div class="label" style="color: #999; font-size: 12px; margin-bottom: 5px">可提现(元)</div>
            <div class="value" style="color: #f6d99d; font-size: 24px; font-weight: bold">200.00</div>
          </div>
          <div
            class="right-box"
            style="
              background: #f6d99d;
              color: #5a350c;
              padding: 5px 15px;
              border-radius: 20px;
              font-size: 12px;
              font-weight: bold;
            "
          >
            立即提现
          </div>
        </div>
      </template>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '会员中心',
  name: 'member',
  desc: '会员中心模块，可以用来展示会员信息、优惠券、积分等',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '展示设置',
  titleImg: '默认头像',
  titleRight: '样式设置',
  titleCurrency: '通用样式',
  infoStyleText: '会员信息',
  memberStyleText: '会员样式',
  iconStyleText: '图标样式',
  assetConfigText: '图文入口',
  styleConfig: {
    title: '选择风格',
    tabVal: 0,
    tabList: [{ name: '样式一' }, { name: '样式二' }, { name: '样式三' }, { name: '样式四' }, { name: '样式五' }],
  },
  memberStyleConfig: {
    title: '会员样式',
    tabVal: 0,
    tabList: [{ name: '样式一' }, { name: '样式二' }, { name: '样式三' }, { name: '样式四' }],
  },
  menuConfig: {
    title: '最多可添加2张图片，建议宽度40 * 40px',
    bnt: '添加',
    listStyleName: '操作内容',
    type: 1,
    listStyle: 0,
    maxList: 2,
    list: [
      {
        img: '',
        type: 0,
        show: true,
        icon: '',
        info: [
          {
            title: '标题',
            value: '标题',
            tips: '选填，不超过4个字',
            max: 4,
          },
          {
            title: '链接',
            value: '',
            tips: '请输入链接',
            max: 100,
          },
        ],
      },
      {
        img: '',
        type: 0,
        show: true,
        icon: '',
        info: [
          {
            title: '标题',
            value: '标题',
            tips: '选填，不超过4个字',
            max: 4,
          },
          {
            title: '链接',
            value: '',
            tips: '请输入链接',
            max: 100,
          },
        ],
      },
    ],
  },
  checkboxInfo: {
    title: '数据内容',
    name: 'checkboxInfo',
    maxList: 5,
    type: [1, 2, 3],
    list: [
      { id: 1, name: '余额' },
      { id: 3, name: '优惠券' },
      { id: 2, name: '积分' },
      { id: 5, name: '收藏商品' },
      { id: 6, name: '浏览记录' },
      { id: 8, name: '推广佣金' },
      { id: 9, name: '推广人' },
      { id: 10, name: '推广订单' },
    ],
  },
  logoConfig: {
    info: '建议：图片尺寸90px * 90px',
    url: '',
    type: 'code',
    delType: 1,
    name: '上传图片',
  },
  bottomBgColor: {
    title: '底部背景',
    default: [{ item: '#fff' }],
    color: [{ item: '#fff' }],
  },
  componentBgConfig: {
    title: '背景设置',
    tabVal: 0,
    tabList: [{ name: '颜色' }, { name: '图片' }],
    colorConfig: {
      title: '背景颜色',
      default: [{ item: '#E93323' }, { item: '#E93323' }],
      color: [{ item: '#E93323' }, { item: '#E93323' }],
    },
    colorDirection: {
      title: '渐变方向',
      tabVal: 0,
      tabList: [{ name: '横向' }, { name: '纵向' }, { name: '左斜' }, { name: '右斜' }],
    },
    imageConfig: {
      header: '背景图片',
      title: '',
      name: '上传图片',
      type: 'code',
      url: '',
      info: '建议尺寸：750px * 400px',
    },
  },
  zIndexConfig: {
    title: '组件上浮',
    val: 0,
    min: 0,
  },
  borderConfig: {
    title: '边框设置',
    tabVal: 0,
    tabList: [{ name: '隐藏' }, { name: '显示' }],
    val: 0, // 0: Hide, 1: Show
    styleConfig: {
      title: '边框样式',
      tabVal: 0,
      tabList: [
        { name: '实线', style: 'solid' },
        { name: '虚线', style: 'dashed' },
        { name: '点状', style: 'dotted' },
      ],
    },
    widthConfig: {
      title: '边框粗细',
      val: 1,
      min: 1,
    },
    colorConfig: {
      title: '边框颜色',
      default: [{ item: '#e5e5e5' }],
      color: [{ item: '#e5e5e5' }],
    },
  },
  shadowConfig: {
    title: '阴影设置',
    tabVal: 0,
    tabList: [{ name: '隐藏' }, { name: '显示' }],
    val: 0, // 0: Off, 1: On
    colorConfig: {
      title: '阴影颜色',
      default: [{ item: 'rgba(0,0,0,0.1)' }],
      color: [{ item: 'rgba(0,0,0,0.1)' }],
    },
    xConfig: {
      title: 'X轴偏移',
      val: 0,
      min: -50,
    },
    yConfig: {
      title: 'Y轴偏移',
      val: 0,
      min: -50,
    },
    blurConfig: {
      title: '模糊半径',
      val: 10,
      min: 0,
    },
    spreadConfig: {
      title: '扩展半径',
      val: 0,
      min: -50,
    },
  },
  fillet: {
    title: '背景圆角',
    type: 0,
    list: [
      { val: '全部', icon: 'iconcaozuo-zhengti' },
      { val: '单个', icon: 'iconcaozuo-bianjiao' },
    ],
    valName: '圆角值',
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  paddingConfig: {
    title: '内边距',
    val: 15,
    min: 0,
    isAll: false,
    valList: [{ val: 15 }, { val: 15 }, { val: 0 }, { val: 15 }],
  },
  marginConfig: {
    title: '外边距',
    val: 0,
    min: 0,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  assetIconColor: {
    title: '图标颜色',
    default: [{ item: '#fff' }],
    color: [{ item: '#fff' }],
  },
  assetIconSize: {
    title: '图标大小',
    val: 20,
    min: 10,
    max: 32,
  },
  assetTextColor: {
    title: '文字颜色',
    default: [{ item: '#fff' }],
    color: [{ item: '#fff' }],
  },
  assetTextSize: {
    title: '文字大小',
    val: 12,
    min: 10,
    max: 32,
  },
});
</script>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'home_member',
  cname: '会员中心',
  configName: 'c_member',
  icon: '#iconzujian-huiyuanxinxi',
  type: 4,
  // 0 基础组件 1 营销组件 2工具组件 3 商品组件 4 用户组件
  defaultName: 'member',
});

const props = defineProps({
  index: {
    type: null,
  },
  num: {
    type: null,
  },
  colorStyle: {
    type: null,
  },
});
// 实例级默认配置（timestamp 取当前实例 num）
const defaultConfig = getDefaultConfig(props.num);

const mobildConfigStore = useMobildConfigStore();

const dataList = computed(() => {
  let list = [
    { id: 1, name: '余额', val: '200' },
    { id: 3, name: '优惠券', val: '2888' },
    { id: 2, name: '积分', val: '3000' },
    { id: 5, name: '收藏商品', val: '1660' },
    { id: 6, name: '浏览记录', val: '1660' },
    { id: 8, name: '推广佣金', val: '666' },
    { id: 9, name: '推广人', val: '1660' },
    { id: 10, name: '推广订单', val: '1660' },
  ];
  return list.filter((item) => checkType.value.indexOf(item.id) != -1);
});

const moduleCardStyle = computed(() => {
  if (styleConfig.value == 3) {
    let color1 = moduleBgColor.value && moduleBgColor.value.color[0] ? moduleBgColor.value.color[0].item : '#fff';
    let color2 = moduleBgColor.value && moduleBgColor.value.color[1] ? moduleBgColor.value.color[1].item : '#fff';
    return {
      background: `linear-gradient(90deg, ${color1} 0%, ${color2} 100%)`,
      borderRadius: moduleRadius.value ? moduleRadius.value : '0px',
    };
  }
  return {};
});

const moduleTitleColor = computed(() => {
  return moduleTextColor.value && moduleTextColor.value.color[0] ? moduleTextColor.value.color[0].item : '#333';
});

const memberTopMargin = computed(() => {
  return styleConfig.value == 3 || styleConfig.value == 4 ? '10px' : '';
});

const cardStyle = computed(() => {
  if (memberStyleConfig.value == 0) {
    let color1 = cardBgColor.value && cardBgColor.value.color[0] ? cardBgColor.value.color[0].item : '#fff';
    let color2 = cardBgColor.value && cardBgColor.value.color[1] ? cardBgColor.value.color[1].item : '#fff';
    return {
      background: `linear-gradient(90deg, ${color1} 0%, ${color2} 100%)`,
      borderRadius: cardBgRadius.value ? cardBgRadius.value : '0px',
    };
  } else if (memberStyleConfig.value == 1) {
    let color1 = cardBgColor.value && cardBgColor.value.color[0] ? cardBgColor.value.color[0].item : '#fff';
    let color2 = cardBgColor.value && cardBgColor.value.color[1] ? cardBgColor.value.color[1].item : '#fff';
    return {
      background: `linear-gradient(90deg, ${color1} 0%, ${color2} 100%)`,
      borderRadius: cardBgRadius.value ? cardBgRadius.value : '0px',
    };
  } else if (memberStyleConfig.value == 2) {
    let style = { borderRadius: cardBgRadius.value ? cardBgRadius.value : '0px' };
    if (ms3BgMode.value === 1 && ms3BackgroundImage.value) {
      style.backgroundImage = `url(${ms3BackgroundImage.value})`;
      style.backgroundRepeat = 'no-repeat';
      style.backgroundSize = '100% 100%';
    } else {
      const c1 = cardBgColor.value && cardBgColor.value.color[0] ? cardBgColor.value.color[0].item : '#fff';
      const c2 = cardBgColor.value && cardBgColor.value.color[1] ? cardBgColor.value.color[1].item : c1;
      style.background = `linear-gradient(90deg, ${c1} 0%, ${c2} 100%)`;
    }
    return style;
  } else if (memberStyleConfig.value == 3) {
    let style = { borderRadius: cardBgRadius.value ? cardBgRadius.value : '0px' };
    if (ms4BgMode.value === 1 && ms4BackgroundImage.value) {
      style.backgroundImage = `url(${ms4BackgroundImage.value})`;
      style.backgroundRepeat = 'no-repeat';
      style.backgroundSize = '100% 100%';
    } else {
      const c1 = cardBgColor.value && cardBgColor.value.color[0] ? cardBgColor.value.color[0].item : '#fff';
      const c2 = cardBgColor.value && cardBgColor.value.color[1] ? cardBgColor.value.color[1].item : c1;
      style.background = `linear-gradient(90deg, ${c1} 0%, ${c2} 100%)`;
    }
    return style;
  }
  return {};
});

const ms4BgColorStyle = computed(() => {
  return ms4BgColor.value && ms4BgColor.value.color ? ms4BgColor.value.color : [];
});

const ms3ContainerStyle = computed(() => {
  let pad = ms3PaddingConfig.value ? ms3PaddingConfig.value.valList : [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }];
  return {
    padding: `${pad[0].val}px ${pad[1].val}px ${pad[2].val}px ${pad[3].val}px`,
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
  };
});

const configObj = ref(null);
const assetIconColor = ref('');
const assetIconSize = ref(20);
const assetTextColor = ref('');
const assetTextSize = ref(12);

const pageData = ref({});
const styleConfig = ref(0);
const userInfoConfig = ref(0);
const memberStyleConfig = ref(0);
const iconStyle = ref({});
const checkType = ref([]);
const logoConfig = ref('');
const menuStyle = ref(0);
const menuList = ref([]);
const shortcutStyle = ref(0);
const shortcutList = ref([]);
const assetMode = ref(0);
const dataStyle = ref(0);
const assetStyle = ref(2);
const assetList = ref([]);
const bgColorLeft = ref('');
const bgColorRight = ref('');
const bottomBgColor = ref('');
const fillet = ref(null);
const paddingConfig = ref({
  title: '内边距',
  isAll: false,
  val: 15,
  min: 0,
  valList: [{ val: 15 }, { val: 15 }, { val: 15 }, { val: 15 }],
});
const marginConfig = ref({
  title: '外边距',
  isAll: false,
  val: 0,
  min: 0,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const nameColor = ref('#fff');
const nameSize = ref(16);
const numColor = ref('#fff');
const numSize = ref(14);
const dataTitleColor = ref('#fff');
const dataNumColor = ref('#fff');
const zIndexConfig = ref(null);
const componentBgConfig = ref(null);
const borderConfig = ref(null);
const shadowConfig = ref(null);
const memberConfig = ref(null);
const memberList = ref([]);
const rightEntryList = ref([]);
const leftMenuList = ref([]);
const cardBgColor = ref(null);
const cardBgRadius = ref(0);
const moduleBgColor = ref(null);
const moduleTextColor = ref(null);
const moduleRadius = ref(null);
const ms2TitleType = ref(0);
const ms2TitleText = ref('');
const ms2TitleColor = ref('');
const ms2TitleImage = ref('');
const ms2IntroText = ref('');
const ms2IntroColor = ref('');
const ms2RightsList = ref([]);
const ms2RightsColor = ref('');
const ms2ExplainIcons = ref('');
const ms2ExplainText = ref('');
const ms2ExplainColor = ref('');
const ms2ButtonText = ref('');
const ms2ButtonLink = ref('');
const ms2ButtonColor = ref('');
const ms2ButtonBgColor = ref('');
const ms1TitleColor = ref('#333');
const ms1DescColor = ref('#ff7d00');
const ms3TitleText = ref('');
const ms3TitleColor = ref('');
const ms3ButtonText = ref('');
const ms3ButtonColor = ref('');
const ms3PaddingConfig = ref(null);
const ms3BackgroundImage = ref('');
const ms3BgColor = ref(null);
const ms3BgMode = ref(0);
const ms4BgColor = ref(null);
const ms4BgMode = ref(0);
const ms4BackgroundImage = ref('');

watch(
  pageData,
  (nVal, oVal) => {
    setConfig(nVal);
  },
  { deep: true },
);
watch(
  () => props.num,
  (nVal, oVal) => {
    let data = mobildConfigStore.defaultArray[nVal];
    setConfig(data);
  },
  { deep: true },
);
watch(
  () => mobildConfigStore.defaultArray,
  (nVal, oVal) => {
    let data = mobildConfigStore.defaultArray[props.num];
    setConfig(data);
  },
  { deep: true },
);
watch(
  configObj,
  (nVal, oVal) => {
    if (!nVal) return;
    ms3BgColor.value = nVal.ms3BgColor ? nVal.ms3BgColor.color : [];
    ms3BgMode.value = nVal.ms3BgMode ? nVal.ms3BgMode.tabVal : 0;
    ms3BackgroundImage.value = nVal.ms3BackgroundImage ? nVal.ms3BackgroundImage.url : '';
    ms4BgColor.value = nVal.ms4BgColor ? nVal.ms4BgColor.color : [];
    ms4BgMode.value = nVal.ms4BgMode ? nVal.ms4BgMode.tabVal : 0;
    ms4BackgroundImage.value = nVal.ms4BackgroundImage ? nVal.ms4BackgroundImage.url : '';
    assetIconColor.value = nVal.assetIconColor ? nVal.assetIconColor.color[0].item : '#ff9900';
    assetIconSize.value = nVal.assetIconSize ? nVal.assetIconSize.val : 20;
    assetTextColor.value = nVal.assetTextColor ? nVal.assetTextColor.color[0].item : '#333';
    assetTextSize.value = nVal.assetTextSize ? nVal.assetTextSize.val : 12;
  },
  { deep: true },
);

onMounted(() => {
  nextTick(() => {
    pageData.value = mobildConfigStore.defaultArray[props.num];
    setConfig(pageData.value);
  });
});

function getNum(item) {
  if (!item.info || !item.info[1]) return 0;
  let val = item.info[1].value;
  if (val.indexOf('money') != -1) return 200;
  if (val.indexOf('integral') != -1) return 3000;
  if (val.indexOf('coupon') != -1) return 2888;
  if (val.indexOf('collection') != -1) return 666;
  if (val.indexOf('visit') != -1) return 1660;
  return 0;
}

function setConfig(data) {
  if (!data) return;

  let dataClone = JSON.parse(JSON.stringify(data));
  for (let key in defaultConfig) {
    if (dataClone[key] === undefined) {
      dataClone[key] = JSON.parse(JSON.stringify(defaultConfig[key]));
    }
  }
  configObj.value = dataClone;
  paddingConfig.value = dataClone.paddingConfig;
  marginConfig.value = dataClone.marginConfig;
  zIndexConfig.value = dataClone.zIndexConfig;
  borderConfig.value = dataClone.borderConfig;
  shadowConfig.value = dataClone.shadowConfig;
  componentBgConfig.value = dataClone.componentBgConfig;

  menuStyle.value = dataClone.menuConfig ? dataClone.menuConfig.listStyle : 0;
  styleConfig.value = dataClone.styleConfig.tabVal;
  userInfoConfig.value = dataClone.userInfoConfig ? dataClone.userInfoConfig.tabVal : 0;
  memberStyleConfig.value = dataClone.memberStyleConfig ? dataClone.memberStyleConfig.tabVal : 0;
  checkType.value = dataClone.checkboxInfo.type;
  logoConfig.value = dataClone.logoConfig.url;

  menuList.value = dataClone.menuConfig ? dataClone.menuConfig.list : [];
  shortcutStyle.value = dataClone.shortcutConfig ? dataClone.shortcutConfig.listStyle : 0;
  shortcutList.value = dataClone.shortcutConfig ? dataClone.shortcutConfig.list : [];
  assetMode.value = dataClone.assetMode ? dataClone.assetMode.tabVal : 0;
  dataStyle.value = dataClone.dataStyle ? dataClone.dataStyle.tabVal : 0;
  memberList.value = dataClone.memberConfig ? dataClone.memberConfig.list : [];
  rightEntryList.value = dataClone.rightEntryConfig ? dataClone.rightEntryConfig.list : [];
  cardBgColor.value = dataClone.cardBgColor ? dataClone.cardBgColor : [];
  let cardFillet = dataClone.cardBgRadius ? dataClone.cardBgRadius.type : 0;
  let cardFilletVal = dataClone.cardBgRadius ? dataClone.cardBgRadius.val : 0;
  let cardValList = dataClone.cardBgRadius && dataClone.cardBgRadius.valList ? dataClone.cardBgRadius.valList : [];
  cardBgRadius.value = cardFillet
    ? cardValList[0].val + 'px ' + cardValList[1].val + 'px ' + cardValList[3].val + 'px ' + cardValList[2].val + 'px'
    : cardFilletVal + 'px';
  moduleBgColor.value = dataClone.moduleBgColor;
  moduleTextColor.value = dataClone.moduleTextColor;
  let moduleFillet = dataClone.moduleRadius ? dataClone.moduleRadius.type : 0;
  let moduleFilletVal = dataClone.moduleRadius ? dataClone.moduleRadius.val : 0;
  let moduleValList = dataClone.moduleRadius && dataClone.moduleRadius.valList ? dataClone.moduleRadius.valList : [];
  moduleRadius.value = moduleFillet
    ? moduleValList[0].val +
      'px ' +
      moduleValList[1].val +
      'px ' +
      moduleValList[3].val +
      'px ' +
      moduleValList[2].val +
      'px'
    : moduleFilletVal + 'px';
  ms2TitleType.value = dataClone.ms2TitleType ? dataClone.ms2TitleType.tabVal : 0;
  ms2TitleText.value = dataClone.ms2TitleText ? dataClone.ms2TitleText.value : '';
  ms2TitleColor.value = dataClone.ms2TitleColor ? dataClone.ms2TitleColor.color[0].item : '';
  ms2TitleImage.value = dataClone.ms2TitleImage ? dataClone.ms2TitleImage.url : '';
  ms2IntroText.value = dataClone.ms2IntroText ? dataClone.ms2IntroText.value : '';
  ms2IntroColor.value = dataClone.ms2IntroColor ? dataClone.ms2IntroColor.color[0].item : '';
  ms2RightsList.value = dataClone.ms2RightsList ? dataClone.ms2RightsList.list : [];
  ms2RightsColor.value = dataClone.ms2RightsColor ? dataClone.ms2RightsColor.color[0].item : '';
  ms2ExplainIcons.value = dataClone.ms2ExplainIcons ? dataClone.ms2ExplainIcons.url : '';
  ms2ExplainText.value = dataClone.ms2ExplainText ? dataClone.ms2ExplainText.value : '';
  ms2ExplainColor.value = dataClone.ms2ExplainColor ? dataClone.ms2ExplainColor.color[0].item : '';
  ms2ButtonText.value = dataClone.ms2ButtonText ? dataClone.ms2ButtonText.value : '';
  ms2ButtonLink.value = dataClone.ms2ButtonLink ? dataClone.ms2ButtonLink.value : '';
  ms2ButtonColor.value = dataClone.ms2ButtonColor ? dataClone.ms2ButtonColor.color[0].item : '';
  ms2ButtonBgColor.value = dataClone.ms2ButtonBgColor ? dataClone.ms2ButtonBgColor.color[0].item : '';
  ms1TitleColor.value = dataClone.ms1TitleColor ? dataClone.ms1TitleColor.color[0].item : '#333';
  ms1DescColor.value = dataClone.ms1DescColor ? dataClone.ms1DescColor.color[0].item : '#ff7d00';
  ms3TitleText.value = dataClone.ms3TitleText ? dataClone.ms3TitleText.value : '';
  ms3TitleColor.value = dataClone.ms3TitleColor ? dataClone.ms3TitleColor.color[0].item : '';
  ms3ButtonText.value = dataClone.ms3ButtonText ? dataClone.ms3ButtonText.value : '';
  ms3ButtonColor.value = dataClone.ms3ButtonColor ? dataClone.ms3ButtonColor.color[0].item : '';
  ms3PaddingConfig.value = dataClone.ms3PaddingConfig;
  ms3BgMode.value = dataClone.ms3BgMode ? dataClone.ms3BgMode.tabVal : 0;
  ms3BgColor.value = dataClone.ms3BgColor ? dataClone.ms3BgColor.color : [];
  ms3BackgroundImage.value = dataClone.ms3BackgroundImage ? dataClone.ms3BackgroundImage.url : '';
  ms4BgMode.value = dataClone.ms4BgMode ? dataClone.ms4BgMode.tabVal : 0;
  ms4BgColor.value = dataClone.ms4BgColor ? dataClone.ms4BgColor.color : [];
  ms4BackgroundImage.value = dataClone.ms4BackgroundImage ? dataClone.ms4BackgroundImage.url : '';
  assetStyle.value = dataClone.assetConfig ? dataClone.assetConfig.listStyle : 2;
  assetList.value = dataClone.assetConfig ? dataClone.assetConfig.list : [];
  iconStyle.value = dataClone.iconStyleConfig || dataClone.iconStyle || {};
  bottomBgColor.value = dataClone.bottomBgColor.color[0].item;
  fillet.value = dataClone.fillet;
  nameColor.value = dataClone.nameColor ? dataClone.nameColor.color[0].item : '#333333';
  nameSize.value = dataClone.nameSize ? dataClone.nameSize.val : 16;
  numColor.value = dataClone.numColor ? dataClone.numColor.color[0].item : '#333333';
  numSize.value = dataClone.numSize ? dataClone.numSize.val : 14;
  dataTitleColor.value = dataClone.dataTitleColor ? dataClone.dataTitleColor.color[0].item : '#333333';
  dataNumColor.value = dataClone.dataNumColor ? dataClone.dataNumColor.color[0].item : '#333333';
  assetIconColor.value = dataClone.assetIconColor ? dataClone.assetIconColor.color[0].item : '#ff9900';
  assetIconSize.value = dataClone.assetIconSize ? dataClone.assetIconSize.val : 20;
  assetTextColor.value = dataClone.assetTextColor ? dataClone.assetTextColor.color[0].item : '#333';
  assetTextSize.value = dataClone.assetTextSize ? dataClone.assetTextSize.val : 12;
}
</script>

<style scoped lang="scss">
.mobile-page {
  display: inline-block;
  width: -webkit-fill-available;
}
.member-card {
  position: relative;
  min-height: 100px;
  overflow: hidden;
  .shortcut-entry {
    .shortcut-img {
      width: 20px;
      height: 20px;
      display: block;
    }
    .empty-icon {
      width: 20px;
      height: 20px;
      background: #f5f5f5;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #999;
      font-size: 12px;
    }
  }
  .menu-entry {
    display: flex;
    align-items: center;
    .iconfont {
      font-size: 20px;
      color: #333;
    }
    .menu-img {
      width: 20px;
      height: 20px;
      display: block;
    }
    .empty-icon {
      width: 20px;
      height: 20px;
      background: #f5f5f5;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #999;
      font-size: 12px;
    }
  }
  .card-header {
    position: relative;
    z-index: 2;
    .user-info {
      &.style-2 {
        width: 100%;
        padding: 0px 21px;
        .avatar {
          margin-right: 0;
        }
      }
      &.style-3 {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        .avatar {
          margin-right: 0;
          margin-bottom: 10px;
        }
        .text {
          text-align: center;
          .name {
            justify-content: center;
          }
        }
      }
      .avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        margin-right: 0;
        margin-right: 16px;
        img {
          width: 100%;
          height: 100%;
          border-radius: 50%;
          object-fit: cover;
        }
        .empty-box {
          background-color: #f3f9ff;
          border: 1px solid #eeeeee;
          display: flex;
          align-items: center;
          justify-content: center;
          width: 100%;
          height: 100%;
          border-radius: 50%;
          img {
            width: 26px;
            height: 20px;
            border-radius: 0;
          }
        }
      }
      .text {
        .name {
          font-size: 16px;
          font-weight: 500;
          color: #fff;
          display: flex;
          align-items: center;
          margin-bottom: 5px;
        }
        .level {
          font-size: 10px;
          color: #ffffff;
        }
      }
    }
  }
  .card-data {
    margin-top: 20px;
    position: relative;
    z-index: 2;
    .item {
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;

      &.style-vert {
        flex-direction: column;
      }
      &.style-horiz {
        flex-direction: row;
        .num {
          margin-left: 5px;
        }
      }
      &.style-vert-2 {
        flex-direction: column;
      }
      &.style-icon {
        flex-direction: column;
        .icon-box {
          margin-bottom: 5px;
          display: flex;
          justify-content: center;
          align-items: center;
          .iconfont {
            font-size: 24px;
            color: #fff;
          }
          .img-icon {
            width: 24px;
            height: 24px;
          }
          .empty-icon {
            width: 24px;
            height: 24px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 12px;
            border-radius: 4px;
          }
        }
      }

      .num {
        font-size: 16px;
        font-weight: bold;
        color: #fff;
      }
      .label {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.8);
        margin-top: 0;
      }
    }
  }

  &.style4 {
    .text-center {
      text-align: center;
      margin-top: 10px;
    }
    .avatar {
      margin-right: 0;
    }
  }
}
.member-style-1 {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px 0px;
  margin-top: 18px;
  .item {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    padding: 0 20px 0 20px;

    .text-box {
      .title {
        font-size: 14px;
        color: #333;
        font-weight: bold;
        margin-bottom: 5px;
      }
      .subtitle {
        font-size: 11px;
        color: #ff7d00;
        display: flex;
        align-items: center;
        .iconfont {
          font-size: 10px;
          margin-top: 1px;
        }
      }
    }

    .img-box {
      width: 44px;
      height: 44px;
      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
      }
      .empty-icon {
        width: 100%;
        height: 100%;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ccc;
        border-radius: 50%;
      }
    }

    .line {
      position: absolute;
      right: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 1px;
      height: 20px;
      background: #eee;
    }
  }
}

.style-4-container {
  align-items: stretch;
  .left-card {
    flex: 2;
    margin-right: 10px;
    padding: 15px;
    background: #fff;
    display: flex;
    flex-direction: column;
    justify-content: space-between;

    .user-info {
      margin-bottom: 10px;
      .avatar {
        width: 40px;
        height: 40px;
        margin-right: 10px;
        img {
          width: 100%;
          height: 100%;
          border-radius: 50%;
          object-fit: cover;
        }
        .empty-box {
          width: 100%;
          height: 100%;
          border-radius: 50%;
          background: #eee;
          display: flex;
          align-items: center;
          justify-content: center;
          img {
            width: 20px;
            height: 20px;
            border-radius: 0;
          }
        }
      }
      .text {
        .name {
          margin-bottom: 3px;
        }
      }
    }
    .stats-row {
      .item {
        display: flex;
        flex-direction: column;
        align-items: center;
      }
    }
  }
  .right-card {
    flex: 1;
    padding: 15px 10px;
    background: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;

    .entry-content {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;

      .img-box {
        width: 60px;
        height: 60px;
        margin-top: 5px;
        img {
          width: 100%;
          height: 100%;
          object-fit: contain;
        }
        .empty-icon {
          width: 100%;
          height: 100%;
          background: #f5f5f5;
          display: flex;
          align-items: center;
          justify-content: center;
          color: #ccc;
          border-radius: 4px;
        }
      }
    }
  }
}

.style-5-container {
  padding: 20px;
  background: #fff;
  border-radius: 12px;
  margin-top: 45px;

  .header {
    margin-bottom: 45px;
    position: relative;
    .left {
      position: absolute;
      top: -46px;
      left: 0px;
      display: flex;
      align-items: flex-end;
      .avatar {
        width: 74px;
        height: 74px;
        margin-right: 12px;
        border-radius: 50%;
        border: 3px solid #fff;
        position: relative;
        img {
          width: 100%;
          height: 100%;
          border-radius: 50%;
          object-fit: cover;
        }
        .empty-box {
          width: 100%;
          height: 100%;
          border-radius: 50%;
          background: #f5f5f5;
          display: flex;
          align-items: center;
          justify-content: center;
          img {
            width: 25px;
            height: 25px;
            border-radius: 0;
          }
        }
      }

      .text {
        .name {
          font-weight: bold;
          margin-bottom: 5px;
        }
      }
    }

    .menu-entry {
      display: flex;
      align-items: center;
      position: absolute;
      top: 0px;
      right: 0px;
      .item {
        .menu-img {
          width: 24px;
          height: 24px;
        }
        .mb-iconfont {
          font-size: 24px;
          color: #333;
        }
      }
    }
  }

  .asset-grid {
    padding-top: 35px;
    .item {
      display: flex;
      flex-direction: column;
      align-items: center;

      .icon-box {
        width: 30px;
        height: 30px;
        margin-bottom: 8px;
        display: flex;
        justify-content: center;
        align-items: center;

        .img-icon {
          width: 100%;
          height: 100%;
          object-fit: contain;
        }
        .iconfont {
          font-size: 30px;
          color: #333;
        }
        .empty-icon {
          width: 30px;
          height: 30px;
          background: #f5f5f5;
          display: flex;
          align-items: center;
          justify-content: center;
          color: #ccc;
          border-radius: 4px;
        }
      }
    }
  }
}

.member-style-2 {
  margin-top: 18px;
  padding: 15px;
  .top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    .left-info {
      .title-area {
        display: flex;
        align-items: center;
        .title-img {
          width: 162px;
          height: 30px;
          object-fit: cover;
        }
        .title-text {
          font-size: 18px;
          font-weight: bold;
        }
      }
      .intro-text {
        font-size: 12px;
        opacity: 0.8;
      }
    }
    .right-rights {
      display: flex;
      align-items: center;
      .right-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-left: 10px;
        .icon-wrap {
          width: 28px;
          height: 28px;
          margin-right: 4px;
          img {
            width: 100%;
            height: 100%;
            object-fit: contain;
          }
          .iconfont {
            font-size: 16px;
          }
          .empty-icon {
            width: 100%;
            height: 100%;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            border-radius: 50%;
            img {
              font-size: 12px;
            }
          }
        }
        .text {
          font-size: 12px;
        }
      }
      .iconyou {
        margin-left: 5px;
        font-size: 12px;
      }
    }
  }
  .divider {
    height: 1px;
    background: rgba(0, 0, 0, 0.05);
    margin: 0;
  }
  .bottom-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 10px;
    .explain-list {
      display: flex;
      align-items: center;
      .icons {
        display: flex;
        margin-right: 10px;
        img {
          height: 32px;
          object-fit: contain;
        }
      }
      .explain-text {
        font-size: 12px;
      }
    }
    .action-btn {
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
    }
  }
}

.member-style-3 {
  margin-top: 18px;
}

.member-style-4 {
  margin-top: 18px;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
