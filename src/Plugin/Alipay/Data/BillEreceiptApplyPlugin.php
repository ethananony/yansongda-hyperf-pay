<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Data;

use Closure;
use Psr\Container\ContainerInterface;
use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Rocket;

class BillEreceiptApplyPlugin implements PluginInterface
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
        $this->logger->info('[alipay][BillEreceiptApplyPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => 'alipay.data.bill.ereceipt.apply',
            'biz_content' => array_merge(
                [
                    'type' => 'FUND_DETAIL',
                ],
                $rocket->getParams(),
            ),
        ]);

        $this->logger->info('[alipay][BillEreceiptApplyPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
