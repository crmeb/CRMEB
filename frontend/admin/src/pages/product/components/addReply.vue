<template>
  <el-dialog v-model="visibleModal" title="添加自评" width="720px" :close-on-click-modal="false" @close="onCancel">
    <el-form :model="formData" label-width="100px" label-position="right">
      <el-form-item label="商品：">
        <div class="upload-box" v-db-click @click="callGoods">
          <img v-if="goods.id" :src="goods.image" class="image" />
          <i v-else class="el-icon-goods"></i>
        </div>
      </el-form-item>
      <el-form-item v-if="goods.id" label="商品规格：">
        <div class="upload-box" v-db-click @click="callAttr">
          <img v-if="attr.pic" :src="attr.pic" class="image" />
          <i v-else class="el-icon-plus" />
        </div>
        <div>{{ attr.suk }}</div>
      </el-form-item>
      <el-form-item label="用户头像：">
        <div class="upload-box" v-db-click @click="callPicture('单选')">
          <img v-if="avatar.att_dir" :src="avatar.att_dir" class="image" />
          <i v-if="avatar.att_dir" class="el-icon-error btn" v-db-click @click.stop="removeUser"></i>
          <i v-else class="el-icon-user" />
        </div>
      </el-form-item>
      <el-form-item label="用户名称：">
        <el-input
          v-model="formData.nickname"
          placeholder="请输入用户名称"
          class="w100"
          maxlength="20"
          show-word-limit
        ></el-input>
      </el-form-item>
      <el-form-item label="评价文字：">
        <el-input
          v-model="formData.comment"
          type="textarea"
          placeholder="请输入评价文字"
          class="w100"
          maxlength="200"
          show-word-limit
        ></el-input>
      </el-form-item>
      <el-form-item label="商品分数：">
        <el-rate v-model="product_score" />
      </el-form-item>
      <el-form-item label="服务分数：">
        <el-rate v-model="service_score" />
      </el-form-item>
      <el-form-item label="评价图片：">
        <div class="df-aic">
          <div v-for="item in picture" :key="item.att_id" class="upload-box">
            <img :src="item.att_dir" class="image" />
            <i class="el-icon-error btn" v-db-click @click.stop="removePicture(item.att_id)"></i>
          </div>
          <div v-if="picture.length < 8" class="upload-box" v-db-click @click="callPicture('多选')">
            <i class="el-icon-picture-outline"></i>
          </div>
        </div>
      </el-form-item>
      <el-form-item label="评价时间：">
        <el-date-picker
          clearable
          v-model="add_time"
          type="datetime"
          range-separator="-"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
          @change="onChange"
          style="width: 414px"
        />
      </el-form-item>
    </el-form>
    <template #footer
      ><span class="dialog-footer">
        <el-button v-db-click @click="onCancel">取 消</el-button>
        <el-button type="primary" v-db-click @click="onOk">确 定</el-button>
      </span></template
    >
  </el-dialog>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { saveFictitiousReply } from '@/api/product';

defineOptions({ name: 'addReply' });

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  goods: {
    type: Object,
    default() {
      return {};
    },
  },
  attr: {
    type: Object,
    default() {
      return {};
    },
  },
  avatar: {
    type: Object,
    default() {
      return {};
    },
  },
  picture: {
    type: Array,
    default() {
      return [];
    },
  },
});

const emit = defineEmits(['removePicture', 'callGoods', 'callAttr', 'callPicture', 'close']);

const formData = reactive({
  avatar: '',
  nickname: '',
  comment: '',
});
const product_score = ref(5);
const service_score = ref(5);
const pics = ref([]);
const add_time = ref('');
const visibleModal = ref(false);

watch(
  () => props.picture,
  (value) => {
    pics.value = value.map((item) => {
      return item.att_dir;
    });
  },
);
watch(
  () => props.visible,
  (value) => {
    visibleModal.value = value;
    if (!value) {
      formData.nickname = '';
      formData.comment = '';
      product_score.value = 5;
      service_score.value = 5;
      add_time.value = '';
    }
  },
);

function removeUser() {
  props.avatar.att_dir = '';
}
function removePicture(att_id) {
  emit('removePicture', att_id);
}
function onChange(date) {
  add_time.value = date;
}
function callGoods() {
  emit('callGoods');
}
function callAttr() {
  emit('callAttr');
}
function callPicture(type) {
  emit('callPicture', type);
}
function onOk() {
  if (!props.goods.id) {
    return ElMessage.error('请选择商品');
  }
  if (!props.attr.pic) {
    return ElMessage.error('请选择商品规格');
  }
  if (!props.avatar.att_dir) {
    return ElMessage.error('请选择用户头像');
  }
  if (!formData.nickname) {
    return ElMessage.error('请填写用户昵称');
  }
  if (!formData.comment) {
    return ElMessage.error('请填写评论内容');
  }
  if (!product_score.value) {
    return ElMessage.error('商品分数必须是1-5之间的整数');
  }
  if (!service_score.value) {
    return ElMessage.error('服务分数必须是1-5之间的整数');
  }
  let data = {
    image: {
      image: props.goods.image,
      product_id: props.goods.id,
    },
    suk: props.attr.suk,
    avatar: props.avatar.att_dir,
    nickname: formData.nickname,
    comment: formData.comment,
    product_score: product_score.value,
    service_score: service_score.value,
    pics: pics.value,
    add_time: add_time.value,
  };
  saveFictitiousReply(data)
    .then((res) => {
      ElMessage.success(res.msg);
      emit('close', false);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function onCancel() {
  emit('close', false);
}
</script>

<style lang="scss" scoped>
.upload-box {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 58px;
  height: 58px;
  border: 1px dashed #c0ccda;
  border-radius: 4px;

  vertical-align: middle;
  text-align: center;
  line-height: 58px;
  cursor: pointer;

  + .upload-box {
    margin-left: 10px;
  }
  .ivu-icon {
    vertical-align: middle;
    font-size: 20px;
  }
  .image {
    width: 100%;
    height: 100%;
    border-radius: 3px;
  }
  .btn {
    position: absolute;
    top: 0;
    right: 0;
    font-size: 14px;
    transform: translate(50%, -50%);
  }
}
.df-aic {
  display: flex;
  flex-wrap: wrap;
}
.w414 {
  width: 414px;
}
.el-rate {
  line-height: 2;
}
</style>
