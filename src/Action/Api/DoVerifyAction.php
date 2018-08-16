<?php

namespace src\Action\Api;

use EmilMassey\Payum\Przelewy24\Action\Api\BaseApiAwareAction;
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

        $model->validateNotEmpty(['p24_session_id', 'p24_amount', 'p24_currency', 'p24_order_id']);

        $sumGenerator = new SumGenerator(Constants::ACTION_VERIFY, $this->api->getOptions());

        try {
            $this->api->doVerify([
                'p24_merchant_id' => $this->api->getOptions()['merchant_id'],
                'p24_pos_id' => $this->api->getOptions()['pos_id'],
                'p24_session_id' => $model['p24_session_id'],
                'p24_amount' => $model['p24_amount'],
                'p24_currency' => $model['p24_currency'],
                'p24_order_id' => $model['p24_order_id'],
                'p24_sign' => $sumGenerator->generate((array)$model),
            ]);
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
