<?php
namespace PmPhp\Route;

use PmPhp\AbstractClass;
use PmPhp\Http\Request;
use PmPhp\PmPhpException;
use PmPhp\Log\Logger;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

class Router extends AbstractClass implements InterfaceRouter, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $rules;
    protected $namespace;


    public function __construct()
    {
        $this->rules = [];
        $this->namespace = null;
    }

    public function execute()
    {
        $this->logger->debug('start router->execute.');
        $request = Request::get();
        $this->logger->debug('method:%s, path:%s, rest:%s', [$request->method(), $request->path(), json_encode($request->rest())]);

        $route = $this->getRoute($request);
        if ($route === null) {
            throw new PmPhpException('Cannot find route.');
        }

        $class = $route['controller'];
        if ($this->namespace) {
            $class = $this->namespace . '\\' . $class;
        }
        $controller = new $class($route);
        $controller->setLogger($this->logger);
        $controller->$route['method']($route['bind']);

        $this->logger->debug('end router->execute.');
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
        return $this;
    }

    public function setRouting(array $rules)
    {
        foreach ($rules as $route => $exec) {
            $rest = [];
            foreach (explode('/', $route) as $r) {
                if ($r === '') {
                    continue;
                }
                $rest[] = $r;
            }

            $isRoot = false;
            if (empty($rest) || (count($rest) === 1 && $rest[0] == '*')) {
                $isRoot = true;
            }

            if (is_array($exec)) {
                $controller = $exec[0];
                $method = isset($exec[1]) ? $exec[1] : 'execute';
            } else {
                $controller = $exec;
                $method = 'execute';
            }

            $item = [
                'rest' => $rest,
                'isRoot' => $isRoot,
                'controller' => $controller,
                'method' => $method,
            ];
            $this->rules[$route] = $item;
        }
        return $this;
    }

    protected function getRoute(Request $request)
    {
        $rest = $request->rest();
        $root = null;
        $res = [];
        foreach ($this->rules as $route => $rule) {
            // root address rule is applied at last.
            if ($rule['isRoot']) {
                // if defined multiple, prioritize first defined.
                if ($root === null) {
                    $root = $rule;
                    Logger::get()->debug('Entry root rule %s', [json_encode($root)]);
                }
                continue;
            }

            // only root rule applied.
            if (empty($rest)) {
                continue;
            }
            
            // check rule
            Logger::get()->debug('Check rule %s', [json_encode($rule)]);
            $bind = [];
            $i = 0;
            for ($i = 0; isset($rest[$i]); $i++) {
                if (!isset($rule['rest'][$i])) {
                    // exists over rule rest uri, its OK.
                    Logger::get()->debug('Exists over rule rest uri, its OK.');
                    break;
                }
                // bind
                if (substr($rule['rest'][$i], 0, 1) === ':') {
                    $key = substr($rule['rest'][$i], 1);
                    if ($key) {
                        $bind[$key] = $rule['rest'][$i];
                    }
                    continue;
                }
                // last magic
                if ($rule['rest'][$i] === '*' && count($rule['rest']) === ($i + 1)) {
                    break;
                }
                // equal
                if ($rest[$i] === $rule['rest'][$i]) {
                    continue;
                }
                // NG, check next rule.
                Logger::get()->debug('NG, check next rule.');
                continue 2;
            }
            $remain = array_slice($rest, $i);
            Logger::get()->debug('i=%d, remain=%s', [$i, json_encode($remain)]);
            $res = $rule + [
                'remain' => $remain,
                'bind' => $bind,
            ];
        }
        if (empty($res) && $root) {
            $res = $root + [
                'remain' => $rest,
                'bind' => [],
            ];
        }
        if (!empty($res)) {
            return $res;
        }
        return null;
    }
}
