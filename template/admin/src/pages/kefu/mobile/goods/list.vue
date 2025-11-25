<template>
  <div class="product_info">
    <div class="head">
      <div class="tab-box">
        <div
          class="tab-item"
          :class="{ on: index == tabCur }"
          v-for="(item, index) in tabList"
          :key="index"
          v-db-click
          @click="bindTab(item)"
        >
          {{ item.title }}
        </div>
      </div>
      <div class="search-box">
        <el-input
          type="text"
          placeholder="搜索商品名称/ID"
          v-model="searchTxt"
          style="border-radius: 0.39rem; background: #f5f6f9"
          :search="true"
          @on-search="bindSearch"
        />
      </div>
    </div>
    <div class="scroll-box" v-if="list.length > 0">
      <vue-scroll :ops="ops">
        <div class="goods-item" v-for="(item, index) in list" :key="index">
          <img :src="item.image" mode="" />
          <div class="info">
            <div class="title line2 mb15">{{ item.store_name }}</div>
            <div class="num">
              <span class="mr15">库存 {{ item.stock }}</span>
              <span>销量 {{ item.sales }}</span>
            </div>
          </div>
          <div class="right">
            <div class="price">￥{{ item.price }}</div>
            <div class="btn" v-db-click @click="bingGoods(item)">推送</div>
          </div>
        </div>
        <div class="slot-load" slot="load-deactive"></div>
        <div class="slot-load" slot="load-active">下滑加载更多</div>
      </vue-scroll>
    </div>
    <empty v-else msg="暂无商品信息"></empty>
  </div>
</template>

<script>
import { Socket } from '@/libs/socket';
import { productCart, productHot, productVisit } from '@/api/kefu.js';
import { serviceInfo } from '@/api/kefu_mobile';
import empty from '../../components/empty';
export default {
  name: 'product_info',
  components: {
    empty,
  },
  data() {
    return {
      ops: {
        bar: {
          background: '#393232',
          opacity: '.5',
          size: '2px',
        },
      },
      searchTxt: '',
      tabCur: 0,
      tabList: [
        {
          key: 0,
          title: '购买',
          api: 'productCart',
        },
        {
          key: 1,
          title: '足迹',
          api: 'productHot',
        },
        {
          key: 2,
          title: '热销',
          api: 'productVisit',
        },
      ],
      toUid: '',
      list: [],
    };
  },
  watch: {
    tabCur(nVal, oVal) {
      this.list = [];
      if (nVal == 0) return this.getBuyList();
      if (nVal == 1) return this.getVisit();
      if (nVal == 2) return this.getProductHot();
    },
  },
  created() {
    serviceInfo().then((res) => {
      window.document.title = `${res.data.site_name} - 商品列表`;
    });
    this.toUid = this.$route.query.toUid;
    this.getBuyList();
  },
  methods: {
    // 购买记录
    getBuyList() {
      productCart(this.toUid, {
        store_name: this.searchTxt,
      }).then((res) => {
        this.list = res.data;
      });
    },
    // 热销商品
    getProductHot() {
      productHot(this.toUid, {
        store_name: this.searchTxt,
      }).then((res) => {
        this.list = res.data;
      });
    },
    // 足迹
    getVisit() {
      productVisit(this.toUid, {
        store_name: this.searchTxt,
      }).then((res) => {
        this.list = res.data;
      });
    },
    // 推送
    bingGoods(item) {
      let obj = {
        type: 'chat',
        data: {
          msn: item.id,
          type: 5,
          to_uid: this.toUid,
        },
      };
      Socket.then((ws) => {
        ws.send(obj);
      });
      // this.bus.$emit('selectGoods',item)
      this.$router.go(-1);
    },
    // 顶部选项卡切换
    bindTab(item) {
      this.tabCur = item.key;
    },
    // 搜索
    bindSearch() {
      if (this.tabCur == 0) return this.getBuyList();
      if (this.tabCur == 1) return this.getVisit();
      if (this.tabCur == 2) return this.getProductHot();
    },
  },
};
</script>
<style>
page {
  height: 100%;
}
</style>
<style lang="scss" scoped>
.product_info {
  display: flex;
  flex-direction: column;
  height: 100vh;
  .head {
    background: #fff;
    .tab-box {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 0.8rem;
      padding: 0 1.46rem;
      .tab-item {
        height: 0.8rem;
        line-height: 0.8rem;
        padding: 0 0.15rem;
        font-size: 0.28rem;
        color: #282828;
        &.on {
          border-bottom: 1px solid #3875ea;
        }
      }
    }
    .search-box {
      display: flex;
      align-items: center;
      height: 1.28rem;
      padding: 0 0.3rem;
      input {
        display: block;
        width: 100%;
        height: 0.68rem;
        padding-left: 0.7rem;
        background: #f5f6f9;
        border-radius: 0.39rem;
        box-sizing: border-box;
        /*background-image: url("../static/search.png");*/
      }
    }
  }
  .scroll-box {
    flex: 1;
    overflow: hidden;
    .goods-item {
      display: flex;
      padding: 0.3rem;
      margin-top: 0.15rem;
      background-color: #fff;
      img {
        width: 1.7rem;
        height: 1.7rem;
        border-radius: 0.06rem;
      }
      .info {
        width: 3.26rem;
        margin-left: 0.22rem;
        .title {
          font-size: 0.28rem;
          color: #282828;
        }
        .num {
          margin-top: 0.1rem;
          font-size: 0.24rem;
          color: #9f9f9f;
        }
      }
      .right {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        margin-left: 0.36rem;
        .price {
          color: #f74c31;
        }
        .btn {
          width: 100%;
          height: 0.6rem;
          line-height: 0.6rem;
          text-align: center;
          color: #fff;
          background: #3875ea;
          border-radius: 0.06rem;
        }
      }
    }
  }
}
</style>
