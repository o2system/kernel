A PHP Error was encountered

Severity: <?php echo $error->getStringSeverity() . PHP_EOL; ?>
Message:  <?php echo $error->getMessage() . PHP_EOL; ?>
Filename: <?php echo $error->getFile() . PHP_EOL; ?>
Line Number: <?php echo $error->getLine() . PHP_EOL; ?>

<?php if ( isset( $backtrace ) ) : ?>
    Backtrace: <?php echo PHP_EOL; ?>
    <?php foreach ( $backtrace->chronology() as $chronology ): ?>
        <?php echo $chronology->call . PHP_EOL; ?>
        <?php echo 'File: ' . @realpath( $chronology->file ) . PHP_EOL; ?>
        <?php echo 'Line: ' . $chronology->line . PHP_EOL; ?>
    <?php endforeach ?>
<?php endif; ?>