<template>
  <div>
    <!-- 常用语 -->
    <div class="words-mask" v-if="isWords">
      <div class="content">
        <div class="title-box">
          <div class="tab-box">
            <div
              class="tan-item"
              :class="{ on: item.key == wordsTabCur }"
              v-for="(item, index) in wordsTab"
              :key="index"
              v-db-click
              @click.stop="bindTab(item)"
            >
              {{ item.title }}
            </div>
            <div class="right-icon">
              <span class="iconfont iconbianji2" v-db-click @click.stop="isWordShow = true"></span>
              <span class="iconfont iconcha" v-db-click @click.stop="closeBox"></span>
            </div>
          </div>
          <div class="input-box">
            <el-input v-model="wordsData.searchTxt" placeholder="搜索快捷回复" :search="true" @on-search="bindSearch" />
          </div>
        </div>
        <div class="scroll-box">
          <div class="scroll-left">
            <div class="left-item add_cate" v-db-click @click.stop="openCate(0)" v-if="wordsTabCur">
              <span class="iconfont iconjiahao"></span> 分组
            </div>
            <div
              class="left-item"
              :class="{ active: wordsData.cateId == item.id }"
              v-for="item in wordsData.cate"
              :key="item.id"
              v-db-click
              @click.stop="changeCate(item)"
            >
              {{ item.name }}
            </div>
          </div>
          <div class="right-box">
            <!-- vue-scroll 为 Vue2 专用库且未再全局注册，改用原生滚动容器（触底加载下一页） -->
            <div class="right-scroll" @scroll="handleWordsScroll">
              <div class="msg-item add-mg" v-show="wordsTabCur" v-db-click @click.stop="addMsg">
                <span class="iconfont icontianjia11"></span>添加话术
              </div>
              <div
                class="msg-item"
                v-for="(item, index) in wordsList"
                :key="index"
                v-db-click
                @click.stop="selectWords(item)"
              >
                <span class="title">{{ item.title }}</span
                >{{ item.message }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- 添加分组  -->
    <el-dialog v-model="cateData.isCate" width="470px" :show-close="true" align-center class="words-box">
      <div class="mask-title">
        {{ cateData.status ? '编辑分组' : '新增分组' }}
        <span class="iconfont iconcha" v-db-click @click.stop="closeCate"></span>
      </div>
      <div class="input-box">
        <el-input class="noinput" v-model="cateData.name" placeholder="请输入分组名称" />
      </div>
      <div class="input-box">
        <el-input class="noinput" v-model="cateData.sort" placeholder="请输入分组排序" />
      </div>
      <el-button v-db-click @click.stop="cateConfirm" class="subBtn" type="primary" :disabled="cateStatus"
        >确定</el-button
      >
    </el-dialog>
    <!-- 添加话术  -->
    <el-dialog v-model="msgData.isCateMeg" width="470px" :show-close="true" align-center class="words-box">
      <div class="mask-title">
        {{ msgData.status ? '修改话术' : '添加话术' }}
        <span class="iconfont iconcha" v-db-click @click.stop="closeMsgBox"></span>
      </div>
      <div class="input-box">
        <el-input class="noinput" v-model="msgData.title" placeholder="请输入标题名称 (选填)" />
      </div>
      <div class="input-box text-area">
        <el-input class="noinput" :rows="4" type="textarea" v-model="msgData.message" placeholder="请输入您的话术" />
      </div>
      <div class="input-box">
        <el-select v-model="msgData.msgCateId">
          <el-option v-for="item in selectData" :value="item.id" :key="item.value" :label="item.name"></el-option>
        </el-select>
      </div>
      <el-button v-db-click @click.stop="msgConfirm" class="subBtn" type="primary" :disabled="msgStatus"
        >确定</el-button
      >
    </el-dialog>
    <!-- 编辑弹窗  -->
    <div class="edit-box" v-if="isWordShow">
      <div class="head">
        <div class="tit-bar">
          {{ wordsTabCur ? '个人库' : '公共库' }}<span v-db-click @click.stop="isWordShow = false">完成</span>
        </div>
        <div class="input-box noinput">
          <el-input v-model="wordsData.searchTxt" placeholder="搜索快捷回复" :search="true" @on-search="bindSearch" />
        </div>
      </div>
      <div class="scroll-box">
        <div class="scroll-left">
          <div class="top">
            <div class="left-item add_cate" v-db-click @click.stop="openCate(0)" v-if="wordsTabCur">
              <span class="iconfont iconjiahao"></span> 分组
            </div>
            <div
              class="left-item"
              :class="{ active: wordsData.cateId == item.id }"
              v-for="item in wordsData.cate"
              :key="item.id"
              v-db-click
              @click.stop="changeCate(item)"
            >
              {{ item.name }}
            </div>
          </div>
          <div class="bom" v-if="wordsTabCur">
            <div class="left-item edits-box" v-db-click @click.stop="editList.status = true">编辑分组</div>
          </div>
        </div>
        <div class="right-box">
          <div class="right-scroll" @scroll="handleWordsScroll">
            <div class="msg-item" v-for="(item, index) in wordsList" :key="index">
              <span class="title">{{ item.title }}</span
              >{{ item.message }}
              <div class="edit-bar" v-if="wordsTabCur">
                <span class="iconfont iconbianji1" v-db-click @click.stop="bindEdit(item)"></span>
                <span class="iconfont iconshanchu1" v-db-click @click.stop="delMsg(item, '删除话术', index)"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- 编辑分组列表 -->
    <el-dialog v-model="editList.status" width="470px" :show-close="true" align-center class="words-box cate-list">
      <div class="mask-title">
        编辑分组
        <span class="iconfont iconcha" v-db-click @click.stop="editList.status = false"></span>
      </div>
      <div class="list-box">
        <div class="item" v-for="(item, index) in wordsData.cate" :key="index" :index="index">
          <span>{{ item.name }}</span>
          <div class="right-box">
            <span class="iconfont iconbianji1" v-if="index > 0" v-db-click @click.stop="openCate(1, item)"></span>
            <span
              class="iconfont iconshanchu1"
              v-if="index > 0"
              v-db-click
              @click.stop="delCate(item, '删除分组', index)"
            ></span>
          </div>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { modalSure } from '@/utils/public';
import {
  speeChcraft,
  serviceCate,
  addServiceCate,
  addSpeeChcraft,
  serviceCateUpdate,
  editServiceCate,
} from '@/pages/plugin/kefu/api';

defineOptions({ name: 'words' });

const props = defineProps({
  isWords: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['selectMsg', 'closeBox']);

const isWordShow = ref(false); // 编辑窗
const wordsList = ref([]);
const wordsTab = ref([
  {
    title: '个人库',
    key: 1,
  },
  {
    title: '公共库',
    key: 0,
  },
]);
const wordsTabCur = ref(1);
const wordsData = reactive({
  isScroll: true,
  page: 1,
  limit: 15,
  searchTxt: '',
  cate: [], // 分类
  cateId: '', // 分类id
});
// 话术列表请求进行中标记：触底滚动高频触发，避免重复请求同一页
const wordsLoading = ref(false);
// 分组数据
const cateData = reactive({
  status: 0, // 0 新增 1编辑
  name: '',
  sort: '',
  isCate: false, // 分组状态开关
  id: '',
});
// 编辑分组列表
const editList = reactive({
  status: false,
});
// 话术添加数据
const msgData = reactive({
  isCateMeg: false,
  msgCateId: '',
  message: '',
  title: '',
  status: 0, // 0 新增 1修改
  editId: '',
});
const selectData = ref('');

const cateStatus = computed(() => {
  if (cateData.name && cateData.sort) {
    return false;
  } else {
    return true;
  }
});
const msgStatus = computed(() => {
  if (msgData.message) {
    return false;
  } else {
    return true;
  }
});

onMounted(() => {
  Promise.all([getServiceCate()]);
});

// 关闭添加话术弹窗
function closeMsgBox() {
  msgData.isCateMeg = false;
}
// 选择话术
function selectWords(item) {
  emit('selectMsg', item.message);
}
// 关闭弹窗
function closeBox() {
  emit('closeBox');
}
// 搜索
function bindSearch() {
  wordsData.page = 1;
  wordsData.isScroll = true;
  wordsLoading.value = false;
  wordsList.value = [];
  getWordsList();
}
// 顶部切换
function bindTab(item) {
  wordsTabCur.value = item.key;
  wordsData.isScroll = true;
  wordsLoading.value = false;
  wordsData.page = 1;
  wordsData.cate = [];
  wordsList.value = [];
  getServiceCate();
}
// 选择话术分类
function changeCate(item) {
  wordsData.isScroll = true;
  wordsLoading.value = false;
  wordsList.value = [];
  wordsData.page = 1;
  wordsData.cateId = item.id;
  msgData.msgCateId = item.id;
  getWordsList();
}
// 获取话术分类
function getServiceCate() {
  serviceCate({
    type: wordsTabCur.value,
  }).then((res) => {
    let tempArr = JSON.parse(JSON.stringify(res.data.data));
    wordsData.cateId = res.data.data.length ? res.data.data[0].id : '';
    msgData.msgCateId = wordsData.cateId;
    wordsData.cate = res.data.data;
    selectData.value = tempArr;
    getWordsList();
  });
}
// 原生滚动触底：距底部不足 10px 时加载下一页
function handleWordsScroll(e) {
  const el = e.target;
  if (el.scrollTop + el.clientHeight >= el.scrollHeight - 10) {
    getWordsList();
  }
}
// 常用语
function getWordsList() {
  if (!wordsData.isScroll || wordsLoading.value) return;
  wordsLoading.value = true;
  speeChcraft({
    page: wordsData.page,
    limit: wordsData.limit,
    title: wordsData.searchTxt,
    cate_id: wordsData.cateId,
    type: wordsTabCur.value,
  })
    .then((res) => {
      wordsLoading.value = false;
      wordsData.isScroll = res.data.length >= wordsData.limit;
      wordsList.value = wordsList.value.concat(res.data);
      wordsData.page++;
    })
    .catch(() => {
      wordsLoading.value = false;
    });
}
// 打开分组弹窗
function openCate(key, item) {
  cateData.status = key;
  cateData.isCate = true;
  if (key == 1) {
    cateData.name = item.name;
    cateData.id = item.id;
  }
}
// 关闭分组弹窗
function closeCate() {
  cateData.isCate = false;
  cateData.name = '';
  cateData.sort = '';
}
// 分组添加
function cateConfirm() {
  if (!cateData.status) {
    addServiceCate({
      name: cateData.name,
      sort: cateData.sort,
    })
      .then((res) => {
        cateData.isCate = false;
        cateData.name = '';
        cateData.sort = '';
        wordsData.isScroll = true;
        wordsList.value = [];
        ElMessage.success(res.msg);
        getServiceCate();
      })
      .catch((error) => {
        ElMessage.error(error.msg);
      });
  } else {
    editServiceCate(cateData.id, {
      name: cateData.name,
      sort: cateData.sort,
    }).then((res) => {
      cateData.isCate = false;
      cateData.name = '';
      cateData.sort = '';
      cateData.id = '';
      wordsData.isScroll = true;
      wordsList.value = [];
      ElMessage.success(res.msg);
      getServiceCate();
    });
  }
}
// 话术打开
function addMsg() {
  msgData.isCateMeg = true;
  msgData.status = 0;
}
// 话术添加
function msgConfirm() {
  if (!msgData.status) {
    addSpeeChcraft({
      title: msgData.title,
      cate_id: msgData.msgCateId,
      message: msgData.message,
    })
      .then((res) => {
        msgData.isCateMeg = false;
        msgData.title = '';
        msgData.message = '';
        msgData.msgCateId = wordsData.cateId;
        ElMessage.success(res.msg);
        wordsData.isScroll = true;
        wordsData.page = 1;
        wordsList.value = [];
        getWordsList();
      })
      .catch((error) => {
        ElMessage.error(error.msg);
      });
  } else {
    serviceCateUpdate(msgData.editId, {
      title: msgData.title,
      cate_id: msgData.msgCateId,
      message: msgData.message,
    }).then((res) => {
      msgData.isCateMeg = false;
      msgData.title = '';
      msgData.message = '';
      msgData.msgCateId = wordsData.cateId;
      ElMessage.success(res.msg);
      wordsData.isScroll = true;
      wordsData.page = 1;
      wordsList.value = [];
      getWordsList();
    });
  }
}
// 编辑话术
function bindEdit(item) {
  msgData.status = 1;
  msgData.isCateMeg = true;
  msgData.message = item.message;
  msgData.title = item.title;
  msgData.editId = item.id;
}
// 删除话术
function delMsg(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `/kefu/service/speechcraft/${row.id}`,
    method: 'DELETE',
    ids: '',
    kefu: true,
  };
  modalSure(delfromData)
    .then((res) => {
      wordsList.value.splice(num, 1);
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function delCate(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `/kefu/service/cate/${row.id}`,
    method: 'DELETE',
    ids: '',
    kefu: true,
  };
  modalSure(delfromData)
    .then((res) => {
      wordsData.cate.splice(num, 1);
      wordsData.isScroll = true;
      wordsList.value = [];
      ElMessage.success(res.msg);
      getServiceCate();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
</script>

<style lang="scss" scoped>
// 话术列表滚动容器（替代未注册的 vue-scroll）：话术弹层与编辑窗两处共用
.right-scroll {
  height: 100%;
  overflow-y: auto;
}
.words-mask {
  z-index: 50;
  position: fixed;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  .content {
    position: absolute;
    left: 0;
    right: 0;
    top: 1.14rem;
    bottom: 0;
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 0.06rem 0.06rem 0px 0px;
    .title-box {
      padding: 0 0.3rem 0.3rem;
      position: relative;
      border-bottom: 1px solid #f5f6f9;
      .tab-box {
        position: relative;
        display: flex;
        justify-content: space-between;
        padding: 0.4rem 2.2rem 0.3rem;
        font-size: 0.32rem;
        color: #9f9f9f;
        .on {
          color: #3875ea;
          font-weight: bold;
        }
        .right-icon {
          position: absolute;
          right: 0;
          top: 50%;
          transform: translateY(-50%);
          .iconfont {
            margin-left: 0.2rem;
            font-size: 0.48rem;
            color: #c8cad0;
          }
        }
      }
      .input-box {
        display: flex;
        align-items: center;
        width: 6.9rem;
        height: 0.64rem;
        padding-right: 0.05rem;
        margin-left: 0.18rem;
        border-radius: 0.32rem;
        overflow: hidden;
        :deep(.el-input__inner) {
          background: #f5f6f9;
        }
        :deep(.el-input__inner),
        .el-input__inner:hover,
        .el-input__inner:focus {
          border: transparent;
          box-shadow: none;
        }
      }
      .icon-cha1 {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
      }
    }
    .scroll-box {
      flex: 1;
      display: flex;
      overflow: hidden;
      .scroll-left {
        width: 1.76rem;
        height: 100%;
        overflow-y: scroll;
        -webkit-overflow-scrolling: touch;
        background: #f5f6f9;
        .left-item {
          position: relative;
          display: flex;
          align-items: center;
          justify-content: center;
          width: 100%;
          height: 1.09rem;
          color: #282828;
          font-size: 0.26rem;
          &.active {
            color: #3875ea;
            background: #fff;
            &:after {
              content: ' ';
              position: absolute;
              left: 0;
              top: 50%;
              transform: translateY(-50%);
              width: 0.06rem;
              height: 0.46rem;
              background: #3875ea;
            }
          }
          &.add_cate {
            color: #9f9f9f;
            font-size: 0.26rem;
            .iconfont {
              margin-right: 0.1rem;
              font-size: 0.24rem;
            }
          }
        }
      }
      .right-box {
        flex: 1;
        overflow: scroll;
        -webkit-overflow-scrolling: touch;
      }
      .msg-item {
        padding: 0.25rem 0.3rem;
        color: #888888;
        font-size: 0.28rem;
        .title {
          margin-right: 0.2rem;
          color: #282828;
        }
        &.add-mg {
          display: flex;
          align-items: center;
          justify-content: flex-end;
          font-size: 0.28rem;
          padding: 0.15rem 0.3rem;
          .iconfont {
            font-size: 0.36rem;
            margin-right: 0.1rem;
          }
        }
      }
    }
  }
}
.words-box {
  .mask-title {
    position: relative;
    text-align: center;
    margin-bottom: 0.5rem;
    color: #282828;
    font-size: 0.32rem;
    font-weight: bold;
    .iconfont {
      position: absolute;
      right: 0;
      top: 50%;
      transform: translateY(-50%);
      color: #c8cad0;
      font-size: 0.44rem;
      font-weight: normal;
    }
  }
  .input-box {
    height: 0.68rem;
    margin-top: 0.32rem;
    background: #f5f5f5;
    border-radius: 0.14rem;
    &.text-area {
      height: 1.92rem;
      textarea {
        display: block;
        height: 100%;
      }
    }
  }
  .subBtn {
    width: 100%;
    height: 0.86rem;
    margin-top: 0.6rem;
    margin-bottom: 0.3rem;
    font-size: 0.3rem !important;
    border-radius: 0.43rem;
    &[disabled] {
      background: #c8cad0;
      color: #fff;
      font-size: 0.3rem !important;
    }
  }
}
.edit-box {
  z-index: 99;
  position: fixed;
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  background: #fff;
  .head {
    padding: 0.4rem 0.3rem 0.3rem;
    .tit-bar {
      position: relative;
      text-align: center;
      font-size: 0.32rem;
      color: #282828;
      font-weight: bold;
      span {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        color: #3875ea;
        font-size: 0.28rem;
        font-weight: normal;
      }
    }
    .input-box {
      margin-top: 0.3rem;
      background: #f5f6f9;
      border-radius: 0.39rem;
    }
  }
  .scroll-box {
    flex: 1;
    display: flex;
    overflow: hidden;
    .scroll-left {
      display: flex;
      flex-direction: column;
      position: relative;
      width: 1.76rem;
      height: 100%;
      background: #f5f6f9;
      .top {
        flex: 1;
        overflow-y: scroll;
        -webkit-overflow-scrolling: touch;
      }
      .left-item {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 1.09rem;
        color: #282828;
        font-size: 0.26rem;
        &.active {
          color: #3875ea;
          background: #fff;
          &:after {
            content: ' ';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 0.06rem;
            height: 0.46rem;
            background: #3875ea;
          }
        }
        &.add_cate {
          color: #9f9f9f;
          font-size: 0.26rem;
          .iconfont {
            margin-right: 0.1rem;
            font-size: 0.24rem;
          }
        }
        &.edits-box {
          color: #3875ea;
        }
      }
    }
    .right-box {
      flex: 1;
      padding-left: 0.3rem;
    }
    .msg-item {
      padding: 0.25rem 0.3rem 0.25rem 0;
      color: #888888;
      font-size: 0.28rem;
      .title {
        margin-right: 0.2rem;
        color: #282828;
      }
      &.add-mg {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        font-size: 0.28rem;
        padding: 0.15rem 0.3rem;
        .iconfont {
          font-size: 0.36rem;
          margin-right: 0.1rem;
        }
      }
      .edit-bar {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        margin-top: 0.25rem;
        padding-bottom: 0.1rem;
        border-bottom: 1px solid #f0f2f7;
        .iconfont {
          margin-left: 0.3rem;
          font-size: 0.32rem;
        }
      }
    }
  }
}
.cate-list {
  .list-box {
    max-height: 7.5rem;
    overflow-y: scroll;
    -webkit-overflow-scrolling: touch;
    .item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 1rem;
      border-bottom: 1px solid #f0f2f7;
      color: #282828;
      font-size: 0.28rem;
      .iconfont {
        color: #9f9f9f;
        font-size: 0.32rem;
        margin-left: 0.4rem;
      }
    }
  }
}
</style>
<style>
.noinput input,
.noinput textarea {
  border-color: transparent !important;
  background: transparent !important;
  resize: none;
}
.noinput input:hover,
.noinput input:focus,
.noinput textarea:hover,
.noinput textarea:focus {
  border-color: transparent !important;
  box-shadow: none !important;
}
</style>
