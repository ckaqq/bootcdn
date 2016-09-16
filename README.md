# 缓存 bootcdn 以方便离线开发

#### apache 配置

1. httpd.conf 中启用 rewrite_module 和 headers_module
```shell
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule headers_module modules/mod_headers.so
```

2. 增加配置
```shell
<VirtualHost *:80>
        ServerName cdn.bootcss.com
        DocumentRoot /web/bootcdn
</VirtualHost>
```

#### 修改目录权限

```shell
chmod o+w /web/bootcdn
```

#### 修改 hosts 文件

```shell
127.0.0.1       cdn.bootcss.com
```
