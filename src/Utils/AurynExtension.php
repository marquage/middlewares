<?php

namespace Marquage\Middlewares\Utils;

use Auryn\Injector;
use Exception;
use Psr\Container\ContainerInterface;
use RuntimeException;

class AurynExtension extends Injector implements ContainerInterface
{
	public $has = [];

	public function get($id)
	{
		try {
			return $this->make($id);
		} catch (Exception $previous) {
			throw new RuntimeException('Unable to get: ' . $id, 0, $previous);
		}
	}

	public function has($id): bool
	{
		if (isset($this->has[$id]) && $this->inspect($id, self::I_ALL)) {
			return $this->has[$id];
		}
		return $this->has[$id] = false;
	}
}
