# ==========================================
# PEMBAHASAN / BAGIAN: Mesin Stateless Ekstraksi dan Verifikasi Wajah
# ==========================================
from __future__ import annotations

import base64
import binascii
import time
from typing import Any

import cv2
import numpy as np

from layanan_wajah.detektor import DetektorYuNet
from layanan_wajah.pengaturan import MODEL_AKTIF
from layanan_wajah.penyedia_model import PenyediaModel, buat_penyedia


class MesinWajah:
    def __init__(self, detektor: Any | None = None) -> None:
        self.detektor = detektor
        self._penyedia: dict[str, PenyediaModel] = {}

    def kesehatan(self) -> dict[str, Any]:
        return {
            "status": "siap",
            "model_aktif": MODEL_AKTIF,
            "model_didukung": ["ArcFace", "FaceNet", "SFace"],
            "stateless": True,
        }

    def embedding(self, isi_foto: bytes, nama_model: str | None) -> dict[str, Any]:
        mulai = time.perf_counter()
        gambar = self._baca_gambar(isi_foto)
        wajah, kualitas = self._deteksi(gambar)
        embedding = None

        if len(wajah) == 1:
            penyedia = self._ambil_penyedia(nama_model)
            embedding = penyedia.hasilkan(gambar, wajah[0]["landmark"])

        hasil: dict[str, Any] = {
            "model": nama_model or MODEL_AKTIF,
            "face_found": len(wajah) == 1,
            "face_count": len(wajah),
            "quality": kualitas,
            "processing_ms": round((time.perf_counter() - mulai) * 1000, 3),
            "embedding_dimension": int(embedding.size) if embedding is not None else None,
            "embedding_base64": self._encode_embedding(embedding) if embedding is not None else None,
        }
        return hasil

    def verifikasi(
        self,
        isi_foto: bytes,
        embedding_referensi: str,
        nama_model: str | None,
    ) -> dict[str, Any]:
        hasil = self.embedding(isi_foto, nama_model)
        skor = None

        if hasil["face_found"] and hasil["embedding_base64"]:
            probe = self._decode_embedding(hasil["embedding_base64"])
            referensi = self._decode_embedding(embedding_referensi)

            if probe.shape != referensi.shape:
                raise ValueError("Dimensi embedding probe dan referensi tidak sama.")

            skor = float(np.clip(np.dot(probe, referensi), -1.0, 1.0))

        hasil.pop("embedding_base64")
        hasil["score"] = skor
        return hasil

    def _deteksi(self, gambar: np.ndarray) -> tuple[list[dict[str, Any]], dict[str, float | None]]:
        detektor = self.detektor or DetektorYuNet()
        self.detektor = detektor
        wajah = detektor.deteksi(gambar)
        kualitas: dict[str, float | None] = {
            "detector_confidence": None,
            "face_area_ratio": None,
            "brightness": None,
            "blur_laplacian_variance": None,
        }

        if wajah:
            utama = max(wajah, key=lambda nilai: float(nilai["keyakinan"]))
            x, y, lebar, tinggi = [float(nilai) for nilai in utama["kotak"]]
            tinggi_gambar, lebar_gambar = gambar.shape[:2]
            x_awal = max(0, int(x))
            y_awal = max(0, int(y))
            x_akhir = min(lebar_gambar, int(x + lebar))
            y_akhir = min(tinggi_gambar, int(y + tinggi))
            potongan = gambar[y_awal:y_akhir, x_awal:x_akhir]

            if potongan.size:
                abu = cv2.cvtColor(potongan, cv2.COLOR_BGR2GRAY)
                kualitas = {
                    "detector_confidence": float(utama["keyakinan"]),
                    "face_area_ratio": float((lebar * tinggi) / (lebar_gambar * tinggi_gambar)),
                    "brightness": float(np.mean(abu)),
                    "blur_laplacian_variance": float(cv2.Laplacian(abu, cv2.CV_64F).var()),
                }

        return wajah, kualitas

    def _ambil_penyedia(self, nama_model: str | None) -> PenyediaModel:
        nama = nama_model or MODEL_AKTIF

        if nama not in self._penyedia:
            self._penyedia[nama] = buat_penyedia(nama)

        return self._penyedia[nama]

    @staticmethod
    def _baca_gambar(isi_foto: bytes) -> np.ndarray:
        if not isi_foto or len(isi_foto) > 10 * 1024 * 1024:
            raise ValueError("Foto kosong atau melebihi batas 10 MB.")

        gambar = cv2.imdecode(np.frombuffer(isi_foto, dtype=np.uint8), cv2.IMREAD_COLOR)

        if gambar is None or gambar.size == 0:
            raise ValueError("Berkas bukan gambar yang dapat dibaca.")

        return gambar

    @staticmethod
    def _encode_embedding(embedding: np.ndarray) -> str:
        return base64.b64encode(embedding.astype("<f4").tobytes()).decode("ascii")

    @staticmethod
    def _decode_embedding(embedding_base64: str) -> np.ndarray:
        try:
            isi = base64.b64decode(embedding_base64, validate=True)
        except (binascii.Error, ValueError) as kesalahan:
            raise ValueError("Embedding referensi bukan base64 yang valid.") from kesalahan

        if not isi or len(isi) % 4 != 0 or len(isi) > 64 * 1024:
            raise ValueError("Ukuran embedding referensi tidak valid.")

        nilai = np.frombuffer(isi, dtype="<f4").astype(np.float32)

        if not np.isfinite(nilai).all() or not np.isclose(np.linalg.norm(nilai), 1.0, atol=0.02):
            raise ValueError("Embedding referensi harus berupa vektor ternormalisasi.")

        return nilai
