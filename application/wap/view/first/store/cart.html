{extend name="public/container"}
{block name="title"}
购物车
{/block}
{block name="content"}
<div id="store-cart" class="buy-car">
    <section>
        <header v-show="validCartList.length > 0" v-cloak="">
            购物数量({{cartNumTotal()}})
            <span class="edit-hock fr">
                <i class="edits"></i>
                <em class="edit-txt" v-show="changeStatus == false" @click="changeStatus = !changeStatus">点击编辑</em>
                <em :class="{cancel:changeStatus == false}" @click="changeStatus = !changeStatus">取消编辑</em>
            </span>
        </header>
        <div class="pro-list" v-show="validCartList.length > 0" v-cloak="">
            <ul>
                <li class="flex" v-for="(cart,index) in validCartList" v-show="cart.is_del !== true">
                    <div class="selected">
                        <label class="well-check">
                            <input class="ckecks" type="checkbox" @change="checkedCart" v-model="cart.checked">
                            <i class="icon"></i>
                        </label>
                    </div>
                    <div class="pro-info clearfix">
                        <div class="img fl"><a :href="getStoreUrl(cart)"><img :src="cart.productInfo.image" /></a></div>
                        <div class="infos fl">
                            <div class="con-cell">
                                <p class="title" v-text="cart.productInfo.store_name"></p>
                                <p class="attr" v-text="getAttrValues(cart)"></p>
                                <span class="price"><i>￥</i>{{cart.truePrice}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="amount" v-show="changeStatus == false">
                        <div class="count">
                            <div class="reduction" @click="changeCartNum(cart,index,-1)">-</div>
                            <input type="number" v-model="cart.cart_num" required>
                            <div class="add" @click="changeCartNum(cart,index,1)">+</div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="tmp-invalid-list" v-show="invalidCartList.length >0" v-cloak="">
            <div class="title"><i class="line"></i><span>失效商品</span><i class="line"></i></div>
            <ul class="list-box" v-cloak="">
                <li class="flex" v-for="(cart,index) in invalidCartList">
                    <div class="picture"><img :src="cart.productInfo.image" /></div>
                    <div class="pro-info flex">
                        <p class="pro-title" v-text="cart.productInfo.store_name"></p>
                        <span class="price">已失效</span>
                    </div>
                    <div class="delete-btn" @click="removeInvalidCart(cart,index)"></div>
                </li>
            </ul>
        </div>
        <template v-if="invalidCartList.length == 0 && validCartList.length == 0">
            <div class="empty">
                <img src="{__WAP_PATH}crmeb/images/empty_cart.png">
                <template v-if="!loading">
                    <p>正在加载购物车数据中......</p>
                </template>
                <template v-else>
                    <p>暂无购物车信息，点击
                        <a href="{:Url('Index/index')}">立即添加</a>
                    </p>
                </template>
            </div>
        </template>
        <div style="height:1rem"></div>
        <div class="car-footer flex">
            <div class="selected">
                <label class="well-check">
                    <input class="ckecks" type="checkbox" v-model="checkedAll" @change="checkedAllCart">
                    <i class="icon"></i>
                </label>
                <span>全选</span>
            </div>
            <div class="all-price" v-show="changeStatus == false">合计：<span v-text="'￥'+totalPrice"></span></div>
            <div class="button">
                <span class="sub_btn" v-show="changeStatus == false" @click="submitCart">结算 ({{cartCount()}})</span>
                <span :class="{'delete_btn':changeStatus == false}" v-cloak="" @click="removeCart">点击删除</span>
            </div>
        </div>
        {include file="public/store_menu"}
    </section>
</div>
<script type="text/javascript" src="{__WAP_PATH}crmeb/module/cart.js"></script>
{/block}