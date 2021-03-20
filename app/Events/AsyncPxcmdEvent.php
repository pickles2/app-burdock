<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class AsyncPxcmdEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public $user_id;
	public $project_code;
	public $branch_name;
	public $stdout;
	public $stderr;
	public $channel_name;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user_id, $project_code, $branch_name, $stdout, $stderr, $channel_name)
    {
		$this->user_id = $user_id;
		$this->project_code = $project_code;
		$this->branch_name = $branch_name;
		$this->stdout = $stdout;
		$this->stderr = $stderr;
		$this->channel_name = $channel_name;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel($this->project_code.'---'.$this->branch_name.'___pxcmd-'.$this->channel_name.''.'.'.$this->user_id);
    }

	public function broadcastWith()
	{
		return [
			'stdout' => $this->stdout,
			'stderr' => $this->stderr,
		];
	}
}
