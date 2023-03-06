<?php

namespace App\Validators;

use GuzzleHttp\Client;
use GuzzelHttp\RequestOptions;

class ReCaptcha
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        if (!config('settings::recaptcha')) {
            return true;
        }
        if (config('settings::recaptcha_type') == 'v2' || config('settings::recaptcha_type') == 'v2_invisible' || config('settings::recaptcha_type') == 'v3') {
            if (!$value) {
                return false;
            }
            $client = new Client();
            $response = $client->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'form_params' => [
                        'secret' => config('settings::recaptcha_secret_key'),
                        'response' => $value,
                    ],
                ]
            );
            $body = json_decode((string) $response->getBody());
            if (config('settings::recaptcha_type') == 'v3') {
                if (!$body->success) {
                    return false;
                }
                if ($body->score < 0.5) {
                    return false;
                }
            }
            return $body->success;
        } elseif (config('settings::recaptcha_type') == 'turnstile') {
            if (!$value) {
                return false;
            }
            $client = new Client();
            $response = $client->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                [
                    'form_params' => [
                        'secret' => config('settings::recaptcha_secret_key'),
                        'response' => $value,
                    ]
                ]
            )->withHeader(
                'Accept',
                'application/json'
            );
            $body = json_decode($response->getBody());
            return $body->success;
        } elseif(config('settings::recaptcha_type') == 'hcaptcha') {
            if (!$value) {
                return false;
            }
            $client = new Client();
            $response = $client->post(
                'https://hcaptcha.com/siteverify',
                [
                    'form_params' => [
                        'secret' => config('settings::recaptcha_secret_key'),
                        'response' => $value,
                    ]
                ]
            )->withHeader(
                'Accept',
                'application/json'
            );
            $body = json_decode($response->getBody());
            return $body->success;
        }
        return false;
    }
}
