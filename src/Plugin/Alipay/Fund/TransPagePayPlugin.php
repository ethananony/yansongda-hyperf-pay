<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Fund;

use Closure;
use Psr\Container\ContainerInterface;
use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Rocket;

class TransPagePayPlugin implements PluginInterface
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
        $this->logger->info('[alipay][TransPagePayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setDirection(ResponseParser::class)
            ->mergePayload([
                'method' => 'alipay.fund.trans.page.pay',
                'biz_content' => $rocket->getParams(),
            ]);

        $this->logger->info('[alipay][TransPagePayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
