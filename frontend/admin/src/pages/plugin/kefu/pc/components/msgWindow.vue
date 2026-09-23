<template>
  <div class="msg-box">
    <div class="head">
      <div class="tab-bar">
        <a
          href="javascript:;"
          class="tab-item"
          :class="{ on: item.key == tabCur }"
          v-for="(item, index) in tabList"
          :key="index"
          v-db-click
          @click="bindTab(item)"
          >{{ item.title }}</a
        >
      </div>
      <div class="search-box">
        <el-input placeholder="搜索快捷回复" style="width: 100%" v-model="searchTxt" />
      </div>
    </div>
    <div class="main">
      <div class="left-box">
        <!-- vue-scroll 为 Vue2 专用库且未再全局注册，改用原生滚动容器 -->
        <div class="left-scroll">
          <div class="left-item" v-if="tabCur">
            <p>分组</p>
            <span class="iconfont iconaddto" v-db-click @click="openAddSort"></span>
          </div>
          <div
            class="left-item"
            v-for="(item, index) in sortList"
            :key="index"
            :class="{ on: cateId == item.id }"
            v-db-click
            @click="selectSort(item)"
          >
            <p>{{ item.name }}</p>
            <template v-if="tabCur">
              <span class="iconfont iconDot" v-db-click @click.stop="bindEdit(item)"></span>

              <div class="edit-wrapper" v-show="item.isEdit">
                <div class="edit-item" v-db-click @click="editSort(item)">编辑</div>
                <div class="edit-item" v-db-click @click="delSort(item, '删除分类', index)">删除</div>
              </div>
              <div class="edit-bg" v-show="item.isEdit" v-db-click @click.stop="item.isEdit = false"></div>
            </template>
          </div>
        </div>
      </div>
      <div class="right-box">
        <div
          v-infinite-scroll="handleReachBottom"
          class="right-scroll"
          :infinite-scroll-immediate="false"
          :infinite-scroll-delay="500"
          style="overflow: auto"
        >
          <div class="msg-item add-box" v-if="tabCur" style="margin-top: 0">
            <div class="box2">
              <el-input
                class="input-box"
                v-model="addMsg.title"
                placeholder="输入标题（选填）"
                style="width: 100%"
                @on-focus="bindFocus"
              />
              <div class="conBox" :class="{ active: addMsg.isEdit }">
                <div class="content">
                  <el-input v-model="addMsg.message" type="textarea" :rows="4" placeholder="请输入内容" />
                </div>
                <div class="bom">
                  <div class="select">
                    <el-select v-model="addMsg.cateId" style="width: 100px">
                      <el-option v-for="item in sortList" :value="item.id" :key="item.id">{{ item.name }} </el-option>
                    </el-select>
                  </div>
                  <div class="btns-box">
                    <el-button v-db-click @click.stop="addMsg.isEdit = false">取消</el-button>
                    <el-button type="primary" v-db-click @click.stop="bindAdd">保存</el-button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <template v-for="(item, index) in list" :key="index">
            <div class="msg-item" v-if="item.id">
              <div class="box1" v-if="!item.isEdit">
                <div class="txt-box" v-db-click @click="bindRadio(item)">
                  <span class="title" v-if="item.title">{{ filtersTitle(item.title) }}</span>
                  <span v-if="item.message">{{ filtersCon(item.message) }}</span>
                </div>
                <div class="edit-box" v-if="tabCur">
                  <span class="iconfont iconbianji" v-db-click @click.stop="editMsg(item)"></span>
                  <span class="iconfont iconshanchu" v-db-click @click.stop="delMsg(item, '删除话术', index)"></span>
                </div>
              </div>
              <div class="box2" v-else>
                <el-input class="input-box" v-model="item.title" placeholder="输入标题（选填）" style="width: 100%" />
                <div class="content">
                  <el-input v-model="item.message" type="textarea" :rows="4" placeholder="请输入内容" />
                </div>
                <div class="bom">
                  <div class="select">
                    <el-select v-model="cateId" style="width: 100px">
                      <el-option
                        v-for="item in sortList"
                        :value="item.id"
                        :key="item.id"
                        :label="item.name"
                      ></el-option>
                    </el-select>
                  </div>
                  <div class="btns-box">
                    <el-button v-db-click @click.stop="item.isEdit = false">取消</el-button>
                    <el-button type="primary" v-db-click @click.stop="updataMsg(item)">保存</el-button>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
    <el-dialog v-model="isAddSort" append-to-body :title="maskTitle" width="304px" class="class-box">
      <div class="item">
        <span>分组名称：</span>
        <el-input v-model="classTitle" placeholder="分组名称" />
      </div>
      <div class="item">
        <span>分组排序：</span>
        <el-input v-model="classSort" placeholder="输入排序" />
      </div>
      <div class="btn"></div>
      <template #footer
        ><span class="dialog-footer">
          <el-button type="primary" v-db-click @click="addServiceCateFn">确定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { modalSure } from '@/utils/public';
import {
  speeChcraft,
  serviceCate as serviceCateApi,
  serviceCateUpdate,
  addSpeeChcraft,
  addServiceCate as addServiceCateApi,
  editServiceCate,
} from '@/pages/plugin/kefu/api';

defineOptions({ name: 'msgWindow' });

const isScroll = ref(true);
const page = ref(1);
const limit = ref(15);
const tabCur = ref(1);
const tabList = ref([
  {
    title: '个人库',
    key: 1,
  },
  {
    title: '公共库',
    key: 0,
  },
]);
const searchTxt = ref(''); // 搜索
const list = ref([
  {
    isEdit: false,
  },
]); // 列表
const model1 = ref('');
const msgTitle = ref(''); // 填写的标题
const sortList = ref([]); // 分类
const cateId = ref(''); // 选中的id
const addMsg = ref({
  title: '',
  message: '',
  cateId: '',
  isEdit: false,
});
const isAddSort = ref(false); // 添加分类
const classTitle = ref(''); // 分类名称
const classSort = ref(''); // 分类排序
const maskTitle = ref(''); // 弹窗标题
const editObj = ref({}); // 编辑分类对象

// 原 filters.filtersTitle
function filtersTitle(val) {
  let len = 37;
  if (val.length > len) {
    let data = val.substring(0, len);
    return `${data}...`;
  } else {
    return val;
  }
}
// 原 filters.filtersCon
function filtersCon(val) {
  let len = 113;
  if (val.length > len) {
    let data = val.substring(0, len);
    return `${data}...`;
  } else {
    return val;
  }
}

onMounted(() => {
  serviceCate();
});

// 打开编辑
function editMsg(item) {
  item.isEdit = true;
  cateId.value = item.cate_id;
}
// 编辑框
function bindEdit(item, index) {
  //   if (index == 0) {
  //     return;
  //   } else {
  item.isEdit = !item.isEdit;
  //   }
}
// 头部选择
function bindTab(item) {
  tabCur.value = item.key;
  cateId.value = '';
  sortList.value = [];
  isScroll.value = true;
  page.value = 1;
  list.value = [];
  serviceCate();
}
// 搜索
function bindSearch() {
  isScroll.value = true;
  page.value = 1;
  list.value = [];
  getList();
}
// 选择分类
function selectSort(item) {
  if (cateId.value == item.id) {
    return;
  }
  sortList.value.forEach((el, index) => {
    if (el.id != item.id) {
      el.isEdit = false;
    }
  });
  cateId.value = item.id;
  isScroll.value = true;
  page.value = 1;
  list.value = [];
  getList();
}
// 删除分类
function delSort(row, tit, num) {
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
      ElMessage.success(res.msg);
      isScroll.value = true;
      page.value = 1;
      list.value = [];
      cateId.value = '';
      serviceCate();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 获取分类（与导入的 api 同名，改名避免遮蔽）
function serviceCate() {
  serviceCateApi({
    type: tabCur.value,
  }).then((res) => {
    let obj = {
      id: '',
      name: '全部',
    };
    res.data.data.forEach((el, index) => {
      el.isEdit = false;
    });
    // res.data.data.unshift(obj)
    sortList.value = res.data.data;
    if (cateId.value === '') {
      cateId.value = res.data.data[0].id;
    }
    getList();
  });
}
// 获取列表
function getList() {
  if (!isScroll.value) return;
  speeChcraft({
    page: page.value,
    limit: limit.value,
    title: searchTxt.value,
    cate_id: cateId.value,
    type: tabCur.value,
  }).then((res) => {
    isScroll.value = res.data.length >= limit.value;
    res.data.forEach((el, index) => {
      el.isEdit = false;
    });
    page.value++;
    list.value = list.value.concat(res.data);
  });
}
// 修改话术
function updataMsg(item) {
  serviceCateUpdate(item.id, {
    title: item.title,
    cate_id: cateId.value,
    message: item.message,
  })
    .then((res) => {
      ElMessage.success('修改成功');
      item.isEdit = false;
    })
    .catch((error) => {
      ElMessage.error(error.msg);
      item.isEdit = true;
    });
}
// 添加框显示
function bindFocus() {
  list.value.forEach((el, item) => {
    el.isEdit = false;
  });
  addMsg.value.isEdit = true;
}
// 打开添加窗口
function openAddSort() {
  isAddSort.value = true;
  maskTitle.value = '添加分组';
  editObj.value.id = 0;
}
// 添加话术
function bindAdd() {
  addSpeeChcraft({
    title: addMsg.value.title,
    cate_id: addMsg.value.cateId,
    message: addMsg.value.message,
  })
    .then((res) => {
      addMsg.value.title = '';
      addMsg.value.message = '';
      addMsg.value.cateId = '';
      addMsg.value.isEdit = false;
      ElMessage.success(res.msg);
      res.data.isEdit = false;
      page.value = 1;
      list.value = [];
      isScroll.value = true;
      serviceCate();
    })
    .catch((error) => {
      ElMessage.error(error.msg);
    });
}
// 删除
function delMsg(row, tit, num, type) {
  let delfromData = {
    title: tit,
    num: num,
    url: `kefu/service/speechcraft/${row.id}`,
    method: 'DELETE',
    ids: '',
    kefu: true,
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      list.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 添加分类（与导入的 api 同名，改名避免遮蔽）
function addServiceCateFn() {
  if (editObj.value.id) {
    editServiceCate(editObj.value.id, {
      name: classTitle.value,
      sort: classSort.value,
    })
      .then((res) => {
        classTitle.value = '';
        classSort.value = '';
        ElMessage.success(res.msg);
        isAddSort.value = false;
        page.value = 1;
        list.value = [];
        isScroll.value = true;
        serviceCate();
      })
      .catch((error) => {
        classTitle.value = '';
        classSort.value = '';
        ElMessage.error(error.msg);
      });
  } else {
    addServiceCateApi({
      name: classTitle.value,
      sort: classSort.value,
    })
      .then((res) => {
        classTitle.value = '';
        classSort.value = '';
        ElMessage.success(res.msg);
        isAddSort.value = false;
        page.value = 1;
        list.value = [];
        isScroll.value = true;
        serviceCate();
      })
      .catch((error) => {
        classTitle.value = '';
        classSort.value = '';
        ElMessage.error(error.msg);
      });
  }
}
// 编辑分类
function editSort(item) {
  classSort.value = item.sort;
  classTitle.value = item.name;
  isAddSort.value = true;
  maskTitle.value = '编辑分组';
  editObj.value = item;
}
function handleReachBottom() {
  getList();
}
function bindRadio(data) {
  emit('activeTxt', data.message);
}

const emit = defineEmits(['close', 'activeTxt']);
</script>

<style lang="scss" scoped>
.head {
  .tab-bar {
    display: flex;
    .tab-item {
      margin-right: 24px;
      color: #999;
      font-size: 14px;
      font-weight: 500;
      &.on {
        color: #333333;
      }
    }
  }
  .search-box {
    margin-top: 15px;
  }
}
.main {
  display: flex;
  margin-top: 15px;
  height: 365px;
  .left-box {
    width: 106px;
    height: 100%;
    border-right: 1px solid #ececec;
    overflow: hidden;
    // 分类列表滚动容器（替代未注册的 vue-scroll）
    .left-scroll {
      height: 100%;
      overflow-y: auto;
    }
    .left-item {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 36px;
      padding: 0 10px 0 14px;
      font-size: 13px;
      cursor: pointer;
      &.on {
        background: var(--prev-color-primary-light-9);
        color: var(--prev-color-primary);
        border-right: 2px solid var(--prev-color-primary);
        .iconDot {
          z-index: 1;
          opacity: 1;
        }
      }
      &:nth-child(1).on,
      &:nth-child(2).on {
        .iconDot {
          display: none;
        }
      }
      .iconaddto {
        font-size: 12px;
      }
      .iconDot {
        z-index: -1;
        opacity: 0;
      }
      .edit-wrapper {
        z-index: 50;
        position: absolute;
        right: -2px;
        top: -4px;
        background: #fff;
        width: 80px;
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.2);
        border-radius: 4px;
        .edit-item {
          padding: 8px 16px;
          color: #666 !important;
          cursor: pointer;
        }
      }
      .edit-bg {
        z-index: 40;
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: transparent;
      }
    }
  }
  .right-box {
    flex: 1;
    padding: 0 12px;
    overflow-x: hidden;
    .msg-item {
      margin-top: 12px;
      transition: all 0.3s ease;
      cursor: pointer;
      .box1 {
        position: relative;
        display: flex;
        .txt-box {
          flex: 1;
          font-size: 12px;
          color: #999999;
          .title {
            max-width: 370px;
            margin-right: 5px;
            color: #333;
            font-weight: 700;
          }
        }
        .edit-box {
          z-index: -1;
          opacity: 0;
          position: absolute;
          right: 7px;
          top: 0;
          width: 60px;
          height: 30px;
          background: #fff;
          .iconfont {
            margin: 0 8px;
            color: #000000;
            font-size: 16px;
            cursor: pointer;
          }
        }
      }
      .box2 {
        padding-bottom: 15px;
        border-radius: 5px;
        background: #f5f5f5;
        .input-box {
          border-bottom: 1px solid #eeeeee;
          :deep(.ivu-input) {
            background: transparent;
            border: 0;
            border-radius: 0;
          }
        }
        .content {
          font-size: 12px;
          padding: 12px 11px 0;
          color: #333333;
        }
        .bom {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 0 20px 0 11px;
          margin-top: 10px;

          button {
            margin-left: 8px;
            width: 70px;
          }
        }
      }
      &:hover {
        transition: all 0.3s ease;
        .box1 .edit-box {
          z-index: 1;
          opacity: 1;
          transition: all 0.3s ease;
        }
      }
    }
    .add-box {
      border-radius: 0;
      margin-bottom: 10px;
      .box2 {
        padding-bottom: 0;
        border-radius: 0;
        .conBox {
          height: 0;
          overflow: hidden;
          &.active {
            animation: mymove 0.4s ease;
            animation-iteration-count: 1;
            animation-fill-mode: forwards;
          }
        }
      }
    }
  }
}
.right-scroll {
  height: 345px;
}
.class-box {
  .item {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    &:last-child {
      margin-bottom: 0;
    }

    input {
      flex: 1;
    }

    span {
      width: 80px;
      font-size: 12px;
    }
  }
}
</style>
<style>
@keyframes mymove {
  0% {
    height: 0;
  }
  100% {
    height: 150px;
  }
}
</style>
