<template>
  <div class="box" :class="configData.type == 'form' ? '' : 'on3'" v-if="configData">
    <div class="c_row-item" :class="{ on: configData.type == 'form', on2: configData.type == 'ranges' }">
      <el-col class="label" :span="configData.type == 'ranges' ? '' : 4">
        {{ configData.title }}
      </el-col>
      <el-col :span="configData.type == 'ranges' ? 24 : configData.type == 'form' ? 19 : 18" class="slider-box">
        <el-input v-model="configData.value" :placeholder="configData.place" :maxlength="configData.max">
          <i v-if="configData.title == '链接'" class="el-icon-link" slot="suffix" @click="getLink(configData)" />
        </el-input>
      </el-col>
    </div>
    <linkaddress
      ref="linkaddres"
      @linkUrl="linkUrl"
      v-if="configData.type != 'form' && (configData.title == '链接' || configData.type == 'link')"
    ></linkaddress>
  </div>
</template>

<script>
import linkaddress from '@/components/linkaddress';
export default {
  name: 'c_input_item',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  components: {
    linkaddress,
  },
  data() {
    return {
      value: '',
      defaults: {},
      configData: {},
    };
  },
  created() {
    this.defaults = this.configObj;
    this.configData = this.configObj[this.configNme];
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    linkUrl(e) {
      this.configData.value = e;
    },
    getLink(configData) {
      if (configData.title != '链接' && configData.type != 'link') {
        return;
      }
      this.$refs.linkaddres.modals = true;
    },
  },
};
</script>

<style scoped lang="scss">
::v-deep .ivu-input {
  font-size: 13px !important;
}

.box {
  &.on3 {
    padding: 0 15px;

    ::v-deep .ivu-input {
      font-size: 12px !important;
    }

    .label {
      font-size: 12px;
      color: #999;
    }

    ::v-deep .ivu-input-icon {
      color: #bbbbbb;
    }
  }
}

.c_row-item {
  margin-bottom: 13px;

  &.on {
    margin-bottom: 20px;

    .label {
      text-align: right;
      color: #666;
    }
  }

  &.on2 {
    display: block;

    .label {
      margin-bottom: 5px;
      color: #666;
    }
  }
}
</style>
