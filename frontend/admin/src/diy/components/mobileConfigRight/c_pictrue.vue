<template>
  <div class="mobile-page">
    <div v-if="isUpdate">
      <!-- <div class="title">布局</div>
            <div class="tip">选定布局区域，在下方添加图片，建议添加比例一致的图片</div> -->
      <div class="advert">
        <template v-for="(item, index) in configData.picList" :key="index">
          <div
            class="advertItem07"
            :class="currentIndex === index ? 'on' : ''"
            @click="currentTab(index, configData)"
            v-if="style === 0"
          >
            <img :src="item.image" v-if="item.image" />
            <div class="empty-box" v-else>750*375</div>
          </div>
        </template>
        <div class="advertItem02 acea-row" v-if="style === 1">
          <div
            class="item"
            :class="currentIndex === index ? 'on' : ''"
            @click="currentTab(index, configData)"
            v-for="(item, index) in configData.picList"
            :key="index"
          >
            <img :src="item.image" v-if="item.image" />
            <div class="empty-box" v-else>
              <div>
                <div>375*750</div>
              </div>
            </div>
          </div>
        </div>
        <div class="advertItem03 acea-row" v-if="style === 2">
          <div
            class="item"
            :class="currentIndex === index ? 'on' : ''"
            @click="currentTab(index, configData)"
            v-for="(item, index) in configData.picList"
            :key="index"
          >
            <img :src="item.image" v-if="item.image" />
            <div class="empty-box" v-else>
              <div>
                <div>250*750</div>
              </div>
            </div>
          </div>
        </div>
        <div class="advertItem08" v-if="style === 3">
          <div class="item acea-row">
            <div class="pic" :class="currentIndex === 0 ? 'on' : ''" @click="currentTab(0, configData)">
              <img :src="configData.picList[0].image" v-if="configData.picList[0].image" />
              <div class="empty-box" v-else>375*375</div>
            </div>
            <div class="pic" :class="currentIndex === 1 ? 'on' : ''" @click="currentTab(1, configData)">
              <img :src="configData.picList[1].image" v-if="configData.picList[1].image" />
              <div class="empty-box" v-else>375*375</div>
            </div>
          </div>
          <div class="item" :class="currentIndex === 2 ? 'on' : ''" @click="currentTab(2, configData)">
            <img :src="configData.picList[2].image" v-if="configData.picList[2].image" />
            <div class="empty-box" v-else>750*375</div>
          </div>
        </div>
        <div class="advertItem08" v-if="style === 4">
          <div class="item" :class="currentIndex === 0 ? 'on' : ''" @click="currentTab(0, configData)">
            <img :src="configData.picList[0].image" v-if="configData.picList[0].image" />
            <div class="empty-box" v-else>750*375</div>
          </div>
          <div class="item acea-row">
            <div class="pic" :class="currentIndex === 1 ? 'on' : ''" @click="currentTab(1, configData)">
              <img :src="configData.picList[1].image" v-if="configData.picList[1].image" />
              <div class="empty-box" v-else>375*375</div>
            </div>
            <div class="pic" :class="currentIndex === 2 ? 'on' : ''" @click="currentTab(2, configData)">
              <img :src="configData.picList[2].image" v-if="configData.picList[2].image" />
              <div class="empty-box" v-else>375*375</div>
            </div>
          </div>
        </div>
        <div class="advertItem04 acea-row" v-if="style === 5">
          <div class="item" :class="currentIndex === 0 ? 'on' : ''" @click="currentTab(0, configData)">
            <img :src="configData.picList[0].image" v-if="configData.picList[0].image" />
            <div class="empty-box" v-else>375*750</div>
          </div>
          <div class="item">
            <div class="pic" :class="currentIndex === 1 ? 'on' : ''" @click="currentTab(1, configData)">
              <img :src="configData.picList[1].image" v-if="configData.picList[1].image" />
              <div class="empty-box" v-else>375*375</div>
            </div>
            <div class="pic" :class="currentIndex === 2 ? 'on' : ''" @click="currentTab(2, configData)">
              <img :src="configData.picList[2].image" v-if="configData.picList[2].image" />
              <div class="empty-box" v-else>375*375</div>
            </div>
          </div>
        </div>
        <div class="advertItem04 acea-row" v-if="style === 6">
          <div class="item">
            <div class="pic" :class="currentIndex === 0 ? 'on' : ''" @click="currentTab(0, configData)">
              <img :src="configData.picList[0].image" v-if="configData.picList[0].image" />
              <div class="empty-box" v-else>375*375</div>
            </div>
            <div class="pic" :class="currentIndex === 1 ? 'on' : ''" @click="currentTab(1, configData)">
              <img :src="configData.picList[1].image" v-if="configData.picList[1].image" />
              <div class="empty-box" v-else>375*375</div>
            </div>
          </div>
          <div class="item" :class="currentIndex === 2 ? 'on' : ''" @click="currentTab(2, configData)">
            <img :src="configData.picList[2].image" v-if="configData.picList[2].image" />
            <div class="empty-box" v-else>375*750</div>
          </div>
        </div>
        <div class="advertItem06 acea-row" v-if="style === 7">
          <div
            class="item"
            :class="currentIndex === index ? 'on' : ''"
            @click="currentTab(index, configData)"
            v-for="(item, index) in configData.picList"
            :key="index"
          >
            <img :src="item.image" v-if="item.image" />
            <div class="empty-box" v-else>375*375</div>
          </div>
        </div>
        <div class="advertItem08" v-if="style === 8">
          <div class="item acea-row">
            <div class="pic" :class="currentIndex === 0 ? 'on' : ''" @click="currentTab(0, configData)">
              <img :src="configData.picList[0].image" v-if="configData.picList[0].image" />
              <div class="empty-box" v-else>375*375</div>
            </div>
            <div class="pic" :class="currentIndex === 1 ? 'on' : ''" @click="currentTab(1, configData)">
              <img :src="configData.picList[1].image" v-if="configData.picList[1].image" />
              <div class="empty-box" v-else>375*375</div>
            </div>
          </div>
          <div class="items acea-row">
            <div class="pic" :class="currentIndex === 2 ? 'on' : ''" @click="currentTab(2, configData)">
              <img :src="configData.picList[2].image" v-if="configData.picList[2].image" />
              <div class="empty-box" v-else>250*375</div>
            </div>
            <div class="pic" :class="currentIndex === 3 ? 'on' : ''" @click="currentTab(3, configData)">
              <img :src="configData.picList[3].image" v-if="configData.picList[3].image" />
              <div class="empty-box" v-else>250*375</div>
            </div>
            <div class="pic" :class="currentIndex === 4 ? 'on' : ''" @click="currentTab(4, configData)">
              <img :src="configData.picList[4].image" v-if="configData.picList[4].image" />
              <div class="empty-box" v-else>250*375</div>
            </div>
          </div>
        </div>
        <div class="advertItem04 acea-row" v-if="style === 9">
          <div class="item" :class="currentIndex === 0 ? 'on' : ''" @click="currentTab(0, configData)">
            <img :src="configData.picList[0].image" v-if="configData.picList[0].image" />
            <div class="empty-box" v-else>375*750</div>
          </div>
          <div class="item">
            <div class="pic" :class="currentIndex === 1 ? 'on' : ''" @click="currentTab(1, configData)">
              <img :src="configData.picList[1].image" v-if="configData.picList[1].image" />
              <div class="empty-box" v-else>375*375</div>
            </div>
            <div class="pic acea-row">
              <div class="picItem" :class="currentIndex === 2 ? 'on' : ''" @click="currentTab(2, configData)">
                <img :src="configData.picList[2].image" v-if="configData.picList[2].image" />
                <div class="empty-box" v-else>375*250</div>
              </div>
              <div class="picItem" :class="currentIndex === 3 ? 'on' : ''" @click="currentTab(3, configData)">
                <img :src="configData.picList[3].image" v-if="configData.picList[3].image" />
                <div class="empty-box" v-else>375*250</div>
              </div>
            </div>
          </div>
        </div>
        <template v-for="(item, index) in configData.picList" :key="index">
          <div class="advertItem01 acea-row" v-if="style === 10">
            <img :src="item.image" v-if="item.image" />
            <div class="empty-box" v-else>尺寸不限</div>
          </div>
        </template>
        <template v-if="style === 11">
          <div class="pic-box" @mousemove.stop="move">
            <div class="advertItem11 acea-row" id="lay1" @click="clickBox">
              <div
                class="lay-item"
                :class="currentIndex === index ? 'on' : ''"
                v-for="(item, index) in configData.picList"
                :key="index + 'aaa'"
              >
                <img :src="item.img" v-if="item.img" />
                <div class="empty-box" v-else>+</div>
              </div>
            </div>
            <div
              v-for="(item, index) in selBoxList"
              :key="index"
              :style="{
                width: item.doc.w + 'px',
                height: item.doc.h + 'px',
                left: item.doc.startX + 'px',
                top: item.doc.startY + 'px',
              }"
              class="areaBox"
              :class="{ active: selPicBox == index }"
              @mouseover="initRect"
              @click="currentTab(index, configData)"
            >
              <img :src="item.img" v-if="item.img" />
              <div class="prompt-text" v-else>{{ item.doc.w }}x{{ item.doc.h }}</div>
              <div v-if="selPicBox == index" class="del" @click.stop="delAreaBox(index)">
                <i class="el-icon-close" />
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';

defineOptions({ name: 'c_pictrue' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const emit = defineEmits(['delAreaBox']);

const defaults = ref({});
const configData = ref({});
const style = ref(0);
const isUpdate = ref(false); // 重新渲染
const currentIndex = ref(0);
const arrayObj = {
  image: '',
  link: '',
};
let select = false;
let lis = null; // document.getElementsByClassName('lay-item') 的 HTMLCollection，无需响应式
let rect = null; // 定义移动元素div
// 记录鼠标按下时的坐标
let downX = 0;
let downY = 0;
// 记录鼠标抬起时候的坐标
let mouseX2 = 0;
let mouseY2 = 0;
let count = 0;
let areaDataIndex; // 遗留：原 this.areaDataIndex 即未定义
const selPicBox = ref(0); // 当前选中的图片盒子

const selBoxList = computed(() => {
  return props.configObj && props.configObj.picStyle ? props.configObj.picStyle.docPicList : [];
});

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    if (props.configObj.hasOwnProperty('timestamp')) {
      isUpdate.value = true;
    } else {
      isUpdate.value = false;
    }
    configData.value = props.configObj[props.configNme];
    style.value = props.configObj.styleConfig.tabVal;
    count = defaults.value.styleConfig.count;
    picArrayConcat(count);

    if (style.value == 11) {
      lis = document.getElementsByClassName('lay-item');
    }
    currentTab(0, configData.value);
  });
});

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
    style.value = nVal.styleConfig.tabVal;
    isUpdate.value = true;
  },
  { deep: true },
);

watch(
  () => props.configObj?.styleConfig?.tabVal,
  () => {
    if (!configData.value.picList) return;
    count = defaults.value.styleConfig?.count;
    picArrayConcat(count);
    configData.value.picList.splice(count);
    currentIndex.value = 0;
    let list = defaults.value.menuConfig.list[0];
    if (configData.value.picList[0]) {
      list.img = configData.value.picList[0].image;
      list.info[0].value = configData.value.picList[0].link;
    }
    lis = document.getElementsByClassName('lay-item');
  },
  { deep: true, immediate: true },
);

watch(
  () => props.configObj?.picStyle?.docPicList,
  () => {
    if (props.configObj?.styleConfig?.tabVal == 11) {
      props.configObj.picStyle.docPicList.map((e, i) => {
        props.configObj.picStyle.docPicList[i].img = props.configObj.picStyle.picList[i].image;
        props.configObj.picStyle.docPicList[i].link = props.configObj.picStyle.picList[i].link;
      });
    }
  },
  { deep: true, immediate: true },
);

function currentTab(e, data) {
  selPicBox.value = e;
  currentIndex.value = e;
  configData.value.tabVal = e;
  if (defaults.value.menuConfig.isCube) {
    if (configData.value.tabVal !== 11) {
      let list = defaults.value.menuConfig.list[0];
      if (data.picList[e] && data.picList[e].image) {
        list.img = data.picList[e].image;
        list.info[0].value = data.picList[e].link;
      } else {
        list.img = '';
        list.info[0].value = '';
      }
    } else {
      selPicBox.value = e;
      let list = defaults.value.docPicList;
      if (data.menuConfig.picStyle.picList[e].image) {
        list[e].img = data.menuConfig.picStyle.picList[e].image;
        list[e].info[0].value = data.menuConfig.picStyle.docPicList[e].value;
      } else {
        list[0].img = '';
        list[0].info[0].value = '';
      }
    }
  }
}
function picArrayConcat(count) {
  for (let i = configData.value.picList.length; i < count; i++) {
    configData.value.picList.push(JSON.parse(JSON.stringify(arrayObj)));
  }
}
// 删除指定热区
function delAreaBox(index) {
  /* 删除某个热区 */
  selBoxList.value.splice(index, 1);
  props.configObj.picStyle.picList.splice(index, 1);
  props.configObj.picStyle.picList.push({ image: '', link: '' });
  if (selBoxList.value.length) currentTab(selBoxList.value.length - 1, configData.value);
}
function initRect() {
  if (rect) {
    document.getElementById('lay1').removeChild(rect);
  }
}
//处理鼠标按下事件
function clickBox(event) {
  if (select) {
    let boxData = up();
    try {
      if (selBoxList.value.length && selBoxList.value.length == 1 && selBoxList.value[0].doc.w === 0) {
        selBoxList.value[0].doc = boxData;
      } else {
        selBoxList.value.push({
          img: '',
          link: '',
          doc: boxData,
        });
      }
      currentTab(selBoxList.value.length - 1, configData.value);
    } catch (error) {
      console.log(error);
    }

    selPicBox.value = selBoxList.value.length ? selBoxList.value.length - 1 : 0;
    return;
  }
  // 鼠标按下时才允许处理鼠标的移动事件
  select = true;
  rect = document.createElement('div');
  // 框选div 样式
  rect.style.cssText =
    'position:absolute;width:0px;height:0px;font-size:0px;margin:0px;padding:0px;border:1px dashed #0099FF;background-color:#C3D5ED;z-index:1000;filter:alpha(opacity:60);opacity:0.6;display:none;';
  rect.id = 'selectDiv';
  // 添加到lay1下
  document.getElementById('lay1').appendChild(rect);
  // 取得鼠标按下时的坐标位置
  downX = event.layerX;
  downY = event.layerY;
  rect.style.left = downX + 'px';
  rect.style.top = downY + 'px';
  //设置你要画的矩形框的起点位置
  rect.style.left = downX + 'px';
  rect.style.top = downY + 'px';
}

//鼠标抬起事件
function up() {
  let topList = [];
  let leftList = [];
  for (let i = 0; i < lis.length; i++) {
    //将移动的div的四个点和和div元素的四个点进行比较
    if (
      //判断div元素 右边框的位置大于移动div的左起始点
      rect.offsetLeft < lis[i].offsetLeft + lis[i].offsetWidth &&
      //判断div元素 下边框的位置大于移动div的上起始点
      lis[i].offsetTop + lis[i].offsetHeight > rect.offsetTop &&
      // 判断div元素左边框的位置小于移动div的右起始点
      rect.offsetLeft + rect.offsetWidth > lis[i].offsetLeft &&
      // 判断div元素上边框的位置小于移动div的下起始点
      rect.offsetTop + rect.offsetHeight > lis[i].offsetTop
    ) {
      //将已选中的样式改变
      if (lis[i].className.indexOf('seled') == -1) {
        topList.push(lis[i].offsetTop);
        leftList.push(lis[i].offsetLeft);
      }
    } else {
      //如果没有选中则清除样式
      if (lis[i].className.indexOf('seled') != -1) {
        lis[i].className = 'lay-item';
      }
    }
    //鼠标抬起,就不允许在处理鼠标移动事件
    select = false;
  }

  //隐藏图层
  if (rect) {
    document.getElementById('lay1').removeChild(rect);
  }

  return {
    startX: getMin(leftList),
    startY: getMin(topList),
    w: getMax(leftList) - getMin(leftList) + 93.75,
    h: getMax(topList) - getMin(topList) + 93.75,
  };
}
// 删除（遗留未被调用）
function del() {
  emit('delAreaBox', areaDataIndex);
}
function getMin(arr) {
  let min = arr[0];
  for (let i = 1; i < arr.length; i++) {
    if (arr[i] < min) {
      min = arr[i];
    }
  }
  return min;
}
function getMax(arr) {
  let max = arr[0];
  for (let i = 1; i < arr.length; i++) {
    if (arr[i] > max) {
      max = arr[i];
    }
  }
  return max;
}
function out() {
  if (rect) {
    select = false;
    document.getElementById('lay1').removeChild(rect);
  }
}
//鼠标移动事件,最主要的事件
function move(event) {
  event.preventDefault();
  if (!select) return;
  /*
        这个部分,根据你鼠标按下的位置,和你拉框时鼠标松开的位置关系,可以把区域分为四个部分,根据四个部分的不同,
        我们可以分别来画框,否则的话,就只能向一个方向画框,也就是点的右下方画框.
        */
  if (select) {
    window.requestAnimationFrame(() => {
      // 取得鼠标移动时的坐标位置
      mouseX2 = event.layerX - 5;
      mouseY2 = event.layerY - 5;
      // 显示框选元素
      if (rect.style.display == 'none') {
        rect.style.display = '';
      }
      rect.style.left = Math.min(mouseX2, downX) + 'px';
      rect.style.top = Math.min(mouseY2, downY) + 'px';
      rect.style.width = mouseX2 - downX + 'px';
      rect.style.height = mouseY2 - downY + 'px';
    });
  }

  // 阻止事件上传
  window.event.cancelBubble = true;
  // 阻止默认事件
  window.event.returnValue = false;
}
</script>
<style scoped lang="scss">
:deep(.ivu-divider-horizontal) {
  margin: 12px 0;
}

img {
  object-fit: cover;
  width: 100%;
  height: 100%;
}

.empty-box {
  color: #8c8c8c;
  font-size: 12px;
  border-radius: 0;
  background-color: #eee;
  width: 100%;
  border: 1px solid #ddd;
  text-align: center;
}

.mobile-page {
  .tip {
    margin-bottom: 2px;
  }

  .advert {
    cursor: pointer;
    padding: 0 15px 20px 15px;

    .advertItem07 {
      height: 185px;

      &.on {
        img {
          border: 1px solid var(--prev-color-primary) !important;
        }

        .empty-box {
          border: 1px solid var(--prev-color-primary) !important;
          color: var(--prev-color-primary);
        }
      }
    }

    .advertItem08 {
      .item {
        height: 185px;

        .pic {
          width: 50%;
          height: 100%;

          &.on {
            img {
              border: 1px solid var(--prev-color-primary) !important;
            }

            .empty-box {
              border: 1px solid var(--prev-color-primary) !important;
              color: var(--prev-color-primary);
            }
          }
        }

        &.on {
          img {
            border: 1px solid var(--prev-color-primary) !important;
          }

          .empty-box {
            border: 1px solid var(--prev-color-primary) !important;
            color: var(--prev-color-primary);
          }
        }
      }

      .items {
        .pic {
          width: 33.33333%;
          height: 185px;

          &.on {
            img {
              border: 1px solid var(--prev-color-primary) !important;
            }

            .empty-box {
              border: 1px solid var(--prev-color-primary) !important;
              color: var(--prev-color-primary);
            }
          }
        }
      }
    }

    .advertItem01 {
      width: 100%;
      height: 100%;

      .empty-box {
        height: 366px;
        border: 1px solid #ddd;
      }

      img {
        width: 100%;
        height: 100%;
        border: 1px solid var(--prev-color-primary);
      }
    }

    .advertItem02 {
      width: 370px;
      height: 370px;

      .item {
        width: 50%;
        height: 370px;

        img {
          width: 100%;
          height: 100%;
        }

        &.on {
          img {
            border: 1px solid var(--prev-color-primary) !important;
          }

          .empty-box {
            border: 1px solid var(--prev-color-primary) !important;
            color: var(--prev-color-primary);
          }
        }

        .empty-box {
          border-right: 1px solid #eee;
        }

        &:nth-child(2) {
          .empty-box {
            border-right: 1px solid #ddd;
          }
        }
      }
    }

    .advertItem03 {
      .item {
        width: 33.3333%;
        height: 370px;

        &.on {
          img {
            border: 1px solid var(--prev-color-primary) !important;
          }

          .empty-box {
            border: 1px solid var(--prev-color-primary) !important;
            color: var(--prev-color-primary);
          }
        }

        .empty-box {
          border-right: 1px solid #eee;
        }

        &:nth-child(2) {
          .empty-box {
            border-right: 1px solid #eee;
          }
        }

        &:nth-child(3) {
          .empty-box {
            border-right: 1px solid #ddd;
          }
        }
      }
    }

    .advertItem04 {
      .item {
        width: 50%;
        height: 370px;

        .empty-box {
          height: 100%;
        }

        img {
          width: 100%;
          height: 100%;
        }

        &.on {
          img {
            border: 1px solid var(--prev-color-primary) !important;
          }

          .empty-box {
            border: 1px solid var(--prev-color-primary) !important;
            color: var(--prev-color-primary);
          }
        }

        .pic {
          width: 100%;
          height: 185px;

          .picItem {
            width: 50%;
            height: 100%;

            &.on {
              img {
                border: 1px solid var(--prev-color-primary) !important;
              }

              .empty-box {
                border: 1px solid var(--prev-color-primary) !important;
                color: var(--prev-color-primary);
              }
            }
          }

          &.on {
            img {
              border: 1px solid var(--prev-color-primary) !important;
            }

            .empty-box {
              border: 1px solid var(--prev-color-primary) !important;
              color: var(--prev-color-primary);
            }
          }
        }

        &:nth-child(1) {
          .empty-box {
            border-right: 1px solid #eee;
          }
        }

        &:nth-child(2) {
          .pic {
            &:nth-child(2) {
              .empty-box {
                border-top: 1px solid #eee;
              }
            }
          }
        }
      }
    }

    .advertItem05 {
      .item {
        width: 25%;

        &.on {
          img {
            border: 1px solid var(--prev-color-primary) !important;
          }

          .empty-box {
            border: 1px solid var(--prev-color-primary) !important;
            color: var(--prev-color-primary);
          }
        }

        .empty-box {
          height: 94.75px;
        }

        &:nth-child(4) {
          .empty-box {
            border-right: 1px solid #ddd;
          }
        }

        &:nth-child(2) {
          .empty-box {
            border-right: 1px solid #eee;
          }
        }
      }
    }

    .advertItem06 {
      .item {
        width: 50%;
        height: 185px;

        img {
          width: 100%;
          height: 100%;
        }

        &.on {
          img {
            border: 1px solid var(--prev-color-primary) !important;
          }

          .empty-box {
            border: 1px solid var(--prev-color-primary) !important;
            color: var(--prev-color-primary);
          }
        }

        .empty-box {
          height: 100%;
          border-right: 1px solid #eee;
          border-bottom: 1px solid #eee;
        }

        &:nth-child(2) {
          .empty-box {
            border-right: 1px solid #ddd;
          }
        }

        &:nth-child(3) {
          .empty-box {
            border-bottom: 1px solid #ddd;
          }
        }

        &:nth-child(4) {
          .empty-box {
            border-right: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
          }
        }
      }
    }

    .advertItem11 {
      width: 375px;
      height: 375px;
      visibility: visible;
      position: relative;

      .seled {
        border: 1px solid red;
        background-color: #d6dff7;
      }

      .lay-item {
        width: 93.75px;
        height: 93.75px;

        .empty-box {
          height: 100%;
        }

        img {
          width: 100%;
          height: 100%;
        }

        &.on {
          img {
            border: 1px solid var(--prev-color-primary) !important;
          }

          .empty-box {
            border: 1px solid var(--prev-color-primary) !important;
            color: var(--prev-color-primary);
          }
        }

        .pic {
          width: 100%;
          height: 100%;

          .picItem {
            width: 50%;

            &.on {
              img {
                border: 1px solid var(--prev-color-primary) !important;
              }

              .empty-box {
                border: 1px solid var(--prev-color-primary) !important;
                color: var(--prev-color-primary);
              }
            }
          }

          &.on {
            img {
              border: 1px solid var(--prev-color-primary) !important;
            }

            .empty-box {
              border: 1px solid var(--prev-color-primary) !important;
              color: var(--prev-color-primary);
            }
          }
        }

        .empty-box {
          border-right: 1px solid #ddd;
        }

        .empty-box {
          border-top: 0px solid #ddd;
        }
      }
    }

    .pic-box {
      position: relative;
    }

    .areaBox.active {
      border: 1px solid var(--prev-color-primary);
    }

    .areaBox {
      position: absolute;
      background: #eee;
      border: 1px solid #dddddd;
      display: flex;
      justify-content: center;
      align-items: center;
      color: var(--prev-color-primary);
      font-size: 12px;
      cursor: pointer;
      z-index: 11;

      .prompt-text {
        overflow: hidden;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        max-width: 100%;
        max-height: 100%;
        text-align: center;
        align-items: center;
        color: var(--prev-color-primary);
      }

      .del {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 16px;
        height: 16px;
        line-height: 16px;
        font-size: 12px;
        background: var(--prev-color-primary);
        color: #fff;
        text-align: center;
        border-radius: 0 0 0 3px;
        position: absolute;
        right: 7px;
        top: 7px;
        transform: translate3d(50%, -50%, 0);
        cursor: default;
      }

      .del:hover {
        width: 16px;
        height: 16px;
        line-height: 16px;
      }
    }
  }
}
</style>
