# 🚀 Build Guide - Dev & Production

## 📋 Dostępne komendy NPM

### Development (watch mode)
```bash
npm run dev          # Tailwind CSS watch (auto-rebuild)
npm run watch        # Alias dla 'npm run dev'
```

### Production (minified)
```bash
npm run build        # Build CSS + copy to public/
npm run build:prod   # Build CSS + Asset Mapper compile
```

---

## 🔧 Workflow Development

### Opcja 1: Dwa terminale (ZALECANE)

**Terminal 1 - CSS Watch:**
```bash
npm run dev
# Tailwind CSS będzie auto-rebuild przy zmianach
```

**Terminal 2 - Symfony Server:**
```bash
symfony server:start
# lub
php -S localhost:8000 -t public/
```

### Opcja 2: Jeden terminal z tmux/screen
```bash
# Start CSS watch w tle
npm run dev &

# Start Symfony server
symfony server:start
```

---

## 🚀 Workflow Production

### Pełny build produkcyjny:
```bash
# 1. Build CSS + Asset Mapper
npm run build:prod

# 2. Optimize Composer
composer dump-autoload --optimize --no-dev

# 3. Clear & warm cache
APP_ENV=prod bin/console cache:clear
APP_ENV=prod bin/console cache:warmup

# 4. Start server
APP_ENV=prod APP_DEBUG=0 symfony server:start --port=8000 --no-tls -d
```

### Szybki build (tylko CSS):
```bash
npm run build
```

---

## ⚙️ Asset Mapper Integration

### Struktura plików:
```
assets/styles/app.css       → Źródło Tailwind
assets/styles/output.css    → Skompilowany CSS
public/styles/output.css    → Serwowany przez web server
public/assets/              → Asset Mapper compiled files
```

### W templates używaj:
```twig
{# CSS z public/styles/ #}
<link rel="stylesheet" href="{{ asset('styles/output.css') }}">

{# JS przez Asset Mapper #}
{{ importmap() }}
```

---

## 🎯 Quick Reference

| Komenda | Cel | Użycie |
|---------|-----|--------|
| `npm run dev` | Watch CSS (auto) | Development |
| `npm run build` | Build CSS (minify) | Pre-deployment |
| `npm run build:prod` | Full prod build | Production |
| `npm run watch` | Alias dla dev | Development |

---

## 💡 Wskazówki

1. **Development**: Zawsze używaj `npm run dev` w osobnym terminalu
2. **Production**: Zawsze używaj `npm run build:prod` przed deploymentem
3. **Asset Mapper**: Automatycznie wersjonuje pliki JS
4. **Tailwind**: Auto-purge nieużywanych klas w production

---

## 🐛 Troubleshooting

### CSS nie ładuje się w produkcji:
```bash
npm run build
cp assets/styles/output.css public/styles/output.css
```

### 404 na JS assets:
```bash
APP_ENV=prod php bin/console asset-map:compile
```

### Cache problems:
```bash
rm -rf var/cache/*
bin/console cache:clear
```
