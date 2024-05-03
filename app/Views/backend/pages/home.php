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
                            Saved Guarantees
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


    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card card-box">
                <div class="card-header">
                    <div class="clearfix">
                        <div class="pull-left">Soon to be expired guarantees</div>
                        <div class="pull-right"></div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Expiration Date</th>
                                <th>Time Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($soonToExpireGuarantees as $guarantee): ?>
                            <?php
                                $expirationTime = strtotime($guarantee->expiration_date);
                                $currentTime = time();
                                $timeDiff = $expirationTime - $currentTime;
                                $daysLeft = floor($timeDiff / (60 * 60 * 24));
                                $rowClass = $daysLeft < 3 ? 'expiring-soon' : '';
                            ?>
                            <tr class="<?= $rowClass ?>">
                                <td><?= esc($guarantee->title) ?></td>
                                <td><?= esc($guarantee->expiration_date) ?></td>
                                <td id="timer-<?= $guarantee->id ?>" style="font-family: monospace;  width: 20%; "></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.expiring-soon {
    background-color: #ff1f00;
    color: white;
    /* Adjust text color if needed */
}
</style>

<script>
<?php foreach ($soonToExpireGuarantees as $guarantee): ?>
var countDownDate<?= $guarantee->id ?> = new Date("<?= $guarantee->expiration_date ?>").getTime();

var x<?= $guarantee->id ?> = setInterval(function() {
    var now = new Date().getTime();
    var distance = countDownDate<?= $guarantee->id ?> - now;
    var timerElement = document.getElementById("timer-<?= $guarantee->id ?>");

    if (distance < 0) {
        clearInterval(x<?= $guarantee->id ?>);
        timerElement.innerHTML = "EXPIRED";
        timerElement.closest('tr').classList.add("expired"); // Add expired class if needed
    } else {
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);
        timerElement.innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";

        // Update row color dynamically
        if (days < 3) {
            timerElement.closest('tr').classList.add("expiring-soon");
        } else {
            timerElement.closest('tr').classList.remove("expiring-soon");
        }
    }
}, 1000);
<?php endforeach; ?>
</script>


<?= $this->endSection()?>