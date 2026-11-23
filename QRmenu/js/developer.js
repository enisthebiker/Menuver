
async function loadBusinesses() {
    const businessList = document.getElementById("businessList");

    try {
        const response = await fetch("api/admin-businesses.php");
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "İşletmeler yüklenemedi.");
        }

        businessList.innerHTML = "";

        if (result.businesses.length === 0) {
            businessList.textContent = "Henüz kayıtlı işletme yok.";
            businessList.className = "empty";
            return;
        }

        result.businesses.forEach(business => {
            const row = document.createElement("div");
            row.className = "product-row";

            const info = document.createElement("div");
            info.className = "product-info";

            const name = document.createElement("strong");
            name.textContent = business.name;

            const details = document.createElement("small");
            details.textContent =
                "ID: " + business.id +
                " · Adres: " + business.slug +
                " · Kullanıcı: " + business.user_count;

           info.appendChild(name);
info.appendChild(details);
row.appendChild(info);
// İşletmeyi yönet butonu
const manageButton = document.createElement("button");
manageButton.className = "secondary";
manageButton.textContent = "Yönet";

manageButton.addEventListener("click", async function() {

    try {
        const response = await fetch("api/select-business.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id: business.id
            })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "İşletme seçilemedi.");
        }

        window.location.href = "admin.html";

    } catch (error) {
        alert("İşletme açılamadı: " + error.message);
    }
});

row.appendChild(manageButton);
// İşletme silme butonu
const deleteButton = document.createElement("button");
deleteButton.className = "danger";
deleteButton.textContent = "İşletmeyi Sil";

deleteButton.addEventListener("click", async function() {

    const confirmed = confirm(
        business.name + " işletmesini ve bütün menü verilerini silmek istediğine emin misin?"
    );

    if (!confirmed) return;

    try {
        const response = await fetch("api/delete-business.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id: business.id
            })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "İşletme silinemedi.");
        }

        alert(result.message);
        loadBusinesses();

    } catch (error) {
        alert("Silme işlemi başarısız: " + error.message);
    }
});

row.appendChild(deleteButton);
businessList.appendChild(row);
        });
    } catch (error) {
        businessList.textContent = error.message;
        businessList.className = "empty";

        if (error.message === "Bu işlem için yetkiniz yok." ||
            error.message === "Oturum açmanız gerekiyor.") {
            window.location.href = "login.html";
        }
    }
}


async function logout() {
    try {
        const response = await fetch("api/logout.php");
        const result = await response.json();

        if (!result.success) {
            throw new Error("Oturum kapatılamadı.");
        }

        window.location.href = "login.html";

    } catch (error) {
        alert("Çıkış yapılırken hata oluştu: " + error.message);
    }
}


loadBusinesses();
// İşletme kayıt ayarı
let registrationEnabled = false;

async function loadRegistrationSetting() {
    const status = document.getElementById("registrationStatus");
    const button = document.getElementById("toggleRegistrationButton");

    try {
        const response = await fetch("api/registration-settings.php");
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Ayar okunamadı.");
        }

        registrationEnabled = result.enabled;

        updateRegistrationDisplay();

        button.disabled = false;

    } catch (error) {
        status.textContent = "Kayıt ayarı yüklenemedi.";
        button.textContent = "Tekrar Dene";
        button.disabled = false;
    }
}

function updateRegistrationDisplay() {
    const status = document.getElementById("registrationStatus");
    const button = document.getElementById("toggleRegistrationButton");

    if (registrationEnabled) {
        status.textContent = "İşletme kayıtları açık.";
        button.textContent = "Kayıtları Kapat";
    } else {
        status.textContent = "İşletme kayıtları kapalı.";
        button.textContent = "Kayıtları Aç";
    }
}

async function toggleRegistration() {
    const button = document.getElementById("toggleRegistrationButton");

    button.disabled = true;

    try {
        const response = await fetch("api/registration-settings.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                enabled: !registrationEnabled
            })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Ayar değiştirilemedi.");
        }

        registrationEnabled = result.enabled;

        updateRegistrationDisplay();

    } catch (error) {
        alert("Kayıt ayarı değiştirilemedi: " + error.message);
    }

    button.disabled = false;
}

document
    .getElementById("toggleRegistrationButton")
    .addEventListener("click", toggleRegistration);

loadRegistrationSetting();
