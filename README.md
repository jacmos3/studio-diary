# Diary Studio

Repo separato per authoring, processing media ed export di nuovi diari custom.

## Obiettivo

Questo repo non contiene il diario pubblico finale. Contiene lo Studio editoriale che:

- gestisce progetti, giorni, testi e persone
- carica e processa media
- esporta un pacchetto runtime file-based
- prepara i dati che un renderer esterno puo leggere

## Avvio locale

```bash
cd /Volumes/HardDisk/diary-studio
STUDIO_ADMIN_PASSWORD=studio-dev php -S 127.0.0.1:4184 -t .
```

Apri `http://127.0.0.1:4184/`

## Configurazione

Variabili utili:

- `STUDIO_ADMIN_PASSWORD`
- `STUDIO_REVERSE_GEOCODE_TEMPLATE`

## Storage locale

- `storage/studio.sqlite`
- `storage/uploads/`
- `storage/processed/`
- `exported/<project-slug>/`

## Export

Ogni progetto puo essere esportato come pacchetto runtime:

- `project.json`
- `entries.<lang>.json`
- `manifest.json`
- `media/...`

Esempio CLI:

```bash
php worker/export-project.php 1
```

## Stato attuale

Il repo e separato dal diario personale e la preview usa una snapshot locale del renderer sotto `renderer/`. Il coupling residuo non e piu runtime, ma solo di manutenzione: quando cambia il renderer vero, questa snapshot va riallineata.

## Sync renderer

Per riallineare la snapshot locale del renderer senza farlo a mano:

```bash
python3 scripts/sync-renderer.py '/percorso/al/repo-renderer/site'
node --check renderer/app.js
```

Lo script:

- copia `app.js`, `styles.css` e l'immagine hero dal renderer sorgente
- riapplica le patch necessarie alla preview Studio
- fallisce se i marker attesi nel renderer sorgente non esistono piu

Questa parte non e elegante, ma e onesta: finche Studio mantiene una snapshot locale del renderer, serve un sync esplicito e verificabile.
