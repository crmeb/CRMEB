<template>
  <div class="hot_imgs">
    <div class="list-box">
      <draggable
        class="dragArea list-group"
        :list="listData"
        group="peoples"
        handle=".move-icon"
        :item-key="uploadPicItemKey"
      >
        <template #item="{ element: item, index }">
          <div class="item">
            <div class="move-icon">
              <span class="iconfont icondrag"></span>
            </div>
            <div class="img-box" v-db-click @click="modalPicTap('单选', index)">
              <img :src="item.pic" alt="" v-if="item.pic && item.pic != ''" />
              <div class="upload-box" v-else>
                <i class="el-icon-picture-outline" style="font-size: 24px"></i>
              </div>
            </div>
            <div class="info">
              <div class="info-item" v-if="item.hasOwnProperty('name')">
                <span>{{ type == 1 ? '管理名称：' : type == 5 ? '广告名称' : '服务名称：' }}</span>
                <div class="input-box">
                  <el-input v-model="item.name" :placeholder="type == 5 ? '请输入名称' : '服务中心'" :maxlength="4" />
                </div>
              </div>
              <div class="info-item">
                <span>链接地址：</span>
                <div class="input-box" v-db-click>
                  <el-input v-model="item.url" placeholder="选择链接">
                    <template #suffix><i class="el-icon-link" @click="getLink(index)" /></template>
                  </el-input>
                </div>
              </div>
              <!-- 开关是否显示 -->
              <div class="info-item">
                <span>是否显示：</span>
                <div class="input-box">
                  <el-switch class="defineSwitch" v-model="item.is_show" active-value="1" inactive-value="0" />
                </div>
              </div>
            </div>
            <div v-if="type != 1" class="delect-btn" v-db-click @click.stop="bindDelete(item, index)">
              <span class="iconfont icondel_1 cup"></span>
            </div>
          </div>
        </template>
      </draggable>
      <div>
        <el-dialog v-model="modalPic" width="950px" title="上传商品图" :close-on-click-modal="false">
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
    <template v-if="listData">
      <div class="add-btn" v-if="type == 2 || (type == 5 && listData.length < 5)">
        <el-button type="primary" ghost style="width: 100px; color: #fff; font-size: 13px" v-db-click @click="addBox"
          >添加{{ type == 5 ? '广告' : '服务' }}</el-button
        >
      </div>
    </template>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script setup>
import iconsPng from '@/assets/images/icons.png';
import { ref, nextTick } from 'vue';
import draggable from 'vuedraggable';
import uploadPictures from '@/components/uploadPictures';
import linkaddress from '@/components/linkaddress';

defineOptions({ name: 'uploadPic' });

const props = defineProps({
  listData: {
    type: Array,
  },
  type: {
    type: Number,
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
const lastObj = ref({
  name: '',
  pic: '',
  url: '',
});
const linkaddres = ref(null);

// vuedraggable4 item-key（数据无唯一 id，用索引）
function uploadPicItemKey(element) {
  return props.listData.indexOf(element);
}

function linkUrl(e) {
  props.listData[activeIndex.value].url = e;
}
function getLink(index) {
  activeIndex.value = index;
  linkaddres.value.modals = true;
}
function addBox() {
  if (props.listData.length == 0) {
    props.listData.push(lastObj.value);
  } else {
    let obj = JSON.parse(JSON.stringify(props.listData[props.listData.length - 1]));
    obj.name = '';
    obj.pic = '';
    obj.url = '';
    props.listData.push(obj);
  }
  // emit('parentFun', props.listData)
}
// 点击图文封面
function modalPicTap(title, index) {
  activeIndex.value = index;
  modalPic.value = true;
}
// 添加自定义弹窗
function addCustomDialog(editorId) {
  window.UE.registerUI(
    'test-dialog',
    function (editor, uiName) {
      let dialog = new window.UE.ui.Dialog({
        iframeUrl: Setting.routePre + '/widget.images/index.html?fodder=dialog',
        editor: editor,
        name: uiName,
        title: '上传图片',
        cssRules: 'width:960px;height:550px;padding:20px;',
      });
      // 参考上面的自定义按钮
      var btn = new window.UE.ui.Button({
        name: 'dialog-button',
        title: '上传图片',
        cssRules: `background-image: url(${iconsPng});background-position: -726px -77px;`,
        onclick: function () {
          // 渲染dialog
          dialog.render();
          dialog.open();
        },
      });

      return btn;
    },
    37,
  );
}
// 获取图片信息
function getPic(pc) {
  nextTick(() => {
    props.listData[activeIndex.value].pic = pc.att_dir;
    modalPic.value = false;
  });
}
// 删除
function bindDelete(item, index) {
  if (props.listData.length == 1) {
    lastObj.value = props.listData[0];
  }
  props.listData.splice(index, 1);
  // emit('parentFun', props.listData)
}
</script>

<style lang="scss" scoped>
.hot_imgs {
  margin-bottom: 20px;
  .title {
    padding: 0 0 13px 0;
    color: #999;
    font-size: 12px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  }
  .list-box {
    .item {
      position: relative;
      display: flex;
      align-items: center;
      margin-top: 10px;
      border: 1px dashed rgba(0, 0, 0, 0.15);
      padding-right: 10px;
      border-radius: 6px;
      .move-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        cursor: move;
      }
      .img-box {
        position: relative;
        width: 50px;
        height: 50px;
        cursor: pointer;
        img {
          width: 100%;
          height: 100%;
        }
      }
      .info {
        flex: 1;
        margin-left: 16px;
        padding-top: 10px;
        .info-item {
          display: flex;
          align-items: center;
          margin-bottom: 10px;

          span {
            width: 70px;
            font-size: 13px;
          }
          .input-box {
            flex: 1;
            :deep(input) {
              cursor: pointer;
            }
          }
          :deep(.ivu-input) {
            font-size: 13px !important;
          }
        }
      }
      .delect-btn {
        position: absolute;
        right: -13px;
        top: -13px;
        .iconfont {
          font-size: 25px;
          color: #ff1818;
        }
      }
    }
  }
  .add-btn {
    margin-top: 24px;
  }
}
.upload-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  background: #ccc;
}
.iconfont {
  color: #dddddd;
  font-size: 28px;
}
</style>
