<?php
namespace Fontibus\View;

use Exception;

class View {

    private string $name;
    private string $cache_name;
    private array $parameters;

    public function __construct(string $name, array $parameters) {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    /**
     * Render requested view
     * @throws Exception
     */
    public function render(): void {
        if(!file_exists(root_path().DIRECTORY_SEPARATOR.'resources/views/'.$this->name.'.blade.php'))
            throw new Exception('View not found', 404);

        $cache_name = self::getCacheName($this->name);
        $parameters = $this->parameters;
        if(!file_exists(storage_path().DIRECTORY_SEPARATOR.'views/'.$cache_name.'.php'))
            $this->parse($this->name, true);

        foreach ($parameters as $key => $value)
            ${''.$key} = $value;

        $path = is_bool(env('MINIFIED', 'FALSE')) ? 'minified' : 'normal';
        include_once storage_path().DIRECTORY_SEPARATOR.'views/'.$path.'/'.$cache_name;
    }

    /**
     * Parse view/layout
     * @param string $name
     * @param bool $b
     * @throws Exception
     */
    private static function parse(string $name, bool $b = false): void {
        $content = file_get_contents(root_path().DIRECTORY_SEPARATOR.'resources/views/'.str_replace('.', DIRECTORY_SEPARATOR, $name).'.blade.php');
        preg_match('/^@extends\(\'([._a-zA-Z0-9]+)\'\)(?s)(.*)$/', $content, $matches);
        $matches = array_filter($matches);
        if(!empty($matches)) {
            $layout = $matches[1];
            $content = $matches[2];

            preg_match_all('/@section\(\'([._a-zA-Z0-9]+)\'\)(?s)(.+?)@endsection/', $content, $matches, PREG_SET_ORDER);
            $matches = array_filter($matches);
            $matches = array_unique($matches, SORT_REGULAR);

            $sections = [];
            foreach ($matches as $match)
                $sections[$match[1]] = trim($match[2]);

            preg_match_all('/@yield\(\'(.*)\'([ ]*),([ ]*)(.*)\)/', $content, $matches, PREG_SET_ORDER);
            $matches = array_filter($matches);
            $matches = array_unique($matches, SORT_REGULAR);

            $yields = [];
            foreach ($matches as $match)
                $yields[$match[1]] = trim($match[count($match) - 1]);

            $path = str_replace('.', DIRECTORY_SEPARATOR, $layout);
            if(!file_exists(root_path().DIRECTORY_SEPARATOR.'resources/views/'.$path.'.blade.php'))
                throw new Exception('Layout: '.$layout.' not found', 404);

            self::parse($layout);
            $path = is_bool(env('MINIFIED', 'FALSE')) ? 'minified' : 'normal';
            $path = storage_path().DIRECTORY_SEPARATOR.'views/'.$path.'/'.self::getCacheName($layout);

            $content = file_get_contents($path);
            foreach($sections as $key => $value)
                $content = str_replace('@section(\''.$key.'\')', $value, $content);

            foreach($yields as $key => $value)
                $content = str_replace('@yield(\''.$key.'\')', '<?php echo '.$value.'; ?>', $content);
        }

        $content = str_replace('{{ ', '<?php echo ', $content);
        $content = str_replace(' }}', '; ?>', $content);
        $content = preg_replace('/@(if|foreach)\((.*)\)/', '<?php $1($2) { ?>', $content);
        $content = preg_replace('/@end(if|foreach)/', '<?php } ?>', $content);
        $content = preg_replace('/@(csrf|CSRF)/', '<input type="text" name="X-CSRF" style="display: none" value="<?php echo session()->get(\'X-CSRF\'); ?>">', $content);

        preg_match_all('/@(.*)\((.*)\)/', $content, $matches, PREG_SET_ORDER);
        $matches = array_filter($matches);
        $matches = array_unique($matches, SORT_REGULAR);
        foreach ($matches as $match)
            if(function_exists($match[1]))
                $content = str_replace('@'.$match[1].'('.$match[2].')', '<?php echo '.$match[1].'('.$match[2].'); ?>', $content);

        if($b)
            $content = preg_replace('/@(.*)\((.*)\)/', '', $content);

        $cache_name = self::getCacheName($name);
        file_put_contents(storage_path().DIRECTORY_SEPARATOR.'views/normal/'.$cache_name, $content);
        $content = trim(preg_replace('/\s\s+/', '', $content));
        file_put_contents(storage_path().DIRECTORY_SEPARATOR.'views/minified/'.$cache_name, $content);
    }

    /**
     * Get name of cache file
     * @param string $name
     * @return string
     */
    private static function getCacheName(string $name): string {
        $args = explode('.', $name);
        $name_index = count($args) - 1;
        $cache_name = md5($args[$name_index]).'.php';
        unset($args[$name_index]);
        $path = implode(DIRECTORY_SEPARATOR, $args);
        if(!empty($path)) {
            $storage = storage_path().DIRECTORY_SEPARATOR.'views/minified/'.$path;
            if(!is_dir($storage))
                mkdir($storage);

            $storage = storage_path().DIRECTORY_SEPARATOR.'views/normal/'.$path;
            if(!is_dir($storage))
                mkdir($storage);

            $path .= DIRECTORY_SEPARATOR;
        }

        return $path.$cache_name;
    }

}