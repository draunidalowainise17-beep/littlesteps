"use strict";

document.addEventListener("DOMContentLoaded", () => {
    // UI Interaction Elements
    const authTrigger = document.getElementById("login-btn");
    const authPanel = document.querySelector(".login-form");

    // Utility: Limit rapid event firing
    const applyDebounce = (callback, delay) => {
        let timer;
        return (...argumentsList) => {
            clearTimeout(timer);
            timer = setTimeout(() => callback(...argumentsList), delay);
        };
    };

    // Toggle Dropdown logic
    if (authTrigger && authPanel) {
        const handlePanelToggle = applyDebounce(() => {
            authPanel.classList.toggle("active");
        }, 200);
        authTrigger.addEventListener("click", handlePanelToggle);
    }
});

// Utility: Navigation
const navigateBack = () => {
    window.history.back();
};

// -------------------------------------------------------------
// State Synchronization (Replaces old auto-refresh hack)
// -------------------------------------------------------------
(() => {
    const SYNC_KEY = "app_sync_timestamp";
    let localRecord = localStorage.getItem(SYNC_KEY) || Date.now().toString();

    const verifySystemState = () => {
        const currentRecord = localStorage.getItem(SYNC_KEY) || Date.now().toString();
        if (parseInt(currentRecord, 10) > parseInt(localRecord, 10)) {
            window.location.reload();
        }
    };

    // Monitor for changes across tabs/windows
    setInterval(verifySystemState, 1500);

    // Initial state registration
    window.addEventListener("load", () => {
        setTimeout(() => {
            localStorage.setItem(SYNC_KEY, Date.now().toString());
        }, 5000);
    });
})();