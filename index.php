<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>神戸市立図書館 座席WEB予約 業務支援・AI自動確保システム</title>
    <!-- Tailwind CSS (utility base) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: "Meiryo", "Hiragino Kaku Gothic ProN", "Yu Gothic", "MS PGothic", sans-serif;
            background-color: #f0f2f5;
            color: #333333;
        }
        /* JTC Enterprise UI Custom Classes */
        .jtc-header {
            background: linear-gradient(180deg, #1e3a8a 0%, #172554 100%);
            border-bottom: 3px solid #f59e0b;
        }
        .jtc-box {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .jtc-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            font-size: 13px;
        }
        .jtc-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            font-weight: 600;
            text-align: left;
        }
        .jtc-table td {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
        }
        .jtc-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .jtc-form-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            font-size: 13px;
        }
        .jtc-form-table th {
            background-color: #e2e8f0;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            padding: 9px 12px;
            width: 25%;
            font-weight: 600;
            text-align: left;
            vertical-align: middle;
        }
        .jtc-form-table td {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            vertical-align: middle;
        }
        .jtc-tab-btn {
            border: 1px solid #cbd5e1;
            border-bottom: none;
            background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
            color: #334155;
            font-weight: 600;
            padding: 8px 16px;
            font-size: 13px;
            cursor: pointer;
            margin-right: 2px;
            border-radius: 4px 4px 0 0;
        }
        .jtc-tab-btn:hover {
            background: #ffffff;
            color: #0f172a;
        }
        .jtc-tab-btn.active {
            background: #ffffff;
            color: #1e3a8a;
            border-top: 3px solid #2563eb;
            border-bottom: 1px solid #ffffff;
            font-weight: 700;
        }
        .jtc-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 3px;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.1s ease-in-out;
        }
        .jtc-btn-primary {
            background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
            border-color: #1e40af;
            color: #ffffff;
        }
        .jtc-btn-primary:hover {
            background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
        }
        .jtc-btn-success {
            background: linear-gradient(180deg, #16a34a 0%, #15803d 100%);
            border-color: #166534;
            color: #ffffff;
        }
        .jtc-btn-success:hover {
            background: linear-gradient(180deg, #22c55e 0%, #16a34a 100%);
        }
        .jtc-btn-danger {
            background: linear-gradient(180deg, #dc2626 0%, #b91c1c 100%);
            border-color: #991b1b;
            color: #ffffff;
        }
        .jtc-btn-danger:hover {
            background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);
        }
        .jtc-btn-warning {
            background: linear-gradient(180deg, #ea580c 0%, #c2410c 100%);
            border-color: #9a3412;
            color: #ffffff;
        }
        .jtc-btn-default {
            background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
            border-color: #cbd5e1;
            color: #334155;
        }
        .jtc-btn-default:hover {
            background: #ffffff;
        }
        .jtc-input {
            border: 1px solid #94a3b8;
            padding: 5px 8px;
            font-size: 13px;
            border-radius: 2px;
            background-color: #ffffff;
            color: #0f172a;
        }
        .jtc-input:focus {
            outline: 2px solid #2563eb;
            border-color: #2563eb;
        }
        .jtc-badge-required {
            background-color: #ef4444;
            color: #ffffff;
            font-size: 11px;
            padding: 1px 5px;
            border-radius: 2px;
            margin-right: 4px;
        }
        .jtc-badge-any {
            background-color: #64748b;
            color: #ffffff;
            font-size: 11px;
            padding: 1px 5px;
            border-radius: 2px;
            margin-right: 4px;
        }
        .jtc-notice-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #f59e0b;
            padding: 10px 14px;
            font-size: 12px;
            line-height: 1.6;
        }
        .jtc-info-box {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #3b82f6;
            padding: 10px 14px;
            font-size: 12px;
            line-height: 1.6;
        }
        .section-header {
            background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            padding: 7px 12px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 2px 2px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col text-slate-800">

    <!-- Top Government/JTC Style Header Bar -->
    <header class="jtc-header text-white">
        <div class="max-w-7xl mx-auto px-4 py-3 flex flex-col md:flex-row md:items-center justify-between gap-2">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 bg-white text-blue-900 font-extrabold flex items-center justify-center rounded-sm text-lg border border-blue-300 shadow">
                    館
                </div>
                <div>
                    <div class="text-[11px] text-blue-200 tracking-wider font-semibold">神戸市立図書館 庁内端末業務支援系システム</div>
                    <h1 class="text-base sm:text-lg font-bold tracking-tight">座席WEB予約 業務支援・AI自動確保ポータル (eBooth-AI v2.4)</h1>
                </div>
            </div>

            <!-- Current User / Status Strip -->
            <div class="flex items-center space-x-2 text-xs bg-blue-950/80 px-3 py-1.5 rounded border border-blue-800">
                <span class="text-blue-300">ログイン利用者:</span>
                <span id="header-user-code" class="font-bold text-amber-300 font-mono">未認証</span>
                <span id="header-user-name" class="text-slate-300 text-[11px]"></span>
                <span class="text-blue-600">|</span>
                <span class="text-slate-300">同期: <span id="header-time" class="font-mono font-semibold">取得中</span></span>
            </div>
        </div>
    </header>

    <!-- Sub-status Breadcrumb & Quick Action Strip -->
    <div class="bg-slate-200 border-b border-slate-300 py-1.5 px-4 text-xs text-slate-600">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-1 font-semibold">
                <span>ポータルトップ</span>
                <span>&gt;</span>
                <span class="text-blue-900" id="current-breadcrumb">AI最適予約・自動支援コンソール</span>
            </div>
            <div class="flex items-center space-x-3 text-[11px]">
                <span>対象基幹: <strong class="text-blue-950">神戸市立図書館 eBoothWeb</strong></span>
                <span>セッション暗号化: <strong class="text-emerald-700">HTTPS (TLS1.3)</strong></span>
            </div>
        </div>
    </div>

    <!-- Main Workspace -->
    <main class="max-w-7xl w-full mx-auto px-4 py-4 flex-1 space-y-4">

        <!-- Important Notice Box (JTC Style) -->
        <div class="jtc-notice-box flex items-start space-x-2">
            <span class="font-bold text-amber-800 shrink-0">【重要なお知らせ】</span>
            <div class="text-slate-700">
                本システムは、神戸市立図書館（垂水・中央・東灘・北神・名谷・西）の座席予約システムと直結し、<strong>AIアルゴリズムによる最適時間判定、キャンセル発生時の即時自動確保、受付開始時の最速ピンポイント確保</strong>を実行する業務支援ツールです。図書館カードの利用者番号（P+数字）とK-libネットパスワードがそのまま使用可能です。
            </div>
        </div>

        <!-- System Notification Toast (Banner) -->
        <div id="toast-banner" class="hidden p-3 rounded text-xs font-bold border flex items-center justify-between">
            <span id="toast-text"></span>
            <button type="button" onclick="hideToast()" class="text-slate-500 hover:text-slate-800 font-bold ml-4">✕ 閉じる</button>
        </div>

        <!-- Tab Navigation (JTC Tab Header) -->
        <div class="flex flex-wrap border-b border-slate-300 pt-1 -mb-[1px]">
            <button type="button" onclick="switchTab('ai_scheduler')" id="tab-btn-ai_scheduler" class="jtc-tab-btn active">
                ■ AI 最適時間 予約支援
            </button>
            <button type="button" onclick="switchTab('instant_snipe')" id="tab-btn-instant_snipe" class="jtc-tab-btn">
                ■ 空席即時確保（スナイプ）
            </button>
            <button type="button" onclick="switchTab('absolute_sniper')" id="tab-btn-absolute_sniper" class="jtc-tab-btn">
                ■ 指定日時絶対確保（ピンポイント）
            </button>
            <button type="button" onclick="switchTab('tasks')" id="tab-btn-tasks" class="jtc-tab-btn">
                ■ 自動監視タスク台帳 (<span id="task-badge-count">0</span>)
            </button>
            <button type="button" onclick="switchTab('my_reservations')" id="tab-btn-my_reservations" class="jtc-tab-btn">
                ■ 予約確認・取消管理
            </button>
            <button type="button" onclick="switchTab('account')" id="tab-btn-account" class="jtc-tab-btn">
                ■ 図書館アカウント認証設定
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: AI 最適時間 予約支援 -->
        <!-- ========================================== -->
        <section id="tab-content-ai_scheduler" class="space-y-4">
            <div class="jtc-box">
                <div class="section-header">
                    <span>▼ AI 座席予約パラメータ設定 & 最適スロット自動診断</span>
                    <span class="text-xs font-normal text-blue-100">※利用目的に合わせてAIが覚醒度・静寂度・混雑傾向を自動スコアリングします</span>
                </div>
                <div class="p-4 space-y-4">
                    <table class="jtc-form-table">
                        <tbody>
                            <tr>
                                <th><span class="jtc-badge-required">必須</span>利用目的プロファイル</th>
                                <td>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        <label class="flex items-center space-x-1.5 p-2 bg-slate-50 border border-slate-300 rounded cursor-pointer hover:bg-blue-50">
                                            <input type="radio" name="purpose" value="focus" checked onchange="changePurpose('focus')" class="text-blue-600">
                                            <span class="font-bold text-xs text-blue-950">① 集中学習</span>
                                        </label>
                                        <label class="flex items-center space-x-1.5 p-2 bg-slate-50 border border-slate-300 rounded cursor-pointer hover:bg-blue-50">
                                            <input type="radio" name="purpose" value="pc_work" onchange="changePurpose('pc_work')" class="text-blue-600">
                                            <span class="font-bold text-xs text-blue-950">② PC作業・タイピング</span>
                                        </label>
                                        <label class="flex items-center space-x-1.5 p-2 bg-slate-50 border border-slate-300 rounded cursor-pointer hover:bg-blue-50">
                                            <input type="radio" name="purpose" value="long_study" onchange="changePurpose('long_study')" class="text-blue-600">
                                            <span class="font-bold text-xs text-blue-950">③ 長時間・試験勉強</span>
                                        </label>
                                        <label class="flex items-center space-x-1.5 p-2 bg-slate-50 border border-slate-300 rounded cursor-pointer hover:bg-blue-50">
                                            <input type="radio" name="purpose" value="quick_read" onchange="changePurpose('quick_read')" class="text-blue-600">
                                            <span class="font-bold text-xs text-blue-950">④ 読書・軽読</span>
                                        </label>
                                    </div>
                                    <div id="purpose-desc" class="text-xs text-slate-500 mt-1.5 font-sans">
                                        【集中学習モード】午前および夕方の静寂時間帯を優先選定し、周囲の出入りが少ない快適な枠を自動判定します。
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="jtc-badge-required">必須</span>対象施設（図書館）</th>
                                <td>
                                    <select id="ai-area" class="jtc-input w-full sm:w-80">
                                        <option value="60000" selected>垂水図書館</option>
                                        <option value="30000">中央図書館</option>
                                        <option value="40000">東灘図書館</option>
                                        <option value="50000">北神図書館</option>
                                        <option value="10000">名谷図書館</option>
                                        <option value="20000">西図書館</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="jtc-badge-required">必須</span>座席種別（コーナー）</th>
                                <td>
                                    <select id="ai-corner" class="jtc-input w-full sm:w-80">
                                        <option value="62000" selected>2F キャレル席</option>
                                        <option value="61000">2F 南カウンター席</option>
                                        <option value="63000">2F 西カウンター席</option>
                                        <option value="64000">3F 学習室</option>
                                        <option value="66000">セミナー室</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="jtc-badge-required">必須</span>希望利用日</th>
                                <td>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button type="button" onclick="setPresetDate('TODAY')" class="jtc-btn jtc-btn-default text-xs">本日</button>
                                        <button type="button" onclick="setPresetDate('TOMORROW')" class="jtc-btn jtc-btn-default text-xs">明日</button>
                                        <button type="button" onclick="setPresetDate('THIS_WEEKEND')" class="jtc-btn jtc-btn-default text-xs">今週末(土)</button>
                                        <input type="date" id="ai-date" class="jtc-input">
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex justify-end space-x-2 pt-1">
                        <button type="button" onclick="executeAiAnalysis()" class="jtc-btn jtc-btn-primary py-2 px-6">
                            ▶ AI 最適スロット診断 & 空席マトリクス解析を実行
                        </button>
                    </div>
                </div>
            </div>

            <!-- AI Output Table / Cards -->
            <div class="jtc-box">
                <div class="section-header">
                    <span>▼ AI 推奨スロット判定結果台帳</span>
                    <span id="ai-result-count" class="text-xs font-normal bg-blue-900 text-blue-100 px-2 py-0.5 rounded">未診断</span>
                </div>
                <div class="p-4">
                    <div id="ai-result-container" class="space-y-3">
                        <div class="p-8 text-center text-slate-500 text-xs">
                            上記パラメータを設定後、「AI 最適スロット診断 & 空席マトリクス解析を実行」ボタンを押下してください。
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- TAB 2: 空席即時確保（スナイプ） -->
        <!-- ========================================== -->
        <section id="tab-content-instant_snipe" class="hidden space-y-4">
            <div class="jtc-box">
                <div class="section-header">
                    <span>▼ 開いている時間を直ぐに取る（即時空き枠スナイパー）</span>
                    <span class="text-xs font-normal text-blue-100">※キャンセル等で解放された空席を検知し即座に予約を確定します</span>
                </div>
                <div class="p-4 space-y-4">
                    <div class="jtc-info-box">
                        <strong>【即時スナイプ機能概要】</strong><br>
                        指定した日付・座席において、現在空いている枠を直ちに予約確保します。万一満席の場合でも、バックグラウンド監視ワーカーが常駐し、他利用者のキャンセルが発生した瞬間にミリ秒単位で奪取します。
                    </div>

                    <table class="jtc-form-table">
                        <tbody>
                            <tr>
                                <th>対象日付</th>
                                <td><input type="date" id="snipe-date" class="jtc-input"></td>
                            </tr>
                            <tr>
                                <th>座席コーナー</th>
                                <td>
                                    <select id="snipe-corner" class="jtc-input w-full sm:w-80">
                                        <option value="62000" selected>2F キャレル席</option>
                                        <option value="61000">2F 南カウンター席</option>
                                        <option value="63000">2F 西カウンター席</option>
                                        <option value="64000">3F 学習室</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>時間帯指定</th>
                                <td>
                                    <select id="snipe-time" class="jtc-input w-full sm:w-80">
                                        <option value="ANY" selected>空いている枠ならいつでも可 (最速確保)</option>
                                        <option value="10:10">10:10 ～ 12:10 (第1枠: 午前)</option>
                                        <option value="12:15">12:15 ～ 14:15 (第2枠: 昼)</option>
                                        <option value="14:20">14:20 ～ 16:20 (第3枠: 午後)</option>
                                        <option value="16:25">16:25 ～ 18:25 (第4枠: 夕方)</option>
                                        <option value="18:30">18:30 ～ 20:30 (第5枠: 夜間)</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex justify-between items-center pt-2">
                        <button type="button" onclick="loadLiveVacancies()" class="jtc-btn jtc-btn-default">
                            ⟳ 最新空席状況を照会
                        </button>
                        <button type="button" onclick="createInstantSnipeTask()" class="jtc-btn jtc-btn-warning py-2 px-6">
                            ⚡ 空き枠即時奪取タスクを起動
                        </button>
                    </div>

                    <!-- Live Grid -->
                    <div class="mt-4 pt-4 border-t border-slate-200">
                        <div class="font-bold text-xs text-slate-700 mb-2">【現時点の空席一覧】</div>
                        <div id="live-vacancies-list" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                            <div class="p-4 text-center text-slate-500 text-xs col-span-full border border-dashed border-slate-300">
                                照会ボタンを押下して空席状況を取得してください。
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- TAB 3: 指定日時絶対確保（ピンポイント） -->
        <!-- ========================================== -->
        <section id="tab-content-absolute_sniper" class="hidden space-y-4">
            <div class="jtc-box">
                <div class="section-header">
                    <span>▼ 取りたい時間に絶対に取る（ピンポイント・ミリ秒スナイパー）</span>
                    <span class="text-xs font-normal text-blue-100">※受付開始日時や特定枠をミリ秒単位で強制ロックします</span>
                </div>
                <div class="p-4 space-y-4">
                    <div class="jtc-notice-box">
                        <strong>【ピンポイント確保仕様】</strong><br>
                        1週間前の予約解禁時刻（朝9:00等）や激戦時間帯をピンポイント指定します。事前キャッシュしたCSRFトークンを用い、受付開始と同時に200ms周期のミリ秒連続リクエストを投入して枠を確実に奪取します。
                    </div>

                    <table class="jtc-form-table">
                        <tbody>
                            <tr>
                                <th><span class="jtc-badge-required">必須</span>確保目標日</th>
                                <td><input type="date" id="target-date" class="jtc-input"></td>
                            </tr>
                            <tr>
                                <th><span class="jtc-badge-required">必須</span>確保目標スロット</th>
                                <td>
                                    <select id="target-time" class="jtc-input w-full sm:w-80">
                                        <option value="10:10">10:10 ～ 12:10 (第1枠)</option>
                                        <option value="12:15">12:15 ～ 14:15 (第2枠)</option>
                                        <option value="14:20">14:20 ～ 16:20 (第3枠)</option>
                                        <option value="16:25">16:25 ～ 18:25 (第4枠)</option>
                                        <option value="18:30">18:30 ～ 20:30 (第5枠)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="jtc-badge-any">任意</span>予約解禁日時 (タイマー起動時刻)</th>
                                <td>
                                    <input type="datetime-local" id="target-launch-time" class="jtc-input">
                                    <span class="text-xs text-slate-500 ml-2">※未指定時は直ちにミリ秒アタックを開始します。</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex justify-end pt-2">
                        <button type="button" onclick="createAbsoluteSniperTask()" class="jtc-btn jtc-btn-danger py-2 px-6">
                            🎯 ピンポイント絶対取得タスクを登録
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- TAB 4: 自動監視タスク管理台帳 -->
        <!-- ========================================== -->
        <section id="tab-content-tasks" class="hidden space-y-4">
            <div class="jtc-box">
                <div class="section-header">
                    <span>▼ 自動予約・監視タスク管理台帳</span>
                    <button type="button" onclick="loadStatus()" class="text-xs bg-blue-900 hover:bg-blue-800 text-white px-2 py-1 rounded border border-blue-400">
                        ⟳ 最新台帳更新
                    </button>
                </div>
                <div class="p-4 overflow-x-auto">
                    <table class="jtc-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">管理番号</th>
                                <th style="width: 140px;">タスク種別</th>
                                <th>対象日 / 時間枠</th>
                                <th style="width: 100px;">状態</th>
                                <th style="width: 90px;">試行回数</th>
                                <th>最終実行結果 / ログ</th>
                                <th style="width: 130px; text-align: center;">処理操作</th>
                            </tr>
                        </thead>
                        <tbody id="task-table-body">
                            <tr><td colspan="7" class="text-center py-4 text-slate-500">データを取得中...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- TAB 5: 予約確認・取消管理 -->
        <!-- ========================================== -->
        <section id="tab-content-my_reservations" class="hidden space-y-4">
            <div class="jtc-box">
                <div class="section-header">
                    <span>▼ 現在取得済みの座席予約一覧</span>
                    <button type="button" onclick="loadMyReservations()" class="text-xs bg-blue-900 hover:bg-blue-800 text-white px-2 py-1 rounded border border-blue-400">
                        ⟳ 図書館システムから再照会
                    </button>
                </div>
                <div class="p-4">
                    <div id="my-reservations-list" class="space-y-2">
                        <div class="text-center py-6 text-slate-500 text-xs">予約データを照会中...</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- TAB 6: 図書館アカウント認証設定 -->
        <!-- ========================================== -->
        <section id="tab-content-account" class="hidden space-y-4">
            <div class="jtc-box">
                <div class="section-header">
                    <span>▼ 神戸市立図書館 公式アカウント認証設定</span>
                </div>
                <div class="p-4 space-y-4">
                    <div class="jtc-info-box">
                        <strong>【認証情報について】</strong><br>
                        神戸市立図書館の<strong>図書館カード番号（利用者番号: Pから始まる半角英数字）</strong>と、K-libネットで利用している<strong>パスワード</strong>を入力してください。本システムが公式予約システム（eBoothWeb）に対して自動認証・セッション確立を行い、予約処理を代行します。
                    </div>

                    <form onsubmit="handleAccountSave(event)" class="space-y-4">
                        <table class="jtc-form-table">
                            <tbody>
                                <tr>
                                    <th><span class="jtc-badge-required">必須</span>利用者番号 (カード番号)</th>
                                    <td>
                                        <input type="text" id="acc-usercode" placeholder="例: P182790698" required class="jtc-input w-full sm:w-80 font-mono">
                                        <span class="text-xs text-slate-500 block mt-1">※先頭のアルファベット「P」は大文字・半角で入力してください。</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><span class="jtc-badge-required">必須</span>K-libネット パスワード</th>
                                    <td>
                                        <input type="password" id="acc-password" placeholder="パスワードを入力" required class="jtc-input w-full sm:w-80 font-mono">
                                        <span class="text-xs text-slate-500 block mt-1">※図書館公式ウェブサイトで登録したパスワードです。</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><span class="jtc-badge-any">任意</span>アカウント識別名</th>
                                    <td>
                                        <input type="text" id="acc-name" placeholder="例: 個人用カード" class="jtc-input w-full sm:w-80">
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="flex justify-end">
                            <button type="submit" class="jtc-btn jtc-btn-primary py-2 px-6">
                                💾 図書館システムと接続検証を行い保存
                            </button>
                        </div>
                    </form>

                    <!-- Registered Accounts Table -->
                    <div class="mt-6 pt-4 border-t border-slate-200">
                        <div class="font-bold text-xs text-slate-700 mb-2">【登録済みアカウント台帳】</div>
                        <table class="jtc-table">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">管理ID</th>
                                    <th>利用者番号</th>
                                    <th>識別名</th>
                                    <th>登録日時</th>
                                    <th style="width: 90px; text-align: center;">操作</th>
                                </tr>
                            </thead>
                            <tbody id="registered-accounts-body">
                                <tr><td colspan="5" class="text-center py-3 text-slate-500">アカウント情報読み込み中...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-slate-800 text-slate-400 text-[11px] py-4 border-t border-slate-700 mt-6">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                神戸市立図書館 座席WEB予約 業務支援・AI自動確保システム (eBooth-AI Engine)<br>
                推奨動作環境: Microsoft Edge / Google Chrome 最新版 (JavaScript / Cookie有効)
            </div>
            <div class="text-right">
                内部接続先: ebwebreserve3.tackport.co.jp (Port 443)<br>
                &copy; 2026 Libraryes Support Infrastructure. All Rights Reserved.
            </div>
        </div>
    </footer>

    <!-- Client-side Controller Script -->
    <script>
        let currentPurpose = 'focus';
        let currentStatusData = null;

        document.addEventListener('DOMContentLoaded', () => {
            setPresetDate('TODAY');
            loadStatus();
            updateClock();
            setInterval(updateClock, 1000);
        });

        function updateClock() {
            const now = new Date();
            const str = now.getFullYear() + '/' + 
                        String(now.getMonth() + 1).padStart(2, '0') + '/' + 
                        String(now.getDate()).padStart(2, '0') + ' ' + 
                        String(now.getHours()).padStart(2, '0') + ':' + 
                        String(now.getMinutes()).padStart(2, '0') + ':' + 
                        String(now.getSeconds()).padStart(2, '0');
            const el = document.getElementById('header-time');
            if (el) el.textContent = str;
        }

        function showToast(msg, isSuccess = true) {
            const banner = document.getElementById('toast-banner');
            const text = document.getElementById('toast-text');
            text.textContent = msg;

            if (isSuccess) {
                banner.className = 'p-3 rounded text-xs font-bold border flex items-center justify-between bg-emerald-50 text-emerald-900 border-emerald-300';
            } else {
                banner.className = 'p-3 rounded text-xs font-bold border flex items-center justify-between bg-rose-50 text-rose-900 border-rose-300';
            }
            banner.classList.remove('hidden');
            setTimeout(hideToast, 6000);
        }

        function hideToast() {
            document.getElementById('toast-banner').classList.add('hidden');
        }

        function switchTab(tabId) {
            document.querySelectorAll('section[id^="tab-content-"]').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.jtc-tab-btn').forEach(el => el.classList.remove('active'));

            const sec = document.getElementById(`tab-content-${tabId}`);
            const btn = document.getElementById(`tab-btn-${tabId}`);
            if (sec) sec.classList.remove('hidden');
            if (btn) btn.classList.add('active');

            const bc = document.getElementById('current-breadcrumb');
            if (tabId === 'ai_scheduler') bc.textContent = 'AI最適予約・自動支援コンソール';
            if (tabId === 'instant_snipe') { bc.textContent = '空席即時確保（スナイプ）'; loadLiveVacancies(); }
            if (tabId === 'absolute_sniper') bc.textContent = '指定日時絶対確保（ピンポイント）';
            if (tabId === 'tasks') { bc.textContent = '自動監視タスク台帳'; loadStatus(); }
            if (tabId === 'my_reservations') { bc.textContent = '予約確認・取消管理'; loadMyReservations(); }
            if (tabId === 'account') { bc.textContent = '図書館アカウント認証設定'; loadStatus(); }
        }

        function setPresetDate(preset) {
            const aiDate = document.getElementById('ai-date');
            const snipeDate = document.getElementById('snipe-date');
            const targetDate = document.getElementById('target-date');

            let d = new Date();
            if (preset === 'TOMORROW') {
                d.setDate(d.getDate() + 1);
            } else if (preset === 'THIS_WEEKEND') {
                const day = d.getDay();
                d.setDate(d.getDate() + (6 - day)); // Saturday
            }

            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            const val = `${yyyy}-${mm}-${dd}`;

            if (aiDate) aiDate.value = val;
            if (snipeDate) snipeDate.value = val;
            if (targetDate) targetDate.value = val;
        }

        function changePurpose(purpose) {
            currentPurpose = purpose;
            const desc = document.getElementById('purpose-desc');
            if (purpose === 'focus') {
                desc.textContent = '【集中学習モード】午前および夕方の静寂時間帯を優先選定し、周囲の出入りが少ない快適な枠を自動判定します。';
            } else if (purpose === 'pc_work') {
                desc.textContent = '【PC作業モード】電源・キャレル席優先。活動的な午後の時間帯を含め作業しやすいスロットを推奨します。';
            } else if (purpose === 'long_study') {
                desc.textContent = '【長時間学習モード】連続確保しやすく、疲労度推移を考慮した安定した時間帯を優先します。';
            } else if (purpose === 'quick_read') {
                desc.textContent = '【読書・軽読モード】スキマ時間や開放感のある座席枠を最優先で推奨します。';
            }
        }

        async function loadStatus() {
            try {
                const res = await fetch('backend/api.php?action=status');
                const data = await res.json();
                if (data.success) {
                    currentStatusData = data;
                    renderHeaderAccount(data.accounts);
                    renderRegisteredAccounts(data.accounts);
                    renderTasksTable(data.tasks);
                }
            } catch (err) {
                console.error(err);
            }
        }

        function renderHeaderAccount(accounts) {
            const userCodeEl = document.getElementById('header-user-code');
            const userNameEl = document.getElementById('header-user-name');
            if (accounts && accounts.length > 0) {
                userCodeEl.textContent = accounts[0].usercode;
                userNameEl.textContent = `(${accounts[0].name || '利用者'})`;
            } else {
                userCodeEl.textContent = '未登録 (設定タブで登録要)';
                userNameEl.textContent = '';
            }
        }

        function renderRegisteredAccounts(accounts) {
            const tbody = document.getElementById('registered-accounts-body');
            if (!accounts || accounts.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-amber-700 bg-amber-50">アカウントが登録されていません。上記フォームから登録してください。</td></tr>`;
                return;
            }
            tbody.innerHTML = accounts.map(a => `
                <tr>
                    <td class="font-mono">${a.id}</td>
                    <td class="font-mono font-bold text-blue-900">${a.usercode}</td>
                    <td>${a.name || '-'}</td>
                    <td class="font-mono text-xs text-slate-500">${a.created_at}</td>
                    <td class="text-center">
                        <button type="button" onclick="deleteAccount(${a.id})" class="jtc-btn jtc-btn-danger text-xs py-0.5 px-2">削除</button>
                    </td>
                </tr>
            `).join('');
        }

        function renderTasksTable(tasks) {
            const tbody = document.getElementById('task-table-body');
            const badgeCount = document.getElementById('task-badge-count');
            const activeCount = tasks.filter(t => t.status === 'pending' || t.status === 'monitoring').length;
            badgeCount.textContent = activeCount;

            if (!tasks || tasks.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-slate-500">現在登録されているタスクはありません</td></tr>`;
                return;
            }

            tbody.innerHTML = tasks.map(t => {
                let badge = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-200 text-slate-700">待機中</span>';
                if (t.status === 'monitoring') badge = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">監視中</span>';
                if (t.status === 'success') badge = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">予約完了</span>';
                if (t.status === 'failed') badge = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-300">失敗</span>';

                let typeLabel = t.type;
                if (t.type === 'ai_optimal') typeLabel = 'AI 最適予約';
                if (t.type === 'instant_snipe') typeLabel = '即時スナイプ';
                if (t.type === 'absolute_sniper') typeLabel = '絶対確保スナイパー';

                return `
                    <tr>
                        <td class="font-mono text-slate-600">#${t.id}</td>
                        <td class="font-bold text-blue-950">${typeLabel}</td>
                        <td class="font-mono">${t.target_date} <strong class="text-blue-900">${t.target_time_slot || ''}</strong></td>
                        <td>${badge}</td>
                        <td class="font-mono text-center">${t.retry_count} / ${t.max_retries}</td>
                        <td class="text-xs text-slate-600 truncate max-w-xs" title="${t.result_message || ''}">${t.result_message || '-'}</td>
                        <td class="text-center space-x-1">
                            <button type="button" onclick="runTaskNow(${t.id})" class="jtc-btn jtc-btn-primary text-xs py-0.5 px-2">実行</button>
                            <button type="button" onclick="deleteTask(${t.id})" class="jtc-btn jtc-btn-danger text-xs py-0.5 px-2">削除</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function handleAccountSave(e) {
            e.preventDefault();
            const usercode = document.getElementById('acc-usercode').value.trim();
            const password = document.getElementById('acc-password').value.trim();
            const name = document.getElementById('acc-name').value.trim();

            showToast('図書館システムに接続し認証確認中...', true);
            try {
                const res = await fetch('backend/api.php?action=save_account', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({usercode, password, name})
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message, true);
                    loadStatus();
                } else {
                    showToast(data.message, false);
                }
            } catch (err) {
                showToast('通信エラーが発生しました。', false);
            }
        }

        async function deleteAccount(id) {
            if (!confirm('このアカウントを削除してもよろしいですか？')) return;
            const res = await fetch('backend/api.php?action=delete_account', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id})
            });
            const data = await res.json();
            if (data.success) {
                showToast('アカウントを削除しました。', true);
                loadStatus();
            }
        }

        async function executeAiAnalysis() {
            const date = document.getElementById('ai-date').value;
            const corner = document.getElementById('ai-corner').value;
            const container = document.getElementById('ai-result-container');
            const resultBadge = document.getElementById('ai-result-count');

            container.innerHTML = `<div class="text-center py-8 text-blue-900 text-xs font-bold">空席マトリクスを取得しAIスコアリングを計算中...</div>`;

            try {
                const res = await fetch(`backend/api.php?action=ai_recommend&date=${date}&corner=${corner}&purpose=${currentPurpose}`);
                const data = await res.json();

                if (!data.success || !data.top_recommendations || data.top_recommendations.length === 0) {
                    resultBadge.textContent = '候補 0件 (満席)';
                    resultBadge.className = 'text-xs font-normal bg-rose-800 text-white px-2 py-0.5 rounded';
                    container.innerHTML = `
                        <div class="jtc-notice-box">
                            <strong>【判定結果: 対象日は現在満席です】</strong><br>
                            指定の座席コーナーは現在空席がありません。「空き枠即時奪取タスク」を登録すると、キャンセルが出た瞬間にAIが自動で確保します。
                            <div class="mt-2">
                                <button type="button" onclick="createInstantSnipeForDate('${date}', '${corner}')" class="jtc-btn jtc-btn-warning text-xs">
                                    ⚡ この日のキャンセル待ち自動スナイパーを起動
                                </button>
                            </div>
                        </div>
                    `;
                    return;
                }

                resultBadge.textContent = `候補 ${data.top_recommendations.length}件 検出`;
                resultBadge.className = 'text-xs font-normal bg-emerald-800 text-white px-2 py-0.5 rounded';

                container.innerHTML = data.top_recommendations.map((slot, idx) => `
                    <div class="border ${idx === 0 ? 'border-blue-500 bg-blue-50/50' : 'border-slate-300 bg-white'} p-4 rounded shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 text-xs font-bold ${idx === 0 ? 'bg-blue-700 text-white' : 'bg-slate-600 text-white'} rounded">
                                    第 ${idx + 1} 推奨枠
                                </span>
                                <span class="text-base font-bold text-blue-950 font-mono">${slot.time || slot.raw_label}</span>
                                <span class="text-xs px-2 py-0.5 rounded font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    ${slot.recommendation_tag}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 mt-1 font-mono">対象日: ${slot.date} | 座席枠ID: ${slot.slot_id}</div>
                            <div class="text-xs text-slate-700 mt-2 bg-white/80 p-2 rounded border border-slate-200">
                                <strong class="text-blue-900">■ AI 判定理由:</strong>
                                <ul class="list-disc list-inside mt-0.5 text-slate-600">
                                    ${slot.reasons.map(r => `<li>${r}</li>`).join('')}
                                </ul>
                            </div>
                        </div>

                        <div class="flex md:flex-col items-end justify-between md:justify-center gap-2 shrink-0">
                            <div class="text-right">
                                <span class="text-[11px] text-slate-500 block">AI 総合快適度</span>
                                <span class="text-2xl font-extrabold text-blue-900 font-mono">${slot.ai_score}<span class="text-xs text-slate-400"> /100点</span></span>
                            </div>
                            <button type="button" onclick="quickReserve('${slot.date}', '${slot.slot_id}', '${corner}')" class="jtc-btn jtc-btn-success py-1.5 px-4 text-xs">
                                ✔ この推奨枠を今すぐ予約
                            </button>
                        </div>
                    </div>
                `).join('');

            } catch (err) {
                container.innerHTML = `<div class="p-4 text-center text-rose-700 text-xs font-bold">解析中に通信エラーが発生しました。</div>`;
            }
        }

        async function quickReserve(date, slotId, corner) {
            showToast('図書館予約システムへ予約リクエストを投入中...', true);
            try {
                const res = await fetch('backend/api.php?action=quick_reserve', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({date, slot_id: slotId, corner_code: corner})
                });
                const data = await res.json();
                if (data.success) {
                    showToast(`予約確定完了: ${date} (予約番号: ${data.data.reservation_number || '受領済み'})`, true);
                    loadMyReservations();
                } else {
                    showToast(data.message, false);
                }
            } catch (err) {
                showToast('予約送信エラーが発生しました。', false);
            }
        }

        async function loadLiveVacancies() {
            const date = document.getElementById('snipe-date').value;
            const corner = document.getElementById('snipe-corner').value;
            const container = document.getElementById('live-vacancies-list');

            container.innerHTML = `<div class="p-4 text-center text-slate-500 text-xs col-span-full">空席状況をスキャン中...</div>`;
            try {
                const res = await fetch(`backend/api.php?action=public_vacancies&date=${date}&corner=${corner}`);
                const data = await res.json();

                if (!data.success || !data.data.slots || data.data.slots.length === 0) {
                    container.innerHTML = `<div class="p-4 text-center text-amber-800 bg-amber-50 border border-amber-200 text-xs col-span-full">現在表示可能な空席はありません。自動スナイパーの待機を推奨します。</div>`;
                    return;
                }

                container.innerHTML = data.data.slots.map(s => `
                    <div class="border border-emerald-300 bg-emerald-50/50 p-2.5 rounded flex items-center justify-between">
                        <div>
                            <strong class="text-xs text-emerald-950 font-mono block">${s.label}</strong>
                            <span class="text-[10px] text-slate-500 font-mono">枠: ${s.slot_id}</span>
                        </div>
                        <button type="button" onclick="quickReserve('${s.date}', '${s.slot_id}', '${corner}')" class="jtc-btn jtc-btn-success text-xs py-1 px-2.5">
                            即時取得
                        </button>
                    </div>
                `).join('');
            } catch (err) {
                container.innerHTML = `<div class="p-4 text-center text-rose-700 text-xs col-span-full">空席取得エラー</div>`;
            }
        }

        async function createInstantSnipeTask() {
            const date = document.getElementById('snipe-date').value;
            const corner = document.getElementById('snipe-corner').value;
            const time = document.getElementById('snipe-time').value;

            showToast('即時スナイパー・監視タスクを登録中...', true);
            const res = await fetch('backend/api.php?action=create_task', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    type: 'instant_snipe',
                    target_date: date,
                    corner_code: corner,
                    target_time_slot: time,
                    run_now: true,
                    max_retries: 50
                })
            });
            const data = await res.json();
            if (data.success) {
                showToast('即時スナイパーが正常に登録・起動されました。', true);
                switchTab('tasks');
            } else {
                showToast(data.message, false);
            }
        }

        async function createInstantSnipeForDate(date, corner) {
            document.getElementById('snipe-date').value = date;
            document.getElementById('snipe-corner').value = corner;
            createInstantSnipeTask();
        }

        async function createAbsoluteSniperTask() {
            const date = document.getElementById('target-date').value;
            const time = document.getElementById('target-time').value;
            const launch = document.getElementById('target-launch-time').value;

            showToast('ピンポイント絶対取得タスクを登録中...', true);
            const res = await fetch('backend/api.php?action=create_task', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    type: 'absolute_sniper',
                    target_date: date,
                    corner_code: '62000',
                    target_time_slot: time,
                    execute_at: launch || null,
                    max_retries: 100
                })
            });
            const data = await res.json();
            if (data.success) {
                showToast('ピンポイント絶対取得タスクが登録されました。', true);
                switchTab('tasks');
            } else {
                showToast(data.message, false);
            }
        }

        async function loadMyReservations() {
            const container = document.getElementById('my-reservations-list');
            container.innerHTML = `<div class="text-center py-6 text-slate-500 text-xs">図書館システムにログインし最新予約を照会中...</div>`;

            try {
                const res = await fetch('backend/api.php?action=my_reservations');
                const data = await res.json();
                if (data.success && data.reservations && data.reservations.length > 0) {
                    container.innerHTML = data.reservations.map(r => `
                        <div class="border border-slate-300 bg-white p-3 rounded flex items-center justify-between shadow-sm">
                            <div>
                                <span class="font-bold text-blue-950 font-mono text-sm">${r.date || ''} ${r.time || ''}</span>
                                <span class="text-xs text-slate-600 block mt-0.5">${r.raw || ''}</span>
                            </div>
                            <button type="button" onclick="cancelReservation('${r.id || '0'}')" class="jtc-btn jtc-btn-danger text-xs py-1 px-3">
                                予約取消
                            </button>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = `<div class="p-6 text-center text-slate-500 text-xs bg-slate-50 border border-slate-200">現在有効な予約はありません。</div>`;
                }
            } catch (err) {
                container.innerHTML = `<div class="p-6 text-center text-rose-700 text-xs bg-rose-50 border border-rose-200 font-bold">予約一覧の照会に失敗しました（アカウント設定を確認してください）。</div>`;
            }
        }

        async function cancelReservation(id) {
            if (!confirm('この予約を取り消します。よろしいですか？')) return;
            const res = await fetch('backend/api.php?action=cancel_reservation', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({slot_id: id})
            });
            const data = await res.json();
            if (data.success) {
                showToast('予約を取り消しました。', true);
                loadMyReservations();
            } else {
                showToast(data.message, false);
            }
        }

        async function runTaskNow(id) {
            showToast(`タスク #${id} を手動実行中...`, true);
            const res = await fetch('backend/api.php?action=run_task', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({task_id: id})
            });
            const data = await res.json();
            if (data.success) {
                showToast(`タスク実行結果: ${data.result.message || '完了'}`, true);
                loadStatus();
            } else {
                showToast(data.message, false);
            }
        }

        async function deleteTask(id) {
            if (!confirm(`タスク #${id} を削除しますか？`)) return;
            const res = await fetch('backend/api.php?action=delete_task', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({task_id: id})
            });
            const data = await res.json();
            if (data.success) {
                showToast('タスクを削除しました。', true);
                loadStatus();
            }
        }
    </script>
</body>
</html>
