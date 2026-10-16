# 美食訂餐網：選餐與會員管理練習

提供餐點分類、商品卡片與購物車，並保留橘色漸層的介面。PHP 版本另外提供會員註冊、登入、登出與管理員庫存調整。

目前是練習用原型：**購物車不會送出訂單、不會扣庫存，也不提供付款**。重新整理或切換選餐頁會清空購物車。

[開啟前端展示](https://owen-xie1995.github.io/Ordering-System-Web/)

## 兩種使用方式

| 方式 | 可使用的功能 | 啟動方式 |
| --- | --- | --- |
| GitHub Pages／靜態伺服器 | 餐點分類、購物車與價格試算 | 開啟上方展示連結 |
| PHP + MySQL／MariaDB | 上述功能，加上會員與管理員庫存頁 | 依下方步驟設定資料庫並啟動 PHP |

GitHub Pages 不執行 PHP，因此展示站不會開放會員與庫存功能。登入導覽會顯示展示模式說明，不會送出無效表單。可參考 [GitHub Pages 說明](https://docs.github.com/en/pages/getting-started-with-github-pages/creating-a-github-pages-site)。

## 本機執行 PHP 版本

需求：PHP 8.0 以上，含 `mysqli`、`gd`；MySQL 8 或 MariaDB 10.4 以上。JavaScript 測試另需 Node.js 20 以上；HTTP 整合測試使用 Python 3 標準函式庫。

1. 建立空的資料庫與專用帳號。例如以資料庫管理者執行以下 SQL；請自行設定密碼：

   ```sql
   CREATE DATABASE ordering_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'ordering'@'127.0.0.1' IDENTIFIED BY '請改成自己的密碼';
   GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX
     ON ordering_system.* TO 'ordering'@'127.0.0.1';
   ```

2. 在準備啟動服務的同一個終端設定環境變數：

   ```sh
   export DB_HOST=127.0.0.1
   export DB_PORT=3306
   export DB_NAME=ordering_system
   export DB_USER=ordering
   export DB_PASSWORD='請改成自己的密碼'
   php scripts/setup.php
   ```

   `setup.php` 建立資料表及 18 筆展示庫存，不建立預設會員或管理員，不覆寫既有庫存。`.env.example` 只列出設定名稱；程式**不會自動讀取 .env**。XAMPP 使用者可用其 PHP 執行檔的完整路徑取代 `php`，並確認 CLI 與伺服器繼承相同環境變數。

3. 如需管理員，使用尚未註冊的帳號建立。先在終端設定 `ADMIN_PASSWORD`，再執行：

   ```sh
   php scripts/create_admin.php admin
   unset ADMIN_PASSWORD
   ```

   新密碼需為 8–72 位元組。此指令不會覆寫或提升既有帳號；請勿把密碼加入版本控制。

4. 啟動開發伺服器：

   ```sh
   php -S 127.0.0.1:8000 router.php
   ```

   開啟 [本機首頁](http://127.0.0.1:8000/)。一般會員可從註冊頁建立；只有管理員會看到庫存入口，直接請求管理頁也會在伺服器端檢查角色。

PHP 開發伺服器僅供本機使用。`router.php` 會阻擋設定檔、SQL、CLI 指令與測試目錄的 HTTP 存取。若改用 Apache／Nginx，需自行設定相同的目錄封鎖及 HTTPS，並將執行帳號與資料庫建置帳號分開。這個專案尚未包含正式營運所需的帳號復原、完整登入限流、稽核紀錄與部署設定。

## 從舊版資料庫升級

舊版 `login.pw` 是明文；新版登入只接受密碼雜湊，不提供明文比對的備援路徑。

1. 暫停服務並完整備份資料庫；設定環境變數指向舊資料庫。
2. 先執行 `php scripts/migrate_legacy.php` 查看待轉換筆數。此步驟不會寫入。
3. 確認備份後執行 `php scripts/migrate_legacy.php --apply`。它會擴充密碼欄位、補上角色欄位及帳號唯一索引，將明文轉為雜湊，並保留已存在的雜湊。新增的角色預設為 `member`。
4. 再執行 `php scripts/setup.php` 補齊庫存表，確認登入後再恢復服務。

遷移前會檢查重複帳號、空密碼與超過 72 位元組的明文密碼；這些情況會停止並要求先處理。MySQL 的欄位變更會隱含提交，因此備份是必要的。舊版不足八位元組的非空密碼可在轉換後登入；新註冊與重設密碼仍強制至少八位元組。

若需重設既有帳號，先設定 `NEW_PASSWORD`，再執行 `php scripts/set_password.php 帳號`；角色不會改變。完成後 `unset NEW_PASSWORD`。

## 檢查方式

```sh
node --test tests/cart.test.js
php tests/security.php
```

購物車回歸測試涵蓋不同飲料的品名、單價、數量及總額，避免不同商品共用 ID。PHP 檢查涵蓋密碼雜湊、拒絕明文驗證、CSRF 與庫存數值邊界。

HTTP 整合測試會啟動自己的 PHP 伺服器及工作階段目錄。請另建**可丟棄且名稱以 `_test` 結尾**的資料庫，將前述 `DB_*` 環境變數改為該庫，然後執行：

```sh
export DB_NAME=ordering_system_test
export ALLOW_TEST_DB=1
python3 tests/http_smoke.py
```

測試涵蓋註冊及舊路由、SQL 輸入、登入與 session 更新、角色授權、CSRF、庫存更新與登出，也會確認密碼遷移的預覽及實際轉換。測試會建表、調整測試庫存及建立測試帳號，結束時恢復庫存並移除測試帳號；**不可指向正式資料庫**。如果 PHP 不在 PATH，可設定 `PHP_BINARY` 為執行檔完整路徑。

GitHub Actions 會使用獨立 MySQL 服務執行相同檢查。實際的桌面及手機版面仍需另外以瀏覽器檢查。

## 檔案配置

- `index.html`、`order.html`：前端展示與導覽。
- `assets/menu-data.js`、`assets/cart.js`：餐點資料及可測試的購物車邏輯。
- `includes/app.php`：環境設定、資料庫、工作階段、角色及 CSRF 檢查。
- `login.php`、`register.php`、`home.php`、`admin.php`：會員與管理流程。
- `database/schema.sql`、`scripts/`：資料表、展示庫存、管理員建立與舊密碼遷移。

商品圖片沿用專案既有素材；來源及使用授權尚未整理，不宣稱可直接用於商業服務。
