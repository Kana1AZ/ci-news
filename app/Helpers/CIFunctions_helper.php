<?php

use App\Libraries\CIAuth;
use App\Models\User;
use App\Models\Setting;
use App\Models\Category;


if (!function_exists('get_user')){
    function get_user(){
        if(CIAuth::check()){
            $user = new User();
            return $user->asObject()->where('id',CIAuth::id())->first();
        }else{
            return null;
        }
    }
}

if (!function_exists('get_settings')){
    function get_settings(){
        $settings = new Setting();
        $settings_data = $settings->asObject()->first();

        if(!$settings_data){
            $data = array(
                'blog_name' => 'Guarantees',
                'blog_email' => 'info@cinews.test',
                'blog_phone' => null,
                'blog_logo' => null,
                'blog_favicon' => null,
            );
            $settings->save($data);
            $new_settings_data = $settings->asObject()->first();
            return $new_settings_data;
        }else{
            return $settings_data;
        }
    }
}

if(!function_exists('current_route_name')){
    function current_route_name(){
        $router = \CodeIgniter\Config\Services::router();
        $route_name = $router->getMatchedRouteOptions()['as'];
        return $route_name;
    }
}


/**
 * Frontend Functions
 */

 if(!function_exists('get_categories')){
    function get_categories(){
        $category = new Category();
        return $category->asObject()->orderBy('ordering', 'asc')->findAll();
    }
 }

