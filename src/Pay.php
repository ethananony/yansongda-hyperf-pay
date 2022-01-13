<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Provider\Wechat;
use Psr\Container\ContainerInterface;

class Pay
{
    /**
     * 正常模式.
     */
    public const MODE_NORMAL = 0;

    /**
     * 沙箱模式.
     */
    public const MODE_SANDBOX = 1;

    /**
     * 服务商模式.
     */
    public const MODE_SERVICE = 2;

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * Bootstrap.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Alipay
     */
    public function alipay()
    {
        return $this->container->get(Alipay::class);
    }

    /**
     * @return Wechat
     */
    public function wechat()
    {
        return $this->container->get(Wechat::class);
    }

    /**
     * @return LoggerInterface
     */
    public function logger()
    {
        return $this->container->get(LoggerInterface::class);
    }
}
