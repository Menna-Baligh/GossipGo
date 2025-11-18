<?php

namespace App\Livewire;

use App\Models\ChatMessage;
use App\Models\User;
use Livewire\Component;
use Illuminate\Container\Attributes\Auth;

class Chat extends Component
{
    public $users ;
    public $selectedUser;
    public $newMessage ;
    public $messages;
    public function mount(){
        $this->users = User::whereNot('id', auth()->id())->latest()->get();
        $this->selectedUser = $this->users->first();
        $this->loadMessages();

    }
    public function selectUser($id){
        $this->selectedUser = User::findOrFail($id);
        $this->loadMessages();
    }
    public function loadMessages(){
        $this->messages = ChatMessage::where(function($query){
            $query->where('sender_id', auth()->id())
                ->where('receiver_id', $this->selectedUser->id);
        })->orWhere(function($query){
            $query->where('sender_id', $this->selectedUser->id)
                ->where('receiver_id',  auth()->id());
        })->latest()->get();
    }

    public function submit(){
        if(!$this->newMessage) return;
        $message = ChatMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->selectedUser->id,
            'message' => $this->newMessage,
        ]);
        $this->messages->push($message);
        $this->newMessage = '';

    }
    public function render()
    {
        return view('livewire.chat');
    }
}
