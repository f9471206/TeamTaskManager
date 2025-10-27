window.api = {
    baseURL: "/api",

    request: async (url, options = {}) => {
        const token = sessionStorage.getItem("api_token");

        const fetchOptions = {
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                ...(token ? { Authorization: `Bearer ${token}` } : {}),
                ...options.headers,
            },
            ...options,
        };

        try {
            const res = await fetch(window.api.baseURL + url, fetchOptions);

            // 204 No Content → 回傳 null
            if (res.status === 204) return null;

            // 嘗試解析 JSON，如果失敗就回傳空物件
            let data;
            try {
                data = await res.json();
            } catch {
                data = {};
            }

            // 401 處理 → Token 過期或未授權
            if (res.status === 401) {
                alert(data.msg || "Token 已過期或未授權，請重新登入");
                sessionStorage.removeItem("api_token");
                window.location.href = "/login"; // 導回登入頁
                throw data;
            }

            if (!res.ok) throw data;

            return data; // 成功回傳資料
        } catch (err) {
            alert(err.msg || err.message || "網路錯誤");
            throw err;
        }
    },

    get: (url) => window.api.request(url, { method: "GET" }),
    post: (url, body) =>
        window.api.request(url, { method: "POST", body: JSON.stringify(body) }),
    put: (url, body) =>
        window.api.request(url, { method: "PUT", body: JSON.stringify(body) }),
    delete: (url) => window.api.request(url, { method: "DELETE" }),
};
