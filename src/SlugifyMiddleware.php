<?php

namespace Marquage\Middlewares;

use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use Marquage\Middlewares\Services\SlugServices;

class SlugifyMiddleware implements MiddlewareInterface
{
    private $factory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->factory = $responseFactory;
    }

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$path = trim($request->getUri()->getPath(), '/');
		$hasSpace = strpos($path, '%20');
		return $hasSpace ? $this->hasSpace($path) : $handler->handle($request);
	}

	private function hasSpace(string $path)
	{
		$normalize = SlugServices::normalize(str_replace('%20', '-', $path));
		$normalized = $this->siteUrl($normalize);
		return $this->factory->createResponse(301)
            ->withHeader('Location', $normalized);
	}
    private function siteUrl(string $url = null):string
    {
        $base = http_response_code() !== FALSE ? 'https://' . $_SERVER['HTTP_HOST'] . '/' : 'https://localhost';
        return $url ? $base.$url : $base;
    }
}
