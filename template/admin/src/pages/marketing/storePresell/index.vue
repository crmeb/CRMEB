<template>
  <div>
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="tableFrom"
        :model="tableFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col v-bind="grid">
            <FormItem label="预售活动状态：">
              <Select
                placeholder="请选择活动状态"
                v-model="tableFrom.time_type"
                clearable
                @on-change="userSearchs"
              >
                <Option value="0">全部</Option>
                <Option value="1">未开始</Option>
                <Option value="2">正在进行</Option>
                <Option value="3">已结束</Option>
              </Select>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="预售商品状态：">
              <Select
                placeholder="请选择商品状态"
                v-model="tableFrom.status"
                clearable
                @on-change="userSearchs"
              >
                <Option value="">全部</Option>
                <Option value="1">上架</Option>
                <Option value="0">下架</Option>
              </Select>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="商品搜索：" label-for="title">
              <Input
                search
                enter-button
                placeholder="请输入商品名称/ID"
                v-model="tableFrom.title"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
        </Row>
        <Row type="flex" class="mb20">
          <Col v-bind="grid">
            <Button
              v-auth="['marketing-store_bargain-create']"
              type="primary"
              icon="md-add"
              @click="add"
              class="mr10"
              >添加预售商品</Button
            >
            <!-- <Button
              v-auth="['export-storeBargain']"
              class="export"
              icon="ios-share-outline"
              @click="exports"
              >导出</Button
            > -->
          </Col>
        </Row>
      </Form>
      <Table
        :columns="columns1"
        :data="tableList"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="is_fail">
          <Icon
            type="md-checkmark"
            v-if="row.is_fail === 1"
            color="#0092DC"
            size="14"
          />
          <Icon type="md-close" v-else color="#ed5565" size="14" />
        </template>
        <template slot-scope="{ row, index }" slot="image">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="row.image" />
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="bargain_min_price">
          <span>{{ row.bargain_min_price }}~{{ row.bargain_max_price }}</span>
        </template>
        <template slot-scope="{ row, index }" slot="stop_time">
          <div>起: {{ row.start_time | formatDate }}</div>
          <div>止: {{ row.stop_time | formatDate }}</div>
        </template>
        <template slot-scope="{ row, index }" slot="status">
          <i-switch
            v-model="row.status"
            :value="row.status"
            :true-value="1"
            :false-value="0"
            @on-change="onchangeIsShow(row)"
            size="large"
          >
            <span slot="open">上架</span>
            <span slot="close">下架</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row)">编辑</a>
          <Divider v-if="row.stop_status === 0" type="vertical" />
          <Divider type="vertical" />
          <a @click="del(row, '删除预售商品', index)">删除</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="tableFrom.page"
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
import {
  presellListApi,
  advanceSetStatusApi,
  stroeBargainApi,
} from "@/api/marketing";
import { formatDate } from "@/utils/validate";
export default {
  name: "storeBargain",
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, "yyyy-MM-dd hh:mm");
      }
    },
  },
  data() {
    return {
      loading: false,
      columns1: [
        {
          title: "ID",
          key: "id",
          width: 80,
        },
        {
          title: "预售图片",
          slot: "image",
          minWidth: 90,
        },
        {
          title: "预售名称",
          key: "title",
          minWidth: 130,
        },
        {
          title: "预售价格",
          key: "price",
          minWidth: 100,
        },
        {
          title: "已售商品数",
          key: "sales",
          minWidth: 100,
        },
        {
          title: "限量",
          key: "quota_show",
          minWidth: 100,
        },
        {
          title: "限量剩余",
          key: "quota",
          minWidth: 100,
        },
        {
          title: "活动时间",
          slot: "stop_time",
          minWidth: 150,
        },
        {
          title: "预售状态",
          slot: "status",
          minWidth: 130,
        },
        {
          title: "操作",
          slot: "action",
          fixed: "right",
          minWidth: 130,
        },
      ],
      tableList: [],
      grid: {
        xl: 7,
        lg: 10,
        md: 12,
        sm: 24,
        xs: 24,
      },
      tableFrom: {
        status: "",
        time_type: 0,
        title: "",
        page: 1,
        limit: 15,
      },
      tableFrom2: {
        status: "",
        store_name: "",
        export: 1,
      },
      total: 0,
    };
  },
  computed: {
    ...mapState("media", ["isMobile"]),
    labelWidth() {
      return this.isMobile ? undefined : 100;
    },
    labelPosition() {
      return this.isMobile ? "top" : "left";
    },
  },
  created() {
    this.getList();
  },
  methods: {
    // 添加
    add() {
      this.$router.push({ path: "/admin/marketing/presell/create/0" });
    },
    // 导出
    exports() {
      let formValidate = this.tableFrom;
      let data = {
        status: formValidate.status,
        store_name: formValidate.store_name,
      };
      stroeBargainApi(data)
        .then((res) => {
          location.href = res.data[0];
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 编辑
    edit(row) {
      this.$router.push({
        path: "/admin/marketing/presell/create/" + row.id + "/0",
      });
    },
    // 一键复制
    copy(row) {
      this.$router.push({
        path: "/admin/marketing/presell/create/" + row.id + "/1",
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/advance/${row.id}`,
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
    // 列表
    getList() {
      this.loading = true;
      this.tableFrom.status = this.tableFrom.status || "";
      presellListApi(this.tableFrom)
        .then(async (res) => {
          let data = res.data;
          this.tableList = data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.tableFrom.page = index;
      this.getList();
    },
    // 表格搜索
    userSearchs() {
      this.tableFrom.page = 1;
      this.getList();
    },
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        status: row.status,
      };
      advanceSetStatusApi(data)
        .then(async (res) => {
          this.getList();
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="stylus">
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}
</style>
