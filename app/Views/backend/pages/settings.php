<?= $this->extend('backend/layout/pages-layout') ?>
<?= $this->section('content') ?>
<div class="page-header">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="title">
                <h4>Settings</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home')?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Settings
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="pd-20 card-box mb-4">
    <div class="tab">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active text-blue" data-toggle="tab" href="#general" role="tab"
                    aria-selected="true">General settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-blue" data-toggle="tab" href="#logo_favicon" role="tab"
                    aria-selected="false">Logo & Favicons</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-blue" data-toggle="tab" href="#user_management" role="tab"
                    aria-selected="false">User Management</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <div class="pd-20">
                    <form action="<?= route_to('update-general-settings')?>" method="POST" id="general_settings_form">
                        <input type="hidden" name="<?= csrf_token()?>" value="<?= csrf_hash()?>" class="ci_csrf_data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Website Title</label>
                                    <input type="text" class="form-control" name="blog_title"
                                        placeholder="Enter blog title" value="<?= get_settings()->blog_name?>">
                                    <span class="text-danger error-text blog_title_error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Contact Email</label>
                                    <input type="text" class="form-control" name="blog_email"
                                        placeholder="Enter blog email" value="<?= get_settings()->blog_email?>">
                                    <span class="text-danger error-text blog_email_error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Contact Phone Number</label>
                                    <input type="text" class="form-control" name="blog_phone"
                                        placeholder="Enter blog phone number" value="<?= get_settings()->blog_phone?>">
                                    <span class="text-danger error-text blog_phone_error"></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class=" tab-pane fade" id="logo_favicon" role="tabpanel">
                <div class="pd-20">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Set website Logo</h5>
                            <div class="mb-2 mt-1" style="max-width: 200px;">
                                <img src="" alt="" class="img-thumbnail" id="logo-image-preview"
                                    data-ijabo-default-img="/images/blog/<?= get_settings()->blog_logo?>">
                            </div>
                            <form action="<?= route_to('update-blog-logo')?>" method="POST"
                                enctype="multipart/form-data" id="changeBlogLogoForm">
                                <input type="hidden" name="<?= csrf_token()?>" value="<?= csrf_hash()?>"
                                    class="ci_csrf_data">
                                <div class="mb-2">
                                    <input type="file" name="blog_logo" id="" class="form-control">
                                    <span class="text-danger error-text"></span>
                                </div>
                                <button type="submit" class="btn btn-primary">Upload Logo</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <h5>Set website favicon</h5>
                            <div class="mb-2 mt-1" style="max-width: 200px;">
                                <img src="" alt="" class="img-thumbnail" id="favicon-image-preview"
                                    data-ijabo-default-img="/images/blog/<?= get_settings()->blog_favicon ?>">
                            </div>
                            <form action="<?= route_to('update-blog-favicon')?>" method="POST"
                                enctype="multipart/form-data" id="changeBlogFaviconForm">
                                <input type="hidden" name="<?= csrf_token()?>" value="<?= csrf_hash()?>"
                                    class="ci_csrf_data">
                                <div class="mb-2">
                                    <input type="file" name="blog_favicon" id="" class="form-control">
                                    <span class="text-danger error-text"></span>
                                </div>
                                <button type="submit" class="btn btn-primary">Change Favicon</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="user_management" role="tabpanel">
                <div class="pd-20">
                    <h5>All Users</h5>
                    <table class="data-table table stripe hover nowrap dataTable no-footer dtr-inline collapsed"
                        id="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- User rows will be populated by DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('modals/edit-user-modal.php')?>

<?= $this->endSection()?>
<?= $this->section('scripts')?>

<script src="/backend/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.responsive.min.js"></script>
<script src="/backend/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>


<script>
$('#general_settings_form').on('submit', function(e) {
    e.preventDefault();
    //CSRF Hash
    var csrfName = $('.ci_csrf_data').attr('name');
    var csrfHash = $('.ci_csrf_data').val();
    var form = this;
    var formdata = new FormData(form);
    formdata.append(csrfName, csrfHash);

    $.ajax({
        url: $(form).attr('action'),
        method: $(form).attr('method'),
        data: formdata,
        processData: false,
        dataType: 'json',
        contentType: false,
        cache: false,
        beforeSend: function() {
            toastr.remove();
            $(form).find('span.error-text').text('');
        },
        success: function(response) {
            //update CSRF Hash
            $('.ci_csrf_data').val(response.token);

            if ($.isEmptyObject(response.error)) {
                if (response.status == 1) {
                    toastr.success(response.msg);
                } else {
                    toastr.error(response.msg);
                }
            } else {
                $.each(response.error, function(prefix, val) {
                    $(form).find('span.' + prefix + '_error').text(val);
                });
            }
        },
    })
});

$('input[type="file"][name="blog_logo"]').ijaboViewer({
    preview: '#logo-image-preview',
    imageShape: 'rectangular',
    allowedExtensions: ['jpg', 'jpeg', 'png'],
    onErrorShape: function(message, element) {
        alert(message);
    },
    onInvalidType: function(message, element) {
        alert(message);
    },
    onSuccess: function(message, element) {}
});

$('#changeBlogLogoForm').on('submit', function(e) {
    e.preventDefault();
    var form = this;
    var formdata = new FormData(form);
    var csrfName = $('.ci_csrf_data').attr('name');
    var csrfHash = $('.ci_csrf_data').val();
    formdata.append(csrfName, csrfHash);

    var inputFileVal = $(form).find('input[type="file"][name="blog_logo"]').val();

    if (inputFileVal == '') {
        $(form).find('span.error-text').text('Please select an image file');
        return false;
    } else {
        $.ajax({
            url: $(form).attr('action'),
            method: $(form).attr('method'),
            data: formdata,
            processData: false,
            contentType: false,
            cache: false,
            datatype: 'json',
            beforeSend: function() {
                toastr.remove();
                $(form).find('span.error-text').text('');
            },
            success: function(response) {
                //update CSRF Hash
                $('.ci_csrf_data').val(response.token);

                if (response.status == 1) {
                    toastr.success(response.msg);
                    $(form)[0].reset();
                } else {
                    toastr.error(response.msg);
                }
            }
        });
    }
});

$('input[type="file"][name="blog_favicon"]').ijaboViewer({
    preview: '#favicon-image-preview',
    imageShape: 'square',
    allowedExtensions: ['jpg', 'jpeg', 'png'],
    onErrorShape: function(message, element) {
        alert(message);
    },
    onInvalidExtension: function(message, element) {
        alert(message);
    },
    onSuccess: function(message, element) {}
});

$('#changeBlogFaviconForm').on('submit', function(e) {
    e.preventDefault();
    var form = this;
    var formdata = new FormData(form);
    var csrfName = $('.ci_csrf_data').attr('name');
    var csrfHash = $('.ci_csrf_data').val();
    formdata.append(csrfName, csrfHash);

    var inputFileVal = $(form).find('input[type="file"][name="blog_favicon"]').val();

    if (inputFileVal == '') {
        $(form).find('span.error-text').text('Please select an image file');
        return false;
    } else {
        $.ajax({
            url: $(form).attr('action'),
            method: $(form).attr('method'),
            data: formdata,
            processData: false,
            contentType: false,
            cache: false,
            datatype: 'json',
            beforeSend: function() {
                toastr.remove();
                $(form).find('span.error-text').text('');
            },
            success: function(response) {
                //update CSRF Hash
                $('.ci_csrf_data').val(response.token);

                if (response.status == 1) {
                    toastr.success(response.msg);
                    $(form)[0].reset();
                } else {
                    toastr.error(response.msg);
                }
            }
        });
    }

});


$('#users-table').DataTable({
    scrollCollapse: true,
    responsive: true,
    autoWidth: false,
    processing: false,
    serverSide: true,
    searching: false,
    sortable: false,
    render: function(data, type, row) {
        return `<button class='btn btn-info btn-sm editUser' data-id='${row.id}'>Edit</button>
                <button class='btn btn-danger btn-sm deleteUser' data-id='${row.id}'>Delete</button>`;
    },
    dom: "IBfrtip",
    language: {
        info: "",
        infoFiltered: ""
    },
    ajax: {
        url: '<?= route_to('get-users') ?>',
        error: function(xhr, error, code) {
            console.log(xhr);
            console.log(code);
        }
    },
    columns: [{
            data: 'id',
            orderable: true,
            searchable: false
        },
        {
            data: 'name',
            orderable: false,
            searchable: false
        },
        {
            data: 'email',
            orderable: false,
            searchable: false
        },
        {
            data: 'role',
            orderable: false,
            searchable: false
        },
        {
            data: 'actions',
            orderable: false,
            searchable: false
        }
    ]
});

$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
    if ($(e.target).attr("href") === "#user_management") {
        $('#users-table').css('width', '100%').DataTable().responsive.recalc().draw();
    }
});

$(document).ready(function() {
    // Handle Edit button click
    $('#users-table').on('click', '.editUser', function() {
        var userId = $(this).data('id');
        // You can now use this userId to fetch user details and show them in a form for editing
        //alert('Edit user with ID: ' + userId);
        // Optionally, open a modal or redirect to an edit page with the user's data
    });

    // Handle Delete button click
    $('#users-table').on('click', '.deleteUser', function() {
        var userId = $(this).data('id');
        if (confirm('Are you sure you want to delete this user?')) {
            // Perform AJAX request to delete the user
            $.ajax({
                url: '<?= route_to('delete-user') ?>', // Your route to the deletion logic
                type: 'POST',
                data: {
                    id: userId
                },
                success: function(response) {
                    // Refresh DataTable or remove the row visually
                    $('#users-table').DataTable().ajax.reload(); // Reload the data
                },
                error: function() {
                    alert('Error deleting user.');
                }
            });
        }
    });
});


// Assuming you have jQuery loaded
$(document).on('click', '.editUser', function() {
    var userId = $(this).data('id'); // Make sure 'data-id' is correctly set on each 'Edit' button
    // Fetch user details from server
    $.ajax({
        url: '<?= route_to('get-user-details') ?>', // Backend URL to fetch user details
        type: 'GET',
        data: {
            id: userId
        },
        dataType: 'json',
        success: function(data) {
            $('#editUserId').val(data.id);
            $('#editName').val(data.name);
            $('#editEmail').val(data.email);
            $('#editRole').val(data.role);
            $('#editUserModal').modal('show');
        },
        error: function() {
            alert('Error loading user data.');
        }
    });
});

function submitEditUser() {
    var formData = $('#editUserForm').serialize(); // Serialize the form data.
    $.ajax({
        url: '<?= route_to('update-user-details') ?>', // Backend URL to update user details
        type: 'POST',
        data: formData,
        success: function(response) {
            $('#editUserModal').modal('hide');
            // Reload or refresh the data table or part of the page to show changes
            $('#users-table').DataTable().ajax.reload(); // Or use DataTables API to reload
        },
        error: function() {
            alert('Error updating user data.');
        }
    });
}
</script>
<?= $this->endSection()?>