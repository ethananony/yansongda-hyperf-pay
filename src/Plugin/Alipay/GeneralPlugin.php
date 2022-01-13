<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Psr\Container\ContainerInterface;
use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Rocket;

abstract class GeneralPlugin implements PluginInterface
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
        $this->logger->info('[alipay][GeneralPlugin] 通用插件开始装载', ['rocket' => $rocket]);

        $this->doSomethingBefore($rocket);

        $rocket->mergePayload([
            'method' => $this->getMethod(),
            'biz_content' => $rocket->getParams(),
        ]);

        $this->logger->info('[alipay][GeneralPlugin] 通用插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function doSomethingBefore(Rocket $rocket): void
    {
    }

    abstract protected function getMethod(): string;
}
