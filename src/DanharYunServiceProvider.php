<?php

namespace Linshunwei\DanharYun;

use Illuminate\Support\ServiceProvider;

class DanharYunServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
	    if ($this->app->runningInConsole()) {
		    $this->publishes([
			    __DIR__ . '/../config/danhar-yun.php' => config_path('danhar-yun.php'),
		    ]);
	    }
	    $this->mergeConfigFrom(__DIR__.'/../config/danhar-yun.php', 'danhar-yun');
        //这里使用到了facades中的字符串
        $this->app->singleton('danharyun',function(){
	        return new DanharYun();
        });
    }
}
