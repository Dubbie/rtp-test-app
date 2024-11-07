<?php

require 'vendor/autoload.php';

use App\Services\RtpPaymentService;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$paymentService = new RtpPaymentService();
$paymentService->getPaymentStatus($_GET['endToEndId']);
