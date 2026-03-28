#!/usr/bin/env python3
from __future__ import annotations

import argparse
import shutil
from pathlib import Path


SYNC_FILES = ('renderer-env.js', 'renderer-day-utils.js', 'renderer-core.js', 'styles.css', 'app.js')


def sync_renderer(source_root: Path, studio_root: Path) -> None:
    renderer_root = studio_root / 'renderer'
    renderer_root.mkdir(parents=True, exist_ok=True)
    for name in SYNC_FILES:
        source_path = source_root / name
        if not source_path.is_file():
            raise FileNotFoundError(f'Missing source file: {source_path}')
        shutil.copy2(source_path, renderer_root / name)


def main() -> int:
    parser = argparse.ArgumentParser(description='Sync renderer snapshot into Diary Studio.')
    parser.add_argument('source', help='Path to the renderer repo site root (the folder containing renderer-env.js, renderer-day-utils.js, renderer-core.js, app.js and styles.css).')
    parser.add_argument('--studio-root', default=str(Path(__file__).resolve().parents[1]), help='Diary Studio repo root.')
    args = parser.parse_args()

    source_root = Path(args.source).expanduser().resolve()
    studio_root = Path(args.studio_root).expanduser().resolve()
    sync_renderer(source_root, studio_root)
    print(f'Synced renderer from {source_root} to {studio_root / "renderer"}')
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
