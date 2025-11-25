<template>
  <div>
    <el-card v-for="(value, key, index) in tableList" :key="index" :bordered="false" shadow="never" class="ivu-mt mt16">
      <div class="head acea-row row-between-wrapper">{{ key | headText }}</div>
      <el-table ref="table" :data="tableList[key]" empty-text="暂无数据">
        <el-table-column :label="key == 'permissions' ? '文件/目录' : '环境'" minWidth="180">
          <template slot-scope="scope">{{ scope.row.name }} </template>
        </el-table-column>
        <el-table-column label="要求" minWidth="180">
          <template slot-scope="scope">
            <span>{{ scope.row.require }} </span>
            <el-tooltip placement="top" v-if="key == 'process' && !scope.row.value">
              <div slot="content" v-html="trips[scope.$index].message"></div>
              <i class="el-icon-warning-outline"></i>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="180">
          <template slot-scope="scope">
            <span v-if="typeof scope.row.value === 'boolean'">
              <i v-if="scope.row.value === true" class="el-icon-check"></i>
              <i v-else class="el-icon-close"></i>
            </span>
            <span v-else>{{ scope.row.value }}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog :visible.sync="isTemplate" title="商业授权" width="550px" @closed="cancel">
      <iframe width="100%" height="780" :src="iframeUrl" frameborder="0"></iframe>
    </el-dialog>
    <el-dialog :visible.sync="modalCopyright" title="版权信息" width="550px">
      <div class="auth">
        <div class="update">修改版权信息:</div>
        <el-input style="width: 460px" v-model="copyrightText" />
      </div>
      <div class="auth">
        <div class="update">上传版权图片:</div>
        <div>
          <div class="uploadPictrue" v-if="authorizedPicture" v-db-click @click="modalPicTap('单选')">
            <img v-lazy="authorizedPicture" />
            <i class="el-icon-error" @click.stop="authorizedPicture = ''"></i>
          </div>
          <div class="upload" v-else v-db-click @click="modalPicTap('单选')">
            <div class="iconfont">+</div>
          </div>
          <div class="tips-info">建议尺寸：宽290px*高100px</div>
        </div>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button v-db-click @click="modalCopyright = false">取 消</el-button>
        <el-button type="primary" v-db-click @click="saveCopyRight">保存</el-button>
      </span>
    </el-dialog>
    <el-dialog :visible.sync="modalPic" width="1024px" title="上传授权图片" :close-on-click-modal="false">
      <uploadPictures :isChoice="isChoice" @getPic="getPic" :gridBtn="gridBtn" :gridPic="gridPic" v-if="modalPic">
      </uploadPictures>
    </el-dialog>
  </div>
</template>
<script>
import uploadPictures from '@/components/uploadPictures';
import { auth, getVersion, crmebProduct, saveCrmebCopyRight, getCrmebCopyRight, copyrightList } from '@/api/system';
import { mapState } from 'vuex';
import { formatDate } from '@/utils/validate';
import QRCode from 'qrcodejs2';
import { t } from 'vxe-table';
export default {
  name: 'system_auth',
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    ...mapState('admin/userLevel', ['categoryId']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },

  data() {
    return {
      baseUrl: 'https://shop.crmeb.net/html/index.html',
      iframeUrl: '',
      captchs: 'http://authorize.crmeb.net/api/captchs/',
      authCode: '',
      status: 1,
      dayNum: 0,
      copyright: '',
      isTemplate: false,
      modalCopyright: false,
      price: '',
      proPrice: '',
      productStatus: false,
      copyrightText: '',
      success: false,
      payType: '',
      disabled: false,
      isShow: false, // 验证码模态框是否出现
      active: 0,
      timer: null,
      version: '',
      label: '',
      productType: '',
      modalPic: false,
      isChoice: '单选',
      authorizedPicture: '', // 版权图片
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
      tableList: [],
      licensingTable: [],
      copyrightTableData: [],
      copyrightList: [{}],
      loading: false,
      trips: [
        {
          title: '温馨提示',
          message:
            '您的【长连接】未开启，没有开启会导致系统默认客服无法使用,后台订单通知无法收到。请尽快执行命令开启！！<a href="https://doc.crmeb.com/single/v54/13667" target="_blank">点击查看开启方法</a>',
        },
        {
          title: '温馨提示',
          message:
            '您的【定时任务】未开启，没有开启会导致自动收货、未支付自动取消订单、订单自动好评、拼团到期退款等任务无法正常执行。请尽快执行命令开启！！<a href="https://doc.crmeb.com/single/v54/13667" target="_blank">点击查看开启方法</a>',
        },
        {
          title: '温馨提示',
          message:
            '您的【消息队列】未开启，没有开启会导致异步任务无法执行。请尽快执行命令开启！！<a href="https://doc.crmeb.com/single/v54/13667" target="_blank">点击查看开启方法</a>',
        },
      ],
    };
  },
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
    headText(z) {
      if (z === 'server') {
        return '服务器信息';
      } else if (z === 'environment') {
        return '系统环境要求';
      } else if (z === 'permissions') {
        return '权限状态';
      } else if (z === 'process') {
        return '启动进程';
      }
    },
  },
  components: {
    uploadPictures,
  },
  mounted() {
    this.getAuth();
    this.getVersion();
    window.addEventListener('message', (e) => {
      if (e.data.event === 'onCancel') {
        this.cancel();
      }
    });
    copyrightList().then((res) => {
      this.tableList = res.data;
    });
  },
  methods: {
    editCopyright() {
      this.modalCopyright = true;
    },
    getVersion() {
      getVersion().then((res) => {
        this.version = res.data.version;
        this.label = res.data.label;
      });
    },
    getCrmebCopyRight() {
      getCrmebCopyRight().then((res) => {
        this.getAuth();
      });
    },
    //保存版权信息
    saveCopyRight() {
      saveCrmebCopyRight({
        copyright: this.copyrightText,
        copyright_img: this.authorizedPicture,
      }).then((res) => {
        this.getCopyRight();
        this.modalCopyright = false;
        return this.$message.success(res.msg);
      });
    },
    // 选择图片
    modalPicTap() {
      this.modalPic = true;
    },
    // 选中图片
    getPic(pc) {
      this.authorizedPicture = pc.att_dir;
      this.modalPic = false;
    },
    //获取版权信息
    getCopyRight() {
      getCrmebCopyRight().then((res) => {
        const { copyrightContext, copyrightImage } = res.data;
        this.copyrightTableData = [
          {
            copyrightContext,
            copyrightImage,
          },
        ];
        this.copyrightText = copyrightContext || '';
        this.authorizedPicture = copyrightImage || '';
      });
    },
    cancel() {
      if (this.productType === 'copyright') {
        this.getCrmebCopyRight();
      } else {
        this.getAuth();
      }
      this.iframeUrl = '';
      this.isTemplate = false;
    },
    loginTabSwitch(index) {
      this.active = index;
    },
    getAuth() {
      auth()
        .then((res) => {
          let data = res.data || {};
          this.licensingTable = [
            {
              authCode: data.authCode || '',
              status: data.status === undefined ? -1 : data.status,
            },
          ];
          this.dayNum = data.day || 0;
          this.copyright = data.copyright;
          if (this.copyright) {
            this.getCopyRight();
          }
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    toCrmeb() {
      window.open('http://www.crmeb.com');
    },
    getProduct() {
      crmebProduct({ type: 'copyright' })
        .then((res) => {
          this.price = res.data.attr.price;
          this.productStatus = true;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
      crmebProduct({ type: 'pro' })
        .then((res) => {
          this.proPrice = res.data.attr.price;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    payment(product) {
      this.productType = product;
      let host = location.host;
      let hostData = host.split('.');
      if (hostData[0] === 'test' && hostData.length === 4) {
        host = host.replace('test.', '');
      } else if (hostData[0] === 'www' && hostData.length === 3) {
        host = host.replace('www.', '');
      }
      this.iframeUrl =
        this.baseUrl + '?url=' + host + '&product=' + product + '&version=' + this.version + '&label=' + this.label;
      this.isTemplate = true;
    },
    // 用户点击遮罩层，应该关闭模态框
    onClose() {
      this.isShow = false;
    },
  },
  destroyed() {},
};
</script>
<style scoped lang="scss">
.auth {
  padding: 9px 16px 9px 10px;
  display: flex;

  .box {
    width: 50px;
  }

  .update {
    white-space: nowrap;
    margin-bottom: 12px;
  }

  .upload {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background: rgba(0, 0, 0, 0.02);
    border-radius: 4px;
    border: 1px solid #dddddd;
  }
}

.auth .iconIos {
  font-size: 40px;
  margin-right: 10px;
  color: #001529;
}

.auth .text {
  font-weight: 400;
  color: rgba(0, 0, 0, 1);
  font-size: 18px;
}

.auth .text .code {
  font-size: 14px;
  color: rgba(0, 0, 0, 0.5);
}

.auth .text .pro_price {
  height: 18px;
  font-size: 14px;
  font-family: PingFangSC-Semibold, PingFang SC;
  font-weight: 600;
  color: #f5222d;
  line-height: 18px;
}

.auth .blue {
  color: var(--prev-color-primary) !important;
}

.auth .red {
  color: #ed4014 !important;
}

.upload .iconfont {
  line-height: 60px;
}

.uploadPictrue {
  width: 60px;
  height: 60px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  margin-left: 2px;
  border-radius: 3px;
  position: relative;
  cursor: pointer;
  .el-icon-error{
    position: absolute;
    top:-3px;
    right: -3px;
    color: #999999;
  }
}

.uploadPictrue img {
  width: 100%;
  height: 100%;
  border-radius: 3px;
}

.phone_code {
  border: 1px solid #eee;
  padding: 0 10px 0;
  cursor: pointer;
}

.grey {
  background-color: #999999;
  border-color: #999999;
  color: #fff;
}

.update {
  font-size: 13px;
  color: rgba(0, 0, 0, 0.85);
  padding-right: 12px;
}

.prompt {
  margin-left: 150px;
  font-size: 12px;
  font-weight: 400;
  color: #999999;
}

.submit {
  width: 100%;
}

.code .input {
  width: 83%;
}

.code .input .ivu-input {
  border-radius: 4px 0 0 4px !important;
}

.code .pictrue {
  height: 32px;
  width: 17%;
}

.customer {
  border-right: 0;
}

.customer a {
  font-size: 12px;
}

.ivu-input-group-prepend,
.ivu-input-group-append {
  background-color: #fff;
}

.ivu-input-group .ivu-input {
  border-right: 0 !important;
}

.qrcode {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 180px;
  height: 180px;
  border: 1px solid #e5e5e6;
}

.qrcode_desc {
  display: inline-block;
  text-align: center;
  margin: 10px 0 10px;
  width: 180px;
  font-size: 12px;
  color: #666;
  line-height: 16px;
}

.login_tab {
  font-size: 16px;
  margin: 0 0 20px;
  justify-content: center;
}

.login_tab_item {
  width: 50%;
  text-align: center;
  padding-bottom: 15px;
  border-bottom: 1px solid #eee;
  cursor: pointer;
}

.active_tab {
  border-bottom: 2px solid var(--prev-color-primary);
  color: var(--prev-color-primary);
  font-weight: 600;
}

iframe {
  height: 550px;
  overflow: hidden;
}

.head {
  font-weight: 400;
  font-size: 14px;
  color: #303133;
  margin-bottom: 20px;
}

.el-icon-check {
  color: var(--prev-color-primary);
  font-size: 22px;
  font-weight: 600;
}

.el-icon-close {
  color: #f5222d;
  font-size: 22px;
  font-weight: 600;
}

.btn {
  color: var(--prev-color-primary);
  margin-right: 10px;
}
.el-icon-warning-outline {
  font-size: 13px;
}
</style>
