<template>
  <el-dialog
    v-model="dialogVisible"
    width="1188px"
    top="5vh"
    custom-class="theme-dialog"
    :show-close="false"
    append-to-body
    :modal="true"
    :modal-append-to-body="true"
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
                @change="searchTheme"
              ></el-input>
            </div>
          </div>

          <div class="theme-grid">
            <!-- 新建空白主题 -->
            <div class="theme-item create-new" v-if="activeTab === 'my'" @click="createNewTheme">
              <div class="create-icon">
                <div class="iconfont iconic_brush"></div>
              </div>
              <div class="create-text">新建空白主题</div>
            </div>

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
                  <div class="button default" @click="viewThemeDetail(theme)">查看详情</div>
                  <div class="button primary" @click="selectTheme(theme)">使用主题</div>
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
              <el-button type="primary" @click="selectTheme(currentTheme)">使用主题</el-button>
            </div>
          </div>
        </div>

        <div class="detail-images-section">
          <div class="section-title">主题图片</div>
          <div class="images-list">
            <div class="image-item" v-for="(opt, index) in filterOptions" :key="index">
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
  getThemeList(reqData).then((res) => {
    themes.value = res.data.list;
    total.value = res.data.count;
  });
}

function handlePageChange(val) {
  page.value = val;
  getList();
}

function toTheme() {
  window.open('https://www.crmeb.com/theme?from=crmebkytheme', '_blank');
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
// 弹窗样式
:deep(.theme-dialog) {
  border-radius: 8px;
  overflow: hidden;

  .el-dialog__header {
    border-bottom: 1px solid #f0f0f0;
    padding: 0px !important;
  }

  .el-dialog__body {
    padding: 0 !important;
  }
  .el-dialog__body::-webkit-scrollbar {
    width: 0%;
  }
}

.dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  padding: 15px 24px;
  .dialog-header-tabs {
    background: #fff;

    .tabs {
      display: flex;
      align-items: center;

      .title {
        font-size: 14px;
        line-height: 22px;
        font-weight: 500;
        color: #333;
        cursor: pointer;
        position: relative;
      }
      .theme-in {
        height: 12px;
        margin-left: 20px;
        cursor: pointer;
      }
    }
  }
}
.el-icon-close {
  font-size: 16px;
  color: #909399;
  cursor: pointer;
}

.dialog-content {
  display: flex;
  flex-direction: column;
  height: calc(100% - 100px);
  padding: 1px 24px 1px 24px !important;

  .content-wrapper {
    flex: 1;
    display: flex;
    overflow: hidden;
    .main-content {
      flex: 1;
      min-height: 756px;
      padding: 20px 0;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      background: #fff;
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

        .theme-item {
          width: 204px;
          height: 295px;
          background: #fff;
          border-radius: 8px;
          overflow: hidden;
          border: 1px solid #f0f0f0;
          display: flex;
          flex-direction: column;

          &.create-new {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #fff;
            cursor: pointer;
            border: 1px solid #eee;
            background: #fafafa;
            height: 295px; // Fixed height to match theme items
            transition: all 0.3s;

            .iconic_brush {
              font-size: 40px;
              color: #ccc;
              padding-bottom: 15px;
            }

            .create-text {
              color: #333;
              font-size: 14px;
            }

            &:hover {
              border-color: var(--prev-color-primary);
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
            background: #f0f2f5;
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
              background: #fff;
              border-radius: 10px 10px 0 0;
              overflow: hidden;
              border: 2px solid #fff;

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
                color: #999;
              }
            }
          }

          .theme-info {
            background: #fff;
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;

            .theme-title {
              padding: 12px 0px 0 12px;
              font-size: 14px;
              color: #333;
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
                color: rgba(2, 86, 255, 1);
                font-size: 12px;
                background: rgba(2, 86, 255, 0.06);
                padding: 4px 6px 4px 8px;
                border-radius: 10px 0 0 10px;
              }
            }

            .theme-time {
              padding: 0 12px 0 12px;

              font-size: 12px;
              color: #999;
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
                cursor: pointer;
              }
              .primary {
                background: var(--prev-color-primary);
                color: #fff;
                &:hover {
                  background: var(--prev-color-primary-light-1);
                }
              }
              .default {
                background: #f5f5f5;
                color: #606266;
                &:hover {
                  background: #e4e7ed;
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
:deep(.el-dialog__body) {
  max-height: calc(100vh - 100px) !important;
}
.detail-content {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 100px);
  background: #f5f7fa;

  .detail-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px 16px;
    background: #fff;
    border-bottom: 1px solid #eee;

    .back-btn {
      cursor: pointer;
      color: #666;
      font-size: 14px;
      margin-right: 10px;
      display: flex;
      align-items: center;

      &:hover {
        color: var(--prev-color-primary-light-1);
      }

      i {
        margin-right: 4px;
      }
    }

    .detail-title {
      font-size: 14px;
      font-weight: 500;
      color: #333;
      border-left: 1px solid #eee;
      padding-left: 10px;
    }
  }

  .detail-body {
    height: calc(100vh - 100px);
    overflow-y: auto;
    background: #fff;
    padding: 0 20px 0 24px !important;

    .detail-top {
      display: flex;
      background: #fff;
      padding: 24px 0 20px 0;
      margin-bottom: 20px;
      border-bottom: 1px solid #eee;

      .detail-preview-main {
        width: 180px;
        height: 210px;
        border-radius: 8px;
        overflow: hidden;
        margin-right: 24px;
        border: 1px solid #eeeeee;
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
          color: #333;
          margin-bottom: 20px;
        }

        .info-desc {
          font-size: 14px;
          color: #666;
          line-height: 1.6;
          margin-bottom: 30px;
          color: #888;
        }
      }
    }

    .detail-images-section {
      .section-title {
        font-size: 16px;
        font-weight: 500;
        color: #333;
        margin-bottom: 15px;
      }

      .images-list {
        display: flex;
        overflow-x: auto;
        padding-bottom: 10px;

        .image-item {
          margin-right: 29px;
          flex-shrink: 0;

          .phone-mockup {
            width: 214px;
            height: 380px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            border: 1px solid #eeeeee;
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
              color: rgba(153, 153, 153, 1);
              font-size: 13px;
            }
          }
        }
      }
      .detail-qrcode {
        width: max-content;
        height: max-content;
        text-align: center;
        background: #f9f9f9;
        border-radius: 8px 8px 8px 8px;
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
          color: #999;
        }
      }
    }
  }
}
</style>
