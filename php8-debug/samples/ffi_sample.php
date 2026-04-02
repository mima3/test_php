<?php
// https://www.php.net/manual/ja/book.ffi.php
$libc = match (PHP_OS_FAMILY) {
    'Linux' => 'libc.so.6',
    'Darwin' => 'libc.dylib',
    default => throw new RuntimeException('このサンプルは Linux/macOS 向けです'),
};

$ffi = FFI::cdef('
    int puts(const char *s);
', $libc);

$ffi->puts("Hello from C via PHP FFI!");