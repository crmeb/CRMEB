<template>
  <el-dialog :visible.sync="modal" @closed="onCancel" title="商品规格" width="1000px" v-loading="spinShow">
    <el-form
      ref="formDynamic"
      :model="formDynamic"
      :rules="rules"
      class="attrFrom"
      label-width="110px"
      label-position="right"
      @submit.native.prevent
    >
      <el-row :gutter="24">
        <el-col :span="24">
          <el-col :span="8">
            <el-form-item label="规格模板名称：" prop="rule_name">
              <el-input placeholder="请输入标题名称" :maxlength="20" v-model.trim="formDynamic.rule_name" />
            </el-form-item>
          </el-col>
        </el-col>
        <el-col :span="23" class="noForm" v-for="(item, index) in formDynamic.spec" :key="index">
          <el-form-item>
            <div class="acea-row row-middle">
              <span class="mr5">{{ item.value }}</span>
              <i class="el-icon-close" style="font-size: 14px" @click="handleRemove(index)"></i>
            </div>
            <div class="rulesBox">
              <el-tag
                class="mr14 mb10"
                closable
                color="primary"
                v-for="(j, indexn) in item.detail"
                :key="indexn"
                @close="handleRemove2(item.detail, indexn)"
                >{{ j }}</el-tag
              >
              <el-input
                placeholder="请输入属性名称"
                v-model.trim="item.detail.attrsVal"
                @keyup.enter.native="createAttr(item.detail.attrsVal, index)"
                class="mb10 form_content_width"
              >
                <template slot="append">
                  <el-button type="primary" @click="createAttr(item.detail.attrsVal, index)">确定</el-button>
                </template>
              </el-input>
            </div>
          </el-form-item>
        </el-col>
        <el-col :span="24" v-if="isBtn" class="mt10">
          <el-col :span="8" class="mr15">
            <el-form-item label="规格名称：">
              <el-input placeholder="请输入规格" v-model="attrsName" />
            </el-form-item>
          </el-col>
          <el-col :span="8" class="mr20">
            <el-form-item label="规格值：">
              <el-input v-model="attrsVal" placeholder="请输入规格值" />
            </el-form-item>
          </el-col>
          <el-col :span="2">
            <el-button type="primary" @click="createAttrName">确定</el-button>
          </el-col>
          <el-col :span="2">
            <el-button @click="offAttrName">取消</el-button>
          </el-col>
        </el-col>
      </el-row>
      <el-button type="primary" @click="addBtn" v-if="!isBtn" class="add">添加新规格</el-button>
    </el-form>
    <span slot="footer" class="dialog-footer">
      <el-button @click="onClose">取消</el-button>
      <el-button type="primary" :loading="modal_loading" @click="handleSubmit('formDynamic')">确定</el-button>
    </span>
  </el-dialog>
</template>

<script>
import { mapState } from 'vuex';
import { ruleAddApi, ruleInfoApi } from '@/api/product';
export default {
  name: 'addAttr',
  data() {
    return {
      spinShow: false,
      modal_loading: false,
      grid: {
        xl: 3,
        lg: 3,
        md: 12,
        sm: 24,
        xs: 24,
      },
      modal: false,
      index: 1,
      rules: {
        rule_name: [{ required: true, message: '请输入规格名称', trigger: 'blur' }],
      },
      formDynamic: {
        rule_name: '',
        spec: [],
      },
      attrsName: '',
      attrsVal: '',
      formDynamicNameData: [],
      isBtn: false,
      formDynamicName: [],
      results: [],
      result: [],
      ids: 0,
    };
  },
  computed: {},
  methods: {
    onCancel() {
      this.ids = 0;
      this.clear();
    },
    onClose() {
      this.ids = 0;
      this.clear();
      this.modal = false;
    },
    // 添加按钮
    addBtn() {
      this.isBtn = true;
    },
    // 详情
    getIofo(row) {
      this.spinShow = true;
      this.ids = row.id;
      ruleInfoApi(row.id)
        .then((res) => {
          this.formDynamic = res.data.info;
          this.spinShow = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.$message.error(res.msg);
        });
    },
    // 提交
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          if (this.formDynamic.spec.length === 0) {
            return this.$message.warning('请至少添加一条商品规格！');
          }
          this.modal_loading = true;
          setTimeout(() => {
            ruleAddApi(this.formDynamic, this.ids)
              .then((res) => {
                this.$message.success(res.msg);
                setTimeout(() => {
                  this.modal = false;
                  this.modal_loading = false;
                }, 500);
                setTimeout(() => {
                  this.$emit('getList');
                  this.clear();
                }, 600);
              })
              .catch((res) => {
                this.modal_loading = false;
                this.$message.error(res.msg);
              });
          }, 1200);
        } else {
          return false;
        }
      });
    },
    clear() {
      this.$refs['formDynamic'].resetFields();
      this.formDynamic.spec = [];
      this.isBtn = false;
      this.attrsName = '';
      this.attrsVal = '';
      this.ids = 0;
    },
    // 取消
    offAttrName() {
      this.isBtn = false;
    },
    // 删除
    handleRemove(index) {
      this.formDynamic.spec.splice(index, 1);
    },
    // 删除属性
    handleRemove2(item, index) {
      item.splice(index, 1);
    },
    // 添加规则名称
    createAttrName() {
      if (this.attrsName && this.attrsVal) {
        let data = {
          value: this.attrsName,
          detail: [this.attrsVal],
        };
        this.formDynamic.spec.push(data);
        var hash = {};
        this.formDynamic.spec = this.formDynamic.spec.reduce(function (item, next) {
          /* eslint-disable */
          hash[next.value] ? '' : (hash[next.value] = true && item.push(next));
          return item;
        }, []);
        this.attrsName = '';
        this.attrsVal = '';
        this.isBtn = false;
      } else {
        this.$message.warning('请添加规格名称或规格值');
      }
    },
    // 添加属性
    createAttr(num, idx) {
      if (num) {
        this.formDynamic.spec[idx].detail.push(num);
        var hash = {};
        this.formDynamic.spec[idx].detail = this.formDynamic.spec[idx].detail.reduce(function (item, next) {
          /* eslint-disable */
          hash[next] ? '' : (hash[next] = true && item.push(next));
          return item;
        }, []);
      } else {
        this.$message.warning('请添加属性');
      }
    },
  },
};
</script>

<style scoped lang="stylus">
.rulesBox {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
}

.attrFrom {
  ::v-deep .ivu-form-item {
    margin-bottom: 0px !important;
  }
}

.add {
  margin-left: 122px;
}
</style>
