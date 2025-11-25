<template>
  <div class="main">
    <el-alert closable class="mb20">
      <template v-slot:title>crud生成文件位置说明</template>
      <template>
        <p>1、生成的文件位置尽量不能修改，可以在创建后自行移动</p>
        <p>2、生成的文件位置不会包含文件的绝对路径，在创建时会携带提前预设的绝对路径</p>
        <p>3、前端模板文件默认位置在：config/app.php的admin_template_path中配置</p>
        <p>4、后端默认生成位置为：网站根目录</p>
        <p>5、确保前端/admin/src目录、后端app目录有写入创建文件权限</p>
      </template>
    </el-alert>
    <el-form ref="foundation" :model="storage" :rules="storageRules" label-width="140px">
      <el-form-item label="生成controller位置：">
        <el-input class="form-width" v-model="storage.controller" disabled placeholder="请输入"></el-input>
        <div class="tip">生成后端控制器文件存放位置</div>
      </el-form-item>
      <el-form-item label="生成service位置：">
        <el-input class="form-width" v-model="storage.service" disabled placeholder="请输入"></el-input>
        <div class="tip">生成后端service文件存放位置</div>
      </el-form-item>
      <el-form-item label="生成dao位置：">
        <el-input class="form-width" v-model="storage.dao" disabled placeholder="请输入"></el-input>
        <div class="tip">生成后端dao文件存放位置</div>
      </el-form-item>
      <el-form-item label="生成model位置：">
        <el-input class="form-width" v-model="storage.model" disabled placeholder="请输入"></el-input>
        <div class="tip">生成后端model文件存放位置</div>
      </el-form-item>
      <el-form-item label="生成route位置：">
        <el-input class="form-width" v-model="storage.route" disabled placeholder="请输入"></el-input>
        <div class="tip">生成后端路由存放位置</div>
      </el-form-item>
      <el-form-item label="生成validate位置：">
        <el-input class="form-width" v-model="storage.validate" disabled placeholder="请输入"></el-input>
        <div class="tip">生成后端验证器存放位置</div>
      </el-form-item>
      <el-form-item label="生成pages位置：">
        <el-input class="form-width" v-model="storage.pages" disabled placeholder="请输入"></el-input>
        <div class="tip">生成前端页面文件存放位置</div>
      </el-form-item>
      <el-form-item label="生成api位置：">
        <el-input class="form-width" v-model="storage.api" disabled placeholder="请输入"></el-input>
        <div class="tip">生成前端api接口文件存放位置</div>
      </el-form-item>
      <el-form-item label="生成router位置：">
        <el-input class="form-width" v-model="storage.router" disabled placeholder="请输入"></el-input>
        <div class="tip">生成前端路由存放位置</div>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
export default {
  name: '',
  props: {
    storage: {
      type: Object,
      default: () => {
        return {};
      },
    },
  },
  data() {
    return {
      storageRules: {},
    };
  },
  created() {},
  mounted() {},
  methods: {},
};
</script>
<style lang="scss" scoped>
.form-width {
  width: 500px;
}
</style>
