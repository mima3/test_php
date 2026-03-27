<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>新規登録 | {{ config('app.name', 'Laravel TODO') }}</title>
        <style>
            :root {
                --bg: #f3efe5;
                --panel: #fffaf1;
                --line: #d7cfbf;
                --text: #20201d;
                --muted: #5f6358;
                --accent: #b85c38;
                --danger: #8f2d2d;
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 24px;
                font-family: "Hiragino Sans", "Yu Gothic", sans-serif;
                background:
                    radial-gradient(circle at top right, rgba(184, 92, 56, 0.18), transparent 28%),
                    linear-gradient(180deg, #f7f3ea 0%, var(--bg) 100%);
                color: var(--text);
            }
            .card {
                width: min(100%, 500px);
                padding: 28px;
                background: var(--panel);
                border: 1px solid var(--line);
                border-radius: 24px;
                box-shadow: 0 18px 50px rgba(64, 49, 34, 0.12);
            }
            h1 { margin: 0 0 8px; font-size: 32px; }
            p { color: var(--muted); line-height: 1.6; }
            label { display: block; margin: 16px 0; font-weight: 700; }
            input {
                width: 100%;
                margin-top: 8px;
                padding: 12px 14px;
                border: 1px solid var(--line);
                border-radius: 14px;
                background: #fffdf8;
                font: inherit;
            }
            .errors {
                padding: 14px 16px;
                border-radius: 16px;
                background: #f8e2e2;
                color: var(--danger);
            }
            button {
                border: 0;
                border-radius: 999px;
                padding: 12px 16px;
                font: inherit;
                font-weight: 700;
                background: var(--accent);
                color: white;
                cursor: pointer;
            }
            .footer { margin-top: 18px; color: var(--muted); }
            a { color: var(--accent); }
        </style>
    </head>
    <body>
        <main class="card">
            <h1>新規登録</h1>
            <p>アカウントを作成して TODO を管理します。</p>

            @if ($errors->any())
                <div class="errors">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}">
                @csrf
                <label>
                    名前
                    <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name">
                </label>

                <label>
                    メールアドレス
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                </label>

                <label>
                    パスワード
                    <input type="password" name="password" required autocomplete="new-password">
                </label>

                <label>
                    パスワード確認
                    <input type="password" name="password_confirmation" required autocomplete="new-password">
                </label>

                <button type="submit">登録する</button>
            </form>

            <p class="footer">すでにアカウントがある場合は <a href="{{ route('login') }}">ログイン</a></p>
        </main>
    </body>
</html>
