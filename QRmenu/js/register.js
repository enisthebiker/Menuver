// İşletme kayıt iznini kontrol et
async function checkRegistrationPermission() {
    const form = document.getElementById("registerForm");
    const closedMessage = document.getElementById("registrationClosedMessage");

    try {
        const response = await fetch("api/registration-settings.php");
        const result = await response.json();

        if (response.ok && result.success && result.enabled === true) {
            form.hidden = false;
            closedMessage.hidden = true;
        } else {
            form.hidden = true;
            closedMessage.hidden = false;
        }

    } catch (error) {
        form.hidden = true;
        closedMessage.hidden = false;
    }
}

checkRegistrationPermission();
document.getElementById("registerForm").addEventListener("submit", async function(event) {
    event.preventDefault();

    const businessName = document.getElementById("businessName").value.trim();
    const username = document.getElementById("username").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    const message = document.getElementById("registerMessage");
    const button = this.querySelector("button[type='submit']");

    message.textContent = "";
    button.disabled = true;
    button.textContent = "Kaydediliyor...";

    try {
        const response = await fetch("api/register.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                businessName,
                username,
                email,
                password
            })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Kayıt oluşturulamadı.");
        }

        message.textContent = "Kayıt başarılı! Giriş sayfasına yönlendiriliyorsunuz.";

        setTimeout(() => {
            window.location.href = "login.html";
        }, 1500);

    } catch (error) {
        message.textContent = error.message;
    }

    button.disabled = false;
    button.textContent = "Kayıt Ol";
});
