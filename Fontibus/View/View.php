<?php
namespace Fontibus\View;

use Exception;

class View {

    private string $name;
    private string $cache_name;
    private array $parameters;

    public function __construct(string $name, array $parameters) {
        $this->name = str_replace('.', '/', $name).'.blade.php';
        $this->cache_name = md5($name).'.php';
        $this->parameters = $parameters;
    }

    public function render(): void {
        if(!file_exists(root_path().DIRECTORY_SEPARATOR.'resources/views/'.$this->name))
            throw new Exception('View not found', 404);

        $parameters = $this->parameters;
        if(!file_exists(storage_path().DIRECTORY_SEPARATOR.'views/'.$this->cache_name))
            $this->parse();

        foreach ($parameters as $key => $value)
            ${''.$key} = $value;

        if(is_bool(env('MINIFIED', 'FALSE'))) {
            include_once storage_path() . DIRECTORY_SEPARATOR . 'views/minified/' . $this->cache_name;
            return;
        }

        include_once storage_path() . DIRECTORY_SEPARATOR . 'views/normal/' . $this->cache_name;
    }

    private function parse(): void {
        $content = file_get_contents(root_path().DIRECTORY_SEPARATOR.'resources/views/'.$this->name);
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

            $path = root_path().DIRECTORY_SEPARATOR.'resources/layouts/'.str_replace('.', DIRECTORY_SEPARATOR, $layout).'.blade.php';
            if(!file_exists($path))
                throw new Exception('Layout: '.$layout.' not found', 404);

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

        $content = preg_replace('/@(.*)\((.*)\)/', '', $content);
        file_put_contents(storage_path().DIRECTORY_SEPARATOR.'views/normal/'.$this->cache_name, $content);
        $content = trim(preg_replace('/\s\s+/', '', $content));
        file_put_contents(storage_path().DIRECTORY_SEPARATOR.'views/minified/'.$this->cache_name, $content);
    }

}