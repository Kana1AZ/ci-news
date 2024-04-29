<?= $this->extend('backend/layout/pages-layout') ?>
<?= $this->section('content') ?>
<div class="xs-pd-20-10 pd-ltr-20">
    <div class="title pb-20">
        <h2 class="h3 mb-0">Guarantees Overview</h2>
    </div>

    <div class="row pb-10">
        <div class="col-lg-4 col-md-6 mb-20">
            <div class="card-box height-100-p widget-style3">
                <div class="d-flex flex-wrap">
                    <div class="widget-data">
                        <div class="weight-700 font-24 text-dark"><?= $totalGuarantees?></div>
                        <div class="font-14 text-secondary weight-500">
                            Guarantees posted
                        </div>
                    </div>
                    <div class="widget-icon">
                        <div class="icon" data-color="#42A5F5" style="color: rgb(0, 236, 207);">
                            <i class="icon-copy ti-save"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-20">
            <div class="card-box height-100-p widget-style3">
                <div class="d-flex flex-wrap">
                    <div class="widget-data">
                        <div class="weight-700 font-24 text-dark"><?= $activeGuarantees?></div>
                        <div class="font-14 text-secondary weight-500">
                            Active Guarantees
                        </div>
                    </div>
                    <div class="widget-icon">
                        <div class="icon" data-color="#76FF03" style="color: rgb(255, 91, 91);">
                            <span class="icon-copy fa fa-check-circle"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-20">
            <div class="card-box height-100-p widget-style3">
                <div class="d-flex flex-wrap">
                    <div class="widget-data">
                        <div class="weight-700 font-24 text-dark"><?= $expiredGuarantees?></div>
                        <div class="font-14 text-secondary weight-500">
                            Expired Guarantees
                        </div>
                    </div>
                    <div class="widget-icon">
                        <div class="icon" data-color="#ff5b5b">
                            <i class="icon-copy fa fa-ban" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="row pb-10">
        <div class="col-lg-12 col-md-6 mb-20">
            <div class="card-box height-50-p pd-20 min-height-60px" style="position: relative;">
                <div class="d-flex justify-content-between">
                    <div class="h5 mb-0">Diseases Report</div>
                    <div class="dropdown">
                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" data-color="#1b3133"
                            href="#" role="button" data-toggle="dropdown" style="color: rgb(27, 49, 51);">
                            <i class="dw dw-more"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                            <a class="dropdown-item" href="#"><i class="dw dw-eye"></i> View</a>
                            <a class="dropdown-item" href="#"><i class="dw dw-edit2"></i> Edit</a>
                            <a class="dropdown-item" href="#"><i class="dw dw-delete-3"></i> Delete</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>



<div class="card-box pb-10">
    <div class="h5 pd-20 mb-0">Recent Patient</div>
    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
        <div class="row">
            <div class="col-sm-12 col-md-6"></div>
            <div class="col-sm-12 col-md-6"></div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <table class="data-table table nowrap dataTable no-footer dtr-inline" id="DataTables_Table_0"
                    role="grid">
                    <thead>
                        <tr role="row">
                            <th class="table-plus sorting_asc" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1" colspan="1" aria-sort="ascending"
                                aria-label="Name: activate to sort column descending">Name</th>
                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1"
                                aria-label="Gender: activate to sort column ascending">Gender</th>
                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1"
                                aria-label="Weight: activate to sort column ascending">Weight</th>
                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1"
                                aria-label="Assigned Doctor: activate to sort column ascending">
                                Assigned
                                Doctor</th>
                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1"
                                aria-label="Admit Date: activate to sort column ascending">Admit
                                Date
                            </th>
                            <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1"
                                aria-label="Disease: activate to sort column ascending">Disease</th>
                            <th class="datatable-nosort sorting_disabled" rowspan="1" colspan="1" aria-label="Actions">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>








                        <tr role="row" class="odd">
                            <td class="table-plus sorting_1" tabindex="0">
                                <div class="name-avatar d-flex align-items-center">
                                    <div class="avatar mr-2 flex-shrink-0">
                                        <img src="vendors/images/photo8.jpg" class="border-radius-100 shadow" width="40"
                                            height="40" alt="">
                                    </div>
                                    <div class="txt">
                                        <div class="weight-600">Christian Dyer</div>
                                    </div>
                                </div>
                            </td>
                            <td>Male</td>
                            <td>80 kg</td>
                            <td>Dr. Sebastian Tandon</td>
                            <td>15 Jun 2020</td>
                            <td>
                                <span class="badge badge-pill" data-bgcolor="#e7ebf5" data-color="#265ed7"
                                    style="color: rgb(38, 94, 215); background-color: rgb(231, 235, 245);">Diabetes</span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="#" data-color="#265ed7" style="color: rgb(38, 94, 215);"><i
                                            class="icon-copy dw dw-edit2"></i></a>
                                    <a href="#" data-color="#e95959" style="color: rgb(233, 89, 89);"><i
                                            class="icon-copy dw dw-delete-3"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr role="row" class="even">
                            <td class="table-plus sorting_1" tabindex="0">
                                <div class="name-avatar d-flex align-items-center">
                                    <div class="avatar mr-2 flex-shrink-0">
                                        <img src="vendors/images/photo5.jpg" class="border-radius-100 shadow" width="40"
                                            height="40" alt="">
                                    </div>
                                    <div class="txt">
                                        <div class="weight-600">Doris L. Larson</div>
                                    </div>
                                </div>
                            </td>
                            <td>Male</td>
                            <td>76 kg</td>
                            <td>Dr. Ren Delan</td>
                            <td>22 Jul 2020</td>
                            <td>
                                <span class="badge badge-pill" data-bgcolor="#e7ebf5" data-color="#265ed7"
                                    style="color: rgb(38, 94, 215); background-color: rgb(231, 235, 245);">Dengue</span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="#" data-color="#265ed7" style="color: rgb(38, 94, 215);"><i
                                            class="icon-copy dw dw-edit2"></i></a>
                                    <a href="#" data-color="#e95959" style="color: rgb(233, 89, 89);"><i
                                            class="icon-copy dw dw-delete-3"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr role="row" class="odd">
                            <td class="table-plus sorting_1" tabindex="0">
                                <div class="name-avatar d-flex align-items-center">
                                    <div class="avatar mr-2 flex-shrink-0">
                                        <img src="vendors/images/photo1.jpg" class="border-radius-100 shadow" width="40"
                                            height="40" alt="">
                                    </div>
                                    <div class="txt">
                                        <div class="weight-600">Doris L. Larson</div>
                                    </div>
                                </div>
                            </td>
                            <td>Male</td>
                            <td>76 kg</td>
                            <td>Dr. Ren Delan</td>
                            <td>22 Jul 2020</td>
                            <td>
                                <span class="badge badge-pill" data-bgcolor="#e7ebf5" data-color="#265ed7"
                                    style="color: rgb(38, 94, 215); background-color: rgb(231, 235, 245);">Dengue</span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="#" data-color="#265ed7" style="color: rgb(38, 94, 215);"><i
                                            class="icon-copy dw dw-edit2"></i></a>
                                    <a href="#" data-color="#e95959" style="color: rgb(233, 89, 89);"><i
                                            class="icon-copy dw dw-delete-3"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr role="row" class="even">
                            <td class="table-plus sorting_1" tabindex="0">
                                <div class="name-avatar d-flex align-items-center">
                                    <div class="avatar mr-2 flex-shrink-0">
                                        <img src="vendors/images/photo9.jpg" class="border-radius-100 shadow" width="40"
                                            height="40" alt="">
                                    </div>
                                    <div class="txt">
                                        <div class="weight-600">Jake Springer</div>
                                    </div>
                                </div>
                            </td>
                            <td>Female</td>
                            <td>45 kg</td>
                            <td>Dr. Garrett Kincy</td>
                            <td>08 Oct 2020</td>
                            <td>
                                <span class="badge badge-pill" data-bgcolor="#e7ebf5" data-color="#265ed7"
                                    style="color: rgb(38, 94, 215); background-color: rgb(231, 235, 245);">Covid
                                    19</span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="#" data-color="#265ed7" style="color: rgb(38, 94, 215);"><i
                                            class="icon-copy dw dw-edit2"></i></a>
                                    <a href="#" data-color="#e95959" style="color: rgb(233, 89, 89);"><i
                                            class="icon-copy dw dw-delete-3"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr role="row" class="odd">
                            <td class="table-plus sorting_1" tabindex="0">
                                <div class="name-avatar d-flex align-items-center">
                                    <div class="avatar mr-2 flex-shrink-0">
                                        <img src="vendors/images/photo4.jpg" class="border-radius-100 shadow" width="40"
                                            height="40" alt="">
                                    </div>
                                    <div class="txt">
                                        <div class="weight-600">Jennifer O. Oster</div>
                                    </div>
                                </div>
                            </td>
                            <td>Female</td>
                            <td>45 kg</td>
                            <td>Dr. Callie Reed</td>
                            <td>19 Oct 2020</td>
                            <td>
                                <span class="badge badge-pill" data-bgcolor="#e7ebf5" data-color="#265ed7"
                                    style="color: rgb(38, 94, 215); background-color: rgb(231, 235, 245);">Typhoid</span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="#" data-color="#265ed7" style="color: rgb(38, 94, 215);"><i
                                            class="icon-copy dw dw-edit2"></i></a>
                                    <a href="#" data-color="#e95959" style="color: rgb(233, 89, 89);"><i
                                            class="icon-copy dw dw-delete-3"></i></a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-5"></div>
            <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">
                    <ul class="pagination">
                        <li class="paginate_button page-item previous disabled" id="DataTables_Table_0_previous"><a
                                href="#" aria-controls="DataTables_Table_0" data-dt-idx="0" tabindex="0"
                                class="page-link"><i class="ion-chevron-left"></i></a></li>
                        <li class="paginate_button page-item active"><a href="#" aria-controls="DataTables_Table_0"
                                data-dt-idx="1" tabindex="0" class="page-link">1</a></li>
                        <li class="paginate_button page-item "><a href="#" aria-controls="DataTables_Table_0"
                                data-dt-idx="2" tabindex="0" class="page-link">2</a></li>
                        <li class="paginate_button page-item next" id="DataTables_Table_0_next"><a href="#"
                                aria-controls="DataTables_Table_0" data-dt-idx="3" tabindex="0" class="page-link"><i
                                    class="ion-chevron-right"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="title pb-20 pt-20">

</div>

<?= $this->endSection()?>