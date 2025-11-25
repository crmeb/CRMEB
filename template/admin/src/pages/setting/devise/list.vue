<template>
  <div>
    <div class="i-layout-page-header header-title">
      <span class="ivu-page-header-title mr20">{{ $route.meta.title }}</span>
      <div>
        <div style="float: right" v-if="cardShow == 1 || cardShow == 2">
          <el-button class="bnt" type="primary" v-db-click @click="submit" :loading="loadingExist">保存</el-button>
          <el-button v-if="cardShow == 1" class="bnt ml20" v-db-click @click="reast">重置</el-button>
        </div>
      </div>
    </div>
    <el-card class="h100" :bordered="false" shadow="never" v-if="cardShow == 0">
      <div class="acea-row no-warp">
        <div class="iframe-col">
          <iframe class="iframe-box" :src="iframeUrl" frameborder="0" ref="iframe"></iframe>
          <div class="mask"></div>
        </div>
        <div class="table-box">
          <div class="acea-row row-between-wrapper">
            <div class="button acea-row row-middle">
              <el-button class="m-r-10" type="primary" @click="createdPage">添加页面</el-button>
              <el-upload
                :action="UploadPath"
                :before-upload="beforeUpload"
                :on-success="handleSuccess"
                :on-error="handleError"
                :limit="1"
                :show-file-list="false"
                accept=".txt"
                :headers="header"
              >
                <el-button type="primary">导入模板</el-button>
              </el-upload>
            </div>
          </div>
          <el-table
            :data="list"
            ref="table"
            class="mt14"
            v-loading="loading"
            highlight-current-row
            no-userFrom-text="暂无数据"
            no-filtered-userFrom-text="暂无筛选结果"
          >
            <el-table-column label="页面ID" width="80">
              <template slot-scope="scope">
                <span>{{ scope.row.id }}</span>
              </template>
            </el-table-column>
            <el-table-column label="模板名称" min-width="130">
              <template slot-scope="scope">
                <span>{{ scope.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="模板类型" min-width="130">
              <template slot-scope="scope">
                <el-tag type="success" size="medium" v-if="scope.row.status == 1">首页</el-tag>
                <el-tag type="info" size="medium" v-else class="mr10">专题页</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="添加时间" min-width="130">
              <template slot-scope="scope">
                <span>{{ scope.row.add_time }}</span>
              </template>
            </el-table-column>
            <el-table-column label="更新时间" min-width="130">
              <template slot-scope="scope">
                <span>{{ scope.row.update_time }}</span>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="210">
              <template slot-scope="scope">
                <div
                  style="display: inline-block"
                  v-if="scope.row.status || scope.row.is_diy"
                  v-db-click
                  @click="edit(scope.row)"
                >
                  <a
                    v-if="scope.row.is_diy === 1"
                    class="target"
                    ref="target"
                    :href="`${url}${$routeProStr}/setting/pages/diy_index?id=${scope.row.id}&name=${
                      scope.row.template_name || 'moren'
                    }`"
                  >
                    编辑</a
                  >
                  <a v-else class="target">编辑</a>
                </div>
                <el-divider
                  direction="vertical"
                  v-if="(scope.row.status || scope.row.is_diy) && scope.row.id != 1 && scope.row.status != 1"
                />

                <div style="display: inline-block" v-if="scope.row.id != 1 && scope.row.status != 1">
                  <a v-db-click @click="del(scope.row, '删除此模板', scope.$index)">删除</a>
                </div>
                <el-divider
                  direction="vertical"
                  v-if="(scope.row.id != 1 && scope.row.status != 1) || scope.row.is_diy"
                />
                <div style="display: inline-block" v-if="scope.row.is_diy">
                  <a v-db-click @click="preview(scope.row, scope.$index)">预览</a>
                </div>
                <el-divider direction="vertical" v-if="scope.row.is_diy && scope.row.status != 1" />
                <div style="display: inline-block" v-if="scope.row.status != 1">
                  <a v-db-click @click="setStatus(scope.row, scope.$index)">设为首页</a>
                </div>
                <el-divider direction="vertical" />
                <div style="display: inline-block">
                  <a v-db-click @click="exportView(scope.row.id)">导出模版</a>
                </div>
              </template>
            </el-table-column>
          </el-table>
          <div class="acea-row row-right page">
            <pagination
              v-if="total"
              :total="total"
              :page.sync="diyFrom.page"
              :limit.sync="diyFrom.limit"
              @pagination="diyProList"
            />
          </div>
        </div>
      </div>
    </el-card>
    <goodClass v-else-if="cardShow == 1" ref="category" @parentFun="getChildData"></goodClass>
    <users v-else ref="users" @parentFun="getChildData"></users>
    <el-dialog :visible.sync="isTemplate" title="开发移动端链接" :z-index="1" width="540px" @closed="cancel">
      <div class="article-manager">
        <el-card :bordered="false" shadow="never" class="ivu-mt">
          <el-form
            ref="formItem"
            :model="formItem"
            label-width="120px"
            label-position="right"
            :rules="ruleValidate"
            @submit.native.prevent
          >
            <el-row :gutter="24">
              <el-col :span="24">
                <el-col>
                  <el-form-item label="开发移动端链接：" prop="link" label-for="link">
                    <el-input v-model="formItem.link" placeholder="http://localhost:8080" />
                  </el-form-item>
                </el-col>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" v-db-click @click="handleSubmit('formItem')">提交</el-button>
      </span>
    </el-dialog>
    <el-dialog :visible.sync="modal" width="540px" title="预览">
      <div>
        <div v-viewer class="acea-row row-around code">
          <div class="acea-row row-column-around row-between-wrapper">
            <div class="QRpic" ref="qrCodeUrl"></div>
            <span class="mt10">公众号二维码</span>
          </div>
          <div class="acea-row row-column-around row-between-wrapper">
            <div class="QRpic">
              <img v-lazy="qrcodeImg" />
            </div>
            <span class="mt10">小程序二维码</span>
          </div>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Setting from '@/setting';
import { diyProList, diyDel, setStatus, recovery, getRoutineCode, setDefault, exportDiyDataApi } from '@/api/diy';
import { mapState, mapActions } from 'vuex';
import QRCode from 'qrcodejs2';
import goodClass from './goodClass';
import users from './users';
import { Upload } from 'element-ui';
import { getCookies } from '@/libs/util';

export default {
  name: 'devise_list',
  computed: {
    ...mapState('admin/layout', ['menuCollapse']),
  },
  components: {
    goodClass,
    users,
  },
  data() {
    return {
      loading: false,
      theme3: 'light',
      menuList: [
        {
          name: '商城首页',
          id: 1,
        },
        {
          name: '商品分类',
          id: 2,
        },
        {
          name: '个人中心',
          id: 3,
        },
      ],
      list: [],
      iframeUrl: '',
      modal: false,
      UploadPath: Setting.apiBaseURL + '/diy_pro/import/data',
      BaseURL: Setting.apiBaseURL.replace(/adminapi/, ''),
      cardShow: 0,
      loadingExist: false,
      isDiy: 1,
      qrcodeImg: '',
      diyFrom: {
        type: '',
        page: 1,
        limit: 15,
      },
      total: 0,
      formItem: {
        id: 0,
        link: '',
      },
      isTemplate: false,
      ruleValidate: {
        link: [{ required: true, message: '请输入移动端链接', trigger: 'blur' }],
      },
      url: window.location.origin,
      header: {},
    };
  },
  watch: {
    $route() {
      this.cardShow = this.$route.params.type;
    },
  },
  created() {
    this.cardShow = this.$route.params.type;
    this.diyProList();
    this.iframeUrl = `${location.origin}/pages/index/index?mdType=iframeWindow`;
    this.getToken();
  },
  mounted() {
    this.$store.commit('mobildConfig/SETEMPTY');
  },
  methods: {
    getToken() {
      this.header['Authori-zation'] = 'Bearer ' + getCookies('token');
    },
    beforeUpload(file) {
      const isTXT = file.type === 'text/plain';
      if (!isTXT) {
        this.$message.error('只能上传TXT文件');
      }
      return isTXT;
    },
    handleSuccess(response, file) {
      if (response.status == 200) {
        this.$message.success(response.msg);
        this.diyProList();
      } else {
        this.$message.error(response.msg);
      }
    },
    handleError(err, file) {
      this.$message.error('文件上传失败');
    },
    exportView(id) {
      exportDiyDataApi(id)
        .then((res) => {
          const textToSave = res.data.value;
          const blob = new Blob([textToSave], { type: 'text/plain;charset=utf-8' });
          const url = URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = res.data.filename; // 设置下载文件的名称
          document.body.appendChild(a);
          a.click(); // 模拟点击触发下载
          document.body.removeChild(a); // 清理DOM
          this.$message.success(res.msg);
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    createdPage() {
      this.$router.push({
        path: this.$routeProStr + '/setting/pages/diy_index',
        query: { id: 0, name: '首页', type: 1 },
      });
      // this.$nextTick(() => {
      //   window.open(`${this.url}${this.$routeProStr}/setting/pages/diy_index?id=0&name=首页&type=0`);
      // });
    },
    cancel() {
      this.$refs['formItem'].resetFields();
    },
    refreshFrame() {
      this.iframeUrl = '';
      setTimeout((e) => {
        this.iframeUrl = `${location.origin}/pages/index/index?mdType=iframeWindow`;
      }, 200);
    },
    getChildData(e) {
      this.loadingExist = e;
    },
    submit() {
      if (this.cardShow == 1) {
        this.$refs.category.onSubmit();
      } else {
        this.$refs.users.onSubmit();
      }
    },
    reast() {
      if (this.cardShow == 1) {
        this.$refs.category.onSubmit(1);
      } else {
        this.$refs.users.getInfo();
      }
    },
    bindMenuItem(index) {
      this.cardShow = index;
    },
    onCopy() {
      this.$message.success('复制预览链接成功');
    },
    onError() {
      this.$message.error('复制预览链接失败');
    },
    //生成二维码
    creatQrCode(id) {
      this.$refs.qrCodeUrl.innerHTML = '';
      let url = `${this.BaseURL}pages/annex/special/index?id=${id}`;
      var qrcode = new QRCode(this.$refs.qrCodeUrl, {
        text: url, // 需要转换为二维码的内容
        width: 160,
        height: 160,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H,
      });
    },
    //小程序二维码
    routineCode(id) {
      getRoutineCode(id)
        .then((res) => {
          this.qrcodeImg = res.data.image;
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    preview(row) {
      this.modal = true;
      this.$nextTick((e) => {
        this.creatQrCode(row.id);
        this.routineCode(row.id);
      });
    },
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          setCookies('moveLink', this.formItem.link);
          this.$router.push({
            path: this.$routeProStr + '/setting/pages/diy',
            query: { id: this.formItem.id, type: 1 },
          });
        } else {
          return false;
        }
      });
    },
    changeMenu(row, index, name) {
      switch (name) {
        case '1':
          this.setDefault(row);
          break;
        case '2':
          this.recovery(row);
          break;
        case '3':
          this.del(row, '删除此模板', index);
          break;
        default:
      }
    },
    //设置默认数据
    setDefault(row) {
      setDefault(row.id)
        .then((res) => {
          this.$message.success(res.msg);
          this.diyProList();
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    // 获取列表
    diyProList() {
      // let storage = window.localStorage;
      // this.iframeUrl = storage.getItem("iframeUrl");
      let that = this;
      this.loading = true;
      diyProList(this.diyFrom).then((res) => {
        this.loading = false;
        let data = res.data;
        this.list = data.list;
        this.total = data.count;
      });
    },
    // 编辑
    edit(row) {
      this.formItem.id = row.id;
      if (!row.is_diy) {
        if (!row.status) {
          this.$message.error('请先设为首页在进行编辑');
        } else {
          this.$router.push({
            path: this.$routeProStr + '/setting/pages/diy',
            query: { id: row.id, type: 0 },
          });
        }
      }
    },
    // 添加
    // add() {
    //   this.$modalForm(getDiyCreate()).then(() => this.diyProList());
    // },
    // 添加
    add() {
      // this.$router.push({
      //   path: this.$routeProStr + '/setting/pages/diy_index',
      //   query: { id: 0, name: '首页', type: 1 },
      // });
    },
    // 删除
    del(row) {
      let delfromData = {
        title: '删除',
        num: 2000,
        url: 'diy/del/' + row.id,
        method: 'DELETE',
        data: {
          type: 1,
        },
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.diyProList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 使用模板
    async setStatus(row) {
      this.$msgbox({
        title: '提示',
        message: '是否把该模板设为首页',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonText: '确定',
        iconClass: 'el-icon-warning',
        confirmButtonClass: 'btn-custom-cancel',
      })
        .then(() => {
          setStatus(row.id, {
            type: 1,
          })
            .then((res) => {
              this.refreshFrame();
              this.$message.success(res.msg);
              this.diyProList();
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        })
        .catch(() => {});
    },
    recovery(row) {
      recovery(row.id).then((res) => {
        this.$message.success(res.msg);
        this.diyProList();
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.ivu-mt {
  background-color: #fff;
  padding-bottom: 50px;
}
.no-warp {
  flex-wrap: nowrap !important;
}
::v-deep .el-card__body {
  padding: 40px;
}
.bnt {
  width: 80px !important;
}
.iframe-col {
  width: 375px;
  min-width: 375px;
  height: 650px;
  margin-right: 30px;
  position: relative;
}
.iframe-box {
  width: 100%;
  height: 100%;
  border-radius: 10px;
  border: 1px solid #eee;
}
.target-add {
  text-decoration: none;
  color: #fff;
}
.mask {
  position: absolute;
  left: 0;
  width: 100%;
  top: 0;
  height: 100%;
  background-color: rgba(0, 0, 0, 0);
}
::v-deep .ivu-menu-vertical .ivu-menu-item,
.ivu-menu-vertical .ivu-menu-submenu-title {
  text-align: center;
}
::v-deep .i-layout-page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
::v-deep .ivu-page-header {
  border-bottom: unset;
  position: fixed;
  z-index: 9;
  width: 100%;
}
::v-deep .ivu-menu-vertical .ivu-menu-item-group-title {
  display: none;
}
::v-deep .ivu-menu-vertical.ivu-menu-light:after {
  display: none;
}
::v-deep .ivu-menu {
  z-index: 0 !important;
}
::v-deep .ivu-row {
  display: flex;
}
.table-box {
  flex: 1;
}
.code {
  position: relative;
}
.QRpic {
  width: 160px;
  height: 160px;

  img {
    width: 100%;
    height: 100%;
  }
}
.left-wrapper {
  padding: 20px 0 0 20px;
  background: #fff;
  border-right: unset;
}
.tree_tit {
  height: 50px;
  line-height: 50px;
  font-size: 15px;
  color: #333;
  font-weight: 500;
  text-align: center;
  border-bottom: 1px solid #ebeef5;
}
.picCon {
  width: 280px;
  height: 510px;
  background: #ffffff;
  border: 1px solid #eeeeee;
  border-radius: 25px;
  .pictrue {
    width: 250px;
    height: 417px;
    border: 1px solid #eeeeee;
    opacity: 1;
    border-radius: 10px;
    margin: 30px auto 0 auto;

    img {
      width: 100%;
      height: 100%;
      border-radius: 10px;
    }
  }
  .circle {
    width: 36px;
    height: 36px;
    background: #ffffff;
    border: 1px solid #eeeeee;
    border-radius: 50%;
    margin: 13px auto 0 auto;
  }
}
.tree-vis {
  display: flex;
  flex-direction: column;
  .tab-item {
    padding: 15px 20px;
    cursor: pointer;
  }
  .active {
    background-color: var(--prev-bg-main-color);
    color: var(--prev-color-primary);
    border-right: 2px solid var(--prev-color-primary);
  }
}
</style>
