{extend name="public/container" /}
{block name="title"}添加地址{/block}
{block name="content"}
<body style="font-size: .24rem !important;">
<div id="edit-address" class="user-add-address">
    <section>
        <form action="" method="post" @submit.prevent="submit" ref="form">
            <div class="address-info">
                <div class="item">
                    <label for="">姓名</label>
                    <input type="text" v-model="info.real_name" placeholder="请输入姓名"/>
                </div>
                <div class="item">
                    <label for="">联系电话</label>
                    <input type="tel" v-model="info.phone" placeholder="请输入联系电话"/>
                </div>
                <div class="item">
                    <label for="">所在地区</label>
                    <input class="select-add" readonly v-model="address" @click="selectAddress" type="text" placeholder="请选择"/>
                </div>
                <div class="item">
                    <label for="">详细地址</label>
                    <input type="text" v-model="info.detail" placeholder="请填写具体地址"/>
                </div>
                <div class="item">
                    <label for="">邮政编码</label>
                    <input type="tel" v-model="info.post_code" placeholder="请填写邮政编码(选填)"/>
                </div>
            </div>
            <div class="set-default">
                <label class="well-check">
                    <input class="ckecks" type="checkbox" v-model="info.is_default"><i class="icon"></i>
                    设为默认地址
                </label>
            </div>
            <button class="sub-btn" type="submit">立即保存</button>
        </form>
        <yd-cityselect :items="district" v-model="addressShow" :callback="changeAddress"></yd-cityselect>
    </section>
</div>
<script src="{__PLUG_PATH}reg-verify.js"></script>
<script>
    requirejs(['vue','ydui','static/plug/ydui/province_city_area','helper','store'],function(Vue,ydui,district,$h,storeApi){
        $addressInfo = <?=json_encode($addressInfo)?>;
        Vue.use(ydui.default);
        new Vue({
            el:"#edit-address",
            data:{
                district:district,
                addressShow:false,
                info:{
                    id:$addressInfo.id || '',
                    address:{
                        province: $addressInfo.province || '',
                        city: $addressInfo.city || '',
                        district:$addressInfo.district || ''
                    },
                    post_code:$addressInfo.post_code || '',
                    detail:$addressInfo.detail || '',
                    real_name:$addressInfo.real_name || '',
                    phone:$addressInfo.phone || '',
                    is_default:$addressInfo.is_default == 1 || false
                }
            },
            computed:{
                address:function(){
                    var address = this.info.address;
                    if(address.province && address.city && address.district)
                        return address.province+' '+address.city+' '+address.district;
                    else
                        return '';
                }
            },
            methods:{
                changeAddress:function(res){
                    var address = this.info.address;
                    address.province = res.itemName1;
                    address.city = res.itemName2;
                    address.district = res.itemName3;
                },
                selectAddress:function(){
                    this.addressShow = true;
                    document.activeElement.blur();
                },
                submit:function(){
                    var address = this.info.address,that = this;
                    if($reg.isEmpty(this.info.real_name))
                        return $h.returnErrorMsg('请输入姓名');
                    if(!$reg.isPhone(this.info.phone))
                        return $h.returnErrorMsg('请输入正确的手机号');
                    if($reg.isEmpty(address.province) || $reg.isEmpty(address.city) || $reg.isEmpty(address.district))
                        return $h.returnErrorMsg('请选择所在地区');
                    if($reg.isEmpty(this.info.detail))
                        return $h.returnErrorMsg('请补充详细地址');
                    if(!$reg.isEmpty(this.info.post_code) && !$reg.isPostCode(this.info.post_code))
                        return $h.returnErrorMsg('请输入正确的邮政编码');
                    $h.load();
                    storeApi.editUserAddress(this.info,function(res){
                        $h.pushMsg('编辑收货地址成功',function(){
                            location.replace( document.referrer || $h.U({
                                c:'my',
                                a:'address'
                            }));
                        })
                    },function(){
                        $h.loadClear();
                        return true;
                    });
                }
            },
            mounted:function(){

            }
        })
    });
</script>
{/block}