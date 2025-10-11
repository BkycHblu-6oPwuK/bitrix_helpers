<?php
/**
* @var string $message
* @var string $file
* @var int $line
* @var array $trace
* @var string $traceString
*/
?>
<style>
    body {
        background: #f8fafc;
        color: #222;
        font-family: 'Fira Code', monospace;
        margin: 0;
        padding: 0;
    }

    .error-container {
        max-width: 960px;
        margin: 3em auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, .1);
        overflow: hidden;
    }

    .error-header {
        background: #000;
        color: #fff;
        padding: 1.5em;
    }

    .error-header h1 {
        margin: 0;
        font-size: 1.6em;
    }

    .error-body {
        padding: 1.5em 2em;
    }

    .error-file {
        color: #fff;
        font-size: .9em;
        margin-top: .3em;
    }

    .error-trace {
        background: #f4f4f5;
        border-radius: 8px;
        padding: 1em;
        margin-top: 1.5em;
        font-size: 0.85em;
        overflow-x: auto;
    }

    .trace-item {
        border-bottom: 1px solid #ddd;
        padding: .6em 0;
    }

    .trace-index {
        color: #888;
        font-weight: bold;
    }

    .trace-file {
        color: #555;
    }

    .trace-func {
        color: #111;
    }
</style>

<div class="error-container">
    <div class="error-header">
        <h1><?= htmlspecialchars($message) ?></h1>
        <div class="error-file"><?= htmlspecialchars($file) ?>:<?= (int)$line ?></div>
    </div>

    <div class="error-body">
        <h2>Stack trace:</h2>
        <div class="error-trace">
            <?php if (!empty($trace)): ?>
                <?php foreach ($trace as $i => $frame): ?>
                    <div class="trace-item">
                        <div class="trace-index">#<?= $i ?></div>
                        <?php if (!empty($frame['file'])): ?>
                            <div class="trace-file"><?= htmlspecialchars($frame['file']) ?>:<?= (int)($frame['line'] ?? 0) ?></div>
                        <?php endif; ?>
                        <div class="trace-func">
                            <?= htmlspecialchars(($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? '')) ?>(
                            <?php
                                if (!empty($frame['args'])) {
                                    $args = array_map(static function($arg) {
                                        if (is_object($arg)) {
                                            return get_class($arg);
                                        } elseif (is_array($arg)) {
                                            return 'Array(' . count($arg) . ')';
                                        } elseif (is_null($arg)) {
                                            return 'null';
                                        } elseif (is_bool($arg)) {
                                            return $arg ? 'true' : 'false';
                                        } elseif (is_string($arg)) {
                                            $short = mb_strimwidth($arg, 0, 256, '…');
                                            return '"' . htmlspecialchars($short) . '"';
                                        }
                                        return htmlspecialchars((string)$arg);
                                    }, $frame['args']);
                                    echo implode(', ', $args);
                                }
                            ?>)
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <em>No trace available</em>
            <?php endif; ?>
        </div>
    </div>
</div>
