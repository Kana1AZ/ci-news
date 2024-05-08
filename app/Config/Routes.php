<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'AuthController::loginForm');

  
    $routes->group('', ['filter' => 'cifilter:auth'], static function($routes){
       // $routes->view('example-page', 'example-page');
        $routes->get('home', 'UserController::index', ['as'=>'home']);
        $routes->get('logout', 'UserController::logoutHandler', ['as'=>'logout']);
        $routes->get('profile', 'UserController::profile', ['as'=>'profile']);
        $routes->get('left-sidebar', 'UserController::getUserRole', ['as'=>'left-sidebar']);
        $routes->post('update-personal-details', 'UserController::updatePersonalDetails', ['as'=>'update-personal-details']);
        $routes->post('update-profile-picture', 'UserController::updateProfilePicture', ['as'=>'update-profile-picture']);
        $routes->post('change-password', 'UserController::changePassword', ['as'=>'change-password']);        
        $routes->get('categories', 'UserController::categories', ['as'=>'categories']);
        $routes->post('add-category', 'UserController::addCategory', ['as'=>'add-category']);
        $routes->get('get-categories', 'UserController::getCategories', ['as'=>'get-categories']);
        $routes->get('get-category', 'UserController::getCategory', ['as'=>'get-category']);
        $routes->post('update-category', 'UserController::updateCategory', ['as'=>'update-category']);
        $routes->get('delete-category', 'UserController::deleteCategory', ['as'=>'delete-category']);
        $routes->get('reorder-categories', 'UserController::reorderCategories', ['as'=>'reorder-categories']);

        $routes->group('posts', static function($routes){
        $routes->get('new-post', 'UserController::addPost', ['as'=>'new-post']);
        $routes->post('create-post', 'UserController::createPost', ['as'=>'create-post']);
        $routes->get('/', 'UserController::allPosts', ['as'=>'all-posts']);
        $routes->get('get-posts', 'UserController::getPosts', ['as'=>'get-posts']);
        $routes->get('edit-post/(:any)', 'UserController::editPost/$1', ['as'=>'edit-post']);
        $routes->post('update-post', 'UserController::updatePost', ['as'=>'update-post']); 
        $routes->get('delete-post', 'UserController::deletePost', ['as'=>'delete-post']);
        
        });
    });
    $routes->group('', ['filter' => 'cifilter:guest'], static function($routes){
       // $routes->view('example-auth', 'example-auth');
       $routes->get('login', 'AuthController::loginForm', ['as'=>'login.form']);
       $routes->post('login', 'AuthController::loginHandler', ['as'=>'login.handler']);
       $routes->get('register', 'AuthController::registerForm', ['as'=>'register.form']);
       $routes->post('register', 'AuthController::registerHandler', ['as'=>'register.handler']);
       $routes->get('forgot-password', 'AuthController::forgotForm', ['as'=>'forgot.form']);
       $routes->post('send-password-reset-link', 'AuthController::sendPasswordResetLink', ['as'=>'send_password_reset_link']);
       $routes->get('password/reset/(:any)', 'AuthController::resetPassword/$1', ['as'=>'reset-password']);
       $routes->post('reset-password-handler/(:any)', 'AuthController::resetPasswordHandler/$1', ['as'=>'reset-password-handler'] );
    });
    
    $routes->group('', ['filter' => 'cifilter:admin'], static function($routes) {
        $routes->get('settings', 'AdminController::settings', ['as'=>'settings']);
        $routes->post('update-blog-favicon', 'AdminController::updateBlogFavicon', ['as'=>'update-blog-favicon']);
        $routes->get('get-users', 'AdminController::getUsers', ['as'=>'get-users']);
        $routes->post('delete-user', 'AdminController::deleteUser', ['as' => 'delete-user']);
        $routes->get('get-user-details', 'AdminController::getUserDetails', ['as' => 'get-user-details']);
        $routes->post('update-user-details', 'AdminController::updateUserDetails', ['as' => 'update-user-details']);
        $routes->post('update-general-settings', 'AdminController::updateGeneralSettings', ['as'=>'update-general-settings']);
        $routes->post('update-blog-logo', 'AdminController::updateBlogLogo', ['as'=>'update-blog-logo']);
    });
        