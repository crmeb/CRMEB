<template>
  <div class="diy-page">
    <div class="i-layout-page-header header_top">
      <div class="i-layout-page-header fl_header">
        <router-link :to="{ path: '/admin/setting/pages/devise' }"
          ><Button icon="ios-arrow-back" size="small" type="text">返回</Button></router-link
        >
        <Divider type="vertical" />
        <span class="ivu-page-header-title mr20" style="padding: 0" v-text="$route.meta.title"></span>
        <div class="rbtn">
          <Button v-if="pageId !== 0" class="bnt" @click="setmoren" :loading="loading">保存默认</Button>
          <Button v-if="pageId !== 0" class="bnt ml20" @click="getmoren" :loading="loading">恢复默认</Button>
          <!-- <div class="data" @click="setmoren">设置默认</div>
            <div class="data" @click="getmoren">恢复默认</div> -->
          <Button class="bnt ml20" type="primary" @click="saveConfig" :loading="loading">保存</Button>
          <Button class="bnt ml20" @click="reast">重置</Button>
        </div>
      </div>
    </div>

    <Card :bordered="false" dis-hover class="ivu-mt" style="margin: 0 10px">
      <div class="diy-wrapper" :style="'height:' + (clientHeight - 150) + 'px;'">
        <!-- 左侧 -->
        <div class="left">
          <div class="title-bar">
            <div
              class="title-item"
              :class="{ on: tabCur == index }"
              v-for="(item, index) in tabList"
              :key="index"
              @click="bindTab(index)"
            >
              {{ item.title }}
            </div>
          </div>
          <div class="wrapper" :style="'height:' + (clientHeight - 150) + 'px;'" v-if="tabCur == 0">
            <div v-for="(item, index) in leftMenu" :key="index">
              <div class="tips" @click="item.isOpen = !item.isOpen">
                {{ item.title }}

                <Icon type="ios-arrow-forward" size="16" v-if="!item.isOpen" />
                <Icon type="ios-arrow-down" size="16" v-else />
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
                  v-for="(element, index) in item.list"
                  :key="element.id"
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
          <!--                    <div style="padding: 0 20px"><Button type="primary" style="width: 100%" @click="saveConfig">保存</Button></div>-->
          <div class="wrapper" v-else :style="'height:' + (clientHeight - 200) + 'px;'">
            <div class="link-item" v-for="(item, index) in urlList" :key="index">
              <div class="name">{{ item.name }}</div>
              <div class="link-txt">地址：{{ item.url }}</div>
              <div class="params">
                <span class="txt">参数：</span>
                <span>{{ item.parameter }}</span>
              </div>
              <div class="lable">
                <p class="txt">例如：{{ item.example }}</p>
                <Button size="small" @click="onCopy(item.example)">复制 </Button>
              </div>
            </div>
          </div>
        </div>
        <!-- 中间 -->
        <div
          class="wrapper-con"
          style="flex: 1; background: #f0f2f5; display: flex; justify-content: center; padding-top: 20px; height: 100%"
        >
          <div class="content">
            <div class="contxt" style="display: flex; flex-direction: column; overflow: hidden; height: 100%">
              <div class="overflowy">
                <div class="picture">
                  <img src="@/assets/images/electric.png" />
                </div>
                <div class="page-title" :class="{ on: activeIndex == -100 }" @click="showTitle">
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
                      ');height:' +
                      rollHeight +
                      'px;'
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
                            <Tooltip content="删除当前模块" placement="top">
                              <div class="iconfont iconshanchu2" @click.stop="bindDelete(item, key)"></div>
                            </Tooltip>

                            <div class="iconfont iconfuzhi" @click.stop="bindAddDom(item, 0, key)"></div>
                            <div
                              class="iconfont iconshangyi"
                              :class="key === 0 ? 'on' : ''"
                              @click.stop="movePage(item, key, 1)"
                            ></div>
                            <div
                              class="iconfont iconxiayi"
                              :class="key === mConfig.length - 1 ? 'on' : ''"
                              @click.stop="movePage(item, key, 0)"
                            ></div>
                          </div>
                        </div>
                        <div class="handle"></div>
                      </div>
                    </draggable>
                  </div>
                </div>
              </div>
              <div class="overflowy">
                <div class="page-foot" @click="showFoot" :class="{ on: activeIndex == -101 }">
                  <footPage></footPage>
                  <div class="delete-box"></div>
                  <div class="handle"></div>
                </div>
              </div>
              <!-- <div class="defaultData" v-if="pageId !== 0">
                <div class="data" @click="setmoren">设置默认</div>
                <div class="data" @click="getmoren">恢复默认</div>
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
    </Card>
    <!--<div class="foot-box">-->
    <!--<Button @click="reast">重置</Button>-->
    <!--<Button type="primary" @click="saveConfig" :loading="loading"-->
    <!--&gt;保存-->
    <!--</Button-->
    <!--&gt;-->
    <!--</div>-->
  </div>
</template>

<script crossorigin="anonymous">
import { categoryList, getDiyInfo, saveDiy, getUrl, setDefault, recovery } from '@/api/diy';
import vuedraggable from 'vuedraggable';
import mPage from '@/components/mobilePageDiy/index.js';
import mConfig from '@/components/mobileConfigDiy/index.js';
import footPage from '@/components/pagesFoot';
import { mapState } from 'vuex';
import html2canvas from 'html2canvas';

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
      isFllow: false,
    };
  },
  beforeRouteLeave(to, from, next) {
    // 导航离开该组件的对应路由时调用
    // 可以访问组件实例 `this`
    if (to.name === 'setting_devise') {
      this.$Modal.confirm({
        title: '确定要离开当前页吗？',
        content: '离开前请确认保存您的设计',
        okText: '保存并离开',
        cancelText: '离开',
        loading: true,
        onOk: () => {
          setTimeout(() => {
            this.saveConfig();
            this.$Modal.remove();
            next();
          }, 1500);
        },
        onCancel: () => {
          next();
        },
      });
    }
    // 执行路由跳转
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
    this.categoryList();
    this.getUrlList();
    this.pageId = this.$route.query.id;
    this.pageName = this.$route.query.name;
    this.pageType = this.$route.query.type;
    this.lConfig = this.objToArr(mPage);
  },
  mounted() {
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
      this.clientHeight = `${document.documentElement.clientHeight}` - 65.81; //获取浏览器可视区域高度
      let H = `${document.documentElement.clientHeight}` - 180;
      this.rollHeight = H > 650 ? 650 : H;
      let that = this;
      window.onresize = function () {
        that.clientHeight = `${document.documentElement.clientHeight}` - 65.81;
        let H = `${document.documentElement.clientHeight}` - 180;
        that.rollHeight = H > 650 ? 650 : H;
      };
    });
  },
  methods: {
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
          this.$Message.success('复制成功');
        })
        .catch((err) => {
          this.$Message.error('复制失败');
        });
    },
    onError() {
      this.$Message.error('复制失败');
    },
    //设置默认数据
    setmoren() {
      this.$Modal.confirm({
        title: '保存为默认数据',
        content: '您确定将当前设计设为默认数据吗？',
        onOk: () => {
          setDefault(this.pageId)
            .then((res) => {
              this.$Message.success(res.msg);
            })
            .catch((err) => {
              this.$Message.error(err.msg);
            });
        },
        onCancel: () => {},
      });
    },
    //恢复默认
    getmoren() {
      this.$Modal.confirm({
        title: '恢复默认数据',
        content: '您确定恢复为之前保存的默认数据吗？',
        onOk: () => {
          recovery(this.pageId)
            .then((res) => {
              this.$Message.success(res.msg);
              this.reload();
            })
            .catch((err) => {
              this.$Message.error(err.msg);
            });
        },
        onCancel: () => {},
      });
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
        if (evt.moved.element.name == 'search_box') {
          return this.$Message.warning('该组件禁止拖拽');
        }
        // if (evt.moved.element.name == "nav_bar") {
        //     return this.$Message.warning("该组件禁止拖拽");
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
      if (item.name == 'search_box') {
        return this.$Message.warning('该组件禁止移动');
      }
      // if (item.name == "nav_bar") {
      //     return this.$Message.warning("该组件禁止移动");
      // }
      if (type) {
        // if(this.mConfig[index-1].name  == "search_box" || this.mConfig[index-1].name  == "nav_bar"){
        if (this.mConfig[index - 1].name == 'search_box') {
          return this.$Message.warning('搜索框必须为顶部');
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
        if (this.isSearch) return this.$Message.error('该组件只能添加一次');
        this.isSearch = true;
      }
      if (item.name == 'nav_bar') {
        if (this.isTab) return this.$Message.error('该组件只能添加一次');
        this.isTab = true;
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
          this.mConfig.push(tempItem);
          this.activeIndex = this.mConfig.length - 1;
          this.rConfig.push(tempItem);
        } else {
          this.mConfig.splice(index + 1, 0, tempItem);
          this.activeIndex = index;
        }
      }
      this.mConfig.forEach((el, index) => {
        el.num = new Date().getTime() * 1000 + index;
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
          marketing.list.push(el);
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
    diySaveDate(val) {
      saveDiy(this.pageId, {
        type: this.pageType,
        value: val,
        title: this.titleTxt,
        name: this.nameTxt,
        is_show: this.showTxt ? 1 : 0,
        is_bg_color: this.colorTxt ? 1 : 0,
        color_picker: this.colorPickerTxt,
        bg_pic: this.picUrlTxt,
        bg_tab_val: this.tabValTxt,
        is_bg_pic: this.picTxt ? 1 : 0,
      })
        .then((res) => {
          this.loading = false;
          this.pageId = res.data.id;
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    // 保存配置
    saveConfig() {
      if (this.mConfig.length == 0) {
        return this.$Message.error('暂未添加任何组件，保存失败！');
      }
      this.loading = true;
      let val = this.$store.state.mobildConfig.defaultArray;
      if (!this.footActive) {
        let timestamp = new Date().getTime() * 1000;
        val[timestamp] = this.$store.state.mobildConfig.pageFooter;
        this.footActive = true;
      }
      this.$nextTick(function () {
        this.diySaveDate(val);
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
        this.$Message.error('新增页面，无法重置');
      } else {
        this.$Modal.confirm({
          title: '提示',
          content: '<p>是否重置当前页面数据</p>',
          onOk: () => {
            this.mConfig = [];
            this.rConfig = [];
            this.activeIndex = -99;
            this.getDefaultConfig();
          },
          onCancel: () => {},
        });
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

<style scoped lang="stylus">
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
  /* min-width 700px; */
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

.left:hover::-webkit-scrollbar-thumb, .right-box:hover::-webkit-scrollbar-thumb {
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
  border: 1px dashed #1890ff;
  color: #1890ff;
  background-color: #edf4fb;
}

.scroll-box .conter {
  display: none !important;
}

.dragClass {
  background-color: #fff;
}

.ivu-mt {
  display: flex;
  justify-content: space-between;
}

.iconfont-diy {
  font-size: 24px;
  color: #1890ff;
}

.diy-wrapper {
  max-width: 100%;
  min-width: 1100px;
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;

  /* height: 84.5vh; */
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
          color: #1890FF;
          font-size: 14px;
          border-bottom: 1px solid #1890FF;
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
      border-bottom: 1px solid #F5F5F5;
      font-size: 12px;
      color: #323232;

      .name {
        font-size: 14px;
        color: #1890FF;
      }

      .link-txt {
        margin-top: 2px;
        word-break: break-all;
      }

      .params {
        margin-top: 5px;
        color: #1CBE6B;
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
        border: 2px dashed #1890ff;
        padding: 10px 0;
      }

      &:hover, &.on {
        /* cursor: move; */
        .delete-box {
          /* display: block; */
        }
      }

      &.on {
        cursor: move;

        .delete-box {
          display: block;
          border: 2px solid #1890ff;
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
        border: 2px dashed #1890ff;
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

      &:hover, &.on {
        /* cursor: move; */
        .delete-box {
          /* display: block; */
        }
      }

      &.on {
        cursor: move;

        .delete-box {
          display: block;
          border: 2px solid #1890ff;
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
          border: 2px dashed #1890ff;

          /* padding: 10px 0; */
          .handleType {
            position: absolute;
            right: -43px;
            top: 0;
            width: 36px;
            height: 143px;
            border-radius: 4px;
            background-color: #1890ff;
            cursor: pointer;
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 4px 0;

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
            border: 2px solid #1890ff;
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

    /deep/ .ivu-tabs-bar {
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

/deep/ .ivu-scroll-loader {
  display: none;
}

/deep/ .ivu-card-body {
  width: 100%;
}

.rbtn {
  position: absolute;
  right: 20px;
}
</style>
