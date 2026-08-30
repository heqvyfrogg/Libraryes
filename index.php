<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>神戸市立図書館 座席予約支援・AI自動確保システム</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Meiryo", "Hiragino Kaku Gothic ProN", "Yu Gothic", sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        /* JTC Enterprise Professional Styling */
        .jtc-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%);
            border-bottom: 3px solid #f59e0b;
        }
        .jtc-panel {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .section-bar {
            background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 3px 3px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .jtc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .jtc-table th {
            background-color: #f8fafc;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            font-weight: 600;
        }
        .jtc-table td {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
        }
        .jtc-form-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .jtc-form-table th {
            background-color: #f8fafc;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            padding: 9px 14px;
            width: 24%;
            font-weight: 600;
            text-align: left;
            vertical-align: middle;
        }
        .jtc-form-table td {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 9px 14px;
            vertical-align: middle;
        }
        .jtc-tab-btn {
            border: 1px solid #cbd5e1;
            border-bottom: none;
            background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
            color: #475569;
            font-weight: 600;
            padding: 8px 16px;
            font-size: 13px;
            cursor: pointer;
            border-radius: 4px 4px 0 0;
            margin-right: 2px;
            transition: all 0.15s ease;
        }
        .jtc-tab-btn:hover {
            background: #ffffff;
            color: #0f172a;
        }
        .jtc-tab-btn.active {
            background: #ffffff;
            color: #1e40af;
            border-top: 3px solid #2563eb;
            border-bottom: 1px solid #ffffff;
            font-weight: 700;
        }
        .jtc-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 3px;
            cursor: pointer;
            border: 1px solid transparent;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.1s ease;
        }
        .jtc-btn-primary {
            background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            border-color: #1e40af;
        }
        .jtc-btn-primary:hover {
            background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
        }
        .jtc-btn-success {
            background: linear-gradient(180deg, #16a34a 0%, #15803d 100%);
            color: #ffffff;
            border-color: #166534;
        }
        .jtc-btn-success:hover {
            background: linear-gradient(180deg, #22c55e 0%, #16a34a 100%);
        }
        .jtc-btn-danger {
            background: linear-gradient(180deg, #dc2626 0%, #b91c1c 100%);
            color: #ffffff;
            border-color: #991b1b;
        }
        .jtc-btn-warning {
            background: linear-gradient(180deg, #ea580c 0%, #c2410c 100%);
            color: #ffffff;
            border-color: #9a3412;
        }
        .jtc-btn-default {
            background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%);
            color: #334155;
            border-color: #cbd5e1;
        }
        .jtc-btn-default:hover {
            background: #ffffff;
            border-color: #94a3b8;
        }
        .jtc-input {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            font-size: 13px;
            border-radius: 3px;
            background-color: #ffffff;
            color: #0f172a;
        }
        .jtc-input:focus {
            outline: 2px solid #2563eb;
            border-color: #2563eb;
        }
        /* Accurate Visual Availability Classes */
        .slot-available {
            background-color: #ecfccb !important; /* Yellow-Green / Lime */
            border: 2px solid #84cc16 !important;
            color: #365314 !important;
        }
        .slot-available:hover {
            background-color: #d9f99d !important;
        }
        .slot-full {
            background-color: #f1f5f9 !important; /* Muted Gray */
            border: 1px solid #cbd5e1 !important;
            color: #64748b !important;
        }
        .slot-closed {
            background-color: #e2e8f0 !important; /* Darker Gray for Closed / Monday */
            border: 1px solid #cbd5e1 !important;
            color: #475569 !important;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Top Banner & System ID -->
    <header class="jtc-header text-white">
        <div class="max-w-7xl mx-auto px-4 py-3 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white text-blue-900 font-extrabold flex items-center justify-center rounded text-xl shadow border border-blue-200">
                    館
                </div>
                <div>
                    <div class="text-[11px] text-blue-200 tracking-wider font-semibold">神戸市立図書館 業務支援系・座席予約自動化基盤</div>
                    <h1 class="text-base sm:text-lg font-bold tracking-tight">座席WEB予約支援・AI自動確保ポータル (Libraryes v2.6)</h1>
                </div>
            </div>

            <!-- Login & Sync Status Bar -->
            <div class="flex items-center space-x-3 text-xs bg-slate-900/80 px-3.5 py-2 rounded border border-slate-700">
                <div>
                    <span class="text-slate-400">利用者番号:</span>
                    <span id="header-user-code" class="font-bold text-amber-300 font-mono ml-1">未認証</span>
                    <span id="header-user-name" class="text-slate-300 ml-1"></span>
                </div>
                <span class="text-slate-600">|</span>
                <div>
                    <span class="text-slate-400">端末時刻:</span>
                    <span id="header-time" class="font-mono text-slate-200 ml-1 font-semibold">--:--:--</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Continuous Sniper Monitoring Banner (Displayed when sniper is active) -->
    <div id="sniper-active-strip" class="hidden bg-amber-500 text-slate-950 font-bold px-4 py-2 text-xs border-b border-amber-600 flex items-center justify-between shadow">
        <div class="flex items-center space-x-2">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-600 animate-ping"></span>
            <span>【⚡ 永続スナイパー待機稼働中】 空席（キャンセル発生）をミリ秒常時監視中... <span id="sniper-target-info" class="font-mono text-blue-950 underline"></span></span>
        </div>
        <button type="button" onclick="stopContinuousSniper()" class="bg-slate-900 text-white px-3 py-1 rounded text-xs hover:bg-slate-800">
            ⏹ 監視待機を停止
        </button>
    </div>

    <!-- Sub Header Status Bar -->
    <div class="bg-slate-200 border-b border-slate-300 py-1.5 px-4 text-xs text-slate-600">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-1">
            <div class="flex items-center space-x-1.5 font-semibold">
                <span>ポータル</span>
                <span>&gt;</span>
                <span class="text-blue-900" id="current-breadcrumb">AI最適予約・自動支援コンソール</span>
            </div>
            <div class="flex items-center space-x-3 text-[11px]">
                <span>デフォルト確保席数: <strong class="text-blue-950 bg-blue-100 px-1.5 py-0.5 rounded border border-blue-300">2席確保モード</strong></span>
                <span>定休日: <strong class="text-red-700">毎週月曜休館</strong></span>
                <span>DB: <strong class="text-emerald-700">SQLite3</strong></span>
            </div>
        </div>
    </div>

    <!-- Main Workspace -->
    <main class="max-w-7xl w-full mx-auto px-4 py-4 flex-1 space-y-4">

        <!-- Notification Banner Box -->
        <div id="toast-banner" class="hidden p-3 rounded text-xs font-bold border flex items-center justify-between shadow-sm">
            <span id="toast-text"></span>
            <button type="button" onclick="hideToast()" class="text-slate-500 hover:text-slate-800 font-bold ml-4">✕</button>
        </div>

        <!-- Tab Headers -->
        <div class="flex flex-wrap border-b border-slate-300 pt-1 -mb-[1px]">
            <button type="button" onclick="switchTab('ai_scheduler')" id="tab-btn-ai_scheduler" class="jtc-tab-btn active">
                ■ AI 最適時間 予約支援
            </button>
            <button type="button" onclick="switchTab('matrix_view')" id="tab-btn-matrix_view" class="jtc-tab-btn">
                ■ 空席週間マトリクス（黄緑/灰色 台帳）
            </button>
            <button type="button" onclick="switchTab('instant_snipe')" id="tab-btn-instant_snipe" class="jtc-tab-btn">
                ■ 空席即時確保（スナイプ待機）
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
                ■ 図書館アカウント設定
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: AI 最適時間 予約支援 -->
        <!-- ========================================== -->
        <section id="tab-content-ai_scheduler" class="space-y-4">
            <div class="jtc-panel">
                <div class="section-bar">
                    <span>▼ AI 座席予約パラメータ設定 & 最適スロット自動診断</span>
                    <span class="text-xs font-normal text-blue-100">※利用目的に合わせてAIが覚醒度・静寂度・混雑傾向を自動スコアリングします</span>
                </div>
                <div class="p-4 space-y-4">
                    <table class="jtc-form-table">
                        <tbody>
                            <tr>
                                <th><span class="bg-red-600 text-white text-[11px] px-1.5 py-0.5 rounded mr-1">必須</span>利用目的プロファイル</th>
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
                                    <div id="purpose-desc" class="text-xs text-slate-500 mt-1.5">
                                        【集中学習モード】午前および夕方の静寂時間帯を優先選定し、周囲の出入りが少ない快適な枠を自動判定します。
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="bg-red-600 text-white text-[11px] px-1.5 py-0.5 rounded mr-1">必須</span>対象施設（図書館）</th>
                                <td>
                                    <select id="ai-area" onchange="onAreaSelectChange('ai-area', 'ai-corner')" class="jtc-input w-full sm:w-80 font-bold text-blue-950">
                                        <option value="60000" selected>垂水図書館</option>
                                        <option value="30000">中央図書館</option>
                                        <option value="40000">東灘図書館</option>
                                        <option value="50000">北神図書館</option>
                                        <option value="10000">名谷図書館</option>
                                        <option value="20000">西図書館</option>
                                    </select>
                                    <span class="text-xs text-slate-500 ml-2">※図書館を変更すると座席種別が自動更新されます</span>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="bg-red-600 text-white text-[11px] px-1.5 py-0.5 rounded mr-1">必須</span>座席種別（コーナー）</th>
                                <td>
                                    <select id="ai-corner" class="jtc-input w-full sm:w-80 font-bold text-blue-950">
                                        <option value="62000" selected>2F キャレル席</option>
                                        <option value="61000">2F 南カウンター席</option>
                                        <option value="63000">2F 西カウンター席</option>
                                        <option value="64000">3F 学習室</option>
                                        <option value="66000">セミナー室</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="bg-red-600 text-white text-[11px] px-1.5 py-0.5 rounded mr-1">必須</span>確保希望席数</th>
                                <td>
                                    <select id="ai-seat-count" class="jtc-input w-48 font-bold text-blue-950 bg-blue-50 border-blue-400">
                                        <option value="2" selected>2席 (推奨/デフォルト)</option>
                                        <option value="1">1席のみ</option>
                                        <option value="3">3席 (複数カード連動)</option>
                                    </select>
                                    <span class="text-xs text-slate-500 ml-2">※複数アカウント登録時は別カードで2席を同時確保します</span>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="bg-red-600 text-white text-[11px] px-1.5 py-0.5 rounded mr-1">必須</span>希望利用日</th>
                                <td>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button type="button" onclick="setPresetDate('TODAY')" class="jtc-btn jtc-btn-default text-xs">本日</button>
                                        <button type="button" onclick="setPresetDate('TOMORROW')" class="jtc-btn jtc-btn-default text-xs">明日</button>
                                        <button type="button" onclick="setPresetDate('THIS_WEEKEND')" class="jtc-btn jtc-btn-default text-xs">今週末(土)</button>
                                        <input type="date" id="ai-date" class="jtc-input font-bold">
                                        <span id="ai-date-note" class="text-xs text-red-600 font-semibold ml-2"></span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex justify-end space-x-2 pt-1">
                        <button type="button" onclick="executeAiAnalysis()" class="jtc-btn jtc-btn-primary py-2 px-6 text-sm">
                            ▶ AI 最適スロット診断 & 空席マトリクス解析を実行
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Toolbar -->
            <div class="jtc-panel p-3 bg-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="font-bold text-slate-700">【絞り込み検索】:</span>
                    <label class="flex items-center space-x-1 cursor-pointer bg-lime-100 text-lime-900 px-2 py-1 rounded border border-lime-400 font-semibold">
                        <input type="checkbox" id="filter-avail-only" onchange="applyFiltersAndSort()" checked class="rounded text-blue-600">
                        <span>黄緑色（空席あり枠のみ表示）</span>
                    </label>
                    <span class="text-slate-300">|</span>
                    <span>時間帯:</span>
                    <select id="filter-time-range" onchange="applyFiltersAndSort()" class="jtc-input py-1 text-xs">
                        <option value="ALL">全時間帯</option>
                        <option value="10:10">午前 (10:10~)</option>
                        <option value="12:15">昼 (12:15~)</option>
                        <option value="14:20">午後 (14:20~)</option>
                        <option value="16:25">夕方 (16:25~)</option>
                        <option value="18:30">夜間 (18:30~)</option>
                    </select>
                    <span class="text-slate-300">|</span>
                    <span>AIスコア下限:</span>
                    <select id="filter-min-score" onchange="applyFiltersAndSort()" class="jtc-input py-1 text-xs">
                        <option value="0">全スコア</option>
                        <option value="70">70点以上 (良好以上)</option>
                        <option value="80">80点以上 (高快適枠)</option>
                        <option value="90">90点以上 (最上位推奨)</option>
                    </select>
                </div>

                <div class="flex items-center space-x-2">
                    <span class="font-bold text-slate-700">【並び順】:</span>
                    <select id="sort-order" onchange="applyFiltersAndSort()" class="jtc-input py-1 text-xs font-semibold">
                        <option value="score_desc">AI推奨スコア順（高い順）</option>
                        <option value="time_asc">日時順（早い順）</option>
                        <option value="time_desc">日時順（遅い順）</option>
                        <option value="avail_first">空席枠優先（黄緑が上）</option>
                    </select>
                </div>
            </div>

            <!-- AI Output Table / Cards -->
            <div class="jtc-panel">
                <div class="section-bar">
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
        <!-- TAB 2: 空席週間マトリクス（黄緑/灰色 台帳） -->
        <!-- ========================================== -->
        <section id="tab-content-matrix_view" class="hidden space-y-4">
            <div class="jtc-panel">
                <div class="section-bar">
                    <span>▼ 7日間 空席週間マトリクス台帳 (黄緑＝空席あり / 灰色＝満席・休館)</span>
                    <div class="flex items-center space-x-2">
                        <span class="inline-block w-3 h-3 bg-lime-300 border border-lime-600 rounded-sm"></span>
                        <span class="text-xs text-white">黄緑: ◯ 空席あり</span>
                        <span class="inline-block w-3 h-3 bg-slate-300 border border-slate-400 rounded-sm ml-2"></span>
                        <span class="text-xs text-slate-300">灰色: ✕ 満席/休館</span>
                    </div>
                </div>
                <div class="p-4 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-200">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="font-bold text-slate-700">対象図書館:</span>
                            <select id="matrix-area" onchange="onAreaSelectChange('matrix-area', 'matrix-corner'); loadWeeklyMatrix();" class="jtc-input py-1 text-xs font-bold text-blue-950">
                                <option value="60000" selected>垂水図書館</option>
                                <option value="30000">中央図書館</option>
                                <option value="40000">東灘図書館</option>
                                <option value="50000">北神図書館</option>
                                <option value="10000">名谷図書館</option>
                                <option value="20000">西図書館</option>
                            </select>

                            <span class="font-bold text-slate-700 ml-2">座席種別:</span>
                            <select id="matrix-corner" onchange="loadWeeklyMatrix()" class="jtc-input py-1 text-xs font-bold text-blue-950">
                                <option value="62000" selected>2F キャレル席</option>
                                <option value="61000">2F 南カウンター席</option>
                                <option value="63000">2F 西カウンター席</option>
                                <option value="64000">3F 学習室</option>
                                <option value="66000">セミナー室</option>
                            </select>

                            <span class="font-bold text-slate-700 ml-2">基準日:</span>
                            <input type="date" id="matrix-date" onchange="loadWeeklyMatrix()" class="jtc-input py-1 text-xs">
                        </div>
                        <button type="button" onclick="loadWeeklyMatrix()" class="jtc-btn jtc-btn-default text-xs">
                            ⟳ マトリクスを再スキャン
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="jtc-table text-center" id="weekly-matrix-table">
                            <thead>
                                <tr>
                                    <th style="width: 140px; text-align: center;">時間帯スロット</th>
                                    <th id="m-th-0">取得中</th>
                                    <th id="m-th-1">取得中</th>
                                    <th id="m-th-2">取得中</th>
                                    <th id="m-th-3">取得中</th>
                                    <th id="m-th-4">取得中</th>
                                    <th id="m-th-5">取得中</th>
                                    <th id="m-th-6">取得中</th>
                                </tr>
                            </thead>
                            <tbody id="weekly-matrix-tbody">
                                <tr><td colspan="8" class="py-8 text-center text-slate-500">データを取得中...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- TAB 3: 空席即時確保（スナイプ待機） -->
        <!-- ========================================== -->
        <section id="tab-content-instant_snipe" class="hidden space-y-4">
            <div class="jtc-panel">
                <div class="section-bar">
                    <span>▼ 開いている時間を直ぐに取る（即時空き枠スナイパー / 永続待機）</span>
                    <span class="text-xs font-normal text-blue-100">※キャンセル等で解放された空席を検知し即座に予約を確定します</span>
                </div>
                <div class="p-4 space-y-4">
                    <div class="bg-amber-50 border border-amber-300 p-3 rounded text-xs text-amber-900">
                        <strong>【永続スナイプ待機機能概要】</strong><br>
                        現在空いている枠（黄緑色）を直ちに2席予約します。満席（灰色）の場合は、「ずっと待機」ボタンを押すとバックグラウンドで<strong>キャンセルが出るまで常時高速スキャンを永続継続</strong>し、空いた瞬間にミリ秒で自動予約を奪取します。
                    </div>

                    <table class="jtc-form-table">
                        <tbody>
                            <tr>
                                <th>対象施設（図書館）</th>
                                <td>
                                    <select id="snipe-area" onchange="onAreaSelectChange('snipe-area', 'snipe-corner'); loadLiveVacancies();" class="jtc-input w-full sm:w-80 font-bold text-blue-950">
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
                                <th>座席コーナー</th>
                                <td>
                                    <select id="snipe-corner" onchange="loadLiveVacancies()" class="jtc-input w-full sm:w-80 font-bold text-blue-950">
                                        <option value="62000" selected>2F キャレル席</option>
                                        <option value="61000">2F 南カウンター席</option>
                                        <option value="63000">2F 西カウンター席</option>
                                        <option value="64000">3F 学習室</option>
                                        <option value="66000">セミナー室</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>対象日付</th>
                                <td><input type="date" id="snipe-date" onchange="loadLiveVacancies()" class="jtc-input font-bold"></td>
                            </tr>
                            <tr>
                                <th>時間帯指定</th>
                                <td>
                                    <select id="snipe-time" class="jtc-input w-full sm:w-80 font-bold">
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
                        <button type="button" onclick="startContinuousSniper()" class="jtc-btn jtc-btn-warning py-2 px-6 font-bold shadow">
                            ⚡ 空くのをずっと待機（永続スナイパー起動）
                        </button>
                    </div>

                    <!-- Live Grid -->
                    <div class="mt-4 pt-4 border-t border-slate-200">
                        <div class="font-bold text-xs text-slate-700 mb-2 flex items-center justify-between">
                            <span>【現時点の空席一覧】（黄緑＝空き枠 / 灰色＝満席・休館）</span>
                        </div>
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
        <!-- TAB 4: 指定日時絶対確保（ピンポイント） -->
        <!-- ========================================== -->
        <section id="tab-content-absolute_sniper" class="hidden space-y-4">
            <div class="jtc-panel">
                <div class="section-bar">
                    <span>▼ 取りたい時間に絶対に取る（ピンポイント・ミリ秒スナイパー）</span>
                    <span class="text-xs font-normal text-blue-100">※受付開始日時や特定枠をミリ秒単位で強制ロックします</span>
                </div>
                <div class="p-4 space-y-4">
                    <div class="bg-blue-50 border border-blue-300 p-3 rounded text-xs text-blue-900">
                        <strong>【ピンポイント確保仕様】</strong><br>
                        1週間前の予約解禁時刻（朝9:00等）や激戦時間帯をピンポイント指定します。事前キャッシュしたCSRFトークンを用い、受付開始と同時に200ms周期のミリ秒連続リクエストを投入して枠を確実に2席奪取します。
                    </div>

                    <table class="jtc-form-table">
                        <tbody>
                            <tr>
                                <th>対象施設（図書館）</th>
                                <td>
                                    <select id="target-area" onchange="onAreaSelectChange('target-area', 'target-corner')" class="jtc-input w-full sm:w-80 font-bold text-blue-950">
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
                                <th>座席コーナー</th>
                                <td>
                                    <select id="target-corner" class="jtc-input w-full sm:w-80 font-bold text-blue-950">
                                        <option value="62000" selected>2F キャレル席</option>
                                        <option value="61000">2F 南カウンター席</option>
                                        <option value="63000">2F 西カウンター席</option>
                                        <option value="64000">3F 学習室</option>
                                        <option value="66000">セミナー室</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="bg-red-600 text-white text-[11px] px-1.5 py-0.5 rounded mr-1">必須</span>確保目標日</th>
                                <td><input type="date" id="target-date" class="jtc-input font-bold"></td>
                            </tr>
                            <tr>
                                <th><span class="bg-red-600 text-white text-[11px] px-1.5 py-0.5 rounded mr-1">必須</span>確保目標スロット</th>
                                <td>
                                    <select id="target-time" class="jtc-input w-full sm:w-80 font-bold">
                                        <option value="10:10">10:10 ～ 12:10 (第1枠: 午前)</option>
                                        <option value="12:15">12:15 ～ 14:15 (第2枠: 昼)</option>
                                        <option value="14:20">14:20 ～ 16:20 (第3枠: 午後)</option>
                                        <option value="16:25">16:25 ～ 18:25 (第4枠: 夕方)</option>
                                        <option value="18:30">18:30 ～ 20:30 (第5枠: 夜間)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="bg-slate-600 text-white text-[11px] px-1.5 py-0.5 rounded mr-1">任意</span>予約解禁日時 (タイマー起動時刻)</th>
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
        <!-- TAB 5: 自動監視タスク管理台帳 -->
        <!-- ========================================== -->
        <section id="tab-content-tasks" class="hidden space-y-4">
            <div class="jtc-panel">
                <div class="section-bar">
                    <span>▼ 自動予約・監視タスク管理台帳</span>
                    <button type="button" onclick="loadStatus()" class="text-xs bg-blue-900 hover:bg-blue-800 text-white px-2.5 py-1 rounded border border-blue-400">
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
        <!-- TAB 6: 予約確認・取消管理 -->
        <!-- ========================================== -->
        <section id="tab-content-my_reservations" class="hidden space-y-4">
            <div class="jtc-panel">
                <div class="section-bar">
                    <span>▼ 現在取得済みの座席予約一覧</span>
                    <button type="button" onclick="loadMyReservations()" class="text-xs bg-blue-900 hover:bg-blue-800 text-white px-2.5 py-1 rounded border border-blue-400">
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
        <!-- TAB 7: 図書館アカウント認証設定 -->
        <!-- ========================================== -->
        <section id="tab-content-account" class="hidden space-y-4">
            <div class="jtc-panel">
                <div class="section-bar">
                    <span>▼ 神戸市立図書館 公式アカウント認証設定</span>
                </div>
                <div class="p-4 space-y-4">
                    <div class="bg-blue-50 border border-blue-300 p-3 rounded text-xs text-blue-900">
                        <strong>【認証情報について】</strong><br>
                        神戸市立図書館の<strong>図書館カード番号（利用者番号: Pから始まる半角英数字）</strong>と、K-libネットで利用している<strong>パスワード</strong>を入力してください。2席以上を同時に確保するために、複数のカード番号を登録しておくことも可能です。
                    </div>

                    <form onsubmit="handleAccountSave(event)" class="space-y-4">
                        <table class="jtc-form-table">
                            <tbody>
                                <tr>
                                    <th><span class="bg-red-600 text-white text-[11px] px-1.5 py-0.5 rounded mr-1">必須</span>利用者番号 (カード番号)</th>
                                    <td>
                                        <input type="text" id="acc-usercode" placeholder="例: P182790698" required class="jtc-input w-full sm:w-80 font-mono font-bold">
                                        <span class="text-xs text-slate-500 block mt-1">※先頭のアルファベット「P」は大文字・半角で入力してください。</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><span class="bg-red-600 text-white text-[11px] px-1.5 py-0.5 rounded mr-1">必須</span>K-libネット パスワード</th>
                                    <td>
                                        <input type="password" id="acc-password" placeholder="パスワードを入力" required class="jtc-input w-full sm:w-80 font-mono">
                                        <span class="text-xs text-slate-500 block mt-1">※図書館公式ウェブサイトで登録したパスワードです。</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><span class="bg-slate-600 text-white text-[11px] px-1.5 py-0.5 rounded mr-1">任意</span>アカウント識別名</th>
                                    <td>
                                        <input type="text" id="acc-name" placeholder="例: 個人用カード1" class="jtc-input w-full sm:w-80">
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
        const LIBRARY_CORNERS = {
            '60000': [
                {id: '62000', name: '2F キャレル席'},
                {id: '61000', name: '2F 南カウンター席'},
                {id: '63000', name: '2F 西カウンター席'},
                {id: '64000', name: '3F 学習室'},
                {id: '66000', name: 'セミナー室'}
            ],
            '30000': [
                {id: '31000', name: '2号館2F 閲覧室1'},
                {id: '32000', name: '2号館3F 閲覧室2'},
                {id: '33000', name: '1号館2F 閲覧室3'},
                {id: '34000', name: '1号館3F 閲覧室4'}
            ],
            '40000': [
                {id: '41000', name: '一般閲覧席'},
                {id: '42000', name: 'キャレル席'}
            ],
            '50000': [
                {id: '51000', name: '一般閲覧席'},
                {id: '52000', name: 'キャレル席'}
            ],
            '10000': [
                {id: '11000', name: '一般閲覧席'},
                {id: '12000', name: 'キャレル席'}
            ],
            '20000': [
                {id: '21000', name: '一般閲覧席'},
                {id: '22000', name: 'キャレル席'}
            ]
        };

        let currentPurpose = 'focus';
        let currentStatusData = null;
        let cachedAiRecommendations = [];
        let cachedWeeklyMatrix = [];
        let continuousSniperTimer = null;

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

        function onAreaSelectChange(areaSelectId, cornerSelectId) {
            const areaEl = document.getElementById(areaSelectId);
            const cornerEl = document.getElementById(cornerSelectId);
            if (!areaEl || !cornerEl) return;

            const areaCode = areaEl.value;
            const corners = LIBRARY_CORNERS[areaCode] || LIBRARY_CORNERS['60000'];

            cornerEl.innerHTML = corners.map((c, i) => `
                <option value="${c.id}" ${i === 0 ? 'selected' : ''}>${c.name}</option>
            `).join('');
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
            if (tabId === 'matrix_view') { bc.textContent = '空席週間マトリクス（黄緑/灰色 台帳）'; loadWeeklyMatrix(); }
            if (tabId === 'instant_snipe') { bc.textContent = '空席即時確保（スナイプ待機）'; loadLiveVacancies(); }
            if (tabId === 'absolute_sniper') bc.textContent = '指定日時絶対確保（ピンポイント）';
            if (tabId === 'tasks') { bc.textContent = '自動監視タスク台帳'; loadStatus(); }
            if (tabId === 'my_reservations') { bc.textContent = '予約確認・取消管理'; loadMyReservations(); }
            if (tabId === 'account') { bc.textContent = '図書館アカウント認証設定'; loadStatus(); }
        }

        function setPresetDate(preset) {
            const aiDate = document.getElementById('ai-date');
            const snipeDate = document.getElementById('snipe-date');
            const targetDate = document.getElementById('target-date');
            const matrixDate = document.getElementById('matrix-date');

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
            if (matrixDate) matrixDate.value = val;

            checkMondayClosed(val);
        }

        function checkMondayClosed(dateStr) {
            const noteEl = document.getElementById('ai-date-note');
            if (!noteEl) return;
            const d = new Date(dateStr);
            if (d.getDay() === 1) { // Monday
                noteEl.textContent = '【注意】選択日は月曜日のため休館日（定休日）です。';
            } else {
                noteEl.textContent = '';
            }
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
                userCodeEl.textContent = '未認証 (設定タブで登録要)';
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
                if (t.status === 'monitoring') badge = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">常時監視待機中</span>';
                if (t.status === 'success') badge = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">予約完了</span>';
                if (t.status === 'failed') badge = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-300">失敗</span>';

                let typeLabel = t.type;
                if (t.type === 'ai_optimal') typeLabel = 'AI 最適予約';
                if (t.type === 'instant_snipe') typeLabel = '即時スナイプ(永続待機)';
                if (t.type === 'absolute_sniper') typeLabel = '絶対確保スナイパー';

                return `
                    <tr>
                        <td class="font-mono text-slate-600">#${t.id}</td>
                        <td class="font-bold text-blue-950">${typeLabel}</td>
                        <td class="font-mono">${t.target_date} <strong class="text-blue-900">${t.target_time_slot || ''}</strong></td>
                        <td>${badge}</td>
                        <td class="font-mono text-center">${t.retry_count} 回監視</td>
                        <td class="text-xs text-slate-600 truncate max-w-xs" title="${t.result_message || ''}">${t.result_message || '-'}</td>
                        <td class="text-center space-x-1">
                            <button type="button" onclick="runTaskNow(${t.id})" class="jtc-btn jtc-btn-primary text-xs py-0.5 px-2">実行</button>
                            <button type="button" onclick="deleteTask(${t.id})" class="jtc-btn jtc-btn-danger text-xs py-0.5 px-2">停止</button>
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
            const area = document.getElementById('ai-area').value;
            const corner = document.getElementById('ai-corner').value;
            const date = document.getElementById('ai-date').value;
            const container = document.getElementById('ai-result-container');
            const resultBadge = document.getElementById('ai-result-count');

            container.innerHTML = `<div class="text-center py-8 text-blue-900 text-xs font-bold">空席マトリクスを取得しAIスコアリングを計算中...</div>`;

            try {
                const res = await fetch(`backend/api.php?action=ai_recommend&area=${area}&date=${date}&corner=${corner}&purpose=${currentPurpose}`);
                const data = await res.json();

                if (!data.success || !data.top_recommendations || data.top_recommendations.length === 0) {
                    resultBadge.textContent = '候補 0件 (満席または休館)';
                    resultBadge.className = 'text-xs font-normal bg-rose-800 text-white px-2 py-0.5 rounded';
                    container.innerHTML = `
                        <div class="bg-amber-50 border border-amber-300 p-4 rounded text-xs text-amber-900">
                            <strong>【判定結果: 指定条件では現在予約可能な空席がありません】</strong><br>
                            満席または休館日の可能性があります。「空き枠即時奪取タスク」を登録すると、キャンセルが出た瞬間にAIが自動で確保します。
                            <div class="mt-2">
                                <button type="button" onclick="startContinuousSniperForParams('${date}', '${area}', '${corner}')" class="jtc-btn jtc-btn-warning text-xs">
                                    ⚡ この条件でキャンセル待ち永続スナイパーを起動
                                </button>
                            </div>
                        </div>
                    `;
                    cachedAiRecommendations = [];
                    return;
                }

                cachedAiRecommendations = data.top_recommendations;
                applyFiltersAndSort();

            } catch (err) {
                container.innerHTML = `<div class="p-4 text-center text-rose-700 text-xs font-bold">解析中に通信エラーが発生しました。</div>`;
            }
        }

        function applyFiltersAndSort() {
            const container = document.getElementById('ai-result-container');
            const resultBadge = document.getElementById('ai-result-count');
            const area = document.getElementById('ai-area').value;
            const corner = document.getElementById('ai-corner').value;

            const availOnly = document.getElementById('filter-avail-only').checked;
            const timeRange = document.getElementById('filter-time-range').value;
            const minScore = parseInt(document.getElementById('filter-min-score').value, 10);
            const sortOrder = document.getElementById('sort-order').value;

            let filtered = [...cachedAiRecommendations];

            // 1. Availability filter
            if (availOnly) {
                filtered = filtered.filter(s => s.available === true && !s.is_closed && !s.is_full);
            }

            // 2. Time range filter
            if (timeRange !== 'ALL') {
                filtered = filtered.filter(s => s.time && s.time.includes(timeRange));
            }

            // 3. Min score filter
            if (minScore > 0) {
                filtered = filtered.filter(s => (s.ai_score || 0) >= minScore);
            }

            // 4. Sorting
            if (sortOrder === 'score_desc') {
                filtered.sort((a, b) => (b.ai_score || 0) - (a.ai_score || 0));
            } else if (sortOrder === 'time_asc') {
                filtered.sort((a, b) => ((a.date + (a.time || '')) > (b.date + (b.time || '')) ? 1 : -1));
            } else if (sortOrder === 'time_desc') {
                filtered.sort((a, b) => ((a.date + (a.time || '')) < (b.date + (b.time || '')) ? 1 : -1));
            } else if (sortOrder === 'avail_first') {
                filtered.sort((a, b) => (b.available ? 1 : 0) - (a.available ? 1 : 0));
            }

            resultBadge.textContent = `表示: ${filtered.length} / 全 ${cachedAiRecommendations.length}件`;
            resultBadge.className = 'text-xs font-normal bg-emerald-800 text-white px-2 py-0.5 rounded';

            if (filtered.length === 0) {
                container.innerHTML = `<div class="p-6 text-center text-slate-500 text-xs bg-slate-50 border border-slate-200">絞り込み条件に一致するスロットはありませんでした。フィルター条件を緩和してください。</div>`;
                return;
            }

            container.innerHTML = filtered.map((slot, idx) => {
                const isAvail = slot.available === true && !slot.is_closed && !slot.is_full;
                const isClosed = slot.is_closed === true;

                let statusBadge = '';
                let cardClass = '';

                if (isClosed) {
                    statusBadge = '<span class="px-2 py-0.5 rounded text-xs font-bold bg-slate-300 text-slate-800 border border-slate-400">休館日 (月曜定休)</span>';
                    cardClass = 'slot-closed';
                } else if (isAvail) {
                    statusBadge = `<span class="px-2 py-0.5 rounded text-xs font-bold bg-lime-300 text-lime-950 border border-lime-600">◯ 空席あり (${slot.remain_text || '予約可'})</span>`;
                    cardClass = 'slot-available';
                } else {
                    statusBadge = '<span class="px-2 py-0.5 rounded text-xs font-bold bg-slate-200 text-slate-600 border border-slate-300">✕ 満席 (0席)</span>';
                    cardClass = 'slot-full';
                }

                return `
                    <div class="border p-4 rounded shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 ${cardClass}">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 text-xs font-bold ${idx === 0 && isAvail ? 'bg-blue-800 text-white' : 'bg-slate-700 text-white'} rounded">
                                    第 ${idx + 1} 推奨枠
                                </span>
                                <span class="text-base font-bold font-mono">${slot.time_range || slot.time || slot.raw_label}</span>
                                ${statusBadge}
                                <span class="text-xs px-2 py-0.5 rounded font-semibold bg-white/80 border border-slate-300">
                                    ${slot.recommendation_tag || '標準'}
                                </span>
                            </div>
                            <div class="text-xs text-slate-700 mt-1 font-mono">対象日: ${slot.date} | スロット: ${slot.label || slot.slot_id}</div>
                            <div class="text-xs text-slate-800 mt-2 bg-white/90 p-2 rounded border border-slate-300">
                                <strong class="text-blue-900">■ AI 判定理由:</strong>
                                <ul class="list-disc list-inside mt-0.5 text-slate-700">
                                    ${(slot.reasons || []).map(r => `<li>${r}</li>`).join('')}
                                </ul>
                            </div>
                        </div>

                        <div class="flex md:flex-col items-end justify-between md:justify-center gap-2 shrink-0">
                            <div class="text-right">
                                <span class="text-[11px] text-slate-600 block">AI 総合快適度</span>
                                <span class="text-2xl font-extrabold text-blue-950 font-mono">${slot.ai_score || 0}<span class="text-xs text-slate-500"> /100点</span></span>
                            </div>
                            ${isAvail ? `
                                <button type="button" onclick="quickReserve('${slot.date}', '${slot.slot_id}', '${corner}', '${area}')" class="jtc-btn jtc-btn-success py-1.5 px-4 text-xs font-bold">
                                    ✔ 2席を今すぐ予約
                                </button>
                            ` : `
                                <button type="button" onclick="startContinuousSniperForParams('${slot.date}', '${area}', '${corner}')" class="jtc-btn jtc-btn-warning text-xs py-1.5 px-3">
                                    ⚡ 空くまでずっと待機 (スナイプ)
                                </button>
                            `}
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function loadWeeklyMatrix() {
            const area = document.getElementById('matrix-area').value;
            const corner = document.getElementById('matrix-corner').value;
            const date = document.getElementById('matrix-date').value;
            const tbody = document.getElementById('weekly-matrix-tbody');

            tbody.innerHTML = `<tr><td colspan="8" class="py-8 text-center text-slate-500">週間空席台帳を取得・解析中...</td></tr>`;

            try {
                const res = await fetch(`backend/api.php?action=public_vacancies&area=${area}&date=${date}&corner=${corner}`);
                const data = await res.json();

                if (!data.success || !data.data.matrix) {
                    tbody.innerHTML = `<tr><td colspan="8" class="py-8 text-center text-rose-700">空席マトリクスを取得できませんでした。</td></tr>`;
                    return;
                }

                cachedWeeklyMatrix = data.data.matrix;

                // Update headers (Day 0 to Day 6)
                data.data.matrix.forEach((dayRow, dIdx) => {
                    const thEl = document.getElementById(`m-th-${dIdx}`);
                    if (thEl) {
                        const isMon = dayRow.weekday === '月';
                        const isSat = dayRow.weekday === '土';
                        const isSun = dayRow.weekday === '日';
                        let colorClass = isMon ? 'text-rose-700 font-extrabold' : (isSat ? 'text-blue-700' : (isSun ? 'text-red-700' : 'text-slate-800'));
                        thEl.innerHTML = `<span class="block text-[11px] text-slate-500 font-mono">${dayRow.md}</span><strong class="${colorClass}">${dayRow.weekday}曜${isMon ? ' (休)' : ''}</strong>`;
                    }
                });

                // Build 5 slot rows
                const slotDefinitions = [
                    {id: '0', time: '10:10 - 12:10', label: '第1枠 (午前)'},
                    {id: '1', time: '12:15 - 14:15', label: '第2枠 (昼)'},
                    {id: '2', time: '14:20 - 16:20', label: '第3枠 (午後)'},
                    {id: '3', time: '16:25 - 18:25', label: '第4枠 (夕方)'},
                    {id: '4', time: '18:30 - 19:50', label: '第5枠 (夜間)'},
                ];

                tbody.innerHTML = slotDefinitions.map((sDef, sIdx) => {
                    const cells = data.data.matrix.map(dayRow => {
                        const isDayClosed = dayRow.is_closed || dayRow.weekday === '月';
                        const s = dayRow.slots[sIdx];

                        if (isDayClosed) {
                            return `
                                <td class="slot-closed text-center p-2">
                                    <span class="text-xs font-semibold">休館日</span>
                                </td>
                            `;
                        }

                        if (s && s.available) {
                            return `
                                <td class="slot-available text-center p-2">
                                    <span class="block text-xs font-bold">◯ 空席あり</span>
                                    <span class="block text-[10px] text-lime-900 font-bold">${s.remain_text || '予約可'}</span>
                                    <button type="button" onclick="quickReserve('${s.date}', '${s.slot_id}', '${corner}', '${area}')" class="jtc-btn jtc-btn-success text-[11px] py-0.5 px-2.5 mt-1 shadow font-bold">
                                        2席予約
                                    </button>
                                </td>
                            `;
                        } else {
                            return `
                                <td class="slot-full text-center p-2">
                                    <span class="text-xs text-slate-500 font-medium">✕ 満席</span>
                                    <button type="button" onclick="startContinuousSniperForParams('${s ? s.date : ''}', '${area}', '${corner}')" class="jtc-btn jtc-btn-warning text-[10px] py-0.5 px-1.5 mt-1 font-bold">
                                        待機
                                    </button>
                                </td>
                            `;
                        }
                    }).join('');

                    return `
                        <tr>
                            <th class="bg-slate-100 text-left px-3 py-2">
                                <span class="font-bold text-blue-950 block">${sDef.label}</span>
                                <span class="text-[11px] text-slate-500 font-mono">${sDef.time}</span>
                            </th>
                            ${cells}
                        </tr>
                    `;
                }).join('');

            } catch (err) {
                tbody.innerHTML = `<tr><td colspan="8" class="py-8 text-center text-rose-700">マトリクス取得中に通信エラーが発生しました。</td></tr>`;
            }
        }

        async function quickReserve(date, slotId, corner, area = '60000') {
            const seatCount = document.getElementById('ai-seat-count') ? document.getElementById('ai-seat-count').value : 2;
            showToast(`図書館予約システムへ ${seatCount}席の予約リクエストを投入中...`, true);

            try {
                const res = await fetch('backend/api.php?action=quick_reserve', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        date,
                        slot_id: slotId,
                        corner_code: corner,
                        area_code: area,
                        seat_count: seatCount
                    })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(`予約完了: ${data.message}`, true);
                    loadMyReservations();
                } else {
                    showToast(data.message, false);
                }
            } catch (err) {
                showToast('予約送信エラーが発生しました。', false);
            }
        }

        async function loadLiveVacancies() {
            const area = document.getElementById('snipe-area').value;
            const corner = document.getElementById('snipe-corner').value;
            const date = document.getElementById('snipe-date').value;
            const container = document.getElementById('live-vacancies-list');

            container.innerHTML = `<div class="p-4 text-center text-slate-500 text-xs col-span-full">空席状況をスキャン中...</div>`;
            try {
                const res = await fetch(`backend/api.php?action=public_vacancies&area=${area}&date=${date}&corner=${corner}`);
                const data = await res.json();

                if (!data.success || !data.data.slots || data.data.slots.length === 0) {
                    container.innerHTML = `<div class="p-4 text-center text-slate-600 bg-slate-100 border border-slate-300 text-xs col-span-full">現在表示可能な空席はありません。自動スナイパーの待機を推奨します。</div>`;
                    return;
                }

                container.innerHTML = data.data.slots.map(s => `
                    <div class="slot-available p-2.5 rounded flex items-center justify-between shadow-sm">
                        <div>
                            <strong class="text-xs font-mono block text-emerald-950">${s.label || s.time}</strong>
                            <span class="text-[11px] text-emerald-900 font-bold block">${s.remain_text || s.status_text}</span>
                        </div>
                        <button type="button" onclick="quickReserve('${s.date}', '${s.slot_id}', '${corner}', '${area}')" class="jtc-btn jtc-btn-success text-xs py-1 px-2.5 font-bold">
                            2席即時取得
                        </button>
                    </div>
                `).join('');
            } catch (err) {
                container.innerHTML = `<div class="p-4 text-center text-rose-700 text-xs col-span-full">空席取得エラー</div>`;
            }
        }

        /* Continuous Indefinite Sniper Monitoring */
        async function startContinuousSniper() {
            const area = document.getElementById('snipe-area').value;
            const corner = document.getElementById('snipe-corner').value;
            const date = document.getElementById('snipe-date').value;
            const time = document.getElementById('snipe-time').value;

            // Register backend task with max_retries = 999999
            showToast('空くまでずっと待機する永続スナイパーを起動中...', true);
            const res = await fetch('backend/api.php?action=create_task', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    type: 'instant_snipe',
                    area_code: area,
                    corner_code: corner,
                    target_date: date,
                    target_time_slot: time,
                    run_now: true,
                    max_retries: 999999
                })
            });

            // Activate front-end active strip
            const strip = document.getElementById('sniper-active-strip');
            const targetInfo = document.getElementById('sniper-target-info');
            strip.classList.remove('hidden');
            targetInfo.textContent = `${date} (館:${area}, 席:${corner}, 枠:${time})`;

            if (continuousSniperTimer) clearInterval(continuousSniperTimer);

            // Frontend continuous loop: Poll every 3 seconds until booked
            continuousSniperTimer = setInterval(async () => {
                try {
                    const checkRes = await fetch(`backend/api.php?action=public_vacancies&area=${area}&date=${date}&corner=${corner}`);
                    const checkData = await checkRes.json();
                    const availableSlots = checkData.data?.slots || [];

                    if (availableSlots.length > 0) {
                        const targetSlot = (time === 'ANY') ? availableSlots[0] : availableSlots.find(s => s.time.includes(time) || s.slot_id === time);
                        if (targetSlot) {
                            showToast(`【空き検知！】スナイパーが枠を即時自動確保中...`, true);
                            await quickReserve(targetSlot.date, targetSlot.slot_id, corner, area);
                            stopContinuousSniper();
                        }
                    }
                } catch (e) {
                    console.warn('Sniper poll heartbeat...', e);
                }
            }, 3000);

            showToast('永続スナイパー待機を開始しました。空きが出次第、自動で予約されます。', true);
            switchTab('tasks');
        }

        function startContinuousSniperForParams(date, area, corner) {
            document.getElementById('snipe-area').value = area;
            onAreaSelectChange('snipe-area', 'snipe-corner');
            document.getElementById('snipe-corner').value = corner;
            document.getElementById('snipe-date').value = date;
            switchTab('instant_snipe');
            startContinuousSniper();
        }

        function stopContinuousSniper() {
            if (continuousSniperTimer) {
                clearInterval(continuousSniperTimer);
                continuousSniperTimer = null;
            }
            document.getElementById('sniper-active-strip').classList.add('hidden');
            showToast('スナイパーの待機を停止しました。', false);
        }

        async function createAbsoluteSniperTask() {
            const area = document.getElementById('target-area').value;
            const corner = document.getElementById('target-corner').value;
            const date = document.getElementById('target-date').value;
            const time = document.getElementById('target-time').value;
            const launch = document.getElementById('target-launch-time').value;

            showToast('ピンポイント絶対取得タスクを登録中...', true);
            const res = await fetch('backend/api.php?action=create_task', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    type: 'absolute_sniper',
                    area_code: area,
                    corner_code: corner,
                    target_date: date,
                    target_time_slot: time,
                    execute_at: launch || null,
                    max_retries: 999999
                })
            });
            const data = await res.json();
            if (data.success) {
                showToast('ピンポイント絶対取得タスクが登録されました（永続待機設定）。', true);
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
