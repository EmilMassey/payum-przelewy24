<?php
namespace EmilMassey\Payum\Przelewy24\Action;

use EmilMassey\Payum\Przelewy24\Constants;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details = array_merge((array)$details, [
            'amount' => $payment->getTotalAmount(),
            'description' => $payment->getDescription(),
            'email' => $payment->getClientEmail(),
            'payment_id' => $payment->getNumber(),
            'currency' => $payment->getCurrencyCode(),
        ]);

        $details['status'] = Constants::STATUS_NEW;

        $request->setResult($details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() == 'array';
    }
}
