<template>
    <div>
        <Modal v-model="modals" :z-index="100" scrollable  footer-hide closable title="发送优惠券" :mask-closable="false" width="900">
            <div class="acea-row">
                <span class="sp">优惠券名称：</span><Input v-model="page.title" search enter-button placeholder="请输入优惠券名称" style="width: 60%;"  @on-search="userSearchs"/>
            </div>
            <Table  :columns="columns" :data="couponList" ref="table" class="mt25"
                    :loading="loading" highlight-row
                    no-userFrom-text="暂无数据"
                    no-filtered-userFrom-text="暂无筛选结果">
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="sendGrant(row,'发送优惠券',index)">发送</a>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" show-elevator show-total   @on-change="pageChange"
                      :page-size="page.limit"  /></div>
        </Modal>
    </div>
</template>

<script>
import { couponApi } from '@/api/user'
export default {
  name: 'send',
  props: {
    userIds: {
      type: String,
      default: ''
    }
  },
  data () {
    return {
      modals: false,
      loading: false,
      couponList: [],
      columns: [
        {
          title: '优惠券名称',
          key: 'title',
          align: 'center',
          minWidth: 100
        },
        {
          title: '优惠券面值',
          key: 'coupon_price',
          align: 'center',
          minWidth: 80
        },
        {
          title: '优惠券最低消费',
          key: 'use_min_price',
          align: 'center',
          minWidth: 150
        },
        {
          title: '优惠券有效期限',
          key: 'coupon_time',
          align: 'center',
          minWidth: 120
        },
        {
          title: '操作',
          slot: 'action',
          align: 'center',
          width: 120
        }
      ],
      page: {
        page: 1, // 当前页
        limit: 15,
        title: ''
      },
      total: 0 // 总条数
    }
  },
  methods: {
    // 优惠券列表
    getList (id) {
      this.loading = true
      couponApi(this.page).then(async res => {
        if (res.status === 200) {
          let data = res.data
          this.couponList = data.list
          this.total = data.count
          this.loading = false
        } else {
          this.loading = false
          this.$Message.error(res.msg)
        }
      }).catch(res => {
        this.loading = false
        this.$Message.error(res.msg)
      })
    },
    // 表格搜索
    userSearchs () {
      this.getList()
    },
    pageChange (index) {
      this.page.page = index
      this.getList()
    },
    // 发送
    sendGrant (row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/coupon/user/grant`,
        method: 'post',
        ids: {
          id: row.id,
          uid: this.userIds
        }
      }
      this.$modalSure(delfromData).then((res) => {
        this.$Message.success(res.msg)
      }).catch(res => {
        this.$Message.error(res.msg)
      })
    }
  }
}
</script>

<style scoped lang="stylus">
    .sp
        line-height 32px

</style>
