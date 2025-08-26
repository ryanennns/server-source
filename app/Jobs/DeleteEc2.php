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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class DeleteEc2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public Server $server)
    {
    }

    public function handle(): void
    {
        Log::info('handle validation passed');

        /** @var Ec2Client $ec2 */
        $ec2 = AWS::createClient('ec2');

        $instanceId = $this->server->ec2_instance_id;

        if (!$instanceId) {
            Log::warning("DeleteEc2: No instance ID on server {$this->server->id}");
            return;
        }

        try {
            $result = $ec2->terminateInstances([
                'InstanceIds' => [$instanceId],
            ]);

            $state = Arr::get($result, 'TerminatingInstances.0.CurrentState.Name') ?? 'unknown';

            Log::info("DeleteEc2: terminated $instanceId", [
                'server_id' => $this->server->id,
                'state'     => $state,
            ]);

            $this->server->updateQuietly([
                'ec2_instance_id' => null,
                'ip'       => null,
                'status'          => Server::STATUS_TERMINATED,
            ]);

        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() === 'InvalidInstanceID.NotFound') {
                Log::warning("DeleteEc2: instance not found $instanceId");
                // Still clear out the record so we don't try again
                $this->server->updateQuietly([
                    'ec2_instance_id' => null,
                    'ip'       => null,
                    'status'          => Server::STATUS_STOPPED,
                ]);
                return;
            }

            Log::error("DeleteEc2 AWS error for $instanceId: {$e->getAwsErrorMessage()}", [
                'code' => $e->getAwsErrorCode(),
            ]);

            throw $e;
        } catch (\Throwable $e) {
            Log::error("DeleteEc2 error for $instanceId: {$e->getMessage()}");
            throw $e;
        }
    }
}
