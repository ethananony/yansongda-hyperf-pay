<?php

declare(strict_types=1);

namespace Yansongda\Pay\Event;

class MethodCalled
{
    /**
     * @var string
     */
    public $provider;

    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $params;

    public function __construct(string $provider, string $name, array $params)
    {
        $this->provider = $provider;
        $this->name = $name;
        $this->params = $params;
    }
}
