import Echo from "laravel-echo";

import Pusher from "pusher-js";
window.Pusher = Pusher;

const sanctumToken = sessionStorage.getItem("api_token");

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
    auth: {
        headers: {
            Authorization: `Bearer ${sanctumToken}`, // 這裡的 Token 必須是有效的！
            Accept: "application/json",
        },
    },
});
