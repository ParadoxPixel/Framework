<?php
namespace Fontibus\Route;

use Exception;
use Fontibus\Collection\Collection;
use Fontibus\String\Str;

class Route {

    private string $method;
    private string $path;
    private $action;
    private string $name = '';
    private array $settings = [];
    private int $min;
    private array $values = [];

    private array $split = [];
    private array $parameter_index = [];
    private array $required_parameters = [];
    private array $optional_parameters = [];

    public function __construct(string $method, string $path, $action) {
        $this->method = $method;
        $this->path = $path;
        $this->formatPath($path);
        $this->action = $action;
    }

    /**
     * Set name of routh
     * @param string $name
     * @return Route
     */
    public function name(string $name): Route {
        $this->name = $name;
        return $this;
    }

    /**
     * Get request method POST/GET
     * @return string
     */
    public function getMethod(): string {
        return $this->method;
    }

    /**
     * Get path
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }

    /**
     * Get name
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Get parameters
     * @return array
     */
    public function getParameters(): array {
        return $this->values;
    }

    /**
     * Set settings for route parameters
     * @param array $settings
     * @return Route
     */
    public function where(array $settings): Route {
        $this->settings = $settings;
        return $this;
    }

    /**
     * Parse path
     * @param string $path
     */
    private function formatPath(string $path): void {
        $array = explode('/', $path);
        $array = array_filter($array);
        $size = count($array) + 1;

        $min = 0;
        for($i = 1; $i < $size; $i++) {
            preg_match('/{(.*?)}/', $array[$i], $match);
            if(empty($match)) {
                $min++;
                continue;
            }

            $this->parameter_index[$i] = Str::endsWith($match[1], '?') ? false : true;
            if(Str::endsWith($match[1], '?')) {
                $this->optional_parameters[$i] = str_replace('?', '', $match[1]);
            } else {
                $this->required_parameters[$i] = $match[1];
                $min++;
            }
        }

        $this->min = $min;
        $this->split = $array;
    }

    /**
     * Check if path matches request
     * @param string $request
     * @return bool
     */
    public function matchRequest(string $request): bool {
        $path = explode('/', $request);
        $path = array_filter($path);
        $size = count($path) + 1;
        if($size <= $this->min)
            return false;

        $split = $this->split;
        $indexes = $this->parameter_index;

        $values = [];
        for($i = 1; $i < $size; $i++) {
            if($split[$i] === $path[$i])
                continue;

            if(!array_key_exists($i, $indexes))
                return false;

            $optional = !$indexes[$i];
            $parameter = null;
            if($optional) {
                $parameter = $this->optional_parameters[$i];
            } else {
                $parameter = $this->required_parameters[$i];
            }

            if(empty($parameter))
                return false;

            $regex = '/^(.*)$/';
            if(array_key_exists($parameter, $this->settings))
                $regex = '/^('.$this->settings[$parameter].')$/';

            if(!preg_match($regex, $path[$i]))
                return false;

            $values[$parameter] = $path[$i];
        }

        $this->values = $values;
        return true;
    }

    /**
     * Execute Route
     * @return mixed
     * @throws Exception
     */
    public function performAction() {
        $action = $this->action;
        $values = $this->values;

        $params = [];
        if($this->method === 'POST')
            $this->parsePost($params);

        foreach($values as $key => $value)
            array_push($params, $value);

        if(is_callable($action)) {
            return call_user_func_array($action, $params);
        } else {
            $args = explode('@', $action);
            if(count($args) < 2)
                throw new Exception('Invalid route action', 500);

            $class = '\App\Controllers'.$args[0];
            if(!file_exists(root_path().$class.'.php'))
                throw new Exception('Controller not found: '.$class, 500);

            $class = new $class();
            if(!is_subclass_of($class, '\Fontibus\Facades\Controller'))
                throw new Exception('Invalid route action', 500);

            $result = $class->checkMiddleware($this);
            if(is_bool($result) && $result === true)
                return call_user_func_array([$class, $args[1]], $params);

            throw new Exception('Something went wrong', 500);
        }
    }

    private function parsePost(&$params) {
        if(empty($_POST))
            return;

        $array = [];
        foreach($_POST as $key => $value)
            $array[$key] = trim(htmlspecialchars($value));

        $collection = new Collection($array);
        array_push($params, $collection);
    }

}