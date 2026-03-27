# Laravel TODO App

Laravel 12 で作成した TODO 管理アプリです。`web` は Apache + PHP、`db` は PostgreSQL を Docker Compose で起動します。

## 起動方法

```bash
docker compose up --build
```

初回起動時に `web` コンテナ内で以下を自動実行します。

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

## アプリ概要

- TODO の追加
- TODO の更新
- 完了 / 未完了の切り替え
- TODO の削除
- PostgreSQL 永続化

## 主なファイル

- `docker-compose.yml`
- `docker/Dockerfile`
- `docker/start-container.sh`
- `app/Http/Controllers/TodoController.php`
- `app/Models/Todo.php`
- `resources/views/todos/index.blade.php`

## テスト

PHP UNITテスト

```bash
docker compose run --rm web php artisan test
```

CI向けのJUnit XML出力

```bash
docker compose exec web composer test:ci
```

コードカバレッジ

```bash
docker compose exec web composer coverage
```

CI向けのカバレッジファイル出力

```bash
docker compose exec web composer coverage:ci
```

出力先:

- `storage/test-results/junit.xml`
- `storage/test-results/clover.xml`
- `storage/test-results/cobertura.xml`

pintによる書式チェック --testを消せば修正する

```bash
docker compose run --rm web ./vendor/bin/pint --test
```

PHPStan + Larastan による静的解析

```bash
docker compose exec web composer analyse
```

PHPMD による複雑度 / 設計チェック

```bash
docker compose exec web composer mess
```

セキュリティチェック

```bash
docker compose exec web composer audit
```
