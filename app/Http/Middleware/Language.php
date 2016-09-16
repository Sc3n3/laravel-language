<?php namespace App\Http\Middleware;

use Closure, Config, App, Redirect, Route; // ... and so on

class Language {

    public function __construct() {
        //
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Make sure current locale exists.
        $locale = $request->segment(1);
        $languages = self::getLanguages();

        if ( ! array_key_exists($locale, $languages)) {
            
            $segments = $request->segments();

            $acceptLanguage = $this->getAcceptLanguage($request);
            $defaultLanguage = in_array($acceptLanguage, array_keys($languages)) ? 
                               $acceptLanguage : Config::get('app.fallback_locale');

            $segments = array_merge(array($defaultLanguage), $segments);

            return Redirect::to(implode('/', $segments));
        }

        App::setLocale($locale);

        $this->setRoutes();

        return $next($request);

    }

    public static function getLanguages() {

        return [
            'en' => 'English',
            'tr' => 'Türkçe'
        ];

    }

    private function getAcceptLanguage($request) {

        return mb_substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);

    }

    private function setRoutes() {

        Route::group([ 'middleware' => [ 'web' ], 'prefix' => App::getLocale() ], function() {
            include_once app_path('/Http/routes.php');
        });

    }

}
