<!DOCTYPE html>
<html lang="ja" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libraryes - 神戸市立図書館 AI自動最適予約 & 高速スナイパー</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
                            800: '#0c4a6e',
                            900: '#082f49',
                            950: '#031726'
                        },
                        cyber: {
                            purple: '#8b5cf6',
                            emerald: '#10b981',
                            amber: '#f59e0b',
                            rose: '#f43f5e'
                        }
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .glow-cyan {
            box-shadow: 0 0 25px -5px rgba(2, 132, 199, 0.35);
        }
        .glow-emerald {
            box-shadow: 0 0 25px -5px rgba(16, 185, 129, 0.35);
        }
        .glow-rose {
            box-shadow: 0 0 25px -5px rgba(244, 63, 94, 0.35);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col font-sans selection:bg-cyan-500 selection:text-white">

    <!-- Header Navigation -->
    <header class="glass-panel sticky top-0 z-50 border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-cyan-500/20">
                    <i data-lucide="sparkles" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold bg-gradient-to-r from-cyan-400 via-sky-300 to-indigo-400 bg-clip-text text-transparent">Libraryes</h1>
                    <p class="text-[10px] text-slate-400 tracking-wider font-mono uppercase">AI Reservation & Sniper Suite</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div id="account-badge" class="hidden sm:flex items-center space-x-2 bg-slate-800/60 px-3 py-1.5 rounded-full border border-slate-700/60">
                    <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                    <span id="active-user-text" class="text-xs text-slate-300 font-mono">ログイン確認中...</span>
                </div>
                <button onclick="switchTab('account')" class="p-2 rounded-lg bg-slate-800/80 hover:bg-slate-700 transition border border-slate-700 text-slate-300 hover:text-white" title="アカウント設定">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- Navigation Tabs -->
        <div class="flex overflow-x-auto space-x-2 pb-2 scrollbar-none border-b border-slate-800">
            <button onclick="switchTab('ai_scheduler')" id="tab-btn-ai_scheduler" class="tab-btn px-4 py-2.5 rounded-xl font-medium text-sm flex items-center space-x-2 transition bg-gradient-to-r from-cyan-600 to-blue-600 text-white shadow-md shadow-cyan-900/30">
                <i data-lucide="bot" class="w-4 h-4"></i>
                <span>AI 最適時間 自動予約</span>
            </button>
            <button onclick="switchTab('instant_snipe')" id="tab-btn-instant_snipe" class="tab-btn px-4 py-2.5 rounded-xl font-medium text-sm flex items-center space-x-2 transition bg-slate-800/60 hover:bg-slate-800 text-slate-300">
                <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i>
                <span>開いている時間を即時確保</span>
            </button>
            <button onclick="switchTab('absolute_sniper')" id="tab-btn-absolute_sniper" class="tab-btn px-4 py-2.5 rounded-xl font-medium text-sm flex items-center space-x-2 transition bg-slate-800/60 hover:bg-slate-800 text-slate-300">
                <i data-lucide="crosshair" class="w-4 h-4 text-rose-400"></i>
                <span>取りたい時間に絶対取る</span>
            </button>
            <button onclick="switchTab('tasks')" id="tab-btn-tasks" class="tab-btn px-4 py-2.5 rounded-xl font-medium text-sm flex items-center space-x-2 transition bg-slate-800/60 hover:bg-slate-800 text-slate-300">
                <i data-lucide="activity" class="w-4 h-4 text-indigo-400"></i>
                <span>自動監視タスク</span>
                <span id="active-tasks-count" class="bg-slate-700 text-cyan-300 text-[11px] px-1.5 py-0.5 rounded-full font-mono">0</span>
            </button>
            <button onclick="switchTab('my_reservations')" id="tab-btn-my_reservations" class="tab-btn px-4 py-2.5 rounded-xl font-medium text-sm flex items-center space-x-2 transition bg-slate-800/60 hover:bg-slate-800 text-slate-300">
                <i data-lucide="calendar-check" class="w-4 h-4 text-emerald-400"></i>
                <span>予約確認・取消</span>
            </button>
            <button onclick="switchTab('account')" id="tab-btn-account" class="tab-btn px-4 py-2.5 rounded-xl font-medium text-sm flex items-center space-x-2 transition bg-slate-800/60 hover:bg-slate-800 text-slate-300">
                <i data-lucide="key" class="w-4 h-4 text-slate-400"></i>
                <span>設定・アカウント</span>
            </button>
        </div>

        <!-- Notification Banner -->
        <div id="toast" class="hidden p-4 rounded-xl border flex items-center justify-between transition-all duration-300">
            <div class="flex items-center space-x-3">
                <i id="toast-icon" data-lucide="info" class="w-5 h-5"></i>
                <span id="toast-message" class="text-sm font-medium"></span>
            </div>
            <button onclick="hideToast()" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>

        <!-- TAB 1: AI 最適時間 自動予約 -->
        <section id="tab-content-ai_scheduler" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Parameters Config Card -->
                <div class="glass-panel rounded-2xl p-6 space-y-5 border border-slate-800 glow-cyan">
                    <div class="flex items-center space-x-2.5 border-b border-slate-800 pb-3">
                        <i data-lucide="brain-circuit" class="w-5 h-5 text-cyan-400"></i>
                        <h2 class="text-lg font-semibold text-white">AI 予約パラメータ分析</h2>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">利用目的 (AI 学習プロファイル)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" onclick="selectPurpose('focus')" id="purpose-focus" class="purpose-btn p-3 rounded-xl border border-cyan-500 bg-cyan-950/40 text-cyan-200 text-left transition flex flex-col justify-between">
                                    <span class="font-bold text-sm flex items-center justify-between">集中学習 <i data-lucide="sparkles" class="w-4 h-4"></i></span>
                                    <span class="text-[11px] text-cyan-300/70 mt-1">静寂・朝〜夕の覚醒ピーク優先</span>
                                </button>
                                <button type="button" onclick="selectPurpose('pc_work')" id="purpose-pc_work" class="purpose-btn p-3 rounded-xl border border-slate-700 bg-slate-900/60 text-slate-300 text-left transition flex flex-col justify-between">
                                    <span class="font-bold text-sm flex items-center justify-between">PC作業 <i data-lucide="laptop" class="w-4 h-4"></i></span>
                                    <span class="text-[11px] text-slate-400 mt-1">電源・キャレル席優先</span>
                                </button>
                                <button type="button" onclick="selectPurpose('long_study')" id="purpose-long_study" class="purpose-btn p-3 rounded-xl border border-slate-700 bg-slate-900/60 text-slate-300 text-left transition flex flex-col justify-between">
                                    <span class="font-bold text-sm flex items-center justify-between">長時間学習 <i data-lucide="clock" class="w-4 h-4"></i></span>
                                    <span class="text-[11px] text-slate-400 mt-1">連続確保しやすい枠を優先</span>
                                </button>
                                <button type="button" onclick="selectPurpose('quick_read')" id="purpose-quick_read" class="purpose-btn p-3 rounded-xl border border-slate-700 bg-slate-900/60 text-slate-300 text-left transition flex flex-col justify-between">
                                    <span class="font-bold text-sm flex items-center justify-between">読書・軽読 <i data-lucide="book-open" class="w-4 h-4"></i></span>
                                    <span class="text-[11px] text-slate-400 mt-1">スキマ時間・開放席優先</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">対象図書館</label>
                            <select id="ai-area" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500">
                                <option value="60000" selected>垂水図書館</option>
                                <option value="30000">中央図書館</option>
                                <option value="40000">東灘図書館</option>
                                <option value="50000">北神図書館</option>
                                <option value="10000">名谷図書館</option>
                                <option value="20000">西図書館</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">座席コーナー</label>
                            <select id="ai-corner" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500">
                                <option value="62000" selected>2F キャレル席</option>
                                <option value="61000">2F 南カウンター席</option>
                                <option value="63000">2F 西カウンター席</option>
                                <option value="64000">3F 学習室</option>
                                <option value="66000">セミナー室</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">対象日</label>
                            <div class="grid grid-cols-3 gap-2 mb-2">
                                <button type="button" onclick="setDatePreset('TODAY')" class="date-preset-btn px-2.5 py-1.5 rounded-lg bg-cyan-950 border border-cyan-500 text-xs font-medium text-cyan-200">今日</button>
                                <button type="button" onclick="setDatePreset('TOMORROW')" class="date-preset-btn px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-xs font-medium text-slate-300 hover:border-slate-500">明日</button>
                                <button type="button" onclick="setDatePreset('THIS_WEEKEND')" class="date-preset-btn px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-xs font-medium text-slate-300 hover:border-slate-500">今週末</button>
                            </div>
                            <input type="date" id="ai-date" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500">
                        </div>

                        <div class="pt-2">
                            <button onclick="runAiAnalysis()" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 hover:from-cyan-400 hover:to-indigo-500 text-white font-semibold text-sm shadow-lg shadow-cyan-500/25 transition flex items-center justify-center space-x-2">
                                <i data-lucide="cpu" class="w-4 h-4"></i>
                                <span>AI 最適時間をスキャン＆判定</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- AI Recommendations & Auto-booking Action Panel -->
                <div class="lg:col-span-2 space-y-5">
                    <div class="glass-panel rounded-2xl p-6 border border-slate-800">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div class="flex items-center space-x-2">
                                <i data-lucide="award" class="w-5 h-5 text-amber-400"></i>
                                <h3 class="text-lg font-semibold text-white">AI リアルタイム最適判定結果</h3>
                            </div>
                            <span id="ai-status-tag" class="text-xs px-2.5 py-1 rounded-full bg-slate-800 text-slate-400 font-mono">未実行</span>
                        </div>

                        <div id="ai-results-container" class="mt-5 space-y-4">
                            <div class="text-center py-12 text-slate-500">
                                <i data-lucide="compass" class="w-12 h-12 mx-auto mb-3 opacity-30 animate-spin"></i>
                                <p class="text-sm">左側のパラメータを設定し、「AI 最適時間をスキャン＆判定」を実行してください。</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- TAB 2: 開いている時間を直ぐに取る (Instant Snipe) -->
        <section id="tab-content-instant_snipe" class="hidden space-y-6">
            <div class="glass-panel rounded-2xl p-6 border border-slate-800 glow-emerald">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                    <div>
                        <div class="flex items-center space-x-2">
                            <i data-lucide="zap" class="w-6 h-6 text-amber-400"></i>
                            <h2 class="text-xl font-bold text-white">開いている時間を直ぐに取る（即時スナイプ）</h2>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">現在の空席枠をリアルタイムスキャンし、即座に予約を確定します。満席の場合はキャンセルが出た瞬間にミリ秒で奪取します。</p>
                    </div>
                    <button onclick="triggerInstantSnipe()" class="px-5 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-emerald-500 hover:from-amber-400 hover:to-emerald-400 text-slate-950 font-bold text-sm shadow-lg shadow-amber-500/20 transition flex items-center space-x-2">
                        <i data-lucide="bolt" class="w-4 h-4"></i>
                        <span>空席が出たら即時自動確保</span>
                    </button>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="glass-card rounded-xl p-4 border border-slate-800">
                        <span class="text-xs text-slate-400">対象日付</span>
                        <input type="date" id="snipe-date" class="mt-1 w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                    </div>
                    <div class="glass-card rounded-xl p-4 border border-slate-800">
                        <span class="text-xs text-slate-400">座席種別</span>
                        <select id="snipe-corner" class="mt-1 w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                            <option value="62000" selected>2F キャレル席</option>
                            <option value="61000">2F 南カウンター席</option>
                            <option value="63000">2F 西カウンター席</option>
                            <option value="64000">3F 学習室</option>
                        </select>
                    </div>
                    <div class="glass-card rounded-xl p-4 border border-slate-800">
                        <span class="text-xs text-slate-400">希望時間帯</span>
                        <select id="snipe-time" class="mt-1 w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                            <option value="ANY" selected>開いている枠ならいつでも (最速)</option>
                            <option value="10:10">10:10 - 12:10 (午前枠)</option>
                            <option value="12:15">12:15 - 14:15 (昼枠)</option>
                            <option value="14:20">14:20 - 16:20 (午後枠)</option>
                            <option value="16:25">16:25 - 18:25 (夕方枠)</option>
                            <option value="18:30">18:30 - 20:30 (夜間枠)</option>
                        </select>
                    </div>
                    <div class="glass-card rounded-xl p-4 border border-slate-800 flex items-end">
                        <button onclick="refreshLiveVacancies()" class="w-full py-2.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-cyan-300 font-semibold text-xs border border-cyan-500/30 transition flex items-center justify-center space-x-1.5">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            <span>空席状況更新</span>
                        </button>
                    </div>
                </div>

                <!-- Live Vacancies Grid -->
                <div class="mt-6">
                    <h4 class="text-sm font-semibold text-slate-300 mb-3 flex items-center space-x-2">
                        <i data-lucide="radar" class="w-4 h-4 text-cyan-400"></i>
                        <span>リアルタイム空席マトリクス</span>
                    </h4>
                    <div id="live-vacancies-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                        <div class="text-center py-8 col-span-full text-slate-500">空席情報を取得しています...</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- TAB 3: 取りたい時間に絶対に取る (Absolute Sniper) -->
        <section id="tab-content-absolute_sniper" class="hidden space-y-6">
            <div class="glass-panel rounded-2xl p-6 border border-slate-800 glow-rose">
                <div class="border-b border-slate-800 pb-4">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="crosshair" class="w-6 h-6 text-rose-500"></i>
                        <h2 class="text-xl font-bold text-white">取りたい時間に絶対に取る（ピンポイント・ミリ秒スナイパー）</h2>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">特定の日時・コマ（1週間前の受付開始や激戦枠）をターゲットに設定し、予約解禁時刻のミリ秒単位で超高速ポーリングを実行して確実に奪取します。</p>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">確保したい目標日</label>
                            <input type="date" id="target-date" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">目標時間帯スロット</label>
                            <select id="target-time" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white">
                                <option value="10:10">10:10 - 12:10 (第1枠)</option>
                                <option value="12:15">12:15 - 14:15 (第2枠)</option>
                                <option value="14:20">14:20 - 16:20 (第3枠)</option>
                                <option value="16:25">16:25 - 18:25 (第4枠)</option>
                                <option value="18:30">18:30 - 20:30 (第5枠)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">予約開始日時 (タイマー発動時刻)</label>
                            <input type="datetime-local" id="target-launch-time" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white">
                            <span class="text-[11px] text-slate-500 mt-1 block">※空欄の場合は即時ミリ秒アタックを開始します。</span>
                        </div>
                        <button onclick="createAbsoluteSniperTask()" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-sm shadow-lg shadow-rose-900/40 transition flex items-center justify-center space-x-2">
                            <i data-lucide="target" class="w-4 h-4"></i>
                            <span>ピンポイント絶対取得タスクを起動</span>
                        </button>
                    </div>

                    <div class="md:col-span-2 glass-card rounded-xl p-5 border border-slate-800 space-y-4">
                        <h4 class="text-sm font-semibold text-slate-300 flex items-center space-x-2">
                            <i data-lucide="gauge" class="w-4 h-4 text-rose-400"></i>
                            <span>超高速スナイパー・アタックシミュレーション</span>
                        </h4>
                        <div class="bg-slate-950/80 rounded-xl p-4 font-mono text-xs text-slate-300 space-y-2 border border-slate-800">
                            <div class="flex items-center justify-between text-slate-400 border-b border-slate-800 pb-1.5">
                                <span>POLLING FREQUENCY:</span>
                                <span class="text-cyan-400">200ms Rapid Interval</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-400 border-b border-slate-800 pb-1.5">
                                <span>PAYLOAD PRE-FETCH:</span>
                                <span class="text-emerald-400">Armed (CSRF + Token Cached)</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-400">
                                <span>RETRY STRATEGY:</span>
                                <span class="text-amber-400">Aggressive Burst (50 Probes)</span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            受付開始と同時に、バックグラウンドワーカーがミリ秒単位で予約確定APIへ直結リクエストを連続投入し、競合他者より最速で枠をロックします。
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- TAB 4: 自動監視タスク -->
        <section id="tab-content-tasks" class="hidden space-y-6">
            <div class="glass-panel rounded-2xl p-6 border border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="list-checks" class="w-5 h-5 text-indigo-400"></i>
                        <h2 class="text-lg font-bold text-white">バックグラウンド自動予約タスク一覧</h2>
                    </div>
                    <button onclick="loadDashboardStatus()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 transition flex items-center space-x-1.5">
                        <i data-lucide="refresh-cw" class="w-3 h-3"></i>
                        <span>更新</span>
                    </button>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900/80 text-slate-400 font-mono uppercase border-b border-slate-800">
                            <tr>
                                <th class="p-3">ID</th>
                                <th class="p-3">タイプ</th>
                                <th class="p-3">対象日 / スロット</th>
                                <th class="p-3">ステータス</th>
                                <th class="p-3">リトライ数</th>
                                <th class="p-3">最新ログ</th>
                                <th class="p-3 text-right">操作</th>
                            </tr>
                        </thead>
                        <tbody id="tasks-table-body" class="divide-y divide-slate-800/60 font-mono">
                            <tr><td colspan="7" class="p-4 text-center text-slate-500">タスクを取得中...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- TAB 5: 予約確認・取消 -->
        <section id="tab-content-my_reservations" class="hidden space-y-6">
            <div class="glass-panel rounded-2xl p-6 border border-slate-800">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="calendar" class="w-5 h-5 text-emerald-400"></i>
                        <h2 class="text-lg font-bold text-white">現在取得済みの座席予約</h2>
                    </div>
                    <button onclick="loadMyReservations()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 transition flex items-center space-x-1.5">
                        <i data-lucide="refresh-cw" class="w-3 h-3"></i>
                        <span>再照会</span>
                    </button>
                </div>

                <div id="my-reservations-container" class="mt-4 space-y-3">
                    <div class="text-center py-8 text-slate-500">予約一覧を取得中...</div>
                </div>
            </div>
        </section>

        <!-- TAB 6: アカウント & 設定 -->
        <section id="tab-content-account" class="hidden space-y-6">
            <div class="max-w-2xl mx-auto glass-panel rounded-2xl p-6 border border-slate-800">
                <div class="border-b border-slate-800 pb-3 mb-5">
                    <h2 class="text-lg font-bold text-white flex items-center space-x-2">
                        <i data-lucide="user-check" class="w-5 h-5 text-cyan-400"></i>
                        <span>神戸市立図書館 アカウント認証設定</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">図書館カードの利用者番号（先頭P）とパスワードを設定します。予約処理時に自動ログインされます。</p>
                </div>

                <form onsubmit="saveAccount(event)" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">利用者番号 (カード番号)</label>
                        <input type="text" id="acc-usercode" placeholder="例: P182790698" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white font-mono focus:outline-none focus:border-cyan-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">K-libネット パスワード</label>
                        <input type="password" id="acc-password" placeholder="パスワードを入力" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white font-mono focus:outline-none focus:border-cyan-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">アカウント表示名 (任意)</label>
                        <input type="text" id="acc-name" placeholder="例: 個人アカウント" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500">
                    </div>
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-sm shadow-md transition flex items-center justify-center space-x-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>アカウントを検証して保存</span>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-slate-800">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">登録済みアカウント</h4>
                    <div id="saved-accounts-list" class="space-y-2">
                        <span class="text-xs text-slate-500">アカウント読み込み中...</span>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 py-4 text-center text-xs text-slate-500 font-mono">
        Libraryes Intelligent Seat Orchestrator &copy; 2026
    </footer>

    <!-- Client-side Logic Script -->
    <script>
        let currentPurpose = 'focus';
        let currentStatus = null;

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            setDatePreset('TODAY');
            loadDashboardStatus();
        });

        function switchTab(tabId) {
            document.querySelectorAll('section[id^="tab-content-"]').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('bg-gradient-to-r', 'from-cyan-600', 'to-blue-600', 'text-white', 'shadow-md');
                el.classList.add('bg-slate-800/60', 'text-slate-300');
            });

            const targetSection = document.getElementById(`tab-content-${tabId}`);
            const targetBtn = document.getElementById(`tab-btn-${tabId}`);
            if (targetSection) targetSection.classList.remove('hidden');
            if (targetBtn) {
                targetBtn.classList.add('bg-gradient-to-r', 'from-cyan-600', 'to-blue-600', 'text-white', 'shadow-md');
                targetBtn.classList.remove('bg-slate-800/60', 'text-slate-300');
            }

            if (tabId === 'instant_snipe') refreshLiveVacancies();
            if (tabId === 'my_reservations') loadMyReservations();
            if (tabId === 'tasks') loadDashboardStatus();
            lucide.createIcons();
        }

        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const msgEl = document.getElementById('toast-message');
            const iconEl = document.getElementById('toast-icon');

            msgEl.textContent = message;
            toast.className = 'p-4 rounded-xl border flex items-center justify-between transition-all duration-300';

            if (type === 'success') {
                toast.classList.add('bg-emerald-950/80', 'border-emerald-500/50', 'text-emerald-200');
                iconEl.setAttribute('data-lucide', 'check-circle');
            } else if (type === 'error') {
                toast.classList.add('bg-rose-950/80', 'border-rose-500/50', 'text-rose-200');
                iconEl.setAttribute('data-lucide', 'alert-circle');
            } else {
                toast.classList.add('bg-slate-900', 'border-slate-700', 'text-slate-200');
                iconEl.setAttribute('data-lucide', 'info');
            }
            toast.classList.remove('hidden');
            lucide.createIcons();

            setTimeout(hideToast, 5000);
        }

        function hideToast() {
            document.getElementById('toast').classList.add('hidden');
        }

        function setDatePreset(type) {
            const dateInput = document.getElementById('ai-date');
            const snipeDateInput = document.getElementById('snipe-date');
            const targetDateInput = document.getElementById('target-date');

            let d = new Date();
            if (type === 'TOMORROW') {
                d.setDate(d.getDate() + 1);
            } else if (type === 'THIS_WEEKEND') {
                const day = d.getDay();
                const diff = d.getDate() + (6 - day); // Next Saturday
                d.setDate(diff);
            }
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            const str = `${yyyy}-${mm}-${dd}`;

            if (dateInput) dateInput.value = str;
            if (snipeDateInput) snipeDateInput.value = str;
            if (targetDateInput) targetDateInput.value = str;
        }

        function selectPurpose(purpose) {
            currentPurpose = purpose;
            document.querySelectorAll('.purpose-btn').forEach(el => {
                el.classList.remove('border-cyan-500', 'bg-cyan-950/40', 'text-cyan-200');
                el.classList.add('border-slate-700', 'bg-slate-900/60', 'text-slate-300');
            });
            const selectedBtn = document.getElementById(`purpose-${purpose}`);
            if (selectedBtn) {
                selectedBtn.classList.add('border-cyan-500', 'bg-cyan-950/40', 'text-cyan-200');
                selectedBtn.classList.remove('border-slate-700', 'bg-slate-900/60', 'text-slate-300');
            }
        }

        async function loadDashboardStatus() {
            try {
                const res = await fetch('backend/api.php?action=status');
                const data = await res.json();
                if (data.success) {
                    currentStatus = data;
                    renderAccounts(data.accounts);
                    renderTasks(data.tasks);
                }
            } catch (err) {
                console.error(err);
            }
        }

        function renderAccounts(accounts) {
            const badge = document.getElementById('account-badge');
            const text = document.getElementById('active-user-text');
            const list = document.getElementById('saved-accounts-list');

            if (accounts && accounts.length > 0) {
                badge.classList.remove('hidden');
                text.textContent = accounts[0].usercode + (accounts[0].name ? ` (${accounts[0].name})` : '');

                list.innerHTML = accounts.map(a => `
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900 border border-slate-800">
                        <div class="font-mono text-sm">
                            <span class="text-cyan-400 font-bold">${a.usercode}</span>
                            <span class="text-slate-400 text-xs ml-2">${a.name || ''}</span>
                        </div>
                        <button onclick="deleteAccount(${a.id})" class="text-rose-400 hover:text-rose-300 text-xs px-2 py-1 bg-rose-950/40 border border-rose-800/40 rounded-lg">削除</button>
                    </div>
                `).join('');
            } else {
                badge.classList.remove('hidden');
                text.textContent = 'アカウント未設定';
                list.innerHTML = `<span class="text-xs text-amber-400">アカウントが登録されていません。上記フォームから登録してください。</span>`;
            }
        }

        function renderTasks(tasks) {
            const tbody = document.getElementById('tasks-table-body');
            const countEl = document.getElementById('active-tasks-count');
            const activeCount = tasks.filter(t => t.status === 'pending' || t.status === 'monitoring').length;
            countEl.textContent = activeCount;

            if (!tasks || tasks.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-center text-slate-500">実行中のタスクはありません</td></tr>`;
                return;
            }

            tbody.innerHTML = tasks.map(t => {
                let badgeClass = 'bg-slate-800 text-slate-300';
                if (t.status === 'success') badgeClass = 'bg-emerald-950 text-emerald-400 border border-emerald-800';
                if (t.status === 'monitoring') badgeClass = 'bg-amber-950 text-amber-400 border border-amber-800 animate-pulse';
                if (t.status === 'failed') badgeClass = 'bg-rose-950 text-rose-400 border border-rose-800';

                return `
                    <tr class="hover:bg-slate-900/40 transition">
                        <td class="p-3 text-slate-400">#${t.id}</td>
                        <td class="p-3 font-semibold text-cyan-300">${t.type}</td>
                        <td class="p-3 text-slate-200">${t.target_date} ${t.target_time_slot || ''}</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${badgeClass}">${t.status}</span></td>
                        <td class="p-3 text-slate-400">${t.retry_count} / ${t.max_retries}</td>
                        <td class="p-3 text-slate-400 truncate max-w-xs" title="${t.result_message || ''}">${t.result_message || '-'}</td>
                        <td class="p-3 text-right space-x-1">
                            <button onclick="runTaskNow(${t.id})" class="px-2 py-1 rounded bg-cyan-950 hover:bg-cyan-900 text-cyan-300 text-[11px] border border-cyan-800" title="即時実行">実行</button>
                            <button onclick="deleteTask(${t.id})" class="px-2 py-1 rounded bg-rose-950 hover:bg-rose-900 text-rose-300 text-[11px] border border-rose-800" title="削除">削除</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function saveAccount(e) {
            e.preventDefault();
            const usercode = document.getElementById('acc-usercode').value;
            const password = document.getElementById('acc-password').value;
            const name = document.getElementById('acc-name').value;

            showToast('神戸市立図書館システムへ接続・認証中...', 'info');
            try {
                const res = await fetch('backend/api.php?action=save_account', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({usercode, password, name})
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message, 'success');
                    loadDashboardStatus();
                } else {
                    showToast(data.message, 'error');
                }
            } catch (err) {
                showToast('通信エラーが発生しました。', 'error');
            }
        }

        async function deleteAccount(id) {
            if (!confirm('アカウントを削除しますか？')) return;
            const res = await fetch('backend/api.php?action=delete_account', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id})
            });
            const data = await res.json();
            if (data.success) {
                showToast('アカウントを削除しました', 'success');
                loadDashboardStatus();
            }
        }

        async function runAiAnalysis() {
            const date = document.getElementById('ai-date').value;
            const corner = document.getElementById('ai-corner').value;
            const container = document.getElementById('ai-results-container');
            const statusTag = document.getElementById('ai-status-tag');

            container.innerHTML = `<div class="text-center py-12 text-cyan-400"><i data-lucide="loader" class="w-8 h-8 mx-auto mb-2 animate-spin"></i><p class="text-xs font-mono">AI スコアリング & 空席マトリクス解析中...</p></div>`;
            lucide.createIcons();

            try {
                const res = await fetch(`backend/api.php?action=ai_recommend&date=${date}&corner=${corner}&purpose=${currentPurpose}`);
                const data = await res.json();

                if (!data.success || !data.top_recommendations || data.top_recommendations.length === 0) {
                    statusTag.textContent = '空席なし';
                    statusTag.className = 'text-xs px-2.5 py-1 rounded-full bg-rose-950 text-rose-400 font-mono';
                    container.innerHTML = `
                        <div class="glass-card rounded-xl p-6 text-center border border-slate-800 space-y-3">
                            <i data-lucide="alert-triangle" class="w-8 h-8 text-amber-400 mx-auto"></i>
                            <h4 class="text-white font-bold text-sm">指定日は現在満席です</h4>
                            <p class="text-xs text-slate-400">キャンセル待ち自動スナイパーを設定すると、空きが出た瞬間にAIが即時予約します。</p>
                            <button onclick="createSnipeTaskForDate('${date}', '${corner}')" class="mt-2 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-md">
                                空き発生時 即時予約タスクを作成
                            </button>
                        </div>
                    `;
                    lucide.createIcons();
                    return;
                }

                statusTag.textContent = `候補 ${data.top_recommendations.length}件 検出`;
                statusTag.className = 'text-xs px-2.5 py-1 rounded-full bg-emerald-950 text-emerald-400 font-mono';

                container.innerHTML = data.top_recommendations.map((slot, idx) => `
                    <div class="glass-card rounded-2xl p-5 border ${idx === 0 ? 'border-cyan-500/80 bg-cyan-950/20' : 'border-slate-800'} space-y-3 transition hover:border-slate-600">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center space-x-3">
                                <span class="w-8 h-8 rounded-xl ${idx === 0 ? 'bg-cyan-500 text-slate-950' : 'bg-slate-800 text-slate-300'} flex items-center justify-center font-bold text-sm">
                                    #${idx + 1}
                                </span>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg font-bold text-white font-mono">${slot.time || slot.raw_label}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full ${idx === 0 ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/40' : 'bg-slate-800 text-slate-400'} font-semibold">${slot.recommendation_tag}</span>
                                    </div>
                                    <span class="text-xs text-slate-400 font-mono">${slot.date}</span>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-400 block font-mono">AI 快適度スコア</span>
                                    <span class="text-xl font-extrabold text-cyan-400 font-mono">${slot.ai_score}<span class="text-xs text-slate-500">/100</span></span>
                                </div>
                                <button onclick="quickReserve('${slot.date}', '${slot.slot_id}', '${corner}')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold text-xs shadow-md transition flex items-center space-x-1.5">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>この枠を今すぐ予約</span>
                                </button>
                            </div>
                        </div>

                        <!-- AI Reasoning -->
                        <div class="bg-slate-900/60 rounded-xl p-3 text-xs text-slate-300 space-y-1 border border-slate-800/80">
                            <span class="font-bold text-cyan-300 flex items-center space-x-1 mb-1">
                                <i data-lucide="sparkles" class="w-3 h-3"></i>
                                <span>AI 推薦理由:</span>
                            </span>
                            ${slot.reasons.map(r => `<p class="text-slate-300">• ${r}</p>`).join('')}
                        </div>
                    </div>
                `).join('');

                lucide.createIcons();
            } catch (err) {
                container.innerHTML = `<div class="p-4 text-center text-rose-400 text-xs">AI 解析中にエラーが発生しました。</div>`;
            }
        }

        async function quickReserve(date, slotId, corner) {
            showToast('神戸市立図書館システムへ予約リクエスト送信中...', 'info');
            try {
                const res = await fetch('backend/api.php?action=quick_reserve', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({date, slot_id: slotId, corner_code: corner})
                });
                const data = await res.json();
                if (data.success) {
                    showToast(`予約完了: ${date} (予約番号: ${data.data.reservation_number || 'OK'})`, 'success');
                    loadMyReservations();
                } else {
                    showToast(data.message, 'error');
                }
            } catch (err) {
                showToast('予約送信エラーが発生しました', 'error');
            }
        }

        async function triggerInstantSnipe() {
            const date = document.getElementById('snipe-date').value;
            const corner = document.getElementById('snipe-corner').value;
            const time = document.getElementById('snipe-time').value;

            showToast('即時スナイパーを起動中...', 'info');
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
                showToast('即時スナイパーが稼働しました！', 'success');
                loadDashboardStatus();
                switchTab('tasks');
            } else {
                showToast(data.message, 'error');
            }
        }

        async function createAbsoluteSniperTask() {
            const date = document.getElementById('target-date').value;
            const time = document.getElementById('target-time').value;
            const launch = document.getElementById('target-launch-time').value;

            showToast('ピンポイント絶対取得タスクを登録中...', 'info');
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
                showToast('絶対取得スナイパーがセットされました！', 'success');
                loadDashboardStatus();
                switchTab('tasks');
            } else {
                showToast(data.message, 'error');
            }
        }

        async function refreshLiveVacancies() {
            const date = document.getElementById('snipe-date').value;
            const corner = document.getElementById('snipe-corner').value;
            const container = document.getElementById('live-vacancies-grid');

            container.innerHTML = `<div class="text-center py-8 col-span-full text-slate-500">スキャン中...</div>`;
            try {
                const res = await fetch(`backend/api.php?action=public_vacancies&date=${date}&corner=${corner}`);
                const data = await res.json();

                if (!data.success || !data.data.slots || data.data.slots.length === 0) {
                    container.innerHTML = `<div class="text-center py-8 col-span-full text-amber-400 text-xs">現在、表示可能な空席はありません。自動スナイパーの待機をおすすめします。</div>`;
                    return;
                }

                container.innerHTML = data.data.slots.map(s => `
                    <div class="glass-card rounded-xl p-3.5 border border-emerald-500/40 bg-emerald-950/20 flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-mono font-bold text-emerald-300 block">${s.label}</span>
                            <span class="text-[10px] text-slate-400 font-mono">枠ID: ${s.slot_id}</span>
                        </div>
                        <button onclick="quickReserve('${s.date}', '${s.slot_id}', '${corner}')" class="mt-3 w-full py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow transition">
                            即時取得
                        </button>
                    </div>
                `).join('');
            } catch (err) {
                container.innerHTML = `<div class="text-center py-8 col-span-full text-rose-400 text-xs">空席照会エラー</div>`;
            }
        }

        async function loadMyReservations() {
            const container = document.getElementById('my-reservations-container');
            container.innerHTML = `<div class="text-center py-8 text-slate-500">予約一覧を取得中...</div>`;
            try {
                const res = await fetch('backend/api.php?action=my_reservations');
                const data = await res.json();
                if (data.success && data.reservations && data.reservations.length > 0) {
                    container.innerHTML = data.reservations.map(r => `
                        <div class="glass-card rounded-xl p-4 border border-slate-800 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-bold text-white font-mono">${r.date || ''} ${r.time || ''}</span>
                                <span class="text-xs text-slate-400 block">${r.raw || ''}</span>
                            </div>
                            <button onclick="cancelMyReservation('${r.id || '0'}')" class="px-3 py-1.5 rounded-lg bg-rose-950/80 hover:bg-rose-900 text-rose-300 border border-rose-800 text-xs font-bold transition">
                                予約取消
                            </button>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = `<div class="text-center py-8 text-slate-500 text-xs">現在有効な予約はありません。</div>`;
                }
            } catch (err) {
                container.innerHTML = `<div class="text-center py-8 text-rose-400 text-xs">予約一覧取得エラー（アカウント設定を確認してください）</div>`;
            }
        }

        async function cancelMyReservation(id) {
            if (!confirm('この予約を取り消しますか？')) return;
            const res = await fetch('backend/api.php?action=cancel_reservation', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({slot_id: id})
            });
            const data = await res.json();
            if (data.success) {
                showToast('予約を取り消しました', 'success');
                loadMyReservations();
            } else {
                showToast(data.message, 'error');
            }
        }

        async function runTaskNow(id) {
            showToast(`タスク #${id} を実行中...`, 'info');
            const res = await fetch('backend/api.php?action=run_task', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({task_id: id})
            });
            const data = await res.json();
            if (data.success) {
                showToast(`タスク実行完了: ${data.result.message || '完了'}`, 'success');
                loadDashboardStatus();
            } else {
                showToast(data.message, 'error');
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
                showToast('タスクを削除しました', 'success');
                loadDashboardStatus();
            }
        }
    </script>
</body>
</html>
