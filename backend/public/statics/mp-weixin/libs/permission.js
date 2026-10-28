"use strict";const e=require("../utils/cache.js");exports.ActivePermission=function(i){return(e.Cache.get("BASIC_CONFIG").site_func||["seckill","bargain","combination"]).indexOf(i)>-1};
