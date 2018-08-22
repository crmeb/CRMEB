{extend name="public/container"}
{block name="head_top"}
<link rel="stylesheet" href="{__PLUG_PATH}daterangepicker/daterangepicker.css">
<link href="{__FRAME_PATH}css/plugins/footable/footable.core.css" rel="stylesheet">
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
<script src="{__PLUG_PATH}moment.js"></script>
<script src="{__PLUG_PATH}daterangepicker/daterangepicker.js"></script>
<script src="{__PLUG_PATH}echarts.common.min.js"></script>
<script src="{__FRAME_PATH}js/plugins/footable/footable.all.min.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline search-form">
                            <div class="search-item" data-name="status">
                                <span class="btn-outline btn-link btn-sm">订单状态：</span>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="">全部</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="0">未支付</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="1">未发货</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="2">待收货</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="3">待评价</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="4">交易完成</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="-1">退款中</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="-2">已退款</button>
                                <input class="search-item-value" type="hidden" name="status" value="{$where.status}" />
                            </div>
                            <div class="search-item" data-name="combination_id">
                                <span  class="btn-outline btn-link btn-sm">订单类型：</span>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="">全部</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="普通订单">普通订单</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="拼团订单">拼团订单</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="秒杀订单">秒杀订单</button>
                                <input class="search-item-value" type="hidden" name="combination_id" value="{$where.combination_id}" />
                            </div>
                            <div class="search-item" data-name="data">
                                <span  class="btn-outline btn-link btn-sm">创建时间：</span>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="{$limitTimeList.yesterday}">昨天</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="{$limitTimeList.today}">今天</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="{$limitTimeList.week}">本周</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="{$limitTimeList.month}">本月</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="{$limitTimeList.quarter}">本季度</button>
                                <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="{$limitTimeList.year}">本年</button>
                                <div class="datepicker" style="display: inline-block;">
                                    <button type="button" class="btn btn-sm btn-info btn-outline btn-link" data-value="{$where.data?:'no'}">自定义</button>
                                </div>
                                <input class="search-item-value" type="hidden" name="data" value="{$where.data}" />
                            </div>
                            <div class="col-sm-4 text-right col-sm-offset-8" style="padding-bottom: 10px;">
                                <div class="input-group">
                                    <input size="26" type="text" name="real_name" value="{$where.real_name}" placeholder="请输入姓名、电话、订单编号" class="input-sm form-control">
                                    <input type="hidden" name="export" value="0">
                                    <input type="hidden" name="order" id="order">
                                <span class="input-group-btn">
                                      <button type="submit" id="no_export" class="btn btn-sm btn-primary"> <i class="fa fa-search" ></i> 搜索</button>
                                      <button type="submit" id="export" class="btn btn-sm btn-info btn-outline"> <i class="fa fa-exchange" ></i> Excel导出</button>
                                    <script>
                                        $('#export').on('click',function(){
                                            $('input[name=export]').val(1);
                                        });
                                        $('#no_export').on('click',function(){
                                            $('input[name=export]').val(0);
                                        });
                                    </script>
                                  </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive ibox-content" style="padding-right: 0;padding-left: 0;overflow:visible">
                    <div class="row">
                        <?php $list_num = $list->toArray(); ?>
                        <div class="col-sm-12 m-b-sm">
                            <span class="m-xs">售出商品：<strong class="text-danger">{$price.total_num}</strong></span>
                            <span class="m-xs">订单数量：<strong class="text-danger">{$list_num.total}</strong></span>
                            <span class="m-xs">订单金额：<strong class="text-danger">￥{$price.pay_price}</strong></span>
                            <span class="m-xs">退款金额：<strong class="text-danger m-sm">￥{$price.refund_price}</strong></span>
                            {if condition="$price['pay_price_wx'] GT 0"}
                            <span class="m-xs">微信支付金额：<strong class="text-danger">￥{$price.pay_price_wx}</strong></span>
                            {/if}
                            {if condition="$price['pay_price_yue'] GT 0"}
                            <span class="m-xs">余额支付金额：<strong class="text-danger">￥{$price.pay_price_yue}</strong></span>
                            {/if}
                            {if condition="$price['pay_price_offline'] GT 0"}
                            <span class="m-xs">线下支付金额：<strong class="text-danger">￥{$price.pay_price_offline}</strong></span>
                            {/if}
                            {if condition="$price['pay_price_other'] GT 0"}
                            <span class="m-xs">线下支付金额：<strong class="text-danger">￥{$price.pay_price_other}</strong></span>
                            {/if}
                            {if condition="$price['use_integral'] GT 0"}
                            <span class="m-xs">积分抵扣：<strong class="text-success">{$price.use_integral} (抵扣金额:￥{$price.deduction_price})</strong></span>
                            {/if}
                            {if condition="$price['back_integral'] GT 0"}
                            <span class="m-xs">退回积分：<strong class="text-success">{$price.back_integral}</strong></span>
                            {/if}
                        </div>
                        <!--<div class="col-sm-8 text-left m-b-sm">

                        </div>-->
                    </div>
                    <table class="footable table table-striped  table-bordered " data-page-size="20">
                        <thead>
                        <tr>
                            <th class="text-center" width="13%">订单号</th>
                            <th class="text-center" width="10%">用户信息</th>
                            <th class="text-center" width="42%">商品信息</th>
                            <th class="text-center" width="8%">
                                <div class="btn-group">
                                    <button data-toggle="dropdown" class="btn btn-white btn-xs dropdown-toggle" style="font-weight: bold;background-color: #f5f5f6;border: solid 0;" aria-expanded="false">
                                        实际支付
                                        <span class="second caret"></span>
                                    </button>
                                    <!-- <span class="caret" data-toggle="dropdown" class="dropdown-toggle" aria-expanded="false"></span>-->
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="ordersoft" data-value="">
                                                <i class="fa fa-arrows-v"></i>   默认排序
                                            </a>
                                        </li>
                                        <li>
                                            <a class="ordersoft" data-value="pay_price asc">
                                                <i class="fa fa-sort-numeric-asc"></i>    升序
                                            </a>
                                        </li>
                                        <li>
                                            <a class="ordersoft" data-value="pay_price desc">
                                                <i class="fa fa-sort-numeric-desc"></i>    降序
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            <th class="text-center" width="5%">支付状态</th>
                            <th class="text-center" width="8%">订单状态</th>
                            <th class="text-center" width="5%">详情</th>
                            <th class="text-center" width="10%">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">
                                {$vo.order_id}　<br/>　
                                <span style="color: {$vo.color};">{$vo.pink_name}</span>　　
                            </td>
                            <td class="text-center">
                                <p><span>{$vo.nickname} / {$vo.uid}</span></p>
                            </td>
                            <td>
                                <?php $info_order = $vo['_info'];?>
                                {volist name="info_order" id="info"}
                                {if condition="isset($info['cart_info']['productInfo']['attrInfo']) && !empty($info['cart_info']['productInfo']['attrInfo'])"}
                                <p>
                                    <span><img class="open_image" data-image="{$info.cart_info.productInfo.image}" style="width: 30px;height: 30px;cursor: pointer;" src="{$info.cart_info.productInfo.attrInfo.image}" alt="{$info.cart_info.productInfo.store_name}" title="{$info.cart_info.productInfo.store_name}"></span>
                                    <span>{$info.cart_info.productInfo.store_name}&nbsp;{$info.cart_info.productInfo.attrInfo.suk}</span><span> | ￥{$info.cart_info.truePrice}×{$info.cart_info.cart_num}</span>
                                </p>
                                {else/}
                                <p>
                                    <span><img class="open_image" data-image="{$info.cart_info.productInfo.image}" style="width: 30px;height: 30px;cursor: pointer;" src="{$info.cart_info.productInfo.image}" alt="{$info.cart_info.productInfo.store_name}" title="{$info.cart_info.productInfo.store_name}"></span>
                                    <span>{$info.cart_info.productInfo.store_name}</span><span> | ￥{$info.cart_info.truePrice}×{$info.cart_info.cart_num}</span>
                                </p>
                                {/if}
                                {/volist}
                            </td>
                            <td class="text-center">
                                ￥{$vo.pay_price}
                            </td>
                            <td class="text-center">
                                {if condition="$vo['paid'] eq 1"}
                                <p><span>
                                           {if condition="$vo['pay_type'] eq 'weixin'"}
                                           微信支付
                                           {elseif condition="$vo['pay_type'] eq 'yue'"}
                                           余额支付
                                           {elseif condition="$vo['pay_type'] eq 'offline'"}
                                           线下支付
                                           {else/}
                                           其他支付
                                           {/if}
                                       </span></p>
                                {else/}
                                {if condition="$vo['pay_type'] eq 'offline'"}
                                <p><span>线下支付</span></p>
                                <p><button data-pay="{$vo.pay_price}" data-url="{:Url('offline',array('id'=>$vo['id']))}" type="button" class="offline_btn btn btn-w-m btn-white">立即支付</button></p>

                                {else/}
                                <p><span>未支付</span></p>
                                {/if}
                                {/if}
                            </td>
                            <td class="text-center">
                                {if condition="$vo['paid'] eq 0 && $vo['status'] eq 0"}
                                未支付
                                {elseif condition="$vo['paid'] eq 1 && $vo['status'] eq 0 && $vo['refund_status'] eq 0"/}
                                未发货
                                {elseif condition="$vo['paid'] eq 1 && $vo['status'] eq 1 && $vo['refund_status'] eq 0"/}
                                待收货

                                {elseif condition="$vo['paid'] eq 1 && $vo['status'] eq 2 && $vo['refund_status'] eq 0"/}
                                待评价
                                {elseif condition="$vo['paid'] eq 1 && $vo['status'] eq 3 && $vo['refund_status'] eq 0"/}
                                交易完成
                                {elseif condition="$vo['paid'] eq 1 && $vo['refund_status'] eq 1"/}
                                <b style="color:#f124c7">申请退款</b><br/>
                                <span>退款原因：{$vo.refund_reason_wap}</span>
                                {elseif condition="$vo['paid'] eq 1 && $vo['refund_status'] eq 2"/}
                                已退款
                                {/if}
                            </td>
                            <td class="text-center">
                                <a class="btn btn-white btn-bitbucket btn-xs" onclick="$eb.createModalFrame('{$vo.nickname}-订单详情','{:Url('order_info',array('oid'=>$vo['id']))}')">
                                    <i class="fa fa-file-text"></i> 订单详情
                                </a>
                            </td>
                            <td class="text-right">
                                <span class="input-group-btn js-group-btn" style="min-width: 106px;">
                                    {if condition="$vo['paid'] eq 0 && $vo['status'] eq 0 && $vo['refund_status'] eq 0"}<!--未支付-->
                                    <div class="btn-group">
                                        <button data-toggle="dropdown" class="btn btn-warning btn-xs dropdown-toggle"
                                                aria-expanded="false">操作
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('修改订单','{:Url('edit',array('id'=>$vo['id']))}')">
                                                    <i class="fa fa-file-text"></i> 修改订单
                                                </a>
                                            </li>
                                            <li>
                                                <a class="save_mark" href="javascript:void(0);"  data-id="{$vo['id']}" data-make="{$vo.remark}" data-url="{:Url('remark')}">
                                                    <i class="fa fa-paste"></i> 订单备注
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('订单记录','{:Url('order_status',array('oid'=>$vo['id']))}')">
                                                    <i class="fa fa-newspaper-o"></i> 订单记录
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    {elseif condition="$vo['paid'] eq 1 && $vo['status'] eq 0 && $vo['refund_status'] eq 0"/}<!--已支付-->

                                    <button class="btn btn-primary btn-xs" type="button" onclick="$eb.createModalFrame('去发货','{:Url('deliver_goods',array('id'=>$vo['id']))}',{w:400,h:300})"><i class="fa fa-cart-plus"></i> 去发货</button>
                                    <div class="btn-group">
                                        <button data-toggle="dropdown" class="btn btn-warning btn-xs dropdown-toggle"
                                                aria-expanded="false">操作
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a  href="javascript:void(0);" onclick="$eb.createModalFrame('去送货','{:Url('delivery',array('id'=>$vo['id']))}',{w:400,h:300})">
                                                    <i class="fa fa-motorcycle"></i> 去送货
                                                </a>
                                            </li>
                                            <li>
                                                <a class="save_mark" href="javascript:void(0);"  data-id="{$vo['id']}" data-make="{$vo.remark}" data-url="{:Url('remark')}">
                                                    <i class="fa fa-paste"></i> 订单备注
                                                </a>
                                            </li>
                                            {if condition="$vo['pay_price'] neq $vo['refund_price']"}
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('退款','{:Url('refund_y',array('id'=>$vo['id']))}',{w:400,h:300})">
                                                    <i class="fa fa-history"></i> 立即退款
                                                </a>
                                            </li>
                                            {/if}
                                            {if condition="$vo['use_integral'] GT 0 && $vo['use_integral'] EGT $vo['back_integral']"}<!-- 退积分 -->
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('退积分','{:Url('integral_back',array('id'=>$vo['id']))}')">
                                                    <i class="fa fa-history"></i> 退积分
                                                </a>
                                            </li>
                                            {/if}
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('订单记录','{:Url('order_status',array('oid'=>$vo['id']))}')">
                                                    <i class="fa fa-newspaper-o"></i> 订单记录
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    {elseif condition="$vo['paid'] eq 1 && $vo['refund_status'] eq 1"/}<!--已支付  退款中-->
                                      <div class="btn-group">
                                          <button data-toggle="dropdown" class="btn btn-warning btn-xs dropdown-toggle"
                                                  aria-expanded="false">操作
                                              <span class="caret"></span>
                                          </button>
                                          <ul class="dropdown-menu">
                                              {if condition="$vo['use_integral'] GT 0 && $vo['use_integral'] EGT $vo['back_integral']"}<!-- 退积分 -->
                                              <li>
                                                  <a class="save_mark" href="javascript:void(0);"  data-id="{$vo['id']}" data-make="{$vo.remark}" data-url="{:Url('remark')}">
                                                      <i class="fa fa-paste"></i> 订单备注
                                                  </a>
                                              </li>
                                              <li>
                                                  <a href="javascript:void(0);" onclick="$eb.createModalFrame('退积分','{:Url('integral_back',array('id'=>$vo['id']))}',{w:400,h:300})">
                                                      <i class="fa fa-history"></i> 退积分
                                                  </a>
                                              </li>
                                              {/if}
                                              {if condition="$vo['pay_price'] neq $vo['refund_price']"}
                                              <li>
                                                  <a href="javascript:void(0);" onclick="$eb.createModalFrame('退款','{:Url('refund_y',array('id'=>$vo['id']))}',{w:400,h:300})">
                                                      <i class="fa fa-history"></i>立即退款
                                                  </a>
                                              </li>
                                              {/if}
                                              <li>
                                                  <a href="javascript:void(0);" onclick="$eb.createModalFrame('不退款','{:Url('refund_n',array('id'=>$vo['id']))}',{w:400,h:300})">
                                                      <i class="fa fa-openid"></i> 不退款
                                                  </a>
                                              </li>

                                              <li>
                                                  <a href="javascript:void(0);" onclick="$eb.createModalFrame('订单记录','{:Url('order_status',array('oid'=>$vo['id']))}')">
                                                      <i class="fa fa-newspaper-o"></i> 订单记录
                                                  </a>
                                              </li>
                                          </ul>
                                      </div>
                                    {elseif condition="$vo['paid'] eq 1 && $vo['status'] eq 1 && $vo['refund_status'] eq 0"/}<!--已支付 已发货 待收货-->
                                    <button class="btn btn-default btn-xs" type="button" onclick="$eb.createModalFrame('配送信息','{:Url('distribution',array('id'=>$vo['id']))}')"><i class="fa fa-cart-arrow-down"></i> 配送信息</button>
                                    <div class="btn-group">
                                        <button data-toggle="dropdown" class="btn btn-warning btn-xs dropdown-toggle"
                                                aria-expanded="false">操作
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="save_mark" href="javascript:void(0);"  data-id="{$vo['id']}" data-make="{$vo.remark}" data-url="{:Url('remark')}">
                                                    <i class="fa fa-paste"></i> 订单备注
                                                </a>
                                            </li>
                                            <li>
                                                <a class="danger-btn" href="javascript:void(0);" data-url="{:Url('take_delivery',array('id'=>$vo['id']))}">
                                                    <i class="fa fa-cart-arrow-down"></i> 已收货
                                                </a>
                                            </li>
                                            {if condition="$vo['pay_price'] neq $vo['refund_price']"}
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('退款','{:Url('refund_y',array('id'=>$vo['id']))}')">
                                                    <i class="fa fa-history"></i> 立即退款
                                                </a>
                                            </li>
                                            {/if}
                                            {if condition="$vo['use_integral'] GT 0 && $vo['use_integral'] EGT $vo['back_integral']"}<!-- 退积分 -->
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('退积分','{:Url('integral_back',array('id'=>$vo['id']))}')">
                                                    <i class="fa fa-history"></i> 退积分
                                                </a>
                                            </li>
                                            {/if}
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('订单记录','{:Url('order_status',array('oid'=>$vo['id']))}')">
                                                    <i class="fa fa-newspaper-o"></i> 订单记录
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    {elseif condition="$vo['paid'] eq 1 && $vo['status'] eq 2 && $vo['refund_status'] eq 0"/}<!--已支付  已收货 待评价-->
                                    <div class="btn-group">
                                        <button data-toggle="dropdown" class="btn btn-warning btn-xs dropdown-toggle"
                                                aria-expanded="false">操作
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="save_mark" href="javascript:void(0);"  data-id="{$vo['id']}" data-make="{$vo.remark}" data-url="{:Url('remark')}">
                                                    <i class="fa fa-paste"></i> 订单备注
                                                </a>
                                            </li>
                                            {if condition="$vo['pay_price'] neq $vo['refund_price']"}
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('退款','{:Url('refund_y',array('id'=>$vo['id']))}')">
                                                    <i class="fa fa-history"></i> 立即退款
                                                </a>
                                            </li>
                                            {/if}
                                            {if condition="$vo['use_integral'] GT 0 && $vo['use_integral'] EGT $vo['back_integral']"}<!-- 退积分 -->
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('退积分','{:Url('integral_back',array('id'=>$vo['id']))}')">
                                                    <i class="fa fa-history"></i> 退积分
                                                </a>
                                            </li>
                                            {/if}
                                            <li>
                                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('订单记录','{:Url('order_status',array('oid'=>$vo['id']))}')">
                                                    <i class="fa fa-newspaper-o"></i> 订单记录
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                        {elseif condition="$vo['paid'] eq 1 && $vo['status'] eq 3 && $vo['refund_status'] eq 0"/} <!--订单完成 -->
                                        <div class="btn-group">
                                            <button data-toggle="dropdown" class="btn btn-warning btn-xs dropdown-toggle"
                                                    aria-expanded="false">操作
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="save_mark" href="javascript:void(0);"  data-id="{$vo['id']}" data-make="{$vo.remark}" data-url="{:Url('remark')}">
                                                        <i class="fa fa-paste"></i> 订单备注
                                                    </a>
                                                </li>
                                                {if condition="$vo['pay_price'] neq $vo['refund_price']"}
                                                <li>
                                                    <a href="javascript:void(0);" onclick="$eb.createModalFrame('退款','{:Url('refund_y',array('id'=>$vo['id']))}')">
                                                        <i class="fa fa-history"></i> 立即退款
                                                    </a>
                                                </li>
                                                {/if}
                                                {if condition="$vo['use_integral'] GT 0 && $vo['use_integral'] EGT $vo['back_integral']"}<!-- 退积分 -->
                                                <li>
                                                    <a href="javascript:void(0);" onclick="$eb.createModalFrame('退积分','{:Url('integral_back',array('id'=>$vo['id']))}')">
                                                        <i class="fa fa-history"></i> 退积分
                                                    </a>
                                                </li>
                                                {/if}
                                                <li>
                                                    <a href="javascript:void(0);" onclick="$eb.createModalFrame('订单记录','{:Url('order_status',array('oid'=>$vo['id']))}')">
                                                        <i class="fa fa-newspaper-o"></i> 订单记录
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
{elseif condition="$vo['paid'] eq 1 && $vo['refund_status'] eq 2"/}<!-- 已支付  已退款-->

                                            <div class="btn-group">
                                                <button data-toggle="dropdown" class="btn btn-warning btn-xs dropdown-toggle"
                                                        aria-expanded="false">操作
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="save_mark" href="javascript:void(0);"  data-id="{$vo['id']}" data-make="{$vo.remark}" data-url="{:Url('remark')}">
                                                            <i class="fa fa-paste"></i> 订单备注
                                                        </a>
                                                    </li>
                                                    {if condition="$vo['use_integral'] GT 0 && $vo['use_integral'] EGT $vo['back_integral']"}<!-- 退积分 -->
                                                    <li>
                                                        <a href="javascript:void(0);" onclick="$eb.createModalFrame('退积分','{:Url('integral_back',array('id'=>$vo['id']))}')">
                                                            <i class="fa fa-history"></i> 退积分
                                                        </a>
                                                    </li>
                                                    {/if}
                                                    <li>
                                                        <a href="javascript:void(0);" onclick="$eb.createModalFrame('订单记录','{:Url('order_status',array('oid'=>$vo['id']))}')">
                                                            <i class="fa fa-newspaper-o"></i> 订单记录
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                    {/if}
                                </span>
                            </td>
                        </tr>
                        {/volist}
                        </tbody>
                    </table>
                </div>
                {include file="public/inner_page"}
            </div>
        </div>
    </div>
</div>
<script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>
{/block}
{block name="script"}
<script>


    $(function init(){
        $('.search-item>.btn').on('click',function(){
            var that = $(this),value = that.data('value'),p = that.parent(),name = p.data('name'),form = p.parents();
            form.find('input[name="'+name+'"]').val(value);
            $('input[name=export]').val(0);
            form.submit();
        });
        $('.search-item-value').each(function(){
            var that = $(this),name = that.attr('name'), value = that.val(),dom = $('.search-item[data-name="'+name+'"] .btn[data-value="'+value+'"]');
            dom.eq(0).removeClass('btn-outline btn-link').addClass('btn-primary').siblings().addClass('btn-outline btn-link btn-sm').removeClass('btn-primary')
        });
    });
    $('.ordersoft').on('click',function(){
        var that = $(this),value = that.data('value');
        $('input[name=order]').val(value);
        $('input[name=export]').val(0);
        $('form').submit();
    });
    $('.js-group-btn').on('click',function(){
        $('.js-group-btn').css({zIndex:1});
        $(this).css({zIndex:2});
    });
    $(".open_image").on('click',function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    });
    $('.danger-btn,.btn-danger').on('click',function (e) {
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                }else
                    return Promise.reject(res.data.msg || '收货失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        },{'title':'您确定要修改收货状态吗？','text':'修改后将无法恢复,请谨慎操作！','confirm':'是的，我要修改'})
    })
    $('.offline_btn').on('click',function (e) {
        window.t = $(this);
        var _this = $(this),url =_this.data('url'),pay_price =_this.data('pay');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                }else
                    return Promise.reject(res.data.msg || '收货失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        },{'title':'您确定要修改已支付'+pay_price+'元的状态吗？','text':'修改后将无法恢复,请谨慎操作！','confirm':'是的，我要修改'})
    })

    $('.add_mark').on('click',function (e) {
        var _this = $(this),url =_this.data('url'),id=_this.data('id');
        $eb.$alert('textarea',{},function (result) {
            if(result){
                $.ajax({
                    url:url,
                    data:'remark='+result+'&id='+id,
                    type:'post',
                    dataType:'json',
                    success:function (res) {
                        console.log(res);
                        if(res.code == 200) {
                            $eb.$swal('success',res.msg);
                        }else
                            $eb.$swal('error',res.msg);
                    }
                })
            }else{
                $eb.$swal('error','请输入要备注的内容');
            }
        });
    })
    $('.save_mark').on('click',function (e) {
        var _this = $(this),url =_this.data('url'),id=_this.data('id'),make=_this.data('make');
        $eb.$alert('textarea',{title:'请修改内容',value:make},function (result) {
            if(result){
                $.ajax({
                    url:url,
                    data:'remark='+result+'&id='+id,
                    type:'post',
                    dataType:'json',
                    success:function (res) {
                        console.log(res);
                        if(res.code == 200) {
                            $eb.$swal('success',res.msg);
                        }else
                            $eb.$swal('error',res.msg);
                    }
                })
            }else{
                $eb.$swal('error','请输入要备注的内容');
            }
        });
    })
    var dateInput =$('.datepicker');
    dateInput.daterangepicker({
        autoUpdateInput: false,
        "opens": "center",
        "drops": "down",
        "ranges": {
            '今天': [moment(), moment().add(1, 'days')],
            '昨天': [moment().subtract(1, 'days'), moment()],
            '上周': [moment().subtract(6, 'days'), moment()],
            '前30天': [moment().subtract(29, 'days'), moment()],
            '本月': [moment().startOf('month'), moment().endOf('month')],
            '上月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        "locale" : {
            applyLabel : '确定',
            cancelLabel : '取消',
            fromLabel : '起始时间',
            toLabel : '结束时间',
            format : 'YYYY/MM/DD',
            customRangeLabel : '自定义',
            daysOfWeek : [ '日', '一', '二', '三', '四', '五', '六' ],
            monthNames : [ '一月', '二月', '三月', '四月', '五月', '六月',
                '七月', '八月', '九月', '十月', '十一月', '十二月' ],
            firstDay : 1
        }
    });

    dateInput.on('cancel.daterangepicker', function(ev, picker) {
        //$("input[name=limit_time]").val('');
    });
    dateInput.on('apply.daterangepicker', function(ev, picker) {
        $("input[name=data]").val(picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
        $('input[name=export]').val(0);
        $('form').submit();
    });
</script>
{/block}
