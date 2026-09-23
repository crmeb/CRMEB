<?php
// This file was auto-generated from sdk-root/src/data/simpledbv2/2025-09-26/waiters-2.json
return [ 'version' => 2, 'waiters' => [ 'ExportSucceeded' => [ 'delay' => 30, 'maxAttempts' => 5, 'operation' => 'GetExport', 'acceptors' => [ [ 'matcher' => 'path', 'argument' => 'exportStatus', 'state' => 'success', 'expected' => 'SUCCEEDED', ], [ 'matcher' => 'path', 'argument' => 'exportStatus', 'state' => 'failure', 'expected' => 'FAILED', ], ], ], ],];
