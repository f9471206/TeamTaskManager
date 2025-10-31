# 📝 任務指派系統

任務指派系統是一個以 Laravel 為核心開發的專案管理平台，提供專案建立、任務分派、成員管理與權限控管等功能。

---

## 📦 技術棧

-   **PHP** 8.2+
-   **Laravel 12** - Web 應用框架，負責路由、控制器與資料處理
-   **Blade** - Laravel 內建模板引擎，用於前端頁面渲染
-   **MySQL** - 資料庫系統，用於儲存專案、任務與使用者資料
-   **Laravel Reverb** - 即時廣播與通知系統（支援 WebSocket）
-   **Laravel Sanctum** - 使用者登入與 API 權限驗證機制
-   **Tailwind CSS** - 前端 UI 樣式框架，快速打造一致視覺
-   **opcodesio/log-viewer** - 伺服器日誌檢視工具
-   **Laravel Sail / Docker** - 容器化開發與部署支援
-   **PHPUnit + Mockery** - 單元測試與模擬工具

---

## 🚀 啟動專案

```bash
# 安裝 Composer 依賴
composer install

# 複製環境檔案
cp .env.example .env

# 產生應用程式金鑰
php artisan key:generate

# 執行資料表遷移
php artisan migrate

# 安裝 Reverb 廣播設定與資料表
php artisan install:broadcasting

# 安裝前端依賴
npm install

# 編譯前端資源（開發模式）
npm run dev
# 或編譯生產模式
# npm run build

# 啟動 Laravel 開發伺服器
php artisan serve

# 啟動隊列監聽 (處理廣播、通知等任務)
php artisan queue:listen --tries=1

# 啟動 Reverb 廣播伺服器
php artisan reverb:start

```
