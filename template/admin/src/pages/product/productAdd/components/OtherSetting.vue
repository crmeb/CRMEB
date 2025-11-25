<template>
  <!-- 其他设置 -->
  <el-row>
    <el-col :span="24">
      <el-form-item label="商品关键字：">
        <el-input
          class="content_width"
          v-model.trim="formValidate.keyword"
          placeholder="请输入商品关键字"
          maxlength="100"
          show-word-limit
        />
        <div class="tips-info">PC端的SEO优化以及可以根据关键字进行商品搜索</div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="商品简介：">
        <el-input
          class="content_width"
          v-model.trim="formValidate.store_info"
          type="textarea"
          :rows="3"
          placeholder="请输入商品简介"
          maxlength="100"
          show-word-limit
        />
        <div class="tips-info">公众号分享商品以及PC端SEO优化使用</div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="商品口令：">
        <el-input
          v-model.trim="formValidate.command_word"
          placeholder="请输入商品口令"
          type="textarea"
          :rows="3"
          class="content_width"
        />
        <div class="tips-info">将其他平台的商品口令填写保存，移动端进入商品详情的时候自动复制</div>
      </el-form-item>
    </el-col>

    <el-col :span="24">
      <el-form-item label="商品推荐图：">
        <div class="pictrueBox" v-db-click @click="modalPicTap('dan', 'recommend_image')">
          <div class="pictrue" v-if="formValidate.recommend_image">
            <img v-lazy="formValidate.recommend_image" />
            <el-input v-model.trim="formValidate.recommend_image" style="display: none"></el-input>
          </div>
          <div class="upLoad acea-row row-center-wrapper" v-else>
            <el-input v-model.trim="formValidate.recommend_image" style="display: none"></el-input>
            <i class="el-icon-picture-outline" style="font-size: 24px"></i>
          </div>
          <div class="tips-info">移动端分类样式2显示的长方形图片，建议比例：5:2</div>
        </div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="商品参数：">
        <el-select
          v-model="paramsType"
          clearable
          style="width: 200px; margin-left: 6px; margin-right: 10px"
          @change="changeParamsType"
        >
          <el-option v-for="items in paramsTypeList" :value="items.id" :key="items.id" :label="items.name"></el-option>
        </el-select>
        <div class="specifications">
          <el-table
            v-if="paramsType || formValidate.params_list.length"
            class="mt15"
            ref="selection"
            :data="formValidate.params_list"
          >
            <el-table-column label="参数名称" min-width="80">
              <template slot-scope="scope">
                <el-input v-model="scope.row.name"></el-input>
              </template>
            </el-table-column>
            <el-table-column label="参数值" min-width="80">
              <template slot-scope="scope">
                <el-input v-model="scope.row.value"></el-input>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="80">
              <template slot-scope="scope">
                <a class="submission mr15" v-db-click @click="deleteRow(scope.$index)">删除</a>
              </template>
            </el-table-column>
          </el-table>
          <el-button
            v-if="formValidate.params_list.length < 8 && paramsType"
            type="primary"
            class="submission mr15 mt20"
            v-db-click
            @click="handleAddParams"
            >添加参数</el-button
          >
        </div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="服务保障：">
        <el-checkbox-group v-model="formValidate.protection_list" v-if="protectionList.length">
          <el-checkbox v-for="(item, index) in protectionList" :key="index" :label="item.id">{{
            item.title
          }}</el-checkbox>
        </el-checkbox-group>
        <el-button v-else type="primary" v-db-click @click="addProtection">添加保障</el-button>
        <div class="tips-info">商品详情中显示的服务保障信息，可多选</div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="自定义表单：">
        <el-switch :active-value="1" :inactive-value="0" v-model="innerCustomBtn" size="large">
          <span slot="open">开启</span>
          <span slot="close">关闭</span>
        </el-switch>
        <div class="addCustom_content" v-if="customBtn">
          <div v-for="(item, index) in formValidate.custom_form" :key="index" class="custom_box">
            <el-input
              v-model.trim="item.title"
              :placeholder="'表单标题' + (index + 1)"
              style="width: 150px; margin-right: 10px"
              maxlength="10"
              show-word-limit
            />
            <el-select v-model="item.label" style="width: 200px; margin-left: 6px; margin-right: 10px">
              <el-option
                v-for="items in CustomList"
                :value="items.value"
                :key="items.value"
                :label="items.label"
              ></el-option>
            </el-select>
            <el-checkbox v-model="item.status">必填</el-checkbox>
            <div class="addfont" v-db-click @click="delcustom(index)">删除</div>
          </div>
        </div>
        <div class="addCustomBox" v-show="customBtn">
          <div class="btn" v-db-click @click="addcustom">+ 添加表单</div>
          <div class="tips-info">用户下单时需填写的信息，最多可设置10条，设置了自定义表单的商品不能加入购物车</div>
        </div>
      </el-form-item>
    </el-col>
  </el-row>
</template>

<script>
export default {
  name: 'OtherSetting',
  props: {
    formValidate: {
      type: Object,
      required: true,
    },
    customBtn: {
      type: Number,
      default: 0,
    },
    paramsType: {
      type: Number,
      default: 0,
    },
    paramsTypeList: {
      type: Array,
      default: () => [],
    },
    protectionList: {
      type: Array,
      default: () => [],
    },
    CustomList: {
      type: Array,
      default: () => [],
    },
  },
  computed: {
    innerCustomBtn: {
      get() {
        return this.customBtn;
      },
      set(val) {
        this.$emit('customMessBtn', val);
      },
    },
  },
  methods: {
    modalPicTap(tit, type) {
      this.$emit('modalPicTap', tit, type);
    },
    changeParamsType(val) {
      this.$emit('changeParamsType', val);
    },
    deleteRow(index) {
      this.$emit('deleteRow', index);
    },
    handleAddParams() {
      this.$emit('handleAddParams');
    },
    addProtection() {
      this.$emit('addProtection');
    },
    // customMessBtn(e) {
    //   console.log(e);
    //   this.$emit('customMessBtn', e);
    // },
    delcustom(index) {
      this.$emit('delcustom', index);
    },
    addcustom() {
      this.$emit('addcustom');
    },
  },
};
</script>
<style lang="scss" scoped>
@use '../productAdd.scss' as *;
</style>
