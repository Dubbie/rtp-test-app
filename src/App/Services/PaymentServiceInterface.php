<?php

namespace App\Services;

interface PaymentServiceInterface
{

    public function verifyPayment(string $endToEndId);
}
