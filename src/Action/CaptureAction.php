<?php
namespace EmilMassey\Payum\Przelewy24\Action;

use EmilMassey\Payum\Przelewy24\Constants;
use EmilMassey\Payum\Przelewy24\Request\Api\DoCancel;
use EmilMassey\Payum\Przelewy24\Request\Api\DoRegister;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (isset($httpRequest->query['cancelled'])) {
            $model['status'] = Constants::STATUS_CANCELED;

            $request->setModel($model);
            $request->getToken()->setTargetUrl($request->getToken()->getAfterUrl());

            return;
        }

        $register = new DoRegister($request->getToken());
        $register->setModel($request->getFirstModel());
        $register->setModel($model);

        $model['status'] = Constants::STATUS_NEW;
        $request->setModel($model);

        $this->gateway->execute($register);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
