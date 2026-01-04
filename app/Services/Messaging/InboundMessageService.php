<?php

namespace App\Services\Messaging;

use App\Models\Message;
use App\Models\MessageThread;
use App\Models\MessageThreadParticipant;
use App\Models\User;
use Illuminate\Support\Arr;

class InboundMessageService
{
    public function handleEmail(array $payload): ?Message
    {
        $from = Arr::get($payload, 'from');
        $subject = trim((string) Arr::get($payload, 'subject'));
        $body = trim((string) (Arr::get($payload, 'text') ?? Arr::get($payload, 'body')));

        if (! $from || $body === '') {
            return null;
        }

        $sender = User::query()->where('email', $from)->first();
        if (! $sender) {
            return null;
        }

        $thread = $this->resolveThread($subject, $sender->id);

        $message = Message::create([
            'thread_id' => $thread->id,
            'user_id' => $sender->id,
            'sender_id' => $sender->id,
            'subject' => $thread->subject,
            'body' => $body,
            'timestamp' => now(),
            'message_type' => 'direct',
            'channel' => 'email',
        ]);

        MessageThreadParticipant::firstOrCreate([
            'thread_id' => $thread->id,
            'user_id' => $sender->id,
        ]);

        return $message;
    }

    public function handleSms(array $payload): ?Message
    {
        $from = Arr::get($payload, 'from');
        $body = trim((string) Arr::get($payload, 'body'));

        if (! $from || $body === '') {
            return null;
        }

        $sender = User::query()->where('phone', $from)->first();
        if (! $sender) {
            return null;
        }

        $thread = MessageThread::query()
            ->whereHas('participants', fn ($builder) => $builder->where('user_id', $sender->id))
            ->latest()
            ->first();

        if (! $thread) {
            $thread = MessageThread::create([
                'subject' => 'SMS Conversation ' . now()->format('M d, H:i'),
                'created_by_user_id' => $sender->id,
                'type' => 'direct',
            ]);
        }

        $message = Message::create([
            'thread_id' => $thread->id,
            'user_id' => $sender->id,
            'sender_id' => $sender->id,
            'subject' => $thread->subject,
            'body' => $body,
            'timestamp' => now(),
            'message_type' => 'direct',
            'channel' => 'sms',
        ]);

        MessageThreadParticipant::firstOrCreate([
            'thread_id' => $thread->id,
            'user_id' => $sender->id,
        ]);

        return $message;
    }

    private function resolveThread(string $subject, int $senderId): MessageThread
    {
        $threadId = null;
        if (preg_match('/thread\s?#(\d+)/i', $subject, $match)) {
            $threadId = (int) $match[1];
        }

        $thread = $threadId ? MessageThread::find($threadId) : null;
        if (! $thread) {
            $thread = MessageThread::query()
                ->where('subject', $subject)
                ->whereHas('participants', fn ($builder) => $builder->where('user_id', $senderId))
                ->latest()
                ->first();
        }

        if (! $thread) {
            $thread = MessageThread::create([
                'subject' => $subject !== '' ? $subject : 'Inbound Message',
                'created_by_user_id' => $senderId,
                'type' => 'direct',
            ]);
        }

        return $thread;
    }
}
