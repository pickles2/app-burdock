<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class AsyncPlumEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public $user_id;
	public $project_code;
	public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user_id, $project_code, $message)
    {
		$this->user_id = $user_id;
		$this->project_code = $project_code;
		$this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel($this->project_code.'___plum-message.'.$this->user_id);
    }

	public function broadcastWith()
	{
		return [
			'message' => $this->message,
		];
	}
}
