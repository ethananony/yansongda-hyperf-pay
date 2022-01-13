<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Traits\HasWechatEncryption;
use Yansongda\Supports\Collection;

class CreatePlugin extends GeneralPlugin
{
    use HasWechatEncryption;

    protected function doSomething(Rocket $rocket): void
    {
        $payload = $rocket->getPayload();
        $params = $this->loadSerialNo($rocket->getParams());

        $extra = $this->getWechatExtra($params, $payload);
        $extra['receivers'] = $this->getReceivers($params);

        $rocket->setParams($params);
        $rocket->mergePayload($extra);
    }

    protected function getUri(Rocket $rocket): string
    {
        return 'v3/profitsharing/orders';
    }

    protected function getWechatExtra(array $params, Collection $payload): array
    {
        $config = get_wechat_config($params);

        $extra = [
            'appid' => $config->get('mp_app_id'),
        ];

        if (Pay::MODE_SERVICE == $config->get('mode')) {
            $extra['sub_mchid'] = $payload->get('sub_mchid', $config->get('sub_mch_id', ''));
        }

        return $extra;
    }

    protected function getReceivers(array $params): array
    {
        $publicKey = $this->getPublicKey($params, $params['_serial_no'] ?? '');
        $receivers = $params['receivers'] ?? [];

        foreach ($receivers as $key => $receiver) {
            if (!empty($receiver['name'])) {
                $receivers[$key]['name'] = encrypt_wechat_contents($receiver['name'], $publicKey);
            }
        }

        return $receivers;
    }
}
