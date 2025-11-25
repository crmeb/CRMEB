<template>
  <div>
    <el-card shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          v-if="search.length"
          ref="curlFrom"
          :model="from"
          :label-width="labelWidth"
          :label-position="labelPosition"
          inline
          @submit.native.prevent
        >
          <el-form-item :label="item.name + ':'" v-for="(item, index) in search" :key="index">
            <el-input
              v-if="item.type === 'input'"
              v-model="from[item.field]"
              :placeholder="'请输入' + item.name"
              class="form_content_width"
              @input="change($event)"
            />
            <el-date-picker
              v-else-if="item.type === 'date-picker'"
              :editable="false"
              clearabl
              @change="searchs"
              v-model="from[item.field]"
              format="yyyy/MM/dd"
              type="daterange"
              value-format="yyyy/MM/dd"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              style="width: 250px"
            ></el-date-picker>
            <el-select
              v-else-if="item.type === 'select'"
              v-model="from[item.field]"
              placeholder="请选择"
              clearable
              @change="searchs"
              class="form_content_width"
            >
              <el-option v-for="(val, i) in item.option" :value="val.value" :label="val.label" :key="i"></el-option>
            </el-select>
          </el-form-item>
          <!-- <template v-for="(item, index) in search">
            <el-form-item :label="item.name + ':'" label-for="name" v-if="item.type === 'input'" :key="index">
              <el-input v-model="from[item.field]" :placeholder="'请输入' + item.name" class="form_content_width"/>
            </el-form-item>
            <el-form-item :label="item.name + ':'" v-else-if="item.type === 'date-picker'" :key="index">
              <el-date-picker
                  :editable="false"
                  clearabl
                  @change="searchs"
                  v-model="from[item.field]"
                  format="yyyy/MM/dd"
                  type="daterange"
                  value-format="yyyy/MM/dd"
                  start-placeholder="开始日期"
                  end-placeholder="结束日期"
                  style="width: 250px"
              ></el-date-picker>
            </el-form-item>
            <el-form-item
                :label="item.name + ':'"
                :label-for="item.field"
                v-else-if="item.type === 'select'"
                :key="index"
            >
              <el-select
                  v-model="from[item.field]"
                  placeholder="请选择"
                  clearable
                  @change="searchs"
                  class="form_content_width"
              >
                <el-option v-for="(val, i) in item.option" :value="val.value" :label="val.label" :key="i"></el-option>
              </el-select>
            </el-form-item>
          </template> -->
          <el-form-item>
            <el-button type="primary" v-db-click @click="searchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card shadow="never" class="ivu-mt" :class="search.length ? 'mt16' : ''">
      <el-row>
        <el-col v-bind="grid">
          <el-button type="primary" v-db-click @click="add">添加</el-button>
        </el-col>
      </el-row>
      <el-table
        :data="dataList"
        ref="table"
        class="mt14"
        v-loading="loading"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column :label="item.title" :min-width="item.minWidth" v-for="(item, index) in columns" :key="index">
          <template slot-scope="scope">
            <template v-if="item.key">
              <span>{{ scope.row[item.key] }}</span>
            </template>
            <template v-else-if="item.from_type == 'frameImageOne'">
              <div class="tabBox_img" v-viewer>
                <img v-lazy="scope.row[item.slot]" />
              </div>
            </template>
            <template v-else-if="item.from_type == 'frameImages'">
              <div class="frame-images">
                <div class="tabBox_img" v-viewer v-for="(item, index) in scope.row[item.slot]" :key="index">
                  <img v-lazy="item" />
                </div>
              </div>
            </template>
            <template v-else-if="item.from_type == 'dateTimeRange'">
              <span>{{ scope.row[item.slot][0] }}--{{ scope.row[item.slot][1] }}</span>
            </template>
            <template v-else-if="item.slot === 'action'">
              <a v-db-click @click="show(scope.row)">详情</a>
              <el-divider direction="vertical" />
              <a v-db-click @click="edit(scope.row)">修改</a>
              <el-divider direction="vertical"></el-divider>
              <a v-db-click @click="del(scope.row, '删除', scope.$index)">删除</a>
            </template>
            <template v-else-if="item.from_type === 'switches'">
              <el-switch
                :active-value="1"
                :inactive-value="0"
                v-model="scope.row[item.slot]"
                :value="scope.row[item.slot]"
                size="large"
                @change="onchangeIsShow(scope.row, item.slot)"
              >
              </el-switch>
            </template>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination v-if="total" :total="total" :page.sync="from.page" :limit.sync="from.limit" @pagination="getList" />
      </div>
    </el-card>

    <el-dialog title="查看详情" :visible.sync="dialogTableVisible" v-if="dialogTableVisible">
      <el-descriptions :title="readFields.name">
        <el-descriptions-item :label="item.comment" v-for="(item, index) in readFields.all" :key="index">
          <div v-if="item.from_type == 'frameImageOne'">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="info[item.field]" />
            </div>
          </div>
          <div v-else-if="item.from_type == 'frameImages'">
            <div class="frame-images">
              <div class="tabBox_img" v-viewer v-for="(item, index) in info[item.field]" :key="index">
                <img v-lazy="item" />
              </div>
            </div>
          </div>
          <div v-else-if="item.from_type == 'dateTimeRange'">
            <span>{{ info[item.field][0] }}--{{ info[item.field][1] }}</span>
          </div>
          <div v-else>{{ info[item.field] }}</div>
        </el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { crudApi, getList, getCreateApi, getStatusApi, getEditApi } from '@/api/crud.js';

export default {
  name: 'crud_index',
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      loading: false,
      columns: [],
      readFields: {
        name: '',
        all: [],
      },
      from: {
        page: 1,
        limit: 15,
      },
      dataList: [],
      total: 0,
      methodApi: {},
      curdKey: '',
      dialogTableVisible: false,
      info: {},
      search: [],
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
  beforeRouteUpdate(to, from, next) {
    this.from.page = 1;
    this.getCrudApi(to.params.table_name);
    next();
  },
  created() {
    this.getCrudApi(this.$route.params.table_name);
  },
  methods: {
    show(row) {
      let url = this.methodApi.read.replace('<id>', row.id);
      getCreateApi(url)
        .then((res) => {
          this.dialogTableVisible = true;
          this.info = res.data;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    onchangeIsShow(row, field) {
      let url = this.methodApi.status.replace('<id>', row.id);
      getStatusApi(url, { field: field, value: row[field] })
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 表格搜索
    searchs() {
      this.from.page = 1;
      this.getList();
    },
    change(e) {
      this.$forceUpdate();
    },
    getCrudApi(tableName) {
      crudApi(tableName).then((res) => {
        this.methodApi = res.data.route;
        this.curdKey = res.data.key;
        this.readFields = res.data.readFields;
        res.data.search.map((item) => {
          this.from[item.field] = '';
        });
        this.search = res.data.search;
        res.data.columns.push({
          title: '操作',
          slot: 'action',
          fixed: 'right',
          width: 100,
          align: 'center',
        });
        res.data.columns.map((item) => {
          if (item.from_type === 'frameImageOne') {
            item.render = (h, params) => {
              return h(
                'div',
                {
                  class: 'tabBox_img',
                  directives: [
                    {
                      name: 'viewer',
                    },
                  ],
                },
                [
                  h('img', {
                    directives: [
                      {
                        name: 'lazy',
                        value: params.row[item.slot],
                      },
                    ],
                  }),
                ],
              );
            };
          } else if (item.from_type === 'frameImages') {
            item.render = (h, params) => {
              let image = params.row[item.slot] || [];
              let imageH = [];
              image.map((item) => {
                imageH.push(
                  h('img', {
                    directives: [
                      {
                        name: 'lazy',
                        value: item,
                      },
                    ],
                  }),
                );
              });
              return h(
                'div',
                {
                  class: 'tabBox_img',
                  directives: [
                    {
                      name: 'viewer',
                    },
                  ],
                },
                imageH,
              );
            };
          }
        });
        this.columns = res.data.columns;
        this.getList();
      });
    },
    // 添加
    add() {
      let url = this.methodApi.create;
      this.$modalForm(getCreateApi(url)).then(() => this.getList());
    },
    //列表
    getList() {
      this.loading = true;
      let url = this.methodApi.index;
      getList(url, this.from)
        .then(async (res) => {
          let data = res.data;
          this.dataList = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 修改
    edit(row) {
      let url = this.methodApi.edit.replace('<id>', row[this.curdKey]);
      this.$modalForm(getEditApi(url)).then(() => this.getList());
    },
    // 删除
    del(row, tit, num) {
      let url = this.methodApi.delete.replace('<id>', row[this.curdKey]);
      let delfromData = {
        title: tit,
        num: num,
        url: url,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="scss">
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;
  margin: 1px;

  img {
    width: 100%;
    height: 100%;
  }
}

.frame-images {
  display: flex;
  flex-wrap: wrap;
}
</style>
