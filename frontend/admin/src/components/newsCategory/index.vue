<template>
  <div :style="{ height: scrollerHeight + 'px' || '' }">
    <div>
      <el-form
        ref="formValidate"
        inline
        :model="formValidate"
        label-width="80px"
        label-position="right"
        class="tabform"
      >
        <el-form-item label="图文搜索：" prop="cate_name">
          <el-input clearable placeholder="请输入" v-model="formValidate.cate_name" class="form_content_width" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          <router-link :to="routePre + '/app/wechat/news_category/save/0'">
            <el-button type="primary" class="bnt ml15">添加图文消息</el-button>
          </router-link>
        </el-form-item>
      </el-form>
    </div>
    <div class="contentBox">
      <div id="content" :style="{ top: contentTop + 'px' || '', width: contentWidth }" ref="content">
        <!-- 原 vue-waterfall-easy（Vue2-only）改为 CSS 多列瀑布流 + 触底加载 -->
        <div ref="scrollBox" class="waterfall-scroll" @scroll="onScroll">
          <div class="waterfall-cols" :style="{ columnCount: colCount }">
            <div
              class="img-info waterfall-item"
              v-for="(item, idx) in imgsArr"
              :key="item.id !== undefined ? item.id : idx"
              v-db-click
              @click="clickFn($event, { index: idx, value: item })"
            >
              <template v-if="item.new && item.new.length !== 0">
                <div v-for="(j, i) in item.new" :key="i">
                  <div v-if="i === 0">
                    <div
                      class="news_pic"
                      :style="{
                        backgroundImage: 'url(' + j.image_input[0] + ')',
                        backgroundSize: '100% 100%',
                      }"
                      @mouseenter="mouseenterOut(j)"
                      @mouseleave="mouseenterOver(j)"
                    >
                      <el-button
                        type="success"
                        icon="Edit"
                        v-show="item.new[i].isDel && isShow"
                        v-db-click
                        @click="clkk(item)"
                      ></el-button>
                      <el-button
                        type="error"
                        icon="Delete"
                        v-show="item.new[i].isDel && isShow"
                        v-db-click
                        @click="del(item, '删除图文', i)"
                        style="margin-top: 5px"
                      ></el-button>
                      <el-button
                        type="primary"
                        icon="Promotion"
                        v-show="item.new[i].isDel && isShowSend"
                        v-db-click
                        @click="send(item, '发送', i)"
                        >推送</el-button
                      >
                    </div>
                    <span class="news_sp">{{ j.title }}</span>
                  </div>
                  <div v-else class="news_cent">
                    <span class="news_sp1" v-if="j.synopsis">{{ j.title }}</span>
                    <div class="news_cent_img" v-if="j.image_input.length !== 0">
                      <img :src="j.image_input[0]" />
                    </div>
                  </div>
                </div>
                <p class="some-info">{{ item.id }}</p>
              </template>
            </div>
          </div>
          <!-- 对应原 waterfall-over 插槽 -->
          <div v-if="over" class="waterfall-over"><div></div></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { wechatNewsListApi } from '@/api/app';
import settings from '@/setting';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'newsCategory' });

const props = defineProps({
  scrollerHeight: {
    type: String,
    default: '100%',
  },
  contentTop: {
    type: String,
    default: '0',
  },
  contentWidth: {
    type: String,
    default: '100%',
  },
  maxCols: {
    type: Number,
    default: 5,
  },
  isShow: {
    type: Boolean,
    default: false,
  },
  isShowSend: {
    type: Boolean,
    default: false,
  },
  userIds: {
    type: Array,
    default: () => {
      [];
    },
  },
});

const emit = defineEmits(['getCentList']);

const router = useRouter();

const routePre = settings.routePre;
const isDel = ref(false);
const imgsArr = ref([]);
const group = ref(0); // 当前加载的加载图片的次数
const fetchImgsArr = ref([]); // 存放每次滚动时下一批要加载的图片的数组
const orderData = ref({});
const cols = ref(NaN); // 需要根据窗口宽度初始化
const gridPic = {
  xl: 6,
  lg: 8,
  md: 8,
  sm: 24,
  xs: 24,
};
const grid = {
  xl: 8,
  lg: 8,
  md: 8,
  sm: 24,
  xs: 24,
};
const formValidate = reactive({
  cate_name: '',
  page: 1,
  limit: 10,
});
const screenWidth = document.body.clientWidth - 200;
const maxCol = ref(1);
const scrollBox = ref(null);
const over = ref(false); // 数据加载完毕（原 waterfallOver）
const loading = ref(false);

// 瀑布流列数
const colCount = computed(() => Math.max(1, Math.min(8, Math.floor(maxCol.value))));

// created
if (props.maxCols === 5) {
  maxCol.value = screenWidth / 240;
} else {
  maxCol.value = props.maxCols;
}
getData();

// 发送图文消息
function send(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `app/wechat/push`,
    method: 'post',
    ids: {
      id: row.id,
      user_ids: props.userIds,
    },
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function clickFn(event, { index, value }) {
  event.preventDefault();
  if (event.target.tagName.toLowerCase() === 'div') {
    emitEvent(value);
  }
}

function emitEvent(value) {
  emit('getCentList', value);
}

// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `app/wechat/news/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      nextTick(() => {
        imgsArr.value = [];
      });
      over.value = false;
      formValidate.page = 1;
      getData();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 编辑
function clkk(item) {
  router.push({
    path: routePre + '/app/wechat/news_category/save/' + item.id,
  });
}
// 鼠标移进
function mouseenterOut(item) {
  item['isDel'] = true;
}
// 鼠标移出
function mouseenterOver(item) {
  item['isDel'] = false;
}
// 搜索
function userSearchs() {
  nextTick(() => {
    imgsArr.value = [];
  });
  over.value = false;
  formValidate.page = 1;
  getData();
}
// 触底加载（reachBottomDistance = 30）
function onScroll() {
  const box = scrollBox.value;
  if (!box || over.value || loading.value) return;
  if (box.scrollTop + box.clientHeight >= box.scrollHeight - 30) {
    getData();
  }
}
// 瀑布流数据
function getData() {
  if (loading.value) return;
  loading.value = true;
  wechatNewsListApi(formValidate)
    .then(async (res) => {
      if (res.data.list.length === 0) {
        // 已无新数据
        imgsArr.value = [];
        over.value = true;
      } else {
        let num = Math.ceil(res.data.count / formValidate.limit) + 1;
        res.data.list.map((item) => {
          item.isDel = false;
        });
        imgsArr.value = imgsArr.value.concat(res.data.list) || [];
        formValidate.page++;
        if (formValidate.page === num) {
          // 已加载全部数据
          over.value = true;
        }
      }
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
</script>

<style lang="scss" scoped>
.contentBox {
  height: 600px;
  width: 100%;
  position: relative;
  #content {
    position: absolute;
    /* top: 280px; */
    bottom: 0;
    width: 86%;
    /* height 1000px; */
  }
}
/* 滚动容器（替代 vue-waterfall-easy-scroll） */
.waterfall-scroll {
  height: 100%;
  overflow-x: hidden;
  overflow-y: auto;
  scrollbar-width: none; /* firefox */
  -ms-overflow-style: none; /* IE 10+ */
  &::-webkit-scrollbar {
    display: none;
  }
}
/* CSS 多列瀑布流 */
.waterfall-cols {
  column-gap: 15px;
}
.waterfall-item {
  break-inside: avoid;
  margin-bottom: 15px;
  background: #f7f7f7;
  border-radius: 5px;
  overflow: hidden;
}
.waterfall-over {
  padding: 10px 0;
  text-align: center;
}
.some-info {
  padding: 7px;
  box-sizing: border-box;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.Refresh {
  font-size: 12px;
  color: var(--prev-color-primary);
  cursor: pointer;
  line-height: 35px;
  display: inline-block;
}
.news_pic {
  width: 100%;
  height: 150px;
  overflow: hidden;
  position: relative;
  background-size: cover;
  border-radius: 5px 5px 0 0;
  padding: 10px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}
.news_sp {
  font-size: 12px;
  color: #000000;
  background: #fff;
  width: 100%;
  height: 38px;
  line-height: 38px;
  padding: 0 12px;
  box-sizing: border-box;
  display: block;
}
.news_cent {
  width: 100%;
  height: auto;
  background: #fff;
  border-top: 1px dashed #eee;
  display: flex;
  padding: 10px;
  box-sizing: border-box;
  justify-content: space-between;
  .news_sp1 {
    font-size: 12px;
    color: #000000;
    width: 71%;
  }
  .news_cent_img {
    width: 81px;
    height: 46px;
    border-radius: 6px;
    overflow: hidden;

    img {
      width: 100%;
      height: 100%;
    }
  }
}
.news_pic :deep(.el-button--error) {
  width: 24px !important;
  height: 24px !important;
  background: #ff5d5f !important;
  color: #fff !important;
  border: 1px solid var(--prev-border-color-lighter) !important;
}
.news_pic :deep(.el-button--error:hover) {
  background: #ff5d5f !important;
  border: 1px solid #fff !important;
  color: #fff !important;
}
.news_pic :deep(.el-button--success) {
  width: 24px !important;
  height: 24px !important;
  border: 1px solid var(--prev-border-color-lighter) !important;
}
</style>
