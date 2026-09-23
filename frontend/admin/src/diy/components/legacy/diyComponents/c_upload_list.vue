<template>
  <div class="hot_imgs">
    <div class="title">
      {{ datas[name].title }}
    </div>
    <div class="list-box">
      <draggable
        class="dragArea list-group"
        :list="datas[name].list"
        group="peoples"
        handle=".move-icon"
        :item-key="uploadListItemKey"
      >
        <template #item="{ element: item, index }">
          <div class="item">
            <div class="move-icon">
              <span class="iconfont icondrag2"></span>
            </div>
            <div class="img-box" v-db-click @click="modalPicTap('单选', index)">
              <img :src="item.img" alt="" v-if="item.img" />
              <div class="upload-box" v-else>
                <i class="el-icon-picture-outline" style="font-size: 24px"></i>
              </div>
              <div v-if="!datas[name].isDelete" class="delect-btn" v-db-click @click.stop="bindDelete(item, index)">
                <i class="el-icon-circle-close" style="font-size: 24px"></i>
              </div>
            </div>
            <div class="info">
              <div class="info-item" v-for="(infos, key) in item.info" :key="key">
                <div class="info-item" v-if="infos.title === '链接'">
                  <span>{{ infos.title }}</span>
                  <div class="input-box" v-db-click>
                    <el-input v-model="infos.value" :placeholder="infos.tips" :maxlength="infos.maxlength">
                      <template #suffix><i class="el-icon-link" @click="getLink(index, key)" /></template>
                    </el-input>
                  </div>
                </div>
                <div v-else class="info-item">
                  <span>{{ infos.title }}</span>
                  <div class="input-box">
                    <el-input
                      v-model="infos.value"
                      :placeholder="infos.tips"
                      :maxlength="infos.maxlength"
                      width="250px"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </draggable>
      <div>
        <el-dialog v-model="modalPic" width="950px" title="上传商品图" :close-on-click-modal="false" :z-index="888">
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
    <template v-if="datas[name]">
      <div class="add-btn" v-if="datas[name].list.length < datas[name].max || datas[name].max == ''">
        <el-button
          type="primary"
          ghost
          style="width: 100%; height: 40px; border-color: var(--prev-color-primary); color: var(--prev-color-primary)"
          v-db-click
          @click="addBox"
          >添加图片
        </el-button>
      </div>
    </template>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script setup>
import iconsPng from '@/assets/images/icons.png';
import { nextTick, ref, watch } from 'vue';
import linkaddress from '@/components/linkaddress';
import uploadPictures from '@/components/uploadPictures';
import settings from '@/setting';
import draggable from 'vuedraggable';
import { useUserInfoStore } from '@/store/module/userInfo';

defineOptions({ name: 'c_upload_list' });

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
  moduleName: {
    type: String,
  },
});

const userInfoStore = useUserInfoStore();

const defaults = ref({});
const menus = ref([]);
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
const keyIndex = ref(0);
const datas = ref(props.configData[props.configNum]);
const linkaddres = ref(null);

watch(
  () => props.configData,
  (nVal) => {
    datas.value = nVal[props.configNum];
  },
  { deep: true },
);

// vuedraggable4 item-key（数据无唯一 id，用索引）
function uploadListItemKey(element) {
  return datas.value[props.name].list.indexOf(element);
}

function getLink(index, key) {
  activeIndex.value = index;
  keyIndex.value = key;
  linkaddres.value.modals = true;
}
function linkUrl(e) {
  datas.value[props.name].list[activeIndex.value].info[keyIndex.value].value = e;
}
function addBox() {
  let val = {
    img: '',
    info: [
      {
        maxlength: 10,
        tips: '选填，不超过十个字',
        title: '标题',
        value: '',
      },
      {
        maxlength: 999,
        tips: '请填写链接',
        title: '链接',
        value: '',
      },
    ],
  };
  if (props.moduleName == 'swiperBg' || props.moduleName == 'menus') {
    props.configData[props.configNum][props.name].list.push(val);
    return;
  }
  if (props.configData[props.configNum][props.name].list.length == 0) {
    let uploadListData = userInfoStore['uploadListData' + props.moduleName];
    props.configData[props.configNum][props.name].list.push(uploadListData);
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
        iframeUrl: settings.routePre + '/widget.images/index.html?fodder=dialog',
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
    props.configData[props.configNum][props.name].list[activeIndex.value].img = pc.att_dir;
    modalPic.value = false;
  });
}
// 删除
function bindDelete(item, index) {
  if (props.configData[props.configNum][props.name].list.length == 1) {
    let lastObj = props.configData[props.configNum][props.name].list[0];
    userInfoStore['uploadList' + props.moduleName](lastObj);
  }
  props.configData[props.configNum][props.name].list.splice(index, 1);
}
</script>

<style lang="scss" scoped>
.icondrag2 {
  font-size: 26px;
  color: #d8d8d8;
}
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
      margin-top: 14px;
      .move-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 80px;
        cursor: move;
        color: #d8d8d8;
      }
      .img-box {
        position: relative;
        width: 80px;
        height: 80px;

        img {
          width: 100%;
          height: 100%;
        }
      }
      .info {
        flex: 1;
        margin-left: 22px;
        .info-item {
          display: flex;
          align-items: center;
          margin-bottom: 10px;
          width: 300px;

          span {
            width: 40px;
            font-size: 13px;
          }
          .input-box {
            flex: 1;
          }
        }
      }
      .delect-btn {
        position: absolute;
        right: -12px;
        top: -12px;
        color: #999999;
        .iconfont {
          font-size: 28px;
          color: #999;
        }
      }
    }
  }
  .add-btn {
    margin-top: 10px;
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
