<?php

namespace App\Observers;

use App\Jobs\CreateEc2;
use App\Jobs\DeleteEc2;
use App\Jobs\StartEc2;
use App\Jobs\StopEc2;
use App\Models\Server;

class ServerObserver
{
    public function created(Server $server): void
    {
        CreateEc2::dispatch($server);
    }

    public function updated(Server $server): void
    {
        if (!$server->isDirty('status')) {
            return;
        }

        if ($server->status === Server::STATUS_STARTING) {
            StartEc2::dispatch($server);
        }

        if ($server->status === Server::STATUS_STOPPING) {
            StopEc2::dispatch($server);
        }
    }

    public function deleted(Server $server): void
    {
        DeleteEc2::dispatch($server);
    }
}
