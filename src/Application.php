<?php
namespace PmPhp;

use PmPhp\Route\InterfaceRouter;
use PmPhp\Http\Response;
use PmPhp\Log\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

class Application extends AbstractClass implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $router;

    public function __construct(InterfaceRouter $router, $logger = null)
    {
        $this->router = $router;
        if (!($logger instanceof LoggerInterface)) {
            $logger = Logger::get();
        }
        $this->setLogger($logger);
    }

    public function run()
    {
        try {
            $this->router->setLogger($this->logger);
            $this->router->execute($this);
        } catch (\Exception $e) {
            $this->getLogger()->critical($e->__toString());
            $r = new Response();
            $r->responseCode(500)
                ->draw();
        }
    }

    public function getLogger()
    {
        return $this->logger;
    }
}
