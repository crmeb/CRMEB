<template>
  <Dropdown
    ref="dropdown"
    @on-click="handleClick"
    :class="hideTitle ? '' : 'collased-menu-dropdown'"
    :transfer="hideTitle"
    :placement="placement"
    transfer-class-name="dro-style"
  >
    <a
      class="drop-menu-a"
      type="text"
      :class="{ on: parentItem.path == activeMenuPath }"
      @mouseover="handleMousemove($event, children)"
      :style="{ textAlign: !hideTitle ? 'left' : '' }"
      @click="handClick(parentItem.path)"
      ><common-icon :type="parentItem.icon" /><Icon
        style="float: right"
        v-if="!hideTitle"
        type="ios-arrow-forward"
        :size="16"
      />
      <span class="title">{{ parentItem.title }}</span></a
    >
    <DropdownMenu ref="dropdown" slot="list" v-if="children">
      <div v-for="child in children">
        <template v-if="child.auth === undefined">
          <collapsed-menu
            class="child-menu"
            v-if="showChildren(child)"
            :parent-item="child"
            :key="`drop-${child.path}`"
          ></collapsed-menu>
          <DropdownItem v-else :key="`drop-${child.path}`" :name="child.path"
            ><common-icon :type="child.icon" /><span class="menu-title">{{ child.title }}</span></DropdownItem
          >
        </template>
      </div>
    </DropdownMenu>
  </Dropdown>
</template>
<script>
import mixin from './mixin';
import itemMixin from './item-mixin';
import { findNodeUpperByClasses } from '@/libs/util';
import settings from '@/setting';

export default {
  name: 'CollapsedMenu',
  mixins: [mixin, itemMixin],
  props: {
    hideTitle: {
      type: Boolean,
      default: false,
    },
    rootIconSize: {
      type: Number,
      default: 16,
    },
    activeMenuPath: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      placement: 'right-start',
    };
  },
  methods: {
    handleClick(name) {
      this.$emit('on-click', name, this.activeMenuPath);
    },
    handClick(name) {
      this.$emit('on-click', name, this.activeMenuPath);
    },
    handleMousemove(event, children) {
      const { pageY } = event;
      const height = children.length * 38;
      const isOverflow = pageY + height < window.innerHeight;
      this.placement = isOverflow ? 'right-start' : 'right-end';
    },
  },
  mounted() {
    let dropdown = findNodeUpperByClasses(this.$refs.dropdown.$el, ['ivu-select-dropdown', 'ivu-dropdown-transfer']);
    if (dropdown) dropdown.style.overflow = 'visible';
  },
};
</script>
<style lang="less" scoped>
@import './side-menu.less';

/deep/ .collased-menu-dropdown {
  width: @side-width;
}

.child-menu {
  display: flex;
  justify-content: space-between;
  width: 100%;
}
.ivu-dropdown-menu {
  padding: 5px 0px;
  width: 140px !important;
}
.ivu-dropdown-menu /deep/ .ivu-dropdown-rel {
  width: 100% !important;
  font-size: 14px;
  line-height: 14px;
  padding: 14px 20px;
}
.ivu-dropdown-menu /deep/ .ivu-dropdown-item {
  width: 140px !important;
  padding: 14px 20px;
  font-size: 14px !important;
  line-height: 14px;
}
.drop-menu-a /deep/ .ivu-dropdown-rel {
  width: 160px !important;
  font-size: 14px;
  line-height: 14px;
  color: #fff;
}
</style>
