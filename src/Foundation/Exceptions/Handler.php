<?php

namespace AurexEngine\Foundation\Exceptions;

use AurexEngine\Http\Request;
use AurexEngine\Http\Response;
use AurexEngine\Support\ConfigRepository;
use AurexEngine\Support\Logger;
use Throwable;

class Handler
{
  public function __construct(protected ConfigRepository $config, protected Logger $logger)
  {
  }

  public function render(Request $request, Throwable $e): Response
  {
    $this->logger->error($e->getMessage(), [
      'exception' => get_class($e),
      'file' => $e->getFile(),
      'line' => $e->getLine(),
    ]);

    $debug = (bool) $this->config->get('app.debug', false);

    if ($debug) {
      return new Response($this->renderDebug($e), 500, [
        'content-type' => 'text/html; charset=UTF-8'
      ]);
    }

    return new Response('500 Server Error', 500);
  }

  protected function renderDebug(Throwable $e): string
  {
    $title = htmlspecialchars(get_class($e) . ': ' . $e->getMessage(), ENT_QUOTES, 'UTF-8');
    $file = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
    $line = (int) $e->getLine();
    $trace = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{$title}</title>
  <style>
    body{font-family:system-ui,Arial;margin:24px;line-height:1.4}
    .box{padding:16px;border:1px solid #ddd;border-radius:10px}
    pre{white-space:pre-wrap;background:#f7f7f7;padding:12px;border-radius:10px;overflow:auto}
    .muted{color:#666}
  </style>
</head>
<body>
  <h1>{$title}</h1>
  <p class="muted">{$file}:{$line}</p>
  <div class="box">
    <h3>Stack trace</h3>
    <pre>{$trace}</pre>
  </div>
</body>
</html>
HTML;
  }
}