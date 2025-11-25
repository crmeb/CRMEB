<template>
  <el-dialog :visible.sync="modal" title="任务列表" width="1000px">
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form
        ref="formValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        class="tabform"
        @submit.native.prevent
      >
        <el-row :gutter="24">
          <el-col span="10">
            <el-form-item label="操作时间：">
              <el-date-picker
                clearable
                :editable="false"
                @change="onchangeTime"
                v-model="timeVal"
                format="yyyy/MM/dd"
                type="datetimerange"
                value-format="yyyy/MM/dd"
                range-separator="-"
                start-placeholder="开始日期"
                end-placeholder="结束日期"
                style="width: 90%"
                :options="options"
              ></el-date-picker>
            </el-form-item>
          </el-col>
          <el-col :span="7">
            <el-form-item label="类型：">
              <el-select v-model="formValidate.type" clearable @change="typeSearchs">
                <el-option
                  v-for="item in typeList"
                  :value="item.value"
                  :key="item.value"
                  :label="item.label"
                ></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="7">
            <el-form-item label="状态：">
              <el-select v-model="formValidate.status" clearable @change="statusSearchs">
                <el-option
                  v-for="item in statusList"
                  :value="item.value"
                  :key="item.value"
                  :label="item.label"
                ></el-option>
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <el-table class="mt14" height="530" :data="data1" v-loading="loading">
        <el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="发货单数" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.total_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="成功发货单数" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.success_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="发货类型" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.status_cn }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <template v-if="scope.row.is_show_log">
              <a v-db-click @click="deliveryLook(scope.row)">查看</a>
              <el-divider direction="vertical"></el-divider>
            </template>
            <template>
              <el-dropdown size="small" @command="changeMenu(scope.row, $event)">
                <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item command="1">下载</el-dropdown-item>
                  <el-dropdown-item command="2">重新执行</el-dropdown-item>
                  <el-dropdown-item v-if="scope.row.is_stop_button" command="3">停止任务</el-dropdown-item>
                  <el-dropdown-item v-if="scope.row.is_error_button" command="4">清除异常任务</el-dropdown-item>
                </el-dropdown-menu>
              </el-dropdown>
            </template>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="page1.total"
          :total="page1.total"
          :page.sync="page1.pageNum"
          :limit.sync="page1.pageSize"
          @pagination="getQueue"
        />
      </div>
    </el-card>
    <el-dialog :visible.sync="modal1" width="1000px">
      <el-table height="500" class="mt14" :data="data2" v-loading="loading2">
        <el-table-column
          :label="item.title"
          :min-width="item.minWidth || 100"
          v-for="(item, index) in columns4"
          :key="index"
        >
          <template slot-scope="scope">
            <template v-if="item.key">
              <div>
                <span>{{ scope.row[item.key] }}</span>
              </div>
            </template>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="page2.total"
          :total="page2.total"
          :page.sync="page2.pageNum"
          :limit.sync="page2.pageSize"
          @pagination="getDeliveryLog"
        />
      </div>
    </el-dialog>
    <!-- </div> -->
  </el-dialog>
</template>

<script>
import { queueIndex, deliveryLog, queueAgain, queueDel, batchOrderDelivery, stopWrongQueue } from '@/api/order';
import { mapState } from 'vuex';

export default {
  data() {
    return {
      modal: false,
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
      return this.isMobile ? undefined : '75px';
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
    // 搜索-操作时间
    onchangeTime(time) {
      this.timeVal = time || [];
      this.formValidate.data = this.timeVal[0] ? (this.timeVal ? this.timeVal.join('-') : '') : '';
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
              this.$message.error(err.msg);
            });
          break;
        // 重新执行
        case '2':
          this.queueAgain(row.id, row.type);
          break;
        // 停止任务
        case '3':
          this.$msgbox({
            title: '谨慎操作',
            message: '确认停止该任务？',
            showCancelButton: true,
            cancelButtonText: '取消',
            confirmButtonText: '确定',
            iconClass: 'el-icon-warning',
            confirmButtonClass: 'btn-custom-cancel',
          })
            .then(() => {
              this.stopQueue(row.id);
            })
            .catch(() => {});
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
          this.$message.success(res.msg);
          this.getQueue();
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    // 清除异常任务
    queueDel(id, type) {
      queueDel(id, type)
        .then((res) => {
          this.$message.success(res.msg);
          this.getQueue();
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    // 停止任务
    stopQueue(id) {
      stopWrongQueue(id)
        .then((res) => {
          this.$message.success(res.msg);
          this.getQueue();
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
  },
};
</script>

<style></style>
