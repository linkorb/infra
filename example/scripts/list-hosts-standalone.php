<?php

/**
 * This example script shows how to utilize the `infra query` command
 * as a standalone script, without the help of external libraries.
 */

// Create the GraphQL query
$query = <<<GRAPHQL
query {
    hosts: allHosts {
        name
        fqdn
        publicIp
        privateIp
    }
}
GRAPHQL;

// Setup the stdin/stdout pipes
$descriptorspec = [
    0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
    1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
    2 => array("pipe", "w"),  // stderr is a pipe that the child will write to
];

// Execute the console command
$cmd = 'infra query';
$process = proc_open($cmd, $descriptorspec, $pipes);
if (!is_resource($process)) {
    echo "Error executing $cmd";
    exit(-1);
}

// Write query to stdin and close it
fwrite($pipes[0], $query);
fclose($pipes[0]);

// Read stdout and close it
$json = stream_get_contents($pipes[1]);
fclose($pipes[1]);

$return_value = proc_close($process);

$data = json_decode($json, true);
// access $data as an array here, do whatever you like with it
foreach ($data['data']['hosts'] ?? [] as $host) {
    echo "* " . $host['name'] . ' (' . $host['publicIp'] . ')' . PHP_EOL;
}