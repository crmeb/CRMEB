<template>
	<view :style="colorStyle">
		<view class="order-details">
			<!-- 给header上与data上加on为退款订单-->
			<view class="header bg-color acea-row row-middle" :class="isGoodsReturn ? 'on' : ''">
				<view class="pictrue" v-if="isGoodsReturn == false">
					<image :src="orderInfo.status_pic"></image>
				</view>
				<view class="data" :class="isGoodsReturn ? 'on' : ''">
					<view class="state">{{ orderInfo._status._msg }}</view>
					<view>
						{{ orderInfo.add_time_y }}
						<text class="time">{{ orderInfo.add_time_h }}</text>
					</view>
				</view>
			</view>
			<view class="refund-msg" v-if="[4, 5].includes(orderInfo.refund_type)">
				<view v-if="orderInfo._status.refund_name != ''">
					<view class="refund-msg-user">
						<text class="name">{{ orderInfo._status.refund_name }}</text>
						<text>{{ orderInfo._status.refund_phone }}</text>
						<!-- #ifndef H5 -->
						<text class="copy-refund-msg" @click="copyAddress()">{{ $t(`复制`) }}</text>
						<!-- #endif -->
						<!-- #ifdef H5 -->
						<text class="copy-refund-msg" :data-clipboard-text="orderInfo._status.refund_name + orderInfo._status.refund_phone + orderInfo._status.refund_address">
							{{ $t(`复制`) }}
						</text>
						<!-- #endif -->
					</view>
					<view class="refund-address">
						{{ orderInfo._status.refund_address }}
					</view>
					<view class="refund-tip">
						<text class="iconfont icon-zhuyi-copy"></text>
						{{ $t(`请按以上退货信息将商品退回`) }}
					</view>
				</view>
				<view v-else>
					<view class="refund-tip1">
						<text class="iconfont icon-zhuyi-copy"></text>
						{{ $t(`请联系管理员获取退货地址`) }}
					</view>
				</view>
			</view>
			<view class="line" v-if="[4, 5].includes(orderInfo.refund_type)">
				<image src="@/static/images/line.jpg"></image>
			</view>
			<view class="mb-16" v-if="isGoodsReturn == false">
				<view class="nav" v-if="is_gift != 2">
					<view class="navCon acea-row row-between-wrapper" v-if="!is_gift">
						<view :class="status.type == 0 || status.type == -9 ? 'on' : ''">{{ $t(`待付款`) }}</view>
						<view :class="status.type == 1 || status.type == 5 ? 'on' : ''">
							{{ orderInfo.shipping_type == 1 ? $t(`待发货`) : $t(`待核销`) }}
						</view>
						<view :class="status.type == 2 ? 'on' : ''" v-if="orderInfo.shipping_type == 1">{{ $t(`待收货`) }}</view>
						<view :class="status.type == 3 ? 'on' : ''">{{ $t(`待评价`) }}</view>
						<view :class="status.type == 4 ? 'on' : ''">{{ $t(`已完成`) }}</view>
					</view>
					<view class="navCon acea-row row-between-wrapper" v-else-if="is_gift !== 2">
						<view :class="status.type == 0 || status.type == -9 || orderInfo.paid == 1 ? 'on' : ''">{{ $t(`待付款`) }}</view>
						<view :class="orderInfo.paid == 1 ? 'on' : ''">{{ $t(`待领取`) }}</view>
						<view :class="orderInfo.gift_uid ? 'on' : ''">{{ $t(`已领取`) }}</view>
						<view :class="status.type == 4 ? 'on' : ''">{{ $t(`已完成`) }}</view>
					</view>
					<view class="progress acea-row row-between-wrapper" v-if="!is_gift">
						<view class="iconfont" :class="(status.type == 0 || status.type == -9 ? 'icon-webicon318' : 'icon-yuandianxiao') + ' ' + (status.type >= 0 ? 'font-num' : '')"></view>
						<view class="line" :class="status.type > 0 ? 'bg-color' : ''"></view>
						<view
							class="iconfont"
							:class="(status.type == 1 || status.type == 5 ? 'icon-webicon318' : 'icon-yuandianxiao') + ' ' + (status.type >= 1 ? 'font-num' : '')"
							v-if="orderInfo.shipping_type != 4"
						></view>
						<view class="line" :class="status.type > 1 && status.type != 5 ? 'bg-color' : ''" v-if="orderInfo.shipping_type == 1"></view>
						<view
							class="iconfont"
							:class="(status.type == 2 ? 'icon-webicon318' : 'icon-yuandianxiao') + ' ' + (status.type >= 2 ? 'font-num' : '')"
							v-if="orderInfo.shipping_type == 1"
						></view>
						<view class="line" :class="status.type > 2 && status.type != 5 ? 'bg-color' : ''"></view>
						<view class="iconfont" :class="(status.type == 3 ? 'icon-webicon318' : 'icon-yuandianxiao') + ' ' + (status.type >= 3 && status.type != 5 ? 'font-num' : '')"></view>
						<view class="line" :class="status.type > 3 && status.type != 5 ? 'bg-color' : ''"></view>
						<view class="iconfont" :class="(status.type == 4 ? 'icon-webicon318' : 'icon-yuandianxiao') + ' ' + (status.type >= 4 && status.type != 5 ? 'font-num' : '')"></view>
					</view>
					<view class="progress acea-row row-between-wrapper" v-else-if="is_gift !== 2">
						<view
							class="iconfont"
							:class="(status.type == 0 || status.type == -9 || orderInfo.paid == 1 ? 'icon-webicon318' : 'icon-yuandianxiao') + ' ' + (status.type >= 0 ? 'font-num' : '')"
						></view>
						<view class="line" :class="orderInfo.paid == 1 ? 'bg-color' : ''"></view>
						<view
							class="iconfont"
							:class="
								(orderInfo.gift_uid == 0 || orderInfo.paid == 1 ? 'icon-webicon318' : 'icon-yuandianxiao') +
								' ' +
								((status.type >= 4 && status.type != 5) || orderInfo.gift_uid == 0 || orderInfo.paid == 1 ? 'font-num' : '')
							"
						></view>
						<view class="line" :class="orderInfo.gift_uid ? 'bg-color' : ''"></view>
						<view
							class="iconfont"
							:class="(orderInfo.gift_uid ? 'icon-webicon318' : 'icon-yuandianxiao') + ' ' + ((status.type >= 4 && status.type != 5) || orderInfo.gift_uid ? 'font-num' : '')"
						></view>
						<view class="line" :class="status.type > 3 && status.type != 5 ? 'bg-color' : ''"></view>
						<view class="iconfont" :class="(status.type == 4 ? 'icon-webicon318' : 'icon-yuandianxiao') + ' ' + (status.type >= 4 && status.type != 5 ? 'font-num' : '')"></view>
					</view>
				</view>
				<view v-if="giftData && is_gift == 2" class="gift-box">
					<view class="acea-row row-middle user-msg">
						<image class="avatar mr-12" :src="giftData.avatar" mode=""></image>
						<text class="nickname">{{ giftData.nickname }} {{ $t(`赠您一份礼物，请查收！`) }}</text>
					</view>
					<view class="line"></view>
					<view class="gift-mark" v-if="giftData.gift_mark">
						{{ giftData.gift_mark }}
					</view>
				</view>
				<!-- <view class="writeOff" v-if="orderInfo.shipping_type == 2 && orderInfo.paid"> -->
				<view class="writeOff" v-if="orderInfo.verify_code && orderInfo.paid == 1">
					<view class="title">{{ $t(`核销信息`) }}</view>
					<view class="grayBg">
						<view class="written" v-if="orderInfo.status == 2">
							<image src="../static/written.png"></image>
						</view>
						<view class="pictrue">
							<image :src="codeSrc" mode=""></image>
							<zb-code
								ref="qrcode"
								:show="codeShow"
								:cid="cid"
								:val="val"
								:size="size"
								:unit="unit"
								:background="background"
								:foreground="foreground"
								:pdground="pdground"
								:icon="icon"
								:iconSize="iconsize"
								:onval="onval"
								:loadMake="loadMake"
								@result="qrR"
							/>
						</view>
					</view>
					<view class="gear">
						<image src="../static/writeOff.jpg"></image>
					</view>
					<view class="num">{{ orderInfo._verify_code }}</view>
					<view class="rules">
						<view class="item" v-if="orderInfo.shipping_type == 2">
							<view class="rulesTitle acea-row row-middle">
								<text class="iconfont icon-shijian"></text>
								{{ $t(`营业时间`) }}
							</view>
							<view class="info">
								{{ $t(`每日`) }}：
								<text class="time">{{ orderInfo.system_store.day_time }}</text>
							</view>
						</view>
						<view class="item">
							<view class="rulesTitle acea-row row-middle">
								<text class="iconfont icon-shuoming1"></text>
								{{ $t(`使用说明`) }}
							</view>
							<view class="info">
								{{ orderInfo.shipping_type == 2 ? $t(`可将二维码出示给店员扫描或提供数字核销码`) : $t(`可将二维码出示给配送员进行核销`) }}
							</view>
						</view>
					</view>
				</view>
				<view class="map acea-row row-between-wrapper" v-if="orderInfo.shipping_type == 2">
					<view>{{ $t(`地址信息`) }}</view>
					<view class="place cart-color acea-row row-center-wrapper" @tap="showMaoLocation">
						<text class="iconfont icon-weizhi"></text>
						{{ $t(`查看位置`) }}
					</view>
				</view>
				<view class="mt-12" v-if="orderInfo.virtual_type == 0 && (is_gift == 0 || is_gift == 2)">
					<view class="address" v-if="orderInfo.shipping_type === 1">
						<view class="name">
							{{ orderInfo.real_name }}
							<text class="phone">{{ orderInfo.user_phone }}</text>
						</view>
						<view>{{ orderInfo.user_address }}</view>
					</view>
					<view v-else class="address acea-row row-between-wrapper">
						<view class="address-box">
							<view class="name" @tap="makePhone">
								{{ orderInfo.system_store.name }}
								<text class="phone">{{ orderInfo.system_store.phone }}</text>
							</view>
							<view>{{ orderInfo.system_store.detailed_address }}</view>
						</view>
						<view class="icon acea-row row-middle">
							<view class="iconfont icon-dianhua" @click.stop="makePhone"></view>
							<view class="iconfont icon-dingwei2" @click.stop="showMaoLocation(system_store)"></view>
						</view>
					</view>
					<view class="line" v-if="orderInfo.shipping_type === 1 && !is_gift">
						<image src="@/static/images/line.jpg"></image>
					</view>
				</view>
				<view v-if="orderInfo.virtual_type != 0" style="paddingtop: 6px"></view>
			</view>
			<view v-else>
				<!-- 拒绝退款 -->
				<view class="refund" v-if="orderInfo.refund_type == 3">
					<view class="title">
						<image src="../static/shuoming.png" mode=""></image>
						{{ $t(`拒绝退款`) }}
					</view>
					<view class="con">{{ $t(`拒绝原因`) }}：{{ orderInfo.refuse_reason || '' }}</view>
				</view>
			</view>
			<orderGoods
				v-for="(item, index) in split"
				:key="item.id"
				:evaluate="item._status._type == 3 ? 3 : 0"
				:orderId="item.order_id"
				:cartInfo="item.cartInfo"
				:jump="false"
				:jumpDetail="true"
				:pid="item.pid"
				:split="true"
				:status_type="item._status._type"
				:index="index"
				:refund_status="item.refund_status"
				:delivery_type="item.delivery_type"
				:is_refund_available="orderInfo.is_refund_available"
				:is_gift="is_gift"
				:gift_uid="orderInfo.gift_uid"
				@confirmOrder="confirmOrder"
				@openSubcribe="openSubcribe"
			></orderGoods>
			<orderGoods
				:evaluate="evaluate"
				:deliveryType="orderInfo.shipping_type"
				:statusType="status.type"
				:sendType="orderInfo.delivery_type"
				:orderId="order_id"
				:oid="orderInfo.id"
				:cartInfo="cartInfo"
				:pid="pid"
				:jump="true"
				:refund_status="orderInfo.refund_status"
				:paid="orderInfo.paid"
				:virtualType="orderInfo.virtual_type"
				:is_refund_available="orderInfo.is_refund_available"
				:is_gift="is_gift"
				:gift_uid="orderInfo.gift_uid"
				@openSubcribe="openSubcribe"
			></orderGoods>
			<!-- #ifdef H5 || APP-PLUS -->
			<div class="goodCall" @click="goGoodCall">
				<span class="iconfont icon-kefu"></span>
				<span>{{ $t(`联系客服`) }}</span>
			</div>
			<!-- #endif -->
			<!-- #ifdef MP -->
			<div class="goodCall" @click="goGoodCall" v-if="routineContact == 0">
				<button hover-class="none">
					<span class="iconfont icon-kefu"></span>
					<span>{{ $t(`联系客服`) }}</span>
				</button>
			</div>
			<div class="goodCall" v-else>
				<button hover-class="none" open-type="contact">
					<span class="iconfont icon-kefu"></span>
					<span>{{ $t(`联系客服`) }}</span>
				</button>
			</div>
			<!-- #endif -->
			<view class="wrapper" v-if="isReturn == 1 && (is_gift == 0 || is_gift == 1)">
				<view class="item acea-row row-between">
					<view>{{ $t(`申请理由`) }}：</view>
					<view class="conter">{{ orderInfo.refund_reason }}</view>
				</view>
				<view class="item acea-row row-between">
					<view>{{ $t(`用户备注`) }}：</view>
					<view class="conter">{{ orderInfo.refund_explain }}</view>
				</view>
				<view class="item acea-row row-between" v-if="orderInfo.refund_img && orderInfo.refund_img.length">
					<view>{{ $t(`申请图片`) }}：</view>
					<view class="upload acea-row row-middle">
						<view class="conter">
							<view class="pictrue" v-for="(item, index) in orderInfo.refund_img" :key="index">
								<image :src="item"></image>
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class="wrapper" v-if="is_gift == 0 || is_gift == 1">
				<view class="item acea-row row-between">
					<view>{{ $t(`订单号`) }}：</view>
					<view class="conter acea-row row-middle row-right">
						<text>{{ orderInfo.order_id }}</text>
						<!-- #ifndef H5 -->
						<text class="copy" @tap="copy(orderInfo.order_id)">{{ $t(`复制`) }}</text>
						<!-- #endif -->
						<!-- #ifdef H5 -->
						<text class="copy copy-data" :data-clipboard-text="orderInfo.order_id">{{ $t(`复制`) }}</text>
						<!-- #endif -->
					</view>
				</view>
				<view class="item acea-row row-between">
					<view>{{ $t(`下单时间`) }}：</view>
					<view class="conter">{{ (orderInfo.add_time_y || '') + ' ' + (orderInfo.add_time_h || 0) }}</view>
				</view>
				<view class="item acea-row row-between">
					<view>{{ $t(`支付状态`) }}：</view>
					<view class="conter" v-if="orderInfo.paid">{{ $t(`已支付`) }}</view>
					<view class="conter" v-else>{{ $t(`未支付`) }}</view>
				</view>
				<view class="item acea-row row-between" v-if="orderInfo.paid">
					<view>{{ $t(`支付方式`) }}：</view>
					<view class="conter">{{ $t(orderInfo._status._payType) }}</view>
				</view>
				<view class="item acea-row row-between" v-if="orderInfo.mark && isReturn != 1">
					<view v-if="orderInfo.pid">{{ $t(`买家备注`) }}：</view>
					<view v-else>{{ $t(`买家留言`) }}：</view>
					<view class="conter">{{ orderInfo.mark }}</view>
				</view>
				<view class="item acea-row row-between" v-if="orderInfo.remark">
					<view>{{ $t(`商家备注`) }}：</view>
					<view class="conter">
						{{ orderInfo.remark }}
						<!-- #ifndef H5 -->
						<view v-if="orderInfo.virtual_type == 1" class="copy" @tap="copy(orderInfo.remark)">{{ $t(`复制`) }}</view>
						<!-- #endif -->
						<!-- #ifdef H5 -->
						<view v-if="orderInfo.virtual_type == 1" class="copy copy-data" :data-clipboard-text="orderInfo.remark">
							{{ $t(`复制`) }}
						</view>
						<!-- #endif -->
					</view>
				</view>
			</view>
			<view class="wrapper" v-if="customForm && customForm.length">
				<view class="item acea-row row-between" v-for="(item, index) in customForm" :key="index">
					<!-- 图片字段：新格式 type=img，或存量订单无类型但值是图片链接 -->
					<view class="upload" v-if="isImageFormItem(item)">
						<view class="diy-from-title">{{ item.title }}：</view>
						<view class="pictrue" v-for="(img, imgIndex) in formImageUrls(item)" :key="imgIndex">
							<image :src="img" @click="previewFormImages(item, imgIndex)"></image>
						</view>
					</view>
					<template v-else>
						<view class="diy-from-title">{{ item.title }}：</view>
						<view class="conter">{{ item.value }}</view>
					</template>
				</view>
				<view class="copy-text" @click="copyText()">{{ $t(`复制`) }}</view>
			</view>
			<!-- 退款订单详情 -->
			<view class="wrapper" v-if="isGoodsReturn && orderInfo.cartInfo[0].productInfo.virtual_type != 3 && (is_gift == 0 || is_gift == 1)">
				<view class="item acea-row row-between">
					<view>{{ $t(`收货人`) }}：</view>
					<view class="conter">{{ orderInfo.real_name }}</view>
				</view>
				<view class="item acea-row row-between">
					<view>{{ $t(`联系电话`) }}：</view>
					<view class="conter">{{ orderInfo.user_phone }}</view>
				</view>
				<view class="item acea-row row-between" v-if="orderInfo.shipping_type && orderInfo.shipping_type == 1">
					<view>{{ $t(`收货地址`) }}：</view>
					<view class="conter">{{ orderInfo.user_address }}</view>
				</view>
			</view>
			<view v-if="orderInfo.status != 0 && (is_gift == 0 || is_gift == 1)">
				<view class="wrapper" v-if="orderInfo.delivery_type == 'express'">
					<view class="item acea-row row-between">
						<view>{{ $t(`配送方式`) }}：</view>
						<view class="conter">{{ $t(`发货`) }}</view>
					</view>
					<view class="item acea-row row-between">
						<view>{{ $t(`快递公司`) }}：</view>
						<view class="conter">{{ orderInfo.delivery_name || '' }}</view>
					</view>
					<view class="item acea-row row-between">
						<view>{{ $t(`快递单号`) }}：</view>
						<view class="conter">{{ orderInfo.delivery_id || '' }}</view>
					</view>
				</view>
				<view class="wrapper" v-else-if="orderInfo.delivery_type == 'send'">
					<view class="item acea-row row-between">
						<view>{{ $t(`配送方式`) }}：</view>
						<view class="conter">{{ $t(`送货`) }}</view>
					</view>
					<view class="item acea-row row-between">
						<view>{{ $t(`配送人姓名`) }}：</view>
						<view class="conter">{{ orderInfo.delivery_name || '' }}</view>
					</view>
					<view class="item acea-row row-between">
						<view>{{ $t(`送货人电话`) }}：</view>
						<view class="conter acea-row row-middle row-right">
							{{ orderInfo.delivery_id || '' }}
							<text class="copy" @tap="goTel">{{ $t(`拨打`) }}</text>
						</view>
					</view>
				</view>
				<view class="wrapper" v-else-if="orderInfo.delivery_type == 'fictitious'">
					<view class="item acea-row row-between">
						<view>{{ $t(`虚拟发货`) }}：</view>
						<view class="conter">{{ $t(`已发货，请注意查收`) }}</view>
					</view>

					<view class="item acea-row row-between" v-if="orderInfo.fictitious_content">
						<view>{{ $t(`虚拟备注`) }}：</view>
						<view class="conter acea-row row-middle row-right">
							<text>{{ orderInfo.fictitious_content }}</text>
							<view class="copy" @click="copyText(orderInfo.fictitious_content)">{{ $t(`复制`) }}</view>
						</view>
					</view>
				</view>
			</view>
			<view class="wrapper" v-if="showAmountWrapper">
				<view class="item acea-row row-between" v-if="hasTotalPrice">
					<view>{{ $t(`商品总价`) }}：</view>
					<view class="conter">{{ $t(`￥`) }}{{ totalPriceText }}</view>
				</view>
				<view class="item acea-row row-between" v-if="orderInfo.pay_postage > 0">
					<view>{{ $t(`配送运费`) }}：</view>
					<view class="conter">{{ $t(`￥`) }}{{ parseFloat(orderInfo.pay_postage).toFixed(2) }}</view>
				</view>
				<view v-if="orderInfo.levelPrice > 0" class="item acea-row row-between">
					<view>{{ $t(`用户等级优惠`) }}：</view>
					<view class="conter">-{{ $t(`￥`) }}{{ parseFloat(orderInfo.levelPrice).toFixed(2) }}</view>
				</view>
				<view v-if="orderInfo.memberPrice > 0" class="item acea-row row-between">
					<view>{{ $t(`付费会员优惠`) }}：</view>
					<view class="conter">-{{ $t(`￥`) }}{{ parseFloat(orderInfo.memberPrice).toFixed(2) }}</view>
				</view>
				<view v-if="orderInfo.gift_price > 0" class="item acea-row row-between">
					<view>{{ $t(`礼品附加费用`) }}：</view>
					<view class="conter">-{{ $t(`￥`) }}{{ parseFloat(orderInfo.gift_price).toFixed(2) }}</view>
				</view>
				<view class="item acea-row row-between" v-if="orderInfo.coupon_price > 0">
					<view>{{ $t(`优惠券抵扣`) }}：</view>
					<view class="conter">-{{ $t(`￥`) }}{{ parseFloat(orderInfo.coupon_price).toFixed(2) }}</view>
				</view>
				<view class="item acea-row row-between" v-if="orderInfo.use_integral > 0">
					<view>{{ $t(`积分抵扣`) }}：</view>
					<view class="conter">-{{ $t(`￥`) }}{{ parseFloat(orderInfo.deduction_price).toFixed(2) }}</view>
				</view>
				<view class="actualPay acea-row row-right" v-if="!orderInfo.help_info.help_status">
					{{ $t(`实付款`) }}：
					<text class="money font-color">{{ $t(`￥`) }}{{ actualPayText }}</text>
				</view>
				<view class="actualPay acea-row row-right" v-else>
					<view class="pay-people">
						<image :src="orderInfo.help_info.pay_avatar" ></image>
						<view class="pay-nickname">
							{{ orderInfo.help_info.pay_nickname || '' }}
						</view>
					</view>
					{{ $t(`总代付`) }}：
					<text class="money font-color">{{ $t(`￥`) }}{{ actualPayText }}</text>
				</view>
				<view class="refundPay acea-row row-right" v-if="showRefundedPrice">
					{{ $t(`已退款`) }}：
					<text class="money font-color">{{ $t(`￥`) }}{{ refundedPriceText }}</text>
				</view>
			</view>
			<view style="height: 120rpx"></view>
			<view class="footer acea-row row-right row-middle" v-if="isGoodsReturn == false || status.type == 9 || orderInfo.refund_type || orderInfo.is_apply_refund">
				<view class="more" v-if="(invoice_func || invoiceData) && orderInfo.paid && !orderInfo.refund_status" @click="more">
					{{ $t(`更多`) }}
					<span class="iconfont icon-xiangshang"></span>
				</view>
				<view class="" v-else></view>
				<view class="more-box" v-if="moreBtn">
					<view class="more-btn" v-if="invoice_func && !invoiceData" @click="invoiceApply">{{ $t(`申请开票`) }}</view>
					<view class="more-btn" v-if="invoiceData" @click="aleartStatusChange">{{ $t(`查看发票`) }}</view>
				</view>
				<view class="right-btn">
					<view class="qs-btn" v-if="status.type == 0 || status.type == -9" @click.stop="cancelOrder">
						{{ $t(`取消订单`) }}
					</view>
					<view class="bnt bg-color" v-if="status.type == 0" @tap="pay_open(orderInfo.order_id)">{{ $t(`立即付款`) }}</view>
					<view
						@click="openSubcribe(`/pages/goods/${cartInfo.length > 1 ? 'goods_return_list' : 'goods_return'}/index?orderId=` + orderInfo.order_id + '&id=' + orderInfo.id)"
						class="bnt cancel"
						v-else-if="orderInfo.is_apply_refund && orderInfo.refund_status == 0 && cartInfo.length > 1 && !orderInfo.virtual_type && orderInfo.is_refund_available"
					>
						{{ cartInfo.length > 1 ? $t(`批量退款`) : $t(`申请退款`) }}
					</view>
					<navigator :render-link="false"
						class="bnt cancel"
						v-if="orderInfo.delivery_type == 'express' && status.class_status == 3 && status.type == 2 && !split.length"
						hover-class="none"
						:url="'/pages/goods/goods_logistics/index?orderId=' + orderInfo.order_id"
					>
						{{ $t(`查看物流`) }}
					</navigator>
					<view class="bnt bg-color" v-if="orderInfo.type == 3 && orderInfo.refund_type == 0 && orderInfo.paid" @tap="goJoinPink">
						{{ $t(`查看拼团`) }}
					</view>
					<view class="bnt bg-color" v-if="status.class_status == 3 && !split.length" @click="confirmOrder()">
						{{ $t(`确认收货`) }}
					</view>
					<view class="bnt bg-color" v-if="orderInfo.paid == 1 && !is_gift && isReturn != 1" @tap="goOrderConfirm">{{ $t(`再次购买`) }}</view>
					<view class="bnt bg-color" v-if="orderInfo.paid == 1 && is_gift != 0 && orderInfo.gift_uid == 0" @tap="giftModalShow = true">{{ $t(`送给好友`) }}</view>
					<view
						class="bnt bg-color"
						v-if="[1, 2, 4].includes(orderInfo.refund_type) && !orderInfo.is_cancel && orderInfo.type != 3 && orderInfo.refund_status != 2"
						@tap="cancelRefundOrder"
					>
						{{ $t(`取消申请`) }}
					</view>
					<view class="bnt bg-color refundBnt" v-if="orderInfo.refund_type == 4" @tap="refundInput">
						{{ $t(`填写退货信息`) }}
					</view>
					<navigator :render-link="false"
						class="bnt cancel refundBnt"
						v-if="orderInfo.refund_type == 5"
						hover-class="none"
						:url="'/pages/goods/goods_logistics/index?orderId=' + orderInfo.order_id + '&type=refund'"
					>
						{{ $t(`查看退货物流`) }}
					</navigator>
					<view class="bnt cancel" v-if="(status.type == 4 && !split.length) || status.type == -2" @tap="delOrder">
						{{ $t(`删除订单`) }}
					</view>
				</view>
			</view>
		</view>
		<home v-show="!aleartStatus && !invShow"></home>
		<view class="mask" v-if="refund_close" @click="refund_close = false"></view>
		<!-- 		<view class="refund-input" :class="refund_close ? 'on' : ''">
			<view class="input-msg">
				<text class='iconfont icon-guanbi5' @tap='refund_close = false'></text>
				<view class="refund-input-title">{{ $t(`填写物流单号`) }}
				</view>
				<view class="refund-input-sty">
					<input type="text" v-model="express_num" :placeholder="$t(`请输入物流单号`)" />
				</view>
				<view class="refund-bth">
					<view class="submit-refund" @click="refundSubmit()">{{ $t(`提交`) }}</view>
				</view>
			</view>
		</view> -->
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<invoiceModal :aleartStatus="aleartStatus" :invoiceData="invoiceData" @close="aleartStatus = false"></invoiceModal>
		<view class="mask invoice-mask" v-if="aleartStatus || giftModalShow" @click="aleartStatus = false"></view>
		<view class="mask more-mask" v-if="moreBtn" @click="moreBtn = false"></view>
		<giftModal :aleartStatus="giftModalShow" :giftData="giftModalData" @shareH5="shareH5" @close="giftModalShow = false"></giftModal>
		<canvas class="canvas" canvas-id="posterCanvas"></canvas>
		<view class="share-box" v-if="H5ShareBox">
			<image :src="imgHost + '/statics/images/share-info.png'" @click="H5ShareBox = false"></image>
		</view>
		<invoice-picker
			:inv-show="invShow"
			:is-special="special_invoice"
			:url-query="urlQuery"
			:inv-checked="invChecked"
			:order-id="order_id"
			:inv-list="invList"
			:is-order="1"
			@inv-close="invClose"
			@inv-change="invSub"
			@inv-cancel="invCancel"
		></invoice-picker>
	</view>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { onLoad, onShow, onHide, onReady } from '@dcloudio/uni-app';
// #ifdef MP
import { onShareAppMessage, onShareTimeline } from '@dcloudio/uni-app';
// #endif
import { storeToRefs } from 'pinia';
import { getOrderDetail, refundOrderDetail, orderAgain, orderTake, orderDel, refundOrderDel, orderCancel, refundExpress, cancelRefundOrder as cancelRefundOrderApi } from '@/api/order.js';
import { openOrderRefundSubscribe } from '@/utils/subscribeMessage.js';
// 导入重命名 getCustomerTypeApi：与本地方法 getCustomerType 同名冲突
import { getCustomerType as getCustomerTypeApi } from '@/api/api.js';
import { getCustomer } from '@/utils/index.js';
// 导入重命名 getUserInfoApi：与本地方法 getUserInfo 同名冲突
import { getUserInfo as getUserInfoApi, invoiceList, makeUpinvoice } from '@/api/user.js';
import home from '@/components/home';
import orderGoods from '@/components/orderGoods';
import ClipboardJS from '@/plugin/clipboard/clipboard.js';
import { toLogin } from '@/libs/login.js';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
// #ifdef H5
import Auth from '@/libs/wechat';
// #endif
import invoicePicker from '../components/invoicePicker/index.vue';
import invoiceModal from '../components/invoiceModal/index.vue';
import giftModal from '../order_pay_status/components/giftModal.vue';
import zbCode from '@/components/zb-code/zb-code.vue';
import { HTTP_REQUEST_URL } from '@/config/app.js';
import { userShare } from '@/api/user.js';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';

defineOptions({ name: 'OrderDetails' });

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const imgHost = HTTP_REQUEST_URL;
const customForm = ref(''); //自定义留言

/**
 * 自定义表单图片字段判断：新格式 type=img，或存量订单无类型但值是图片链接
 * （旧数据 label 存类型值，新数据 label 是 custom_field_N，不能再用 label 判断）
 */
const IMAGE_EXT_RE = /\.(jpe?g|png|gif|webp|bmp)(\?.*)?$/i;
function formImageUrls(item) {
	if (!item || !item.value) return [];
	const list = Array.isArray(item.value) ? item.value : [item.value];
	return list.filter((v) => typeof v === 'string' && v.indexOf('http') === 0);
}
function isImageFormItem(item) {
	if (!item) return false;
	if (item.type === 'img') return true;
	if (item.type) return false;
	const urls = formImageUrls(item);
	return urls.length > 0 && urls.every((v) => IMAGE_EXT_RE.test(v) || v.indexOf('/uploads/') !== -1);
}
function previewFormImages(item, index) {
	const urls = formImageUrls(item);
	if (!urls.length) return;
	uni.previewImage({ urls, current: index });
}

//二维码参数
const codeShow = ref(false);
const cid = ref('1');
const ifShow = ref(true);
const val = ref(''); // 要生成的二维码值
const size = ref(200); // 二维码大小
const unit = ref('upx'); // 单位
const background = ref('#FFF'); // 背景色
const foreground = ref('#000'); // 前景色
const pdground = ref('#000'); // 角标色
const icon = ref(''); // 二维码图标
const iconsize = ref(40); // 二维码图标大小
const lv = ref(3); // 二维码容错级别 ， 一般不用设置，默认就行
const onval = ref(true); // val值变化时自动重新生成二维码
const loadMake = ref(true); // 组件加载完成后自动生成二维码
const src = ref(''); // 二维码生成后的图片地址或base64
const codeSrc = ref('');
const wd = ref(0);
const hg = ref(0);
const mpUrl = ref('');

const order_id = ref('');
const evaluate = ref(0);
const cartInfo = ref([]); //购物车产品
const pid = ref(0); //上级订单ID
const split = ref([]); //分单商品
const orderInfo = ref({
	help_info: {},
	system_store: {},
	_status: {}
}); //订单详情
const system_store = ref({});
const isGoodsReturn = ref(false); //是否为退款订单
const status = ref({}); //订单底部按钮状态
const refund_close = ref(false);
const isClose = ref(false);
const H5ShareBox = ref(false);
const giftModalShow = ref(false);
const payMode = ref([
	{
		name: i18n.t(`微信支付`),
		icon: 'icon-weixinzhifu',
		value: 'weixin',
		title: i18n.t(`使用微信快捷支付`),
		payStatus: true
	},
	// #ifdef H5 || APP-PLUS
	{
		name: i18n.t(`支付宝支付`),
		icon: 'icon-zhifubao',
		value: 'alipay',
		title: i18n.t(`使用支付宝支付`),
		payStatus: true
	},
	// #endif
	{
		name: i18n.t(`余额支付`),
		icon: 'icon-yuezhifu',
		value: 'yue',
		title: i18n.t(`可用余额`),
		number: 0,
		payStatus: true
	},
	{
		name: i18n.t(`好友代付`),
		icon: 'icon-haoyoudaizhifu',
		value: 'friend',
		title: i18n.t(`找微信好友支付`),
		payStatus: 1
	},
	{
		name: i18n.t(`通联支付`),
		icon: 'icon-tonglianzhifu1',
		value: 'allinpay',
		title: i18n.t(`使用通联支付付款`),
		payStatus: 1
	}
]);
const pay_close = ref(false);
const pay_order_id = ref('');
const totalPrice = ref('0');
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const routineContact = ref(0);
const express_num = ref('');
const invoice_func = ref(false);
const invoiceData = ref({});
const invoice_id = ref(0);
const invChecked = ref('');
const moreBtn = ref(false);
const invShow = ref(false);
const aleartStatus = ref(false); //发票弹窗
const special_invoice = ref(false);
const invList = ref([]);
const customerInfo = ref({});
const userInfo = ref({});
const isReturn = ref('');
const urlQuery = ref('');
const is_gift = ref(0); // 0正常商品 || 无人领取   1 购买者   2领取人
const giftData = ref(null);
const giftModalData = ref(null);
const mpGiftImg = HTTP_REQUEST_URL + '/statics/images/gift_share.jpg';

// 遗留：invCancel/getInvoiceList 原写 this.invTitle（data 未声明、模板未使用，恒为非响应式实例属性），以同名普通变量保持同等语义
var invTitle;
// 遗留：onShareTimeline 原取 this.id / this.uid（均未在 data/computed 声明，恒为 undefined），以同名 undefined 变量保持同等语义
var id, uid;

// ---------- computed ----------
const hasTotalPrice = computed(() => {
	const orderInfoLocal = orderInfo.value || {};
	return orderInfoLocal.total_price !== undefined && orderInfoLocal.total_price !== null && orderInfoLocal.total_price !== '' && !Number.isNaN(parseFloat(orderInfoLocal.total_price));
});
const hasPayPrice = computed(() => {
	const payPrice = parseFloat((orderInfo.value || {}).pay_price);
	return !Number.isNaN(payPrice);
});
const showAmountWrapper = computed(() => {
	return (is_gift.value == 0 || is_gift.value == 1) && (hasTotalPrice.value || hasPayPrice.value || showRefundedPrice.value);
});
const totalPriceText = computed(() => {
	const orderInfoLocal = orderInfo.value || {};
	// 局部变量重命名 totalPriceNum：避免与同名 ref（totalPrice）互相遮蔽
	const totalPriceNum = parseFloat(orderInfoLocal.total_price);
	const vipTruePrice = parseFloat(orderInfoLocal.vip_true_price);
	const total = (Number.isNaN(totalPriceNum) ? 0 : totalPriceNum) + (Number.isNaN(vipTruePrice) ? 0 : vipTruePrice);
	return total.toFixed(2);
});
const actualPayText = computed(() => {
	const payPrice = parseFloat((orderInfo.value || {}).pay_price);
	return Number.isNaN(payPrice) ? '0.00' : payPrice.toFixed(2);
});
const showRefundedPrice = computed(() => {
	const orderInfoLocal = orderInfo.value || {};
	const refundedPrice = parseFloat(orderInfoLocal.refunded_price);
	return Number(isReturn.value) === 1 && refundedPrice > 0;
});
const refundedPriceText = computed(() => {
	const refundedPrice = parseFloat((orderInfo.value || {}).refunded_price);
	return Number.isNaN(refundedPrice) ? '0.00' : refundedPrice.toFixed(2);
});

// ---------- 生命周期 ----------
onLoad((options) => {
	// 订单数据结构已迁移至中台 OrderCenter，旧版扁平接口（order/detail）已废弃
	// 延用原页面路径作为入口，重定向到新版引擎页面，避免修改 20+ 个跳转入口
	if (options.order_id) {
		uni.redirectTo({ url: '/pages/center/order/detail/index?order_no=' + options.order_id });
		return;
	}
	if (options.order_id) {
		order_id.value = options.order_id;
		isReturn.value = options.isReturn;
	}
	if (options.invoice_id) {
		invoice_id.value = options.invoice_id;
	}
});

onShow(() => {
	if (isLogin.value) {
		getOrderInfo();
		getUserInfo();
		getCustomerType();
		let opt = wx.getEnterOptionsSync();
		if (opt.scene == '1038' && opt.referrerInfo.appId == 'wxef277996acc166c3') {
			// 代表从收银台小程序返回
			let extraData = opt.referrerInfo.extraData;
			if (!extraData) {
				// "当前通过物理按键返回，未接收到返参，建议自行查询交易结果";
				getOrderInfo();
			} else {
				if (extraData.code == 'success') {
					// "支付成功";
					getOrderInfo();
				} else if (extraData.code == 'cancel') {
					// "支付已取消";
					util.Tips({
						title: i18n.t(`支付已取消`)
					});
				} else {
					// "支付失败：" + extraData.errmsg;
					util.Tips({
						title: i18n.t(`支付失败：${extraData.errmsg}`)
					});
				}
			}
		}
	} else {
		toLogin();
	}
});
onHide(() => {
	isClose.value = true;
});
onReady(() => {
	// #ifdef H5
	nextTick(function () {
		const clipboard = new ClipboardJS('.copy-data');
		clipboard.on('success', () => {
			util.Tips({
				title: i18n.t(`复制成功`)
			});
		});
		const address = new ClipboardJS('.copy-refund-msg');
		address.on('success', () => {
			util.Tips({
				title: i18n.t(`复制成功`)
			});
		});
	});

	// #endif
});
/**
 * 用户点击右上角分享
 */
// #ifdef MP
onShareAppMessage(() => {
	userShare();
	return {
		title: giftModalData.value.gift_mark || '',
		imageUrl: mpGiftImg || '',
		path: '/pages/goods/receive_gift/index?id=' + giftModalData.value.id + '&spid=' + appStore.uid
	};
});
onShareTimeline(() => {
	userShare();
	return {
		title: giftModalData.value.gift_mark,
		query: {
			id: id,
			spid: uid || 0
		},
		path: '/pages/goods/receive_gift/index?id=' + giftModalData.value.id + '&spid=' + appStore.uid,
		imageUrl: mpGiftImg
	};
});
// #endif

// ---------- methods ----------
// #ifdef H5
// 微信分享；
function setOpenShare() {
	if (Auth.isWeixin()) {
		let configAppMessage = {
			desc: giftModalData.value.gift_mark,
			title: giftModalData.value.title,
			link: window.location.protocol + '//' + window.location.host + '/pages/goods/receive_gift/index?id=' + giftModalData.value.id + '&spid=' + appStore.uid,
			imgUrl: mpGiftImg
		};
		Auth.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareAppMessage', 'onMenuShareTimeline'], configAppMessage);
	}
}
// #endif
function qrR(res) {
	codeSrc.value = res;
}
function shareH5() {
	H5ShareBox.value = true;
}
function cancelRefundOrder(orderId) {
	uni.showModal({
		title: i18n.t(`取消申请`),
		content: i18n.t(`您确认放弃此次申请吗`),
		success: (res) => {
			if (res.confirm) {
				cancelRefundOrderApi(order_id.value)
					.then((res) => {
						return util.Tips(
							{
								title: i18n.t(`操作成功`),
								icon: 'success'
							},
							{
								tab: 4,
								url: '/pages/users/user_return_list/index'
							}
						);
					})
					.catch((err) => {
						return util.Tips({
							title: err
						});
					});
			}
		}
	});
}
function refundInput() {
	uni.navigateTo({
		url: `/pages/goods/order_refund_goods/index?orderId=` + order_id.value
	});
}
function getCustomerType() {
	getCustomerTypeApi()
		.then((res) => {
			customerInfo.value = res.data;
		})
		.catch((err) => {
			util.Tips({
				title: err
			});
		});
}
function goGoodCall() {
	getCustomer(`/pages/extension/customer_list/chat?orderId=${order_id.value}&isReturn=${isReturn.value}`);
}
function openSubcribe(e) {
	let page = e;
	// #ifndef MP
	uni.navigateTo({
		url: page
	});
	// #endif
	// #ifdef MP
	uni.showLoading({
		title: i18n.t(`正在加载中`)
	});
	openOrderRefundSubscribe()
		.then((res) => {
			uni.hideLoading();
			uni.navigateTo({
				url: page
			});
		})
		.catch((err) => {
			uni.hideLoading();
		});
	// #endif
}
function goReturnGoods() {}
/**
 * 拨打电话
 */
function makePhone() {
	uni.makePhoneCall({
		phoneNumber: system_store.value.phone
	});
}
/**
 * 打开地图
 *
 */
function showMaoLocation() {
	if (!system_store.value.latitude || !system_store.value.longitude)
		return util.Tips({
			title: i18n.t(`缺少经纬度信息无法查看地图`)
		});
	uni.openLocation({
		latitude: parseFloat(system_store.value.latitude),
		longitude: parseFloat(system_store.value.longitude),
		scale: 8,
		name: system_store.value.name,
		address: system_store.value.address + system_store.value.detailed_address,
		success: function () {}
	});
}
/**
 * 打开支付组件
 *
 */
function pay_open() {
	uni.navigateTo({
		url: `/pages/goods/cashier/index?order_id=${orderInfo.value.order_id}&from_type=order`
	});
	// pay_close.value = true;
	// pay_order_id.value = orderInfo.value.order_id;
	// totalPrice.value = orderInfo.value.pay_price;
}
/**
 * 支付失败回调
 *
 */
function pay_fail() {
	pay_close.value = false;
	pay_order_id.value = '';
}
/**
 * 登录授权回调
 *
 */
function onLoadFun() {
	getOrderInfo();
	getUserInfo();
}
/**
 * 获取用户信息
 *
 */
function getUserInfo() {
	getUserInfoApi().then((res) => {
		userInfo.value = res.data;
		// #ifdef H5
		payMode.value[2].number = res.data.now_money;
		// #endif
		// #ifdef APP-PLUS
		payMode.value[2].number = res.data.now_money;
		// #endif
		// #ifdef MP
		payMode.value[1].number = res.data.now_money;
		// #endif
		payMode.value = payMode.value;
	});
}
/**
 * 获取订单详细信息
 *
 */
function getOrderInfo() {
	uni.showLoading({
		title: i18n.t(`正在加载中`)
	});
	let obj = '';
	if (Number(isReturn.value) === 1) {
		obj = refundOrderDetail(order_id.value);
	} else {
		obj = getOrderDetail(order_id.value);
	}
	obj
		.then((res) => {
			if (res.data.pid && res.data.pid == -1) {
				util.Tips(
					{
						title: i18n.t(`订单信息不存在`)
					},
					'/pages/goods/order_list/index'
				);
			}
			let _type = res.data._status._type;
			uni.hideLoading();
			orderInfo.value = res.data;
			//处理自定义留言非必填项的数据展示
			let arr = [];
			orderInfo.value.custom_form.map((i) => {
				if (i.value != '') {
					arr.push(i);
				}
			});
			customForm.value = arr;
			cartInfo.value = res.data.cartInfo;
			pid.value = res.data.pid;
			split.value = res.data.split;
			evaluate.value = _type == 3 ? 3 : 0;
			system_store.value = res.data.system_store;
			invoiceData.value = res.data.invoice;
			if (res.data.is_gift) {
				let giftStatus = res.data.gift_uid === appStore.uid;
				is_gift.value = giftStatus ? 2 : 1;
				uni.setNavigationBarTitle({
					title: i18n.t(`礼物详情`)
				});
				giftData.value = {
					avatar: res.data.avatar,
					gift_mark: res.data.gift_mark,
					nickname: res.data.nickname
				};
				giftModalData.value = {
					image: res.data.cartInfo[0].productInfo.image,
					title: res.data.cartInfo[0].productInfo.store_name,
					message: res.data.gift_mark,
					id: res.data.id,
					avatar: res.data.avatar,
					nickname: res.data.nickname,
					gift_mark: res.data.gift_mark,
					code: res.data.gift_code
				};
				//#ifdef H5
				setOpenShare();
				//#endif
			}
			if(!res.data.is_gift || is_gift.value == 2) {
				uni.hideShareMenu();
			}
			if (invoiceData.value) {
				invoiceData.value.pay_price = res.data.pay_price;
			}
			invoice_func.value = res.data.invoice_func;
			special_invoice.value = res.data.special_invoice;
			routineContact.value = Number(res.data.routine_contact_type);
			// #ifdef MP
			if (orderInfo.value.code) {
				codeSrc.value = orderInfo.value.code || '';
			}
			// #endif
			if (orderInfo.value.refund_status != 0) {
				isGoodsReturn.value = true;
			} else {
				isReturn.value = 0;
			}
			if (invoice_id.value && !invoiceData.value) {
				invChecked.value = invoice_id.value || '';
				invoiceApply();
			}
			payMode.value.map((item) => {
				if (item.value == 'weixin') {
					item.payStatus = res.data.pay_weixin_open ? true : false;
				}
				if (item.value == 'alipay') {
					item.payStatus = res.data.ali_pay_status ? true : false;
				}
				if (item.value == 'yue') {
					item.payStatus = res.data.yue_pay_status == 1 ? true : false;
				}
				if (item.value == 'friend') {
					item.payStatus = res.data.friend_pay_status == 1 ? true : false;
				}
				if (item.value == 'allinpay') {
					item.payStatus = res.data.pay_allin_open == 1 ? true : false;
				}
			});

			getOrderStatus();
		})
		.catch((err) => {
			uni.hideLoading();
			util.Tips(
				{
					title: err
				},
				'/pages/goods/order_list/index'
			);
		});
}
// 不开发票
function invCancel() {
	invChecked.value = '';
	invTitle = i18n.t(`不开发票`);
	invShow.value = false;
}
// 选择发票
function invSub(id) {
	invChecked.value = id;
	let data = {
		order_id: order_id.value,
		invoice_id: invChecked.value
	};
	makeUpinvoice(data)
		.then((res) => {
			uni.showToast({
				title: i18n.t(`申请成功`),
				icon: 'success'
			});
			invShow.value = false;
			aleartStatus.value = true;
			getOrderInfo();
		})
		.catch((err) => {
			uni.showToast({
				title: err,
				icon: 'none'
			});
		});
}
// 关闭发票
function invClose() {
	invShow.value = false;
	getInvoiceList();
}
//申请开票
function invoiceApply() {
	urlQuery.value = `&specialInvoice=${userInfo.value.special_invoice}`;
	getInvoiceList();
	moreBtn.value = false;
	invShow.value = true;
}
function aleartStatusChange() {
	moreBtn.value = false;
	aleartStatus.value = true;
}
function getInvoiceList() {
	uni.showLoading({
		title: i18n.t(`正在加载中`)
	});
	invoiceList()
		.then((res) => {
			uni.hideLoading();
			invList.value = res.data.map((item) => {
				item.id = item.id.toString();
				return item;
			});
			const result = invList.value.find((item) => item.id == invChecked.value);
			if (result) {
				let name = '';
				name += result.header_type === 1 ? i18n.t(`个人`) : i18n.t(`企业`);
				name += result.type === 1 ? i18n.t(`普通`) : i18n.t(`专用`);
				name += i18n.t(`发票`);
				invTitle = name;
			}
		})
		.catch((err) => {
			uni.showToast({
				title: err,
				icon: 'none'
			});
		});
}
function more() {
	moreBtn.value = !moreBtn.value;
}
/**
 *
 * 剪切订单号
 */
// #ifndef H5
function copy(text) {
	uni.setClipboardData({
		data: text
	});
}
// #endif
// #ifndef H5
function copyAddress() {
	uni.setClipboardData({
		data: orderInfo.value._status.refund_name + orderInfo.value._status.refund_phone + orderInfo.value._status.refund_address,
		success() {
			uni.Tips({
				title: i18n.t(`复制成功`),
				icon: 'success'
			});
		}
	});
}
// #endif
function copyText(text) {
	let str = '';
	if (text) {
		str = text;
	} else {
		customForm.value.map((e) => {
			if (e.label !== 'img') {
				str += e.title + e.value;
			}
		});
	}
	uni.setClipboardData({
		data: str
	});
}
// #ifdef H5
function copyAddress() {
	// let msg =
	// return msg
}
// #endif
/**
 * 打电话
 */
function goTel() {
	uni.makePhoneCall({
		phoneNumber: orderInfo.value.delivery_id
	});
}
/**
 * 设置底部按钮
 *
 */
function getOrderStatus() {
	// 局部变量重命名 orderInfoLocal/statusLocal：避免与同名 ref（orderInfo/status）互相遮蔽
	let orderInfoLocal = orderInfo.value || {},
		_status = orderInfoLocal._status || {
			_type: 0
		},
		statusLocal = {};
	let type = parseInt(_status._type),
		delivery_type = orderInfoLocal.delivery_type,
		seckill_id = orderInfoLocal.seckill_id ? parseInt(orderInfoLocal.seckill_id) : 0,
		bargain_id = orderInfoLocal.bargain_id ? parseInt(orderInfoLocal.bargain_id) : 0,
		discount_id = orderInfoLocal.discount_id ? parseInt(orderInfoLocal.discount_id) : 0,
		combination_id = orderInfoLocal.combination_id ? parseInt(orderInfoLocal.combination_id) : 0;
	statusLocal = {
		type: type == 9 ? -9 : type,
		class_status: 0
	};
	if (type == 1 && combination_id > 0) statusLocal.class_status = 1; //查看拼团
	if (type == 2 && delivery_type == 'express') statusLocal.class_status = 2; //查看物流
	if (type == 2) statusLocal.class_status = 3; //确认收货
	if (type == 4 || type == 0) statusLocal.class_status = 4; //删除订单
	if (!seckill_id && !bargain_id && !combination_id && !discount_id && !orderInfoLocal.type && (type == 3 || type == 4)) statusLocal.class_status = 5; //再次购买
	status.value = statusLocal;
}
/**
 * 去拼团详情
 *
 */
function goJoinPink() {
	uni.navigateTo({
		url: '/pages/activity/goods_combination_status/index?id=' + orderInfo.value.pink_id
	});
}
/**
 * 再此购买
 *
 */
function goOrderConfirm() {
	orderAgain(orderInfo.value.order_id)
		.then((res) => {
			return uni.navigateTo({
				url: '/pages/center/order/create/index?new=1&cartId=' + res.data.cateId
			});
		})
		.catch((err) => {
			return util.Tips({
				title: err
			});
		});
}
function confirmOrder(orderId) {
	// #ifdef MP
	if (wx.openBusinessView && orderInfo.value.order_shipping_open && orderInfo.value.trade_no && orderInfo.value.uid == orderInfo.value.pay_uid) {
		uni.showLoading({
			title: i18n.t(`加载中`)
		});
		wx.openBusinessView({
			businessType: 'weappOrderConfirm',
			extraData: {
				transaction_id: orderInfo.value.trade_no
			},
			success() {},
			fail(err) {
				uni.hideLoading();
				return util.Tips({
					title: err.errMsg
				});
			},
			complete() {
				uni.hideLoading();
			}
		});
	} else {
		defaultTake(orderId);
	}
	// #endif
	// #ifndef MP
	defaultTake(orderId);
	// #endif
}
function defaultTake(orderId) {
	uni.showModal({
		title: i18n.t(`确认收货`),
		content: i18n.t(`为保障权益，请收到货确认无误后，再确认收货`),
		success: (res) => {
			if (res.confirm) {
				orderTake(orderId ? orderId : order_id.value)
					.then((res) => {
						return util.Tips(
							{
								title: i18n.t(`操作成功`),
								icon: 'success'
							},
							function () {
								getOrderInfo();
							}
						);
					})
					.catch((err) => {
						return util.Tips({
							title: err
						});
					});
			}
		}
	});
}
/**
 *
 * 删除订单
 */
function delOrder() {
	uni.showModal({
		title: i18n.t(`删除订单`),
		content: i18n.t(`确定删除该订单`),
		success: (res) => {
			if (res.confirm) {
				(isReturn.value ? refundOrderDel : orderDel)(order_id.value)
					.then((res) => {
						if (status.value.type == -2) {
							return util.Tips(
								{
									title: i18n.t(`删除成功`),
									icon: 'success'
								},
								{
									tab: 5,
									url: '/pages/users/user_return_list/index'
								}
							);
						} else {
							return util.Tips(
								{
									title: i18n.t(`删除成功`),
									icon: 'success'
								},
								{
									tab: 5,
									url: '/pages/goods/order_list/index'
								}
							);
						}
					})
					.catch((err) => {
						return util.Tips({
							title: err
						});
					});
			} else if (res.cancel) {
				return util.Tips({
					title: i18n.t(`已取消`)
				});
			}
		}
	});
}
function cancelOrder() {
	uni.showModal({
		title: i18n.t(`提示`),
		content: i18n.t(`确认取消该订单`),
		success: function (res) {
			if (res.confirm) {
				orderCancel(orderInfo.value.order_id)
					.then((data) => {
						// #ifndef MP
						util.Tips(
							{
								title: data.msg
							},
							{
								tab: 3
							}
						);
						// #endif
						// #ifdef MP
						util.Tips(
							{
								title: data.msg
							},
							'/pages/goods/order_list/index'
						);
						// #endif
					})
					.catch(() => {
						getOrderInfo();
					});
			} else if (res.cancel) {
			}
		}
	});
}
</script>
<style scoped lang="scss">
.refund-tip {
	font-size: 24rpx;
	margin-top: 10rpx;
	color: var(--view-theme);

	.iconfont {
		font-size: 24rpx;
		margin-right: 6rpx;
	}
}

.refund-tip1 {
	font-size: 24rpx;
	color: var(--view-theme);

	.iconfont {
		font-size: 24rpx;
		margin-right: 6rpx;
	}
}

.qs-btn {
	width: auto;
	height: 60rpx;
	text-align: center;
	line-height: 60rpx;
	border-radius: 50rpx;
	font-size: 27rpx;
	white-space: nowrap;
	padding: 0 26rpx;
	color: #666;
	border: 1px solid #ccc;
	margin-right: 20rpx;
}

.refund-input {
	position: fixed;
	bottom: 0;
	left: 0;
	width: 100%;
	border-radius: 16rpx 16rpx 0 0;
	background-color: #fff;
	z-index: 99;
	padding: 40rpx 0 70rpx 0;
	transition: all 0.3s cubic-bezier(0.25, 0.5, 0.5, 0.9);
	transform: translate3d(0, 100%, 0);

	.refund-input-title {
		font-size: 32rpx;
		margin-bottom: 60rpx;
		color: #282828;
	}

	.refund-input-sty {
		border: 1px solid #ddd;
		padding: 20rpx 20rpx;
		border-radius: 40rpx;
		width: 100%;
		margin: 20rpx 65rpx;
	}

	.input-msg {
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		position: relative;
		margin: 0 65rpx;

		.iconfont {
			position: absolute;
			font-size: 32rpx;
			color: #282828;
			top: 8rpx;
			right: -30rpx;
		}
	}

	.refund-bth {
		display: flex;
		margin: 0 65rpx;
		margin-top: 20rpx;
		justify-content: space-around;
		width: 100%;

		.close-refund {
			padding: 24rpx 80rpx;
			border-radius: 80rpx;
			color: #fff;
			background-color: #ccc;
		}

		.submit-refund {
			width: 100%;
			padding: 24rpx 0rpx;
			text-align: center;
			border-radius: 80rpx;
			color: #fff;
			background-color: var(--view-theme);
		}
	}
}

.refund-input.on {
	transform: translate3d(0, 0, 0);
}

.goodCall {
	color: var(--view-theme);
	text-align: center;
	width: 100%;
	height: 86rpx;
	padding: 0 30rpx;
	border-top: 1rpx solid #eee;
	font-size: 30rpx;
	line-height: 86rpx;
	background: #fff;

	.icon-kefu {
		font-size: 36rpx;
		margin-right: 15rpx;
	}

	/* #ifdef MP */
	button {
		display: flex;
		align-items: center;
		justify-content: center;
		height: 86rpx;
		font-size: 30rpx;
		color: var(--view-theme);
	}

	/* #endif */
}

.order-details {
	padding-bottom: calc(15rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
	padding-bottom: calc(15rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
}

.order-details .header {
	padding: 0 30rpx;
	height: 150rpx;
	display: flex;
	align-items: center;
	flex-wrap: nowrap;
}

.order-details .header.on {
	background-color: #666 !important;
}

.order-details .header .pictrue {
	width: 110rpx;
	height: 110rpx;
}

.order-details .header .pictrue image {
	width: 100%;
	height: 100%;
}

.order-details .header .data {
	color: rgba(255, 255, 255, 0.8);
	font-size: 24rpx;
	margin-left: 27rpx;
}

.order-details .header .data.on {
	margin-left: 0;
}

.order-details .header .data .state {
	font-size: 30rpx;
	font-weight: bold;
	color: #fff;
	margin-bottom: 7rpx;
}

.order-details .header .data .time {
	margin-left: 3rpx;
}

.order-details .nav {
	background-color: #fff;
	font-size: 26rpx;
	color: #282828;
	padding: 25rpx 0;
}

.order-details .nav .navCon {
	padding: 0 40rpx;
}

.order-details .nav .on {
	color: var(--view-theme);
}

.order-details .nav .progress {
	padding: 0 65rpx;
	margin-top: 10rpx;
}

.order-details .nav .progress .line {
	width: 100rpx;
	height: 2rpx;
	background-color: #939390;
}

.order-details .nav .progress .iconfont {
	font-size: 25rpx;
	color: #939390;
	margin-top: -2rpx;
}

.order-details .address {
	font-size: 26rpx;
	color: #868686;
	background-color: #fff;
	padding: 35rpx 30rpx;
	margin-bottom: 12rpx;
	.icon {
		.iconfont {
			width: 44rpx;
			height: 44rpx;
			background: var(--view-minorColorT);
			font-size: 20rpx;
			border-radius: 50%;
			text-align: center;
			line-height: 44rpx;
			color: var(--view-theme);
			margin-left: 26rpx;
		}
	}
}

.order-details .address .name {
	font-size: 30rpx;
	color: #282828;
	margin-bottom: 15rpx;
}

.order-details .address .name .phone {
	margin-left: 40rpx;
}

.order-details .line {
	width: 100%;
	height: 3rpx;
}

.order-details .line image {
	width: 100%;
	height: 100%;
	display: block;
}

.order-details .wrapper {
	background-color: #fff;
	margin-top: 12rpx;
	padding: 30rpx;
}

.order-details .wrapper .acea-row {
	display: flex;
	flex-wrap: nowrap;
}

.order-details .wrapper .item {
	font-size: 28rpx;
	color: #282828;
}

.order-details .wrapper .item ~ .item {
	margin-top: 20rpx;
	white-space: normal;
	word-break: break-all;
	word-wrap: break-word;
}

.order-details .wrapper .item .conter {
	color: #868686;
	width: 480srpx;
	display: flex;
	flex-wrap: nowrap;
	justify-content: flex-end;
}

.order-details .wrapper .item .conter .copy {
	font-size: 20rpx;
	color: #333;
	border-radius: 3rpx;
	border: 1rpx solid #666;
	padding: 3rpx 15rpx;
	margin-left: 24rpx;
	white-space: nowrap;
}

.order-details .wrapper .actualPay {
	border-top: 1rpx solid #eee;
	margin-top: 30rpx;
	padding-top: 30rpx;
	display: flex;
	align-items: center;

	.pay-people {
		display: flex;
		align-items: center;

		image {
			width: 40rpx;
			height: 40rpx;
			border-radius: 50%;
		}

		.pay-nickname {
			margin-right: 20rpx;
			padding: 0 10rpx;
		}
	}
}

.order-details .wrapper .actualPay .money {
	font-weight: bold;
	font-size: 30rpx;
}

.order-details .wrapper .refundPay {
	margin-top: 16rpx;
	display: flex;
	align-items: center;
	font-size: 28rpx;
	color: #282828;
}

.order-details .wrapper .refundPay .money {
	font-weight: bold;
	font-size: 30rpx;
}

.order-details .footer {
	width: 100%;
	position: fixed;
	display: flex;
	justify-content: space-between;
	bottom: 0;
	left: 0;
	background-color: #fff;
	padding: 20rpx 30rpx;
	padding-bottom: calc(20rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
	padding-bottom: calc(20rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
	box-sizing: border-box;
	border-top: 1px solid #f5f5f5;

	.more {
		// position: absolute;
		left: 30rpx;
		font-size: 26rpx;
		color: #333;

		.icon-xiangshang {
			margin-left: 6rpx;
			font-size: 22rpx;
		}
	}

	.right-btn {
		display: flex;
	}

	.more-box {
		color: #333;
		position: absolute;
		left: 30rpx;
		background-color: #fff;
		padding: 18rpx 24rpx;
		border-radius: 4rpx;
		font-size: 28rpx;
		-webkit-box-shadow: 0px 0px 3px 0px rgba(200, 200, 200, 0.75);
		-moz-box-shadow: 0px 0px 3px 0px rgba(200, 200, 200, 0.75);
		box-shadow: 0px 0px 3px 0px rgba(200, 200, 200, 0.75);
		bottom: 110rpx;
		/* #ifdef APP-PLUS */
		bottom: calc(110rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		bottom: calc(110rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/

		/* #endif */
		.more-btn {
			color: #333;
			padding: 4rpx;
			z-index: 9999;
		}
	}

	.more-box:before {
		content: '';
		width: 0rpx;
		height: 0rpx;
		border-top: 20rpx solid rgba(200, 200, 200, 0.4);
		border-bottom: 0rpx solid transparent;
		border-top: 20rpx solid rgba(200, 200, 200, 0.4);
		border-left: 20rpx solid rgba(0, 0, 0, 0);
		border-right: 20rpx solid rgba(0, 0, 0, 0);
		position: absolute;
		bottom: -20rpx;
		left: 20rpx;
	}

	.more-box::after {
		content: '';
		width: 0rpx;
		height: 0rpx;
		border-top: 20rpx solid #fff;
		border-bottom: 0rpx solid rgba(0, 0, 0, 0);
		border-left: 20rpx solid rgba(0, 0, 0, 0);
		border-right: 20rpx solid rgba(0, 0, 0, 0);
		position: absolute;
		bottom: -18rpx;
		left: 20rpx;
		z-index: 9;
	}
}

.order-details .footer .bnt {
	width: 200rpx;
	height: 60rpx;
	text-align: center;
	line-height: 60rpx;
	border-radius: 50rpx;
	color: #fff;
	font-size: 27rpx;
}

.order-details .footer .bnt.refundBnt {
	width: 210rpx;
}

.order-details .footer .bnt.cancel {
	color: #666;
	border: 1rpx solid #ccc;
}

.order-details .footer .bnt ~ .bnt {
	margin-left: 18rpx;
}

.order-details .writeOff {
	background-color: #fff;
	margin-top: 13rpx;
	padding-bottom: 30rpx;
}

.order-details .writeOff .title {
	font-size: 30rpx;
	color: #282828;
	height: 87rpx;
	border-bottom: 1px solid #f0f0f0;
	padding: 0 30rpx;
	line-height: 87rpx;
}

.order-details .writeOff .grayBg {
	background-color: #f2f5f7;
	width: 590rpx;
	height: 384rpx;
	border-radius: 20rpx 20rpx 0 0;
	margin: 50rpx auto 0 auto;
	padding-top: 55rpx;
	position: relative;
}

.order-details .writeOff .grayBg .written {
	position: absolute;
	top: 0;
	right: 0;
	width: 60rpx;
	height: 60rpx;
}

.order-details .writeOff .grayBg .written image {
	width: 100%;
	height: 100%;
}

.order-details .writeOff .grayBg .pictrue {
	width: 290rpx;
	height: 290rpx;
	margin: 0 auto;
}

.order-details .writeOff .grayBg .pictrue image {
	width: 100%;
	height: 100%;
	display: block;
}

.order-details .writeOff .gear {
	width: 590rpx;
	height: 30rpx;
	margin: 0 auto;
}

.order-details .writeOff .gear image {
	width: 100%;
	height: 100%;
	display: block;
}

.order-details .writeOff .num {
	background-color: #f0c34c;
	width: 590rpx;
	height: 84rpx;
	color: #282828;
	font-size: 48rpx;
	margin: 0 auto;
	border-radius: 0 0 20rpx 20rpx;
	text-align: center;
	padding-top: 4rpx;
}

.order-details .writeOff .rules {
	margin: 46rpx 30rpx 0 30rpx;
	border-top: 1px solid #f0f0f0;
	padding-top: 10rpx;
}

.order-details .writeOff .rules .item {
	margin-top: 20rpx;
}

.order-details .writeOff .rules .item .rulesTitle {
	font-size: 28rpx;
	color: #282828;
}

.order-details .writeOff .rules .item .rulesTitle .iconfont {
	font-size: 30rpx;
	color: #333;
	margin-right: 8rpx;
	margin-top: 5rpx;
}

.order-details .writeOff .rules .item .info {
	font-size: 28rpx;
	color: #999;
	margin-top: 7rpx;
}

.order-details .writeOff .rules .item .info .time {
	margin-left: 20rpx;
}

.order-details .map {
	height: 86rpx;
	font-size: 30rpx;
	color: #282828;
	line-height: 86rpx;
	border-bottom: 1px solid #f0f0f0;
	margin-top: 13rpx;
	background-color: #fff;
	padding: 0 30rpx;
}

.order-details .map .place {
	font-size: 26rpx;
	// width: 176rpx;
	height: 50rpx;
	border-radius: 25rpx;
	line-height: 50rpx;
	text-align: center;
	padding: 0 10rpx;
}

.order-details .map .place .iconfont {
	font-size: 27rpx;
	height: 27rpx;
	line-height: 27rpx;
	margin: 2rpx 3rpx 0 0;
}

.order-details .address .name .iconfont {
	font-size: 34rpx;
	margin-left: 10rpx;
}

.refund {
	padding: 0 30rpx 30rpx;
	margin: 12rpx 0;
	background-color: #fff;

	.title {
		display: flex;
		align-items: center;
		font-size: 30rpx;
		color: #333;
		height: 86rpx;
		border-bottom: 1px solid #f5f5f5;

		image {
			width: 32rpx;
			height: 32rpx;
			margin-right: 10rpx;
		}
	}

	.con {
		padding-top: 25rpx;
		font-size: 28rpx;
		color: #868686;
	}
}
</style>

<style scoped lang="scss">
.invoice-mask {
	background-color: #999999;
	opacity: 1;
}

.more-mask {
	background-color: #fff;
	opacity: 0;
	left: 300rpx;
}

.goodCall {
	color: var(--view-theme);
	text-align: center;
	width: 100%;
	height: 86rpx;
	padding: 0 30rpx;
	border-bottom: 1rpx solid #eee;
	font-size: 30rpx;
	line-height: 86rpx;
	background: #fff;

	.icon-kefu {
		font-size: 36rpx;
		margin-right: 15rpx;
	}

	/* #ifdef MP */
	button {
		display: flex;
		align-items: center;
		justify-content: center;
		height: 86rpx;
		font-size: 30rpx;
		color: var(--view-theme);
	}

	/* #endif */
}

.order-details .header {
	padding: 0 30rpx;
	height: 150rpx;
}

.order-details .header.on {
	background-color: #666 !important;
}

.order-details .header .pictrue {
	width: 110rpx;
	height: 110rpx;
}

.order-details .header .pictrue image {
	width: 100%;
	height: 100%;
}

.order-details .header .data {
	color: rgba(255, 255, 255, 0.8);
	font-size: 24rpx;
	margin-left: 27rpx;
}

.order-details .header .data.on {
	margin-left: 0;
}

.order-details .header .data .state {
	font-size: 30rpx;
	font-weight: bold;
	color: #fff;
	margin-bottom: 7rpx;
}

.order-details .header .data .time {
	margin-left: 20rpx;
}

.order-details .nav {
	background-color: #fff;
	font-size: 26rpx;
	color: #282828;
	padding: 25rpx 0;
}

.order-details .nav .navCon {
	padding: 0 40rpx;
}

.order-details .nav .on {
	color: var(--view-theme);
}

.order-details .nav .progress {
	padding: 0 65rpx;
	margin-top: 10rpx;
}

.order-details .nav .progress .line {
	width: 100rpx;
	height: 2rpx;
	background-color: #939390;
}

.order-details .nav .progress .iconfont {
	font-size: 25rpx;
	color: #939390;
	margin-top: -2rpx;
}

.order-details .address {
	font-size: 26rpx;
	color: #868686;
	background-color: #fff;
	padding: 35rpx 30rpx;
}

.order-details .address .name {
	font-size: 30rpx;
	color: #282828;
	margin-bottom: 15rpx;
}

.order-details .address .name .phone {
	margin-left: 40rpx;
}

.order-details .line {
	width: 100%;
	height: 3rpx;
}

.order-details .line image {
	width: 100%;
	height: 100%;
	display: block;
}

.order-details .wrapper {
	background-color: #fff;
	margin-top: 12rpx;
	padding: 30rpx;
}

.order-details .wrapper .item {
	font-size: 28rpx;
	color: #282828;
}

.order-details .wrapper .item ~ .item {
	margin-top: 20rpx;
}

.order-details .wrapper .item .conter {
	color: #868686;
	// width: 380rpx;
	text-align: justify;
	flex: 1;
	word-break: break-all;
}

.order-details .wrapper .item .conter .upload {
	padding-bottom: 36rpx;
}

.order-details .wrapper .diy-from-title {
	white-space: nowrap;
	width: 5em;
}

.order-details .wrapper .item .conter .upload .pictrue {
	margin: 22rpx 23rpx 0 0;
	width: 156rpx;
	height: 156rpx;
	position: relative;
	font-size: 24rpx;
	color: #bbb;
}

.order-details .wrapper .item .conter .copy {
	font-size: 20rpx;
	color: #333;
	height: max-content;
	border-radius: 3rpx;
	border: 1rpx solid #666;
	padding: 3rpx 15rpx;
	margin-left: 24rpx;
}

.order-details .wrapper .actualPay {
	border-top: 1rpx solid #eee;
	margin-top: 30rpx;
	padding-top: 30rpx;
}

.order-details .wrapper .actualPay .money {
	font-weight: bold;
	font-size: 30rpx;
}

.order-details .footer .bnt {
	width: 160rpx;
	height: 60rpx;
	text-align: center;
	line-height: 60rpx;
	border-radius: 50rpx;
	color: #fff;
	font-size: 27rpx;
}

.order-details .footer .bnt ~ .bnt {
	margin-left: 18rpx;
}

.order-details .writeOff {
	background-color: #fff;
	margin-top: 13rpx;
	padding-bottom: 30rpx;
}

.order-details .writeOff .title {
	font-size: 30rpx;
	color: #282828;
	height: 87rpx;
	border-bottom: 1px solid #f0f0f0;
	padding: 0 30rpx;
	line-height: 87rpx;
}

.order-details .writeOff .grayBg {
	background-color: #f2f5f7;
	width: 590rpx;
	height: 384rpx;
	border-radius: 20rpx 20rpx 0 0;
	margin: 50rpx auto 0 auto;
	padding-top: 55rpx;
	position: relative;
}

.order-details .writeOff .grayBg .written {
	position: absolute;
	top: 0;
	right: 0;
	width: 60rpx;
	height: 60rpx;
}

.order-details .writeOff .grayBg .written image {
	width: 100%;
	height: 100%;
}

.order-details .writeOff .grayBg .pictrue {
	width: 290rpx;
	height: 290rpx;
	margin: 0 auto;
}

.order-details .writeOff .grayBg .pictrue image {
	width: 100%;
	height: 100%;
	display: block;
}

.order-details .writeOff .gear {
	width: 590rpx;
	height: 30rpx;
	margin: 0 auto;
}

.order-details .writeOff .gear image {
	width: 100%;
	height: 100%;
	display: block;
}

.order-details .writeOff .num {
	background-color: #f0c34c;
	width: 590rpx;
	height: 84rpx;
	color: #282828;
	font-size: 48rpx;
	margin: 0 auto;
	border-radius: 0 0 20rpx 20rpx;
	text-align: center;
	padding-top: 4rpx;
}

.order-details .writeOff .rules {
	margin: 46rpx 30rpx 0 30rpx;
	border-top: 1px solid #f0f0f0;
	padding-top: 10rpx;
}

.order-details .writeOff .rules .item {
	margin-top: 20rpx;
}

.order-details .writeOff .rules .item .rulesTitle {
	font-size: 28rpx;
	color: #282828;
}

.order-details .writeOff .rules .item .rulesTitle .iconfont {
	font-size: 30rpx;
	color: #333;
	margin-right: 8rpx;
	margin-top: 5rpx;
}

.order-details .writeOff .rules .item .info {
	font-size: 28rpx;
	color: #999;
	margin-top: 7rpx;
}

.order-details .writeOff .rules .item .info .time {
	margin-left: 20rpx;
}

.order-details .map {
	height: 86rpx;
	font-size: 30rpx;
	color: #282828;
	line-height: 86rpx;
	border-bottom: 1px solid #f0f0f0;
	margin-top: 13rpx;
	background-color: #fff;
	padding: 0 30rpx;
}

.order-details .map .place {
	font-size: 26rpx;
	// width: 176rpx;
	height: 50rpx;
	border-radius: 25rpx;
	line-height: 50rpx;
	text-align: center;
}

.order-details .map .place .iconfont {
	font-size: 27rpx;
	height: 27rpx;
	line-height: 27rpx;
	margin: 2rpx 3rpx 0 0;
}

.order-details .address .name .iconfont {
	font-size: 34rpx;
	margin-left: 10rpx;
}

.refund {
	padding: 0 30rpx 30rpx;
	margin: 12rpx 0;
	background-color: #fff;

	.title {
		display: flex;
		align-items: center;
		font-size: 30rpx;
		color: #333;
		height: 86rpx;
		border-bottom: 1px solid #f5f5f5;

		image {
			width: 32rpx;
			height: 32rpx;
			margin-right: 10rpx;
		}
	}

	.con {
		padding-top: 25rpx;
		font-size: 28rpx;
		color: #868686;
	}
}

.refund-msg {
	background-color: #fff;
	padding: 20rpx 40rpx;
	font-size: 28rpx;

	.refund-msg-user {
		font-weight: bold;
		margin-bottom: 10rpx;

		.copy-refund-msg {
			font-size: 10px;
			border-radius: 1px;
			border: 0.5px solid #666;
			padding: 1px 7px;
			margin-left: 12px;
		}

		.name {
			margin-right: 20rpx;
		}
	}

	.refund-address {
		color: #868686;
	}
}

.copy-text {
	width: max-content;
	font-size: 10px;
	border-radius: 1px;
	border: 0.5px solid #666;
	padding: 1px 7px;
	margin-left: auto;
}

.upload .pictrue {
	display: inline-block;
	margin: 22rpx 17rpx 20rpx 0;
	width: 156rpx;
	height: 156rpx;
	color: #bbb;
}

.upload .pictrue image {
	width: 100%;
	height: 100%;
}
.gift-box {
	background: #ffffff;
	margin: 20rpx 0;
	.user-msg {
		padding: 28rpx 30rpx;
	}
	image {
		width: 36rpx;
		height: 36rpx;
		border-radius: 70rpx 70rpx 70rpx 70rpx;
		border: 2rpx solid #ffffff;
	}
	.nickname {
		font-weight: 400;
		font-size: 28rpx;
		color: #ae5a2a;
	}
	.gift-mark {
		border-top: 1px solid #f0f0f0;
		font-weight: 400;
		font-size: 28rpx;
		color: #333333;
		padding: 28rpx 30rpx;
	}
}
.share-box {
	z-index: 1000;
	position: fixed;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;

	image {
		width: 100%;
		height: 100%;
	}
}
.canvas {
	width: 750rpx;
	height: 1108rpx;
	z-index: 9999;
	position: absolute;
	bottom: 40000rpx;
	right: 30000rpx;
}
</style>
