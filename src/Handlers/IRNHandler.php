<?php

namespace Pine\SimplePay\Handlers;

use Exception;
use Pine\SimplePay\Payloads\StatusPayload;
use Pine\SimplePay\Support\Config;
use Pine\SimplePay\Support\Log;
use Pine\SimplePay\Support\Request;

class IRNHandler extends Handler
{
    /**
     * Handle the IRN request.
     *
     * @param  array  $payload
     * @return void
     */
    public function handle($payload)
    {
        Log::info(sprintf(
            "%s\n%s",
            __('IRN event was fired.', 'pine-simplepay'),
            json_encode($payload)
        ));

        $request = Request::post(
            Config::url('query'),
            StatusPayload::handle($payload['transactionId'])
        );

        try {
            $request->send();

            if ($request->valid()) {
                $amount = (float) $this->order->get_remaining_refund_amount();
                $amount -= (float) $request->body('transactions.0.remainingTotal');

                if ($amount > 0) {
                    wc_create_refund([
                        'amount' => $amount,
                        'order_id' => $this->order->get_id(),
                    ]);
                }
            }
        } catch (Exception $e) {
            //
        }
    }
}
