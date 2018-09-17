(function(global,factory){
    typeof define == 'function' && define('store',['axios','helper'],factory);
})(this,function(axios,$h){
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
    return {
        baseGet:function(url,successCallback,errorCallback){
            axios.get(url).then(function(res){
                if(res.status == 200 && res.data.code == 200){
                    successCallback && successCallback(res);
                }else{
                    var err = res.data.msg || '请求失败!';
                    errorCallback && errorCallback(err);
                    $h.pushMsgOnce(err)
                }
            }).catch(function(err){
                errorCallback && errorCallback(err);
                $h.pushMsgOnce(err)
            });
        },
        basePost:function(url,data,successCallback,errorCallback){
            axios.post(url,data).then(function(res){
                if(res.status == 200 && res.data.code == 200){
                    successCallback && successCallback(res);
                }else{
                    var err = res.data.msg || '请求失败!';
                    errorCallback && errorCallback(err);
                    $h.pushMsgOnce(err)
                }
            }).catch(function(err){
                errorCallback && errorCallback(err);
                $h.pushMsgOnce(err)
            });
        },
        setCart:function(opt,successCallback,errorCallback){
            axios.get($h.U({
                c:"auth_api",
                a:"set_cart",
                p:opt
            })).then(function(res){
                if(res.status == 200 && res.data.code == 200)
                    successCallback && successCallback();
                else{
                    var error = res.data.msg || '加入购物车失败!';
                    errorCallback && errorCallback(error);
                    $h.pushMsg(error);
                }
            }).catch(function(err){
                errorCallback && errorCallback(err);
                $h.pushMsg(err);
            });
        },
        goBuy:function(opt,successCallback,errorCallback){
            axios.get($h.U({
                c:"auth_api",
                a:"now_buy",
                p:opt
            })).then(function(res){
                if(res.status == 200 && res.data.code == 200)
                    successCallback && successCallback(res.data.data.cartId);
                else{
                    var error = res.data.msg || '订单生成失败!';
                    errorCallback && errorCallback(error);
                    $h.pushMsg(error);
                }
            }).catch(function(err){
                errorCallback && errorCallback(err);
                $h.pushMsg(err);
            });
        },
        addBargainShare:function(opt,successCallback,errorCallback){
            axios.get($h.U({
                c:"auth_api",
                a:"add_bargain_share",
                p:opt
            })).then(function(res){

            }).catch(function(err){

            });
        },
        likeProduct:function(productId,category,successCallback,errorCallback) {
            axios.get($h.U({
                c:"auth_api",
                a:"like_product",
                p:{productId:productId,category:category}
            })).then(function(res){
                if(res.status == 200 && res.data.code == 200){
                    successCallback && successCallback(res.data);
                }else{
                    var error = res.data.msg || '点赞失败!';
                    errorCallback && errorCallback(error);
                    $h.pushMsg(error);
                }
            }).catch(function(err){
                errorCallback && errorCallback(err);
                $h.pushMsg(err)
            });
        },
        bargainFriends:function(bargain,successCallback,errorCallback){
            this.basePost($h.U({
                c:'auth_api',
                a:'set_bargain_help'
            }),bargain,successCallback,errorCallback)
        },
        unlikeProduct:function(productId,category,successCallback,errorCallback) {
            axios.get($h.U({
                c:"auth_api",
                a:"unlike_product",
                p:{productId:productId,category:category}
            })).then(function(res){
                if(res.status == 200 && res.data.code == 200){
                    successCallback && successCallback(res.data);
                }else{
                    var error = res.data.msg || '取消点赞失败!';
                    errorCallback && errorCallback(error);
                    $h.pushMsg(error);
                }
            }).catch(function(err){
                errorCallback && errorCallback(err);
                $h.pushMsg(err)
            });
        },
        collectProduct(productId,category,successCallback,errorCallback){
            axios.get($h.U({
                c:'auth_api',
                a:'collect_product',
                p:{productId:productId,category:category}
            })).then(function(res){
                if(res.status == 200 && res.data.code == 200){
                    successCallback && successCallback(res.data);
                }else{
                    var error = res.data.msg || '收藏失败!';
                    errorCallback && errorCallback(error);
                    $h.pushMsg(error);
                }
            }).catch(function(err){
                errorCallback && errorCallback(err);
                $h.pushMsg(err)
            });
        },
        unCollectProduct(productId,category,successCallback,errorCallback){
            axios.get($h.U({
                c:'auth_api',
                a:'uncollect_product',
                p:{productId:productId,category:category}
            })).then(function(res){
                if(res.status == 200 && res.data.code == 200){
                    successCallback && successCallback(res.data);
                }else{
                    var error = res.data.msg || '取消收藏失败!';
                    errorCallback && errorCallback(error);
                    $h.pushMsg(error);
                }
            }).catch(function(err){
                errorCallback && errorCallback(err);
                $h.pushMsg(err)
            });
        },
        getCartNum:function(callback){
            axios.get($h.U({
                c:'auth_api',
                a:'get_cart_num'
            })).then(function(res){
                if(res.status == 200 && res.data.code == 200){
                    callback && callback(res.data.data);
                }else{
                    callback && callback(0);
                }
            }).catch(function(){
                callback && callback(0);
            });
        },
        changeCartNum:function(cartId,cartNum,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'change_cart_num',
                p:{cartId:cartId,cartNum:cartNum}
            }),successCallback,errorCallback);
        },
        getCartList:function(successCallback,errorCallback){
            axios.get($h.U({
                c:'auth_api',
                a:'get_cart_list'
            })).then(function(res){
                if(res.status == 200 && res.data.code == 200){
                    successCallback && successCallback(res.data.data,res.data);
                }else{
                    var error = res.data.msg || '获取购物车数据失败!';
                    errorCallback && errorCallback(error);
                    $h.pushMsg(error);
                }
            }).catch(function(err){
                errorCallback && errorCallback(err);
                $h.pushMsg(err)
            });
        },
        removeCart:function(cartId,successCallback,errorCallback){
            axios.get($h.U({
                c:'auth_api',
                a:'remove_cart',
                p:{ids:cartId}
            })).then(function(res){
                if(res.status == 200 && res.data.code == 200){
                    successCallback && successCallback(res.data.data,res.data);
                }else{
                    var error = res.data.msg || '删除失败!';
                    errorCallback && errorCallback(error);
                    $h.pushMsg(error);
                }
            }).catch(function(err){
                errorCallback && errorCallback(err);
                $h.pushMsg(err)
            });
        },
        getUseCoupon:function(successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'get_use_coupon'
            }),successCallback,errorCallback);
        },
        getArticleList:function(p,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'public_api',
                a:'get_cid_article',
                p:p
            }),successCallback,errorCallback)
        },
        getVideoList:function(p,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'public_api',
                a:'get_video_list',
                p:p
            }),successCallback,errorCallback)
        },
        getCollectProduct:function(p,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'get_user_collect_product',
                p:p
            }),successCallback,errorCallback)
        },
        removeCollectProduct:function(productId,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'remove_user_collect_product',
                p:{productId:productId}
            }),successCallback,errorCallback)
        },
        editUserAddress:function(addressInfo,successCallback,errorCallback){
            this.basePost($h.U({
                c:'auth_api',
                a:'edit_user_address'
            }),addressInfo,successCallback,errorCallback)
        },
        getUserDefaultAddress:function(successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'user_default_address'
            }),successCallback,errorCallback)
        },
        setUserDefaultAddress:function(addressId,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'set_user_default_address',
                p:{addressId:addressId}
            }),successCallback,errorCallback)
        },
        removeUserAddress:function(addressId,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'remove_user_address',
                p:{addressId:addressId}
            }),successCallback,errorCallback)
        },
        submitOrder:function(key,order,successCallback,errorCallback){
            this.basePost($h.U({
                c:'auth_api',
                a:'create_order',
                p:{key:key}
            }),order,successCallback,errorCallback)
        },
        getUserOrderList:function(p,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'get_user_order_list',
                p:p
            }),successCallback,errorCallback);
        },
        removeUserOrder:function(uni,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'user_remove_order',
                p:{uni:uni}
            }),successCallback,errorCallback);
        },
        payOrder:function(uni,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'pay_order',
                p:{uni:uni}
            }),successCallback,errorCallback);
        },
        orderApplyRefund:function(uni,text,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'apply_order_refund',
                p:{uni:uni,text:text}
            }),successCallback,errorCallback);
        },
        orderDetails:function(uni,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'order_details',
                p:{uni:uni}
            }),successCallback,errorCallback);
        },
        userTakeOrder:function(uni,successCallback,errorCallback) {
            this.baseGet($h.U({
                c:'auth_api',
                a:'user_take_order',
                p:{uni:uni}
            }),successCallback,errorCallback);
        },
        getProductCategory:function(successCallback,errorCallback) {
            this.baseGet($h.U({
                c:'auth_api',
                a:'get_product_category'
            }),successCallback,errorCallback);
        },
        userCommentProduct:function(unique,data,successCallback,errorCallback){
            this.basePost($h.U({
                c:'auth_api',
                a:'user_comment_product',
                p:{unique:unique}
            }),data,successCallback,errorCallback)
        },
        getSpreadList:function(p,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'get_spread_list',
                p:p
            }),successCallback,errorCallback);
        },
        getProductList:function(search,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'get_product_list',
                p:search
            }),successCallback,errorCallback);
        },
        getUserBalanceList:function(p,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'user_balance_list',
                p:p
            }),successCallback,errorCallback);
        },
        getUserIntegralList:function(p,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'user_integral_list',
                p:p
            }),successCallback,errorCallback);
        },
        getProductReply:function(p,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'product_reply_list',
                p:p
            }),successCallback,errorCallback);
        },
        getUserAddress:function(successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'user_address_list'
            }),successCallback,errorCallback);
        },
        getProductAttr:function(productId,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'product_attr_detail',
                p:{productId:productId}
            }),successCallback,errorCallback);
        },
        userWechatRecharge:function(price,successCallback,errorCallback) {
            this.baseGet($h.U({
                c:'auth_api',
                a:'user_wechat_recharge',
                p:{price:price}
            }),successCallback,errorCallback);
        },
        getNoticeList:function(p,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'get_notice_list',
                p:p
            }),successCallback,errorCallback);
        },
        seeNotice: function(p,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'see_notice',
                p:p
            }),successCallback,errorCallback);
        },
        getIssueCouponList:function(limit,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'get_issue_coupon_list',
                p:{limit:limit}
            }),successCallback,errorCallback);
        },
        getCategoryProductList:function(limit,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'public_api',
                a:'get_category_product_list',
                p:{limit:limit}
            }),successCallback,errorCallback);
        },
        getBestProductList:function(p,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'public_api',
                a:'get_best_product_list',
                p:p
            }),successCallback,errorCallback);
        },
        userGetCoupon:function(couponId,successCallback,errorCallback){
            this.baseGet($h.U({
                c:'auth_api',
                a:'user_get_coupon',
                p:{couponId:couponId}
            }),successCallback,errorCallback);
        },
        isLogin:function(){
            return $h.getCookie('is_login') == 1;
        },
        goLogin:function(){
            if(!this.isLogin()){
                $h.pushMsg('未登录,立即登陆',function(){
                    location.href = $h.U({
                        c:'login',
                        a:'index',
                        p:{ref:window.btoa(unescape(encodeURIComponent( location.href )))}
                    });
                });
                return false;
            }
            return true;
        },
        wechatUploadImg:function(wxApi,count,successCallback,errorCallback){
            wxApi.chooseImage({count:count,sizeType:['compressed']},function(localIds){
                $h.prompt('图片上传中...');
                wxApi.uploadImage(localIds,function(serverIds){
                    axios.get($h.U({
                        c:"public_api",
                        a:"wechat_media_id_by_image",
                        p:{mediaIds:serverIds}
                    })).then(function(result){
                        $h.promptClear();
                        if(result.status == 200 && result.data.code == 200)
                            return Promise.resolve(result.data.data);
                        else
                            return Promise.reject('上传失败!');
                    }).then(function(picList){
                        if(!picList) return Promise.reject('请选择上传图片!');
                        successCallback && successCallback(picList);
                    }).catch(function(err){
                        $h.promptClear();
                        $h.pushMsgOnce(err);
                        errorCallback && errorCallback(err);
                    });
                })
            });
        }
    }
});