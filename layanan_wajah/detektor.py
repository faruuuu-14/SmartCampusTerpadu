# ==========================================
# PEMBAHASAN / BAGIAN: Detektor YuNet Bersama untuk Semua Model
# ==========================================
from __future__ import annotations

from pathlib import Path

import cv2
import numpy as np

from layanan_wajah.pengaturan import MODEL_YUNET


class DetektorYuNet:
    def __init__(self, jalur_model: Path = MODEL_YUNET) -> None:
        if not jalur_model.is_file():
            raise RuntimeError(
                f"Model detektor YuNet tidak ditemukan: {jalur_model}. "
                "Unduh model sesuai petunjuk README layanan_wajah."
            )

        self._detektor = cv2.FaceDetectorYN.create(
            str(jalur_model),
            "",
            (320, 320),
            0.7,
            0.3,
            5000,
        )

    def deteksi(self, gambar: np.ndarray) -> list[dict[str, np.ndarray | float]]:
        tinggi, lebar = gambar.shape[:2]
        self._detektor.setInputSize((lebar, tinggi))
        _, hasil = self._detektor.detect(gambar)

        if hasil is None:
            return []

        wajah = []
        for baris in hasil:
            wajah.append(
                {
                    "kotak": baris[:4].astype(np.float32),
                    "landmark": baris[4:14].reshape(5, 2).astype(np.float32),
                    "keyakinan": float(baris[14]),
                }
            )

        return wajah
