<template>
  <div class="newsBox">
    <pages-header
      ref="pageHeader"
      :title="$route.meta.title"
      :backUrl="$routeProStr + '/app/wechat/news_category/index'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="save_from mt16">
      <el-row :gutter="24">
        <el-col :xl="6" :lg="6" :md="12" :sm="24" :xs="24">
          <!--                    v-if="list.length!=0"-->
          <div v-for="(item, i) in list" :key="i">
            <div
              v-if="i === 0"
              v-db-click
              @click="onSubSave(i)"
              :class="{ checkClass: i === current }"
              @mouseenter="isDel = true"
              @mouseleave="isDel = false"
            >
              <div
                class="news_pic"
                :style="{
                  backgroundImage: 'url(' + (item.image_input ? item.image_input : baseImg) + ')',
                  backgroundSize: '100% 100%',
                }"
              >
                <el-button type="error" icon="el-icon-delete" v-db-click @click="del(i)" v-show="isDel"></el-button>
              </div>
              <span class="news_sp">{{ item.title }}</span>
            </div>
            <div class="news_cent" v-else v-db-click @click="onSubSave(i)" :class="{ checkClass: i === current }">
              <span class="news_sp1">{{ item.title }}</span>
              <div class="news_cent_img ivu-mr-8">
                <img :src="item.image_input ? item.image_input : baseImg" />
              </div>
              <el-button type="error" icon="el-icon-delete" v-db-click @click="del(i)"></el-button>
            </div>
          </div>
          <!-- <div class="acea-row row-center-wrapper">
            <el-button class="mt20" type="primary" v-db-click @click="handleAdd">添加图文</el-button>
          </div> -->
        </el-col>
        <el-col :xl="18" :lg="18" :md="12" :sm="24" :xs="24">
          <el-form
            class="saveForm"
            ref="saveFormRef"
            :model="saveForm"
            :label-width="labelWidth"
            :rules="ruleValidate"
            :label-position="labelPosition"
            @submit.prevent
          >
            <el-row :gutter="24">
              <el-col :span="24" class="ml40">
                <el-form-item label="标题：" prop="title">
                  <el-input style="width: 60%" v-model="saveForm.title" type="text" placeholder="请输入文章标题" />
                </el-form-item>
              </el-col>
              <el-col :span="24" class="ml40">
                <el-form-item label="作者：" prop="author">
                  <el-input style="width: 60%" v-model="saveForm.author" type="text" placeholder="请输入作者名称" />
                </el-form-item>
              </el-col>
              <el-col :span="24" class="ml40">
                <el-form-item label="摘要：" prop="synopsis">
                  <el-input style="width: 60%" v-model="saveForm.synopsis" type="textarea" placeholder="请输入摘要" />
                </el-form-item>
              </el-col>
              <el-col :span="24" class="ml40">
                <el-form-item label="图文封面：" prop="image_input">
                  <div class="picBox" v-db-click @click="modalPicTap('单选')">
                    <div class="pictrue" v-if="saveForm.image_input">
                      <img :src="saveForm.image_input" />
                    </div>
                    <div class="upLoad acea-row row-center-wrapper" v-else>
                      <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                    </div>
                  </div>
                </el-form-item>
                <el-form-item label="正文：" prop="content">
                  <WangEditor style="width: 90%" :content="content" @editorContent="getEditorContent"></WangEditor>
                </el-form-item>
              </el-col>
              <el-col :span="24" class="ml40">
                <el-form-item>
                  <el-button type="primary" class="submission" v-db-click @click="subFrom('saveForm')">提交</el-button>
                </el-form-item>
              </el-col>
              <el-dialog v-model="modalPic" width="1024px" title="上传文章图" :close-on-click-modal="false">
                <uploadPictures
                  :isChoice="isChoice"
                  @getPic="getPic"
                  :gridBtn="gridBtn"
                  :gridPic="gridPic"
                  v-if="modalPic"
                ></uploadPictures>
              </el-dialog>
            </el-row>
          </el-form>
        </el-col>
      </el-row>
    </el-card>
  </div>
</template>

<script setup>
import __asset_bjt_0 from '@/assets/images/bjt.png';

import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { ElMessage } from 'element-plus';
import { useMediaStore } from '@/store/module/media';
import WangEditor from '@/components/wangEditor/index.vue';
import uploadPictures from '@/components/uploadPictures';
import { wechatNewsAddApi, wechatNewsInfotApi } from '../../api';
import Setting from '@/setting';

defineOptions({ name: 'newsCategorySave' });

const route = useRoute();
const router = useRouter();

const { isMobile } = storeToRefs(useMediaStore());
const labelWidth = computed(() => (isMobile.value ? undefined : '100px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const myConfig = {
  autoHeightEnabled: false, // 编辑器不自动被内容撑高
  initialFrameHeight: 500, // 初始容器高度
  initialFrameWidth: '100%', // 初始容器宽度
  UEDITOR_HOME_URL: '/UEditor/',
  serverUrl: '',
};
const isChoice = ref('单选');
const dragging = ref(null);
const isDel = ref(false);
const msg = ref('');
const count = ref([]);
const baseImg = ref(__asset_bjt_0);
const saveForm = ref({
  title: '',
  author: '',
  synopsis: '',
  image_input: '',
  content: '',
  id: 0,
});
const current = ref(0);
const list = ref([
  {
    title: '',
    author: '',
    synopsis: '',
    image_input: '',
    content: '',
    id: 0,
  },
]);
const uploadList = ref([]);
const modalPic = ref(false);
const gridPic = {
  xl: 6,
  lg: 8,
  md: 12,
  sm: 12,
  xs: 12,
};
const gridBtn = {
  xl: 4,
  lg: 8,
  md: 8,
  sm: 8,
  xs: 8,
};
const content = ref('');

const validateUpload = (rule, value, callback) => {
  if (saveForm.value.image_input) {
    callback();
  } else {
    callback(new Error('请上传图文封面'));
  }
};
const ruleValidate = ref({
  title: [{ required: true, message: '请输入标题', trigger: 'blur' }],
  author: [{ required: true, message: '请输入作者', trigger: 'blur' }],
  image_input: [{ required: true, validator: validateUpload, trigger: 'change' }],
  content: [{ required: true, message: '请输入正文', trigger: 'change' }],
  synopsis: [{ required: true, message: '请输入文章摘要', trigger: 'blur' }],
});

const saveFormRef = ref(null);
const pageHeader = ref(null);

watch(
  () => route.params.id,
  (to, from) => {
    if (route.params.id !== '0') {
      info();
    } else {
      list.value = [
        {
          title: '',
          author: '',
          synopsis: '',
          image_input: '',
          content: '',
          id: 0,
        },
      ];
      saveForm.value = list.value[current.value];
    }
  },
);

onMounted(() => {
  if (route.params.id !== '0') {
    info();
  } else {
    saveForm.value = list.value[current.value];
  }
});

function getEditorContent(data) {
  saveForm.value.content = data;
}
// 点击图文封面
function modalPicTap() {
  modalPic.value = true;
}
// 获取图片信息
function getPic(pc) {
  saveForm.value.image_input = pc.att_dir;
  modalPic.value = false;
}
// 添加图文按钮
function handleAdd() {
  if (!check()) return false;
  let obj = {
    title: '',
    author: '',
    synopsis: '',
    image_input: '',
    content: '',
    id: 0,
  };
  list.value.push(obj);
}
// 点击模块
function onSubSave(i) {
  current.value = i;
  list.value.map((item, index) => {
    if (index === current.value) return (saveForm.value = list.value[current.value]);
  });
  content.value = saveForm.value.content;
}
// 删除
function del(i) {
  if (i === 0) {
    ElMessage.warning('不能再删除了');
  } else {
    list.value.splice(i, 1);
    saveForm.value = {};
  }
}
// 详情
function info() {
  wechatNewsInfotApi(route.params.id)
    .then(async (res) => {
      let data = res.data.info;
      list.value = data.new;
      saveForm.value = list.value[current.value];
      content.value = list.value[current.value].content;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 提交数据
function subFrom(name) {
  saveFormRef.value.validate((valid) => {
    if (valid) {
      let data = {
        id: route.params.id || 0,
        list: list.value,
      };
      wechatNewsAddApi(data)
        .then(async (res) => {
          ElMessage.success(res.msg);
          setTimeout(() => {
            router.push({
              path: Setting.routePre + '/app/wechat/news_category/index',
            });
          }, 500);
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    } else {
      return false;
    }
  });
}
function check() {
  for (let index in list.value) {
    if (!list.value[index].title) {
      ElMessage.warning('请输入文章的标题');
      return false;
    } else if (!list.value[index].author) {
      ElMessage.warning('请输入文章的作者');
      return false;
    } else if (!list.value[index].synopsis) {
      ElMessage.warning('请输入文章的摘要');
      return false;
    } else if (!list.value[index].image_input) {
      ElMessage.warning('请输入文章的图文封面');
      return false;
    } else if (!list.value[index].content) {
      ElMessage.warning('请输入文章的内容');
      return false;
    } else {
      return true;
    }
  }
}
</script>

<style lang="scss" scoped>
.newsBox {
  :deep(.ivu-global-footer) {
    display: none !important;
  }
}
.demo-upload-list {
  display: inline-block;
  width: 60px;
  height: 60px;
  text-align: center;
  line-height: 60px;
  border: 1px solid transparent;
  border-radius: 4px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
  margin-right: 15px;
  position: relative;
}
.btndel {
  position: absolute;
  z-index: 111;
  width: 20px !important;
  height: 20px !important;
  left: 46px;
  top: -4px;
}
.demo-upload-list img {
  width: 100%;
  height: 100%;
}
.demo-upload-list-cover {
  display: none;
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.6);
}
.demo-upload-list:hover .demo-upload-list-cover {
  display: block;
}
.demo-upload-list-cover i {
  color: #fff;
  font-size: 20px;
  cursor: pointer;
  margin: 0 2px;
}
.save_from :deep(.ivu-btn-error) {
  width: 24px !important;
  height: 24px !important;
  background: #fff !important;
  color: #999 !important;
  border: 1px solid #eee !important;
}
.save_from :deep(.ivu-btn-error:hover) {
  background: #ff5d5f !important;
  border: 1px solid #fff !important;
  color: #fff !important;
}
.picBox {
  display: inline-block;
  cursor: pointer;
}
.pictrue {
  width: 60px;
  height: 60px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  margin-right: 10px;
}
.pictrue img {
  width: 100%;
  height: 100%;
}
.upLoad {
  width: 58px;
  height: 58px;
  line-height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.02);
}
.checkClass {
  border: 1px dashed #0091ff !important;
}
.checkClass2 {
  border: 1px solid #0091ff !important;
}
.submission {
  width: 10%;
}
.cover {
  width: 60px;
  height: 60px;

  img {
    width: 100%;
    height: 100%;
  }
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
  background-size: 100%;
  background-position: center center;
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
  border-bottom: 1px dashed #eee;
}
.news_cent {
  width: 100%;
  height: auto;
  background: #fff;
  border-bottom: 1px dashed #eee;
  display: flex;
  padding: 10px;
  box-sizing: border-box;
  justify-content: space-between;
  align-items: center;
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
</style>
