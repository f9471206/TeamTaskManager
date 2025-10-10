window.api = {
    baseURL: "/api",

    request: async (url, options = {}) => {
        const token = sessionStorage.getItem("api_token");

        try {
            const res = await fetch(window.api.baseURL + url, {
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    ...(token ? { Authorization: `Bearer ${token}` } : {}),
                },
                ...options,
            });

            const data = await res.json();

            if (!res.ok) throw data;
            return data; // ✅ 成功回傳資料
        } catch (err) {
            // 網路錯誤或其他 fetch 錯誤
            alert(err.msg || "網路錯誤");
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
