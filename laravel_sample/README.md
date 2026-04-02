# Laravel TODO App

Laravel 12 で作成した TODO 管理アプリです。`app` は PHP-FPM、`nginx` は Web サーバー、`db` は PostgreSQL を Docker Compose で起動します。

## 起動方法

```bash
docker compose up --build
```

初回起動時に `app` コンテナ内で以下を自動実行しています。

- `composer install`
- `php artisan key:generate`
- `php artisan migrate`

起動後は http://localhost:8000 で確認できます。

## 停止方法

```bash
docker compose down
```

DB データも削除したい場合:

```bash
docker compose down -v
```

## FastCGIキャプチャ

`app` コンテナには `tcpdump` を入れてあり、`capture` ディレクトリへ pcap を直接保存します。デフォルトでは `app` コンテナ起動時に自動でキャプチャを開始するので、最初の `FCGI_BEGIN_REQUEST` も取りこぼしにくくしています。

イメージ更新を含めて起動:

```bash
docker compose up --build
```

自動キャプチャを無効化したい場合:

```bash
FCGI_CAPTURE_AUTO=false docker compose up --build
```

キャプチャ開始:

```bash
./scripts/fastcgi-capture-start
```

別名で保存したい場合:

```bash
./scripts/fastcgi-capture-start /capture/custom-name.pcap
```

キャプチャ終了:

```bash
./scripts/fastcgi-capture-stop
```

出力先:

- `capture/fastcgi-YYYYMMDD-HHMMSS.pcap`

## アプリ概要

- TODO の追加
- TODO の更新
- 完了 / 未完了の切り替え
- TODO の削除
- PostgreSQL 永続化

## 主なファイル

- `docker-compose.yml`
- `docker/Dockerfile`
- `docker/nginx/default.conf`
- `docker/start-container.sh`
- `app/Http/Controllers/TodoController.php`
- `app/Models/Todo.php`
- `resources/views/todos/index.blade.php`

## テスト

PHP UNITテスト

```bash
docker compose run --rm app php artisan test
```

CI向けのJUnit XML出力

```bash
docker compose exec app composer test:ci
```

コードカバレッジ

```bash
docker compose exec app composer coverage
```

CI向けのカバレッジファイル出力

```bash
docker compose exec app composer coverage:ci
```

出力先:

- `storage/test-results/junit.xml`
- `storage/test-results/clover.xml`
- `storage/test-results/cobertura.xml`

pintによる書式チェック --testを消せば修正する

```bash
docker compose run --rm app ./vendor/bin/pint --test
```

PHPStan + Larastan による静的解析

```bash
docker compose exec app composer analyse
```

PHPMD による複雑度 / 設計チェック

```bash
docker compose exec app composer mess
```

セキュリティチェック

```bash
docker compose exec app composer audit
```

シェルにはいる

```bash
docker compose exec app bash
```
