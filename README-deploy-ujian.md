# Deploy Backend Ujian Praktik (Laravel) di VPS

Runbook untuk VPS **4 vCPU / 8 GB RAM / 100 GB, masih kosong**, target domain
`ujian-uts.khalidperwira.com`.

---

## 1. Arsitektur yang dipakai

```
            Internet
               │  :80 / :443
        ┌──────▼───────┐
        │  Caddy (edge)│  HTTPS otomatis, satu-satunya yang buka port
        └──────┬───────┘
               │ network "edge"
        ┌──────▼───────────────────────────┐
        │  app  — Laravel + FrankenPHP     │  :8080 (internal saja)
        └──────┬───────────────────────────┘
               │ network "internal" (tertutup dari internet)
        ┌──────▼──────┐   ┌──────────┐
        │ MySQL 8.4   │   │ Redis 7  │
        └─────────────┘   └──────────┘
```

**Kenapa begini:**

| Pilihan | Alasan |
|---|---|
| **Caddy terpisah sebagai edge** | HTTPS + perpanjangan sertifikat otomatis, nol konfigurasi. Aplikasi berikutnya tinggal ikut network `edge` dan tambah 3 baris di `Caddyfile` — persis pola subdomain yang kamu rencanakan. |
| **FrankenPHP** (bukan nginx + php-fpm) | Satu container, tidak perlu sinkron volume `public/` antara nginx dan fpm. Sudah didukung resmi Laravel dan bisa dinaikkan ke Octane kapan saja tanpa ganti image. |
| **MySQL 8.4 LTS** | Familiar untuk siswa, dan kontrak API di repo `SolusiUTS` tinggal jalan apa adanya. |
| **Redis** | Cache + session + queue. Ringan (256 MB dibatasi). |
| **DB & Redis tanpa `ports:`** | Tidak bisa diakses dari internet sama sekali. Ini kesalahan deploy paling umum di VPS. |

Struktur folder di VPS:

```
/srv/edge/          docker-compose.yml + Caddyfile   (reverse proxy, 1x saja)
/srv/ujian-api/     repo Laravel + Dockerfile + compose + .env
/srv/backups/       dump database harian
/srv/scripts/       backup-db.sh
```

---

## 2. Menyiapkan VPS kosong (sekali saja, ±20 menit)

### 2.1 Login & user non-root

```bash
ssh root@IP_VPS

adduser khalid
usermod -aG sudo khalid
rsync --archive --chown=khalid:khalid ~/.ssh /home/khalid
```

### 2.2 Kunci SSH & firewall

```bash
# /etc/ssh/sshd_config
PermitRootLogin no
PasswordAuthentication no
```

```bash
systemctl restart ssh

apt update && apt install -y ufw fail2ban
ufw default deny incoming
ufw default allow outgoing
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
systemctl enable --now fail2ban
```

> **Catatan penting:** Docker menembus aturan UFW untuk port yang di-`publish`.
> Karena itu di stack ini **hanya Caddy** yang punya `ports:`. Jangan pernah
> menambah `ports: 3306:3306` di service `db`.

### 2.3 Update otomatis

```bash
apt install -y unattended-upgrades
dpkg-reconfigure -plow unattended-upgrades
```

### 2.4 Docker

```bash
curl -fsSL https://get.docker.com | sh
usermod -aG docker khalid
docker network create edge      # network bersama antar-aplikasi
```

Logout–login lagi supaya grup `docker` aktif.

---

## 3. DNS

Di panel domain `khalidperwira.com`, tambahkan:

| Type | Name | Value |
|---|---|---|
| A | `ujian-uts` | `IP_VPS` |

Tunggu propagasi, cek dengan `dig +short ujian-uts.khalidperwira.com`.
**Jangan** nyalakan proxy Cloudflare (awan oranye) saat pertama kali — Caddy
butuh akses langsung untuk verifikasi Let's Encrypt.

---

## 4. Menyalakan reverse proxy

```bash
mkdir -p /srv/edge && cd /srv/edge
# salin docker-compose.yml dan Caddyfile dari bundel ini
echo "ACME_EMAIL=email-kamu@contoh.com" > .env
docker compose up -d
docker compose logs -f caddy      # pastikan sertifikat terbit
```

---

## 5. Deploy aplikasi Laravel

```bash
cd /srv
git clone <URL-REPO-BACKEND> ujian-api
cd ujian-api

# salin Dockerfile, docker-compose.yml, docker/, deploy.sh dari bundel ini
cp .env.production.example .env
nano .env                      # isi password DB (pakai password acak panjang)

# generate APP_KEY
docker compose build
docker compose run --rm app php artisan key:generate --show
# tempel hasilnya ke APP_KEY di .env

docker compose up -d
docker compose logs -f app
```

Cek:

```bash
curl -I https://ujian-uts.khalidperwira.com/up      # harus 200
```

Migrasi jalan otomatis di entrypoint. Kalau perlu seed data awal:

```bash
docker compose exec app php artisan db:seed --force
```

---

## 6. Penyesuaian kecil di kode Laravel

### 6.1 Percaya reverse proxy (wajib)

`bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*');
})
```

Tanpa ini, `url()` akan menghasilkan `http://` dan `$request->ip()` berisi IP
container Caddy, bukan IP siswa.

### 6.2 Rate limit — hati-hati soal NAT sekolah

Semua siswa di lab keluar lewat **satu IP publik sekolah**. Throttle bawaan
Laravel (`60 per menit per IP`) akan langsung memblokir satu kelas.
Batasi per token siswa, bukan per IP:

```php
// AppServiceProvider::boot()
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(600)->by(
        $request->bearerToken() ?: $request->ip()
    );
});
```

### 6.3 Log ke stderr

`.env` sudah berisi `LOG_CHANNEL=stderr`, jadi log terbaca lewat
`docker compose logs app` dan otomatis dirotasi Docker. Tidak ada file log yang
membengkak di volume.

### 6.4 Isolasi data antar siswa

36 siswa memukul endpoint CRUD yang sama. Kalau tidak dipisah, siswa A akan
menghapus data siswa B dan nilainya kacau. Dua cara termudah:

1. **Token per siswa** — tabel `students(nis, token)`, middleware mengambil
   `student_id` dari bearer token, semua query di-scope
   `where('student_id', ...)`. Ini yang paling rapi dan sekalian mengajarkan
   konsep autentikasi API.
2. **Prefix path** — `/api/{nis}/produk`, divalidasi middleware.

Sediakan juga perintah reset supaya siswa bisa mengulang dari awal:

```bash
php artisan ujian:reset {nis}
```

---

## 7. Operasional harian

| Kebutuhan | Perintah |
|---|---|
| Lihat log realtime | `docker compose logs -f app` |
| Restart aplikasi | `docker compose restart app` |
| Update kode | `./deploy.sh` |
| Masuk shell container | `docker compose exec app bash` |
| Artisan | `docker compose exec app php artisan <perintah>` |
| Backup manual | `/srv/scripts/backup-db.sh` |
| Restore | `gunzip < backup.sql.gz \| docker compose exec -T db mysql -uroot -p<pass> ujian_api` |
| Akses DB dari laptop | `ssh -L 3306:127.0.0.1:3306 khalid@IP_VPS` lalu buka DBeaver ke `localhost:3306` |

Backup otomatis (crontab root):

```
0 2 * * * /srv/scripts/backup-db.sh >> /var/log/backup-db.log 2>&1
```

---

## 8. Checklist H-1 dan hari ujian

**H-1**

- [ ] `curl https://ujian-uts.khalidperwira.com/up` → 200
- [ ] Uji semua endpoint 4 paket soal pakai file `docs/openapi.json` (Postman/Bruno)
- [ ] Data 36 siswa (NIS + token) sudah di-seed dan tokennya dicetak
- [ ] Backup + **snapshot VPS** dari panel provider (rollback tercepat kalau kacau)
- [ ] `APP_DEBUG=false` sudah pasti
- [ ] Sertifikat HTTPS valid minimal 30 hari lagi: `docker compose exec caddy caddy list-certificates`
- [ ] Simulasi beban ringan: `ab -n 500 -c 36 https://.../api/produk`
- [ ] Fallback `localhost:8000` (starter project) tetap dites dan tetap ditulis di naskah soal

**Hari-H**

- [ ] Buka `docker compose logs -f app` di satu terminal, biarkan berjalan
- [ ] Kalau internet sekolah putus → siswa pindah ke base URL `localhost:8000`, tidak perlu panik
- [ ] Setelah ujian: `backup-db.sh` langsung, sebelum ada yang mengubah data
- [ ] Ekspor data per siswa sebagai bukti penilaian

---

## 9. Kapasitas

Beban ujian ini (36 siswa, request CRUD sesekali dari aplikasi desktop) berada
di bawah 5 request/detik. VPS 4 vCPU / 8 GB **sangat berlebih** — itu bagus,
artinya tidak ada risiko kehabisan sumber daya saat ujian. Alokasi aman:

| Komponen | RAM |
|---|---|
| MySQL (buffer pool 1 GB) | ~1,5 GB |
| FrankenPHP | ~300 MB |
| Redis | ≤256 MB |
| Caddy | ~50 MB |
| **Sisa untuk aplikasi lain** | **±5,5 GB** |

Octane belum perlu. Kalau nanti dibutuhkan, tinggal ganti `CMD` di Dockerfile
menjadi `php artisan octane:frankenphp --workers=4` — image-nya sudah siap.

---

## 10. Langkah berikutnya (opsional)

- **CI/CD**: GitHub Actions build image → push ke GHCR → VPS `docker compose pull && up -d`. Menghilangkan proses build di VPS.
- **Monitoring**: Uptime Kuma (container ringan) memantau `/up` dan mengirim notifikasi Telegram.
- **Staging**: subdomain `ujian-dev.khalidperwira.com` dengan stack yang sama, `.env` berbeda, untuk uji soal sebelum dipakai siswa.
