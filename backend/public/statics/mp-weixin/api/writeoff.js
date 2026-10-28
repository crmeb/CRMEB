"use strict";const r=require("../utils/request.js");exports.orderVerific=function(e,i=0){return r.request.post("order/order_verific",{verify_code:e,is_confirm:i})};
