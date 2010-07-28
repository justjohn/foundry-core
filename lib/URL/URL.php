<?php
class URL {
    private $url_base;
    private $page_prefix;
    private $rewrite;

    private $no_prefix = array();

    function __construct($url_base, $rewrite = false, $page_prefix = "hub/") {
        if (substr($url_base, -1) != "/") {
            $url_base .= "/";
        }
        $this->url_base = $url_base;
        $this->rewrite = $rewrite;
        $this->page_prefix = $page_prefix;
    }
    function skip_prefix($page) {
        $this->no_prefix[$page] = true;
    }
    function getLoginURL($redirect_url) {
        if (substr($redirect_url, 0, 6) == "/login") {
            $redirect_url = substr($redirect_url, 6);
        }
        if ($this->rewrite) {
            $url = "login/".$redirect_url;
            $url = str_replace("//", "/", $url);
            return $this->url_base . $url;
        } else {
            return "index.php?redirect=".$redirect_url;
        }
    }
    function getPageURL($page_name) {
        if ($this->rewrite) {
            $page_prefix = isset($this->no_prefix[$page_name])?'':"{$this->page_prefix}/";
            return "{$this->url_base}$page_prefix$page_name";
        } else {
            return "index.php?page=$page_name";
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
