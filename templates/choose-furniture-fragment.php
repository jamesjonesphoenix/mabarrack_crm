<?php
$name = $name ?? '';
$quantity = $quantity ?? 0;
$job_id = $job_id ?? 0;
$id = $id ?? 0;
?>
<div class="row cjob">
    <div class="col-md-12">
        <span><b><?php echo $name; ?></b><br>Quantity: <?php echo $quantity; ?></span>
        <a href="chooseactivity.php?job_id=<?php echo $job_id; ?>&furniture_id=<?php echo $id; ?>"
           class="btn btn-default">Select</a>
    </div>
</div>