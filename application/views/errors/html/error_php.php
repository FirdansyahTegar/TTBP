<div style="border:1px solid #990000;padding:15px;margin:10px 0;background:#fee2e2;color:#991b1b;border-radius:6px;">
    <h4 style="margin-0 0 10px 0;">A PHP Error was encountered</h4>
    <p><b>Severity:</b> <?php echo $severity; ?></p>
    <p><b>Message:</b> <?php echo $message; ?></p>
    <p><b>Filename:</b> <?php echo $filepath; ?></p>
    <p><b>Line Number:</b> <?php echo $line; ?></p>
    <?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE): ?>
        <p><b>Backtrace:</b></p>
        <?php foreach (debug_backtrace() as $error): ?>
            <?php if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0): ?>
                <p style="margin-left:10px">
                File: <?php echo $error['file'] ?><br />
                Line: <?php echo $error['line'] ?><br />
                Function: <?php echo $error['function'] ?>
                </p>
            <?php endif ?>
        <?php endforeach ?>
    <?php endif ?>
</div>