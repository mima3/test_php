<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'TODO App') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/js/app.js'])
        @endif
        <style>
            :root {
                --bg: #f3efe5;
                --panel: #fffaf1;
                --line: #d7cfbf;
                --text: #20201d;
                --muted: #5f6358;
                --accent: #b85c38;
                --accent-dark: #8f4122;
                --success: #3f6b4f;
                --danger: #8f2d2d;
                --shadow: 0 18px 50px rgba(64, 49, 34, 0.12);
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: "Hiragino Sans", "Yu Gothic", sans-serif;
                color: var(--text);
                background:
                    radial-gradient(circle at top right, rgba(184, 92, 56, 0.18), transparent 28%),
                    linear-gradient(180deg, #f7f3ea 0%, var(--bg) 100%);
            }

            .page {
                width: min(1100px, calc(100% - 32px));
                margin: 0 auto;
                padding: 40px 0 64px;
            }

            .hero {
                display: grid;
                gap: 20px;
                margin-bottom: 24px;
            }

            .hero-card,
            .panel {
                background: color-mix(in srgb, var(--panel) 92%, white);
                border: 1px solid var(--line);
                border-radius: 24px;
                box-shadow: var(--shadow);
            }

            .hero-card {
                padding: 28px;
            }

            .top-nav {
                display: flex;
                justify-content: flex-end;
                align-items: center;
                gap: 12px;
                margin-bottom: 16px;
            }

            .top-nav span {
                color: var(--muted);
                font-size: 14px;
            }

            .eyebrow {
                display: inline-block;
                margin-bottom: 14px;
                padding: 6px 10px;
                border-radius: 999px;
                background: rgba(184, 92, 56, 0.1);
                color: var(--accent-dark);
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0.08em;
            }

            h1 {
                margin: 0 0 10px;
                font-size: clamp(32px, 5vw, 56px);
                line-height: 1;
            }

            .lead {
                margin: 0;
                max-width: 640px;
                color: var(--muted);
                line-height: 1.7;
            }

            .stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 12px;
                margin-top: 24px;
            }

            .hero-layout {
                display: grid;
                grid-template-columns: minmax(0, 1.4fr) minmax(300px, 0.9fr);
                gap: 20px;
                align-items: stretch;
            }

            .stat {
                padding: 18px;
                border-radius: 18px;
                background: white;
                border: 1px solid var(--line);
            }

            .stat strong {
                display: block;
                font-size: 28px;
                margin-bottom: 6px;
            }

            .content {
                display: grid;
                grid-template-columns: 340px 1fr;
                gap: 24px;
                align-items: start;
            }

            .panel {
                padding: 22px;
            }

            h2 {
                margin: 0 0 14px;
                font-size: 22px;
            }

            form {
                margin: 0;
            }

            label {
                display: block;
                margin-bottom: 14px;
                font-size: 14px;
                font-weight: 700;
            }

            input,
            select,
            textarea {
                width: 100%;
                margin-top: 8px;
                padding: 12px 14px;
                border: 1px solid var(--line);
                border-radius: 14px;
                background: #fffdf8;
                color: var(--text);
                font: inherit;
            }

            textarea {
                min-height: 120px;
                resize: vertical;
            }

            .btn-row {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }

            button {
                border: 0;
                border-radius: 999px;
                padding: 11px 16px;
                font: inherit;
                font-weight: 700;
                cursor: pointer;
            }

            .btn-primary {
                background: var(--accent);
                color: white;
            }

            .btn-secondary {
                background: #ece4d5;
                color: var(--text);
            }

            .btn-danger {
                background: #f2dede;
                color: var(--danger);
            }

            .todo-list {
                display: grid;
                gap: 16px;
            }

            .filter-bar {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 180px;
                gap: 12px;
                margin-bottom: 18px;
            }

            .filter-field {
                margin-bottom: 0;
            }

            .filter-empty {
                padding: 28px 20px;
                text-align: center;
                border: 1px dashed var(--line);
                border-radius: 18px;
                color: var(--muted);
                background: rgba(255, 255, 255, 0.65);
            }

            .todo-item {
                border-radius: 22px;
                border: 1px solid var(--line);
                background: white;
                overflow: hidden;
            }

            .todo-item.completed {
                background: #f5f8f1;
            }

            .todo-accordion {
                display: block;
            }

            .todo-accordion summary {
                list-style: none;
                cursor: pointer;
                padding: 20px;
            }

            .todo-accordion summary::-webkit-details-marker {
                display: none;
            }

            .todo-head {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                align-items: flex-start;
                margin: 0;
            }

            .todo-heading {
                display: flex;
                align-items: center;
                gap: 12px;
                min-width: 0;
            }

            .todo-chevron {
                width: 36px;
                height: 36px;
                flex: 0 0 36px;
                border-radius: 999px;
                display: grid;
                place-items: center;
                background: #efe7da;
                color: var(--accent-dark);
                font-size: 18px;
                font-weight: 700;
                transition: transform 0.2s ease;
            }

            .todo-accordion[open] .todo-chevron {
                transform: rotate(90deg);
            }

            .todo-title {
                margin: 0;
                font-size: 22px;
            }

            .todo-meta {
                margin: 8px 0 0;
                color: var(--muted);
                font-size: 14px;
            }

            .badge {
                display: inline-block;
                padding: 7px 10px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 700;
            }

            .badge-open {
                background: #fce7db;
                color: var(--accent-dark);
            }

            .badge-done {
                background: #dcecdc;
                color: var(--success);
            }

            .todo-description {
                margin: 0 0 16px;
                color: #45473f;
                line-height: 1.7;
                white-space: pre-wrap;
            }

            .todo-body {
                padding: 0 20px 20px;
                border-top: 1px solid rgba(215, 207, 191, 0.7);
            }

            .todo-actions {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                margin-top: 12px;
            }

            .flash,
            .errors {
                margin-bottom: 16px;
                padding: 14px 16px;
                border-radius: 16px;
            }

            .flash {
                background: #e4f1e6;
                color: var(--success);
            }

            .errors {
                background: #f8e2e2;
                color: var(--danger);
            }

            .empty {
                padding: 36px 20px;
                text-align: center;
                border: 1px dashed var(--line);
                border-radius: 20px;
                color: var(--muted);
            }

            .chart-card {
                padding: 24px;
                display: grid;
                grid-template-rows: auto 1fr auto;
                gap: 16px;
            }

            .chart-header h2 {
                margin-bottom: 6px;
            }

            .chart-header p,
            .chart-summary {
                margin: 0;
                color: var(--muted);
                line-height: 1.6;
            }

            .chart-shell {
                position: relative;
                min-height: 260px;
            }

            .chart-center {
                position: absolute;
                inset: 50% auto auto 50%;
                transform: translate(-50%, -50%);
                text-align: center;
                pointer-events: none;
            }

            .chart-center strong {
                display: block;
                font-size: clamp(28px, 4vw, 42px);
                line-height: 1;
            }

            .chart-center span {
                color: var(--muted);
                font-size: 13px;
                font-weight: 700;
                letter-spacing: 0.08em;
            }

            @media (max-width: 860px) {
                .hero-layout,
                .content {
                    grid-template-columns: 1fr;
                }

                .filter-bar {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <main class="page">
            <div class="top-nav">
                <span>{{ auth()->user()->name }} さんでログイン中</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary">ログアウト</button>
                </form>
            </div>

            <section class="hero">
                <div class="hero-layout">
                    <div class="hero-card">
                        <span class="eyebrow">LARAVEL TODO MANAGER</span>
                        <h1>今日やることを、見える形に。</h1>
                        <p class="lead">
                            PostgreSQL に保存されるシンプルな TODO 管理アプリです。追加、編集、完了切替、削除を 1 画面で完結できます。
                        </p>
                        <div class="stats">
                            <div class="stat">
                                <strong>{{ $remainingCount }}</strong>
                                <span>未完了</span>
                            </div>
                            <div class="stat">
                                <strong>{{ $completedCount }}</strong>
                                <span>完了済み</span>
                            </div>
                            <div class="stat">
                                <strong>{{ $todos->count() }}</strong>
                                <span>合計</span>
                            </div>
                        </div>
                    </div>

                    <div class="panel chart-card">
                        <div class="chart-header">
                            <h2>消化率</h2>
                            <p>Chart.js で、完了と未完了の比率を可視化しています。</p>
                        </div>

                        <div class="chart-shell">
                            <canvas
                                data-completion-chart
                                data-completed="{{ $completedCount }}"
                                data-remaining="{{ $remainingCount }}"
                            ></canvas>
                            <div class="chart-center">
                                <strong>
                                    {{ $todos->count() > 0 ? round(($completedCount / $todos->count()) * 100) : 0 }}%
                                </strong>
                                <span>COMPLETE</span>
                            </div>
                        </div>

                        <p class="chart-summary">
                            {{ $todos->count() }}件中 {{ $completedCount }}件が完了しています。
                        </p>
                    </div>
                </div>
            </section>

            <section class="content">
                <aside class="panel">
                    <h2>TODOを追加</h2>

                    @if (session('status'))
                        <div class="flash">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="errors">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('todos.store') }}">
                        @csrf
                        <label>
                            タイトル
                            <input type="text" name="title" value="{{ old('title') }}" required>
                        </label>

                        <label>
                            詳細
                            <textarea name="description">{{ old('description') }}</textarea>
                        </label>

                        <label>
                            期限
                            <input type="date" name="due_date" value="{{ old('due_date') }}">
                        </label>

                        <div class="btn-row">
                            <button type="submit" class="btn-primary">追加する</button>
                        </div>
                    </form>
                </aside>

                <section class="panel">
                    <h2>TODO一覧</h2>

                    @if ($todos->isEmpty())
                        <div class="empty">まだ TODO はありません。左のフォームから追加してください。</div>
                    @else
                        <div class="filter-bar" data-todo-filters>
                            <label class="filter-field">
                                タイトルで検索
                                <input type="search" placeholder="例: 買い物" data-filter-keyword>
                            </label>

                            <label class="filter-field">
                                ステータス
                                <select data-filter-status>
                                    <option value="all">すべて</option>
                                    <option value="open">未完了</option>
                                    <option value="completed">完了</option>
                                </select>
                            </label>
                        </div>

                        <div class="todo-list">
                            @foreach ($todos as $todo)
                                <article
                                    class="todo-item {{ $todo->is_completed ? 'completed' : '' }}"
                                    data-todo-item
                                    data-todo-title="{{ \Illuminate\Support\Str::lower($todo->title) }}"
                                    data-todo-status="{{ $todo->is_completed ? 'completed' : 'open' }}"
                                >
                                    <details class="todo-accordion">
                                        <summary>
                                            <div class="todo-head">
                                                <div class="todo-heading">
                                                    <span class="todo-chevron">›</span>
                                                    <div>
                                                        <h3 class="todo-title">{{ $todo->title }}</h3>
                                                        <p class="todo-meta">
                                                            作成日: {{ $todo->created_at->format('Y-m-d') }}
                                                            @if ($todo->due_date)
                                                                / 期限: {{ $todo->due_date->format('Y-m-d') }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                                <span class="badge {{ $todo->is_completed ? 'badge-done' : 'badge-open' }}">
                                                    {{ $todo->is_completed ? '完了' : '進行中' }}
                                                </span>
                                            </div>
                                        </summary>

                                        <div class="todo-body">
                                            @if ($todo->description)
                                                <p class="todo-description">{{ $todo->description }}</p>
                                            @endif

                                            <form method="POST" action="{{ route('todos.update', $todo) }}">
                                                @csrf
                                                @method('PATCH')

                                                <label>
                                                    タイトル
                                                    <input type="text" name="title" value="{{ $todo->title }}" required>
                                                </label>

                                                <label>
                                                    詳細
                                                    <textarea name="description">{{ $todo->description }}</textarea>
                                                </label>

                                                <label>
                                                    期限
                                                    <input
                                                        type="date"
                                                        name="due_date"
                                                        value="{{ optional($todo->due_date)->format('Y-m-d') }}"
                                                    >
                                                </label>

                                                <div class="todo-actions">
                                                    <button type="submit" class="btn-secondary">保存</button>
                                                </div>
                                            </form>

                                            <div class="todo-actions">
                                                <form method="POST" action="{{ route('todos.toggle', $todo) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn-primary">
                                                        {{ $todo->is_completed ? '未完了に戻す' : '完了にする' }}
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('todos.destroy', $todo) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-danger">削除</button>
                                                </form>
                                            </div>
                                        </div>
                                    </details>
                                </article>
                            @endforeach
                        </div>

                        <div class="filter-empty" data-filter-empty hidden>
                            条件に一致する TODO はありません。
                        </div>
                    @endif
                </section>
            </section>
        </main>
    </body>
</html>
