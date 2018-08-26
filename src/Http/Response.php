<?php
namespace PmPhp\Http;

use PmPhp\AbstractClass;
use PmPhp\View\View;

class Response extends AbstractClass
{
    protected $headers;
    protected $template;
    protected $bind;
    protected $responseCode;

    protected static $errorCodeDescMap = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        409 => 'Conflict',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
    ];

    public function __construct()
    {
        $this->headers = [];
        $this->template = 'layout/layout.phtml';
        $this->bind = [];
        $this->responseCode();
    }

    public function template($template)
    {
        $this->template = $template;
        return $this;
    }

    public function responseCode($code = 200, $notAutoSetOnErrorCode = false)
    {
        $this->responseCode = $code;
        if ($code >= 300 && $notAutoSetOnErrorCode !== true) {
            $this->headers = [];
            if ($code >= 400) {
                $this->template('layout/error.phtml');
                $this->setHeaderTemplate(true);
                if (array_key_exists($code, self::$errorCodeDescMap)) {
                    $errMsg = $code . ' ' . self::$errorCodeDescMap[$code];;
                } else {
                    $errMsg = $code . ' Error';
                }
                $this->bind('errMsg', $errMsg);
            } else {
                $this->template(null);
            }
        }
        return $this;
    }

    public function bind($k, $v = null)
    {
        if (is_array($k)) {
            // array bind
            $this->bind = array_merge($this->bind, $k);
        } else {
            // key value bind
            $this->bind[$k] = $v;
        }
        return $this;
    }

    public function header($type, $value)
    {
        $this->headers[$type] = $value;
        return $this;
    }

    public function setHeaderTemplate($contentType = true)
    {
        if ($contentType !== null) {
            if ($contentType === true || $contentType === 'html') {
                $contentType = 'text/html; charset=utf-8';
            }
            $this->header('Content-Type', $contentType);
        }
    }

    public function draw()
    {
        http_response_code($this->responseCode);
        $this->sendHeader();

        if ($this->template) {
            $view = new View($this->template, $this->bind);
            $view->draw();
        }
    }

    protected function sendHeader()
    {
        foreach ($this->headers as $headerType => $headerValue) {
            if (!empty($headerValue)) {
                header($headerType . ': ' . $headerValue);
            }
        }
    }
}
