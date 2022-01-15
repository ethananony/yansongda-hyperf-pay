# yansongda-hyperf/pay

基于 [https://github.com/yansongda/pay](https://github.com/yansongda/pay) 修改支持hyperf框架，版权归属 [https://github.com/yansongda/pay](https://github.com/yansongda/pay) 所有

## 安装

```shell
composer require yansongda-hyperf/pay
php bin/hyperf.php vendor:publish yansongda-hyperf/pay
```

## 使用

```php
use Yansongda\Pay\Pay;

// 使用注解定义
/**
 * @Inject
 * @var Pay
 */
protected $pay;

// 通过container属性
/** @var Pay */
$pay = $this->container->get(Pay::class);
// 或者 $pay = ApplicationContext::getContainer()->get(Pay::class);

// 支付宝支付
$result = $pay->alipay()->web([
    'out_trade_no' => ''.time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 1',
]);

// 微信支付
$result = $pay->wechat()->mp([
    'out_trade_no' => time(),
    'total_fee' => '1', // **单位：分**
    'body' => 'test body - 测试',
    'openid' => 'onkVf1FjWS5SBIixxxxxxx',
]);

// 记录日志
$pay->logger()->info('info message', [
    'context-key' => 'context-value',
]);
```

## 详细文档

[https://pay.yansongda.cn](https://pay.yansongda.cn)
