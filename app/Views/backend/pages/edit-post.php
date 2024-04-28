<?= $this->extend('backend/layout/pages-layout') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Add post</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home')?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Edit post
                    </li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <a href="<?= route_to('all_posts')?>" class="btn btn-primary">View all posts</a>
        </div>
    </div>
</div>

<form action="<?= route_to('update-post')?>" method="post" autocomplete="off" enctype="multipart/form-data"
    id="updatePostForm">
    <input type="hidden" name="<?= csrf_token()?>" value="<?= csrf_hash()?>" class="ci-csrf-data">
    <input type="hidden" name="post_id" value="<?= $post->id?>">
    <div class="row">
        <div class="col-md-9">
            <div class="card card-box mb-2">
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">Post title</label>
                        <input type="text" name="title" class="form-control" placeholder="Enter post title" value="<?= $post->title?>">
                        <span class="text-danger error-text title_error"></span>
                    </div>
                    <div class="form-group">
                        <label for=""><b>Content</b></label>
                        <textarea name="content" id="" cols="30" rows="10" class="form-control"
                            placeholder="Type...."><?= $post->content?></textarea>
                        <span class="text-danger error-text content_error"></span>
                    </div>
                </div>
            </div>
            <div class="card card-box mb-2">
                <h5 class="card-header weight-500">SEO</h5>
                <div class="card-body">
                    <div class="form-group">
                        <label for=""><b>Post meta keywords</b><small>(Separated by comma)</small></label>
                        <input type="text" class="form-control" placeholder="Enter post meta keywords"
                            name="meta_keywords" value="<?= $post->meta_keywords?>">
                    </div>
                    <div class="form-group">
                        <label for=""><b>Post meta description</b></label>
                        <textarea name="meta_description" id="" cols="30" rows="5" class="form-control"
                            placeholder="Enter post meta description"><?= $post->meta_description?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-box mb-2">
                <h5 class="card-header weight-500">Publish</h5>
                <div class="card-body">
                    <div class="form-group">
                        <label for=""><b>Post category</b></label>
                        <select name="category" id="" class="custom-select form-control">
                            <?php foreach($categories as $category): ?>
                            <option value="<?= $category->id?>" <?= $category->id == $post->category_id ? 'selected': ''?>><?= $category->name?></option>
                            <?php endforeach?>
                        </select>
                        <span class="text-danger error-text category_error"></span>
                    </div>
                    <div class="form-group">
                        <label for=""><b>Post featured image</b></label>
                        <input type="file" name="featured_image" class="form-control-file form-control" height="auto" />
                        <span class="text-danger error-text featured_image_error"></span>
                    </div>
                    <div class="d-block mb-3" style="max-width: 250px;">
                        <img src="" alt="" class="img-thumbnail" id="image-previewer" data-ijabo-default-img="/images/posts/resized_<?= $post->featured_image?>">
                    </div>
                    <div class="form-group">
                        <label for=""><b>Tags</b></label>
                        <input type="text" name="tags" class="form-control" data-role="tagsinput" value="<?= $post->tags?>"/>
                        <span class="text-danger error-text tags_error"></span>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for=""><b>Visibility</b></label>
                        <div class="custom-control custom-radio mb-5">
                            <input type="radio" name="visibility" id="customRadio1" class="custom-control-input"
                                value="1" <?= $post->visibility == 1 ? 'checked' :''?>>
                            <label class="custom-control-label" for="customRadio1">Public</label>
                        </div>
                        <div class="custom-control custom-radio mb-5">
                            <input type="radio" name="visibility" id="customRadio2" class="custom-control-input"
                                value="0"<?= $post->visibility == 0 ? 'checked' :''?> >
                            <label class="custom-control-label" for="customRadio2">Private</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <button type="submit " class="btn btn-primary">Save changes</button>
    </div>
</form>



<?= $this->endSection()?>
<?= $this->section('stylesheets') ?>
<link rel="stylesheet" href="/backend/src/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css" />
<?= $this->endSection()?>
<?= $this->section('scripts') ?>
<script src="/backend/src/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js"></script>
<script>
$('input[type="file"][name="featured_image"]').ijaboViewer({
    preview: '#image-previewer',
    allowedExtensions: ['jpg', 'jpeg', 'png'],
    onInvalidType: function(message, element) {
        alert(message);
    },
});

$('#updatePostForm').submit(function(e) {
    e.preventDefault();
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
        beforeSend: function() {
            toastr.remove();
            $(form).find('span.error-text').text('');
        },
        success: function(response) {
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
        }
    });
});
</script>
<?= $this->endSection()?>