<?php

namespace App\Livewire;

use Livewire\Component;

class Toast extends Component
{
    public array $notifications = [];

    public function mount()
    {
        if (session()->has('status')) {
            $this->addNotification(['message' => session('status'), 'type' => 'success']);
        }
        if (session()->has('success')) {
            $this->addNotification(['message' => session('success'), 'type' => 'success']);
        }
        if (session()->has('error')) {
            $this->addNotification(['message' => session('error'), 'type' => 'error']);
        }
        if (session()->has('warning')) {
            $this->addNotification(['message' => session('warning'), 'type' => 'warning']);
        }
    }

    protected $listeners = [
        'notify' => 'addNotification',
        'toast' => 'addNotification',
    ];

    public function addNotification($data)
    {
        if (is_string($data)) {
            $data = ['message' => $data, 'type' => 'success'];
        }

        $id = uniqid();
        $this->notifications[$id] = [
            'id' => $id,
            'message' => $data['message'] ?? '',
            'type' => $data['type'] ?? 'success',
            'title' => $data['title'] ?? null,
        ];

        // Auto-remove after 5 seconds
        $this->dispatch('remove-toast', id: $id);
    }

    public function remove($id)
    {
        unset($this->notifications[$id]);
    }

    public function render()
    {
        return view('livewire.toast');
    }
}
