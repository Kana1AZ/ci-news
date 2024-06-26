<div class="left-side-bar">
    <div class="brand-logo">
        <a href="<?= route_to('home');?>">
            <img src="/images/blog/<?= get_settings()->blog_logo?>" alt="" class="dark-logo" />
            <img src="/images/blog/<?= get_settings()->blog_logo?>" alt="" class="light-logo" />
        </a>
        <div class="close-sidebar" data-toggle="left-sidebar-close">
            <i class="ion-close-round"></i>
        </div>
    </div>
    <div class="menu-block customscroll">
        <div class="sidebar-menu">
            <ul id="accordion-menu">
                <li>
                    <a href="<?= route_to('home');?>"
                        class="dropdown-toggle no-arrow <?= current_route_name() == 'home' ? 'active' : '' ?>">
                        <span class="micon dw dw-home"></span><span class="mtext">Home</span>
                    </a>
                </li>
                <li>
                    <a href="<?= route_to('categories')?>"
                        class="dropdown-toggle no-arrow <?= current_route_name() == 'categories' ? 'active' : '' ?>">
                        <span class="micon dw dw-list"></span><span class="mtext">Categories</span>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle"
                        <?= current_route_name() == 'all-posts' || current_route_name() == 'new-post' ? 'active' : '' ?>>
                        <span class="micon dw dw-newspaper"></span><span class="mtext">Guarantees</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="<?= route_to('all-posts')?>"
                                class="<?= current_route_name() == 'all-posts' ? 'active' : '' ?>">
                                </i> All</a>
                        </li>
                        <li><a href="<?= route_to('new-post')?>"
                                class="<?= current_route_name() == 'new-post' ? 'active' : '' ?>">
                                </i> Add new</a>
                        </li>
                    </ul>
                </li>

                <li>
                    <div class="dropdown-divider"></div>
                </li>
                <li>
                    <div class="sidebar-small-cap">Settings</div>
                </li>

                <li>
                    <a href="<?= route_to("profile");?>"
                        class="dropdown-toggle no-arrow <?= current_route_name() == 'profile' ? 'active' : '' ?>">
                        <span class="micon dw dw-user"></span>
                        <span class="mtext">Profile
                        </span>
                    </a>
                </li>

                <?php if (get_user()->role === 'admin'): ?>
                <li>
                    <a href="<?= route_to('settings')?>"
                        class="dropdown-toggle no-arrow <?= current_route_name() == 'settings' ? 'active' : '' ?>">
                        <span class="micon dw dw-settings2"></span>
                        <span class="mtext">Admin Panel</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>