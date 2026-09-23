<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-table :data="tbody" v-loading="loading" highlight-current-row>
        <el-table-column label="权益名称" min-width="120">
          <template #default="scope"
            ><span>{{ scope.row.title }}</span></template
          >
        </el-table-column>
        <el-table-column label="展示名称" min-width="120">
          <template #default="scope"
            ><span>{{ scope.row.show_title }}</span></template
          >
        </el-table-column>
        <el-table-column label="权益图标" min-width="120">
          <template #default="scope">
            <div class="image-wrap" v-viewer><img v-lazy="scope.row.image" /></div>
          </template>
        </el-table-column>
        <el-table-column label="权益简介" min-width="120">
          <template #default="scope"
            ><span>{{ scope.row.explain }}</span></template
          >
        </el-table-column>
        <el-table-column label="权益状态" min-width="120">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              size="large"
              @change="statusChange(scope.row)"
              active-text="启用"
              inactive-text="禁用"
            ></el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope"><a v-db-click @click="edit(scope.row)">编辑</a></template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination v-if="total" :total="total" v-model:page="page" v-model:limit="limit" @pagination="getRightList" />
      </div>
    </el-card>
    <el-dialog v-model="modal1" title="编辑会员权益" width="540px">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="90px">
        <el-input v-model="form.id" style="display: none"></el-input>
        <el-input v-model="form.status" style="display: none"></el-input>
        <el-input v-model="form.right_type" style="display: none"></el-input>
        <el-form-item label="权益名称：" prop="title">
          <el-input v-model.trim="form.title" placeholder="请输入权益名称" disabled class="w100"></el-input>
        </el-form-item>
        <el-form-item label="展示名称：" prop="show_title">
          <el-input v-model.trim="form.show_title" placeholder="请输入展示名称" class="w100"></el-input>
        </el-form-item>
        <el-form-item label="权益图标：" prop="image">
          <div class="image-group" v-db-click @click="callImage">
            <img v-if="form.image" v-lazy="form.image" />
            <i v-else class="el-icon-picture-outline" style="font-size: 24px"></i>
          </div>
          <el-input v-model="form.image" style="display: none"></el-input>
        </el-form-item>
        <el-form-item label="权益简介：" prop="show_title">
          <el-input
            v-model.trim="form.explain"
            type="textarea"
            :autosize="{ minRows: 2, maxRows: 10 }"
            placeholder="请输入权益简介"
            class="w100"
          ></el-input>
        </el-form-item>
        <el-form-item
          v-show="form.right_type !== 'coupon' && form.right_type !== 'vip_price'"
          :label="
            form.right_type === 'offline' || form.right_type === 'express' || form.right_type === 'vip_price'
              ? '折扣数(%)：'
              : '积分倍数：'
          "
          prop="number"
        >
          <el-input-number :controls="false" v-model="form.number" :min="1"></el-input-number>
        </el-form-item>
        <el-form-item>
          <div class="acea-row row-right">
            <el-button type="primary" v-db-click @click="formSubmit('form')">提交</el-button>
          </div>
        </el-form-item>
      </el-form>
    </el-dialog>
    <el-dialog v-model="modal2" width="1024px" title="选择权益图标">
      <uploadPictures
        v-if="modal2"
        isChoice="单选"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        @getPic="getPic"
      ></uploadPictures>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { memberRight, memberRightSave } from '../../api';
import uploadPictures from '@/components/uploadPictures';

defineOptions({ name: 'memberRightList' });

const tbody = ref([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(15);
const modal1 = ref(false);
const form = ref({ id: '', right_type: '', title: '', show_title: '', image: '', explain: '', number: 1, status: 1 });
const rules = {
  title: [{ required: true, message: '请输入权益名称', trigger: 'blur' }],
  show_title: [{ required: true, message: '请输入展示名称', trigger: 'blur' }],
  image: [{ required: true, message: '请上传权益图标' }],
  explain: [{ required: true, message: '请输入权益简介', trigger: 'blur' }],
  number: [{ required: true, type: 'integer', message: '请输入正整数' }],
};
const modal2 = ref(false);
const gridPic = { xl: 6, lg: 8, md: 12, sm: 12, xs: 12 };
const gridBtn = { xl: 4, lg: 8, md: 8, sm: 8, xs: 8 };
const formRef = ref(null);

// created
getRightList();

function getRightList() {
  loading.value = true;
  memberRight()
    .then((res) => {
      loading.value = false;
      total.value = res.data.count;
      tbody.value = res.data.list;
    })
    .catch((err) => {
      loading.value = false;
      ElMessage.error(err);
    });
}

function statusChange(row) {
  Object.assign(form.value, {
    id: row.id,
    right_type: row.right_type,
    title: row.title,
    show_title: row.show_title,
    image: row.image,
    explain: row.explain,
    number: row.number,
    status: row.status,
  });
  rightSave();
}

function edit(row) {
  modal1.value = true;
  Object.assign(form.value, {
    id: row.id,
    status: row.status,
    right_type: row.right_type,
    title: row.title,
    show_title: row.show_title,
    image: row.image,
    explain: row.explain,
    number: row.number,
  });
}

function rightSave() {
  memberRightSave(form.value)
    .then((res) => {
      modal1.value = false;
      getRightList();
      ElMessage.success(res.msg);
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

function formSubmit(name) {
  formRef.value.validate((valid) => {
    if (valid) rightSave();
  });
}

function callImage() {
  modal2.value = true;
}

function getPic(image) {
  form.value.image = image.att_dir;
  modal2.value = false;
}
</script>

<style lang="scss" scoped>
.image-wrap {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  img {
    width: 100%;
    height: 100%;
  }
}
.image-group {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  width: 60px;
  height: 60px;
  border: 1px solid #dcdee2;
  border-radius: 4px;
  cursor: pointer;
  &:hover {
    border-color: #57a3f3;
  }
  img {
    width: 100%;
    height: 100%;
  }
}
</style>
