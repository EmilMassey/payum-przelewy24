<?php

namespace EmilMassey\Payum\Przelewy24;

use EmilMassey\Payum\Przelewy24\Action\Api\DoVerifyAction;
use EmilMassey\Payum\Przelewy24\Action\ConvertPaymentAction;
use EmilMassey\Payum\Przelewy24\Action\CaptureAction;
use EmilMassey\Payum\Przelewy24\Action\NotifyAction;
use EmilMassey\Payum\Przelewy24\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use EmilMassey\Payum\Przelewy24\Action\Api\DoRegisterAction;

class Przelewy24GatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'przelewy24',
            'payum.factory_title' => 'Przelewy24',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.api.do_register' => new DoRegisterAction(),
            'payum.action.api.do_verify' => new DoVerifyAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'sandbox' => false,
                'merchant_id' => null,
                'pos_id' => null,
                'sign' => null,
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['merchant_id', 'pos_id', 'sign'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array)$config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
