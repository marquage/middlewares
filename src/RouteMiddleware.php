<?php

namespace Marquage\Middlewares;

use Auryn\Injector;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class RouteMiddleware implements MiddlewareInterface
{

    public $inj;
    public $routes = [];
    public $reservedTerms;

    /**
     * RouteMiddleware constructor.
     * @param \Auryn\Injector $inj
     * @param array           $routes
     */
    public function __construct(Injector $inj, array $routes, array $reservedTerms = [])
    {
        $this->inj = $inj;
        $this->routes = $routes;
        $this->reservedTerms = $reservedTerms;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Auryn\InjectionException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $matched = $this->match($request->getMethod(), $request->getUri()->getPath());
        return is_array($matched) ? $this->inj->execute([$matched[0], $matched[1]]) : $this->inj->execute($matched);
    }

    /**
     * Four possible matches against defined routes:
     * 1. strict match for the few static pages, such as '/search' or '/tags,
     * 2. partial match so that '/tags/x' matches '/tags')
     * 3. reserved match so that a few reserved terms, such as "/auth" are not overwritten
     * 4. flat wildcard: anything else can be a folder or a file; there is thus no 404.
     *
     * @param string $protocol
     * @param string $path
     * @return mixed
     */
    private function match(string $protocol, string $path)
    {
        $semiWild = strtolower( strtok($path, '/'));
        switch ($path) {
            case isset($this->routes[$protocol][strtolower($path)]):
                return $this->routes[$protocol][strtolower($path)];
                break;
            case isset($this->routes[$protocol]['/'.$semiWild]):
                return $this->routes[$protocol][strtolower( '/'.strtok($path, '/'))];
                break;
            case in_array($semiWild, $this->reservedTerms):
                return $this->routes['RESERVED'];
            default:
                return $this->routes['WILDCARDS'][$protocol];
        }
    }
}
