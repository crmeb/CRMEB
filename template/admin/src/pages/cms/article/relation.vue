<template>
  <Modal
    v-model="modals"
    scrollable
    footer-hide
    closable
    title="选择商品"
    :mask-closable="false"
    width="950"
    @on-cancel="handleReset"
  >
    <Form
      ref="levelFrom"
      :model="levelFrom"
      :label-width="labelWidth"
      :label-position="labelPosition"
      @submit.native.prevent
    >
      <Row type="flex" :gutter="24">
        <Col v-bind="grid">
          <FormItem label="商品名称：" prop="status2" label-for="status2">
            <Input
              search
              enter-button
              v-model="levelFrom.name"
              placeholder="请输入商品名称"
              @on-search="userSearchs"
              style="width: 100%"
            />
          </FormItem>
        </Col>
      </Row>
    </Form>
    <Divider dashed />
    <Table
      :columns="columns1"
      :data="levelLists"
      ref="table"
      :loading="loading"
      no-userFrom-text="暂无数据"
      no-filtered-userFrom-text="暂无筛选结果"
    >
      <template slot-scope="{ row, index }" slot="is_shows">
        <i-switch
          v-model="row.is_show"
          :value="row.is_show"
          :true-value="1"
          :false-value="0"
          size="large"
          @on-change="onchangeIsShow(row)"
        >
          <span slot="open">显示</span>
          <span slot="close">隐藏</span>
        </i-switch>
      </template>
      <template slot-scope="{ row, index }" slot="is_musts">
        <i-switch
          v-model="row.is_must"
          :value="row.is_must"
          :true-value="1"
          :false-value="0"
          size="large"
          @on-change="onchangeIsMust(row)"
        >
          <span slot="open">全部完成</span>
          <span slot="close">达成其一</span>
        </i-switch>
      </template>
      <template slot-scope="{ row, index }" slot="action">
        <a @click="edit(row)">编辑 | </a>
        <a @click="del(row, '删除任务')"> 删除</a>
      </template>
    </Table>
    <div class="acea-row row-right page">
      <Page :total="total" show-elevator show-total @on-change="pageChange" :page-size="levelFrom.limit" />
    </div>
    <!-- 新建 编辑表单-->
    <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail" :titleType="titleType"></edit-from>
  </Modal>
</template>

<script>
export default {
  name: 'relation',
  data() {
    return {
      modals: false,
      grid: {
        xl: 12,
        lg: 12,
        md: 12,
        sm: 24,
        xs: 24,
      },
    };
  },
  methods: {
    // 关闭模态框
    handleReset() {
      this.modals = false;
    },
    // 表格搜索
    userSearchs() {
      this.getList();
    },
    // 任务列表
    getList() {
      this.loading = true;
      taskListApi(this.levelId, this.levelFrom)
        .then(async (res) => {
          let data = res.data;
          this.levelLists = data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.levelFrom.page = index;
      this.getList();
    },
  },
};
</script>

<style scoped></style>
