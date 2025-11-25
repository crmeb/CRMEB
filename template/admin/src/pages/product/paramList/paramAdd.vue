<template>
  <el-dialog :visible.sync="modal" @closed="onCancel" title="商品参数" width="1000px" v-loading="spinShow">
    <el-form
      ref="formDynamic"
      :model="formDynamic"
      class="attrFrom"
      label-position="right"
      label-width="auto"
      @submit.native.prevent
    >
      <el-row :gutter="24">
        <el-col :span="24">
          <el-col :span="8">
            <el-form-item label="模板名称：" prop="rule_name">
              <el-input placeholder="请输入模板名称" :maxlength="20" v-model.trim="formDynamic.name" />
            </el-form-item>
          </el-col>
        </el-col>
        <el-col :span="24">
          <el-col :span="8">
            <el-form-item label="排序：" prop="rule_name">
              <el-input type="number" placeholder="请输入排序" :maxlength="20" v-model.trim="formDynamic.sort" />
            </el-form-item>
          </el-col>
        </el-col>
        <el-col :span="24" class="noForm" :key="index">
          <el-form-item label="">
            <div class="specifications">
              <el-table ref="selection" :data="formDynamic.value">
                <el-table-column width="50">
                  <template slot-scope="scope">
                    <div class="drag" @on-drag-drop="onDragDrop">
                      <img class="handle" src="@/assets/images/drag-icon.png" alt="" />
                    </div>
                  </template>
                </el-table-column>
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
                v-if="formDynamic.value.length < 8"
                type="primary"
                class="submission mr15 mt20"
                v-db-click
                @click="handleAddRole"
                >添加参数</el-button
              >
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
import { paramSaveApi, paramInfoApi } from '@/api/product';
import vuedraggable from 'vuedraggable';
import Sortable from 'sortablejs';
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
      formDynamic: {
        id: 0,
        name: '',
        sort: 0,
        value: [],
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
  watch: {
    modal(val) {
      if (val) {
        this.$nextTick(() => {
          this.setSort();
        });
      }
    },
  },
  mounted() {},
  methods: {
    setSort() {
      // ref一定跟table上面的ref一致
      const el = this.$refs.selection.$el.querySelectorAll('.el-table__body-wrapper > table > tbody')[0];
      this.sortable = Sortable.create(el, {
        ghostClass: 'sortable-ghost',
        handle: '.handle',
        setData: function (dataTransfer) {
          dataTransfer.setData('Text', '');
        },
        // 监听拖拽事件结束时触发
        onEnd: (evt) => {
          this.elChangeExForArray(evt.oldIndex, evt.newIndex, this.formDynamic.value);
        },
      });
    },
    elChangeExForArray(index1, index2, array, init) {
      const arr = array;
      const temp = array[index1];
      const tempt = array[index2];
      if (init) {
        arr[index2] = tempt;
        arr[index1] = temp;
      } else {
        arr[index1] = tempt;
        arr[index2] = temp;
      }
      this.formDynamic.value = [];
      this.$nextTick((e) => {
        this.formDynamic.value = arr;
      });
    },
    handleShowPop(index) {
      this.$refs['inputRef_' + index][0].focus();
    },
    handleAddRole() {
      let data = {
        name: '',
        value: '',
      };
      this.formDynamic.value.push(data);
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
    deleteRow(index) {
      this.formDynamic.value.splice(index, 1);
    },
    // 添加按钮
    addBtn() {
      this.isBtn = true;
    },
    //修改排序
    onDragDrop(a, b) {
      console.log(a, b);
      this.formDynamic.value.splice(b, 1, ...this.formDynamic.value.splice(a, 1, this.formDynamic.value[b]));
    },
    // 详情
    getIofo(row) {
      this.ids = row.id;
      paramInfoApi(row.id)
        .then((res) => {
          this.formDynamic = res.data;
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
          if (this.formDynamic.value.length === 0) {
            return this.$message.warning('请至少添加一条商品参数！');
          }
          this.modal_loading = true;
          paramSaveApi(this.formDynamic)
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
        } else {
          return false;
        }
      });
    },
    clear() {
      this.$refs['formDynamic'].resetFields();
      this.formDynamic.value = [];
      this.formDynamic.name = '';
      this.formDynamic.sort = '';
      this.isBtn = false;
      this.attrsName = '';
      this.attrsVal = '';
      this.ids = 0;
    },
    // 删除
    handleRemove(index) {
      this.formDynamic.value.splice(index, 1);
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
.value {
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
.value:hover {
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
