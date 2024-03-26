<template>
  <div class="" id="shopp-manager" v-loading="spinShow">
    <pages-header
      ref="pageHeader"
      :title="$route.params.id ? '编辑商品' : '添加商品'"
      :backUrl="$routeProStr + '/product/product_list'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16" :body-style="{ padding: '0 20px 20px' }">
      <el-tabs v-model="currentTab">
        <el-tab-pane v-for="(item, index) in headTab" :key="index" :label="item.tit" :name="item.name"></el-tab-pane>
      </el-tabs>
      <el-form
        class="formValidate mt20"
        ref="formValidate"
        :rules="ruleValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <!-- 基础信息-->
        <el-row :gutter="24" v-show="currentTab === '1'">
          <el-col :span="24">
            <el-form-item label="商品类型：" props="is_virtual">
              <div
                class="virtual"
                :class="formValidate.virtual_type == item.id ? 'virtual_boder' : 'virtual_boder2'"
                v-for="(item, index) in virtual"
                :key="index"
                @click="virtualbtn(item.id, 2)"
              >
                <div class="virtual_top">{{ item.tit }}</div>
                <div class="virtual_bottom">({{ item.tit2 }})</div>
                <div v-if="formValidate.virtual_type == item.id" class="virtual_san"></div>
                <div v-if="formValidate.virtual_type == item.id" class="virtual_dui">✓</div>
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="商品分类：" prop="cate_id">
              <!-- {{ formValidate.cate_id }}
              <el-select v-model="formValidate.cate_id" placeholder="请选择商品分类" multiple class="content_width">
                <el-option v-for="item in treeSelect" :disabled="item.pid === 0" :value="item.id" :key="item.id">{{
                  item.html + item.cate_name
                }}</el-option>
              </el-select>
              {{ formValidate.cate_id }} -->
              <el-cascader
                class="content_width"
                v-model="formValidate.cate_id"
                size="small"
                :options="treeSelect"
                :props="{ multiple: true, checkStrictly: true, emitPath: false }"
                clearable
              ></el-cascader>
              <span class="addfont" @click="addCate">新增分类</span>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="商品名称：" prop="store_name">
              <el-input class="content_width" v-model.trim="formValidate.store_name" placeholder="请输入商品名称" />
            </el-form-item>
          </el-col>

          <el-col :span="24">
            <el-form-item label="单位：" prop="unit_name">
              <el-input class="content_width" v-model="formValidate.unit_name" placeholder="请输入单位" />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="商品轮播图：" prop="slider_image">
              <div class="acea-row">
                <div
                  class="pictrue"
                  v-for="(item, index) in formValidate.slider_image"
                  :key="index"
                  draggable="true"
                  @dragstart="handleDragStart($event, item)"
                  @dragover.prevent="handleDragOver($event, item)"
                  @dragenter="handleDragEnter($event, item)"
                  @dragend="handleDragEnd($event, item)"
                >
                  <img v-lazy="item" />
                  <i class="el-icon-error btndel" @click="handleRemove(index)"></i>
                </div>
                <div
                  v-if="formValidate.slider_image.length < 10"
                  class="upLoad acea-row row-center-wrapper"
                  @click="modalPicTap('duo')"
                >
                  <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                </div>
                <el-input v-model="formValidate.slider_image[0]" style="display: none"></el-input>
              </div>

              <div class="titTip">建议尺寸：800*800，可拖拽改变图片顺序，默认首张图为主图，最多上传10张</div>

              <!-- <div class="tips">(最多10张<br />750*750)</div> -->
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="添加视频：">
              <el-switch :active-value="1" :inactive-value="0" v-model="formValidate.video_open" size="large">
                <span slot="open">开启</span>
                <span slot="close">关闭</span>
              </el-switch>
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="formValidate.video_open && !formValidate.video_link">
            <el-form-item label="视频类型：">
              <el-radio-group v-model="seletVideo" @input="changeVideo">
                <el-radio :label="0" class="radio">上传视频</el-radio>
                <el-radio :label="1">视频链接</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="formValidate.video_open" id="selectvideo">
            <el-form-item label="" prop="video_link">
              <el-input
                v-if="seletVideo == 1 && !formValidate.video_link"
                class="content_width"
                v-model="videoLink"
                placeholder="请输入视频链接"
              />
              <input type="file" ref="refid" @change="zh_uploadFile_change" style="display: none" />
              <div
                v-if="seletVideo == 0 && (upload_type !== '1' || videoLink) && !formValidate.video_link"
                class="ml10 videbox"
                @click="zh_uploadFile"
              >
                +
              </div>
              <el-button
                v-if="seletVideo == 1 && (upload_type !== '1' || videoLink) && !formValidate.video_link"
                type="primary"
                class="ml10"
                @click="zh_uploadFile"
                >确认添加</el-button
              >
              <el-upload
                v-if="upload_type === '1' && !videoLink && !formValidate.video_link"
                :show-file-list="false"
                :action="fileUrl2"
                :before-upload="videoSaveToUrl"
                :data="uploadData"
                :headers="header"
                :multiple="false"
                style="display: inline-block"
                accept=".mp4"
              >
                <div v-if="seletVideo === 0 && !formValidate.video_link" class="videbox">+</div>
              </el-upload>
              <div class="box-video-style" v-if="formValidate.video_link">
                <video
                  style="width: 100%; height: 100%;"
                  :src="formValidate.video_link"
                  controls="controls"
                >
                  您的浏览器不支持 video 标签。
                </video>
                <div class="mark"></div>
                <i class="el-icon-delete iconv" @click="delVideo"></i>
              </div>
              <Progress class="progress" :percent="progress" :stroke-width="5" v-if="upload.videoIng || videoIng" />
              <div class="titTip">建议时长：9～30秒，视频宽高比16:9</div>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item label="商品状态：">
              <el-radio-group v-model="formValidate.is_show">
                <el-radio :label="1" class="radio">上架</el-radio>
                <el-radio :label="0">下架</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>

          <!-- <el-col :span="24">
                        <el-form-item label="商品标签：" prop="label_id">
                            <el-select v-model="formValidate.label_id" multiple v-width="'50%'">
                                <el-option v-for="item in dataLabel" :value="item.id" :key="item.id">{{ item.label_name }}</el-option>
                            </el-select>
                        </el-form-item>
                    </el-col> -->
        </el-row>
        <!-- 规格库存-->
        <el-row :gutter="24" v-show="currentTab === '2'">
          <el-col :span="24">
            <el-form-item label="商品规格：" props="spec_type">
              <el-radio-group v-model="formValidate.spec_type" @input="changeSpec">
                <el-radio :label="0" class="radio">单规格</el-radio>
                <el-radio :label="1">多规格</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <!-- 多规格添加-->
          <el-col :span="24" v-if="formValidate.spec_type === 1" class="noForm">
            <el-form-item label="选择规格：" prop="">
              <div class="acea-row row-middle">
                <el-select v-model="formValidate.selectRule" class="content_width mr14">
                  <el-option
                    v-for="(item, index) in ruleList"
                    :value="item.rule_name"
                    :key="index"
                    :label="item.rule_name"
                  ></el-option>
                </el-select>
                <el-button type="primary" @click="confirm">确认</el-button>
                <el-button @click="addRule">添加规格模板</el-button>
              </div>
            </el-form-item>

            <el-form-item v-if="attrs.length !== 0">
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
                  <div style="width: 90%" :class="moveIndex === index ? 'borderStyle' : ''">
                    <div class="acea-row row-middle">
                      <span class="mr5">{{ item.value }}</span>
                      <i size="14" class="curs el-icon-error" @click="handleRemoveRole(index)" />
                    </div>
                    <div class="rulesBox">
                      <draggable class="item" :list="item.detail" handle=".drag">
                        <el-tag
                          closable
                          color="primary"
                          v-for="(j, indexn) in item.detail"
                          :key="indexn"
                          class="mr20 drag"
                          @close="handleRemove2(item.detail, indexn)"
                          >{{ j }}</el-tag
                        >
                      </draggable>
                      <el-input
                        placeholder="请输入属性名称"
                        v-model="item.detail.attrsVal"
                        @keyup.enter.native="createAttr(item.detail.attrsVal, index)"
                        style="width: 190px"
                      >
                        <template slot="append"
                          ><el-button type="primary" @click="createAttr(item.detail.attrsVal, index)"
                            >添加</el-button
                          ></template
                        >
                      </el-input>
                    </div>
                  </div>
                </div>
              </draggable>
              <!-- <div  v-for="(item, index) in attrs" :key="index">
                                    <div class="acea-row row-middle"><span class="mr5">{{item.value}}</span><Icon type="ios-close-circle" size="14" class="curs" @click="handleRemoveRole(index)"/></div>
                                    <div class="rulesBox">
                                        <el-tag type="dot" closable color="primary" v-for="(j, indexn) in item.detail" :key="indexn" :name="j" class="mr20" @on-close="handleRemove2(item.detail,indexn)">{{j}}</el-tag>
                                        <el-input search enter-button="添加" placeholder="请输入属性名称" v-model="item.detail.attrsVal" @on-search="createAttr(item.detail.attrsVal,index)" style="width: 150px"/>
                                    </div>
                                </div> -->
            </el-form-item>
            <!-- <el-col :span="24" v-if="createBnt"> -->
            <el-form-item v-if="createBnt">
              <el-button type="primary" @click="addBtn">添加新规格</el-button>
              <el-button type="success" @click="generate(1)">立即生成</el-button>
            </el-form-item>
            <!-- </el-col> -->
            <el-col :span="24" v-if="showIput">
              <el-col :xl="6" :lg="9" :md="10" :sm="24" :xs="24">
                <el-form-item label="规格：">
                  <el-input placeholder="请输入规格" v-model="formDynamic.attrsName" />
                </el-form-item>
              </el-col>
              <el-col :xl="6" :lg="9" :md="10" :sm="24" :xs="24">
                <el-form-item label="规格值：">
                  <el-input v-model="formDynamic.attrsVal" placeholder="请输入规格值" />
                </el-form-item>
              </el-col>
              <el-col :xl="6" :lg="5" :md="10" :sm="24" :xs="24">
                <div class="df-n-warp">
                  <el-button type="primary" @click="createAttrName">确定</el-button>
                  <el-button @click="offAttrName">取消</el-button>
                </div>
              </el-col>
            </el-col>
            <!-- 多规格设置-->
            <el-col
              :xl="24"
              :lg="24"
              :md="24"
              :sm="24"
              :xs="24"
              v-if="manyFormValidate.length && formValidate.header.length !== 0 && attrs.length !== 0"
            >
              <!-- 批量设置-->
              <el-col :span="24" v-if="[0, 3].includes(formValidate.virtual_type)">
                <el-form-item label="批量设置：" class="labeltop">
                  <el-table :data="oneFormBatch" border>
                    <el-table-column
                      :label="item.title"
                      :min-width="item.minWidth"
                      v-for="(item, index) in formValidate.is_virtual ? columns3 : columns2"
                      :key="index"
                    >
                      <template slot-scope="scope">
                        <template v-if="item.key">
                          <div>
                            <span>{{ scope.row[item.key] }}</span>
                          </div>
                        </template>
                        <template v-else-if="item.slot === 'pic'">
                          <div
                            class="acea-row row-middle row-center-wrapper"
                            @click="modalPicTap('dan', 'duopi', scope.$index)"
                          >
                            <div class="pictrue pictrueTab" v-if="oneFormBatch[0].pic">
                              <img v-lazy="oneFormBatch[0].pic" />
                            </div>
                            <div class="upLoad pictrueTab acea-row row-center-wrapper" v-else>
                              <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                            </div>
                          </div>
                        </template>
                        <template v-else-if="item.slot === 'price'">
                          <el-input-number
                            :controls="false"
                            v-model="oneFormBatch[0].price"
                            :min="0"
                            :max="9999999999"
                            class="priceBox"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'cost'">
                          <el-input-number
                            :controls="false"
                            v-model="oneFormBatch[0].cost"
                            :min="0"
                            :max="9999999999"
                            class="priceBox"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'ot_price'">
                          <el-input-number
                            :controls="false"
                            v-model="oneFormBatch[0].ot_price"
                            :min="0"
                            class="priceBox"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'stock'">
                          <el-input-number
                            :controls="false"
                            v-model="oneFormBatch[0].stock"
                            :disabled="formValidate.virtual_type == 1"
                            :min="0"
                            :max="9999999999"
                            class="priceBox"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'fictitious'">
                          <el-button
                            v-if="!scope.row.coupon_id && formValidate.virtual_type == 2"
                            @click="addGoodsCoupon(scope.$index, 'oneFormBatch')"
                            >添加优惠券</el-button
                          >
                          <span
                            class="see"
                            v-else-if="formValidate.virtual_type == 2 && scope.row.coupon_id"
                            @click="see(scope.row, 'manyFormValidate', scope.$index)"
                            >{{ scope.row.coupon_name }}</span
                          >
                          <el-button
                            v-else-if="!scope.row.virtual_list.length && formValidate.virtual_type == 1"
                            @click="addVirtual(scope.$index, 'oneFormBatch')"
                            >添加卡密</el-button
                          >
                          <span
                            class="see"
                            v-else-if="scope.row.virtual_list.length && formValidate.virtual_type == 1"
                            @click="see(scope.row, 'oneFormBatch', scope.$index)"
                            >已设置</span
                          >
                        </template>
                        <template v-else-if="item.slot === 'bar_code'">
                          <el-input v-model="oneFormBatch[0].bar_code"></el-input>
                        </template>
                        <template v-else-if="item.slot === 'weight'">
                          <el-input-number
                            :controls="false"
                            v-model="oneFormBatch[0].weight"
                            :step="0.1"
                            :min="0"
                            :max="9999999999"
                            class="priceBox"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'volume'">
                          <el-input-number
                            :controls="false"
                            v-model="oneFormBatch[0].volume"
                            :step="0.1"
                            :min="0"
                            :max="9999999999"
                            class="priceBox"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'action'">
                          <a @click="batchAdd">批量添加</a>
                          <el-divider direction="vertical"></el-divider>
                          <a @click="batchDel">清空</a>
                        </template>
                      </template>
                    </el-table-column>
                  </el-table>
                </el-form-item>
              </el-col>
              <!-- 多规格表格-->
              <el-col :span="24">
                <el-form-item label="商品属性：" class="labeltop">
                  <el-table :data="manyFormValidate" border>
                    <el-table-column
                      :label="item.title"
                      :min-width="item.minWidth"
                      v-for="(item, index) in formValidate.header"
                      :key="index"
                    >
                      <template slot-scope="scope">
                        <template v-if="item.key">
                          <div>
                            <span>{{ scope.row[item.key] }}</span>
                          </div>
                        </template>
                        <template v-else-if="item.slot === 'pic'">
                          <div
                            class="acea-row row-middle row-center-wrapper"
                            @click="modalPicTap('dan', 'duoTable', scope.$index)"
                          >
                            <div class="pictrue pictrueTab" v-if="manyFormValidate[scope.$index].pic">
                              <img v-lazy="manyFormValidate[scope.$index].pic" />
                            </div>
                            <div class="upLoad pictrueTab acea-row row-center-wrapper" v-else>
                              <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                            </div>
                          </div>
                        </template>
                        <template v-else-if="item.slot === 'price'">
                          <el-input-number
                            :controls="false"
                            v-model="manyFormValidate[scope.$index].price"
                            :min="0"
                            :max="9999999999"
                            class="priceBox"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'cost'">
                          <el-input-number
                            :controls="false"
                            v-model="manyFormValidate[scope.$index].cost"
                            :min="0"
                            :max="9999999999"
                            class="priceBox"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'ot_price'">
                          <el-input-number
                            :controls="false"
                            v-model="manyFormValidate[scope.$index].ot_price"
                            :min="0"
                            :max="9999999999"
                            class="priceBox"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'stock'">
                          <el-input-number
                            :controls="false"
                            v-model="manyFormValidate[scope.$index].stock"
                            :disabled="formValidate.virtual_type == 1"
                            :min="0"
                            :max="9999999999"
                            :precision="0"
                            class="priceBox"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'bar_code'">
                          <el-input v-model="manyFormValidate[scope.$index].bar_code"></el-input>
                        </template>
                        <template v-else-if="item.slot === 'weight'">
                          <el-input-number
                            :controls="false"
                            v-model="manyFormValidate[scope.$index].weight"
                            :min="0"
                            :max="9999999999"
                            class="priceBox"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'volume'">
                          <el-input-number
                            :controls="false"
                            v-model="manyFormValidate[scope.$index].volume"
                            :min="0"
                            :max="9999999999"
                            class="priceBox"
                          ></el-input-number>
                        </template>
                        <template v-else-if="item.slot === 'fictitious'">
                          <el-button
                            v-if="!scope.row.coupon_id && formValidate.virtual_type == 2"
                            @click="addGoodsCoupon(scope.$index, 'manyFormValidate')"
                            >添加优惠券</el-button
                          >
                          <span
                            class="see"
                            v-else-if="scope.row.coupon_id && formValidate.virtual_type == 2"
                            @click="see(scope.row, 'manyFormValidate', scope.$index)"
                            >{{ scope.row.coupon_name }}</span
                          >
                          <el-button
                            v-else-if="!scope.row.virtual_list && !scope.row.stock && formValidate.virtual_type == 1"
                            @click="addVirtual(scope.$index, 'manyFormValidate')"
                            >添加卡密</el-button
                          >
                          <span
                            class="see"
                            v-else-if="
                              (scope.row.virtual_list.length || scope.row.stock) && formValidate.virtual_type == 1
                            "
                            @click="see(scope.row, 'manyFormValidate', scope.$index)"
                            >已设置</span
                          >
                        </template>
                        <template v-else-if="item.slot === 'action'">
                          <a @click="delAttrTable(scope.$index)">删除</a>
                        </template>
                      </template>
                    </el-table-column>
                  </el-table>
                </el-form-item>
              </el-col>
            </el-col>
          </el-col>
          <!-- 单规格表格-->
          <div v-if="formValidate.spec_type === 0">
            <el-col :span="24">
              <el-form-item label="图片：">
                <div class="pictrueBox" @click="modalPicTap('dan', 'danTable', 0)">
                  <div class="pictrue" v-if="oneFormValidate[0].pic">
                    <img v-lazy="oneFormValidate[0].pic" />
                    <el-input v-model="oneFormValidate[0].pic" style="display: none"></el-input>
                  </div>
                  <div class="upLoad acea-row row-center-wrapper" v-else>
                    <el-input v-model="oneFormValidate[0].pic" style="display: none"></el-input>
                    <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                  </div>
                </div>
              </el-form-item>
            </el-col>
            <el-col :span="24">
              <el-form-item label="售价：">
                <el-input-number
                  :controls="false"
                  v-model="oneFormValidate[0].price"
                  :min="0"
                  :precision="2"
                  :max="9999999999"
                  class="content_width"
                  :active-change="false"
                ></el-input-number>
              </el-form-item>
            </el-col>
            <el-col :span="24">
              <el-form-item label="成本价：">
                <el-input-number
                  :controls="false"
                  v-model="oneFormValidate[0].cost"
                  :min="0"
                  :max="9999999999"
                  :precision="2"
                  :active-change="false"
                  class="content_width"
                ></el-input-number>
              </el-form-item>
            </el-col>
            <el-col :span="24">
              <el-form-item label="原价：">
                <el-input-number
                  :controls="false"
                  v-model="oneFormValidate[0].ot_price"
                  :min="0"
                  :max="9999999999"
                  :precision="2"
                  :active-change="false"
                  class="content_width"
                ></el-input-number>
              </el-form-item>
            </el-col>
            <el-col :span="24">
              <el-form-item label="库存：">
                <el-input-number
                  :controls="false"
                  v-model="oneFormValidate[0].stock"
                  :min="0"
                  :max="9999999999"
                  :disabled="formValidate.virtual_type == 1"
                  :precision="0"
                  class="content_width"
                ></el-input-number>
              </el-form-item>
            </el-col>
            <el-col :span="24">
              <el-form-item label="商品编号：">
                <el-input v-model.trim="oneFormValidate[0].bar_code" class="content_width"></el-input>
              </el-form-item>
            </el-col>
            <el-col :span="24" v-if="formValidate.virtual_type == 0">
              <el-form-item label="重量(KG)：">
                <el-input-number
                  :controls="false"
                  v-model="oneFormValidate[0].weight"
                  :min="0"
                  :max="9999999999"
                  class="content_width"
                ></el-input-number>
              </el-form-item>
            </el-col>
            <el-col :span="24">
              <el-form-item label="体积(m³)：" v-if="formValidate.virtual_type == 0">
                <el-input-number
                  :controls="false"
                  v-model="oneFormValidate[0].volume"
                  :min="0"
                  :max="9999999999"
                  class="content_width"
                ></el-input-number>
              </el-form-item>
            </el-col>

            <el-col :span="24">
              <el-form-item label="虚拟商品：" v-if="formValidate.virtual_type == 1 || formValidate.virtual_type == 2">
                <el-button
                  v-if="!oneFormValidate[0].coupon_id && formValidate.virtual_type == 2"
                  @click="addGoodsCoupon(0, 'oneFormValidate')"
                  >添加优惠券</el-button
                >
                <span
                  class="see"
                  v-else-if="oneFormValidate[0].coupon_id && formValidate.virtual_type == 2"
                  @click="see(oneFormValidate[0], 'oneFormValidate', 0)"
                  >{{ oneFormValidate[0].coupon_name }}</span
                >
                <el-button
                  v-if="
                    !oneFormValidate[0].virtual_list.length &&
                    !oneFormValidate[0].stock &&
                    formValidate.virtual_type == 1
                  "
                  @click="addVirtual(0, 'oneFormValidate')"
                  >添加卡密</el-button
                >
                <span
                  class="see"
                  v-else-if="
                    (oneFormValidate[0].virtual_list.length || oneFormValidate[0].stock > 0) &&
                    formValidate.virtual_type == 1
                  "
                  @click="see(oneFormValidate[0], 'oneFormValidate', 0)"
                  >已设置</span
                >
              </el-form-item>
            </el-col>
          </div>
        </el-row>
        <!-- 商品详情-->
        <el-row v-show="currentTab === '3'">
          <el-col :span="16">
            <el-form-item label="商品详情：">
              <WangEditor style="width: 100%" :content="contents" @editorContent="getEditorContent"></WangEditor>
            </el-form-item>
          </el-col>
          <el-col :span="6" style="width: 33%">
            <div class="ifam">
              <div class="content" v-html="content"></div>
            </div>
          </el-col>
        </el-row>

        <!-- 物流设置-->
        <el-row v-show="headTab.length === 6 ? currentTab === '4' : false">
          <el-col :span="24">
            <el-form-item label="物流方式：" prop="logistics">
              <el-checkbox-group v-model="formValidate.logistics" @change="logisticsBtn">
                <el-checkbox label="1">快递</el-checkbox>
                <el-checkbox label="2">到店核销</el-checkbox>
              </el-checkbox-group>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="运费设置：">
              <el-radio-group v-model="formValidate.freight">
                <!-- <el-radio :label="1">包邮</el-radio> -->
                <el-radio :label="2">固定邮费</el-radio>
                <el-radio :label="3">运费模板</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="formValidate.freight != 3 && formValidate.freight != 1">
            <el-form-item label="" :prop="formValidate.freight != 1 ? 'freight' : ''">
              <div class="acea-row">
                <el-input-number
                  :controls="false"
                  :min="0"
                  v-model="formValidate.postage"
                  placeholder="请输入金额"
                  class="content_width maxW"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="formValidate.freight == 3">
            <el-form-item label="" prop="temp_id">
              <div class="acea-row">
                <el-select
                  v-model="formValidate.temp_id"
                  clearable
                  placeholder="请选择运费模板"
                  class="content_width maxW"
                >
                  <el-option
                    v-for="(item, index) in templateList"
                    :value="item.id"
                    :key="index"
                    :label="item.name"
                  ></el-option>
                </el-select>
                <span class="addfont" @click="addTemp">新增运费模板</span>
              </div>
            </el-form-item>
          </el-col>
        </el-row>
        <!-- 营销设置-->
        <el-row :gutter="24" v-show="headTab.length === 6 ? currentTab === '5' : currentTab === '4'">
          <el-col :span="24">
            <el-form-item label="已售数量：">
              <el-input-number
                :controls="false"
                :min="0"
                :max="9999999999"
                v-model="formValidate.ficti"
                placeholder="请输入虚拟销量"
                class="content_width"
              />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="排序：">
              <el-input-number
                :controls="false"
                :min="0"
                :max="9999999999"
                v-model="formValidate.sort"
                placeholder="请输入排序"
                class="content_width"
              />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <div class="line"></div>
          </el-col>

          <el-col :span="24">
            <el-form-item label="购买送积分：" prop="give_integral">
              <el-input-number
                :controls="false"
                v-model="formValidate.give_integral"
                :min="0"
                :max="9999999999"
                placeholder="请输入积分"
                class="content_width"
              />
            </el-form-item>
          </el-col>
          <el-col v-bind="grid3">
            <el-form-item label="购买送优惠券：">
              <div v-if="couponName.length" class="mb20">
                <el-tag closable v-for="(item, index) in couponName" :key="index" @close="handleClose(item)">{{
                  item.title
                }}</el-tag>
              </div>
              <el-button type="primary" @click="addCoupon">添加优惠券</el-button>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="关联用户标签：" prop="label_id">
              <div style="display: flex">
                <div class="labelInput acea-row row-between-wrapper" @click="openLabel">
                  <div style="width: 90%">
                    <div v-if="dataLabel.length">
                      <el-tag closable v-for="(item, index) in dataLabel" @close="closeLabel(item)" :key="index">{{
                        item.label_name
                      }}</el-tag>
                    </div>
                    <span class="span" v-else>选择用户关联标签</span>
                  </div>
                  <div class="iconfont iconxiayi"></div>
                </div>
                <span class="addfont" @click="addLabel">新增标签</span>
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <div class="line"></div>
          </el-col>
          <el-col :span="24">
            <el-form-item label="付费会员专属：">
              <el-switch :active-value="1" :inactive-value="0" v-model="formValidate.vip_product" size="large">
                <span slot="open">开启</span>
                <span slot="close">关闭</span>
              </el-switch>
              <div class="titTip">开启后仅付费会员可以看见并购买此商品</div>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="单独设置：">
              <el-checkbox-group v-model="formValidate.is_sub" @change="checkAllGroupChange">
                <el-checkbox :label="1">佣金设置（数字即返佣金额）</el-checkbox>
                <el-checkbox :label="0">付费会员价</el-checkbox>
              </el-checkbox-group>
              <!-- <el-radio-group v-model="formValidate.is_sub">
                                <el-radio :label="1" class="radio">佣金设置</el-radio>
                                <el-radio :label="0">会员价</el-radio>
                            </el-radio-group> -->
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="formValidate.is_sub.length">
            <!--单规格返佣-->
            <el-form-item label="商品属性：" v-if="formValidate.spec_type === 0">
              <el-table :data="oneFormValidate" border>
                <el-table-column
                  :label="item.title"
                  :min-width="item.minWidth"
                  v-for="(item, index) in columnsInstall"
                  :key="index"
                >
                  <template slot-scope="scope">
                    <template v-if="item.key">
                      <div>
                        <span>{{ scope.row[item.key] }}</span>
                      </div>
                    </template>
                    <template v-else-if="item.slot === 'pic'">
                      <div class="pictrue pictrueTab">
                        <img v-lazy="oneFormValidate[0].pic" />
                      </div>
                    </template>
                    <template v-else-if="item.slot === 'price'">
                      <span>{{ oneFormValidate[0].price }}</span>
                    </template>
                    <template v-else-if="item.slot === 'cost'">
                      <span>{{ oneFormValidate[0].cost }}</span>
                    </template>
                    <template v-else-if="item.slot === 'ot_price'">
                      <span>{{ oneFormValidate[0].ot_price }}</span>
                    </template>
                    <template v-else-if="item.slot === 'stock'">
                      <span>{{ oneFormValidate[0].stock }}</span>
                    </template>
                    <template v-else-if="item.slot === 'bar_code'">
                      <span>{{ oneFormValidate[0].bar_code }}</span>
                    </template>
                    <template v-else-if="item.slot === 'weight'">
                      <span>{{ oneFormValidate[0].weight }}</span>
                    </template>
                    <template v-else-if="item.slot === 'fictitious'">
                      <el-button
                        v-if="!row.coupon_id && formValidate.virtual_type == 2"
                        @click="addGoodsCoupon(scope.$index, 'oneFormValidate')"
                        >添加优惠券</el-button
                      >
                      <span
                        class="see"
                        v-else-if="row.coupon_id && formValidate.virtual_type == 2"
                        @click="see(row, 'manyFormValidate', scope.$index)"
                        >{{ row.coupon_name }}</span
                      >
                      <el-button
                        v-else-if="!row.virtual_list.length && !row.stock && formValidate.virtual_type == 1"
                        @click="addVirtual(scope.$index, 'oneFormValidate')"
                        >添加卡密</el-button
                      >
                      <span
                        class="see"
                        v-else-if="(row.virtual_list.length || row.stock) && formValidate.virtual_type == 1"
                        @click="see(row, 'oneFormValidate', scope.$index)"
                        >已设置</span
                      >
                    </template>
                    <template v-else-if="item.slot === 'brokerage'">
                      <el-input-number
                        :controls="false"
                        v-model="oneFormValidate[0].brokerage"
                        :min="0"
                        :max="9999999999"
                        class="priceBox"
                      ></el-input-number>
                    </template>
                    <template v-else-if="item.slot === 'brokerage_two'">
                      <el-input-number
                        :controls="false"
                        v-model="oneFormValidate[0].brokerage_two"
                        :min="0"
                        :max="9999999999"
                        class="priceBox"
                      ></el-input-number>
                    </template>
                    <template v-else-if="item.slot === 'vip_price'">
                      <el-input-number
                        :controls="false"
                        v-model="oneFormValidate[0].vip_price"
                        :min="0"
                        :max="9999999999"
                        class="priceBox"
                      ></el-input-number>
                    </template>
                  </template>
                </el-table-column>
              </el-table>
            </el-form-item>
            <!--多规格返佣-->
            <el-form-item label="批量设置：" v-if="formValidate.spec_type === 1">
              <span v-if="formValidate.is_sub.indexOf(1) > -1">
                <span class="brokerage">一级返佣：</span
                ><el-input
                  type="number"
                  :controls="false"
                  placeholder="请输入一级返佣"
                  class="columnsBox content_width"
                  v-model="manyBrokerage"
                >
                </el-input>
                <span class="brokerage">二级返佣：</span
                ><el-input
                  type="number"
                  :controls="false"
                  placeholder="请输入二级返佣"
                  class="columnsBox content_width"
                  v-model="manyBrokerageTwo"
                  ></el-input
                >
              </span>
              <span v-if="formValidate.is_sub.indexOf(0) > -1">
                会员价：<el-input-number
                  :controls="false"
                  placeholder="请输入会员价"
                  :min="0"
                  :max="9999999999"
                  class="columnsBox content_width"
                  v-model="manyVipPrice"
                ></el-input-number>
              </span>
              <el-button type="primary" @click="brokerageSetUp">批量设置</el-button>
              <!-- <template v-if="formValidate.is_sub">
                                <el-input-number controls-position="right" v-width="'20%'" placeholder="请输入一级返佣" :min="0" class="columnsBox" v-model="manyBrokerage"></el-input-number>
                                <el-input-number controls-position="right" v-width="'20%'" placeholder="请输入二级返佣" :min="0" class="columnsBox" v-model="manyBrokerageTwo"></el-input-number>
                                <el-button type="primary" @click="brokerageSetUp">批量设置</el-button>
                            </template>
                            <template v-else>
                                <el-input-number controls-position="right" v-width="'20%'" placeholder="请输入会员价" :min="0" class="columnsBox" v-model="manyVipPrice"></el-input-number>
                                <el-button type="primary" @click="vipPriceSetUp">批量设置</el-button>
                            </template> -->
            </el-form-item>
            <el-form-item label="商品属性：" v-if="formValidate.spec_type === 1 && manyFormValidate.length">
              <el-table v-if="formValidate.is_sub" :data="manyFormValidate" border>
                <el-table-column
                  :label="item.title"
                  :min-width="item.minWidth"
                  v-for="(item, index) in columnsInstal2"
                  :key="index"
                >
                  <template slot-scope="scope">
                    <template v-if="item.key">
                      <div>
                        <span>{{ scope.row[item.key] }}</span>
                      </div>
                    </template>
                    <template v-else-if="item.slot === 'pic'">
                      <div class="pictrue pictrueTab">
                        <img v-lazy="manyFormValidate[scope.$index].pic" />
                      </div>
                    </template>
                    <template v-else-if="item.slot === 'price'">
                      <span>{{ manyFormValidate[scope.$index].price }}</span>
                    </template>
                    <template v-else-if="item.slot === 'cost'">
                      <span>{{ manyFormValidate[scope.$index].cost }}</span>
                    </template>
                    <template v-else-if="item.slot === 'ot_price'">
                      <span>{{ manyFormValidate[scope.$index].ot_price }}</span>
                    </template>
                    <template v-else-if="item.slot === 'stock'">
                      <span>{{ manyFormValidate[scope.$index].stock }}</span>
                    </template>
                    <template v-else-if="item.slot === 'bar_code'">
                      <span>{{ manyFormValidate[scope.$index].bar_code }}</span>
                    </template>
                    <template v-else-if="item.slot === 'weight'">
                      <span>{{ manyFormValidate[index].weight }}</span>
                    </template>
                    <template v-else-if="item.slot === 'fictitious'">
                      <el-button
                        v-if="!row.coupon_id && formValidate.virtual_type == 2"
                        @click="addGoodsCoupon(scope.$index, 'manyFormValidate')"
                        >添加优惠券</el-button
                      >
                      <span
                        class="see"
                        v-else-if="row.coupon_id && formValidate.virtual_type == 2"
                        @click="see(row, 'manyFormValidate', scope.$index)"
                        >{{ row.coupon_name }}</span
                      >
                      <el-button
                        v-else-if="!row.virtual_list.length && !row.stock && formValidate.virtual_type == 1"
                        @click="addVirtual(scope.$index, 'manyFormValidate')"
                        >添加卡密</el-button
                      >
                      <span
                        class="see"
                        v-else-if="(row.virtual_list.length || row.stock) && formValidate.virtual_type == 1"
                        @click="see(row, 'manyFormValidate', scope.$index)"
                        >已设置</span
                      >
                    </template>
                    <template v-else-if="item.slot === 'volume'">
                      <span>{{ manyFormValidate[scope.$index].volume }}</span>
                    </template>
                    <template v-else-if="item.slot === 'brokerage'">
                      <el-input-number
                        :controls="false"
                        v-model="manyFormValidate[scope.$index].brokerage"
                        :min="0"
                        :max="9999999999"
                        class="priceBox"
                      ></el-input-number>
                    </template>
                    <template v-else-if="item.slot === 'brokerage_two'">
                      <el-input-number
                        :controls="false"
                        v-model="manyFormValidate[scope.$index].brokerage_two"
                        :min="0"
                        :max="9999999999"
                        class="priceBox"
                      ></el-input-number>
                    </template>
                    <template v-else-if="item.slot === 'vip_price'">
                      <el-input-number
                        :controls="false"
                        v-model="manyFormValidate[scope.$index].vip_price"
                        :min="0"
                        :max="9999999999"
                        class="priceBox"
                      ></el-input-number>
                    </template>
                  </template>
                </el-table-column>
              </el-table>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <div class="line"></div>
          </el-col>
          <el-col :span="24">
            <el-form-item label="起购数量：">
              <el-input-number
                :controls="false"
                :min="1"
                :max="9999999999"
                :precision="0"
                v-model="formValidate.min_qty"
                placeholder="请输入起购数量"
                class="content_width"
              />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="是否限购：">
              <el-switch :active-value="1" :inactive-value="0" v-model="formValidate.is_limit" size="large">
                <span slot="open">开启</span>
                <span slot="close">关闭</span>
              </el-switch>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="限购类型：" v-if="formValidate.is_limit">
              <el-radio-group v-model="formValidate.limit_type">
                <el-radio :label="1">单次限购</el-radio>
                <el-radio :label="2">单人限购</el-radio>
              </el-radio-group>
              <div class="titTip">单次限购是限制每次下单最多购买的数量，单人限购是限制一个用户总共可以购买的数量</div>
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="formValidate.is_limit">
            <el-form-item label="限购数量：" prop="limit_num">
              <div class="acea-row row-middle">
                <el-input-number
                  :controls="false"
                  placeholder="请输入限购数量"
                  :precision="0"
                  :min="1"
                  v-model="formValidate.limit_num"
                  class="content_width"
                />
                <span class="ml10"> 件 </span>
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="formValidate.virtual_type == 0 || formValidate.virtual_type == 3">
            <el-form-item label="预售商品：">
              <el-switch :active-value="1" :inactive-value="0" v-model="formValidate.presale" size="large">
                <span slot="open">开启</span>
                <span slot="close">关闭</span>
              </el-switch>
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="formValidate.presale">
            <el-form-item label="预售活动时间：" prop="presale_time">
              <div class="acea-row row-middle">
                <el-date-picker
                  clearable
                  :editable="false"
                  type="datetimerange"
                  format="yyyy-MM-dd HH:mm"
                  value-format="yyyy-MM-dd HH:mm"
                  range-separator="-"
                  start-placeholder="开始日期"
                  end-placeholder="结束日期"
                  @change="onchangeTime"
                  v-model="formValidate.presale_time"
                ></el-date-picker>
              </div>
              <div class="titTip">设置活动开启结束时间，用户可以在设置时间内发起参与预售</div>
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="formValidate.presale">
            <el-form-item label="发货时间：" prop="presale_day">
              <div class="acea-row row-middle">
                <span class="mr10">预售活动结束后</span>
                <el-input-number
                  class="w-80"
                  :controls="false"
                  placeholder="请输入发货时间"
                  :precision="0"
                  :min="1"
                  v-model="formValidate.presale_day"
                />
                <span class="ml10"> 天之内 </span>
              </div>
            </el-form-item>
          </el-col>

          <el-col :span="24">
            <el-form-item label="商品推荐：">
              <el-checkbox-group v-model="formValidate.recommend">
                <el-checkbox label="is_hot">热卖单品</el-checkbox>
                <el-checkbox label="is_benefit">促销单品</el-checkbox>
                <el-checkbox label="is_best">精品推荐</el-checkbox>
                <el-checkbox label="is_new">首发新品</el-checkbox>
                <el-checkbox label="is_good">优品推荐</el-checkbox>
              </el-checkbox-group>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid3">
            <el-form-item label="活动优先级：">
              <div class="color-list acea-row row-middle">
                <div
                  class="color-item"
                  :class="activity[color]"
                  v-for="color in formValidate.activity"
                  v-dragging="{
                    item: color,
                    list: formValidate.activity,
                    group: 'color',
                  }"
                  :key="color"
                >
                  {{ color }}
                </div>
              </div>
              <div class="titTip">可拖动按钮调整活动的优先展示顺序</div>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid3">
            <el-form-item label="选择优品推荐商品：">
              <div class="picBox">
                <div class="pictrue" v-for="(item, index) in formValidate.recommend_list" :key="index">
                  <img v-lazy="item.image" />
                  <i class="el-icon-error btndel" @click="handleRemoveRecommend(index)"></i>
                </div>
                <div class="upLoad acea-row row-center-wrapper" @click="changeGoods">
                  <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                </div>
              </div>
            </el-form-item>
          </el-col>
        </el-row>
        <!-- 其他设置-->
        <el-row justify="space-between" v-show="headTab.length === 6 ? currentTab === '6' : currentTab === '5'">
          <el-col :span="24">
            <el-form-item label="商品关键字：">
              <el-input class="content_width" v-model.trim="formValidate.keyword" placeholder="请输入商品关键字" />
              <div class="titTip">PC端的SEO优化以及可以根据关键字进行商品搜索</div>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="商品简介：">
              <el-input
                class="content_width"
                v-model.trim="formValidate.store_info"
                type="textarea"
                :rows="3"
                placeholder="请输入商品简介"
              />
              <div class="titTip">公众号分享商品以及PC端SEO优化使用</div>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="商品口令：">
              <el-input
                v-model.trim="formValidate.command_word"
                placeholder="请输入商品口令"
                type="textarea"
                :rows="3"
                class="content_width"
              />
              <div class="titTip">将其他平台的商品口令填写保存，移动端进入商品详情的时候自动复制</div>
            </el-form-item>
          </el-col>

          <el-col :span="24">
            <el-form-item label="商品推荐图：">
              <div class="pictrueBox" @click="modalPicTap('dan', 'recommend_image')">
                <div class="pictrue" v-if="formValidate.recommend_image">
                  <img v-lazy="formValidate.recommend_image" />
                  <el-input v-model.trim="formValidate.recommend_image" style="display: none"></el-input>
                </div>
                <div class="upLoad acea-row row-center-wrapper" v-else>
                  <el-input v-model.trim="formValidate.recommend_image" style="display: none"></el-input>
                  <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                </div>
                <div class="titTip">移动端分类样式3显示的长方形图片，建议比例：5:2</div>
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="自定义表单：">
              <el-switch :active-value="1" :inactive-value="0" v-model="customBtn" @change="customMessBtn" size="large">
                <span slot="open">开启</span>
                <span slot="close">关闭</span>
              </el-switch>
              <div class="addCustom_content" v-if="customBtn">
                <div v-for="(item, index) in formValidate.custom_form" :key="index" class="custom_box">
                  <el-input
                    v-model.trim="item.title"
                    :placeholder="'表单标题' + (index + 1)"
                    style="width: 150px; margin-right: 10px"
                    :maxlength="10"
                  />
                  <el-select v-model="item.label" style="width: 200px; margin-left: 6px; margin-right: 10px">
                    <el-option
                      v-for="items in CustomList"
                      :value="items.value"
                      :key="items.value"
                      :label="items.label"
                    ></el-option>
                  </el-select>
                  <el-checkbox v-model="item.status">必填</el-checkbox>
                  <div class="addfont" @click="delcustom()">删除</div>
                </div>
              </div>
              <div class="addCustomBox" v-show="customBtn">
                <div class="btn" @click="addcustom">+ 添加表单</div>
                <div class="titTip">用户下单时需填写的信息，最多可设置10条，设置了自定义表单的商品不能加入购物车</div>
              </div>
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item>
          <el-button v-if="currentTab !== '1'" @click="upTab">上一步</el-button>
          <el-button
            type="primary"
            class="submission"
            v-if="currentTab !== '6' && formValidate.virtual_type == 0"
            @click="downTab"
            >下一步</el-button
          >
          <el-button
            type="primary"
            class="submission"
            v-if="currentTab !== '5' && formValidate.virtual_type != 0"
            @click="downTab"
            >下一步</el-button
          >
          <el-button
            type="primary"
            :disabled="openSubimit"
            class="submission"
            @click="handleSubmit('formValidate')"
            v-if="($route.params.id || currentTab === '6') && formValidate.virtual_type == 0"
            >保存</el-button
          >
          <el-button
            type="primary"
            :disabled="openSubimit"
            class="submission"
            @click="handleSubmit('formValidate')"
            v-if="($route.params.id || currentTab === '5') && formValidate.virtual_type != 0"
            >保存</el-button
          >
        </el-form-item>
      </el-form>
      <el-dialog :visible.sync="modalPic" width="950px" scrollable title="上传商品图" :close-on-click-modal="false">
        <uploadPictures
          :isChoice="isChoice"
          @getPic="getPic"
          @getPicD="getPicD"
          :gridBtn="gridBtn"
          :gridPic="gridPic"
          v-if="modalPic"
        ></uploadPictures>
      </el-dialog>
      <el-dialog
        :visible.sync="addVirtualModel"
        width="720px"
        title="添加卡密"
        :show-close="true"
        :close-on-click-modal="false"
        @closed="initVirtualData"
      >
        <div class="trip"></div>
        <div class="type-radio">
          <el-form label-width="85px">
            <el-form-item label="卡密类型：">
              <el-radio-group v-model="disk_type" size="large">
                <el-radio :label="1">固定卡密</el-radio>
                <el-radio :label="2">一次性卡密</el-radio>
              </el-radio-group>
              <div v-if="disk_type == 1">
                <div class="stock-disk">
                  <el-input v-model="disk_info" size="large" type="textarea" :rows="4" placeholder="填写卡密信息" />
                </div>
                <div class="stock-input">
                  <!-- <el-input type="number" v-model="stock" size="large" :min='0' placeholder="填写库存数量">
                    <span slot="append">件</span>
                  </el-input> -->
                  <el-input-number :controls="false" :max="100000" :min="1" :step="1" :precision="0" v-model="stock" />
                  <span class="pl10">件</span>
                </div>
              </div>
              <div class="scroll-virtual" v-if="disk_type == 2">
                <div class="virtual-data mb10" v-for="(item, index) in virtualList" :key="index">
                  <span class="mr10 virtual-title">卡号{{ index + 1 }}：</span>
                  <el-input
                    class="mr10"
                    type="text"
                    v-model.trim="item.key"
                    style="width: 150px"
                    placeholder="请输入卡号(非必填)"
                  ></el-input>
                  <span class="mr10 virtual-title">卡密{{ index + 1 }}：</span>
                  <el-input
                    class="mr10"
                    type="text"
                    v-model.trim="item.value"
                    style="width: 150px"
                    placeholder="请输入卡密"
                  ></el-input>
                  <span class="deteal-btn" @click="removeVirtual(index)">删除</span>
                </div>
              </div>
              <div class="add-more" v-if="disk_type == 2">
                <el-button class="h-33" type="primary" @click="handleAdd">新增</el-button>
                <el-upload class="ml10" :action="cardUrl" :data="uploadData" :headers="header" :on-success="upFile" :before-upload="beforeUpload"
 >
                  <el-button>导入卡密</el-button>
                </el-upload>
              </div>
            </el-form-item>
          </el-form>
        </div>
        <span slot="footer" class="dialog-footer">
          <el-button @click="closeVirtual">取 消</el-button>
          <el-button type="primary" @click="upVirtual">确 定</el-button>
        </span>
      </el-dialog>
    </el-card>
    <freightTemplate :template="template" v-on:changeTemplate="changeTemplate" ref="templates"></freightTemplate>
    <add-attr ref="addattr" @getList="userSearchs"></add-attr>
    <coupon-list
      ref="couponTemplates"
      @nameId="nameId"
      :couponids="formValidate.coupon_ids"
      :updateIds="updateIds"
      :updateName="updateName"
    ></coupon-list>
    <coupon-list ref="goodsCoupon" many="one" :luckDraw="true" @getCouponId="goodsCouponId"></coupon-list>
    <!-- 生成淘宝京东表单-->
    <el-dialog
      :visible.sync="modals"
      @closed="cancel"
      class="Box"
      title="复制淘宝、天猫、京东、苏宁、1688"
      :close-on-click-modal="false"
      width="720px"
    >
      <tao-bao ref="taobaos" v-if="modals" @on-close="onClose"></tao-bao>
    </el-dialog>
    <el-dialog :visible.sync="goods_modals" title="商品列表" footerHide class="paymentFooter" scrollable width="1000px">
      <goods-list v-if="goods_modals" ref="goodslist" :ischeckbox="true" @getProductId="getProductId"></goods-list>
    </el-dialog>
    <!-- 用户标签 -->
    <el-dialog
      :visible.sync="labelShow"
      title="请选择用户标签"
      :show-close="true"
      width="540px"
      :close-on-click-modal="false"
    >
      <userLabel ref="userLabel" @activeData="activeData" @close="labelClose"></userLabel>
    </el-dialog>
  </div>
</template>

<script>
import userLabel from '@/components/labelList';
import { mapState } from 'vuex';
import vuedraggable from 'vuedraggable';
import uploadPictures from '@/components/uploadPictures';
import freightTemplate from '@/components/freightTemplate';
import couponList from '@/components/couponList';
import addAttr from '../productAttr/addAttr';
import goodsList from '@/components/goodsList/index';
import taoBao from './taoBao';
import WangEditor from '@/components/wangEditor/index.vue';
import { userLabelAddApi } from '@/api/user';
import {
  productInfoApi,
  cascaderListApi,
  productAddApi,
  generateAttrApi,
  productGetRuleApi,
  productGetTemplateApi,
  productGetTempKeysApi,
  checkActivityApi,
  productCache,
  cacheDelete,
  uploadType,
  importCard,
  productCreateApi,
} from '@/api/product';
import Setting from '@/setting';
import { getCookies } from '@/libs/util';
import { uploadByPieces } from '@/utils/upload'; //引入uploadByPieces方法
import { isFileUpload, isVideoUpload } from '@/utils';
import checkArray from '@/libs/permission';

export default {
  name: 'product_productAdd',
  components: {
    // VueUeditorWrap,
    uploadPictures,
    freightTemplate,
    addAttr,
    couponList,
    taoBao,
    draggable: vuedraggable,
    goodsList,
    WangEditor,
    userLabel,
  },
  data() {
    return {
      labelShow: false,
      dataLabel: [],
      headTab: [
        { tit: '基础信息', name: '1' },
        { tit: '规格库存', name: '2' },
        { tit: '商品详情', name: '3' },
        { tit: '物流设置', name: '4' },
        { tit: '营销设置', name: '5' },
        { tit: '其他设置', name: '6' },
      ],
      virtual: [
        { tit: '普通商品', id: 0, tit2: '物流发货' },
        { tit: '卡密/网盘', id: 1, tit2: '自动发货' },
        { tit: '优惠券', id: 2, tit2: '自动发货' },
        { tit: '虚拟商品', id: 3, tit2: '虚拟发货' },
      ],
      seletVideo: 0, //选择视频类型
      customBtn: false, //自定义留言开关
      content: '',
      contents: '',
      fileUrl: Setting.apiBaseURL + '/file/upload',
      fileUrl2: Setting.apiBaseURL + '/file/video_upload',
      cardUrl: Setting.apiBaseURL + '/file/upload/1',
      upload_type: '', //视频上传类型 1 本地上传 2 3 4 OSS上传
      uploadData: {}, // 上传参数
      header: {},

      type: 0,
      modals: false,
      goods_modals: false,
      spinShow: false,
      openSubimit: false,
      virtualData: '',
      virtualList: [
        {
          key: '',
          value: '',
        },
      ],
      grid2: {
        xl: 10,
        lg: 12,
        md: 12,
        sm: 24,
        xs: 24,
      },
      grid3: {
        xl: 18,
        lg: 18,
        md: 20,
        sm: 24,
        xs: 24,
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
          volume: 0,
          virtual_list: [],
        },
      ],
      // 规格数据
      formDynamic: {
        attrsName: '',
        attrsVal: '',
      },
      disk_type: 1, //卡密类型
      tabIndex: 0,
      tabName: '',
      formDynamicNameData: [],
      isBtn: false,
      columns2: [
        {
          title: '图片',
          slot: 'pic',
          align: 'center',
          minWidth: 80,
        },
        {
          title: '售价',
          slot: 'price',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '成本价',
          slot: 'cost',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '原价',
          slot: 'ot_price',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '库存',
          slot: 'stock',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '商品编号',
          slot: 'bar_code',
          align: 'center',
          minWidth: 120,
        },
        {
          title: '重量（KG）',
          slot: 'weight',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '体积(m³)',
          slot: 'volume',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          align: 'center',
          minWidth: 120,
        },
      ],
      columns3: [
        {
          title: '图片',
          slot: 'pic',
          align: 'center',
          minWidth: 80,
        },
        {
          title: '售价',
          slot: 'price',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '成本价',
          slot: 'cost',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '原价',
          slot: 'ot_price',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '库存',
          slot: 'stock',
          align: 'center',
          minWidth: 95,
        },
        {
          title: '商品编号',
          slot: 'bar_code',
          align: 'center',
          minWidth: 120,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          align: 'center',
          minWidth: 120,
        },
      ],
      columns: [],
      columnsInstall: [],
      columnsInstal2: [],
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
      //自定义留言下拉选择
      CustomList: [
        {
          value: 'text',
          label: '文本框',
        },
        {
          value: 'number',
          label: '数字',
        },
        {
          value: 'email',
          label: '邮件',
        },
        {
          value: 'data',
          label: '日期',
        },
        {
          value: 'time',
          label: '时间',
        },
        {
          value: 'id',
          label: '身份证',
        },
        {
          value: 'phone',
          label: '手机号',
        },
        {
          value: 'img',
          label: '图片',
        },
      ],
      customess: {
        content: [],
      }, //自定义留言内容

      formValidate: {
        disk_info: '', //卡密类型
        logistics: ['1'], //选择物流方式
        freight: 2, //运费设置
        postage: 0, //设置运费金额
        recommend: [], //商品推荐
        presale_day: 1, //预售发货时间-结束
        presale: false, //预售商品开关
        is_limit: false,
        limit_type: 0,
        limit_num: 0,
        video_open: false, //视频按钮是否显示
        vip_product: false, //付费会员专属开关
        custom_form: [], //自定义留言
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
        recommend_list: [],
        virtual_type: 0,
        // is_sub: 0,
        id: 0,
        spec_type: 0,
        is_virtual: 0,
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
            bar_code: '',
          },
        ],
        activity: ['默认', '秒杀', '砍价', '拼团'],
        couponName: [],
        header: [],
        selectRule: '',
        coupon_ids: [],
        command_word: '',
        min_qty: 1,
      },
      ruleList: [],
      templateList: [],
      createBnt: true,
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
          brokerage: 0,
          brokerage_two: 0,
          vip_price: 0,
          virtual_list: [],
          coupon_id: 0,
        },
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
        xs: 24,
      },
      loading: false,
      modalPic: false,
      addVirtualModel: false,
      template: false,
      uploadList: [],
      treeSelect: [],
      picTit: '',
      tableIndex: 0,
      ruleValidate: {
        store_name: [{ required: true, message: '请输入商品名称', trigger: 'blur' }],
        cate_id: [
          {
            required: true,
            message: '请选择商品分类',
            trigger: 'change',
            type: 'array',
            min: '1',
          },
        ],
        unit_name: [{ required: true, message: '请输入单位', trigger: 'blur' }],
        // image: [{ required: true, message: "请上传商品图", trigger: "change" }],
        slider_image: [
          {
            required: true,
            message: '请上传商品轮播图',
            type: 'array',
            trigger: 'change',
          },
        ],
        spec_type: [{ required: true, message: '请选择商品规格', trigger: 'change' }],
        is_virtual: [{ required: true, message: '请选择商品类型', trigger: 'change' }],
        selectRule: [{ required: true, message: '请选择商品规格属性', trigger: 'change' }],
        temp_id: [
          {
            required: true,
            message: '请选择运费模板',
            trigger: 'change',
            type: 'number',
          },
        ],
        presale_time: [
          {
            required: true,
            type: 'array',
            message: '请选择活动时间',
            trigger: 'change',
          },
        ],
        logistics: [
          {
            required: true,
            type: 'array',
            min: 1,
            message: '请选择物流方式',
            trigger: 'change',
          },
          {
            type: 'array',
            max: 2,
            message: '请选择物流方式',
            trigger: 'change',
          },
        ],
        give_integral: [{ type: 'integer', message: '请输入整数' }],
      },
      manyBrokerage: 0,
      manyBrokerageTwo: 0,
      manyVipPrice: 0,
      upload: {
        videoIng: false, // 是否显示进度条；
      },
      videoIng: false, // 是否显示进度条；
      progress: 0, // 进度条默认0
      stock: 0,
      disk_info: '',
      videoLink: '',
      attrs: [],
      activity: { 默认: 'red', 秒杀: 'blue', 砍价: 'green', 拼团: 'yellow' },
      couponName: [],
      updateIds: [],
      updateName: [],
      couponIds: '',
      couponNames: [],
      rakeBack: [
        {
          title: '一级返佣',
          slot: 'brokerage',
          align: 'center',
          width: 95,
        },
        {
          title: '二级返佣',
          slot: 'brokerage_two',
          align: 'center',
          width: 95,
        },
      ],
      member: [
        {
          title: '会员价',
          slot: 'vip_price',
          align: 'center',
          width: 95,
        },
      ],
      columnsInstalM: [],
      moveIndex: ''
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '120px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
    labelBottom() {
      return this.isMobile ? undefined : '15px';
    },
  },
  beforeRouteUpdate(to, from, next) {
    this.bus.$emit('onTagsViewRefreshRouterView', this.$route.path);
    next();
  },
  created() {
    this.columns = this.columns2.slice(0, 8);
    this.getToken();

    // this.columnsInstall = this.columns2.slice(0, 4).concat(this.columnsInstall);
    // this.columnsInsta8 = this.columns2.slice(0, 4).concat(this.columnsInsta8);
  },
  mounted() {
    if (this.$route.params.id !== '0' && this.$route.params.id) {
      this.getInfo();
    } else if (this.$route.params.id === '0') {
      productCache()
        .then((res) => {
          let data = res.data.info;
          if (!Array.isArray(data)) {
            let cate_id = data.cate_id.map(Number);
            let label_id = data.label_id.map(Number);
            this.attrs = data.items || [];
            let ids = [];
            // let names = [];
            if (data.coupons) {
              data.coupons.map((item) => {
                ids.push(item.id);
                // names.push(item.title);
              });
              this.couponName = data.coupons;
            }

            this.formValidate = data;
            // this.couponName = data.coupons;
            // that.couponName = names;
            this.dataLabel = data.label_id;
            this.formValidate.coupon_ids = ids;
            this.updateIds = ids;
            this.updateName = data.coupons;
            this.formValidate.cate_id = cate_id;
            // this.formValidate.label_id = label_id;
            this.oneFormValidate = data.attrs;
            this.formValidate.logistics = data.logistics || ['1'];
            this.formValidate.header = [];
            this.generate(0);
            this.manyFormValidate = data.attrs;
            this.spec_type = data.spec_type;
            this.formValidate.is_virtual = data.is_virtual;
            this.formValidate.custom_form = data.custom_form || [];
            if (this.formValidate.custom_form.length != 0) {
              this.customBtn = true;
            }
            this.virtualbtn(data.virtual_type, 1);
            if (data.spec_type === 0) {
              this.manyFormValidate = [];
            } else {
              this.createBnt = true;
              this.oneFormValidate = [
                {
                  pic: data.image,
                  price: 0,
                  cost: 0,
                  ot_price: 0,
                  stock: 0,
                  bar_code: '',
                  weight: 0,
                  volume: 0,
                  brokerage: 0,
                  brokerage_two: 0,
                  vip_price: 0,
                  virtual_list: [],
                  coupon_id: 0,
                },
              ];
            }
            this.watchActivity()
            this.spinShow = false;
          }
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    }
    if (this.$route.query.type) {
      this.modals = true;
      this.type = this.$route.query.type;
    } else {
      this.type = 0;
    }
    this.goodsCategory();
    this.productGetRule();
    this.productGetTemplate();
    // this.userLabel();
    this.uploadType();
    this.watchActivity()
  },
  methods: {
    beforeUpload(file) {
      return isFileUpload(file)
    },
    // 分片上传
    videoSaveToUrl(file) {
      if(isVideoUpload(file)){
        uploadByPieces({
          file: file, // 视频实体
          pieceSize: 3, // 分片大小
          success: (data) => {
            this.formValidate.video_link = data.file_path;
            this.progress = 100;
          },
          error: (e) => {
            this.$message.error(e.msg);
          },
          uploading: (chunk, allChunk) => {
            this.videoIng = true;
            let st = Math.floor((chunk / allChunk) * 100);
            this.progress = st;
          },
        });
      }
      return false;
    },
    // 类型选择/填入内容判断
    virtualbtn(index, type) {
      if (type != 1) {
        this.formValidate.is_sub = [];
        let id = this.$route.params.id;
        if (id) {
          checkActivityApi(id)
            .then((res) => {})
            .catch((res) => {
              this.formValidate.spec_type = this.spec_type;
              this.$message.error(res.msg);
            });
        } else {
          if (this.formValidate.spec_type == 1) {
            this.generate(1);
          }
        }
      }
      switch (index) {
        case 0:
          this.formValidate.virtual_type = 0;
          this.formValidate.is_virtual = 0;
          this.headTab = [
            { tit: '基础信息', name: '1' },
            { tit: '规格库存', name: '2' },
            { tit: '商品详情', name: '3' },
            { tit: '物流设置', name: '4' },
            { tit: '营销设置', name: '5' },
            { tit: '其他设置', name: '6' },
          ];
          break;
        case 1:
          this.formValidate.virtual_type = 1;
          this.formValidate.postage = 0;
          this.formValidate.is_virtual = 1;
          this.headTab = [
            { tit: '基础信息', name: '1' },
            { tit: '规格库存', name: '2' },
            { tit: '商品详情', name: '3' },
            // { tit: "物流设置", name: "4" },
            { tit: '营销设置', name: '4' },
            { tit: '其他设置', name: '5' },
          ];
          break;
        case 2:
          this.formValidate.virtual_type = 2;
          this.formValidate.is_virtual = 1;
          this.headTab = [
            { tit: '基础信息', name: '1' },
            { tit: '规格库存', name: '2' },
            { tit: '商品详情', name: '3' },
            // { tit: "物流设置", name: "4" },
            { tit: '营销设置', name: '4' },
            { tit: '其他设置', name: '5' },
          ];
          break;
        case 3:
          this.formValidate.virtual_type = 3;
          this.formValidate.is_virtual = 1;
          this.headTab = [
            { tit: '基础信息', name: '1' },
            { tit: '规格库存', name: '2' },
            { tit: '商品详情', name: '3' },
            // { tit: "物流设置", name: "4" },
            { tit: '营销设置', name: '4' },
            { tit: '其他设置', name: '5' },
          ];
          break;
      }
    },
    // 新增分类
    addCate() {
      this.$modalForm(productCreateApi()).then(() => this.goodsCategory());
    },
    // 物流方式选择
    logisticsBtn(e) {
      this.formValidate.logistics = e;
    },
    // 新增标签
    addLabel() {
      this.$modalForm(userLabelAddApi(0)).then(() => this.userLabel());
    },
    // 自定义留言 开启关闭
    customMessBtn(e) {
      if (!e) {
        this.formValidate.custom_form = [];
      }
    },
    // 自定义留言 新增表单
    addcustom() {
      if (this.formValidate.custom_form.length > 9) {
        this.$message.warning('最多添加10条');
      } else {
        this.formValidate.custom_form.push({
          title: '',
          label: 'text',
          value: '',
          status: false,
        });
      }
    },
    // 删除
    delcustom(index) {
      this.formValidate.custom_form.splice(index, 1);
    },
    // 预售具体日期
    onchangeTime(e) {
      this.formValidate.presale_time = e;
    },
    // 商品详情
    getEditorContent(data) {
      this.content = data;
    },
    cancel() {
      this.modals = false;
    },
    // 上传头部token
    getToken() {
      this.header['Authori-zation'] = 'Bearer ' + getCookies('token');
    },
    // 导入卡密
    upFile(res) {
      importCard({ file: res.data.src }).then((res) => {
        this.virtualList = this.virtualList.concat(res.data);
      });
    },
    //获取视频上传类型
    uploadType() {
      uploadType().then((res) => {
        this.upload_type = res.data.upload_type;
      });
    },
    // 初始化数据展示
    infoData(data, isCopy) {
      let cate_id = data.cate_id.map(Number);
      let label_id = data.label_id.map(Number);
      this.attrs = data.items || [];
      let ids = [];
      data.coupons.map((item) => {
        ids.push(item.id);
      });
      this.formValidate = data;
      this.seletVideo = data.seletVideo;
      this.contents = data.description;
      this.couponName = data.coupons;
      this.formValidate.coupon_ids = ids;
      this.updateIds = ids;
      this.dataLabel = data.label_id;
      this.updateName = data.coupons;
      this.virtualbtn(data.virtual_type, 1);
      this.formValidate.logistics = data.logistics || ['1'];
      this.formValidate.custom_form = data.custom_form || [];
      if (this.formValidate.custom_form.length != 0) {
        this.customBtn = true;
      }
      this.formValidate.cate_id = cate_id;
      if (data.attr) {
        this.oneFormValidate = [data.attr];
      }
      this.formValidate.header = [];
      this.generate(0, isCopy, data.attrs);
      // this.manyFormValidate = data.attrs;
      // this.$set(this, 'manyFormValidate', data.attrs);
      this.spec_type = data.spec_type;
      this.formValidate.is_virtual = data.is_virtual;
      if (data.spec_type === 0) {
        this.manyFormValidate = [];
      } else {
        this.createBnt = true;
        this.oneFormValidate = [
          {
            pic: '',
            price: 0,
            cost: 0,
            ot_price: 0,
            stock: 0,
            bar_code: '',
            weight: 0,
            volume: 0,
            brokerage: 0,
            brokerage_two: 0,
            vip_price: 0,
            virtual_list: [],
            coupon_id: 0,
          },
        ];
      }
      this.watchActivity()
    },
    //关闭淘宝弹窗并生成数据；
    onClose(data) {
      this.modals = false;
      this.infoData(data, 1);
    },

    checkMove(evt) {
      this.moveIndex = evt.draggedContext.index;
    },
    end() {
      this.moveIndex = '';
      this.generate(1);
    },
    // 单独设置会员设置
    checkAllGroupChange(data) {
      this.checkAllGroup(data);
    },
    checkAllGroup(data) {
      if (this.formValidate.spec_type === 0) {
        if (data.indexOf(0) > -1) {
          this.columnsInstall = this.columns2.slice(0, 4).concat(this.member);
        } else if (data.indexOf(1) > -1) {
          this.columnsInstall = this.columns2.slice(0, 4).concat(this.rakeBack);
        } else {
          this.columnsInstall = this.columns2.slice(0, 4);
        }
        if (data.length === 2) {
          this.columnsInstall = this.columns2.slice(0, 4).concat(this.rakeBack).concat(this.member);
        }
      } else {
        if (data.indexOf(0) > -1) {
          this.columnsInstal2 = this.columnsInstalM.slice(0, 4).concat(this.member);
        } else if (data.indexOf(1) > -1) {
          this.columnsInstal2 = this.columnsInstalM.slice(0, 4).concat(this.rakeBack);
        } else {
          this.columnsInstal2 = this.columnsInstalM.slice(0, 4);
        }
        if (data.length === 2) {
          this.columnsInstal2 = this.columnsInstalM.slice(0, 4).concat(this.rakeBack).concat(this.member);
        }
      }
    },
    // 添加优惠券
    addCoupon() {
      this.$refs.couponTemplates.isTemplate = true;
      this.$refs.couponTemplates.tableList();
    },
    // 规格中优惠券查看
    see(data, name, index) {
      this.tabName = name;
      this.tabIndex = index;

      if (this.formValidate.virtual_type === 1) {
        if (data.disk_info != '') {
          this.disk_type = 1;
          this.disk_info = data.disk_info;
          this.stock = data.stock;
        } else if (data.virtual_list.length) {
          this.disk_type = 2;
          this.virtualList = data.virtual_list;
        }
        this.addVirtualModel = true;
      } else {
        this.$refs.goodsCoupon.isTemplate = true;
        this.$refs.goodsCoupon.tableList(3);
      }
    },
    // 添加优惠券
    addGoodsCoupon(index, name) {
      this.tabIndex = index;
      this.tabName = name;
      this.$refs.goodsCoupon.isTemplate = true;
      this.$refs.goodsCoupon.tableList(3);
    },
    addVirtual(index, name) {
      this.tabIndex = index;
      this.tabName = name;
      this.addVirtualModel = true;
    },
    // 提交卡密信息
    upVirtual() {
      if (this.disk_type == 2) {
        for (let i = 0; i < this.virtualList.length; i++) {
          const element = this.virtualList[i];
          if (!element.value) {
            this.$message.error('请输入所有卡密');
            return;
          }
        }
        this.$set(this[this.tabName][this.tabIndex], 'virtual_list', this.virtualList);
        this.$set(this[this.tabName][this.tabIndex], 'stock', this.virtualList.length);
        this.virtualList = [
          {
            key: '',
            value: '',
          },
        ];
        this.$set(this[this.tabName][this.tabIndex], 'disk_info', '');
      } else {
        if (!this.disk_info.length) {
          return this.$message.error('请填写卡密信息');
        }
        if (!this.stock) {
          return this.$message.error('请填写库存数量');
        }
        this.$set(this[this.tabName][this.tabIndex], 'stock', Number(this.stock));
        this.$set(this[this.tabName][this.tabIndex], 'stock', Number(this.stock));
        this.$set(this[this.tabName][this.tabIndex], 'disk_info', this.disk_info);
        this.$set(this[this.tabName][this.tabIndex], 'virtual_list', []);
      }
      this.addVirtualModel = false;
      this.closeVirtual();
    },
    //  初始化卡密数据信息
    closeVirtual() {
      this.addVirtualModel = false;
      this.virtualList = [
        {
          key: '',
          value: '',
        },
      ];
      this.disk_info = '';
      this.stock = 0;
    },
    //对象数组去重；
    unique(arr) {
      const res = new Map();
      return arr.filter((arr) => !res.has(arr.id) && res.set(arr.id, 1));
    },
    // 获取优惠券id数据
    nameId(id, names) {
      this.formValidate.coupon_ids = id;
      this.couponName = this.unique(names);
    },
    // 获取优惠券信息
    goodsCouponId(data) {
      // this[this.tabName][this.tabIndex].coupon_id = data.id;
      // this[this.tabName][this.tabIndex].coupon_name = data.title;
      this.$set(this[this.tabName][this.tabIndex], 'coupon_id', data.id);
      this.$set(this[this.tabName][this.tabIndex], 'coupon_name', data.title);
      this.$refs.goodsCoupon.isTemplate = false;
    },
    handleClose(name) {
      let index = this.couponName.indexOf(name);
      this.couponName.splice(index, 1);
      let couponIds = this.formValidate.coupon_ids;
      couponIds.splice(index, 1);
      this.updateIds = couponIds;
      this.updateName = this.couponName;
    },
    // 运费模板
    getList() {
      this.productGetTemplate();
    },
    // 添加运费模板
    addTemp() {
      this.$refs.templates.isTemplate = true;
    },
    // 删除视频；
    delVideo() {
      let that = this;
      that.$set(that.formValidate, 'video_link', '');
      that.$set(that, 'progress', 0);
      that.videoIng = false;
      that.upload.videoIng = false;
    },
    zh_uploadFile() {
      if (this.seletVideo == 1) {
        this.formValidate.video_link = this.videoLink;
      } else {
        this.$refs.refid.click();
      }
    },
    // 上传视频
    zh_uploadFile_change(evfile) {
      let that = this;
      let suffix = evfile.target.files[0].name.substr(evfile.target.files[0].name.indexOf('.'));
      if (suffix.indexOf('.mp4') === -1) {
        return that.$message.error('只能上传MP4文件');
      }
      let types = {
        key: evfile.target.files[0].name,
        contentType: evfile.target.files[0].type,
      };
      productGetTempKeysApi(types)
        .then((res) => {
          that.$videoCloud
            .videoUpload({
              type: res.data.type,
              evfile: evfile,
              res: res,
              uploading(status, progress) {
                that.upload.videoIng = status;
                if (res.status == 200) {
                  that.progress = 100;
                }
              },
            })
            .then((res) => {
              that.formValidate.video_link = res.url;
              that.$message.success('视频上传成功');
              that.upload.videoIng = false;
            })
            .catch((res) => {
              that.$message.error(res);
            });
        })
        .catch((res) => {
          that.$message.error(res.msg);
        });
    },
    // 上一页；
    upTab() {
      this.currentTab = (Number(this.currentTab) - 1).toString();
    },
    // 下一页；
    downTab() {
      this.currentTab = (Number(this.currentTab) + 1).toString();
    },
    // 属性弹窗回调函数；
    userSearchs() {
      this.productGetRule();
    },
    // 添加规则；
    addRule() {
      this.$refs.addattr.modal = true;
    },
    // 批量设置分佣；
    brokerageSetUp() {
      let that = this;
      if (that.formValidate.is_sub.indexOf(1) > -1) {
        if (that.manyBrokerage <= 0 || that.manyBrokerageTwo <= 0) {
          return that.$message.error('请填写返佣金额后进行批量添加');
        }
      } else if (that.formValidate.is_sub.indexOf(0) > -1) {
        if (that.manyVipPrice <= 0) {
          return that.$message.error('请填写会员价后进行批量添加');
        }
      }
      if (this.formValidate.is_sub.length === 2) {
        if (that.manyBrokerage <= 0 || that.manyBrokerageTwo <= 0 || that.manyVipPrice <= 0) {
          return that.$message.error('请填写完金额后进行批量添加');
        }
      }
      for (let val of that.manyFormValidate) {
        this.$set(val, 'brokerage', that.manyBrokerage);
        this.$set(val, 'brokerage_two', that.manyBrokerageTwo);
        this.$set(val, 'vip_price', that.manyVipPrice);
      }
    },
    // 批量设置会员价
    vipPriceSetUp() {
      let that = this;
      if (that.manyVipPrice <= 0) {
        return that.$message.error('请填写会员价在进行批量添加');
      } else {
        for (let val of that.manyFormValidate) {
          this.$set(val, 'vip_price', that.manyVipPrice);
        }
      }
    },
    // 新增卡密
    handleAdd() {
      this.virtualList.push({
        key: '',
        value: '',
      });
    },
    // 初始化卡密信息
    initVirtualData(status) {
      this.virtualList = [
        {
          key: '',
          value: '',
        },
      ];
    },
    removeVirtual(index) {
      this.virtualList.splice(index, 1);
    },
    // 清空批量规格信息
    batchDel() {
      this.oneFormBatch = [
        {
          pic: '',
          price: 0,
          cost: 0,
          ot_price: 0,
          stock: 0,
          bar_code: '',
          weight: 0,
          volume: 0,
          virtual_list: [],
        },
      ];
    },
    confirm() {
      let that = this;
      that.createBnt = true;
      if (that.formValidate.selectRule.trim().length <= 0) {
        return that.$message.error('请选择属性');
      }
      that.ruleList.forEach(function (item, index) {
        if (item.rule_name === that.formValidate.selectRule) {
          that.attrs = item.rule_value;
        }
      });
    },
    // 获取商品属性模板；
    productGetRule() {
      productGetRuleApi().then((res) => {
        this.ruleList = res.data;
      });
    },
    // 获取运费模板；
    productGetTemplate() {
      productGetTemplateApi().then((res) => {
        this.templateList = res.data;
      });
    },
    // 删除表格中的属性
    delAttrTable(index) {
      let id = this.$route.params.id;
      if (id) {
        checkActivityApi(id)
          .then((res) => {
            this.manyFormValidate.splice(index, 1);
            this.$message.success(res.msg);
          })
          .catch((res) => {
            this.$message.error(res.msg);
          });
      } else {
        this.manyFormValidate.splice(index, 1);
      }
    },
    // 批量添加
    batchAdd() {
      for (let val of this.manyFormValidate) {
        if (this.oneFormBatch[0].pic) {
          this.$set(val, 'pic', this.oneFormBatch[0].pic);
        }
        if (this.oneFormBatch[0].price > 0) {
          this.$set(val, 'price', this.oneFormBatch[0].price);
        }
        if (this.oneFormBatch[0].cost > 0) {
          this.$set(val, 'cost', this.oneFormBatch[0].cost);
        }
        if (this.oneFormBatch[0].ot_price > 0) {
          this.$set(val, 'ot_price', this.oneFormBatch[0].ot_price);
        }
        if (this.oneFormBatch[0].stock > 0) {
          this.$set(val, 'stock', this.oneFormBatch[0].stock);
        }
        if (this.oneFormBatch[0].bar_code !== '') {
          this.$set(val, 'bar_code', this.oneFormBatch[0].bar_code);
        }
        if (this.oneFormBatch[0].weight > 0) {
          this.$set(val, 'weight', this.oneFormBatch[0].weight);
        }
        if (this.oneFormBatch[0].volume > 0) {
          this.$set(val, 'volume', this.oneFormBatch[0].volume);
        }
      }
    },
    // 添加按钮
    addBtn() {
      this.clearAttr();
      this.createBnt = false;
      this.showIput = true;
    },
    // 立即生成
    generate(type, isCopy, arr) {
      generateAttrApi(
        {
          attrs: this.attrs,
          is_virtual: [1, 2].includes(this.formValidate.virtual_type) ? 1 : 0,
          virtual_type: this.formValidate.virtual_type,
        },
        this.formValidate.id,
        type,
      )
        .then((res) => {
          let info = res.data.info,
            header1 = JSON.parse(JSON.stringify(info.header));
          if (this.$route.params.id !== '0' && (this.$route.query.type != -1 || type) && !isCopy) {
            this.manyFormValidate = info.value;
          }
          if (isCopy) {
            this.manyFormValidate = arr;
          }
          let header = info.header;
          if ([1, 2].includes(this.formValidate.virtual_type)) {
            this.columnsInstalM = header;
            this.formValidate.header = header;
          } else {
            this.formValidate.header = header1;
            this.columnsInstalM = info.header;
          }
          this.checkAllGroup(this.formValidate.is_sub);
          if (!this.$route.params.id && this.formValidate.spec_type === 1 && !isCopy) {
            this.manyFormValidate.map((item) => {
              item.pic = this.formValidate.image;
            });
            this.oneFormBatch[0].pic = this.formValidate.image;
          } else if (this.$route.params.id) {
            this.manyFormValidate.map((item) => {
              if (!item.pic) {
                item.pic = this.formValidate.image;
              }
            });
            this.oneFormBatch[0].pic = this.formValidate.image;
          }
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 取消
    offAttrName() {
      this.showIput = false;
      this.createBnt = true;
    },
    clearAttr() {
      this.formDynamic.attrsName = '';
      this.formDynamic.attrsVal = '';
    },
    // 删除规格
    handleRemoveRole(index) {
      this.attrs.splice(index, 1);
      this.manyFormValidate.splice(index, 1);
    },
    // 删除属性
    handleRemove2(item, index) {
      item.splice(index, 1);
    },
    // 添加规则名称
    createAttrName() {
      if (this.formDynamic.attrsName && this.formDynamic.attrsVal) {
        let data = {
          value: this.formDynamic.attrsName,
          detail: [this.formDynamic.attrsVal],
        };
        this.attrs.push(data);
        var hash = {};
        this.attrs = this.attrs.reduce(function (item, next) {
          /* eslint-disable */
          hash[next.value] ? '' : (hash[next.value] = true && item.push(next));
          return item;
        }, []);
        this.clearAttr();
        this.showIput = false;
        this.createBnt = true;
      } else {
        this.$message.warning('请添加完整的规格！');
      }
    },
    // 添加属性
    createAttr(num, idx) {
      if (num) {
        this.attrs[idx].detail.push(num);
        var hash = {};
        this.attrs[idx].detail = this.attrs[idx].detail.reduce(function (item, next) {
          /* eslint-disable */
          hash[next] ? '' : (hash[next] = true && item.push(next));
          return item;
        }, []);
      } else {
        this.$message.warning('请添加属性');
      }
    },
    // 商品分类；
    goodsCategory() {
      cascaderListApi(1)
        .then((res) => {
          this.treeSelect = res.data;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    //视视上传类型
    changeVideo(e) {
      this.formValidate.video_link = '';
      this.videoLink = '';
    },
    // 改变规格
    changeSpec() {
      this.formValidate.is_sub = [];
      let id = this.$route.params.id;
      if (id) {
        checkActivityApi(id)
          .then((res) => {})
          .catch((res) => {
            this.formValidate.spec_type = this.spec_type;
            this.$message.error(res.msg);
          });
      }
    },
    // 详情
    getInfo() {
      let that = this;
      that.spinShow = true;
      productInfoApi(that.$route.params.id)
        .then(async (res) => {
          let data = res.data.productInfo;
          this.infoData(data);
          this.spinShow = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.$message.error(res.msg);
        });
    },
    handleRemove(i) {
      this.images.splice(i, 1);
      this.formValidate.slider_image.splice(i, 1);
      this.oneFormValidate[0].pic = this.formValidate.slider_image[0];
    },
    // 关闭图片上传模态框
    changeCancel(msg) {
      this.modalPic = false;
    },
    // 点击商品图
    modalPicTap(tit, picTit, index) {
      this.modalPic = true;
      this.isChoice = tit === 'dan' ? '单选' : '多选';
      this.picTit = picTit;
      this.tableIndex = index;
    },
    // 获取单张图片信息
    getPic(pc) {
      switch (this.picTit) {
        case 'danFrom':
          this.formValidate.image = pc.att_dir;
          if (!this.$route.params.id) {
            if (this.formValidate.spec_type === 0) {
              this.oneFormValidate[0].pic = pc.att_dir;
            } else {
              this.manyFormValidate.map((item) => {
                item.pic = pc.att_dir;
              });
              this.oneFormBatch[0].pic = pc.att_dir;
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
          this.formValidate.recommend_image = pc.att_dir;
          break;
        default:
          this.manyFormValidate[this.tableIndex].pic = pc.att_dir;
      }
      this.modalPic = false;
    },
    // 获取多张图信息
    getPicD(pc) {
      this.images = pc;
      this.images.map((item) => {
        this.formValidate.slider_image.push(item.att_dir);
        this.formValidate.slider_image = this.formValidate.slider_image.splice(0, 10);
      });
      this.oneFormValidate[0].pic = this.formValidate.slider_image[0];
      this.modalPic = false;
    },
    // 提交
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.formValidate.type = this.type;
          if (this.formValidate.spec_type === 0) {
            this.formValidate.attrs = this.oneFormValidate;
            this.formValidate.header = [];
            this.formValidate.items = [];
            this.formValidate.is_copy = 0;
          } else {
            this.formValidate.items = this.attrs;
            this.formValidate.attrs = this.manyFormValidate;
            this.formValidate.is_copy = 1;
          }
          if (this.formValidate.spec_type === 1 && this.manyFormValidate.length === 0) {
            return this.$message.warning('商品信息-请点击生成多规格');
            // return this.$message.warning('请点击生成规格！');
          }
          let item = this.formValidate.attrs;
          for (let i = 0; i < item.length; i++) {
            if (item[i].stock > 1000000) {
              return this.$message.error('规格库存-库存超出系统范围(1000000)');
            }
          }
          if (this.formValidate.is_sub[0] === 1) {
            for (let i = 0; i < item.length; i++) {
              if (item[i].brokerage === null || item[i].brokerage_two === null) {
                return this.$message.error('营销设置- 一二级返佣不能为空');
              }
            }
          } else {
            for (let i = 0; i < item.length; i++) {
              if (item[i].vip_price === null) {
                return this.$message.error('营销设置-会员价不能为空');
              }
            }
          }
          if (this.formValidate.is_sub.length === 2) {
            for (let i = 0; i < item.length; i++) {
              if (item[i].brokerage === null || item[i].brokerage_two === null || item[i].vip_price === null) {
                return this.$message.error('营销设置- 一二级返佣和会员价不能为空');
              }
            }
          }
          if (this.formValidate.freight == 3 && !this.formValidate.temp_id) {
            return this.$message.warning('商品信息-运费模板不能为空');
          }
          let activeIds = [];
          this.dataLabel.forEach((item) => {
            activeIds.push(item.id);
          });
          this.formValidate.label_id = activeIds;
          if (this.openSubimit) return;
          this.openSubimit = true;
          this.formValidate.description = this.formatRichText(this.content);
          productAddApi(this.formValidate)
            .then(async (res) => {
              this.openSubimit = false;
              this.$message.success(res.msg);
              if (this.$route.params.id === '0') {
                cacheDelete().catch((err) => {
                  this.$message.error(err.msg);
                });
              }
              setTimeout(() => {
                this.openSubimit = false;
                this.$router.push({ path: this.$routeProStr + '/product/product_list' });
              }, 500);
            })
            .catch((res) => {
              setTimeout((e) => {
                this.openSubimit = false;
              }, 1000);
              this.$message.error(res.msg);
            });
        } else {
          if (!this.formValidate.store_name) {
            return this.$message.warning('商品信息-商品名称不能为空');
          } else if (!this.formValidate.cate_id.length) {
            return this.$message.warning('商品信息-商品分类不能为空');
          } else if (!this.formValidate.unit_name) {
            return this.$message.warning('商品信息-商品单位不能为空');
          } else if (!this.formValidate.slider_image.length) {
            return this.$message.warning('商品信息-商品轮播图不能为空');
          } else if (!this.formValidate.logistics.length && !this.formValidate.virtual_type) {
            return this.$message.warning('物流设置-至少选择一种物流方式');
          } else if (!this.formValidate.temp_id && this.formValidate.freight == 3) {
            return this.$message.warning('商品信息-运费模板不能为空');
          }
        }
      });
    },
    changeTemplate(msg) {
      this.template = msg;
    },
    // 表单验证
    validate(prop, status, error) {
      if (status === false) {
        this.$message.warning(error);
      }
    },
    // 移动
    handleDragStart(e, item) {
      this.dragging = item;
    },
    handleDragEnd(e, item) {
      this.dragging = null;
    },
    handleDragOver(e) {
      e.dataTransfer.dropEffect = 'move';
    },
    handleDragEnter(e, item) {
      e.dataTransfer.effectAllowed = 'move';
      if (item === this.dragging) {
        return;
      }
      const newItems = [...this.formValidate.slider_image];
      const src = newItems.indexOf(this.dragging);
      const dst = newItems.indexOf(item);
      newItems.splice(dst, 0, ...newItems.splice(src, 1));
      this.formValidate.slider_image = newItems;
    },
    // 过滤详情内容
    formatRichText(html) {
      let newContent = html.replace(/<img[^>]*>/gi, function (match, capture) {
        match = match.replace(/style="[^"]+"/gi, '').replace(/style='[^']+'/gi, '');
        match = match.replace(/width="[^"]+"/gi, '').replace(/width='[^']+'/gi, '');
        match = match.replace(/height="[^"]+"/gi, '').replace(/height='[^']+'/gi, '');
        return match;
      });
      newContent = newContent.replace(/style="[^"]+"/gi, function (match, capture) {
        match = match.replace(/width:[^;]+;/gi, 'max-width:100%;').replace(/max-max-width:[^;]+;/gi, 'max-width:100%;')
        return match;
      });
      newContent = newContent.replace(/<br[^>]*\/>/gi, '');
      newContent = newContent.replace(
        /\<img/gi,
        '<img style="max-width:100%;height:auto;display:block;margin-top:0;margin-bottom:0;"',
      );
      return newContent;
    },
    //对象数组去重；
    unique(arr) {
      const res = new Map();
      return arr.filter((arr) => !res.has(arr.product_id) && res.set(arr.product_id, 1));
    },
    // 商品id
    getProductId(data) {
      this.goods_modals = false;
      this.formValidate.recommend_list = this.unique(this.formValidate.recommend_list.concat(data));
    },
    // 选择推荐商品
    changeGoods() {
      this.goods_modals = true;
      this.$refs.goodslist.getList();
      this.$refs.goodslist.goodsCategory();
    },
    // 选择用户标签
    activeData(dataLabel) {
      this.labelShow = false;
      this.dataLabel = dataLabel;
    },
    // 标签弹窗关闭
    labelClose() {
      this.labelShow = false;
    },
    // 删除用户标签
    closeLabel(label) {
      let index = this.dataLabel.indexOf(this.dataLabel.filter((d) => d.id == label.id)[0]);
      this.dataLabel.splice(index, 1);
    },
    // 打开选择用户标签
    openLabel(row) {
      this.labelShow = true;
      this.$nextTick((e) => {
        // this.$refs.userLabel.userLabel(JSON.parse(JSON.stringify(this.dataLabel)));
      });
    },
    uniques(songs) {
      let result = {};
      let finalResult = [];
      for (let i = 0; i < songs.length; i++) {
        result[songs[i].product_id] = songs[i];
      }
      for (let item in result) {
        finalResult.push(result[item]);
      }
      return finalResult;
    },
    handleRemoveRecommend(i) {
      this.formValidate.recommend_list.splice(i, 1);
    },
    watchActivity() {
      let marketing = []
       this.formValidate.activity.map((el) => {
        if (el == '默认') {
          marketing.push(el);
        }
        if (el == '秒杀' && checkArray('seckill')) {
          marketing.push(el);
        } 
         if (el == '砍价' && checkArray('bargain')) {
          marketing.push(el);
        } 
         if (el == '拼团' && checkArray('combination')) {
          marketing.push(el);
        } 
      });
      this.formValidate.activity = marketing
    },
  },
};
</script>
<style scoped lang="stylus">
::v-deep .el-tabs__item {
  height: 54px !important;
  line-height: 54px !important;
}

.content_width {
  width: 460px;
}

.list-group {
  margin-left: -8px;
}

.borderStyle {
  border: 1px solid #ccc;
  padding: 8px;
  border-radius: 4px;
}

.drag {
  cursor: move;
  margin: 3px 0
}

.move-icon {
  width: 30px;
  cursor: move;
  margin-right: 10px;
}

.move-icon .icondrag2 {
  font-size: 26px;
  color: #d8d8d8;
}

.maxW ::v-deep .ivu-select-dropdown {
  max-width: 600px;
}

#shopp-manager .ivu-table-wrapper {
  border-left: 1px solid #dcdee2;
  border-top: 1px solid #dcdee2;
}

.noLeft {
  ::v-deep .ivu-form-item-content {
    margin-left: 0 !important;
  }
}

#shopp-manager .ivu-form-item {
  position: relative;
}

#shopp-manager .ivu-form-item .tips {
  position: absolute;
  color: #999;
  top: 29px;
  left: -77px;
  font-size: 12px;
}

.box-video-style {
  width: 375px; 
  height: 211px;
  border-radius: 10px;
  background-color: #707070;
  margin-top: 10px;
  position: relative;
  overflow: hidden;
}

.box-video-style .iconv {
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
  cursor: pointer;
}

.box-video-style .mark {
  position: absolute;
  width: 100%;
  height: 30px;
  top: 0;
  background-color: rgba(0, 0, 0, 0.5);
  text-align: center;
}

.submission {
  margin-left: 10px;
}

.color-list .tip {
  color: #c9c9c9;
  font-size: 12px;
}

.color-list .color-item {
  height: 30px;
  line-height: 30px;
  padding: 0 10px;
  color: #fff;
  margin-right: 10px;
  font-size: 12px;

}

.color-list .color-item.blue {
  background-color: #1E9FFF;
}

.color-list .color-item.yellow {
  background-color: rgb(254, 185, 0);
}

.color-list .color-item.green {
  background-color: #009688;
}

.color-list .color-item.red {
  background-color: #ed4014;
}

.columnsBox {
  margin-right: 10px;
  width: 200px;
}

.priceBox {
  width: 100%;
}

.rulesBox {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  .item{
    display:flex;
    flex-wrap: wrap;
  }
}

.pictrueBox {
  display: inline-block;
}

.pictrueTab {
  width: 40px !important;
  height: 40px !important;
}

.pictrue {
  width: 60px;
  height: 60px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  margin-right: 15px;
  display: inline-block;
  position: relative;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }

  .btndel {
    position: absolute;
    z-index: 1;
    width: 20px !important;
    height: 20px !important;
    left: 46px;
    top: -4px;
  }
}

.upLoad {
  width: 58px;
  height: 58px;
  line-height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.02);
  cursor: pointer;
}

.curs {
  cursor: pointer;
}

.inpWith {
  width: 60%;
}

.labeltop {
  ::v-deep .ivu-form-item-label {
    float: none !important;
    display: inline-block !important;
    margin-left: 120px !important;
    width: auto !important;
  }
}

.video-icon {
  background-image: url('https://cdn.oss.9gt.net/prov1.1/1/icons.png'); // cdn.oss.9gt.net/prov1.1/1/icons.png);
  background-color: #fff;
  background-position: -9999px;
  background-repeat: no-repeat;
}

.see {
  color: #2d8cf0;
  cursor: pointer;
}

.trip {
  color: #bbb;
  margin-bottom: 10px;
  font-size: 12px;
}


.virtual-data {
  display: flex;
  align-items: center;
}

.add-more {
  margin-top: 20px;
  display: flex;
}

.virtual-title {
  width: 60px;
}

.scroll-virtual {
  max-height: 400px;
  overflow-y: auto;
  margin-top: 10px;
}

.footer {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 30px;

  .clear, .submit {
    padding: 10px 20px;
    border-radius: 5px;
    color: #fff;
    cursor: pointer;
  }

  .clear {
    background-color: #ccc;
    margin-right: 20px;
  }

  .submit {
    background-color: #2d8cf0;
  }
}

.picBox {
  display: flex;
}

.btndel {
  position: absolute;
  z-index: 9;
  width: 20px !important;
  height: 20px !important;
  left: 46px;
  top: -4px;
}

.ifam {
  width: 344px;
  height: 644px;
  background: url('../../../assets/images/phonebg.png') no-repeat center top;
  background-size: 344px 644px;
  padding: 40px 20px;
  padding-top: 50px;
  margin: 0 auto;

  .content {
    height: 560px;
    overflow: hidden;
    scrollbar-width: none; /* firefox */
    -ms-overflow-style: none; /* IE 10+ */
    overflow-x: hidden;
    overflow-y: auto;
  }

  .content::-webkit-scrollbar {
    display: none; /* Chrome Safari */
  }
}
</style>
<style scoped lang="stylus">
::v-deep .ivu-date-picker {
  width: 300px;
}

.virtual_boder {
  border: 1px solid var(--prev-color-primary);
}

.virtual_boder2 {
  border: 1px solid #E7E7E7;
}

.virtual_san {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 0;
  height: 0;
  border-bottom: 26px solid var(--prev-color-primary);
  border-left: 26px solid transparent;
}

.virtual_dui {
  position: absolute;
  bottom: -2px;
  right: 2px;
  color: #FFFFFF;
  font-family: system-ui;
}

.virtual {
  width: 120px;
  height: 60px;
  background: #FFFFFF;
  border-radius: 3px;
  // border: 1px solid #E7E7E7;
  float: left;
  text-align: center;
  padding-top: 8px;
  position: relative;
  cursor: pointer;
  line-height: 23px;

  .virtual_top {
    font-size: 14px;
    font-weight: 600;
    color: rgba(0, 0, 0, 0.85);
  }

  .virtual_bottom {
    font-size: 12px;
    font-weight: 400;
    color: #999999;
  }
}

.virtual:nth-child(2n) {
  margin: 0 12px;
}

.addfont {
  display: inline-block;
  font-size: 12px;
  font-weight: 400;
  color: var(--prev-color-primary);
  margin-left: 14px;
  cursor: pointer;
}

.titTip {
  display: inline-bolck;
  font-size: 12px;
  line-height: 24px;
  font-weight: 400;
  color: #999999;
}

.videbox {
  width: 60px;
  height: 60px;
  background: rgba(0, 0, 0, 0.02);
  border-radius: 4px;
  border: 1px dashed #DDDDDD;
  line-height: 50px;
  text-align: center;
  color: #898989;
  font-size: 30px;
  font-weight: 400;
  cursor: pointer;
}

.addCustom_content {
  margin-top: 20px;

  .custom_box {
    margin-bottom: 10px;
  }
}

.addCustomBox {
  margin-top: 12px;
  font-size: 13px;
  font-weight: 400;
  color: var(--prev-color-primary);

  .btn {
    cursor: pointer;
    width: max-content;
  }
}

.type-radio {
  margin-buttom: 10px;
}

.deteal-btn {
  color: #5179ea;
}

.stock-disk {
  margin: 10px 0;
}

.line {
  border-bottom: 1px dashed #eee;
  margin-bottom: 20px;
}

.labelInput {
  border: 1px solid #dcdee2;
  width: 414px;
  padding: 0 15px;
  border-radius: 5px;
  min-height: 30px;
  cursor: pointer;
  font-size: 12px;

  .span {
    color: #c5c8ce;
  }

  .iconxiayi {
    font-size: 12px;
  }
}

#shopp-manager ::v-deep .ivu-form-item-content {
  line-height: 33px !important;
}

#selectvideo ::v-deep .ivu-form-item-content {
  line-height: 0px !important;
}

.progress {
  margin-top: 10px;
}
.labelInput ::v-deep .el-tag{
  color: #606266;
  background-color: #F0F2F5;
  border-color: #F0F2F5;
  margin-right: 6px;
}
.labelInput ::v-deep .el-tag .el-tag__close{
  color: #909399;
}
.labelInput ::v-deep .el-tag .el-tag__close:hover{
  color: #fff;
  background-color: #909399;
}
.brokerage{
  font-size: 12px;
}
</style>
