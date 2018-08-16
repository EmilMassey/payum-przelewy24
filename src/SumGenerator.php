<?php

namespace EmilMassey\Payum\Przelewy24;

final class SumGenerator
{
    /**
     * @var string
     */
    private $action;

    /**
     * @var array
     */
    private $options;

    public function __construct(string $action, array $options)
    {
        if (!in_array($action, [Constants::ACTION_REGISTER, Constants::ACTION_VERIFY])) {
            throw new \InvalidArgumentException("Invalid action");
        }

        $this->action = $action;
        $this->options = $options;
    }

    /**
     * @throws \Exception
     */
    public function generate(array $details): string
    {
        switch ($this->action) {
            case Constants::ACTION_REGISTER:
                return $this->generateForRegister($details);
            case Constants::ACTION_VERIFY:
                return $this->generateForVerify($details);
        }

        throw new \Exception("Invalid action");
    }

    private function generateForRegister(array $details): string
    {
        return md5(
            implode("|", [
                $details['p24_session_id'],
                $this->options['merchant_id'],
                $details['p24_amount'],
                $details['p24_currency'],
                $this->options['sign'],
            ])
        );
    }

    private function generateForVerify(array $details): string
    {
        return md5(
            implode("|", [
                $details['p24_session_id'],
                $details['p24_order_id'],
                $details['p24_amount'],
                $details['p24_currency'],
                $this->options['sign'],
            ])
        );
    }
}
