<?php

namespace core;

use app\model\Category;
use Cartalyst\Sentry\Facades\Native\Sentry as Sentry;
use core\exception\ViewNotFoundException;
use Exception;
use Illuminate\View\Factory;
use Philo\Blade\Blade;

class TemplateEngine {
    /**
     * @var Factory $factory
     */
    private static $factory = null;
    
    /**
     * @var Factory $blade
     */
    private static $blade = null;
    private static $view  = null;
    private static $data  = [];
    
    public static function set_user_information() {
        if ( ! App::has("user")) {
            static::$data["is_logged_in"] = false;
            static::$data["user"]         = [];
            
            return;
        }
        
        try {
            static::$data["is_logged_in"] = Sentry::check() ? true : false;
            static::$data["user"]         = App::get("user");
        } catch (Exception $exception) {
            static::$data["is_logged_in"] = false;
            static::$data["user"]         = [];
        }
    }
    
    public static function render($name, $options = []) {
        static::$data = $options;
        static::set_user_information();
        static::$data["config"] = App::get("config");
        
        if (is_null(static::$factory))
            static::set_view($name);
        
        static::$view = static::$factory->make($name, self::$data);
        
        return self::$view->render();
    }
    
    public static function set_view($name) {
        $name = str_replace(".", "/", $name);
        
        /**
         * @var Blade $blade
         */
        static::$blade = App::get("blade");
        
        /**
         * @var Factory $factory
         */
        static::$factory = static::$blade->view();
        if ( ! static::$factory->exists($name)) {
            $array    = explode("/", $name);
            $template = $array[count($array) - 1];
            
            throw new ViewNotFoundException("{$template} template does not exist");
        }
    }
}