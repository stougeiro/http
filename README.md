![phpstan-level](https://img.shields.io/badge/PHPStan-Level%209-brightgreen)
![pest-php](https://img.shields.io/badge/Tests-%20Passed-brightgreen)

# Http

A lightweight, high‑performance HTTP implementation designed for real‑world PHP applications.
This package provides concrete Request, Response, Uri, and UploadedFile objects built around a simple, expressive, and predictable API — following an approach closer to modern express‑style frameworks while maintaining a clean, consistent, and framework‑agnostic design.

The library focuses on clarity, ergonomics, and practical HTTP handling: reading input, processing middleware, and producing output. Every external dependency (filesystem operations, input streams, server parameters) is wrapped in mockable seams, enabling deterministic behavior and effortless testing. The result is a fast, minimal, and highly reliable HTTP layer suitable for APIs, micro‑frameworks, routers, and modular architectures.

## ✨ Features

- **High‑performance, expressive HTTP API**  
  A clean, practical implementation inspired by express‑style frameworks,focused on how HTTP is actually used in real applications.

- **Predictable Request handling**  
  Reads method, URI, headers, cookies, query params, body, and uploaded files with zero magic and no unnecessary abstractions.

- **Minimal, API‑focused URI parsing**  
  Only the components that matter for routing and dispatching: path and query. No reconstruction of host, scheme, or port unless explicitly provided.

- **Mockable filesystem and input operations**  
  `is_dir()`, `rename()`, `move_uploaded_file()`, and `php://input` are wrapped in protected methods, making the entire HTTP layer fully testable.

- **Precise UploadedFile implementation**  
  Strongly typed metadata (name, type, tmp_name, error, size) and safe movement/renaming logic — fully compatible with PHPStan Level 9.

- **Clean, expressive Response builder**  
  Straightforward status, headers, and body handling designed for clarity and performance.

- **Convenient Response helpers for common patterns**  
  Built‑in methods for sending JSON, plain text, and raw output make response construction fast and ergonomic, reducing boilerplate and improving readability in express‑style flows.

- **Zero dependencies**  
  Pure PHP implementation that can be adopted by any router, micro‑framework, or custom HTTP server.

---

## 📦 Installation

Install via Composer:

```bash
composer require stougeiro/http
```

## 🚀 Usage Example

```php
use STDW\Contract\Http\MiddlewareInterface;
use STDW\Contract\Http\RequestInterface;
use STDW\Contract\Http\ResponseInterface;
use Closure;

class SimpleMiddleware implements MiddlewareInterface
{
    public function process(
      RequestInterface $request,
      ResponseInterface $response,
      Closure $next): ResponseInterface
    {
        // Perform any pre-processing here...
        return $next($request, $response);
        // Or post-processing here...
    }
}
```

```php
use STDW\Http\Handler\RequestHandler;
use STDW\Http\Middleware\MiddlewareManager;
use STDW\Http\Request;
use STDW\Http\Response;

$manager = new MiddlewareManager;
$manager->add(new SimpleMiddleware);

$request = new Request;
$response = new Response;
$handler = new RequestHandler($manager);

$result = $handler->handle($request, $response);
echo $result->send();
```

---

## 🧠 Why?

Because real‑world HTTP handling benefits from a simpler, more expressive, and performance‑oriented approach than the highly abstract, stream‑based models commonly found in formal standards. While those models provide conceptual rigor, everyday request/response workflows rarely require that level of complexity.

This library intentionally focuses on:

- **Practical request lifecycle**  
  Reading input, processing middleware, and producing output — without artificial immutability or heavy abstractions.

- **Express‑like ergonomics**  
  A straightforward API that mirrors how developers naturally think about HTTP: path, query, body, headers, and files.

- **Predictable, explicit behavior**  
  No hidden transformations, no guessing environment details, no reconstruction of full URLs unless explicitly requested.

- **Performance and simplicity**  
  Minimal overhead, zero dependencies, and a design optimized for fast routing and efficient request processing.

- **Maximum testability**  
  Every external dependency is wrapped in a mockable seam, enabling deterministic tests without filesystem hacks or global state manipulation.

---

## 🤝 Contributions

Contributions are welcome.
Feel free to open issues or submit pull requests.

<br><br>

[<img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" width="170"/>](https://www.buymeacoffee.com/stougeiro)
