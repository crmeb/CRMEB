<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form label-width="85px" @submit.native.prevent>
        <el-form-item label="协议名称：">
          <el-input v-model="agreement.title"></el-input>
        </el-form-item>
        <el-form-item label="协议内容：">
          <WangEditor :content="agreement.content" @editorContent="getEditorContent"></WangEditor>
        </el-form-item>
        <el-form-item label="开启状态：">
          <el-switch :active-value="1"  :inactive-value="0" v-model="agreement.status" size="large">
           </el-switch>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" v-db-click @click="memberAgreementSave">保存</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script>
import WangEditor from '@/components/wangEditor/index.vue';
import { memberAgreement, memberAgreementSave } from '@/api/user';

export default {
  components: { WangEditor },
  data() {
    return {
      ueConfig: {
        autoHeightEnabled: false,
        initialFrameHeight: 500,
        initialFrameWidth: '100%',
        UEDITOR_HOME_URL: '/UEditor/',
        serverUrl: '',
      },
      id: 0,
      agreement: {
        title: '',
        content: '',
        status: 1,
      },
      spinShow: false,
    };
  },
  created() {
    this.memberAgreement();
  },
  methods: {
    getEditorContent(data) {
      this.agreement.content = data;
    },
    memberAgreement() {
      this.spinShow = true;
      memberAgreement()
        .then((res) => {
          this.spinShow = false;
          const { title, content, status, id } = res.data;
          this.agreement.title = title;
          this.agreement.content = content;
          this.agreement.status = status;
          this.id = id;
        })
        .catch((err) => {
          this.$message.error(err);
          this.spinShow = false;
        });
    },
    // 保存
    memberAgreementSave() {
      memberAgreementSave(this.id, this.agreement)
        .then((res) => {
          this.$message.success('保存成功');
          this.memberAgreement();
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
  },
};
</script>

<style scoped lang="stylus">
::v-deep .ivu-form-item-content {
  line-height: unset !important;
}
</style>
