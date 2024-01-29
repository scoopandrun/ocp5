<?php

namespace App\Core\HTTP;

/**
 * HTTP response.
 * 
 * Builds an HTTP response with body compression.
 */
class HTTPResponse
{
    private int $code = 200;
    private array $headers = [];
    private ?string $body = null;
    private bool $compression = true;
    private string $type = 'text/html; charset=UTF-8';
    private bool $exit = true;
    private bool $isSent = false;

    public function __construct(?int $code = null)
    {
        if ($code) {
            $this->setCode($code);
        }
    }

    /**
     * Send the HTTP response.
     */
    public function send(): void
    {
        if ($this->isSent === true) {
            return;
        }

        if ($this->compression) {
            $this->compressResponse();
        }

        $this->applyStatusCode();
        $this->applyHeaders();

        // Response body
        if ($this->body && $_SERVER["REQUEST_METHOD"] !== "HEAD") {
            echo $this->body;
        }

        $this->isSent = true;

        // Script exit
        if ($this->exit) {
            if ($this->code >= 400) {
                $exitCode = $this->code;
            } else {
                $exitCode = 0;
            }

            exit($exitCode);
        }
    }

    /**
     * Set HTTP reponse status code.  
     * 
     * The default code is `200`.
     * 
     * @param int $code HTTP status code.
     * 
     * @return HTTPResponse
     */
    public function setCode(int $code): HTTPResponse
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Set HTTP reponse headers.
     * 
     * @param array $headers Array of HTTP headers `[name => value]`.
     * 
     * @return HTTPResponse
     */
    public function setHeaders(array $headers): HTTPResponse
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Add a header to the HTTP response.
     * 
     * @param string $name  Name of the header.
     * @param string $value Value of the header.
     * 
     * @return HTTPResponse
     */
    public function addHeader(string $name, string $value): HTTPResponse
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Set the HTTP response body MIME type.
     * 
     * By default, the type is set to `text/html; charset=UTF-8`.
     * 
     * @param string $type MIME type of the body.
     * 
     * @return HTTPResponse
     */
    public function setType(string $type): HTTPResponse
    {
        $type = match ($type) {
            'text', 'plain' => 'text/plain',
            'html' => 'text/html; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'yaml' => 'application/x-yaml',
            default => $type
        };

        $this->type = $type;

        return $this;
    }

    /**
     * Set the body of the HTTP response.
     * 
     * @param mixed $body Body of the HTTP response.
     * 
     * @return HTTPResponse
     */
    public function setBody(mixed $body): HTTPResponse
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Set the HTML response type and body.
     * 
     * This method is a shortcut to:  
     * ```php
     * $response->setType("html")->setBody($html)
     * ```
     * 
     * @param string $html HTML body of the response.
     * 
     * @return HTTPResponse 
     */
    public function setHTML(string $html): HTTPResponse
    {
        $this->setType("html");
        $this->setBody($html);

        return $this;
    }

    /**
     * Set the JSON response type and body.
     * 
     * This method is a shortcut to:  
     * ```php
     * $response->setType("text")->setBody($json)
     * ```
     * 
     * @param string $json JSON body of the response.
     * 
     * @return HTTPResponse 
     */
    public function setJSON(string $json): HTTPResponse
    {
        $this->setType("json");
        $this->setBody($json);

        return $this;
    }

    /**
     * Set the plain text response type and body.
     * 
     * This method is a shortcut to:  
     * ```php
     * $response->setType("text")->setBody($text)
     * ```
     * 
     * @param string $text Text body of the response.
     * 
     * @return HTTPResponse 
     */
    public function setText(string $text): HTTPResponse
    {
        $this->setType("text");
        $this->setBody($text);

        return $this;
    }

    /**
     * Send the HTML response.
     * 
     * This method is a shortcut to:  
     * ```php
     * $response->setType("html")->setBody($html)->send()
     * ```
     * 
     * @param string $html HTML body of the response.
     */
    public function sendHTML(string $html): void
    {
        $this->setType("html");
        $this->setBody($html);
        $this->send();
    }

    /**
     * Send the JSON response.
     * 
     * The body must already be JSON-encoded.
     * 
     * This method is a shortcut to:  
     * ```php
     * $response->setType("text")->setBody($json)->send()
     * ```
     * 
     * @param string $json JSON body of the response.
     */
    public function sendJSON(string $json): void
    {
        $this->setType("json");
        $this->setBody($json);
        $this->send();
    }

    /**
     * Send the plain text response.
     * 
     * This method is a shortcut to:  
     * ```php
     * $response->setType("text")->setBody($text)->send()
     * ```
     * 
     * @param string $text Text body of the response.
     */
    public function sendText(string $text): void
    {
        $this->setType("text");
        $this->setBody($text);
        $this->send();
    }

    /**
     * Redirect the user to the target URI.
     * 
     * The script exits after the redirect.
     * 
     * @param string $targetURI Target of the redirection.
     * @param int    $code      Optional. HTTP status code. Default = 302.
     */
    public function redirect(string $targetURI, int $code = 302): void
    {
        $this
            ->setCode($code)
            ->addHeader("Location", $targetURI)
            ->setBody(null)
            ->setCompression(false)
            ->setExit(true)
            ->send();
    }

    /**
     * Activate or deactivate compression for the HTTP response.  
     * 
     * By default the compression is set to `TRUE`.
     * 
     * @param bool $compression TRUE (activate) or FALSE (deactivate).
     * 
     * @return HTTPResponse
     */
    public function setCompression(bool $compression = true): HTTPResponse
    {
        $this->compression = $compression;

        return $this;
    }

    /**
     * Set if the script must exit after sending the HTTP reponse.
     * 
     * Default is `TRUE`.
     * 
     * @param bool $exit 
     * 
     * @return HTTPResponse
     */
    public function setExit(bool $exit = true): HTTPResponse
    {
        $this->exit = $exit;

        return $this;
    }


    /**
     * Compress the HTTP response body.
     * 
     * Check the accepted compression methods.  
     * Compress according to the accepted methods.
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Encoding
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Encoding
     */
    private function compressResponse(): void
    {
        // If the body is empty, skip compression
        if (!$this->body) {
            return;
        }

        /**
         * Compression methods accepted by the client.
         * @var string|null
         */
        $clientAcceptEncoding = $_SERVER["HTTP_ACCEPT_ENCODING"] ?? null;

        // If no compression method is accepted, skip compression
        if ($clientAcceptEncoding === null) {
            return;
        }

        /**
         * Array of compression methods accepted by the client, ordered by priority.
         * @var string[]
         */
        $clientAcceptedMethods = explode(",", $clientAcceptEncoding);

        /**
         * Compression priorities array.
         * 
         * ```php
         * [(string) $method => (float) $priority]
         * ```
         * @var float[]
         */
        $clientCompressionPriority = [];

        foreach ($clientAcceptedMethods as $method) {
            $methodArray = explode(";q=", $method);
            $methodName = trim($methodArray[0]);
            $methodPriority = (float) ($methodArray[1] ?? 1);

            $clientCompressionPriority[$methodName] = $methodPriority;
        }

        // Sort the array by descending priority
        arsort($clientCompressionPriority);


        /**
         * Compression methods supported by the server.
         * @var bool[]
         */
        $serverSupportedMethods = [
            "gzip" => true,
            "deflate" => true,
            "compress" => false,
            "br" => false, // See below for implementation
            "identity" => true,
        ];

        /**
         * Méthode de compression utilisée ("identity" par défaut, modifié ci-dessous).
         * @var string
         */
        $compressionMethod = "identity";

        // Enregistrement de la première méthode acceptée par le client
        // et supportée par le serveur
        foreach ($clientCompressionPriority as $method => $priority) {
            $isSupported = $serverSupportedMethods[$method] ?? false;

            if ($isSupported && $priority != 0) {
                $compressionMethod = $method;
                break;
            }
        }

        // HTTP header
        $this->headers["Content-Encoding"] = $compressionMethod;

        // Compression methods
        switch ($compressionMethod) {
            case 'gzip':
                // GZIP (== PHP gzencode)
                $this->body = gzencode($this->body);
                break;

            case 'deflate':
                // HTTP DEFLATE (== PHP gzcompress)
                $this->body = gzcompress($this->body, 9);
                break;

            case 'br':
                // Brotli
                // Not implemented (requires compilation of the module for PHP and Apache)
                break;

            case 'identity':
            default:
                // Identity (no compression)
                // No change to the response body
                break;
        }
    }


    /**
     * Set up the HTTP response headers.
     * 
     * First, default headers.  
     * Then additional headers set by the script.
     */
    private function applyHeaders(): void
    {
        // Default headers
        header("Cache-control: no-cache");

        if (!($this->code < 200 || $this->code === 204)) {
            header("Content-Length: " . strlen($this->body ?? ""));
        }

        // "Content-Type" header
        if ($this->body) {
            header("Content-Type: {$this->type}");
        }

        // Additional headers
        foreach ($this->headers as $name => $value) {
            header($name ? "$name: $value" : $value);
        }
    }

    /**
     * Apply the appropriate header based on the status code.
     */
    private function applyStatusCode(): void
    {
        match ($this->code) {
            100 => $this->set100Continue(),
            101 => $this->set101SwitchingProtocols(),
            103 => $this->set103EarlyHints(),
            200 => $this->set200OK(),
            201 => $this->set201Created(),
            202 => $this->set202Accepted(),
            203 => $this->set203NonAuthoritativeInformation(),
            204 => $this->set204NoContent(),
            205 => $this->set205ResetContent(),
            206 => $this->set206PartialContent(),
            301 => $this->set301MovedPermanently(),
            302 => $this->set302Found(),
            303 => $this->set303SeeOther(),
            304 => $this->set304NotModified(),
            307 => $this->set307TemporaryRedirect(),
            308 => $this->set308PermanentRedirect(),
            400 => $this->set400BadRequest(),
            401 => $this->set401Unauthorized(),
            402 => $this->set402PaymentRequired(),
            403 => $this->set403Forbidden(),
            404 => $this->set404NotFound(),
            405 => $this->set405MethodNotAllowed(),
            406 => $this->set406NotAcceptable(),
            407 => $this->set407ProxyAuthenticationRequired(),
            408 => $this->set408RequestTimeout(),
            409 => $this->set409Conflict(),
            410 => $this->set410Gone(),
            411 => $this->set411LengthRequired(),
            412 => $this->set412PreconditionFailed(),
            413 => $this->set413PayloadTooLarge(),
            414 => $this->set414URITooLong(),
            415 => $this->set415UnsupportedMediaType(),
            416 => $this->set416RangeNotSatisfiable(),
            417 => $this->set417ExpectationFailed(),
            418 => $this->set418Imateapot(),
            422 => $this->set422UnprocessableEntity(),
            425 => $this->set425TooEarly(),
            426 => $this->set426UpgradeRequired(),
            428 => $this->set428PreconditionRequired(),
            429 => $this->set429TooManyRequests(),
            431 => $this->set431RequestHeaderFieldsTooLarge(),
            451 => $this->set451UnavailableForLegalReasons(),
            500 => $this->set500InternalServerError(),
            501 => $this->set501NotImplemented(),
            502 => $this->set502BadGateway(),
            503 => $this->set503ServiceUnavailable(),
            504 => $this->set504GatewayTimeout(),
            505 => $this->set505HTTPVersionNotSupported(),
            506 => $this->set506VariantAlsoNegotiates(),
            507 => $this->set507InsufficientStorage(),
            508 => $this->set508LoopDetected(),
            510 => $this->set510NotExtended(),
            511 => $this->set511NetworkAuthenticationRequired(),
            default => $this->set500InternalServerError()
        };
    }


    /* ============================= */
    /* == CODE SPECIFIC FUNCTIONS == */
    /* ============================= */

    /** === 1XX - INFORMATION === */

    /**
     * Response 100 (Continue).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/100
     */
    private function set100Continue(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 100 Continue";
    }

    /**
     * Response 101 (Switching Protocols).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/101
     */
    private function set101SwitchingProtocols(): void
    {
        if ($_SERVER["SERVER_PROTOCOL"] === "HTTP/1.1") {
            $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 101 Switching Protocols";
            // self::$response["Connection"] = "upgrade";
            // self::$response["headers"]["Upgrade"] = null; // Inclure le nouveau protocole dans ce header

        } else {
            $this->set200OK("");
        }
    }

    /**
     * Response 103 (Early Hints).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/103
     */
    private function set103EarlyHints(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 103 Early Hints";
        // $this->headers["Link"] = null; // En-tête Link à compléter par l'utilisateur

    }


    /** === 2XX - SUCCESS */

    /**
     * Response 200 (OK).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/200
     */
    private function set200OK(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 200 OK";
    }

    /**
     * Response 201 (Created).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/201
     */
    private function set201Created(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 201 Created";
    }

    /**
     * Response 202 (Accepted).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/202
     */
    private function set202Accepted(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 202 Accepted";
    }

    /**
     * Response 203 (Non-Authoritative Information).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/203
     */
    private function set203NonAuthoritativeInformation(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 203 Non-Authoritative Information";
    }

    /**
     * Response 204 (No Content).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/204
     */
    private function set204NoContent(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 204 No Content";
    }

    /**
     * Response 205 (Reset Content).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/205
     */
    private function set205ResetContent(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 205 Reset Content";
    }

    /**
     * Response 206 (Partial Content).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/206
     */
    private function set206PartialContent(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 206 Partial Content";
    }


    /** === 3XX - REDIRECTION MESSAGES */

    /**
     * Response 301 (Moved Permamently).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/301
     */
    private function set301MovedPermanently(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 301 Moved Permanently";
    }

    /**
     * Response 302 (Found).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/302
     */
    private function set302Found(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 302 Found";
    }

    /**
     * Response 303 (See Other).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/303
     */
    private function set303SeeOther(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 303 See Other";
    }

    /**
     * Response 304 (Not Modified).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/304
     */
    private function set304NotModified(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 304 Not Modified";
        // Headers à envoyer : Cache-Control, Content-Location, ETag, Expires, and Vary

    }

    /**
     * Response 307 (Temporary Redirect).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/307
     */
    private function set307TemporaryRedirect(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 307 Temporary Redirect";
    }

    /**
     * Response 308 (Permanent Redirect).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/308
     */
    private function set308PermanentRedirect(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 308 Permanent Redirect";
    }


    /** === 4XX - CLIENT ERRORS === */

    /**
     * Response 400 (Bad Request).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/400
     */
    private function set400BadRequest(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request";
    }

    /**
     * Response 401 (Unauthorized).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/401
     */
    private function set401Unauthorized(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 401 Unauthorized";
        // $this->headers["WWW-Authenticate"] = null; // En-tête WWW-Athenticate à renseigner par l'utilisateur

    }

    /**
     * Response 402 (Payment Required).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/402
     */
    private function set402PaymentRequired(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 402 Payment Required";
    }

    /**
     * Response 403 (Forbidden).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/403
     */
    private function set403Forbidden(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden";
    }

    /**
     * Response 404 (Not Found).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/404
     */
    private function set404NotFound(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 404 Not Found";
    }

    /**
     * Response 405 (Method Not Allowed).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/405
     */
    private function set405MethodNotAllowed(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 405 Method Not Allowed";
    }

    /**
     * Response 406 (Not Acceptable).
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/406
     *  
     * @return array Contenu de la réponse HTTP
     */
    private function set406NotAcceptable(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 406 Not Acceptable";
    }

    /**
     * Response 407 (Proxy Authentication Required)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/407
     */
    private function set407ProxyAuthenticationRequired(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 407 Proxy Authentication Required";
    }

    /**
     * Response 408 (Request Timeout)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/408
     */
    private function set408RequestTimeout(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 408 Request Timeout";
    }

    /**
     * Response 409 (Conflict)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/409
     */
    private function set409Conflict(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 409 Conflict";
    }

    /**
     * Response 410 (Gone)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/410
     */
    private function set410Gone(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 410 Gone";
    }

    /**
     * Response 411 (Length Required)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/411
     */
    private function set411LengthRequired(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 411 Length Required";
    }

    /**
     * Response 412 (Precondition Failed)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/412
     */
    private function set412PreconditionFailed(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 412 Precondition Failed";
    }

    /**
     * Response 413 (Payload Too Large)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/413
     */
    private function set413PayloadTooLarge(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 413 Payload Too Large";
    }

    /**
     * Response 414 (URI Too Long)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/414
     */
    private function set414URITooLong(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 414 URI Too Long";
    }

    /**
     * Response 415 (Unsupported Media Type)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/415
     */
    private function set415UnsupportedMediaType(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 415 Unsupported Media Type";
    }

    /**
     * Response 416 (Range Not Satisfiable)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/416
     */
    private function set416RangeNotSatisfiable(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 416 Range Not Satisfiable";
    }

    /**
     * Response 417 (Expectation Failed)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/417
     */
    private function set417ExpectationFailed(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 417 Expectation Failed";
    }

    /**
     * Response 418 (I'm a teapot)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/418
     */
    private function set418Imateapot(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 418 I'm a teapot";
    }

    /**
     * Response 422 (Unprocessable Entity)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/422
     */
    private function set422UnprocessableEntity(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 422 Unprocessable Entity";
    }

    /**
     * Response 425 (Too Early)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/425
     */
    private function set425TooEarly(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 425 Too Early";
    }

    /**
     * Response 426 (Upgrade Required)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/426
     */
    private function set426UpgradeRequired(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 426 Upgrade Required";
    }

    /**
     * Response 428 (Precondition Required)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/428
     */
    private function set428PreconditionRequired(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 428 Precondition Required";
    }

    /**
     * Response 429 (Too Many Requests)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/429
     */
    private function set429TooManyRequests(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 429 Too Many Requests";
    }

    /**
     * Response 431 (Request Header Fields Too Large)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/431
     */
    private function set431RequestHeaderFieldsTooLarge(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 431 Request Header Fields Too Large";
    }

    /**
     * Response 451 (Unavailable For Legal Reasons)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/451
     */
    private function set451UnavailableForLegalReasons(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 451 Unavailable For Legal Reasons";
    }


    /** === 5XX - SERVER ERRORS === */

    /**
     * Response 500 (Internal Server Error)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/500
     */
    private function set500InternalServerError(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error";
    }

    /**
     * Response 501 (Not Implemented)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/501
     */
    private function set501NotImplemented(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 501 Not Implemented";
    }

    /**
     * Response 502 (Bad Gateway)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/502
     */
    private function set502BadGateway(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 502 Bad Gateway";
    }

    /**
     * Response 503 (Service Unavailable)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/503
     */
    private function set503ServiceUnavailable(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 503 Service Unavailable";
    }

    /**
     * Response 504 (Gateway Timeout)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/504
     */
    private function set504GatewayTimeout(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 504 Gateway Timeout";
    }

    /**
     * Response 505 (HTTP Version Not Supported)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/505
     */
    private function set505HTTPVersionNotSupported(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 505 HTTP Version Not Supported";
    }

    /**
     * Response 506 (Variant Also Negotiates)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/506
     */
    private function set506VariantAlsoNegotiates(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 506 Variant Also Negotiates";
    }

    /**
     * Response 507 (Insufficient Storage)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/507
     */
    private function set507InsufficientStorage(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 507 Insufficient Storage";
    }

    /**
     * Response 508 (Loop Detected)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/508
     */
    private function set508LoopDetected(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 508 Loop Detected";
    }

    /**
     * Response 510 (Not Extended)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/510
     */
    private function set510NotExtended(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 510 Not Extended";
    }

    /**
     * Response 511 (Network Authentication Required)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/511
     */
    private function set511NetworkAuthenticationRequired(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 511 Network Authentication Required";
    }
}
