if (!("Notification" in window)) {
    alert("This browser does not support desktop notifications.");
}

function showNotification() {
    if (Notification.permission === "granted") {
        new Notification("Earthquake Detected!", {
            body: "Please stay safe and follow evacuation protocols if necessary."
        });
    } else if (Notification.permission !== "denied") {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                new Notification("Earthquake Detected!", {
                    body: "Please stay safe and follow evacuation protocols if necessary."
                });
            }
        });
    }
}

document.getElementById("notify-btn").addEventListener("click", showNotification);