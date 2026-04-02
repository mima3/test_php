# PHP 8.5.4 Debug Build

このディレクトリは PHP 8.5.4 の debug build を Docker で起動するための独立構成です。

## 起動

```bash
docker compose up --build -d
```

## 確認

```bash
docker compose exec php85-debug php -v
docker compose exec php85-debug phpdbg -v
docker compose exec php85-debug php -m | grep -i xdebug
docker compose exec php85-debug php --ri xdebug
docker compose exec php85-debug php -m | grep -i ffi
```

`php -v` の出力に `debug` build であることが含まれます。
この構成の `phpdbg` は readline なしでビルドしているため、履歴や行編集はありません。
`php -m` に `xdebug` が出れば `xdebug_debug_zval()` を使えます。
`php -m` に `ffi` が出れば `FFI::cdef()` を使うサンプルも実行できます。

## マウント

- `./php-8.5.4` -> `/usr/src/php-src`
- `./samples` -> `/samples`

ホスト側の `samples` フォルダを編集すると、コンテナ内 `/samples` に即時反映されます。zval を確認したい PHP コードはこの `samples` に置いて実行します。

## samples で PHPUnit を使う

`samples` 配下には `composer.json` と `phpunit.xml` を置いてあるので、イメージを作り直した後にそのまま依存を入れられます。

```bash
docker compose up --build -d
docker compose exec php85-debug composer install
docker compose exec php85-debug ./vendor/bin/phpunit
```

## samples で PHPStan / Psalm を使う

`samples/composer.json` には `phpstan/phpstan` と `vimeo/psalm` も入れてあるので、依存解決後にそのまま実行できます。

```bash
docker compose exec php85-debug composer install
docker compose exec php85-debug ./vendor/bin/phpstan analyse /samples
docker compose exec php85-debug ./vendor/bin/psalm
```

phpstanで厳しめにチェックする

```
./vendor/bin/phpstan analyse sample_type1.php --level=max
```

psalm.xmlを作る

```
./vendor/bin/psalm --init
```

errorLevelは1が厳しい, 8が最も緩い

INFOレベルのissuesも表示

```
./vendor/bin/psalm --show-info=true sample_type1.php
```

## 使い方

```bash
docker compose exec php85-debug bash
```

サンプルコード:

```bash
docker compose exec php85-debug php /samples/zval_playground.php
```

`xdebug_debug_zval()` を使うサンプル:

```php
<?php

$a = 'hello';
xdebug_debug_zval('a');
```

`phpdbg` で行単位に追う:

```bash
docker compose exec php85-debug phpdbg -qrr /samples/zval_playground.php
```

`gdb` で debug build の PHP を起動する:

```bash
docker compose exec php85-debug gdb --args php /samples/zval_playground.php
```

## 手順

1. `cd /Users/itagakimasaki/dev/test_php/php8-debug`
2. `docker compose up --build -d`
3. `docker compose exec php85-debug php -v`
4. `docker compose exec php85-debug php /samples/zval_playground.php`
5. 必要なら `docker compose exec php85-debug phpdbg -qrr /samples/zval_playground.php`
6. さらに深く見るなら `docker compose exec php85-debug gdb --args php /samples/zval_playground.php`

## zval 確認の考え方

まずは `debug_zval_dump()` で参照カウントや共有状態の変化を見ます。ポインタそのものは出ませんが、値渡し・参照渡し・copy-on-write の違いを観察するには十分です。

本当に `zval *` を追いたい場合は `gdb` を使います。PHP スクリプトの変数名をそのまま直接取るのではなく、実行中の Zend Engine 側で受け取っている `zval *` を見る形になります。


xdebugを無効にしてデバッガを起動する。(xdebugが有効だとvar_dumpの挙動がxdebugのものになる)
```
gdb --args php -n debug_array.php
```

var_dump()の処理でbreakがかかるようにする

```
break zif_var_dump
run
```



```
step
step
step
print *args
run
```

文字列を見る例：

```
print *args->value->str
x/s args->value.str->val
```
