<template>
  <div>
    <Form ref="formItem" :model="formItem" :label-width="100" @submit.native.prevent>
      <FormItem label="编号：" v-if="formItem.uid">
        <Input class="form-sty" disabled v-model="formItem.uid" placeholder="请输入编号" style="width: 80%"></Input>
      </FormItem>
      <FormItem label="真实姓名：">
        <Input
          class="form-sty"
          v-model.trim="formItem.real_name"
          placeholder="请输入真实姓名"
          style="width: 80%"
        ></Input>
      </FormItem>
      <FormItem label="手机号码：">
        <Input class="form-sty" v-model="formItem.phone" placeholder="请输入手机号码" style="width: 80%"></Input>
      </FormItem>
      <FormItem label="生日：">
        <DatePicker
          class="form-sty"
          type="date"
          :value="formItem.birthday"
          placeholder="请选择生日"
          style="width: 80%"
          format="yyyy-MM-dd"
          @on-change="formItem.birthday = $event"
        ></DatePicker>
      </FormItem>
      <FormItem label="身份证号：">
        <Input class="form-sty" v-model.trim="formItem.card_id" placeholder="请输入身份证号" style="width: 80%"></Input>
      </FormItem>
      <FormItem label="用户地址：">
        <Input class="form-sty" v-model="formItem.addres" placeholder="请输入用户地址" style="width: 80%"></Input>
      </FormItem>
      <FormItem label="用户备注：">
        <Input class="form-sty" v-model="formItem.mark" placeholder="请输入用户备注" style="width: 80%"></Input>
      </FormItem>
      <FormItem label="登录密码：">
        <Input
          class="form-sty"
          type="password"
          v-model="formItem.pwd"
          placeholder="请输入登录密码"
          style="width: 80%"
        ></Input>
      </FormItem>
      <FormItem label="确认密码：">
        <Input
          class="form-sty"
          type="password"
          v-model="formItem.true_pwd"
          placeholder="请输入确认密码"
          style="width: 80%"
        ></Input>
      </FormItem>

      <FormItem label="用户等级：">
        <Select v-model="formItem.level" class="form-sty" clearable>
          <Option v-for="(item, index) in infoData.levelInfo" :key="index" :value="item.id">{{ item.name }}</Option>
        </Select>
      </FormItem>
      <FormItem label="用户分组：">
        <Select v-model="formItem.group_id"  class="form-sty" clearable>
          <Option v-for="(item, index) in infoData.groupInfo" :key="index" :value="item.id">{{
            item.group_name
          }}</Option>
        </Select>
      </FormItem>
      <FormItem label="用户标签：">
        <!-- <Select v-model="formItem.label_id">
          <Option
            v-for="(item, index) in infoData.labelInfo"
            :key="index"
            :value="item.value"
            >{{ item.label }}</Option
          >
        </Select> -->
        <div style="display: flex">
          <div class="labelInput acea-row row-between-wrapper" @click="openLabel">
            <div style="width: 90%">
              <div v-if="dataLabel.length">
                <Tag closable v-for="(item, index) in dataLabel" :key="index" @on-close="closeLabel(item)">{{
                  item.label_name
                }}</Tag>
              </div>
              <span class="span" v-else>选择用户关联标签</span>
            </div>
            <div class="ivu-icon ivu-icon-ios-arrow-down"></div>
          </div>
          <span class="addfont" @click="addLabel">新增标签</span>
        </div>
      </FormItem>
      <FormItem label="推广资格：">
        <RadioGroup v-model="formItem.spread_open" class="form-sty">
          <Radio :label="1">启用</Radio>
          <Radio :label="0">禁用</Radio>
        </RadioGroup>
        <div class="tip">禁用用户的推广资格后，在任何分销模式下该用户都无分销权限</div>
      </FormItem>
      <FormItem label="推广权限：">
        <RadioGroup v-model="formItem.is_promoter" class="form-sty">
          <Radio :label="1">开启</Radio>
          <Radio :label="0">锁定</Radio>
          <div class="tip">指定分销模式下，开启或关闭用户的推广权限</div>
        </RadioGroup>
      </FormItem>
      <FormItem label="用户状态：">
        <RadioGroup v-model="formItem.status" class="form-sty">
          <Radio :label="1">开启</Radio>
          <Radio :label="0">锁定</Radio>
        </RadioGroup>
      </FormItem>
    </Form>

    <Modal v-model="labelShow" scrollable title="请选择用户标签" :closable="false" width="500" :footer-hide="true">
      <userLabel
        v-if="labelShow"
        :only_get="true"
        :uid="formItem.uid"
        @close="labelClose"
        @activeData="activeData"
      ></userLabel>
    </Modal>
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
      orderStatus: 0,
      total_num: 0,
      splitSwitch: true,
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
      express: [],
      expressTemp: [],
      deliveryList: [],
      temp: {},
      export_open: true,
      manyFormValidate: [],
      groupInfo: [],
      labelInfo: [],
      levelInfo: [],
      infoData: {
        groupInfo: [],
        labelInfo: [],
        levelInfo: [],
      },
      dataLabel: [],
    };
  },
  mounted() {
    this.$set(this.infoData, 'groupInfo', this.userData.groupInfo);
    this.$set(this.infoData, 'levelInfo', this.userData.levelInfo);
    this.$set(this.infoData, 'labelInfo', this.userData.labelInfo);
    let arr = Object.keys(this.formItem);
    if (this.userData.userInfo.uid) {
      arr.map((i) => {
        this.formItem[i] = this.userData.userInfo[i];
      });
      if (!this.formItem.birthday) this.formItem.birthday = '';
      if (this.formItem.label_id.length) {
        this.dataLabel = this.formItem.label_id;
      }
    }

    // this.formItem = this.userData.userInfo;
  },
  methods: {
    addLabel() {
      this.$modalForm(userLabelAddApi(0)).then(() => {});
    },
    closeLabel(label) {
      let index = this.dataLabel.indexOf(this.dataLabel.filter((d) => d.id == label.id)[0]);
      this.dataLabel.splice(index, 1);
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
      };
    },
  },
};
</script>

<style lang="stylus" scoped>
.labelInput {
  border: 1px solid #dcdee2;
  width: 400px;
  padding: 0 8px;
  border-radius: 5px;
  min-height: 30px;
  cursor: pointer;

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
  font-size: 13px;
  font-weight: 400;
  color: #1890FF;
  margin-left: 14px;
  cursor: pointer;
  margin-left: 10px;
}

.ivu-icon-ios-arrow-down {
  font-size: 14px;
}

.tip {
  color: #bbb;
}
</style>
