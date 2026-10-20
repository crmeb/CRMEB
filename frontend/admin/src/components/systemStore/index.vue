<template>
  <div>
    <el-dialog
      v-model="isTemplate"
      :title="formItem.id ? '编辑门店' : '添加门店'"
      width="720px"
      @closed="cancel"
      append-to-body
    >
      <div class="article-manager" v-loading="spinShow">
        <el-form
          ref="formItemRef"
          :model="formItem"
          label-width="110px"
          label-position="right"
          :rules="ruleValidate"
          @submit.prevent
        >
          <el-row :gutter="24">
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="门店名称：" prop="name" label-for="name">
                  <el-input v-model="formItem.name" placeholder="请输入门店名称" />
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="门店简介：" label-for="introduction">
                  <el-input v-model="formItem.introduction" placeholder="请输入门店简介" />
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="门店电话：" label-for="phone" prop="phone">
                  <el-input v-model="formItem.phone" placeholder="请输入门店电话" />
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="门店地址：" label-for="address" prop="address">
                  <el-cascader :options="addresData" v-model="formItem.address" style="width: 100%"></el-cascader>
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="详细地址：" label-for="detailed_address" prop="detailed_address">
                  <el-input v-model="formItem.detailed_address" placeholder="请输入详细地址" />
                </el-form-item>
              </el-col>
            </el-col>
            <!--<el-col :span="24">-->
            <!--<el-col v-bind="grid">-->
            <!--<el-form-item label="核销时效：" label-for="valid_time">-->
            <!--<DatePicker @change="onchangeDate" :value="formItem.valid_time" v-model="formItem.valid_time" format="YYYY/MM/DD" type="daterange" split-panels placeholder="请选择核销时效" ></DatePicker>-->
            <!--</el-form-item>-->
            <!--</el-col>-->
            <!--</el-col>-->
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="门店营业：" label-for="day_time" prop="day_time">
                  <el-time-picker
                    is-range
                    @change="onchangeTime"
                    v-model="formItem.day_time"
                    format="HH:mm:ss"
                    value-format="HH:mm:ss"
                    range-separator="-"
                    start-placeholder="开始时间"
                    end-placeholder="结束时间"
                    placeholder="选择时间范围"
                    style="width: 100%"
                  ></el-time-picker>
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="门店logo：" prop="image">
                  <div class="picBox" v-db-click @click="modalPicTap('单选', 'logo')">
                    <div class="pictrue" v-if="formItem.image">
                      <img v-lazy="formItem.image" />
                    </div>
                    <div class="upLoad acea-row row-center-wrapper" v-else>
                      <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                    </div>
                  </div>
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="门店大图：" prop="oblong_image">
                  <div class="picBox" v-db-click @click="modalPicTap('单选', 'oblong')">
                    <div class="pictrue" v-if="formItem.oblong_image">
                      <img v-lazy="formItem.oblong_image" />
                    </div>
                    <div class="upLoad acea-row row-center-wrapper" v-else>
                      <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                    </div>
                  </div>
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="经纬度：" label-for="status2" prop="latlng">
                  <el-tooltip>
                    <el-input v-model="formItem.latlng" style="width: 100%" placeholder="请查找位置">
                      <template #append
                        ><el-button type="primary" v-db-click @click="onSearch">查找位置</el-button></template
                      >
                    </el-input>
                    <template #content><div>请点击查找位置选择位置</div></template>
                  </el-tooltip>
                </el-form-item>
              </el-col>
            </el-col>
          </el-row>
          <!-- <el-row>
              <div class="btn">
                <el-button type="primary" long v-db-click @click="handleSubmit('formItem')">{{
                  formItem.id ? '修改' : '提交'
                }}</el-button>
              </div>
            </el-row> -->
        </el-form>

        <el-dialog v-model="modalPic" width="1024px" :title="modalTitle" :close-on-click-modal="false" append-to-body>
          <uploadPictures
            :isChoice="isChoice"
            @getPic="getPic"
            :gridBtn="gridBtn"
            :gridPic="gridPic"
            v-if="modalPic"
          ></uploadPictures>
        </el-dialog>
      </div>
      <template #footer
        ><span class="dialog-footer">
          <el-button type="primary" long v-db-click @click="handleSubmit('formItem')">{{
            formItem.id ? '修改' : '提交'
          }}</el-button>
        </span></template
      >
    </el-dialog>
    <el-dialog
      v-model="modalMap"
      title="请选择地址"
      append-to-body
      :close-on-click-modal="false"
      width="720px"
      class="mapBox"
    >
      <iframe id="mapPage" width="100%" height="600px" frameborder="0" v-bind:src="keyUrl"></iframe>
    </el-dialog>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { storeApi, keyApi, storeAddApi, storeGetInfoApi } from '@/api/setting';
import uploadPictures from '@/components/uploadPictures';
import { cityList } from '@/api/app';

defineOptions({ name: 'systemStore' });

const emit = defineEmits(['success']);

const isTemplate = ref(false);
const spinShow = ref(false);
const modalMap = ref(false);
const addresData = ref([]);
const modalTitle = ref('');
const formItem = reactive({
  name: '',
  introduction: '',
  phone: '',
  address: [],
  address2: [],
  detailed_address: '',
  valid_time: [],
  day_time: ['', ''],
  latlng: '',
  id: 0,
});
const formItemRef = ref(null);
const ruleValidate = {
  name: [{ required: true, message: '请输入门店名称', trigger: 'blur' }],
  mail: [
    {
      required: true,
      message: 'Mailbox cannot be empty',
      trigger: 'blur',
    },
    { type: 'email', message: 'Incorrect email format', trigger: 'blur' },
  ],
  address: [
    {
      required: true,
      message: '请选择门店地址',
      type: 'array',
      trigger: 'change',
    },
  ],
  valid_time: [
    {
      required: true,
      type: 'array',
      message: '请选择核销时效',
      trigger: 'change',
      fields: {
        0: { type: 'date', required: true, message: '请选择年度范围' },
        1: { type: 'date', required: true, message: '请选择年度范围' },
      },
    },
  ],
  day_time: [
    {
      required: true,
      type: 'array',
      message: '请选择门店营业时间',
      trigger: 'change',
    },
  ],
  phone: [{ required: true, validator: validatePhone, trigger: 'blur' }],
  detailed_address: [{ required: true, message: '请输入详细地址', trigger: 'blur' }],
  image: [{ required: true, validator: validateUpload, trigger: 'change' }],
  oblong_image: [{ required: true, validator: oblongImageUpload, trigger: 'change' }],
  latlng: [{ required: true, message: '请选择经纬度', trigger: 'blur' }],
};
const keyUrl = ref('');
const grid = {
  xl: 20,
  lg: 24,
  md: 20,
  sm: 24,
  xs: 24,
};
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
const modalPic = ref(false);
const isChoice = ref('单选');
let picTit = '';

function validatePhone(rule, value, callback) {
  if (!value) {
    return callback(new Error('请填写电话号码'));
  } else {
    callback();
  }
}
function validateUpload(rule, value, callback) {
  if (!formItem.image) {
    callback(new Error('请上传门店logo'));
  } else {
    callback();
  }
}
function oblongImageUpload(rule, value, callback) {
  if (!formItem.oblong_image) {
    callback(new Error('请上传门店大图'));
  } else {
    callback();
  }
}

// created
getCityList();

onMounted(function () {
  window.addEventListener(
    'message',
    function (event) {
      // 接收位置信息，用户选择确认位置点后选点组件会触发该事件，回传用户的位置信息
      var loc = event.data;
      if (loc && loc.module === 'locationPicker') {
        // 防止其他应用也会向该页面post信息，需判断module是否为'locationPicker'
        window.parent.selectAdderss(loc);
      }
    },
    false,
  );
  window.selectAdderss = selectAdderss;
});

function getCityList() {
  cityList().then((res) => {
    res.data.map((item) => {
      item.value = item.label;
      if (item.children && item.children.length) {
        item.children.map((j) => {
          j.value = j.label;
          if (j.children && j.children.length) {
            j.children.map((o) => {
              o.value = o.label;
            });
          }
        });
      }
    });
    addresData.value = res.data;
  });
}
function cancel() {
  formItemRef.value.resetFields();
  clearFrom();
}
function clearFrom() {
  formItem.introduction = '';
  formItem.day_time = ['', ''];
  formItem.oblong_image = '';
  formItem.id = 0;
}
// 选择经纬度
function selectAdderss(data) {
  formItem.latlng = data.latlng.lat + ',' + data.latlng.lng;
  modalMap.value = false;
}
// key值
function getKey() {}
// 详情
function getInfo(id) {
  formItem.id = id;
  spinShow.value = true;
  storeGetInfoApi(id)
    .then((res) => {
      let info = res.data.info || null;
      Object.assign(formItem, info || {});
      formItem.address = info.address2;
      formItem.day_time = info.day_time.split('-');
      spinShow.value = false;
    })
    .catch(function (res) {
      spinShow.value = false;
      ElMessage.error(res.msg);
    });
}
// 选择图片
function modalPicTap(tit, picTitParam) {
  modalTitle.value = picTitParam == 'oblong' ? '门店大图' : '门店LOGO';
  modalPic.value = true;
  picTit = picTitParam;
}
// 选中图片
function getPic(pc) {
  switch (picTit) {
    case 'logo':
      formItem.image = pc.att_dir;
      break;
    case 'oblong':
      formItem.oblong_image = pc.att_dir;
      break;
  }
  modalPic.value = false;
}
// 核销时效
function onchangeDate(e) {
  formItem.valid_time = e;
}
// 营业时间
function onchangeTime(e) {
  formItem.day_time = e;
}
function onSearch() {
  if (!keyUrl.value) {
    keyApi()
      .then(async (res) => {
        let keys = res.data.key;
        keyUrl.value = `https://apis.map.qq.com/tools/locpicker?type=1&key=${keys}&referer=myapp`;
        modalMap.value = true;
      })
      .catch((res) => {
        ElMessage.error(res.msg);
      });
  } else {
    modalMap.value = true;
  }
}
// 提交
function handleSubmit(name) {
  formItemRef.value.validate((valid) => {
    if (valid) {
      storeAddApi(formItem)
        .then(async (res) => {
          ElMessage.success(res.msg);
          isTemplate.value = false;
          emit('success');
          formItemRef.value.resetFields();
          clearFrom();
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    } else {
      return false;
    }
  });
}

// 页面通过 $refs.template.getInfo(id) / isTemplate 驱动
defineExpose({ getInfo, isTemplate });
</script>

<style lang="scss" scoped>
.picBox {
  display: inline-block;
  cursor: pointer;
  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
  }
  .pictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 10px;

    img {
      width: 100%;
      height: 100%;
    }
  }
  .iconfont {
    color: #898989;
  }
}
.mapBox :deep(.ivu-modal-body) {
  height: 640px !important;
}
.btn {
  margin: 0 auto;
  width: 40%;
}
</style>
