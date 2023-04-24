<?php
declare(strict_types=1);

namespace TestApp\Http;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TestRequestHandler implements RequestHandlerInterface
{
    public $callable;

    public $request;

    public function __construct(?callable $callable = null)
    {
        $this->callable = $callable ?: function ($request) {
            return new Response();
        };
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        return ($this->callable)($request);
    }
}
