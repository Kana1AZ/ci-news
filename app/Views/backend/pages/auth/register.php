<?= $this->extend('backend/layout/register-layout') ?>
<?= $this->section('content') ?>

<div class="login-box bg-white box-shadow border-radius-10">
    <div class="login-title">
        <h2 class="text-center text-primary">Register</h2>
    </div>
    <?php $validation = \Config\Services::validation();?>
    <form action="<?= route_to('admin.register.handler')?>" method="POST">
        <?= csrf_field()?>
        <div class="input-group custom">
            <input type="text" class="form-control form-control-lg" placeholder="Username or Email" name="email"
                value="<?= set_value('email') ?>">
            <div class="input-group-append custom">
                <span class="input-group-text"><i class="icon-copy dw dw-user1"></i></span>
            </div>
        </div>
        <?php if($validation->getError('email')): ?>
        <div class="d-block text-danger" style="margin-top:-25px; margin-bottom:15px;">
            <?= $validation->getError('email') ?>
        </div>
        <?php endif;?>
        <div class="input-group custom">
            <input type="password" class="form-control form-control-lg" placeholder="Enter password" name="password"
                value="<?= set_value('password') ?>">
            <div class="input-group-append custom">
                <span class="input-group-text"><i class="dw dw-padlock1"></i></span>
            </div>
        </div>
        <?php if($validation->getError('password')): ?>
        <div class="d-block text-danger" style="margin-top:-25px; margin-bottom:15px;">
            <?= $validation->getError('password') ?>
        </div>
        <?php endif;?>
        <div class="input-group custom">
            <input type="password" class="form-control form-control-lg" placeholder="Confirm password" name="cpassword"
                value="<?= set_value('cpassword') ?>">
            <div class="input-group-append custom">
                <span class="input-group-text"><i class="dw dw-padlock1"></i></span>
            </div>
        </div>
        <?php if($validation->getError('cpassword')): ?>
        <div class="d-block text-danger" style="margin-top:-25px; margin-bottom:15px;">
            <?= $validation->getError('cpassword') ?>
        </div>
        <?php endif;?>
        <div class="row pb-30">
            <div class="col-6">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="customCheck1">
                    <label class="custom-control-label" for="customCheck1">Remember</label>
                </div>
            </div>
            <div class="col-6">
                <div class="forgot-password">
                    <a href="<?= route_to('admin.forgot.form')?>">Forgot Password</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="input-group mb-0">
                    <input class="btn btn-primary btn-lg btn-block" type="submit" value="Sign In">
                </div>
            </div>
        </div>
    </form>
</div>

<?= $this->endSection()?>