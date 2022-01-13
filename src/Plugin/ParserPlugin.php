<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin;

use Closure;
use Psr\Container\ContainerInterface;
use Yansongda\Pay\Contract\ParserInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Rocket;

class ParserPlugin implements PluginInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        /* @var \Psr\Http\Message\ResponseInterface $response */
        $response = $rocket->getDestination();

        return $rocket->setDestination(
            $this->getPacker($rocket)->parse($response)
        );
    }

    protected function getPacker(Rocket $rocket): ParserInterface
    {
        $packer = $this->container->get($rocket->getDirection() ?? ParserInterface::class);

        $packer = is_string($packer) ? $this->container->get($packer) : $packer;

        if (!($packer instanceof ParserInterface)) {
            throw new InvalidConfigException(Exception::INVALID_PACKER);
        }

        return $packer;
    }
}
