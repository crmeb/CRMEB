<?php
// This file was auto-generated from sdk-root/src/data/ds/2015-04-16/waiters-2.json
return [ 'version' => 2, 'waiters' => [ 'HybridADUpdated' => [ 'operation' => 'DescribeHybridADUpdate', 'delay' => 120, 'maxAttempts' => 60, 'acceptors' => [ [ 'state' => 'success', 'matcher' => 'pathAll', 'argument' => 'UpdateActivities.SelfManagedInstances[].Status', 'expected' => 'Updated', ], [ 'state' => 'failure', 'matcher' => 'pathAny', 'argument' => 'UpdateActivities.SelfManagedInstances[].Status', 'expected' => 'UpdateFailed', ], ], ], ],];
