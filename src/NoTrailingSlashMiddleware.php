<?php

namespace Marquage\Middlewares;

use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface,RequestHandlerInterface};

class NoTrailingSlashMiddleware  implements MiddlewareInterface
{
    public $factory;

    /**
     * NoTrailingSlashMiddleware constructor.
     * A less intelligent version of https://github.com/middlewares/trailing-slash, suited for Marques
     * @param \Psr\Http\Message\ResponseFactoryInterface $factory
     */
    public function __construct(ResponseFactoryInterface $factory) {

        $this->factory = $factory;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$path = $request->getUri()->getPath();
		if(strlen($path) === 1){
			return $handler->handle($request);
		}
		elseif (substr($path, -1) !== "/")  {
			return $handler->handle($request);
		}
		else{
			return $this->factory->createResponse(301)
				->withHeader('Location', (string) rtrim($path,"/"));
		}
	}
}
