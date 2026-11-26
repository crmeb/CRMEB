#!/usr/bin/env python3
"""Export local theme + material-library attachments into install SQL and uploads/demo."""

from __future__ import annotations

import shutil
from pathlib import Path

import pymysql

ROOT = Path("/Users/wuhaotian/Code/ZhongBang/crmebbz")
PUBLIC = ROOT / "backend/public"
INSTALL_DATA = PUBLIC / "install/data"
UPLOADS = PUBLIC / "uploads"
DEMO_HOST = "http://demo.crmeb.com"
LOCAL_HOSTS = ("http://v8.bz.net", "https://v8.bz.net")

THEME_COLS = [
    "id", "version", "title", "info", "type",
    "home_data", "home_image", "home_data_id", "home_data_update_time",
    "category_data", "category_image", "category_data_id", "category_data_update_time",
    "detail_data", "detail_image", "detail_data_id", "detail_data_update_time",
    "user_data", "user_image", "user_data_id", "user_data_update_time",
    "theme_data", "theme_data_id", "theme_data_update_time",
    "home_default_data", "home_default_image",
    "category_default_data", "category_default_image",
    "detail_default_data", "detail_default_image",
    "user_default_data", "user_default_image",
    "theme_default_data", "page_type", "is_use", "is_del", "add_time", "up_time",
]

ATT_COLS = [
    "att_id", "name", "att_dir", "satt_dir", "att_size", "att_type", "pid", "time",
    "image_type", "module_type", "real_name", "scan_token", "type",
]

CAT_COLS = ["id", "pid", "name", "enname", "type"]


def sql_quote(value) -> str:
    if value is None:
        return "NULL"
    if isinstance(value, bool):
        return "1" if value else "0"
    if isinstance(value, (int, float)) and not isinstance(value, bool):
        return str(value)
    s = str(value)
    s = s.replace("\\", "\\\\").replace("'", "\\'")
    return "'" + s + "'"


def write_insert(path: Path, table: str, cols: list[str], rows: list[list], header: str) -> None:
    col_sql = ", ".join(f"`{c}`" for c in cols)
    lines = [header, "", f"INSERT INTO `{table}` ({col_sql}) VALUES"]
    rendered = ["(" + ", ".join(sql_quote(v) for v in row) + ")" for row in rows]
    lines.append(",\n".join(rendered) + ";")
    lines.append("")
    path.write_text("\n".join(lines), encoding="utf-8")
    print(f"wrote {path.relative_to(ROOT)} rows={len(rows)} bytes={path.stat().st_size}")


def rewrite_paths(text: str) -> str:
    """Rewrite local upload hosts/paths to demo install placeholders."""
    if not text:
        return text
    out = text
    # Absolute host → demo placeholder (plain + JSON-escaped)
    for host in LOCAL_HOSTS:
        out = out.replace(host, DEMO_HOST)
        out = out.replace(host.replace("/", "\\/"), DEMO_HOST.replace("/", "\\/"))
    # Path segment: attach → demo/attach (plain + JSON-escaped)
    out = out.replace("/uploads/attach/", "/uploads/demo/attach/")
    out = out.replace("\\/uploads\\/attach\\/", "\\/uploads\\/demo\\/attach\\/")
    return out


def to_demo_rel(path: str) -> str:
    if not path:
        return path
    p = path.strip()
    for host in LOCAL_HOSTS + (DEMO_HOST,):
        if p.startswith(host):
            p = p[len(host) :]
    if p.startswith("/uploads/attach/"):
        p = "/uploads/demo/attach/" + p[len("/uploads/attach/") :]
    elif p.startswith("uploads/attach/"):
        p = "/uploads/demo/attach/" + p[len("uploads/attach/") :]
    return p


IMAGE_FIELDS = {
    "home_image", "category_image", "detail_image", "user_image",
    "home_default_image", "category_default_image",
    "detail_default_image", "user_default_image",
}


def copy_to_demo(rel_att_dir: str) -> Path:
    """Copy a public-relative /uploads/attach/... file into uploads/demo/attach/..."""
    src_rel = rel_att_dir.lstrip("/")
    if src_rel.startswith("uploads/demo/"):
        dest = PUBLIC / src_rel
        if not dest.is_file():
            raise FileNotFoundError(f"missing demo file: {dest}")
        return dest
    if not src_rel.startswith("uploads/attach/"):
        raise ValueError(f"unexpected att_dir: {rel_att_dir}")
    src = PUBLIC / src_rel
    if not src.is_file():
        raise FileNotFoundError(f"missing source file: {src}")
    dest_rel = "uploads/demo/attach/" + src_rel[len("uploads/attach/") :]
    dest = PUBLIC / dest_rel
    dest.parent.mkdir(parents=True, exist_ok=True)
    if not dest.exists() or dest.stat().st_size != src.stat().st_size:
        shutil.copy2(src, dest)
    return dest


def materialize_theme_image(path: str) -> str:
    """Copy a theme preview image into uploads/demo and return the demo-relative path."""
    if not path or not str(path).strip():
        return path
    p = str(path).strip()
    for host in LOCAL_HOSTS + (DEMO_HOST,):
        if p.startswith(host):
            p = p[len(host) :]
    if not p.startswith("/"):
        p = "/" + p

    if p.startswith("/uploads/demo/"):
        dest = PUBLIC / p.lstrip("/")
        if not dest.is_file():
            raise FileNotFoundError(f"missing demo theme image: {dest}")
        return p

    if p.startswith("/uploads/attach/"):
        copy_to_demo(p)
        return to_demo_rel(p)

    if p.startswith("/statics/"):
        src = PUBLIC / p.lstrip("/")
        if not src.is_file():
            raise FileNotFoundError(f"missing statics theme image: {src}")
        dest_rel = f"/uploads/demo/attach/theme/{src.name}"
        dest = PUBLIC / dest_rel.lstrip("/")
        dest.parent.mkdir(parents=True, exist_ok=True)
        if not dest.exists() or dest.stat().st_size != src.stat().st_size:
            shutil.copy2(src, dest)
        return dest_rel

    raise ValueError(f"unsupported theme image path: {path}")


def main() -> None:
    conn = pymysql.connect(
        host="127.0.0.1", user="root", password="root",
        database="crmeb_v8", charset="utf8mb4",
    )
    cur = conn.cursor(pymysql.cursors.DictCursor)

    cur.execute("SELECT * FROM eb_theme WHERE is_use=1 AND is_del=0 ORDER BY id DESC LIMIT 1")
    theme = cur.fetchone()
    if not theme:
        raise SystemExit("no active theme in local DB")

    cur.execute("SELECT * FROM eb_system_attachment_category ORDER BY id")
    cats = list(cur.fetchall())

    cur.execute("SELECT * FROM eb_system_attachment ORDER BY att_id")
    atts = list(cur.fetchall())
    cur.close()
    conn.close()

    # --- copy attachment files ---
    copied = 0
    for a in atts:
        copy_to_demo(a["att_dir"])
        if a.get("satt_dir") and a["satt_dir"] != a["att_dir"]:
            try:
                copy_to_demo(a["satt_dir"])
            except FileNotFoundError:
                pass
        copied += 1
    print(f"copied/verified {copied} attachment files into uploads/demo")

    # --- theme preview images (home/category/detail/user) ---
    image_map: dict[str, str] = {}
    for col in IMAGE_FIELDS:
        raw = theme.get(col) or ""
        if not raw:
            continue
        demo = materialize_theme_image(raw)
        image_map[col] = demo
        print(f"theme image {col}: {raw} -> {demo}")

    # --- theme seed (id=1, is_use=1) ---
    theme_row = []
    for col in THEME_COLS:
        val = theme.get(col)
        if col == "id":
            val = 1
        elif col == "is_use":
            val = 1
        elif col == "is_del":
            val = 0
        elif col in image_map:
            val = image_map[col]
        elif isinstance(val, str):
            val = rewrite_paths(val)
        theme_row.append(val)
    write_insert(
        INSTALL_DATA / "theme_data.sql",
        "eb_theme",
        THEME_COLS,
        [theme_row],
        "-- eb_theme: exported from local active theme (id remapped to 1)",
    )

    # --- attachment category ---
    cat_rows = [[c[col] for col in CAT_COLS] for c in cats]
    write_insert(
        INSTALL_DATA / "system_attachment_category_data.sql",
        "eb_system_attachment_category",
        CAT_COLS,
        cat_rows,
        "-- eb_system_attachment_category default demo data",
    )

    # --- attachments (att_id remapped 1..n, paths under /uploads/demo/) ---
    att_rows = []
    for i, a in enumerate(atts, start=1):
        row = []
        for col in ATT_COLS:
            if col == "att_id":
                row.append(i)
            elif col in ("att_dir", "satt_dir"):
                row.append(to_demo_rel(a.get(col) or ""))
            else:
                row.append(a.get(col))
        att_rows.append(row)
    write_insert(
        INSTALL_DATA / "system_attachment_data.sql",
        "eb_system_attachment",
        ATT_COLS,
        att_rows,
        "-- eb_system_attachment: material-library demo images under /uploads/demo",
    )

    # sanity checks
    blob = "\n".join(str(v) for v in theme_row if isinstance(v, str))
    plain = blob.replace("\\/", "/")
    if "v8.bz.net" in plain:
        raise SystemExit("theme still contains local host v8.bz.net")
    if "/uploads/attach/" in plain:
        raise SystemExit("theme still contains /uploads/attach/ (expected /uploads/demo/attach/)")
    for row in att_rows:
        for p in (row[2], row[3]):
            if p and not str(p).startswith("/uploads/demo/"):
                raise SystemExit(f"attachment path not under /uploads/demo/: {p}")
    print("ok: theme + attachment seeds ready")


if __name__ == "__main__":
    main()
