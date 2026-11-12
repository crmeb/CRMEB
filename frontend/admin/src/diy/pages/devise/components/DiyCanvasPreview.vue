<template>
  <div class="wrapper-con">
    <div class="content">
      <div class="contxt">
        <div class="overflowy">
          <div class="picture"><img src="@/assets/images/electric.png" /></div>
          <div
            v-if="pageType == 'home'"
            class="page-title"
            :class="{ on: activeIndex == -100 }"
            @click="$emit('select-title')"
          >
            {{ titleTxt }}
            <div class="delete-box"></div>
            <div class="handle"></div>
          </div>
        </div>
        <div class="scrollCon" :style="'height:' + rollHeight + 'px;'">
          <div style="width: 460px; margin: 0 auto">
            <div
              class="scroll-box"
              :class="
                picTxt && tabValTxt == 2
                  ? 'fullsize noRepeat'
                  : picTxt && tabValTxt == 1
                  ? 'repeat ysize'
                  : 'noRepeat ysize'
              "
              :style="
                'background-color:' +
                (colorTxt ? colorPickerTxt : '') +
                ';background-image: url(' +
                (picTxt ? picUrlTxt : '') +
                ');min-height:' +
                rollHeight +
                'px;'
              "
              id="imgContainer"
            >
              <draggable
                class="dragArea list-group"
                :list="mConfig"
                group="people"
                @change="$emit('drag-change', $event)"
                filter=".top"
                :move="onMove"
                animation="300"
                :item-key="getDraggableItemKey"
              >
                <template #item="{ element: item, index: key }">
                  <div
                    class="mConfig-item"
                    :class="{
                      on: activeIndex == key,
                      top: item.name == 'search_box' || item.name == 'nav_bar',
                      hide: defaultArrays[item.num] && defaultArrays[item.num].isHide,
                    }"
                    @click.stop="$emit('select-item', item, key)"
                    :style="
                      colorTxt
                        ? 'background-color:' + colorPickerTxt + ';'
                        : 'background-color: rgba(255,255,255, 0);'
                    "
                  >
                    <div v-if="isCompDisabled(item)" class="disabled-comp-tip">
                      <span>「{{ item.cname }}」当前不可用，数据已保留</span>
                    </div>
                    <component
                      v-else
                      :is="resolveComp(item.name)"
                      :configData="propsObj"
                      :index="key"
                      :num="item.num"
                      :colorStyle="colorStyle"
                    ></component>
                    <div class="delete-box">
                      <div class="handleType">
                        <div
                          class="iconfont"
                          :class="
                            defaultArrays[item.num] && defaultArrays[item.num].isHide
                              ? 'iconyincang'
                              : 'iconxianshi'
                          "
                          @click.stop="$emit('hide-item', item)"
                        ></div>
                        <div class="iconfont iconshanchu3" @click.stop="$emit('delete-item', item, key)"></div>
                        <div class="iconfont icona-fuzhi1" @click.stop="$emit('duplicate-item', item, 0, key)"></div>
                        <div
                          class="iconfont iconshang"
                          :class="key === 0 ? 'on' : ''"
                          @click.stop="$emit('move-item', item, key, 1)"
                        ></div>
                        <div
                          class="iconfont iconxia"
                          :class="key === mConfig.length - 1 ? 'on' : ''"
                          @click.stop="$emit('move-item', item, key, 0)"
                        ></div>
                      </div>
                    </div>
                    <div class="handle"></div>
                    <div class="delete-name" :class="{ on: activeIndex == key }">
                      <span
                        v-if="
                          item.name == 'home_custom_component' &&
                          defaultArrays[item.num] &&
                          defaultArrays[item.num].selectType
                        "
                        class="custom-badge"
                        >{{ getBadgeText(defaultArrays[item.num].selectType.activeValue) }}</span
                      >
                      {{ item.cname }}
                    </div>
                  </div>
                </template>
              </draggable>
            </div>
          </div>
        </div>
        <div class="overflowy" v-if="pageType == 'home' && !isMicroPage">
          <div
            class="page-foot"
            @click="$emit('select-foot')"
            :class="{ on: activeIndex == -101 }"
            :style="pageFooterType == 1 ? 'bottom:' + (50 + pageFooterBottom) + 'px' : ''"
          >
            <footPage></footPage>
            <div class="delete-box"></div>
            <div class="handle"></div>
          </div>
        </div>
        <div class="overflowy" v-if="pageType == 'detail'">
          <div class="page-foot" @click="$emit('select-bottom-menu')" :class="{ on: activeIndex == -102 }">
            <home_bottom_menu :colorStyle="colorStyle"></home_bottom_menu>
            <div class="delete-box"></div>
            <div class="handle"></div>
          </div>
        </div>
        <div class="defaultData" v-if="pageId !== 0">
          <el-button class="data" @click="$emit('open-page-settings')">页面设置</el-button>
          <el-button v-db-click class="data" @click="$emit('open-save-modal')">另存模板</el-button>
          <el-button class="data" @click="$emit('reset')">重置</el-button>
          <el-button v-db-click v-if="!isMicroPage" class="data" @click="$emit('save-cover')">保存为封面</el-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import draggable from 'vuedraggable';
import { getDraggableItemKey } from '@/utils/draggableKey';
import footPage from '@diy/components/pagesFoot';
import home_bottom_menu from '@diy/components/mobilePage/home_bottom_menu.vue';

defineProps({
  mConfig: { type: Array, required: true },
  rollHeight: { type: [Number, String], default: '' },
  pageType: { type: String, default: '' },
  isMicroPage: { type: Boolean, default: false },
  pageId: { type: [Number, String], default: '' },
  titleTxt: { type: String, default: '' },
  activeIndex: { type: Number, required: true },
  colorStyle: { type: Object, default: () => ({}) },
  propsObj: { type: Object, default: () => ({}) },
  defaultArrays: { type: Object, default: () => ({}) },
  colorTxt: { type: [Number, Boolean], default: 0 },
  colorPickerTxt: { type: String, default: '' },
  picTxt: { type: [Number, Boolean], default: 0 },
  tabValTxt: { type: [Number, String], default: 0 },
  picUrlTxt: { type: String, default: '' },
  pageFooterType: { type: [Number, String], default: 0 },
  pageFooterBottom: { type: [Number, String], default: 0 },
  resolveComp: { type: Function, required: true },
  isCompDisabled: { type: Function, required: true },
  getBadgeText: { type: Function, required: true },
  onMove: { type: Function, required: true },
});

defineEmits([
  'drag-change',
  'select-title',
  'select-foot',
  'select-bottom-menu',
  'select-item',
  'hide-item',
  'delete-item',
  'duplicate-item',
  'move-item',
  'open-page-settings',
  'open-save-modal',
  'reset',
  'save-cover',
]);
</script>
