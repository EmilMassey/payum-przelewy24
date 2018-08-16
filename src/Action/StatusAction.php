<?php

namespace EmilMassey\Payum\Przelewy24\Action;

use EmilMassey\Payum\Przelewy24\Constants;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['status']) {
            $request->markNew();
            return;
        }
        if (Constants::STATUS_PENDING === $model['status']) {
            $request->markPending();
            return;
        }
        if (Constants::STATUS_SUCCESS === $model['status']) {
            $request->markCaptured();
            return;
        }
        if (Constants::STATUS_CANCELED === $model['status']) {
            $request->markCanceled();
            return;
        }
        if (Constants::STATUS_FAILED === $model['status']) {
            $request->markFailed();
            return;
        }
        if (Constants::STATUS_EXPIRED === $model['status']) {
            $request->markExpired();
            return;
        }

        $request->markUnknown();

    }

    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
