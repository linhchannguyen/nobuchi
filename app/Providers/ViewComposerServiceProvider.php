<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider {
    public function boot() {
        view()->composer("layouts.master","App\Http\ViewComposers\ShareViewCompoer");
    }
}