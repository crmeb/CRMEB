{extend name="public/container"}
{block name="title"}地址管理{/block}
{block name="content"}
<div id="address-list" class="user-addresslist">
    <section>
        <form action="" method="post">
            <?php $isDefault = ''; ?>
        {volist name="address" id="vo"}
        <div class="address-item">
            <div class="user-info">
                <p>收货人：{$vo.real_name} <span> {$vo.phone}</span></p>
                <p>收货地址：{$vo.province} {$vo.city} {$vo.district} {$vo.detail}</p>
            </div>
            <div class="address-btn flex">
                <div class="switch-btn">
                    <label class="well-check">
                        <input class="ckecks" v-model="isDefault" type="radio" {eq name="vo.is_default" value="1"}checked="checked" <?php $isDefault = $vo['id']; ?>{/eq} value="{$vo.id}"><i class="icon"></i>
                        设为默认地址
                    </label>
                </div>
                <div class="action-btn">
                    <span class="edit" @click="redirect('{$vo.id}')">编辑</span>
                    <span class="delete" @click="remove('{$vo.id}',$event)">删除</span>
                </div>
            </div>
        </div>
        {/volist}
        </form>
        <a class="address-add" href="{:Url('edit_address')}">
            新增地址
        </a>
    </section>
</div>
<script>
    (function(){
        requirejs(['vue','lodash','store','helper'],function(Vue,_,storeApi,$h){
            new Vue({
                el:"#address-list",
                data:{
                    isDefault:"<?=$isDefault?>"
                },
                watch:{
                    isDefault:_.debounce(function(v){
                        $h.loadFFF();
                        storeApi.setUserDefaultAddress(v,function(){
                            $h.loadClear();
                        },function(){
                            $h.loadClear();
                            return true;
                        })
                    },300)
                },
                methods:{
                    remove:function(addressId,e){
                        storeApi.removeUserAddress(addressId,function(){
                            $(e.target).parents('.address-item').remove();
                        })
                    },
                    redirect:function(addressId){
                        location.href = $h.U({
                            c:"my",
                            a:"edit_address",
                            p:{addressId:addressId}
                        });
                    }
                }
            });
        });

    })();
</script>
{/block}