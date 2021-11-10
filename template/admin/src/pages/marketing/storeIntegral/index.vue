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
          <Col>
            <FormItem label="创建时间：" label-for="user_time">
              <!--<DatePicker clearable @on-change="onchangeTime" v-model="timeVal" :value="timeVal"  format="yyyy/MM/dd" type="daterange" placement="bottom-end" placeholder="选择时间" v-width="'100%'"></DatePicker>-->
              <DatePicker
                :editable="false"
                @on-change="onchangeTime"
                :value="timeVal"
                format="yyyy/MM/dd"
                type="daterange"
                placement="bottom-start"
                placeholder="自定义时间"
                style="width: 200px"
                class="mr20"
              ></DatePicker>
            </FormItem>
          </Col>
          <Col>
            <FormItem label="上架状态：">
              <Select
                placeholder="请选择"
                clearable
                style="width: 150px"
                v-model="tableFrom.is_show"
                @on-change="userSearchs"
              >
                <Option value="1">上架</Option>
                <Option value="0">下架</Option>
              </Select>
            </FormItem>
          </Col>
          <Col>
            <FormItem label="商品搜索：" label-for="store_name">
              <Input
                search
                enter-button
                style="width: 200px"
                placeholder="请输入商品名称，ID"
                v-model="tableFrom.store_name"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
        </Row>
        <Row type="flex" class="mb20">
          <Col>
            <Button
              v-auth="['marketing-store_seckill-create']"
              type="primary"
              icon="md-add"
              @click="add"
              class="mr10"
              >添加积分商品</Button
            >
            <!-- <Button
              v-auth="['marketing-store_seckill-create']"
              type="primary"
              icon="md-add"
              @click="addMore"
              class="mr10"
              >批量添加积分商品</Button
            > -->
            <!--<Button v-auth="['export-storeSeckill']" class="export" icon="ios-share-outline" @click="exports">导出</Button>-->
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
        <template slot-scope="{ row, index }" slot="image">
          <viewer>
            <div class="tabBox_img">
              <img v-lazy="row.image" />
            </div>
          </viewer>
        </template>
        <template slot-scope="{ row, index }" slot="stop_time">
          <span> {{ row.stop_time | formatDate }}</span>
        </template>
        <template slot-scope="{ row, index }" slot="is_show">
          <i-switch
            v-model="row.is_show"
            :value="row.is_show"
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
          <!-- <a v-if="row.stop_status === 1" @click="copy(row)" >一键复制</a>
                    <a v-else @click="edit(row)" >编辑</a> -->
          <a @click="orderList(row)">兑换记录</a>
          <Divider type="vertical" />
          <a @click="edit(row)">编辑</a>
          <Divider type="vertical" />
          <a @click="copy(row)">复制</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除积分商品', index)">删除</a>
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
  integralProductListApi,
  integralIsShowApi,
  storeSeckillApi,
} from "@/api/marketing";
import { formatDate } from "@/utils/validate";
export default {
  name: "storeIntegral",
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, "yyyy-MM-dd");
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
          title: "商品图片",
          slot: "image",
          minWidth: 90,
        },
        {
          title: "活动标题",
          key: "title",
          minWidth: 130,
        },
        {
          title: "兑换积分",
          key: "price",
          minWidth: 100,
        },
        {
          title: "限量",
          key: "quota_show",
          minWidth: 130,
        },
        {
          title: "限量剩余",
          key: "quota",
          minWidth: 130,
        },
        {
          title: "创建时间",
          key: "add_time",
          minWidth: 130,
        },
        {
          title: "排序",
          key: "sort",
          minWidth: 50,
        },
        {
          title: "状态",
          slot: "is_show",
          minWidth: 100,
        },
        {
          title: "操作",
          slot: "action",
          fixed: "right",
          minWidth: 130,
        },
      ],
      tableList: [],
      timeVal: [],
      grid: {
        xl: 7,
        lg: 10,
        md: 12,
        sm: 24,
        xs: 24,
      },
      tableFrom: {
        integral_time: "",
        is_show: "",
        store_name: "",
        page: 1,
        limit: 15,
      },
      total: 0,
    };
  },
  computed: {
    ...mapState("media", ["isMobile"]),
    labelWidth() {
      return this.isMobile ? undefined : 80;
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
      this.$router.push({ path: "/admin/marketing/store_integral/create" });
    },
    addMore() {
      this.$router.push({
        path: "/admin/marketing/store_integral/add_store_integral",
      });
    },
    orderList(row) {
      this.$router.push({
        path: "/admin/marketing/store_integral/order_list",
        query: {
          product_id: row.id,
        },
      });
    },
    // 导出
    exports() {
      let formValidate = this.tableFrom;
      let data = {
        start_status: formValidate.start_status,
        status: formValidate.status,
        store_name: formValidate.store_name,
      };
      storeSeckillApi(data)
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
        path: "/admin/marketing/store_integral/create/" + row.id + "/0",
      });
    },
    // 一键复制
    copy(row) {
      this.$router.push({
        path: "/admin/marketing/store_integral/create/" + row.id + "/1",
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/integral/${row.id}`,
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
      this.tableFrom.start_status = this.tableFrom.start_status || "";
      this.tableFrom.is_show = this.tableFrom.is_show || "";
      integralProductListApi(this.tableFrom)
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
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.integral_time = this.timeVal.join("-");
    },
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        is_show: row.is_show,
      };
      integralIsShowApi(data)
        .then(async (res) => {
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
