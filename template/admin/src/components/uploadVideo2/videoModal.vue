<template>
  <el-dialog :visible.sync="visible" width="1024px" title="选择视频" :close-on-click-modal="false">
    <uploaFile
      v-if="visible"
      :isChoice="isMore"
      @getVideo="getImage"
      :gridBtn="gridBtn"
      :gridPic="gridPic"
    ></uploaFile>
  </el-dialog>
</template>

<script>
import uploaFile from './index.vue';
export default {
  name: '',
  components: { uploaFile },
  data() {
    return {
      isChoice: '',
      visible: false,
      callback: function () {},
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
      more: false,
    };
  },
  computed: {
    isMore() {
      return this.more ? 'more' : 'one';
    },
  },
  methods: {
    handleClose() {
      this.visible = false;
      this.callback(this.visible);
    },
    getImage(img) {
      this.callback(img);
      this.visible = false;
    },
  },
};
</script>
<style lang="scss" scoped></style>
