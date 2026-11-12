<?php
declare(strict_types=1);
// Existing services in this Compose project are safe to reuse on an installer rerun.
$process = proc_open(['docker', 'compose', '--profile', 'standalone', '--profile', 'search', 'ps', '--format', 'json'], [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes);
fclose($pipes[0]);
$json = stream_get_contents($pipes[1]); fclose($pipes[1]);
$error = stream_get_contents($pipes[2]); fclose($pipes[2]);
if (proc_close($process) !== 0) {
    fwrite(STDERR, "Cannot inspect Compose services. Check that Docker or Podman is running.\n".$error);
    exit(1);
}
$containers = json_decode($json, true);
if (!is_array($containers) || !array_is_list($containers)) {
    $containers = array_filter(array_map(static fn ($line) => json_decode($line, true), explode("\n", trim($json))));
}
$owned = [];
foreach ($containers as $container) foreach ($container['Publishers'] ?? [] as $publisher) $owned[] = (int) $publisher['PublishedPort'];
$conflicts = [];
foreach ([5544, 8088, 19200] as $port) {
    if (in_array($port, $owned, true)) continue;
    $socket = @stream_socket_client('tcp://127.0.0.1:'.$port, $errno, $error, 0.3);
    if ($socket !== false) { fclose($socket); $conflicts[] = $port; }
}
if ($conflicts) {
    fwrite(STDERR, 'Another service is using local port(s): '.implode(', ', $conflicts).".\nRun bin/stop from your previous MemorialAtlas checkout, then retry.\nTo identify the owner: docker ps --format '{{.Names}} {{.Ports}}'\nNo existing services were stopped.\n");
    exit(1);
}
