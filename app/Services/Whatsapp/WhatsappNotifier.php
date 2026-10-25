<?php

namespace App\Services\Whatsapp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappNotifier
{
    public function send(string $toPhoneNumber, string $message): bool
    {
        $baseUrl = config('loans.whatsapp.base_url');
        $phoneNumberId = config('loans.whatsapp.phone_number_id');
        $token = config('loans.whatsapp.token');

        if (! $phoneNumberId || ! $token) {
            Log::warning('Envío de WhatsApp omitido: credenciales no configuradas.', [
                'to' => $toPhoneNumber,
            ]);

            return false;
        }

        try {
            $response = Http::withToken($token)
                ->post("{$baseUrl}/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $toPhoneNumber,
                    'type' => 'text',
                    'text' => ['body' => $message],
                ]);

            if ($response->failed()) {
                Log::error('Fallo al enviar notificación de WhatsApp.', [
                    'to' => $toPhoneNumber,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Excepción al enviar notificación de WhatsApp.', [
                'to' => $toPhoneNumber,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
