<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class AsyncGeneralProgressEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public $user_id;
	public $project_code;
	public $branch_name;
	public $status;
	public $exitcode;
	public $stdout;
	public $stderr;
	public $channel_name;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user_id, $project_code, $branch_name, $status, $exitcode, $stdout, $stderr, $channel_name)
    {
		$this->user_id = $user_id;
		$this->project_code = $project_code;
		$this->branch_name = $branch_name;
		$this->status = $status;
		$this->exitcode = $exitcode;
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
		$channel_name = $this->channel_name;
		if( !strlen($channel_name) ){
			$channel_name = $this->project_code.'---'.$this->branch_name.'___async-progress.'.$this->user_id; // default
		}
        return new Channel($channel_name);
    }

	public function broadcastWith()
	{
		return [
			'status' => $this->status,
			'exitcode' => $this->exitcode,
			'stdout' => $this->stdout,
			'stderr' => $this->stderr,
		];
	}
}
