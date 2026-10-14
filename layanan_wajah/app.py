# ==========================================
# PEMBAHASAN / BAGIAN: API FastAPI Penilaian Wajah
# ==========================================
from __future__ import annotations

import hmac
from typing import Annotated

from fastapi import Depends, FastAPI, File, Form, Header, HTTPException, UploadFile

from layanan_wajah.mesin import MesinWajah
from layanan_wajah.pengaturan import KUNCI_LAYANAN

app = FastAPI(title="Layanan Wajah Smart Campus", version="1.0.0")
mesin = MesinWajah()


def pastikan_kunci(x_kunci_layanan: Annotated[str | None, Header()] = None) -> None:
    if not KUNCI_LAYANAN:
        raise HTTPException(status_code=503, detail="WAJAH_KUNCI belum dikonfigurasi.")

    if x_kunci_layanan is None or not hmac.compare_digest(x_kunci_layanan, KUNCI_LAYANAN):
        raise HTTPException(status_code=401, detail="Kunci layanan tidak sah.")


def dapatkan_mesin() -> MesinWajah:
    return mesin


@app.get("/v1/kesehatan")
def kesehatan(
    mesin_wajah: Annotated[MesinWajah, Depends(dapatkan_mesin)],
    _: Annotated[None, Depends(pastikan_kunci)],
) -> dict[str, object]:
    return mesin_wajah.kesehatan()


@app.post("/v1/embedding")
async def buat_embedding(
    foto: Annotated[UploadFile, File()],
    mesin_wajah: Annotated[MesinWajah, Depends(dapatkan_mesin)],
    _: Annotated[None, Depends(pastikan_kunci)],
    model: Annotated[str | None, Form()] = None,
) -> dict[str, object]:
    try:
        return mesin_wajah.embedding(await foto.read(10 * 1024 * 1024 + 1), model)
    except (RuntimeError, ValueError) as kesalahan:
        raise HTTPException(status_code=422, detail=str(kesalahan)) from kesalahan


@app.post("/v1/verifikasi")
async def verifikasi(
    foto: Annotated[UploadFile, File()],
    referensi_base64: Annotated[str, Form()],
    mesin_wajah: Annotated[MesinWajah, Depends(dapatkan_mesin)],
    _: Annotated[None, Depends(pastikan_kunci)],
    model: Annotated[str | None, Form()] = None,
) -> dict[str, object]:
    try:
        return mesin_wajah.verifikasi(await foto.read(10 * 1024 * 1024 + 1), referensi_base64, model)
    except (RuntimeError, ValueError) as kesalahan:
        raise HTTPException(status_code=422, detail=str(kesalahan)) from kesalahan
