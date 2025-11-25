<template>
  <div>
    <div class="list-wrapper">
      <div class="user-item" v-for="(item, index) in labelLists" :key="index" v-db-click @click="bindActive(item)">
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
          this.$message.success(res.msg);
          this.$emit('close');
        })
        .catch((error) => {
          this.$message.error(error.msg);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.list-wrapper {
  .user-item {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    cursor: pointer;
    &:last-child {
      margin-bottom: 0;
    }
    img {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      margin-right: 8px;
    }
    p {
      width: 80%;
      color: #333;
      font-size: 13px;
    }
  }
}
</style>
