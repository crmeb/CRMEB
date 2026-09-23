<template>
  <!-- 基础信息 -->
  <el-row :gutter="24">
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

        <div class="tip">建议尺寸：800*800，可拖拽改变图片顺序，默认首张图为主图，最多上传10张</div>

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
        <el-progress class="progress" :percentage="progress" :stroke-width="5" v-if="upload.videoIng" />
        <div class="tip">建议时长：9～30秒，视频宽高比16:9</div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="商品分类：" prop="cate_id">
        <el-cascader
          class="content_width"
          v-model="formValidate.cate_id"
          filterable
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
            v-model:activeId="formValidate.label_list"
            :listData="tileLabelList"
          ></useLabel>
          <el-button v-db-click @click="addGoodsTag">选择标签</el-button>
        </div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="商品状态：">
        <el-radio-group v-model="formValidate.is_show">
          <el-radio :label="1" :value="1" class="radio">上架</el-radio>
          <el-radio :label="0" :value="0">下架</el-radio>
        </el-radio-group>
      </el-form-item>
    </el-col>
  </el-row>
</template>

<script setup>
import useLabel from '@/components/goodsLabel/useLabel';

defineOptions({ name: 'BasicInfo' });

defineProps({
  formValidate: {
    type: Object,
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
    type: [Number, String],
    required: true,
  },
  progress: {
    type: Number,
    default: 0,
  },
  videoIng: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  'handleDragStart',
  'handleDragOver',
  'handleDragEnter',
  'handleDragEnd',
  'handleRemove',
  'modalPicTap',
  'addVideo',
  'delVideo',
  'addCate',
  'addGoodsTag',
]);

function handleDragStart(e, item) {
  emit('handleDragStart', e, item);
}
function handleDragOver(e, item) {
  emit('handleDragOver', e, item);
}
function handleDragEnter(e, item) {
  emit('handleDragEnter', e, item);
}
function handleDragEnd(e, item) {
  emit('handleDragEnd', e, item);
}
function handleRemove(index) {
  emit('handleRemove', index);
}
function modalPicTap(type) {
  emit('modalPicTap', type);
}
function addVideo() {
  emit('addVideo');
}
function delVideo() {
  emit('delVideo');
}
function addCate() {
  emit('addCate');
}
function addGoodsTag() {
  emit('addGoodsTag');
}
</script>
<style lang="scss" scoped>
@use '../productAdd.scss' as *;
</style>
