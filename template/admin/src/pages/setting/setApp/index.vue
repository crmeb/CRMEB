<template>
  <div class="article-manager">
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">系统设置</span>
        <div>
          <Tabs v-model="currentTab">
            <TabPane label="公众号配置" name="onsale" />
            <TabPane label="配置" name="forsale" />
          </Tabs>
        </div>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt" v-if="currentTab === 'onsale'">
      <!--<form-create  :rule="Array.from(FromData.rules)" @submit="onSubmit" ></form-create>-->
    </Card>
  </div>
</template>

<script>
import formCreate from '@form-create/iview';
export default {
  name: 'setApp',
  components: { formCreate },
  data() {
    return {
      FromData: null,
      currentTab: '',
    };
  },
  mounted() {},
  methods: {},
};
</script>

<style scoped></style>
