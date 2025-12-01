# 🚀 Symfony CLI Build Commands

## 📋 Nowa komenda: `assets:build`

Zintegrowany build CSS bezpośrednio z Symfony!

---

## 🎯 Podstawowe użycie

### Development (build raz)
```bash
bin/console assets:build
```

### Development (watch mode - auto-rebuild)
```bash
bin/console assets:build --watch
# lub
bin/console assets:build -w
```

### Production (minified + Asset Mapper)
```bash
bin/console assets:build --prod
# lub
bin/console assets:build -p
```

---

## 🔧 Wszystkie opcje

```bash
bin/console assets:build [opcje]

Opcje:
  -w, --watch      Watch mode (auto-rebuild przy zmianach)
  -p, --prod       Production build (minified + asset-map compile)
      --no-copy    Pomiń kopiowanie do public/
  -h, --help       Pomoc
```

---

## 🚀 Workflow Symfony

### Development z watch mode

**Terminal 1 - CSS Watch:**
```bash
bin/console assets:build --watch
```

**Terminal 2 - Symfony Server:**
```bash
symfony server:start
```

### Quick Development Build
```bash
# Build CSS + copy to public
bin/console assets:build

# Clear cache (opcjonalnie)
bin/console cache:clear
```

### Production Deployment
```bash
# 1. Build CSS + Asset Mapper
bin/console assets:build --prod

# 2. Optimize & cache
composer dump-autoload --optimize --no-dev
APP_ENV=prod bin/console cache:clear
APP_ENV=prod bin/console cache:warmup

# 3. Start production server
APP_ENV=prod symfony server:start -d
```

---

## 📊 Output przykładowy

### Development Build:
```
🎨 Building CSS Assets
======================

Building Tailwind CSS...
------------------------

Rebuilding...
Done in 335ms.

✅ Tailwind CSS built successfully

Copying CSS to public/styles/...
--------------------------------

✅ CSS copied to public/styles/output.css

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ Build completed successfully!
📦 Mode: Development
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

### Production Build:
```
🎨 Building CSS Assets
======================

Building Tailwind CSS...
------------------------

Done in 398ms.

✅ Tailwind CSS built successfully

Copying CSS to public/styles/...
--------------------------------

✅ CSS copied to public/styles/output.css

Compiling Asset Mapper...
-------------------------

// Compiled 14 assets
// Manifest written to public/assets/manifest.json

✅ Asset Mapper compiled successfully

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ Build completed successfully!
📦 Mode: Production (minified)
🚀 Ready for deployment!
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## 🎯 Quick Reference

| Komenda | Co robi | Kiedy |
|---------|---------|-------|
| `bin/console assets:build` | Build CSS dev | Development |
| `bin/console assets:build -w` | Watch mode | Development |
| `bin/console assets:build -p` | Prod build | Deployment |
| `bin/console assets:build --no-copy` | Build bez copy | Testing |

---

## 💡 Kombinacje z innymi komendami

### Full dev setup:
```bash
# Terminal 1
bin/console assets:build -w

# Terminal 2
symfony server:start
symfony server:log
```

### Quick rebuild all:
```bash
bin/console assets:build && bin/console cache:clear
```

### Production pipeline:
```bash
bin/console assets:build -p && \
composer dump-autoload --optimize --no-dev && \
APP_ENV=prod bin/console cache:warmup
```

---

## 🆚 NPM vs Symfony

| NPM | Symfony | Różnica |
|-----|---------|---------|
| `npm run dev` | `bin/console assets:build -w` | To samo |
| `npm run build` | `bin/console assets:build` | To samo |
| `npm run build:prod` | `bin/console assets:build -p` | To samo |

**Teraz możesz używać któregokolwiek!** 🎉

---

## 🐛 Troubleshooting

### Komenda nie działa:
```bash
bin/console cache:clear
composer dump-autoload
```

### CSS się nie zmienia:
```bash
bin/console assets:build --no-cache
rm -rf var/cache/*
```

### Production 404 na assets:
```bash
bin/console assets:build -p
chmod -R 755 public/assets
```
