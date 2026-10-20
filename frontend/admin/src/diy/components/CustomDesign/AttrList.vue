<template>
  <div class="attr-list">
    <!-- Multi-select Position/Alignment Panel -->
    <ConfigMultiSelect
      v-if="activeComponentIds.length > 1"
      :groupX="groupX"
      :groupY="groupY"
      :canvasWidth="canvasWidth"
      :canvasHeight="canvasHeight"
      @align="alignComponents"
      @update:groupX="(val) => (groupX = val)"
      @update:groupY="(val) => (groupY = val)"
    />

    <template v-else-if="curComponent">
      <div class="custom-tabs" v-if="curComponent.component !== 'Line'">
        <div class="tab-item" :class="{ active: activeName === 'attr' }" @click="activeName = 'attr'">内容设置</div>
        <div class="tab-item" :class="{ active: activeName === 'style' }" @click="activeName = 'style'">样式设置</div>
      </div>

      <div class="tab-content" v-show="activeName === 'attr' && curComponent.component !== 'Line'">
        <el-form label-width="70px" label-position="left">
          <template v-if="curComponent.component === 'Text'">
            <ConfigTextContent
              :curComponent="curComponent"
              :type="type"
              :currentFieldList="currentFieldList"
              @change="onChange"
              @get-link="getLink"
            />
          </template>

          <template v-if="curComponent.component === 'Picture'">
            <ConfigPictureContent
              :curComponent="curComponent"
              :type="type"
              :currentFieldList="currentFieldList"
              @change="onChange"
              @get-link="getLink"
            />
          </template>

          <template v-if="curComponent.component === 'Icon'">
            <ConfigIconContent
              :curComponent="curComponent"
              :type="type"
              :currentFieldList="currentFieldList"
              @change="onChange"
              @get-link="getLink"
            />
          </template>

          <template v-if="curComponent.component === 'Panel'">
            <ConfigPanelContent
              :curComponent="curComponent"
              :type="type"
              :currentFieldList="currentFieldList"
              @change="onChange"
              @get-link="getLink"
            />
          </template>
        </el-form>
      </div>

      <div class="tab-content" v-show="activeName === 'style' && curComponent.component !== 'Line'">
        <ConfigPosition
          :curComponent="curComponent"
          :canvasWidth="canvasWidth"
          :canvasHeight="canvasHeight"
          @change="onChange"
        />
        <div v-if="curComponent.component === 'Picture'" class="section-title">图片设置</div>
        <div v-if="curComponent.component === 'Icon'" class="section-title">图标设置</div>
        <el-form label-width="70px" label-position="left">
          <template v-if="curComponent.component === 'Picture'">
            <ConfigRadius :curComponent="curComponent" labelPrefix="图片" @change="onChange" />
            <ConfigSize
              :curComponent="curComponent"
              :canvasWidth="canvasWidth"
              :canvasHeight="canvasHeight"
              labelPrefix="图片"
              @change="onChange"
            />
            <ConfigShadow :curComponent="curComponent" @change="onChange" />
            <ConfigBorder :curComponent="curComponent" @change="onChange" />
          </template>

          <template v-if="curComponent.component === 'Text'">
            <ConfigText :curComponent="curComponent" @change="onChange" />
            <div class="section-title">容器设置</div>
            <ConfigSize
              :curComponent="curComponent"
              :canvasWidth="canvasWidth"
              :canvasHeight="canvasHeight"
              @change="onChange"
            />
            <ConfigBackground :curComponent="curComponent" @change="onChange" />
            <div class="section-title">圆角样式</div>
            <ConfigRadius :curComponent="curComponent" @change="onChange" />
            <ConfigPadding :curComponent="curComponent" @change="onChange" />
            <ConfigBorder :curComponent="curComponent" @change="onChange" />
          </template>

          <template v-if="curComponent.component === 'Icon'">
            <ConfigIconStyle :curComponent="curComponent" @change="onChange" />

            <div class="section-title">容器设置</div>
            <ConfigSize
              :curComponent="curComponent"
              :canvasWidth="canvasWidth"
              :canvasHeight="canvasHeight"
              @change="onChange"
            />
            <ConfigBackground :curComponent="curComponent" @change="onChange" />
            <div class="section-title">圆角样式</div>
            <ConfigRadius :curComponent="curComponent" @change="onChange" />
            <ConfigPadding :curComponent="curComponent" @change="onChange" />
            <ConfigBorder :curComponent="curComponent" @change="onChange" />
          </template>

          <template v-if="curComponent.component === 'Panel'">
            <ConfigSize
              :curComponent="curComponent"
              :canvasWidth="canvasWidth"
              :canvasHeight="canvasHeight"
              @change="onChange"
            />
            <ConfigBackground :curComponent="curComponent" @change="onChange" />
            <ConfigRadius :curComponent="curComponent" @change="onChange" />
            <ConfigBorder :curComponent="curComponent" @change="onChange" />
            <ConfigShadow :curComponent="curComponent" @change="onChange" />
          </template>
        </el-form>
      </div>

      <!-- Special Line Component Settings -->
      <div class="line-settings" v-if="curComponent.component === 'Line'">
        <ConfigPosition
          :curComponent="curComponent"
          :canvasWidth="canvasWidth"
          :canvasHeight="canvasHeight"
          @change="onChange"
        />
        <ConfigLine :curComponent="curComponent" :canvasWidth="canvasWidth" @change="onChange" />
      </div>
    </template>

    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import linkaddress from '@/components/linkaddress';
import ConfigPosition from './components/ConfigPosition';
import ConfigSize from './components/ConfigSize';
import ConfigBorder from './components/ConfigBorder';
import ConfigRadius from './components/ConfigRadius';
import ConfigBackground from './components/ConfigBackground';
import ConfigPadding from './components/ConfigPadding';
import ConfigShadow from './components/ConfigShadow';
import ConfigText from './components/ConfigText';
import ConfigMultiSelect from './components/ConfigMultiSelect';
import ConfigLine from './components/ConfigLine';
import ConfigIconStyle from './components/ConfigIconStyle';
import ConfigTextContent from './components/ConfigTextContent';
import ConfigPictureContent from './components/ConfigPictureContent';
import ConfigIconContent from './components/ConfigIconContent';
import ConfigPanelContent from './components/ConfigPanelContent';

defineOptions({ name: 'AttrList' });

const props = defineProps({
  curComponent: {
    type: Object,
    default: () => ({}),
  },
  activeComponentIds: {
    type: Array,
    default: () => [],
  },
  componentData: {
    type: Array,
    default: () => [],
  },
  canvasWidth: {
    type: Number,
    default: 375,
  },
  canvasHeight: {
    type: Number,
    default: 380,
  },
  type: {
    type: String,
    default: 'user',
  },
  fieldList: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['change', 'update-group']);

const activeName = ref('attr');
const linkaddres = ref(null);

watch(
  () => props.curComponent?.id,
  (val) => {
    if (val && props.curComponent && props.curComponent.style) {
      // Ensure padding properties are reactive (for ConfigPadding)
      if (props.curComponent.propValue.paddingTop === undefined) props.curComponent.propValue['paddingTop'] = 0;
      if (props.curComponent.propValue.paddingRight === undefined) props.curComponent.propValue['paddingRight'] = 0;
      if (props.curComponent.propValue.paddingBottom === undefined) props.curComponent.propValue['paddingBottom'] = 0;
      if (props.curComponent.propValue.paddingLeft === undefined) props.curComponent.propValue['paddingLeft'] = 0;

      // Ensure rotate is reactive in style (for drag rotate)
      if (props.curComponent.style.rotate === undefined) {
        props.curComponent.style['rotate'] = 0;
      }

      if (props.curComponent.component === 'Text' && props.curComponent.propValue.linkType === undefined) {
        props.curComponent.propValue['linkType'] = 'url';
      }
      // Ensure typeLabel is synced if missing
      if (props.curComponent.propValue.fieldType && !props.curComponent.propValue.typeLabel) {
        const field = currentFieldList.value.find((item) => item.value === props.curComponent.propValue.fieldType);
        if (field) {
          props.curComponent.propValue['typeLabel'] = field.label;
        }
      }
    }
  },
  { immediate: true },
);

const selectedComponents = computed(() => {
  return props.componentData.filter((item) => props.activeComponentIds.includes(item.id));
});
const groupRect = computed(() => {
  if (selectedComponents.value.length === 0) return { left: 0, top: 0, width: 0, height: 0 };
  const lefts = selectedComponents.value.map((c) => c.style.left);
  const tops = selectedComponents.value.map((c) => c.style.top);
  const rights = selectedComponents.value.map((c) => c.style.left + c.style.width);
  const bottoms = selectedComponents.value.map((c) => c.style.top + c.style.height);

  const minLeft = Math.min(...lefts);
  const minTop = Math.min(...tops);
  const maxRight = Math.max(...rights);
  const maxBottom = Math.max(...bottoms);

  return {
    left: minLeft,
    top: minTop,
    width: maxRight - minLeft,
    height: maxBottom - minTop,
  };
});
const groupX = computed({
  get() {
    return groupRect.value.left;
  },
  set(val) {
    const delta = val - groupRect.value.left;
    selectedComponents.value.forEach((c) => {
      c.style.left += delta;
    });
    emit('update-group');
  },
});
const groupY = computed({
  get() {
    return groupRect.value.top;
  },
  set(val) {
    const delta = val - groupRect.value.top;
    selectedComponents.value.forEach((c) => {
      c.style.top += delta;
    });
    emit('update-group');
  },
});
const currentFieldList = computed(() => {
  const map = {
    user: 'user',
    article: 'article',
    coupon: 'coupon',
    goods: 'product',
  };
  const key = map[props.type] || 'user';
  const list = props.fieldList[key] || [];
  if (Array.isArray(list)) {
    return list.map((item) => {
      if (typeof item === 'object') {
        return {
          label: item.label || item.title || item.name,
          value: item.value || item.field || item.key || item.name,
        };
      }
      return { label: item, value: item };
    });
  }
  if (typeof list === 'object') {
    return Object.keys(list).map((k) => ({
      label: list[k],
      value: k,
    }));
  }
  return [];
});

function alignComponents(type) {
  if (selectedComponents.value.length < 2) return;

  const rect = groupRect.value;
  selectedComponents.value.forEach((c) => {
    switch (type) {
      case 'left':
        c.style.left = rect.left;
        break;
      case 'center':
        c.style.left = rect.left + (rect.width - c.style.width) / 2;
        break;
      case 'right':
        c.style.left = rect.left + rect.width - c.style.width;
        break;
      case 'top':
        c.style.top = rect.top;
        break;
      case 'middle':
        c.style.top = rect.top + (rect.height - c.style.height) / 2;
        break;
      case 'bottom':
        c.style.top = rect.top + rect.height - c.style.height;
        break;
      case 'all-center':
        c.style.left = rect.left + (rect.width - c.style.width) / 2;
        c.style.top = rect.top + (rect.height - c.style.height) / 2;
        break;
    }
  });
  emit('update-group');
}
function onChange() {
  emit('change');
}
function getLink() {
  linkaddres.value.modals = true;
}
function linkUrl(e) {
  props.curComponent.propValue['link'] = e;
  onChange();
}
</script>

<style scoped lang="scss">
.flex {
  display: flex;
  align-items: center;
}
.justify-between {
  justify-content: space-between;
}

.attr-list {
  padding: 15px;
  .tip {
    color: #f56c6c;
    line-height: 1.5;
  }
  .section-title {
    font-size: 14px;
    color: #333;
    margin-bottom: 18px;
    margin-top: 10px;
    width: calc(100% + 30px);
    margin-left: -15px;
    padding-left: 15px;
    padding-top: 20px;
    border-top: 6px solid #f0f2f5;
  }
  .custom-tabs {
    display: flex;
    border-bottom: 1px solid #e4e7ed;
    margin-bottom: 20px;
    .tab-item {
      flex: 1;
      text-align: center;
      padding: 10px 0;
      cursor: pointer;
      font-size: 14px;
      color: #303133;
      position: relative;
      &.active {
        color: var(--prev-color-primary);
        font-weight: 500;
        &:after {
          content: '';
          position: absolute;
          left: 50%;
          bottom: 0;
          transform: translateX(-50%);
          width: 40px;
          height: 2px;
          background: var(--prev-color-primary);
        }
      }
    }
  }
  :deep(.el-radio),
  :deep(.el-checkbox) {
    margin-bottom: 0;
  }
  :deep(.el-form-item__content) {
    margin-left: 80px !important;
    line-height: 36px !important;
    /* EP 表单内容区是 flex 容器，行容器默认不撑满会被压缩，
       其内部的 el-slider（flex: 1）随之被压成 0 宽，表现为滑块不可见/样式异常；
       basis 取 100% 保证每行独占一行（如背景色的两行取色不会挤到同一行）。
       圆角/内边距的 .c_rows/.c_content 由各自组件自行撑满 */
    > .row,
    > .row-space-between {
      flex: 1 1 100%;
      min-width: 0;
    }
  }
  :deep(.el-form-item--small .el-form-item__label) {
    line-height: 38px !important;
    word-break: break-word;
  }
}
:deep(.reset-color) {
  font-size: 13px;
  white-space: nowrap;
  color: var(--prev-color-primary);
  cursor: pointer;
  margin-left: 15px;
  &:hover {
    color: var(--prev-color-primary-light-1);
  }
}
</style>
