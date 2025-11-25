<template>
  <div class="list">
    <vuedraggable
      class="flex"
      group="label"
      :disabled="labelList.length < 2"
      :list="labelList"
      handle=".label"
      @end="onMoveSpec"
      animation="300"
    >
      <div
        class="label"
        v-for="(label, j) in labelList"
        :key="j"
        v-dragging="{
          list: activeId,
        }"
      >
        <div
          class="label-item"
          :style="{
            backgroundColor: label.bg_color,
            color: label.font_color,
            border: label.border_color ? '1px solid ' + label.border_color : 'none',
          }"
          v-if="!label.image"
        >
          {{ label.name }}
        </div>
        <img :src="label.image" class="img-tag" v-else />
      </div>
    </vuedraggable>
  </div>
</template>

<script>
import { productLabelUseListApi } from '@/api/product';
import vuedraggable from 'vuedraggable';

export default {
  name: 'userLabel',
  components: { vuedraggable },
  props: {
    activeId: {
      type: Array,
      default: () => {
        [];
      },
    },
    listData: {
      type: Array,
      default: () => {
        [];
      },
    },
  },
  data() {
    return {
      labelList: [],
    };
  },
  watch: {
    activeId: {
      handler(nVal, oVal) {
        if (nVal != oVal) {
          if (nVal.length) {
            this.labelList = [];
            this.listData.map((item) => {
              if (nVal.includes(item.id)) {
                this.labelList.push(item);
              }
            });
          }
        }
      },
      deep: true,
      immediate: true,
    },
  },
  mounted() {},
  methods: {
    async onMoveSpec(event) {
      console.log(event);
      const { newIndex, oldIndex } = event;
      console.log(newIndex, oldIndex);
      const label = this.activeId[oldIndex];
      const newLabelList = [...this.activeId];
      newLabelList.splice(oldIndex, 1);
      newLabelList.splice(newIndex, 0, label);
      console.log(newLabelList);
      this.$emit('update:activeId', newLabelList);
    },
  },
};
</script>

<style lang="scss" scoped>
.list {
  display: flex;
  flex-wrap: wrap;
  .label {
    padding: 2px;
    border-radius: 4px;
    margin: 0 8px 0px 0;
    cursor: move;
    display: flex;
    align-items: center;
  }
  .label-item {
    height: 22px;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0px 8px;

    background: #eeeeee;
    color: #333333;
    border-radius: 2px;
    cursor: pointer;
    font-size: 12px;
  }

  .img-tag {
    height: 22px;
    border-radius: 2px;
  }
}
</style>
