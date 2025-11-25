<template>
  <div>
    <el-row class="ivu-mt box-wrapper">
      <el-col v-bind="grid1" class="left-wrapper">
        <div class="tree_tit" v-db-click @click="addSort">
          <i class="el-icon-circle-plus"></i>
          添加分组
        </div>
        <div class="tree">
          <el-tree
            :data="labelSort"
            node-key="id"
            default-expand-all
            highlight-current
            :expand-on-click-node="false"
            @node-click="bindMenuItem"
            :current-node-key="treeId"
          >
            <span class="custom-tree-node" slot-scope="{ data }">
              <div class="file-name">
                <img class="icon" src="@/assets/images/file.jpg" />
                <el-tooltip class="item" effect="dark" :content="data.name" placement="top">
                  <div class="text line1">
                    {{ data.name }}
                  </div>
                </el-tooltip>
              </div>
              <span v-if="data.id">
                <el-dropdown @command="(command) => clickMenu(data, command)">
                  <i class="el-icon-more el-icon--right"></i>
                  <template slot="dropdown">
                    <el-dropdown-menu>
                      <el-dropdown-item command="1">编辑分类</el-dropdown-item>
                      <el-dropdown-item v-if="data.id" command="2">删除分类</el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
              </span>
            </span>
          </el-tree>
        </div>
      </el-col>
      <el-col v-bind="grid2" ref="rightBox">
        <el-card :bordered="false" shadow="never">
          <el-row>
            <el-col>
              <el-button type="primary" v-db-click @click="add">添加标签</el-button>
            </el-col>
          </el-row>
          <el-table
            :data="labelLists"
            ref="table"
            class="mt14"
            v-loading="loading"
            highlight-current-row
            no-userFrom-text="暂无数据"
            no-filtered-userFrom-text="暂无筛选结果"
          >
            <el-table-column label="ID" width="80">
              <template slot-scope="scope">
                <span>{{ scope.row.id }}</span>
              </template>
            </el-table-column>
            <!-- <el-table-column label="标签名称" width="80">
              <template slot-scope="scope">
                <span>{{ scope.row.name }}</span>
              </template>
            </el-table-column> -->
            <el-table-column label="标签名称" width="180">
              <template slot-scope="scope">
                <div
                  v-if="scope.row.type == 1"
                  class="words-tag"
                  :style="{
                    backgroundColor: scope.row.bg_color,
                    color: scope.row.font_color,
                    border: scope.row.border_color ? '1px solid ' + scope.row.border_color : 'none',
                  }"
                >
                  <span>{{ scope.row.name }}</span>
                </div>
                <img :src="scope.row.image" class="tag-img" v-else />
              </template>
            </el-table-column>
            <el-table-column label="分类名称" min-width="140">
              <template slot-scope="scope">
                <span>{{ scope.row.cate_name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="状态" min-width="140">
              <template slot-scope="scope">
                <el-switch
                  class="defineSwitch"
                  :active-value="1"
                  :inactive-value="0"
                  v-model="scope.row.status"
                  :value="scope.row.status"
                  @change="onchangeStatus(scope.row)"
                  size="large"
                  active-text="开启"
                  inactive-text="关闭"
                >
                </el-switch>
              </template>
            </el-table-column>
            <el-table-column label="移动端展示" min-width="140">
              <template slot-scope="scope">
                <el-switch
                  class="defineSwitch"
                  :active-value="1"
                  :inactive-value="0"
                  v-model="scope.row.is_show"
                  :value="scope.row.is_show"
                  @change="onchangeShow(scope.row)"
                  size="large"
                  active-text="开启"
                  inactive-text="关闭"
                >
                </el-switch>
              </template>
            </el-table-column>
            <el-table-column fixed="right" label="操作" width="100">
              <template slot-scope="scope">
                <a v-db-click @click="edit(scope.row.id)">修改</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="del(scope.row, '删除', scope.$index)">删除</a>
              </template>
            </el-table-column>
          </el-table>
          <div class="acea-row row-right page">
            <pagination
              v-if="total"
              :total="total"
              :page.sync="labelFrom.page"
              :limit.sync="labelFrom.limit"
              @pagination="getList"
            />
          </div>
        </el-card>
        <el-dialog :visible.sync="modals" closable :title="isEdit ? '编辑标签' : '添加标签'" width="560" @close="cancel">
          <div>
            <el-form label-position="right" size="small" ref="form" :rules="rules" :model="form" label-width="100px">
              <el-form-item label="标签名称：" prop="name">
                <el-input v-model="form.name" class="w-420"></el-input>
              </el-form-item>
              <el-form-item label="分组选择：" prop="label_cate">
                <el-select v-model="form.cate_id" clearable class="w-420">
                  <el-option
                    v-for="item in labelSort.slice(1)"
                    :value="item.id"
                    :label="item.name"
                    :key="item.id"
                  ></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="移动端展示：">
                <el-switch v-model="form.is_show" :active-value="1" :inactive-value="0"> </el-switch>
              </el-form-item>
              <el-form-item label="效果设置：">
                <el-radio-group v-model="form.type" :true-value="1" :false-value="2">
                  <el-radio :label="1">自定义</el-radio>
                  <el-radio :label="2">图片</el-radio>
                </el-radio-group>
              </el-form-item>
              <el-form-item label="字体颜色：" v-if="form.type == 1">
                <el-color-picker v-model="form.font_color" show-alpha></el-color-picker>
                <p class="tip">若未设置颜色，则为默认色</p>
              </el-form-item>
              <el-form-item label="背景颜色：" v-if="form.type == 1">
                <el-color-picker v-model="form.bg_color" show-alpha></el-color-picker>
                <p class="tip">若未设置颜色，则为默认色</p>
              </el-form-item>
              <el-form-item label="边框颜色：" v-if="form.type == 1">
                <el-color-picker v-model="form.border_color" show-alpha></el-color-picker>
                <p class="tip">若未设置颜色，则无边框</p>
              </el-form-item>
              <el-form-item label="上传图标：" v-if="form.type == 2">
                <div v-if="form.image" class="upload-list">
                  <div class="upload-item">
                    <img :src="form.image" />
                    <div class="close" @click="form.image = ''">
                      <i class="el-icon-close"></i>
                    </div>
                  </div>
                </div>
                <el-button
                  v-else
                  class="upload-select"
                  type="dashed"
                  icon="el-icon-plus"
                  @click="modalPicTap(1)"
                ></el-button>
                <p class="tip">建议尺寸：80px*30px，若未上传则为空白</p>
              </el-form-item>
              <el-form-item label="排序：">
                <el-input-number v-model="form.sort" :min="0" :max="999" class="selWidth"></el-input-number>
              </el-form-item>
              <el-form-item label="是否开启：">
                <el-switch v-model="form.status" :active-value="1" :inactive-value="0" size="large">
                  <span slot="open">开启</span>
                  <span slot="close">关闭</span>
                </el-switch>
              </el-form-item>
            </el-form>
          </div>
          <span slot="footer" class="dialog-footer">
            <el-button @click="cancel">取 消</el-button>
            <el-button type="primary" v-db-click @click="addWordsConfirm">确 定</el-button>
          </span>
        </el-dialog>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import {
  labelCateListApi,
  productLabelListApi,
  productLabelInfoApi,
  productLabelSaveApi,
  userLabelEdit,
  productLabelCateFormApi,
  labelStatusApi,
  labelIsShowApi,
} from '@/api/product';
export default {
  name: 'labelList',
  data() {
    return {
      treeId: '',
      grid1: {
        xl: 4,
        lg: 4,
        md: 6,
        sm: 8,
        xs: 0,
      },
      grid2: {
        xl: 20,
        lg: 20,
        md: 18,
        sm: 16,
        xs: 24,
      },

      loading: false,
      labelFrom: {
        page: 1,
        limit: 15,
        cate_id: '',
      },
      labelLists: [],
      total: 0,
      theme3: 'light',
      labelSort: [],
      sortName: '',
      current: 0,
      modals: false,
      isEdit: false,
      form: {
        id: 0,
        cate_id: '',
        name: '',
        type: 1, //样式类型 1自定义 2图片
        font_color: '#e93323',
        bg_color: '#fff',
        border_color: '#e93323',
        sort: 0,
        is_show: 1,
        image: '',
        status: 1,
      },
      rules: {
        name: [
          { required: true, message: '请输入标签名称', trigger: 'blur' },
          { min: 2, max: 6, message: '长度在 2 到 6 个字符', trigger: 'blur' },
        ],
        cate_id: [{ required: true, message: '请选择分组' }],
      },
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
  created() {
    this.getLabelLabelAll();
  },
  methods: {
    // 添加
    add() {
      this.modals = true;
      this.isEdit = false;
    },
    modalPicTap() {
      this.$imgModal((e) => {
        this.form.image = e.att_dir;
      });
    },
    // 分组列表
    getList() {
      this.loading = true;
      productLabelListApi(this.labelFrom)
        .then(async (res) => {
          let data = res.data;
          this.labelLists = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 修改
    edit(id) {
      productLabelInfoApi({ id: id }).then((res) => {
        this.form = res.data;
        this.isEdit = true;
        this.modals = true;
      });

      // this.$modalForm(userLabelAddApi(id)).then(() => this.getList());
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `product/label/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.labelLists.splice(num, 1);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 修改是否显示
    onchangeStatus(row) {
      labelStatusApi(row)
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    onchangeShow(row) {
      labelIsShowApi(row)
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },

    // 标签分类
    getLabelLabelAll(key) {
      labelCateListApi().then((res) => {
        let obj = {
          name: '全部',
          id: '',
        };
        res.data.unshift(obj);
        res.data.forEach((el) => {
          el.status = false;
        });
        if (!key) {
          this.sortName = res.data[0].id;
          this.labelFrom.cate_id = res.data[0].id;
          this.getList();
        }
        this.labelSort = res.data;
      });
    },
    // 显示标签小菜单
    showMenu(item) {
      this.labelSort.forEach((el) => {
        if (el.id == item.id) {
          el.status = item.status ? false : true;
        } else {
          el.status = false;
        }
      });
    },
    addWordsConfirm() {
      if (!this.form.cate_id) return this.$message.error('请选择分组');
      this.$refs.form.validate((valid) => {
        if (valid) {
          productLabelSaveApi(this.form)
            .then((res) => {
              this.$message.success(res.msg);
              this.modals = false;
              this.cancel();
              this.labelFrom.page = 1;
              this.getList();
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        }
      });
    },
    cancel() {
      this.form = {
        id: 0,
        cate_id: '',
        name: '',
        type: 1, //样式类型 1自定义 2图片
        font_color: '#e93323',
        bg_color: '#ffffff',
        border_color: '#e93323',
        sort: 0,
        is_show: 1,
        image: '',
        status: 1,
      };
      this.modals = false;
    },
    //编辑标签
    labelEdit(item) {
      this.$modalForm(productLabelCateFormApi(item.id)).then(() => this.getLabelLabelAll(1));
    },
    // 添加分类
    addSort() {
      this.$modalForm(productLabelCateFormApi(0)).then(() => this.getLabelLabelAll());
    },
    deleteSort(row, tit) {
      let num = this.labelSort.findIndex((e) => {
        return e.id == row.id;
      });
      let delfromData = {
        title: tit,
        num: num,
        url: `product/label_cate/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.labelSort.splice(num, 1);
          this.labelSort = [];
          this.getLabelLabelAll();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    clickMenu(data, name) {
      if (name == 1) {
        this.labelEdit(data);
      } else if (name == 2) {
        this.deleteSort(data, '删除分类');
      }
    },
    bindMenuItem(name, index) {
      this.labelFrom.page = 1;
      this.current = index;
      this.labelSort.forEach((el) => {
        el.status = false;
      });
      this.labelFrom.cate_id = name.id;
      this.form.cate_id = name.id;
      this.getList();
    },
  },
};
</script>

<style lang="scss" scoped>
.showOn {
  color: #2d8cf0;
  background: #f0faff;
  z-index: 2;
}

::v-deep .ivu-menu-vertical .ivu-menu-item-group-title {
  display: none;
}

::v-deep .ivu-menu-vertical.ivu-menu-light:after {
  display: none;
}

.left-wrapper {
  height: 920px;
  background: #fff;
  border-right: 1px solid #f2f2f2;
}
.w-420 {
  width: 420px;
}
.words-tag {
  background-color: #f4f4f4;
  display: inline-block;
  padding: 0 10px;
  font-size: 12px;
  color: #4f4f4f;
  border-radius: 4px;
  box-sizing: border-box;
  white-space: nowrap;
  height: 28px;
  line-height: 26px;
}
.tag-img {
  display: block;
  height: 28px;
  object-fit: cover;
  border-radius: 4px;
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
.tip {
  color: #888;
  font-size: 12px;
  line-height: 16px;
}
.upload-select {
  width: 64px;
  height: 64px;
  font-size: 32px !important;
  background: #f5f5f5;
  color: #ccc;
  margin-bottom: 6px;
}
.upload-item {
  position: relative;
  display: inline-block;
  width: 64px;
  height: 64px;
  border-radius: 4px;
  margin: 0 15px 10px 0;
  img {
    width: 64px;
    height: 64px;
    border-radius: 4px;
    vertical-align: middle;
  }
  .close {
    cursor: pointer;
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    top: 0;
    right: 0;
    width: 20px;
    height: 20px;
    margin: -10px -10px 0 0;
    background-color: #aaa;
    color: #fff;
  }
}
</style>
