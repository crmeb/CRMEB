<template>
  <div class="agreemant">
    <Card :bordered="false" dis-hover class="ivu-mt" :padding="0">
      <div class="new_card_pd">
        <Tabs v-model="currentTab" @on-click="changeTab">
          <TabPane :label="item.label" :name="item.value.toString()" v-for="(item, index) in headerList" :key="index" />
        </Tabs>
      </div>
    </Card>

    <Row class="mb10 content">
      <Col span="16">
        <WangEditor style="width: 100%" :content="formValidate.content" @editorContent="getEditorContent"></WangEditor>
      </Col>
      <Col span="6" style="width: 33%">
        <div class="ifam">
          <div class="content" v-html="content"></div>
        </div>
      </Col>
    </Row>
    <!-- <Row class="mb10 content">
      <Button class="bnt" type="primary" @click="save" :loading="loadingExist"
        >保存</Button
      >
    </Row> -->
    <Card
      :bordered="false"
      dis-hover
      class="fixed-card"
      :style="{ left: `${!menuCollapse ? '200px' : isMobile ? '0' : '80px'}` }"
    >
      <div class="acea-row row-center">
        <Button class="bnt" type="primary" @click="save" :loading="loadingExist">保存</Button>
      </div>
    </Card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import WangEditor from '@/components/wangEditor/index.vue';
import { getAgreements, setAgreements } from '@/api/system';

export default {
  components: { WangEditor },
  data() {
    return {
      loadingExist: false,
      currentTab: '1',
      headerList: [
        { label: '付费会员协议', value: '1' },
        { label: '代理商协议', value: '2' },
        { label: '隐私协议', value: '3' },
        { label: '用户协议', value: '4' },
        { label: '注销协议', value: '5' },
        { label: '积分协议', value: '6' },
      ],
      ueConfig: {
        autoHeightEnabled: false,
        initialFrameHeight: 500,
        initialFrameWidth: '100%',
        UEDITOR_HOME_URL: '/admin/UEditor/',
        serverUrl: '',
      },
      id: 0,
      formValidate: {
        content: '',
      },
      content: '',
      spinShow: false,
    };
  },
  computed: {
    ...mapState('admin/layout', ['isMobile', 'menuCollapse']),
  },
  created() {
    this.changeTab(this.currentTab);
  },
  methods: {
    save() {
      this.formValidate.content = this.content;
      setAgreements(this.formValidate)
        .then(async (res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    getEditorContent(content) {
      this.content = content;
    },
    changeTab(data) {
      this.formValidate.content = ' ';
      getAgreements(data).then((res) => {
        this.formValidate.id = res.data.id || 0;
        this.formValidate.type = res.data.type;
        this.formValidate.title = res.data.title;
        this.formValidate.content = res.data.content;
        this.content = res.data.content;
      });
    },
  },
};
</script>

<style lang="stylus" scoped>
.agreemant {
  background-color: #fff;
  padding: 5px 15px ;
  margin-top 10px;
}

.content {
  padding: 10px 0px;
}

.ifam {
  width: 344px;
  height: 644px;
  background: url('../../../assets/images/ag-phone.png') no-repeat center top;
  background-size: 344px 644px;
  padding: 40px 20px;
  padding-top: 50px;
  margin: 0 auto 0 20px;

  .content {
    height: 560px;
    overflow: hidden;
    scrollbar-width: none; /* firefox */
    -ms-overflow-style: none; /* IE 10+ */
    overflow-x: hidden;
    overflow-y: auto;
  }

  .content::-webkit-scrollbar {
    display: none; /* Chrome Safari */
  }
}

.new_tab {
  >>>.ivu-tabs-nav .ivu-tabs-tab {
    padding: 4px 16px 20px !important;
    font-weight: 500;
  }
}

.fixed-card {
  position: fixed;
  right: 0;
  bottom: 0;
  left: 200px;
  z-index: 99;
  box-shadow: 0 -1px 2px rgb(240, 240, 240);

  /deep/ .ivu-card-body {
    padding: 15px 16px 14px;
  }

  .ivu-form-item {
    margin-bottom: 0;
  }

  /deep/ .ivu-form-item-content {
    margin-right: 124px;
    text-align: center;
  }
}
</style>
