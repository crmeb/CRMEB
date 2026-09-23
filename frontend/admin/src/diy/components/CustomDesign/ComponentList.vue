<template>
  <div class="component-list">
    <el-collapse v-model="activeNames">
      <el-collapse-item title="组件" name="1">
        <div class="list-wrapper">
          <div
            v-for="(item, index) in list"
            :key="index"
            class="list-item"
            draggable="true"
            @dragstart="handleDragStart($event, item)"
          >
            <div class="icon-box">
              <svg class="iconfont" aria-hidden="true">
                <use :xlink:href="'#' + item.icon"></use>
              </svg>
            </div>
            <div class="text">{{ item.label }}</div>
          </div>
        </div>
      </el-collapse-item>
    </el-collapse>
  </div>
</template>

<script setup>
import { ref } from 'vue';

defineOptions({ name: 'ComponentList' });

const props = defineProps({
  type: {
    type: String,
    default: 'user',
  },
});

const activeNames = ref(['1']);
const list = [
  {
    label: '图片',
    icon: 'icontupian-2',
    type: 'Picture',
    propValue: {
      url: '',
      width: 80,
      height: 80,
      borderRadius: 0,
      isRadiusAll: true,
      borderRadiusTopLeft: 0,
      borderRadiusTopRight: 0,
      borderRadiusBottomRight: 0,
      borderRadiusBottomLeft: 0,
      showShadow: false,
      linkType: 'url',
      link: '',
      shadowX: 0,
      shadowY: 0,
      shadowBlur: 5,
      shadowSpread: 0,
      shadowColor: '#000000',
      showBorder: false,
      borderWidth: 1,
      borderColor: '#000000',
      borderStyle: 'solid',
    },
  },
  {
    label: '文本',
    icon: 'iconwenben-2',
    type: 'Text',
    propValue: {
      text: '',
      fontSize: 12,
      color: '#000000',
      width: 200,
      height: 20,
      fontWeight: 'normal',
      fontStyle: 'normal',
      textDecoration: 'none',
      textAlign: 'left',
      lineHeight: 1.5,
      letterSpacing: 0,
      backgroundColor: '',
      bgColor2: '',
      bgDirection: 'vertical',
      ellipsis: 0,
      showBorder: false,
      borderWidth: 1,
      borderColor: '#000000',
      borderStyle: 'solid',
      borderRadius: 0,
      isRadiusAll: true,
      borderRadiusTopLeft: 0,
      borderRadiusTopRight: 0,
      borderRadiusBottomRight: 0,
      borderRadiusBottomLeft: 0,
      paddingTop: 0,
      paddingBottom: 0,
      paddingLeft: 0,
      paddingRight: 0,
      linkType: 'url',
      link: '',
      showTextShadow: false,
      shadowX: 0,
      shadowY: 0,
      shadowBlur: 5,
      shadowSpread: 0,
      shadowColor: '#000000',
    },
  },
  {
    label: '图标',
    icon: 'icontubiao-2',
    type: 'Icon',
    propValue: {
      class: 'icon-wutouxiang',
      size: 20,
      color: '#000000',
      width: 30,
      height: 30,
      rotate: 0,
      iconAlign: 'center',
      backgroundColor: '',
      bgColor2: '',
      bgDirection: 'vertical',
      linkType: 'url',
      link: '',
      showBorder: false,
      borderWidth: 1,
      borderColor: '#000000',
      borderStyle: 'solid',
      borderRadius: 0,
      isRadiusAll: true,
      borderRadiusTopLeft: 0,
      borderRadiusTopRight: 0,
      borderRadiusBottomRight: 0,
      borderRadiusBottomLeft: 0,
      paddingTop: 0,
      paddingBottom: 0,
      paddingLeft: 0,
      paddingRight: 0,
    },
  },
  {
    label: '线条',
    icon: 'iconxiantiao-2',
    type: 'Line',
    propValue: { style: 'solid', width: 200, height: 2, color: '#000000', direction: 'horizontal' },
  },
  {
    label: '面板',
    icon: 'iconmianban-2',
    type: 'Panel',
    propValue: {
      width: 100,
      height: 100,
      backgroundColor: '#ffffff',
      bgColor2: '',
      bgDirection: 'vertical',
      borderRadius: 0,
      isRadiusAll: true,
      borderRadiusTopLeft: 0,
      borderRadiusTopRight: 0,
      borderRadiusBottomRight: 0,
      borderRadiusBottomLeft: 0,
      showShadow: false,
      linkType: 'url',
      link: '',
      shadowX: 0,
      shadowY: 0,
      shadowBlur: 5,
      shadowSpread: 0,
      shadowColor: '#000000',
      showBorder: false,
      borderWidth: 1,
      borderColor: '#000000',
      borderStyle: 'solid',
    },
  },
];

function handleDragStart(e, item) {
  const data = { ...item, propValue: { ...item.propValue } };
  // 商品类型下，支持跳转的组件默认跳转商品详情（linkType 为 detail，见 ConfigLink）
  if (props.type === 'goods' && data.propValue.linkType !== undefined) {
    data.propValue.linkType = 'detail';
  }
  e.dataTransfer.setData('component', JSON.stringify(data));
}
</script>

<style scoped lang="scss">
.component-list {
  padding: 10px 15px 10px 15px;
  :deep(.el-collapse) {
    border: none;
    .el-collapse-item__wrap {
      border: none;
      overflow: visible;
    }
    .el-collapse-item__content {
      padding-bottom: 0;
    }
  }
  :deep(.el-collapse-item__header) {
    border: none;
    color: #333;
    font-size: 14px;
    font-weight: 400;
  }
  .list-wrapper {
    display: flex;
    flex-wrap: wrap;
    // justify-content: space-between;
  }
  .list-item {
    width: 33.33%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: grab;
    padding: 10px 0;
    transition: all 0.3s;
    margin-bottom: 10px;
    border-radius: 5px;
    overflow: hidden;
    &:hover {
      box-shadow: 0 0 5px 0 rgba(24, 144, 255, 0.3);
      transition: all 0.2s;
    }

    .icon-box {
      font-size: 28px;
      line-height: 28px;
      .iconfont {
        width: 28px;
        height: 28px;
      }
    }
    .text {
      font-size: 12px;
    }
  }
}
</style>
