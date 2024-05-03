<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\User;
use App\Models\Setting;



class AdminController extends BaseController
{
    protected $helpers = ["url", "form", "CIMail", "CIFunctions"];
    protected $db;
    

    public function __construct()
    {
        require_once APPPATH . "ThirdParty/ssp.php";
        $this->db = db_connect();
    }

    public function settings()
    {
        $data = [
            "pageTitle" => "Settings",
        ];
        return view("backend/pages/settings", $data);
    }

    public function updateGeneralSettings()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();

            $this->validate([
                "blog_title" => [
                    "rules" => "required",
                    "errors" => [
                        "required" => "Blog title is required",
                    ],
                ],
                "blog_email" => [
                    "rules" => "required|valid_email",
                    "errors" => [
                        "required" => "Blog email is required",
                        "valid_email" => "Enter a valid email",
                    ],
                ],
            ]);

            if ($validation->run() === false) {
                $errors = $validation->getErrors();
                return $this->response->setJSON([
                    "status" => 0,
                    "token" => csrf_hash(),
                    "error" => $errors,
                ]);
            } else {
                $settings = new Setting();
                $setting_id = $settings->asObject()->first()->id;
                $update = $settings
                    ->where("id", $setting_id)
                    ->set([
                        "blog_name" => $request->getVar("blog_title"),
                        "blog_email" => $request->getVar("blog_email"),
                        "blog_phone" => $request->getVar("blog_phone"),
                    ])
                    ->update();
                if ($update) {
                    return $this->response->setJSON([
                        "status" => 1,
                        "token" => csrf_hash(),
                        "msg" => "Settings have been successfully updated!",
                    ]);
                } else {
                    return $this->response->setJSON([
                        "status" => 0,
                        "token" => csrf_hash(),
                        "msg" => "Something went wrong!",
                    ]);
                }
            }
        }
    }

    public function updateBlogLogo()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $settings = new Setting();
            $path = "images/blog";
            $file = $request->getFile("blog_logo");
            $setting_data = $settings->asObject()->first();
            $old_blog_logo = $setting_data->blog_logo;
            $new_filename = "CInews_logo" . $file->getRandomName();

            if ($file->move($path, $new_filename)) {
                if (
                    $old_blog_logo != null &&
                    file_exists($path . $old_blog_logo)
                ) {
                    unlink($path . $old_blog_logo);
                }
                $update = $settings
                    ->where("id", $setting_data->id)
                    ->set(["blog_logo" => $new_filename])
                    ->update();
                if ($update) {
                    return $this->response->setJSON([
                        "status" => 1,
                        "token" => csrf_hash(),
                        "msg" => "Blog logo updated successfully",
                    ]);
                } else {
                    return $this->response->setJSON([
                        "status" => 1,
                        "token" => csrf_hash(),
                        "msg" => "Something went wrong",
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    "status" => 0,
                    "token" => csrf_hash(),
                    "msg" => "Something went wrong",
                ]);
            }
        }
    }

    public function updateBlogFavicon()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $settings = new Setting();
            $path = "images/blog";
            $file = $request->getFile("blog_favicon");
            $setting_data = $settings->asObject()->first();
            $old_blog_favicon = $setting_data->blog_favicon;
            $new_filename = "CInews_favicon" . $file->getRandomName();

            if ($file->move($path, $new_filename)) {
                if (
                    $old_blog_favicon != null &&
                    file_exists($path . $old_blog_favicon)
                ) {
                    unlink($path . $old_blog_favicon);
                }
                $update = $settings
                    ->where("id", $setting_data->id)
                    ->set(["blog_favicon" => $new_filename])
                    ->update();
                if ($update) {
                    return $this->response->setJSON([
                        "status" => 1,
                        "token" => csrf_hash(),
                        "msg" => "Blog favicon updated successfully",
                    ]);
                } else {
                    return $this->response->setJSON([
                        "status" => 0,
                        "token" => csrf_hash(),
                        "msg" => "Something went wrong",
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    "status" => 0,
                    "token" => csrf_hash(),
                    "msg" => "Something went wrong",
                ]);
            }
        }
    }

    public function getUsers()
    {
        $userModel = new \App\Models\User();
        $users = $userModel->findAll();
    
        $data = array_map(function ($user) {
            // Check if $user is an array and convert to object if necessary
            $user = is_array($user) ? (object)$user : $user;
    
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'actions' => "<button class='btn btn-info btn-sm editUser' data-id='$user->id''>Edit</button>
                <button class='btn btn-danger btn-sm deleteUser' data-id='$user->id''>Delete</button>"
            ];
        }, $users);
    
        return $this->response->setJSON([
            'data' => $data
        ]);
    }
    
    public function deleteUser() {
        $userId = $this->request->getPost('id');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User ID is required']);
        }
    
        $userModel = new \App\Models\User();
        $postModel = new \App\Models\Post();
        $categoryModel = new \App\Models\Category();
        $user = $userModel->find($userId);
    
        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not found']);
        }
    
        // Start transaction to ensure data integrity
        $this->db->transStart();
            // Delete user's posts and associated images
            $posts = $postModel->where('author_id', $userId)->findAll();
            foreach ($posts as $post) {
                $path = "images/posts/";
                $imageFile = $path . $post['featured_image'];
                if (!empty($post['featured_image']) && file_exists($imageFile)) {
                    @unlink($imageFile);
                    @unlink($path . "thumb_" . $post['featured_image']);
                    @unlink($path . "resized_" . $post['featured_image']);
                }
                $postModel->delete($post['id']);
            }
    
            // Delete categories created by the user
            $categoryModel->where('author_id', $userId)->delete();
    
            // Delete user's profile picture
            $profileImagePath = "images/users/";
            if (!empty($user['picture']) && file_exists($profileImagePath . $user['picture'])) {
                @unlink($profileImagePath . $user['picture']);
            }
    
            // Finally, delete the user
            $userModel->delete($userId);
    }    
    
    public function getUserDetails() {
        $userId = $this->request->getGet('id');
        $userModel = new \App\Models\User();
        $user = $userModel->find($userId);
        return $this->response->setJSON($user);
    }

    public function updateUserDetails() {
        $userId = $this->request->getPost('user_id');
        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'role' => $this->request->getPost('role'),
        ];
        $userModel = new \App\Models\User();
        $userModel->update($userId, $data);
        return $this->response->setJSON(['status' => 'success', 'message' => 'User updated successfully']);
    }

}