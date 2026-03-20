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
- `STUDIO_RENDERER_ORIGIN`
- `STUDIO_REVERSE_GEOCODE_TEMPLATE`

`STUDIO_RENDERER_ORIGIN` punta al renderer del diario usato dalla preview. Default: `http://127.0.0.1:4173`.

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

Il repo e separato dal diario personale, ma la preview usa ancora JS/CSS del renderer esterno configurato con `STUDIO_RENDERER_ORIGIN`. Il prossimo step e spezzare anche quel coupling.
