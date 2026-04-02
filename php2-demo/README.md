## 目的
このコンテナはPHP2の動作確認を検証するものである。

以下を参照
https://museum.php.net/php2/
https://www.php.net/manual/ja/history.php.php

## コンテナの制御

**起動方法**

```
docker compose up --build
```

**Dockerfileを修正した場合**

```
docker compose build --no-cache
docker compose up
```

**終了方法**

```
docker compose down
```

## 動作確認

サンプルページが開きます

**phpinfoの実行**
```
http://localhost:8080/index.phtml
```

**formの例**

```
http://localhost:8080/form.html
```

## 補足
php2-demo/php-2.0.1/install を改竄してデフォルトはログを無効にしました。
これは、ログを有効にすると２回目以降のアクセスでエラーとなるための暫定処置です。
