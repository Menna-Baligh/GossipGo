<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Events\MessageSent;
use App\Models\ChatMessage;
use Illuminate\Container\Attributes\Auth;

class Chat extends Component
{
    public $users ;
    public $selectedUser;
    public $newMessage ;
    public $messages;
    public $authId ;
    public $loginId ;
    public function mount(){
        $this->users = User::whereNot('id', auth()->id())->latest()->get();
        $this->selectedUser = $this->users->first();
        $this->loadMessages();
        $this->loginId = auth()->id();

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
        broadcast(new MessageSent($message));

    }
    public function getListeners()
    {
        return [
            "echo-private:chat.{$this->loginId},MessageSent" => 'newChatMessageNotification',
        ];
    }
    public function newChatMessageNotification($message)
    {
        if($message['sender_id'] == $this->selectedUser->id){
            $messageObj = ChatMessage::find($message['id']);
            $this->messages->push($messageObj);
        }
    }
    public function render()
    {
        return view('livewire.chat');
    }
}
