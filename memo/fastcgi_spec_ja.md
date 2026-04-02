# FastCGI Specification

**Mark R. Brown**  
Open Market, Inc.

**Document Version:** 1.0  
**Date:** 29 April 1996

> 原文: FastCGI Specification  
> URL: https://fast-cgi.github.io/spec

---

## 1. Introduction

FastCGI は CGI のオープンな拡張であり、Web サーバ API に伴う不利益を負うことなく、あらゆるインターネット・アプリケーションに高性能を提供する。

この仕様の目的は限定的である。すなわち、アプリケーションの観点から、FastCGI アプリケーションと FastCGI をサポートする Web サーバとの間のインターフェースを規定することである。FastCGI に関連する Web サーバ機能の多く、たとえばアプリケーション管理機構などは、アプリケーションと Web サーバのインターフェースとは無関係であり、ここでは記述しない。

この仕様は Unix 向け、より正確には Berkeley Sockets をサポートする POSIX システム向けである。仕様の大部分は単純な通信プロトコルであり、バイトオーダーに依存せず、他のシステムにも拡張可能である。

FastCGI を、従来の Unix における CGI/1.1 実装と比較することで導入する。FastCGI は、長寿命のアプリケーション・プロセス、すなわちアプリケーション・サーバをサポートするよう設計されている。これは、従来の Unix における CGI/1.1 実装が、アプリケーション・プロセスを生成し、それを 1 つの要求に応答するために使い、終了させるのと比べて大きな違いである。

FastCGI プロセスの初期状態は、CGI/1.1 プロセスの初期状態よりも簡素である。というのも、FastCGI プロセスは何にも接続されていない状態で開始するからである。従来の open されたファイル `stdin`、`stdout`、`stderr` を持たず、環境変数から受け取る情報も多くない。FastCGI プロセスにおける初期状態の重要な要素は listening socket であり、これを通じて Web サーバからの接続を受け入れる。

FastCGI プロセスが listening socket 上で接続を受け入れた後、そのプロセスは単純なプロトコルを実行してデータを受信・送信する。このプロトコルには 2 つの目的がある。第一に、単一のトランスポート接続上で複数の独立した FastCGI リクエストを多重化することである。これにより、イベント駆動型またはマルチスレッド型プログラミング技法を用いて並行リクエストを処理できるアプリケーションをサポートする。第二に、各リクエスト内部で、各方向に複数の独立したデータストリームを提供する。

このようにして、たとえば `stdout` と `stderr` のデータは、CGI/1.1 のように別々の pipe を必要とするのではなく、アプリケーションから Web サーバへの単一のトランスポート接続上を流れる。

FastCGI アプリケーションは、よく定義された複数の役割のいずれかを担う。もっとも馴染み深いのは Responder 役割であり、この役割ではアプリケーションは HTTP リクエストに関連するすべての情報を受け取り、HTTP レスポンスを生成する。これは CGI/1.1 プログラムが果たす役割である。2 番目の役割は Authorizer であり、この役割ではアプリケーションは HTTP リクエストに関連するすべての情報を受け取り、認可／非認可の判定を生成する。

3 番目の役割は Filter であり、この役割ではアプリケーションは HTTP リクエストに関連するすべての情報に加えて、Web サーバ上に保存されたファイルからの追加データストリームを受け取り、そのデータストリームの「フィルタ済み」版を HTTP レスポンスとして生成する。この枠組みは拡張可能であり、今後さらに多くの FastCGI 役割を定義できる。

以降、本仕様では「FastCGI application」「application process」「application server」という用語を、混乱を招かない限り「application」と略記する。

## 2. Initial process state

### 2.1. Argument list

デフォルトでは、Web サーバは 1 要素だけを含む引数リストを生成する。その要素はアプリケーション名であり、実行ファイルのパス名の最後の構成要素であるとみなされる。Web サーバは、別のアプリケーション名や、より複雑な引数リストを指定する手段を提供してもよい。

Web サーバが実行するファイルはインタプリタ・ファイル（文字列 `#!` で始まるテキストファイル）である場合があることに注意されたい。その場合、アプリケーションの引数リストは `execve` の manpage に記述されたとおりに構成される。

### 2.2. File descriptors

Web サーバは、アプリケーションの実行開始時に、`FCGI_LISTENSOCK_FILENO` という単一のファイル記述子を open のまま残す。この記述子は、Web サーバによって作成された listening socket を参照する。

`FCGI_LISTENSOCK_FILENO` は `STDIN_FILENO` に等しい。標準記述子 `STDOUT_FILENO` と `STDERR_FILENO` は、アプリケーションの実行開始時に閉じられている。アプリケーションが CGI と FastCGI のどちらとして起動されたかを確実に判定する方法は、`getpeername(FCGI_LISTENSOCK_FILENO)` を呼び出すことであり、FastCGI アプリケーションでは `errno` が `ENOTCONN` に設定された状態で -1 を返す。

Web サーバが選択する信頼できるトランスポート、すなわち Unix stream pipe (`AF_UNIX`) または TCP/IP (`AF_INET`) は、`FCGI_LISTENSOCK_FILENO` ソケットの内部状態に暗黙に表現される。

### 2.3. Environment variables

Web サーバは、環境変数を使ってアプリケーションへパラメータを渡してもよい。本仕様では、そのような変数の 1 つとして `FCGI_WEB_SERVER_ADDRS` を定義する。仕様の発展に伴い、さらに多くの変数が定義されることを想定している。Web サーバは、`PATH` 変数のような他の環境変数を束縛する方法を提供してもよい。

### 2.4. Other state

Web サーバは、アプリケーションの初期プロセス状態の他の構成要素、たとえばプロセスの優先度、ユーザ ID、グループ ID、ルートディレクトリ、作業ディレクトリなどを指定する方法を提供してもよい。

## 3. Protocol basics

### 3.1. Notation

本仕様では、プロトコル・メッセージ形式の定義に C 言語の記法を用いる。すべての構造体要素は `unsigned char` 型で定義され、ISO C コンパイラがパディングなしで自明な方法で配置するように並べられる。構造体中で最初に定義されたバイトが最初に送信され、2 番目のバイトが次に送信される。

定義を簡略化するために、以下の 2 つの慣例を用いる。

第一に、隣接する 2 つの構造体要素の名前が、接尾辞 `B1` と `B0` を除いて同一である場合、それら 2 つの要素は `B1<<8 + B0` によって計算される単一の数値としてみなしてよい。この単一の数値の名前は、接尾辞を除いた要素名である。この慣例は、2 バイトを超える表現にも自明な形で一般化される。

第二に、C の `struct` を拡張して、次の形式を許す。

```c
struct {
    unsigned char mumbleLengthB1;
    unsigned char mumbleLengthB0;
    ... /* other stuff */
    unsigned char mumbleData[mumbleLength];
};
```

これは可変長の構造体を意味し、ある構成要素の長さが、先行する 1 つまたは複数の構成要素の値によって決定される。

### 3.2. Accepting transport connections

FastCGI アプリケーションは、ファイル記述子 `FCGI_LISTENSOCK_FILENO` が参照するソケットに対して `accept()` を呼び出し、新しいトランスポート接続を受け入れる。`accept()` が成功し、かつ環境変数 `FCGI_WEB_SERVER_ADDRS` が束縛されている場合、アプリケーションは直ちに以下の特別な処理を行う。

- `FCGI_WEB_SERVER_ADDRS`: 値は Web サーバの有効な IP アドレスの一覧である。

`FCGI_WEB_SERVER_ADDRS` が束縛されていた場合、アプリケーションは新しい接続の peer IP アドレスがその一覧に含まれているかを確認する。この確認に失敗した場合（接続が TCP/IP トランスポートを使用していなかった場合を含む）、アプリケーションはその接続を閉じることで応答する。

`FCGI_WEB_SERVER_ADDRS` はカンマ区切りの IP アドレス一覧として表される。各 IP アドレスは、`[0..255]` の範囲にある 4 つの 10 進数をピリオドで区切って記述する。したがって、この変数の合法な束縛例の 1 つは `FCGI_WEB_SERVER_ADDRS=199.170.183.28,199.170.183.71` である。

アプリケーションは複数の並行トランスポート接続を受け入れてもよいが、そうする必要はない。

### 3.3. Records

アプリケーションは、単純なプロトコルを使って Web サーバからのリクエストを実行する。プロトコルの詳細はアプリケーションの役割に依存するが、大まかには、まず Web サーバがパラメータその他のデータをアプリケーションへ送信し、次にアプリケーションが結果データを Web サーバへ送信し、最後にアプリケーションがリクエスト完了を示す。

トランスポート接続上を流れるすべてのデータは FastCGI レコードに格納される。FastCGI レコードは 2 つのことを実現する。第一に、複数の独立した FastCGI リクエストの間でトランスポート接続を多重化する。この多重化は、イベント駆動型またはマルチスレッド型プログラミング技法によって並行リクエストを処理できるアプリケーションを支援する。第二に、単一のリクエスト内部で、各方向に複数の独立したデータストリームを提供する。

このようにして、たとえば `stdout` と `stderr` の両方のデータが、別々の接続を必要とせず、アプリケーションから Web サーバへの単一のトランスポート接続上を流れることができる。

```c
typedef struct {
    unsigned char version;
    unsigned char type;
    unsigned char requestIdB1;
    unsigned char requestIdB0;
    unsigned char contentLengthB1;
    unsigned char contentLengthB0;
    unsigned char paddingLength;
    unsigned char reserved;
    unsigned char contentData[contentLength];
    unsigned char paddingData[paddingLength];
} FCGI_Record;
```

FastCGI レコードは、固定長の接頭部と、それに続く可変個数の content バイトおよび padding バイトから構成される。レコードは 7 つの構成要素を含む。

- `version`: FastCGI プロトコルのバージョンを識別する。本仕様では `FCGI_VERSION_1` を記述する。
- `type`: FastCGI レコード型を識別する。すなわち、そのレコードが果たす一般的な機能を示す。個別のレコード型とその機能は後の節で詳述する。
- `requestId`: そのレコードが属する FastCGI リクエストを識別する。
- `contentLength`: レコードの `contentData` 構成要素に含まれるバイト数。
- `paddingLength`: レコードの `paddingData` 構成要素に含まれるバイト数。
- `contentData`: 0 〜 65535 バイトのデータであり、解釈はレコード型に依存する。
- `paddingData`: 0 〜 255 バイトのデータであり、無視される。

定数 FastCGI レコードを指定するために、緩和された C `struct` 初期化子構文を用いる。`version` 構成要素は省略し、padding は無視し、`requestId` は数値として扱う。したがって `{FCGI_END_REQUEST, 1, {FCGI_REQUEST_COMPLETE,0}}` は、`type == FCGI_END_REQUEST`、`requestId == 1`、`contentData == {FCGI_REQUEST_COMPLETE,0}` のレコードである。

#### Padding

このプロトコルでは、送信側が送るレコードにパディングを追加でき、受信側は `paddingLength` を解釈して `paddingData` を読み飛ばすことが要求される。パディングにより、送信側はより効率的な処理のためにデータのアラインメントを保つことができる。X window system プロトコルの経験は、このようなアラインメントが性能上の利益をもたらすことを示している。

レコードは 8 バイト境界の倍数に配置することを推奨する。`FCGI_Record` の固定長部分は 8 バイトである。

#### Managing request IDs

Web サーバは FastCGI request ID を再利用する。アプリケーションは、与えられたトランスポート接続上の各 request ID の現在状態を追跡する。request ID `R` は、アプリケーションが `{FCGI_BEGIN_REQUEST, R, ...}` というレコードを受信したときに active になり、アプリケーションが `{FCGI_END_REQUEST, R, ...}` というレコードを Web サーバへ送信したときに inactive になる。

request ID `R` が inactive の間、アプリケーションは `requestId == R` のレコードを無視する。ただし、上で述べた `FCGI_BEGIN_REQUEST` レコードは例外である。

Web サーバは FastCGI request ID を小さく保とうとする。そのため、アプリケーションは長い配列やハッシュテーブルではなく短い配列を用いて request ID の状態を追跡できる。アプリケーションは一度に 1 つのリクエストしか受け付けないという選択も可能である。この場合、アプリケーションは受信した `requestId` 値を現在の request ID と単純に比較する。

#### Types of record types

FastCGI レコード型を分類する有用な方法は 2 つある。

第一の区別は、management record と application record の区別である。management record は、アプリケーションのプロトコル能力に関する情報のように、特定の Web サーバ・リクエストに固有でない情報を含む。application record は、`requestId` 構成要素によって識別される特定のリクエストに関する情報を含む。

management record は `requestId` 値 0、すなわち null request ID を持つ。application record は非 0 の `requestId` を持つ。

第二の区別は、discrete record と stream record の区別である。discrete record は、それ単独で意味のある 1 単位のデータを含む。stream record はストリームの一部であり、すなわち 0 個以上の空でないレコード（`length != 0`）に続き、最後に空のレコード（`length == 0`）が来る、同一ストリーム型の列である。ストリームを構成するレコードの `contentData` 構成要素を連結するとバイト列が得られ、このバイト列がそのストリームの値である。

したがって、ストリームの値は、それが何個のレコードを含むかや、そのバイトが空でないレコード群の間でどのように分割されているかには依存しない。

これら 2 つの分類は独立している。本バージョンの FastCGI プロトコルで定義されているレコード型のうち、すべての management record 型は discrete record 型でもあり、application record 型のほとんどは stream record 型である。しかし、3 つの application record 型は discrete であり、将来のプロトコル・バージョンで management record 型として stream を定義することを妨げるものは何もない。

### 3.4. Name-Value pairs

FastCGI アプリケーションは、その多くの役割において、可変長の値を可変個数読み書きする必要がある。したがって、name-value pair をエンコードするための標準形式を採用することは有用である。

FastCGI は、name-value pair を、name の長さ、value の長さ、name、本体の value、という順序で送信する。長さが 127 バイト以下であれば 1 バイトでエンコードでき、それより長い長さは常に 4 バイトでエンコードされる。

```c
typedef struct {
    unsigned char nameLengthB0;  /* nameLengthB0  >> 7 == 0 */
    unsigned char valueLengthB0; /* valueLengthB0 >> 7 == 0 */
    unsigned char nameData[nameLength];
    unsigned char valueData[valueLength];
} FCGI_NameValuePair11;

typedef struct {
    unsigned char nameLengthB0;  /* nameLengthB0  >> 7 == 0 */
    unsigned char valueLengthB3; /* valueLengthB3 >> 7 == 1 */
    unsigned char valueLengthB2;
    unsigned char valueLengthB1;
    unsigned char valueLengthB0;
    unsigned char nameData[nameLength];
    unsigned char valueData[valueLength
            ((B3 & 0x7f) << 24) + (B2 << 16) + (B1 << 8) + B0];
} FCGI_NameValuePair14;

typedef struct {
    unsigned char nameLengthB3;  /* nameLengthB3  >> 7 == 1 */
    unsigned char nameLengthB2;
    unsigned char nameLengthB1;
    unsigned char nameLengthB0;
    unsigned char valueLengthB0; /* valueLengthB0 >> 7 == 0 */
    unsigned char nameData[nameLength
            ((B3 & 0x7f) << 24) + (B2 << 16) + (B1 << 8) + B0];
    unsigned char valueData[valueLength];
} FCGI_NameValuePair41;

typedef struct {
    unsigned char nameLengthB3;  /* nameLengthB3  >> 7 == 1 */
    unsigned char nameLengthB2;
    unsigned char nameLengthB1;
    unsigned char nameLengthB0;
    unsigned char valueLengthB3; /* valueLengthB3 >> 7 == 1 */
    unsigned char valueLengthB2;
    unsigned char valueLengthB1;
    unsigned char valueLengthB0;
    unsigned char nameData[nameLength
            ((B3 & 0x7f) << 24) + (B2 << 16) + (B1 << 8) + B0];
    unsigned char valueData[valueLength
            ((B3 & 0x7f) << 24) + (B2 << 16) + (B1 << 8) + B0];
} FCGI_NameValuePair44;
```

長さを表す最初のバイトの最上位ビットは、その長さのエンコード方式を示す。最上位ビットが 0 なら 1 バイト・エンコード、1 なら 4 バイト・エンコードである。

この name-value pair 形式により、送信側は追加のエンコードなしにバイナリ値を送信でき、受信側は値が大きい場合でも即座に適切な量の記憶域を確保できる。

### 3.5. Closing transport connections

トランスポート接続の寿命は Web サーバが制御する。Web サーバは、active なリクエストが存在しないときに接続を閉じることができる。また、接続を閉じる権限をアプリケーションへ委譲することもできる（`FCGI_BEGIN_REQUEST` を参照）。この場合、アプリケーションは指定されたリクエストの終了時に接続を閉じる。

この柔軟性は、多様なアプリケーション・スタイルに対応する。単純なアプリケーションは、一度に 1 リクエストずつ処理し、各リクエストごとに新しいトランスポート接続を受け入れる。より複雑なアプリケーションは、1 つまたは複数のトランスポート接続上で並行リクエストを処理し、トランスポート接続を長期間開いたまま保持する。

## 4. Management record types

### 4.1. `FCGI_GET_VALUES`, `FCGI_GET_VALUES_RESULT`

FastCGI アプリケーションと Web サーバは、管理情報の問い合わせに `FCGI_GET_VALUES` および `FCGI_GET_VALUES_RESULT` レコードを用いる。`FCGI_GET_VALUES` レコードの `contentData` は、問い合わせ対象の変数名からなる name-value pair 列であり、各 value は空文字列である。

アプリケーションは、与えられた値を含む `{FCGI_GET_VALUES_RESULT, 0, ...}` レコードを返して応答する。問い合わせに含まれていた変数名をアプリケーションが理解しない場合、その名前は応答から省略する。

`FCGI_GET_VALUES` は、開放的な変数集合を許容するよう設計されている。初期集合は、サーバがアプリケーション管理および接続管理を行うのに役立つ情報を提供する。

- `FCGI_MAX_CONNS`: このアプリケーションが受け入れる同時トランスポート接続の最大数。例: `"1"`、`"10"`
- `FCGI_MAX_REQS`: このアプリケーションが受け入れる同時リクエストの最大数。例: `"1"`、`"50"`
- `FCGI_MPXS_CONNS`: このアプリケーションが接続を多重化しない（すなわち各接続上で並行リクエストを扱わない）場合は `"0"`、そうでなければ `"1"`

アプリケーションは、いつでも `FCGI_GET_VALUES` レコードを受信しうる。アプリケーションの応答はアプリケーション本体ではなく FastCGI ライブラリだけを関与させるべきである。

### 4.2. `FCGI_UNKNOWN_TYPE`

このプロトコルの将来バージョンでは、management record 型の集合が拡張される可能性が高い。この進化に備えるため、プロトコルには `FCGI_UNKNOWN_TYPE` management record が含まれる。アプリケーションが、理解できない型 `T` を持つ management record を受信した場合、アプリケーションは `{FCGI_UNKNOWN_TYPE, 0, {T}}` を返して応答する。

`FCGI_UNKNOWN_TYPE` レコードの `contentData` 構成要素は次の形式を持つ。

```c
typedef struct {
    unsigned char type;
    unsigned char reserved[7];
} FCGI_UnknownTypeBody;
```

`type` 構成要素は、認識できなかった management record の型である。

## 5. Application record types

### 5.1. `FCGI_BEGIN_REQUEST`

Web サーバは、リクエストを開始するために `FCGI_BEGIN_REQUEST` レコードを送信する。

`FCGI_BEGIN_REQUEST` レコードの `contentData` 構成要素は次の形式を持つ。

```c
typedef struct {
    unsigned char roleB1;
    unsigned char roleB0;
    unsigned char flags;
    unsigned char reserved[5];
} FCGI_BeginRequestBody;
```

`role` 構成要素は、Web サーバがアプリケーションに期待する役割を設定する。現在定義されている役割は次のとおりである。

- `FCGI_RESPONDER`
- `FCGI_AUTHORIZER`
- `FCGI_FILTER`

役割の詳細は後の 6 節で述べる。

`flags` 構成要素は、接続の shutdown を制御するビットを含む。

- `flags & FCGI_KEEP_CONN`: これが 0 なら、アプリケーションはこのリクエストへの応答後に接続を閉じる。0 でなければ、アプリケーションは応答後に接続を閉じない。接続に対する責任は Web サーバに残る。

### 5.2. Name-Value pair streams: `FCGI_PARAMS`

`FCGI_PARAMS` は、Web サーバからアプリケーションへ name-value pair を送信するために使用される stream record 型である。name-value pair は、指定順序なしに、ストリーム上へ次々に送られる。

### 5.3. Byte streams: `FCGI_STDIN`, `FCGI_DATA`, `FCGI_STDOUT`, `FCGI_STDERR`

`FCGI_STDIN` は、Web サーバからアプリケーションへ任意データを送るために使われる stream record 型である。`FCGI_DATA` は、アプリケーションへ追加データを送るための第 2 の stream record 型である。

`FCGI_STDOUT` と `FCGI_STDERR` は、それぞれアプリケーションから Web サーバへ任意データおよびエラーデータを送るための stream record 型である。

### 5.4. `FCGI_ABORT_REQUEST`

Web サーバは、リクエストを中止するために `FCGI_ABORT_REQUEST` レコードを送信する。`{FCGI_ABORT_REQUEST, R}` を受信した後、アプリケーションは可能な限り速やかに `{FCGI_END_REQUEST, R, {FCGI_REQUEST_COMPLETE, appStatus}}` で応答する。これはアプリケーションからの本当の応答であり、FastCGI ライブラリからの低レベルな確認応答ではない。

HTTP クライアントが、自身に代わって実行中の FastCGI リクエストの最中にトランスポート接続を閉じたとき、Web サーバは FastCGI リクエストを中止する。この状況はありそうにないように見えるかもしれない。多くの FastCGI リクエストは短い応答時間しか持たず、クライアントが遅い場合は Web サーバが出力バッファリングを提供するからである。しかし FastCGI アプリケーションは、別のシステムとの通信や server push の実行によって遅延する可能性がある。

Web サーバがトランスポート接続上でリクエストを多重化していない場合、Web サーバはそのリクエストのトランスポート接続を閉じることでリクエストを中止できる。しかし、リクエストが多重化されている場合、トランスポート接続を閉じると、その接続上のすべてのリクエストを中止してしまうという不都合がある。

### 5.5. `FCGI_END_REQUEST`

アプリケーションは、リクエストを処理し終えたか、またはリクエストを拒否したために、リクエストを終了するための `FCGI_END_REQUEST` レコードを送信する。

`FCGI_END_REQUEST` レコードの `contentData` 構成要素は次の形式を持つ。

```c
typedef struct {
    unsigned char appStatusB3;
    unsigned char appStatusB2;
    unsigned char appStatusB1;
    unsigned char appStatusB0;
    unsigned char protocolStatus;
    unsigned char reserved[3];
} FCGI_EndRequestBody;
```

`appStatus` 構成要素はアプリケーション・レベルのステータスコードである。各役割は `appStatus` の使用法を文書化する。

`protocolStatus` 構成要素はプロトコル・レベルのステータスコードであり、取りうる値は次のとおりである。

- `FCGI_REQUEST_COMPLETE`: リクエストの正常終了。
- `FCGI_CANT_MPX_CONN`: 新しいリクエストを拒否している。これは、Web サーバが 1 接続上で並行リクエストを送信したが、アプリケーションが接続ごとに一度に 1 リクエストしか処理しないよう設計されている場合に起こる。
- `FCGI_OVERLOADED`: 新しいリクエストを拒否している。これは、アプリケーションが何らかの資源、たとえばデータベース接続を使い果たしたときに起こる。
- `FCGI_UNKNOWN_ROLE`: 新しいリクエストを拒否している。これは、Web サーバがアプリケーションにとって未知の役割を指定したときに起こる。

## 6. Roles

### 6.1. Role protocols

役割プロトコルは application record 型のレコードだけを含む。それらは、本質的にすべてのデータをストリームによって転送する。

プロトコルの信頼性を高め、アプリケーション・プログラミングを単純化するために、役割プロトコルはほぼ逐次的な marshalling を用いるよう設計されている。厳密に逐次的な marshalling を持つプロトコルでは、アプリケーションは第 1 の入力を受け取り、次に第 2 の入力を受け取り、というように、すべてを受け取り終えるまで進む。同様に、アプリケーションは第 1 の出力を送り、次に第 2 の出力を送り、というように、すべてを送り終えるまで進む。入力同士はインターリーブされず、出力同士もインターリーブされない。

逐次的な marshalling 規則は、いくつかの FastCGI 役割に対しては制約が強すぎる。というのも CGI プログラムは、時間上の制約なしに `stdout` と `stderr` の両方へ書き込めるからである。そのため、`FCGI_STDOUT` と `FCGI_STDERR` の両方を用いる役割プロトコルでは、これら 2 つのストリームのインターリーブを許容する。

すべての役割プロトコルは、通常のアプリケーション・プログラミングで `stderr` が使われるのと同じように、`FCGI_STDERR` ストリームを利用する。すなわち、アプリケーション・レベルのエラーを理解可能な形で報告するためである。`FCGI_STDERR` ストリームの利用は常に任意である。アプリケーションに報告すべきエラーがない場合、`FCGI_STDERR` レコードをまったく送らないか、あるいは長さ 0 の `FCGI_STDERR` レコード 1 個を送る。

役割プロトコルが `FCGI_STDERR` 以外のストリームの送信を要求する場合、そのストリームが空であっても、必ず少なくとも 1 個のそのストリーム型レコードが送信される。

同様に、プロトコルの信頼性とアプリケーション・プログラミングの単純化のために、役割プロトコルはほぼ request-response 型になるよう設計されている。真の request-response プロトコルでは、アプリケーションは最初の出力レコードを送る前に、すべての入力レコードを受け取る。request-response プロトコルではパイプライニングはできない。

しかし、request-response 規則は一部の FastCGI 役割には制約が強すぎる。というのも、CGI プログラムは `stdin` をすべて読み終える前に `stdout` への書き込みを始めることを禁止されていないからである。そのため、一部の役割プロトコルでは、その特定の可能性を許容する。まずアプリケーションは、最後のストリーム入力を除くすべての入力を受け取る。その最後のストリーム入力を受け取り始めると同時に、出力の書き込みを開始できる。

役割プロトコルが、CGI プログラムが環境変数から取得する値のようなテキスト値を `FCGI_PARAMS` で送信する場合、その value の長さには終端の null バイトは含まれず、value 自体にも null バイトは含まれない。`environ(7)` 形式の name-value pair を提供する必要があるアプリケーションは、name と value の間に等号を挿入し、value の後ろに null バイトを追加しなければならない。

役割プロトコルは、CGI の non-parsed header 機能をサポートしない。FastCGI アプリケーションは、`Status` および `Location` CGI ヘッダを使って応答ステータスを設定する。

### 6.2. Responder

Responder FastCGI アプリケーションは、CGI/1.1 プログラムと同じ目的を持つ。すなわち、HTTP リクエストに関連するすべての情報を受け取り、HTTP レスポンスを生成する。

Responder において CGI/1.1 の各要素がどのようにエミュレートされるかを説明すれば十分である。

- Responder アプリケーションは、CGI/1.1 環境変数を Web サーバから `FCGI_PARAMS` 経由で受け取る。
- 次に Responder アプリケーションは、CGI/1.1 の `stdin` データを Web サーバから `FCGI_STDIN` 経由で受け取る。アプリケーションは、ストリーム終端の表示を受け取る前に、このストリームから最大で `CONTENT_LENGTH` バイトを受け取る。（`CONTENT_LENGTH` より少ないバイトしか受け取らないのは、HTTP クライアントがそれらを提供できなかった場合、たとえばクライアントがクラッシュした場合に限られる。）
- Responder アプリケーションは、CGI/1.1 の `stdout` データを `FCGI_STDOUT` 経由で Web サーバへ送り、CGI/1.1 の `stderr` データを `FCGI_STDERR` 経由で送る。アプリケーションはこれらを逐次ではなく並行に送る。アプリケーションは `FCGI_STDOUT` と `FCGI_STDERR` の書き込みを始める前に `FCGI_PARAMS` の読み取りを終えていなければならないが、これら 2 ストリームの書き込みを始める前に `FCGI_STDIN` の読み取りを終えている必要はない。
- すべての `stdout` および `stderr` データを送信した後、Responder アプリケーションは `FCGI_END_REQUEST` レコードを送信する。アプリケーションは `protocolStatus` 構成要素を `FCGI_REQUEST_COMPLETE` に設定し、`appStatus` 構成要素を、CGI プログラムが `exit` システムコール経由で返したであろうステータスコードに設定する。

更新処理、たとえば `POST` メソッドの実装を行う Responder は、`FCGI_STDIN` 上で受信したバイト数を `CONTENT_LENGTH` と比較し、両者が等しくない場合は更新を中止すべきである。

### 6.3. Authorizer

Authorizer FastCGI アプリケーションは、HTTP リクエストに関連するすべての情報を受け取り、認可／非認可の判定を生成する。認可する場合、Authorizer は HTTP リクエストに name-value pair を関連付けることもできる。非認可とする場合、Authorizer は HTTP クライアントへ完全なレスポンスを送信する。

CGI/1.1 は HTTP リクエストに関連する情報を表現する十分によい方法をすでに定義しているので、Authorizer も同じ表現を用いる。

- Authorizer アプリケーションは、Responder と同じ形式で、HTTP リクエスト情報を Web サーバから `FCGI_PARAMS` ストリーム上で受け取る。Web サーバは `CONTENT_LENGTH`、`PATH_INFO`、`PATH_TRANSLATED`、`SCRIPT_NAME` ヘッダを送信しない。
- Authorizer アプリケーションは、Responder と同じ方法で `stdout` および `stderr` データを送信する。CGI/1.1 の応答ステータスが、そのリクエストの取り扱いを規定する。アプリケーションがステータス 200 (OK) を送信した場合、Web サーバはアクセスを許可する。設定に応じて、Web サーバは他の Authorizer への問い合わせを含む追加のアクセス検査を継続してもよい。

Authorizer アプリケーションの 200 応答には、名前が `Variable-` で始まるヘッダを含めてもよい。これらのヘッダは、アプリケーションから Web サーバへ name-value pair を伝達する。たとえば、次の応答ヘッダは

```text
Variable-AUTH_METHOD: database lookup
```

名前 `AUTH-METHOD` に値 `"database lookup"` を送る。サーバは、そのような name-value pair を HTTP リクエストに関連付け、その HTTP リクエストの処理中に後続で実行される CGI または FastCGI リクエストにそれらを含める。アプリケーションが 200 応答を返した場合、サーバは名前が `Variable-` で始まらない応答ヘッダを無視し、応答内容も無視する。

Authorizer の応答ステータス値が 200 (OK) 以外である場合、Web サーバはアクセスを拒否し、その応答ステータス、ヘッダ、および内容を HTTP クライアントへ返送する。

### 6.4. Filter

Filter FastCGI アプリケーションは、HTTP リクエストに関連するすべての情報に加えて、Web サーバに保存されたファイルからの追加データストリームを受け取り、そのデータストリームの「フィルタ済み」版を HTTP レスポンスとして生成する。

Filter は、データファイルをパラメータとして受け取る Responder と機能的に似ている。違いは、Filter では、データファイルと Filter 自身の両方に対して Web サーバのアクセス制御機構を適用できるのに対し、データファイル名をパラメータとして受け取る Responder は、そのデータファイルに対するアクセス制御検査を自前で行わなければならない点である。

Filter が取る手順は Responder の手順に似ている。サーバはまず環境変数を Filter に提示し、次に標準入力（通常は `POST` データ）、最後にデータファイル入力を提示する。

- Responder と同様に、Filter アプリケーションは Web サーバから `FCGI_PARAMS` 経由で name-value pair を受け取る。Filter アプリケーションは、Filter 固有の 2 つの変数 `FCGI_DATA_LAST_MOD` と `FCGI_DATA_LENGTH` を受け取る。
- 次に Filter アプリケーションは、CGI/1.1 の `stdin` データを Web サーバから `FCGI_STDIN` 経由で受け取る。アプリケーションは、このストリームから、終端表示を受け取る前に最大 `CONTENT_LENGTH` バイトを受け取る。（`CONTENT_LENGTH` より少ないのは、HTTP クライアントがそれらを提供できなかった場合、たとえばクライアントがクラッシュした場合だけである。）
- 次に Filter アプリケーションは、Web サーバから `FCGI_DATA` 経由でファイルデータを受け取る。このファイルの最終更新時刻（UTC の 1970 年 1 月 1 日からの秒数として表される整数）が `FCGI_DATA_LAST_MOD` である。アプリケーションはこの変数を参照し、ファイルデータを読まずにキャッシュから応答してもよい。アプリケーションは、終端表示を受け取る前に、このストリームから最大 `FCGI_DATA_LENGTH` バイトを読み取る。
- Filter アプリケーションは、CGI/1.1 の `stdout` データを `FCGI_STDOUT` 経由で Web サーバへ送り、CGI/1.1 の `stderr` データを `FCGI_STDERR` 経由で送る。アプリケーションはこれらを逐次ではなく並行に送る。アプリケーションは `FCGI_STDOUT` と `FCGI_STDERR` の書き込みを始める前に `FCGI_STDIN` の読み取りを終えていなければならないが、これら 2 ストリームの書き込みを始める前に `FCGI_DATA` の読み取りを終えている必要はない。
- すべての `stdout` および `stderr` データを送信した後、アプリケーションは `FCGI_END_REQUEST` レコードを送信する。アプリケーションは `protocolStatus` 構成要素を `FCGI_REQUEST_COMPLETE` に設定し、`appStatus` 構成要素を、類似の CGI プログラムが `exit` システムコール経由で返したであろうステータスコードに設定する。

Filter は、`FCGI_STDIN` 上で受け取ったバイト数と `CONTENT_LENGTH` を、また `FCGI_DATA` 上で受け取ったバイト数と `FCGI_DATA_LENGTH` を比較すべきである。これらの数が一致しない場合で、Filter が問い合わせであるなら、Filter の応答はデータ欠落を示すべきである。これらの数が一致しない場合で、Filter が更新であるなら、Filter は更新を中止すべきである。

## 7. Errors

FastCGI アプリケーションが 0 ステータスで終了するのは、意図的に終了したことを示す。たとえば、粗雑な形のガベージコレクションを行うためである。FastCGI アプリケーションが非 0 ステータスで終了した場合、それはクラッシュしたものとみなされる。0 または非 0 ステータスで終了したアプリケーションに対し、Web サーバや他のアプリケーション管理機構がどう応答するかは、本仕様の範囲外である。

Web サーバは、`SIGTERM` を送信することで FastCGI アプリケーションに終了を要求できる。アプリケーションが `SIGTERM` を無視した場合、Web サーバは `SIGKILL` に頼ることができる。

FastCGI アプリケーションは、`FCGI_STDERR` ストリームと `FCGI_END_REQUEST` レコードの `appStatus` 構成要素によって、アプリケーション・レベルのエラーを報告する。多くの場合、エラーは `FCGI_STDOUT` ストリームを通じて直接ユーザにも報告される。

Unix では、アプリケーションは、FastCGI プロトコル・エラーや FastCGI 環境変数の構文エラーを含む、より低レベルのエラーを `syslog` に報告する。エラーの重大度に応じて、アプリケーションは処理を継続することも、非 0 ステータスで終了することもある。

## 8. Types and constants

```c
/*
 * Listening socket file number
 */
#define FCGI_LISTENSOCK_FILENO 0

typedef struct {
    unsigned char version;
    unsigned char type;
    unsigned char requestIdB1;
    unsigned char requestIdB0;
    unsigned char contentLengthB1;
    unsigned char contentLengthB0;
    unsigned char paddingLength;
    unsigned char reserved;
} FCGI_Header;
/*
 * Number of bytes in a FCGI_Header.  Future versions of the protocol
 * will not reduce this number.
 */
#define FCGI_HEADER_LEN  8

/*
 * Value for version component of FCGI_Header
 */
#define FCGI_VERSION_1           1
/*
 * Values for type component of FCGI_Header
 */
#define FCGI_BEGIN_REQUEST       1
#define FCGI_ABORT_REQUEST       2
#define FCGI_END_REQUEST         3
#define FCGI_PARAMS              4
#define FCGI_STDIN               5
#define FCGI_STDOUT              6
#define FCGI_STDERR              7
#define FCGI_DATA                8
#define FCGI_GET_VALUES          9
#define FCGI_GET_VALUES_RESULT  10
#define FCGI_UNKNOWN_TYPE       11
#define FCGI_MAXTYPE (FCGI_UNKNOWN_TYPE)
/*
 * Value for requestId component of FCGI_Header
 */
#define FCGI_NULL_REQUEST_ID     0

typedef struct {
    unsigned char roleB1;
    unsigned char roleB0;
    unsigned char flags;
    unsigned char reserved[5];
} FCGI_BeginRequestBody;

typedef struct {
    FCGI_Header header;
    FCGI_BeginRequestBody body;
} FCGI_BeginRequestRecord;

/*
 * Mask for flags component of FCGI_BeginRequestBody
 */
#define FCGI_KEEP_CONN  1
/*
 * Values for role component of FCGI_BeginRequestBody
 */
#define FCGI_RESPONDER  1
#define FCGI_AUTHORIZER 2
#define FCGI_FILTER     3

typedef struct {
    unsigned char appStatusB3;
    unsigned char appStatusB2;
    unsigned char appStatusB1;
    unsigned char appStatusB0;
    unsigned char protocolStatus;
    unsigned char reserved[3];
} FCGI_EndRequestBody;
typedef struct {
    FCGI_Header header;
    FCGI_EndRequestBody body;
} FCGI_EndRequestRecord;

/*
 * Values for protocolStatus component of FCGI_EndRequestBody
 */
#define FCGI_REQUEST_COMPLETE 0
#define FCGI_CANT_MPX_CONN    1
#define FCGI_OVERLOADED       2
#define FCGI_UNKNOWN_ROLE     3
/*
 * Variable names for FCGI_GET_VALUES / FCGI_GET_VALUES_RESULT records
 */
#define FCGI_MAX_CONNS  "FCGI_MAX_CONNS"
#define FCGI_MAX_REQS   "FCGI_MAX_REQS"
#define FCGI_MPXS_CONNS "FCGI_MPXS_CONNS"

typedef struct {
    unsigned char type;
    unsigned char reserved[7];
} FCGI_UnknownTypeBody;

typedef struct {
    FCGI_Header header;
    FCGI_UnknownTypeBody body;
} FCGI_UnknownTypeRecord;
```

## 9. References

National Center for Supercomputer Applications, *The Common Gateway Interface*, version CGI/1.1.

D.R.T. Robinson, *The WWW Common Gateway Interface Version 1.1*, Internet-Draft, 15 February 1996.

### Updated references

W3C, *CGI: Common Gateway Interface*

Robinson, D. and K. Coar, *The Common Gateway Interface (CGI) Version 1.1*, RFC 3875, DOI 10.17487/RFC3875, October 2004

## A. Table: Properties of the record types

次の表は、すべてのレコード型を挙げ、それぞれについて以下の性質を示す。

- **WS->App**: この型のレコードは Web サーバからアプリケーションへしか送れない。他の型のレコードはアプリケーションから Web サーバへしか送れない。
- **management**: この型のレコードは Web サーバ・リクエスト固有ではない情報を含み、null request ID を使う。他の型のレコードはリクエスト固有の情報を含み、null request ID を使えない。
- **stream**: この型のレコードはストリームを形成し、空の `contentData` を持つレコードで終端される。他の型のレコードは discrete であり、それぞれが意味のある 1 単位のデータを運ぶ。

| Record Type | WS->App | management | stream |
|---|---:|---:|---:|
| `FCGI_GET_VALUES` | x | x |  |
| `FCGI_GET_VALUES_RESULT` |  | x |  |
| `FCGI_UNKNOWN_TYPE` |  | x |  |
| `FCGI_BEGIN_REQUEST` | x |  |  |
| `FCGI_ABORT_REQUEST` | x |  |  |
| `FCGI_END_REQUEST` |  |  |  |
| `FCGI_PARAMS` | x |  | x |
| `FCGI_STDIN` | x |  | x |
| `FCGI_DATA` | x |  | x |
| `FCGI_STDOUT` |  |  | x |
| `FCGI_STDERR` |  |  | x |

## B. Typical protocol message flow

例に関する追加の記法上の慣例:

- stream record（`FCGI_PARAMS`, `FCGI_STDIN`, `FCGI_STDOUT`, `FCGI_STDERR`）の `contentData` は文字列で表す。`" ... "` で終わる文字列は長すぎて表示できないため、接頭部だけを示している。
- Web サーバへ送られるメッセージは、Web サーバから受信するメッセージに対してインデントして示す。
- メッセージは、アプリケーションが経験する時系列順に示す。

### 1. `stdin` 上にデータがなく、応答が成功する単純なリクエスト

```text
{FCGI_BEGIN_REQUEST,   1, {FCGI_RESPONDER, 0}}
{FCGI_PARAMS,          1,
    "\013\002 SERVER_PORT80\013\016 SERVER_ADDR199.170.183.42 ... "}
{FCGI_PARAMS,          1, ""}
{FCGI_STDIN,           1, ""}

    {FCGI_STDOUT,      1, "Content-type: text/html\r\n\r\n<html>\n<head> ... "}
    {FCGI_STDOUT,      1, ""}
    {FCGI_END_REQUEST, 1, {0, FCGI_REQUEST_COMPLETE}}
```

### 2. 例 1 に似ているが、今回は `stdin` 上にデータがある場合

今回は Web サーバが、前回より多くの `FCGI_PARAMS` レコードを使ってパラメータを送ることを選んでいる。

```text
{FCGI_BEGIN_REQUEST,   1, {FCGI_RESPONDER, 0}}
{FCGI_PARAMS,          1, "\013\002 SERVER_PORT80\013\016 SER"}
{FCGI_PARAMS,          1, "VER_ADDR199.170.183.42 ... "}
{FCGI_PARAMS,          1, ""}
{FCGI_STDIN,           1, "quantity=100&item=3047936"}
{FCGI_STDIN,           1, ""}
    {FCGI_STDOUT,      1, "Content-type: text/html\r\n\r\n<html>\n<head> ... "}
    {FCGI_STDOUT,      1, ""}
    {FCGI_END_REQUEST, 1, {0, FCGI_REQUEST_COMPLETE}}
```

### 3. 例 1 に似ているが、今回はアプリケーションがエラーを検出した場合

アプリケーションは `stderr` にメッセージを記録し、クライアントへページを返し、非 0 の exit status を Web サーバへ返す。アプリケーションは、そのページをより多くの `FCGI_STDOUT` レコードに分けて送ることを選んでいる。

```text
{FCGI_BEGIN_REQUEST,   1, {FCGI_RESPONDER, 0}}
{FCGI_PARAMS,          1,
    "\013\002 SERVER_PORT80\013\016 SERVER_ADDR199.170.183.42 ... "}
{FCGI_PARAMS,          1, ""}
{FCGI_STDIN,           1, ""}
    {FCGI_STDOUT,      1, "Content-type: text/html\r\n\r\n<ht"}
    {FCGI_STDERR,      1, "config error: missing SI_UID\n"}
    {FCGI_STDOUT,      1, "ml>\n<head> ... "}
    {FCGI_STDOUT,      1, ""}
    {FCGI_STDERR,      1, ""}
    {FCGI_END_REQUEST, 1, {938, FCGI_REQUEST_COMPLETE}}
```

### 4. 例 1 の 2 インスタンスを単一接続上に多重化した場合

最初のリクエストのほうが 2 番目より難しいので、アプリケーションは順不同でリクエストを完了する。

```text
{FCGI_BEGIN_REQUEST,   1, {FCGI_RESPONDER, FCGI_KEEP_CONN}}
{FCGI_PARAMS,          1,
    "\013\002 SERVER_PORT80\013\016 SERVER_ADDR199.170.183.42 ... "}
{FCGI_PARAMS,          1, ""}
{FCGI_BEGIN_REQUEST,   2, {FCGI_RESPONDER, FCGI_KEEP_CONN}}
{FCGI_PARAMS,          2,
    "\013\002 SERVER_PORT80\013\016 SERVER_ADDR199.170.183.42 ... "}
{FCGI_STDIN,           1, ""}

    {FCGI_STDOUT,      1, "Content-type: text/html\r\n\r\n"}
{FCGI_PARAMS,          2, ""}
{FCGI_STDIN,           2, ""}

    {FCGI_STDOUT,      2, "Content-type: text/html\r\n\r\n<html>\n<head> ... "}
    {FCGI_STDOUT,      2, ""}
    {FCGI_END_REQUEST, 2, {0, FCGI_REQUEST_COMPLETE}}
    {FCGI_STDOUT,      1, "<html>\n<head> ... "}
    {FCGI_STDOUT,      1, ""}
    {FCGI_END_REQUEST, 1, {0, FCGI_REQUEST_COMPLETE}}
```
