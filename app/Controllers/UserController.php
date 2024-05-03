<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\CIAuth;
use App\Models\User;
use App\Libraries\Hash;
use App\Models\Category;
use SSP;
use App\Models\Post;

class UserController extends BaseController
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
        $postModel = new \App\Models\Post(); // Assuming you have a Post model
        $userId = CIAuth::id();  // Get the logged-in user's ID.
        $userModel = new \App\Models\User();
        $user = $userModel->find($userId);

        // Check if user data is an array and convert to object if necessary
        $user = is_array($user) ? (object) $user : $user;
        $userRole = isset($user->role) ? $user->role : null;

        // Count total guarantees posted by the author
        $totalGuarantees = $postModel->where('author_id', $userId)->countAllResults();

        // Count active guarantees (expiration date in the future)
        $activeGuarantees = $postModel->where('author_id', $userId)
                                       ->where('expiration_date >', date('Y-m-d'))
                                       ->countAllResults();

        // Count expired guarantees (expiration date in the past)
        $expiredGuarantees = $postModel->where('author_id', $userId)
                                        ->where('expiration_date <=', date('Y-m-d'))
                                        ->countAllResults();

        // Get the 5 guarantees that are soon to expire
        $soonToExpireGuarantees = $postModel->asObject()->where('author_id', $userId)
                                   ->where('expiration_date >', date('Y-m-d')) // make sure to adjust this as per your requirements
                                   ->orderBy('expiration_date', 'asc')
                                   ->findAll(5); // Limit to 5 results

        $data = [
            "pageTitle" => "Dashboard",
            "totalGuarantees" => $totalGuarantees,
            "activeGuarantees" => $activeGuarantees,
            "expiredGuarantees" => $expiredGuarantees,
            "soonToExpireGuarantees" => $soonToExpireGuarantees,
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

    public function categories()
    {
        $data = [
            "pageTitle" => "Categories",
            "userId" => CIAuth::id()
        ];
        //   echo view('categories', ['userId' => $userId]);
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
                $user_id = CIAuth::id();
                log_message('debug', 'Request Data: ' . print_r($request->getPost(), true));
                log_message('debug', 'Current User ID: ' . $user_id);

                $save = $category->save([
                    "name" => $request->getVar("category_name"),
                    "author_id" => $user_id,
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
            ["db" => "id", "dt" => 0],
            ["db" => "name", "dt" => 1],
            [
                "db" => "id",
                "dt" => 2,
                "formatter" => function ($id, $row) {
                    $post = new Post();
                    return $post->where("category_id", $row["id"])->countAllResults();
                },
            ],
            [
                "db" => "id",
                "dt" => 3,
                "formatter" => function ($id, $row) {
                    return "<div class='btn-group'>
                    <a href='javascript:void(0);' class='btn btn-info btn-sm editCategoryBtn' style='min-width:70%' data-id='{$row["id"]}'><i class='icon-copy dw dw-edit2'></i></a>
                    <a href='javascript:void(0);' class='btn btn-danger btn-sm deleteCategoryBtn' style='min-width:70%' data-id='{$row["id"]}'><i class='icon-copy dw dw-delete-3'></i></a>
                </div>";
                },
            ],
            ["db" => "ordering", "dt" => 4],
        ];

        // Custom WHERE condition to filter categories by the current user
        $where = "author_id = " . CIAuth::id();

        return json_encode(
            SSP::complex($_GET, $dbDetails, $table, $primaryKey, $columns, $where)
        );
    }

    //EDIT CATEGORY BUTTON
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
        $userId = CIAuth::id();
        log_message('debug', $userId);

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

    public function addPost()
    {
        $category = new Category();
        $userId = CIAuth::id();  // Get the logged-in user's ID.

        $data = [
            "pageTitle" => "Add Post",
            "categories" => $category->asObject()->where('author_id', $userId)->findAll(),  // Only fetch categories created by the logged-in user.
        ];

        return view("backend/pages/new-post", $data);
    }

    public function createPost()
    {
        $request = \Config\Services::request();
        // helper([]);

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
                        "min_length" => "Post content must have at least 20 characters",
                    ],
                ],
                "category" => [
                    "rules" => "required",
                    "errors" => [
                        "required" => "Select post category",
                    ],
                ],
                "featured_image" => [
                    "rules" => "uploaded[featured_image]|is_image[featured_image]|max_size[featured_image, 2048]",
                    "errors" => [
                        "uploaded" => "Featured image is required",
                        "is_image" => "Featured image must be a jpg, jpeg, or png",
                        "max_size" => "Featured image size must be less than 2MB",
                    ],
                ],
                "expiration_date" => [
                    "rules" => "required|valid_date[Y-m-d]|check_date_is_future[expiration_date]",
                    "errors" => [
                        "required" => "Expiration date is required",
                        "valid_date" => "Provide a valid expiration date",
                        "check_date_is_future" => "Expiration date cannot be in the past",
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
                $filename = 'pimg_' . time() . $file->getClientName();

                // Make post featured image folder if not exist
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }

                // Move file to folder
                if ($file->move($path, $filename)) {
                    \Config\Services::image()
                        ->withFile($path . $filename)
                        ->fit(150, 150, "center")
                        ->save($path . "thumb_" . $filename);

                    \Config\Services::image()
                        ->withFile($path . $filename)
                        ->save($path . "resized_" . $filename);

                    // Save post data to DB
                    $post = new Post();
                    $data = [
                        "author_id" => $user_id,
                        "category_id" => $request->getVar("category"),
                        "title" => $request->getVar("title"),
                        "content" => $request->getVar("content"),
                        "featured_image" => $filename,
                        "visibility" => $request->getVar("visibility"),
                        "expiration_date" => $request->getVar("expiration_date"), // Include expiration date
                    ];
                    $save = $post->insert($data);

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
            ["db" => "id", "dt" => 0],
            ["db" => "id", "dt" => 1, "formatter" => function ($d, $row) {
                $post = new Post();
                $image = $post->asObject()->find($row["id"])->featured_image;
                return "<img src='/images/posts/thumb_$image' class='img-thumbnail' style='max-width: 70px'>";
            }],
            ["db" => "title", "dt" => 2],
            ["db" => "id", "dt" => 3, "formatter" => function ($d, $row) {
                $post = new Post();
                $category_id = $post->asObject()->find($row["id"])->category_id;
                $category = new Category();
                $category_name = $category->asObject()->find($category_id)->name;
                return $category_name;
            }],
            ["db" => "id", "dt" => 4, "formatter" => function ($d, $row) {
                $post = new Post();
                $postDetails = $post->asObject()->find($row["id"]);
                $expirationDate = $postDetails->expiration_date;
                $status = strtotime($expirationDate) > time() ? 'Active' : 'Expired';
                $style = $status === 'Expired' ? 'style="color: red;"' : 'style="color: green;"';
                return "<span $style>" . $expirationDate . ' (' . $status . ')</span>';
            }],
            ["db" => "id", "dt" => 5, "formatter" => function ($d, $row) {
                $editUrl = route_to("edit-post", $row["id"]); // Assuming you have a named route for editing
                return "<div class='btn-group'>
                <a href='" . $editUrl . "' class='btn btn-info btn-sm' style='min-width:80%;'>
                <i class='icon-copy dw dw-edit2'></i>
            </a>
            <a href='javascript:void(0);' class='btn btn-danger btn-sm deletePostBtn' data-id='" . $row["id"] . "' style='min-width:80%;'>
                <i class='icon-copy dw dw-delete-3'></i>
                </div>";
            }],
        ];
        $where = "author_id = " . CIAuth::id();

        return json_encode(
            SSP::complex($_GET, $dbDetails, $table, $primaryKey, $columns, $where)
        );
    }

    public function editPost($id)
    {
        $post = new Post();
        $category = new Category();

        // Assuming CIAuth is your authentication helper and it can provide the current user's ID
        $userId = CIAuth::id();  // Get the logged-in user's ID

        $data = [
            "pageTitle" => "Edit Post",
            "post" => $post->asObject()->find($id),
            // Fetch only the categories that were created by the logged-in user
            "categories" => $category->asObject()->where('author_id', $userId)->findAll(),
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
                    "expiration_date" => [
                        "rules" => "required|valid_date[Y-m-d]|check_date_is_future[expiration_date]",
                        "errors" => [
                            "required" => "Expiration date is required",
                            "valid_date" => "Provide a valid expiration date",
                            "check_date_is_future" => "Expiration date cannot be in the past",
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
                    "expiration_date" => [
                        "rules" => "required|valid_date[Y-m-d]|check_date_is_future[expiration_date]",
                        "errors" => [
                            "required" => "Expiration date is required",
                            "valid_date" => "Provide a valid expiration date",
                            "check_date_is_future" => "Expiration date cannot be in the past",
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
                            "content" => $request->getVar("content"),
                            "featured_image" => $filename,
                            "visibility" => $request->getVar("visibility"),
                            "expiration_date" => $request->getVar("expiration_date"), // Include expiration date
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
                        "content" => $request->getVar("content"),
                        "visibility" => $request->getVar("visibility"),
                        "expiration_date" => $request->getVar("expiration_date"), // Include expiration date
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