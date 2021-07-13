<template>
  <div>
    <div :id="echarts" :style="styles" />
  </div>
</template>

<script>
import echarts from 'echarts'
export default {
  name: 'Index',
  props: {
    styles: {
      type: Object,
      default: null
    },
    optionData: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      myChart: null
    }
  },
  computed: {
    echarts() {
      return 'echarts' + Math.ceil(Math.random() * 100)
    }
  },
  watch: {
    optionData: {
      handler(newVal, oldVal) {
        this.handleSetVisitChart()
      },
      deep: true // 对象内部属性的监听，关键。
    }
  },
  mounted: function() {
    const vm = this
    vm.$nextTick(() => {
      vm.handleSetVisitChart()
      window.addEventListener('resize', this.wsFunc)
    })
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.wsFunc)
    if (!this.myChart) {
      return
    }
    this.myChart.dispose()
    this.myChart = null
  },
  methods: {
    wsFunc() {
      this.myChart.resize()
    },
    handleSetVisitChart() {
      this.myChart = echarts.init(document.getElementById(this.echarts))
      let option = null
      option = this.optionData
      // 基于准备好的dom，初始化echarts实例
      this.myChart.setOption(option, true)
    }
  }
}
</script>

<style scoped>

</style>
