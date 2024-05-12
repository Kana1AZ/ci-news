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
    protected $validation;
    protected $request;
    protected $id;


    public function __construct()
    {
        require_once APPPATH . "ThirdParty/ssp.php";
        $this->db = db_connect();
        $this->request = \Config\Services::request();
        $this->validation = \Config\Services::validation();
        $this->id = CIAuth::id();
    }

    public function index()
    {
        $postModel = new \App\Models\Post();
        $userModel = new \App\Models\User();
        $user = $userModel->find($this->id);
        $user = is_array($user) ? (object) $user : $user;

        $totalGuarantees = $postModel->where('author_id', $this->id)->countAllResults();
        $activeGuarantees = $postModel->where('author_id', $this->id)
                                       ->where('expiration_date >', date('Y-m-d'))
                                       ->countAllResults();

        $expiredGuarantees = $postModel->where('author_id', $this->id)
                                        ->where('expiration_date <=', date('Y-m-d'))
                                        ->countAllResults();

        $soonToExpireGuarantees = $postModel->asObject()->where('author_id', $this->id)
                                   ->where('expiration_date >', date('Y-m-d'))
                                   ->orderBy('expiration_date', 'asc')
                                   ->findAll(5);

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
            ->route("login.form")
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


        if ($this->request->isAJAX()) {
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
                        $this->id .
                        "]",
                    "errors" => [
                        "required" => "Username is required",
                        "min_length" =>
                            "Username must have a minimun 4 characters",
                        "is_unique" => "Username is already taken!",
                    ],
                ],
            ]);

            if ($this->validation->run() == false) {
                $errors = $this->validation->getErrors();
                return json_encode(["status" => 0, "error" => $errors]);
            } else {
                $user = new User();
                $update = $user
                    ->where("id", $this->id)
                    ->set([
                        "name" => $this->request->getVar("name"),
                        "username" => $this->request->getVar("username"),
                        "bio" => $this->request->getVar("bio"),
                    ])
                    ->update();

                if ($update) {
                    $user_info = $user->find($this->id);
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
        $user = new User();
        $user_info = $user
            ->asObject()
            ->where("id", $this->id)
            ->first();

        $path = "images/users/";
        $file = $this->request->getFile("user_profile_file");
        $old_picture = $user_info->picture;
        $new_filename = "UIMG_" . $this->id . $file->getRandomName();

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
        if ($this->request->isAJAX()) {
            $user = new User();
            $user_info = $user
                ->asObject()
                ->where("id", $this->id)
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

            if ($this->validation->run() === false) {
                $errors = $this->validation->getErrors();
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
                            $this->request->getVar("new_password")
                        ),
                    ])
                    ->update();

                //send email notification
                $mail_data = [
                    "user" => $user_info,
                    "new_password" => $this->request->getVar("new_password"),
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
            "userId" => $this->id,
        ];
        return view("backend/pages/categories", $data);
    }

    public function addCategory()
    {
        if ($this->request->isAJAX()) {
            $this->validate([
                "category_name" => [
                    "rules" => "required|is_unique[categories.name]",
                    "errors" => [
                        "required" => "Category name is required",
                        "is_unique" => "Category name is already taken!",
                    ],
                ],
            ]);

            if ($this->validation->run() === false) {
                $errors = $this->validation->getErrors();
                return $this->response->setJSON([
                    "status" => 0,
                    "token" => csrf_hash(),
                    "error" => $errors,
                ]);
            } else {
                // return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'Category has been successfully added!']);
                $category = new Category();
                log_message('debug', 'Request Data: ' . print_r($this->request->getPost(), true));
                log_message('debug', 'Current User ID: ' . $this->id);

                $save = $category->save([
                    "name" => $this->request->getVar("category_name"),
                    "author_id" => $this->id,
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
    $db = db_connect();
    $currentUserId = $this->id;

    // DataTables parameters
    $start = $this->request->getGet('start') ?? 0;
    $length = $this->request->getGet('length') ?? 10;
    $draw = $this->request->getGet('draw') ?? 1;
    $order = $this->request->getGet('order');

    // Columns array must exactly match the order in the DataTables initialization
    $columns = ['id', 'name', 'post_count'];
    $orderColumnIndex = isset($order[0]['column']) ? $order[0]['column'] : 1; // Default to 'name'
    $orderDir = isset($order[0]['dir']) ? $order[0]['dir'] : 'asc';

    // Check if the column index is for post count and adjust query
    if (isset($columns[$orderColumnIndex]) && $columns[$orderColumnIndex] === 'post_count') {
        // Special handling for ordering by post count
        $query = $db->table('categories')
                    ->select('categories.id, categories.name, COUNT(posts.id) as post_count')
                    ->join('posts', 'posts.category_id = categories.id', 'left')
                    ->where('categories.author_id', $currentUserId)
                    ->groupBy('categories.id')
                    ->orderBy('post_count', $orderDir);
    } else {
        // Standard ordering
        $query = $db->table('categories')
                    ->select('id, name')
                    ->where('author_id', $currentUserId)
                    ->orderBy($columns[$orderColumnIndex], $orderDir);
    }

    $query = $query->limit($length, $start);
    $data = $query->get()->getResultArray();
    $totalRecords = $db->table('categories')
                       ->where('author_id', $currentUserId)
                       ->countAllResults();

    // Prepare the data for DataTables
    $result = [
        'draw' => intval($draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => []
    ];
    foreach ($data as $index => $row) {
        $result['data'][] = [
            $start + $index + 1,
            $row['name'],
            $row['post_count'] ?? $this->getPostCount($row['id'], $this->id),
            $this->getActionButtons($row['id'])
        ];
    }

    return $this->response->setJSON($result);
}
    
    private function getPostCount($categoryId, $userId) {
        $db = db_connect();
        return $db->table('posts')
                  ->where('category_id', $categoryId)
                  ->where('author_id', $userId)  // Filter posts by user ID as well
                  ->countAllResults();
    }
    
    private function getActionButtons($categoryId) {
        return "<div class='btn-group'>
            <a href='javascript:void(0);' class='btn btn-info btn-sm editCategoryBtn' data-id='{$categoryId} style='min-width:80%;'><i class='icon-copy dw dw-edit2' ></i></a>
            <a href='javascript:void(0);' class='btn btn-danger btn-sm deleteCategoryBtn' data-id='{$categoryId} style='min-width:80%;'><i class='icon-copy dw dw-delete-3'></i></a>
        </div>";
    }
    

    //EDIT CATEGORY BUTTON
    public function getCategory()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getVar("category_id");
            $category = new Category();
            $category_data = $category->find($id);
            return $this->response->setJSON(["data" => $category_data]);
        }
    }

    public function updateCategory()
    {
        $id = $this->request->getVar("category_id");
        log_message('debug', $this->id);

        if ($this->request->isAJAX()) {

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

            if ($this->validation->run() === false) {
                $errors = $this->validation->getErrors();
                return $this->response->setJSON([
                    "status" => 0,
                    "token" => csrf_hash(),
                    "error" => $errors,
                ]);
            } else {
                $category = new Category();
                $update = $category
                    ->where("id", $this->request->getVar("category_id"))
                    ->set(["name" => $this->request->getVar("category_name")])
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
        $id = $this->request->getVar("category_id");
        $category = new Category();

        if ($this->request->isAJAX()) {

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

        $data = [
            "pageTitle" => "Add Post",
            "categories" => $category->asObject()->where('author_id', $this->id)->findAll(),  // Only fetch categories created by the logged-in user.
        ];

        return view("backend/pages/new-post", $data);
    }

    public function createPost()
    {
        if ($this->request->isAJAX()) {

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

            if ($this->validation->run() === false) {
                $errors = $this->validation->getErrors();
                return $this->response->setJSON([
                    "status" => 0,
                    "token" => csrf_hash(),
                    "error" => $errors,
                ]);
            } else {
                $path = "images/posts/";
                $file = $this->request->getFile("featured_image");
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
                        "author_id" => $this->id,
                        "category_id" => $this->request->getVar("category"),
                        "title" => $this->request->getVar("title"),
                        "content" => $this->request->getVar("content"),
                        "featured_image" => $filename,

                        "expiration_date" => $this->request->getVar("expiration_date"), // Include expiration date
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
        $db = db_connect();  // Establish database connection
    
        // Retrieve DataTables parameters
        $start = $this->request->getGet('start') ?? 0;
        $length = $this->request->getGet('length') ?? 10;
        $draw = $this->request->getGet('draw') ?? 1;
        $order = $this->request->getGet('order');
        $search = $this->request->getGet('search')['value'] ?? '';  // Search value
    
        $orderColumn = $order[0]['column'];  // Column index
        $orderDirection = $order[0]['dir'];  // Direction 'asc' or 'desc'
    
        // Mapping DataTables columns index to database columns
        $columns = ['id', 'featured_image', 'title', 'category_name', 'expiration_date', 'actions'];
        $orderColumnName = $columns[$orderColumn];
    
        // Prepare query
        $query = $db->table('posts')
                    ->select('posts.id, posts.title, posts.expiration_date, categories.name as category_name, posts.featured_image')
                    ->join('categories', 'posts.category_id = categories.id', 'left')
                    ->where('posts.author_id', $this->id) ; // Filter by author_id
    
        // Search functionality (if applicable)
        if (!empty($search)) {
            $query->groupStart()  // Start grouping for OR conditions
            ->like('posts.title', $search)
            ->orLike('categories.name', $search)
            ->groupEnd();  // End grouping
        }
    
        // Adding order by condition
        if ($orderColumnName == 'category_name') {
            $query->orderBy("categories.name", $orderDirection);
        } else if ($orderColumnName == 'title' || $orderColumnName == 'expiration_date') {
            $query->orderBy("posts." . $orderColumnName, $orderDirection);
        }
    
        // Execute the query with limit and offset
        $data = $query->limit($length, $start)->get()->getResult();
    
        // Count filtered records and total records
        $filteredCount = $query->countAllResults(false);  // Count results after filtering but without the limit
        $totalCount = $db->table('posts')->where('author_id', $this->id)->countAllResults();  // Total results without any filtering
    
        // Prepare data for DataTables
        $result = [
            'draw' => intval($draw),
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => []
        ];
    
        $counter = $start + 1; // Start counter from 1 on each page
        foreach ($data as $post) {
            $expirationDate = $post->expiration_date;
            $status = strtotime($expirationDate) > time() ? 'Active' : 'Expired';
            $color = $status === 'Expired' ? 'red' : 'green';
            $formattedDate = "<span style='color: {$color};'>{$expirationDate} ({$status})</span>";
    
            $result['data'][] = [
                $counter++, // Increment the counter for each row
                "<img src='/images/posts/thumb_{$post->featured_image}' class='img-thumbnail' style='max-width: 70px'>",
                $post->title,
                $post->category_name,
                $formattedDate,
                $this->getPostActionButtons($post->id)
            ];
        }
    
        return $this->response->setJSON($result);
    }
    
    private function getPostActionButtons($postId) {
        $editUrl = route_to("edit-post", $postId); // Assuming you have a named route for editing
        return "<div class='btn-group'>
            <a href='" . $editUrl . "' class='btn btn-info btn-sm' style='min-width:80%;'>
                <i class='icon-copy dw dw-edit2'></i> Edit
            </a>
            <a href='javascript:void(0);' class='btn btn-danger btn-sm deletePostBtn' data-id='" . $postId . "' style='min-width:80%;'>
                <i class='icon-copy dw dw-delete-3'></i> Delete
            </a>
        </div>";
    }
    
    

    public function editPost($id)
    {
        $post = new Post();
        $category = new Category();

        $data = [
            "pageTitle" => "Edit Post",
            "post" => $post->asObject()->find($id),
            // Fetch only the categories that were created by the logged-in user
            "categories" => $category->asObject()->where('author_id', $this->id)->findAll(),
        ];
        return view("backend/pages/edit-post", $data);
    }

    public function updatePost()
    {
        if ($this->request->isAJAX()) {
            $post_id = $this->request->getVar("post_id");
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

            if ($this->validation->run() === false) {
                $errors = $this->validation->getErrors();
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
                    $file = $this->request->getFile("featured_image");
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
                            "author_id" => $this->id,
                            "category_id" => $this->request->getVar("category"),
                            "title" => $this->request->getVar("title"),
                            "content" => $this->request->getVar("content"),
                            "featured_image" => $filename,
                            "expiration_date" => $this->request->getVar("expiration_date"), // Include expiration date
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
                        "author_id" => $this->id,
                        "category_id" => $this->request->getVar("category"),
                        "title" => $this->request->getVar("title"),
                        "content" => $this->request->getVar("content"),
                        "visibility" => $this->request->getVar("visibility"),
                        "expiration_date" => $this->request->getVar("expiration_date"), // Include expiration date
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
        if ($this->request->isAJAX()) {
            $post_id = $this->request->getVar("post_id");
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