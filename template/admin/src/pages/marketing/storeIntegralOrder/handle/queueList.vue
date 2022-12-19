<template>
  <Modal v-model="modal" title="任务列表" width="1000" footer-hide>
    <!-- <div> -->
    <!-- <div class="i-layout-page-header">
            <PageHeader class="product_tabs" title="任务列表" hidden-breadcrumb></PageHeader>
        </div> -->
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="formValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        class="tabform"
        @submit.native.prevent
      >
        <Row :gutter="24" type="flex">
          <Col span="10">
            <FormItem label="操作时间：">
              <DatePicker
                :editable="false"
                @on-change="onchangeTime"
                :value="timeVal"
                format="yyyy/MM/dd"
                type="datetimerange"
                placement="bottom-start"
                placeholder="自定义时间"
                style="width: 90%"
                :options="options"
              ></DatePicker>
            </FormItem>
          </Col>
          <Col span="7">
            <FormItem label="类型：">
              <Select v-model="formValidate.type" clearable @on-change="typeSearchs">
                <Option v-for="item in typeList" :value="item.value" :key="item.value">{{ item.label }}</Option>
              </Select>
            </FormItem>
          </Col>
          <Col span="7">
            <FormItem label="状态：">
              <Select v-model="formValidate.status" clearable @on-change="statusSearchs">
                <Option v-for="item in statusList" :value="item.value" :key="item.value">{{ item.label }}</Option>
              </Select>
            </FormItem>
          </Col>
        </Row>
      </Form>
      <Table class="mt25" height="530" :columns="columns1" :data="data1" :loading="loading">
        <template slot-scope="{ row, index }" slot="action">
          <template v-if="row.is_show_log">
            <a @click="deliveryLook(row)">查看</a>
            <Divider type="vertical" />
          </template>
          <template>
            <Dropdown @on-click="changeMenu(row, $event)">
              <a>更多<Icon type="ios-arrow-down"></Icon></a>
              <DropdownMenu slot="list">
                <DropdownItem name="1">下载</DropdownItem>
                <DropdownItem name="2">重新执行</DropdownItem>
                <DropdownItem v-if="row.is_stop_button" name="3">停止任务</DropdownItem>
                <DropdownItem v-if="row.is_error_button" name="4">清除异常任务</DropdownItem>
              </DropdownMenu>
            </Dropdown>
          </template>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="page1.total"
          :current="page1.pageNum"
          show-elevator
          show-total
          @on-change="pageChange"
          @on-page-size-change="limitChange"
          :page-size="page1.pageSize"
          show-sizer
        />
      </div>
    </Card>
    <Modal v-model="modal1" width="900" footer-hide>
      <Table height="500" class="mt25" :columns="columns4" :data="data2" :loading="loading2"></Table>
      <div class="acea-row row-right page">
        <Page
          :total="page2.total"
          :current="page2.pageNum"
          show-elevator
          show-total
          @on-change="pageChange2"
          @on-page-size-change="limitChange2"
          :page-size="page2.pageSize"
          show-sizer
        />
      </div>
    </Modal>
    <!-- </div> -->
  </Modal>
</template>

<script>
import { queueIndex, deliveryLog, queueAgain, queueDel, batchOrderDelivery, stopWrongQueue } from '@/api/order';
import { mapState } from 'vuex';

export default {
  data() {
    return {
      modal: false,
      columns1: [
        {
          title: 'ID',
          key: 'id',
        },
        {
          title: '操作时间',
          key: 'add_time',
        },
        {
          title: '发货单数',
          key: 'total_num',
        },
        {
          title: '成功发货单数',
          key: 'success_num',
        },
        {
          title: '发货类型',
          key: 'title',
        },
        {
          title: '状态',
          key: 'status_cn',
        },
        {
          title: '操作',
          slot: 'action',
          // fixed: 'right',
          width: 150,
          align: 'center',
        },
      ],
      data1: [],
      page1: {
        total: 0, // 总条数
        pageNum: 1, // 当前页
        pageSize: 10, // 每页显示条数
      },
      formValidate: {
        type: '',
        status: '',
        data: '',
      },
      options: {
        shortcuts: [
          {
            text: '今天',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()));
              return [start, end];
            },
          },
          {
            text: '昨天',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)),
              );
              end.setTime(
                end.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)),
              );
              return [start, end];
            },
          },
          {
            text: '最近7天',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 6)),
              );
              return [start, end];
            },
          },
          {
            text: '最近30天',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)),
              );
              return [start, end];
            },
          },
          {
            text: '本月',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), 1)));
              return [start, end];
            },
          },
          {
            text: '本年',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(start.setTime(new Date(new Date().getFullYear(), 0, 1)));
              return [start, end];
            },
          },
        ],
      },
      timeVal: [],
      typeList: [
        // {
        //     label: '批量发放用户优惠券',
        //     value: '1'
        // },
        // {
        //     label: '批量设置用户分组',
        //     value: '2'
        // },
        // {
        //     label: '批量设置用户标签',
        //     value: '3'
        // },
        // {
        //     label: '批量下架商品',
        //     value: '4'
        // },
        // {
        //     label: '批量删除商品规格',
        //     value: '5'
        // },
        {
          label: '批量删除订单',
          value: '6',
        },
        {
          label: '批量手动发货',
          value: '7',
        },
        {
          label: '批量打印电子面单',
          value: '8',
        },
        {
          label: '批量配送',
          value: '9',
        },
        {
          label: '批量虚拟发货',
          value: '10',
        },
      ],
      statusList: [
        {
          label: '未处理',
          value: '0',
        },
        {
          label: '处理中',
          value: '1',
        },
        {
          label: '已完成',
          value: '2',
        },
        {
          label: '处理失败',
          value: '3',
        },
      ],
      columns2: [
        {
          title: '订单ID',
          key: 'order_id',
        },
        {
          title: '物流公司',
          key: 'delivery_name',
        },
        {
          title: '物流单号',
          key: 'delivery_id',
        },
        {
          title: '处理状态',
          key: 'status_cn',
        },
        {
          title: '异常原因',
          key: 'error',
        },
      ],
      columns3: [
        {
          title: '订单ID',
          key: 'order_id',
        },
        {
          title: '备注',
          key: 'fictitious_content',
        },
        {
          title: '处理状态',
          key: 'status_cn',
        },
        {
          title: '异常原因',
          key: 'error',
        },
      ],
      columns5: [
        {
          title: '订单ID',
          key: 'order_id',
        },
        {
          title: '配送员',
          key: 'delivery_name',
        },
        {
          title: '配送员电话',
          key: 'delivery_id',
        },
        {
          title: '处理状态',
          key: 'status_cn',
        },
        {
          title: '异常原因',
          key: 'error',
        },
      ],
      columns4: [],
      data2: [],
      page2: {
        total: 0, // 总条数
        pageNum: 1, // 当前页
        pageSize: 12, // 每页显示条数
      },
      modal1: false,
      deliveryLog: null,
      deliveryLogId: 0,
      deliveryLogType: '',
      loading: false,
      loading2: false,
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 75;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  created() {
    this.getQueue();
  },
  methods: {
    getQueue() {
      let data = {
        page: this.page1.pageNum,
        limit: this.page1.pageSize,
      };
      if (this.formValidate.status) {
        data.status = this.formValidate.status;
      }
      if (this.formValidate.type) {
        data.type = this.formValidate.type;
      }
      if (this.formValidate.data) {
        data.data = this.formValidate.data;
      }
      this.loading = true;
      queueIndex(data)
        .then((res) => {
          this.loading = false;
          this.data1 = res.data.list;
          this.page1.total = res.data.count;
        })
        .catch((err) => {
          this.loading = false;
        });
    },
    pageChange(index) {
      this.page1.pageNum = index;
      this.getQueue();
    },
    // 查看-分页
    pageChange2(index) {
      this.page2.pageNum = index;
      this.getDeliveryLog();
    },
    limitChange(limit) {
      this.page1.pageSize = limit;
      this.getQueue();
    },
    limitChange2(limit) {
      this.page2.pageSize = limit;
      this.getDeliveryLog();
    },
    // 搜索-操作时间
    onchangeTime(time) {
      this.timeVal = time;
      this.formValidate.data = this.timeVal[0] ? this.timeVal.join('-') : '';
      this.page1.pageNum = 1;
      this.getQueue();
    },
    // 搜索-类型
    typeSearchs() {
      this.page1.pageNum = 1;
      this.getQueue();
    },
    // 搜索-状态
    statusSearchs() {
      this.page1.pageNum = 1;
      this.getQueue();
    },
    // 查看-获取数据
    getDeliveryLog() {
      this.loading2 = true;
      deliveryLog(this.deliveryLogId, this.deliveryLogType, {
        page: this.page2.pageNum,
        limit: this.page2.pageSize,
      })
        .then((res) => {
          this.loading2 = false;
          this.data2 = res.data.list;
          this.page2.total = res.data.count;
        })
        .catch((err) => {
          this.loading2 = false;
        });
    },
    // 查看
    deliveryLook(row) {
      this.modal1 = true;
      this.deliveryLogId = row.id;
      this.deliveryLogType = row.cache_type;
      this.deliveryLog = row;
      switch (row.type) {
        case 7:
        case 8:
          this.columns4 = this.columns2;
          break;
        case 9:
          this.columns4 = this.columns5;
          break;
        case 10:
          this.columns4 = this.columns3;
          break;
      }
      this.getDeliveryLog();
    },
    // 更多
    changeMenu(row, $event) {
      switch ($event) {
        // 下载
        case '1':
          batchOrderDelivery(row.id, row.type, row.cache_type)
            .then((res) => {
              window.open(res.data[0]);
            })
            .catch((err) => {
              this.$Message.error(err.msg);
            });
          break;
        // 重新执行
        case '2':
          this.queueAgain(row.id, row.type);
          break;
        // 停止任务
        case '3':
          this.$Modal.confirm({
            title: '谨慎操作',
            content: '<p>确认停止该任务？</p>',
            onOk: () => {
              this.stopQueue(row.id);
            },
          });
          break;
        // 清除异常任务
        case '4':
          this.queueDel(row.id, row.type);
          break;
      }
    },
    // 重新执行
    queueAgain(id, type) {
      queueAgain(id, type)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getQueue();
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    // 清除异常任务
    queueDel(id, type) {
      queueDel(id, type)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getQueue();
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    // 停止任务
    stopQueue(id) {
      stopWrongQueue(id)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getQueue();
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
  },
};
</script>

<style></style>
