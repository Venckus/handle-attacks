# handle-attacks

Security solution for Botnet attacks implementation using Laravel.

## Conditions:

* '>' 15 requests per second per domain for more than 10s by nginx log - set mode to "Attack mode"
* '>' 20 requests per second per domain >6min by nginx log after enabling "Attack mode” - sets rate limiting to "on"
* '<' 8 requests per second per domain for more than 20s by nginx log - sets rate limiting to "off” if current rate limiting is set to “on".

## Solution description

Command for CRON running in short periods of time (for example each 5 minutes). Command is reading access log files from the end and scanning requests of the last time period (for example 5 minutes), count requests per second and according to count different 'modes' are set assigned.

### Class structure

`LogFileReader` is responsible for reading access.log files line by line from the end. Each `DomainEntity` is put into `DomainCollection->domainList` (array). `DomainEntity->timestamps` (array) contains `DomainModesNCounts` with attributes `requestCount`, `attackMode`, `limiterMode`.
Example structure tree `LogFileReader->DomainCollection->domainList[$domainName=>DomainEntity]->timestamps[$timestamp=>DomainModesNCounts]`.

### Abstract logic

* `LogFileReader` read log files and count requests for each domain.
* Then calculate modes by passing requests count results through mode handlers.
* and update calculated modes to DB after reading file.
Modes calculation is performed after check on how many time intervals (seconds) of log file are processed.

## command for cronjob
use command 'php artisan check:attacks path_to_file' or `file_name` if access.log is in `storage/logs/` for cronjob.

# TODO
* finish with saving attack modes to DB in handlers.