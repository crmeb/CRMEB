<template>
<div class="upgrade">
    <Card :bordered="false" dis-hover class="ivu-mt">
      <div class="header">
        <div>当前版本<span class="v"></span><span class="num">{{$store.state.userInfo.version}}</span></div>
        <div class="info title">
          更新说明：
          <span v-if="upgradeStatus.status != 1">已升级至最新版本，无需更新</span>
        <ul v-if="upgradeStatus.status == 1">
          <li>{{upgradeStatus.title}}</li>
        </ul>
        </div>
        <Button v-if="currentTab == 1 && upgradeStatus.status == 1" type="primary" class="primary btn update" @click="update()">立即更新</Button>
      </div>
    </Card>
    <Card :bordered="false" dis-hover class="ivu-mt">
        <div>
          <Tabs v-model="currentTab" @on-click="handleClick">
            <TabPane :label="item.label" :name="item.value.toString()" v-for="item in headerList" :key="item.id"/>
          </Tabs>
        </div>
          <div class="contentTime" v-if="currentTab == 1">
            <div class="acea-row row-top on" @mouseenter="quearyEvear(item.id,index)" v-for="(item, index) in upgradeList" :key="index" :class="{active:index==dynamic}">
                <div class="time">{{item.release_time}}</div>
                <Timeline class="list">
                    <TimelineItem>
                        <!-- <Icon :type="index==0 ? 'md-radio-button-on' : 'md-radio-button-off'" slot="dot"/> -->
                        <Collapse simple>
                            <Panel hide-arrow>
                                {{item.title}} v{{item.first_version}}.{{item.second_version}}.{{item.third_version}}.{{item.fourth_version}}<Icon type="ios-arrow-down" />
                              <p slot="content" class="info">
                                <ul style="white-space: pre-wrap;">
                                  <li v-html="item.content"></li>
                                </ul>
                              </p>
                            </Panel>
                        </Collapse>
                    </TimelineItem>
                    <Button v-if="item.client_package_link" type="success"  class="primary btn" @click="downloadFile(item.client_package_link)">移动端源码</Button>
                    <Button v-if="item.pc_package_link" type="primary" class="primary btn1" @click="downloadFile(item.pc_package_link)">PC端源码</Button>
                </Timeline>
            </div>
        </div>
        <Scroll v-if="currentTab == 2" :on-reach-bottom="handleReachBottom" height="550">
        <div class="contentTime" >
            <div class="acea-row row-top off" @mouseenter="quearyEvear(item.id,index)" v-for="(item,index) in upgradeLogList" :key="index" :class="{active:index==dynamic}">
                <div class="time">
                    <div v-if="index == 0">最近更新</div>
                    <div>{{item.upgrade_time}}</div>  
                  </div>
                <Timeline class="list">
                    <TimelineItem>
                        <Icon :type="index==0 ? 'md-radio-button-on' : 'md-radio-button-off'" slot="dot"/>
                        <Collapse simple>
                            <Panel hide-arrow>
                                {{item.title}} v{{item.first_version}}.{{item.second_version}}.{{item.third_version}}.{{item.fourth_version}}<Icon type="ios-arrow-down" />
                              <p slot="content" class="info">
                                <ul style="white-space: pre-wrap;">
                                  <li v-html="item.content"></li>
                                </ul>
                              </p>
                            </Panel>
                        </Collapse>
                    </TimelineItem>
                    <!-- <Button v-if="item.data_status == 1" type="primary" class="primary btn" @click="handleDownload(item)">数据库备份</Button>
                    <Button v-if="item.file_status == 1" type="primary" class="primary btn1" @click="exports(item)">导出文件</Button> -->
                </Timeline>
            </div>
        </div>
        </Scroll>
        
    </Card>
    <!-- 免责声明 -->
    <Modal :loading="modal_loading" v-model="declaration" width="340" height="96" :closable="false" class-name="vertical-center-modal" :mask-closable="false">
        <p slot="header" class="header-modal">
          <span>{{upgradeAgreement.title}}</span>
        </p>
        <div class="describe">
          <p v-html="upgradeAgreement.content"></p>
        </div>
        <div slot="footer" class="footer">
            <Button class="cancel" shape="circle" @click="reject">拒绝</Button>
            <Button shape="circle" type="primary" @click="agree()">同意</Button>
        </div>
    </Modal>
    <!-- 升级 -->
    <Modal v-model="updateModal" width="340" height="96" :closable="false" class-name="vertical-center-modal" :mask-closable="false" @on-cancel="cancel">
        <p slot="header" class="header-modal2">
          <span >升级至v{{forceVersion}}</span>
        </p>
        <div class="describe" v-if="upgradeProgress.speed != '100.0' || this.downloadStatus !='200'">
          <i-circle
            :size="140"
            :trail-width="4"
            :stroke-width="5"
            :percent="Number(upgradeProgress.speed)"
            stroke-linecap="square"
            stroke-color="#43a3fb">
            <div class="demo-i-circle-custom">
                <p>{{upgradeProgress.tip || '升级中'}}</p>
            </div>
          </i-circle>
          <div class="proportion">{{!upgradeProgress.speed?'':upgradeProgress.speed+'%'}}</div>
          <div class="wait">正在更新，请耐心等候～</div>
        </div>
        <div slot="footer">
          <!-- <div v-if="upgradeProgress.speed == '100.0'"><Button class="back" type="primary" shape="circle" @click="updateModal = false">确认</Button></div> -->
        </div>
        <div v-if="upgradeProgress.speed == '100.0'" class="describe">
          <i-circle :percent="100" stroke-color="#5cb85c">
            <Icon type="ios-checkmark" size="90" style="color:#5cb85c"></Icon>
          </i-circle>
          <div class="success">升级成功</div>
        </div>
        <!-- <div v-if="upgradeProgress.speed == '100.0'" class="describe">
          <i-circle :percent="100" stroke-color="#5cb85c">
            <Icon type="md-close" size="90" style="color:#5cb85c">
          </i-circle>
          <div class="success">升级失败</div>
        </div> -->
        <div v-if="upgradeProgress.speed == '100.0'" slot="footer" class="footer2">
          <Button class="confirm" type="primary" shape="circle" @click="back()">确认</Button>
          <!-- <div><Button class="back" shape="circle" @click="backSure()">返回</Button></div> -->
        </div>
    </Modal>
</div>
</template>

<script>
import {
  upgradeListApi,
  upgradeProgressApi,
  upgradeAgreementApi,
  upgradeStatusApi,
  upgradeLogListApi,
  upgradeableListApi,
  downloadApi,
} from '@/api/system';
import axios from 'axios';
import { AccountLogout } from '@/api/account';
import { getCookies,removeCookies} from '@/libs/util';

import Setting from '@/setting';
export default {
  name: 'systemUpgradeclient',
  data() {
    return {
      Panel: '1',
      currentTab: '1',
      declaration: false,
      updateModal: false,
      modal_loading: false,
      percent: 0,
      params: [],
      arr: [],
      dynamic: false,
      status: false,
      version: '',
      forceVersion: '',
      headerList: [
        {
          label: '系统升级',
          value: 1,
        },
        {
          label: '升级记录',
          value: 2,
        },
      ],
      upgradeList: [],
      upgradeLogList: [],
      upgradeableList: [],
      upgradeProgress: {
        speed: 0,
      },
      upgradeAgreement: [],
      upgradeStatus: {},
      downloadStatus: null,
      page: 1,
      limit: 15,
      // 定时器
      timer: null,
      params_key: undefined,
      newKey: null,
      servionStatus: false,
      count:0
    };
  },
  created() {
    // this.getUpgradeList();
    // this.getUpgradeLogList();
    // this.getupgradeableList();
    // if (this.$route.params.items) {
    //   this.getUpgradeAgreement();
    // }
  },
  async mounted() {

    this.getUpgradeStatus();
    await this.getUpgradeList();
    await this.getupgradeableList();
    if (this.$store.state.upgrade.toggleStatus) {
      this.servionStatus = true;
      this.getUpgradeAgreement();
    }
    // this.compare();
  },
  watch: {
    'upgradeProgress.speed': {
      handler(newVal, oldVal) {
        if (newVal === '100.0') {
          clearInterval(this.timer);
        }
      },
    },
    // 'this.downloadStatus': {
    //   handler(newVal, oldVal) {
    //     if (newVal = '200') {
    //       this.updateModal = false;
    //       clearInterval(this.timer);
    //     }
    //   }
    // }
  },
  methods: {
    handleReachBottom(){
      if(this.count === this.upgradeLogList.length) {
        this.$Message.warning('暂无更多升级记录')
      } else {
        this.getUpgradeLogList();
      }
    },
    handleClick(tab, event) {
      console.log(tab, event)
      this.page = 1
      if(tab == 1){
        this.getupgradeableList();
      } else {
        this.upgradeLogList = []
        this.getUpgradeLogList();
      }
    },
    // 升级列表
    async getUpgradeList() {
      let data = {
        page: this.page,
        limit: this.limit,
      };
      let res = await upgradeListApi(data);
      this.upgradeList = res.data.list;
      this.upgradeList = res.data.list;
      console.log('升级列表', this.upgradeList);
    },
    // 升级记录
    getUpgradeLogList() {
      let data = {
        page: this.page,
        limit: this.limit,
      };
      upgradeLogListApi(data)
        .then((res) => {
          this.upgradeLogList = [...this.upgradeLogList,...res.data.list]
          this.count = res.data.count
          if(this.upgradeLogList.length < this.count){
            this.page++
          }
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
 
    // 可升级列表
    async getupgradeableList() {
      let res = await upgradeableListApi();
      console.log('111111')
      this.upgradeableList = res.data;
      let firstVer = res.data[0]
      if(this.$store.state.upgrade.toggleStatus || this.upgradeStatus.force_reminder){
        const data = res.data.find(item => item.force_reminder === 1)
        this.newKey = data.package_key;
        console.log('data',data)

        this.forceVersion = data.first_version + '.' + data.second_version + '.' + data.third_version + '.' + data.fourth_version
      }else{
        this.params_key = this.upgradeableList[0].package_key
        this.forceVersion = firstVer.first_version + '.' + firstVer.second_version + '.' + firstVer.third_version + '.' + firstVer.fourth_version
      }
 
      
      // arr.forEach((item) => {
      //   this.$set(
      //     item,
      //     'servion',
      //     item.first_version + '.' + item.second_version + '.' + item.third_version + '.' + item.fourth_version,
      //   );
      // });

      // let data = [];
      // arr.map((ele) => {
      //   data.push(ele.servion);
      // });
      // // 版本号排序
      // function sortVersion(list) {
      //   return list.sort((version1, version2) => {
      //     const arr1 = version1.split('.').map((e) => e * 1);
      //     const arr2 = version2.split('.').map((e) => e * 1);
      //     const length = Math.max(arr1.length, arr2.length);
      //     for (let i = 0; i < length; i++) {
      //       if ((arr1[i] || 0) > (arr2[i] || 0)) return 1;
      //       if ((arr1[i] || 0) < (arr2[i] || 0)) return -1;
      //     }
      //     return 0;
      //   });
      // }
      // this.forceVersion = data[0];
      // this.params.map((item) => {
      //   if (item.servion == data[data.length - 1]) {
      //     this.newKey = item.package_key;
      //   }
      // });
    },
    // 下载升级包
    getdownload() {
      console.log(this.newKey);
      console.log(this.$store.state.upgrade.toggleStatus);
      if (this.$store.state.upgrade.toggleStatus || this.upgradeStatus.force_reminder) {
        this.params_key = this.newKey;
      }
      console.log('下载key', this.params_key);
      downloadApi(this.params_key)
        .then((res) => {
          console.log('下载升级包调用成功', res.status);
          // this.downloadStatus = res.status;
          if (res.status == 200) {
            if (this.upgradeProgress.speed !== '100.0') {
              this.timer = setInterval(() => {
                setTimeout(() => {
                    this.getUpgradeProgress();
                }, 0);
              }, 5000);
            } else {
              clearInterval(this.timer);
              this.updateModal = false;
            }
          } else {
            this.updateModal = false;
          }
        })
        .catch((err) => {
          clearInterval(this.timer);
          this.$Message.error('下载终止');
          this.updateModal = false;
        });
    },
    downloadFile(url){
      window.open(url, '_blank');
    },

    // 升级进度
    getUpgradeProgress() {
      upgradeProgressApi()
        .then((res) => {
          this.upgradeProgress = res.data;
          this.downloadStatus = res.status;
        })
        .catch((res) => {
          clearInterval(this.timer);
          this.$Message.error(res.msg);
        });
    },
    // 升级协议
    getUpgradeAgreement() {
      upgradeAgreementApi()
        .then((res) => {
          this.upgradeAgreement = res.data;
          this.declaration = true;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 升级状态
    getUpgradeStatus() {
      upgradeStatusApi()
        .then((res) => {
          this.upgradeStatus = res.data;
          if(res.data.force_reminder){
            this.declaration = true
            this.getUpgradeAgreement()
          }
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 立即更新
    update(item) {
      this.declaration = true;
      if (item && this.$store.state.upgrade.toggleStatus == false) {
        this.params_key = item.params_key;
      }
      // this.version = item.first_version + "." + item.second_version + "." + item.third_version + "." + item.fourth_version;
      this.getUpgradeAgreement();
    },
    // 比较相同版本号
    compare() {
      this.upgradeList.map((ele, i) => {
        this.$set(
          this.upgradeList[i],
          'mn',
          ele.first_version + ele.second_version + ele.third_version + ele.fourth_version,
        );

        this.upgradeableList.map((item, i) => {
          this.$set(
            this.upgradeableList[i],
            'mn',
            item.first_version + item.second_version + item.third_version + item.fourth_version,
          );
        });
        if (this.upgradeList[i].mn == this.upgradeableList[i].mn) {
          this.$set(this.upgradeList[i], 'indexn', true);
          this.$set(this.upgradeList[i], 'params_key', this.upgradeableList[i].package_key);
          this.$set(this.upgradeableList[i], 'indexn', true);
        }
      });
    },
    // 同意
    agree() {
      this.declaration = false;
      this.updateModal = true;
      this.getdownload();
      this.$store.commit("upgrade/TOGGLE_STATUS",false)
    },
    reject(){
      this.declaration = false
      this.$store.commit("upgrade/TOGGLE_STATUS",false)
    },
    back() {
      this.updateModal = false;
      // this.getUpgradeList();
      clearInterval(this.timer);
      AccountLogout()
        .then((res) => {
          this.$Message.success('您已成功退出');
          this.$router.replace('/admin/login');
          localStorage.clear();
          removeCookies('token');
          removeCookies('expires_time');
          removeCookies('uuid');
          // window.location.reload()
        })
        .catch((res) => {});
    },
    backSure() {
      this.updateModal = false;
      clearInterval(this.timer);
    },
    cancel() {
      this.updateModal = false;
      clearInterval(this.timer);
    },
    // 导出备份文件
    exports(item) {
      let name =
        item.title +
        'v' +
        item.first_version +
        '.' +
        item.second_version +
        '.' +
        item.third_version +
        '.' +
        item.fourth_version +
        '.' +
        item.upgrade_time;
      let href = Setting.apiBaseURL + `/system/upgrade_export/${item.id}/file`;
      axios({
        method: 'get',
        url: href,
        responseType: 'blob',
        headers: {
          'Authori-zation': 'Bearer ' + getCookies('token'),
        },
      }).then((res) => {
        var blob = new Blob([res.data], {
          type: 'application/octet-stream;charset=UTF-8',
        });
        var downloadElement = document.createElement('a');
        // 下载的文件名
        downloadElement.download = `${name}.zip`;
        // 创建下载的链接
        downloadElement.href = window.URL.createObjectURL(blob);
        // 点击下载
        downloadElement.click();
        document.body.appendChild(downloadElement);
        // 下载完成移除元素
        document.body.removeChild(downloadElement);
        // 释放掉blob对象
        window.URL.revokeObjectURL(downloadElement.href);
      });
    },
    handleDownload(item) {
      let name =
        item.title +
        'v' +
        item.first_version +
        '.' +
        item.second_version +
        '.' +
        item.third_version +
        '.' +
        item.fourth_version +
        '.' +
        item.upgrade_time;
      let href = Setting.apiBaseURL + `/system/upgrade_export/${item.id}/data`;
      axios({
        method: 'get',
        url: href,
        responseType: 'blob',
        headers: {
          'Authori-zation': 'Bearer ' + getCookies('token'),
        },
      }).then((res) => {
        var blob = new Blob([res.data], {
          type: 'application/octet-stream;charset=UTF-8',
        });
        var downloadElement = document.createElement('a');
        // 下载的文件名
        downloadElement.download = `${name}.gz`;
        // 创建下载的链接
        downloadElement.href = window.URL.createObjectURL(blob);
        // 点击下载
        downloadElement.click();
        document.body.appendChild(downloadElement);
        // 下载完成移除元素
        document.body.removeChild(downloadElement);
        // 释放掉blob对象
        window.URL.revokeObjectURL(downloadElement.href);
      });
    },
    quearyEvear(id, index) {
      this.dynamic = index;
    },
  },
  destroyed() {
    //销毁
    clearInterval(tihs.timer);
  },
};
</script>

<style lang="stylus" scoped>
.active {
  padding: 6px 0;
  // background-color: #eee !important;
}

.active .ivu-collapse-simple {
  // background-color: #eee !important;
}

.active .ivu-collapse-content {
  // background-color: #eee !important;
}

.active .ivu-timeline-item-head-custom {
  // background-color: #fff !important;
  // z-index 9999
}

.active .btn {
  top: 50px;
}

.primary {
  position: absolute;
  right: 0;
  bottom: 0;
}

.upgrade .header {
  font-size: 12px;
  color: #000;
  // border-bottom:1px dotted rgba(221,221,221,1);
  // padding-bottom: 25px;
}

.upgrade .header .v {
  color: #1890FF;
  margin-left: 10px;
}

.upgrade .header .num {
  color: #1890FF;
  font-size: 24px;
}

.upgrade .header .info {
  color: #999999;
}

.upgrade .header .title {
  color: #999999;
  display: flex;
}

.upgrade .header .info ul {
  color: #999999;
  display: flex;
  margin-left: 14px;
}

.upgrade .header .info ul li::marker {
  color: red;
}

.upgrade .header .info ul li+li {
  margin-left: 40px;
}

.upgrade .contentTime .acea-row {
  padding: 5px;
}

.upgrade .contentTime .list {
  position: relative;
  width: 85%;
}

.upgrade .contentTime .info {
  font-size: 12px !important;
  color: #999 !important;
  margin-top: 13px;
}

.upgrade .contentTime .info li {
  list-style-type: disc;
  margin-left: 16px;
  line-height: 26px;
  list-style: none;
}

.upgrade .contentTime .collapse {
  width: 100%;
}

.upgrade .contentTime .ivu-collapse {
  border: 0 !important;
}

.upgrade .contentTime .ivu-collapse > .ivu-collapse-item > .ivu-collapse-header {
  height: unset !important;
  line-height: 20px !important;
  border: 0 !important;
  font-size: 16px !important;
  color: #333333;
  font-weight: 600;
  // background: #eee;
}

.upgrade .contentTime .ivu-collapse > .ivu-collapse-item > .ivu-collapse-header > i {
  color: #BBBBBB !important;
  margin-left: 9px;
}

.upgrade .contentTime .ivu-collapse > .ivu-collapse-item.ivu-collapse-item-active > .ivu-collapse-header > i {
  transform: rotate(180deg);
}

.upgrade .contentTime {
  margin-top: 30px;
  position: relative;
}

.upgrade .contentTime .btn {
  position: absolute;
  top: 0;
  right: 60px;
  z-index: 99;
}

.upgrade .contentTime .btn1 {
  position: absolute;
  top: 0;
  right: -29px;
  z-index: 99;
  margin-left: 14px;
}

.upgrade .contentTime .time {
  font-size: 14px;
  line-height: 14px;
  color: #999;
  text-align: right;
  padding-right: 28px;
  min-width: 150px;
}

.upgrade .contentTime .ivu-timeline-item:after {
  content: ' ';
  position: absolute;
  top: 13px;
  left: 6.5px;
  width: 1px;
  height: calc(100% - 13px);
  background-color: #e8eaec;
}

.upgrade .contentTime:nth-child(2n) .ivu-timeline-item-head-custom {
  z-index: 2;
  font-size: 16px;
  color: #DDDCDD;
  // background: #eee;
  margin-left: 12px;
  margin-top: 10px;
}

.ivu-timeline-item-head-custom {
  left: -24px;
}
.upgrade .contentTimed .on{
  display:flex;
  flex-wrap: nowrap;
}
.upgrade .contentTime .on:first-child .ivu-timeline-item-head-custom {
  color: #1890FF;
  font-size: 18px;
}
/deep/ .ivu-collapse > .ivu-collapse-item > .ivu-collapse-header{
  line-height: 20px;

}
.upgrade .contentTimed .on .ivu-collapse > .ivu-collapse-item > .ivu-collapse-header {
  color: #1890FF;
}

.on:first-child .ivu-collapse-header {
  color: #1890FF !important;
}

.upgrade .contentTime .off:first-child .ivu-timeline-item-head-custom {
  color: #1890FF;
  font-size: 18px;
}

.off:first-child .ivu-collapse-header {
  color: #1890FF !important;
}

.off:first-child .time {
  color: #1890FF !important;
}

.header-modal {
  background: url('../../../assets/images/bg.png') no-repeat;
  background-size: 100% 100%;
  text-align: center;
  border-radius: 6px 6px 0 0;
}

.ivu-modal{
  border-radius: 12px;
  overflow: hidden;
}

.header-modal2 {
  background: url('../../../assets/images/bg2.png') no-repeat;
  background-size: 100% 100%;
  text-align: center;
  border-radius: 6px 6px 0 0;
}

.ivu-modal-header {
  padding: 0;
  border-radius: 10px;
  border: none;
}

.ivu-modal-header .header-modal2 {
  height: 74px;
  line-height: 74px;
  font-size: 20px;
  font-weight: 500;
}

.ivu-modal-header p, .ivu-modal-header-inner {
  height: 96px;
  color: #fff;
  line-height: 96px;
  font-size: 24px;
}

.describe {
  text-align: center;
  padding-top: 30px;

  .success {
    font-size: 20px;
    color: #333333;
    margin-top: 10px;
  }
}

.ivu-modal-footer {
  border: none;
  display: flex;
  justify-content: center;
}

.footer .ivu-btn {
  width: 128px;
  height: 40px;
  border: none;
}

.cancel {
  background: #EEEEEE;
}

.vertical-center-modal {
  display: flex;
  align-items: center;
  justify-content: center;

  .ivu-modal {
    top: 0;
  }
}
/deep/ .vertical-center-modal .ivu-modal-header{
  padding: 0 !important;
  border: none;
}
/deep/ .ivu-modal-content{
  border-radius: 14px 14px 0 0;
  overflow hidden
}
.footer {
  display: flex;
  justify-content: space-around;
}

.demo-i-circle-custom {
  & h1 {
    color: #CCCCCC;
    font-size: 12px;
    font-weight: normal;
    line-height: 17px;
  }

  & p {
    color: #2A7EFB;
    font-size: 14px;
    line-height: 20px;
    margin: 6px 0;
  }

  & span {
    display: block;
    color: #CCCCCC;
    font-size: 12px;
    line-height: 17px;
  }

  & span i {
    font-style: normal;
    color: #3f414d;
  }
}

.proportion {
  font-size: 20px;
  font-weight: 600;
  color: #2A7EFB;
  margin: 12px 0 6px;
}

.wait {
  font-size: 12px;
  font-weight: 400;
  color: #999999;
}

.footer2 {
  display: flex;
  flex-wrap: wrap;
  justify-content center
  align-items: center
  flex-direction: column;

  .confirm, .back {
    width: 210px;
    height: 40px;
  }

  .back {
    border: none;
  }
}

.describe {
  padding-top: 0;
}

.describe h2 {
  font-size: 22px;
  font-weight: 400;
  color: #333333;
  margin-bottom: 12px;
}

.acea-row.row-top {
  position: relative;
  overflow: hidden;
}

.update {
  margin: 27px 16px;
}
</style>
