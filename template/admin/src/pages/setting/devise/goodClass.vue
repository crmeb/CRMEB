<template>
  <div class="goodClass">
    <Card :bordered="false" dis-hover>
      <!-- <div class="title">页面设置</div> -->
      <div class="list acea-row row-top">
        <div
          class="item"
          :class="activeStyle == index ? 'on' : ''"
          v-for="(item, index) in classList"
          :key="index"
          @click="selectTap(index)"
        >
          <div class="pictrue"><img :src="item.image" /></div>
          <div class="name">{{ item.name }}</div>
        </div>
      </div>
    </Card>
  </div>
</template>

<script>
import { colorChange, getColorChange } from '@/api/diy';
export default {
  name: 'goodClass',
  props: {},
  data() {
    return {
      classList: [
        { image: require('@/assets/images/sort01.jpg'), name: '样式1' },
        { image: require('@/assets/images/sort02.jpg'), name: '样式2' },
        { image: require('@/assets/images/sort03.jpg'), name: '样式3' },
      ],
      activeStyle: '-1',
    };
  },
  created() {
    this.getInfo();
  },
  methods: {
    getInfo() {
      getColorChange('category').then((res) => {
        this.activeStyle = res.data.status ? res.data.status - 1 : 0;
      });
    },
    selectTap(index) {
      this.activeStyle = index;
    },
    onSubmit(num) {
      this.$emit('parentFun', true);
      this.activeStyle = num == 1 ? 0 : this.activeStyle;
      colorChange(num == 1 ? 1 : this.activeStyle + 1, 'category')
        .then((res) => {
          this.$emit('parentFun', false);
          this.$Message.success(res.msg);
        })
        .catch((err) => {
          this.$Message.error(err.msg);
          this.$emit('parentFun', false);
        });
    },
  },
};
</script>
<style scoped lang="stylus">
.goodClass{
    .title{
        font-size 14px;
        color: rgba(0, 0, 0, 0.85);
        position relative;
        padding-left 11px;
        font-weight bold;
        &:after{
            position absolute;
            content ' ';
            width 2px;
            height 14px;
            background-color #1890FF;
            left:0;
            top:3px;
        }
    }
    .list{
        .item{
            width 264px;
            margin 20px 30px 0 0;
            cursor pointer;
            .pictrue{
                width 100%;
                height 496px;
                border: 1px solid #EEEEEE;
                border-radius: 10px;
                img{
                    width 100%
                    height 100%
                    border-radius: 10px;
                }
            }
            .name{
                font-size: 13px;
                color: rgba(0, 0, 0, 0.85);
                margin-top 26px;
                text-align center;
            }
            &.on{
                .pictrue{
                    border: 2px solid #1890FF;
                }
                .name{
                    color #1890FF;
                }
            }
        }
    }
}
</style>
