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
            $result = $ec2->stopInstances([
                'InstanceIds' => [$this->server->ec2_instance_id],
            ]);

            $state = Arr::get($result, 'StoppingInstances.0.CurrentState.Name', 'unknown');
            Log::info("StopEc2: stop requested for {$this->server->ec2_instance_id}", [
                'server_id' => $this->server->id,
                'state'     => $state,
            ]);

            $desc = $ec2->describeInstances([
                'InstanceIds' => [$this->server->ec2_instance_id],
            ]);

            $instance = Arr::get($desc, 'Reservations.0.Instances.0', []);
            $publicIp = Arr::get($instance, 'PublicIpAddress');
            $state = Arr::get($instance, 'State.Name', $state);

            $this->server->updateQuietly([
                'status' => $state,
                'ip'     => $publicIp,
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
