<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Resolve Telegram external_id from username.
     *
     * @param string $username
     * @return string|null
     */
    public function resolveExternalIdFromUsername(string $username): ?string
    {
        $cleanUsername = ltrim($username, '@');
        $chatId = '@' . $cleanUsername;

        $token = config('services.telegram.posting_bot_token') ?: '8477015476:AAGKvIdxdmbJ7LVk4f5JHyjUl1BJ40KOo-I';
        $url = "https://api.telegram.org/bot{$token}/getChat";

        try {
            $response = Http::get($url, ['chat_id' => $chatId]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['ok']) && $data['ok'] === true && isset($data['result']['id'])) {
                    return (string) $data['result']['id'];
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to resolve Telegram chat_id for {$chatId}: " . $e->getMessage());
        }

        return null;
    }
}
