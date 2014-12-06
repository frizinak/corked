#! /usr/bin/env php

<?php
chdir(__DIR__);

@unlink('corked.phar');
$regex = '/^..\/(src|vendor)\/((?!(Tests|Test|test|tests)).)*\.php$/';
$phar = new Phar('corked.phar', FilesystemIterator::UNIX_PATHS, 'corked.phar');
$phar->buildFromDirectory('..', $regex);
$phar->addFromString('bin/corked', preg_replace('/^#!.*(\n|$)/', '', file_get_contents('../bin/corked')));

$phar->setStub(<<<'nowdoc'
#! /usr/bin/env php

<?php
Phar::mapPhar('corked.phar');
require 'phar://corked.phar/bin/corked';

__HALT_COMPILER();
nowdoc
);

chmod('corked.phar', 0777);
