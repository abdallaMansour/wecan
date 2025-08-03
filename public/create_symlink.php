<?php
$target = __DIR__ . '/../storage/app/public';
$link = __DIR__ . '/storage';
if (file_exists($link)) {
    unlink($link);
}
if (symlink($target, $link)) {
    echo "Symlink created successfully";
} else {
    echo "Failed to create symlink";
}