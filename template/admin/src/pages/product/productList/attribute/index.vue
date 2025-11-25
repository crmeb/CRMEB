<template>
  <div>
    <el-dialog :visible.sync="val" title="商品属性" width="1000px" @closed="cancel">
      <div class="Modals">
        <el-form class="form" ref="form" label-width="70px" label-position="right">
          <el-row :gutter="24">
            <el-col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
              <el-form-item label="规格：" prop="store_name" label-for="store_name">
                <el-input
                  placeholder="规格"
                  style="width: 10%"
                  class="input"
                  :value="item"
                  v-for="(item, index) in specs"
                  :key="index"
                >
                </el-input>
                <el-input placeholder="请输入" v-model="specsVal" style="width: 10%" class="input">
                  <i slot="suffix" class="el-input__icon el-icon-plus" v-db-click @click="confirm"></i>
                </el-input>
                <!--<el-button type="primary" v-db-click @click="confirm"></el-button>-->
              </el-form-item>
            </el-col>
            <el-col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
              <el-form-item
                :label="item.attr + ':'"
                prop="store_name"
                label-for="store_name"
                v-for="(item, index) in attrList"
                :key="index"
              >
                <el-tag closable color="primary" v-for="(itemn, index) in item.attrVal" :key="index">{{
                  itemn
                }}</el-tag>
                <el-input placeholder="请输入" v-model="item.inputVal" style="width: 10%" class="input">
                  <i slot="suffix" class="el-input__icon el-icon-plus" v-db-click @click="confirmAttr(index)"></i>
                </el-input>
                <!--<el-button type="primary" v-db-click @click="confirm"></el-button>-->
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex';
export default {
  name: 'attribute',
  props: {
    attrTemplate: {
      type: Boolean,
    },
  },
  data() {
    return {
      val: false,
      specsVal: '',
      specs: [],
      attrVal: '',
      attrList: [],
    };
  },
  watch: {
    attrTemplate: function (n) {
      this.val = n;
    },
  },
  computed: {},
  methods: {
    cancel() {
      this.$emit('changeTemplate', false);
    },
    confirm() {
      if (this.specsVal === '') {
        this.$message.error('请填写规格名称');
      } else {
        this.specs.push(this.specsVal);
        this.attrList.push({
          attr: this.specsVal,
          inputVal: '',
          attrVal: [],
        });
        this.specsVal = '';
        if (this.specsVal !== '') {
          this.attrList.forEach((item) => {
            if (item.attrVal.length < 1) {
              this.$message.error('请填写规格属性');
            }
          });
        }
      }
    },
    confirmAttr(index) {
      let attrList = this.attrList[index];
      if (attrList.inputVal === '') {
        this.$message.error('请填写规格属性');
      } else {
        attrList.attrVal.push(attrList.inputVal);
        attrList.inputVal === '';
      }
    },
  },
  mounted() {},
};
</script>

<style lang="scss" scoped>
.Modals ::v-deep .input {
  margin-right: 10px;
}
</style>
