<?php
// This file was auto-generated from sdk-root/src/data/account-access/2018-05-10/waiters-2.json
return [ 'version' => 2, 'waiters' => [ 'ApplicationActive' => [ 'delay' => 5, 'maxAttempts' => 24, 'operation' => 'GetApplication', 'acceptors' => [ [ 'matcher' => 'path', 'argument' => 'status', 'state' => 'success', 'expected' => 'ACTIVE', ], [ 'matcher' => 'path', 'argument' => 'status', 'state' => 'failure', 'expected' => 'CREATE_FAILED', ], [ 'matcher' => 'path', 'argument' => 'status', 'state' => 'failure', 'expected' => 'DELETE_FAILED', ], ], ], ],];
