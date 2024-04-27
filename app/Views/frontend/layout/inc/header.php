<header class="navigation">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light px-0">
            <a class="navbar-brand order-1 py-0" href="index.html">
                <img loading="prelaod" decoding="async" class="img-fluid" src="/images/blog/<?= get_settings()->blog_logo ?>"
                    alt="<?= get_settings()->blog_name?>" style="max-width:170px">
            </a>
            <div class="navbar-actions order-3 ml-0 ml-md-4">
                <button aria-label="navbar toggler" class="navbar-toggler border-0" type="button" data-toggle="collapse"
                    data-target="#navigation"> <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <form action="#!" class="search order-lg-3 order-md-2 order-3 ml-auto">
                <input id="search-query" name="s" type="search" placeholder="Search..." autocomplete="off">
            </form>
            <div class="collapse navbar-collapse text-center order-lg-2 order-4" id="navigation">
                <ul class="navbar-nav mx-auto mt-3 mt-lg-0">
                    <li class="nav-item"> <a class="nav-link" href="">Home</a>
                    </li>
                    <?php foreach(get_categories() as $category):?>
                    <li class="nav-item "> <a class="nav-link" href="#" role="button"
                             aria-haspopup="true" >
                            <?= $category->name?>
                        </a>
                    </li>
                    <?php endforeach;?>

                    <li class="nav-item"> <a class="nav-link" href="contact.html">Contact</a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</header>