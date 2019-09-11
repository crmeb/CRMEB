# 贡献代码指南

我们非常欢迎大家来贡献代码，我们会向贡献者致以最诚挚的敬意。

一般可以通过在Github上提交[Pull Request](https://github.com/qiniu/php-sdk)来贡献代码。

## Pull Request要求

- **[PSR-2 编码风格标准](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** 。要通过项目中的code sniffer检查。

- **代码格式** 提交前 请按 ./vendor/bin/phpcbf --standard=PSR2  进行格式化。

- **必须添加测试！** - 如果没有测试（单元测试、集成测试都可以），那么提交的补丁是不会通过的。

- **记得更新文档** - 保证`README.md`以及其他相关文档及时更新，和代码的变更保持一致性。

- **考虑我们的发布周期** - 我们的版本号会服从[SemVer v2.0.0](http://semver.org/)，我们绝对不会随意变更对外的API。

- **创建feature分支** - 最好不要从你的master分支提交 pull request。

- **一个feature提交一个pull请求** - 如果你的代码变更了多个操作，那就提交多个pull请求吧。

- **清晰的commit历史** - 保证你的pull请求的每次commit操作都是有意义的。如果你开发中需要执行多次的即时commit操作，那么请把它们放到一起再提交pull请求。

## 运行测试

``` bash
./vendor/bin/phpunit tests/Qiniu/Tests/

```
