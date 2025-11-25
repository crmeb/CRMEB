<template>
  <div>
    <el-form ref="orderData" label-width="85px" label-position="right" class="tabform">
      <el-row :gutter="24" v-for="(item, index) in fromList" :key="index">
        <el-col :xl="8" :lg="8" :md="8" :sm="24" :xs="24">
          <el-form-item :label="item.title + '：'">
            <el-radio-group type="button" v-model="date">
              <el-radio-button :label="itemn.text" v-for="(itemn, indexn) in item.fromTxt" :key="indexn"
                >{{ itemn.text }}{{ item.num }}</el-radio-button
              >
            </el-radio-group>
          </el-form-item>
        </el-col>
        <el-col v-if="item.custom">
          <el-form-item class="tab_data">
            <el-date-picker
              :editable="false"
              value-format="yyyy/MM/dd"
              type="daterange"
              range-separator="-"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              style="width: 200px"
            ></el-date-picker>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row :gutter="24" v-if="isExist.existOne">
        <el-col span="10" class="mr">
          <el-form-item :label="searchFrom.title + '：'" prop="real_name" label-for="real_name">
            <el-input search enter-button :placeholder="searchFrom.place" element-id="name" />
          </el-form-item>
        </el-col>
        <el-col>
          <el-button class="mr">导出</el-button>
          <span class="Refresh">刷新</span>
        </el-col>
      </el-row>
      <el-row :gutter="24" class="withdrawal" v-if="isExist.existTwo">
        <el-col span="2.5" class="item">
          <TreeSelect v-model="withdrawalTxt" :data="treeData.withdrawal" class="perW160" @change="changeTree" />
        </el-col>
        <el-col span="2.5" class="item">
          <TreeSelect v-model="paymentTxt" :data="treeData.payment" class="perW160" @change="changeTree" />
        </el-col>
        <el-col :span="6" class="item">
          <el-input search enter-button placeholder="微信名称、姓名、支付宝账号、银行卡号" element-id="name" />
        </el-col>
      </el-row>
    </el-form>
  </div>
</template>
<style lang="scss" scoped>
.Refresh {
  font-size: 12px;
  color: var(--prev-color-primary);
  cursor: pointer;
}
.ivu-form-item {
  margin-bottom: 10px;
}
.tabform ::v-deep .ivu-col {
  padding: 0 !important;
}
.tabform ::v-deep .ivu-row-flex {
  margin: 0 !important;
}
.withdrawal ::v-deep .item {
  margin-right: 10px;
}
.tab_data ::v-deep .ivu-form-item-content {
  margin-left: 10px !important;
}
.ivu-form-label-left ::v-deep .ivu-form-item-label {
  text-align: right;
}
</style>
<script>
import { mapState } from 'vuex';
export default {
  name: 'publicSearchFrom',
  props: {
    fromList: {
      type: Array,
    },
    searchFrom: {
      type: Object,
    },
    treeData: {
      type: Object,
    },
    isExist: {
      type: Object,
    },
  },
  data() {
    return {
      date: '全部',
      withdrawalTxt: '提现状态',
      paymentTxt: '提现方式',
    };
  },
  computed: {},
  mounted() {},
  methods: {
    changeTree() {},
  },
};
</script>
