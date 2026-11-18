# Menuver

**QR Kod Destekli Dijital Menü Yönetim Sistemi**

Menuver, işletmelerin kendi dijital menülerini oluşturmasını ve yönetmesini sağlayan, PHP ve MySQL tabanlı bir web uygulamasıdır.

Müşteriler menüye QR kod veya bağlantı üzerinden ulaşabilir. İşletme yöneticileri ise yönetim panelinden menülerini düzenleyebilir.

## Özellikler

- İşletme kaydı ve kullanıcı girişi
- İşletmelere özel dijital menü sayfaları
- Kategori oluşturma ve yönetme
- Ürün ekleme, düzenleme ve silme
- İşletme adı ve logo yönetimi
- QR kod ile menüye erişim
- İşletme yönetim paneli
- Geliştirici yönetim paneli
- İşletme seçerek yönetim işlemlerini gerçekleştirme
- Şifre değiştirme ve hesap silme

## Kullanılan Teknolojiler

- HTML5
- CSS3
- JavaScript
- PHP
- MySQL

## Yerel Kurulum

Projeyi kendi bilgisayarınızda çalıştırmak için:

1. XAMPP kurun ve Apache ile MySQL servislerini başlatın.
2. Proje klasörünü XAMPP içerisindeki `htdocs` klasörüne kopyalayın.
3. phpMyAdmin üzerinden `qrmenu` adında bir veritabanı oluşturun.
4. `database` klasöründeki SQL yedek dosyasını içe aktarın.
5. `api/config.php` dosyasındaki veritabanı bağlantı ayarlarını kendi ortamınıza göre kontrol edin.
6. Tarayıcıdan aşağıdaki adresi açın:

   `http://localhost/QRMenu/`

## Proje Hakkında

Menuver, bağımsız olarak geliştirilmiş bir dijital menü projesidir.

Proje şu anda yerel geliştirme ortamında çalışacak şekilde hazırlanmıştır. Canlı yayın bağlantısı ve hosting bilgileri ileride eklenecektir.

---

**Geliştiren:** Enis Baydemir
