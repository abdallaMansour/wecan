<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class FCMService
{
    protected $client;
    protected $firebaseUrl;
    protected $serverKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->firebaseUrl = 'https://fcm.googleapis.com/v1/projects/wecan-t-ahx2/messages:send';
        $this->serverKey = file_get_contents(storage_path('/app/firebase.json'));
    }

  public function sendNotification($fcmToken, $title, $body, $data = [])
    {
        // Ensure $data is always an associative array
        $data = !empty($data) ? $data : (object)[];

        $message = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ],
        ];

        Log::info('Sending FCM notification', [
            'fcm_token' => $fcmToken,
            'title' => $title,
            'body' => $body,
            'data' => $data
        ]);

        try {
            $response = $this->client->post($this->firebaseUrl, [
                'json' => $message,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                ],
            ]);

            $responseBody = json_decode($response->getBody(), true);
            Log::info('FCM notification sent successfully', ['response' => $responseBody]);

            return $responseBody;
        } catch (RequestException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            Log::error('FCM notification failed', [
                'error' => $errorResponse,
                'exception' => $e->getMessage()
            ]);

            return $errorResponse;
        }
    }

    private function getAccessToken()
    {
        $client = new \Google_Client();
        $client->setAuthConfig(storage_path('/app/firebase.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $token = $client->fetchAccessTokenWithAssertion();
        return $token['access_token'];
    }
}