#!/usr/bin/env python3
"""Export local products id 6-9 into install SQL + uploads/demo (remap ids to 1-4)."""

from __future__ import annotations

import base64
import hashlib
import json
import re
import subprocess
import urllib.request
from pathlib import Path

ROOT = Path("/Users/wuhaotian/Code/ZhongBang/crmebbz")
INSTALL_DATA = ROOT / "backend/public/install/data"
NORMAL_DATA = ROOT / "backend/modules/product/normal/data"
DEST_UPLOADS = ROOT / "backend/public/uploads/demo"
DEMO_HOST = "http://demo.crmeb.com"
SRC_IDS = [6, 7, 8, 9]
ID_MAP = {6: 1, 7: 2, 8: 3, 9: 4}

MYSQL = [
    "mysql", "-h127.0.0.1", "-P3306", "-uroot", "-proot",
    "crmeb_v8", "--default-character-set=utf8mb4", "-N", "-B",
]


def mysql_query(sql: str) -> str:
    r = subprocess.run(MYSQL + ["-e", sql], capture_output=True, text=True)
    if r.returncode != 0:
        raise RuntimeError(r.stderr)
    return r.stdout


def mysql_tsv(sql: str) -> list[list[str]]:
    out = mysql_query(sql)
    rows = []
    for line in out.splitlines():
        if not line.strip():
            continue
        rows.append(line.split("\t"))
    return rows


def sql_quote(value) -> str:
    if value is None:
        return "NULL"
    if isinstance(value, (int, float)) and not isinstance(value, bool):
        return str(value)
    s = str(value)
    s = s.replace("\\", "\\\\").replace("'", "\\'")
    return "'" + s + "'"


def write_insert(path: Path, table: str, cols: list[str], rows: list[list], header: str) -> None:
    col_sql = ", ".join(f"`{c}`" for c in cols)
    lines = [header, "", f"INSERT INTO `{table}` ({col_sql}) VALUES"]
    rendered = []
    for row in rows:
        rendered.append("(" + ", ".join(sql_quote(v) for v in row) + ")")
    lines.append(",\n".join(rendered) + ";")
    lines.append("")
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text("\n".join(lines), encoding="utf-8")
    print(f"wrote {path.relative_to(ROOT)} rows={len(rows)} bytes={path.stat().st_size}")


def normalize_url(raw: str) -> str:
    u = raw.strip().replace("\\/", "/")
    while "\\/" in u:
        u = u.replace("\\/", "/")
    u = u.replace("&quot;", "").replace('"', "").strip()
    if u.startswith("//"):
        u = "https:" + u
    elif u.startswith("http://"):
        # keep, or upgrade jd to https
        if "360buyimg.com" in u:
            u = "https://" + u[len("http://") :]
    return u


def collect_urls_from_text(text: str) -> set[str]:
    urls: set[str] = set()
    if not text:
        return urls
    normalized = text
    while "\\/" in normalized:
        normalized = normalized.replace("\\/", "/")
    normalized = normalized.replace("&quot;", '"').replace("&amp;", "&")
    for m in re.finditer(r"https?://[^'\"\s<>]+", normalized):
        urls.add(normalize_url(m.group(0).rstrip("\\).,;'\"<>")))
    for m in re.finditer(r"//img[^'\"\s<>]+360buyimg\.com[^'\"\s<>]+", normalized):
        urls.add(normalize_url(m.group(0).rstrip("\\).,;'\"<>")))
    for m in re.finditer(r"/uploads/[^'\"\s<>]+", normalized):
        urls.add(m.group(0).rstrip("\\).,;'\"<>"))
    return urls


class ImageStore:
    def __init__(self, root: Path):
        self.root = root
        self.ext_dir = root / "attach" / "external"
        self.desc_dir = root / "attach" / "desc"
        self.local_dir = root / "attach" / "local"
        self.ext_dir.mkdir(parents=True, exist_ok=True)
        self.desc_dir.mkdir(parents=True, exist_ok=True)
        self.local_dir.mkdir(parents=True, exist_ok=True)
        self.map: dict[str, str] = {}  # original url/path -> demo url

    def demo_url(self, rel: str) -> str:
        return f"{DEMO_HOST}/uploads/demo/{rel}"

    def remember(self, keys: set[str], demo: str) -> str:
        for k in keys:
            if not k:
                continue
            self.map[k] = demo
            self.map[k.replace("/", "\\/")] = demo.replace("/", "\\/")
        return demo

    def download(self, url: str) -> str:
        raw = url.strip()
        # already demo asset
        if raw.startswith(DEMO_HOST) or raw.startswith("/uploads/demo/") or "uploads/demo/" in raw.replace("\\/", "/"):
            if raw.startswith("/uploads/demo/"):
                return DEMO_HOST + raw
            if raw.startswith("uploads/demo/"):
                return DEMO_HOST + "/" + raw
            return raw if raw.startswith("http") else DEMO_HOST + (raw if raw.startswith("/") else "/" + raw)

        if raw in self.map:
            return self.map[raw]

        url = normalize_url(raw)
        if url in self.map:
            return self.map[url]

        keys = {raw, url, url.replace("https://", "http://"), url.replace("https://", "//"), url.replace("http://", "//")}

        if url.startswith("/uploads/") and not url.startswith("/uploads/demo/"):
            src = ROOT / "backend/public" / url.lstrip("/")
            if src.is_file():
                name = src.name
                dest = self.local_dir / name
                if not dest.exists():
                    dest.write_bytes(src.read_bytes())
                return self.remember(keys, self.demo_url(f"attach/local/{name}"))
            print(f"WARN local missing {url}")
            return raw

        if not url.startswith("http"):
            return raw

        ext = Path(url.split("?")[0]).suffix.lower()
        if ext not in {".jpg", ".jpeg", ".png", ".gif", ".webp"}:
            ext = ".jpg"
        name = hashlib.md5(url.encode()).hexdigest() + ext
        dest = self.ext_dir / name
        if not dest.exists():
            try:
                req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0"})
                with urllib.request.urlopen(req, timeout=40) as resp:
                    dest.write_bytes(resp.read())
                print(f"downloaded {url[:90]} -> {name} ({dest.stat().st_size})")
            except Exception as e:
                print(f"WARN download fail {url[:90]}: {e}")
                return raw
        return self.remember(keys, self.demo_url(f"attach/external/{name}"))

    def save_base64(self, mime: str, b64: str, product_id: int) -> str:
        data = base64.b64decode(re.sub(r"\s+", "", b64))
        ext = {"jpeg": ".jpg", "jpg": ".jpg", "png": ".png", "gif": ".gif", "webp": ".webp"}.get(mime, ".png")
        name = hashlib.md5(data).hexdigest() + ext
        out = self.desc_dir / str(product_id)
        out.mkdir(parents=True, exist_ok=True)
        (out / name).write_bytes(data)
        return self.demo_url(f"attach/desc/{product_id}/{name}")

    def rewrite_text(self, text: str, new_product_id: int | None = None) -> str:
        if not text:
            return text

        # JSON 字段里常见 \/ 转义，先还原再替换（结果里 / 无需再转义）
        text = text.replace("\\/", "/")

        def repl_b64(m: re.Match) -> str:
            return self.save_base64(m.group(1), m.group(2), new_product_id or 0)

        text = re.sub(
            r"data:image/(jpeg|jpg|png|gif|webp);base64,([A-Za-z0-9+/=\s]+)",
            repl_b64,
            text,
            flags=re.I,
        )

        patterns = [
            r"https?://[^'\"\s<>]+",
            r"//img[^'\"\s<>]+360buyimg\.com[^'\"\s<>]+",
            r"/uploads/(?!demo/)[^'\"\s<>]+",
        ]
        spans: list[tuple[int, int, str]] = []
        for pat in patterns:
            for m in re.finditer(pat, text):
                spans.append((m.start(), m.end(), m.group(0)))
        used: list[tuple[int, int]] = []
        chosen: list[tuple[int, int, str]] = []
        for s, e, raw in sorted(spans, key=lambda x: (-(x[1] - x[0]), x[0])):
            if any(s < ue and e > us for us, ue in used):
                continue
            used.append((s, e))
            chosen.append((s, e, raw))
        for s, e, raw in sorted(chosen, key=lambda x: -x[0]):
            plain = normalize_url(raw) if raw.startswith("//") or raw.startswith("http") else raw
            if "uploads/demo/" in plain or plain.startswith(DEMO_HOST):
                continue
            demo = self.download(plain if plain.startswith("http") or plain.startswith("/uploads/") else raw)
            if not demo or demo in {plain, raw}:
                continue
            text = text[:s] + demo + text[e:]

        text = re.sub(r"https?://v8\.bz\.net", DEMO_HOST, text)
        return text


def fetch_products() -> list[dict]:
    cols = [r[0] for r in mysql_tsv("SHOW COLUMNS FROM eb_product_center")]
    id_list = ",".join(str(i) for i in SRC_IDS)
    # JSON export via mysql is messy; use SELECT and map by column order with -B
    # Escape: use SELECT CONCAT_WS with unlikely sep? Better: dump to JSON via PHP or python mysql connector.
    # Fallback: one query per product with HEX for text fields? Use python pymysql if available.
    try:
        import pymysql  # type: ignore
    except ImportError:
        pymysql = None

    if pymysql is None:
        # install? try
        subprocess.run(["pip3", "install", "pymysql", "-q"], check=False)
        import pymysql  # type: ignore

    conn = pymysql.connect(host="127.0.0.1", port=3306, user="root", password="root", database="crmeb_v8", charset="utf8mb4")
    cur = conn.cursor(pymysql.cursors.DictCursor)
    cur.execute(f"SELECT * FROM eb_product_center WHERE id IN ({id_list}) ORDER BY id")
    products = list(cur.fetchall())
    cur.execute(f"SELECT * FROM eb_pt_normal WHERE product_id IN ({id_list}) ORDER BY product_id")
    normals = {r["product_id"]: r for r in cur.fetchall()}
    cur.execute(f"SELECT * FROM eb_pt_normal_sku WHERE product_id IN ({id_list}) ORDER BY product_id, id")
    skus = list(cur.fetchall())
    cur.execute(f"SELECT * FROM eb_store_product_description WHERE product_id IN ({id_list})")
    descs = list(cur.fetchall())
    cur.execute(f"SELECT * FROM eb_store_product_cate WHERE product_id IN ({id_list})")
    cates = list(cur.fetchall())
    cur.close()
    conn.close()
    return products, normals, skus, descs, cates, cols


def main() -> None:
    if DEST_UPLOADS.exists():
        # only clear attach under demo, keep folder
        import shutil
        shutil.rmtree(DEST_UPLOADS, ignore_errors=True)
    DEST_UPLOADS.mkdir(parents=True, exist_ok=True)
    store = ImageStore(DEST_UPLOADS)

    products, normals, skus, descs, cates, _cols = fetch_products()
    print(f"products={len(products)} skus={len(skus)} descs={len(descs)} cates={len(cates)}")

    # remap helpers
    def map_pid(pid: int) -> int:
        return ID_MAP[int(pid)]

    # product_center rows
    pc_cols = [
        "id", "product_type", "name", "intro", "params_list", "custom_form", "protection_list",
        "label_list", "video_link", "keyword", "image", "recommend_list", "recommend_image",
        "slider_image", "price", "original_price", "vip_price", "stock", "sales", "virtual_sales",
        "views", "cate_id", "sort_order", "unit_name", "spec_type", "delivery_methods",
        "freight_config", "is_limit", "limit_type", "limit_num", "min_qty", "is_gift", "gift_price",
        "give_integral", "give_coupon_ids", "is_show", "is_del", "is_sub", "is_vip", "vip_product",
        "presale", "presale_start_time", "presale_end_time", "presale_day", "valid_type",
        "valid_start_time", "valid_end_time", "valid_days", "service_region", "service_address",
        "add_time", "update_time",
    ]
    pc_rows = []
    for p in products:
        new_id = map_pid(p["id"])
        row = []
        for c in pc_cols:
            v = p.get(c)
            if c == "id":
                v = new_id
            elif c in ("image", "recommend_image", "video_link", "slider_image", "recommend_list"):
                v = store.rewrite_text("" if v is None else str(v), new_id)
            elif c in ("delivery_methods", "freight_config", "service_region", "params_list", "custom_form"):
                if v is None:
                    v = None
                elif isinstance(v, (dict, list)):
                    v = json.dumps(v, ensure_ascii=False)
                else:
                    v = store.rewrite_text(str(v), new_id)
            elif c in ("is_show",):
                v = 1
            elif c in ("is_del",):
                v = 0
            row.append(v)
        pc_rows.append(row)

    write_insert(
        INSTALL_DATA / "product_center_data.sql",
        "eb_product_center",
        pc_cols,
        pc_rows,
        "-- eb_product_center default demo products",
    )

    # descriptions
    desc_cols = ["product_id", "activity_type", "description", "type"]
    desc_rows = []
    for d in descs:
        new_id = map_pid(d["product_id"])
        html = store.rewrite_text("" if d.get("description") is None else str(d["description"]), new_id)
        desc_rows.append([new_id, d.get("activity_type") or "normal", html, d.get("type") if d.get("type") is not None else 0])
    # ensure order by new id
    desc_rows.sort(key=lambda r: r[0])
    write_insert(
        INSTALL_DATA / "store_product_description_data.sql",
        "eb_store_product_description",
        desc_cols,
        desc_rows,
        "-- eb_store_product_description default demo data",
    )

    # product cate
    cate_rows = []
    if cates:
        rid = 1
        for c in cates:
            cate_rows.append([rid, map_pid(c["product_id"]), c["cate_id"], c.get("add_time") or 0, c.get("cate_pid") or 0, c.get("status") if c.get("status") is not None else 1])
            rid += 1
    else:
        rid = 1
        for p in products:
            cate_rows.append([rid, map_pid(p["id"]), p.get("cate_id") or 0, p.get("add_time") or 0, 0, 1])
            rid += 1
    write_insert(
        INSTALL_DATA / "store_product_cate_data.sql",
        "eb_store_product_cate",
        ["id", "product_id", "cate_id", "add_time", "cate_pid", "status"],
        cate_rows,
        "-- eb_store_product_cate default demo data",
    )

    # pt_normal
    pn_cols = ["id", "product_id", "weight", "volume", "add_time", "update_time"]
    pn_rows = []
    for old_pid in SRC_IDS:
        n = normals.get(old_pid)
        if not n:
            continue
        new_id = map_pid(old_pid)
        pn_rows.append([new_id, new_id, n.get("weight") or "0.00", n.get("volume") or "0.00", n.get("add_time") or 0, n.get("update_time") or 0])
    write_insert(
        NORMAL_DATA / "pt_normal_data.sql",
        "eb_pt_normal",
        pn_cols,
        pn_rows,
        "-- eb_pt_normal default demo data (loaded by normal Installer)",
    )

    # sku - no brokerage columns
    sku_cols = [
        "id", "product_id", "suk", "price", "original_price", "cost", "vip_price", "stock",
        "bar_code", "bar_code_number", "weight", "volume", "unit_name", "image", "unique_code",
        "is_default_select", "create_time", "update_time", "is_show",
    ]
    sku_rows = []
    new_sku_id = 1
    for s in skus:
        new_pid = map_pid(s["product_id"])
        img = store.rewrite_text("" if s.get("image") is None else str(s["image"]), new_pid)
        ct = s.get("create_time")
        ut = s.get("update_time")
        if hasattr(ct, "strftime"):
            ct = ct.strftime("%Y-%m-%d %H:%M:%S")
        if hasattr(ut, "strftime"):
            ut = ut.strftime("%Y-%m-%d %H:%M:%S")
        suk = s.get("suk") or ""
        if isinstance(suk, (dict, list)):
            suk = json.dumps(suk, ensure_ascii=False)
        sku_rows.append([
            new_sku_id, new_pid, suk, s.get("price") or "0.00", s.get("original_price") or "0.00",
            s.get("cost") or "0.00", s.get("vip_price") or "0.00", s.get("stock") or 0,
            s.get("bar_code") or "", s.get("bar_code_number") or "", s.get("weight") or "0.00",
            s.get("volume") or "0.00", s.get("unit_name") or "", img, s.get("unique_code") or "",
            s.get("is_default_select") if s.get("is_default_select") is not None else 1,
            ct, ut, s.get("is_show") if s.get("is_show") is not None else 1,
        ])
        new_sku_id += 1
    write_insert(
        NORMAL_DATA / "pt_normal_sku_data.sql",
        "eb_pt_normal_sku",
        sku_cols,
        sku_rows,
        "-- eb_pt_normal_sku default demo data (loaded by normal Installer)",
    )

    # validation
    bad = []
    for path in [
        INSTALL_DATA / "product_center_data.sql",
        INSTALL_DATA / "store_product_description_data.sql",
        NORMAL_DATA / "pt_normal_sku_data.sql",
    ]:
        t = path.read_text(encoding="utf-8")
        if "360buyimg.com" in t:
            bad.append((path.name, "360buyimg"))
        if "data:image" in t:
            bad.append((path.name, "base64 leftover"))
        if "v8.bz.net" in t:
            bad.append((path.name, "v8.bz.net"))
    files = sum(1 for p in DEST_UPLOADS.rglob("*") if p.is_file())
    print("validation:", bad or "ok")
    print(f"demo files={files} size_mb={sum(p.stat().st_size for p in DEST_UPLOADS.rglob('*') if p.is_file())/1024/1024:.1f}")


if __name__ == "__main__":
    main()
