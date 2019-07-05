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

	public $parse;
	public $judge;
	public $queue_count;
	public $publish_file;
	public $end_publish;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($parse, $judge, $queue_count, $publish_file, $end_publish)
    {
        //
		$this->parse = $parse;
		$this->judge = $judge;
		$this->queue_count = $queue_count;
		$this->publish_file = $publish_file;
		$this->end_publish = $end_publish;
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
			'parse' => $this->parse,
			'judge' => $this->judge,
			'queue_count' => $this->queue_count,
			'publish_file' => $this->publish_file,
			'end_publish' => $this->end_publish
		];
	}
}
