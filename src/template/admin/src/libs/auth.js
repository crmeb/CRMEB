/**
 * @description 判断列表1中是否包含了列表2中的某一项
 * 因为用户权限 access 为数组，includes 方法无法直接得出结论
 * */
function includeArray (list1, list2) {
  let status = false
  if (list1 === true) {
    return true
  } else {
    if (typeof list2 !== 'object') {
      return false
    }
    list2.forEach(item => {
      if (list1.includes(item)) status = true
    })
    return status
  }
}
export { includeArray }
