<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{padding:0}">
      <div class="padding-add">
        <el-form
            ref="formValidate"
            :model="formValidate"
            :label-width="labelWidth"
            :label-position="labelPosition"
            inline
            @submit.native.prevent
            class="tabform"
        >
          <el-form-item label="会员类型：">
            <el-select v-model="formValidate.member_type" clearable @change="userSearchs" class="form_content_width">
              <el-option v-for="item in treeSelect" :value="item.id" :key="item.id" :label="item.label"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="支付方式：">
            <el-select v-model="formValidate.pay_type" clearable @change="paySearchs" class="form_content_width">
              <el-option v-for="item in payList" :value="item.val" :key="item.val" :label="item.label"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="购买时间：">
            <el-date-picker
                clearable
                v-model="timeVal"
                type="daterange"
                :editable="false"
                @change="onchangeTime"
                format="yyyy/MM/dd"
                value-format="yyyy/MM/dd"
                start-placeholder="开始日期"
                end-placeholder="结束日期"
                :picker-options="pickerOptions"
                style="width: 250px"
            ></el-date-picker>
          </el-form-item>
          <el-form-item label="搜索：">
            <el-input
                clearable
                placeholder="请输入用户名称搜索"
                v-model="formValidate.name"
                class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-table
        :data="tbody"
        ref="table"
        v-loading="loading"
        size="small"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="订单号" width="170">
          <template slot-scope="scope">
            <span>{{ scope.row.order_id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户名" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.user.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="手机号码" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.user.phone || '--' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="会员类型" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.member_type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="有效期限（天）" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.vip_day === -1 ? '永久' : scope.row.vip_day }}</span>
          </template>
        </el-table-column>
        <el-table-column label="支付金额（元）" min-width="50">
          <template slot-scope="scope">
            <span>{{ scope.row.pay_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="支付方式" min-width="30">
          <template slot-scope="scope">
            <span>{{ scope.row.pay_type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="购买时间" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.pay_time }}</span>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="tablePage.page"
          :limit.sync="tablePage.limit"
          @pagination="getMemberRecord"
        />
      </div>
    </el-card>
  </div>
</template>

<script>
import { userMemberCard, memberRecord } from '@/api/user';
import { mapState } from 'vuex';

export default {
  name: 'card',
  data() {
    return {
      treeSelect: [
        {
          id: 'free',
          label: '试用',
        },
        {
          id: 'card',
          label: '卡密',
        },
        {
          id: 'month',
          label: '月卡',
        },
        {
          id: 'quarter',
          label: '季卡',
        },
        {
          id: 'year',
          label: '年卡',
        },
        {
          id: 'ever',
          label: '永久',
        },
      ],
      payList: [
        {
          val: 'free',
          label: '免费',
        },
        {
          val: 'weixin',
          label: '微信',
        },
        {
          val: 'alipay',
          label: '支付宝',
        },
      ],
      tbody: [],
      loading: false,
      total: 0,
      formValidate: {
        name: '',
        member_type: '',
        pay_type: '',
        add_time: '',
      },
      pickerOptions: this.$timeOptions,
      timeVal: [],
      tablePage: {
        page: 1,
        limit: 15,
      },
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  created() {
    this.getMemberRecord();
  },
  methods: {
    // 用户名搜索；
    selChange() {
      this.tablePage.page = 1;
      this.getMemberRecord();
    },
    //用户类型搜索；
    userSearchs() {
      this.tablePage.page = 1;
      this.getMemberRecord();
    },
    //支付方式搜索；
    paySearchs() {
      this.tablePage.page = 1;
      this.getMemberRecord();
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e || [];
      this.formValidate.add_time = this.timeVal[0] ? this.timeVal ? this.timeVal.join('-') : '' : '';
      this.tablePage.page = 1;
      this.getMemberRecord();
    },
    getMemberRecord() {
      this.loading = true;
      let data = {
        page: this.tablePage.page,
        limit: this.tablePage.limit,
        member_type: this.formValidate.member_type,
        pay_type: this.formValidate.pay_type,
        add_time: this.formValidate.add_time,
        name: this.formValidate.name,
      };
      memberRecord(data)
        .then((res) => {
          this.loading = false;
          const { list, count } = res.data;
          this.tbody = list;
          this.total = count;
        })
        .catch((err) => {
          this.loading = false;
          this.$message.error(err.msg);
        });
    },
  },
};
</script>
