<?php

namespace App\Services\Messaging;

use App\Models\NotificationPreference;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class MessagingPolicyService
{
    public function checkRateLimit(int $senderId, string $channel, string $action = 'send'): bool
    {
        $limit = $action === 'reply'
            ? (int) config('messaging.rate_limits.reply_per_minute', 30)
            : (int) config('messaging.rate_limits.send_per_minute', 20);

        $channelLimit = (int) config('messaging.rate_limits.channels.' . $channel, $limit);
        $limit = min($limit, $channelLimit);

        $key = 'messaging:' . $action . ':' . $channel . ':' . $senderId;
        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return false;
        }

        RateLimiter::hit($key, 60);
        return true;
    }

    public function canDeliver(User $recipient, string $channel, string $messageType, ?User $sender = null): bool
    {
        $preference = NotificationPreference::query()
            ->where('user_id', $recipient->id)
            ->where('channel', $channel)
            ->first();

        if ($sender && $preference && is_array($preference->vip_senders) && ! empty($preference->vip_senders)) {
            if (in_array($sender->email, $preference->vip_senders, true)) {
                return true;
            }
        }

        if ($preference && ! $preference->is_enabled) {
            return false;
        }

        if ($channel === 'sms' && config('messaging.compliance.tcpa.require_opt_in')) {
            if (! $preference || ! $preference->is_enabled) {
                return false;
            }
        }

        if ($preference && is_array($preference->message_types) && ! empty($preference->message_types)) {
            if (! in_array($messageType, $preference->message_types, true)) {
                return false;
            }
        }

        if ($preference && $this->withinQuietHours($preference)) {
            return false;
        }

        return true;
    }

    public function applyComplianceFooters(string $body, string $channel): string
    {
        $body = trim($body);

        if ($channel === 'sms') {
            $body = trim(strip_tags($body));
        }

        if ($channel === 'email' && config('messaging.compliance.can_spam.require_unsubscribe')) {
            $footer = trim((string) config('messaging.compliance.can_spam.footer'));
            if ($footer !== '' && ! Str::contains($body, $footer)) {
                $body .= "\n\n" . $footer;
            }
        }

        if ($channel === 'sms' && config('messaging.compliance.tcpa.require_opt_in')) {
            $notice = trim((string) config('messaging.compliance.tcpa.stop_notice'));
            if ($notice !== '' && ! Str::contains(Str::lower($body), Str::lower($notice))) {
                $body .= ' ' . $notice;
            }
        }

        return $body;
    }

    private function withinQuietHours(NotificationPreference $preference, ?CarbonImmutable $now = null): bool
    {
        $start = $preference->quiet_hours_start;
        $end = $preference->quiet_hours_end;

        if (! $start || ! $end) {
            return false;
        }

        $now = $now ?: CarbonImmutable::now();
        $startMinutes = ((int) $start->format('H')) * 60 + (int) $start->format('i');
        $endMinutes = ((int) $end->format('H')) * 60 + (int) $end->format('i');
        $nowMinutes = ((int) $now->format('H')) * 60 + (int) $now->format('i');

        if ($startMinutes === $endMinutes) {
            return false;
        }

        if ($startMinutes < $endMinutes) {
            return $nowMinutes >= $startMinutes && $nowMinutes <= $endMinutes;
        }

        return $nowMinutes >= $startMinutes || $nowMinutes <= $endMinutes;
    }
}
