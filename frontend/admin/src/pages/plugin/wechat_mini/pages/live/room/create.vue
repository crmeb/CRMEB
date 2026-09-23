<template>
  <div>
    <pages-header
      ref="pageHeader"
      :title="$route.meta.title"
      :backUrl="$routeProStr + '/marketing/live/room'"
      description="填写直播间名称、主播与背景图等信息，提交微信审核通过后开播。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-form
        ref="formValidateRef"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        class="tabform"
        :rules="ruleValidate"
        @submit.prevent
      >
        <el-row :gutter="24">
          <el-col :span="24">
            <el-alert class="mb10" type="warning" show-icon :closable="false">
              <template #title
                ><span
                  >必须前往微信小程序官方后台开通直播权限，关注<span
                    style="color: red; cursor: pointer"
                    v-db-click
                    @click="codeImg"
                    >【小程序直播】</span
                  >须知直播状态</span
                ></template
              >
            </el-alert>
          </el-col>
          <el-col :span="24">
            <el-form-item label="选择主播：" prop="anchor_wechat">
              <el-select
                v-model="formValidate.anchor_wechat"
                filterable
                clearable
                class="content_width"
                @change="anchorName"
              >
                <el-option
                  v-for="(item, index) in liveList"
                  :value="item.wechat"
                  :key="index"
                  :label="item.wechat"
                ></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="直播间名称：" prop="name">
              <el-input
                enter-button
                placeholder="请输入直播间名称"
                element-id="name"
                v-model="formValidate.name"
                class="content_width"
                maxlength="80"
                show-word-limit
              />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <div style="display: flex">
              <el-form-item label="背景图：" prop="name">
                <div v-db-click @click="modalPicTap(0)" class="box">
                  <img :src="formValidate.cover_img" alt="" v-if="formValidate.cover_img" />
                  <div class="upload-box acea-row row-center-wrapper" v-else>
                    <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                  </div>
                </div>
                <div class="desc">尺寸：1080*1920px</div>
              </el-form-item>
            </div>
          </el-col>
          <el-col :span="24">
            <div style="display: flex">
              <el-form-item label="分享图：" prop="name">
                <div v-db-click @click="modalPicTap(1)" class="box">
                  <img :src="formValidate.share_img" alt="" v-if="formValidate.share_img" />
                  <div class="upload-box acea-row row-center-wrapper" v-else>
                    <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                  </div>
                </div>
                <div class="desc">尺寸：800*640px</div>
              </el-form-item>
            </div>
          </el-col>
          <el-col :span="24">
            <el-form-item label="联系电话：">
              <el-input
                placeholder="请输入主播联系电话"
                v-model="formValidate.phone"
                class="content_width"
                maxlength="11"
                show-word-limit
              />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="直播时间：" prop="name">
              <el-date-picker
                clearable
                type="datetimerange"
                format="YYYY-MM-DD HH:mm"
                placeholder="请选择直播时间"
                class="content_width"
                v-model="timeVal"
                @change="selectDate"
                value-format="YYYY-MM-DD HH:mm"
                range-separator="-"
                start-placeholder="开始日期"
                end-placeholder="结束日期"
              ></el-date-picker>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="排序：">
              <el-input type="number" placeholder="0" v-model="formValidate.sort" class="content_width" />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="直播间类型：">
              <el-radio-group v-model="formValidate.type">
                <el-radio :label="item.label" :value="item.label" v-for="(item, index) in type" :key="index">
                  <span>{{ item.value }}</span>
                </el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="直播间点赞：">
              <el-switch
                class="defineSwitch"
                :active-value="1"
                :inactive-value="0"
                v-model="formValidate.close_like"
                size="large"
                active-text="开启"
                inactive-text="关闭"
              >
              </el-switch>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="直播卖货：">
              <el-switch
                class="defineSwitch"
                :active-value="1"
                :inactive-value="0"
                v-model="formValidate.close_goods"
                size="large"
                active-text="开启"
                inactive-text="关闭"
              >
              </el-switch>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="直播间评论：">
              <el-switch
                class="defineSwitch"
                :active-value="1"
                :inactive-value="0"
                v-model="formValidate.close_comment"
                size="large"
                active-text="开启"
                inactive-text="关闭"
              >
              </el-switch>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="24">
          <el-col v-bind="grid" :span="24">
            <el-button
              :loading="loading"
              type="primary"
              style="margin-left: 120px"
              v-db-click
              @click="handleSubmit('formItem')"
            >
              提交
            </el-button>
          </el-col>
        </el-row>
      </el-form>
    </el-card>
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
    <el-dialog v-model="modal3" title="二维码">
      <div class="acea-row row-around">
        <div v-viewer class="QRpic">
          <img src="https://res.wx.qq.com/op_res/9rSix1dhHfK4rR049JL0PHJ7TpOvkuZ3mE0z7Ou_Etvjf-w1J_jVX0rZqeStLfwh" />
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
const grid = { xl: 10, lg: 16, md: 18, sm: 24, xs: 24 };
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
});

const formValidateRef = ref(null);

onMounted(() => {
  getLive();
});

function cancel() {
  modal3.value = false;
}
function codeImg() {
  modal3.value = true;
}
function anchorName(e) {
  liveList.value.filter((el) => {
    if (el.wechat === e) {
      formValidate.value.anchor_name = el.name;
    }
  });
}
/** 主播列表 */
function getLive() {
  let form = { kerword: '', page: '', limit: '' };
  liveAnchorListApi(form)
    .then((res) => {
      liveList.value = res.data.list;
    })
    .catch((error) => {
      ElMessage.error(error.msg);
    });
}
/** 点击图文封面 */
function modalPicTap(t) {
  activeIndex.value = t;
  modalPic.value = true;
}
/** 选择日期 */
function selectDate(e) {
  formValidate.value.start_time = e;
}
/** 获取图片信息 */
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
/** 保存 */
function handleSubmit(name) {
  loading.value = true;
  liveAddApi(formValidate.value)
    .then((res) => {
      ElMessage.success('添加成功');
      setTimeout(() => {
        loading.value = false;
        router.push({ path: Setting.routePre + '/marketing/live/room' });
      }, 500);
    })
    .catch((error) => {
      setTimeout(() => {
        loading.value = false;
      }, 1000);
      ElMessage.error(error.msg);
    });
}
</script>

<style lang="scss" scoped>
.content_width {
  width: 460px;
}
.QRpic {
  width: 180px;
  height: 180px;
}
.QRpic img {
  width: 100%;
  height: 100%;
}
.desc {
  font-size: 12px;
  color: #999;
}
.upload-box {
  width: 58px;
  height: 58px;
  line-height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.02);
  cursor: pointer;
}
.box {
  width: 60px;
  height: 60px;
  border-radius: 4px;
  cursor: pointer;
}
.box img {
  width: 100%;
  height: 100%;
}
</style>
