<?= $this->extend('backend/layout/pages-layout') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>All guarantees</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('home')?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        All guarantees
                    </li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <div class="dropdown">
                <a class="btn btn-primary" href="<?= route_to('new-post')?>">
                    Add new
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
                    <div class="pull-left">All Guarantees</div>
                    <div class="pull-right"></div>
                </div>
            </div>
            <div class="card-body">
                <table class="data-table table stripe hover nowrap dataTable no-footer dtr-inline collapsed"
                    id="posts-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Image</th>
                            <th scope="col">Title</th>
                            <th scope="col">Category</th>
                            <th scope="col">Exp. Date</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody> </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<?= $this->endSection()?>
<?= $this->section('stylesheets') ?>
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/responsive.bootstrap4.min.css">
<?= $this->endSection()?>
<?= $this->section('scripts') ?>
<script src="/backend/src/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.responsive.min.js"></script>
<script src="/backend/src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>
<script>
//retrieve posts
var posts_DT = $('#posts-table').DataTable({
    scrollCollapse: true,
    responsive: true,
    autoWidth: false,
    processing: true,
    serverSide: true,
    ajax: "<?= route_to('get-posts') ?>",
    dom: "1Bfrtip",
    language: {
        info: "",
        infoFiltered: ""
    },
    columnDefs: [{
        targets: [0,1,5],
        searchable: false,
        orderable: false,
      //  width: "5%",
        //className: "dt-body-center"
    }, {
        targets: [2,3,4],
        orderable: true,
        searchable: true
    }],
    order: [
        [4, 'asc']
    ],

    drawCallback: function(settings) {
        $(document).on('click', '.deletePostBtn', function(e) {
            e.preventDefault();
            var post_id = $(this).data('id');
            var url = "<?= route_to('delete-post') ?>";
            swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this post!',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonColor: '#d33',
                confirmButtonColor: '#556ee6',
                width: '400px',
                allowOutsideClick: false
            }).then(function(result) {
                if (result.value) {
                    $.getJSON(url, {
                        post_id: post_id
                    }, function(response) {
                        if (response.status == 1) {
                            posts_DT.ajax.reload(null, false);
                            toastr.success(response.msg);
                        } else {
                            toastr.error(response.msg);
                        }
                    });
                }
            });
        });
    }
});

$(window).resize($.debounce(250, function() {
    posts_DT.columns.adjust().responsive.recalc();
}));

(function($, sr) {
    var debounce = function(func, threshold, execAsap) {
        var timeout;

        return function debounced() {
            var obj = this,
                args = arguments;

            function delayed() {
                if (!execAsap)
                    func.apply(obj, args);
                timeout = null;
            };

            if (timeout)
                clearTimeout(timeout);
            else if (execAsap)
                func.apply(obj, args);

            timeout = setTimeout(delayed, threshold || 100);
        };
    };
    jQuery.debounce = debounce;
})(jQuery);
</script>
<?= $this->endSection()?>