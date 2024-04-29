<div class="header">
    <div class="header-left">
        <div class="menu-icon bi bi-list"></div>
    </div>
    <div class="header-right">
        <div class="dashboard-setting user-notification">
            <div class="dropdown">
                <a class="dropdown-toggle no-arrow" href="javascript:;" data-toggle="right-sidebar">
                    <i class="dw dw-settings2"></i>
                </a>
            </div>
        </div>
        <div class="user-info-dropdown">
            <div class="dropdown">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <span class="user-icon">
                        <img src="<?= get_user()->picture == null ? '/images/users/default-avatar.png' : '/images/users/'.get_user()->picture?>" alt="" class="avatar-photo ci-avatar-photo" alt="" class="ci-avatar-photo" />
                    </span>
                    <span class="user-name ci-user-name"><?= get_user()->name?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                    <a class="dropdown-item" href="<?= route_to("admin.profile")?>"><i class="dw dw-user1"></i>
                        Profile</a>
                    <a class="dropdown-item" href="<?= route_to('admin.logout')?>"><i class="dw dw-logout"></i> Log
                        Out</a>
                </div>
            </div>
        </div>
    </div>
</div>