<template>
  <div>
    <el-form ref="formItem" :rules="ruleValidate" :model="formItem" label-width="100px" @submit.native.prevent>
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
      <el-form-item label="生日：">
        <el-date-picker
          clearable
          class="form-sty"
          type="date"
          v-model="formItem.birthday"
          placeholder="请选择生日"
          style="width: 80%"
          format="yyyy-MM-dd"
          value-format="yyyy-MM-dd"
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
      <el-form-item label="推广资格：">
        <el-radio-group v-model="formItem.spread_open" class="form-sty">
          <el-radio :label="1">启用</el-radio>
          <el-radio :label="0">禁用</el-radio>
        </el-radio-group>
        <div class="tip">禁用用户的推广资格后，在任何分销模式下该用户都无分销权限</div>
      </el-form-item>
      <el-form-item label="推广权限：">
        <el-radio-group v-model="formItem.is_promoter" class="form-sty">
          <el-radio :label="1">开启</el-radio>
          <el-radio :label="0">锁定</el-radio>
        </el-radio-group>
        <div class="tip">指定分销模式下，开启或关闭用户的推广权限</div>
      </el-form-item>
      <el-form-item label="用户状态：">
        <el-radio-group v-model="formItem.status" class="form-sty">
          <el-radio :label="1">开启</el-radio>
          <el-radio :label="0">锁定</el-radio>
        </el-radio-group>
      </el-form-item>
    </el-form>

    <el-dialog
      :visible.sync="labelShow"
      scrollable
      title="请选择用户标签"
      :modal="false"
      :show-close="true"
      width="540px"
    >
      <userLabel
        v-if="labelShow"
        :only_get="true"
        :uid="formItem.uid"
        @close="labelClose"
        @activeData="activeData"
      ></userLabel>
    </el-dialog>
  </div>
</template>

<script>
import userLabel from '@/components/userLabel';

import { userLabelAddApi } from '@/api/user';
export default {
  name: 'userEdit',
  components: { userLabel },
  props: {
    // modals: {
    //   default: false,
    //   type: Boolean,
    // },
    userData: {
      type: Object,
      default: () => {},
    },
  },
  watch: {},
  data() {
    return {
      modals: false,
      labelShow: false,
      formItem: {
        uid: 0,
        real_name: '',
        phone: '',
        birthday: '',
        card_id: '',
        addres: '',
        mark: '',
        pwd: '',
        true_pwd: '',
        level: '',
        group_id: '',
        label_id: [],
        spread_open: 0,
        is_promoter: 0,
        status: 1,
      },
      groupInfo: [],
      labelInfo: [],
      levelInfo: [],
      infoData: {
        groupInfo: [],
        labelInfo: [],
        levelInfo: [],
      },
      ruleValidate: {
        real_name: [{ required: true, message: ' ', trigger: 'blur' }],
        phone: [{ required: true, message: ' ', trigger: 'blur' }],
        pwd: [{ required: true, message: ' ', trigger: 'blur' }],
        true_pwd: [{ required: true, message: ' ', trigger: 'blur' }],
      },
      dataLabel: [],
    };
  },
  mounted() {
    this.$set(this.infoData, 'groupInfo', this.userData.groupInfo);
    this.$set(this.infoData, 'levelInfo', this.userData.levelInfo);
    this.$set(this.infoData, 'labelInfo', this.userData.labelInfo);
    let arr = Object.keys(this.formItem);
    if (this.userData.userInfo) {
      arr.map((i) => {
        this.formItem[i] = this.userData.userInfo[i];
      });
      if (!this.formItem.birthday) this.formItem.birthday = '';
      if (this.formItem.label_id.length) {
        this.dataLabel = this.formItem.label_id;
      }
    } else {
      this.reset();
    }

    // this.formItem = this.userData.userInfo;
  },
  methods: {
    addLabel() {
      this.$modalForm(userLabelAddApi(0)).then(() => {});
    },
    changeModal(status) {
      if (!status) {
        this.cancel();
        this.reset();
      }
    },
    openLabel(row) {
      this.labelShow = true;
      this.$refs.userLabel.userLabel(JSON.parse(JSON.stringify(this.infoData.labelInfo)));
    },
    cancel() {},
    activeData(dataLabel) {
      this.labelShow = false;
      this.dataLabel = dataLabel;
    },
    // 标签弹窗关闭
    labelClose() {
      this.labelShow = false;
    },
    closeLabel(label) {
      let index = this.dataLabel.indexOf(this.dataLabel.filter((d) => d.id == label.id)[0]);
      this.dataLabel.splice(index, 1);
    },
    reset() {
      this.formItem = {
        uid: '',
        real_name: '',
        phone: '',
        birthday: '',
        card_id: '',
        addres: '',
        mark: '',
        pwd: '',
        true_pwd: '',
        level: '',
        group_id: '',
        label_id: [],
        spread_open: 0,
        is_promoter: 0,
        status: 1,
      };
    },
  },
};
</script>

<style lang="stylus" scoped>
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
  color: #bbb;
  font-size: 12px;
  line-height: 12px;
}
</style>
