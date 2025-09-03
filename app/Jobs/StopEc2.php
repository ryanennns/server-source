<?php

namespace App\Jobs;

use App\Models\Server;
use Aws\Ec2\Ec2Client;
use Aws\Exception\AwsException;
use Aws\Laravel\AwsFacade as AWS;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class StopEc2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Server $server)
    {
    }

    public function handle(): void
    {
        /** @var Ec2Client $ec2 */
        $ec2 = AWS::createClient('ec2');

        if (!$this->server->ec2_instance_id) {
            Log::warning("StopEc2: No instance ID for server {$this->server->id}");
            return;
        }

        try {
            $ec2->stopInstances([
                'InstanceIds' => [$this->server->ec2_instance_id],
            ]);

            $ec2->waitUntil('InstanceRunning', [
                'InstanceIds' => [$this->server->ec2_instance_id],
                '@waiter'     => [
                    'delay'       => 1,
                    'maxAttempts' => 10,
                ]
            ]);

            $this->server->updateQuietly([
                'status' => Server::STATUS_STOPPED,
                'ip'     => null,
            ]);

        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() === 'InvalidInstanceID.NotFound') {
                Log::warning("StopEc2: instance not found {$this->server->ec2_instance_id}");
                return;
            }
            throw $e;
        }
    }
}
