<?php
require_once __DIR__ . '/../vendor/autoload.php';
$options = getopt('', array('target-dir'));

$branch = 'master';
if (!$branch) {
    $branch = 'bla';
}
$list = `git diff --name-only $branch`;

$dir = $options['target-dir'];
rm -rfv $dir/*
echo $list | xargs cp -r --parents --target-directory="$dir/"
if [ !$? ]; then
        echo "Exportálni sikerült az alábbi fájlokat:"
        echo "$list"
        exit 0
else
        exit 1
fi
