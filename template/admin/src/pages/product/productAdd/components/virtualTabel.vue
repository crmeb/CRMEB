<template>
  <div>
    <slot></slot>
  </div>
</template>

<script>
import throttle from 'lodash/throttle';

export default {
  name: 'el-table-virtual-scroll',
  props: {
    data: {
      type: Array,
      required: true,
    },
    height: {
      type: Number,
      default: 60,
    },
    buffer: {
      type: Number,
      default: 500,
    },
    keyProp: {
      type: String,
      default: 'id',
    },
    throttleTime: {
      type: Number,
      default: 100,
    },
  },
  data() {
    return {
      sizes: {}, // 尺寸映射（依赖响应式）
    };
  },
  computed: {
    // 计算出每个item（的key值）到滚动容器顶部的距离
    offsetMap({ keyProp, height, sizes, data }) {
      const res = {};
      let total = 0;
      for (let i = 0; i < data.length; i++) {
        const key = data[i][keyProp];
        res[key] = total;

        const curSize = sizes[key];
        const size = typeof curSize === 'number' ? curSize : height;
        total += size;
      }
      return res;
    },
  },
  methods: {
    // 初始化数据
    initData() {
      // 可视范围内显示数据
      this.renderData = [];
      // 页面可视范围顶端、底部
      this.top = undefined;
      this.bottom = undefined;
      // 截取页面可视范围内显示数据的开始和结尾索引
      this.start = 0;
      this.end = undefined;

      this.scroller = this.$el.querySelector('.el-table__body-wrapper');

      // 初次执行
      setTimeout(() => {
        this.handleScroll();
      }, 100);

      // 监听事件
      this.onScroll = throttle(this.handleScroll, this.throttleTime);
      this.scroller.addEventListener('scroll', this.handleScroll);
      window.addEventListener('resize', this.onScroll);
    },

    // 更新尺寸（高度）
    updateSizes() {
      const rows = this.$el.querySelectorAll('.el-table__body > tbody > .el-table__row');

      Array.from(rows).forEach((row, index) => {
        const item = this.renderData[index];
        if (!item) return;

        const key = item[this.keyProp];
        const offsetHeight = row.offsetHeight;

        if (this.sizes[key] !== offsetHeight) {
          this.$set(this.sizes, key, offsetHeight);
        }
      });
    },

    // 处理滚动事件
    handleScroll(shouldUpdate = true) {
      // 更新当前尺寸（高度）
      this.updateSizes();
      // 计算renderData
      this.calcRenderData();
      // 计算位置
      this.calcPosition();
      shouldUpdate && this.updatePosition();
      // 触发事件
      this.$emit('change', this.renderData, this.start, this.end);
    },

    // 获取某条数据offsetTop
    getOffsetTop(index) {
      const item = this.data[index];
      if (item) {
        return this.offsetMap[item[this.keyProp]] || 0;
      }
      return 0;
    },

    // 获取某条数据的尺寸
    getSize(index) {
      const item = this.data[index];
      if (item) {
        const key = item[this.keyProp];
        return this.sizes[key] || this.height;
      }
      return this.height;
    },

    // 计算只在视图上渲染的数据
    calcRenderData() {
      const { scroller, data, buffer } = this;
      // 计算可视范围顶部、底部
      const top = scroller.scrollTop - buffer;
      const bottom = scroller.scrollTop + scroller.offsetHeight + buffer;

      // 二分法计算可视范围内的开始的第一个内容
      let l = 0;
      let r = data.length - 1;
      let mid = 0;
      while (l <= r) {
        mid = Math.floor((l + r) / 2);
        const midVal = this.getOffsetTop(mid);
        if (midVal < top) {
          const midNextVal = this.getOffsetTop(mid + 1);
          if (midNextVal > top) break;
          l = mid + 1;
        } else {
          r = mid - 1;
        }
      }

      // 计算渲染内容的开始、结束索引
      let start = mid;
      let end = data.length - 1;
      for (let i = start + 1; i < data.length; i++) {
        const offsetTop = this.getOffsetTop(i);
        if (offsetTop >= bottom) {
          end = i;
          break;
        }
      }

      // 开始索引始终保持偶数，如果为奇数，则加1使其保持偶数【确保表格行的偶数数一致，不会导致斑马纹乱序显示】
      if (start % 2) {
        start = start - 1;
      }
      // console.log(start, end, 'start end')

      this.top = top;
      this.bottom = bottom;
      this.start = start;
      this.end = end;
      this.renderData = data.slice(start, end + 1);
    },

    // 计算位置
    calcPosition() {
      const last = this.data.length - 1;
      // 计算内容总高度
      const wrapHeight = this.getOffsetTop(last) + this.getSize(last);
      // 计算当前滚动位置需要撑起的高度
      const offsetTop = this.getOffsetTop(this.start);

      // 设置dom位置
      const classNames = [
        '.el-table__body-wrapper',
        '.el-table__fixed-right .el-table__fixed-body-wrapper',
        '.el-table__fixed .el-table__fixed-body-wrapper',
      ];
      classNames.forEach((className) => {
        const el = this.$el.querySelector(className);
        if (!el) return;

        // 创建wrapEl、innerEl
        if (!el.wrapEl) {
          const wrapEl = document.createElement('div');
          const innerEl = document.createElement('div');
          wrapEl.appendChild(innerEl);
          innerEl.appendChild(el.children[0]);
          el.insertBefore(wrapEl, el.firstChild);
          el.wrapEl = wrapEl;
          el.innerEl = innerEl;
        }

        if (el.wrapEl) {
          // 设置高度
          el.wrapEl.style.height = wrapHeight + 'px';
          // 设置transform撑起高度
          el.innerEl.style.transform = `translateY(${offsetTop}px)`;
          // 设置paddingTop撑起高度
          // el.innerEl.style.paddingTop = `${offsetTop}px`
        }
      });
    },

    // 空闲时更新位置
    updatePosition() {
      this.timer && clearTimeout(this.timer);
      this.timer = setTimeout(() => {
        this.timer && clearTimeout(this.timer);
        // 传入false，避免一直循环调用
        this.handleScroll(false);
      }, this.throttleTime + 10);
    },

    // 【外部调用】更新
    update() {
      this.handleScroll();
    },

    // 【外部调用】滚动到第几行
    scrollTo(index, stop = false) {
      const item = this.data[index];
      if (item && this.scroller) {
        this.updateSizes();
        this.calcRenderData();

        this.$nextTick(() => {
          const offsetTop = this.getOffsetTop(index);
          this.scroller.scrollTop = offsetTop;

          // 调用两次scrollTo，第一次滚动时，如果表格行初次渲染高度发生变化时，会导致滚动位置有偏差，此时需要第二次执行滚动，确保滚动位置无误
          if (!stop) {
            setTimeout(() => {
              this.scrollTo(index, true);
            }, 50);
          }
        });
      }
    },

    // 【外部调用】重置
    reset() {
      this.sizes = {};
      this.scrollTo(0, false);
    },
  },
  watch: {
    data() {
      this.update();
    },
  },
  created() {
    this.$nextTick(() => {
      this.initData();
    });
  },
  beforeDestroy() {
    if (this.scroller) {
      this.scroller.removeEventListener('scroll', this.onScroll);
      window.removeEventListener('resize', this.onScroll);
    }
  },
};
</script>

<style lang="less" scoped></style>
