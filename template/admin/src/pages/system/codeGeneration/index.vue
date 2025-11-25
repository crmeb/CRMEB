<template>
  <div class="code-wapper">
    <pages-header
      ref="pageHeader"
      :title="$route.meta.title"
      :backUrl="$routeProStr + '/system/code_generation_list'"
    ></pages-header>
    <div class="message mt10">
      <el-card :bordered="false" shadow="never" class="">
        <steps :stepList="headerList" :isActive="currentTab"></steps>
      </el-card>
    </div>
    <div class="pt10 tab-1" v-show="currentTab == '0'" v-loading="isLoading">
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <FoundationForm
          ref="Foundation"
          :foundation="formItem.foundation"
          :tableField="tableField"
          @storageData="storageData"
        />
      </el-card>
    </div>
    <div class="pt10" v-show="currentTab == '1'">
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <TableForm
          ref="TableForm"
          :foundation="formItem.foundation"
          :tableField="tableField"
          :id="id"
          @storageData="storageData"
        />
      </el-card>
    </div>
    <div class="pt10" v-show="currentTab == '2'">
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <StorageLoc :storage="formItem.storage" />
      </el-card>
    </div>
    <el-card :bordered="false" class="fixed-card" :style="{ left: `${fixBottomWidth}` }" shadow="never">
      <el-button :disabled="!currentTab" class="mr20" v-db-click @click="beforeTab">上一步</el-button>
      <el-button type="primary" v-db-click @click="nextTab">{{ currentTab == 2 ? '提交' : '下一步' }}</el-button>
    </el-card>
  </div>
</template>

<script>
import { codeCrud } from '@/api/setting';
import FoundationForm from './components/FoundationFor.vue';
import TableForm from './components/TableForm.vue';
import StorageLoc from './components/StorageLoc.vue';
import { getMenusUnique } from '@/api/systemMenus';
import { formatFlatteningRoutes } from '@/libs/system';
import { crudFilePath } from '@/api/systemCodeGeneration';
import { crudDet } from '@/api/systemCodeGeneration';
import { setStatus } from '@api/diy';
import steps from '@/components/steps/index';

export default {
  name: 'system_code_generation',
  components: { FoundationForm, StorageLoc, TableForm, steps },
  data() {
    return {
      currentTab: 0,
      headerList: ['基础信息', '字段配置', '存放位置'],
      formItem: {
        foundation: {
          pid: '',
          tableName: '',
          modelName: '',
          isTable: 1,
          menuName: '',
        },
        tableForm: {},
        storage: {},
        field: {},
        formItem: {},
      },
      ruleValidate: {
        foundation: {},
      },
      tableField: [],
      rowList: [],
      reqloading: false,
      isLoading: false,
      id: '',
    };
  },
  computed: {
    // 设置是否显示 tagsView
    fixBottomWidth() {
      let { layout, isCollapse } = this.$store.state.themeConfig.themeConfig;
      let w;
      if (['columns'].includes(layout)) {
        if (isCollapse) {
          w = '85px';
        } else {
          w = '265px';
        }
      } else if (['classic'].includes(layout)) {
        if (isCollapse) {
          w = '69px';
        } else {
          w = '190px';
        }
      } else if (['defaults', 'classic'].includes(layout)) {
        if (isCollapse) {
          w = '64px';
        } else {
          w = '180px';
        }
      } else {
        w = '0px';
      }
      return w;
    },
  },
  created() {
    if (this.$route.query.id) {
      this.id = this.$route.query.id;
      this.getDetail(this.$route.query.id);
    }
  },
  mounted: function () {},
  methods: {
    getDetail(id) {
      this.isLoading = true;
      crudDet(id)
        .then((res) => {
          let data = res.data.crudInfo.field;
          this.formItem.foundation.pid = Number(data.pid);
          this.formItem.foundation.tableName = data.tableName;
          this.formItem.foundation.modelName = data.modelName;
          this.formItem.foundation.menuName = data.menuName;
          this.$refs.TableForm.tableField = data.tableField;
          this.formItem.storage = data.filePath;
          let i = 0;
          data.tableField.map((e) => {
            if (e.field === 'create_time' || e.field === 'update_time') {
              i++;
              if (i == 2) this.$refs.TableForm.isCreate = true;
            }
            if (e.field === 'delete_time') {
              this.$refs.TableForm.isDelete = true;
            }
          });
          this.isLoading = false;
        })
        .catch((err) => {
          this.$message.warning(err.msg);
        });
    },
    storageData(data) {
      this.formItem.storage = data;
    },
    beforeTab() {
      this.currentTab--;
    },
    addRow() {
      let foundation = this.formItem.foundation;
      if (!foundation.tableName) return this.$message.warning('请先填写表名');
      let data = {
        menuName: foundation.menuName,
        tableName: foundation.tableName,
        // isTable: foundation.isTable,
        fromField: [],
        columnField: [],
      };
      crudFilePath(data)
        .then((res) => {
          this.$refs.TableForm.tableField = res.data.tableField.length ? res.data.tableField : [];
          this.formItem.storage = res.data.makePath;
          if (!res.data.tableField.length) {
            this.$refs.TableForm.tableField.push({
              field: 'id',
              field_type: 'int',
              default: '',
              comment: '自增ID',
              required: false,
              is_table: true,
              table_name: 'ID',
              limit: '15',
              primaryKey: 1,
              from_type: '',
            });
          }
          this.currentTab++;
        })
        .catch((err) => {
          this.$message.warning(err.msg);
        });
    },
    nextTab() {
      if (this.currentTab == 0) {
        // if (!this.formItem.foundation.pid) return this.$message.warning('请选择菜单');
        if (!this.formItem.foundation.tableName) return this.$message.warning('请输入表名');
        if (!this.formItem.foundation.modelName) return this.$message.warning('请输入模块名');
        if (!this.formItem.foundation.isTable) {
          if (!this.$refs.TableForm.tableField.length) return this.$message.warning('请先添加表数据');
          if (this.$refs.TableForm.tableField.length)
            for (let i = 0; i < this.$refs.TableForm.tableField.length; i++) {
              const el = this.$refs.TableForm.tableField[i];
              if (
                ['addSoftDelete', 'addTimestamps'].indexOf(el.field_type) === -1 &&
                (!el.field || !el.field_type || !el.comment)
              ) {
                return this.$message.warning('请完善sql表数据');
              }
            }
        }
        if (this.id) {
          return this.currentTab++;
        }
        this.addRow();
      } else if (this.currentTab == 2) {
        if (this.reqloading) return;
        let data = {
          ...this.formItem.foundation,
          filePath: this.formItem.storage,
          tableField: this.$refs.TableForm.tableField,
          deleteField: this.id ? this.$refs.TableForm.deleteField : [],
        };
        if (this.id) {
          data.id = this.id;
          this.$msgbox({
            title: '生成提醒',
            message: '重新提交会重新生成文件,删除、新增、修改的字段将直接从改表中进行修改,请慎重操作！！',
            showCancelButton: true,
            cancelButtonText: '取消',
            confirmButtonText: '确定',
            iconClass: 'el-icon-warning',
            confirmButtonClass: 'btn-custom-cancel',
          })
            .then(() => {
              this.saveCodeCrud(data, true);
            })
            .catch(() => {});
        } else {
          this.$msgbox({
            title: '生成提醒',
            message:
              '生成后本地开发调试会直接加载生成的vue页面；如果是上线后进行生成,可以进行浏览，代码生成列表中的修改文件将不生效。需要重新打包上线！',
            showCancelButton: true,
            cancelButtonText: '取消',
            confirmButtonText: '确定',
            iconClass: 'el-icon-warning',
            confirmButtonClass: 'btn-custom-cancel',
          })
            .then(() => {
              this.saveCodeCrud(data, true);
            })
            .catch(() => {});
        }
      } else {
        if (this.currentTab < 3) this.currentTab++;
      }
    },
    saveCodeCrud(data, loading) {
      this.reqloading = true;
      codeCrud(data)
        .then((res) => {
          this.$message.success(res.msg);
          this.getMenusUnique();
          this.reqloading = false;
          this.$router.push({
            name: 'system_code_generation_list',
          });
        })
        .catch((err) => {
          this.reqloading = false;
          this.$message.error(err.msg);
        });
    },
    getMenusUnique() {
      getMenusUnique().then((res) => {
        let data = res.data;
        this.$store.commit('userInfo/uniqueAuth', data.uniqueAuth);
        this.$store.commit('menus/getmenusNav', data.menus);
        this.$store.dispatch('routesList/setRoutesList', data.menus);
        let arr = formatFlatteningRoutes(this.$router.options.routes);
        this.formatTwoStageRoutes(arr);
        let routes = formatFlatteningRoutes(data.menus);
        this.$store.commit('menus/setOneLvRoute', routes);
        this.bus.$emit('routesListChange');
      });
    },
    formatTwoStageRoutes(arr) {
      if (arr.length <= 0) return false;
      const newArr = [];
      const cacheList = [];
      arr.forEach((v) => {
        if (v && v.meta && v.meta.keepAlive) {
          newArr.push({ ...v });
          cacheList.push(v.name);
          this.$store.dispatch('keepAliveNames/setCacheKeepAlive', cacheList);
        }
      });
      return newArr;
    },
  },
};
</script>
<style lang="scss" scoped>
.ivu-steps .ivu-steps-title {
  line-height: 26px;
}
.code-wapper {
  min-height: 800px;
  padding-bottom: 90px;
}
.btn {
  position: fixed;
  bottom: 10px;
  // height: 80px;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  background-color: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(4px);
  z-index: 2;
}
.tab-1 {
  padding-bottom: 100px;
}
::v-deep .el-input__inner {
  padding-left: 7px;
}
::v-deep .ivu-form-item {
  margin-bottom: 17px;
}
::v-deep .ivu-form-item-error-tip {
  padding-top: 2px;
}
::v-deep .tip {
  color: #bbb;
  line-height: 16px;
  padding-top: 5px;
  font-size: 12px;
}
</style>
