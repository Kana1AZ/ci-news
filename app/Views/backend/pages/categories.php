<?= $this->extend('backend/layout/pages-layout') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Categories</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('home')?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Categories
                    </li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <div class="dropdown">
                <a class="btn btn-primary" id="add-category-btn" style="color: white">
                    Add new category
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card card-box">
            <div class="card-header">
                <div class="clearfix">
                    <div class="pull-left">
                        Categories
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless table-hover table-striped" id="categories-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Category</th>
                            <th scope="col">N. of items</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('modals/category-modal.php')?>
<?php include('modals/edit-category-modal.php')?>

<?= $this->endSection()?>
<?= $this->section('stylesheets')?>
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="/extra-assets/jquery-ui-1.13.2/jquery-ui.min.css">
<link rel="stylesheet" href="/extra-assets/jquery-ui-1.13.2/jquery-ui.structure.min.css">
<link rel="stylesheet" href="/extra-assets/jquery-ui-1.13.2/jquery-ui.theme.min.css">
<?= $this->endSection()?>
<?= $this->section('scripts')?>
<script src="/backend/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.responsive.min.js"></script>
<script src="/backend/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>
<script src="/extra-assets/jquery-ui-1.13.2/jquery-ui.min.js"></script>
<script>

$(document).on('click', '#add-category-btn', function(e) {
    e.preventDefault();
    var modal = $('body').find('div#category-modal');
    var modal_title = 'Add category';
    var modal_btn_text = 'Add';
    modal.find('.modal-title').text(modal_title);
    modal.find('.modal-footer button.action').text(modal_btn_text);
    modal.find('input.error-text').html('');
    modal.find('input[type="text"]').val('');
    modal.modal('show');
});

$(document).ready(function() {
    console.log("Document ready!");
    // Initialize the DataTable
    var categories_DT = $('#categories-table').DataTable({
        scrollCollapse: true,
        responsive: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: "<?= route_to('get-categories') ?>",
        dom: "Brtip",
        language: {
            info: "",
            infoFiltered: ""
        },
        columnDefs: [{
                targets: [0], // Assuming column 0 is the index column
                searchable: false,
                orderable: false,
                className: "dt-body-center"
            },
            {
                targets: '_all',
                orderable: true,
                searchable: true
            }
        ],
        order: [
            [2, 'asc']
        ]
    });

    // Event bindings for edit and delete buttons
    $(document).on('click', '.editCategoryBtn', function(e) {
        e.preventDefault();
        var category_id = $(this).data('id');
        var url = '<?= route_to('get-category') ?>';
        $.get(url, {
            category_id: category_id
        }, function(response) {
            var modal_title = 'Edit category';
            var modal_btn_text = 'Update';
            var modal = $('body').find('div#edit-category-modal');
            modal.find('form').find('input[type="hidden"][name="category_id"]').val(
                category_id);
            modal.find('.modal-title').html(modal_title);
            modal.find('.modal-footer button.action').html(modal_btn_text);
            modal.find('input[type="text"]').val(response.data.name);
            modal.find('span.error-text').html('');
            modal.modal('show');
        }, 'json');
    });

    $(document).on('click', '.deleteCategoryBtn', function(e) {
        e.preventDefault();
        var category_id = $(this).data('id');
        var url = "<?= route_to('delete-category') ?>";
        swal.fire({
            title: 'Are you sure?',
            text: 'You are about to delete this category',
            showCloseButton: true,
            showCancelButton: true,
            cancelButtonText: 'No, cancel',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            width: '30%',
        }).then(function(result) {
            if (result.value) {
                $.get(url, {
                    category_id: category_id
                }, function(response) {
                    if (response.status == 1) {
                        toastr.success(response.msg);
                        categories_DT.ajax.reload(null, false);
                    } else {
                        toastr.error(response.msg);
                    }
                }, 'json');
            }
        });
    });

    $(document).on('submit', '#update_category_form', function(e) {
        e.preventDefault();
        console.log("Form submission intercepted");
        var form = $(this);
        var formData = new FormData(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status == 1) {
                    toastr.success(response.msg);
                    $('#edit-category-modal').modal('hide');
                    categories_DT.ajax.reload(null, false);
                } else {
                    toastr.error(response.msg);
                }
                // Update CSRF token
                if (response.csrfHash) {
                    $('.ci_csrf_data').val(response.csrfHash);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error details:", textStatus,
                    errorThrown);
                toastr.error('Error occurred while updating the category.');
            }
        });
    });

    $('#add_category_form').on('submit', function(e) {
        e.preventDefault();
        //CSRF HASH
        var csrfName = $(".ci_csrf_data").attr('name');
        var csrfHash = $(".ci_csrf_data").val();
        var form = this;
        var modal = $('body').find('div#category-modal');
        var formData = new FormData(form);
        formData.append(csrfName, csrfHash);

        $.ajax({
            url: $(form).attr('action'),
            method: $(form).attr('method'),
            data: formData,
            processData: false,
            dataType: 'json',
            contentType: false,
            cache: false,
            beforeSend: function() {
                toastr.remove();
                $(form).find('span.error-text').text('');
            },
            success: function(response) {
                //update CSRF HASH
                $('.ci_csrf_data').val(response.token);

                if ($.isEmptyObject(response.error)) {
                    if (response.status == 1) {
                        form.reset();
                        modal.modal('hide');
                        toastr.success(response.msg);
                        categories_DT.ajax.reload(null,
                        false); //update table withouut refreshing
                    } else {
                        toastr.error(response.msg);
                    }
                } else {
                    $.each(response.error, function(prefix, val) {
                        $(form).find('span.' + prefix + '_error').text(val);
                    });
                }
            }
        });
    });
});


//update category
</script>
<?= $this->endSection()?>