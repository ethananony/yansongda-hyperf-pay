<?php

declare(strict_types=1);

namespace Yansongda\Pay\Service;

use Psr\Container\ContainerInterface;
use Hyperf\Logger\LoggerFactory as HyperfLoggerFactory;

class LoggerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = config('pay.logger', []);

        return $container->get(HyperfLoggerFactory::class)->get($config['name'] ?? 'pay', $config['group'] ?? 'default');
    }
}
