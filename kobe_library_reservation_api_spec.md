# 神戸市立図書館 座席WEB予約システム (eBoothWeb) API 仕様書

## 1. 概要
- **システム名**: 神戸市立図書館 座席WEB予約システム
- **開発ベンダー**: 株式会社タックポート (`eBoothWeb`)
- **ベースホスト**: `https://ebwebreserve3.tackport.co.jp`
- **ベースパス**: `/eboothweb_kobe`
- **プロトコル**: HTTPS (HTTP/1.1)
- **データ形式**:
  - リクエスト: `application/x-www-form-urlencoded`
  - レスポンス: `text/html; charset=UTF-8`
  - パラメータペイロード: Base64 エンコードされた JSON 文字列

---

## 2. 認証 & セッション管理

### 2.1 セッション Cookie
| Cookie 名 | 説明 |
| :--- | :--- |
| `EBOOTHWEB` | PHPセッション識別子 |
| `csrfToken` | CSRFトークン |

### 2.2 CSRF 対策
- フォーム送信（POST）時には、Cookie の `csrfToken` と一致する値をフォームパラメータ `_csrfToken` として送信する必要がある。
- 初期トークンは `GET /eboothweb_kobe/areainfo/login` にアクセスした際の `Set-Cookie` および HTML 内の `<input name="_csrfToken">` より取得する。

### 2.3 ログイン資格情報
- `usercode`: 利用者番号（先頭大文字 `P` + 数字、正規表現 `^[P0-9]+$`、最大10文字）
- `password`: パスワード（K-libネット共通、最大30文字）

---

## 3. マスターコード定義

### 3.1 図書館・エリアコード (`AreaCode`)
| コード | 図書館名 |
| :--- | :--- |
| `10000` | 名谷図書館 |
| `20000` | 西図書館 |
| `30000` | 中央図書館 |
| `40000` | 東灘図書館 |
| `50000` | 北神図書館 |
| `60000` | 垂水図書館 |

### 3.2 コーナーコード (`CornerCode`) ※垂水図書館の例
| コード | コーナー名 | 予約種別エンドポイント |
| :--- | :--- | :--- |
| `61000` | 2F 南カウンター席 | `/reservation/select?id=61000` |
| `62000` | 2F キャレル席 | `/reservation/select?id=62000` |
| `63000` | 2F 西カウンター席 | `/reservation/groups?id=63000` |
| `64000` | 3F 学習室 | `/reservation/groups?id=64000` |
| `66000` | セミナー室 | `/reservation/select2?id=66000` |

---

## 4. エンドポイント仕様

### 4.1 ログイン (`/areainfo/login`)
- **GET**: ログインフォームおよび現時点の空席速報を取得
  - クエリパラメータ: `area` (任意: 初期表示する図書館コード)
- **POST**: ログイン認証実行
  - リクエストボディ (`application/x-www-form-urlencoded`):
    - `_method`: `POST`
    - `_csrfToken`: CSRFトークン文字列
    - `usercode`: 利用者番号
    - `password`: パスワード
  - レスポンス:
    - 成功時: `302 Found` (Location: `/eboothweb_kobe/menu`)
    - 失敗時: `200 OK` (エラーメッセージ付きHTML)

### 4.2 ログアウト (`/areainfo/logout`)
- **GET**: セッション破棄
  - レスポンス: `302 Found` (Location: `/eboothweb_kobe/areainfo/login`)

### 4.3 公開空席照会 (`/areainfo/usagesWeb`)
- **GET**: 未ログイン状態で特定日・特定館・コーナーの空席状況一覧を取得
  - クエリパラメータ:
    - `date`: 照会対象日 (`YYYYMMDD` 形式)
    - `area`: 図書館コード (`AreaCode`)
    - `corner`: コーナーコード (`CornerCode`, 任意)
    - `group`: グループID (任意)

### 4.4 メインメニュー (`/menu` または `/menu/index`)
- **GET**: ログイン後のトップメニュー画面表示
  - 認証: 必須

### 4.5 利用規約同意 (`/rule` / `/rule/ruleconfirm`)
- **GET `/rule`**: 規約画面表示
  - クエリパラメータ: `RN` (予約番号), `DT` (日付: `YYYY/MM/DD`) ※予約変更時のみ付与
- **POST `/rule/ruleconfirm`**: 規約同意送信
  - リクエストボディ:
    - `_csrfToken`: CSRFトークン
    - `ck01`: `1`
  - レスポンス: `302 Found` (Location: `/eboothweb_kobe/reservation/areas`)

### 4.6 予約・空席選択 (`/reservation/...`)
- **GET `/reservation/areas`**: 図書館選択
- **GET `/reservation/corners?id={areaCode}`**: コーナー選択
- **GET `/reservation/select?id={cornerCode}&date={YYYYMMDD}`**: 指定コーナーの空席カレンダー表示
- **GET `/reservation/confirm?date={YYYYMMDD}&id={slotIndex}`**: 予約確認画面
  - HTML内に予約情報 Base64 JSON を含んだ `<input type="hidden" name="reservation" value="...">` が生成される。
- **POST `/reservation/result`**: 予約確定送信
  - リクエストボディ:
    - `_csrfToken`: CSRFトークン
    - `reservation`: Base64 エンコードされた予約パラメータ
  - レスポンス: `200 OK` (予約完了画面)

### 4.7 予約確認・取消 (`/choice/...`)
- **GET `/choice/index`**: 取得済み予約の一覧表示
- **GET `/choice/deleteconfirm?id={reservationSlotId}`**: 予約取消確認画面
- **GET `/choice/delete`**: 予約取消実行
  - レスポンス: `200 OK` (取消完了画面)

---

## 5. データペイロード仕様 (`reservation`)

予約確定時に送信される `reservation` フィールドは、以下の JSON オブジェクトを Base64 エンコードした文字列。

```json
{
  "CornerCode": "62000",
  "StartTime": "10:10",
  "EndTime": "12:10",
  "Date": "2026/09/02",
  "Weekday": "(水)",
  "AreaCode": "60000",
  "AreaName": "垂水図書館",
  "CornerName": "2F キャレル席",
  "GroupCode": null,
  "GroupName": null,
  "BoothCode": null,
  "DatabaseReserve": null
}
```

---

## 6. Python による実装例

```python
import base64
import json
import re
import requests


class KobeLibraryClient:

  BASE_URL = "https://ebwebreserve3.tackport.co.jp/eboothweb_kobe"

  def __init__(self):
    self.session = requests.Session()
    self.session.headers.update({
        "User-Agent": (
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
            " (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
        )
    })
    self.csrf_token = None

  def fetch_initial_token(self):
    res = self.session.get(f"{self.BASE_URL}/areainfo/login")
    match = re.search(r'name="_csrfToken"\s+value="([^"]+)"', res.text)
    if match:
      self.csrf_token = match.group(1)
    return self.csrf_token

  def login(self, usercode, password):
    if not self.csrf_token:
      self.fetch_initial_token()
    payload = {
        "_method": "POST",
        "_csrfToken": self.csrf_token,
        "usercode": usercode,
        "password": password,
    }
    res = self.session.post(
        f"{self.BASE_URL}/areainfo/login", data=payload, allow_redirects=True
    )
    return "/menu" in res.url

  def get_public_usages(self, date_str, area_code="60000", corner_code=None):
    params = {"date": date_str, "area": area_code}
    if corner_code:
      params["corner"] = corner_code
    res = self.session.get(f"{self.BASE_URL}/areainfo/usagesWeb", params=params)
    return res.text

  def reserve(self, reservation_data: dict):
    # 規約同意
    self.session.post(
        f"{self.BASE_URL}/rule/ruleconfirm",
        data={"_csrfToken": self.csrf_token, "ck01": "1"},
        allow_redirects=True,
    )
    # 予約確定
    res_b64 = base64.b64encode(
        json.dumps(reservation_data, ensure_ascii=False).encode("utf-8")
    ).decode("utf-8")
    payload = {"_csrfToken": self.csrf_token, "reservation": res_b64}
    res = self.session.post(
        f"{self.BASE_URL}/reservation/result", data=payload
    )
    return res.status_code == 200

  def cancel_reservation(self):
    # 予約一覧から最新の取消を実行
    res = self.session.get(f"{self.BASE_URL}/choice/delete")
    return res.status_code == 200
```
