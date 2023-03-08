<template>
  <div class="user-info">
    <Form ref="formItem" :rules="ruleValidate" :model="formItem" :label-width="100" @submit.native.prevent>
      <div class="section">
        <div class="section-hd">基本信息</div>
        <div class="section-bd">
          <div class="item">
            <FormItem label="用户ID：">
              <Input class="form-sty" disabled v-model="formItem.uid" placeholder="请输入编号"></Input>
            </FormItem>
          </div>
          <div class="item">
            <FormItem label="真实姓名：" prop="real_name">
              <Input class="form-sty" v-model.trim="formItem.real_name" placeholder="请输入真实姓名"></Input>
            </FormItem>
          </div>
          <div class="item">
            <FormItem label="手机号码：" prop="phone">
              <Input class="form-sty" v-model="formItem.phone" placeholder="请输入手机号码"></Input>
            </FormItem>
          </div>
          <div class="item">
            <FormItem label="生日：">
              <DatePicker
                class="form-sty"
                type="date"
                :value="formItem.birthday"
                placeholder="请选择生日"
                format="yyyy-MM-dd"
                @on-change="formItem.birthday = $event"
              ></DatePicker>
            </FormItem>
          </div>
          <div class="item">
            <FormItem label="身份证号：">
              <Input class="form-sty" v-model.trim="formItem.card_id" placeholder="请输入身份证号"></Input>
            </FormItem>
          </div>
          <div class="item">
            <FormItem label="用户地址：">
              <Input class="form-sty" v-model="formItem.addres" placeholder="请输入用户地址"></Input>
            </FormItem>
          </div>
        </div>
      </div>
      <div class="section">
        <div class="section-hd">密码</div>
        <div class="section-bd">
          <div class="item">
            <FormItem label="登录密码：" prop="pwd">
              <Input
                class="form-sty"
                type="password"
                v-model="formItem.pwd"
                placeholder="请输入登录密码（修改用户可不填写，不填写不修改原密码）"
              ></Input>
            </FormItem>
          </div>
          <div class="item">
            <FormItem label="确认密码：" prop="true_pwd">
              <Input
                class="form-sty"
                type="password"
                v-model="formItem.true_pwd"
                placeholder="请输入确认密码（修改用户可不填写，不填写不修改原密码）"
              ></Input>
            </FormItem>
          </div>
        </div>
      </div>
      <div class="section">
        <div class="section-hd">用户概况</div>
        <div class="section-bd">
          <div class="item">
            <FormItem label="用户等级：">
              <Select v-model="formItem.level" class="form-sty" clearable>
                <Option v-for="(item, index) in infoData.levelInfo" :key="index" :value="item.id">{{
                  item.name
                }}</Option>
              </Select>
            </FormItem>
          </div>
          <div class="item">
            <FormItem label="用户分组：">
              <Select v-model="formItem.group_id" class="form-sty" clearable>
                <Option v-for="(item, index) in infoData.groupInfo" :key="index" :value="item.id">{{
                  item.group_name
                }}</Option>
              </Select>
            </FormItem>
          </div>
          <div class="item lang">
            <FormItem label="用户标签：">
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
          </div>
          <div class="item lang">
            <FormItem label="推广资格：">
              <RadioGroup v-model="formItem.spread_open" class="form-sty">
                <Radio :label="1">开启</Radio>
                <Radio :label="0">关闭</Radio>
              </RadioGroup>
              <div class="tip">关闭用户的推广资格后，在任何分销模式下该用户都无分销权限</div>
            </FormItem>
          </div>
          <div class="item lang">
            <FormItem label="推广权限：">
              <RadioGroup v-model="formItem.is_promoter" class="form-sty">
                <Radio :label="1">开启</Radio>
                <Radio :label="0">关闭</Radio>
                <div class="tip">指定分销模式下，开启或关闭用户的推广权限</div>
              </RadioGroup>
            </FormItem>
          </div>
          <div class="item lang">
            <FormItem label="用户状态：">
              <RadioGroup v-model="formItem.status" class="form-sty">
                <Radio :label="1">开启</Radio>
                <Radio :label="0">锁定</Radio>
              </RadioGroup>
            </FormItem>
          </div>
        </div>
      </div>
      <div class="section">
        <div class="section-hd">用户备注</div>
        <div class="section-bd">
          <div class="item">
            <FormItem label="用户备注：">
              <Input
                class="form-sty"
                type="textarea"
                :rows="5"
                v-model="formItem.mark"
                placeholder="请输入用户备注"
              ></Input>
            </FormItem>
          </div>
        </div>
      </div>
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

import { userLabelAddApi, getUserInfo, editUser, setUser } from '@/api/user';
import dayjs from 'dayjs';

export default {
  name: 'userInfo',
  components: { userLabel },
  props: {
    userId: {
      type: Number,
      default: 0,
    },
  },
  filters: {
    timeFormat(value) {
      if (!value) {
        return '-';
      }
      return dayjs(value * 1000).format('YYYY-MM-DD HH:mm:ss');
    },
    gender(value) {
      if (value == 1) {
        return '男';
      } else if (value == 2) {
        return '女';
      } else {
        return '未知';
      }
    },
  },
  data() {
    return {
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
  computed: {
    hasExtendInfo() {
      //   return this.psInfo.extend_info.some((item) => item.value);
    },
  },
  created() {
    this.getUserFrom(this.userId);

    // this.formItem = this.userData.userInfo;
  },
  methods: {
    setUser() {
      let data = this.formItem;
      let ids = [];
      this.dataLabel.map((i) => {
        ids.push(i.id);
      });
      data.label_id = ids;
      // if (!data.real_name) return this.$Message.warning("请输入真实姓名");
      // if (!data.phone) return this.$Message.warning("请输入手机号");
      // if (!data.pwd) return this.$Message.warning("请输入密码");
      // if (!data.true_pwd) return this.$Message.warning("请输入确认密码");
      if (data.uid) {
        editUser(data)
          .then((res) => {
            this.$Message.success(res.msg);
            this.$emit('success');
          })
          .catch((err) => {
            this.$Message.error(err.msg);
          });
      } else {
        setUser(data)
          .then((res) => {
            this.$emit('success');
            this.$Message.success(res.msg);
          })
          .catch((err) => {
            this.$Message.error(err.msg);
          });
      }
    },
    addLabel() {
      this.$modalForm(userLabelAddApi(0)).then(() => {});
    },
    openLabel(row) {
      this.labelShow = true;
      this.$refs.userLabel.userLabel(JSON.parse(JSON.stringify(this.infoData.labelInfo)));
    },
    closeLabel(label) {
      let index = this.dataLabel.indexOf(this.dataLabel.filter((d) => d.id == label.id)[0]);
      this.dataLabel.splice(index, 1);
    },
    getUserFrom(id) {
      getUserInfo(id)
        .then(async (res) => {
          this.userData = res.data;
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
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 标签弹窗关闭
    labelClose() {
      this.labelShow = false;
    },
    activeData(dataLabel) {
      this.labelShow = false;
      this.dataLabel = dataLabel;
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

<style lang="less" scoped>
.labelInput {
  border: 1px solid #dcdee2;
  width: 300px;
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
.width-add {
  width: 40px;
}
.mr30 {
  margin-right: 30px;
}

.user-info {
  .section {
    padding: 25px 0;
    border-bottom: 1px dashed #eeeeee;

    &-hd {
      padding-left: 10px;
      border-left: 3px solid #1890ff;
      font-weight: 500;
      font-size: 14px;
      line-height: 16px;
      color: #303133;
    }

    &-bd {
      display: flex;
      flex-wrap: wrap;
    }

    .item {
      width: 50%;
      display: flex;
      margin: 16px 0px 0 0;
      font-size: 13px;
      color: #606266;

      &:nth-child(3n + 3) {
        margin: 16px 0 0;
      }
      .form-sty {
        width: 300px;
      }
      .ivu-form-item {
        margin: 3px 0;
      }
      .addfont {
        display: inline-block;
        font-size: 13px;
        font-weight: 400;
        color: #1890ff;
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
