<?php

class CURL
{
    /**
     * cURL句柄
     * @var resource
     */
    protected $_ch;
    /**
     * 最后一次传输的相关信息
     * @var array
     */
    protected $_info = [];
    /**
     * 返回信息头
     * @var string
     */
    protected $_header = '';
    /**
     * 结果
     * @var string
     */
    protected $_response = '';
    /**
     * 是否保存最后一次传输的相关信息
     * @var bool
     */
    protected $_saveInfo = false;
    /**
     * 是否保存 cookie
     * @var bool
     */
    protected $_saveCookie = false;

    /**
     * 构造函数
     * @param string $url
     */
    public function __construct($url = '')
    {
        $this->_ch = curl_init();
        if ($url) {
            curl_setopt($this->_ch, CURLOPT_URL, $url);
        }
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * get方式
     * @param string $url
     * @param string $cookie
     * @param int    $timeout
     * @return string
     */
    static public function get($url, $cookie='', $timeout=0)
    {
        $curl = new CURL($url);
        if ($cookie) {
            $curl->setCookie($cookie);
        }
        if ($timeout) {
            $curl->setTimeout($timeout);
        }
        return $curl->exec();
    }

    /**
     * post方式
     * @param string       $url
     * @param string|array $data
     * @param string       $cookie
     * @param int          $timeout
     * @return string
     */
    static public function post($url, $data, $cookie='', $timeout=0)
    {
        $curl = new CURL($url);
        if ($cookie) {
            $curl->setCookie($cookie);
        }
        if ($timeout) {
            $curl->setTimeout($timeout);
        }
        return $curl->setPostData($data)->exec();
    }

    /**
     * 初始化
     * @return $this
     */
    public function init()
    {
        $this->_info       = [];
        $this->_header     = '';
        $this->_response   = '';
        $this->_saveInfo   = false;
        $this->_saveCookie = false;
        return $this;
    }

    /**
     * 设置 url
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        return $this;
    }

    /**
     * 设置 post数据
     * @param string|array $post
     * @return $this
     */
    public function setPostData($post = '')
    {
        curl_setopt($this->_ch, CURLOPT_POST, true);
        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $post);
        return $this;
    }

    /**
     * 设置 cookie
     * @param string $cookie
     * @return $this
     */
    public function setCookie($cookie = '')
    {
        curl_setopt($this->_ch, CURLOPT_COOKIE, $cookie);
        return $this;
    }

    /**
     * 设置超时
     * @param integer $timeout
     * @return $this
     */
    public function setTimeout($timeout = 3)
    {
        curl_setopt($this->_ch, CURLOPT_TIMEOUT, intval($timeout));
        return $this;
    }

    /**
     * 设置 referer
     * @param string $referer
     * @return $this
     */
    public function setReferer($referer = '')
    {
        if ($referer) {
            curl_setopt($this->_ch, CURLOPT_REFERER, $referer);
        }
        return $this;
    }

    /**
     * 设置 ua
     * @param string $useragent
     * @return $this
     */
    public function setUserAgent($useragent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)')
    {
        curl_setopt($this->_ch, CURLOPT_USERAGENT, $useragent);
        return $this;
    }

    /**
     * 设置其他信息头
     * @param array $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $headers);
        return $this;
    }

    /**
     * 设置代理
     * @param string $proxy
     * @param bool   $socks
     * @return $this
     */
    public function setProxy($proxy, $socks = false)
    {
        if ($socks) {
            curl_setopt($this->_ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        }
        curl_setopt($this->_ch, CURLOPT_PROXY, $proxy);
        return $this;
    }

    /**
     * 保存 cookie
     * @param bool $saveCookie
     * @return $this
     */
    public function saveCookie($saveCookie = true)
    {
        $this->_saveCookie = $saveCookie;
        return $this;
    }

    /**
     * 保存最后一次传输的相关信息
     * @param bool $saveInfo
     * @return $this
     */
    public function saveInfo($saveInfo = true)
    {
        $this->_saveInfo = $saveInfo;
        return $this;
    }

    /**
     * 执行
     * @param bool $isJson
     * @return array|string
     */
    public function exec($isJson = false)
    {
        $this->_header = '';

        if ($this->_saveCookie) {
            curl_setopt($this->_ch, CURLOPT_HEADERFUNCTION, array(&$this, '_readHeader'));
        }

        $this->_response = curl_exec($this->_ch);

        if ($this->_saveInfo) {
            $this->_info = curl_getinfo($this->_ch);
        }

        if ($isJson) {
            return json_decode($this->_response, true);
        }

        return $this->_response;
    }

    /**
     * 获取最后一次传输的相关信息
     * @return array
     */
    public function getInfo()
    {
        return $this->_info;
    }

    /**
     * 获取返回内容
     * @return string
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * 获取返回信息头
     * @return string
     */
    public function getHeaders()
    {
        return $this->_header;
    }

    /**
     * 获取返回 cookies
     * @return array
     */
    public function getCookies()
    {
        $result = array();
        preg_match_all("/set-cookie:\\s*([^=]*)=([^;]*)/i", trim($this->_header), $matches);
        for($i = 0; $i < count($matches[1]); $i++) {
            $key = $matches[1][$i];
            $value = $matches[2][$i];
            if ($key && $value) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /***
     * @param $ch
     * @param $string
     * @return int
     */
    private function _readHeader($ch, $string)
    {
        $this->_header .= $string;
        return strlen($string);
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        curl_close($this->_ch);
    }
}