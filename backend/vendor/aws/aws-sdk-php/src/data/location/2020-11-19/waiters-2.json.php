<?php
// This file was auto-generated from sdk-root/src/data/location/2020-11-19/waiters-2.json
return [ 'version' => 2, 'waiters' => [ 'JobCompleted' => [ 'delay' => 60, 'maxAttempts' => 5, 'operation' => 'GetJob', 'acceptors' => [ [ 'matcher' => 'path', 'argument' => 'Status', 'state' => 'success', 'expected' => 'Completed', ], [ 'matcher' => 'path', 'argument' => 'Status', 'state' => 'failure', 'expected' => 'Failed', ], [ 'matcher' => 'path', 'argument' => 'Status', 'state' => 'failure', 'expected' => 'Cancelled', ], ], ], ],];
