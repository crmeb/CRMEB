<template>
  <div>
    <Modal v-model="isTemplate" title="优惠券列表" width="60%" @on-ok="ok" @on-cancel="cancel">
      <Table
        :columns="columns"
        :data="couponList"
        ref="table"
        class="mt25"
        :loading="loading"
        highlight-row
        @on-select="handleSelectRow"
        @on-select-cancel="handleCancelRow"
        @on-select-all="handleSelectAll"
        @on-select-all-cancel="handleSelectAll"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="count">
          <span v-if="row.is_permanent">不限量</span>
          <div v-else>
            <span class="fa">发布：{{ row.total_count }}</span>
            <span class="sheng">剩余：{{ row.remain_count }}</span>
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="start_time">
          <div v-if="row.start_time">{{ row.start_time | formatDate }} - {{ row.end_time | formatDate }}</div>
          <span v-else>不限时</span>
        </template>
        <template slot-scope="{ row }" slot="type">
          <span v-if="row.type === 1">品类券</span>
          <span v-else-if="row.type === 2">商品券</span>
          <span v-else-if="row.type === 3">会员券</span>
          <span v-else>通用券</span>
        </template>
        <template slot-scope="{ row, index }" slot="status">
          <Tag color="blue" v-show="row.status === 1">正常</Tag>
          <Tag color="gold" v-show="row.status === 0">未开启</Tag>
          <Tag color="red" v-show="row.status === -1">已失效</Tag>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page :total="total" show-elevator show-total @on-change="receivePageChange" :page-size="tableFrom.limit" />
      </div>
    </Modal>
  </div>
</template>

<script>
import { releasedListApi } from '@/api/marketing';
import { formatDate } from '@/utils/validate';

export default {
  name: 'index',
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  props: {
    couponids: {
      type: Array,
    },
    updateIds: {
      type: Array,
    },
    updateName: {
      type: Array,
    },
    luckDraw: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      currentid: 0,
      productRow: {},
      isTemplate: false,
      loading: false,
      tableFrom: {
        receive_type: 1,
        page: 1,
        limit: 10,
      },
      total: 0,
      ids: [],
      texts: [],
      columns: [
        {
          title: 'ID',
          key: 'id',
          width: 60,
        },
        {
          title: '优惠券名称',
          key: 'title',
          minWidth: 150,
        },
        {
          title: '优惠券类型',
          slot: 'type',
          minWidth: 80,
        },
        {
          title: '面值',
          key: 'coupon_price',
          minWidth: 100,
        },
        {
          title: '最低消费额',
          key: 'use_min_price',
          minWidth: 100,
        },
        {
          title: '发布数量',
          slot: 'count',
          minWidth: 120,
        },
        {
          title: '有效期限',
          slot: 'start_time',
          minWidth: 120,
        },
        {
          title: '状态',
          slot: 'status',
          minWidth: 80,
        },
      ],
      couponList: [],
      selectedIds: new Set(),
      selectedNames: new Set(),
    };
  },
  mounted() {},
  watch: {
    updateIds: function (newVal) {
      this.selectedIds = new Set(newVal);
    },
    updateName: function (newVal) {
      this.selectedNames = new Set(newVal);
    },
  },
  created() {
    let radio = {
      width: 60,
      align: 'center',
      render: (h, params) => {
        let id = params.row.id;
        let flag = false;
        if (this.currentid === id) {
          flag = true;
        } else {
          flag = false;
        }
        let self = this;
        return h('div', [
          h('Radio', {
            props: {
              value: flag,
            },
            on: {
              'on-change': () => {
                self.currentid = id;
                this.productRow = params.row;
              },
            },
          }),
        ]);
      },
    };

    let checkbox = {
      type: 'selection',
      width: 60,
      align: 'center',
    };
    if (this.luckDraw) {
      this.columns.unshift(radio);
    } else {
      this.columns.unshift(checkbox);
    }
  },
  methods: {
    //对象数组去重；
    unique(arr) {
      const res = new Map();
      return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
    },
    handleSelectAll(selection) {
      // 取消全选 数组为空
      if (selection.length === 0) {
        // cy 若取消全选，删除保存在selectedIds里和当前table数据的id一致的数据，达到，当前页取消全选的效果
        // 当前页的table数据
        let that = this;
        let data = that.$refs.table.data;
        data.forEach((item) => {
          if (that.selectedIds.has(item.id)) {
            that.selectedIds.delete(item.id);
            let nameList = that.unique(Array.from(that.selectedNames));
            that.unique(Array.from(that.selectedNames)).forEach((j, index) => {
              if (j.id === item.id) {
                nameList.splice(index, 1);
              }
            });
            that.selectedNames = new Set(nameList);
            // this.selectedNames.clear();
          }
        });
      } else {
        selection.forEach((item) => {
          this.selectedIds.add(item.id);
          this.selectedNames.add({ id: item.id, title: item.title });
        });
      }
      this.$nextTick(() => {
        //确保dom加载完毕
        this.setChecked();
      });
    },

    //  选中某一行
    handleSelectRow(selection, row) {
      this.selectedIds.add(row.id);
      this.selectedNames.add({ id: row.id, title: row.title });
      this.$nextTick(() => {
        //确保dom加载完毕
        this.setChecked();
      });
    },
    //  取消某一行
    handleCancelRow(selection, row) {
      let that = this;
      that.selectedIds.delete(row.id);
      let nameList = Array.from(that.selectedNames);
      Array.from(that.selectedNames).forEach((item, index) => {
        if (item.id === row.id) {
          nameList.splice(index, 1);
        }
      });
      that.selectedNames = new Set(nameList);
      this.$nextTick(() => {
        //确保dom加载完毕
        this.setChecked();
      });
    },
    setChecked() {
      this.ids = [...this.selectedIds];
      this.texts = [...this.selectedNames];
      // 找到绑定的table的ref对应的dom，找到table的objData对象，objData保存的是当前页的数据
      let objData = this.$refs.table.objData;
      for (let index in objData) {
        if (this.selectedIds.has(objData[index].id)) {
          // cy 弊端 每次切换select都会触发table的on-select事件
          // this.$refs.purchaseTable.toggleSelect(index) // 在保存选中的ids的set合集里找与当前页数据id一样的行，使用toggleSelect（index），将这一行选中
          // cy 改进
          objData[index]._isChecked = true;
        }
      }
    },
    cancel() {
      this.isTemplate = false;
      if (this.luckDraw) {
        this.currentid = 0;
      }
    },
    tableList() {
      this.loading = true;
      releasedListApi(this.tableFrom).then((res) => {
        let data = res.data;
        this.couponList = data.list;
        this.$nextTick(() => {
          //确保dom加载完毕
          this.setChecked();
        });
        this.total = data.count;
        this.loading = false;
      });
    },
    ok() {
      if (this.luckDraw) {
        this.$emit('getCouponId', this.productRow);
        this.currentid = 0;
      } else {
        this.$emit('nameId', this.ids, this.texts);
      }
    },
    receivePageChange(index) {
      this.tableFrom.page = index;
      this.tableList();
    },
  },
};
</script>

<style scoped></style>
