<template>
  <div class="user-info">
    <el-form :rules="ruleValidate" :model="formItem" label-width="100px" @submit.prevent>
      <div class="section">
        <div class="section-hd">基本信息</div>
        <div class="section-bd">
          <div class="item">
            <el-form-item label="用户ID：">
              <el-input class="form-sty" disabled v-model="formItem.uid" placeholder="请输入编号"></el-input>
            </el-form-item>
          </div>
          <div class="item">
            <el-form-item label="真实姓名：" prop="real_name">
              <el-input class="form-sty" v-model.trim="formItem.real_name" placeholder="请输入真实姓名"></el-input>
            </el-form-item>
          </div>
          <div class="item">
            <el-form-item label="手机号码：" prop="phone">
              <el-input class="form-sty" v-model="formItem.phone" placeholder="请输入手机号码"></el-input>
            </el-form-item>
          </div>
          <div class="item">
            <el-form-item label="邮箱：">
              <el-input class="form-sty" v-model.trim="formItem.email" placeholder="请输入邮箱"></el-input>
            </el-form-item>
          </div>
          <div class="item">
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
          </div>
          <div class="item">
            <el-form-item label="身份证号：">
              <el-input class="form-sty" v-model.trim="formItem.card_id" placeholder="请输入身份证号"></el-input>
            </el-form-item>
          </div>
          <div class="item">
            <el-form-item label="用户地址：">
              <el-input class="form-sty" v-model="formItem.addres" placeholder="请输入用户地址"></el-input>
            </el-form-item>
          </div>
        </div>
      </div>
      <div class="section">
        <div class="section-hd">密码</div>
        <div class="section-bd">
          <div class="item">
            <el-form-item label="登录密码：" prop="pwd">
              <el-input
                class="form-sty"
                type="password"
                v-model="formItem.pwd"
                placeholder="请输入登录密码（修改用户可不填写，不填写不修改原密码）"
              ></el-input>
            </el-form-item>
          </div>
          <div class="item">
            <el-form-item label="确认密码：" prop="true_pwd">
              <el-input
                class="form-sty"
                type="password"
                v-model="formItem.true_pwd"
                placeholder="请输入确认密码（修改用户可不填写，不填写不修改原密码）"
              ></el-input>
            </el-form-item>
          </div>
        </div>
      </div>
      <div class="section">
        <div class="section-hd">用户概况</div>
        <div class="section-bd">
          <div class="item">
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
          </div>
          <div class="item">
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
          </div>
          <div class="item lang">
            <el-form-item label="用户标签：">
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
          </div>
          <div class="item lang">
            <el-form-item label="用户状态：">
              <el-radio-group v-model="formItem.status" class="form-sty">
                <el-radio :label="1" :value="1">开启</el-radio>
                <el-radio :label="0" :value="0">锁定</el-radio>
              </el-radio-group>
            </el-form-item>
          </div>
        </div>
      </div>
      <div class="section">
        <div class="section-hd">用户备注</div>
        <div class="section-bd">
          <div class="item">
            <el-form-item label="用户备注：">
              <el-input
                class="form-sty"
                type="textarea"
                :rows="5"
                v-model="formItem.mark"
                placeholder="请输入用户备注"
              ></el-input>
            </el-form-item>
          </div>
        </div>
      </div>
    </el-form>
    <el-dialog v-model="labelShow" append-to-body title="请选择用户标签" :show-close="true" width="540px">
      <userLabel
        v-if="labelShow"
        ref="userLabelRef"
        :only_get="true"
        :uid="formItem.uid"
        @close="labelClose"
        @activeData="activeData"
      >
      </userLabel>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import userLabel from '@/components/userLabel';

import { userLabelAddApi, getUserInfo, editUser, setUser } from '@/api/user';
import dayjs from 'dayjs';
import modalForm from '@/utils/modalForm';

defineOptions({ name: 'userInfo' });

const props = defineProps({
  userId: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['success']);

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
const userData = ref({});
const userLabelRef = ref(null);

// created
getUserFrom(props.userId);

function setUserFn() {
  let data = formItem.value;
  let ids = [];
  dataLabel.value.map((i) => {
    ids.push(i.id);
  });
  data.label_id = ids;
  // if (!data.real_name) return ElMessage.warning("请输入真实姓名");
  // if (!data.phone) return ElMessage.warning("请输入手机号");
  // if (!data.pwd) return ElMessage.warning("请输入密码");
  // if (!data.true_pwd) return ElMessage.warning("请输入确认密码");
  if (data.uid) {
    editUser(data)
      .then((res) => {
        ElMessage.success(res.msg);
        emit('success');
      })
      .catch((err) => {
        ElMessage.error(err.msg);
      });
  } else {
    setUser(data)
      .then((res) => {
        emit('success');
        ElMessage.success(res.msg);
      })
      .catch((err) => {
        ElMessage.error('err.msg');
      });
  }
}

function addLabel() {
  modalForm(userLabelAddApi(0)).then(() => {});
}

function openLabel(row) {
  labelShow.value = true;
  userLabelRef.value?.userLabel?.(JSON.parse(JSON.stringify(infoData.value.labelInfo)));
}

function closeLabel(label) {
  let index = dataLabel.value.indexOf(dataLabel.value.filter((d) => d.id == label.id)[0]);
  dataLabel.value.splice(index, 1);
}

function getUserFrom(id) {
  getUserInfo(id)
    .then(async (res) => {
      userData.value = res.data;
      infoData.value['groupInfo'] = userData.value.groupInfo;
      infoData.value['levelInfo'] = userData.value.levelInfo;
      infoData.value['labelInfo'] = userData.value.labelInfo;
      let arr = Object.keys(formItem.value);
      if (userData.value.userInfo) {
        arr.map((i) => {
          formItem.value[i] = userData.value.userInfo[i];
        });
        if (!formItem.value.birthday) formItem.value.birthday = '';
        if (formItem.value.label_id.length) {
          dataLabel.value = formItem.value.label_id;
        }
      } else {
        reset();
      }
    })
    .catch((res) => {
      ElMessage.error('res.msg');
    });
}

// 标签弹窗关闭
function labelClose() {
  labelShow.value = false;
}

function activeData(labelList) {
  labelShow.value = false;
  dataLabel.value = labelList;
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

// 父组件（用户详情抽屉）通过 editForm.setUser() 提交
defineExpose({ setUser: setUserFn });
</script>

<style lang="scss" scoped>
.labelInput {
  border: 1px solid #dcdee2;
  width: 300px;
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

.width-add {
  width: 40px;
}

.mr30 {
  margin-right: 30px;
}

.user-info {
  .section {
    padding: 25px 0 0;
    border-bottom: 1px dashed #eeeeee;

    &-hd {
      margin-bottom: 18px;
      padding-left: 10px;
      border-left: 3px solid var(--prev-color-primary);
      font-weight: 500;
      font-size: 14px;
      line-height: 16px;
      color: var(--prev-color-text-regular);
    }

    &-bd {
      display: flex;
      flex-wrap: wrap;
    }

    .item {
      width: 50%;
      display: flex;
      font-size: 13px;
      color: #666;

      .form-sty {
        width: 300px;
      }

      .ivu-form-item {
        margin: 3px 0;
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
    }

    .item.lang {
      width: 100%;
    }

    .value {
      flex: 1;
    }

    .avatar {
      width: 60px;
      height: 60px;
      overflow: hidden;

      img {
        width: 100%;
        height: 100%;
      }
    }
  }
}
</style>
