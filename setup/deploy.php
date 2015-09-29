<?php
echo shell_exec('cd /var/www/html');
echo '<br>';
echo shell_exec('sudo -Hu apache git pull origin master');
?>
