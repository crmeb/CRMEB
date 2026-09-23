<template>
  <common_wrapper :config="configObj">
    <div class="spec-preview-box" v-if="configObj">
      <!-- 标题 -->
      <div
        class="title"
        v-if="titleShow"
        :style="{ color: titleColor, fontSize: titleSize + 'px', textAlign: titleAlign }"
      >
        售卖形态
      </div>
      <!-- mock 日历预览：全月历风格 -->
      <div class="mock-calendar-month" v-if="calendarStyleVal == 0">
        <div class="mock-calendar__week">
          <span>日</span><span>一</span><span>二</span><span>三</span><span>四</span><span>五</span><span>六</span>
        </div>
        <div class="mock-calendar-month__grid">
          <span class="day muted">26</span>
          <span class="day muted">27</span>
          <span class="day muted">28</span>
          <span class="day muted">29</span>
          <span class="day muted">30</span>
          <span class="day">1</span>
          <span class="day">2</span>
          <span class="day">3</span>
          <span class="day">4</span>
          <span class="day">5</span>
          <span class="day">6</span>
          <span class="day">7</span>
          <span class="day">8</span>
          <span class="day">9</span>
          <span class="day">10</span>
          <span class="day">11</span>
          <span class="day">12</span>
          <span class="day">13</span>
          <span class="day">14</span>
          <span class="day active">15</span>
          <span class="day">16</span>
          <span class="day">17</span>
          <span class="day">18</span>
          <span class="day">19</span>
          <span class="day">20</span>
          <span class="day">21</span>
          <span class="day">22</span>
          <span class="day">23</span>
          <span class="day">24</span>
          <span class="day">25</span>
          <span class="day">26</span>
          <span class="day">27</span>
          <span class="day">28</span>
          <span class="day">29</span>
          <span class="day">30</span>
          <span class="day muted">1</span>
          <span class="day muted">2</span>
        </div>
      </div>
      <!-- mock 日历预览：单行日历风格 -->
      <div class="mock-calendar" v-else>
        <div class="mock-calendar__week">
          <span>日</span><span>一</span><span>二</span><span>三</span><span>四</span><span>五</span><span>六</span>
        </div>
        <div class="mock-calendar__days">
          <span class="day muted">25</span>
          <span class="day muted">26</span>
          <span class="day">27</span>
          <span class="day">28</span>
          <span class="day active">29</span>
          <span class="day">30</span>
          <span class="day">31</span>
        </div>
      </div>
      <!-- mock 场次列表 -->
      <div class="mock-slots">
        <div class="mock-slot active">
          <span class="mock-slot__name">上午场</span>
          <span class="mock-slot__time">09:00-12:00</span>
        </div>
        <div class="mock-slot">
          <span class="mock-slot__name">下午场</span>
          <span class="mock-slot__time">14:00-17:00</span>
        </div>
      </div>
      <!-- mock 票种列表 -->
      <div class="mock-prices">
        <div class="mock-price">
          <span class="mock-price__name">成人票</span>
          <span class="mock-price__val">¥99</span>
        </div>
        <div class="mock-price">
          <span class="mock-price__name">儿童票</span>
          <span class="mock-price__val">¥59</span>
        </div>
        <div class="mock-price">
          <span class="mock-price__name">老人票</span>
          <span class="mock-price__val">¥49</span>
        </div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '售卖形态展示位',
  name: 'specPreview',
  timestamp: num,
  contentTitle: '内容设置',
  titleStyle: '标题样式',
  setUp: {
    tabVal: 0,
  },
  isShow: {
    title: '显示标题',
    tabVal: 0,
    tabList: [{ name: '显示' }, { name: '隐藏' }],
  },

  textPosition: {
    title: '对齐方式',
    val: 'center',
  },
  textColor: {
    title: '文字颜色',
    default: [{ item: '#333' }],
    color: [{ item: '#333' }],
  },
  fontSize: {
    title: '字体大小',
    val: 16,
    min: 12,
    max: 40,
  },
  titleCurrency: '通用样式',
  moduleColor: {
    title: '背景颜色',
    name: 'moduleColor',
    default: [{ item: '#fff' }],
    color: [{ item: '#fff' }],
  },
  bottomBgColor: {
    title: '底部背景',
    name: 'bottomBgColor',
    default: [{ item: '#fff' }],
    color: [{ item: '#fff' }],
  },
  marginConfig: {
    title: '外边距',
    val: 0,
    min: 0,
    max: 100,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  paddingConfig: {
    title: '内边距',
    val: 10,
    min: 0,
    max: 100,
    isAll: false,
    valList: [{ val: 10 }, { val: 10 }, { val: 10 }, { val: 10 }],
  },
  componentBgConfig: {
    title: '组件背景',
    tabVal: 0,
    colorConfig: {
      title: '颜色设置',
      default: [{ item: 'transparent' }],
      color: [{ item: 'transparent' }],
    },
    imageConfig: {
      title: '图片设置',
      url: '',
    },
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
    tabList: [
      {
        name: '隐藏',
      },
      {
        name: '显示',
      },
    ],
    val: 0,
    colorConfig: {
      title: '阴影颜色',
      default: [
        {
          item: 'rgba(0,0,0,0.1)',
        },
      ],
      color: [
        {
          item: 'rgba(0,0,0,0.1)',
        },
      ],
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
  borderRadius: '0',
  zIndexConfig: {
    title: '层级',
    val: 0,
    min: 0,
  },
});
</script>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'home_spec_preview',
  cname: '售卖形态',
  configName: 'c_spec_preview',
  icon: '#iconzujian-shangpinxinxi',
  type: 3,
  defaultName: 'specPreview',
});

const props = defineProps({
  index: {
    type: null,
  },
  num: {
    type: null,
  },
});
// 实例级默认配置（timestamp 取当前实例 num）
const defaultConfig = getDefaultConfig(props.num);

const mobildConfigStore = useMobildConfigStore();

const titleShow = computed(() => {
  return configObj.value?.isShow?.tabVal == 0;
});

const calendarStyleVal = computed(() => {
  return configObj.value?.calendarStyle?.tabVal ?? 0;
});

const titleColor = computed(() => {
  return configObj.value?.textColor?.color?.[0]?.item || '#333';
});

const titleSize = computed(() => {
  return configObj.value?.fontSize?.val || 16;
});

const titleAlign = computed(() => {
  return configObj.value?.textPosition.val || 'left';
});

const configObj = ref(null);
const pageData = ref({}); // watch(pageData) 引用，缺声明会在 setup 阶段抛 ReferenceError

// Vue2 原代码 watch 的 pageData 未在 data 中声明（死监听），转换时移除
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

onMounted(() => {
  nextTick(() => {
    setConfig(mobildConfigStore.defaultArray[props.num]);
  });
});

function setConfig(data) {
  if (!data) return;
  if (data) {
    configObj.value = data;
  }
}
</script>

<style scoped>
.spec-preview-box {
  padding: 4px 0;
}
.title {
  font-size: 16px;
  font-weight: bold;
  color: #333;
  margin-bottom: 10px;
}
/* mock 日历 */
.mock-calendar {
  margin-bottom: 10px;
}
/* mock 日历-全月历风格 */
.mock-calendar-month {
  margin-bottom: 10px;
}
.mock-calendar-month__grid {
  display: flex;
  flex-wrap: wrap;
}
.mock-calendar-month__grid .day {
  flex: 0 0 calc(100% / 7);
  text-align: center;
  font-size: 11px;
  padding: 5px 0;
  color: #333;
}
.mock-calendar-month__grid .day.muted {
  color: #ccc;
}
.mock-calendar-month__grid .day.active {
  background: #e93323;
  color: #fff;
  border-radius: 4px;
}
.mock-calendar__week {
  display: flex;
  justify-content: space-between;
  margin-bottom: 4px;
}
.mock-calendar__week span {
  flex: 1;
  text-align: center;
  font-size: 11px;
  color: #999;
}
.mock-calendar__days {
  display: flex;
  justify-content: space-between;
}
.mock-calendar__days .day {
  flex: 1;
  text-align: center;
  font-size: 12px;
  padding: 4px 0;
  border-radius: 4px;
  color: #333;
}
.mock-calendar__days .day.muted {
  color: #ccc;
}
.mock-calendar__days .day.active {
  background: #e93323;
  color: #fff;
}
/* mock 场次 */
.mock-slots {
  display: flex;
  gap: 8px;
  margin-bottom: 10px;
}
.mock-slot {
  flex: 1;
  padding: 6px 8px;
  border: 1px solid #eee;
  border-radius: 4px;
  text-align: center;
}
.mock-slot.active {
  border-color: #e93323;
  background: rgba(233, 51, 35, 0.05);
}
.mock-slot__name {
  display: block;
  font-size: 12px;
  color: #333;
  font-weight: 500;
}
.mock-slot__time {
  display: block;
  font-size: 10px;
  color: #999;
  margin-top: 2px;
}
/* mock 票种 */
.mock-prices {
  display: flex;
  gap: 8px;
}
.mock-price {
  flex: 1;
  padding: 6px 8px;
  background: #f7f8fa;
  border-radius: 4px;
  text-align: center;
}
.mock-price__name {
  display: block;
  font-size: 12px;
  color: #333;
}
.mock-price__val {
  display: block;
  font-size: 14px;
  color: #e93323;
  font-weight: 600;
  margin-top: 2px;
}
</style>
