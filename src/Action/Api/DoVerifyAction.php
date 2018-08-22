<?php

namespace EmilMassey\Payum\Przelewy24\Action\Api;

use EmilMassey\Payum\Przelewy24\Constants;
use EmilMassey\Payum\Przelewy24\Request\Api\DoVerify;
use EmilMassey\Payum\Przelewy24\SumGenerator;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;

class DoVerifyAction extends BaseApiAwareAction
{
    /**
     * @param DoVerify $request
     *
     * @throws \Exception
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if ('POST' !== $httpRequest->method || empty($httpRequest->content)) {
            throw new HttpResponse('Bad request', 400);
        }

        $model->validateNotEmpty(['payment_id', 'amount', 'currency']);
        $model['order_id'] = $httpRequest->request['p24_order_id'];

        $sumGenerator = new SumGenerator(Constants::ACTION_VERIFY, $this->api->getOptions());

        try {
            $fields = [
                'p24_merchant_id' => $this->api->getOptions()['merchant_id'],
                'p24_pos_id' => $this->api->getOptions()['pos_id'],
                'p24_session_id' => $model['payment_id'],
                'p24_amount' => $model['amount'],
                'p24_currency' => $model['currency'],
                'p24_order_id' => $model['order_id'],
            ];

            $fields['p24_sign'] = $sumGenerator->generate($fields);

            $this->api->doVerify($fields);
        } catch (RuntimeException $e) {
            $model['status'] = Constants::STATUS_FAILED;
            $request->setModel($model);

            throw new HttpResponse('Cannot verify', 400);
        }

        $model['status'] = Constants::STATUS_SUCCESS;
        $request->setModel($model);
    }

    public function supports($request)
    {
        return $request instanceof DoVerify && $request->getModel() instanceof \ArrayObject;
    }
}
