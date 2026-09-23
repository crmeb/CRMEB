<template>
  <a
    :href="linkUrl"
    :target="target"
    class="i-link"
    :class="{ 'i-link-color': !linkColor }"
    v-db-click
    @click.exact="handleClickItem($event, false)"
    @click.ctrl="handleClickItem($event, true)"
    @click.meta="handleClickItem($event, true)"
    ><slot></slot
  ></a>
</template>
<script setup>
// view-design 的 mixins/link 在 Vue3 依赖树中已不存在，此处内联等价实现
import { computed } from 'vue';
import { useRouter } from 'vue-router';

defineOptions({ name: 'i-link' });

const props = defineProps({
  to: {
    type: [Object, String],
    default: undefined,
  },
  replace: {
    type: Boolean,
    default: false,
  },
  target: {
    type: String,
    default: undefined,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  // 开启后，链接颜色为默认的蓝色，默认关闭为继承效果
  linkColor: {
    type: Boolean,
    default: false,
  },
});

const router = useRouter();

const linkUrl = computed(() => {
  const type = typeof props.to;
  if (type !== 'undefined') {
    if (type === 'string') return props.to;
    if (type === 'object') return router ? router.resolve(props.to).href : '';
  }
  return null;
});

function handleOpenTo() {
  if (props.target === '_blank') {
    const href = typeof props.to === 'string' ? props.to : router ? router.resolve(props.to).href : '';
    window.open(href, '_blank');
  } else {
    location.href = linkUrl.value;
  }
}

function handleCheckClick(event, new_window = false) {
  if (props.to) {
    if (new_window) {
      handleOpenTo();
    } else if (router) {
      props.replace ? router.replace(props.to) : router.push(props.to);
    } else {
      location.href = linkUrl.value;
    }
  }
}

function handleClickItem(event, new_window = false) {
  if (props.disabled) return;

  handleCheckClick(event, new_window);
}
</script>
<style lang="scss">
.i-link {
  cursor: pointer;
  &-color {
    &,
    &:hover,
    &:active {
      color: inherit;
    }
  }
}
</style>
