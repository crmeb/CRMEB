<template>
  <div class="customer">
    <el-form ref="formValidate" :model="formValidate" label-width="80px" inline @submit.native.prevent>
      <el-form-item label="搜索日期：">
        <el-date-picker
          :editable="false"
          @change="onchangeTime"
          v-model="timeVal"
          value-format="yyyy/MM/dd"
          type="daterange"
          placement="bottom-end"
          range-separator="-"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
          style="width: 250px"
        ></el-date-picker>
      </el-form-item>
      <el-form-item label="用户名称：">
        <el-input
          clearable
          placeholder="请输入用户名称"
          v-model="formValidate.nickname"
          class="form_content_width"
        ></el-input>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="userSearchs">查询</el-button>
      </el-form-item>
    </el-form>
    <el-table
      class="mt15"
      v-loading="loading2"
      highlight-current-row
      no-userFrom-text="暂无数据"
      no-filtered-userFrom-text="暂无筛选结果"
      ref="selection"
      :data="tableList2"
      height="450"
    >
      <el-table-column width="50">
        <template slot-scope="scope">
          <el-radio
            v-model="currentid"
            :disabled="!!scope.row.is_del"
            :label="scope.row.uid"
            @input="() => currentidRadio(scope.row)"
            >&nbsp;</el-radio
          >
        </template>
      </el-table-column>
      <el-table-column label="ID" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.uid }}</span>
        </template>
      </el-table-column>
      <el-table-column label="微信用户名称" min-width="180">
        <template slot-scope="scope">
          <div>{{ scope.row.nickname }}</div>
          <div style="color: red">{{ scope.row.is_del ? '用户已注销' : '' }}</div>
        </template>
      </el-table-column>
      <el-table-column label="客服头像" min-width="90">
        <template slot-scope="scope">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="scope.row.headimgurl" />
          </div>
        </template>
      </el-table-column>

      <el-table-column label="用户类型" min-width="130">
        <template slot-scope="scope">
          <span v-if="scope.row.user_type === 'wechat'">公众号</span>
          <span v-else-if="scope.row.user_type === 'routine'">小程序</span>
          <span v-else-if="scope.row.user_type === 'h5'">H5</span>
          <span v-else-if="scope.row.user_type === 'pc'">PC</span>
          <span v-else>--</span>
        </template>
      </el-table-column>
      <el-table-column label="性别" min-width="130">
        <template slot-scope="scope">
          <span v-show="scope.row.sex === 1">男</span>
          <span v-show="scope.row.sex === 2">女</span>
          <span v-show="scope.row.sex === 0">保密</span>
          <span v-show="scope.row.sex === null">--</span>
        </template>
      </el-table-column>
      <!--      <el-table-column label="地区" min-width="130">-->
      <!--        <template slot-scope="scope">-->
      <!--          <span v-if="scope.row.country || scope.row.province || scope.row.city">{{-->
      <!--            scope.row.country + scope.row.province + scope.row.city-->
      <!--          }}</span>-->
      <!--          <span v-else>&#45;&#45;</span>-->
      <!--        </template>-->
      <!--      </el-table-column>-->
      <el-table-column label="是否关注公众号" min-width="130">
        <template slot-scope="scope">
          <span v-text="scope.row.subscribe === 1 ? '关注' : '未关注'"></span>
        </template>
      </el-table-column>
    </el-table>
    <div class="acea-row row-right page">
      <pagination
        v-if="total2"
        :total="total2"
        :page.sync="formValidate.page"
        :limit.sync="formValidate.limit"
        @pagination="getListService"
      />
    </div>
  </div>
</template>
<script>
import { kefucreateApi } from '@/api/setting';
export default {
  name: 'index',
  data() {
    return {
      formValidate: {
        page: 1,
        limit: 15,
        data: '',
        nickname: '',
      },
      tableList2: [],
      timeVal: [],
      fromList: {
        title: '选择时间',
        custom: true,
        fromTxt: [
          { text: '全部', val: '' },
          { text: '今天', val: 'today' },
          { text: '昨天', val: 'yesterday' },
          { text: '最近7天', val: 'lately7' },
          { text: '最近30天', val: 'lately30' },
          { text: '本月', val: 'month' },
          { text: '本年', val: 'year' },
        ],
      },
      currentid: 0,
      productRow: {},
      loading2: false,
      total2: 0,
    };
  },
  created() {},
  mounted() {
    this.getListService();
  },
  methods: {
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.data = this.timeVal ? this.timeVal.join('-') : '';
      this.getListService();
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.data = tab;
      this.timeVal = [];
      this.getListService();
    },
    // 客服列表
    getListService() {
      this.loading2 = true;
      kefucreateApi(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tableList2 = data.list;
          this.total2 = data.count;
          this.tableList2.map((item) => {
            item._isChecked = false;
          });
          this.loading2 = false;
        })
        .catch((res) => {
          this.loading2 = false;
          this.$message.error(res.msg);
        });
    },
    // 搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getListService();
    },
    currentidRadio(row) {
      self.currentid = row.uid;
      this.productRow = row;
      if (this.productRow.uid) {
        if (this.$route.query.fodder === 'image') {
          /* eslint-disable */
          let imageObject = {
            image: this.productRow.headimgurl,
            uid: this.productRow.uid,
          };
          form_create_helper.set('image', imageObject);
          form_create_helper.close('image');
        } else {
          this.$emit('imageObject', {
            image: this.productRow.headimgurl,
            uid: this.productRow.uid,
          });
        }
      } else {
        this.$message.warning('请先选择商品');
      }
    },
  },
};
</script>

<style scoped lang="stylus">
.customer{
  height 100%
  background-color: #fff;
}
.tabBox_img
    width 36px
    height 36px
    border-radius:4px;
    cursor pointer
    img
        width 100%
        height 100%
.modelBox
    >>>
    .ivu-table-header
        width 100% !important
.trees-coadd
    width: 100%;
    height: 385px;
    .scollhide
        width: 100%;
        height: 100%;
        overflow-x: hidden;
        overflow-y: scroll;
.scollhide::-webkit-scrollbar {
    display: none;
}
.footer{
    margin: 15px 0;
    padding-right: 20px;
}
/deep/ .el-form--inline .el-form-item{
  margin-bottom 0
}
</style>
