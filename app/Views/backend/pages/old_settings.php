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
                <a class="nav-link active text-blue" data-toggle="tab" href="#general_settings" role="tab"
                    aria-selected="true">General settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-blue" data-toggle="tab" href="#logo_favicon" role="tab"
                    aria-selected="false">Logo & Favicons</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-blue" data-toggle="tab" href="#social_media" role="tab"
                    aria-selected="false">Social media</a>
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
                                    <label for="">Blog Title</label>
                                    <input type="text" class="form-control" name="blog_title"
                                        placeholder="Enter blog title" value="<?= get_settings()->blog_name?>">
                                    <span class="text-danger error-text blog_title_error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Blog Email</label>
                                    <input type="text" class="form-control" name="blog_email"
                                        placeholder="Enter blog email" value="<?= get_settings()->blog_email?>">
                                    <span class="text-danger error-text blog_email_error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Blog Phone Number</label>
                                    <input type="text" class="form-control" name="blog_phone"
                                        placeholder="Enter blog phone number" value="<?= get_settings()->blog_phone?>">
                                    <span class="text-danger error-text blog_phone_error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Blog Meta Keywords</label>
                                    <input type="text" class="form-control" name="blog_meta_keywords"
                                        placeholder="Enter blog keywords"
                                        value="<?= get_settings()->blog_meta_keywords?>">
                                    <span class="text-danger error-text blog_meta_keywords_error"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="">Blog Meta Description</label>
                            <textarea class="form-control" name="blog_meta_description" id="" cols="4" rows="3"
                                placeholder="Enter blog meta description"
                                value="<?= get_settings()->blog_meta_description?>"></textarea>
                            <span class="text-danger error-text blog_meta_description_error"></span>
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
                            <h5>Set Blog Logo</h5>
                            <div class="mb-2 mt-1" style="max-width: 200px;">
                                <img src="" alt="" class="img-thumbnail" id="logo-image-preview"
                                data-ijabo-default-img="/images/blog/<?= get_settings()->blog_logo?>">
                            </div>
                            <form action = "<?= route_to('update-blog-logo')?>" method = "POST" enctype="multipart/form-data" id="changeBlogLogoForm">
                                    <input type="hidden" name="<?= csrf_token()?>" value="<?= csrf_hash()?>" class="ci_csrf_data">
                                <div class="mb-2">
                                    <input type="file" name="blog_logo" id="" class="form-control">
                                    <span class="text-danger error-text"></span>
                                </div>
                                <button type="submit" class="btn btn-primary">Upload Logo</button>
                            </form>
                            </div>
                         <div class="col-md-6">
                                <h5>Set blog favicon</h5>
                                <div class="mb-2 mt-1" style="max-width: 200px;">
                                    <img src="" alt="" class="img-thumbnail" id="favicon-image-preview" 
                                    data-ijabo-default-img="/images/blog/<?= get_settings()->blog_favicon ?>">
                                </div>
                                <form action="<?= route_to('update-blog-favicon')?>" method="POST" enctype="multipart/form-data" id="changeBlogFaviconForm">
                                    <input type="hidden" name="<?= csrf_token()?>" value="<?= csrf_hash()?>" class="ci_csrf_data">
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
            </div>
        </div>
    </div>
</div>

<?= $this->endSection()?>
<?= $this->section('scripts')?>
<script>
    $('#general_settings_form').on('submit', function (e) {
        e.preventDefault();
       //CSRF Hash
       var csrfName = $('.ci_csrf_data').attr('name');
       var csrfHash = $('.ci_csrf_data').val();
       var form = this;
       var formdata = new FormData(form);
         formdata.append(csrfName, csrfHash);

         $.ajax({
                url: $(form).attr('action'),
                method:$(form).attr('method'),
                data: formdata,
                processData: false,
                dataType: 'json',
                contentType: false,
                cache: false,
                beforeSend: function(){
                    toastr.remove();
                    $(form).find('span.error-text').text('');
                },
                success:function(response){
                    //update CSRF Hash
                    $('.ci_csrf_data').val(response.token);

                    if( $.isEmptyObject(response.error)){
                        if( response.status == 1){
                            toastr.success(response.msg);
                        }else{
                            toastr.error(response.msg);
                        }
                    }else{
                    $.each(response.error, function(prefix, val){
                        $(form).find('span.'+prefix+'_error').text(val);
                    });
                }
                },
         })
    });

    $('input[type="file"][name="blog_logo"]').ijaboViewer({
        preview:'#logo-image-preview',
        imageShape:'rectangular',
        allowedExtensions:['jpg','jpeg','png'],
        onErrorShape:function(message, element){
            alert(message);
        },
        onInvalidType:function(message, element){
            alert(message);
        },
        onSuccess:function(message, element){
        }
    });

    $('#changeBlogLogoForm').on('submit', function(e){
        e.preventDefault();
        var form = this;
        var formdata = new FormData(form);
        var csrfName = $('.ci_csrf_data').attr('name');
        var csrfHash = $('.ci_csrf_data').val();
        formdata.append(csrfName, csrfHash);

        var inputFileVal = $(form).find('input[type="file"][name="blog_logo"]').val();

        if(inputFileVal == ''){
            $(form).find('span.error-text').text('Please select an image file');
            return false;
        }else{
            $.ajax({
                url: $(form).attr('action'),
                method: $(form).attr('method'),
                data: formdata,
                processData: false,
                contentType: false,
                cache: false,
                datatype: 'json',
                beforeSend: function(){
                    toastr.remove();
                    $(form).find('span.error-text').text('');
                },
                success: function(response){
                    //update CSRF Hash
                    $('.ci_csrf_data').val(response.token);

                    if(response.status == 1){
                        toastr.success(response.msg);
                         $(form)[0].reset();
                    }else{
                         toastr.error(response.msg);
                     }
                }
            });
        }
        });

    $('input[type="file"][name="blog_favicon"]').ijaboViewer({
        preview:'#favicon-image-preview',
        imageShape:'square',
        allowedExtensions:['jpg','jpeg','png'],
        onErrorShape:function(message, element){
            alert(message);
        },
        onInvalidExtension:function(message, element){
            alert(message);
        },
        onSuccess:function(message, element){
        }
    });

    $('#changeBlogFaviconForm').on('submit', function(e){
        e.preventDefault();
        var form = this;
        var formdata = new FormData(form);
        var csrfName = $('.ci_csrf_data').attr('name');
        var csrfHash = $('.ci_csrf_data').val();
        formdata.append(csrfName, csrfHash);

        var inputFileVal = $(form).find('input[type="file"][name="blog_favicon"]').val();

        if(inputFileVal == ''){
            $(form).find('span.error-text').text('Please select an image file');
            return false;
        }else{
            $.ajax({
                url: $(form).attr('action'),
                method: $(form).attr('method'),
                data: formdata,
                processData: false,
                contentType: false,
                cache: false,
                datatype: 'json',
                beforeSend: function(){
                    toastr.remove();
                    $(form).find('span.error-text').text('');
                },
                success: function(response){
                    //update CSRF Hash
                    $('.ci_csrf_data').val(response.token);

                    if(response.status == 1){
                        toastr.success(response.msg);
                         $(form)[0].reset();
                    }else{
                         toastr.error(response.msg);
                     }
                }
            });
        }

    });

</script>
<?= $this->endSection()?>
