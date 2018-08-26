<?php
namespace PmPhp\Controller;

use PmPhp\AbstractClass;
use PmPhp\Http\Response;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class AbstractController extends AbstractClass implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $route;
    protected $response;

    public function __construct($route)
    {
        $this->route = $route;
        $this->response = new Response;
    }
}
