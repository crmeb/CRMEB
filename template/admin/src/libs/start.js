#!/usr/bin/env node

const chalk = require('chalk');

// 打印欢迎信息
console.log(chalk.hex('#DEADED').underline('😄 Hello ~ 欢迎使用CRMEB标准版，我们将竭诚为您服务！'));
console.log(chalk.yellow('info - [提示] 点击这里查看更多商品~ ') + chalk.blue.underline('https://doc.crmeb.com'));
console.log(chalk.yellow('info - [提示] 点击这里可以查看开发文档喔~ ') + chalk.blue.underline('https://www.crmeb.com'));
console.log(
  chalk.yellow('info - [提示] 点击这里可以查看我们的论坛社区~ ') + chalk.blue.underline('https://www.crmeb.com/ask'),
);
console.log(chalk.blue('info - [你知道吗？] 按 Ctrl+C 可以停止服务呢~'));
