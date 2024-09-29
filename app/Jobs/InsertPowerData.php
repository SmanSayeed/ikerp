<?php
// app/Jobs/InsertPowerData.php

namespace App\Jobs;

use App\Models\PowerData; // Ensure to create a model for the PowerData table
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InsertPowerData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        foreach ($this->data as $item) {
            PowerData::create([
                'client_id' => 1,
                'time' => \Carbon\Carbon::createFromTimestampMs($item['doc']['time']),
                'nodeid' => $item['doc']['nodeid'],
                'power' => $item['doc']['power'],
            ]);
        }
    }
}
