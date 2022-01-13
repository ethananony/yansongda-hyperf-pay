<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class QueryReturnPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'GET';
    }

    protected function doSomething(Rocket $rocket): void
    {
        $rocket->setPayload(null);
    }

    protected function getUri(Rocket $rocket): string
    {
        $payload = $rocket->getPayload();
        $config = get_wechat_config($rocket->getParams());

        if (is_null($payload->get('out_return_no')) ||
            is_null($payload->get('out_order_no'))) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        $url = 'v3/profitsharing/return-orders/'.
            $payload->get('out_return_no').
            '?out_order_no='.$payload->get('out_order_no');

        if (Pay::MODE_SERVICE == $config->get('mode')) {
            $url .= '&sub_mchid='.$payload->get('sub_mchid', $config->get('sub_mch_id', ''));
        }

        return $url;
    }
}
