<template>
  <div>
    <div class="i-layout-page-header header-title">
      <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form label-width="85px" @submit.native.prevent v-loading="spinShow">
        <el-form-item label="协议内容：">
          <WangEditor :content="agreement.content" @editorContent="getEditorContent"></WangEditor>
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
import { agentAgreement, agentAgreementSave } from '@/api/user';

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
      agreement: {
        content: '',
        id: 0,
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
      agentAgreement()
        .then((res) => {
          this.spinShow = false;
          const { title, content, status, id } = res.data;
          this.agreement.content = content;
          this.agreement.id = id || 0;
        })
        .catch((err) => {
          this.$message.error(err.msg);
          this.spinShow = false;
        });
    },
    // 保存
    memberAgreementSave() {
      agentAgreementSave(this.agreement)
        .then((res) => {
          this.$message.success('保存成功');
          this.memberAgreement();
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
::v-deep .ivu-form-item-content {
  line-height: unset !important;
}
</style>
