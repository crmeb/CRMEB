<template>
  <Card :bordered="false" dis-hover class="ivu-mt">
    <div class="acea-row row-between-wrapper mb20">
      <div class="header-title">商品排行</div>
      <div class="acea-row">
        <Select v-model="formValidate.sort" style="width: 200px" class="mr20" @on-change="changeMenu">
          <Option v-for="item in list" :value="item.val" :key="item.val">{{ item.name }}</Option>
        </Select>
        <DatePicker
          :editable="false"
          :clearable="false"
          @on-change="onchangeTime"
          :value="timeVal"
          format="yyyy/MM/dd"
          type="datetimerange"
          placement="bottom-start"
          placeholder="请选择时间"
          style="width: 200px"
          :options="options"
          class="mr20"
        ></DatePicker>
        <Button type="primary" class="mr20" @click="getList">查询</Button>
      </div>
    </div>
    <Table
      ref="selection"
      :columns="columns4"
      :data="tabList"
      :loading="loading"
      no-data-text="暂无数据"
      highlight-row
      no-filtered-data-text="暂无筛选结果"
    >
      <template slot-scope="{ row, index }" slot="image">
        <div class="tabBox_img" v-viewer>
          <img v-lazy="row.image" />
        </div>
      </template>
      <template slot-scope="{ row, index }" slot="profit">
        <span v-text="$tools.accMul(row.profit, 100).toFixed(2) + '%'"></span>
      </template>
      <template slot-scope="{ row, index }" slot="repeats">
        <span v-text="$tools.accMul(row.repeats, 100) + '%'"></span>
      </template>
      <template slot-scope="{ row, index }" slot="changes">
        <span>{{ $tools.accMul(row.changes, 100) + '%' }}</span>
      </template>
      <template slot-scope="{ row, index }" slot="action">
        <a @click="look(row)">查看</a>
      </template>
    </Table>
    <!-- 商品弹窗 -->
    <div v-if="isProductBox">
      <div class="bg" @click="isProductBox = false"></div>
      <goodsDetail :goodsId="goodsId"></goodsDetail>
    </div>
  </Card>
</template>

<script>
import { statisticProductListApi } from '@/api/statistic';
import goodsDetail from '../components/goodsDetail';
import { formatDate } from '@/utils/validate';
export default {
  name: 'productRanking',
  components: {
    goodsDetail,
  },
  data() {
    return {
      validateFun: this.$validateFun,
      options: this.$timeOptions,
      name: '近30天',
      timeVal: [],
      dataTime: '',
      formValidate: {
        limit: 10,
        page: 1,
        sort: 'visit',
        data: '',
      },
      loading: false,
      tabList: [],
      total: 0,
      columns4: [
        {
          title: '商品图片',
          slot: 'image',
          minWidth: 80,
        },
        {
          title: '商品名称',
          width: 180,
          key: 'store_name',
        },
        {
          title: '浏览量',
          key: 'visit',
          minWidth: 100,
        },
        {
          title: '访客数',
          key: 'user',
          minWidth: 100,
        },
        {
          title: '加购件数',
          key: 'cart',
          minWidth: 100,
        },
        {
          title: '下单件数',
          key: 'orders',
          minWidth: 100,
        },
        {
          title: '支付件数',
          key: 'pay',
          minWidth: 100,
        },
        {
          title: '支付金额',
          key: 'price',
          minWidth: 100,
        },
        {
          title: '毛利率(%)',
          slot: 'profit',
          minWidth: 100,
        },
        {
          title: '收藏数',
          key: 'collect',
          minWidth: 100,
        },
        {
          title: '访客-支付转化率(%)',
          slot: 'changes',
          minWidth: 120,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 80,
        },
      ],
      goodsId: '',
      isProductBox: false,
      list: [
        {
          val: 'visit',
          name: '浏览量',
        },
        {
          val: 'user',
          name: '访客数',
        },
        {
          val: 'cart',
          name: '加购件数',
        },
        {
          val: 'orders',
          name: '下单件数',
        },
        {
          val: 'price',
          name: '支付金额',
        },
        {
          val: 'profit',
          name: '毛利率',
        },
        {
          val: 'collect',
          name: '收藏数',
        },
        {
          val: 'changes',
          name: '访客-支付转化率',
        },
      ],
    };
  },
  created() {
    const end = new Date();
    const start = new Date();
    start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)));
    this.timeVal = [start, end];
    this.formValidate.data = formatDate(start, 'yyyy/MM/dd') + '-' + formatDate(end, 'yyyy/MM/dd');
  },
  mounted() {
    this.getList();
  },
  methods: {
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.data = this.timeVal.join('-');
      this.name = this.formValidate.data;
    },
    changeMenu(name) {
      this.formValidate.sort = name;
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      statisticProductListApi(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tabList = data;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    look(row) {
      this.goodsId = row.product_id;
      this.isProductBox = true;
    },
  },
};
</script>

<style scoped lang="less">
.header {
  &-title {
    font-size: 16px;
    color: rgba(0, 0, 0, 0.85);
  }
  &-time {
    font-size: 12px;
    color: #000000;
    opacity: 0.45;
  }
}
</style>
<style scoped lang="stylus">
.bg
    position fixed
    left 0
    top 0
    width 100%
    height 100%
    background rgba(0,0,0,0.5)
    z-index: 11;
/deep/.happy-scroll-content
    width 100%
    .demo-spin-icon-load{
        animation: ani-demo-spin 1s linear infinite;
    }
    @keyframes ani-demo-spin {
        from { transform: rotate(0deg);}
        50%  { transform: rotate(180deg);}
        to   { transform: rotate(360deg);}
    }
    .demo-spin-col{
        height: 100px;
        position: relative;
        border: 1px solid #eee;
    }
</style>
