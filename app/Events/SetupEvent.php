<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

use App\Setup;

class SetupEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public $status;
	public $exitcode;
	public $result;
	public $stdout;
	public $stderr;
	public $path_composer;
	public $std_parse;
	public $std_array;
	public $numerator;
	public $denominator;
	public $rate;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($status, $exitcode, $result, $stdout, $stderr, $path_composer, $std_parse, $std_array, $numerator, $denominator, $rate)
    {
        //
		$this->status = $status;
		$this->exitcode = $exitcode;
		$this->result = $result;
		$this->stdout = $stdout;
		$this->stderr = $stderr;
		$this->path_composer = $path_composer;
		$this->std_parse = $std_parse;
		$this->std_array = $std_array;
		$this->numerator = $numerator;
		$this->denominator = $denominator;
		$this->rate = $rate;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('setup-event');
    }

	public function broadcastWith()
	{
		return [
			'status' => $this->status,
			'exitcode' => $this->exitcode,
			'result' => $this->result,
			'stdout' => $this->stdout,
			'stderr' => $this->stderr,
			'path_composer'=> $this->path_composer,
			'std_parse' => $this->std_parse,
			'std_array' => $this->std_array,
			'numerator' => $this->numerator,
			'denominator' => $this->denominator,
			'rate' => $this->rate
		];
	}
}
