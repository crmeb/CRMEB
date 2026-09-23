<template>
  <div class="tabBars">
    <div class="title">{{ datas[name].title }}</div>
    <draggable
      class="dragArea list-group"
      :list="datas[name].list"
      group="peoples"
      handle=".iconfont"
      :item-key="tabBarItemKey"
    >
      <template #item="{ element: item, index }">
        <div class="box-item">
          <div class="left-tool">
            <span class="iconfont icondrag2"></span>
          </div>
          <div class="right-wrapper">
            <div class="img-wrapper">
              <div class="img-item" v-for="(img, j) in item.imgList" v-db-click @click="modalPicTap('单选', index, j)">
                <img :src="img" alt="" v-if="img" />
                <p class="txt" v-if="img">{{ j == 0 ? '选中' : '未选中' }}</p>
                <div class="empty-img" v-else>
                  <span class="iconfont iconjiahao"></span>
                  <p>{{ j == 0 ? '选中' : '未选中' }}</p>
                </div>
              </div>
            </div>
            <div class="c_row-item">
              <el-col class="label" :span="4"> 名称 </el-col>
              <el-col :span="19" class="slider-box">
                <el-input v-model="item.name" placeholder="选填不超过10个字" />
              </el-col>
            </div>
            <div class="c_row-item">
              <el-col class="label" :span="4"> 链接 </el-col>
              <el-col :span="19" class="slider-box">
                <el-input v-model="item.link" placeholder="选填不超过10个字" />
              </el-col>
            </div>
          </div>
          <div class="del-box" v-db-click @click="deleteMenu(index)">
            <span class="iconfont iconcha"></span>
          </div>
        </div>
      </template>
    </draggable>
    <div class="add-btn" v-if="datas[name].list.length < 5">
      <el-button
        type="primary"
        ghost
        style="width: 100%; height: 40px; border-color: var(--prev-color-primary); color: var(--prev-color-primary)"
        v-db-click
        @click="addMenu"
        >添加图文导航
      </el-button>
    </div>
    <div>
      <el-dialog
        v-model="modalPic"
        width="950px"
        scrollable
        footer-hide
        :show-close="true"
        title="上传商品图"
        :mask-closable="false"
        :z-index="888"
      >
        <uploadPictures
          :isChoice="isChoice"
          @getPic="getPic"
          :gridBtn="gridBtn"
          :gridPic="gridPic"
          v-if="modalPic"
        ></uploadPictures>
      </el-dialog>
    </div>
  </div>
</template>

<script setup>
import { nextTick, ref, watch } from 'vue';
import { ElMessageBox } from 'element-plus';
import draggable from 'vuedraggable';
import uploadPictures from '@/components/uploadPictures';

defineOptions({ name: 'c_tab_bar' });

const props = defineProps({
  name: {
    type: String,
  },
  configData: {
    type: null,
  },
  configNum: {
    type: [Number, String],
    default: 'default',
  },
});

const modalPic = ref(false);
const isChoice = ref('单选');
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
const activeIndex = ref(0);
const isSelect = ref(0);
const datas = ref(props.configData[props.configNum]);
const lastObj = ref({});

watch(
  () => props.configData,
  (nVal) => {
    datas.value = nVal[props.configNum];
  },
  { deep: true },
);

// vuedraggable4 item-key（数据无唯一 id，用索引）
function tabBarItemKey(element) {
  return datas.value[props.name].list.indexOf(element);
}

// 添加模块
function addMenu() {
  if (props.configData[props.configNum][props.name].list.length == 0) {
    props.configData[props.configNum][props.name].list.push(lastObj.value);
  } else {
    let obj = JSON.parse(
      JSON.stringify(
        props.configData[props.configNum][props.name].list[
          props.configData[props.configNum][props.name].list.length - 1
        ],
      ),
    );
    props.configData[props.configNum][props.name].list.push(obj);
  }
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
      if (props.configData[props.configNum][props.name].list.length == 1) {
        lastObj.value = props.configData[props.configNum][props.name].list[0];
      }
      props.configData[props.configNum][props.name].list.splice(index, 1);
    })
    .catch(() => {});
}
// 点击图文封面
function modalPicTap(title, index, select) {
  activeIndex.value = index;
  modalPic.value = true;
  isSelect.value = select;
}
// 获取图片信息
function getPic(pc) {
  nextTick(() => {
    props.configData[props.configNum][props.name].list[activeIndex.value].imgList[isSelect.value] = pc.att_dir;
    modalPic.value = false;
  });
}
</script>
<style lang="scss" scoped>
.tabBars .box-item:last-child {
  margin-bottom: 20px;
}
.tabBars {
  .title {
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    font-size: 12px;
    color: #999;
  }
  .box-item {
    position: relative;
    display: flex;
    margin-top: 15px;
    padding: 20px 30px 20px 0;
    border: 1px solid #dddddd;
    border-radius: 3px;
    .del-box {
      position: absolute;
      right: -13px;
      top: -18px;
      cursor: pointer;
      .iconfont {
        color: #999;
        font-size: 30px;
      }
    }
    .left-tool {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 72px;
      .iconfont {
        color: #999;
        font-size: 36px;
        cursor: move;
      }
    }
    .right-wrapper {
      flex: 1;
      .img-wrapper {
        display: flex;
        .img-item {
          position: relative;
          width: 80px;
          height: 80px;
          margin-right: 20px;
          cursor: pointer;
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
            background: #f7f7f7;
            font-size: 12px;
            color: #bfbfbf;
            .iconfont {
              font-size: 16px;
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
            background: rgba(0, 0, 0, 0.4);
            color: #fff;
            font-size: 12px;
          }
        }
      }
      .c_row-item {
        margin-top: 10px;
      }
    }
  }
  .add-btn {
    margin-bottom: 20px;
    width: 100%;
    height: 40px;
  }
}
</style>
