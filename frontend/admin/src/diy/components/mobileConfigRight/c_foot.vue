<template>
  <div class="footer" v-if="footConfig">
    <p class="tips">图片建议宽度81*81px；鼠标拖拽左侧圆点可调整导航顺序</p>
    <draggable
      class="dragArea list-group"
      :list="footConfig"
      group="peoples"
      handle=".iconfont"
      :item-key="footItemKey"
    >
      <template #item="{ element: item, index }">
        <div class="box-item">
          <div class="left-tool">
            <span class="iconfont iconxingzhuangjiehe"></span>
          </div>
          <div class="right-wrapper">
            <div class="acea-row" v-if="navStyle != 1">
              <div class="title">图标</div>
              <div class="img-wrapper">
                <div class="img-item" v-for="(img, j) in item.imgList" :key="j" @click="modalPicTap(index, j)">
                  <div class="pictrue" v-if="img">
                    <img :src="img" alt="" />
                    <p class="txt">替换</p>
                  </div>
                  <div class="empty-img" v-else>
                    <span class="iconfont iconjiahao"></span>
                  </div>
                  <div class="name">{{ j == 0 ? '选中' : '未选中' }}</div>
                </div>
              </div>
            </div>
            <div class="c_row-item" v-if="navStyle != 2">
              <el-col class="label" :span="4"> 名称 </el-col>
              <el-col class="slider-box" :span="20">
                <el-input v-model="item.name" placeholder="选填不超过10个字" />
              </el-col>
            </div>
            <div class="c_row-item">
              <el-col class="label" :span="4"> 链接 </el-col>
              <el-col class="slider-box" :span="20">
                <div>
                  <el-input v-model="item.link" placeholder="选填不超过10个字">
                    <template #suffix><i class="el-icon-link" @click="getLink(index)" /></template>
                  </el-input>
                </div>
              </el-col>
            </div>
          </div>
          <div class="del-box" @click="deleteMenu(index)">
            <span class="iconfont iconcha"></span>
          </div>
        </div>
      </template>
    </draggable>
    <el-button class="add-btn" @click="addMenu" v-if="footConfig.length < 5">+ 添加板块</el-button>
    <div>
      <el-dialog v-model="modalPic" width="960px" title="上传底部菜单" :mask-closable="false">
        <uploadPictures
          :isChoice="isChoice"
          @getPic="getPic"
          :gridBtn="gridBtn"
          :gridPic="gridPic"
          v-if="modalPic"
        ></uploadPictures>
      </el-dialog>
    </div>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { ElMessageBox } from 'element-plus';
import draggable from 'vuedraggable';
import uploadPictures from '@/components/uploadPictures';
import linkaddress from '@/components/linkaddress';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

import __asset_noPictrue_0 from '@diy/assets/images/noPictrue.png';

defineOptions({ name: 'c_foot' });

const props = defineProps({
  configObj: {
    type: Object,
    default: function () {
      return {};
    },
  },
  configNme: {
    type: String,
    default: '',
  },
});

const mobildConfigStore = useMobildConfigStore();

const footConfig = ref([]);
const modalPic = ref(false);
const isChoice = ref('单选');
const itemIndex = ref(0);
const itemChildIndex = ref(0);
const gridBtn = ref({
  xl: 4,
  lg: 8,
  md: 8,
  sm: 8,
  xs: 8,
});
const gridPic = ref({
  xl: 6,
  lg: 8,
  md: 12,
  sm: 12,
  xs: 12,
});
const navStyle = ref(0);
const noPic = __asset_noPictrue_0;

const linkaddres = ref(null);

// vuedraggable4 item-key（数据无唯一 id，用索引）
function footItemKey(element) {
  return footConfig.value.indexOf(element);
}

// created
footConfig.value = props.configObj[props.configNme];

watch(
  () => props.configObj,
  (nVal, oVal) => {
    footConfig.value = nVal[props.configNme];
    navStyle.value = nVal.navStyleConfig.tabVal;
  },
  { deep: true },
);

function linkUrl(e) {
  footConfig.value[itemIndex.value].link = e;
}
function getLink(index) {
  itemIndex.value = index;
  linkaddres.value.modals = true;
}
// 点击图文封面
function modalPicTap(parent, child) {
  itemIndex.value = parent;
  itemChildIndex.value = child;
  modalPic.value = true;
}
// 获取图片信息
function getPic(pc) {
  nextTick(() => {
    footConfig.value[itemIndex.value].imgList[itemChildIndex.value] = pc.att_dir;
    modalPic.value = false;
    mobildConfigStore.footUpdata(footConfig.value);
  });
}
// 添加模块
function addMenu() {
  let obj = {
    imgList: [noPic, noPic],
    name: '自定义',
    link: '',
  };
  footConfig.value.push(obj);
}
function deleteMenu(index) {
  ElMessageBox({
    title: '提示',
    message: '是否确定删除该菜单',
    showCancelButton: true,
    cancelButtonText: '取消',
    confirmButtonText: '删除',
    iconClass: 'el-icon-warning',
    confirmButtonClass: 'btn-custom-cancel',
  })
    .then(() => {
      footConfig.value.splice(index, 1);
    })
    .catch(() => {});
}
</script>

<style scoped lang="scss">
:deep(.ivu-input) {
  font-size: 12px !important;
}
.dragArea {
  padding-bottom: 20px;
}
.footer {
  padding: 0 15px;
}
.tips {
  font-size: 12px;
  color: #bbbbbb;
}
.box-item {
  position: relative;
  display: flex;
  margin-top: 15px;
  padding: 20px 20px 20px 0;
  background: #f9f9f9;
  border-radius: 3px;
  .del-box {
    position: absolute;
    right: -13px;
    top: -18px;
    cursor: pointer;
    .iconfont {
      color: #ccc;
      font-size: 24px;
    }
  }
}
.left-tool {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 42px;
  .iconfont {
    color: #999;
    font-size: 18px;
    cursor: move;
  }
}
.right-wrapper {
  flex: 1;
  .title {
    color: #999999;
    font-size: 12px;
    width: 50px;
    margin-top: 23px;
  }
  .img-wrapper {
    display: flex;
    .img-item {
      width: 64px;
      margin-right: 20px;
      .name {
        color: #bbbbbb;
        font-size: 12px;
        text-align: center;
        margin-top: 7px;
      }
      .pictrue {
        width: 100%;
        height: 64px;
        cursor: pointer;
        border: 1px solid #eeeeee;
        position: relative;
        border-radius: 3px;
      }
      img {
        display: block;
        width: 100%;
        height: 100%;
      }
      .empty-img {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        width: 100%;
        height: 100%;
        font-size: 12px;
        color: #bfbfbf;
        border: 1px solid #eeeeee;
        border-radius: 3px;
        .iconfont {
          font-size: 24px;
        }
      }
      .txt {
        position: absolute;
        left: 0;
        bottom: 0;
        width: 100%;
        height: 22px;
        line-height: 22px;
        text-align: center;
        background: rgba(0, 0, 0, 0.5);
        color: #fff;
        font-size: 12px;
        border-radius: 0 0 3px 3px;
      }
    }
  }
  .c_row-item {
    margin-top: 10px;
    .label {
      color: #999999;
      font-size: 12px;
    }
  }
}
.add-btn {
  width: 100%;
  height: 40px;
  margin-bottom: 30px;
}
</style>
