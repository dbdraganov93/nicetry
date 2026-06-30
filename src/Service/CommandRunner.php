<?php

declare(strict_types=1);

namespace GeoProxy\Service;

class CommandRunner
{
    /** @return array{exit_code:int, output:string} */
    public function run(array $command, int $timeoutSeconds = 60): array
    {
        $descriptorSpec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptorSpec, $pipes);
        if (!is_resource($process)) {
            return ['exit_code' => 127, 'output' => 'Unable to start command.'];
        }

        $startedAt = time();
        $output = '';
        foreach ($pipes as $pipe) {
            stream_set_blocking($pipe, false);
        }

        while (true) {
            $status = proc_get_status($process);
            $output .= stream_get_contents($pipes[1]) ?: '';
            $output .= stream_get_contents($pipes[2]) ?: '';

            if (!$status['running']) {
                break;
            }

            if (time() - $startedAt > $timeoutSeconds) {
                proc_terminate($process);
                foreach ($pipes as $pipe) {
                    fclose($pipe);
                }
                proc_close($process);

                return ['exit_code' => 124, 'output' => trim($output . "\nCommand timed out.")];
            }

            usleep(100000);
        }

        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        $exitCode = proc_close($process);

        return ['exit_code' => $exitCode, 'output' => trim($output)];
    }
}
