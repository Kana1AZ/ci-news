<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\CIAuth;
use App\Models\User;
use App\Libraries\Hash;
use App\Models\Setting;
use App\Models\Category;
use SSP;
use App\Models\Post;
use Mberecall\CI_Slugify\SlugService;

class AdminController extends BaseController
{
    protected $helpers = ["url", "form", "CIMail", "CIFunctions"];
    protected $db;

    public function __construct()
    {
        require_once APPPATH . "ThirdParty/ssp.php";
        $this->db = db_connect();
    }

    public function index()
    {
        $data = [
            "pageTitle" => "Dashboard",
        ];
        return view("backend/pages/home", $data);
    }

    public function logoutHandler()
    {
        CIAuth::forget();
        return redirect()
            ->route("admin.login.form")
            ->with("fail", "You are logged out");
    }

    public function profile()
    {
        $data = [
            "pageTitle" => "Profile",
        ];
        return view("backend/pages/profile", $data);
    }

    public function updatePersonalDetails()
    {
        $request = \Config\Services::request();
        $validation = \Config\Services::validation();
        $user_id = CIAuth::id();

        if ($request->isAJAX()) {
            $this->validate([
                "name" => [
                    "rules" => "required",
                    "errors" => [
                        "required" => "Full name is required",
                    ],
                ],
                "username" => [
                    "rules" =>
                        "required|min_length[4]|is_unique[users.username,id," .
                        $user_id .
                        "]",
                    "errors" => [
                        "required" => "Username is required",
                        "min_length" =>
                            "Username must have a minimun 4 characters",
                        "is_unique" => "Username is already taken!",
                    ],
                ],
            ]);

            if ($validation->run() == false) {
                $errors = $validation->getErrors();
                return json_encode(["status" => 0, "error" => $errors]);
            } else {
                $user = new User();
                $update = $user
                    ->where("id", $user_id)
                    ->set([
                        "name" => $request->getVar("name"),
                        "username" => $request->getVar("username"),
                        "bio" => $request->getVar("bio"),
                    ])
                    ->update();

                if ($update) {
                    $user_info = $user->find($user_id);
                    return json_encode([
                        "status" => 1,
                        "user_info" => $user_info,
                        "msg" => "Your details have been successfully updated!",
                    ]);
                } else {
                    return json_encode([
                        "status" => 0,
                        "msg" => "Something went wrong!",
                    ]);
                }
            }
        }
    }

    public function updateProfilePicture()
    {
        $request = \Config\Services::request();
        $user_id = CIAuth::id();
        $user = new User();
        $user_info = $user
            ->asObject()
            ->where("id", $user_id)
            ->first();

        $path = "images/users/";
        $file = $request->getFile("user_profile_file");
        $old_picture = $user_info->picture;
        $new_filename = "UIMG_" . $user_id . $file->getRandomName();

        //image manipulation
        $upload_image = \Config\Services::image()
            ->withFile($file)
            ->resize(450, 450, true, "height")
            ->save($path . $new_filename);

        if ($upload_image) {
            if ($old_picture != null && file_exists($path . $new_filename)) {
                unlink($path . $old_picture);
            }
            $user
                ->where("id", $user_info->id)
                ->set(["picture" => $new_filename])
                ->update();

            echo json_encode([
                "status" => 1,
                "msg" =>
                    "Done! Your profile picture has been successfully updated.",
            ]);
        } else {
            echo json_encode(["status" => 0, "msg" => "Something went wrong!"]);
        }
    }

    public function changePassword()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();
            $user_id = CIAuth::id();
            $user = new User();
            $user_info = $user
                ->asObject()
                ->where("id", $user_id)
                ->first();

            $this->validate([
                "current_password" => [
                    "rules" =>
                        "required|min_length[5]|check_current_password[current_password]",
                    "errors" => [
                        "required" => "Enter current password",
                        "min_length" =>
                            "Password must have at least 5 characters",
                        "check_current_password" =>
                            "Current password is incorrect",
                    ],
                ],
                "new_password" => [
                    "rules" =>
                        "required|min_length[5]|max_length[20]|is_password_strong[new_password]",
                    "errors" => [
                        "required" => "New password is required",
                        "min_length" =>
                            "Password must be at least 5 characters long.",
                        "max_length" =>
                            "Password can not be more than 20 characters long.",
                        "is_password_strong" =>
                            "Password must contain at least 1 uppercase and 1 lowercase letter, 1 number and 1 special character",
                    ],
                ],
                "confirm_new_password" => [
                    "rules" => "required|matches[new_password]",
                    "errors" => [
                        "required" => "Confirm new password",
                        "matches" => "Password must match",
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
                //update password in DB
                $user
                    ->where("id", $user_info->id)
                    ->set([
                        "password" => Hash::make(
                            $request->getVar("new_password")
                        ),
                    ])
                    ->update();

                //send email notification
                $mail_data = [
                    "user" => $user_info,
                    "new_password" => $request->getVar("new_password"),
                ];

                $view = \Config\Services::renderer();
                $mail_body = $view
                    ->setVar("mail_data", $mail_data)
                    ->render("email-templates/password-changed-email-template");

                $mailConfig = [
                    "mail_from_email" => env("EMAIL_FROM_ADDRESS"),
                    "mail_from_name" => env("EMAIL_FROM_NAME"),
                    "mail_recipient_email" => $user_info->email,
                    "mail_recipient_name" => $user_info->name,
                    "mail_subject" => "Password Changed",
                    "mail_body" => $mail_body,
                ];

                sendEmail($mailConfig);
                return $this->response->setJSON([
                    "status" => 1,
                    "token" => csrf_hash(),
                    "msg" => "Password has been successfully updated!",
                ]);
            }
        }
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
                        "blog_meta_description" => $request->getVar(
                            "blog_meta_description"
                        ),
                        "blog_meta_keywords" => $request->getVar(
                            "blog_meta_keywords"
                        ),
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

    public function categories()
    {
        $data = [
            "pageTitle" => "Categories",
        ];
        return view("backend/pages/categories", $data);
    }

    public function addCategory()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();

            $this->validate([
                "category_name" => [
                    "rules" => "required|is_unique[categories.name]",
                    "errors" => [
                        "required" => "Category name is required",
                        "is_unique" => "Category name is already taken!",
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
                // return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'Category has been successfully added!']);
                $category = new Category();
                $save = $category->save([
                    "name" => $request->getVar("category_name"),
                ]);

                if ($save) {
                    return $this->response->setJSON([
                        "status" => 1,
                        "token" => csrf_hash(),
                        "msg" => "Category has been successfully added!",
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

    public function getCategories()
    {
        $dbDetails = [
            "host" => $this->db->hostname,
            "user" => $this->db->username,
            "pass" => $this->db->password,
            "db" => $this->db->database,
        ];

        $table = "categories";
        $primaryKey = "id";
        $columns = [
            [
                "db" => "id",
                "dt" => 0,
            ],
            [
                "db" => "name",
                "dt" => 1,
            ],
            [
                "db" => "id",
                "dt" => 2,
                "formatter" => function ($id, $row) {
                    // return "(x) will be added later";
                    $post = new Post();
                    $posts = $post
                        ->where("category_id", $row["id"])
                        ->countAllResults();
                    return $posts;
                },
            ],
            [
                "db" => "id",
                "dt" => 3,
                "formatter" => function ($id, $row) {
                    return "<div class='btn-group'>
                        <button class='btn btn-sm btn-link p-0 mx-1 editCategoryBtn' data-id='" .
                        $row["id"] .
                        "'>Edit</button>
                        <button class='btn btn-sm btn-link p-0 mx-1 deleteCategoryBtn' data-id='" .
                        $row["id"] .
                        "'>Delete</button>
                    </div>";
                },
            ],
            [
                "db" => "ordering",
                "dt" => 4,
            ],
        ];

        return json_encode(
            SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns)
        );
    }

    public function getCategory()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $id = $request->getVar("category_id");
            $category = new Category();
            $category_data = $category->find($id);
            return $this->response->setJSON(["data" => $category_data]);
        }
    }

    public function updateCategory()
    {
        $request = \Config\Services::request();
        $id = $request->getVar("category_id");

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();

            $this->validate([
                "category_name" => [
                    "rules" =>
                        "required|is_unique[categories.name,id," . $id . "]",
                    "errors" => [
                        "required" => "Category name is required",
                        "is_unique" => "Category name is already taken!",
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
                $category = new Category();
                $update = $category
                    ->where("id", $request->getVar("category_id"))
                    ->set(["name" => $request->getVar("category_name")])
                    ->update();

                if ($update) {
                    return $this->response->setJSON([
                        "status" => 1,
                        "token" => csrf_hash(),
                        "msg" => "Category has been successfully updated!",
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

    public function deleteCategory()
    {
        $request = \Config\Services::request();
        $id = $request->getVar("category_id");
        $category = new Category();

        if ($request->isAJAX()) {
            // $delete = $category->delete($id);

            // //check if there are any posts in this category

            // if ($delete) {
            //     return $this->response->setJSON([
            //         "status" => 1,
            //         "token" => csrf_hash(),
            //         "msg" => "Category has been successfully deleted!",
            //     ]);
            // } else {
            //     return $this->response->setJSON([
            //         "status" => 0,
            //         "token" => csrf_hash(),
            //         "msg" => "Something went wrong!",
            //     ]);
            // }

            $post = new Post();
            $posts = $post->where("category_id", $id)->findAll();
            $msg = "";
            if (count($posts) > 0) {
                $msg =
                    "There are posts in this category. Please delete them first!";
                return $this->response->setJSON([
                    "status" => 0,
                    "token" => csrf_hash(),
                    "msg" => $msg,
                ]);
            } else {
                $delete = $category->delete($id);
                $msg = "Category has been successfully deleted!";
                if ($delete) {
                    return $this->response->setJSON([
                        "status" => 1,
                        "token" => csrf_hash(),
                        "msg" => $msg,
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

    public function reorderCategories()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $positions = $request->getVar("positions");
            $category = new Category();

            foreach ($positions as $position) {
                $index = $position[0];
                $newPosition = $position[1];
                $category
                    ->where("id", $index)
                    ->set(["ordering" => $newPosition])
                    ->update();
            }
            return $this->response->setJSON([
                "status" => 1,
                "msg" => "Categories have been successfully reordered!",
            ]);
        }
    }

    public function addPost()
    {
        $category = new Category();
        $data = [
            "pageTitle" => "Add new Post",
            "categories" => $category->asObject()->findAll(),
        ];
        return view("backend/pages/new-post", $data);
    }

    public function createPost()
    {
        $request = \Config\Services::request();
        log_message(
            "debug",
            "Received data: " . print_r($request->getPost(), true)
        ); // Log all post data

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();

            $this->validate([
                "title" => [
                    "rules" => "required|is_unique[posts.title]",
                    "errors" => [
                        "required" => "Post title is required",
                        "is_unique" => "Post title is already taken!",
                    ],
                ],
                "content" => [
                    "rules" => "required|min_length[20]",
                    "errors" => [
                        "required" => "Post content is required",
                        "min_length" =>
                            "Post content must have at least 20 characters",
                    ],
                ],
                "category" => [
                    "rules" => "required",
                    "errors" => [
                        "required" => "Select post category",
                    ],
                ],
                "featured_image" => [
                    "rules" =>
                        "uploaded[featured_image]|is_image[featured_image]|max_size[featured_image, 2048]",
                    "errors" => [
                        "uploaded" => "Featured image is required",
                        "is_image" =>
                            "Featured image must be a jpg, jpeg or png",
                        "max_size" =>
                            "Featured image size must be less than 2MB",
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
                $user_id = CIAuth::id();
                $path = "images/posts/";
                $file = $request->getFile("featured_image");
                //$filename = $file->getClientName();
                $filename = 'pimg_'.time().$file->getClientName();


                //Make post featured image folder if not exist

                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }

                //Move file to folder

                if ($file->move($path, $filename)) {
                    //create thumbnail
                    \Config\Services::image()
                        ->withFile($path . $filename)
                        ->fit(150, 150, "center")
                        ->save($path . "thumb_" . $filename);

                    //create resized image
                    \Config\Services::image()
                        ->withFile($path . $filename)
                        ->resize(450, 300, true, "width")
                        ->save($path . "resized_" . $filename);

                    //save post data to DB
                    $post = new Post();
                    $data = [
                        "author_id" => $user_id,
                        "category_id" => $request->getVar("category"),
                        "title" => $request->getVar("title"),
                        "slug" => SlugService::model(Post::class)->make(
                            $request->getVar("title")
                        ),
                        "content" => $request->getVar("content"),
                        "featured_image" => $filename,
                        "tags" => $request->getVar("tags"),
                        "meta_keywords" => $request->getVar("meta_keywords"),
                        "meta_description" => $request->getVar(
                            "meta_description"
                        ),
                        "visibility" => $request->getVar("visibility"),
                    ];
                    $save = $post->insert($data);
                    $last_id = $post->getInsertID();

                    if ($save) {
                        return $this->response->setJSON([
                            "status" => 1,
                            "token" => csrf_hash(),
                            "msg" => "Post has been successfully added!",
                        ]);
                    } else {
                        return $this->response->setJSON([
                            "status" => 0,
                            "token" => csrf_hash(),
                            "msg" => "Error while adding post!",
                        ]);
                    }
                } else {
                    return $this->response->setJSON([
                        "status" => 0,
                        "token" => csrf_hash(),
                        "msg" => "Error while uploading image!",
                    ]);
                }
            }
        }
    }

    public function allPosts()
    {
        $data = [
            "pageTitle" => "All Posts",
        ];
        return view("backend/pages/all-posts", $data);
    }

    public function getPosts()
    {
        $dbDetails = [
            "host" => $this->db->hostname,
            "user" => $this->db->username,
            "pass" => $this->db->password,
            "db" => $this->db->database,
        ];
        $table = "posts";
        $primaryKey = "id";
        $columns = [
            [
                "db" => "id",
                "dt" => 0,
            ],
            [
                "db" => "id",
                "dt" => 1,
                "formatter" => function ($d, $row) {
                    $post = new Post();
                    $image = $post->asObject()->find($row["id"])
                        ->featured_image;
                    return "<img src='/images/posts/thumb_$image' class='img-thumbnail' style='max-width: 70px'>";
                },
            ],
            [
                "db" => "title",
                "dt" => 2,
            ],
            [
                "db" => "id",
                "dt" => 3,
                "formatter" => function ($d, $row) {
                    $post = new Post();
                    $category_id = $post->asObject()->find($row["id"])
                        ->category_id;
                    $category = new Category();
                    $category_name = $category->asObject()->find($category_id)
                        ->name;
                    return $category_name;
                },
            ],
            [
                "db" => "id",
                "dt" => 4,
                "formatter" => function ($d, $row) {
                    $post = new Post();
                    $visibility = $post->asObject()->find($row["id"])
                        ->visibility;
                    return $visibility == 1 ? "Published" : "Draft";
                },
            ],
            [
                "db" => "id",
                "dt" => 5,
                "formatter" => function ($d, $row) {
                    return "<div class='btn-group'>
                        <a href='' class='btn btn-sm btn-link p-0 mx-1'>View</a>
                        <a href='" .
                        route_to("edit-post", $row["id"]) .
                        "' class='btn btn-sm btn-link p-0 mx-1' >Edit</a>
                        <button class='btn btn-sm btn-link p-0 mx-1 deletePostBtn' data-id='" .
                        $row["id"] .
                        "'>Delete</button>
                    </div>";
                },
            ],
        ];
        return json_encode(
            SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns)
        );
    }

    public function editPost($id)
    {
        $post = new Post();
        $category = new Category();
        $data = [
            "pageTitle" => "Edit Post",
            "post" => $post->asObject()->find($id),
            "categories" => $category->asObject()->findAll(),
        ];
        return view("backend/pages/edit-post", $data);
    }

    public function updatePost()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();
            $post_id = $request->getVar("post_id");
            $user_id = CIAuth::id();
            $post = new Post();

            if (
                isset($_FILES["featured_image"]["name"]) &&
                !empty($_FILES["featured_image"]["name"])
            ) {
                $this->validate([
                    "title" => [
                        "rules" =>
                            "required|is_unique[posts.title,id," .
                            $post_id .
                            "]",
                        "errors" => [
                            "required" => "Post title is required",
                            "is_unique" => "Post title is already taken!",
                        ],
                    ],
                    "content" => [
                        "rules" => "required|min_length[20]",
                        "errors" => [
                            "required" => "Post content is required",
                            "min_length" =>
                                "Post content must have at least 20 characters",
                        ],
                    ],
                    "featured_image" => [
                        "rules" =>
                            "uploaded[featured_image]|is_image[featured_image]|max_size[featured_image,2048]",
                        "errors" => [
                            "uploaded" => "Featured image is required",
                            "is_image" =>
                                "Featured image must be a jpg, jpeg or png",
                            "max_size" =>
                                "Featured image size must be less than 2MB",
                        ],
                    ],
                ]);
            } else {
                $this->validate([
                    "title" => [
                        "rules" =>
                            "required|is_unique[posts.title,id," .
                            $post_id .
                            "]",
                        "errors" => [
                            "required" => "Post title is required",
                            "is_unique" => "Post title is already taken!",
                        ],
                    ],
                    "content" => [
                        "rules" => "required|min_length[20]",
                        "errors" => [
                            "required" => "Post content is required",
                            "min_length" =>
                                "Post content must have at least 20 characters",
                        ],
                    ],
                ]);
            }

            if ($validation->run() === false) {
                $errors = $validation->getErrors();
                return $this->response->setJSON([
                    "status" => 0,
                    "token" => csrf_hash(),
                    "error" => $errors,
                ]);
            } else {
                if (
                    isset($_FILES["featured_image"]["name"]) &&
                    !empty($_FILES["featured_image"]["name"])
                ) {
                    $path = "images/posts/";
                    $file = $request->getFile("featured_image");
                    // $filename = $file->getClientName();
                    $filename = 'pimg_'.time().$file->getClientName();
                    $old_post_featured_image = $post->asObject()->find($post_id)
                        ->featured_image;

                    //upload new image if it possible

                    if ($file->move($path, $filename)) {
                        //create thumbnail
                        \Config\Services::image()
                            ->withFile($path . $filename)
                            ->fit(150, 150, "center")
                            ->save($path . "thumb_" . $filename);

                        //create resized image
                        \Config\Services::image()
                            ->withFile($path . $filename)
                            ->resize(450, 300, true, "width")
                            ->save($path . "resized_" . $filename);

                        //delete old image
                        if (
                            $old_post_featured_image != null &&
                            file_exists($path . $old_post_featured_image)
                        ) {
                            unlink($path . $old_post_featured_image);
                            unlink($path . "thumb_" . $old_post_featured_image);
                            unlink(
                                $path . "resized_" . $old_post_featured_image
                            );
                        }

                        //update post data
                        $data = [
                            "author_id" => $user_id,
                            "category_id" => $request->getVar("category"),
                            "title" => $request->getVar("title"),
                            "slug" => SlugService::model(Post::class)->make(
                                $request->getVar("title")
                            ),
                            "content" => $request->getVar("content"),
                            "featured_image" => $filename,
                            "tags" => $request->getVar("tags"),
                            "meta_keywords" => $request->getVar(
                                "meta_keywords"
                            ),
                            "meta_description" => $request->getVar(
                                "meta_description"
                            ),
                            "visibility" => $request->getVar("visibility"),
                        ];

                        $update = $post->update($post_id, $data);

                        if ($update) {
                            return $this->response->setJSON([
                                "status" => 1,
                                "token" => csrf_hash(),
                                "msg" => "Post has been successfully updated!",
                            ]);
                        } else {
                            return $this->response->setJSON([
                                "status" => 0,
                                "token" => csrf_hash(),
                                "msg" => "Error while updating post!",
                            ]);
                        }
                    } else {
                        return $this->response->setJSON([
                            "status" => 0,
                            "token" => csrf_hash(),
                            "msg" => "Error while uploading image!",
                        ]);
                    }
                } else {
                    //update post data
                    $data = [
                        "author_id" => $user_id,
                        "category_id" => $request->getVar("category"),
                        "title" => $request->getVar("title"),
                        "slug" => SlugService::model(Post::class)->make(
                            $request->getVar("title")
                        ),
                        "content" => $request->getVar("content"),
                        "tags" => $request->getVar("tags"),
                        "meta_keywords" => $request->getVar("meta_keywords"),
                        "meta_description" => $request->getVar(
                            "meta_description"
                        ),
                        "visibility" => $request->getVar("visibility"),
                    ];

                    $update = $post->update($post_id, $data);

                    if ($update) {
                        return $this->response->setJSON([
                            "status" => 1,
                            "token" => csrf_hash(),
                            "msg" => "Post has been successfully updated!",
                        ]);
                    } else {
                        return $this->response->setJSON([
                            "status" => 0,
                            "token" => csrf_hash(),
                            "msg" => "Error while updating post!",
                        ]);
                    }
                }
            }
        }
    }

    public function deletePost()
    {
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $post_id = $request->getVar("post_id");
            $post = new Post();
            $path = "images/posts/";
            $post_featured_image = $post->asObject()->find($post_id)
                ->featured_image;

            $delete = $post->delete($post_id);

            if ($delete) {
                if (
                    $post_featured_image != null &&
                    file_exists($path . $post_featured_image)
                ) {
                    unlink($path . $post_featured_image);
                    unlink($path . "thumb_" . $post_featured_image);
                    unlink($path . "resized_" . $post_featured_image);
                }
                return $this->response->setJSON([
                    "status" => 1,
                    "token" => csrf_hash(),
                    "msg" => "Post has been successfully deleted!",
                ]);
            } else {
                return $this->response->setJSON([
                    "status" => 0,
                    "token" => csrf_hash(),
                    "msg" => "Error while deleting post!",
                ]);
            }
        }
    }
}
