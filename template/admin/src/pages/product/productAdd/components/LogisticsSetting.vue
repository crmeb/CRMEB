<template>
  <!--  物流设置 -->
  <el-row>
    <el-col :span="24">
      <el-form-item label="物流方式：" prop="logistics">
        <el-checkbox-group v-model="formValidate.logistics" @change="logisticsBtn">
          <el-checkbox label="1">快递</el-checkbox>
          <el-checkbox label="2">到店</el-checkbox>
        </el-checkbox-group>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="运费设置：">
        <el-radio-group v-model="formValidate.freight">
          <!-- <el-radio :label="1">包邮</el-radio> -->
          <el-radio :label="2">固定邮费</el-radio>
          <el-radio :label="3">运费模板</el-radio>
        </el-radio-group>
      </el-form-item>
    </el-col>
    <el-col :span="24" v-if="formValidate.freight != 3 && formValidate.freight != 1">
      <el-form-item label="" :prop="formValidate.freight != 1 ? 'freight' : ''">
        <div class="acea-row">
          <el-input-number
            :controls="false"
            :min="0"
            v-model="formValidate.postage"
            placeholder="请输入金额"
            class="input_width maxW input-number-unit-class"
            class-unit="元"
          />
        </div>
      </el-form-item>
    </el-col>
    <el-col :span="24" v-if="formValidate.freight == 3">
      <el-form-item label="" prop="temp_id">
        <div class="acea-row">
          <el-select v-model="formValidate.temp_id" clearable placeholder="请选择运费模板" class="input_width maxW">
            <el-option
              v-for="(item, index) in templateList"
              :value="item.id"
              :key="index"
              :label="item.name"
            ></el-option>
          </el-select>
          <span class="addfont" v-db-click @click="addTemp">新增运费模板</span>
        </div>
      </el-form-item>
    </el-col>
  </el-row>
</template>

<script>
export default {
  name: 'LogisticsSetting',
  props: {
    formValidate: {
      type: Object,
      required: true,
    },
    templateList: {
      type: Array,
      required: true,
    },
  },
  methods: {
    logisticsBtn(val) {
      this.$emit('logisticsBtn', val);
    },
    addTemp() {
      this.$emit('addTemp');
    },
  },
};
</script>
<style lang="scss" scoped>
@use '../productAdd.scss' as *;
</style>
