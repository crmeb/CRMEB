// 全局 echarts 统一入口：按需注册（echarts/core 体积约为全量引入的一半）。
// 全项目图表 series 目前只有 line/pie（bar 备用），组件覆盖 tooltip/legend/grid/toolbox/dataZoom。
// 注意：不要单独注册 AxisPointerComponent——TooltipComponent 自带 axisPointer（含 type: 'cross'），
// 两者同时注册会抛 "axisPointer CartesianAxisPointer exists"；独立组件仅在跨图 axisPointer.link 时需要。
import * as echarts from 'echarts/core';
import { BarChart, LineChart, PieChart } from 'echarts/charts';
import {
  DataZoomComponent,
  GridComponent,
  LegendComponent,
  TitleComponent,
  ToolboxComponent,
  TooltipComponent,
} from 'echarts/components';
import { LabelLayout, UniversalTransition } from 'echarts/features';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([
  BarChart,
  LineChart,
  PieChart,
  DataZoomComponent,
  GridComponent,
  LegendComponent,
  TitleComponent,
  ToolboxComponent,
  TooltipComponent,
  LabelLayout,
  UniversalTransition,
  CanvasRenderer,
]);

export * from 'echarts/core';
