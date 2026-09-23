$ErrorActionPreference = 'Stop'
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
& node (Join-Path $scriptDir 'scripts/project-command.mjs') @args
exit $LASTEXITCODE

