<template>
  <div
    :style="{
      width: areaInit.areaWidth + 'px',
      height: areaInit.areaHeight + 'px',
      left: areaInit.starX + 'px',
      top: areaInit.starY + 'px',
    }"
    class="areaBox"
    @dblclick="editBoxShow = true"
    @mousedown.left.stop="mouseDownLint($event)"
    @mouseup.left.stop="mouseUp($event)"
  >
    <div class="prompt-text">
      <div class="prompt-item num">热区 {{ areaInit.number }}</div>
      <div class="prompt-item" :style="{ color: isSet ? '#2d8cf0' : '#f00' }">
        {{ isSet ? '(已设置)' : '(未设置)' }}
      </div>
    </div>
    <!--删除-->
    <div class="del" @click.stop="del()">
      <i class="el-icon-close" size="16" />
    </div>
    <!--形变点-->
    <div class="shape" @mousedown.left.stop="shapeDown($event)" @mouseup.left.stop="mouseUp($event)" />
    <!--编辑框-->

    <div>
      <el-dialog v-model="editBoxShow" title="设置热区" width="560px" append-to-body>
        <div class="area-set">
          <div class="area-label">热区跳转链接：</div>
          <div class="area-content">
            <el-input v-model="url" style="width: 100%" placeholder="选择跳转链接">
              <template #suffix><i class="el-icon-link" @click="getLink()" /></template>
            </el-input>
          </div>
        </div>
        <template #footer
          ><span class="dialog-footer">
            <el-button @click.stop="editBoxShow = false">取 消</el-button>
            <el-button type="primary" @click.stop="addURL">确 定</el-button>
          </span></template
        >
      </el-dialog>
      <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import linkaddress from '@/components/linkaddress';

defineOptions({ name: 'AreaBox' });

const emit = defineEmits(['delAreaBox', 'addURL']);

const props = defineProps({
  areaInit: {
    type: Object,
    default: () => {},
  },
  areaDataIndex: {
    type: Number,
    default: null,
  },
  link: {
    type: String,
    default: '',
  },
  title: {
    type: String,
    default: '',
  },
  type: {
    type: Number,
    default: -1,
  },
  parentWidth: {
    type: Number,
    default: 0,
  },
  parentHeight: {
    type: Number,
    default: 0,
  },
});

const areaTitle = ref('');
const url = ref('');
const editBoxShow = ref(false);
const promptText = ref('双击设置热区');
// box操作初始点（拖动过程中的临时坐标，无需响应式）
const move = {
  // 拖动
  startX: 0,
  starY: 0,
  // 形变
  start1X: 0,
  start1Y: 0,
};
let starX = 0;
let starY = 0;
let star1X = 0;
let star1Y = 0;

const linkaddres = ref(null);

const isSet = computed(() => !!props.link);

watch(
  () => props.title,
  (val) => {
    areaTitle.value = val;
  },
);
watch(
  () => props.link,
  (val) => {
    url.value = val;
  },
);

onMounted(() => {
  url.value = props.link;
});

// 删除
function del() {
  emit('delAreaBox', props.areaDataIndex);
}
// 添加网址
function addURL() {
  if (!url.value) {
    ElMessage.error('请输入链接');
  } else {
    emit('addURL', props.areaDataIndex, url.value);
    editBoxShow.value = false;
  }
}
// 开始拖动限制范围
function mouseDownLint(e) {
  e.preventDefault();
  starX = e.clientX;
  starY = e.clientY;
  const childrenDiv = e.target || e;
  //获取子元素的宽高
  let childrenWidth = childrenDiv.getBoundingClientRect().width;
  let childrenHight = childrenDiv.getBoundingClientRect().height;
  // console.log(childrenWidth, childrenHight)
  if (!document.onmousemove) {
    const initX = props.areaInit.starX;
    const initY = props.areaInit.starY;
    document.onmousemove = (ev) => {
      // 移动位置
      let nLeft = initX + ev.clientX - starX;
      let nTop = initY + ev.clientY - starY;
      nLeft = nLeft <= 0 ? 0 : nLeft; //判断左边是否越界
      nTop = nTop <= 0 ? 0 : nTop; //判断上边是否越界
      let nRight = nLeft + childrenWidth;
      let nBottom = nTop + childrenHight;
      // 判断右边是否越界
      if (nRight >= props.parentWidth) {
        nLeft = props.parentWidth - childrenWidth;
      }
      // 判断下边是否越界
      if (nBottom >= props.parentHeight) {
        nTop = props.parentHeight - childrenHight;
      }
      props.areaInit.starX = nLeft;
      props.areaInit.starY = nTop;
    };
  }
}
// 开始拖动不限制范围
function mouseDown(e) {
  e.preventDefault();
  starX = e.clientX;
  starY = e.clientY;
  if (!document.onmousemove) {
    const initX = props.areaInit.starX;
    const initY = props.areaInit.starY;
    document.onmousemove = (ev) => {
      props.areaInit.starX = initX + ev.clientX - starX;
      props.areaInit.starY = initY + ev.clientY - starY;
    };
  }
}
// 结束拖动/变形
function mouseUp() {
  document.onmousemove = null;
}
// 形变开始
function shapeDown(e) {
  e.preventDefault();

  star1X = e.clientX;
  star1Y = e.clientY;
  // 获取左部和底部的偏移量

  if (!document.onmousemove) {
    const initX = props.areaInit.areaWidth;
    const initY = props.areaInit.areaHeight;
    document.onmousemove = (ev) => {
      props.areaInit.areaWidth = initX + ev.clientX - star1X;
      props.areaInit.areaHeight = initY + ev.clientY - star1Y;
    };
  }
}
function getLink() {
  linkaddres.value.modals = true;
}
function linkUrl(e) {
  url.value = e;
}
</script>

<style lang="scss" scoped>
.areaBox {
  position: absolute;
  background: rgba(24, 144, 255, 0.5);
  border: 1px dashed var(--prev-color-primary);
  display: flex;
  justify-content: center;
  align-items: center;
  color: var(--prev-color-primary);
  font-size: 12px;
  cursor: move;
  .prompt-text {
    overflow: hidden;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    max-width: 100%;
    max-height: 100%;
    text-align: center;
    align-items: center;
    color: #fff;
    .num {
      font-size: 12px;
    }
    .prompt-item {
      color: #fff;
      margin: 0 2px;
    }
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
  .shape {
    position: absolute;
    width: 7px;
    height: 7px;
    background: transparent;
    right: 0;
    bottom: 0;
    transform: translate3d(50%, 50%, 0);
    cursor: nwse-resize;
  }
}
.area-set {
  display: flex;
  align-items: center;
  margin: 16px 0;
}
.area-label {
  width: 100px;
}
.area-content {
  flex: 1;
}
</style>
