<?php
namespace PmPhp\Http;

use PmPhp\AbstractClass;

class Request extends AbstractClass
{
    protected static $singleton;
    protected function __construct()
    {
    }
    public static function get()
    {
        if (!self::$singleton) {
            self::$singleton = new static();
        }
        return self::$singleton;
    }

    public function user()
    {
        return null;
    }

    public function method()
    {
        return getenv('REQUEST_METHOD');
    }

    public function path()
    {
        static $path = null;
        if ($path === null) {
            $path = getenv('PATH_INFO');
            if (empty($path)) {
                $path = getenv('REQUEST_URI');
            }
        }
        return $path;
    }

    public function rest()
    {
        static $rest = null;
        if ($rest === null) {
            $rest = explode('/', $this->path());
            while (isset($rest[0]) && $rest[0] === '') {
                array_shift($rest);
            }
        }
        return $rest;
    }

    public function getParams()
    {
        $array = isset($_GET) ? $_GET : [];
        return $array;
    }

    public function postParams()
    {
        $array = isset($_POST) ? $_POST : [];
        return $array;
    }

    public function requestParams()
    {
        $array = isset($_REQUEST) ? $_REQUEST : [];
        return $array;
    }

    public function host()
    {
        if (isset($_SERVER['HTTP_X_GOOGLE_APPS_METADATA'])) {
            $gappMetadata = $this->parseKvCsv2Array($_SERVER['HTTP_X_GOOGLE_APPS_METADATA']);
            if (isset($gappMetadata['host'])) {
                return $gappMetadata['host'];
            }
        }
        if (isset($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }
        if (isset($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }
        return null;
    }

    public function fromCountry()
    {
        return isset($_SERVER["HTTP_X_APPENGINE_COUNTRY"]) ? $_SERVER["HTTP_X_APPENGINE_COUNTRY"] : null;
    }

    public function fromCity()
    {
        return isset($_SERVER['HTTP_X_APPENGINE_CITY']) ? $_SERVER['HTTP_X_APPENGINE_CITY'] : null;
    }

    public function fromLatlong()
    {
        return isset($_SERVER['HTTP_X_APPENGINE_CITYLATLONG']) ? $_SERVER['HTTP_X_APPENGINE_CITYLATLONG'] : null;
    }

    public function from()
    {
        $from = [
            'country' => $this->fromCountry(),
            'city'    => $this->fromCity(),
            'latlong' => $this->fromLatlong(),
        ];
        
        return $this->parseArray2KvCsv($from);
    }

    public function timestamp()
    {
        static $t = null;
        if ($t === null) {
            $t = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
        }
        return $t;
    }

    public function timestampFloat()
    {
        static $t = null;
        if ($t === null) {
            $t = isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true);
        }
        return $t;
    }

    protected function parseKvCsv2Array($string)
    {
        $list = explode(',', $string);
        $array = [];
        foreach ($list as $item) {
            list($k, $v) = explode('=', $item, 2);
            $array[$k] = $v;
        }
        return $array;
    }

    protected function parseArray2KvCsv(array $array)
    {
        $list = [];
        foreach ($array as $k => $v) {
            $list[] = $k . '=' . $v;
        }
        return implode(',', $list);
    }
}
