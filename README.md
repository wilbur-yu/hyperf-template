# 介绍
结合自 `hyperf/hyperf-skeleton` 和 `hyperf/biz-skeleton` 项目, 添加了一些自用代码和第三方包以及三方项目代码
> 自用, 仅供参考

# 使用
```bash
# 安装
composer create-project wilbur-yu/hyperf-template new-project
# 启动
composer start-dev
```
访问: http://localhost:9501/

# 工具清单
1. 参数签名和验证
2. 带有效期的加解密(authcode)
3. 请求与响应的数据完整日志记录
4. Redis Bitmap 工具类封装
5. 计数器/漏斗/时间窗口限流注解
6. Resource值可动态隐藏与显示
7. 异常告警(基于[https://github.com/guanguans/notify](https://github.com/guanguans/notify))
8. 
