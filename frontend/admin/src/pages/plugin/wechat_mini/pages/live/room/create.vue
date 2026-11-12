<template>
  <div class="live-room-create">
    <pages-header
      ref="pageHeader"
      :title="$route.meta.title"
      :backUrl="$routeProStr + '/marketing/live/room'"
      description="填写直播间名称、主播与背景图等信息，提交微信审核通过后开播。"
    ></pages-header>

    <el-card :bordered="false" shadow="never" class="mt16">
      <el-alert class="live-room-create__alert" type="warning" show-icon :closable="false">
        <template #title>
          须先在微信小程序官方后台开通直播权限；关注
          <a class="live-room-create__link" href="javascript:;" v-db-click @click="codeImg">【小程序直播】</a>
          了解直播状态须知。
        </template>
      </el-alert>

      <el-form
        ref="formValidateRef"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        class="live-room-create__form"
        :rules="ruleValidate"
        @submit.prevent
      >
        <div class="live-room-create__section">
          <div class="live-room-create__section-title">基本信息</div>
          <el-form-item label="选择主播：" prop="anchor_wechat">
            <el-select
              v-model="formValidate.anchor_wechat"
              filterable
              clearable
              placeholder="请选择主播微信号"
              class="live-room-create__control"
              @change="anchorName"
            >
              <el-option
                v-for="(item, index) in liveList"
                :key="index"
                :value="item.wechat"
                :label="item.wechat"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="直播间名称：" prop="name">
            <el-input
              v-model="formValidate.name"
              class="live-room-create__control"
              placeholder="请输入直播间名称"
              maxlength="80"
              show-word-limit
            />
          </el-form-item>
          <el-form-item label="联系电话：">
            <el-input
              v-model="formValidate.phone"
              class="live-room-create__control"
              placeholder="请输入主播联系电话"
              maxlength="11"
              show-word-limit
            />
          </el-form-item>
          <el-form-item label="直播时间：" prop="start_time">
            <el-date-picker
              v-model="timeVal"
              clearable
              type="datetimerange"
              format="YYYY-MM-DD HH:mm"
              value-format="YYYY-MM-DD HH:mm"
              class="live-room-create__control"
              range-separator="-"
              start-placeholder="开始时间"
              end-placeholder="结束时间"
              @change="selectDate"
            />
          </el-form-item>
          <el-form-item label="排序：">
            <el-input-number v-model="formValidate.sort" :min="0" :controls="false" class="live-room-create__number" />
          </el-form-item>
        </div>

        <div class="live-room-create__section">
          <div class="live-room-create__section-title">直播封面</div>
          <el-form-item label="背景图：" prop="cover_img" class="live-room-create__cover-item">
            <div class="live-room-create__uploads">
              <div class="live-room-create__upload-block">
                <div class="live-room-create__upload live-room-create__upload--cover" v-db-click @click="modalPicTap(0)">
                  <img v-if="formValidate.cover_img" :src="formValidate.cover_img" alt="" />
                  <div v-else class="live-room-create__upload-empty">
                    <el-icon :size="22"><Picture /></el-icon>
                    <span>上传背景图</span>
                  </div>
                </div>
                <div class="live-room-create__tip">建议尺寸 1080×1920</div>
              </div>
              <el-form-item label="分享图：" prop="share_img" class="live-room-create__share-item">
                <div class="live-room-create__upload-block">
                  <div class="live-room-create__upload live-room-create__upload--share" v-db-click @click="modalPicTap(1)">
                    <img v-if="formValidate.share_img" :src="formValidate.share_img" alt="" />
                    <div v-else class="live-room-create__upload-empty">
                      <el-icon :size="22"><Picture /></el-icon>
                      <span>上传分享图</span>
                    </div>
                  </div>
                  <div class="live-room-create__tip">建议尺寸 800×640</div>
                </div>
              </el-form-item>
            </div>
          </el-form-item>
        </div>

        <div class="live-room-create__section">
          <div class="live-room-create__section-title">直播设置</div>
          <el-form-item label="直播间类型：">
            <el-radio-group v-model="formValidate.type">
              <el-radio v-for="(item, index) in type" :key="index" :value="item.label">{{ item.value }}</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="直播间点赞：">
            <el-switch
              v-model="formValidate.close_like"
              :active-value="1"
              :inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
            />
          </el-form-item>
          <el-form-item label="直播卖货：">
            <el-switch
              v-model="formValidate.close_goods"
              :active-value="1"
              :inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
            />
          </el-form-item>
          <el-form-item label="直播间评论：">
            <el-switch
              v-model="formValidate.close_comment"
              :active-value="1"
              :inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
            />
          </el-form-item>
        </div>

        <el-form-item>
          <el-button :loading="loading" type="primary" v-db-click @click="handleSubmit">提交</el-button>
          <el-button v-db-click @click="goBack">取消</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <el-dialog v-model="modalPic" width="950px" title="选择图片" :close-on-click-modal="false" :z-index="888">
      <uploadPictures
        v-if="modalPic"
        :isChoice="isChoice"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        @getPic="getPic"
      ></uploadPictures>
    </el-dialog>

    <el-dialog v-model="modal3" title="小程序直播二维码" width="360px">
      <div class="live-room-create__qr">
        <div v-viewer class="live-room-create__qr-pic">
          <img src="https://res.wx.qq.com/op_res/9rSix1dhHfK4rR049JL0PHJ7TpOvkuZ3mE0z7Ou_Etvjf-w1J_jVX0rZqeStLfwh" alt="" />
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { ElMessage } from 'element-plus';
import { Picture } from '@element-plus/icons-vue';
import { useMediaStore } from '@/store/module/media';
import uploadPictures from '@/components/uploadPictures';
import { liveAddApi, liveAnchorListApi } from '../../../api';
import Setting from '@/setting';

defineOptions({ name: 'LiveRoomCreate' });

const router = useRouter();

const { isMobile } = storeToRefs(useMediaStore());
const labelWidth = computed(() => (isMobile.value ? undefined : '120px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const gridBtn = { xl: 4, lg: 8, md: 8, sm: 8, xs: 8 };
const gridPic = { xl: 6, lg: 8, md: 12, sm: 12, xs: 12 };
const loading = ref(false);
const formValidate = ref({
  name: '',
  anchor_name: '',
  anchor_wechat: '',
  phone: '',
  screen_type: 0,
  close_like: 1,
  close_goods: 1,
  close_comment: 1,
  cover_img: '',
  share_img: '',
  sort: 0,
  type: 0,
  start_time: '',
});
const type = [{ value: '手机直播', label: 0 }];
const timeVal = ref('');
const modalPic = ref(false);
const isChoice = ref('单选');
const activeIndex = ref(0);
const liveList = ref([]);
const modal3 = ref(false);
const ruleValidate = ref({
  anchor_wechat: [{ required: true, message: '请选择主播', trigger: 'change' }],
  name: [{ required: true, message: '请输入直播间名称', trigger: 'blur' }],
  cover_img: [{ required: true, message: '请上传背景图', trigger: 'change' }],
  share_img: [{ required: true, message: '请上传分享图', trigger: 'change' }],
  start_time: [{ required: true, message: '请选择直播时间', trigger: 'change' }],
});

const formValidateRef = ref(null);

onMounted(() => {
  getLive();
});

function goBack() {
  router.push({ path: Setting.routePre + '/marketing/live/room' });
}

function codeImg() {
  modal3.value = true;
}

function anchorName(e) {
  const hit = liveList.value.find((el) => el.wechat === e);
  formValidate.value.anchor_name = hit?.name || '';
}

function getLive() {
  liveAnchorListApi({ kerword: '', page: '', limit: '' })
    .then((res) => {
      liveList.value = res.data.list;
    })
    .catch((error) => {
      ElMessage.error(error.msg);
    });
}

function modalPicTap(t) {
  activeIndex.value = t;
  modalPic.value = true;
}

function selectDate(e) {
  formValidate.value.start_time = e || '';
}

function getPic(pc) {
  nextTick(() => {
    if (activeIndex.value == 0) {
      formValidate.value.cover_img = pc.att_dir;
    } else {
      formValidate.value.share_img = pc.att_dir;
    }
    modalPic.value = false;
  });
}

function handleSubmit() {
  formValidateRef.value.validate((valid) => {
    if (!valid) return;
    loading.value = true;
    liveAddApi(formValidate.value)
      .then(() => {
        ElMessage.success('添加成功');
        setTimeout(() => {
          loading.value = false;
          goBack();
        }, 500);
      })
      .catch((error) => {
        loading.value = false;
        ElMessage.error(error.msg);
      });
  });
}
</script>

<style lang="scss" scoped>
.live-room-create {
  &__alert {
    margin-bottom: 20px;
  }

  &__link {
    color: var(--prev-color-primary);
    cursor: pointer;
  }

  &__form {
    max-width: 860px;
  }

  &__section {
    margin-bottom: 8px;
  }

  &__section-title {
    margin: 0 0 16px;
    padding-left: 8px;
    border-left: 3px solid var(--prev-color-primary);
    font-size: 14px;
    font-weight: 500;
    line-height: 14px;
    color: var(--prev-color-text-regular);
  }

  &__control {
    width: 460px;
    max-width: 100%;
  }

  &__number {
    width: 160px;
  }

  &__cover-item {
    :deep(.el-form-item__label) {
      line-height: 22px;
      height: auto;
      padding-top: 0;
    }

    :deep(.el-form-item__content) {
      align-items: flex-start;
    }
  }

  &__uploads {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 24px 56px;
  }

  &__share-item {
    margin-bottom: 0;

    :deep(.el-form-item__label) {
      width: auto !important;
      margin-right: 12px;
      line-height: 22px;
      height: auto;
      padding-top: 0;
      justify-content: flex-start;
    }

    :deep(.el-form-item__content) {
      margin-left: 0 !important;
    }
  }

  &__upload-block {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }

  &__upload {
    overflow: hidden;
    border: 1px dashed var(--prev-border-color-lighter);
    border-radius: 4px;
    background: var(--prev-bg-color);
    cursor: pointer;

    &:hover {
      border-color: var(--prev-color-primary);
    }

    img {
      display: block;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    &--cover {
      width: 90px;
      height: 160px;
    }

    &--share {
      width: 120px;
      height: 96px;
    }
  }

  &__upload-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    height: 100%;
    color: var(--prev-color-text-secondary);
    font-size: 12px;
  }

  &__tip {
    margin-top: 8px;
    font-size: 12px;
    line-height: 16px;
    color: var(--prev-color-text-secondary);
  }

  &__qr {
    display: flex;
    justify-content: center;
    padding: 8px 0 4px;
  }

  &__qr-pic {
    width: 180px;
    height: 180px;

    img {
      width: 100%;
      height: 100%;
    }
  }
}
</style>
