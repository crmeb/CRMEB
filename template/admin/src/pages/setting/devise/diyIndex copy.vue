<template>
  <div class="diy-page">
    <div class="i-layout-page-header header-title">
      <div class="fl_header">
        <span class="ivu-page-header-title mr20" style="padding: 0" v-text="$route.meta.title"></span>
        <div class="rbtn">
          <el-button class="ml20 header-btn look" v-db-click @click="preview" :loading="loading">预览</el-button>
          <el-button class="ml20 header-btn close" v-db-click @click="closeWindow" :loading="loading">关闭</el-button>
          <el-button class="ml20 header-btn save" v-db-click @click="saveConfig(0)" :loading="loading">保存</el-button>
        </div>
      </div>
    </div>

    <el-card :bordered="false" shadow="never">
      <div class="diy-wrapper">
        <!-- 左侧 -->
        <div class="left">
          <div class="title-bar">
            <div
              class="title-item"
              :class="{ on: tabCur == index }"
              v-for="(item, index) in tabList"
              :key="index"
              v-db-click
              @click="bindTab(index)"
            >
              {{ item.title }}
            </div>
          </div>
          <div class="wrapper" v-if="tabCur == 0">
            <div v-for="(item, index) in leftMenu" :key="index">
              <div class="tips" v-db-click @click="item.isOpen = !item.isOpen">
                {{ item.title }}

                <i class="el-icon-arrow-right" style="font-size: 16px" v-if="!item.isOpen" />
                <i type="ios-el-icon-arrow-down" style="font-size: 16px" v-else />
              </div>
              <draggable
                class="dragArea list-group"
                :list="item.list"
                :group="{ name: 'people', pull: 'clone', put: false }"
                :clone="cloneDog"
                dragClass="dragClass"
                filter=".search , .navbar"
              >
                <!--filter=".search , .navbar"-->
                <!--:class="{ search: element.cname == '搜索框' , navbar: element.cname == '商品分类' }"-->
                <div
                  class="list-group-item"
                  :class="{
                    search: element.cname == '搜索框',
                    navbar: element.cname == '商品分类',
                  }"
                  v-for="element in item.list"
                  :key="element.id"
                  v-db-click
                  @click="addDom(element, 1)"
                  v-show="item.isOpen"
                >
                  <div>
                    <div class="position" style="display: none">释放鼠标将组建添加到此处</div>
                    <span class="conter iconfont-diy" :class="element.icon"></span>
                    <p class="conter">{{ element.cname }}</p>
                  </div>
                </div>
              </draggable>
            </div>
          </div>
          <!--                    <div style="padding: 0 20px"><el-button type="primary" style="width: 100%" v-db-click @click="saveConfig">保存</el-button></div>-->
          <div class="wrapper" v-else :style="'height:' + (clientHeight - 200) + 'px;'">
            <div class="link-item" v-for="(item, index) in urlList" :key="index">
              <div class="acea-row row-between-wrapper">
                <div class="name">{{ item.name }}</div>
                <span class="copy_btn" v-db-click @click="onCopy(item.example)">复制</span>
              </div>
              <div class="link-txt">地址：{{ item.url }}</div>
              <div class="params">
                <span class="txt">参数：</span>
                <span>{{ item.parameter }}</span>
              </div>
              <div class="lable">
                <p class="txt">例如：{{ item.example }}</p>
              </div>
            </div>
          </div>
        </div>
        <!-- 中间 -->
        <div
          class="wrapper-con"
          style="flex: 1; background: #f0f2f5; display: flex; justify-content: center; padding-top: 20px; height: 100%"
        >
          <div class="acticons">
            <el-button class="bnt mb10" v-db-click @click="showTitle">页面设置</el-button>
            <span></span>
            <el-button class="bnt mb10" v-db-click @click="nameModal = true">另存模板</el-button>
            <span></span>
            <el-button class="bnt" v-db-click @click="reast">重置</el-button>
          </div>
          <div class="content">
            <div class="contxt" style="display: flex; flex-direction: column; overflow: hidden; height: 100%">
              <div class="overflowy">
                <div class="picture">
                  <img src="@/assets/images/electric.png" />
                </div>
                <div class="page-title" :class="{ on: activeIndex == -100 }" v-db-click @click="showTitle">
                  {{ titleTxt }}
                  <div class="delete-box"></div>
                  <div class="handle"></div>
                </div>
              </div>
              <div class="scrollCon">
                <div style="width: 460px; margin: 0 auto">
                  <div
                    class="scroll-box"
                    :class="
                      picTxt && tabValTxt == 2
                        ? 'fullsize noRepeat'
                        : picTxt && tabValTxt == 1
                        ? 'repeat ysize'
                        : 'noRepeat ysize'
                    "
                    :style="
                      'background-color:' +
                      (colorTxt ? colorPickerTxt : '') +
                      ';background-image: url(' +
                      (picTxt ? picUrlTxt : '') +
                      ');height: calc(100vh - 155px);'
                    "
                    ref="imgContainer"
                  >
                    <draggable
                      class="dragArea list-group"
                      :list="mConfig"
                      group="people"
                      @change="log"
                      filter=".top"
                      :move="onMove"
                      animation="300"
                    >
                      <div
                        class="mConfig-item"
                        :class="{
                          on: activeIndex == key,
                          top: item.name == 'search_box' || item.name == 'nav_bar',
                        }"
                        v-for="(item, key) in mConfig"
                        :key="key"
                        v-db-click
                        @click.stop="bindconfig(item, key)"
                        :style="colorTxt ? 'background-color:' + colorPickerTxt + ';' : 'background-color:#fff;'"
                      >
                        <component
                          :is="item.name"
                          ref="getComponentData"
                          :configData="propsObj"
                          :index="key"
                          :num="item.num"
                        ></component>
                        <div class="delete-box">
                          <div class="handleType">
                            <div
                              class="iconfont iconshangyi"
                              :class="key === 0 ? 'on' : ''"
                              v-db-click
                              @click.stop="movePage(item, key, 1)"
                            ></div>
                            <div
                              class="iconfont iconxiayi"
                              :class="key === mConfig.length - 1 ? 'on' : ''"
                              v-db-click
                              @click.stop="movePage(item, key, 0)"
                            ></div>
                            <div class="iconfont iconfuzhi" v-db-click @click.stop="bindAddDom(item, 0, key)"></div>
                            <el-tooltip content="删除当前模块" placement="top">
                              <div class="iconfont iconshanchu2" v-db-click @click.stop="bindDelete(item, key)"></div>
                            </el-tooltip>
                          </div>
                        </div>
                        <div class="handle"></div>
                      </div>
                    </draggable>
                  </div>
                </div>
              </div>
              <div class="overflowy">
                <div class="page-foot" v-db-click @click="showFoot" :class="{ on: activeIndex == -101 }">
                  <footPage></footPage>
                  <div class="delete-box"></div>
                  <div class="handle"></div>
                </div>
              </div>
              <!-- <div class="defaultData" v-if="pageId !== 0">
                <div class="data" v-db-click @click="setmoren">设置默认</div>
                <div class="data" v-db-click @click="getmoren">恢复默认</div>
              </div> -->
            </div>
          </div>
        </div>
        <!-- 右侧 -->
        <div class="right-box">
          <div class="mConfig-item" style="background-color: #fff" v-for="(item, key) in rConfig" :key="key">
            <div class="title-bar">{{ item.cname }}</div>
            <component
              :is="item.configName"
              @config="config"
              :activeIndex="activeIndex"
              :num="item.num"
              :index="key"
            ></component>
          </div>
        </div>
      </div>
    </el-card>
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
    <el-dialog :visible.sync="nameModal" width="470px" title="设置模版名称" :show-close="true">
      <el-input v-model="saveName" placeholder="请输入模版名称"></el-input>
      <span slot="footer" class="dialog-footer">
        <el-button v-db-click @click="nameModal = false">取 消</el-button>
        <el-button type="primary" v-db-click @click="saveModal">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script crossorigin="anonymous">
import { categoryList, getDiyInfo, saveDiy, getUrl, setDefault, recovery, getRoutineCode } from '@/api/diy';
import vuedraggable from 'vuedraggable';
import mPage from '@/components/mobilePageDiy/index.js';
import mConfig from '@/components/mobileConfigDiy/index.js';
import footPage from '@/components/pagesFoot';
import { mapState } from 'vuex';
import html2canvas from 'html2canvas';
import QRCode from 'qrcodejs2';
import { writeUpdate } from '@api/order';
import checkArray from '@/libs/permission';
import Setting from '@/setting';

let idGlobal = 0;
export default {
  inject: ['reload'],
  name: 'index.vue',
  components: {
    footPage,
    html2canvas,
    draggable: vuedraggable,
    ...mPage,
    ...mConfig,
  },
  filters: {
    filterTxt(val) {
      if (val) {
        return (val = val.substr(0, val.length - 1));
      }
    },
  },
  computed: {
    ...mapState({
      titleTxt: (state) => state.mobildConfig.pageTitle || '首页',
      nameTxt: (state) => state.mobildConfig.pageName || '模板',
      showTxt: (state) => state.mobildConfig.pageShow,
      colorTxt: (state) => state.mobildConfig.pageColor,
      picTxt: (state) => state.mobildConfig.pagePic,
      colorPickerTxt: (state) => state.mobildConfig.pageColorPicker,
      tabValTxt: (state) => state.mobildConfig.pageTabVal,
      picUrlTxt: (state) => state.mobildConfig.pagePicUrl,
    }),
  },
  data() {
    return {
      clientHeight: '', //页面动态高度
      rollHeight: '',
      leftMenu: [], // 左侧菜单
      lConfig: [], // 左侧组件
      mConfig: [], // 中间组件渲染
      rConfig: [], // 右侧组件配置
      activeConfigName: '',
      propsObj: {}, // 组件传递的数据,
      activeIndex: -100, // 选中的下标
      number: 0,
      pageId: '',
      pageName: '',
      pageType: '',
      category: [],
      BaseURL: Setting.apiBaseURL.replace(/adminapi/, ''),
      tabList: [
        {
          title: '组件库',
          key: 0,
        },
        {
          title: '页面链接',
          key: 1,
        },
      ],
      tabCur: 0,
      urlList: [],
      footActive: false,
      loading: false,
      isSearch: false,
      isTab: false,
      isHomeProduct: false,
      isFllow: false,
      qrcodeImg: '',
      modal: false,
      nameModal: false,
      saveName: '',
    };
  },
  beforeRouteLeave(to, from, next) {
    // 导航离开该组件的对应路由时调用
  },
  beforeCreate() {
    this.$store.commit('mobildConfig/titleUpdata', '');
    this.$store.commit('mobildConfig/nameUpdata', '');
    this.$store.commit('mobildConfig/showUpdata', 1);
    this.$store.commit('mobildConfig/colorUpdata', 0);
    this.$store.commit('mobildConfig/picUpdata', 0);
    this.$store.commit('mobildConfig/pickerUpdata', '#f5f5f5');
    this.$store.commit('mobildConfig/radioUpdata', 0);
    this.$store.commit('mobildConfig/picurlUpdata', '');
    this.$store.commit('mobildConfig/SETEMPTY');
  },
  created() {
    window.onbeforeunload = () => {
      return '刷新页面将丢失内容,是否继续?';
    };
    this.categoryList();
    this.getUrlList();
    this.pageId = this.$route.query.id;
    this.pageName = this.$route.query.name;
    this.pageType = this.$route.query.type;
    this.lConfig = this.objToArr(mPage);
  },
  mounted() {
    // window.addEventListener('onbeforeunload', this.beforeUnload);
    let imgList = {
      imgList: [require('@/assets/images/foot-005.png'), require('@/assets/images/foot-006.png')],
      name: '购物车',
      link: '/pages/order_addcart/order_addcart',
    };
    this.$nextTick(() => {
      this.$store.commit('mobildConfig/FOOTER', {
        title: '专题页是否显示',
        name: imgList,
      });
      this.arraySort();
      if (this.pageId != 0) {
        this.getDefaultConfig();
      } else {
        this.showTitle();
      }
      this.clientHeight = `${document.documentElement.clientHeight}`; //获取浏览器可视区域高度
      let H = `${document.documentElement.clientHeight}` - 180;
      this.rollHeight = H > 650 ? 650 : H;
      let that = this;
      window.onresize = function () {
        that.clientHeight = `${document.documentElement.clientHeight}`;
        let H = `${document.documentElement.clientHeight}` - 180;
        that.rollHeight = H > 650 ? 650 : H;
      };
    });
  },
  methods: {
    saveModal() {
      if (!this.saveName) return this.$message.warning('请先输入模板名称');
      this.saveConfig(1, this.saveName);
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
        this.creatQrCode(this.pageId);
        this.routineCode(this.$route.query.id);
      });
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
    closeWindow() {
      this.$msgbox({
        title: '提示',
        message: '关闭页面前请先保存数据，未保存的话数据会丢失',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonText: '确定',
        iconClass: 'el-icon-warning',
        confirmButtonClass: 'btn-custom-cancel',
      })
        .then(() => {
          setTimeout(() => {
            // this.saveConfig();
            window.close();
          }, 1000);
        })
        .catch(() => {});
    },
    leftRemove({ to, from, item, clone, oldIndex, newIndex }) {
      if (this.isSearch && newIndex == 0) {
        if (item._underlying_vm_.name == 'z_wechat_attention') {
          this.isFllow = true;
        } else {
          this.$store.commit('mobildConfig/ARRAYREAST', this.mConfig[0].num);
          this.mConfig.splice(0, 1);
        }
      }
      if ((this.isFllow = true && newIndex >= 1)) {
        this.$store.commit('mobildConfig/ARRAYREAST', this.mConfig[0].num);
      }
    },
    onMove(e) {
      if (e.relatedContext.element.name == 'search_box') return false;
      if (e.relatedContext.element.name == 'nav_bar') return false;
      return true;
    },
    onCopy(copyData) {
      this.$copyText(copyData)
        .then((message) => {
          this.$message.success('复制成功');
        })
        .catch((err) => {
          this.$message.error('复制失败');
        });
    },
    onError() {
      this.$message.error('复制失败');
    },
    //设置默认数据
    setmoren() {
      this.$msgbox({
        title: '保存为默认数据',
        message: '您确定将当前设计设为默认数据吗',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonText: '确定',
        iconClass: 'el-icon-warning',
        confirmButtonClass: 'btn-custom-cancel',
      })
        .then(() => {
          setDefault(this.pageId)
            .then((res) => {
              this.$message.success(res.msg);
            })
            .catch((err) => {
              this.$message.error(err.msg);
            });
        })
        .catch(() => {});
    },
    //恢复默认
    getmoren() {
      this.$msgbox({
        title: '恢复默认数据',
        message: '您确定恢复为之前保存的默认数据吗',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonText: '确定',
        iconClass: 'el-icon-warning',
        confirmButtonClass: 'btn-custom-cancel',
      })
        .then(() => {
          recovery(this.pageId)
            .then((res) => {
              this.$message.success(res.msg);
              this.reload();
            })
            .catch((err) => {
              this.$message.error(err.msg);
            });
        })
        .catch(() => {});
    },
    // 获取url
    getUrlList() {
      getUrl().then((res) => {
        this.urlList = res.data.url;
      });
    },
    // 左侧tab
    bindTab(index) {
      this.tabCur = index;
    },
    // 页面标题点击
    showTitle() {
      this.activeIndex = -100;
      let obj = {};
      for (var i in mConfig) {
        if (i == 'pageTitle') {
          // this.rConfig = obj
          obj = mConfig[i];
          obj.configName = mConfig[i].name;
          obj.cname = '页面设置';
        }
      }
      let abc = obj;
      this.rConfig = [];
      this.rConfig[0] = JSON.parse(JSON.stringify(obj));
    },
    // 页面底部点击
    showFoot() {
      this.activeIndex = -101;
      let obj = {};
      for (var i in mConfig) {
        if (i == 'pageFoot') {
          // this.rConfig = obj
          obj = mConfig[i];
          obj.configName = mConfig[i].name;
          obj.cname = '底部菜单';
        }
      }
      let abc = obj;
      this.rConfig = [];
      this.rConfig[0] = JSON.parse(JSON.stringify(obj));
    },
    // 对象转数组
    objToArr(data) {
      let obj = Object.keys(data);
      let m = obj.map((key) => data[key]);
      return m;
    },
    log(evt) {
      // 中间拖拽排序
      if (evt.moved) {
        if (evt.moved.element.name == 'search_box' || evt.moved.element.name == 'nav_bar') {
          return this.$message.warning('该组件禁止拖拽');
        }

        // if (evt.moved.element.name == "nav_bar") {
        //     return this.$message.warning("该组件禁止拖拽");
        // }
        evt.moved.oldNum = this.mConfig[evt.moved.oldIndex].num;
        evt.moved.newNum = this.mConfig[evt.moved.newIndex].num;
        evt.moved.status = evt.moved.oldIndex > evt.moved.newIndex;
        this.mConfig.forEach((el, index) => {
          el.num = new Date().getTime() * 1000 + index;
        });
        evt.moved.list = this.mConfig;
        this.rConfig = [];
        let item = evt.moved.element;
        let tempItem = JSON.parse(JSON.stringify(item));
        this.rConfig.push(tempItem);
        this.activeIndex = evt.moved.newIndex;
        this.$store.commit('mobildConfig/SETCONFIGNAME', item.name);
        this.$store.commit('mobildConfig/defaultArraySort', evt.moved);
      }
      // 从左向右拖拽排序
      if (evt.added) {
        let data = evt.added.element;
        let obj = {};
        let timestamp = new Date().getTime() * 1000;
        data.num = timestamp;
        this.activeConfigName = data.name;
        let tempItem = JSON.parse(JSON.stringify(data));
        tempItem.id = 'id' + tempItem.num;
        this.mConfig[evt.added.newIndex] = tempItem;
        this.rConfig = [];
        this.rConfig.push(tempItem);
        this.mConfig.forEach((el, index) => {
          el.num = new Date().getTime() * 1000 + index;
        });
        evt.added.list = this.mConfig;
        this.activeIndex = evt.added.newIndex;
        // 保存组件名称
        this.$store.commit('mobildConfig/SETCONFIGNAME', data.name);
        this.$store.commit('mobildConfig/defaultArraySort', evt.added);
      }
    },
    cloneDog(data) {
      // this.mConfig.push(tempItem)
      return {
        ...data,
      };
    },
    //数组元素互换位置
    swapArray(arr, index1, index2) {
      arr[index1] = arr.splice(index2, 1, arr[index1])[0];
      return arr;
    },
    //点击上下移动；
    movePage(item, index, type) {
      if (type) {
        if (index == 0) {
          return;
        }
      } else {
        if (index == this.mConfig.length - 1) {
          return;
        }
      }
      if (item.name == 'search_box' || item.name == 'nav_bar') {
        return this.$message.warning('该组件禁止移动');
      }
      // if (item.name == "nav_bar") {
      //     return this.$message.warning("该组件禁止移动");
      // }
      if (type) {
        // if(this.mConfig[index-1].name  == "search_box" || this.mConfig[index-1].name  == "nav_bar"){
        if (this.mConfig[index - 1].name == 'search_box') {
          return this.$message.warning('搜索框必须为顶部');
        }
        this.swapArray(this.mConfig, index - 1, index);
      } else {
        this.swapArray(this.mConfig, index, index + 1);
      }
      let obj = {};
      this.rConfig = [];
      obj.oldIndex = index;
      if (type) {
        obj.newIndex = index - 1;
      } else {
        obj.newIndex = index + 1;
      }
      this.mConfig.forEach((el, index) => {
        el.num = new Date().getTime() * 1000 + index;
      });
      let tempItem = JSON.parse(JSON.stringify(item));
      this.rConfig.push(tempItem);
      obj.element = item;
      obj.list = this.mConfig;
      if (type) {
        this.activeIndex = index - 1;
      } else {
        this.activeIndex = index + 1;
      }
      this.$store.commit('mobildConfig/SETCONFIGNAME', item.name);
      this.$store.commit('mobildConfig/defaultArraySort', obj);
    },
    // 组件添加
    addDomCon(item, type, index) {
      if (item.name == 'search_box') {
        if (this.isSearch) return this.$message.error('该组件只能添加一次');
        this.isSearch = true;
      }
      if (item.name == 'nav_bar') {
        if (this.isTab) return this.$message.error('该组件只能添加一次');
        this.isTab = true;
      }
      if (item.name == 'home_product') {
        if (this.isHomeProduct) return this.$message.error('该组件只能添加一次');
        this.isHomeProduct = true;
      }
      idGlobal += 1;
      let obj = {};
      let timestamp = new Date().getTime() * 1000;
      item.num = `${timestamp}`;
      item.id = `id${timestamp}`;
      this.activeConfigName = item.name;
      let tempItem = JSON.parse(JSON.stringify(item));
      if (item.name == 'search_box') {
        this.rConfig = [];
        this.mConfig.unshift(tempItem);
        this.activeIndex = 0;
        this.rConfig.push(tempItem);
      }
      // 动态拖动可上传此部分代码
      else if (item.name == 'nav_bar') {
        this.rConfig = [];
        if (this.mConfig[0] && this.mConfig[0].name === 'search_box') {
          this.mConfig.splice(1, 0, tempItem);
          this.activeIndex = 1;
        } else {
          this.mConfig.splice(0, 0, tempItem);
          this.activeIndex = 0;
        }
        this.rConfig.push(tempItem);
      } else {
        if (type) {
          this.rConfig = [];
          this.mConfig.splice(this.activeIndex + 1, 0, tempItem);
          this.rConfig.splice(this.activeIndex + 1, 0, tempItem);
          this.activeIndex += 1;
        } else {
          this.mConfig.splice(index + 1, 0, tempItem);
          this.activeIndex = index;
        }
      }
      this.mConfig.forEach((el, i) => {
        el.num = new Date().getTime() * 1000 + i;
      });
      // 保存组件名称
      obj.element = item;
      obj.list = this.mConfig;
      this.$store.commit('mobildConfig/SETCONFIGNAME', item.name);
      this.$store.commit('mobildConfig/defaultArraySort', obj);
    },
    //中间页点击添加模块；
    bindAddDom(item, type, index) {
      let i = item;
      this.lConfig.forEach((j) => {
        if (item.name == j.name) {
          i = j;
        }
      });
      this.addDomCon(i, type, index);
    },
    //左边配置模块点击添加；
    addDom(item, type) {
      this.addDomCon(item, type);
    },
    // 点击显示相应的配置
    bindconfig(item, index) {
      this.rConfig = [];
      let tempItem = JSON.parse(JSON.stringify(item));
      this.rConfig.push(tempItem);
      this.activeIndex = index;
      this.$store.commit('mobildConfig/SETCONFIGNAME', item.name);
    },
    // 组件删除
    bindDelete(item, key) {
      if (item.name == 'search_box') {
        this.isSearch = false;
      }
      if (item.name == 'nav_bar') {
        this.isTab = false;
      }
      if (item.name == 'home_product') {
        this.isHomeProduct = false;
      }
      this.mConfig.splice(key, 1);
      this.rConfig.splice(0, 1);
      if (this.mConfig.length != key) {
        this.rConfig.push(this.mConfig[key]);
      } else {
        if (this.mConfig.length) {
          this.activeIndex = key - 1;
          this.rConfig.push(this.mConfig[key - 1]);
        } else {
          this.showTitle();
        }
      }
      // 删除第几个配置
      this.$store.commit('mobildConfig/DELETEARRAY', item);
    },
    // 组件返回
    config(data) {
      let propsObj = this.propsObj;
      propsObj.data = data;
      propsObj.name = this.activeConfigName;
    },
    addSort(arr, index1, index2) {
      arr[index1] = arr.splice(index2, 1, arr[index1])[0];
      return arr;
    },
    // 数组排序
    arraySort() {
      let tempArr = [];
      let basis = {
        title: '基础组件',
        list: [],
        isOpen: true,
      };
      let marketing = {
        title: '营销组件',
        list: [],
        isOpen: true,
      };
      let tool = {
        title: '工具组件',
        list: [],
        isOpen: true,
      };
      this.lConfig.map((el, index) => {
        if (el.type == 0) {
          basis.list.push(el);
        }
        if (el.type == 1) {
          if (el.name == 'home_seckill' && checkArray('seckill')) {
            marketing.list.push(el);
          } else if (el.name == 'home_bargain' && checkArray('bargain')) {
            marketing.list.push(el);
          } else if (el.name == 'home_pink' && checkArray('combination')) {
            marketing.list.push(el);
          } else if (el.name != 'home_seckill' && el.name != 'home_bargain' && el.name != 'home_pink') {
            marketing.list.push(el);
          }
        }
        if (el.type == 2) {
          tool.list.push(el);
        }
      });
      tempArr.push(basis, marketing, tool);
      this.leftMenu = tempArr;
    },
    // toImage(val){
    //     html2canvas(this.$refs.imgContainer,{
    //         useCORS:true,
    //         logging:true,
    //         taintTest: false,
    //         backgroundColor: null
    //     }).then((canvas) => {
    //         let imgUrl = canvas.toDataURL('image/jpeg');
    //         this.diySaveDate(val,imgUrl)
    //     });
    // },
    diySaveDate(val, init, name) {
      saveDiy(init ? 0 : this.pageId, {
        type: this.pageType,
        value: val,
        title: this.titleTxt,
        name: name || this.nameTxt,
        is_show: this.showTxt ? 1 : 0,
        is_bg_color: this.colorTxt ? 1 : 0,
        color_picker: this.colorPickerTxt,
        bg_pic: this.picUrlTxt,
        bg_tab_val: this.tabValTxt,
        is_bg_pic: this.picTxt ? 1 : 0,
      })
        .then((res) => {
          this.loading = false;
          if (!init) {
            this.pageId = res.data.id;
          }
          this.saveName = '';
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 保存配置
    saveConfig(init, name) {
      if (this.mConfig.length == 0) {
        return this.$message.error('暂未添加任何组件，保存失败！');
      }
      this.loading = true;
      let val = this.$store.state.mobildConfig.defaultArray;
      if (!this.footActive) {
        let timestamp = new Date().getTime() * 1000;
        val[timestamp] = this.$store.state.mobildConfig.pageFooter;
        this.footActive = true;
      }
      this.$nextTick(() => {
        this.nameModal = false;
        this.diySaveDate(val, init, name);
      });
    },
    // 获取默认配置
    getDefaultConfig() {
      getDiyInfo(this.pageId).then(({ data }) => {
        let obj = {};
        let tempARR = [];
        this.$store.commit('mobildConfig/titleUpdata', data.info.title);
        this.$store.commit('mobildConfig/nameUpdata', data.info.name);
        this.$store.commit('mobildConfig/showUpdata', data.info.is_show);
        this.$store.commit('mobildConfig/colorUpdata', data.info.is_bg_color || 0);
        this.$store.commit('mobildConfig/picUpdata', data.info.is_bg_pic || 0);
        this.$store.commit('mobildConfig/pickerUpdata', data.info.color_picker || '#f5f5f5');
        this.$store.commit('mobildConfig/radioUpdata', data.info.bg_tab_val || 0);
        this.$store.commit('mobildConfig/picurlUpdata', data.info.bg_pic || '');
        let newArr = this.objToArr(data.info.value);

        function sortNumber(a, b) {
          return a.timestamp - b.timestamp;
        }

        newArr.sort(sortNumber);
        newArr.map((el, index) => {
          if (el.name == 'headerSerch') {
            this.isSearch = true;
          }
          if (el.name == 'tabNav') {
            this.isTab = true;
          }
          if (el.name == 'promotionList') {
            this.isHomeProduct = true;
          }
          if (el.name == 'goodList') {
            let storage = window.localStorage;
            storage.setItem(el.timestamp, el.selectConfig.activeValue);
          }
          el.id = 'id' + el.timestamp;
          this.lConfig.map((item, j) => {
            if (el.name == item.defaultName) {
              item.num = el.timestamp;
              item.id = 'id' + el.timestamp;
              let tempItem = JSON.parse(JSON.stringify(item));
              tempARR.push(tempItem);
              obj[el.timestamp] = el;
              this.mConfig.push(tempItem);
              // 保存默认组件配置
              this.$store.commit('mobildConfig/ADDARRAY', {
                num: el.timestamp,
                val: el,
              });
            }
          });
        });

        let objs = newArr[newArr.length - 1];

        if (objs.name == 'pageFoot') {
          this.$store.commit('mobildConfig/footPageUpdata', objs);
        }
        this.showTitle();
        // this.rConfig = [];
        // this.activeIndex = 0;
        // this.rConfig.push(this.mConfig[0]);
      });
    },
    categoryList() {
      categoryList((res) => {
        this.category = res.data;
      });
    },
    // 重置
    reast() {
      if (this.pageId == 0) {
        this.$message.error('新增页面，无法重置');
      } else {
        this.$msgbox({
          title: '提示',
          message: '重置会恢复到上次保存的数据，确定不保存当前操作吗？',
          showCancelButton: true,
          cancelButtonText: '取消',
          confirmButtonText: '确定',
          iconClass: 'el-icon-warning',
          confirmButtonClass: 'btn-custom-cancel',
        })
          .then(() => {
            this.mConfig = [];
            this.rConfig = [];
            this.activeIndex = -99;
            this.getDefaultConfig();
          })
          .catch(() => {});
      }
    },
  },
  beforeDestroy() {
    this.$store.commit('mobildConfig/titleUpdata', '');
    this.$store.commit('mobildConfig/nameUpdata', '');
    this.$store.commit('mobildConfig/showUpdata', 1);
    this.$store.commit('mobildConfig/colorUpdata', 0);
    this.$store.commit('mobildConfig/picUpdata', 0);
    this.$store.commit('mobildConfig/pickerUpdata', '#f5f5f5');
    this.$store.commit('mobildConfig/radioUpdata', 0);
    this.$store.commit('mobildConfig/picurlUpdata', '');
    this.$store.commit('mobildConfig/SETEMPTY');
  },
  destroyed() {
    this.$store.commit('mobildConfig/titleUpdata', '');
    this.$store.commit('mobildConfig/nameUpdata', '');
    this.$store.commit('mobildConfig/showUpdata', 1);
    this.$store.commit('mobildConfig/colorUpdata', 0);
    this.$store.commit('mobildConfig/picUpdata', 0);
    this.$store.commit('mobildConfig/pickerUpdata', '#f5f5f5');
    this.$store.commit('mobildConfig/radioUpdata', 0);
    this.$store.commit('mobildConfig/picurlUpdata', '');
    this.$store.commit('mobildConfig/SETEMPTY');
  },
};
</script>
<style>
.el-main {
  padding: 0px !important;
}

.header-title {
  background: var(--prev-color-primary);
  border-radius: 0;
  margin-bottom: 0;
  padding: 16px;
}
.ivu-page-header-title {
  color: #fff;
  font-size: 16px;
}
</style>
<style scoped lang="scss">
::v-deep .el-card__body {
  padding: 0;
}
::v-deep .el-button--small {
  // border-radius: 0;
  border-radius: 4px;
}
.look,
.look:hover,
.look:focus,
.look:active,
.close,
.close:hover,
.close:focus,
.close:active {
  background: var(--prev-color-primary);
  color: #fff;
  border-color: #fff;
}

.save,
.save:hover,
.save:active,
.save:focus {
  background: #fff;
  color: var(--prev-color-primary);
  border-color: var(--prev-color-primary);
}

.ysize {
  background-size: 100%;
}

.fullsize {
  background-size: 100% 100%;
}

.repeat {
  background-repeat: repeat;
}

.noRepeat {
  background-repeat: no-repeat;
}

.wrapper-con {
  position: relative;
  .acticons {
    position: absolute;
    right: 20px;
    top: 20px;
    display: flex;
    flex-direction: column;
    z-index: 1;
    .el-button + .el-button {
      margin-left: 0;
    }
  }
  /* min-width 700px; */
}
.main .content-wrapper {
  padding: 0 !important;
}
.defaultData {
  /* margin-left 20px; */
  cursor: pointer;
  position: absolute;
  left: 50%;
  margin-left: 245px;

  .data {
    margin-top: 20px;
    color: #282828;
    background-color: #fff;
    width: 94px;
    text-align: center;
    height: 32px;
    line-height: 32px;
    border-radius: 3px;
    font-size: 12px;
  }

  .data:hover {
    background-color: #2d8cf0;
    color: #fff;
    border: 0;
  }
}

.overflowy {
  overflow-y: scroll;

  .picture {
    width: 379px;
    height: 20px;
    margin: 0 auto;
    background-color: #fff;
  }
}

.bnt {
  width: 80px !important;
}

/* 定义滑块 内阴影+圆角 */
::-webkit-scrollbar-thumb {
  -webkit-box-shadow: inset 0 0 6px #fff;
  display: none;
}

.left:hover::-webkit-scrollbar-thumb,
.right-box:hover::-webkit-scrollbar-thumb {
  display: block;
}

.contxt:hover ::-webkit-scrollbar-thumb {
  display: block;
}

::-webkit-scrollbar {
  width: 4px !important; /* 对垂直流动条有效 */
}

.scrollCon {
  overflow-y: scroll;
  overflow-x: hidden;
}

.scroll-box .position {
  display: block !important;
  height: 40px;
  text-align: center;
  line-height: 40px;
  border: 1px dashed var(--prev-color-primary);
  color: var(--prev-color-primary);
  background-color: #edf4fb;
}

.scroll-box .conter {
  display: none !important;
}
.conter {
  margin-top: 3px;
}
.dragClass {
  background-color: #fff;
}

.ivu-mt {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.iconfont-diy {
  font-size: 24px;
  color: var(--prev-color-primary);
}

.diy-wrapper {
  max-width: 100%;
  min-width: 1100px;
  display: flex;
  justify-content: space-between;
  height: calc(100vh - 62px);
  .left {
    min-width: 300px;
    max-width: 300px;
    /* border 1px solid #DDDDDD */
    border-radius: 4px;
    height: 100%;

    .title-bar {
      display: flex;
      color: #333;
      border-bottom: 1px solid #eee;
      border-radius: 4px;
      cursor: pointer;

      .title-item {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 1;
        height: 45px;

        &.on {
          color: var(--prev-color-primary);
          font-size: 14px;
          border-bottom: 1px solid var(--prev-color-primary);
        }
      }
    }

    .wrapper {
      padding: 15px;
      overflow-y: scroll;
      -webkit-overflow-scrolling: touch;

      .tips {
        display: flex;
        justify-content: space-between;
        padding-bottom: 15px;
        font-size: 13px;
        color: #000;
        cursor: pointer;

        .ivu-icon {
          color: #000;
        }
      }
    }

    .link-item {
      padding: 10px;
      border-bottom: 1px solid #f5f5f5;
      font-size: 12px;
      color: #323232;

      .name {
        font-size: 14px;
        color: var(--prev-color-primary);
      }
      .copy_btn {
        cursor: pointer;
      }

      .link-txt {
        margin-top: 2px;
        word-break: break-all;
      }

      .params {
        margin-top: 5px;
        color: #1cbe6b;
        word-break: break-all;

        .txt {
          color: #323232;
        }

        span {
          &:last-child i {
            display: none;
            color: red;
          }
        }
      }

      .lable {
        display: flex;
        margin-top: 5px;
        color: #999;

        p {
          flex: 1;
          word-break: break-all;
        }

        button {
          margin-left: 30px;
          width: 38px;
        }
      }
    }

    .dragArea.list-group {
      display: flex;
      flex-wrap: wrap;

      .list-group-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 74px;
        height: 66px;
        margin-right: 17px;
        margin-bottom: 10px;
        font-size: 12px;
        color: #666;
        cursor: pointer;
        border-radius: 5px;
        text-align: center;

        &:hover {
          box-shadow: 0 0 5px 0 rgba(24, 144, 255, 0.3);
          border-right: 5px;
          transform: scale(1.1);
          transition: all 0.2s;
        }

        &:nth-child(3n) {
          margin-right: 0;
        }
      }
    }
  }

  .content {
    position: relative;
    height: 100%;
    width: 100%;

    .page-foot {
      position: relative;
      width: 379px;
      margin: 0 auto 20px auto;

      .delete-box {
        display: none;
        position: absolute;
        left: -2px;
        top: 0;
        width: 383px;
        height: 100%;
        border: 2px dashed var(--prev-color-primary);
        padding: 10px 0;
      }

      &:hover,
      &.on {
        /* cursor: move; */
        .delete-box {
          /* display: block; */
        }
      }

      &.on {
        cursor: move;

        .delete-box {
          display: block;
          border: 2px solid var(--prev-color-primary);
          box-shadow: 0 0 10px 0 rgba(24, 144, 255, 0.3);
        }
      }
    }

    .page-title {
      position: relative;
      height: 35px;
      line-height: 35px;
      background: #fff;
      font-size: 15px;
      color: #333333;
      text-align: center;
      width: 379px;
      margin: 0 auto;

      .delete-box {
        display: none;
        position: absolute;
        left: -2px;
        top: 0;
        width: 383px;
        height: 100%;
        border: 2px dashed var(--prev-color-primary);
        padding: 10px 0;

        span {
          position: absolute;
          right: 0;
          bottom: 0;
          width: 32px;
          height: 16px;
          line-height: 16px;
          display: inline-block;
          text-align: center;
          font-size: 10px;
          color: #fff;
          background: rgba(0, 0, 0, 0.4);
          margin-left: 2px;
          cursor: pointer;
          z-index: 11;
        }
      }

      &:hover,
      &.on {
        /* cursor: move; */
        .delete-box {
          /* display: block; */
        }
      }

      &.on {
        cursor: move;

        .delete-box {
          display: block;
          border: 2px solid var(--prev-color-primary);
          box-shadow: 0 0 10px 0 rgba(24, 144, 255, 0.3);
        }
      }
    }

    .scroll-box {
      flex: 1;
      background-color: #fff;
      width: 379px;
      margin: 0 auto;
      padding-top: 1px;
    }

    .dragArea.list-group {
      width: 100%;
      height: 100%;

      .mConfig-item {
        position: relative;
        cursor: move;

        .delete-box {
          display: none;
          position: absolute;
          left: -2px;
          top: 0;
          width: 383px;
          height: 100%;
          border: 2px dashed var(--prev-color-primary);

          /* padding: 10px 0; */
          .handleType {
            position: absolute;
            right: -43px;
            top: 0;
            width: 36px;
            height: 111px;
            border-radius: 4px;
            background-color: var(--prev-color-primary);
            cursor: pointer;
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 4px 0;
            .el-tooltip {
              background-color: inherit;
              color: inherit;
            }
            .iconfont {
              padding: 5px 0;

              &.on {
                opacity: 0.4;
              }
            }
          }
        }

        &.on {
          cursor: move;

          .delete-box {
            display: block;
            border: 2px solid var(--prev-color-primary);
            box-shadow: 0 0 10px 0 rgba(24, 144, 255, 0.3);
          }
        }
      }

      .mConfig-item:hover {
        transform: scale(1.01);
        box-shadow: 0 0 10px 0 rgba(24, 144, 255, 0.3);
        transition: all 0.2s;
      }
    }
  }

  .right-box {
    max-width: 400px;
    min-width: 400px;
    height: 100%;
    border-radius: 4px;
    overflow: scroll;
    -webkit-overflow-scrolling: touch;

    ::v-deep .ivu-tabs-bar {
      margin-bottom: 16px;
    }

    .title-bar {
      width: 100%;
      height: 45px;
      line-height: 45px;
      padding-left: 24px;
      color: #000;
      border-radius: 4px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
    }
  }

  ::-webkit-scrollbar {
    width: 6px;
    background-color: transparent;
  }

  ::-webkit-scrollbar-track {
    border-radius: 10px;
  }

  ::-webkit-scrollbar-thumb {
    background-color: #bfc1c4;
  }
}

.foot-box {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 80px;
  background: #fff;
  box-shadow: 0px -2px 4px 0px rgba(0, 0, 0, 0.03);

  button {
    width: 100px;
    height: 32px;
    font-size: 13px;

    &:first-child {
      margin-right: 20px;
    }
  }
}

::v-deep .ivu-scroll-loader {
  display: none;
}

::v-deep .ivu-card-body {
  width: 100%;
  padding: 0;
  height: calc(100vh - 73px);
}

.rbtn {
  position: absolute;
  right: 20px;
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
</style>
