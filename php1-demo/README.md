## 目的
このコンテナはPHP1の動作確認を検証するものである。

以下を参照
https://museum.php.net/php1/
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

**version表示**
```
http://localhost:8080/cgi-bin/phpl.cgi?version
```

**index.htmlをパース**

```
http://localhost:8080/cgi-bin/phpl.cgi?index.html
```

**formのサンプル**
formを入力したらその値を`/cgi-bin/phpl.cgi?form-result.html`に渡す

```
http://localhost:8080/form.html
```
