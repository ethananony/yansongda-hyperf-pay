<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Rocket;

class LaunchPlugin implements PluginInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(LoggerInterface::class);
    }

    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        $this->logger->info('[wechat][LaunchPlugin] 插件开始装载', ['rocket' => $rocket]);

        if (should_do_http_request($rocket->getDirection())) {
            verify_wechat_sign($this->container, $rocket->getDestinationOrigin(), $rocket->getParams());

            $rocket->setDestination($this->validateResponse($rocket));
        }

        $this->logger->info('[wechat][LaunchPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @return array|\Psr\Http\Message\MessageInterface|\Yansongda\Supports\Collection|null
     */
    protected function validateResponse(Rocket $rocket)
    {
        $response = $rocket->getDestination();

        if ($response instanceof ResponseInterface &&
            ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300)) {
            throw new InvalidResponseException(Exception::INVALID_RESPONSE_CODE);
        }

        return $response;
    }
}
