<template>
  <div class="pb-50">
    <el-form :rules="ruleValidate" :model="formItem" label-width="100px" @submit.prevent>
      <el-form-item label="用户ID：" v-if="formItem.uid">
        <el-input
          class="form-sty"
          disabled
          v-model="formItem.uid"
          placeholder="请输入编号"
          style="width: 80%"
        ></el-input>
      </el-form-item>
      <el-form-item label="真实姓名：" prop="real_name">
        <el-input
          class="form-sty"
          v-model.trim="formItem.real_name"
          placeholder="请输入真实姓名"
          style="width: 80%"
        ></el-input>
      </el-form-item>
      <el-form-item label="手机号码：" prop="phone">
        <el-input class="form-sty" v-model="formItem.phone" placeholder="请输入手机号码" style="width: 80%"></el-input>
      </el-form-item>
      <el-form-item label="邮箱：">
        <el-input class="form-sty" v-model.trim="formItem.email" placeholder="请输入邮箱" style="width: 80%"></el-input>
      </el-form-item>
      <el-form-item label="生日：">
        <el-date-picker
          clearable
          class="form-sty"
          type="date"
          v-model="formItem.birthday"
          placeholder="请选择生日"
          format="YYYY-MM-DD"
          value-format="YYYY-MM-DD"
        ></el-date-picker>
      </el-form-item>
      <el-form-item label="身份证号：">
        <el-input
          class="form-sty"
          v-model.trim="formItem.card_id"
          placeholder="请输入身份证号"
          style="width: 80%"
        ></el-input>
      </el-form-item>
      <el-form-item label="用户地址：">
        <el-input class="form-sty" v-model="formItem.addres" placeholder="请输入用户地址" style="width: 80%"></el-input>
      </el-form-item>
      <el-form-item label="用户备注：">
        <el-input class="form-sty" v-model="formItem.mark" placeholder="请输入用户备注" style="width: 80%"></el-input>
      </el-form-item>
      <el-form-item label="登录密码：" prop="pwd">
        <el-input
          class="form-sty"
          type="password"
          v-model="formItem.pwd"
          placeholder="请输入登录密码（修改用户可不填写，不填写不修改原密码）"
          style="width: 80%"
        ></el-input>
      </el-form-item>
      <el-form-item label="确认密码：" prop="true_pwd">
        <el-input
          class="form-sty"
          type="password"
          v-model="formItem.true_pwd"
          placeholder="请输入确认密码（修改用户可不填写，不填写不修改原密码）"
          style="width: 80%"
        ></el-input>
      </el-form-item>

      <el-form-item label="用户等级：">
        <el-select v-model="formItem.level" class="form-sty" clearable>
          <el-option
            v-for="(item, index) in infoData.levelInfo"
            :key="index"
            :value="item.id"
            :label="item.name"
          ></el-option>
        </el-select>
      </el-form-item>
      <el-form-item label="用户分组：">
        <el-select v-model="formItem.group_id" class="form-sty" clearable>
          <el-option
            v-for="(item, index) in infoData.groupInfo"
            :key="index"
            :value="item.id"
            :label="item.group_name"
          ></el-option>
        </el-select>
      </el-form-item>
      <el-form-item label="用户标签：">
        <!-- <el-select v-model="formItem.label_id">
          <el-option
            v-for="(item, index) in infoData.labelInfo"
            :key="index"
            :value="item.value"
            >{{ item.label }}</el-option
          >
        </el-select> -->
        <div style="display: flex">
          <div class="labelInput acea-row row-between-wrapper" v-db-click @click="openLabel">
            <div style="width: 90%">
              <div v-if="dataLabel.length">
                <el-tag
                  closable
                  v-for="(item, index) in dataLabel"
                  :key="index"
                  @close="closeLabel(item)"
                  class="mr10"
                  >{{ item.label_name }}</el-tag
                >
              </div>
              <span class="span" v-else>选择用户关联标签</span>
            </div>
            <div class="ivu-icon ivu-icon-ios-arrow-down"></div>
          </div>
          <span class="addfont" v-db-click @click="addLabel">新增标签</span>
        </div>
      </el-form-item>
      <el-form-item label="用户状态：">
        <el-radio-group v-model="formItem.status" class="form-sty">
          <el-radio :label="1" :value="1">开启</el-radio>
          <el-radio :label="0" :value="0">锁定</el-radio>
        </el-radio-group>
      </el-form-item>
    </el-form>

    <el-dialog v-model="labelShow" scrollable title="请选择用户标签" :show-close="true" width="540px">
      <userLabel
        v-if="labelShow"
        ref="userLabelRef"
        :only_get="true"
        :uid="formItem.uid"
        @close="labelClose"
        @activeData="activeData"
      ></userLabel>
    </el-dialog>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import userLabel from '@/components/userLabel';

import { userLabelAddApi } from '@/api/user';
import modalForm from '@/utils/modalForm';

defineOptions({ name: 'userEdit' });

const props = defineProps({
  // modals: {
  //   default: false,
  //   type: Boolean,
  // },
  userData: {
    type: Object,
    default: () => {},
  },
});

const modals = ref(false);
const labelShow = ref(false);
const formItem = ref({
  uid: 0,
  real_name: '',
  phone: '',
  email: '',
  birthday: '',
  card_id: '',
  addres: '',
  mark: '',
  pwd: '',
  true_pwd: '',
  level: '',
  group_id: '',
  label_id: [],
  status: 1,
});
const groupInfo = ref([]);
const labelInfo = ref([]);
const levelInfo = ref([]);
const infoData = ref({
  groupInfo: [],
  labelInfo: [],
  levelInfo: [],
});
const ruleValidate = {
  real_name: [{ required: true, message: ' ', trigger: 'blur' }],
  phone: [{ required: true, message: ' ', trigger: 'blur' }],
  pwd: [{ required: true, message: ' ', trigger: 'blur' }],
  true_pwd: [{ required: true, message: ' ', trigger: 'blur' }],
};
const dataLabel = ref([]);
const userLabelRef = ref(null);

onMounted(() => {
  infoData.value['groupInfo'] = props.userData.groupInfo;
  infoData.value['levelInfo'] = props.userData.levelInfo;
  infoData.value['labelInfo'] = props.userData.labelInfo;
  let arr = Object.keys(formItem.value);
  if (props.userData.userInfo) {
    arr.map((i) => {
      formItem.value[i] = props.userData.userInfo[i];
    });
    if (!formItem.value.birthday) formItem.value.birthday = '';
    if (formItem.value.label_id.length) {
      dataLabel.value = formItem.value.label_id;
    }
  } else {
    reset();
  }

  // formItem.value = props.userData.userInfo;
});

function addLabel() {
  modalForm(userLabelAddApi(0)).then(() => {});
}

function changeModal(status) {
  if (!status) {
    cancel();
    reset();
  }
}

function openLabel(row) {
  labelShow.value = true;
  userLabelRef.value?.userLabel?.(JSON.parse(JSON.stringify(infoData.value.labelInfo)));
}

function cancel() {}

function activeData(labelList) {
  labelShow.value = false;
  dataLabel.value = labelList;
}

// 标签弹窗关闭
function labelClose() {
  labelShow.value = false;
}

function closeLabel(label) {
  let index = dataLabel.value.indexOf(dataLabel.value.filter((d) => d.id == label.id)[0]);
  dataLabel.value.splice(index, 1);
}

function reset() {
  formItem.value = {
    uid: '',
    real_name: '',
    phone: '',
    email: '',
    birthday: '',
    card_id: '',
    addres: '',
    mark: '',
    pwd: '',
    true_pwd: '',
    level: '',
    group_id: '',
    label_id: [],
    status: 1,
  };
}

// 页面通过 userEditRef.formItem / userEditRef.dataLabel 驱动
defineExpose({ formItem, dataLabel });
</script>

<style lang="scss" scoped>
.labelInput {
  border: 1px solid #dcdee2;
  width: 400px;
  padding: 0 15px;
  border-radius: 5px;
  min-height: 30px;
  cursor: pointer;
  font-size: 12px;
  .span {
    color: #c5c8ce;
  }
  .iconxiayi {
    font-size: 12px;
  }
}
.ivu-form-item {
  margin-bottom: 10px;
}
.form-sty {
  width: 400px !important;
}
.addfont {
  display: inline-block;
  font-size: 12px;
  font-weight: 400;
  color: var(--prev-color-primary);
  margin-left: 14px;
  cursor: pointer;
  margin-left: 10px;
}
.ivu-icon-ios-arrow-down {
  font-size: 14px;
}
.tip {
  line-height: 12px;
}
.pb-50 {
  padding-bottom: 50px;
}
</style>
