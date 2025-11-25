<template>
  <div class="checkboxs acea-row row-top">
    <div class="title-tips" v-if="configData">
      <span>{{ configData.title }}</span>
    </div>
    <div class="checkbox-box">
      <el-checkbox-group size="small" v-model="configData.type" @change="checkboxChange()">
        <el-checkbox
          :label="item.id"
          :disabled="
            selectedData.length >= 3 && userStyle && configData.userType && !selectedData.includes(item.id)
              ? true
              : false
          "
          v-for="(item, index) in configData.list"
          :key="index"
        >
          <span>{{ item.name }}</span>
        </el-checkbox>
      </el-checkbox-group>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_checkbox',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  data() {
    return {
      formData: {
        type: 0,
      },
      defaults: {},
      configData: {},
      selectedData: [],
      userStyle: 0,
    };
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
        this.userStyle = nVal.styleConfig.tabVal;
        this.selectedData = nVal.checkboxInfo.type;
      },
      deep: true,
    },
    'configObj.styleConfig.tabVal': {
      handler(nVal, oVal) {
        if (this.configData.userType) {
          this.configData.type = [3, 1, 2];
        }
      },
    },
    'configObj.storeStyleConfig.tabVal': {
      handler(nVal, oVal) {
        if (this.configData.storeType) {
          if (nVal == 1) {
            this.configData.list = [
              {
                id: 0,
                name: '配送方式',
              },
              {
                id: 2,
                name: '门店距离',
              },
              {
                id: 3,
                name: '门店地址',
              },
            ];
          } else {
            this.configData.list = [
              {
                id: 0,
                name: '配送方式',
              },
              {
                id: 1,
                name: '营业时间',
              },
              {
                id: 2,
                name: '门店距离',
              },
              {
                id: 3,
                name: '门店地址',
              },
            ];
          }
        }
      },
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
      this.userStyle = this.defaults.styleConfig.tabVal;
      this.selectedData = this.defaults.checkboxInfo.type;
    });
  },
  methods: {
    checkboxChange(e) {
      // this.$emit('getConfig', e);
    },
  },
};
</script>

<style scoped lang="scss">
.checkboxs {
  padding: 0 15px;
  margin-bottom: 20px;
}
.title-tips {
  margin-right: 14px;
  color: #999;
  font-size: 12px;
  width: 82px;
}
.checkbox-box {
  width: 270px;
}
.ivu-checkbox-group-item {
  margin-bottom: 15px;
  margin-right: 15px;
}
::v-deep.ivu-checkbox {
  margin-right: 4px;
}
::v-deep.ivu-checkbox-group-item {
  font-size: 12px;
}
::v-deep.ivu-checkbox-wrapper:nth-last-child(1) {
  margin-right: 0;
}
</style>
