<template>
    <div class="article-manager" id="shopp-manager">
      <div class="i-layout-page-header">
        <div class="i-layout-page-header">
          <router-link :to="{path:'/admin/product/product_list'}"><Button icon="ios-arrow-back" size="small"  class="mr20">返回</Button></router-link>
          <span class="ivu-page-header-title mr20" v-text="$route.params.id?'编辑商品':'添加商品'"></span>
        </div>
      </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Tabs v-model="currentTab" @on-click="onhangeTab">
                <TabPane label="商品信息" name="1"></TabPane>
                <TabPane label="商品详情" name="2"></TabPane>
                <TabPane label="其他设置" name="3"></TabPane>
            </Tabs>
            <Form class="formValidate mt35" ref="formValidate" :rules="ruleValidate" :model="formValidate" :label-width="120" label-position="right" @submit.native.prevent>
                <Row :gutter="24" type="flex" v-show="currentTab === '1'">
                    <!-- 商品信息-->
                    <Col span="24">
                        <FormItem label="商品名称：" prop="store_name">
                            <Input v-model="formValidate.store_name" placeholder="请输入商品名称" class="perW50"/>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="商品分类：" prop="cate_id">
                            <Select v-model="formValidate.cate_id" multiple class="perW50">
                                <Option v-for="item in treeSelect" :disabled="item.pid === 0" :value="item.id" :key="item.id">{{ item.html + item.cate_name }}</Option>
                            </Select>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="商品关键字：" prop="">
                            <Input v-model="formValidate.keyword" placeholder="请输入商品关键字" class="perW50"/>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="单位：" prop="unit_name">
                            <Input v-model="formValidate.unit_name"  placeholder="请输入单位" class="perW50"/>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="商品简介：" prop="">
                            <Input v-model="formValidate.store_info" type="textarea" :rows="3" placeholder="请输入商品简介" class="perW50"/>
                        </FormItem>
                    </Col>
                    <!--<Col v-bind="grid2">-->
                        <!--<FormItem label="邮费：">-->
                            <!--<InputNumber  v-model="formValidate.postage" placeholder="请输入邮费"  />-->
                        <!--</FormItem>-->
                    <!--</Col>-->
                    <Col span="24">
                        <FormItem label="商品封面图：" prop="image">
                            <div class="pictrueBox" @click="modalPicTap('dan','danFrom')">
                                <div class="pictrue" v-if="formValidate.image">
                                    <img v-lazy="formValidate.image">
                                    <Input v-model="formValidate.image" style="display: none"></Input>
                                </div>
                                <div class="upLoad acea-row row-center-wrapper" v-else>
                                    <Input v-model="formValidate.image" style="display: none"></Input>
                                    <Icon type="ios-camera-outline" size="26"/>
                                </div>
                            </div>
                        </FormItem>
                    </Col>
                  <Col span="24">
                    <FormItem label="商品推荐图：">
                      <div class="pictrueBox" @click="modalPicTap('dan','recommend_image')">
                        <div class="pictrue" v-if="formValidate.recommend_image">
                          <img v-lazy="formValidate.recommend_image">
                          <Input v-model="formValidate.recommend_image" style="display: none"></Input>
                        </div>
                        <div class="upLoad acea-row row-center-wrapper" v-else>
                          <Input v-model="formValidate.recommend_image" style="display: none"></Input>
                          <Icon type="ios-camera-outline" size="26"/>
                        </div>
                      </div>
                    </FormItem>
                  </Col>
                    <Col span="24">
                        <FormItem label="商品轮播图：" prop="slider_image">
                            <div class="acea-row">
                                <div class="pictrue" v-for="(item,index) in formValidate.slider_image" :key="index"
                                     draggable="true"
                                     @dragstart="handleDragStart($event, item)"
                                     @dragover.prevent="handleDragOver($event, item)"
                                     @dragenter="handleDragEnter($event, item)"
                                     @dragend="handleDragEnd($event, item)">
                                    <img v-lazy="item">
                                    <Button shape="circle" icon="md-close"  @click.native="handleRemove(index)" class="btndel"></Button>
                                </div>
                                <div v-if="formValidate.slider_image.length<10" class="upLoad acea-row row-center-wrapper" @click="modalPicTap('duo')">
                                    <Icon type="ios-camera-outline" size="26"/>
                                </div>
                                <Input v-model="formValidate.slider_image[0]" style="display: none"></Input>
                            </div>
                            <div class="tips">(最多10张)</div>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="主图视频：" prop="video_link">
                            <Input class="perW50" v-model="videoLink" placeholder="请输入视频链接"/>
                            <input type="file" ref='refid' @change="zh_uploadFile_change" style="display:none">
                            <Button type="primary" icon="ios-cloud-upload-outline" class="uploadVideo" @click="zh_uploadFile">{{videoLink ? '确认添加' : '上传视频'}}</Button>
                            <Progress :percent = progress :stroke-width="5" v-if="upload.videoIng" />
                            <div class="iview-video-style" v-if="formValidate.video_link">
                                <video style="width:100%;height: 100%!important;border-radius: 10px;" :src="formValidate.video_link" controls="controls">
                                    您的浏览器不支持 video 标签。
                                </video>
                                <div class="mark">
                                </div>
                                <Icon type="ios-trash-outline" class="iconv" @click="delVideo"/>
                            </div>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="商品规格：" props="spec_type">
                            <RadioGroup v-model="formValidate.spec_type">
                                <Radio :label="0" class="radio">单规格</Radio>
                                <Radio :label="1">多规格</Radio>
                            </RadioGroup>
                        </FormItem>
                    </Col>
                    <!-- 多规格添加-->
                    <Col span="24" v-if="formValidate.spec_type === 1" class="noForm">
                        <Col span="24">
                            <FormItem label="选择规格：" prop="">
                                <div  class="acea-row row-middle">
                                    <Select v-model="formValidate.selectRule" style="width: 23%;">
                                        <Option v-for="(item, index) in ruleList" :value="item.rule_name" :key="index">{{ item.rule_name }}</Option>
                                    </Select>
                                    <Button type="primary" class="mr20" @click="confirm">确认</Button>
                                    <Button @click="addRule">添加规格模板</Button>
                                </div>
                            </FormItem>
                        </Col>
                        <Col span="24">
                            <FormItem v-if="attrs.length!==0">
                                <draggable
                                        class="dragArea list-group"
                                        :list="attrs"
                                        group="peoples"
                                        handle=".move-icon"
                                        :move="checkMove"
                                        @end="end"
                                >
                                    <div v-for="(item, index) in attrs" :key="index" class="acea-row row-middle mb10">
                                        <div class="move-icon">
                                            <span class="iconfont icondrag2"></span>
                                        </div>
                                        <div :class="moveIndex===index?'borderStyle':''">
                                            <div class="acea-row row-middle"><span class="mr5">{{item.value}}</span><Icon type="ios-close-circle" size="14" class="curs" @click="handleRemoveRole(index)"/></div>
                                            <div class="rulesBox">
                                                <draggable
                                                        :list="item.detail"
                                                        handle=".drag"
                                                >
                                                    <Tag type="dot" closable color="primary" v-for="(j, indexn) in item.detail" :key="indexn" :name="j" class="mr20 drag" @on-close="handleRemove2(item.detail,indexn)">{{j}}</Tag>
                                                </draggable>
                                                <Input search enter-button="添加" placeholder="请输入属性名称" v-model="item.detail.attrsVal" @on-search="createAttr(item.detail.attrsVal,index)" style="width: 150px"/>
                                            </div>
                                        </div>
                                    </div>
                                </draggable>
                            </FormItem>
                        </Col>
                        <Col span="24" v-if="createBnt">
                            <FormItem>
                                <Button type="primary" icon="md-add" @click="addBtn" class="mr15">添加新规格</Button>
                                <Button type="success" @click="generate(1)">立即生成</Button>
                            </FormItem>
                        </Col>
                        <Col span="24" v-if="showIput">
                            <Col  :xl="6" :lg="9" :md="10" :sm="24" :xs="24" >
                                <FormItem label="规格：">
                                    <Input  placeholder="请输入规格" v-model="formDynamic.attrsName"  />
                                </FormItem>
                            </Col>
                            <Col  :xl="6" :lg="9" :md="10" :sm="24" :xs="24">
                                <FormItem label="规格值：">
                                    <Input v-model="formDynamic.attrsVal" placeholder="请输入规格值"  />
                                </FormItem>
                            </Col>
                            <Col :xl="6" :lg="5" :md="10" :sm="24" :xs="24" >
                                <FormItem>
                                    <Button type="primary" class="mr15"   @click="createAttrName">确定</Button>
                                    <Button  @click="offAttrName" >取消</Button>
                                </FormItem>
                            </Col>
                        </Col>
                        <!-- 多规格设置-->
                        <Col :xl="24" :lg="24" :md="24" :sm="24" :xs="24" v-if="manyFormValidate.length && formValidate.header.length!==0 && attrs.length!==0">
                            <!-- 批量设置-->
                            <Col span="24">
                                <FormItem label="批量设置：" class="labeltop">
                                    <Table :data="oneFormBatch" :columns="columns2" border>
                                        <template slot-scope="{ row, index }" slot="pic">
                                            <div class="acea-row row-middle row-center-wrapper" @click="modalPicTap('dan','duopi',index)">
                                                <div class="pictrue pictrueTab" v-if="oneFormBatch[0].pic"><img v-lazy="oneFormBatch[0].pic"></div>
                                                <div class="upLoad pictrueTab acea-row row-center-wrapper"   v-else>
                                                    <Icon type="ios-camera-outline" size="21"/>
                                                </div>
                                            </div>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="price">
                                            <InputNumber  v-model="oneFormBatch[0].price" :min="0"  class="priceBox"></InputNumber>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="cost">
                                            <InputNumber  v-model="oneFormBatch[0].cost" :min="0"  class="priceBox"></InputNumber>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="ot_price">
                                            <InputNumber  v-model="oneFormBatch[0].ot_price" :min="0"  class="priceBox"></InputNumber>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="stock">
                                            <InputNumber  v-model="oneFormBatch[0].stock" :min="0" class="priceBox"></InputNumber>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="bar_code">
                                            <Input  v-model="oneFormBatch[0].bar_code"></Input>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="weight">
                                            <InputNumber  v-model="oneFormBatch[0].weight" :step="0.1" :min="0" class="priceBox"></InputNumber>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="volume">
                                            <InputNumber  v-model="oneFormBatch[0].volume" :step="0.1" :min="0"  class="priceBox"></InputNumber>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="action">
                                            <a @click="batchAdd">批量添加</a>
                                            <Divider type="vertical"/>
                                            <a @click="batchDel">清空</a>
                                        </template>
                                    </Table>
                                </FormItem>
                            </Col>
                            <!-- 多规格表格-->
                            <Col span="24">
                                <FormItem label="商品属性：" class="labeltop">
                                    <Table :data="manyFormValidate" :columns="formValidate.header" border>
                                        <template slot-scope="{ row, index }" slot="pic">
                                            <div class="acea-row row-middle row-center-wrapper" @click="modalPicTap('dan','duoTable',index)">
                                                <div class="pictrue pictrueTab" v-if="manyFormValidate[index].pic"><img v-lazy="manyFormValidate[index].pic"></div>
                                                <div class="upLoad pictrueTab acea-row row-center-wrapper"   v-else>
                                                    <Icon type="ios-camera-outline" size="21"/>
                                                </div>
                                            </div>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="price">
                                            <InputNumber  v-model="manyFormValidate[index].price" :min="0"  class="priceBox"></InputNumber>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="cost">
                                            <InputNumber  v-model="manyFormValidate[index].cost" :min="0"  class="priceBox"></InputNumber>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="ot_price">
                                            <InputNumber  v-model="manyFormValidate[index].ot_price" :min="0"  class="priceBox"></InputNumber>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="stock">
                                            <InputNumber  v-model="manyFormValidate[index].stock" :min="0" :precision="0" class="priceBox"></InputNumber>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="bar_code">
                                            <Input  v-model="manyFormValidate[index].bar_code"></Input>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="weight">
                                            <InputNumber  v-model="manyFormValidate[index].weight" :min="0" class="priceBox"></InputNumber>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="volume">
                                            <InputNumber  v-model="manyFormValidate[index].volume" :min="0" class="priceBox"></InputNumber>
                                        </template>
                                        <template slot-scope="{ row, index }" slot="action">
                                            <a @click="delAttrTable(index)">删除</a>
                                        </template>
                                    </Table>
                                </FormItem>
                            </Col>
                        </Col>
                    </Col>
                    <!-- 单规格表格-->
                    <Col :xl="23" :lg="24" :md="24" :sm="24" :xs="24" v-if="formValidate.spec_type === 0">
                        <FormItem >
                            <Table :data="oneFormValidate" :columns="columns" border>
                                <template slot-scope="{ row, index }" slot="pic">
                                    <div class="acea-row row-middle row-center-wrapper" @click="modalPicTap('dan','danTable',index)">
                                        <div class="pictrue pictrueTab" v-if="oneFormValidate[0].pic"><img v-lazy="oneFormValidate[0].pic"></div>
                                        <div class="upLoad pictrueTab acea-row row-center-wrapper"   v-else>
                                            <Icon type="ios-camera-outline" size="21"/>
                                        </div>
                                    </div>
                                </template>
                                <template slot-scope="{ row, index }" slot="price">
                                    <InputNumber  v-model="oneFormValidate[0].price" :min="0"  class="priceBox"></InputNumber>
                                </template>
                                <template slot-scope="{ row, index }" slot="cost">
                                    <InputNumber  v-model="oneFormValidate[0].cost" :min="0"  class="priceBox"></InputNumber>
                                </template>
                                <template slot-scope="{ row, index }" slot="ot_price">
                                    <InputNumber  v-model="oneFormValidate[0].ot_price" :min="0"  class="priceBox"></InputNumber>
                                </template>
                                <template slot-scope="{ row, index }" slot="stock">
                                    <InputNumber  v-model="oneFormValidate[0].stock" :min="0" :precision="0" class="priceBox"></InputNumber>
                                </template>
                                <template slot-scope="{ row, index }" slot="bar_code">
                                    <Input  v-model="oneFormValidate[0].bar_code"></Input>
                                </template>
                                <template slot-scope="{ row, index }" slot="weight">
                                    <InputNumber  v-model="oneFormValidate[0].weight" :min="0" class="priceBox"></InputNumber>
                                </template>
                                <template slot-scope="{ row, index }" slot="volume">
                                    <InputNumber  v-model="oneFormValidate[0].volume" :min="0" class="priceBox"></InputNumber>
                                </template>
                            </Table>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="运费模板：" prop="temp_id">
                            <div class="acea-row">
                                <Select v-model="formValidate.temp_id" clearable class="mr20 perW50 maxW">
                                    <Option v-for="(item,index) in templateList" :value="item.id" :key="index">{{ item.name }}</Option>
                                </Select>
                                <Button @click="addTemp">添加运费模板</Button>
                            </div>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="关联用户标签：" prop="label_id">
                            <Select v-model="formValidate.label_id" multiple class="perW50">
                                <Option v-for="item in labelSelect" :value="item.id" :key="item.id">{{ item.label_name }}</Option>
                            </Select>
                        </FormItem>
                    </Col>
                </Row>
                <!-- 商品详情-->
                <Row v-show="currentTab === '2'">
                    <Col span="24">
                        <FormItem label="商品详情：">
                            <vue-ueditor-wrap v-model="formValidate.description"  @beforeInit="addCustomDialog"  :config="myConfig"  style="width: 90%;"></vue-ueditor-wrap>
                        </FormItem>
                    </Col>
                </Row>
                <!-- 其他设置-->
                <Row type="flex" justify="space-between" v-show="currentTab === '3'">
                    <Col v-bind="grid">
                        <FormItem label="虚拟销量：">
                            <InputNumber class="perW50" :min="0" v-model="formValidate.ficti" placeholder="请输入虚拟销量"  />
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="积分：" prop="give_integral">
                            <InputNumber class="perW50" v-model="formValidate.give_integral" :min="0" placeholder="请输入积分" />
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="排序：">
                            <InputNumber :min="0" class="perW50" v-model="formValidate.sort" placeholder="请输入排序" />
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="商品状态：">
                            <RadioGroup v-model="formValidate.is_show" >
                                <Radio :label="1" class="radio">上架</Radio>
                                <Radio :label="0">下架</Radio>
                            </RadioGroup>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="热卖单品：">
                            <RadioGroup v-model="formValidate.is_hot" >
                                <Radio :label="1" class="radio">开启</Radio>
                                <Radio :label="0">关闭</Radio>
                            </RadioGroup>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="促销单品：">
                            <RadioGroup v-model="formValidate.is_benefit" >
                                <Radio :label="1" class="radio">开启</Radio>
                                <Radio :label="0">关闭</Radio>
                            </RadioGroup>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="精品推荐：">
                            <RadioGroup v-model="formValidate.is_best" >
                                <Radio :label="1" class="radio">开启</Radio>
                                <Radio :label="0">关闭</Radio>
                            </RadioGroup>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="首发新品：">
                            <RadioGroup v-model="formValidate.is_new" >
                                <Radio :label="1" class="radio">开启</Radio>
                                <Radio :label="0">关闭</Radio>
                            </RadioGroup>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="优品推荐：">
                            <RadioGroup v-model="formValidate.is_good">
                                <Radio :label="1" class="radio">开启</Radio>
                                <Radio :label="0">关闭</Radio>
                            </RadioGroup>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid3">
                        <FormItem label="赠送优惠券：">
                            <div v-if="couponName.length" class="mb20">
                                <Tag closable v-for="(item, index) in couponName" :key="index" @on-close="handleClose(item)">{{item.title}}</Tag>
                            </div>
                            <Button type="primary" @click="addCoupon">添加优惠券</Button>
                        </FormItem>
                    </Col>
                </Row>
                <FormItem>
                    <Button v-if="currentTab !== '1'" @click="upTab">上一步</Button>
                    <Button type="primary" class="submission" v-if="currentTab !== '3'" @click="downTab">下一步</Button>
                    <Button type="primary" :disabled="openSubimit" class="submission" @click="handleSubmit('formValidate')" v-if="$route.params.id || currentTab === '3'">保存</Button>
                </FormItem>
                <Spin size="large" fix v-if="spinShow"></Spin>
            </Form>
            <Modal v-model="modalPic" width="60%" scrollable  footer-hide closable title='上传商品图' :mask-closable="false" :z-index="1">
                <uploadPictures :isChoice="isChoice" @getPic="getPic" @getPicD="getPicD" :gridBtn="gridBtn" :gridPic="gridPic" v-if="modalPic"></uploadPictures>
            </Modal>
        </Card>
        <freightTemplate :template="template" v-on:changeTemplate="changeTemplate" ref="templates"></freightTemplate>
        <add-attr ref="addattr" @getList="userSearchs"></add-attr>
        <coupon-list ref="couponTemplates" @nameId="nameId" :couponids="formValidate.coupon_ids" :updateIds="updateIds" :updateName="updateName"></coupon-list>
    </div>
</template>

<script>
// import COS from 'cos-js-sdk-v5'
    import { mapState } from 'vuex'
    import vuedraggable from 'vuedraggable'
    import uploadPictures from '@/components/uploadPictures'
    import freightTemplate from '@/components/freightTemplate'
    import couponList from '@/components/couponList'
    import addAttr from '../productAttr/addAttr'
    import VueUeditorWrap from 'vue-ueditor-wrap'
    import { productInfoApi, treeListApi, productAddApi, generateAttrApi, productGetRuleApi, productGetTemplateApi, productGetTempKeysApi, labelListApi } from '@/api/product'
    export default {
        name: 'product_productAdd',
        components: { VueUeditorWrap, uploadPictures, freightTemplate, addAttr, couponList, draggable: vuedraggable },
        data () {
            return {
                spinShow: false,
                openSubimit:false,
                grid2: {
                    xl: 10,
                    lg: 12,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                grid3: {
                    xl: 18,
                    lg: 18,
                    md: 20,
                    sm: 24,
                    xs: 24
                },
                // 批量设置表格data
                oneFormBatch: [
                    {
                        pic: '',
                        price: 0,
                        cost: 0,
                        ot_price: 0,
                        stock: 0,
                        bar_code: '',
                        weight: 0,
                        volume: 0
                    }
                ],
                // 规格数据
                formDynamic: {
                    attrsName: '',
                    attrsVal: ''
                },
                formDynamicNameData: [],
                isBtn: false,
                myConfig: {
                    autoHeightEnabled: false, // 编辑器不自动被内容撑高
                    initialFrameHeight: 500, // 初始容器高度
                    initialFrameWidth: '100%', // 初始容器宽度
                    UEDITOR_HOME_URL: '/admin/UEditor/',
                    serverUrl: ''
                },
                columns2: [
                    {
                        title: '图片',
                        slot: 'pic',
                        align: 'center',
                        minWidth: 80
                    },
                    {
                        title: '售价',
                        slot: 'price',
                        align: 'center',
                        minWidth: 95
                    },
                    {
                        title: '成本价',
                        slot: 'cost',
                        align: 'center',
                        minWidth: 95
                    },
                    {
                        title: '原价',
                        slot: 'ot_price',
                        align: 'center',
                        minWidth: 95
                    },
                    {
                        title: '库存',
                        slot: 'stock',
                        align: 'center',
                        minWidth: 95
                    },
                    {
                        title: '商品编号',
                        slot: 'bar_code',
                        align: 'center',
                        minWidth: 120
                    },
                    {
                        title: '重量（KG）',
                        slot: 'weight',
                        align: 'center',
                        minWidth: 95
                    },
                    {
                        title: '体积(m³)',
                        slot: 'volume',
                        align: 'center',
                        minWidth: 95
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        align: 'center',
                        minWidth: 140
                    }
                ],
                columns: [],
                columnsInstall: [],
                columnsInstal2: [],
                gridPic: {
                    xl: 6,
                    lg: 8,
                    md: 12,
                    sm: 12,
                    xs: 12
                },
                gridBtn: {
                    xl: 4,
                    lg: 8,
                    md: 8,
                    sm: 8,
                    xs: 8
                },
                formValidate: {
                    store_name: '',
                    cate_id: [],
                    label_id: [],
                    keyword: '',
                    unit_name: '',
                    store_info: '',
                    image: '',
                    recommend_image: '',
                    slider_image: [],
                    description: '',
                    ficti: 0,
                    give_integral: 0,
                    sort: 0,
                    is_show: 1,
                    is_hot: 0,
                    is_benefit: 0,
                    is_best: 0,
                    is_new: 0,
                    is_good: 0,
                    is_postage: 0,
                    is_sub: [],
                    id: 0,
                    spec_type: 0,
                    video_link: '',
                    // postage: 0,
                    temp_id: '',
                    attrs: [],
                    items: [
                        {
                            pic: '',
                            price: 0,
                            cost: 0,
                            ot_price: 0,
                            stock: 0,
                            bar_code: ''
                        }
                    ],
                    couponName: [],
                    header: [],
                    selectRule: '',
                    coupon_ids: [],
                },
                ruleList: [],
                templateList: [],
                createBnt: false,
                showIput: false,
                manyFormValidate: [],
                // 单规格表格data
                oneFormValidate: [
                    {
                        pic: '',
                        price: 0,
                        cost: 0,
                        ot_price: 0,
                        stock: 0,
                        bar_code: '',
                        weight: 0,
                        volume: 0,
                    }
                ],
                images: [],
                imagesTable: '',
                currentTab: '1',
                isChoice: '',
                grid: {
                    xl: 8,
                    lg: 8,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                loading: false,
                modalPic: false,
                template: false,
                uploadList: [],
                treeSelect: [],
                labelSelect: [],
                picTit: '',
                tableIndex: 0,
                ruleValidate: {
                    store_name: [
                        { required: true, message: '请输入商品名称', trigger: 'blur' }
                    ],
                    cate_id: [
                        { required: true, message: '请选择商品分类', trigger: 'change', type: 'array', min: '1' }
                    ],
                    keyword: [
                        { required: true, message: '请输入商品关键字', trigger: 'blur' }
                    ],
                    unit_name: [
                        { required: true, message: '请输入单位', trigger: 'blur' }
                    ],
                    store_info: [
                        { required: true, message: '请输入商品简介', trigger: 'blur' }
                    ],
                    image: [
                        { required: true, message: '请上传商品图', trigger: 'change' }
                    ],
                    slider_image: [
                        { required: true, message: '请上传商品轮播图', type: 'array', trigger: 'change' }
                    ],
                    spec_type: [
                        { required: true, message: '请选择商品规格', trigger: 'change' }
                    ],
                    selectRule: [
                        { required: true, message: '请选择商品规格属性', trigger: 'change' }
                    ],
                    temp_id: [
                        { required: true, message: '请选择运费模板', trigger: 'change', type: 'number' }
                    ],
                    give_integral: [
                        { type: "integer", message: "请输入整数" }
                    ]
                },
                manyBrokerage: 0,
                manyBrokerageTwo: 0,
                manyVipPrice: 0,
                upload: {
                    videoIng: false // 是否显示进度条；
                },
                progress: 0, // 进度条默认0
                videoLink: '',
                attrs: [],
                couponName: [],
                updateIds:[],
                updateName:[],
                columnsInstalM:[],
                moveIndex:''
            }
        },
        computed: {
            labelBottom () {
                return this.isMobile ? undefined : 15
            }
        },
        created () {
            this.columns = this.columns2.slice(0, 8);
        },
        mounted () {
            if (this.$route.params.id !== '0' && this.$route.params.id) {
                this.getInfo()
            }
            this.goodsCategory()
            this.productGetRule()
            this.productGetTemplate()
            this.userLabel()
        },
        methods: {
            checkMove(evt){
                this.moveIndex = evt.draggedContext.index;
            },
            end(){
                this.moveIndex = '';
            },
            // 添加优惠券
            addCoupon () {
                this.$refs.couponTemplates.isTemplate = true
                this.$refs.couponTemplates.tableList()
            },
            //对象数组去重；
            unique(arr) {
                const res = new Map();
                return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1))
            },
            nameId (id,names) {
                this.formValidate.coupon_ids = id;
                this.couponName = this.unique(names);
            },
            handleClose (name) {
                let index = this.couponName.indexOf(name);
                this.couponName.splice(index, 1);
                let couponIds = this.formValidate.coupon_ids;
                couponIds.splice(index, 1);
                this.updateIds = couponIds;
                this.updateName = this.couponName;
            },
            // 运费模板
            getList () {
                this.productGetTemplate()
            },
            // 添加运费模板
            addTemp () {
                this.$refs.templates.isTemplate = true
            },
            // 删除视频；
            delVideo () {
                let that = this
                that.$set(that.formValidate, 'video_link', '')
            },
            zh_uploadFile () {
                if (this.videoLink) {
                    this.formValidate.video_link = this.videoLink
                } else {
                    this.$refs.refid.click()
                }
            },
            zh_uploadFile_change (evfile) {
                let that = this
                let suffix = evfile.target.files[0].name.substr(evfile.target.files[0].name.indexOf('.'))
                if (suffix !== '.mp4') {
                    return that.$Message.error('只能上传MP4文件')
                }
                productGetTempKeysApi().then(res => {
                    that.$videoCloud.videoUpload({
                        type: res.data.type,
                        evfile: evfile,
                        res: res,
                        uploading (status, progress) {
                            that.upload.videoIng = status
                            console.log(status, progress)
                        }
                    }).then(res => {
                        that.formValidate.video_link = res.url
                        that.$Message.success('视频上传成功')
                    }).catch(res => {
                        that.$Message.error(res)
                    })
                }).catch(res => {
                    that.$Message.error(res.msg);
                });
            },
            // 上一页；
            upTab () {
                this.currentTab = (Number(this.currentTab) - 1).toString()
            },
            // 下一页；
            downTab () {
                this.currentTab = (Number(this.currentTab) + 1).toString()
            },
            // 属性弹窗回调函数；
            userSearchs () {
                this.productGetRule()
            },
            // 添加规则；
            addRule () {
                this.$refs.addattr.modal = true
            },
            batchDel () {
                this.oneFormBatch = [
                    {
                        pic: '',
                        price: 0,
                        cost: 0,
                        ot_price: 0,
                        stock: 0,
                        bar_code: '',
                        weight: 0,
                        volume: 0
                    }
                ]
            },
            confirm () {
                let that = this
                that.createBnt = true
                if (that.formValidate.selectRule.trim().length <= 0) {
                    return that.$Message.error('请选择属性')
                }
                that.ruleList.forEach(function (item, index) {
                    if (item.rule_name === that.formValidate.selectRule) {
                        that.attrs = item.rule_value
                    }
                })
            },
            // 获取商品属性模板；
            productGetRule () {
                productGetRuleApi().then(res => {
                    this.ruleList = res.data
                })
            },
            // 获取运费模板；
            productGetTemplate () {
                productGetTemplateApi().then(res => {
                    this.templateList = res.data
                })
            },
            // 批量添加
            batchAdd () {
                // if (!this.oneFormBatch[0].pic || !this.oneFormBatch[0].price || !this.oneFormBatch[0].cost || !this.oneFormBatch[0].ot_price ||
                //     !this.oneFormBatch[0].stock || !this.oneFormBatch[0].bar_code) return this.$Message.warning('请填写完整的批量设置内容！');
                // if (!this.oneFormBatch[0].pic) {
                //     return this.$Message.warning('请选择有效图片');
                // }
                for (let val of this.manyFormValidate) {
                    if (this.oneFormBatch[0].pic) {
                        this.$set(val, 'pic', this.oneFormBatch[0].pic)
                    }
                    if (this.oneFormBatch[0].price > 0) {
                        this.$set(val, 'price', this.oneFormBatch[0].price)
                    }
                    if (this.oneFormBatch[0].cost > 0) {
                        this.$set(val, 'cost', this.oneFormBatch[0].cost)
                    }
                    if (this.oneFormBatch[0].ot_price > 0) {
                        this.$set(val, 'ot_price', this.oneFormBatch[0].ot_price)
                    }
                    if (this.oneFormBatch[0].stock > 0) {
                        this.$set(val, 'stock', this.oneFormBatch[0].stock)
                    }
                    if (this.oneFormBatch[0].bar_code !== '') {
                        this.$set(val, 'bar_code', this.oneFormBatch[0].bar_code)
                    }
                    if (this.oneFormBatch[0].weight > 0) {
                        this.$set(val, 'weight', this.oneFormBatch[0].weight)
                    }
                    if (this.oneFormBatch[0].volume > 0) {
                        this.$set(val, 'volume', this.oneFormBatch[0].volume)
                    }
                    // this.$set(val, 'price', this.oneFormBatch[0].price);
                    // this.$set(val, 'cost', this.oneFormBatch[0].cost);
                    // this.$set(val, 'ot_price', this.oneFormBatch[0].ot_price);
                    // this.$set(val, 'stock', this.oneFormBatch[0].stock);
                    // this.$set(val, 'bar_code', this.oneFormBatch[0].bar_code);
                    // this.$set(val, 'weight', this.oneFormBatch[0].weight);
                    // this.$set(val, 'volume', this.oneFormBatch[0].volume);
                }
            },
            // 添加按钮
            addBtn () {
                this.clearAttr()
                this.createBnt = false
                this.showIput = true
            },
            // 立即生成
            generate (type) {
                generateAttrApi({ attrs: this.attrs }, this.formValidate.id, type).then(res => {
                    let info = res.data.info, header1 = JSON.parse(JSON.stringify(info.header));
                    this.manyFormValidate = info.value;
                    this.formValidate.header = header1;
                    let header = info.header;
                    // let brokerage = [
                    //     {
                    //         title: '一级返佣',
                    //         slot: 'brokerage',
                    //         align: 'center',
                    //         width: 95
                    //     },
                    //     {
                    //         title: '二级返佣',
                    //         slot: 'brokerage_two',
                    //         align: 'center',
                    //         width: 95
                    //     }
                    // ];
                    header.pop();
                    // this.columnsInstal2 = info.header.concat(brokerage);
                    this.columnsInstalM = info.header;
                    this.checkAllGroup(this.formValidate.is_sub);
                    if (!this.$route.params.id && this.formValidate.spec_type === 1) {
                        this.manyFormValidate.map((item) => {
                            item.pic = this.formValidate.image
                        });
                        this.oneFormBatch[0].pic = this.formValidate.image;
                    }
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 取消
            offAttrName () {
                this.showIput = false
                this.createBnt = true
            },
            clearAttr () {
                this.formDynamic.attrsName = ''
                this.formDynamic.attrsVal = ''
            },
            // 删除规格
            handleRemoveRole (index) {
                this.attrs.splice(index, 1)
                this.manyFormValidate.splice(index, 1)
            },
            // 删除属性
            handleRemove2 (item, index) {
                item.splice(index, 1)
            },
            // 添加规则名称
            createAttrName () {
                if (this.formDynamic.attrsName && this.formDynamic.attrsVal) {
                    let data = {
                        value: this.formDynamic.attrsName,
                        detail: [
                            this.formDynamic.attrsVal
                        ]
                    }
                    this.attrs.push(data)
                    var hash = {}
                    this.attrs = this.attrs.reduce(function (item, next) {
                        /* eslint-disable */
                        hash[next.value] ? '' : hash[next.value] = true && item.push(next);
                        return item
                    }, [])
                    this.clearAttr();
                    this.showIput = false;
                    this.createBnt = true;
                } else {
                    this.$Message.warning('请添加完整的规格！');
                }
            },
            // 添加属性
            createAttr (num, idx) {
                if (num) {
                    this.attrs[idx].detail.push(num);
                    var hash = {};
                    this.attrs[idx].detail = this.attrs[idx].detail.reduce(function (item, next) {
                        /* eslint-disable */
                        hash[next] ? '' : hash[next] = true && item.push(next);
                        return item
                    }, [])
                } else {
                    this.$Message.warning('请添加属性');
                }
            },
            // 商品分类；
            goodsCategory () {
                treeListApi(1).then(res => {
                    this.treeSelect = res.data;
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 用户标签
            userLabel () {
                labelListApi().then(res => {
                    this.labelSelect = res.data.list;
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 详情
            getInfo () {
                let that = this;
                that.spinShow = true;
                productInfoApi(that.$route.params.id).then(async res => {
                    let data = res.data.productInfo;
                    let cate_id = data.cate_id.map(Number);
                    let label_id = data.label_id.map(Number);
                    this.attrs = data.items || [];
                    let ids = [];
                    // let names = [];
                    data.coupons.map((item) => {
                        ids.push(item.id);
                        // names.push(item.title);
                    });
                    that.formValidate = data;
                    that.couponName = data.coupons;
                    that.formValidate.coupon_ids = ids;
                    that.updateIds = ids;
                    that.updateName = data.coupons;
                    that.formValidate.cate_id = cate_id;
                    that.formValidate.label_id = label_id;
                    that.oneFormValidate = [data.attr];
                    that.formValidate.header = [];
                    that.generate(0);
                    that.manyFormValidate = data.attrs;
                    that.spec_type = data.spec_type;
                    if(data.spec_type === 0){
                        that.manyFormValidate = [];
                    }else {
                        that.createBnt = true;
                        that.oneFormValidate = [
                            {
                                pic: '',
                                price: 0,
                                cost: 0,
                                ot_price: 0,
                                stock: 0,
                                bar_code: '',
                                weight:0,
                                volume:0,
                                brokerage:0,
                                brokerage_two:0,
                                vip_price:0
                            }
                        ]
                    }
                    this.spinShow = false;
                }).catch(res => {
                    this.spinShow = false;
                    this.$Message.error(res.msg);
                })
            },
            // tab切换
            onhangeTab (name) {
                this.currentTab = name;
            },
            handleRemove (i) {
                this.images.splice(i, 1);
                this.formValidate.slider_image.splice(i, 1);
            },
            // 关闭图片上传模态框
            changeCancel (msg) {
                this.modalPic = false
            },
            // 点击商品图
            modalPicTap (tit,picTit,index) {
                this.modalPic = true;
                this.isChoice = tit === 'dan' ? '单选' : '多选';
                this.picTit = picTit;
                this.tableIndex = index;
            },
            // 获取单张图片信息
            getPic (pc) {
                switch (this.picTit) {
                    case 'danFrom':
                        this.formValidate.image = pc.att_dir;
                        if(!this.$route.params.id){
                            if(this.formValidate.spec_type === 0){
                                this.oneFormValidate[0].pic =  pc.att_dir;
                            }else{
                                this.manyFormValidate.map((item) => {
                                    item.pic = pc.att_dir;
                                });
                                this.oneFormBatch[0].pic =  pc.att_dir;
                            }
                        }
                        break;
                    case 'danTable':
                        this.oneFormValidate[this.tableIndex].pic = pc.att_dir;
                        break;
                    case 'duopi':
                        this.oneFormBatch[this.tableIndex].pic = pc.att_dir;
                        break;
                  case 'recommend_image':
                        this.formValidate.recommend_image=pc.att_dir;
                        break;
                  default:
                        this.manyFormValidate[this.tableIndex].pic=pc.att_dir;
                }
                this.modalPic = false;
            },
            // 获取多张图信息
            getPicD (pc) {
                this.images = pc;
                this.images.map((item) => {
                    this.formValidate.slider_image.push(item.att_dir);
                    this.formValidate.slider_image = this.formValidate.slider_image.splice(0,10);
                });
                this.modalPic = false;
            },
            // 提交
            handleSubmit (name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        if(this.formValidate.spec_type ===0 ){
                            this.formValidate.attrs = this.oneFormValidate;
                            this.formValidate.header = [];
                            this.formValidate.items = [];
                        }else{
                            this.formValidate.items = this.attrs;
                            this.formValidate.attrs = this.manyFormValidate;
                        }
                        if(this.formValidate.spec_type === 1 && this.manyFormValidate.length===0){
                            return this.$Message.warning('商品信息-请点击生成多规格');
                        }
                        let item = this.formValidate.attrs;
                        this.openSubimit = true;
                        productAddApi(this.formValidate).then(async res => {
                            this.openSubimit = false;
                            this.$Message.success(res.msg);
                            setTimeout(() => {
                                this.$router.push({ path: '/admin/product/product_list' });
                            }, 500);
                        }).catch(res => {
                            this.openSubimit = false;
                            this.$Message.error(res.msg);
                        })
                    } else {
                        if(!this.formValidate.store_name){
                            return this.$Message.warning("商品信息-商品名称不能为空");
                        }else if(!this.formValidate.cate_id.length){
                            return this.$Message.warning("商品信息-商品分类不能为");
                        }else if(!this.formValidate.keyword){
                            return this.$Message.warning("商品信息-商品关键字不能为");
                        }else if(!this.formValidate.unit_name){
                            return this.$Message.warning("商品信息-商品单位不能为");
                        }else if(!this.formValidate.store_info){
                            return this.$Message.warning("商品信息-商品简介不能为");
                        }else if(!this.formValidate.image){
                            return this.$Message.warning("商品信息-商品封面图不能为");
                        }else if(!this.formValidate.slider_image.length){
                            return this.$Message.warning("商品信息-商品轮播图不能为");
                        }else if(!this.formValidate.temp_id){
                            return this.$Message.warning("商品信息-运费模板不能为");
                        }
                    }
                })
            },
            changeTemplate (msg) {
                this.template = msg
            },
            // 表单验证
            validate (prop, status, error) {
                if (status === false) {
                    this.$Message.warning(error);
                }
            },
            // 移动
            handleDragStart (e, item) {
                this.dragging = item;
            },
            handleDragEnd (e, item) {
                this.dragging = null
            },
            handleDragOver (e) {
                e.dataTransfer.dropEffect = 'move'
            },
            handleDragEnter (e, item) {
                e.dataTransfer.effectAllowed = 'move'
                if (item === this.dragging) {
                    return
                }
                const newItems = [...this.formValidate.slider_image]
                const src = newItems.indexOf(this.dragging)
                const dst = newItems.indexOf(item)
                newItems.splice(dst, 0, ...newItems.splice(src, 1))
                this.formValidate.slider_image = newItems;
            },
            // 添加自定义弹窗
            addCustomDialog (editorId) {
                window.UE.registerUI('test-dialog', function (editor, uiName) {
                    // 创建 dialog
                    let dialog = new window.UE.ui.Dialog({
                        iframeUrl: '/admin/widget.images/index.html?fodder=dialog',
                        editor: editor,
                        name: uiName,
                        title: '上传图片',
                        cssRules: 'width:1200px;height:500px;padding:20px;'
                    });
                    this.dialog = dialog;
                    let btn = new window.UE.ui.Button({
                        name: 'dialog-button',
                        title: '上传图片',
                        cssRules: `background-image: url(../../../assets/images/icons.png);background-position: -726px -77px;`,
                        onclick: function () {
                            // 渲染dialog
                            dialog.render();
                            dialog.open();
                        }
                    });
                    return btn;
                }, 37);
                window.UE.registerUI('video-dialog', function (editor, uiName) {
                    let dialog = new window.UE.ui.Dialog({
                        iframeUrl: '/admin/widget.video/index.html?fodder=video',
                        editor: editor,
                        name: uiName,
                        title: '上传视频',
                        cssRules: 'width:1000px;height:500px;padding:20px;'
                    });
                    this.dialog = dialog;
                    let btn = new window.UE.ui.Button({
                        name: 'video-button',
                        title: '上传视频',
                        cssRules: `background-image: url(../../../assets/images/icons.png);background-position: -320px -20px;`,
                        onclick: function () {
                            // 渲染dialog
                            dialog.render();
                            dialog.open();
                        }
                    });
                    return btn;
                }, 38);
            }
        }
    }
</script>
<style scoped lang="stylus">
    .list-group{
        margin-left -8px
    }
    .borderStyle{
        border: 1px solid #ccc;
        padding: 8px;
        border-radius: 4px;
    }
    .drag
        cursor move
    .move-icon{
        width 30px
        cursor move
        margin-right 10px
    }
    .move-icon .icondrag2{
        font-size: 26px;
        color: #d8d8d8;
    }

    .maxW /deep/.ivu-select-dropdown{
        max-width 600px;
    }
    #shopp-manager .ivu-table-wrapper
      border-left: 1px solid #dcdee2;
      border-top: 1px solid #dcdee2;
    .noLeft
        >>> .ivu-form-item-content
         margin-left 0 !important
    #shopp-manager .ivu-form-item{
        position: relative;
    }
    #shopp-manager .ivu-form-item .tips{
        position: absolute;
        color: #999;
        top: 29px;
        left: -77px;
        font-size: 12px;
    }
    .iview-video-style{
        width: 40%;
        height: 180px;
        border-radius: 10px;
        background-color: #707070;
        margin-top: 10px;
        position: relative;
        overflow: hidden;
    }
    .iview-video-style .iconv{
        color: #fff;
        line-height: 180px;
        width: 50px;
        height: 50px;
        display: inherit;
        font-size: 26px;
        position: absolute;
        top: -74px;
        left: 50%;
        margin-left: -25px;
    }
    .iview-video-style .mark{
        position: absolute;
        width: 100%;
        height: 30px;
        top: 0;
        background-color: rgba(0,0,0,.5);
        text-align: center;
    }
        .uploadVideo
            margin-left 10px;
        .submission
            margin-left 10px;
        .color-list .tip{
            color: #c9c9c9;
        }
        .color-list .color-item{
            height: 30px;
            line-height: 30px;
            padding: 0 10px;
            color:#fff;
            margin-right :10px;
        }
        .color-list .color-item.blue{
            background-color: #1E9FFF;
        }
        .color-list .color-item.yellow{
            background-color: rgb(254, 185, 0);
        }
        .color-list .color-item.green{
            background-color: #009688;
        }
        .color-list .color-item.red{
            background-color: #ed4014;
        }
        .columnsBox
            margin-right 10px
        .priceBox
            width 100%
        .rulesBox
            display flex
            flex-wrap: wrap;
        .pictrueBox
            display inline-block;
        .pictrueTab
            width:40px !important;
            height:40px !important;
        .pictrue
            width:60px;
            height:60px;
            border:1px dotted rgba(0,0,0,0.1);
            margin-right:15px;
            display: inline-block;
            position: relative;
            cursor: pointer;
            img
                width 100%
                height 100%
            .btndel
                position: absolute;
                z-index: 1;
                width :20px !important;
                height: 20px !important;
                left: 46px;
                top: -4px;
        .upLoad {
            width: 58px;
            height: 58px;
            line-height: 58px;
            border: 1px dotted rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.02);
            cursor: pointer;
        }
        .curs
            cursor pointer
        .inpWith
            width 60%;
        .labeltop
            >>> .ivu-form-item-label
                float: none !important;
                display: inline-block !important;
                margin-left: 120px !important;
                width auto !important

</style>
