
document.getElementById("loginForm").addEventListener("submit", async function(event) {
    event.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    const message = document.getElementById("loginMessage");
    const button = this.querySelector("button[type='submit']");

    message.textContent = "";
    button.disabled = true;
    button.textContent = "Giriş yapılıyor...";

    try {
        const response = await fetch("api/login.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                email,
                password
            })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Giriş yapılamadı.");
        }

      if (result.role === "admin") {
    window.location.href = "developer.html";
} else {
    window.location.href = "admin.html";
}

    } catch (error) {
        message.textContent = error.message;
    }

    button.disabled = false;
    button.textContent = "Giriş Yap";
});
// İşletme kayıt bağlantısının görünürlüğü
async function loadRegistrationVisibility() {
    const registrationLink = document.getElementById("registrationLink");

    try {
        const response = await fetch("api/registration-settings.php");
        const result = await response.json();

        if (!response.ok || !result.success || result.enabled !== true) {
            registrationLink.hidden = true;
        }

    } catch (error) {
        registrationLink.hidden = true;
    }
}

loadRegistrationVisibility();
