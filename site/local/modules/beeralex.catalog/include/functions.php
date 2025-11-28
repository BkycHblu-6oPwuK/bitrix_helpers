<?
declare(strict_types=1);

namespace Beeralex\Catalog;

function log(string $message, int $traceDepth = 6, bool $showArgs = false): void
{
    \AddMessage2Log($message, service(Options::class)->getModuleId(), $traceDepth, $showArgs);
}