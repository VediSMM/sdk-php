<?php

declare(strict_types=1);

if ($argc !== 2 || !is_file($argv[1])) {
    fwrite(STDERR, "Usage: php scripts/inspect_artifact.php <archive.zip>\n");
    exit(1);
}
$zip = new ZipArchive();
if ($zip->open($argv[1]) !== true) {
    fwrite(STDERR, "Unable to open artifact\n");
    exit(1);
}
$entries = [];
for ($index = 0; $index < $zip->numFiles; ++$index) {
    $name = $zip->getNameIndex($index);
    if (!is_string($name)) {
        continue;
    }
    $entries[] = $name;
    if (preg_match('#(?:^|/)(?:vendor|tests|contract|scripts|\.git|dist)(?:/|$)#', $name) === 1) {
        fwrite(STDERR, sprintf("Forbidden artifact entry: %s\n", $name));
        exit(1);
    }
    $contents = $zip->getFromIndex($index);
    if (is_string($contents)
        && (preg_match('#/(?:Users|home)/[^/\s]+/#', $contents) === 1
            || preg_match('/-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----/', $contents) === 1)) {
        fwrite(STDERR, sprintf("Sensitive artifact content: %s\n", $name));
        exit(1);
    }
}
$zip->close();

foreach (['composer.json', 'src/Client.php', 'src/VediSMM.php', 'README.md', 'README.ru.md', 'docs/en/guide.md', 'docs/ru/guide.md', 'LICENSE'] as $required) {
    if (!in_array($required, $entries, true)) {
        fwrite(STDERR, sprintf("Missing artifact entry: %s\n", $required));
        exit(1);
    }
}

fwrite(STDOUT, sprintf("Artifact verified: %d files.\n", count($entries)));
