<script>
jQuery(function($){
    $('#pca-load-daily-payments').on('click', function(){
        let date = $('#pca-report-date').val();
        pcaLoadReport(ajaxurl + '?action=pca_report_daily_payments&date=' + date, '#pca-report-results');
    });
});
</script>

<input type="date" id="pca-report-date" value="<?php echo date('Y-m-d'); ?>">
<button class="button" id="pca-load-daily-payments">Load Report</button>

<pre id="pca-report-results"></pre>
