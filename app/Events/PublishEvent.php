<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use App\Publish;

class PublishEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public $stdout;
	public $stderr;
	public $parse;
	public $judge;
	public $queue_count;
	public $alert_array;
	public $time_array;
	public $publish_file;
	public $end_publish;
	public $total_files;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($stdout, $stderr, $parse, $judge, $queue_count, $alert_array, $time_array, $publish_file, $end_publish, $total_files)
    {
        //
		$this->stdout = $stdout;
		$this->stderr = $stderr;
		$this->parse = $parse;
		$this->judge = $judge;
		$this->queue_count = $queue_count;
		$this->alert_array = $alert_array;
		$this->time_array = $time_array;
		$this->publish_file = $publish_file;
		$this->end_publish = $end_publish;
		$this->total_files = $total_files;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('publish-event');
    }

	public function broadcastWith()
	{
		return [
			'message' => $this->stdout,
			'error' => $this->stderr,
			'parse' => $this->parse,
			'judge' => $this->judge,
			'queue_count' => $this->queue_count,
			'alert_array' => $this->alert_array,
			'time_array' => $this->time_array,
			'publish_file' => $this->publish_file,
			'end_publish' => $this->end_publish,
			'total_files' => $this->total_files,
		];
	}
}
