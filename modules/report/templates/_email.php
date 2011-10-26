Dear Moderator,

<?php echo get_class($reported_item) ?> has been reported by <?php printf('%s <%s>', $report_log['reporter'], $report_log['email']) ?>.

Reason: <?php echo $report_log['reason'] ?>

<?php if($report_log['message']): ?>
Message: <?php echo $report_log['message'] ?>
<?php endif ?>


------------------------------------------------------
This is an automatic email, please do not reply.
------------------------------------------------------
