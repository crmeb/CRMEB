<template>
  <div>
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Row type="flex" class="mb20">
        <Col span="24">
          <Button
            type="primary"
            icon="md-add"
            @click="add"
            class="mr10"
            >发布版本</Button
          >
        </Col>
      </Row>
      <Table
        :columns="columns1"
        :data="tableList"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row }" slot="platform">
          <span>{{ row.platform === 1 ? "安卓" : "苹果" }}</span>
        </template>
        <template slot-scope="{ row }" slot="is_force">
          <span>{{ row.is_force === 1 ? "强制" : "非强制" }}</span>
        </template>
        <!-- <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row)">编辑</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除版本', index)">删除</a>
        </template> -->
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="tableFrom.limit"
        />
      </div>
    </Card>
  </div>
</template>

<script>
import { mapState } from "vuex";
import { versionList, versionCrate } from "@/api/system";
export default {
  name: "index",
  computed: {
    ...mapState("media", ["isMobile"]),
    ...mapState("userLevel", ["categoryId"]),
    labelWidth() {
      return this.isMobile ? undefined : 80;
    },
    labelPosition() {
      return this.isMobile ? "top" : "left";
    },
  },
  data() {
    return {
      verModal: false,
      total: 20,
      tableFrom: {
        page: 1,
        limit: 15,
      },
      columns1: [
        {
          title: "版本号",
          key: "version",
          width: 80,
        },
        {
          title: "平台类型",
          slot: "platform",
          align: "center",
          minWidth: 120,
        },
        {
          title: "升级信息",
          key: "info",
          minWidth: 60,
        },
        {
          title: "是否强制",
          slot: "is_force",
          minWidth: 120,
        },
        {
          title: "下载地址",
          key: "url",
          minWidth: 120,
        },
        {
          title: "操作",
          slot: "action",
          align: "center",
          minWidth: 50,
        },
      ],
      loading: false,
      tableList: [],
    };
  },
  created() {
    this.getList();
  },
  methods: {
    // 修改成功
    submitFail() {
      this.getList();
    },
    // 聊天记录
    record(row) {
      this.rows = row;
      this.modals3 = true;
      this.isChat = true;
      this.getListRecord();
    },
    // 添加
    add() {
      this.$modalForm(versionCrate()).then((res) => {
        this.getList();
      });
    },
    // 版本信息列表
    getList() {
      this.loading = true;
      versionList()
        .then((res) => {
          this.tableList = res.data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((err) => {
          this.$Message.error(err);
          this.loading = false;
        });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `app/wechat/kefu/${row.id}`,
        method: "DELETE",
        ids: "",
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.tableList.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.$Message.success("成功!");
        } else {
          this.$Message.error("失败!");
        }
      });
    },
    handleReset(name) {
      this.$refs[name].resetFields();
    },
    pageChange() {},
  },
};
</script>

<style scoped lang="stylus"></style>
