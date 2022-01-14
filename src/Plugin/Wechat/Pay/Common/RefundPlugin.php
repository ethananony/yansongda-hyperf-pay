<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Common;

use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class RefundPlugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'v3/refund/domestic/refunds';
    }

    protected function doSomething(Rocket $rocket): void
    {
        if (! $rocket->getPayload()->has('notify_url')) {
            $config = get_wechat_config($rocket->getParams());
            $rocket->mergePayload(['notify_url' => $config->get('notify_url')]);
        }
    }
}
