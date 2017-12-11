<?php

$isCli = php_sapi_name() == 'cli';
$eol = $isCli ? PHP_EOL : '<br>';

exec('git fetch --all && git reset --hard origin/master 2>&1', $out);

if (!$isCli) echo "<pre>";
print_r($out);
if (!$isCli) echo "</pre>";

if (!$isCli) echo "<hr><address>";
echo "Done.{$eol}";
if (!$isCli) echo "</address>";
