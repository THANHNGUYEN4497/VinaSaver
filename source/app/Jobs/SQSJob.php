<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use ShiftOneLabs\LaravelSqsFifoQueue\Bus\SqsFifoQueueable;

use Log;

class SQSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SqsFifoQueueable;

    protected $payload;
    public function __construct($payload)
    {
        $this->payload = json_encode($payload);
    }
    public function getPayload()
    {
        Log::info($this->payload);
        return $this->payload;
    }
}
