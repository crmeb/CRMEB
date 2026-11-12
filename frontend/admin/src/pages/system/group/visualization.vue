<template>
  <div :style="bgcolors">
    <div class="i-layout-page-header header-title">
      <div class="header-main">
        <span class="ivu-page-header-title mr20">{{ $route.meta.title }}</span>
        <span class="header-description">
          <el-icon class="header-description-icon"><InfoFilled /></el-icon>
          维护移动端可视化数据（开屏广告、充值引导等），右侧可实时预览效果。
        </span>
      </div>
      <div>
        <div style="float: right">
          <el-button class="bnt" type="primary" v-db-click @click="save">保存</el-button>
        </div>
      </div>
    </div>
    <div class="box-wrapper h100">
      <div class="left-wrapper" v-if="!$route.params.id && groupAll.length">
        <div class="tree-vis">
          <div
            class="tab-item"
            :class="{ active: item.id == pageId }"
            v-for="(item, index) in groupAll"
            :key="index"
            v-db-click
            @click="edits(item)"
          >
            {{ item.name }}
          </div>
          <div class="tab-item" :class="{ active: pageId == 1617 }" v-db-click @click="edits(2)">开屏广告</div>
        </div>
      </div>
      <div v-if="name == 'user_recharge_quota'" class="iframe">
        <div class="iframe-boxs">
          <div class="moneyBox">
            <div class="box1">
              <div class="font1">我的余额</div>
              <div>￥ <i class="font2">0.00</i></div>
            </div>
            <div class="moneyBox_content">
              <div class="box2">
                <div>账户充值</div>
                <div>佣金导入</div>
              </div>
              <div class="box3">
                <div v-show="item.status != 0" class="box3_box" v-for="(item, index) in sginList.list" :key="index">
                  <div>{{ item.price }}<i class="font">元</i></div>
                  <div class="font">赠送:{{ item.give_money }}元</div>
                </div>
                <div class="box3_box">
                  <div class="other">其他</div>
                </div>
              </div>
              <div class="box4">
                <div class="tips">注意事项：</div>
                <div class="tips-samll">
                  <p>充值后帐户的金额不能提现，可用于商城消费使用。</p>
                  <p>佣金导入账户之后不能再次导出、不可提现。</p>
                  <p>账户充值出现问题可联系商城客服，也可拨打商城客服热线：40088888889。</p>
                </div>
              </div>
              <div class="box5">立即充值</div>
            </div>
          </div>
        </div>
      </div>
      <div v-if="name == 'admin_login_slide'" class="pciframe" :bordered="false" shadow="never">
        <img src="../../../assets/images/pclogin.png" class="pciframe-box" />
        <div class="pcmoddile_goods">
          <div class="nofont" v-if="tabList.list == ''">暂无照片，请添加~</div>
          <swiper v-else :options="swiperOption" class="pcswiperimg_goods">
            <swiper-slide class="spcwiperimg_goods" v-for="(item, index) in tabList.list" :key="index">
              <img :src="item.slide" />
            </swiper-slide>
          </swiper>
        </div>
      </div>
      <div v-if="name == 'integral_shop_banner'" class="iframe" :bordered="false">
        <div class="iframe-box">
          <img src="../../../assets/images/integral.png" style="width: 100%" />
          <div class="moddile_goods">
            <div class="nofonts" v-if="tabList.list == '' || !tabList.list.length || !tabList.list[0].img">
              暂无照片，请添加~
            </div>
            <swiper v-else :options="swiperOption" class="pcswiperimg_goods">
              <swiper-slide class="swiperimg_goods" v-for="(item, index) in tabList.list" :key="index">
                <img :src="item.img" />
              </swiper-slide>
            </swiper>
          </div>
        </div>
      </div>
      <div
        v-if="
          name != 'sign_day_num' &&
          name != 'admin_login_slide' &&
          name != 'user_recharge_quota' &&
          name != 'integral_shop_banner' &&
          a != 1 &&
          guide != 2
        "
        class="iframe"
        :bordered="false"
        shadow="never"
      >
        <iframe :src="url" class="iframe-box" frameborder="0"></iframe>
        <div class="moddile"></div>
        <div v-if="name == 'combination_banner'" class="moddile_goods">
          <div class="nofonts" v-if="tabList.list == ''">暂无照片，请添加~</div>
          <swiper v-else :options="swiperOption" class="swiperimg_goods">
            <swiper-slide class="swiperimg_goods" v-for="(item, index) in tabList.list" :key="index">
              <img :src="item.img" />
            </swiper-slide>
          </swiper>
        </div>
      </div>
      <div v-if="name == 'sign_day_num'" class="iframe" :bordered="false">
        <div class="iframe-box">
          <img v-if="bgimg == 1" src="../../../assets/images/bluesgin.png" />
          <img v-if="bgimg == 2" src="../../../assets/images/greesgin.png" />
          <img v-if="bgimg == 3" src="../../../assets/images/redsgin.png" />
          <img v-if="bgimg == 4" src="../../../assets/images/pinksgin.png" />
          <img v-if="bgimg == 5" src="../../../assets/images/oragesgin.png" />
        </div>
      </div>
      <div v-if="a == 1" class="iframe" :bordered="false">
        <div class="agreement-box">
          <div class="template"></div>
          <div class="htmls_box">
            <div class="htmls_top">服务协议与隐私政策</div>
            <div class="htmls_font">
              <div class="ok">我同意</div>
              <div>不同意</div>
            </div>
            <div class="htmls" v-html="formValidate.content"></div>
          </div>
        </div>
      </div>
      <div v-if="a == 1" style="margin-left: 40px">
        <div class="table_box">
          <div>
            <div v-bind="grid">
              <div class="title">隐私权限页面展示：</div>
            </div>
          </div>
          <div>
            <el-form
              class="form"
              ref="formValidateRef"
              :model="formValidate"
              :rules="ruleValidate"
              :label-width="labelWidth"
              :label-position="labelPosition"
              @submit.prevent
            >
              <div class="goodsTitle acea-row"></div>
              <el-form-item label="" prop="content" style="margin: 0px">
                <WangEditor
                  style="width: 90%"
                  :content="formValidate.content"
                  @editorContent="getEditorContent"
                ></WangEditor>
              </el-form-item>
            </el-form>
          </div>
        </div>
      </div>
      <div v-if="guide == 2" class="iframe" :bordered="false">
        <div class="nofonts" v-if="tabList.list == '' || !tabList.list.length || !tabList.list[0].img">
          暂无照片，请添加~
        </div>
        <swiper :options="swiperOption" class="swiperimgs" v-else>
          <swiper-slide class="swiperimgs" v-for="(item, index) in tabList.list" :key="index">
            <img :src="item.img" />
          </swiper-slide>
        </swiper>
      </div>
      <div v-if="name == 'sign_day_num'" style="margin-left: 20px">
        <div class="table_box">
          <div>
            <div v-bind="grid">
              <div class="title">签到天数设置</div>
              <el-button
                type="primary"
                v-db-click
                @click="groupAdd('添加数据')"
                style="margin-left: 14px; margin-top: 30px"
                >添加数据</el-button
              >
            </div>
          </div>
          <div class="table">
            <el-table
              :data="cmsList"
              ref="table"
              class="mt14"
              v-loading="loading"
              highlight-current-row
              no-userFrom-text="暂无数据"
              no-filtered-userFrom-text="暂无筛选结果"
            >
              <el-table-column label="编号" width="80">
                <template #default="scope">
                  <span>{{ scope.row.id }}</span>
                </template>
              </el-table-column>
              <el-table-column label="第几天" min-width="80">
                <template #default="scope">
                  <span>{{ scope.row.day }}</span>
                </template>
              </el-table-column>
              <el-table-column label="获取积分" min-width="80">
                <template #default="scope">
                  <span>{{ scope.row.sign_num }}</span>
                </template>
              </el-table-column>

              <el-table-column label="是否可用" min-width="80">
                <template #default="scope">
                  <!-- 后端 status 为字符串，Number 归一化避免 ElSwitch 类型不匹配报错 -->
                  <el-switch
                    :active-value="1"
                    :inactive-value="0"
                    :model-value="Number(scope.row.status)"
                    @change="
                      (val) => {
                        scope.row.status = val;
                        onchangeIsShow(scope.row);
                      }
                    "
                    size="large"
                  >
                  </el-switch>
                </template>
              </el-table-column>
              <el-table-column label="排序" min-width="80">
                <template #default="scope">
                  <span>{{ scope.row.sort }}</span>
                </template>
              </el-table-column>
              <el-table-column label="操作" fixed="right" width="150">
                <template #default="scope">
                  <a v-db-click @click="edit(scope.row, '编辑')">编辑</a>
                  <el-divider direction="vertical"></el-divider>
                  <a v-db-click @click="del(scope.row, '删除这条信息', scope.$index)">删除</a>
                </template>
              </el-table-column>
            </el-table>
          </div>
        </div>
      </div>
      <div v-if="name == 'user_recharge_quota'" style="margin-left: 20px">
        <div class="table_box">
          <div>
            <div v-bind="grid">
              <div class="title">充值金额设置</div>
              <el-button
                type="primary"
                v-db-click
                @click="groupAdd('添加数据')"
                style="margin-left: 14px; margin-top: 30px"
                >添加数据</el-button
              >
            </div>
          </div>
          <div class="table">
            <el-table
              :data="sginList.list"
              ref="table"
              class="mt14"
              v-loading="loading"
              highlight-current-row
              no-userFrom-text="暂无数据"
              no-filtered-userFrom-text="暂无筛选结果"
            >
              <el-table-column
                :label="item.title"
                :min-width="item.minWidth"
                v-for="(item, index) in columns1"
                :key="index"
              >
                <template #default="scope">
                  <template v-if="item.key">
                    <div>
                      <span>{{ scope.row[item.key] }}</span>
                    </div>
                  </template>
                  <template v-else-if="item.slot === 'status'">
                    <!-- 后端 status 为字符串，Number 归一化避免 ElSwitch 类型不匹配报错 -->
                    <el-switch
                      :active-value="1"
                      :inactive-value="0"
                      :model-value="Number(scope.row.status)"
                      @change="
                        (val) => {
                          scope.row.status = val;
                          onchangeIsShow(scope.row);
                        }
                      "
                      size="large"
                    >
                    </el-switch>
                  </template>
                  <template v-else-if="item.slot === 'action'">
                    <a v-db-click @click="edit(scope.row, '编辑')">编辑</a>
                    <el-divider direction="vertical"></el-divider>
                    <a v-db-click @click="del(scope.row, '删除这条信息', scope.$index)">删除</a>
                  </template>
                </template>
              </el-table-column>
            </el-table>
          </div>
        </div>
      </div>
      <div v-else>
        <div
          v-if="name != 'sign_day_num' && a != 1 && guide != 2"
          :class="name != 'admin_login_slide' ? 'content' : 'contents'"
        >
          <div class="right-box">
            <div class="hot_imgs">
              <div class="title" v-if="name == 'admin_login_slide'">幻灯片设置</div>
              <div class="title" v-else>轮播图设置</div>
              <div class="title-text">建议尺寸：690 * 240px，拖拽图片可调整图片顺序哦，最多添加五张</div>
              <div class="list-box">
                <draggable
                  v-if="name == 'admin_login_slide'"
                  class="dragArea list-group Bbox"
                  :list="tabList.list"
                  group="peoples"
                  handle=".move-icon"
                  :item-key="dragItemKey"
                >
                  <template #item="{ element: item, index }">
                    <div class="items">
                      <div class="move-icon">
                        <span class="iconfont icondrag2"></span>
                      </div>
                      <div class="img-box" v-db-click @click="modalPicTap('单选', index)">
                        <img :src="item.slide" alt="" v-if="item.slide" />
                        <div class="upload-box" v-else>
                          <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                        </div>
                        <div class="delect-btn" v-db-click @click.stop="bindDelete(item, index)">
                          <i class="el-icon-circle-close" style="font-size: 24px" />
                        </div>
                      </div>
                      <div class="info"></div>
                    </div>
                  </template>
                </draggable>
                <draggable
                  v-else
                  class="dragArea list-group"
                  :list="tabList.list"
                  group="peoples"
                  handle=".move-icon"
                  :item-key="dragItemKey"
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
                        <div class="delect-btn" v-db-click @click.stop="bindDelete(item, index)">
                          <i class="el-icon-circle-close" style="font-size: 24px" />
                        </div>
                      </div>
                      <div class="info">
                        <div class="info-item">
                          <span>图片名称：</span>
                          <div class="input-box">
                            <el-input v-model="item.comment" placeholder="请填写名称" />
                          </div>
                        </div>
                        <div class="info-item">
                          <span>链接地址：</span>
                          <div class="input-box" v-db-click>
                            <el-input v-model="item.link" placeholder="选择链接">
                              <template #suffix><i class="el-icon-link" @click="getLink(index)" /></template>
                            </el-input>
                          </div>
                        </div>
                      </div>
                    </div>
                  </template>
                </draggable>
                <div>
                  <el-dialog
                    v-model="modalPic"
                    width="950px"
                    title="上传商品图"
                    :close-on-click-modal="false"
                    :show-close="true"
                  >
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
              <div class="add-btn">
                <el-button
                  type="primary"
                  ghost
                  style="width: 100px; height: 35px; background-color: var(--prev-color-primary); color: #ffffff"
                  v-db-click
                  @click="addBox"
                  >添加图片
                </el-button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-if="guide === 2" :class="name != 'admin_login_slide' ? 'content' : 'contents'">
        <div class="right-box">
          <div class="hot_imgs">
            <div class="title">引导页设置</div>
            <div class="title-text">建议尺寸：750 * 1334px，拖拽图片可调整图片顺序哦，最多添加五张</div>
            <div class="list-box">
              <div>
                <el-form :model="formItem" label-width="85px">
                  <el-form-item label="开屏广告:">
                    <!-- 后端/本地初始值的 status 类型不稳定（数字或字符串），Number 归一化后
                         再传入，避免 model-value 与 active-value 类型不一致触发 ElSwitch 报错 -->
                    <el-switch
                      :active-value="1"
                      :inactive-value="0"
                      :model-value="Number(formItem.status)"
                      @change="(val) => (formItem.status = val)"
                      size="large"
                    >
                    </el-switch>
                  </el-form-item>
                  <el-form-item label="广告时间:">
                    <el-input
                      v-model.number="formItem.time"
                      type="number"
                      placeholder="请输入开屏广告时间"
                      style="width: 150px"
                    ></el-input>
                    单位(秒)
                  </el-form-item>
                </el-form>
              </div>
              <draggable
                class="dragArea list-group"
                :list="tabList.list"
                group="peoples"
                handle=".move-icon"
                :item-key="dragItemKey"
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
                      <div class="delect-btn" v-db-click @click.stop="bindDelete(item, index)">
                        <i class="el-icon-circle-close" style="font-size: 24px" />
                      </div>
                    </div>
                    <div class="info">
                      <div class="info-item">
                        <span>图片名称：</span>
                        <div class="input-box">
                          <el-input v-model="item.comment" placeholder="请填写名称" />
                        </div>
                      </div>
                      <div class="info-item">
                        <span>链接地址：</span>
                        <div class="input-box" v-db-click>
                          <el-input v-model="item.link" placeholder="选择链接">
                            <template #suffix><i class="el-icon-link" @click="getLink(index)" /></template>
                          </el-input>
                        </div>
                      </div>
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
            <template v-if="tabList.list.length < 5">
              <div class="add-btn">
                <el-button
                  type="primary"
                  ghost
                  style="width: 100px; height: 35px; background-color: var(--prev-color-primary); color: #ffffff"
                  v-db-click
                  @click="addBox"
                  >添加图片
                </el-button>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script setup>
// TODO(vue3): swiper 仍为旧模板写法（:options 在 swiper/vue 组件上不生效），如需轮播参数需迁移为组件 props
import { ref, reactive, computed, nextTick, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import WangEditor from '@/components/wangEditor/index.vue';
import Setting from '@/setting';
import { diyGetInfo, diySave, getColorChange } from '@diy/api';
import editFrom from '@/components/from/from';
import { productGetTempKeysApi, uploadType as uploadTypeApi } from '@/api/product';
import {
  groupAllApi,
  groupDataListApi,
  groupSaveApi,
  openAdvSave,
  groupDataAddApi,
  groupDataHeaderApi,
  groupDataEditApi,
  groupDataSetApi,
  getAgreement as getAgreementApi,
  setAgreement,
  getOpenAdv,
} from '@/api/system';
import draggable from 'vuedraggable';
import uploadPictures from '@/components/uploadPictures';
import linkaddress from '@/components/linkaddress';
import { getCookies } from '@/libs/util';
import videoCloud from '@/utils/videoCloud';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';
import { useLayoutStore } from '@/store/module/layout';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'list' });

// 原 mapState('admin/layout') 模块不存在；按语义映射到现有 layout/media store
const layoutStore = useLayoutStore();
const mediaStore = useMediaStore();

const formValidateRef = ref(null);
const table = ref(null);
const linkaddres = ref(null);
const refid = ref(null);

const bgcolors = computed(() => {
  return {
    '--color-theme': bgCol.value,
  };
});
const isMobile = computed(() => mediaStore.isMobile);
const menuCollapse = computed(() => layoutStore.menuCollapse);
const labelWidth = computed(() => (isMobile.value ? undefined : '120px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const formValidate = ref({
  content: '',
});
const ruleValidate = {};
const myConfig = {
  autoHeightEnabled: false, // 编辑器不自动被内容撑高
  initialFrameHeight: 500, // 初始容器高度
  initialFrameWidth: '100%', // 初始容器宽度
  UEDITOR_HOME_URL: '/UEditor/',
  serverUrl: '',
};
const a = ref(0); //判断的隐私协议
const guide = ref(0);
const bgimg = ref(0);
const columns1 = ref([]);
const bgCol = ref('');
const name = ref('admin_login_slide');
const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const loading = ref(false);
const sginList = ref([]);
const progress = ref(0); // 进度条默认0
const swiperOption = {
  //显示分页
  pagination: {
    el: '.swiper-pagination',
  },
  //设置点击箭头
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
  //自动轮播
  autoplay: false,
  //开启循环模式
  loop: false,
};
const url = ref('');
const BaseURL = Setting.apiBaseURL.replace(/adminapi/, '');
const pageId = ref(0);
const theme3 = ref('light');
const tabList = ref([]);
const upload_type = ref(''); //视频上传类型 1 本地上传 2 3 4 OSS上传
const uploadData = ref({}); // 上传参数
const lastObj = ref({
  add_time: '',
  comment: '',
  gid: '',
  id: '',
  img: '',
  link: '',
  sort: '',
  status: 1,
});
const isChoice = ref('单选');
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
const groupAll = ref([]);
const activeIndex = ref(0);
const sortName = ref(null);
const activeIndexs = ref(0);
const cmsList = ref([]);
const loadingExist = ref(false);
const formItem = ref({
  time: '',
  type: 'pic',
  status: 1,
  value: [],
  video_link: '',
});
const fileUrl = Setting.apiBaseURL + '/file/upload';
const cardUrl = Setting.apiBaseURL + '/file/upload/1';
const header = ref({});
const type = ref(0);
const upload = reactive({
  videoIng: false, // 是否显示进度条；
});

function getEditorContent(data) {
  formValidate.value.content = data;
}
// 删除视频；
function delVideo() {
  formItem.value['video_link'] = '';
}
//获取视频上传类型
function getUploadType() {
  uploadTypeApi().then((res) => {
    upload_type.value = res.data.upload_type;
  });
}
// 上传成功
function handleSuccess(res, file, fileList) {
  if (res.status === 200) {
    formItem.value['video_link'] = res.data.src;
    ElMessage.success(res.msg);
  } else {
    ElMessage.error(res.msg);
  }
}
function zh_uploadFile() {
  if (video_link.value) {
    formValidate.value.video_link = video_link.value;
  } else {
    refid.value.click();
  }
}
function zh_uploadFile_change(evfile) {
  let suffix = evfile.target.files[0].name.substr(evfile.target.files[0].name.indexOf('.'));
  if (suffix.indexOf('.mp4') === -1) {
    return ElMessage.error('只能上传MP4文件');
  }
  let types = {
    key: evfile.target.files[0].name,
    contentType: evfile.target.files[0].type,
  };
  productGetTempKeysApi(types)
    .then((res) => {
      videoCloud
        .videoUpload({
          type: res.data.type,
          evfile: evfile,
          res: res,
          uploading(status, progress) {
            upload.videoIng = status;
          },
        })
        .then((res) => {
          formValidate.value.video_link = res.url;
          ElMessage.success('视频上传成功');
        })
        .catch((res) => {
          ElMessage.error(res);
        });
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 上传头部token
function getToken() {
  header.value['Authori-zation'] = 'Bearer ' + getCookies('token');
}
function beforeUpload() {
  uploadData.value = {};
  let promise = new Promise((resolve) => {
    nextTick(function () {
      resolve(true);
    });
  });
  return promise;
}
function linkUrl(e) {
  tabList.value.list[activeIndexs.value].link = e;
}
function color() {
  getColorChange('color_change').then((res) => {
    switch (res.data.status) {
      case 1:
        bgCol.value = '#3875EA';
        bgimg.value = 1;
        break;
      case 2:
        bgCol.value = '#00C050';
        bgimg.value = 2;
        break;
      case 3:
        bgCol.value = '#E93323';
        bgimg.value = 3;
        break;
      case 4:
        bgCol.value = '#FF448F';
        bgimg.value = 4;
        break;
      case 5:
        bgCol.value = '#FE5C2D';
        bgimg.value = 5;
        break;
    }
  });
}
// 添加表单
function groupAdd() {
  modalForm(groupDataAddApi({ gid: pageId.value, config_name: name.value }, 'setting/group_data/create')).then(() => {
    url.value = BaseURL + 'pages/users/user_sgin/index';
    info();
  });
}
function info() {
  groupDataListApi({ config_name: name.value }, 'setting/group_data')
    .then(async (res) => {
      tabList.value = res.data;
      if (name.value == 'admin_login_slide') {
        tabList.value.list.forEach((item, index, array) => {
          if (typeof item.slide != 'string' && item.slide != 'undefined') {
            item.slide = item.slide[0];
          }
        });
      } else if (name.value == 'sign_day_num') {
        cmsList.value = res.data.list;
      } else if (name.value == 'user_recharge_quota') {
        sginList.value = res.data;
      } else {
        tabList.value.list.forEach((item, index, array) => {
          if (typeof item.img != 'string' && item.img != 'undefined') {
            item.img = item.img[0];
          }
        });
      }
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function edits(row) {
  pageId.value = row.id || 0;
  name.value = row.config_name || '';
  if (row == 1) {
    a.value = 1;
    guide.value = 0;
    getAgreementLocal();
  } else if (row == 2) {
    a.value = 0;
    guide.value = 2;
    pageId.value = 1617;
    getOpenAdv().then((res) => {
      if (res.data) {
        formItem.value = res.data;
        tabList.value.list = res.data.value;
      } else {
        formItem.value = {
          time: '',
          type: 'pic',
          status: 1,
          value: [],
          video_link: '',
        };
        tabList.value.list = [];
      }
    });
  } else {
    info();
    guide.value = 0;
    a.value = 0;
    switch (row.config_name) {
      case 'sign_day_num':
        url.value = '';
        getListHeader();
        break;
      case 'combination_banner':
        url.value = BaseURL + 'pages/activity/goods_combination/index';
        break;
      case 'user_recharge_quota':
        url.value = '';
        getListHeader();
        break;
      case 'admin_login_slide':
        url.value = '';
        break;
      case 'integral_shop_banner':
        url.value = '';
        break;
    }
  }
}
function addBox() {
  if (tabList.value.list.length == 0) {
    tabList.value.list.push(lastObj.value);
    lastObj.value = {
      add_time: '',
      comment: '',
      gid: '',
      id: '',
      img: '',
      link: '',
      sort: '',
      status: 1,
    };
  } else {
    if (tabList.value.list.length == 5) {
      ElMessage.warning('最多添加五张呦');
    } else {
      let obj = JSON.parse(JSON.stringify(lastObj.value));
      tabList.value.list.push(obj);
    }
  }
}
// vuedraggable4 item-key（数据无唯一 id，用索引）
function dragItemKey(element) {
  return tabList.value.list.indexOf(element);
}
// 删除
function bindDelete(item, index) {
  tabList.value.list.splice(index, 1);
}
// 点击图文封面
function modalPicTap(title, index) {
  activeIndex.value = index;
  modalPic.value = true;
}
// 获取图片信息
function getPic(pc) {
  nextTick(() => {
    if (name.value == 'admin_login_slide') {
      tabList.value.list[activeIndex.value].slide = pc.att_dir;
    } else {
      tabList.value.list[activeIndex.value].img = pc.att_dir;
    }
    modalPic.value = false;
  });
}
function save() {
  if (a.value == 1) {
    onsubmit();
  } else if (guide.value == 2) {
    formItem.value.value = tabList.value.list;
    openAdvSave(formItem.value).then((res) => {
      ElMessage.success(res.msg);
    });
  } else {
    loadingExist.value = true;
    groupSaveApi({
      gid: pageId.value,
      config_name: name.value,
      data: tabList.value.list,
    })
      .then((res) => {
        loadingExist.value = false;
        ElMessage.success(res.msg);
      })
      .catch((err) => {
        loadingExist.value = false;
        ElMessage.error(err.msg);
      });
  }
}
function getLink(index) {
  activeIndexs.value = index;
  linkaddres.value.modals = true;
}
function getListHeader() {
  loading.value = true;
  groupDataHeaderApi({ config_name: name.value }, 'setting/sign_data/header')
    .then((res) => {
      let data = res.data;
      let header = data.header;
      let index = [];
      columns1.value = header;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 编辑
function edit(row) {
  modalForm(
    groupDataEditApi({ gid: pageId.value, config_name: name.value }, 'setting/group_data/' + row.id + '/edit'),
  ).then(() => {
    info();
    url.value = BaseURL + 'pages/users/user_sgin/index';
  });
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: 'setting/group_data/' + row.id,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      info();
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 修改是否显示
function onchangeIsShow(row) {
  groupDataSetApi('setting/group_data/set_status/' + row.id + '/' + row.status)
    .then(async (res) => {
      url.value = BaseURL + '/pages/users/user_sgin/index';
      ElMessage.success(res.msg);
      info();
    })
    .catch((res) => {
      url.value = BaseURL + '/pages/users/user_sgin/index';
      ElMessage.error(res.msg);
    });
}
function getGroupAll() {
  groupAllApi()
    .then(async (res) => {
      groupAll.value = res.data;
      sortName.value = res.data[0].config_name;
      pageId.value = res.data[0].id;
      name.value = res.data[0].config_name;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function getContent(val) {
  formValidate.value.content = val;
}
// 提交数据
function onsubmit() {
  formValidateRef.value.validate((valid) => {
    if (valid) {
      setAgreement(formValidate.value)
        .then(async (res) => {
          ElMessage.success(res.msg);
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    } else {
      return false;
    }
  });
}
//详情
function getAgreementLocal() {
  getAgreementApi()
    .then(async (res) => {
      let data = res.data;
      formValidate.value = {
        content: data.content,
      };
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}

let video_link = '';

color();
getUploadType();
getToken();

onMounted(() => {
  getGroupAll();
  info();
});
</script>
<style scoped lang="scss">
@import './visualization.scss';
</style>
