<?php
namespace Fontibus\Url;

use League\Uri\Contracts\UriException;
use League\Uri\UriTemplate;

class Url {

    private static string $method;
    private static string $scheme;
    private static string $host;

    public static function init(): void {
        self::$method = $_SERVER['REQUEST_METHOD'];
        self::$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        self::$host = $_SERVER['HTTP_HOST'];
    }

    /**
     * Get the method used to request the page
     *
     * @return string
     */
    public static function getMethod(): string {
        return self::$method;
    }

    /**
     * Get the scheme
     *
     * @return string
     */
    public static function getScheme(): string {
        return self::$scheme;
    }

    /**
     * Get the current host
     *
     * @return string
     */
    public static function getHost(): string {
        return self::$host;
    }

    /**
     * Get the base url
     *
     * @return string
     */
    public static function getBaseUrl(): string {
        return env('BASE_URL', self::$scheme.'://'.self::$host);
    }

    /**
     * Get the requested url
     *
     * @return string
     */
    public static function getRequest(): string {
        return str_replace(self::getBaseUrl(), '', self::$scheme.'://'.self::$host.$_SERVER['REQUEST_URI']);
    }

    /**
     * Build a url
     *
     * @param string $path
     * @param array $variables
     * @return string
     */
    public static function route(string $path, array $variables = []): string {
        if(filter_var($path, FILTER_VALIDATE_URL))
            return $path;

        if(empty($path) || strpos($path, "/") !== 0)
            $path = '/'.$path;

        $template = new UriTemplate(self::getBaseUrl().$path);
        try {
            return $template->expand($variables);
        } catch (UriException $e) {
            die($e->getMessage());
        }
    }

}