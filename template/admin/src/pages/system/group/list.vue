<template>
  <div>
    <!--    <div class="i-layout-page-header header-title">-->
    <!--      <div class="fl_header">-->
    <!--        <router-link v-if="$route.params.id != 49" :to="{ path: $routeProStr + '/system/config/system_group/index' }"-->
    <!--          ><el-button size="small" type="text">返回</el-button></router-link-->
    <!--        >-->
    <!--        <el-divider direction="vertical" v-if="$route.params.id != 49" />-->
    <!--        <span class="ivu-page-header-title mr20" style="padding: 0" v-text="$route.meta.title"></span>-->
    <!--      </div>-->
    <!--    </div>-->
    <pages-header class="mb16" ref="pageHeader" :title="$route.meta.title"></pages-header>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
          inline
        >
          <el-form-item label="是否显示：">
            <el-select
              v-model="formValidate.status"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="1" label="显示"></el-option>
              <el-option value="0" label="不显示"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt14">
      <el-button type="primary" v-db-click @click="groupAdd('添加数据')" class="mr20">添加数据</el-button>
      <el-table
        :data="tabList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column :label="item.title" :min-width="item.minWidth" v-for="(item, index) in columns1" :key="index">
          <template slot-scope="scope">
            <template v-if="item.key">
              <div v-if="item.type !== 'img'">
                <span>{{ scope.row[item.key] }}</span>
              </div>
              <div v-else>
                <div class="tabBox_img" v-viewer>
                  <img v-lazy="scope.row[item.key][0]" />
                </div>
              </div>
            </template>
            <template v-else-if="item.slot === 'status'">
              <el-switch
                :active-value="1"
                :inactive-value="0"
                v-model="scope.row.status"
                :value="scope.row.status"
                @change="onchangeIsShow(scope.row)"
                size="large"
              >
              </el-switch>
            </template>
            <template v-else-if="item.slot === 'action'">
              <a v-db-click @click="edit(scope.row, '编辑')">编辑</a>
              <el-divider direction="vertical"></el-divider>
              <a v-db-click @click="del(scope.row, '删除这条信息', scope.$index)">删除</a>
            </template>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="formValidate.page"
          :limit.sync="formValidate.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import editFrom from '@/components/from/from';
import {
  groupDataListApi,
  groupDataAddApi,
  groupDataEditApi,
  groupDataHeaderApi,
  groupDataSetApi,
  groupAllApi,
} from '@/api/system';
export default {
  name: 'list',
  components: { editFrom },
  data() {
    return {
      treeId: '',
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      formValidate: {
        status: '',
        page: 1,
        limit: 20,
        gid: 0,
      },
      total: 0,
      tabList: [],
      columns1: [],
      FromData: null,
      loading: false,
      titleType: 'group',
      groupAll: [],
      theme3: 'light',
      labelSort: [],
      sortName: null,
      current: 0,
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  watch: {
    $route(to, from) {
      if (this.$route.params.id) {
        this.getList();
        this.getListHeader();
      } else {
        this.getGroupAll();
      }
    },
  },
  mounted() {
    if (this.$route.params.id) {
      this.getList();
      this.getListHeader();
    } else {
      this.getGroupAll();
    }
  },
  methods: {
    bindMenuItem(name, index) {
      this.current = index;
      this.formValidate.gid = name.id;
      this.getListHeader();
      this.getList();
    },
    getGroupAll() {
      groupAllApi()
        .then(async (res) => {
          this.groupAll = res.data;
          this.sortName = res.data[0].id;
          this.formValidate.gid = res.data[0].id;
          this.getListHeader();
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 返回
    back() {
      this.$router.push({ path: this.$routeProStr + '/system/config/system_group/index' });
    },
    getUrl(type) {
      let url = 'setting/group_data' + type;
      if (this.$route.params.id) {
        let arr = {
          setting_groupDataSign: 'setting/sign_data' + type,
          setting_groupDataOrder: 'setting/order_data' + type,
          setting_groupDataUser: 'setting/usermenu_data' + type,
          setting_groupDataPoster: 'setting/poster_data' + type,
          marketing_storeSeckillData: 'setting/seckill_data' + type,
        };
        if (arr[this.$route.name] === undefined) return url;
        return arr[this.$route.name];
      } else {
        return url;
      }
    },
    // 列表
    getList() {
      this.loading = true;
      this.formValidate.gid = this.$route.params.id ? this.$route.params.id : this.formValidate.gid;
      this.formValidate.status = this.formValidate.status || '';
      groupDataListApi(this.formValidate, this.getUrl(''))
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 表格头部
    getListHeader() {
      this.loading = true;
      let data = {
        gid: this.$route.params.id ? this.$route.params.id : this.formValidate.gid,
      };
      groupDataHeaderApi(data, this.getUrl('/header'))
        .then(async (res) => {
          let data = res.data;
          let header = data.header;
          let index = [];
          header.forEach(function (item, i) {
            if (item.type === 'img') {
              index.push(i);
            }
          });
          index.forEach(function (item) {
            header[item].render = (h, params) => {
              let arr = params.row[header[item].key];
              let newArr = [];
              if (arr !== undefined && arr.length) {
                arr.forEach(function (e, i) {
                  newArr.push(
                    h(
                      'div',
                      {
                        style: {
                          width: '36px',
                          height: '36px',
                          'border-radius': '4px',
                          cursor: 'pointer',
                          display: 'inline-block',
                        },
                      },
                      [
                        h('img', {
                          attrs: {
                            src: params.row[header[item].key][i],
                          },
                          style: {
                            width: '100%',
                            height: '100%',
                          },
                        }),
                      ],
                    ),
                  );
                });
              }
              return h('viewer', newArr);
            };
          });
          this.columns1 = header;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 表格搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
    // 添加表单
    groupAdd() {
      this.$modalForm(
        groupDataAddApi(
          { gid: this.$route.params.id ? this.$route.params.id : this.formValidate.gid },
          this.getUrl('/create'),
        ),
      ).then(() => this.getList());
    },
    // 修改是否显示
    onchangeIsShow(row) {
      groupDataSetApi(this.getUrl(`/set_status/${row.id}/${row.status}`))
        .then(async (res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 编辑
    edit(row) {
      let data = {
        gid: row.gid,
      };
      this.$modalForm(groupDataEditApi(data, this.getUrl(`/${row.id}/edit`))).then(() => this.getList());
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: this.getUrl(`/${row.id}`),
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.tabList.splice(num, 1);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
::v-deep .ivu-menu-vertical .ivu-menu-item-group-title {
  display: none;
}
::v-deep .ivu-menu-vertical.ivu-menu-light:after {
  display: none;
}
.left-wrapper {
  height: 904px;
  background: #fff;
  border-right: 1px solid #dcdee2;
}
.menu-item {
  z-index: 50;
  position: relative;
  display: flex;
  justify-content: space-between;
  word-break: break-all;
  .icon-box {
    z-index: 3;
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    display: none;
  }
  &:hover .icon-box {
    display: block;
  }
  .right-menu {
    z-index: 10;
    position: absolute;
    right: -106px;
    top: -11px;
    width: auto;
    min-width: 121px;
  }
}
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;
  img {
    width: 100%;
    height: 100%;
  }
}
</style>
