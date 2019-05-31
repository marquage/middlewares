<?php

namespace Marquage\Middlewares;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;


class MiddleManagement implements RequestHandlerInterface
{

    public $tpsReports;

    private $container, $factory;

    /**
     * MiddleManagement constructor.
     * @param \Psr\Container\ContainerInterface          $container
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory
     */
    public function __construct(ContainerInterface $container, ResponseFactoryInterface $responseFactory)
    {
        $this->container = $container;
        $this->factory = $responseFactory;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = array_shift($this->tpsReports);
        if (null === $middleware) {
            return $this->factory->createResponse();
        }
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, clone $this);
        }
        array_unshift($this->tpsReports, $this->container->get($middleware));
        return $this->handle($request);
    }
}
