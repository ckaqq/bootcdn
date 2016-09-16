<?php

require_once __DIR__ . '/CURL.php';

// 直接访问当前文件则返回
$requestUri = $_SERVER['REQUEST_URI'];
if ($requestUri === '/index.php' || $requestUri === '/') {
	exit;
}

// 要保存的文件位置
$pathInfo = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['PATH_INFO']);

// 创建目录
$path = dirname($pathInfo);
if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

// 发送请求
$url = "http://bootcdn.kchen.cn{$requestUri}";
$headers = array('Host: cdn.bootcss.com');
$curl = new CURL();
$result = $curl->seturl($url)->setHeaders($headers)->saveInfo(true)->exec();

// 错误处理
$info = $curl->getInfo();
if ($info['http_code'] !== 200) {
    $result = '';
}

// 写入并返回结果
file_put_contents($pathInfo, $result);
echo $result;
