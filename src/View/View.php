<?php
namespace PmPhp\View;

use PmPh\PmPhpException;

class View
{
    protected $viewTemplate;
    protected $viewBaseArray;

    protected static $sysViewMap = [
        'head' => 'layout/head.phtml',
        'body' => 'layout/body.phtml',
        'main' => 'sample/main.phtml',
        'header' => 'sample/header.phtml',
        'footer' => 'sample/footer.phtml',
        'meta' => 'sample/meta.phtml',
        'link' => 'sample/link.phtml',
    ];

    public function __construct($template, array $bind = [])
    {
        $this->setTemplate($template);
        $this->setBind($bind);

        // initialize viewBaseArray, read env.
        $this->viewBaseArray = [];
        for ($i = 0; $i < 10; $i++) {
            $envName = 'PMPHP_VIEW_BASE';
            if ($i > 0) {
                $envName .= $i;
            }
            if (($base = getenv($envName)) === false) {
                if ($i === 0) {
                    continue;
                }
                break;
            }
            $this->setTemplateBase($base);
        }
    }

    public function setTemplate($template)
    {
        $this->viewTemplate = $template;
    }

    public function setTemplateBase($base)
    {
        if (in_array($base, $this->viewBaseArray)) {
            return $this;
        }
        if (!file_exists($base)) {
            throw new PmPhpException('Not Found template base ' . $base);
        }
        $this->viewBaseArray[] = $base;
        return $this;
    }

    protected function getViewBaseArray()
    {
        return $this->viewBaseArray + [__DIR__ . '/../template'];
    }

    protected function getViewFilePath()
    {
        $baseArray = $this->getViewBaseArray();
        foreach ($baseArray as $base) {
            if (is_readable($path = $base . '/' . $this->viewTemplate)) {
                return $path;
            }
        }
        return null;
    }

    public function draw()
    {
        if (($path = $this->getViewFilePath()) === null) {
            throw new PmPhpException('Not Found viewFile ' . $this->viewTemplate);
        }
        require($path);
    }

    public function sysView($name, array $bind = [])
    {
        if (isset($this->template[$name])) { // check template bind
            $t = $this->template[$name];
        } elseif (isset(self::$sysViewMap[$name])) { // check system template
            $t = self::$sysViewMap[$name];
        } else {
            return;
        }
        $this->view($t, $bind);
    }

    public function view($template, array $bind = [])
    {
        $view = clone $this;
        $view->setTemplate($template);
        $view->setBind($bind);
        $view->draw();
    }

    public function setBind(array $bind)
    {
        foreach ($bind as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        } else {
            return null;
        }
    }

    public function __set($name, $value)
    {
        if (in_array($name, [
            'viewTemplate',
            'viewBaseArray',
        ])) {
            throw new PmPhpException('Cannot set view ' . $name . ' property.');
        }
        $this->$name = $value;
    }
}
