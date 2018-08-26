<?php
namespace PmPhp\Route;

use PmPhp\AbstractClass;
use PmPhp\Http\Request;
use PmPhp\PmPhpException;

class HostRouter extends AbstractClass implements InterfaceRouter
{
    protected $rules;
    protected $namespace;

    public function __construct()
    {
        $this->rules = [];
        $this->namespace = null;
    }

    public function execute($app)
    {
        $this->logger->debug('start hostRouter->execute.');
        $request = Request::get();

        $route = $this->getRoute($request);
        if ($route === null) {
            throw new PmPhpException('Cannot find route.');
        }
        $route->setLogger($this->logger);
        $route->execute();

        $this->logger->debug('end hostRouter->execute.');
    }

    public function setRouting(array $rules)
    {
        foreach ($rules as $host => $router) {
            if (!($router instanceof InterfaceRouter)) {
                throw new PmPhpException('Invalid specified router:' . get_class($router));
            }
            $split = [];
            foreach (explode('.', $host) as $r) {
                if ($r === '') {
                    continue;
                }
                $split[] = $r;
            }
            $reverse = array_reverse($split);

            $isMagic = false;
            $isRoot = false;
            if ($split[0] == '*') {
                $isMagic = true;
                if (count($split) === 1) {
                    $isRoot = true;
                }
            }

            $item = [
                'reverse' => $reverse,
                'isMagic' => $isMagic,
                'isRoot' => $isRoot,
                'router' => $router,
            ];
            $this->rules[$host] = $item;
        }
        return $this;
    }

    protected function getRoute(Request $request)
    {
        $host = $request->host();
        if ($host === null) {
            // only isRoot
            if (isset($this->rules['*'])) {
                 $this->rules['*']['router'];
            }
            return null;
        }

        $root = null;
        $rh = array_reverse(explode('.', $host));
        foreach ($this->rules as $h => $rule) {
            if ($rule['isRoot']) {
                $root = $rule;
                // skip, next rule.
                continue;
            }
            $rev = $rule['reverse'];
            if (!$rule['isMagic']) {
                if (implode('.', $rh) == implode('.', $rev)) {
                    return $rule['router'];
                }
                // NG, next rule.
                continue;
            }
            foreach ($rev as $i => $item) {
                if ($item == $rh[$i]) {
                    // OK, next item.
                    continue;
                }
                if ($item == '*' && !isset($rev[$i + 1])) {
                    // OK, return router.
                    return $rule['router'];
                }
                // NG, next rule.
                continue 2;
            }
            return $rule['router'];
        }
        if ($root) {
            return $root['router'];
        }

        return null;
    }
}
