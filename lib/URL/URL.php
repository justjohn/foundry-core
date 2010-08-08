<?php
class URL {
    public $filename = "index.php";
    public $prefix = "hub/";

    private $url_base;
    private $rewrite;
    private $no_prefix = array();

    function __construct($url_base, $rewrite = false, $prefix = false) {
        if (substr($url_base, -1) != "/") {
            $url_base .= "/";
        }
        $this->url_base = $url_base;
        $this->rewrite = $rewrite;
        if ($rewrite && $prefix !== false) {
            $this->prefix = $prefix;
        }
        if (!$rewrite && $prefix !== false) {
            $this->filename = $prefix;
        }
        if (substr($this->prefix, -1) != "/") {
            $this->prefix .= "/";
        }
    }
    function skip_prefix($page) {
        $this->no_prefix[$page] = true;
    }
    function getLoginURL($redirect_url) {
        if (substr($redirect_url, 0, 6) == "/login") {
            $redirect_url = substr($redirect_url, 6);
        }
        if ($this->rewrite) {
            $url = "login/r/".$redirect_url;
            $url = str_replace("//", "/", $url);
            return $this->url_base . $url;
        } else {
            return "{$this->filename}?redirect=".$redirect_url;
        }
    }
    function getPageURL($page_name) {
        if ($this->rewrite) {
            $page_prefix = isset($this->no_prefix[$page_name])?'':"{$this->prefix}";
            return "{$this->url_base}$page_prefix$page_name";
        } else {
            return "{$this->filename}?page=$page_name";
        }
    }
    function getURL($page_name, $value='', $action='', $data='') {
        if ($this->rewrite) {
            if ($action !== '') {
                $action = "/$action";
            }
            if ($data !== '') {
                $data = "/$data";
            }
            if ($value !== '') {
                $value = "/$value$action$data";
            }
            return $this->getPageURL($page_name).$value;
        } else {
            if ($action !== '') {
                $action = "&amp;action=$action";
            }
            if ($data !== '') {
                $data = "&amp;data=$data";
            }
            if ($value !== '') {
                $value = "&amp;$page_name=$value$action$data";
            }

            return $this->getPageURL($page_name).$value;
        }
    }
}
?>
