<div class="header">
    <div class="header-left">
        <div class="menu-icon bi bi-list"></div>
    </div>
    <div class="header-right">
        <div class="user-info-dropdown">
            <div class="dropdown">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <span class="user-icon">
                        <img src="<?= get_user()->picture == null ? '/images/users/default-avatar.png' : '/images/users/'.get_user()->picture?>"
                            alt="" class="avatar-photo ci-avatar-photo" alt="" class="ci-avatar-photo" />
                    </span>
                    <span class="user-name ci-user-name"><?= get_user()->name?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                    <a class="dropdown-item" href="<?= route_to("profile")?>"><i class="dw dw-user1"></i>
                        Profile</a>
                    <a class="dropdown-item" href="<?= route_to('logout')?>"><i class="dw dw-logout"></i> Log
                        Out</a>
                </div>
            </div>
        </div>
    </div>
</div>