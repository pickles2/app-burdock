<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PublishEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public $user_id;
	public $project_code;
	public $branch_name;
	public $parse;
	public $judge;
	public $queue_count;
	public $publish_file;
	public $end_publish;
	public $process;
	public $pipes;
	public $channel_name;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user_id, $project_code, $branch_name, $parse, $judge, $queue_count, $publish_file, $end_publish, $process, $pipes, $channel_name)
    {
		$this->user_id = $user_id;
		$this->project_code = $project_code;
		$this->branch_name = $branch_name;
		$this->parse = $parse;
		$this->judge = $judge;
		$this->queue_count = $queue_count;
		$this->publish_file = $publish_file;
		$this->end_publish = $end_publish;
		$this->process = $process;
		$this->pipes = $pipes;
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
			$channel_name = urlencode($this->project_code).'----'.urlencode($this->branch_name).'___publish'.'.'.$this->user_id; // default
		}
        return new Channel($channel_name);
    }

	public function broadcastWith()
	{
		return [
			'parse' => $this->parse,
			'judge' => $this->judge,
			'queue_count' => $this->queue_count,
			'publish_file' => $this->publish_file,
			'end_publish' => $this->end_publish,
			'process' => $this->process,
			'pipes' => $this->pipes
		];
	}
}
