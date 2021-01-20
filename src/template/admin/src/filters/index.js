// import parseTime, formatTime and set to filter
/**
 * 直播状态
 * @param {String} value
 */
export function liveReviewStatusFilter(value){
  const statusMap = {
    '101': '直播中',
    '102': '未开始',
    '103': '已结束',
    '104': '已结束',
    '105': '直播中',
    '106': '直播中',
    '107': '已结束',
  }
  return statusMap[value]
}

/**
 * 审核状态
 * @param {String} value
 */
export function liveStatusFilter(value){
  const statusMap = {
    '0': '未审核 ',
    '1': '审核中',
    '2': '审核通过',
    '3': '审核失败',
  }
  return statusMap[value]
}


/**
 * 时间戳转时间
 * @param {String} data
 */
export function formatDate(data){
  let date = new Date(data);
  let YY = date.getFullYear() + '-';
  let MM = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
  let DD = (date.getDate() < 10 ? '0' + (date.getDate()) : date.getDate());
  let hh = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
  let mm = (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()) + ':';
  let ss = (date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds());
  return YY + MM + DD +" "+hh + mm + ss;
}

/**
 * @description 直播间类型
 */
export function broadcastType(type) {
  const typeMap = {
    0: '手机直播',
    1: '推流'
  }
  return typeMap[type]
}

/**
 * @description 是否关闭点赞、评论
 */
export function filterClose(value) {
  return value ? '✔' : '✖'
}

/**
 * @description 直播显示类型
 */
export function broadcastDisplayType(type) {
  const typeMap = {
    0: '竖屏',
    1: '横屏'
  }
  return typeMap[type]
}


// 公共过滤器
export function filterEmpty(val) {
    let _result = '-'
    if (!val) {
        return _result
    }
    _result = val
    return _result
}

/**
 * @description 用户类型
 */
export function userType(type) {
    const typeMap = {
        'routine': '小程序',
        'wechat ': '微信',
        'h5': 'H5'
    }
    return typeMap[type]
}

/**
 * @description 访问来源类型
 */
export function sourceType(type) {
    const typeMap = {
        0: 'PC端',
        1: '公众号',
        2: '小程序',
        3: 'H5'
    }
    return typeMap[type]
}

