<?php

namespace EmilMassey\Payum\Przelewy24\Action\Api;

use EmilMassey\Payum\Przelewy24\Constants;
use EmilMassey\Payum\Przelewy24\Request\Api\DoRegister;
use EmilMassey\Payum\Przelewy24\SumGenerator;
use League\Uri\Http as HttpUri;
use League\Uri\Modifiers\MergeQuery;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;

class DoRegisterAction extends BaseApiAwareAction implements GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;

    /**
     * @var GenericTokenFactory
     */
    protected $tokenFactory;

    /**
     * @param DoRegister $request
     *
     * @throws \Exception
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty(['email', 'amount', 'description', 'currency', 'payment_id']);

        if (!in_array($model['currency'], Constants::ALLOWED_CURRENCIES)) {
            throw new InvalidArgumentException('Invalid currency');
        }

        $token = $request->getToken();

        if (null !== $model['order_id']) {
            return;
        }

        $order = [];
        $this->setOrderUrls($token, $order);
        $this->setOrderClientData($model, $order);
        $this->setOrderBasicData($model, $order);

        $reply = $this->api->doRegister($order);

        $model['status'] = Constants::STATUS_PENDING;
        $request->setModel($model);

        throw new HttpRedirect($reply->getUrl());
    }

    public function supports($request)
    {
        return $request instanceof DoRegister && $request->getModel() instanceof \ArrayObject;
    }

    private function setOrderUrls(TokenInterface $token, array &$order): void
    {
        $order['p24_url_return'] = $token->getAfterUrl();
        $order['p24_url_status'] = $this->tokenFactory->createNotifyToken(
            $token->getGatewayName(),
            $token->getDetails()
        )->getTargetUrl();

        $cancelUri = HttpUri::createFromString($token->getTargetUrl());
        $modifier = new MergeQuery('cancelled=1');
        $cancelUri = (string) $modifier->process($cancelUri);
        $order['p24_url_cancel'] = $cancelUri;
    }

    private function setOrderClientData(ArrayObject $model, array &$order): void
    {
        $order['p24_email'] = $model['email'];

        if (isset($model['firstname']) || isset($model['lastname'])) {
            $order['p24_client'] = trim($model['firstname'] . ' ' . $model['lastname']);
        }

        if (isset($model['address'])) {
            $order['p24_address'] = $model['address'];
        }

        if (isset($model['city'])) {
            $order['p24_city'] = $model['city'];
        }

        if (isset($model['zip'])) {
            $order['p24_zip'] = $model['postalcode'];
        }
    }

    /**
     * @throws \Exception
     */
    private function setOrderBasicData(ArrayObject $model, array &$order): void
    {
        $sumGenerator = new SumGenerator(Constants::ACTION_REGISTER, $this->api->getOptions());

        $order['p24_merchant_id'] = $this->api->getOptions()['merchant_id'];
        $order['p24_pos_id'] = $this->api->getOptions()['pos_id'];
        $order['p24_session_id'] = $model['payment_id'];
        $order['p24_amount'] = $model['amount'];
        $order['p24_description'] = $model['description'];
        $order['p24_currency'] = $model['currency'];
        $order['p24_country'] = 'PL';
        $order['p24_api_version'] = Constants::API_VERSION;
        $order['p24_sign'] = $sumGenerator->generate($order);
    }
}
