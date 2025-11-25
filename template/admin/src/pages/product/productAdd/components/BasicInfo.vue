<template>
  <!-- 基础信息 -->
  <el-row :gutter="24">
    <el-col :span="24">
      <el-form-item label="商品类型：" props="is_virtual">
        <div
          class="virtual"
          :class="formValidate.virtual_type == item.id ? 'virtual_boder' : 'virtual_boder2'"
          v-for="(item, index) in goodsType"
          :key="index"
          v-db-click
          @click="virtualbtn(item.id, 2)"
          v-show="
            (formValidate.id && formValidate.virtual_type == item.id) ||
            (isCai == -1 && index == 0 && !formValidate.id) ||
            (isCai == 0 && !formValidate.id)
          "
        >
          <div class="virtual_top">{{ item.tit }}</div>
          <div class="virtual_bottom">({{ item.tit2 }})</div>
          <div v-if="formValidate.virtual_type == item.id" class="virtual_san"></div>
          <div v-if="formValidate.virtual_type == item.id" class="virtual_dui">✓</div>
        </div>
      </el-form-item>
    </el-col>

    <el-col :span="24">
      <el-form-item label="商品名称：" prop="store_name">
        <el-input
          class="content_width"
          v-model="formValidate.store_name"
          placeholder="请输入商品名称"
          maxlength="80"
          show-word-limit
        />
      </el-form-item>
    </el-col>

    <el-col :span="24">
      <el-form-item label="单位：" prop="unit_name">
        <el-input
          class="input_width"
          v-model="formValidate.unit_name"
          placeholder="请输入单位"
          maxlength="5"
          show-word-limit
        />
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="商品轮播图：" prop="slider_image">
        <div class="acea-row">
          <div
            class="pictrue"
            v-for="(item, index) in formValidate.slider_image"
            :key="index"
            draggable="true"
            @dragstart="handleDragStart($event, item)"
            @dragover.prevent="handleDragOver($event, item)"
            @dragenter="handleDragEnter($event, item)"
            @dragend="handleDragEnd($event, item)"
          >
            <img v-lazy="item" />
            <i class="el-icon-error btndel" v-db-click @click="handleRemove(index)"></i>
          </div>
          <div
            v-if="formValidate.slider_image.length < 10"
            class="upLoad acea-row row-center-wrapper"
            v-db-click
            @click="modalPicTap('duo')"
          >
            <i class="el-icon-picture-outline" style="font-size: 24px"></i>
          </div>
          <el-input v-model="formValidate.slider_image[0]" style="display: none"></el-input>
        </div>

        <div class="tips-info">建议尺寸：800*800，可拖拽改变图片顺序，默认首张图为主图，最多上传10张</div>

        <!-- <div class="tips">(最多10张<br />750*750)</div> -->
      </el-form-item>
    </el-col>
    <el-col :span="24" id="selectvideo">
      <el-form-item label="添加视频：" prop="video_link">
        <div v-if="!formValidate.video_link" class="videbox" @click="addVideo">
          <i class="el-icon-video-camera"></i>
        </div>
        <div class="box-video-style" v-if="formValidate.video_link">
          <video style="width: 100%; height: 100%" :src="formValidate.video_link" controls="controls">
            您的浏览器不支持 video 标签。
          </video>
          <div class="mark"></div>
          <i class="el-icon-delete iconv" v-db-click @click="delVideo"></i>
        </div>
        <Progress class="progress" :percent="progress" :stroke-width="5" v-if="upload.videoIng" />
        <div class="tips-info">建议时长：9～30秒，视频宽高比16:9</div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="商品分类：" prop="cate_id">
        <el-cascader
          class="content_width"
          v-model="formValidate.cate_id"
          filterable
          size="small"
          :options="treeSelect"
          :props="{ multiple: true, checkStrictly: true, emitPath: false }"
          clearable
        ></el-cascader>
        <span class="addfont" v-db-click @click="addCate">新增分类</span>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="商品标签：">
        <div class="flex">
          <useLabel
            v-if="tileLabelList.length"
            :activeId.sync="formValidate.label_list"
            :listData="tileLabelList"
          ></useLabel>
          <el-button v-db-click @click="addGoodsTag">选择标签</el-button>
        </div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="商品状态：">
        <el-radio-group v-model="formValidate.is_show">
          <el-radio :label="1" class="radio">上架</el-radio>
          <el-radio :label="0">下架</el-radio>
        </el-radio-group>
      </el-form-item>
    </el-col>
  </el-row>
</template>

<script>
import useLabel from '@/components/goodsLabel/useLabel';

export default {
  name: 'BasicInfo',
  components: {
    useLabel,
  },
  props: {
    formValidate: {
      type: Object,
      required: true,
    },
    goodsType: {
      type: Array,
      required: true,
    },
    treeSelect: {
      type: Array,
      required: true,
    },
    tileLabelList: {
      type: Array,
      required: true,
    },
    upload: {
      type: Object,
      required: true,
    },
    isCai: {
      type: Number | String,
      required: true,
    },
  },
  methods: {
    virtualbtn(id, type) {
      this.$emit('virtualbtn', id, type);
    },
    handleDragStart(e, item) {
      this.$emit('handleDragStart', e, item);
    },
    handleDragOver(e, item) {
      this.$emit('handleDragOver', e, item);
    },
    handleDragEnter(e, item) {
      this.$emit('handleDragEnter', e, item);
    },
    handleDragEnd(e, item) {
      this.$emit('handleDragEnd', e, item);
    },
    handleRemove(index) {
      this.$emit('handleRemove', index);
    },
    modalPicTap(type) {
      this.$emit('modalPicTap', type);
    },
    addVideo() {
      this.$emit('addVideo');
    },
    delVideo() {
      this.$emit('delVideo');
    },
    addCate() {
      this.$emit('addCate');
    },
    addGoodsTag() {
      this.$emit('addGoodsTag');
    },
  },
};
</script>
<style lang="scss" scoped>
@use '../productAdd.scss' as *;
</style>
