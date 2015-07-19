# 微信分享示例

作者： tlanyan （tlanyan at hotmail.com）  
联系： http://tlanyan.me

## 使用方法：


1. 在config.php中填入您的微信公众号appid和app secret
2. 编辑index.html，找到页面底部的js代码，主要是填充和修改callback函数中的信息
3. 确保当前域名在公众号的js接口安全域名中（进入公众号后台 -》 公众号设置 -》 功能设置 -》 JS接口安全域名）

## 杂项

根据微信官方文档，access token和jsapi ticket都应当全局缓存。如果有时间和精力，可采用redis等方案来实现cache。
