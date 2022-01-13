<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Yansongda\Pay\Contract\HttpClientInterface;
use Yansongda\Pay\Contract\LoggerInterface;
use Yansongda\Pay\Contract\ParserInterface;
use Yansongda\Pay\Parser\CollectionParser;
use Yansongda\Pay\Service\HttpClientFactory;
use Yansongda\Pay\Service\LoggerFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ParserInterface::class => CollectionParser::class,
                HttpClientInterface::class => HttpClientFactory::class,
                LoggerInterface::class => LoggerFactory::class,
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'Pay 配置文件.',
                    'source' => __DIR__ . '/../publish/pay.php',
                    'destination' => BASE_PATH . '/config/autoload/pay.php',
                ],
            ],
        ];
    }
}
