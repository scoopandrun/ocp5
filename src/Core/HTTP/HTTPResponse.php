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
            exit;
        }
    }

    /**
     * Debug HTTP response.
     * 
     * @return never
     */
    public function debug()
    {
        echo "<pre>";
        print_r([
            "code" => $this->code,
            "headers" => $this->headers,
            "body" => $this->body,
            "compression" => $this->compression,
            "type" => $this->type,
            "exit" => $this->exit
        ]);
        echo "</pre>";

        exit;
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
                // TODO: Implement Brotli
                // https://github.com/kjdev/php-ext-brotli
                // https://blog.anthony-jacob.com/compiler-le-module-brotli-apache-et-lextension-brotli-php-pour-ubuntu-18-04/

                // $this->body = brotli_compress($this->body);
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
        // header("Date: " . gmdate("D, d M Y H:i:s T")); // GMT time, deactivated because added by default by the web server
        header("Cache-control: no-cache");

        // ! FIXME : Se conformer à la RFC 7230 https://datatracker.ietf.org/doc/html/rfc7230#section-3.3.2
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
            100 => $this->_100_Continue(),
            101 => $this->_101_SwitchingProtocols(),
            103 => $this->_103_EarlyHints(),
            200 => $this->_200_OK(),
            201 => $this->_201_Created(),
            202 => $this->_202_Accepted(),
            203 => $this->_203_NonAuthoritativeInformation(),
            204 => $this->_204_NoContent(),
            205 => $this->_205_ResetContent(),
            206 => $this->_206_PartialContent(),
            301 => $this->_301_MovedPermanently(),
            302 => $this->_302_Found(),
            303 => $this->_303_SeeOther(),
            304 => $this->_304_NotModified(),
            307 => $this->_307_TemporaryRedirect(),
            308 => $this->_308_PermanentRedirect(),
            400 => $this->_400_BadRequest(),
            401 => $this->_401_Unauthorized(),
            402 => $this->_402_PaymentRequired(),
            403 => $this->_403_Forbidden(),
            404 => $this->_404_NotFound(),
            405 => $this->_405_MethodNotAllowed(),
            406 => $this->_406_NotAcceptable(),
            407 => $this->_407_ProxyAuthenticationRequired(),
            408 => $this->_408_RequestTimeout(),
            409 => $this->_409_Conflict(),
            410 => $this->_410_Gone(),
            411 => $this->_411_LengthRequired(),
            412 => $this->_412_PreconditionFailed(),
            413 => $this->_413_PayloadTooLarge(),
            414 => $this->_414_URITooLong(),
            415 => $this->_415_UnsupportedMediaType(),
            416 => $this->_416_RangeNotSatisfiable(),
            417 => $this->_417_ExpectationFailed(),
            418 => $this->_418_Imateapot(),
            422 => $this->_422_UnprocessableEntity(),
            425 => $this->_425_TooEarly(),
            426 => $this->_426_UpgradeRequired(),
            428 => $this->_428_PreconditionRequired(),
            429 => $this->_429_TooManyRequests(),
            431 => $this->_431_RequestHeaderFieldsTooLarge(),
            451 => $this->_451_UnavailableForLegalReasons(),
            500 => $this->_500_InternalServerError(),
            501 => $this->_501_NotImplemented(),
            502 => $this->_502_BadGateway(),
            503 => $this->_503_ServiceUnavailable(),
            504 => $this->_504_GatewayTimeout(),
            505 => $this->_505_HTTPVersionNotSupported(),
            506 => $this->_506_VariantAlsoNegotiates(),
            507 => $this->_507_InsufficientStorage(),
            508 => $this->_508_LoopDetected(),
            510 => $this->_510_NotExtended(),
            511 => $this->_511_NetworkAuthenticationRequired(),
            default => $this->_500_InternalServerError()
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
    private function _100_Continue(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 100 Continue";
    }

    /**
     * Response 101 (Switching Protocols).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/101
     */
    private function _101_SwitchingProtocols(): void
    {
        if ($_SERVER["SERVER_PROTOCOL"] === "HTTP/1.1") {
            $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 101 Switching Protocols";
            // self::$response["Connection"] = "upgrade";
            // self::$response["headers"]["Upgrade"] = null; // Inclure le nouveau protocole dans ce header

        } else {
            $this->_200_OK("");
        }
    }

    /**
     * Response 103 (Early Hints).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/103
     */
    private function _103_EarlyHints(): void
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
    private function _200_OK(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 200 OK";
    }

    /**
     * Response 201 (Created).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/201
     */
    private function _201_Created(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 201 Created";
    }

    /**
     * Response 202 (Accepted).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/202
     */
    private function _202_Accepted(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 202 Accepted";
    }

    /**
     * Response 203 (Non-Authoritative Information).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/203
     */
    private function _203_NonAuthoritativeInformation(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 203 Non-Authoritative Information";
    }

    /**
     * Response 204 (No Content).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/204
     */
    private function _204_NoContent(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 204 No Content";
    }

    /**
     * Response 205 (Reset Content).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/205
     */
    private function _205_ResetContent(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 205 Reset Content";
    }

    /**
     * Response 206 (Partial Content).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/206
     */
    private function _206_PartialContent(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 206 Partial Content";
    }


    /** === 3XX - REDIRECTION MESSAGES */

    /**
     * Response 301 (Moved Permamently).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/301
     */
    private function _301_MovedPermanently(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 301 Moved Permanently";
    }

    /**
     * Response 302 (Found).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/302
     */
    private function _302_Found(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 302 Found";
    }

    /**
     * Response 303 (See Other).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/303
     */
    private function _303_SeeOther(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 303 See Other";
    }

    /**
     * Response 304 (Not Modified).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/304
     */
    private function _304_NotModified(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 304 Not Modified";
        // Headers à envoyer : Cache-Control, Content-Location, ETag, Expires, and Vary

    }

    /**
     * Response 307 (Temporary Redirect).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/307
     */
    private function _307_TemporaryRedirect(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 307 Temporary Redirect";
    }

    /**
     * Response 308 (Permanent Redirect).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/308
     */
    private function _308_PermanentRedirect(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 308 Permanent Redirect";
    }


    /** === 4XX - CLIENT ERRORS === */

    /**
     * Response 400 (Bad Request).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/400
     */
    private function _400_BadRequest(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request";
    }

    /**
     * Response 401 (Unauthorized).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/401
     */
    private function _401_Unauthorized(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 401 Unauthorized";
        // $this->headers["WWW-Authenticate"] = null; // En-tête WWW-Athenticate à renseigner par l'utilisateur

    }

    /**
     * Response 402 (Payment Required).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/402
     */
    private function _402_PaymentRequired(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 402 Payment Required";
    }

    /**
     * Response 403 (Forbidden).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/403
     */
    private function _403_Forbidden(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden";
    }

    /**
     * Response 404 (Not Found).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/404
     */
    private function _404_NotFound(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 404 Not Found";
    }

    /**
     * Response 405 (Method Not Allowed).
     * 
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/405
     */
    private function _405_MethodNotAllowed(): void
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
    private function _406_NotAcceptable(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 406 Not Acceptable";
    }

    /**
     * Response 407 (Proxy Authentication Required)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/407
     */
    private function _407_ProxyAuthenticationRequired(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 407 Proxy Authentication Required";
    }

    /**
     * Response 408 (Request Timeout)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/408
     */
    private function _408_RequestTimeout(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 408 Request Timeout";
    }

    /**
     * Response 409 (Conflict)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/409
     */
    private function _409_Conflict(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 409 Conflict";
    }

    /**
     * Response 410 (Gone)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/410
     */
    private function _410_Gone(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 410 Gone";
    }

    /**
     * Response 411 (Length Required)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/411
     */
    private function _411_LengthRequired(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 411 Length Required";
    }

    /**
     * Response 412 (Precondition Failed)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/412
     */
    private function _412_PreconditionFailed(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 412 Precondition Failed";
    }

    /**
     * Response 413 (Payload Too Large)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/413
     */
    private function _413_PayloadTooLarge(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 413 Payload Too Large";
    }

    /**
     * Response 414 (URI Too Long)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/414
     */
    private function _414_URITooLong(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 414 URI Too Long";
    }

    /**
     * Response 415 (Unsupported Media Type)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/415
     */
    private function _415_UnsupportedMediaType(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 415 Unsupported Media Type";
    }

    /**
     * Response 416 (Range Not Satisfiable)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/416
     */
    private function _416_RangeNotSatisfiable(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 416 Range Not Satisfiable";
    }

    /**
     * Response 417 (Expectation Failed)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/417
     */
    private function _417_ExpectationFailed(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 417 Expectation Failed";
    }

    /**
     * Response 418 (I'm a teapot)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/418
     */
    private function _418_Imateapot(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 418 I'm a teapot";
    }

    /**
     * Response 422 (Unprocessable Entity)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/422
     */
    private function _422_UnprocessableEntity(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 422 Unprocessable Entity";
    }

    /**
     * Response 425 (Too Early)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/425
     */
    private function _425_TooEarly(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 425 Too Early";
    }

    /**
     * Response 426 (Upgrade Required)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/426
     */
    private function _426_UpgradeRequired(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 426 Upgrade Required";
    }

    /**
     * Response 428 (Precondition Required)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/428
     */
    private function _428_PreconditionRequired(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 428 Precondition Required";
    }

    /**
     * Response 429 (Too Many Requests)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/429
     */
    private function _429_TooManyRequests(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 429 Too Many Requests";
    }

    /**
     * Response 431 (Request Header Fields Too Large)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/431
     */
    private function _431_RequestHeaderFieldsTooLarge(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 431 Request Header Fields Too Large";
    }

    /**
     * Response 451 (Unavailable For Legal Reasons)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/451
     */
    private function _451_UnavailableForLegalReasons(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 451 Unavailable For Legal Reasons";
    }


    /** === 5XX - SERVER ERRORS === */

    /**
     * Response 500 (Internal Server Error)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/500
     */
    private function _500_InternalServerError(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error";
    }

    /**
     * Response 501 (Not Implemented)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/501
     */
    private function _501_NotImplemented(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 501 Not Implemented";
    }

    /**
     * Response 502 (Bad Gateway)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/502
     */
    private function _502_BadGateway(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 502 Bad Gateway";
    }

    /**
     * Response 503 (Service Unavailable)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/503
     */
    private function _503_ServiceUnavailable(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 503 Service Unavailable";
    }

    /**
     * Response 504 (Gateway Timeout)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/504
     */
    private function _504_GatewayTimeout(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 504 Gateway Timeout";
    }

    /**
     * Response 505 (HTTP Version Not Supported)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/505
     */
    private function _505_HTTPVersionNotSupported(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 505 HTTP Version Not Supported";
    }

    /**
     * Response 506 (Variant Also Negotiates)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/506
     */
    private function _506_VariantAlsoNegotiates(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 506 Variant Also Negotiates";
    }

    /**
     * Response 507 (Insufficient Storage)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/507
     */
    private function _507_InsufficientStorage(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 507 Insufficient Storage";
    }

    /**
     * Response 508 (Loop Detected)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/508
     */
    private function _508_LoopDetected(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 508 Loop Detected";
    }

    /**
     * Response 510 (Not Extended)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/510
     */
    private function _510_NotExtended(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 510 Not Extended";
    }

    /**
     * Response 511 (Network Authentication Required)
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/511
     */
    private function _511_NetworkAuthenticationRequired(): void
    {
        $this->headers[] = $_SERVER["SERVER_PROTOCOL"] . " 511 Network Authentication Required";
    }
}
