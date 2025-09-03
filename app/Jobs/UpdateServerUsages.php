<?php

namespace App\Jobs;

use App\Models\Server;
use Aws\Laravel\AwsFacade;
use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;

class UpdateServerUsages implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $cw = AwsFacade::createClient('cloudwatch');

        $period = 300;

        $start = (Carbon::parse('first day of this month 00:00:00'))
            ->format(DateTime::ATOM);
        $end = (Carbon::parse('now'))
            ->format(DateTime::ATOM);

        Server::query()
            ->chunk(50, function (Collection $servers) use ($cw, $period, $end, $start) {
                $servers->each(function (Server $server) use ($cw, $period, $end, $start) {
                    $uptimeSeconds = 0;
                    $nextToken = null;

                    do {
                        $params = [
                            'StartTime'         => $start,
                            'EndTime'           => $end,
                            'MetricDataQueries' => [[
                                'Id'         => 'cpuutil',
                                'MetricStat' => [
                                    'Metric' => [
                                        'Namespace'  => 'AWS/EC2',
                                        'MetricName' => 'CPUUtilization',
                                        'Dimensions' => [
                                            ['Name' => 'InstanceId', 'Value' => $server->ec2_instance_id]
                                        ],
                                    ],
                                    'Period' => $period,
                                    'Stat'   => 'Average',
                                ],
                                'ReturnData' => true,
                            ]],
                            'ScanBy'            => 'TimestampAscending',
                        ];

                        if ($nextToken) {
                            $params['NextToken'] = $nextToken;
                        }

                        $res = $cw->getMetricData($params);

                        foreach ($res['MetricDataResults'] as $series) {
                            $count = 0;
                            foreach (Arr::get($series, 'Values') as $v) {
                                if ($v !== null) {
                                    $count++;
                                }
                            }
                            $uptimeSeconds += $count * $period;
                        }

                        $nextToken = $res['NextToken'] ?? null;
                    } while ($nextToken);

                    $server->monthlyServerUsages()
                        ->latest()
                        ->first()
                        ->update(['uptime_in_seconds' => $uptimeSeconds]);

                    return $uptimeSeconds;
                });
            });
    }
}
