<?php

namespace App\Services\Messaging;

use App\Mail\MessageNotificationMail;
use App\Models\Message;
use App\Models\MessageParticipant;
use App\Services\SmsGateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MessagingDispatchService
{
    public function __construct(
        private readonly MessagingPolicyService $policy,
        private readonly SmsGateway $smsGateway,
    ) {
    }

    public function dispatch(Message $message): void
    {
        $message->loadMissing(['participants.user', 'sender', 'user']);

        foreach ($message->participants as $participant) {
            $user = $participant->user;
            if (! $user) {
                $participant->update(['delivery_status' => 'skipped']);
                continue;
            }

            $channel = $message->channel ?? 'in_app';
            $messageType = $message->message_type ?? 'direct';
            $sender = $message->sender ?? $message->user;
            if (! $this->policy->canDeliver($user, $channel, $messageType, $sender)) {
                $participant->update(['delivery_status' => 'skipped']);
                continue;
            }

            $body = $this->policy->applyComplianceFooters($message->body, $channel);

            try {
                $status = match ($channel) {
                    'email' => $this->sendEmail($message, $user->email, $body),
                    'sms' => $this->sendSms($user->phone, $body),
                    'push' => $this->queuePush($message, $user->id),
                    default => $this->markInApp($message, $user->id),
                };

                if ($status) {
                    $participant->update(['delivery_status' => $status]);
                }
            } catch (\Throwable $error) {
                Log::error('Message dispatch failed', [
                    'message_id' => $message->id,
                    'recipient_id' => $user->id,
                    'channel' => $channel,
                    'error' => $error->getMessage(),
                ]);

                $participant->update(['delivery_status' => 'failed']);
            }
        }
    }

    private function sendEmail(Message $message, ?string $email, string $body): string
    {
        if (! $email) {
            return 'skipped';
        }

        Mail::to($email)->send(new MessageNotificationMail($message, $body));
        return 'sent';
    }

    private function sendSms(?string $phone, string $body): string
    {
        if (! $phone) {
            return 'skipped';
        }

        $this->smsGateway->send($phone, $body);
        return 'sent';
    }

    private function queuePush(Message $message, int $userId): string
    {
        Log::info('Push notification queued', [
            'message_id' => $message->id,
            'user_id' => $userId,
        ]);

        return 'queued';
    }

    private function markInApp(Message $message, int $userId): string
    {
        MessageParticipant::query()
            ->where('message_id', $message->id)
            ->where('user_id', $userId)
            ->update(['delivery_status' => 'delivered']);

        return 'delivered';
    }
}
