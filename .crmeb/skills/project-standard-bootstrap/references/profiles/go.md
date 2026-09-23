# Go Profile

- 从 `go.mod`、toolchain、CI 和镜像核对 Go 版本；依赖由 `go.sum` 锁定。
- 后端位于 `backend/`；多模块仓库使用明确的 `go.work` 和模块边界。
- 遵循 `gofmt`/`goimports`，错误保留上下文并使用 `errors.Is/As` 等可判定机制。
- 标准检查映射 `go vet`、项目 lint、`go test ./...`、race 测试和构建。
- Context、超时、goroutine 生命周期、连接关闭和信号处理必须可验证。
- 静态二进制、CGO 和目标架构进入 Docker 与升级包说明。

