let businessSlug = "";

async function loadBusinessSession() {
    try {
        const response = await fetch("api/session.php");
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Oturum bilgisi alınamadı.");
        }

        businessSlug = result.business.slug;
     if (result.role === "admin") {
    document.getElementById("backToDeveloper").hidden = false;
}
        document.getElementById("businessInfo").textContent =
            result.business.name + " · Yönetim Paneli";

        document.getElementById("restaurantName").value =
            result.business.name;

    } catch (error) {
        alert("İşletme bilgileri alınamadı: " + error.message);
        window.location.href = "login.html";
    }
}
    const defaultCategories = [
        "Kahvaltı",
        "Ana Yemekler",
        "İçecekler"
    ];

    const defaultProducts = [
        {
            id: 1,
            name: "Serpme Kahvaltı",
            category: "Kahvaltı",
            price: 450,
            description: "Peynir, zeytin, yumurta ve kahvaltılıklar.",
            image: "https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?w=300",
            active: true
        },
        {
            id: 2,
            name: "Karışık Omlet",
            category: "Kahvaltı",
            price: 180,
            description: "Kaşar peyniri ve sebzelerle.",
            image: "https://images.unsplash.com/photo-1525351484163-7529414344d8?w=300",
            active: true
        },
        {
            id: 3,
            name: "Özel Salata",
            category: "Ana Yemekler",
            price: 220,
            description: "Taze mevsim sebzeleriyle.",
            image: "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300",
            active: true
        },
        {
            id: 4,
            name: "Çay",
            category: "İçecekler",
            price: 30,
            description: "İnce belli bardakta taze çay.",
            image: "https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=300",
            active: true
        }
    ];

    let categories = JSON.parse(
        localStorage.getItem("qrmenu_categories")
    ) || defaultCategories;

    let products = JSON.parse(
        localStorage.getItem("qrmenu_products")
    ) || defaultProducts;
    let selectedProductImage = "";
    let selectedRestaurantLogo = "";
    document.getElementById("productImageFile").addEventListener("change", function() {
    const file = this.files[0];

    if (!file) return;

    if (file.size > 8 * 1024 * 1024) {
        alert("Fotoğraf en fazla 8 MB olabilir.");
        this.value = "";
        return;
    }

    const reader = new FileReader();

    reader.onload = function(event) {
        const image = new Image();

        image.onload = function() {
            const maxSize = 1000;
            const scale = Math.min(
                1,
                maxSize / Math.max(image.width, image.height)
            );

            const canvas = document.createElement("canvas");

            canvas.width = Math.round(image.width * scale);
            canvas.height = Math.round(image.height * scale);

            const context = canvas.getContext("2d");

            context.fillStyle = "#ffffff";
            context.fillRect(0, 0, canvas.width, canvas.height);

            context.drawImage(
                image,
                0,
                0,
                canvas.width,
                canvas.height
            );

            selectedProductImage = canvas.toDataURL("image/jpeg", 0.82);

            alert("Fotoğraf hazır. Ürünü kaydedebilirsin.");
        };

        image.onerror = function() {
            alert("Fotoğraf okunamadı.");
        };

        image.src = event.target.result;
    };

    reader.readAsDataURL(file);
});
document.getElementById("restaurantLogoFile").addEventListener("change", function() {
    const file = this.files[0];

    if (!file) return;

    if (file.size > 8 * 1024 * 1024) {
        alert("Logo en fazla 8 MB olabilir.");
        this.value = "";
        return;
    }

    const reader = new FileReader();

    reader.onload = function(event) {
        const image = new Image();

        image.onload = function() {
            const maxSize = 600;
            const scale = Math.min(
                1,
                maxSize / Math.max(image.width, image.height)
            );

            const canvas = document.createElement("canvas");

            canvas.width = Math.round(image.width * scale);
            canvas.height = Math.round(image.height * scale);

            const context = canvas.getContext("2d");

            context.drawImage(
                image,
                0,
                0,
                canvas.width,
                canvas.height
            );

            selectedRestaurantLogo =
                canvas.toDataURL("image/png");

            alert("Logo hazır. Bilgileri kaydedebilirsin.");
        };

        image.onerror = function() {
            alert("Logo okunamadı.");
        };

        image.src = event.target.result;
    };

    reader.readAsDataURL(file);
});
    function saveData() {
        localStorage.setItem(
            "qrmenu_categories",
            JSON.stringify(categories)
        );

        localStorage.setItem(
            "qrmenu_products",
            JSON.stringify(products)
        );
    }

    function renderCategories() {
        const list = document.getElementById("categoryList");
        const select = document.getElementById("productCategory");

        list.innerHTML = "";
        select.innerHTML = "";

        categories.forEach((category, index) => {
            const option = document.createElement("option");
            option.value = category;
            option.textContent = category;
            select.appendChild(option);

            const row = document.createElement("div");
            row.style.cssText =
                "display:flex;justify-content:space-between;" +
                "align-items:center;padding:10px 0;border-bottom:1px solid #eee;";

            const name = document.createElement("span");
            name.textContent = category;

            const button = document.createElement("button");
            button.className = "danger";
            button.textContent = "Sil";
            button.onclick = () => deleteCategory(index);

            row.append(name, button);
            list.appendChild(row);
        });
    }

    async function loadCategoriesFromDatabase() {
    try {
        const response = await fetch(
    "api/categories.php?isletme=" + encodeURIComponent(businessSlug)
);
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Kategoriler yüklenemedi.");
        }

        categories = result.categories;
        renderCategories();

    } catch (error) {
        console.error("Kategori yükleme hatası:", error);
        alert("Kategoriler veritabanından yüklenemedi: " + error.message);
    }
}
async function addCategory() {
    const input = document.getElementById("newCategory");
    const name = input.value.trim();

    if (!name) return;

    if (categories.some(c => c.toLowerCase() === name.toLowerCase())) {
        alert("Bu kategori zaten mevcut.");
        return;
    }

    try {
        const response = await fetch("api/categories.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ name })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Kategori eklenemedi.");
        }

        categories.push(name);
        input.value = "";
        renderCategories();

        alert("Kategori eklendi.");

    } catch (error) {
        console.error(error);
        alert("Kategori eklenemedi: " + error.message);
    }
}


    
async function deleteCategory(index) {
    const category = categories[index];

    if (products.some(p => p.category === category)) {
        alert("Bu kategoride ürünler var. Önce ürünleri başka kategoriye taşıyın veya silin.");
        return;
    }

    if (!confirm(category + " kategorisi silinsin mi?")) return;

    try {
        const response = await fetch(
            "api/categories.php?name=" + encodeURIComponent(category),
            { method: "DELETE" }
        );

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Kategori silinemedi.");
        }

        categories.splice(index, 1);
        renderCategories();

        alert("Kategori silindi.");

    } catch (error) {
        console.error(error);
        alert("Kategori silinemedi: " + error.message);
    }
}


    function renderProducts() {
        const list = document.getElementById("productList");
        list.innerHTML = "";

        if (products.length === 0) {
            list.innerHTML = '<div class="empty">Henüz ürün eklenmemiş.</div>';
            return;
        }

        products.forEach(product => {
            const row = document.createElement("div");
            row.className = "product-row" +
                (product.active ? "" : " inactive");

            const imageArea = document.createElement("div");
imageArea.className = "product-image";

if (product.image) {
    const img = document.createElement("img");
    img.src = product.image;
    img.alt = product.name;

    img.onerror = () => {
        img.remove();
        imageArea.textContent = "Resim Yok";
    };

    imageArea.appendChild(img);
} else {
    imageArea.textContent = "Resim Yok";
}
            const info = document.createElement("div");
            info.className = "product-info";

            const name = document.createElement("strong");
            name.textContent = product.name;

            const category = document.createElement("small");
            category.textContent = product.category;

            const price = document.createElement("div");
            price.className = "product-price";
            price.textContent = product.price + " TL";

            info.append(name, category, price);

            const actions = document.createElement("div");
            actions.className = "product-actions";

            const edit = document.createElement("button");
            edit.className = "secondary";
            edit.textContent = "Düzenle";
            edit.onclick = () => editProduct(product.id);

            const toggle = document.createElement("button");
            toggle.className = "secondary";
            toggle.textContent = product.active ? "Pasif yap" : "Aktif yap";
            toggle.onclick = () => toggleProduct(product.id);

            const remove = document.createElement("button");
            remove.className = "danger";
            remove.textContent = "Sil";
            remove.onclick = () => deleteProduct(product.id);

            actions.append(edit, toggle, remove);
            row.append(imageArea, info, actions);
            list.appendChild(row);
        });
    }
    function clearProductImage() {
    selectedProductImage = "";

    document.getElementById("productImage").value = "";
    document.getElementById("productImageFile").value = "";
}
    
async function saveProduct() {
    const id = document.getElementById("editId").value;
    const name = document.getElementById("productName").value.trim();
    const category = document.getElementById("productCategory").value;
    const price = document.getElementById("productPrice").value;
    const description = document.getElementById("productDescription").value.trim();
    const image = document.getElementById("productImage").value.trim();

    if (!name || !category || price === "" || Number(price) < 0) {
        alert("Ürün adı, kategori ve geçerli fiyat girilmelidir.");
        return;
    }

    const data = {
        name,
        category,
        price: Number(price),
        description,
        image: selectedProductImage || image,
        active: true
    };

    try {
        let response;

        if (id) {
            data.id = Number(id);

            response = await fetch("api/products.php", {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });

        } else {
            response = await fetch("api/products.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "İşlem başarısız.");
        }

        const yenileme = await fetch(
    "api/products.php?isletme=" + encodeURIComponent(businessSlug)
);
        const sonuc = await yenileme.json();

        if (!sonuc.success) {
            throw new Error("Ürün listesi yenilenemedi.");
        }

        products = sonuc.products;

        resetForm();
        renderProducts();

        alert(id ? "Ürün güncellendi." : "Yeni ürün eklendi.");

    } catch (error) {
        console.error(error);
        alert("İşlem yapılamadı: " + error.message);
    }
}


    function editProduct(id) {
        const product = products.find(p => p.id === id);

        document.getElementById("editId").value = product.id;
        document.getElementById("productName").value = product.name;
        document.getElementById("productCategory").value = product.category;
        document.getElementById("productPrice").value = product.price;
        document.getElementById("productDescription").value = product.description;
        selectedProductImage = product.image || "";

document.getElementById("productImage").value =
    product.image && !product.image.startsWith("data:")
    ? product.image
    : "";

        document.getElementById("formTitle").textContent = "Ürünü Düzenle";
        document.getElementById("cancelEdit").hidden = false;

        document.getElementById("formTitle").scrollIntoView({
    behavior: "smooth",
    block: "start"
});
    }

    function resetForm() {
        document.getElementById("editId").value = "";
        document.getElementById("productName").value = "";
        document.getElementById("productPrice").value = "";
        document.getElementById("productDescription").value = "";
        document.getElementById("productImage").value = "";

        document.getElementById("formTitle").textContent = "Yeni Ürün Ekle";
        document.getElementById("cancelEdit").hidden = true;
        selectedProductImage = "";
        document.getElementById("productImageFile").value = "";
    }

    
async function toggleProduct(id) {
    const product = products.find(p => p.id === id);
    if (!product) return;

    const yeniDurum = !product.active;

    try {
        const response = await fetch("api/products.php", {
            method: "PUT",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                ...product,
                active: yeniDurum
            })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Durum değiştirilemedi.");
        }

        product.active = yeniDurum;
        renderProducts();

    } catch (error) {
        console.error(error);
        alert("Ürün durumu değiştirilemedi: " + error.message);
    }
}


    
async function deleteProduct(id) {
    if (!confirm("Bu ürün silinsin mi?")) return;

    try {
        const response = await fetch("api/products.php?id=" + id, {
            method: "DELETE"
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Ürün silinemedi.");
        }

        products = products.filter(p => p.id !== id);
        renderProducts();

    } catch (error) {
        console.error(error);
        alert("Ürün silinemedi: " + error.message);
    }
}


    async function saveRestaurant() {
    const name = document.getElementById("restaurantName").value.trim();

    const logo = selectedRestaurantLogo ||
        document.getElementById("restaurantLogo").value.trim();

    if (!name) {
        alert("İşletme adı boş bırakılamaz.");
        return;
    }

    try {
        const response = await fetch("api/restaurant.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ name, logo })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Kayıt yapılamadı.");
        }

        alert("İşletme bilgileri kaydedildi.");

    } catch (error) {
        console.error(error);
        alert("İşletme bilgileri kaydedilemedi: " + error.message);
    }
}
     function restoreDefaultLogo() {
    selectedRestaurantLogo = "";

    document.getElementById("restaurantLogo").value = "";
    document.getElementById("restaurantLogoFile").value = "";

    saveRestaurant();
}
    async function loadRestaurant() {
    try {
        const response = await fetch(
    "api/restaurant.php?isletme=" + encodeURIComponent(businessSlug)
);
        const data = await response.json();

        if (!data.success) {
            throw new Error("İşletme bilgileri alınamadı.");
        }

        document.getElementById("restaurantName").value =
            data.restaurant.name;

        document.getElementById("restaurantLogo").value =
            data.restaurant.logo;

        selectedRestaurantLogo = data.restaurant.logo || "";

    } catch (error) {
        console.error(error);
        alert("İşletme bilgileri yüklenemedi: " + error.message);
    }
}

    function generateMenuQR() {
        const qrArea = document.getElementById("qrCode");
        const downloadButton = document.getElementById("downloadQR");

        // Müşteri menüsünün adresini oluştur
        const menuUrl = new URL("index.html?isletme=" + encodeURIComponent(businessSlug), window.location.href).href;

        // Önceki QR kodunu temizle
        qrArea.innerHTML = "";

        // Yeni QR kodunu oluştur
        new QRCode(qrArea, {
            text: menuUrl,
            width: 220,
            height: 220,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        downloadButton.hidden = false;
    }

    function downloadMenuQR() {
        const qrImage = document.querySelector("#qrCode img");

        if (!qrImage) {
            alert("Önce QR kod oluşturmalısın.");
            return;
        }

        const link = document.createElement("a");
        link.href = qrImage.src;
        link.download = "menu-qr-kodu.png";
        link.click();
    }
   
async function loadProductsFromDatabase() {
    try {
        const response = await fetch(
            "api/products.php?isletme=" + encodeURIComponent(businessSlug)
        );

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || "Ürünler yüklenemedi.");
        }

        products = data.products;
        renderProducts();

    } catch (error) {
        console.error(error);
        alert("Veritabanından ürünler alınamadı: " + error.message);
    }
}

    async function initializeAdmin() {
    await loadBusinessSession();

    if (!businessSlug) return;

    loadCategoriesFromDatabase();
    loadProductsFromDatabase();
    loadRestaurant();
}

initializeAdmin();

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

async function deleteAccount() {
    const password = document.getElementById("deleteAccountPassword").value;

    if (!password) {
        alert("Lütfen mevcut şifrenizi girin.");
        return;
    }

    const confirmed = confirm(
        "İşletmeniz, hesabınız, ürünleriniz ve kategorileriniz kalıcı olarak silinecek. Emin misiniz?"
    );

    if (!confirmed) return;

    try {
        const response = await fetch("api/delete-account.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ password })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Hesap silinemedi.");
        }

        alert(result.message);
        window.location.href = "login.html";

    } catch (error) {
        alert("Silme işlemi başarısız: " + error.message);
    }
}
async function changePassword() {
    const currentPassword =
        document.getElementById("currentPassword").value;

    const newPassword =
        document.getElementById("newPassword").value;

    const confirmPassword =
        document.getElementById("confirmPassword").value;

    if (!currentPassword || !newPassword || !confirmPassword) {
        alert("Lütfen tüm şifre alanlarını doldurun.");
        return;
    }

    if (newPassword !== confirmPassword) {
        alert("Yeni şifreler birbiriyle eşleşmiyor.");
        return;
    }

    if (newPassword.length < 8) {
        alert("Yeni şifre en az 8 karakter olmalıdır.");
        return;
    }

    try {
        const response = await fetch("api/change-password.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                currentPassword,
                newPassword
            })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || "Şifre değiştirilemedi.");
        }

        alert("Şifreniz başarıyla değiştirildi.");

        document.getElementById("currentPassword").value = "";
        document.getElementById("newPassword").value = "";
        document.getElementById("confirmPassword").value = "";

    } catch (error) {
        alert("Şifre değiştirme başarısız: " + error.message);
    }
}