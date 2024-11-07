<?php

namespace App\Services;

class RtpPaymentService implements PaymentServiceInterface
{
    const SUCCES_STATUS = 'SUCCESS';

    protected string $checkPaymentApiUrl;

    public function __construct()
    {
        $this->checkPaymentApiUrl = sprintf(
            '%s%s',
            $_ENV['RTP_API_URL'],
            '/public/api/payment-request/v1/status',
        );

        echo $this->checkPaymentApiUrl . "<br>";
    }


    public function verifyPayment(string $endToEndId): bool
    {
        $responseData = $this->getPaymentStatus($endToEndId);
        return $responseData['status'] === self::SUCCES_STATUS;
    }

    private function generateJWT()
    {
        $privateKeyPath = __DIR__ . '/../../certs/private.pem';

        $privateKey = file_get_contents($privateKeyPath);
        if (!$privateKey) {
            die('Nem található privát kulcs');
        }

        // Define header
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];


        $iat = time();
        $exp = $iat + (60 * 60);
        $merchantId = $_ENV['RTP_MERCHANT_ID'];

        if ($merchantId === null) {
            die('Nincs megadva Merchant az RTP fizetés ellenőrzéséhez.');
        }

        $payload = [
            'iat' => $iat,
            'exp' => $exp,
            'merchantId' => $merchantId,
        ];

        // Encode header and payload to Base64 URL format.
        $base64UrlHeader = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $base64UrlPayload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

        // Create the signature base string
        $signatureBase = $base64UrlHeader . "." . $base64UrlPayload;

        // Sign the data
        openssl_sign($signatureBase, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        // Encode signature to Base64 URL format
        $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        // Create the JWT
        $jwt = sprintf('%s.%s.%s', $base64UrlHeader, $base64UrlPayload, $base64UrlSignature);

        return $jwt;
    }

    public function getPaymentStatus(string $endToEndId)
    {
        // Generate JWT
        $jwt = $this->generateJWT();

        echo "RTP Fizetés lekérése folyamatban..." . '<br>';
        echo "- REQUEST_URL: " . $this->checkPaymentApiUrl . '<br>';
        echo "- END_TO_END_ID: " . $endToEndId . '<br>';
        echo "- JWT: " . $jwt . '<br>';

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $this->checkPaymentApiUrl . '?endToEndId=' . $endToEndId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // To return the response as a string
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Merchant-Authorization: Bearer ' . $jwt
        ]);

        // Execute the cURL request and get the response
        $response = curl_exec($ch);

        // Check if there were any errors
        if (curl_errno($ch)) {
            // Handle cURL error
            echo 'cURL error: ' . curl_error($ch) . '<br>';
            curl_close($ch);
            return ['status' => 'ERROR', 'message' => curl_error($ch)];
        }

        // Close the cURL session
        curl_close($ch);

        // Decode JSON response
        $responseData = json_decode($response, true);

        // Echo the decoded response
        echo "<br>Válasz: " . print_r($responseData, true) . '<br>';

        // Check if the response is successful
        if (isset($responseData['status']) && $responseData['status'] === 'SUCCESS') {
            echo "RTP Fizetés lekérés sikeres!" . '<br>';
            return $responseData;
        }

        // Log unsuccessful response
        echo "RTP Fizetés lekérés sikertelen!" . '<br>';
        return ['status' => 'FAILED'];
    }
}
