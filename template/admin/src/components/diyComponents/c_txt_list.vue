<template>
  <div class="c_product" v-if="datas">
    <div class="title">{{ datas[name].title }}</div>
    <div class="list-box">
      <draggable class="dragArea list-group" :list="datas[name].list" group="peoples" handle=".move-icon">
        <div class="item" v-for="(item, index) in datas[name].list" :key="index">
          <div class="move-icon">
            <span class="iconfont icondrag2"></span>
          </div>
          <div class="content">
            <div class="con-item" v-for="(list, key) in item.chiild" :key="key">
              <div class="dif" v-if="list.title === '链接'">
                <el-col class="label" :span="4">
                  <span>{{ list.title }}</span>
                </el-col>
                <el-col class="slider-box">
                  <div class="input-box" v-db-click>
                    <el-input v-model="list.val" :placeholder="list.pla" :maxlength="list.max">
                      <i class="el-icon-link" slot="suffix" @click="getLink(index, key)" />
                    </el-input>
                  </div>
                </el-col>
              </div>
              <div class="dif" v-else>
                <el-col class="label" :span="4">
                  <span>{{ list.title }}</span>
                </el-col>
                <el-col :span="19" class="slider-box">
                  <el-input
                    v-model="list.val"
                    :placeholder="list.pla"
                    :maxlength="list.max"
                    style="text-align: right"
                  />
                </el-col>
              </div>
              <!-- <span>{{ list.title }}</span>
              <el-input
                v-model="list.val"
                :placeholder="list.pla"
                :maxlength="list.max"
              /> -->
            </div>
            <div class="con-item" v-if="item.link">
              <span>{{ item.link.title }}</span>
              <el-select v-model="item.link.activeVal" style="">
                <el-option
                  v-for="(item, j) in item.link.optiops"
                  :value="item.value"
                  :key="j"
                  :label="item.label"
                ></el-option>
              </el-select>
            </div>
          </div>
          <div class="delete" v-db-click @click.stop="bindDelete(index)" v-if="datas[name].max > 1">
            <i class="el-icon-circle-close" style="font-size: 24px"></i>
          </div>
        </div>
      </draggable>
    </div>
    <div v-if="datas[name]">
      <div class="add-btn" v-db-click @click="addHotTxt" v-if="datas[name].list.length < datas[name].max">
        <el-button
          type="primary"
          ghost
          style="width: 100%; height: 40px; border-color: var(--prev-color-primary); color: var(--prev-color-primary)"
          >添加模块</el-button
        >
      </div>
    </div>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import vuedraggable from 'vuedraggable';
import linkaddress from '@/components/linkaddress';

export default {
  name: 'c_txt_list',
  props: {
    name: {
      type: String,
    },
    configData: {
      type: null,
    },
    configNum: {
      type: Number | String,
      default: 'default',
    },
  },
  components: {
    draggable: vuedraggable,
    linkaddress,
  },
  data() {
    return {
      defaults: {},
      itemObj: {},
      activeIndex: 0,
      keyIndex: 0,
      datas: this.configData[this.configNum],
    };
  },
  mounted() {},
  watch: {
    configData: {
      handler(nVal, oVal) {
        this.datas = nVal[this.configNum];
      },
      deep: true,
    },
  },
  methods: {
    getLink(index, key) {
      this.activeIndex = index;
      this.keyIndex = key;
      this.$refs.linkaddres.modals = true;
    },
    linkUrl(e) {
      this.datas[this.name].list[this.activeIndex].chiild[this.keyIndex].val = e;
    },
    addHotTxt() {
      let val = {
        chiild: [
          {
            max: 20,
            pla: '选填，不超过四个字',
            title: '标题',
            val: '',
          },
          {
            max: 99,
            pla: '选填',
            title: '链接',
            val: '',
          },
        ],
      };
      if (this.name == 'newList') {
        let arrs = this.datas[this.name].list[this.datas[this.name].list.length - 1];
        let obj = arrs ? JSON.parse(JSON.stringify(arrs)) : '';
        this.datas[this.name].list.push(obj || val);
        return;
      }
      if (this.datas[this.name].list.length == 0) {
        let txtListData = this.$store.state.userInfo.txtListData;
        this.datas[this.name].list.push(txtListData);
      } else {
        let obj = JSON.parse(JSON.stringify(this.datas[this.name].list[this.datas[this.name].list.length - 1]));
        this.datas[this.name].list.push(obj);
      }
    },
    // 删除数组
    bindDelete(index) {
      if (this.datas[this.name].list.length == 1) {
        let itemObj = this.datas[this.name].list[0];
        this.$store.commit('userInfo/txtList', itemObj);
      }
      this.datas[this.name].list.splice(index, 1);
    },
  },
};
</script>

<style lang="scss" scoped>
.icondrag2 {
  font-size: 26px;
  color: #d8d8d8;
}
.c_product {
  margin-bottom: 20px;
  .list-box {
    .item {
      position: relative;
      display: flex;
      margin-top: 23px;
      padding: 18px 20px 18px 0;
      border: 1px solid rgba(238, 238, 238, 1);
      .delete {
        position: absolute;
        right: 0;
        top: 0;
        right: -13px;
        top: -14px;
        color: #999999;
        cursor: pointer;
      }
    }
    .move-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 50px;
      cursor: move;
    }
    .content {
      flex: 1;
      .con-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        width: 300px;
        &:last-child {
          margin-bottom: 0;
        }

        span {
          width: 45px;
          font-size: 13px;
        }
      }
    }
  }
  .add-btn {
    margin-top: 18px;
  }
}
.title {
  font-size: 12px;
  color: #999;
}
.iconfont {
  color: #dddddd;
  font-size: 28px;
}
.dif {
  display: flex;
  align-items: center;
}
.slider-box {
  margin-left: 10px;
  width: 250px;
}
</style>
