<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use App\Setup;

class SetupOptionEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public $stdout;
	public $stderr;
	public $std_parse;
	public $std_array;
	public $numerator;
	public $denominator;
	public $rate;
	public $checked_option;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($stdout, $stderr, $std_parse, $std_array, $numerator, $denominator, $rate, $checked_option)
    {
        //
		$this->stdout = $stdout;
		$this->stderr = $stderr;
		$this->std_parse = $std_parse;
		$this->std_array = $std_array;
		$this->numerator = $numerator;
		$this->denominator = $denominator;
		$this->rate = $rate;
		$this->checked_option = $checked_option;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('setup-option-event');
    }

	public function broadcastWith()
	{
		return [
			'stdout' => $this->stdout,
			'stderr' => $this->stderr,
			'std_parse' => $this->std_parse,
			'std_array' => $this->std_array,
			'numerator' => $this->numerator,
			'denominator' => $this->denominator,
			'rate' => $this->rate,
			'checked_option' => $this->checked_option
		];
	}
}
