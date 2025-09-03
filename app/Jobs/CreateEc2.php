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

class CreateEc2 implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const INSTANCE_TYPE = 't3a.small';

    public function __construct(
        public Server $server,
        public string $instanceType = self::INSTANCE_TYPE,
        public string $amiId = '',
    )
    {
    }

    public function handle(): void
    {
        /** @var Ec2Client $ec2 */
        $ec2 = AWS::createClient('ec2');
        $nameTag = $this->server->name;

        try {
            $existing = $this->findInstanceByName($ec2, $nameTag);
            if ($existing !== null) {
                Log::info("CreateEc2: instance already exists for '{$nameTag}'", [
                    'instanceId' => $existing['InstanceId'] ?? null,
                    'state'      => $existing['State']['Name'] ?? null,
                ]);
                return;
            }

            // 2) Resolve AMI (env override or latest Amazon Linux 2023)
            $imageId = $this->amiId ?: $this->latestAl2023Ami($ec2);

            $result = $ec2->runInstances([
                'ImageId'                           => $imageId,
                'InstanceType'                      => self::INSTANCE_TYPE,
                'MinCount'                          => 1,
                'MaxCount'                          => 1,
                'InstanceInitiatedShutdownBehavior' => 'stop',
                'IamInstanceProfile'                => [
                    'Name' => 'EC2xS3Access',
                ],
                'NetworkInterfaces'                 => [[
                    'AssociatePublicIpAddress' => true,
                    'DeviceIndex'              => 0,
                    'Groups'                   => [Server::MC_SERVER_SG]
                ]],
                'TagSpecifications'                 => [[
                    'ResourceType' => 'instance',
                    'Tags'         => [
                        ['Key' => 'Name', 'Value' => $nameTag],
                    ],
                ]],
            ]);

            $instanceId = Arr::get($result, 'Instances.0.InstanceId') ?? null;

            $ec2->waitUntil('InstanceRunning', [
                'InstanceIds' => [$instanceId],
                '@waiter'     => [
                    'delay'       => 1,
                    'maxAttempts' => 10,
                ]
            ]);

            $desc = $ec2->describeInstances(['InstanceIds' => [$instanceId]]);

            $interfaceId = Arr::get($desc, 'Reservations.0.Instances.0.NetworkInterfaces.0.NetworkInterfaceId');

            $response = $ec2->modifyNetworkInterfaceAttribute([
                'NetworkInterfaceId' => $interfaceId,
                'Groups'             => [Server::MC_SERVER_SG],
            ]);

            Log::info('Snickers', json_decode(json_encode($response), true));

            $publicIp = Arr::get($desc, 'Reservations.0.Instances.0.PublicIpAddress');

            Log::info("CreateEc2: launched instance for '{$nameTag}'", [
                'instanceId' => $instanceId,
                'imageId'    => $imageId,
                'type'       => self::INSTANCE_TYPE,
                'publicIp'   => $publicIp,
            ]);

            $this->server->updateQuietly([
                'ec2_instance_id' => $instanceId,
                'ip'              => $publicIp,
                'instance_type'   => self::INSTANCE_TYPE,
                'status'          => Server::STATUS_STARTED,
                'region'          => config('aws.region', 'us-east-1'),
            ]);
        } catch (AwsException $e) {
            Log::error("CreateEc2 AWS error for '{$nameTag}': {$e->getAwsErrorMessage()}", [
                'code' => $e->getAwsErrorCode(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error("CreateEc2 error for '{$nameTag}': {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Return the first non-terminated instance that has tag Name=$name, or null.
     */
    private function findInstanceByName(Ec2Client $ec2, string $name): ?array
    {
        $resp = $ec2->describeInstances([
            'Filters' => [
                ['Name' => 'tag:Name', 'Values' => [$name]],
                ['Name' => 'instance-state-name', 'Values' => [
                    'pending', 'running', 'stopping', 'stopped', 'shutting-down'
                ]],
            ],
        ]);

        foreach ($resp['Reservations'] ?? [] as $res) {
            foreach ($res['Instances'] ?? [] as $inst) {
                return $inst; // first match
            }
        }
        return null;
    }

    /**
     * Pick the newest Amazon Linux 2023 AMI (x86_64, HVM, EBS).
     * Set AWS_EC2_AMI_ID in .env to pin a specific AMI instead.
     */
    private function latestAl2023Ami(Ec2Client $ec2): string
    {
        $resp = $ec2->describeImages([
            'Owners'  => ['amazon'],
            'Filters' => [
                ['Name' => 'name', 'Values' => ['al2023-ami-*-x86_64']],
                ['Name' => 'state', 'Values' => ['available']],
                ['Name' => 'root-device-type', 'Values' => ['ebs']],
                ['Name' => 'virtualization-type', 'Values' => ['hvm']],
            ],
        ]);

        $images = $resp['Images'] ?? [];
        usort($images, fn($a, $b) => strcmp($b['CreationDate'], $a['CreationDate']));

        if (!$images) {
            throw new \RuntimeException('No Amazon Linux 2023 AMIs found in this region.');
        }

        return $images[0]['ImageId'];
    }
}
