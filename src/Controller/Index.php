<?php
namespace PmPhp\Controller;

use google\appengine\api\users\UserService;

class Index extends AbstractController
{
    public function execute()
    {
        $this->logger->debug('Index execute.');
        $this->response->bind(['title' => 'IndexPage']);
        $this->response->draw();
    }

    public function info()
    {
        $this->logger->debug('Index info:phpinfo.');
        phpinfo();
    }

    public function home()
    {
        $this->logger->debug('Home execute.');
        $this->response->bind(['title' => 'HomePage']);
        $this->response->draw();
    }

    public function error()
    {
        throw new \Exception('Hoge App Error!!!');
    }

    public function login()
    {
        $this->logger->debug('Login execute.');
        $user = UserService::getCurrentUser();
        $this->response->bind([
            'title' => 'UserPage',
            'dump' => $user->__toString(),
        ]);
        $this->response->draw();
    }
}
