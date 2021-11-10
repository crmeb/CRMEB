<template>
    <div>
        <div class="i-layout-page-header">
            <div class="i-layout-page-header">
                <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
            </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form :label-width="80" @submit.native.prevent>
                <FormItem label="协议名称：">
                    <Input v-model="agreement.title"></Input>
                </FormItem>
                <FormItem label="协议内容：">
                    <vue-ueditor-wrap
                        v-model="agreement.content"
                        :config="ueConfig"
                    ></vue-ueditor-wrap>
                </FormItem>
                <FormItem label="开启状态：">
                    <i-switch
                        v-model="agreement.status"
                        size="large"
                        :true-value="1"
                        :false-value="0"
                    >
                        <span slot="open">开启</span>
                        <span slot="close">关闭</span>
                    </i-switch>
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
import VueUeditorWrap from "vue-ueditor-wrap";
import { memberAgreement, memberAgreementSave } from "@/api/user";

export default {
    components: { VueUeditorWrap },
    data() {
        return {
            ueConfig: {
                autoHeightEnabled: false,
                initialFrameHeight: 500,
                initialFrameWidth: "100%",
                UEDITOR_HOME_URL: "/admin/UEditor/",
                serverUrl: ""
            },
            id: 0,
            agreement: {
                title: "",
                content: "",
                status: 1
            },
            spinShow:false
        };
    },
    created() {
        this.memberAgreement();
    },
    methods: {
        memberAgreement() {
            this.spinShow = true;
            memberAgreement()
                .then(res => {
                    this.spinShow = false;
                    const { title, content, status, id } = res.data;
                    this.agreement.title = title;
                    this.agreement.content = content;
                    this.agreement.status = status;
                    this.id = id;
                })
                .catch(err => {
                    this.$Message.error(err);
                    this.spinShow = false;
                });
        },
        // 保存
        memberAgreementSave() {
            this.$Spin.show();
            memberAgreementSave(this.id, this.agreement)
                .then(res => {
                    this.$Spin.hide();
                    this.$Message.success("保存成功");
                    this.memberAgreement();
                })
                .catch(err => {
                    this.$Spin.hide();
                    this.$Message.error(err);
                });
        }
    }
};
</script>

<style scoped lang="stylus">
    /deep/.ivu-form-item-content{
        line-height unset!important;
    }
</style>
