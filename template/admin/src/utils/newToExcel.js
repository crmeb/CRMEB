// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import { export_json_to_excel } from '../vendor/Export2Excel';

/**
 * @method exportExcel
 * @param {Array} header   表头
 * @param {Array} filterVal 表头属性字段
 * @param {String} filename 文件名称
 * @param {Array} tableData 列表数据
 **/
export default function exportExcel(header, filterVal, filename, tableData) {
  var data = formatJson(filterVal, tableData);
  export_json_to_excel(header, data, filename);
}

function formatJson(filterVal, tableData) {
  return tableData.map((v) => {
    return filterVal.map((j) => {
      return v[j];
    });
  });
}
