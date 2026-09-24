<template>
  <el-dialog
    v-model="dialogVisible"
    width="1188px"
    top="5vh"
    class="theme-dialog"
    :show-close="false"
    append-to-body
    :modal="true"
    :lock-scroll="true"
    @close="handleClose"
  >
    <template #header>
      <div v-if="!showDetail" class="dialog-header">
        <div class="dialog-header-tabs">
          <div class="tabs">
            <span class="title">我的主题</span>
            <img class="theme-in" src="https://www.crmeb.com/static/images/zhutishichang.png" alt="" @click="toTheme" />
          </div>
        </div>
        <i class="el-icon-close" @click="handleClose"></i>
      </div>
    </template>
    <div class="dialog-content" v-if="!showDetail">
      <!-- 顶部Tab切换 -->

      <div class="content-wrapper">
        <!-- 右侧内容 -->
        <div class="main-content">
          <div class="filters-header">
            <div v-if="type != 'mall'" class="filter-left">
              <el-select v-model="currentFilter" placeholder="首页" style="width: 204px">
                <el-option
                  v-for="item in filterOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                ></el-option>
              </el-select>
            </div>
            <div class="search-box">
              <el-input
                v-model="searchKeyword"
                placeholder="请输入主题名称"
                suffix-icon="Search"
                clearable
                @clear="searchTheme"
                @change="searchTheme"
              ></el-input>
            </div>
          </div>

          <div class="theme-grid" v-loading="loading">
            <!-- 新建空白主题 -->
            <div class="theme-item create-new" v-if="activeTab === 'my'" v-db-click @click="createNewTheme">
              <div class="create-icon">
                <div class="iconfont iconic_brush"></div>
              </div>
              <div class="create-text">新建空白主题</div>
            </div>

            <!-- 空状态 -->
            <div class="theme-empty" v-if="!loading && themes.length === 0">未找到相关主题，换个关键词试试</div>

            <!-- 主题列表 -->
            <div class="theme-item" v-for="(theme, index) in themes" :key="index">
              <div class="theme-cover">
                <!-- 背景模糊层 -->
                <div class="blur-bg" :style="{ backgroundImage: `url(${theme[currentFilter]})` }"></div>
                <div class="phone-preview">
                  <img
                    v-if="theme[currentFilter] || theme.home_image"
                    :src="theme[currentFilter] || theme.home_image"
                    alt="theme"
                  />
                  <div class="no-poster" v-else>
                    <img :src="noThemePoster" class="preview-image" alt="no poster" />
                    <div>暂无封面</div>
                  </div>
                </div>
              </div>
              <div class="theme-info">
                <div class="theme-title">
                  <div class="line1">
                    {{ theme.title || '暂无主题名称' }}
                  </div>
                  <div class="tag" v-if="theme.page_type == 'micro'">专题页</div>
                </div>
                <div class="theme-time">修改时间：{{ theme.up_time }}</div>

                <div class="theme-hover-actions">
                  <div class="button default" v-db-click @click="viewThemeDetail(theme)">查看详情</div>
                  <div class="button primary" v-db-click @click="selectTheme(theme)">使用主题</div>
                </div>
              </div>
            </div>
          </div>
          <div class="acea-row row-right page" v-if="total > limit">
            <pagination
              v-if="total"
              :total="total"
              v-model:page="page"
              v-model:limit="limit"
              layout="total, prev, pager, next, jumper"
              @pagination="handlePageChange"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- 详情页视图 -->
    <div class="detail-content" v-else>
      <div class="detail-header">
        <div class="flex">
          <div class="back-btn" @click="showDetail = false"><i class="el-icon-arrow-left"></i> 返回</div>
          <div class="detail-title">主题详情</div>
        </div>
        <i class="el-icon-close" @click="handleClose"></i>
      </div>
      <div class="detail-body">
        <div class="detail-top">
          <div class="detail-preview-main">
            <img
              v-if="currentTheme[currentFilter] || currentTheme.home_image"
              :src="currentTheme[currentFilter] || currentTheme.home_image"
              alt="preview"
            />
            <div class="no-poster" v-else>
              <img :src="noThemePoster" class="preview-image" alt="no poster" />
            </div>
          </div>
          <div class="detail-info-box">
            <div>
              <div class="info-title">{{ currentTheme.title || '暂无主题名称' }}</div>
              <div class="info-desc">
                {{ currentTheme.info || '暂无描述' }}
              </div>
            </div>
            <div class="info-btn">
              <el-button type="primary" v-db-click @click="selectTheme(currentTheme)">使用主题</el-button>
            </div>
          </div>
        </div>

        <div class="detail-images-section">
          <div class="section-title">主题图片</div>
          <div class="images-list">
            <div class="image-item" v-for="(opt, index) in filterOptions" :key="index">
              <div class="image-label">{{ opt.label }}</div>
              <div class="phone-mockup">
                <img
                  v-if="currentTheme[opt.value]"
                  v-viewer
                  v-lazy="currentTheme[opt.value]"
                  :src="currentTheme[opt.value]"
                  class="preview-image"
                />
                <div class="no-poster" v-else>
                  <img :src="noThemePoster" class="preview-image" alt="no poster" />
                  <div>暂无封面</div>
                </div>
              </div>
            </div>
            <div class="detail-qrcode">
              <div class="qrcode-box" id="qrcodeDetail"></div>
              <div class="qrcode-text">扫码查看完整演示</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </el-dialog>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage, ElMessageBox } from 'element-plus';
import { getThemeList, useThemeData } from '@diy/api';
import QRCode from '@/vendor/qrcodejs2';
import Setting from '@/setting';
import noThemePoster from '@/assets/images/no-theme-poster.png';

defineOptions({ name: 'ThemeSelectDialog' });

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  type: {
    type: String,
    default: 'mall', // mall: 商城装修, my: 我的主题
  },
  themeId: {
    type: [Number, String],
    default: 0,
  },
  currentType: {
    type: String,
    default: 'home',
  },
  activeTab: {
    type: String,
    default: 'my',
  },
});

const emit = defineEmits(['update:visible', 'select', 'success']);

const router = useRouter();

// visible 为 prop，父组件用 v-model:visible；el-dialog 关闭时透传 update:visible
const dialogVisible = computed({
  get() {
    return props.visible;
  },
  set(val) {
    emit('update:visible', val);
  },
});

const showDetail = ref(false);
const currentTheme = ref({});
const currentCategory = ref('all');
const currentFilter = ref('home_image');
const searchKeyword = ref('');
const filterOptions = [
  { label: '首页', value: 'home_image' },
  { label: '分类页', value: 'category_image' },
  { label: '详情页', value: 'detail_image' },
  { label: '个人中心', value: 'user_image' },
];
const themes = ref([]);
const loading = ref(false);
const page = ref(1);
const limit = ref(9);
const total = ref(0);
const BaseURL = Setting.apiBaseURL.replace(/adminapi/, '');

watch(
  () => props.currentType,
  (newVal) => {
    currentFilter.value = `${newVal}_image`;
  },
  { immediate: true },
);

watch(
  () => props.activeTab,
  (newVal) => {
    if (newVal === 'mall') {
      limit.value = 10;
    } else {
      limit.value = 9;
    }
  },
  { immediate: true },
);

// created
getList();

function getList() {
  const reqData = { page: page.value, limit: limit.value, title: searchKeyword.value };
  if (props.type == 'mall' && props.currentType == 'home') {
    reqData.page_type = 'all';
  }
  loading.value = true;
  getThemeList(reqData)
    .then((res) => {
      themes.value = res.data.list;
      total.value = res.data.count;
    })
    .finally(() => {
      loading.value = false;
    });
}

function handlePageChange(val) {
  page.value = val;
  getList();
}

function toTheme() {
  window.open('https://www.crmeb.com/theme?from=crmebkyv8theme', '_blank');
}

function handleClose() {
  showDetail.value = false;
  emit('update:visible', false);
}

function selectTheme(theme) {
  if (props.themeId && props.themeId != 0) {
    ElMessageBox.confirm('确定要使用该主题数据吗？这将覆盖当前页面配置', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning',
    })
      .then(() => {
        const type = currentFilter.value.replace('_image', '');
        useThemeData(props.themeId, {
          theme_id: theme.id,
          type: type,
        })
          .then(() => {
            ElMessage.success('设置成功');
            emit('success');
            handleClose();
            showDetail.value = false;
          })
          .catch((err) => {
            ElMessage.error(err.msg || '设置失败');
          });
      })
      .catch(() => {});
  } else {
    emit('select', theme);
    handleClose();
    showDetail.value = false;
  }
}

function viewThemeDetail(theme) {
  currentTheme.value = theme;
  showDetail.value = true;
  nextTick(() => {
    creatQrCode();
  });
}

function creatQrCode() {
  if (document.getElementById('qrcodeDetail')) {
    document.getElementById('qrcodeDetail').innerHTML = '';
  }
  let url = `${BaseURL}pages/index/index?theme_id=${currentTheme.value.id}`;
  var qrcode = new QRCode(document.getElementById('qrcodeDetail'), {
    text: url,
    width: 120,
    height: 120,
    colorDark: '#000000',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H,
  });
}

function createNewTheme() {
  router.push({
    path: Setting.routePre + '/setting/edit_theme',
    query: {
      id: 0,
      type: 'home',
    },
  });
}

function searchTheme() {
  page.value = 1;
  getList();
}
</script>

<style lang="scss" scoped>
// 弹窗壳层（header/body/圆角）覆盖在本文件末尾的非 scoped 块中：
// append-to-body 弹窗 teleport 到 body 后脱离组件样式作用域，scoped :deep 无法命中

.dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  padding: 15px 24px;
  .dialog-header-tabs {
    background: var(--prev-bg-white);

    .tabs {
      display: flex;
      align-items: center;

      .title {
        font-size: 14px;
        line-height: 22px;
        font-weight: 500;
        color: var(--prev-color-text-regular);
        cursor: pointer;
        position: relative;
      }
      .theme-in {
        height: 12px;
        margin-left: 20px;
        cursor: pointer;
        transition: opacity 0.3s;

        &:hover {
          opacity: 0.75;
        }
      }
    }
  }
}
.el-icon-close {
  font-size: 16px;
  color: var(--prev-color-text-secondary);
  cursor: pointer;
  transition: color 0.3s;

  &:hover {
    color: var(--prev-color-primary);
  }
}

.dialog-content {
  display: flex;
  flex-direction: column;
  padding: 0 24px 24px !important;

  .content-wrapper {
    flex: 1;
    display: flex;
    overflow: hidden;
    .main-content {
      flex: 1;
      padding: 20px 0;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      background: var(--prev-bg-white);
      .filters-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        .filter-left {
          margin-right: 10px;
        }
        .search-box {
          width: 250px;
        }
      }

      .theme-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        overflow-y: auto;
        flex: 1;
        max-height: calc(100vh - 300px);

        .theme-empty {
          width: 100%;
          text-align: center;
          padding: 60px 0;
          font-size: 13px;
          color: var(--prev-color-text-secondary);
        }

        .theme-item {
          width: 204px;
          height: 295px;
          background: var(--prev-bg-white);
          border-radius: 6px;
          overflow: hidden;
          border: 1px solid var(--prev-border-color-lighter);
          display: flex;
          flex-direction: column;
          transition: box-shadow 0.3s, border-color 0.3s;

          &:hover {
            border-color: var(--prev-color-primary-light-5);
            box-shadow: 0 6px 16px rgba(0, 21, 41, 0.08);
          }

          &.create-new {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            border: 1px dashed var(--prev-border-color-base);
            background: var(--prev-bg-color);
            height: 295px; // Fixed height to match theme items
            transition: all 0.3s;

            .iconic_brush {
              font-size: 40px;
              color: var(--prev-color-text-placeholder);
              padding-bottom: 15px;
            }

            .create-text {
              color: var(--prev-color-text-regular);
              font-size: 14px;
            }

            &:hover {
              border-color: var(--prev-color-primary);
              background: var(--prev-color-primary-light-9);
              .iconic_brush,
              .create-text {
                color: var(--prev-color-primary);
              }
            }
          }

          .theme-cover {
            position: relative;
            height: 215px;
            overflow: hidden;
            background: var(--prev-bg-main-color);
            transition: height 0.3s;

            .blur-bg {
              position: absolute;
              top: 0;
              left: 0;
              right: 0;
              bottom: 0;
              background-size: cover;
              background-position: top;
              filter: blur(20px);
              z-index: 1;
            }

            .phone-preview {
              position: relative;
              z-index: 2;
              height: 95%;
              margin: 25px 24px 0 24px;
              background: var(--prev-bg-white);
              border-radius: 10px 10px 0 0;
              overflow: hidden;
              border: 2px solid var(--prev-bg-white);

              img {
                width: 100%;
              }
              .no-poster {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                height: 100%;
                font-size: 12px;
                color: var(--prev-color-text-secondary);
              }
            }
          }

          .theme-info {
            background: var(--prev-bg-white);
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;

            .theme-title {
              padding: 12px 0px 0 12px;
              font-size: 14px;
              color: var(--prev-color-text-regular);
              margin-bottom: 12px;
              font-weight: 500;
              white-space: nowrap;
              overflow: hidden;
              text-overflow: ellipsis;
              display: flex;
              align-items: center;
              justify-content: space-between;
              .tag {
                font-weight: 400;
                color: var(--prev-color-primary);
                font-size: 12px;
                background: var(--prev-color-primary-light-9);
                padding: 4px 6px 4px 8px;
                border-radius: 10px 0 0 10px;
              }
            }

            .theme-time {
              padding: 0 12px 0 12px;

              font-size: 12px;
              color: var(--prev-color-text-secondary);
              margin-bottom: 10px;
            }

            .theme-hover-actions {
              display: flex;
              justify-content: space-between;
              align-items: center;
              opacity: 0;
              transform: translateY(20px);
              transition: all 0.3s;
              height: 0;
              padding: 0 12px;
              overflow: hidden;

              .button {
                width: 84px;
                height: 28px;
                line-height: 28px;
                text-align: center;
                border-radius: 4px;
                font-size: 12px;
                cursor: pointer;
                transition: all 0.3s;
              }
              .primary {
                background: var(--prev-color-primary);
                color: #fff;
                &:hover {
                  background: var(--prev-color-primary-light-1);
                }
              }
              .default {
                background: var(--prev-bg-color);
                color: var(--prev-color-text-primary);
                &:hover {
                  background: var(--prev-border-color-base);
                }
              }
            }
          }

          &:hover {
            .theme-cover {
              height: 170px;
            }

            .theme-info {
              .theme-hover-actions {
                opacity: 1;
                transform: translateY(0);
                height: 32px;
                margin-top: 10px;
              }
            }
          }
        }
      }
    }
  }
}
.detail-content {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 100px);
  background: var(--prev-bg-color);

  .detail-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px 16px;
    background: var(--prev-bg-white);
    border-bottom: 1px solid var(--prev-border-color-lighter);

    .back-btn {
      cursor: pointer;
      color: var(--prev-color-text-primary);
      font-size: 14px;
      margin-right: 10px;
      display: flex;
      align-items: center;
      transition: color 0.3s;

      &:hover {
        color: var(--prev-color-primary);
      }

      i {
        margin-right: 4px;
      }
    }

    .detail-title {
      font-size: 14px;
      font-weight: 500;
      color: var(--prev-color-text-regular);
      border-left: 1px solid var(--prev-border-color-lighter);
      padding-left: 10px;
    }
  }

  .detail-body {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    background: var(--prev-bg-white);
    padding: 0 20px 24px 24px;

    .detail-top {
      display: flex;
      background: var(--prev-bg-white);
      padding: 24px 0 20px 0;
      margin-bottom: 20px;
      border-bottom: 1px solid var(--prev-border-color-lighter);

      .detail-preview-main {
        width: 180px;
        height: 210px;
        border-radius: 6px;
        overflow: hidden;
        margin-right: 24px;
        border: 1px solid var(--prev-border-color-lighter);
        img {
          width: 100%;
          object-fit: cover;
        }
      }

      .detail-info-box {
        flex: 1;
        margin-right: 24px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        .info-title {
          font-size: 16px;
          font-weight: 500;
          color: var(--prev-color-text-regular);
          margin-bottom: 20px;
        }

        .info-desc {
          font-size: 14px;
          line-height: 1.6;
          margin-bottom: 30px;
          color: var(--prev-color-text-secondary);
        }
      }
    }

    .detail-images-section {
      .section-title {
        font-size: 16px;
        font-weight: 500;
        color: var(--prev-color-text-regular);
        margin-bottom: 15px;
      }

      .images-list {
        display: flex;
        overflow-x: auto;
        padding-bottom: 10px;

        .image-item {
          margin-right: 29px;
          flex-shrink: 0;

          .image-label {
            font-size: 12px;
            color: var(--prev-color-text-secondary);
            text-align: center;
            margin-bottom: 10px;
          }

          .phone-mockup {
            width: 214px;
            height: 380px;
            background: var(--prev-bg-white);
            border-radius: 6px;
            overflow: hidden;
            position: relative;
            border: 1px solid var(--prev-border-color-lighter);
            .preview-image {
              width: 100%;
              object-fit: cover;
            }
            .no-poster {
              display: flex;
              flex-direction: column;
              justify-content: center;
              align-items: center;
              padding: 10px;
              margin-top: 70px;
              color: var(--prev-color-text-secondary);
              font-size: 13px;
            }
          }
        }
      }
      .detail-qrcode {
        width: max-content;
        height: max-content;
        text-align: center;
        background: var(--prev-bg-color);
        border-radius: 6px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 16px;

        .qrcode-box {
          width: 120px;
          height: 120px;
          margin-bottom: 10px;

          img {
            width: 100%;
            height: 100%;
          }
        }

        .qrcode-text {
          font-size: 12px;
          color: var(--prev-color-text-secondary);
        }
      }
    }
  }
}
</style>

<style lang="scss">
/* 弹窗壳层覆盖（非 scoped）：append-to-body 弹窗 teleport 到 body 后
   脱离组件样式作用域，scoped :deep 无法命中，必须用业务前缀全局类 */
.theme-dialog {
  border-radius: 6px;
  overflow: hidden;

  /* header 内边距归零，留白由内部 .dialog-header（15px 24px）提供，
     避免与全局 .el-dialog__header 的 15px 叠加成 30px 双重边距 */
  .el-dialog__header {
    padding: 0 !important;
    border-bottom: 1px solid var(--prev-border-color-lighter);
  }

  .el-dialog__body {
    padding: 0 !important;
    /* 放开全局 720px 上限，与 top:5vh 匹配，滚动交给内部网格/详情区 */
    max-height: calc(100vh - 100px);

    &::-webkit-scrollbar {
      width: 0;
    }
  }
}
</style>
