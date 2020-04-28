<?php

namespace Illuminate\Foundation\Providers;

use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Foundation\Console\RequestMakeCommand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\ServiceProvider;

class FormRequestServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RequestMakeCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->afterResolving(ValidatesWhenResolved::class, function ($resolved) {
            $resolved->validateResolved();
        });

        $this->app->resolving(FormRequest::class, function (FormRequest $request, $app) {
            $request = FormRequest::createFrom($app['request'], $request);

            $request->setContainer($app);
        });
    }
}
