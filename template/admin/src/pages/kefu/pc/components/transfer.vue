<template>
  <div>
    <!--        <div class="head">-->
    <!--            客户名称：<Input search enter-button placeholder="请输入名称或者ID" style="width: 40%" @on-search="search" v-model="name" />-->
    <!--        </div>-->
    <!--        <Table :columns="columns1" :data="labelLists" ref="table" class="mt25"-->
    <!--               :loading="loading" highlight-row-->
    <!--               no-userFrom-text="暂无数据"-->
    <!--               no-filtered-userFrom-text="暂无筛选结果">-->
    <!--            <template slot-scope="{ row, index }" slot="avatar">-->
    <!--                <viewer>-->
    <!--                    <div class="tabBox_img">-->
    <!--                        <img v-lazy="row.avatar">-->
    <!--                    </div>-->
    <!--                </viewer>-->
    <!--            </template>-->
    <!--        </Table>-->
    <div class="list-wrapper">
      <div class="user-item" v-for="(item, index) in labelLists" :key="index" @click="bindActive(item)">
        <img v-lazy="item.avatar" alt="" />
        <p class="line1">{{ item.wx_name }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import { transferList, serviceTransfer } from '@/api/kefu';
export default {
  name: 'transfer',
  props: {
    userUid: {
      type: String | Number,
      default: '',
    },
  },
  data() {
    return {
      loading: false,
      currentChoose: '',
      labelLists: [],
      name: '',
    };
  },
  mounted() {
    this.getList();
  },
  methods: {
    getList() {
      transferList({
        nickname: this.name,
        uid: this.userUid,
      }).then((res) => {
        this.labelLists = res.data.list;
      });
    },
    bindActive(item) {
      // this.$emit('transferPeople',item)
      serviceTransfer({
        uid: this.userUid,
        kefuToUid: item.uid,
      })
        .then((res) => {
          this.$Message.success(res.msg);
          this.$emit('close');
        })
        .catch((error) => {
          this.$Message.error(error.msg);
        });
    },
  },
};
</script>

<style lang="stylus" scoped>
.list-wrapper
    .user-item
        display flex
        align-items center
        margin-bottom 12px
        cursor pointer
        &:last-child
            margin-bottom 0
        img
            width 32px
            height 32px
            border-radius 50%
            margin-right 8px
        p
            width 80%
            color #333
            font-size 13px
</style>
