<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

use App\Publish;

class CustomConsoleExtensionsEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public $project_code;
	public $branch_name;
	public $cce_id;
	public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($project_code, $branch_name, $cce_id, $message)
    {
		$this->project_code = $project_code;
		$this->branch_name = $branch_name;
		$this->cce_id = $cce_id;
		$this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel($this->project_code.'---'.$this->branch_name.'___cce---'.$this->cce_id);
    }

	public function broadcastWith()
	{
		return [
			'project_code' => $this->project_code,
			'branch_name' => $this->branch_name,
			'cce_id' => $this->cce_id,
			'message' => $this->message,
		];
	}
}
