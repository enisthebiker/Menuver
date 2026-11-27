
        // Yönetim panelinde kullanılan varsayılan veriler
const businessSlug = new URLSearchParams(window.location.search).get("isletme")
    || new URLSearchParams(window.location.search).get("i");
             
        const defaultCategories = [
            "Kahvaltı",
            "Ana Yemekler",
            "İçecekler",
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

        // Yönetim panelindeki kayıtları oku

        let categories = [];

        let products = [];
        let restaurant = {
    name: "Demo Cafe",
    logo: ""
};

        let selectedCategory = "all";

        // İşletme bilgilerini göster

        function renderRestaurant() {
            const name = restaurant.name || "Demo Cafe";
document.title = name;
            document.getElementById("restaurantName").textContent = name;
            document.getElementById("footerText").textContent =
                name + " · Afiyet olsun";

            if (restaurant.logo) {
                const logo = document.getElementById("restaurantLogo");
                const image = document.createElement("img");

                image.src = restaurant.logo;
                image.alt = name;

                image.onerror = () => {
                    logo.textContent = "☕";
                };

                logo.textContent = "";
                logo.appendChild(image);
            }
        }
       async function loadRestaurantFromDatabase() {
    try {
        const response = await fetch(
    "api/restaurant.php?isletme=" + encodeURIComponent(businessSlug)
);
        const data = await response.json();

        if (!data.success) {
            throw new Error("İşletme bilgileri alınamadı.");
        }

        restaurant = data.restaurant;
        renderRestaurant();

    } catch (error) {
        console.error("İşletme bilgileri yüklenemedi:", error);
    }
}
        // Kategori düğmelerini oluştur

        function renderCategories() {
            const nav = document.getElementById("categoryNav");
            nav.innerHTML = "";

            const allButton = document.createElement("button");
            allButton.textContent = "Tümü";
            allButton.className = "active";
            allButton.onclick = () => showCategory("all", allButton);

            nav.appendChild(allButton);

            categories.forEach(category => {
                const button = document.createElement("button");

                button.textContent = category;
                button.onclick = () => showCategory(category, button);

                nav.appendChild(button);
            });
        }

        // Ürünleri oluştur

        // Ürünleri oluştur

function renderProducts() {
    const list = document.getElementById("productList");
    list.innerHTML = "";

    const activeProducts = products.filter(product =>
        product.active !== false &&
        (selectedCategory === "all" ||
         product.category === selectedCategory)
    );

    if (activeProducts.length === 0) {
        list.innerHTML =
            '<div class="empty">Bu kategoride henüz ürün bulunmuyor.</div>';
        return;
    }

    // Tümü seçiliyse ürünleri kategori başlıkları altında göster
    if (selectedCategory === "all") {

        categories.forEach(category => {
            const categoryProducts = activeProducts.filter(
                product => product.category === category
            );

            // Kategoride ürün yoksa başlık da gösterme
            if (categoryProducts.length === 0) return;

            const heading = document.createElement("h2");
            heading.className = "category-heading";
            heading.textContent = category;
            list.appendChild(heading);

            categoryProducts.forEach(product => {
                createProductCard(product, list);
            });
        });

        // Herhangi bir kategoriye bağlı olmayan ürünleri de göster
        const uncategorizedProducts = activeProducts.filter(
            product => !categories.includes(product.category)
        );

        uncategorizedProducts.forEach(product => {
            createProductCard(product, list);
        });

        return;
    }

    // Belirli bir kategori seçiliyse eski davranış devam eder
    activeProducts.forEach(product => {
        createProductCard(product, list);
    });
}


// Ürün kartını oluştur
function createProductCard(product, list) {
    const card = document.createElement("section");
    card.className = "product";

    const imageArea = document.createElement("div");
    imageArea.className = "product-image";

    if (product.image) {
        const image = document.createElement("img");

        image.src = product.image;
        image.alt = product.name;

        image.onerror = () => {
            image.remove();
            imageArea.textContent = "Resim Yok";
        };

        imageArea.appendChild(image);
    } else {
        imageArea.textContent = "Resim Yok";
    }

    card.appendChild(imageArea);

    const info = document.createElement("div");
    info.className = "product-info";

    const name = document.createElement("h3");
    name.textContent = product.name;

    const description = document.createElement("p");
    description.textContent = product.description || "";

    const price = document.createElement("div");
    price.className = "price";
    price.textContent =
        Number(product.price).toLocaleString("tr-TR") + " TL";

    info.append(name, description, price);
    card.appendChild(info);

    list.appendChild(card);
}

        // Kategori filtresi

        function showCategory(category, button) {
            selectedCategory = category;

            document.querySelectorAll("nav button").forEach(btn => {
                btn.classList.remove("active");
            });

            button.classList.add("active");

            renderProducts();
        }
     async function loadCategoriesFromDatabase() {
    try {
        const response = await fetch(
            "api/categories.php?isletme=" + encodeURIComponent(businessSlug)
        );

        const data = await response.json();

        if (!data.success) {
            throw new Error("Kategoriler alınamadı.");
        }

        categories = data.categories;

        renderCategories();
        renderProducts();

    } catch (error) {
        console.error("Kategori yükleme hatası:", error);
        document.getElementById("categoryNav").innerHTML =
            '<div class="empty">Kategoriler yüklenemiyor.</div>';
    }
}
async function loadProductsFromDatabase() {
    try {
        const response = await fetch(
    "api/products.php?isletme=" + encodeURIComponent(businessSlug)
);
        const data = await response.json();

        if (!data.success) {
            throw new Error("Ürünler alınamadı.");
        }

        products = data.products;
        renderProducts();

    } catch (error) {
        console.error(error);
        document.getElementById("productList").innerHTML =
            '<div class="empty">Menü şu anda yüklenemiyor.</div>';
    }
}

        // Sayfayı başlat

       loadRestaurantFromDatabase();
loadCategoriesFromDatabase();
loadProductsFromDatabase();
