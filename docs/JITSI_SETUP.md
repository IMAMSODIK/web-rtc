# Jitsi Server Configuration Guide

Aplikasi ini mendukung 3 jenis server Jitsi dengan sistem **fallback otomatis**:

```
Priority: JaaS (Primary) → Self-hosted (Fallback) → Public (Development)
```

---

## 🔧 Konfigurasi di `public/js/video-call.js`

Edit bagian `JITSI_SERVERS` di awal file:

```javascript
const JITSI_SERVERS = {
    jaas: {
        domain: '8x8.vc',
        appId: 'vpaas-magic-cookie-xxxxx', // Ganti dengan AppID kamu
        enabled: true // Set true setelah dapat AppID
    },
    selfHosted: {
        domain: 'jitsi.yourdomain.com', // Ganti dengan domain server kamu
        enabled: true // Set true setelah setup server
    },
    public: {
        domain: 'meet.jit.si',
        enabled: true
    }
};
```

---

## ☁️ OPSI 1: JaaS (Jitsi as a Service) - RECOMMENDED

### Kelebihan:
- ✅ Gratis 25 participant-minutes/bulan
- ✅ Tidak perlu manage server
- ✅ Reliable & fast
- ✅ No login required untuk participants

### Cara Setup:

#### 1. Daftar di JaaS
1. Buka https://jaas.8x8.vc/
2. Klik **"Start Building Free"**
3. Daftar dengan email (atau Google/GitHub)
4. Verify email

#### 2. Buat Application
1. Setelah login, masuk ke Dashboard
2. Klik **"Create New App"** atau gunakan default app
3. Copy **App ID** (format: `vpaas-magic-cookie-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`)

#### 3. Update Code
Edit `public/js/video-call.js`:
```javascript
jaas: {
    domain: '8x8.vc',
    appId: 'vpaas-magic-cookie-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', // Paste AppID disini
    enabled: true
},
```

#### 4. (Optional) Setup JWT untuk keamanan
Untuk production, setup JWT authentication di JaaS Dashboard.

### Pricing:
| Plan | Price | Minutes |
|------|-------|---------|
| Free | $0 | 25 participant-minutes/month |
| Developer | $9.99/month | 5,000 minutes |
| Business | Custom | Unlimited |

---

## 🖥️ OPSI 2: Self-Hosted Jitsi Server

### Kelebihan:
- ✅ Unlimited usage
- ✅ Full control & privacy
- ✅ No time limits
- ✅ Customizable

### Requirements:
- VPS dengan min 4GB RAM, 2 CPU cores
- Ubuntu 22.04 LTS
- Domain dengan SSL certificate
- Port 80, 443, 10000/UDP terbuka

### Cara Setup di Ubuntu 22.04:

#### 1. Siapkan VPS
- Beli VPS di DigitalOcean, Vultr, Linode, dll (~$10-20/bulan)
- Pilih Ubuntu 22.04 LTS
- Set hostname: `jitsi.yourdomain.com`

#### 2. Setup Domain
- Buat A record di DNS:
  ```
  jitsi.yourdomain.com → IP_VPS_KAMU
  ```

#### 3. Install Jitsi (SSH ke server)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Set hostname
sudo hostnamectl set-hostname jitsi.yourdomain.com

# Add to /etc/hosts
echo "127.0.0.1 jitsi.yourdomain.com" | sudo tee -a /etc/hosts

# Install Jitsi repository
curl https://download.jitsi.org/jitsi-key.gpg.key | sudo sh -c 'gpg --dearmor > /usr/share/keyrings/jitsi-keyring.gpg'
echo 'deb [signed-by=/usr/share/keyrings/jitsi-keyring.gpg] https://download.jitsi.org stable/' | sudo tee /etc/apt/sources.list.d/jitsi-stable.list > /dev/null

# Update and install
sudo apt update
sudo apt install -y jitsi-meet

# During installation:
# - Enter hostname: jitsi.yourdomain.com
# - Choose: "Generate a new self-signed certificate"

# Setup Let's Encrypt SSL
sudo /usr/share/jitsi-meet/scripts/install-letsencrypt-cert.sh
```

#### 4. Configure Jitsi (Optional tapi recommended)

Edit `/etc/jitsi/meet/jitsi.yourdomain.com-config.js`:
```javascript
// Disable prejoin page
prejoinPageEnabled: false,

// Allow guests (no login required)
enableUserRolesBasedOnToken: false,
```

Edit `/etc/prosody/conf.avail/jitsi.yourdomain.com.cfg.lua`:
```lua
-- Allow anonymous access
authentication = "anonymous"
```

Restart services:
```bash
sudo systemctl restart prosody
sudo systemctl restart jicofo
sudo systemctl restart jitsi-videobridge2
```

#### 5. Update Code
Edit `public/js/video-call.js`:
```javascript
selfHosted: {
    domain: 'jitsi.yourdomain.com', // Domain server kamu
    enabled: true
},
```

### Firewall Rules:
```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 10000/udp
sudo ufw allow 22/tcp
sudo ufw enable
```

---

## 🌐 OPSI 3: Public Servers (Development Only)

### ⚠️ WARNING: 
Public servers sekarang punya batasan:
- `meet.jit.si` - Butuh login
- `jitsi.member.fsf.org` - Limit 5 menit
- `meet.element.io` - Limit 5 menit

**Hanya gunakan untuk development/testing!**

---

## 📊 Rekomendasi Berdasarkan Use Case

| Use Case | Recommendation |
|----------|----------------|
| Development/Testing | Public (meet.jit.si dengan login) |
| Small scale (< 25 min/month) | JaaS Free Tier |
| Medium scale | JaaS Developer ($9.99/month) |
| Large scale / Privacy critical | Self-hosted |
| Enterprise | JaaS Business / Self-hosted cluster |

---

## 🔄 Cara Kerja Fallback System

```
1. User mulai video call
   ↓
2. Coba connect ke JaaS (primary)
   ↓
3. Jika gagal/quota habis → Switch ke Self-hosted
   ↓
4. Jika gagal → Switch ke Public server
   ↓
5. Jika semua gagal → Show error message
```

Fallback otomatis terjadi jika:
- Connection timeout (30 detik)
- Quota/limit exceeded error
- Server error

---

## 🔐 Security Tips

1. **JaaS**: Enable JWT authentication di dashboard
2. **Self-hosted**: 
   - Setup authentication untuk moderator
   - Enable lobby feature
   - Use secure passwords
3. **Semua**: Gunakan unique room names (sudah di-handle oleh app)

---

## 📞 Support

Jika ada masalah:
1. Check browser console untuk error
2. Pastikan room name sama di kedua sisi (guru & murid)
3. Test dengan dua browser berbeda
4. Check firewall tidak block WebRTC ports
