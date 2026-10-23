<template>
  <div>
    <el-dialog title="编辑热区" v-model="dialogVisible" @opened="openModal" fullscreen>
      <div class="operationFloor">
        <div class="imgBox" @mouseup.left.stop="changeStop()">
          <div ref="container" id="img-box-container" class="container">
            <img
              ref="backgroundImg"
              :src="imgs"
              ondragstart="return false;"
              oncontextmenu="return false;"
              onselect="document.selection.empty();"
              alt="img"
              @mousedown.left.stop="mouseDown($event)"
            />
            <!--draw hotpot-->
            <div
              v-show="caseShow"
              :style="{
                width: areaWidth + 'px',
                height: areaHeight + 'px',
                left: starX + 'px',
                top: starY + 'px',
              }"
              class="area"
            />
            <!--be hotpot-->
            <AreaBox
              v-for="(item, index) in areaData"
              :key="'area' + index"
              :area-data-index="index"
              :link="item.link"
              :title="item.title"
              :type="parseInt(item.type)"
              :area-init="areaData[index]"
              :parent-width="parentWidth"
              :parent-height="parentHeight"
              @delAreaBox="delAreaBox"
              @addURL="addURL"
            />
          </div>
        </div>
        <!-- 热区链接配置 -->
        <div class="form">
          <h2 class="mb20">图片热区</h2>
          <el-alert type="warning" :closable="false" show-icon>框选热区范围，双击设置热区信息</el-alert>

          <div v-for="(item, index) in areaData" :key="index" class="form-row">
            <div class="form-item">
              <span class="num">热区{{ item.number }}</span>
            </div>
            <div class="form-item label">
              <div>
                <el-input
                  icon="ios-arrow-forward"
                  v-model="item.link"
                  :style="linkInputStyle"
                  placeholder="选择跳转链接"
                >
                  <template #suffix><i class="el-icon-link" @click="getLink(index)" /></template>
                </el-input>
              </div>
            </div>
            <i class="el-icon-delete" @click="delAreaBox(index)" />
          </div>
        </div>
      </div>
      <template #footer
        ><div>
          <el-button class="mr20" type="primary" @click="saveAreaData"> 完成 </el-button>
        </div></template
      >
    </el-dialog>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script setup>
import { nextTick, onMounted, ref, watch } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import AreaBox from './AreaBox';
import linkaddress from '@/components/linkaddress';

defineOptions({ name: 'OperationFloor' });

const emit = defineEmits(['delAreaData', 'saveAreaData']);

const props = defineProps({
  /**
   * @description 图片数据对象
   * @type {ImgData}
   */
  imgs: {
    type: String, // 图片类型
    default: () => '', // 默认值为空字符串
  },
  /**
   * @description 是否为热门汤品
   * @type {boolean}
   */
  isHotPot: {
    type: Boolean, // 布尔类型
    default: () => false, // 默认值为false
  },
  /**
   * @description 图片区域数据对象
   * @type {AreaData[]}
   */
  imgAreaData: {
    type: Array, // 数组类型
    default: () => [], // 默认值为空数组
  },
  /**
   * @description 链接输入框样式对象
   * @type {LinkInputStyle}
   */
  linkInputStyle: {
    type: Object, // 对象类型
    default: () => ({
      // 默认值为一个包含width属性的对象
      width: '300px',
    }),
  },
});

const dialogVisible = ref(false);
const starX = ref(0);
const starY = ref(0);
const areaWidth = ref(0);
const areaHeight = ref(0);
const caseShow = ref(false);
const nowImgWidth = ref(null);
const areaData = ref([]);
const imgNum = ref(1);
const parentWidth = ref(0);
const parentHeight = ref(0);
const defaultWidth = 750;
const itemIndex = ref(0);
const linkaddres = ref(null);

watch(
  () => props.imgAreaData,
  (val) => {
    areaData.value = [...val];
  },
);

onMounted(() => {
  areaData.value = [...props.imgAreaData];
});

function openModal() {
  nextTick(() => {
    const parentDiv = document.querySelector('#img-box-container');
    //获取元素的宽高
    parentWidth.value = defaultWidth;
    // parentWidth.value = parentDiv.clientWidth;
    parentHeight.value = parentDiv.clientHeight;
    // console.log("parentWidth", parentWidth.value, parentHeight.value)
  });
}
function closeModal() {
  ElMessageBox.confirm('未保存内容，是否在离开前放弃保存？', '提示信息', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning',
  })
    .then(() => {
      dialogVisible.value = false;
    })
    .catch(() => {
      ElMessage({
        type: 'info',
        message: '已取消',
      });
    });
}
// 绘画热区开始
function mouseDown(e) {
  e.preventDefault();
  caseShow.value = true;
  // 记录滑动的初始值
  starX.value = e.layerX - 5;
  starY.value = e.layerY - 5;
  // 鼠标滑动的过程
  if (!document.onmousemove) {
    let maxWidth = defaultWidth - e.layerX;
    document.onmousemove = (ev) => {
      if (ev.layerX - starX.value < maxWidth) {
        areaWidth.value = ev.layerX - starX.value - 5;
      } else {
        areaWidth.value = maxWidth;
      }
      areaHeight.value = ev.layerY - starY.value - 5;
    };
  }
}
// 绘画热区结束
function changeStop() {
  document.onmousemove = null;
  imgNum.value = areaData.value.length + 1;
  if (caseShow.value && areaWidth.value > 10 && areaHeight.value > 10) {
    const data = {
      number: imgNum.value,
      starX: starX.value,
      starY: starY.value,
      areaWidth: areaWidth.value,
      areaHeight: areaHeight.value,
      nowImgWidth: defaultWidth,
      link: '',
    };
    areaData.value.push(data);
  }
  // 初始化绘图
  caseShow.value = false;
  starX.value = 0;
  starY.value = 0;
  areaWidth.value = 0;
  areaHeight.value = 0;
}
// 删除指定热区
function delAreaBox(index) {
  /* 删除某个热区 */
  areaData.value.splice(index, 1);
  emit('delAreaData', areaData.value);
  /* 删除后 每个热区按顺序重新编号 */
  if (areaData.value) {
    const arr = areaData.value.filter((i) => i.number > index);
    if (!arr) return;
    arr.forEach((i) => i.number--);
    if (areaData.value[areaData.value.length - 1]) {
      imgNum.value = areaData.value[areaData.value.length - 1].number + 1;
    } else {
      imgNum.value = 1;
    }
  }
}
// 添加网址
function addURL(index, url) {
  let obj = {
    ...areaData.value[index],
    link: url,
  };
  areaData.value[index] = obj;
}
// 保存热区信息
function saveAreaData() {
  if ((areaData.value && !areaData.value.length) || !checkData(areaData.value)) {
    ElMessage.error('热区是否配置链接、是否至少添加一个热区?');
    return;
  }
  emit('saveAreaData', areaData.value);
  dialogVisible.value = false;
  ElMessage.success('编辑成功!');
}
/**
 * 检查列表中每个元素是否都有 link 属性
 * @param {Array} list - 待检查的列表
 * @returns {Boolean} - 是否所有元素都有 link 属性
 */
function checkData(list) {
  let isCheck = true;
  list.some((val) => {
    if (!val.link) {
      isCheck = false;
    }
  });
  return isCheck;
}
/**
 * @description 获取链接地址并打开添加链接的模态框
 * @param {number} index - 当前项的索引值
 */
function getLink(index) {
  // 设置当前项的索引值
  itemIndex.value = index;
  // 打开添加链接的模态框
  linkaddres.value.modals = true;
}
/**
 * @description 处理链接地址的输入事件
 * @param {string} e - 链接地址
 */
function linkUrl(e) {
  // 将链接地址存储到对应的数据项中
  areaData.value[itemIndex.value].link = e;
}
// script setup 默认封闭，父组件通过模板 ref 驱动 dialogVisible，必须显式暴露
defineExpose({ dialogVisible });
</script>

<style lang="scss" scoped>
:deep(.el-dialog) {
  border-radius: 0px !important;
  .el-alert__icon.is-big {
    font-size: 14px;
    width: 16px;
  }
  .el-alert .el-alert__description {
    margin: 0;
  }
}
.btn {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 16px 0;
}
.dialog-footer {
  text-align: right;
  margin-top: 20px;
  margin-right: 20px;
}
.operationFloor {
  display: flex;
  position: relative;
  max-height: 80vh;
  .header {
    .titleBox {
      display: flex;
      justify-content: space-between;
      align-items: center;
      height: 100px;
      .name {
        font-size: 13px;
        font-weight: bold;
      }
    }
    .textBox {
      font-size: 12px;
      color: #777;
      margin-bottom: 10px;
    }
  }
  .imgBox::-webkit-scrollbar {
    display: none; /* Chrome Safari */
  }
  .imgBox {
    display: flex;
    justify-content: center;
    width: 65%;
    overflow-y: scroll;
    max-height: 800px;
    .container {
      position: relative;
      border: 1px solid #f5f5f5;
    }

    img {
      cursor: crosshair;
      display: block;
      width: 750px;
    }
    .area {
      position: absolute;
      width: 200px;
      height: 200px;
      left: 200px;
      top: 300px;
      background: rgba(#2980b9, 0.3);
      border: 1px dashed #34495e;
    }
  }
}
.form {
  font-size: 12px;
  width: 30%;
  max-height: 800px;
  overflow-y: scroll;
  .form-row {
    display: flex;
    margin: 12px 0;
    align-items: center;
    .form-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      white-space: nowrap;
      margin: 0 5px;
      font-size: 12px;
      .num {
        width: 69px;
        color: #999;
        font-size: 12px;
      }
      .label {
        color: #c7c7c7;
      }
    }
  }
  .el-icon-delete {
    font-size: 16px;
    cursor: pointer;
  }
}
</style>
