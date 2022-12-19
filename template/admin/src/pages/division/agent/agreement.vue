<template>
  <div>
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form :label-width="80" @submit.native.prevent>
        <FormItem label="协议内容：">
          <WangEditor :content="agreement.content" @editorContent="getEditorContent"></WangEditor>
        </FormItem>
        <FormItem>
          <Button type="primary" @click="memberAgreementSave">保存</Button>
        </FormItem>
      </Form>
      <Spin fix v-if="spinShow"></Spin>
    </Card>
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
        UEDITOR_HOME_URL: '/admin/UEditor/',
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
          this.$Message.error(err);
          this.spinShow = false;
        });
    },
    // 保存
    memberAgreementSave() {
      this.$Spin.show();
      agentAgreementSave(this.agreement)
        .then((res) => {
          this.$Spin.hide();
          this.$Message.success('保存成功');
          this.memberAgreement();
        })
        .catch((err) => {
          this.$Spin.hide();
          this.$Message.error(err);
        });
    },
  },
};
</script>

<style scoped lang="stylus">
/deep/.ivu-form-item-content {
  line-height: unset !important;
}
</style>
