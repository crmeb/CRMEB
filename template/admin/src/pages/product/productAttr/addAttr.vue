<template>
  <el-dialog :visible.sync="modal" @closed="onCancel" title="商品规格" width="1000px" v-loading="spinShow">
    <el-form
      ref="formDynamic"
      :model="formDynamic"
      :rules="rules"
      class="attrFrom"
      label-width="120px"
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
        <el-col :span="23" class="noForm" :key="index">
          <el-form-item label="">
            <div class="specifications">
              <draggable group="specifications" :list="formDynamic.spec" handle=".move-icon" animation="300">
                <div class="specifications-item active" v-for="(item, index) in formDynamic.spec" :key="index">
                  <div class="move-icon">
                    <span class="iconfont icondrag2"></span>
                  </div>
                  <i class="del el-icon-error" @click="handleRemoveRole(index)"></i>
                  <div class="specifications-item-box">
                    <div class="lineBox"></div>
                    <div class="specifications-item-name mb18">
                      <el-input
                        v-model="item.value"
                        placeholder="规格名称"
                        class="specifications-item-name-input"
                        maxlength="30"
                        show-word-limit
                      ></el-input>
                    </div>
                    <div class="rulesBox ml30">
                      <draggable class="item" :list="item.detail" handle=".drag">
                        <div v-for="(j, indexn) in item.detail" :key="indexn" class="mr10 spec drag">
                          <i class="el-icon-error" @click="handleRemove2(item.detail, indexn)"></i>

                          <el-input
                            style="width: 120px"
                            v-model="item.detail[indexn]"
                            placeholder="规格值"
                            maxlength="30"
                          >
                            <template slot="prefix">
                              <span class="iconfont icondrag2"></span>
                            </template>
                          </el-input>
                        </div>
                        <el-popover
                          :ref="'popoverRef_' + index"
                          placement=""
                          width="210"
                          trigger="click"
                          @after-enter="handleShowPop(index)"
                        >
                          <el-input
                            :ref="'inputRef_' + index"
                            placeholder="请输入规格值"
                            v-model="item.detail.attrsVal"
                            @keyup.enter.native="createAttr(item.detail.attrsVal, index)"
                            @blur="createAttr(item.detail.attrsVal, index)"
                            maxlength="30"
                            show-word-limit
                          >
                          </el-input>
                          <div class="addfont" slot="reference" type="text" v-db-click>添加规格值</div>
                        </el-popover>
                      </draggable>
                    </div>
                  </div>
                </div>
              </draggable>
              <el-button v-if="formDynamic.spec.length < 4" v-db-click @click="handleAddRole()">添加新规格</el-button>
            </div>
          </el-form-item>
        </el-col>
      </el-row>
    </el-form>
    <span slot="footer" class="dialog-footer">
      <el-button v-db-click @click="onClose">取消</el-button>
      <el-button type="primary" :loading="modal_loading" v-db-click @click="handleSubmit('formDynamic')"
        >确定</el-button
      >
    </span>
  </el-dialog>
</template>

<script>
import { mapState } from 'vuex';
import { ruleAddApi, ruleInfoApi } from '@/api/product';
import vuedraggable from 'vuedraggable';
export default {
  name: 'addAttr',
  components: {
    draggable: vuedraggable,
  },
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
    handleShowPop(index) {
      this.$refs['inputRef_' + index][0].focus();
    },
    // 删除规格
    handleRemoveRole(index) {
      this.formDynamic.spec.splice(index, 1);
      if (!this.formDynamic.spec.length) {
        this.formDynamic.spec = [];
      }
    },
    handleAddRole() {
      let data = {
        value: this.formDynamic.attrsName,
        detail: [],
      };
      this.formDynamic.spec.push(data);
    },
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

<style lang="scss" scoped>
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
.noForm {
  margin-left: 12px;
}
.add {
  margin-left: 132px;
}
.drag {
  cursor: move;
}
.spec {
  display: block;
  margin: 5px 0;
  position: relative;
  .el-icon-error {
    position: absolute;
    display: none;
    right: -3px;
    top: -3px;
    z-index: 9;
  }
}
.spec:hover {
  .el-icon-error {
    display: block;
    z-index: 999;
    cursor: pointer;
  }
}
.move-icon {
  width: 30px;
  cursor: move;
  margin-right: 10px;
}
.move-icon .icondrag2 {
  font-size: 26px;
  color: #bbb;
}
.specifications {
  .specifications-item:hover {
    background-color: var(--prev-bg-menu-hover-ba-color);
  }
  .specifications-item:hover .del {
    display: block;
  }
  .specifications-item:last-child {
    margin-bottom: 14px;
  }
  .specifications-item {
    position: relative;
    display: flex;
    align-items: center;
    padding: 20px 15px;
    transition: all 0.1s;
    background-color: #fafafa;
    margin-bottom: 10px;
    border-radius: 4px;
    .del {
      display: none;
      position: absolute;
      right: 15px;
      top: 15px;
      font-size: 22px;
      color: var(--prev-color-primary);
      cursor: pointer;
    }
    .specifications-item-box {
      position: relative;
      .lineBox {
        position: absolute;
        left: 13px;
        top: 24px;
        width: 30px;
        height: 45px;
        border-radius: 6px;
        border-left: 1px solid #dcdfe6;
        border-bottom: 1px solid #dcdfe6;
      }
      .specifications-item-name-input {
        width: 200px;
      }
    }
  }
  .rulesBox {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    .item {
      display: flex;
      flex-wrap: wrap;
    }
    .addfont {
      margin-top: 5px;
    }
    ::v-deep .el-popover {
      border: none;
      box-shadow: none;
      padding: 0;
      margin-top: 5px;
      line-height: 1.5;
    }
  }
  .addfont {
    display: inline-block;
    font-size: 12px;
    font-weight: 400;
    color: var(--prev-color-primary);
    margin-left: 14px;
    cursor: pointer;
  }
}
</style>
